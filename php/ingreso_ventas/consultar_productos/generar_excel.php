<?php
/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/

/* ------------------------------------------------------------------------------------------------------------
   -------------------------------- INICIO ITred Spa generar_excel .PHP ---------------------------------------
   ------------------------------------------------------------------------------------------------------------ */

        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
if ($mysqli->connect_error) {
    http_response_code(500);
    die("Error de conexión: ".$mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

// --- Librería PhpSpreadsheet ---
require __DIR__ . '/../../../programas/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

// --- Sesión / Rol ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_usuario_final = (($_SESSION['rol'] ?? '') === 'usuario_final');

// --- Filtros (acepta POST y GET para depurar desde URL) ---
function inreq($k){ return $_POST[$k] ?? ($_GET[$k] ?? ''); }

$sku         = trim(inreq('sku'));
$lote        = trim(inreq('lote'));
$rut         = trim(inreq('rut'));
$nombre      = trim(inreq('nombre'));
$factura     = trim(inreq('numero_fact'));
$fecha_desde = trim(inreq('fecha_desde'));
$fecha_hasta = trim(inreq('fecha_hasta'));
$serial      = trim(inreq('buscar_serial')); // sólo usuario_final

$debug = isset($_GET['debug']);

// --- SQL dinámico (idéntico criterio a la pantalla) ---
if ($is_usuario_final){
    $sqlBase = "SELECT v.sku, v.fecha_despacho, v.producto, v.cantidad, v.lote,
                       v.fecha_fabricacion, v.fecha_vencimiento
                FROM venta v
                LEFT JOIN cliente c ON v.rut=c.rut
                WHERE 1=1";
} else {
    $sqlBase = "SELECT v.sku, v.rut, c.nombre, v.numero_fact, v.fecha_despacho,
                       v.producto, v.cantidad, v.lote, v.fecha_fabricacion, v.fecha_vencimiento,
                       v.n_serie_ini, v.n_serie_fin
                FROM venta v
                LEFT JOIN cliente c ON v.rut=c.rut
                WHERE 1=1";
}

$sql   = $sqlBase;
$args  = [];
$types = "";

// Filtros
if ($sku !== "")          { $sql.=" AND v.sku = ?";                    $args[]=$sku;          $types.="s"; }
if ($lote !== "")         { $sql.=" AND v.lote = ?";                   $args[]=$lote;         $types.="s"; }
if ($rut !== "")          { $sql.=" AND v.rut = ?";                    $args[]=$rut;          $types.="s"; }

// nombre: LIKE (prefijo/subcadena)
if ($nombre !== "")       { $sql.=" AND LOWER(c.nombre) LIKE LOWER(?)";$args[]="%".$nombre."%";$types.="s"; }

if ($factura !== "")      { $sql.=" AND v.numero_fact = ?";            $args[]=$factura;      $types.="s"; }
if ($fecha_desde !== "")  { $sql.=" AND v.fecha_despacho >= ?";        $args[]=$fecha_desde." 00:00:00"; $types.="s"; }
if ($fecha_hasta !== "")  { $sql.=" AND v.fecha_despacho <= ?";        $args[]=$fecha_hasta." 23:59:59"; $types.="s"; }

// serial sólo para usuario_final (rango invertido permitido)
if ($is_usuario_final && $serial !== "") {
    $sql .= " AND ? BETWEEN LEAST(v.n_serie_ini, v.n_serie_fin) AND GREATEST(v.n_serie_ini, v.n_serie_fin)";
    $args[] = $serial;
    $types .= "s";
}

$sql .= " ORDER BY v.id DESC";

// --- Depuración opcional ---
if ($debug) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ROL: ".( $_SESSION['rol'] ?? '(sin rol)' )."\n";
    echo "SQL:\n".$sql."\n\n";
    echo "Params: ".json_encode($args, JSON_UNESCAPED_UNICODE)."\n";
}

// --- Ejecutar ---
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    if ($debug) { echo "Error prepare: ".$mysqli->error; exit; }
    http_response_code(500);
    die("Error en la consulta.");
}
if ($types !== "") {
    $stmt->bind_param($types, ...$args);
}
$stmt->execute();
$res = $stmt->get_result();

if ($debug) {
    echo "Filas encontradas: ".$res->num_rows."\n";
    if ($res->num_rows === 0) { exit; }
}

// Si no hay filas, corta con mensaje claro
if ($res->num_rows === 0) {
    die("Sin datos que exportar.");
}

// --- Construir Excel ---
try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if ($is_usuario_final) {
        $headers = ['SKU','SERIE','FECHA DE DESPACHO','PRODUCTO','CANTIDAD','LOTE','FECHA DE FABRICACION','FECHA DE VENCIMIENTO'];
    } else {
        $headers = ['SKU','RUT','NOMBRE CLIENTE','Nº FACTURA','FECHA DE DESPACHO','PRODUCTO','CANTIDAD','LOTE',
                    'FECHA DE FABRICACION','FECHA DE VENCIMIENTO','SERIE DE INICIO','SERIE DE TERMINO'];
    }

    // Encabezados
    $sheet->fromArray($headers, null, 'A1');
    $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
    foreach (range(1, count($headers)) as $i) {
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
    }

    // Helper para escribir TEXTO en columna+fila (A1) – compatible con tu versión
    $putText = function($colIndex, $rowIndex, $value) use ($sheet) {
        $addr = Coordinate::stringFromColumnIndex($colIndex) . (string)$rowIndex;
        $sheet->setCellValueExplicit($addr, (string)$value, DataType::TYPE_STRING);
    };

    $row = 2;
    while ($v = $res->fetch_assoc()) {
        $fab = $v['fecha_fabricacion'] ?? '';
        $ven = $v['fecha_vencimiento'] ?? '';
        if ($ven==='' && $fab!=='') {
            try {
                $dt = new DateTime($fab);
                $dt->modify('+5 years');
                $ven = $dt->format('Y-m-d');
            } catch (Exception $e) {}
        }

         $writeDate = function($colIndex, $rowIndex, $dateStr) use ($sheet) {
            $addr = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
            if (trim($dateStr) === '') {
                $sheet->setCellValue($addr, '');
                return;
            }
            try {
                $dt = new DateTime($dateStr);
                $excelDate = XlsDate::PHPToExcel($dt);
                $sheet->setCellValue($addr, $excelDate);
                $sheet->getStyle($addr)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            } catch (Exception $e) {
                // si no se puede parsear, escribir como texto
                $sheet->setCellValueExplicit($addr, (string)$dateStr, DataType::TYPE_STRING);
            }
        };

        if ($is_usuario_final) {
            // 1 SKU (texto), 2 SERIE (texto), 3 fecha_despacho, 4 producto, 5 LOTE (texto), 6 fab, 7 ven
            $putText(1, $row, $v['sku'] ?? '');
            $putText(2, $row, $serial);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(3).$row, $v['fecha_despacho'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(4).$row, $v['producto'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(5).$row, $v['cantidad'] ?? '');
            $putText(6, $row, $v['lote'] ?? '');
            $writeDate(7, $row, $fab);
            $writeDate(8, $row, $ven);
        } else {
            // Padding series a 7 dígitos si son completamente numéricas
            $ini = $v['n_serie_ini'] ?? '';
            $fin = $v['n_serie_fin'] ?? '';
            $iniPad = (ctype_digit((string)$ini) ? str_pad((string)$ini, 7, '0', STR_PAD_LEFT) : (string)$ini);
            $finPad = (ctype_digit((string)$fin) ? str_pad((string)$fin, 7, '0', STR_PAD_LEFT) : (string)$fin);

            // Columnas:
            // 1 SKU (texto), 2 RUT (texto), 3 NOMBRE, 4 Nº FACT (texto), 5 FECHA DESP, 6 PRODUCTO,
            // 7 LOTE (texto), 8 FAB, 9 VEN, 10 SERIE INI (texto), 11 SERIE FIN (texto)
            $putText(1,  $row, $v['sku'] ?? '');
            $putText(2,  $row, $v['rut'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(3).$row, $v['nombre'] ?? '');
            $putText(4,  $row, $v['numero_fact'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(5).$row, $v['fecha_despacho'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(6).$row, $v['producto'] ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(7).$row, $v['cantidad'] ?? '');
            $putText(8,  $row, $v['lote'] ?? '');
            $writeDate(9, $row, $fab);
            $writeDate(10, $row, $ven);
            $putText(11, $row, $iniPad);
            $putText(12, $row, $finPad);
        }
        $row++;
    }

    // --- Descarga ---
    $filename = 'ventas-'.date('Y-m-d').'.xlsx';
    // Registrar exportación
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    include_once __DIR__ . '/../../inicio_sesion/seguridad/log_registros.php';
    if (function_exists('app_log')) {
        app_log('export', 'venta', "Exportación Excel: $filename", ['filters' => ['sku'=>$sku,'rut'=>$rut,'nombre'=>$nombre], 'actor' => $_SESSION['username'] ?? '']);
    }

    // Evitar que salidas anteriores (espacios, warnings, etc.) corrompan el archivo binario
    while (ob_get_level()) { ob_end_clean(); }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (\Throwable $e) {
    if ($debug) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Excepción al generar Excel: ".$e->getMessage()."\n";
        echo $e->getTraceAsString();
    } else {
        http_response_code(500);
        echo "Error generando Excel.";
    }
    exit;
}

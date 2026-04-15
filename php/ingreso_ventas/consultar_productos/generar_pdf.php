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

/*   ------------------------------------------------------------------------------------------------------------
     ------------------------------------- INICIO ITred Spa generar_pdf .PHP ------------------------------------
     ------------------------------------------------------------------------------------------------------------ */

/*   ------------------------
     -- INICIO CONEXION BD --
     ------------------------ */

        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");

/*   ---------------------
     -- FIN CONEXION BD --
     --------------------- */

// TITULO CONFIGURACION PDF

// Mostrar errores para debug y registrar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);
error_log("generar_pdf.php iniciando");

// Iniciar sesión temprana
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();
require('fpdf.php');

// Helpers padding
function digits_only($s){ return preg_replace('/\D+/', '', (string)$s); }
function pad_series_pair($iniRaw, $finRaw, $minWidth = 7){
    $iniRaw = (string)$iniRaw; $finRaw = (string)$finRaw;
    $iniDigits = digits_only($iniRaw);
    $finDigits = digits_only($finRaw);
    $width = max($minWidth, strlen($iniDigits), strlen($finDigits));
    $iniDisplay = $iniDigits !== '' ? preg_replace('/\d+/', str_pad($iniDigits, $width, '0', STR_PAD_LEFT), $iniRaw, 1) : $iniRaw;
    $finDisplay = $finDigits !== '' ? preg_replace('/\d+/', str_pad($finDigits, $width, '0', STR_PAD_LEFT), $finRaw, 1) : $finRaw;
    return [$iniDisplay, $finDisplay, $width];
}
function pad_single_serial($serialRaw, $width = 7){
    $digits = digits_only($serialRaw);
    return $digits !== '' ? str_pad($digits, max(7,$width), '0', STR_PAD_LEFT) : (string)$serialRaw;
}

// TITULO CONSULTA SQL

$mysqli->set_charset("utf8");

// Sesión y rol (session iniciada arriba)
$session_rol     = $_SESSION['rol'] ?? '';
$is_usuario_final= ($session_rol === 'usuario_final');

// Filtros
$sku            = $_POST['sku']            ?? ($_GET['sku']            ?? '');
$lote           = $_POST['lote']           ?? ($_GET['lote']           ?? '');
$rut            = $_POST['rut']            ?? ($_GET['rut']            ?? '');
$nombre         = $_POST['nombre']         ?? ($_GET['nombre']         ?? '');
$fecha_despacho = $_POST['fecha_despacho'] ?? ($_GET['fecha_despacho'] ?? '');
$serial         = $_REQUEST['buscar_serial']  ?? '';

// WHERE dinámico
$where  = [];
$params = [];

if (!empty($sku)) { $where[] = "v.sku = ?";  $params[] = $sku; }
if (!empty($lote)) { $where[] = "v.lote = ?"; $params[] = $lote; }
if (!empty($rut)) { $where[] = "v.rut = ?";   $params[] = $rut; }
if (!empty($nombre)) {
    $where[] = "LOWER(c.nombre) LIKE LOWER(?)"; 
    $params[] = '%'.$nombre.'%';
}
if (!empty($_POST['numero_fact']) || !empty($_GET['numero_fact'])) {
    $factura = $_POST['numero_fact'] ?? $_GET['numero_fact'];
    $where[] = "v.numero_fact = ?";
    $params[] = $factura;
}
if (!empty($fecha_despacho)) { $where[] = "v.fecha_despacho = ?"; $params[] = $fecha_despacho; }
if (!empty($_POST['fecha_desde']) || !empty($_GET['fecha_desde'])) {
    $fecha_desde = $_POST['fecha_desde'] ?? $_GET['fecha_desde'];
    $where[] = "v.fecha_despacho >= ?";
    $params[] = $fecha_desde . " 00:00:00";
}
if (!empty($_POST['fecha_hasta']) || !empty($_GET['fecha_hasta'])) {
    $fecha_hasta = $_POST['fecha_hasta'] ?? $_GET['fecha_hasta'];
    $where[] = "v.fecha_despacho <= ?";
    $params[] = $fecha_hasta . " 23:59:59";
}
// serial solo usuario_final
// Usar la misma condición que en imprimir_directo para evitar inconsistencias
if ($is_usuario_final && !empty($serial)) {
    $where[]  = "(v.n_serie_ini <= ? AND v.n_serie_fin >= ?)";
    $params[] = $serial;
    $params[] = $serial;
}

// SELECT (incluye series para poder calcular padding aunque no se muestren en usuario_final)
// Usar LEFT JOIN en ambos casos para no filtrar filas sin cliente
if ($is_usuario_final) {
    $sql = "SELECT v.sku, v.fecha_despacho, v.producto, v.cantidad, v.lote, v.fecha_fabricacion, v.fecha_vencimiento,
                   v.n_serie_ini, v.n_serie_fin
            FROM venta v 
            LEFT JOIN cliente c ON v.rut = c.rut";
} else {
    $sql = "SELECT v.rut, c.nombre, v.numero_fact, v.fecha_despacho, v.sku,
                   v.producto, v.cantidad, v.lote, v.fecha_fabricacion, v.fecha_vencimiento,
                   v.n_serie_ini, v.n_serie_fin
            FROM venta v 
            LEFT JOIN cliente c ON v.rut = c.rut";
}

if (!empty($where)) { $sql .= " WHERE " . implode(" AND ", $where); }
$sql .= " ORDER BY v.id DESC";

error_log("SQL final generar_pdf: " . $sql);
error_log("Params generar_pdf: " . print_r($params, true));

$conexion = $mysqli->prepare($sql);
if ($conexion === false) {
    error_log("Error al preparar SQL: " . $mysqli->error);
    die('Error interno al preparar la consulta');
}

if (!empty($params)) {
    $types = str_repeat("s", count($params));
    $bindParams = [];
    $bindParams[] = $types;
    // bind_param requiere referencias
    foreach ($params as $k => $v) {
        $bindParams[] = &$params[$k];
    }
    call_user_func_array([$conexion, 'bind_param'], $bindParams);
}

if (!$conexion->execute()) {
    error_log("Error al ejecutar consulta: " . $conexion->error);
    die('Error interno al ejecutar la consulta');
}

$result = $conexion->get_result();
if ($result === false) {
    error_log("get_result devolvió false. Error stmt: " . $conexion->error);
    die('Error interno al obtener resultados');
}

// Para calcular un ancho global consistente de series
$bufferRows = [];
$globalWidth = 7;
while($r = $result->fetch_assoc()){
    $bufferRows[] = $r;
    [$idisp,$fdisp,$w] = pad_series_pair($r['n_serie_ini'] ?? '', $r['n_serie_fin'] ?? '', 7);
    $globalWidth = max($globalWidth, $w);
}
// Reposicionar puntero con datos en buffer
// (ya tenemos $bufferRows, avanzamos con ese arreglo)

// TITULO GENERACIÓN PDF

class PDF_NbLines extends FPDF {
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if($w==0) $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 and $s[$nb-1]=="\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while($i<$nb){
            $c=$s[$i];
            if($c=="\n"){ $i++; $sep=-1; $j=$i; $l=0; $nl++; continue; }
            if($c==' ') $sep=$i;
            $l += isset($cw[$c]) ? $cw[$c] : 0;
            if($l>$wmax){
                if($sep==-1){
                    if($i==$j) $i++;
                } else {
                    $i=$sep+1;
                }
                $sep=-1; $j=$i; $l=0; $nl++;
            } else $i++;
        }
        return $nl;
    }
}

$pdf = new PDF_NbLines('L','mm','A3');
$pdf->AddPage();

// Título principal
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,utf8_decode('Listado de Productos Despachados'),0,1,'C');
$pdf->Ln(5);

// Encabezados
$pdf->SetFont('Arial','B',10);
if ($is_usuario_final) {
    $headers = ['SKU','SERIE','FECHA DE DESPACHO','PRODUCTO', 'CANTIDAD', 'LOTE','FECHA DE FABRICACION','FECHA DE VENCIMIENTO'];
    $widths  = [40,50,60,90,40,50,50,40];
} else {
    $headers = ['SKU','RUT','Nombre Cliente','Numero de Factura','Fecha Despacho','Producto', 'Cantidad', 'Lote','Fecha Fab.','Fecha Ven.','Serie de Inicio','Serie de Termino'];
    $widths  = [25,30,50,35,35,50,40,21,25,25,30,30];
}

$totalWidth       = array_sum($widths);
$Pocision_inicial = ($pdf->GetPageWidth() - $totalWidth) / 2;
$pdf->SetX($Pocision_inicial);

// Header row
$headerHeight=10;
foreach($headers as $i=>$h){
    $pdf->Cell($widths[$i],$headerHeight,utf8_decode($h),1,0,'C');
}
$pdf->Ln();

$pdf->SetFont('Arial','',9);

// Render filas
foreach($bufferRows as $row){

    $pdf->SetX($Pocision_inicial);

    $fab = $row['fecha_fabricacion']   ?? '';
    $ven = $row['fecha_vencimiento']   ?? '';
    if ($ven=='' && $fab!=''){
        try{ $dt = new DateTime($fab); $dt->modify('+5 years'); $ven = $dt->format('Y-m-d'); }catch(Exception $e){}
    }

    // Padding consistente de series
    [$iniDisp, $finDisp, $localW] = pad_series_pair($row['n_serie_ini'] ?? '', $row['n_serie_fin'] ?? '', $globalWidth);

    if($is_usuario_final){
        $serialPadded = pad_single_serial($serial, $globalWidth);
        $data = [
            utf8_decode((string)$row['sku']),
            utf8_decode((string)$serialPadded),
            utf8_decode((string)$row['fecha_despacho']),
            utf8_decode((string)$row['producto']),
            utf8_decode((string)$row['cantidad']),
            utf8_decode((string)$row['lote']),
            utf8_decode((string)$fab),
            utf8_decode((string)$ven)
        ];
    } else {
        $data = [
            utf8_decode((string)$row['sku']),
            utf8_decode((string)$row['rut']),
            utf8_decode((string)$row['nombre']),
            utf8_decode((string)$row['numero_fact']),
            utf8_decode((string)$row['fecha_despacho']),
            utf8_decode((string)$row['producto']),
            utf8_decode((string)$row['cantidad']),
            utf8_decode((string)$row['lote']),
            utf8_decode((string)$fab),
            utf8_decode((string)$ven),
            utf8_decode((string)$iniDisp),
            utf8_decode((string)$finDisp)
        ];
    }

    // altura
    $alturaCelda   = 8;
    $maxLineas     = 1;
    foreach($data as $i=>$txt){
        $lineas = $pdf->NbLines($widths[$i],$txt);
        $maxLineas = max($maxLineas,$lineas);
    }
    $alturaFila = $alturaCelda * $maxLineas;

    // salto
    if($pdf->GetY()+$alturaFila > $pdf->GetPageHeight() - $pdf->GetMargins()['bottom']){
        $pdf->AddPage();
        $Pocision_inicial=($pdf->GetPageWidth()-$totalWidth)/2;
        $pdf->SetX($Pocision_inicial);
        $pdf->SetFont('Arial','B',10);
        foreach($headers as $i=>$h){
            $pdf->Cell($widths[$i],$headerHeight,utf8_decode($h),1,0,'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
    }

    $posY = $pdf->GetY();
    $pdf->SetX($Pocision_inicial);

    // celdas
    foreach($data as $i=>$txt){
        $x = $pdf->GetX();
        $y = $posY;
        $pdf->Rect($x,$y,$widths[$i],$alturaFila);
        $nb = $pdf->NbLines($widths[$i],$txt);
        $space = ($alturaFila - ($nb*$alturaCelda))/2;
        $pdf->SetXY($x,$y+$space);
        $pdf->MultiCell($widths[$i],$alturaCelda,$txt,0,'C');
        $pdf->SetXY($x+$widths[$i],$posY);
    }

    $pdf->SetY($posY + $alturaFila);
}

// SALIDA PDF
ob_end_clean();
$pdf->Output('I','despacho_productos.pdf');

// $mysqli->close();

/*   ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa generar_pdf .PHP --------------------------------------
     ------------------------------------------------------------------------------------------------------------ */

/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/
?>

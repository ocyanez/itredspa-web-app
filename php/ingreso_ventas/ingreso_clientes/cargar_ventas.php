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
     -------------------------------------- INICIO ITred Spa cargar_ventas .PHP ---------------------------------
     ------------------------------------------------------------------------------------------------------------ */


// Desactivamos errores visibles para evitar romper la salida
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Limpiamos cualquier buffer previo que pueda interferir con la respuesta
while (ob_get_level() > 0) { ob_end_clean(); }
// Desactivamos compresión si está activa (puede romper el archivo Excel)
if (ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off'); }

        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
        // Usamos UTF-8 para soportar acentos y caracteres especiales
        $mysqli->set_charset("utf8mb4");

/* ------------------------------- EXCEL -------------------------------------------- */
// Cargamos PhpSpreadsheet para poder leer archivos Excel
require __DIR__ . '/../../../programas/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

/* ------------------------------- UTILIDADES --------------------------------------- */
// Normaliza el RUT: limpia puntos, guiones raros y lo deja en formato XX.XXX.XXX-X
function normaliza_rut($rut) {
    $rut = strtoupper(trim((string)$rut));
    $rut = str_replace(['.', '–', '—'], ['', '-', '-'], $rut);
    $rut = preg_replace('/[^0-9K\-]/', '', $rut);
    if (strpos($rut, '-') === false && strlen($rut) >= 2) {
        $rut = substr($rut, 0, -1) . '-' . substr($rut, -1);
    }
    return $rut;
}
// Valida que el RUT tenga formato correcto y dígito verificador válido
function rut_valido($rutRaw) {
    $rut = normaliza_rut($rutRaw);
    if (!preg_match('/^(\d{1,8})-([\dK])$/', $rut, $m)) return false;
    $cuerpo = $m[1]; $dv = $m[2];
    $suma = 0; $mult = 2;
    for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
        $suma += intval($cuerpo[$i]) * $mult;
        $mult = ($mult == 7) ? 2 : $mult + 1;
    }
    $res = 11 - ($suma % 11);
    $dvCal = ($res == 11) ? '0' : (($res == 10) ? 'K' : (string)$res);
    return strtoupper($dvCal) === strtoupper($dv);
}
// Convierte fechas desde Excel a formato YYYY-MM-DD
function normalizar_fecha_excel($valor) {
    if ($valor === null || $valor === '') return null;
    if (is_numeric($valor)) {
        try { return XlsDate::excelToDateTimeObject($valor)->format('Y-m-d'); }
        catch (\Throwable $e) {}
    }
    $v = trim((string)$valor);
    // Aceptar YYYY-MM-DD pero corregir si la "mes" es >12 (posible swap D/M)
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $v, $m)) {
        $Y = intval($m[1]); $M = intval($m[2]); $D = intval($m[3]);
        // si mes inválido pero día válido <=12, intercambiar
        if ($M < 1 || $M > 12) {
            if ($D >=1 && $D <= 12 && $M >=1 && $M <= 31) {
                $tmp = $M; $M = $D; $D = $tmp;
            } else {
                return null;
            }
        }
        return sprintf('%04d-%02d-%02d', $Y, $M, $D);
    }

    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $v, $m)) {
        $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $M = str_pad($m[2], 2, '0', STR_PAD_LEFT);
        return $m[3] . '-' . $M . '-' . $d;
    }

    if (preg_match('/^(\d{1,2})[\/\-](\d{4})$/', $v, $m)) {
        $M = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        return $m[2] . '-' . $M . '-01';
    }
    $ts = strtotime($v);
    return $ts !== false ? date('Y-m-d', $ts) : null;
}
// Convierte fechas con hora desde Excel a formato YYYY-MM-DD HH:MM:SS
function normalizar_fecha_hora_excel($valor) {
    if ($valor === null || $valor === '') return null;
    if (is_numeric($valor)) {
        try { return XlsDate::excelToDateTimeObject($valor)->format('Y-m-d H:i:s'); }
        catch (\Throwable $e) {}
    }
    $v = trim((string)$valor);

    // Formato D/M/Y H:i o YYYY-M-D H:i - manejar ambos y corregir swap si aplica
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\s+(\d{1,2}):(\d{2})$/', $v, $m)) {
        $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $M = str_pad($m[2], 2, '0', STR_PAD_LEFT);
        $h = str_pad($m[4], 2, '0', STR_PAD_LEFT);
        $i = str_pad($m[5], 2, '0', STR_PAD_LEFT);
        return $m[3] . '-' . $M . '-' . $d . " $h:$i:00";
    }
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})\s+(\d{1,2}):(\d{2})$/', $v, $m)) {
        $Y = intval($m[1]); $M = intval($m[2]); $D = intval($m[3]);
        // corregir swap si mes inválido
        if ($M < 1 || $M > 12) {
            if ($D >=1 && $D <= 12) { $tmp = $M; $M = $D; $D = $tmp; }
            else return null;
        }
        $h = str_pad($m[4], 2, '0', STR_PAD_LEFT);
        $i = str_pad($m[5], 2, '0', STR_PAD_LEFT);
        return sprintf('%04d-%02d-%02d %s:%s:00', $Y, $M, $D, $h, $i);
    }

    

    $ts = strtotime($v);
    return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
}
// Calcula fecha de vencimiento sumando 5 años a la fecha de fabricación
function calcular_fecha_vencimiento($fechaFabricacion) {
    if (!$fechaFabricacion) return null;
    try { $dt = new DateTime($fechaFabricacion); $dt->modify('+5 years'); return $dt->format('Y-m-d'); }
    catch (\Throwable $e) { return null; }
}

/* ------------------------------- FUNCIÓN SALIDA TEXTO PLANO ----------------------- */
// Función de salida texto plano segura
function salida_texto_segura($estado, $param1 = '', $param2 = '', $param3 = '') {
    ob_clean(); // Limpiar cualquier output previo
    header('Content-Type: text/plain; charset=utf-8');
    // Formato: ESTADO|param1|param2|param3
    echo $estado . '|' . $param1 . '|' . $param2 . '|' . $param3;
    exit;
}

/* ------------------------------- ENTRADA ----------------------------------------- */
// Detectamos si el archivo subido es de ventas o clientes
$fileKey = isset($_FILES['archivo_excel_ventas']) ? 'archivo_excel_ventas'
         : (isset($_FILES['archivo_excel']) ? 'archivo_excel' : null);

// Si no llegó archivo o vino con error, respondemos con mensaje claro
if (!$fileKey || $_FILES[$fileKey]['error'] !== 0) {
    salida_texto_segura('ERROR', 'Error al subir el archivo.');
}

/* ------------------------------- LECTURA ----------------------------------------- */
// Intentamos cargar el archivo Excel
try {
    $spreadsheet = IOFactory::load($_FILES[$fileKey]['tmp_name']);
} catch (\Throwable $e) {
    // Si falla, respondemos con error explicativo
    salida_texto_segura('ERROR', 'No se pudo leer el archivo Excel: ' . $e->getMessage());
}
// Extraemos la hoja activa y convertimos su contenido a array
$sheet = $spreadsheet->getActiveSheet();
$rows  = $sheet->toArray();

// Guardamos los encabezados y las primeras filas para preview/log
$encabezados_detectados = $rows[0] ?? [];
$filas_totales_archivo  = max(0, count($rows) - 1);
$preview_filas = [];
for ($pi = 1; $pi < count($rows) && $pi <= 3; $pi++) { $preview_filas[] = $rows[$pi]; }

/* ------------------------------- PREP SQL ---------------------------------------- */
// Inicializamos contadores
$filasNuevasInsertadas = 0;
$filasExistentes       = 0;
$filasConErrores       = 0;
$mensajesError         = [];

// Consulta para verificar si la fila ya existe
$checkStmt = $mysqli->prepare(
    "SELECT 1 FROM venta
     WHERE REPLACE(REPLACE(rut,'.',''),'-','') = REPLACE(REPLACE(? ,'.',''),'-','')
       AND numero_fact = ?
       AND sku         = ?
       AND lote        = ?
       AND n_serie_ini = ?
       AND n_serie_fin = ?
       AND fecha_despacho = ?"
);
// Consulta para insertar nueva fila
$insertStmt = $mysqli->prepare(
    "INSERT INTO venta
    (rut, numero_fact, fecha_despacho, sku, producto, cantidad , lote, fecha_fabricacion, fecha_vencimiento, n_serie_ini, n_serie_fin)
    VALUES (?,?,?,?,?,?,?,?,?,?,?)"
);
// Si alguna consulta falla, salimos con error
if (!$checkStmt || !$insertStmt) {
    salida_texto_segura('ERROR', 'Error preparando consultas: ' . $mysqli->error);
}

/* ------------------------------- PROCESO FILAS ----------------------------------- */
for ($i = 1; $i < count($rows); $i++) {
    $datos = $rows[$i];

    // A=rut, B=numero_fact, C=fecha_despacho, D=sku, E=producto, F=lote, G=fecha_fabricacion, H=n_serie_ini, I=n_serie_fin
    $rut               = trim((string)($datos[0] ?? ''));
    $numero_fact       = trim((string)($datos[1] ?? ''));
    $fecha_despacho    = (string)($datos[2] ?? '');
    $sku               = trim((string)($datos[3] ?? ''));
    $producto          = trim((string)($datos[4] ?? ''));
    $cantidad_raw      = trim((string)($datos[5] ?? '')); // nuevo: cantidad de los productos
    $lote              = trim((string)($datos[6] ?? ''));
    $fecha_fabricacion = (string)($datos[7] ?? '');
    $fecha_vencimiento_input = (string)($datos[8] ?? ''); // nuevo: puede venir en Excel
    $n_serie_ini       = trim((string)($datos[9] ?? ''));
    $n_serie_fin       = trim((string)($datos[10] ?? ''));

    // Si la fila está completamente vacía, la saltamos
    if ($rut==="" && $numero_fact==="" && $fecha_despacho==="" && $sku==="" &&
        $producto==="" && $cantidad_raw==="" &&  $lote==="" && $fecha_fabricacion==="" && $fecha_vencimiento_input==="" &&
        $n_serie_ini==="" && $n_serie_fin==="") {
        continue;
    }

    // Validamos campos obligatorios
    if ($rut==="" || $numero_fact==="" || $fecha_despacho==="" || $sku==="" ||
        $producto==="" || $cantidad_raw==="" || $lote==="" || $fecha_fabricacion==="") {
        $mensajesError[] = "Fila ".($i+1).": faltan campos obligatorios.";
        $filasConErrores++; continue;
    }

    // Validamos RUT y existencia del cliente
    if (!rut_valido($rut)) {
        $mensajesError[] = "Fila ".($i+1).": RUT inválido ($rut).";
        $filasConErrores++; continue;
    }
    $rutNorm = normaliza_rut($rut);

    // Cliente existe
    $resCli = $mysqli->prepare("SELECT 1 FROM cliente WHERE REPLACE(REPLACE(rut,'.',''),'-','') = REPLACE(REPLACE(? ,'.',''),'-','') LIMIT 1");
    $resCli->bind_param("s", $rutNorm);
    $resCli->execute();
    $existeCli = $resCli->get_result()->fetch_row();
    $resCli->close();
    if (!$existeCli) {
        $mensajesError[] = "Fila ".($i+1).": el RUT '$rut' no existe en la tabla cliente.";
        $filasConErrores++; continue;
    }

   // Validamos cantidad
    $cantidad = null;
    if ($cantidad_raw !== '') {
        if (!preg_match('/^\d+$/', $cantidad_raw)) {
            $mensajesError[] = "Fila ".($i+1).": CANTIDAD inválida (debe ser número entero positivo): '$cantidad_raw'.";
            $filasConErrores++; continue;
        }
        $cantidad = intval($cantidad_raw);
    }


    // Normalizamos fechas
    $fecha_despacho_sql    = normalizar_fecha_hora_excel($fecha_despacho);   // acepta fecha+hora
    $ffTmp                 = normalizar_fecha_hora_excel($fecha_fabricacion); // acepta fecha+hora
    $fecha_fabricacion_sql = $ffTmp ? substr($ffTmp, 0, 10) : normalizar_fecha_excel($fecha_fabricacion);

    if (!$fecha_despacho_sql || !$fecha_fabricacion_sql) {
        $mensajesError[] = "Fila ".($i+1).": fecha(s) inválidas (despacho='$fecha_despacho', fabricacion='$fecha_fabricacion').";
        $filasConErrores++; continue;
    }

    // Fecha de vencimiento: si viene en la planilla, validar y usar; si no, calcular +5 años
     $fecha_vencimiento_sql = null;
    if (trim($fecha_vencimiento_input) !== '') {
        $tmp = normalizar_fecha_hora_excel($fecha_vencimiento_input);
        if ($tmp) {
            $fecha_vencimiento_sql = substr($tmp, 0, 10);
        } else {
            $fecha_vencimiento_sql = normalizar_fecha_excel($fecha_vencimiento_input);
        }
        if (!$fecha_vencimiento_sql) {
            $mensajesError[] = "Fila ".($i+1).": Fecha de vencimiento inválida: '".str_replace("\n"," ",$fecha_vencimiento_input)."'.";
            $filasConErrores++; continue;
        }
    } else {
        // calcular +5 años desde fecha_fabricacion
        $fecha_vencimiento_sql = calcular_fecha_vencimiento($fecha_fabricacion_sql);
        if (!$fecha_vencimiento_sql) {
            $mensajesError[] = "Fila ".($i+1).": No se pudo calcular fecha de vencimiento desde '$fecha_fabricacion_sql'.";
            $filasConErrores++; continue;
        }
    }

    // Validaciones estrictas antes de insertar: asegurar formato YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fabricacion_sql)) {
        $mensajesError[] = "Fila ".($i+1).": Fecha de fabricación inválida después de normalizar: '$fecha_fabricacion_sql'.";
        $filasConErrores++; continue;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_vencimiento_sql)) {
        $mensajesError[] = "Fila ".($i+1).": Fecha de vencimiento inválida después de normalizar: '$fecha_vencimiento_sql'.";
        $filasConErrores++; continue;
    }

    // Guardamos en el log interno los datos clave de la fila actual (no se muestra al usuario)
    error_log(sprintf(
        "IMPORT ROW %d -> rut=%s numero_fact=%s sku=%s lote=%s fecha_despacho=%s fecha_fab=%s fecha_ven=%s serie_ini=%s serie_fin=%s",
        $i,
        $rutNorm,
        $numero_fact,
        $sku,
        $lote,
        $fecha_despacho_sql,
        $fecha_fabricacion_sql,
        $fecha_vencimiento_sql,
        $n_serie_ini,
        $n_serie_fin
    ));
    
    // Validamos que el SKU tenga solo letras y números, y no supere los 20 caracteres
    if (!preg_match('/^[a-zA-Z0-9]{1,20}$/', $sku)) {
        $mensajesError[] = "Fila ".($i+1).": SKU inválido (solo letras y números, máx. 20 caracteres): '$sku'.";
        $filasConErrores++; continue;
    }
    // LOTE deben ser numéricos (solo dígitos). Si no, no se inserta la fila.
    if (!preg_match('/^\d+$/', $lote)) {
        $mensajesError[] = "Fila ".($i+1).": LOTE inválido (debe contener solo dígitos): '$lote'.";
        $filasConErrores++; continue;
    }


    // Vencimiento +5 años
    

    //Series: si vienen vacías o mal formateadas, se reemplazan por "0"
    if ($n_serie_ini === '' || !preg_match('/^\d+$/', $n_serie_ini)) $n_serie_ini = '0';
    if ($n_serie_fin === '' || !preg_match('/^\d+$/', $n_serie_fin)) $n_serie_fin = '0';

    // Verificamos si ya existe una venta con los mismos datos clave
    $checkStmt->bind_param("sssssss", $rutNorm, $numero_fact, $sku, $lote, $n_serie_ini, $n_serie_fin, $fecha_despacho_sql);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->fetch_row();

    // Si ya existe, no insertamos y contamos como fila existente
    if ($exists) { 
        $filasExistentes++; 
        continue; }

    // Insertamos la fila en la tabla venta
    $insertStmt->bind_param(
        "sssssssssss",
        $rutNorm, $numero_fact, $fecha_despacho_sql, $sku, $producto, $cantidad , $lote,
        $fecha_fabricacion_sql, $fecha_vencimiento_sql, $n_serie_ini, $n_serie_fin
    );

    // Si se inserta correctamente, sumamos al contador
    if ($insertStmt->execute()) { $filasNuevasInsertadas++; }
    else {
        // Si falla, guardamos el error y lo registramos en el log interno
        $mensajesError[] = "Fila ".($i+1).": error al insertar - ".$mysqli->error;
        // DEBUG adicional: escribir error de statement
        error_log("IMPORT ERROR ROW {$i} -> stmt error: " . $insertStmt->error);
        $filasConErrores++;
    }
}

/* ------------------------------- LOG (sin imprimir) ------------------------------- */
// Iniciamos sesión si no está activa (necesaria para registrar quién hizo la carga)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Incluimos el archivo de logging, pero evitamos que imprima algo
ob_start();
@include_once $_SERVER['DOCUMENT_ROOT'].'/php/inicio_sesion/seguridad/log_registros.php';
ob_end_clean();
// Si existe la función de log, registramos el evento con todos los detalles útiles
if (function_exists('app_log')) {
    app_log('import','venta','Importación desde Excel (ventas)', [
        'archivo'                 => $_FILES[$fileKey]['name'] ?? null,
        'hoja'                    => $sheet->getTitle(),
        'columnas_detectadas'     => $encabezados_detectados,
        'filas_totales_archivo'   => $filas_totales_archivo,
        'nuevas'                  => $filasNuevasInsertadas,
        'existentes'              => $filasExistentes,
        'errores'                 => $filasConErrores,
        'errores_detalle'         => array_slice($mensajesError, 0, 10),
        'preview_primeras_filas'  => $preview_filas,
        'plantilla_esperada'      => [
            'A=RUT','B=NUMERO_FACTURA','C=FECHA_DESPACHO','D=SKU','E=PRODUCTO',
            'F=LOTE','G=FECHA_FABRICACION','H=SERIE_INICIO','I=SERIE_FIN'
        ]
    ]);
}

/* ------------------------------- RESPUESTA ---------------------------------------- */
// Respuesta en texto plano: OK|nuevas|existentes|errores
salida_texto_segura('OK', $filasNuevasInsertadas, $filasExistentes, $filasConErrores);

/*   ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa cargar_ventas .PHP ------------------------------------
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
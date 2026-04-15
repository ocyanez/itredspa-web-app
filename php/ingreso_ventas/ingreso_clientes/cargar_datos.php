<?php
/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.cl@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/

/*   ------------------------------------------------------------------------------------------------------------
     ------------------------------------- INICIO ITred Spa cargar_datos .PHP -----------------------------------
     ------------------------------------------------------------------------------------------------------------ */

/* Higiene de salida texto plano (evita que cualquier echo rompa la respuesta) */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(0); // Suprimir todos los warnings/notices
while (ob_get_level() > 0) { ob_end_clean(); }
ob_start(); // Iniciar buffer de salida limpio
if (ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off'); }

/*   ------------------------
     -- INICIO CONEXION BD --
     ------------------------ */
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
        $mysqli->set_charset("utf8mb4");
/*   ---------------------
     -- FIN CONEXION BD --
     --------------------- */

/* TITULO IMPORTACIÓN DE LIBRERÍA EXCEL */
require __DIR__ . '/../../../programas/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

/* HELPERS RUT */

// solo números
function rut_solo_digitos($s){ return preg_replace('/\D/','',(string)$s); }

// calcular dígito verificador
function rut_calcular_dv($cuerpo){
    $cuerpo = strrev((string)$cuerpo);
    $suma=0; $mult=2;
    for($i=0;$i<strlen($cuerpo);$i++){
        $suma += intval($cuerpo[$i])*$mult;
        $mult = ($mult==7)?2:$mult+1;
    }
    $res = 11 - ($suma % 11);
    return $res==11?'0':($res==10?'K':(string)$res);
}

// formatear RUT con validación robusta
function rut_formatear_cuerpo_dv($input){
    $input = strtoupper(trim((string)$input));
    $clean = preg_replace('/[^0-9K]/','',$input);        // deja dígitos y K por si ya venía dv
    // Si trae dv (8-9 números + 1 char)
    if (preg_match('/^(\d{7,8})(\d|K)$/',$clean,$m)) {
        return $m[1].'-'.$m[2];
    }
    // Si viene solo cuerpo numérico, calculamos dv
    $cuerpo = rut_solo_digitos($input);
    if ($cuerpo==='') return null;
    $dv = rut_calcular_dv($cuerpo);
    return $cuerpo.'-'.$dv;
}

// validar RUT completo con todas las verificaciones
function rut_valido_completo($rutIn){
    // Normalizar/validar RUT (acepta con o sin DV; guarda cuerpo-DV)
    $rutForm = rut_formatear_cuerpo_dv($rutIn);
    if ($rutForm === null) return false;
    
    // Si el usuario ingresó explícitamente un DV, verificar que coincida con el calculado
    $inputClean = strtoupper(trim((string)$rutIn));
    $inputDigits = preg_replace('/[^0-9Kk]/','', $inputClean);
    if (preg_match('/^(\d{7,8})([0-9Kk])$/', $inputDigits, $m)) {
        $provCuerpo = $m[1];
        $provDv = strtoupper($m[2]);
        $calcDv = rut_calcular_dv($provCuerpo);
        if ($provDv !== $calcDv) return false;
    }

    // Rechazar cuerpos obvios/falsos: repetidos (ej. 11111111) o secuencias típicas
    $cuerpo = rut_solo_digitos($rutForm);
    // Repetición del mismo dígito (7+ o 8 dígitos idénticos)
    if (preg_match('/^(\d)\1{6,7}$/', $cuerpo)) return false;
    
    // Secuencias ascendentes/descendentes simples (ej. 12345678, 87654321)
    $blacklist_sequences = ['1234567','12345678','23456789','87654321','76543210'];
    foreach ($blacklist_sequences as $seq) {
        if (strpos($cuerpo, $seq) !== false) return false;
    }
    
    // Validar longitud
    $rutCmp = rut_normalizado_sql($rutForm);
    if (strlen($rutCmp) < 8 || strlen($rutCmp) > 9) return false;
    
    return true;
}

// normalizar para comparar en SQL (quita espacios, puntos, guiones y NBSP)
function rut_normalizado_sql($s){
    $s = trim((string)$s);
    $s = str_replace(["\xC2\xA0"], '', $s); // NBSP
    $s = str_replace([' ','-','.'],'',$s);
    return strtoupper($s);
}

/* TITULO CARGA DE DATOS */

// Función de salida texto plano segura
function salida_texto_segura($estado, $param1 = '', $param2 = '', $param3 = '') {
    ob_clean(); // Limpiar cualquier output previo
    header('Content-Type: text/plain; charset=utf-8');
    // Formato: ESTADO|param1|param2|param3
    echo $estado . '|' . $param1 . '|' . $param2 . '|' . $param3;
    exit;
}

if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] == 0) {

    $archivo_temp = $_FILES['archivo_excel']['tmp_name'];

    // Carga Excel
    try {
        $spreadsheet = IOFactory::load($archivo_temp);
    } catch (Throwable $e) {
        salida_texto_segura('ERROR', 'No se pudo leer el archivo Excel: ' . $e->getMessage());
    }

    // Conservar cadenas tal cual (A,B,C…)
    $sheet = $spreadsheet->getActiveSheet();
    $rows  = $sheet->toArray(null, true, true, true);

    // META para el log
    $encabezados_detectados = isset($rows[1]) ? $rows[1] : [];
    $filas_totales_archivo  = max(0, count($rows) - 1);
    $preview_filas = [];
    for ($pi = 2; $pi <= count($rows) && $pi <= 4; $pi++) {
        if (isset($rows[$pi])) $preview_filas[] = $rows[$pi];
    }

    // contadores
    $filasNuevasInsertadas = 0;
    $filasExistentes = 0;
    $filasConErrores = 0;
    $mensajesError = [];

    // Sugerencia: tener UNIQUE en cliente.rut
    $stmtInsert = $mysqli->prepare("INSERT INTO cliente (nombre, rut) VALUES (?, ?)");
    if (!$stmtInsert) {
        salida_texto_segura('ERROR', 'Error preparando consulta: ' . $mysqli->error);
    }

    // Statement para detectar duplicado con limpieza robusta
    $stmtDup = $mysqli->prepare("
        SELECT 1
        FROM cliente
        WHERE UPPER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(rut),' ',''),'.',''),'-',''), CHAR(160), '')) = ?
        LIMIT 1
    ");
    if (!$stmtDup) {
        salida_texto_segura('ERROR', 'Error preparando verificación: ' . $mysqli->error);
    }

    // recorrer filas (desde la 2, la 1 es encabezado)
    for ($i = 2; $i <= count($rows); $i++) {
        if (!isset($rows[$i])) continue;
        $fila = $rows[$i];

        // A=nombre, B=rut
        $nombreRaw = isset($fila['A']) ? trim((string)$fila['A']) : '';
        $rutRaw    = isset($fila['B']) ? trim((string)$fila['B']) : '';

        // Fila vacía
        if ($nombreRaw === '' && $rutRaw === '') continue;

        // Validación básica
        if ($nombreRaw === "" || $rutRaw === "") {
            $mensajesError[] = "Fila {$i}: nombre o RUT vacío, fila ignorada.";
            $filasConErrores++;
            continue;
        }

        // Normalizar/Validar RUT con todas las verificaciones robustas
        if (!rut_valido_completo($rutRaw)) {
            $mensajesError[] = "Fila {$i}: RUT inválido ({$rutRaw}).";
            $filasConErrores++;
            continue;
        }
        $rut     = rut_formatear_cuerpo_dv($rutRaw);      // guarda siempre cuerpo-DV
        $rutNorm = rut_normalizado_sql($rut);
        $nombre  = $nombreRaw;

        // ¿Existe?
        $stmtDup->bind_param('s', $rutNorm);
        $stmtDup->execute();
        $existe = $stmtDup->get_result()->fetch_row();

        if ($existe) {
            $filasExistentes++;
            // Registrar por qué no se cargó esta fila (duplicado)
            $mensajesError[] = "Fila {$i}: RUT ya existe ({$rut}).";
            continue;
        }

        // Insertar
        $stmtInsert->bind_param('ss', $nombre, $rut);
        if ($stmtInsert->execute()) {
            $filasNuevasInsertadas++;
        } else {
            $mensajesError[] = "Fila {$i}: error en la inserción - " . $stmtInsert->error;
            $filasConErrores++;
        }
    }

    $stmtInsert->close();
    $stmtDup->close();

    // LOG DETALLADO (sin imprimir nada)
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    ob_start();
    @include_once $_SERVER['DOCUMENT_ROOT'].'/php/inicio_sesion/seguridad/log_registros.php';
    ob_end_clean();
    if (function_exists('app_log')) {
        app_log('import','cliente','Importación desde Excel (clientes)', [
            'archivo'                 => $_FILES['archivo_excel']['name'] ?? null,
            'hoja'                    => $sheet->getTitle(),
            'columnas_detectadas'     => $encabezados_detectados,
            'filas_totales_archivo'   => $filas_totales_archivo,
            'nuevas'                  => $filasNuevasInsertadas,
            'existentes'              => $filasExistentes,
            'errores'                 => $filasConErrores,
            'errores_detalle'         => array_slice($mensajesError, 0, 10),
            'preview_primeras_filas'  => $preview_filas,
            'plantilla_esperada'      => ['A=NOMBRE', 'B=RUT (con o sin DV)']
        ]);
    }

    // Respuesta en texto plano: OK|nuevas|existentes|errores
    salida_texto_segura('OK', $filasNuevasInsertadas, $filasExistentes, $filasConErrores);

} else {
    salida_texto_segura('ERROR', 'Error al subir el archivo.');
}

/*   -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- */

// $mysqli->close();

/*   ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- */

/*   ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa cargar_datos .PHP -------------------------------------
     ------------------------------------------------------------------------------------------------------------ */


/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.cl@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/
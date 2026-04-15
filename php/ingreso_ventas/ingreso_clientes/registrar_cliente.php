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
   ------------------------------------- INICIO ITred Spa registrar_cliente .PHP ------------------------------
   ------------------------------------------------------------------------------------------------------------ */

ini_set('display_errors','0');
error_reporting(E_ALL);
while (ob_get_level() > 0) { ob_end_clean(); }
if (ini_get('zlib.output_compression')) { ini_set('zlib.output_compression','Off'); }
header('Content-Type: text/plain; charset=utf-8');

/* ------------------------
   -- INICIO CONEXION BD --
   ------------------------ */

        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
        $mysqli->set_charset("utf8");

/* include auditoría global (sin permitir salida) */
ob_start();
@include_once __DIR__ . '/../../inicio_sesion/seguridad/log_registros.php';
ob_end_clean();

/* ---------------------
   -- FIN CONEXION BD --
   --------------------- */

/* ------------------ UTILIDADES RUT (mismo archivo) ------------------ */
function rut_solo_digitos($s){ return preg_replace('/\D/','',(string)$s); } // solo números
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
// normalizar para comparar en SQL (quita espacios, puntos, guiones y NBSP)
function rut_normalizado_sql($s){
    $s = trim((string)$s);
    $s = str_replace(["\xC2\xA0"], '', $s); // NBSP
    $s = str_replace([' ','-','.'],'',$s);
    return strtoupper($s);
}

// TITULO PROCESO DE REGISTRO CLIENTE 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Captura datos
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $rutIn  = isset($_POST['rut'])    ? trim($_POST['rut'])    : '';

    // Errores conexión
    if ($mysqli->connect_error) {
        echo 'ERROR|Error de conexión: '.$mysqli->connect_error;
        exit;
    }

    // Validaciones básicas
    if ($nombre === '' || $rutIn === '') {
        echo 'ERROR|Todos los campos son obligatorios';
        exit;
    }
    if (mb_strlen($nombre) < 3) {
        echo 'ERROR|El nombre debe tener al menos 3 caracteres';
        exit;
    }

    // Normalizar/validar RUT (acepta con o sin DV; guarda cuerpo-DV)
    $rutForm = rut_formatear_cuerpo_dv($rutIn);
    if ($rutForm === null) {
        echo 'ERROR|RUT inválido';
        exit;
    }
    // Si el usuario ingresó explícitamente un DV, verificar que coincida con el calculado
    // Detectamos DV en la entrada original (formato con guion o último carácter no numérico)
    $inputClean = strtoupper(trim((string)$rutIn));
    $inputDigits = preg_replace('/[^0-9Kk]/','', $inputClean);
    if (preg_match('/^(\d{7,8})([0-9Kk])$/', $inputDigits, $m)) {
        $provCuerpo = $m[1];
        $provDv = strtoupper($m[2]);
        $calcDv = rut_calcular_dv($provCuerpo);
        if ($provDv !== $calcDv) {
            echo 'ERROR|Dígito verificador no coincide (RUT inválido)';
            exit;
        }
    }

    // Rechazar cuerpos obvios/falsos: repetidos (ej. 11111111) o secuencias típicas
    $cuerpo = rut_solo_digitos($rutForm);
    // Repetición del mismo dígito (7+ o 8 dígitos idénticos)
    if (preg_match('/^(\d)\1{6,7}$/', $cuerpo)) {
        echo 'ERROR|RUT aparentemente inválido';
        exit;
    }
    // Secuencias ascendentes/descendentes simples (ej. 12345678, 87654321)
    $blacklist_sequences = ['1234567','12345678','23456789','87654321','76543210'];
    foreach ($blacklist_sequences as $seq) {
        if (strpos($cuerpo, $seq) !== false) {
            echo 'ERROR|RUT aparentemente inválido';
            exit;
        }
    }
    $rutCmp = rut_normalizado_sql($rutForm); // para comparar en SQL
    if (strlen($rutCmp) < 8 || strlen($rutCmp) > 9) {
        echo 'ERROR|RUT fuera de rango';
        exit;
    }

    // Verifica duplicado (limpieza robusta en SQL)
    $sqlDup = $mysqli->prepare("
        SELECT 1
        FROM cliente
        WHERE
            UPPER(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(rut),' ',''),'.',''),'-',''), CHAR(160), ''))
            = ?
        LIMIT 1
    ");
    if (!$sqlDup) {
        echo 'ERROR|Error interno al preparar verificación';
        exit;
    }
    $sqlDup->bind_param('s', $rutCmp);
    $sqlDup->execute();
    $dup = $sqlDup->get_result()->fetch_row();
    $sqlDup->close();

    if ($dup) {
        echo 'ERROR|El RUT ya está registrado';
        exit;
    }

    // Inserción: guardar SIEMPRE "cuerpo-DV"
    $stmt = $mysqli->prepare("INSERT INTO cliente (nombre, rut) VALUES (?, ?)");
    if (!$stmt) {
        echo 'ERROR|Error interno al preparar inserción';
        exit;
    }
    $stmt->bind_param("ss", $nombre, $rutForm);

    if ($stmt->execute()) {
        echo 'OK|Cliente registrado exitosamente';

        // LOG (sin imprimir nada)
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        ob_start();
        @include_once $_SERVER['DOCUMENT_ROOT'].'/php/inicio_sesion/seguridad/log_registros.php';
        ob_end_clean();
        if (function_exists('app_log')) {
            app_log('create','cliente','Alta manual', [
                'rut'    => $rutForm,
                'nombre' => $nombre
            ]);
        }

    } else {
        $msg = (stripos($stmt->error,'Duplicate')!==false)
            ? 'El RUT ya está registrado'
            : ('Error al registrar cliente: '.$stmt->error);
        echo 'ERROR|'.$msg;
    }
    $stmt->close();

} else {
    echo 'ERROR|Método no permitido';
}

/* -------------------------------
   -- INICIO CIERRE CONEXION BD --
   ------------------------------- */

// $mysqli->close();

/* ----------------------------
   -- FIN CIERRE CONEXION BD --
   ---------------------------- */

/* ------------------------------------------------------------------------------------------------------------
   -------------------------------------- FIN ITred Spa registrar_cliente .PHP ---------------------------------
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

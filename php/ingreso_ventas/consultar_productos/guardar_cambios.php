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

// Formato de respuesta: "success|mensaje" o "error|mensaje"
header('Content-Type: text/plain; charset=utf-8');

/*   ------------------------------------------------------------------------------------------------------------
     ------------------------------------- INICIO ITred Spa guardar_cambios .PHP --------------------------------
     ------------------------------------------------------------------------------------------------------------ */

/*   ------------------------
     -- INICIO CONEXION BD --
     ------------------------ */

$mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
require_once __DIR__ . '/../../inicio_sesion/seguridad/log_registros.php';

if ($mysqli->connect_errno) {
    echo 'error|Error conexión BD';
    exit;
}

/*   ---------------------
     -- FIN CONEXION BD --
     --------------------- */

// TITULO OBTENCION DE DATOS DE LA TABLA

$mysqli->set_charset("utf8");

// Verifica si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'error|Método no permitido';
    exit;
}

// Obtiene los datos enviados desde el cliente
$id = $_POST['id'] ?? null;
$sku = $_POST['sku'] ?? null;
$rut = $_POST['rut'] ?? null;
$nombre = $_POST['nombre'] ?? null;
$numero_fact = $_POST['numero_fact'] ?? null;
$fecha_despacho = $_POST['fecha_despacho'] ?? null;
$producto = $_POST['producto'] ?? null;
$cantidad = $_POST['cantidad'] ?? null;
$lote = $_POST['lote'] ?? null;
$fecha_fabricacion = $_POST['fecha_fabricacion'] ?? null;
$fecha_vencimiento = $_POST['fecha_vencimiento'] ?? null;
$n_serie_ini = $_POST['n_serie_ini'] ?? null;
$n_serie_fin = $_POST['n_serie_fin'] ?? null;

// Convierte los datos a los tipos correctos
$id = (int) $id;
$sku = $sku;
$lote = $lote;
$numero_fact = $numero_fact;
$n_serie_ini = (string) $n_serie_ini;
$n_serie_fin = (string) $n_serie_fin;

// Verifica si se envió la fecha de despacho
if (isset($_POST['fecha_despacho'])) {
    $fecha_despacho = $_POST['fecha_despacho'];
    // Verifica que el formato sea válido
    if (DateTime::createFromFormat('Y-m-d\TH:i', $fecha_despacho)) {
        // Convierte la fecha al formato deseado
        $fecha_despacho = str_replace('T', ' ', $fecha_despacho);
    } else {
        $fecha_despacho = null;
    }
}

// TITULO CONSULTA SQL

// Prepara la consulta SQL para actualizar los datos
$queryVenta = "UPDATE venta SET 
    sku = ?, 
    numero_fact = ?, 
    fecha_despacho = ?, 
    producto = ?,
    cantidad = ?, 
    lote = ?, 
    fecha_fabricacion = ?, 
    fecha_vencimiento = ?, 
    n_serie_ini = ?, 
    n_serie_fin = ? 
WHERE id = ?";

$stmtVenta = $mysqli->prepare($queryVenta);

if ($stmtVenta === false) {
    echo 'error|Error al preparar la consulta: ' . $mysqli->error;
    exit;
}

// Enlaza los parámetros a la consulta
$stmtVenta->bind_param(
    "ssssssssssi",
    $sku,
    $numero_fact,
    $fecha_despacho,
    $producto,
    $cantidad,
    $lote,
    $fecha_fabricacion,
    $fecha_vencimiento,
    $n_serie_ini,
    $n_serie_fin,
    $id
);

// Ejecuta la consulta
if (!$stmtVenta->execute()) {
    echo 'error|Error al actualizar la tabla venta: ' . $stmtVenta->error;
    exit;
}

$stmtVenta->close();

// Registrar actualización de venta en log
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (function_exists('app_log')) {
    app_log('update', 'venta', "Edición de venta id={$id}", ['sku' => $sku, 'rut' => $rut, 'actor' => $_SESSION['username'] ?? '']);
}

// TITULO ACTUALIZACION TABLA CLIENTE

if (!empty($rut) && !empty($nombre)) {
    // Usar consulta preparada para evitar SQL injection
    $queryCliente = "UPDATE cliente SET nombre = ? WHERE rut = ?";
    $stmtCliente = $mysqli->prepare($queryCliente);

    if ($stmtCliente === false) {
        echo 'error|Error al preparar la consulta de cliente: ' . $mysqli->error;
        exit;
    }

    $stmtCliente->bind_param("ss", $nombre, $rut);

    if (!$stmtCliente->execute()) {
        echo 'error|Error al actualizar la tabla cliente: ' . $stmtCliente->error;
        exit;
    }

    $stmtCliente->close();
}

// TITULO RESPUESTA EXITOSA

echo 'success|Datos actualizados correctamente.';

/*   -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- */

//    "<?php"
        // Cierra la conexión a la base de datos
        // $mysqli->close();
//    ?">"

/*   ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- */

/*   ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa guardar_cambios .PHP -----------------------------------
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
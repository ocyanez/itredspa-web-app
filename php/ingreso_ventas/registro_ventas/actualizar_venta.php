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
   ------------------------------------- INICIO ITred Spa actualizar_venta .PHP -------------------------------
   ------------------------------------------------------------------------------------------------------------ */

/* ------------------------
   -- INICIO CONEXION BD --
   ------------------------ */

// establece la conexión a la base de datos
$mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");

// include auditoría global
require_once __DIR__ . '/../../inicio_sesion/seguridad/log_registros.php';

/* ---------------------
   -- FIN CONEXION BD --
   --------------------- */

/* TITULO CONFIGURACION HEADER */

// CAMBIO: Definimos respuesta como Texto Plano (No JSON)
header('Content-Type: text/plain; charset=utf-8');
$mysqli->set_charset("utf8");

/* TITULO OBTENCION DE DATOS (YA NO JSON) */

// Verifica si se ha enviado una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CAMBIO: Obtiene los datos enviados desde el formulario usando $_POST
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $sku = $_POST['sku'] ?? null;
    $rut = $_POST['rut'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $numero_fact = $_POST['numeroFact'] ?? null;
    $fecha_despacho = $_POST['fechaActual'] ?? null;
    $producto = $_POST['producto'] ?? null;
    $lote = $_POST['lote'] ?? null;
    
    // Obtenemos la fecha de fabricacion y calculamos 5 años despues
    $fecha_fabricacion = $_POST['fechaFabricacion'] ?? null;
    $fecha_de_vencimiento = null;

    if ($fecha_fabricacion) {
        try {
            $fecha_fabricacion_formateada = new DateTime($fecha_fabricacion);
            $fecha_fabricacion_formateada->add(new DateInterval('P5Y')); // Añade 5 años
            $fecha_de_vencimiento = $fecha_fabricacion_formateada->format('Y-m-d');
        } catch (Exception $e) {
            // Manejo de error de fecha si es necesario
        }
    }

    // Series
    $n_serie_ini = isset($_POST['serieInicio']) ? (string)$_POST['serieInicio'] : null;
    $n_serie_fin = isset($_POST['serieFin']) ? (string)$_POST['serieFin'] : null;

    // Verifica si el ID de la venta está presente
    if (!$id) {
        // CAMBIO: Respuesta texto plano
        echo "Error: Falta el ID de la venta";
        exit;
    }

/* TITULO CONSULTA SQL VENTA */

    // Prepara la consulta SQL para actualizar los datos de la venta
    $queryVenta = "UPDATE venta SET 
        sku = ?,
        numero_fact = ?, 
        fecha_despacho = ?, 
        producto = ?, 
        lote = ?, 
        fecha_fabricacion = ?, 
        fecha_vencimiento = ?,
        n_serie_ini = ?, 
        n_serie_fin = ? 
    WHERE id = ?";

    $stmtVenta = $mysqli->prepare($queryVenta);

    if ($stmtVenta === false) {
        echo "Error: Error al preparar la consulta: " . $mysqli->error;
        exit;
    }

    // Vincula los parámetros a la consulta SQL
    $stmtVenta->bind_param(
        "sssssssssi", 
        $sku, 
        $numero_fact, 
        $fecha_despacho, 
        $producto, 
        $lote, 
        $fecha_fabricacion,
        $fecha_de_vencimiento,
        $n_serie_ini, 
        $n_serie_fin, 
        $id
    );

    // Ejecuta la consulta SQL
    if (!$stmtVenta->execute()) {
        echo "Error: Error al actualizar la tabla venta: " . $stmtVenta->error;
        exit;
    }

    // Cierra la declaración después de ejecutar la consulta
    $stmtVenta->close();
    
/* TITULO CONSULTA SQL CLIENTE */

    if (!empty($rut) && !empty($nombre)) {
        $queryCliente = "UPDATE cliente SET nombre = ? WHERE rut = ?";
        $stmtCliente = $mysqli->prepare($queryCliente);
        if ($stmtCliente === false) {
            echo "Error: Error al preparar la consulta de cliente: " . $mysqli->error;
            exit;
        }
        $stmtCliente->bind_param("ss", $nombre, $rut);
        if (!$stmtCliente->execute()) {
            echo "Error: Error al actualizar la tabla cliente: " . $stmtCliente->error;
            exit;
        }
        $stmtCliente->close();
    }

/* TITULO LOG DE AUDITORIA */

    if (function_exists('app_log')) {
        app_log('update', 'venta', 'Edición de venta', [
            'id'                => $id,
            'rut'               => $rut,
            'numero_fact'       => $numero_fact,
            'fecha_despacho'    => $fecha_despacho,
            'producto'          => $producto,
            'sku'               => $sku,
            'lote'              => $lote,
            'fecha_fabricacion' => $fecha_fabricacion,
            'fecha_vencimiento' => $fecha_de_vencimiento,
            'n_serie_ini'       => $n_serie_ini,
            'n_serie_fin'       => $n_serie_fin
        ]);
    }

/* TITULO RESPUESTA EXITOSA */

    // Respondemos solo "OK"
    echo "OK";
    exit;

} else {
    // CAMBIO: Respuesta texto plano
    echo "Error: Solicitud invalida o datos incompletos.";
    exit;
}

/* -------------------------------
   -- INICIO CIERRE CONEXION BD --
   ------------------------------- */

// $mysqli->close();

/* ----------------------------
   -- FIN CIERRE CONEXION BD --
   ---------------------------- */

/* ------------------------------------------------------------------------------------------------------------
   -------------------------------------- FIN ITred Spa actualizar_venta .PHP ---------------------------------
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
<?php

// Conexión a la Base de Datos
$mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");

if ($mysqli->connect_errno) {
    echo "Error BD: " . $mysqli->connect_error;
    exit;
}

$mysqli->set_charset("utf8");

// Solo aceptamos peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibimos los 3 datos clave
    $rut     = $_POST['rut'] ?? '';
    $factura = $_POST['factura'] ?? '';
    $sku     = $_POST['sku'] ?? '';

    // Limpieza de datos (igual que al guardar)
    $rutLimpio = str_replace(['.', ' '], '', $rut); 
    $factura   = trim($factura);
    $sku       = trim($sku);

    // Si falta algún dato, devolvemos OK.
    // ¿Por qué? Porque significa que el producto NO se guardó en la BD (estaba incompleto),
    // así que el JS solo tiene que borrarlo de la pantalla visualmente.
    if (empty($rutLimpio) || empty($factura) || empty($sku)) {
        echo "OK"; 
        exit;
    }

    // Borramos el registro que coincida con esos 3 datos
    // Usamos LIMIT 1 para asegurarnos de borrar solo uno a la vez
    $sql = "DELETE FROM venta WHERE rut = ? AND numero_fact = ? AND sku = ? LIMIT 1";
    
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sss", $rutLimpio, $factura, $sku);
        
        if ($stmt->execute()) {
            // Respondemos "OK" para que ventas.js sepa que todo salió bien
            echo "OK"; 
        } else {
            echo "Error al eliminar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la consulta: " . $mysqli->error;
    }

} else {
    echo "Metodo no permitido";
}

$mysqli->close();
?>
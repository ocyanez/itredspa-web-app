<?php
//
// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
//

// -----------------------------------------------------------------------------------------------------------------
// ---------------------------------------- INICIO ITred Spa guardar_datos.PHP -------------------------------------
// -----------------------------------------------------------------------------------------------------------------

// TITULO CONFIGURACION HEADER

    // Eliminar cualquier salida anterior
    ob_clean();

    // Respuesta en texto plano
    header('Content-Type: text/plain; charset=utf-8');

    // Prevenir CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');

    // Capturar errores
    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

// TITULO RECEPCION DE DATOS

    try {

        // Recibir datos del POST
        $rut = $_POST['rut'] ?? '';
        $numeroFact = $_POST['numeroFact'] ?? '';
        $fechaActual = $_POST['fechaActual'] ?? '';
        $productos = $_POST['productos'] ?? [];

        error_log("GuardarDatos: Inicio proceso para Factura: $numeroFact");
// TITULO VALIDACION DE RUT

    // Validaciones
    if (empty($productos) || !is_array($productos)) {
        throw new Exception('No se recibieron productos válidos');
    }

    if (empty($rut) || empty($numeroFact)) {
        throw new Exception('Faltan datos de cabecera (RUT o Factura)');
    }

// TITULO INSERCION DE DATOS

    // Conexión a BD
    $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    if ($mysqli->connect_error) {
        throw new Exception("Error conexión BD: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8");

    // Iniciar transacción
    $mysqli->begin_transaction();

 
    // Preparar consultas INSERT y UPDATE
    // Consulta para verificar si existe
    $sqlCheck = "SELECT id FROM venta WHERE rut = ? AND numero_fact = ? AND sku = ? LIMIT 1";
    $stmtCheck = $mysqli->prepare($sqlCheck);

    // Consulta INSERT (para productos nuevos)
    $sqlInsert = "INSERT INTO venta (sku, numero_fact, fecha_despacho, producto, cantidad, lote, fecha_fabricacion, fecha_vencimiento, n_serie_ini, n_serie_fin, rut) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = $mysqli->prepare($sqlInsert);

    // Consulta UPDATE (para productos existentes)
    $sqlUpdate = "UPDATE venta 
                  SET producto = ?, 
                      cantidad = ?, 
                      lote = ?, 
                      fecha_fabricacion = ?, 
                      fecha_vencimiento = ?, 
                      n_serie_ini = ?, 
                      n_serie_fin = ?, 
                      fecha_despacho = ? 
                  WHERE rut = ? AND numero_fact = ? AND sku = ?";
    $stmtUpdate = $mysqli->prepare($sqlUpdate);

    if (!$stmtCheck || !$stmtInsert || !$stmtUpdate) {
        throw new Exception("Error preparando consultas: " . $mysqli->error);
    }

    // Recorrer productos
    foreach ($productos as $p) {
        
        if (!isset($p['sku'])) continue;

        // Extraer datos del producto
        $p_sku = $p['sku'];
        $p_producto = $p['producto'];
        $p_cantidad = $p['cantidad'];
        $p_lote = $p['lote'];
        $p_fecha_fab = $p['fecha_fabricacion'];
        $p_fecha_venc = $p['fecha_vencimiento'];
        $p_serie_ini = $p['n_serie_ini'];
        $p_serie_fin = $p['n_serie_fin'];

        
        // Verificar si el producto ya existe en la BD
        $stmtCheck->bind_param("sss", $rut, $numeroFact, $p_sku);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            // caso a: El producto YA EXISTE → Hacer UPDATE
            error_log("UPDATE: SKU {$p_sku} ya existe, actualizando...");

            $stmtUpdate->bind_param("sssssssssss",
                $p_producto,    // 1
                $p_cantidad,    // 2
                $p_lote,        // 3
                $p_fecha_fab,   // 4
                $p_fecha_venc,  // 5
                $p_serie_ini,   // 6
                $p_serie_fin,   // 7
                $fechaActual,   // 8
                $rut,           // 9 (WHERE)
                $numeroFact,    // 10 (WHERE)
                $p_sku          // 11 (WHERE)
            );

            if (!$stmtUpdate->execute()) {
                throw new Exception("Error actualizando SKU {$p_sku}: " . $stmtUpdate->error);
            }

        } else {
            // caso b: El producto NO EXISTE → Hacer INSERT=
            error_log("INSERT: SKU {$p_sku} es nuevo, insertando...");

            $stmtInsert->bind_param("sssssssssss",
                $p_sku,
                $numeroFact,
                $fechaActual,
                $p_producto,
                $p_cantidad,
                $p_lote,
                $p_fecha_fab,
                $p_fecha_venc,
                $p_serie_ini,
                $p_serie_fin,
                $rut
            );

            if (!$stmtInsert->execute()) {
                throw new Exception("Error insertando SKU {$p_sku}: " . $stmtInsert->error);
            }
        }

        // Liberar resultado del CHECK
        $stmtCheck->free_result();
    }

    // Confirmar cambios
    $mysqli->commit();

    // Respuesta exitosa
    echo "OK";

} catch (Exception $e) {
    // Si hay error, revertir
    if (isset($mysqli)) {
        $mysqli->rollback();
    }
    error_log("Error en guardar_datos.php: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
    
} finally {
    // Cerrar statements
    if (isset($stmtCheck)) $stmtCheck->close();
    if (isset($stmtInsert)) $stmtInsert->close();
    if (isset($stmtUpdate)) $stmtUpdate->close();
    if (isset($mysqli)) $mysqli->close();
}

// -----------------------------------------------------------------------------------------------------------------
// ------------------------------------------ FIN ITred Spa guardar_datos.PHP --------------------------------------
// -----------------------------------------------------------------------------------------------------------------

//
// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
//
?>
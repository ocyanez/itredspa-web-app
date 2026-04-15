<?php

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

// -----------------------------------------------------------------------------------------------------------------
// ------------------------------------- INICIO ITred Spa validar_factura .PHP -------------------------------------
// -----------------------------------------------------------------------------------------------------------------

// header('Content-Type: text/plain; charset=utf-8');

// ACTIVAR REPORTE DE ERRORES (Solo para detectar fallas internas)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
    $mysqli->set_charset("utf8");
} catch (Exception $e) {
    echo "ERROR_DB: " . $e->getMessage();
    exit;
}

// // TITULO CONSULTA SQL

    // 1. Recibimos los datos y forzamos TRIM para quitar espacios que vengan del JS
    $rut = isset($_GET['rut']) ? trim($_GET['rut']) : '';
    $factura = isset($_GET['factura']) ? trim($_GET['factura']) : '';
    $sku = isset($_GET['sku']) ? trim($_GET['sku']) : ''; 

    // 2. Limpieza de seguridad y formato
    // Quitamos puntos al RUT por si la BD los tiene sin puntos
    $rut_limpio_str = str_replace('.', '', $rut);
    $rut_formateado = $mysqli->real_escape_string($rut_limpio_str); 
    $factura_limpia = $mysqli->real_escape_string($factura);
    $sku_limpio = $mysqli->real_escape_string($sku);

    if ($rut_formateado == '' || $factura_limpia == '') { echo "VACIO"; exit; }


    // CASO 1: Validación Completa (Tenemos RUT, Factura y SKU)
    if ($sku_limpio != '') {
        
        // CORRECCIÓN APLICADA: Usamos TRIM() en las columnas de la BD 
        // Esto soluciona si en la base de datos dice "567 " (con espacio)
        $sql = "SELECT cantidad_producto, descripcion_producto FROM factura 
                WHERE TRIM(rut_empresa) = '$rut_formateado' 
                AND TRIM(n_factura) = '$factura_limpia' 
                AND TRIM(codigo_producto) = '$sku_limpio' 
                LIMIT 1";
                
        $resultado = $mysqli->query($sql);
        
        if ($resultado && $resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            // ÉXITO: Devolvemos cantidad y descripción
            echo $fila['cantidad_producto'] . '|' . $fila['descripcion_producto']; 
        } else {
            // ERROR: No se encontró. 
            // Truco de depuración: Si quieres saber qué pasó, descomenta la siguiente línea temporalmente:
            // echo "ERROR: SQL ejecutado: $sql"; 
            echo "ERROR: SKU_NO_EXISTE_EN_FACTURA";
        }

    } 

    // CASO 2: Validación Simple (Solo verificamos si existe la factura, sin SKU)
    else {
        $sql = "SELECT id FROM factura 
                WHERE TRIM(rut_empresa) = '$rut_formateado' 
                AND TRIM(n_factura) = '$factura_limpia' 
                LIMIT 1";

        $resultado = $mysqli->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            echo "EXISTE";
        } else {
            echo "NO_EXISTE";
        }
    }
    
    $mysqli->close();

// -----------------------------------------------------------------------------------------------------------------
// -------------------------------------- FIN ITred Spa validar_factura .PHP ---------------------------------------
// -----------------------------------------------------------------------------------------------------------------
    
?>
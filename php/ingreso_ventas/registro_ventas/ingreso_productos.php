<?php

// <!--
// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
// -->

// <!-- ------------------------------------------------------------------------------------------------------------
//      ------------------------------------- INICIO ITred Spa ingreso_datos .PHP ----------------------------------
//      ------------------------------------------------------------------------------------------------------------ -->


// <!-- ------------------------
//      -- INICIO CONEXION BD --
//      ------------------------ -->

// Evitar salidas de error visibles que rompan el JS
error_reporting(0);

// 1. Conexión a la Base de Datos
$mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    echo "ERROR|Fallo conexion BD";
    exit();
}

// <!-- ---------------------
//      -- FIN CONEXION BD --
//      --------------------- -->

// TITULO CONSULTA SQL INGRESO PRODUCTOS

    // 2. Recibir datos (usamos los 'name' del formulario HTML)
    $sku      = trim($_POST['sku'] ?? '');
    $producto = trim($_POST['producto'] ?? '');

    // 3. Validaciones
    if ($sku === '' || $producto === '') {
        echo "ERROR|El SKU y el Producto son obligatorios.";
        exit();
    }

    // 4. Verificar si el SKU ya existe (para no duplicar)
    $check = $mysqli->prepare("SELECT id FROM producto WHERE sku = ?");
    $check->bind_param("s", $sku);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "ERROR|El SKU '$sku' ya existe en el sistema.";
        $check->close();
        exit();
    }
    $check->close();

    // 5. Insertar en la tabla 'producto' (usando columnas: sku, producto)
    $sql = "INSERT INTO producto (sku, producto) VALUES (?, ?)";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        // "ss" significa: String, String
        $stmt->bind_param("ss", $sku, $producto);
        
        if ($stmt->execute()) {
            echo "OK|Producto '$producto' registrado correctamente.";
        } else {
            echo "ERROR|No se pudo guardar en la BD: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "ERROR|Error en la consulta SQL: " . $mysqli->error;
    }

//  <!-- -------------------------------
//      -- INICIO CIERRE CONEXION BD --
//      ------------------------------- -->   

// $mysqli->close();

// <!-- ----------------------------
//      -- FIN CIERRE CONEXION BD --
//      ---------------------------- -->

// <!-- ----------------------------
//      -- FIN CIERRE CONEXION BD --
//      ---------------------------- -->

// <!-- ------------------------------------------------------------------------------------------------------------
//      -------------------------------------- FIN ITred Spa ingreso_datos .PHP ------------------------------------
//      ------------------------------------------------------------------------------------------------------------ -->

?>


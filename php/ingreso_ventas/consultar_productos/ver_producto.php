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
//      ------------------------------------- INICIO ITred Spa ver_producto .PHP -----------------------------------
//      ------------------------------------------------------------------------------------------------------------ -->

// <!-- ------------------------
//      -- INICIO CONEXION BD --
//      ------------------------ -->

// reportar errores de mysqli como excepciones
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // conwexión a la base de datos
    $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
    $mysqli->set_charset("utf8");
} catch (Exception $e) {
    die("<h1>Error de Conexión:</h1> " . $e->getMessage());
}

// <!-- ---------------------
//      -- FIN CONEXION BD --
//      --------------------- -->

// Obtener SKU desde GET y sanitizar
$sku_original = isset($_GET['sku']) ? $_GET['sku'] : '';
$sku = $mysqli->real_escape_string(trim($sku_original));
// Inicializar variable de datos
$datos = null;
// Buscar en historial_qr primero
if ($sku) {
    // Consulta en historial_qr
    $sql = "SELECT * FROM historial_qr WHERE TRIM(sku) = '$sku' ORDER BY id DESC LIMIT 1";
    $resultado = $mysqli->query($sql);
    // Verificar si se encontraron datos
    if ($resultado && $resultado->num_rows > 0) {
        $datos = $resultado->fetch_assoc();
    } else {
        // Si no se encontró en historial_qr, buscar en venta
        $sql2 = "SELECT * FROM venta WHERE TRIM(sku) = '$sku' LIMIT 1";
        // Ejecutar la segunda consulta
        $resultado2 = $mysqli->query($sql2);
        // Verificar si se encontraron datos
        if ($resultado2 && $resultado2->num_rows > 0) {
            $datos = $resultado2->fetch_assoc();
        }
    }
}
?>

<!-- TITULO HTML -->

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detalle: <?php echo htmlspecialchars($sku); ?></title>
        <!-- llama al archivo css que contiene los estilos para la página del menú -->
        <link rel="stylesheet" href="/css/ingreso_ventas/consultar_productos/ver_producto.css?v=<?= time() ?>">
    </head>
    <body>
        
        <!-- TITULO TARJETA -->

            <!-- llama a la función de verificación de producto -->
            <?php if ($datos): ?>
                <!-- Tarjeta de producto -->
                <div class="tarjeta">
                    <!-- Contenido de la tarjeta -->
                    <div class="encabezado">
                        <!-- Encabezado de la tarjeta -->
                        <h1><?php echo htmlspecialchars($datos['producto']); ?></h1>
                        <!-- SKU -->
                        <div class="sku">SKU: <?php echo htmlspecialchars($datos['sku']); ?></div>
                    </div>
                    <!-- Cuerpo de la tarjeta -->
                    <div class="cuerpo">
                        <!-- Información del producto -->
                        <div class="fila">
                            <!-- Etiqueta de cantidad -->
                            <span class="etiqueta">Cantidad</span>
                            <!-- Valor de cantidad -->
                            <span class="valor"><?php echo htmlspecialchars($datos['cantidad']); ?></span>
                        </div>
                        <!-- Lote -->
                        <div class="fila">
                            <!-- Etiqueta de lote -->
                            <span class="etiqueta">Lote</span>
                            <!-- Valor de lote -->
                            <span class="valor"><?php echo htmlspecialchars($datos['lote']); ?></span>
                        </div>
                        <!-- Fecha de fabricación -->
                        <div class="fila">
                            <!-- Etiqueta de fecha de fabricación -->
                            <span class="etiqueta">Fecha Fab.</span>
                            <!-- Valor de fecha de fabricación -->
                            <span class="valor"><?php echo htmlspecialchars($datos['fecha_fabricacion']); ?></span>
                        </div>
                        <!-- Número de serie -->
                        <?php if(isset($datos['n_serie_ini']) && !empty($datos['n_serie_ini'])): ?>
                        <!-- Número de serie inicio -->
                        <div class="fila">
                            <!-- Etiqueta de número de serie inicio -->
                            <span class="etiqueta">Serie Inicio</span>
                            <!-- Valor de número de serie inicio -->
                            <span class="valor"><?php echo htmlspecialchars($datos['n_serie_ini']); ?></span>
                        </div>
                
                        <?php endif; ?>
                        <!-- Número de serie fin -->
                        <?php if(isset($datos['n_serie_fin']) && !empty($datos['n_serie_fin'])): ?>
                        <!-- Etiqueta de número de serie fin -->
                        <div class="fila">
                            <!-- Valor de número de serie fin -->
                            <span class="etiqueta">Serie Fin</span>
                            <!-- Valor de número de serie fin -->
                            <span class="valor"><?php echo htmlspecialchars($datos['n_serie_fin']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Pie de la tarjeta -->
                    <div class="pie">Verificado por Sistema Trazabilidad ITred</div>
                </div>
            <!-- Fin del bloque de información del producto -->
            <?php else: ?>
                <!-- Mensaje de error -->
                <div class="tarjeta">
                    <!-- Contenido del mensaje de error -->
                    <div class="error">
                        <!-- Icono de advertencia -->
                        <span style="font-size:40px; display:block; margin-bottom:10px;">⚠️</span>
                        <h3>Producto no encontrado</h3>
                        <p>Buscando SKU: <strong>[<?php echo htmlspecialchars($sku); ?>]</strong></p>
                        <br>
                        <small style="color:red;">
                            Diagnóstico:<br>
                            Conexión BD: OK<br>
                            Tabla historial: Sin resultados<br>
                            Tabla venta: Sin resultados
                        </small>
                    </div>
                </div>
            <?php endif; ?>
    <!-- llama al archivo js que contiene la lógica para la página del ver_producto -->
    <script src="/js/ingreso_ventas/consultar_productos/ver_producto.js"></script>
    </body>
    </html>

<!-- ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa ver_producto.php ---------------------------------------
    ------------------------------------------------------------------------------------------------------------- -->
	
<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

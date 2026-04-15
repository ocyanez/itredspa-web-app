<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<!-- ------------------------------------------------------------------------------------------------------------
     ------------------------------------- INICIO ITred Spa procesar_producto .PHP ------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

<?php
    // procesar_producto.php
    // 1. Conexión
    $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
    $mysqli->set_charset("utf8mb4");

    if ($mysqli->connect_error) {
        die("Error de conexión: " . $mysqli->connect_error);
    }
?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>procesar_producto</title>
</head>
<body>

<!-- TITULO EDITAR PRODUCTO -->
 
    <?php

        // Función para volver atrás con alerta
        function volver($mensaje) {
            // Usamos "../" una sola vez en lugar de dos.
            // Esto asume que menu.php está en la carpeta 'ingreso_ventas' (una arriba de 'ingreso_clientes')
            header("Location: ../renderizar_menu.php?pagina=ingreso_datos&mensaje_producto=" . urlencode($mensaje));
            exit();
        }
        // Procesamiento del formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Determinar la acción
            $accion = $_POST['accion'] ?? '';

            // Editar producto
            if ($accion === 'editar_producto') {
                // Recibir datos
                $id = intval($_POST['id']);
                $sku = trim($_POST['sku']);
                $nombre = trim($_POST['nombre']);
                // Validar datos
                if ($id > 0 && !empty($sku) && !empty($nombre)) {
                    // Preparar consulta
                    $sql = "UPDATE producto SET sku = ?, producto = ? WHERE id = ?";
                    $stmt = $mysqli->prepare($sql);
                    // Ejecutar y verificar
                    if ($stmt) {
                        // Vincular parámetros
                        $stmt->bind_param("ssi", $sku, $nombre, $id);
                        // Ejecutar
                        if ($stmt->execute()) {
                            // Éxito
                            $stmt->close();
                            // Cerrar conexión
                            $mysqli->close();
                            volver("Producto actualizado correctamente.");
                        } else {
                            // Error en ejecución
                            $error = $stmt->error;
                            // Cerrar conexiones
                            $stmt->close();
                            $mysqli->close();
                            volver("Error al actualizar: " . $error);
                        }
                    } else {
                        // Error en preparación
                        $mysqli->close();
                        // Cerrar conexión
                        volver("Error en la preparación de la consulta.");
                    }
                } else {
                    // Datos incompletos
                    $mysqli->close();
                    // Cerrar conexión
                    volver("Error: Datos incompletos.");
                }
            }
        } else {
            // Si entran directo sin POST
            header("Location: ../renderizar_menu.php?pagina=ingreso_datos");
            exit();
        }
    ?>
  <script src="/js/ingreso_ventas/ingreso_clientes/procesar_producto.js?v=<?= time() ?>"></script>
</body>
</html>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

<?php
// Cierra la conexión a la base de datos
// $mysqli->close();
?>

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa procesar_producto .PHP --------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

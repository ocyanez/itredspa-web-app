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
     ------------------------------------- INICIO ITred Spa buscar_empresa .PHP ---------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

    <?php
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
        // Establecer el charset a utf8mb4
        $mysqli->set_charset("utf8mb4");
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Empresa</title>
</head>
<body>

<!-- TITULO BUSCAR EMPRESA -->

   <script> 
    <?php
    // 2. Consulta (Usamos $mysqli que es la variable definida arriba)
    $sql_val = "SELECT rut_empresa, nombre_empresa FROM factura WHERE rut_empresa IS NOT NULL AND rut_empresa != ''";
    $res_val = $mysqli->query($sql_val);

    // 3. Generar la variable JS manualmente (Sin json_encode)
    echo "var baseDatosEmpresas = {";

    if ($res_val) {
        while ($fila = $res_val->fetch_assoc()) {
            // Limpieza de RUT (Quitar puntos y espacios)
            $rutLimpio = str_replace(array('.', ' '), '', trim($fila['rut_empresa']));
            
            // Limpieza de Nombre (addslashes es CRÍTICO para evitar errores con comillas)
            $nombreLimpio = addslashes($fila['nombre_empresa']);
            $nombreLimpio = str_replace(array("\r", "\n"), " ", $nombreLimpio); // Quitar Enters

            // Escribir línea: '123456789': 'Nombre Empresa',
            echo "'$rutLimpio': '$nombreLimpio',";
        }
    }

    echo "};";

    ?>
    </script>
    
  <script src="/js/ingreso_ventas/registro_ventas/buscar_empresa.js?v=<?= time() ?>"></script>
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
     -------------------------------------- FIN ITred Spa buscar_empresa .PHP -----------------------------------
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
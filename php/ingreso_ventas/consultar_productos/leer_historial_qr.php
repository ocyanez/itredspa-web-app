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
//      ------------------------------------- INICIO ITred Spa leer_historial_qr.php -------------------------------
//      ------------------------------------------------------------------------------------------------------------ -->

// <!-- ------------------------
//      -- INICIO CONEXION BD --
//      ------------------------ -->

    $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
    $mysqli->set_charset("utf8");

// <!-- ---------------------
//      -- FIN CONEXION BD --
//      --------------------- -->
?>

<!-- TITULO CONSULTA SQL  -->

<?php
    // seleccionar todo el historial ordenado por el ultimo ingresado
    $sql = "SELECT * FROM historial_qr ORDER BY id DESC LIMIT 50";
    $result = $mysqli->query($sql);

    $lineas = [];

    while($row = $result->fetch_assoc()) {
        // formato estandarizado ITred: dato|||dato|||dato
        // Usamos los separadores que tu JS ya entiende
        $lineas[] = $row['sku'] . "|||" . $row['producto'] . "|||" . $row['cantidad'] . "|||" . $row['lote'] . "|||" . $row['fecha_fabricacion'];
    }

    // unir todo con ;;;
    echo implode(";;;", $lineas);
?>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <?php
        // $mysqli->close();
    ?>

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->	

<!-- ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa leer_historial_qr.php ----------------------------------
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

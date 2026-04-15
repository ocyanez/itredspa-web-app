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
//      ------------------------------------- INICIO ITred Spa borrar_historial_qr .PHP ----------------------------
//      ------------------------------------------------------------------------------------------------------------ -->

// <!-- ------------------------
//      -- INICIO CONEXION BD --
//      ------------------------ -->

    // Conexión
    $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
    $mysqli->set_charset("utf8");

    if ($mysqli->connect_error) {
        die("Fallo: " . $mysqli->connect_error);
    }

// <!-- ---------------------
//      -- FIN CONEXION BD --
//      --------------------- -->

// TITULO BORRADO HISTORIAL QR

    //  Lógica de Borrado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Borrar TODO
        if (isset($_POST['todo']) && $_POST['todo'] === 'si') {
            $mysqli->query("DELETE FROM historial_qr"); 
        }
        
        // Borrar UNO SOLO
        else if (isset($_POST['sku'])) {
            $sku = $mysqli->real_escape_string($_POST['sku']);
            $mysqli->query("DELETE FROM historial_qr WHERE sku = '$sku'");
        }
    }

    // Usamos ruta absoluta para que no falle.
    header("Location: /php/ingreso_ventas/renderizar_menu.php?pagina=generar_qr");
    exit();
?>

<script src="/js/ingreso_ventas/consultar_productos/borrar_historial_qr.js"></script>


<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->
    <!-- <?php 
        // $mysqli->close();
    ?> -->
<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->	

<!-- ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa borrar_historial_qr .PHP -------------------------------
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

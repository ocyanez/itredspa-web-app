<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<!-- ------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa CSFR Middleware .PHP--------------------------------------
    ------------------------------------------------------------------------------------------------------------- -->
<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

    <?php
        // establece la conexión a la base de datos trazabil_ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<?php

// TITULO INICIA LA SESIÓN SI NO HA SIDO INICIADA
    $mysqli->set_charset("utf8");
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Inicia la sesión PHP si aún no ha sido iniciada
    }

// TITULO FUNCIÓN PARA PROTEGER CONTRA CSRF

    function csrf_protect() {
        // Verifica si el método de la solicitud es post
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Verifica si el token csrf está presente en la sesión y en la solicitud
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                // Si el token no es válido, detiene la ejecución y muestra un error
                die("Error: CSRF token inválido.");
            }
        }
    }

// TITULO GENERA UN NUEVO TOKEN CSRF SI NO EXISTE

    if (empty($_SESSION['csrf_token'])) {
        // Genera un token CSRF seguro y lo almacena en la sesión
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
?>



<!-- ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa CSFR Middleware .PHP ----------------------------------------
    ------------------------------------------------------------------------------------------------------------- -->
<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

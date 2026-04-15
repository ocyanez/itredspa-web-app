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
     ------------------------------------- INICIO ITred Spa usuario_final .PHP ----------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

    <?php
        // Establece la conexión a la base de datos de ingreso_ventas_bd
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/inicio_sesion/superadmin/usuario_final.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    <title>Página de Usuario Final</title>
</head>


<?php

// TITULO INICIO DE SESION Y VALIDACION

    // Inicia sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Validación: solo Usuario Final
    if (empty($accesoDesdeMenu) && (!isset($_SESSION['correo']) || $_SESSION['rol'] !== 'usuario_final')) {
        header("Location: /ingreso_ventas.php");
        exit();
    }

    // Conexión a la base de datos
    $mysqli = new mysqli("localhost", "root", "Segma1@@", "ingreso_ventas_bd");
    $mysqli->set_charset("utf8");

    // Variables de control
    $rol = $_SESSION['rol'];
    $esSuperadmin = false;

    // Guarda la vista actual
    $_SESSION['vista'] = 'usuario_final.php';

    // Incluye el contenido común
    include __DIR__ . '/usuarios_contenido.php';
    ?>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <?php
    //$mysqli->close();
    ?>

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa usuario_final .PHP ------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->
<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

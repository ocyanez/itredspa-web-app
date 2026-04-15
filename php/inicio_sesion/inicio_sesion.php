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
     -------------------------------------- FIN ITred Spa inicio_sesion .PHP ------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

    <?php
        // establece la conexión a la base de datos trazabil_ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
        // include auditoría global
        require_once __DIR__ . '/seguridad/log_registros.php';
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<!-- TITULO HTML -->

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <!-- carga el archivo de estilos CSS para la página de el inicio de sesion principal -->
        <link rel="stylesheet" href="/css/inicio_sesion/inicio_sesion.css?v=<?= time() ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
        <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
        <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    </head>

        <!-- TITULO BODY -->

    <body>

            <?php

        session_start();

        // TITULO AUTENTICACION Y VALIDACION DE CORREO EN BASE DE DATOS
            $mysqli->set_charset("utf8");
            if (isset($_SESSION['correo'])) {
                header("Location: /php/ingreso_ventas/renderizar_menu.php");
                exit();
            }

        // TITULO MENSAJE DE ERROR

            //Variable para almacenar mensajes de error
                $error_mensaje = '';
            ?>

        <!-- TITULO CONTENEDOR DE FORMULARIOS -->

            <div class="container">
                <div id="formulario-container">

                        <!-- Contenedor con el formulario de login -->
                        <div id="loginForm" class="form-content" style="display: block;">
                            <!-- Llamado a php con el formulario de login -->
                            <?php include '../inicio_sesion/formulario_login.php'; ?> 
                        </div>

                        <!-- Contenedor con el formulario de registro -->
                        <div id="registroForm" class="form-content" style="display: none;">
                            <!-- Llamado a php con el formulario de registro -->
                                <?php include '../inicio_sesion/formulario_registro.php'; ?> 
                        </div>

                </div>
            </div>


        <!-- TITULO ARCHIVO JS -->

            <!-- Enlace al archivo JavaScript para la funcionalidad del inicio de sesión -->
                <script src="/js/inicio_sesion/inicio_sesion.js?v=<?= time() ?>"></script>

    </body>
</html>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <!-- <?php 
        // Cierra la conexión a la base de datos
        // $mysqli->close();
    ?> -->

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa inicio_sesion .PHP ------------------------------------
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

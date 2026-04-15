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
     ------------------------------------- INICIO ITred Spa inicio_roles .PHP -----------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

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

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Establece la codificación de caracteres a UTF-8 -->
    <meta charset="UTF-8">
    <!-- Configura el diseño responsivo -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Enlace al archivo CSS para estilos -->
    <link rel="stylesheet" href="/css/inicio_sesion/inicio_principal/inicio_roles.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    <!-- Título de la página, muestra el rol del usuario -->
    <title>Inicio - <?php echo htmlspecialchars(ucfirst($rol)); ?></title>
</head>

    <!-- TITULO BODY -->

    <body>

        <?php

            session_start();
            $mysqli->set_charset("utf8");
            
    // TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN

            // Construir la URL completa
            $Url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            // Verificar si la URL contiene 'superadmin.php'
            $esSuperadmin = strpos($Url, 'superadmin.php') !== false;
            // Verifica si el usuario ha iniciado sesión
            if (!$esSuperadmin && !isset($_SESSION['correo'])) {
                // Si el usuario no ha iniciado sesión, redirige a la página de inicio
                $archivo = '/ingreso_ventas.php';
                header("Location: ".$archivo);
                exit();
            }

    // TITULO OBTENER DATOS DEL USUARIO DESDE LA SESIÓN

            // Obtener los datos del usuario desde la sesión
            $nombre = $_SESSION['nombre'];
            $apellido = $_SESSION['apellido'];
            $username = $_SESSION['username'];
            $correo = $_SESSION['correo'];
            $rol = $_SESSION['rol'];

    // TITULO DEFINIR LA RUTA DE LA PÁGINA DE USUARIOS SEGÚN EL ROL

            // Definir la ruta de la página de usuarios según el rol
            $usuarioPagina = "";
            if ($rol === 'admin') {
                // Página para administradores
                // La original sería: $usuarioPagina = "/../php/inicio_sesion/superadmin/admin.php";
                $usuarioPagina = "/../php/ingreso_ventas/?pagina=inicio_sesion"; 
            } elseif ($rol === 'superadmin') {
                // Página para superadministradores
                $usuarioPagina = "../../ingreso_ventas/?pagina=inicio_sesion";
            } elseif ($rol === 'supervisor') {
                // Página para supervisores
                $usuarioPagina = "../../ingreso_ventas/?pagina=inicio_sesion";
            }
        ?>
        
    <!-- TITULO BARRA DE NAVEGACIÓN EDITOR_LOGIN -->

        <main>

    <!-- TITULO BARRA DE NAVEGACIÓN GENERAL -->

                <!-- Contenedor principal de la barra de navegación -->
                <div class="navbar">
                    <!-- Centra los elementos de navegación -->
                    <div class="nav-center">
                        <!-- Enlace al inicio -->
                        <a href="inicio_roles.php" class="nav-button" id="botonInicio">Inicio</a>
                        <!-- Enlace a la página lista usuarios -->
                        <a href="<?php echo $usuarioPagina; ?>" class="nav-button" id="botonUsuario">Usuarios</a>
                    </div>

                    <!-- Formulario para cerrar sesión -->
                    <form action="/inicio_principal/logout.php" method="post" style="display:inline;">
                        <!-- Botón para cerrar sesión -->
                        <button type="submit" class="nav-button">Cerrar sesión</button>
                    </form>
                </div>

    <!-- TITULO CONTENEDOR PRINCIPAL DEL CONTENIDO -->

                <div class="container">
                    <!-- Título de bienvenida con el nombre del usuario -->
                    <h1>Bienvenido, <?php echo htmlspecialchars($nombre); ?> <?php echo htmlspecialchars($apellido); ?>!</h1>
                    <!-- Muestra el correo electrónico del usuario -->
                    <p>Correo electrónico: <?php echo htmlspecialchars($correo); ?></p>
                </div>
        </main>

    <!-- TITULO ARCHIVO JS -->

            <script src="/js/inicio_sesion/seguridad/log_registros.js?v=<?= time() ?>"></script>
            
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
     -------------------------------------- FIN ITred Spa inicio_roles .PHP -------------------------------------
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

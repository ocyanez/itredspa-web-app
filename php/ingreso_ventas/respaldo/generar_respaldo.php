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
     ------------------------------------- INICIO ITred Spa generar_respaldo .PHP -----------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

     <?php
        // establece la conexión a la base de datos trazabil_ingreso_ventas_bd_itred con el usuario root
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
    <title>Descargar Respaldo</title>
    <!-- hoja de estilos a css -->
    <link rel="stylesheet" href="/css/ingreso_ventas/respaldo/generar_respaldo.css">

    <!-- íconos para diferentes dispositivos y tamaños -->
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
</head>

    <!-- TITULO BODY -->

<body class="generar-respaldo">

    <!-- TITULO CONTENEDOR PRINCIPAL PARA ESTILOS DE PLANTILLA -->

        <!-- contenedor principal que engloba todo -->
        <div class="contenedor-principal">

    <!-- TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN -->

        <!-- bloque php para verificar si el usuario ha iniciado sesión -->
        <?php
            // Define codificación utf-8 para la conexión
            $mysqli->set_charset("utf8");
            if (session_status() === PHP_SESSION_NONE) {
                // inicia sesión si no está iniciada
                session_start();
            }

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
        ?>

    <!-- TITULO RESPALDO -->

        <!-- Tarjeta con opciones de respaldo -->
        <div class="card">
            <h1>DESCARGAR RESPALDO DE DATOS</h1>
            <p>Selecciona el formato de respaldo:</p>
            <!-- Opciones de formato de respaldo -->
            <div class="format-options">
                    <?php
                        // Asegurar que la sesión está iniciada y obtener rol
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        $rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;

                        // Si es admin, sólo mostrar la opción Excel Simple
                        if ($rol === 'admin') {
                            echo '<button class="formato-btn" onclick="seleccionar(\'excel_simple\')" id="btn-excel-simple">Excel Simple</button>';
                        } else {
                            // Para otros roles (incluyendo superadmin) mostrar todas las opciones
                            echo '<button class="formato-btn" onclick="seleccionar(\'sql\')" id="btn-sql">SQL</button>';
                            echo '<button class="formato-btn" onclick="seleccionar(\'excel_simple\')" id="btn-excel-simple">Excel Simple</button>';
                            echo '<button class="formato-btn" onclick="seleccionar(\'excel\')" id="btn-excel"> .csv Completo (.zip)</button>';
                        }
                    ?>
                </div>
            <!-- Formulario para enviar la solicitud de respaldo -->
            <form method="POST" id="formulario" action="/php/ingreso_ventas/respaldo/obtener_bd.php">
                <!-- Campo oculto para guardar el formato seleccionado -->
                <input type="hidden" name="formato" id="formato">
                <!-- Botón para descargar, inicialmente deshabilitado hasta seleccionar una opción -->
                <button type="submit" id="descargarBtn" disabled>Descargar</button>
            </form>
        </div>

    <!-- TITULO JS -->

        <!-- Archivo javascript para manejar la lógica de respaldo -->             
        <script src="/js/ingreso_ventas/respaldo/generar_respaldo.js"></script>

    </div> <!-- fin del contenedor principal-->

</body>
</html>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <?php 
        // cierra la conexión con la base de datos
        // $mysqli->close();
        ?>

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa generar_respaldo .PHP -------------------------------------------
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

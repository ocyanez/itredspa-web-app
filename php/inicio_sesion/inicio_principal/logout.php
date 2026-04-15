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
     ------------------------------------- INICIO ITred Spa logout .PHP -----------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

    <?php
        // establece la conexión a la base de datos trazabil_ingreso_ventas_bd con el usuario root
            $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
        // include auditoría global
            require_once __DIR__ . '/../seguridad/log_registros.php';
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

    <?php

    session_start();

    // TITULO VERIFICAR SI EL USUARIO ESTÁ LOGUEADO
    
        $mysqli->set_charset("utf8");
        // Verifica si el usuario está logueado
        if (isset($_SESSION['correo'])) {
            
            // Captura la información del usuario antes de destruir la sesión
            $username  = $_SESSION['username'];
            $correo    = $_SESSION['correo'];
            $nombre    = $_SESSION['nombre'];
            $apellido  = $_SESSION['apellido'];
            $telefono  = $_SESSION['telefono'];
            $direccion = $_SESSION['direccion'];
            $rol       = $_SESSION['rol'];
            $cargo     = $_SESSION['cargo'];
            $rut       = $_SESSION['rut'];

            // Incluye el archivo de registro
            require_once $_SERVER['DOCUMENT_ROOT'].'/php/inicio_sesion/seguridad/log_registros.php';
            // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
            // CAMBIO: registrar cierre de sesión con datos del usuario
            app_log(
                'logout',         // acción
                'usuario',        // entidad
                'Cierre de sesión',
                [
                    'correo'    => $correo,
                    'username'  => $username,
                    'nombre'    => $nombre,
                    'apellido'  => $apellido,
                    'telefono'  => $telefono,
                    'direccion' => $direccion,
                    'cargo'     => $cargo,
                    'rol'       => $rol,
                    'rut'       => $rut
                ]
            );
            // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        }

    // TITULO CERRAR SESIÓN

        // Cierra la sesión eliminando variables y destruyendo la sesión
        session_unset();
        session_destroy();

        // Redirige al usuario a la página de inicio de sesión
        header("Location: /ingreso_ventas.php");
        exit();
?>

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
     -------------------------------------- FIN ITred Spa logout .PHP -------------------------------------------
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

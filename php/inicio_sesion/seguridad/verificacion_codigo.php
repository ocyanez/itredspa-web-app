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
     ------------------------------------- INICIO ITred Spa verificacion_codigo .PHP ----------------------------
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

<?php
    session_start();

    // TITULO VERIFICA SI SE HA ENVIADO EL FORMULARIO

        // Establece el conjunto de caracteres UTF-8 para la conexión MySQL, asegurando compatibilidad con acentos y caracteres especiales.
        $mysqli->set_charset("utf8");
      // Verifica si el formulario fue enviado mediante el método POST.
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $codigo_ingresado = $_POST['codigo_verificacion'];

        // verifica si el código ingresado coincide con el código almacenado en la sesión
        if ($codigo_ingresado == $_SESSION['verification_code']) {
        // si coincide, realiza el registro del usuario
        include_once __DIR__ . '/log_registros.php';


    // TITULO OBTENCIÓN DE DATOS DEL USUARIO DESDE LA SESIÓN

            // obtiene el nombre del usuario almacenado temporalmente en la sesión
            $nombre = $_SESSION['registro_temporal']['nombre'];

            // obtiene el apellido del usuario almacenado temporalmente en la sesión
            $apellido = $_SESSION['registro_temporal']['apellido'];

            // obtiene el nombre de usuario (username) almacenado temporalmente en la sesión
            $username = $_SESSION['registro_temporal']['username'];

            // obtiene el correo electrónico del usuario almacenado temporalmente en la sesión
            $correo = $_SESSION['registro_temporal']['correo'];

            // obtiene la contraseña ya encriptada (hashed) almacenada temporalmente en la sesión
            $hashed_password = $_SESSION['registro_temporal']['hashed_password'];

            // obtiene el número de teléfono del usuario almacenado temporalmente en la sesión
            $telefono = $_SESSION['registro_temporal']['telefono'];

            // obtiene la dirección del usuario almacenada temporalmente en la sesión
            $direccion = $_SESSION['registro_temporal']['direccion'];

            // obtiene el cargo del usuario almacenado temporalmente en la sesión
            $cargo = $_SESSION['registro_temporal']['cargo'];

            // obtiene el rol del usuario almacenado temporalmente en la sesión
            $rol = $_SESSION['registro_temporal']['rol'];

            // obtiene el RUT del usuario almacenado temporalmente en la sesión
            $rut = $_SESSION['registro_temporal']['rut'];

    // TITULO INSERCIÓN DEL USUARIO EN LA BASE DE DATOS

            // Consulta SQL para insertar un nuevo usuario en la tabla 'usuario'
            $sql_insert = "INSERT INTO usuario (nombre, apellido, username, correo, password, telefono, direccion, cargo, rol, rut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Prepara la consulta SQL con el objeto MySQLi
            $stmt_insert = $mysqli->prepare($sql_insert);

            if ($stmt_insert) {
                // Vincula los parámetros a los valores obtenidos de la sesión
                $stmt_insert->bind_param("ssssssssss", $nombre, $apellido, $username, $correo, $hashed_password, $telefono, $direccion, $cargo, $rol, $rut);
                
                // Ejecuta la consulta preparada
                $stmt_insert->execute();

                if ($stmt_insert->affected_rows > 0) {
                    
                // LIMPIEZA DE LA SESIÓN
                    
                    // elimina el código de verificación de la sesión
                    unset($_SESSION['verification_code']);
                    
                    // elimina los datos temporales de registro de la sesión
                    unset($_SESSION['registro_temporal']);
                    

                    // Redirección según el origen del registro
                    if (isset($_SESSION['vista']) && $_SESSION['vista'] === 'formulario_registro.php') {
                        header("Location: /php/inicio_sesion/inicio_sesion.php?registro=exitoso");
                        
                    } else {
                        header("Location: /php/ingreso_ventas/renderizar_menu.php?pagina=usuarios");
                    }
                    
                    // incluye el archivo para registrar el log de actividad (asegurado con include_once)
                    include_once __DIR__ . '/log_registros.php';

                // REGISTRO DE USUARIO EN LOG
                    // Registra en el log la creación del nuevo usuario y su rol (usa app_log para formato consistente)
                    app_log('create', 'usuario', "Creación de usuario: $username", ['role' => $rol, 'actor' => $_SESSION['username'] ?? '']);
                    exit();
                } else {
                    // Si la inserción falla, almacena un mensaje de error en la sesión
                    $_SESSION['error'] = "Error al registrar el usuario.";
                    
                    // Redirige al usuario nuevamente a la página de verificación de código
                    header("Location: verificacion_codigo.php");
                    exit();
                }
                // cierra la declaración preparada
                $stmt_insert->close();
            }

            $mysqli->close();
        } else {

            // Redirección a la misma página con un error si el código es incorrecto
            $_SESSION['error'] = "Código incorrecto. Por favor, intenta nuevamente.";
            header("Location: verificacion_codigo.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Código</title>
    <link rel="stylesheet" href="/css/inicio_sesion/seguridad/verificacion_codigo.css?v=<?= time() ?>">
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

        <main>

    <!-- TITULO CONTAINER PRINCIPAL DONDE SE VERIFICA EL CÓDIGO -->

            <div class="container">
                <h2>Verificación de Código</h2>

                <!-- Formulario de verificación de código -->
                <form action="verificacion_codigo.php" method="POST">

                    <!-- Verifica si existe un mensaje de error en la sesión -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <!-- muestra el mensaje de error -->
                        <div class="error_mensaje"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                        <!-- Limpia el mensaje de error de la sesión para que no se muestre nuevamente -->
                        <?php unset($_SESSION['error']);
                        ?>
                    <?php endif; ?>

                    <!-- Campo de entrada para el código de verificación -->
                    <label for="codigo_verificacion">Ingresa el código que te enviamos por correo:</label>
                    
                    <!-- Campo de entrada para el código de verificación -->
                    <input type="text" id="codigo_verificacion" name="codigo_verificacion" required>

                    <!-- Botón para enviar el formulario -->
                    <button type="submit">Verificar Código</button>
                </form>
            </div>
        </main>

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
     -------------------------------------- FIN ITred Spa verificacion_codigo .PHP ------------------------------
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

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
     ------------------------------------- INICIO ITred Spa login .PHP ------------------------------------------
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
    // inicia el buffer al principio
    ob_start();
    // incluye auditoría global
    include_once '/itred.cl/php/inicio_sesion/seguridad/log_registros.php';



    // TITULO INCLUIR EL ARCHIVO DE MIDDLEWARE CSRF Y PROTEGER CONTRA ATAQUES CSRF

        include_once 'seguridad/csrf_middleware.php'; 



    // TITULO PROTEGE CONTRA ATAQUES CSRF

        // Se utiliza la función de csrf_middleware.php
            csrf_protect(); 



    // TITULO INCLUIR EL ARCHIVO PARA VERIFICAR INTENTOS FALLIDOS

        // Incluye el middleware CSRF para asegurar la protección CSRF en los formularios
            include_once 'seguridad/verificar_intento.php'; 



    // TITULO VERIFICAR SESIÓN Y REDIRECCIÓN SEGÚN EL ROL

         // Establece el conjunto de caracteres UTF-8 para la conexión MySQL
        $mysqli->set_charset("utf8");
        if (isset($_SESSION['correo'])) {
            // Si el usuario ya inició sesión, redirige al menú
            header("Location: /php/ingreso_ventas/renderizar_menu.php");

            exit();
         }



    // TITULO VERIFICACIÓN DE LA SOLICITUD POST

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Obtiene la dirección IP del cliente
            $ip_address = $_SERVER['REMOTE_ADDR']; 



    // TITULO VERIFICACIÓN DEL TOKEN CSRF

        // Verifica si el token CSRF está presente y es válido
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            // Termina la ejecución si el token es inválido
            die('Solicitud no válida');
        }



    // TITULO VERIFICACIÓN DE CONEXIÓN CON LA BASE DE DATOS

        // Verifica si hay algún error en la conexión con la base de datos
        if ($mysqli->connect_error) {
            // Detiene si hay error de conexión
            die("Error en la conexión: " . $mysqli->connect_error);
        }



    // TITULO VERIFICACIÓN DE INTENTOS FALLIDOS

        // Verifica si el número de intentos fallidos supera el límite permitido
        if (!verificar_intentos($mysqli)) {
            // Redirige si los intentos fallidos superan el límite
            header("Location: /php/inicio_sesion/inicio_sesion.php?error=3");
            exit();
        }



    // TITULO OBTENCIÓN DE DATOS DEL FORMULARIO

        // Obtiene el correo y la contraseña enviados por el formulario
        $correo = $_POST['correo'];
        $password = $_POST['password_login'];



    // TITULO CONSULTA SQL PARA OBTENER DATOS DEL USUARIO

        // Prepara la consulta SQL para obtener la información del usuario con el correo proporcionado
        $sql = "SELECT id, password, rol, username, nombre, apellido, telefono, direccion, cargo, rut FROM usuario WHERE correo=?";
        $stmt = $mysqli->prepare($sql);



    // TITULO EJECUCIÓN DE LA CONSULTA

        if ($stmt) {
            // Vincula el correo a la consulta SQL
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();



    // TITULO VERIFICACIÓN DE LA EXISTENCIA DEL USUARIO

            // Verifica si se encontró un usuario con el correo proporcionado
            if ($result->num_rows > 0) {
                // Obtiene los datos del usuario
                $row = $result->fetch_assoc();
                $hashed_password = $row['password'];
                $rol = $row['rol'];
                $username = $row['username'];
                $nombre = $row['nombre'];
                $apellido = $row['apellido'];
                $telefono = $row['telefono'];
                $direccion = $row['direccion'];
                $cargo = $row['cargo'];
                $rut = $row['rut'];



    // TITULO VERIFICACIÓN DE LA CONTRASEÑA

                // Verifica si la contraseña proporcionada coincide con la almacenada
                if (password_verify($password, $hashed_password)) {
                


    // TITULO INICIO DE SESIÓN EXITOSO

                // Asigna los valores del usuario a la sesión
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['rol'] = $rol;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['username'] = $username;
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['apellido'] = $apellido;
                    $_SESSION['telefono'] = $telefono;
                    $_SESSION['direccion'] = $direccion;
                    $_SESSION['cargo'] = $cargo;
                    $_SESSION['rut'] = $rut;



    // TITULO REGISTRO DEL INICIO DE SESIÓN

                    // Incluye el archivo de log para registrar el evento de inicio de sesión
                        require_once $_SERVER['DOCUMENT_ROOT'].'/php/inicio_sesion/seguridad/log_registros.php';
                        // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
                        // CAMBIO: log con detalles del usuario para evitar valores nulos
                        app_log(
                            'login',            // acción
                            'usuario',          // entidad
                            'Inicio de sesión', // mensaje
                            [                   // datos adicionales al log (JSON)
                                'id'        => $row['id'],
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



    // TITULO REDIRECCIÓN SEGÚN EL ROL DEL USUARIO

                    // Redirige a la página de inicio para todos los roles
                        ob_clean();
                        header("Location: ../ingreso_ventas/renderizar_menu.php?pagina=inicio");

                    // Asegura que el script termine después de la redirección
                        exit(); 
                } else {
                    


    // TITULO REGISTRO DE INTENTO FALLIDO

                    // Registra el intento fallido y redirige a la página de inicio de sesión con un mensaje de error
                        registrar_intento($mysqli, $ip_address, $correo); 
                    // Contraseña incorrecta
                        header("Location: /php/inicio_sesion/inicio_sesion.php?error=1"); 
                    // Detiene el script
                        exit(); 
                }
            } else {



    // TITULO REGISTRO DE INTENTO FALLIDO (USUARIO NO EXISTE)

                    // Si el usuario no existe, registra el intento fallido y redirige con mensaje de error
                        registrar_intento($mysqli, $ip_address, $correo); 
                    // Usuario no encontrado
                        header("Location: /php/inicio_sesion/inicio_sesion.php?error=2"); 
                    // Detiene el script
                        exit(); 
            }

            // Cierra la declaración preparada
                $stmt->close(); 
        } else {
        // Mensaje de error si la consulta no se prepara correctamente
            echo "Error en la preparación de la consulta.";
        }
    }



    // TITULO GENERACIÓN DEL TOKEN CSRF SI NO EXISTE

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Envía la salida final
    ob_end_flush();
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
     -------------------------------------- FIN ITred Spa login .PHP --------------------------------------------
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

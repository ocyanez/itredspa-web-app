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
     ------------------------------------- INICIO ITred Spa editar_usuario .PHP ---------------------------------
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

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="/css/inicio_sesion/registro/editar_usuario.css?v=<?= time() ?>">
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

    // TITULO INICIAR SESIÓN Y VERIFICAR ACCESO
            $mysqli->set_charset("utf8");
            // Inicia la sesión PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Incluye el archivo 'log_registros.php'
            //include(__DIR__ . '/../seguridad/log_registros.php');
            
            // Verificación de superadmin por URL
            $esSuperadmin = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'superadmin.php') !== false;

            // Evita errores si no hay sesión iniciada
            $rol = $_SESSION['rol'] ?? ($esSuperadmin ? 'superadmin' : null);

            // Si no es superadmin ni tiene sesión válida, redirige
            if (!$esSuperadmin && (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin')) {
                header("Location: /ingreso_ventas.php");
                exit;
            }
            

    // TITULO COMPROBACIÓN DE ROLES Y ACCESO A PÁGINAS

            // Define los roles permitidos
            $roles_permitidos = array('superadmin', 'admin');

            // Verifica si el correo no está en la sesión o si el rol no está en los permitidos
            if (!$esSuperadmin && !in_array($rol, $roles_permitidos)) {
                echo '<script>window.location.href = "/ingreso_ventas.php";</script>';
                exit();
            }
    // TITULO COMPROBACIÓN DEL ID DEL USUARIO EN GET

            // Verifica si no se recibe el ID o no es un número
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                // Dependiendo del rol, redirige a la página correspondiente
                if ($rol === 'superadmin') {
                    echo '<script>window.location.href = "/php/inicio_sesion/superadmin/superadmin.php";</script>';
                } elseif ($rol === 'admin') {
                    echo '<script>window.location.href = "/php/inicio_sesion/superadmin/admin.php";</script>';
                }
                exit();
            }

            // Asigna el valor del ID del usuario desde GET a una variable
            $id_usuario = $_GET['id'];

    // TITULO ACTUALIZACIÓN DEL USUARIO

            // Verifica si la solicitud es de tipo POST y el formulario ha sido enviado
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar'])) {
                // Recoge los datos del formulario
                $id = $_POST['id'];
                $username = $_POST['username'];
                $correo = $_POST['correo'];
                $rol_usuario_editado = $_POST['rol'];
                $nombre = $_POST['nombre'];
                $apellido = $_POST['apellido'];
                $telefono = $_POST['telefono'];
                $direccion = $_POST['direccion'];
                $cargo = $_POST['cargo'];

                // Consulta SQL para seleccionar el usuario por ID
                $sql = "SELECT * FROM usuario WHERE id=?";
                // Prepara la consulta
                $stmt = $mysqli->prepare($sql);
                // Vincula el parámetro ID
                $stmt->bind_param("i", $id);
                // Ejecuta la consulta
                $stmt->execute();
                // Obtiene el resultado de la consulta
                $resultado = $stmt->get_result();
                // Almacena el usuario actual
                $usuario_actual = $resultado->fetch_assoc();
                $stmt->close();

                // Verifica si se ingresaron ambas contraseñas para cambiarlas
                if (!empty($_POST['password']) && !empty($_POST['confirm_password'])) {

                    // Asigna la contraseña y la confirmación a variables
                    $password = $_POST['password'];
                    $confirm_password = $_POST['confirm_password'];

                    // Verifica si las contraseñas no coinciden
                    if ($password !== $confirm_password) {

                        // Muestra una alerta y redirige al formulario
                        echo '<script>alert("Las contraseñas no coinciden. Por favor, inténtalo de nuevo."); window.location.href = "editar_usuario.php?id=' . $id_usuario . '";</script>';
                        exit();
                    }

                    // Hash de la nueva contraseña
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Consulta SQL para actualizar todos los campos del usuario incluyendo la contraseña
                    $update_sql = "UPDATE usuario SET nombre=?, apellido=?, username=?, telefono=?, direccion=?, cargo=?, password=?, correo=?, rol=? WHERE id=?";
                    $update_stmt = $mysqli->prepare($update_sql); // Prepara la consulta de actualización
                    $update_stmt->bind_param("sssssssssi", $nombre, $apellido, $username, $telefono, $direccion, $cargo, $hashed_password, $correo, $rol_usuario_editado, $id); // Vincula los parámetros
                
                } else {

                    // Consulta SQL para actualizar el usuario sin cambiar la contraseña
                    $update_sql = "UPDATE usuario SET nombre=?, apellido=?, username=?, telefono=?, direccion=?, cargo=?, correo=?, rol=? WHERE id=?";
                    // Prepara la consulta de actualización
                    $update_stmt = $mysqli->prepare($update_sql);
                    // Vincula los parámetros
                    $update_stmt->bind_param("ssssssssi", $nombre, $apellido, $username, $telefono, $direccion, $cargo, $correo, $rol_usuario_editado, $id);
                }



                // Ejecuta la consulta de actualización
                if ($update_stmt->execute()) {
                    // Verifica si hubo cambios en la consulta
                    if ($update_stmt->affected_rows > 0) {
                        // Array para almacenar los cambios
                        $cambios = [];

                        // Comprueba si el nombre de usuario fue cambiado y lo agrega al array de cambios
                        if ($username !== $usuario_actual['username']) {
                            $cambios[] = "username de '{$usuario_actual['username']}' a '$username'";
                        }
                        // Comprueba si el correo fue cambiado y lo agrega al array de cambios
                        if ($correo !== $usuario_actual['correo']) {
                            $cambios[] = "correo de '{$usuario_actual['correo']}' a '$correo'";
                        }
                        // Comprueba si el rol fue cambiado y lo agrega al array de cambios
                        if ($rol_usuario_editado !== $usuario_actual['rol']) {
                            $cambios[] = "rol de '{$usuario_actual['rol']}' a '$rol'";
                        }
                        // Comprueba si el nombre fue cambiado y lo agrega al array de cambios
                        if ($nombre !== $usuario_actual['nombre']) {
                            $cambios[] = "nombre de '{$usuario_actual['nombre']}' a '$nombre'";
                        }
                        // Comprueba si el apellido fue cambiado y lo agrega al array de cambios
                        if ($apellido !== $usuario_actual['apellido']) {
                            $cambios[] = "apellido de '{$usuario_actual['apellido']}' a '$apellido'";
                        }
                        // Comprueba si el teléfono fue cambiado y lo agrega al array de cambios
                        if ($telefono !== $usuario_actual['telefono']) {
                            $cambios[] = "telefono de '{$usuario_actual['telefono']}' a '$telefono'";
                        }
                        // Comprueba si la dirección fue cambiada y lo agrega al array de cambios
                        if ($direccion !== $usuario_actual['direccion']) {
                            $cambios[] = "direccion de '{$usuario_actual['direccion']}' a '$direccion'";
                        }
                        // Comprueba si el cargo fue cambiado y lo agrega al array de cambios
                        if ($cargo !== $usuario_actual['cargo']) {
                            $cambios[] = "cargo de '{$usuario_actual['cargo']}' a '$cargo'";
                        }

                        // Si hay cambios, los convierte en una cadena
                        if (!empty($cambios)) {

                            // Convierte el array en una cadena separada por comas
                            $cambios_str = implode(", ", $cambios);

                            // Incluye el archivo para registrar logs
                            include_once __DIR__ . '/../seguridad/log_registros.php';

                            // Llama a la función de logging (app_log) con los cambios detectados
                            app_log('update', 'usuario', "Edición de usuario: $username", [
                                'actor' => $_SESSION['username'] ?? '',
                                'changes' => $cambios_str
                            ]);
                        }

                        // Redirige a la página de usuarios
                        if ($esSuperadmin) {
                            echo '<script>window.location.href = "/php/inicio_sesion/superadmin/superadmin.php?pagina=usuarios&superadmin=1";</script>';
                        } elseif ($rol === 'admin') {
                            echo '<script>window.location.href = "/php/ingreso_ventas/renderizar_menu.php?pagina=usuarios";</script>';
                        }
                        exit();
                    } else {
                        // Muestra mensaje si no se realizaron cambios
                        echo "No se realizaron cambios en el usuario.";
                    }
                } else {
                    // Muestra un error si falla la consulta
                    echo "Error al ejecutar la consulta de actualización.";
                }
                $update_stmt->close();
            }


    // TITULO CONSULTA SQL PARA OBTENER LOS DATOS DEL USUARIO

            // Consulta SQL para obtener los datos del usuario por su ID
            $sql = "SELECT * FROM usuario WHERE id=?";

            // Prepara la consulta SQL para ejecutar con el ID de usuario especificado
            $stmt = $mysqli->prepare($sql); 
            
            // Vincula el parámetro del ID del usuario a la consulta preparada
            $stmt->bind_param("i", $id_usuario);
            
            // Ejecuta la consulta preparada
            $stmt->execute(); 
            
            // Obtiene el resultado de la consulta ejecutada
            $resultado = $stmt->get_result(); 

            // Verifica si existe exactamente un usuario con el ID proporcionado
            if ($resultado->num_rows === 1) {
                // Almacena los datos del usuario en un array asociativo
                $usuario = $resultado->fetch_assoc();
            } else {
                // Si no se encuentra el usuario, redirige a la página correspondiente según el rol
                if ($rol === 'superadmin') {
                    // Si es superadmin redirige a superadmin.php
                    echo '<script>window.location.href = "/php/inicio_sesion/superadmin/superadmin.php";</script>';
                } elseif ($rol === 'admin') {
                    // Si es admin redirige a admin.php
                    echo '<script>window.location.href = "/php/inicio_sesion/superadmin/admin.php";</script>';
                }
                exit();
            }

        ?>

    <!-- TITULO CONTAINER PRINCIPAL PARA EDITAR USUARIO-->

            <!-- Container principal para el contenido -->
            <div class="container" id="editar-usuario" style="margin-left: 12px;">

            <!-- Título de la sección de editar usuario -->
            <h1>EDITAR USUARIO</h1>

    <!-- TITULO FORMULARIO DE EDITAR USUARIO -->
            
                <!-- Se toma la id del usuario para ser almacenada -->
                <form id="editarUsuarioForm" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post" onsubmit="return validatePasswords()">
                    
                    <!-- Fila 1 del contenedor de los datos del usuario -->                  
                    <div class="input-group">
                        <!-- Almacena la id -->
                        <?php if (isset($usuario['id'])) { ?>
                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                        <?php } ?>

                        <div class="cuadro">
                            <label for="nombre">Nombre</label>
                            <!-- Almacena el nombre -->
                            <input type="text" id="nombre" name="nombre"
                            value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" placeholder="Nombre">
                        </div>

                        <div class="cuadro">
                            <label for="apellido">Apellido</label>
                            <!-- Almacena el apellido -->
                            <input type="text" id="apellido" name="apellido"
                            value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>" placeholder="Apellido">
                        </div>

                        <div class="cuadro">
                            <label for="username">Nombre de Usuario</label>
                            <!-- Almacena el nombre de usuario -->
                            <input type="text" id="username" name="username"
                            value="<?php echo htmlspecialchars($usuario['username'] ?? ''); ?>" placeholder="Nombre de Usuario">
                        </div>
                    </div>                        

                    <!-- Fila 2 del contenedor de los datos del usuario -->
                    <div class="input-group">
                        <div class="cuadro">
                            <label for="correo">Correo Electrónico</label>
                            <!-- Almacena el correo -->
                            <input type="email" id="correo" name="correo"
                            value="<?php echo htmlspecialchars($usuario['correo'] ?? ''); ?>" placeholder="Correo Electrónico">
                        </div>
                        <div class="cuadro">
                            <label for="telefono">Teléfono</label>
                            <!-- Almacena el teléfono -->
                            <input type="text" id="telefono" name="telefono"
                            value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" placeholder="Teléfono">
                        </div>    

                        <div class="cuadro">
                            <label for="direccion">Dirección</label>
                            <!-- Almacena la dirección -->
                            <input type="text" id="direccion" name="direccion"
                            value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>" placeholder="Dirección">
                        </div>
                    </div> 

                    <!-- Fila 3 del contenedor de los datos del usuario -->
                    <div class="input-group">
                        <div class="cuadro">
                            <label for="cargo">Cargo</label>
                            <!-- Almacena el cargo -->
                            <input type="text" id="cargo" name="cargo"
                            value="<?php echo htmlspecialchars($usuario['cargo'] ?? ''); ?>" placeholder="Cargo">
                        </div>    

                        
                        <div class="cuadro">
                            <label for="rut">RUT</label>
                        <!-- Almacena el RUT -->
                            <input type="text" id="rut" name="rut" value="<?php echo htmlspecialchars($usuario['rut'] ?? ''); ?>"
                            placeholder="RUT" readonly>
                        </div>

                        <div class="cuadro">
                            <label for="rol">Rol</label>
                            <!-- Almacena el rol según con que rol este logeado -->
                            <select id="rol" name="rol">
                                <option value="admin" <?php echo ($usuario['rol'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="distribuidor" <?php echo ($usuario['rol'] === 'distribuidor') ? 'selected' : ''; ?>>Usuario Distribuidor</option>
                                <option value="usuario_final" <?php echo ($usuario['rol'] === 'usuario_final') ? 'selected' : ''; ?>>Usuario Final</option>
                            </select>
                        </div>    
                    </div>

                        <!-- Fila 4 y 5 del contenedor contraseña -->
                        <div class="cuadro">
                            <label for="password">Nueva Contraseña</label>
                            <!-- Almacena contraseña -->
                            <div class="password-container">
                                <input type="password" id="password" name="password" placeholder="Nueva Contraseña">
                                <button type="button" onclick="togglePasswordVisibility('password')">Mostrar</button>
                            </div>
                        </div>
                        <!-- Mensaje de error contraseña -->
                        <div id="password-error" class="error_mensaje"></div>

                        <!-- Cuarto contenedor de los datos del usuario -->
                        <div class="cuadro">
                            <label for="confirm_password">Repetir Nueva Contraseña</label>
                            <!-- Almacena la confirmación de la contraseña -->
                            <div class="password-container">
                                <input type="password" id="confirm_password" name="confirm_password"
                                    placeholder="Repetir Nueva Contraseña">
                                <button type="button" onclick="togglePasswordVisibility('confirm_password')">Mostrar</button>
                            </div>
                        </div>

                        <!-- Mensaje de error de confirmación de la contraseña -->
                        <div id="confirm-password-error" class="error_mensaje"></div>


                        <!-- Botón para enviar el formulario -->
                        <div class="button-group">
                            <button id="BotonGuardar" type="submit" name="actualizar">Guardar Cambios</button>
                        </div>
                </form>
        </div>

    <!-- TITULO ARCHIVO JS -->

        <!-- script con validación en tiempo real (ep_*) -->
        <script src="/js/inicio_sesion/registro/editar_usuario.js?v=<?= time() ?>"></script>
        <script src="/js/inicio_sesion/registro/validacion_ep.js?v=<?= time() ?>"></script>
        <script src="/js/inicio_sesion/registro/editar_perfil.js?v=<?= time() ?>"></script>
        <script src="/js/inicio_sesion/formulario_registro.js?v=<?= time() ?>"></script>
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
     -------------------------------------- FIN ITred Spa editar_usuario .PHP -----------------------------------
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

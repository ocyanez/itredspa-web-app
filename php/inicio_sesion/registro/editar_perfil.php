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
     ------------------------------------- INICIO ITred Spa editar_perfil .PHP ----------------------------------
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


<!-- establece la codificación de caracteres para evitar errores con acentos y caracteres especiales -->
    <?php $mysqli->set_charset("utf8");
// inicia la sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) session_start();

// verifica si la solicitud es de tipo POST (formulario enviado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check si aplica
    // $token = $_POST['csrf_token'] ?? '';
    // if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) { die('CSRF error'); }

    // obtiene los datos enviados por el formulario, con valores por defecto si están vacíos
    $id_post = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $username = $_POST['username'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // si se ingresó una nueva contraseña, se valida que ambas coincidan
    if (!empty($password) || !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            // si no coinciden, redirige al formulario con mensaje de error
            header('Location: /php/inicio_sesion/registro/editar_perfil.php?id=' . $id_post . '&error=password_mismatch');
            exit();
        }
        // si coinciden, se encripta la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // prepara la consulta SQL para actualizar todos los campos incluyendo la contraseña
        $update_sql = "UPDATE usuario SET nombre=?, apellido=?, username=?, telefono=?, direccion=?, cargo=?, password=? WHERE id=?";
        $update_stmt = $mysqli->prepare($update_sql);
        $update_stmt->bind_param("sssssssi", $nombre, $apellido, $username, $telefono, $direccion, $cargo, $hashed_password, $id_post);
    } else {
        // si no se actualiza la contraseña, prepara la consulta sin ese campo
        $update_sql = "UPDATE usuario SET nombre=?, apellido=?, username=?, telefono=?, direccion=?, cargo=? WHERE id=?";
        $update_stmt = $mysqli->prepare($update_sql);
        $update_stmt->bind_param("ssssssi", $nombre, $apellido, $username, $telefono, $direccion, $cargo, $id_post);
    }

    // ejecuta la consulta preparada
    if ($update_stmt->execute()) {
        // si el usuario editado es el mismo que está en sesión, actualiza los datos
        if (isset($_SESSION['usuario_id']) && intval($_SESSION['usuario_id']) === $id_post) {
            $_SESSION['nombre'] = $nombre;
            $_SESSION['apellido'] = $apellido;
            $_SESSION['username'] = $username;
            $_SESSION['telefono'] = $telefono;
            $_SESSION['direccion'] = $direccion;
            $_SESSION['cargo'] = $cargo;
        }

        // registra el cambio en el log de actividad
        $changes = "Perfil actualizado (id={$id_post})";
        app_log('update', 'usuario', "Actualización de perfil: $username", ['actor' => $_SESSION['username'] ?? '', 'details' => $changes]);

        // redirige al menú principal después de actualizar
        header('Location: /php/ingreso_ventas/renderizar_menu.php?pagina=usuarios&busqueda=');
        exit();
    } else {
        // opcional: si falla la actualización, registra el error y redirige con mensaje
        error_log("editar_perfil update error: " . $mysqli->error);
        header('Location: /php/inicio_sesion/registro/editar_perfil.php?id=' . $id_post . '&error=update_failed');
        exit();
    }
}  ?>


    <!-- TITULO HTML -->

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/inicio_sesion/registro/editar_perfil.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    <title>Editar Perfil</title>

    <!-- TITULO BODY -->

    <body>

        <!-- establece codificación para evitar errores con acentos y caracteres especiales -->
        <?php
            $mysqli->set_charset("utf8");

            // inicia sesión si aún no está iniciada
            if (session_status() === PHP_SESSION_NONE) session_start();


            // extrae variables de sesión si existen
            $correo          = $_SESSION['correo']   ?? '';
            $username        = $_SESSION['username'] ?? '';
            $nombre          = $_SESSION['nombre']   ?? '';
            $apellido        = $_SESSION['apellido'] ?? '';
            $telefono        = $_SESSION['telefono'] ?? '';
            $direccion       = $_SESSION['direccion'] ?? '';
            $cargoSesion     = $_SESSION['cargo']    ?? '';
            $rutSesion       = $_SESSION['rut']      ?? '';
            $rolSesion       = strtolower(trim($_SESSION['rol'] ?? ''));

            // Normalizar: si se accede mediante menu.php?pagina=editar_perfil sin id, usar el id de la sesión
            if (isset($_GET['pagina']) && $_GET['pagina'] === 'editar_perfil' && (empty($_GET['id']) || intval($_GET['id']) === 0)) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                // aceptar varias claves posibles en la sesión por si tu app usa otro nombre
                $sesId = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;
                $sesId = intval($sesId);
                if ($sesId > 0) {
                    $_GET['id'] = $sesId;
                }
            }

            // obtiene el id de sesión del usuario actual
            $usuarioSesionId = intval($_SESSION['usuario_id'] ?? 0);
            $rolSesion = $_SESSION['rol'] ?? '';

            // si no hay id en la url, usar el id de sesión
            // Normaliza $_GET['id'] para el resto del script
            if ((empty($_GET['id']) || intval($_GET['id']) === 0) && $usuarioSesionId > 0) {
                $_GET['id'] = $usuarioSesionId;
            }

            // verifica si se está en modo superadmin por parámetro en la url
            $esSuperadmin = isset($_GET['superadmin']) && $_GET['superadmin'] == '1';

            // función auxiliar para validar si el rol actual puede editar otro rol
            function puedeGestionarLocal(string $rolSesion, string $rolObjetivo): bool {
                $rs = strtolower(trim($rolSesion));
                $ro = strtolower(trim($rolObjetivo));
                if ($rs === 'superadmin') return true;
                if ($rs === 'admin') return in_array($ro, ['admin','distribuidor','usuario_final'], true);
                return false;
            }

            // determinar qué id editar: prioridad GET id -> sesión
            if (isset($_GET['id']) && intval($_GET['id']) > 0) {
                $edit_id = intval($_GET['id']);
            } else {
                // si no viene id, usar el id de sesión si existe
                $edit_id = $usuarioSesionId;
            }

            // si estamos en modo superadmin por URL pero no hay id y no hay sesión válida, pedir id
            if ($esSuperadmin && $edit_id === 0) {
                echo "<div class='error_mensaje'>Falta el parámetro id para editar (o inicia sesión como superadmin).</div>";
                exit();
            }

            // si no es superadmin y se solicita editar a otro usuario, validar permiso
            if (!$esSuperadmin && $edit_id !== $usuarioSesionId) {
                // consulta el rol del usuario objetivo
                $stmtR = $mysqli->prepare("SELECT rol FROM usuario WHERE id=?");
                $stmtR->bind_param('i', $edit_id);
                $stmtR->execute();
                $stmtR->bind_result($rolObjetivo);
                $okR = $stmtR->fetch();
                $stmtR->close();
                // si no tiene permiso, mostrar error
                if (!$okR || !puedeGestionarLocal($rolSesion, $rolObjetivo)) {
                    echo "<div class='error_mensaje'>No autorizado para editar este usuario.</div>";
                    exit();
                }
            }

            // cargar datos del usuario por id (si no existe se aborta)
            $stmt = $mysqli->prepare("SELECT id, nombre, apellido, username, correo, telefono, direccion, rol, cargo, rut FROM usuario WHERE id=?");
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $resultado = $stmt->get_result();

            // DEBUG TEMPORAL: si sigue fallando, muestra valores (elimina en producción)
            // error_log("editar_perfil GET: " . json_encode($_GET));
            // error_log("editar_perfil SESSION: " . json_encode($_SESSION));
            // error_log("editar_perfil edit_id: " . var_export($edit_id, true));

            // si no se encuentra el usuario, mostrar error
            if (!$resultado || $resultado->num_rows !== 1) {
                echo "<div class='error_mensaje'>Usuario no encontrado.</div>";
                exit();
            }
            // guarda los datos del usuario en un arreglo asociativo
            $usuario = $resultado->fetch_assoc();
            $stmt->close();


            // Verifica si la solicitud es de tipo POST
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // obtiene los datos del formulario
                $id_post = isset($_POST['id']) ? intval($_POST['id']) : $edit_id;
                $nombre = $_POST['nombre'] ?? '';
                $apellido = $_POST['apellido'] ?? '';
                $username = $_POST['username'] ?? '';
                $telefono = $_POST['telefono'] ?? '';
                $direccion = $_POST['direccion'] ?? '';
                $cargo = $_POST['cargo'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';

                // si se ingresó contraseña, validar coincidencia
                if (!empty($password) || !empty($confirm_password)) {
                    if ($password !== $confirm_password) {
                        echo '<script>alert("Las contraseñas no coinciden. Por favor, inténtalo de nuevo."); window.location.href = window.location.href;</script>';
                        exit();
                    }
                    // encripta la nueva contraseña
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    // prepara la consulta con contraseña
                    $update_sql = "UPDATE usuario SET nombre=?, apellido=?, username=?, telefono=?, direccion=?, cargo=?, password=? WHERE id=?";
                    $update_stmt = $mysqli->prepare($update_sql);
                    $update_stmt->bind_param("sssssssi", $nombre, $apellido, $username, $telefono, $direccion, $cargo, $hashed_password, $id_post);
                } else {
                    // prepara la consulta sin contraseña
                    $update_sql = "UPDATE usuario SET nombre=?, apellido=?, username=?, telefono=?, direccion=?, cargo=? WHERE id=?";
                    $update_stmt = $mysqli->prepare($update_sql);
                    $update_stmt->bind_param("ssssssi", $nombre, $apellido, $username, $telefono, $direccion, $cargo, $id_post);
                }

                // ejecuta la actualización
                if ($update_stmt->execute()) {
                    // Sólo actualizar la sesión si se editó el propio usuario
                    if (isset($_SESSION['usuario_id']) && intval($_SESSION['usuario_id']) === $id_post) {
                        $_SESSION['nombre'] = $nombre;
                        $_SESSION['apellido'] = $apellido;
                        $_SESSION['username'] = $username;
                        $_SESSION['telefono'] = $telefono;
                        $_SESSION['direccion'] = $direccion;
                        $_SESSION['cargo'] = $cargo;
                    }

                    // Registra el evento de actualización de perfil
                    include __DIR__ . '/../seguridad/log_registros.php';
                    $changes = "Perfil actualizado (id={$id_post})";
                    app_log('update', 'usuario', "Actualización de perfil: $username", ['actor' => $_SESSION['username'] ?? '', 'details' => $changes]);


                   // Redirigir a la lista de usuarios (forzar top.location si headers ya se enviaron)
                   $redirect = '/php/ingreso_ventas/renderizar_menu.php?pagina=usuarios&busqueda=';
                   if (!headers_sent()) {
                       header('Location: ' . $redirect);
                       exit();
                   } else {
                       // usar top para garantizar que la ventana principal cambie (evita iframes/includes que no navegan)
                       echo '<script>top.location.href = "' . htmlspecialchars($redirect, ENT_QUOTES) . '";</script>';
                       exit();
                   }


                } else {
                    // muestra error si la actualización falla
                    echo "Error al actualizar el perfil: " . $mysqli->error;
                }
            }
        ?>

    <!-- TITULO CONTAINER PRINCIPAL -->

            <!-- Container principal para el contenido -->
            <div class="container">
                <!-- Título de la sección de edición de perfil -->
                <h1>Editar Perfil</h1>

    <!-- TITULO FORMULARIO DE EDICIÓN -->

                    <form id="editarPerfilForm" action="/php/inicio_sesion/registro/editar_perfil.php?id=<?= intval($usuario['id']) ?>" method="post">
                        <!-- id del usuario a actualizar -->
                        <input type="hidden" name="id" value="<?= intval($usuario['id']) ?>">
                        <!-- si usas CSRF, incluye aquí el token -->
                        <?php if (!empty($_SESSION['csrf_token'])): ?>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <?php endif; ?>
                        
                        <!-- Formulario para editar el perfil -->
                        <div class="input-group-container">

                            <!-- container para grupos de entrada -->
                            <div class="input-group">
                                <!-- Grupo de entrada para datos del usuario -->
                                <div class="cuadro">
                                <!-- Campo para el nombre -->
                                    <label for="nombre">Nombre</label>
                                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" placeholder="Nombre" required>
                                </div>
                
                                <div class="cuadro">
                                <!-- Campo para el apellido -->
                                    <label for="apellido">Apellido</label>
                                    <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" placeholder="Apellido" required>
                                </div>

                                <div class="cuadro">
                                <!-- Campo para el nombre de usuario -->
                                    <label for="username">Nombre de usuario</label>
                                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" placeholder="Nombre de Usuario" required>
                                </div>
                            </div>

                                
                            <div class="input-group">
                                <div class="cuadro">
                                <!-- Campo para el correo electrónico, deshabilitado para edición -->
                                    <label for="correo">Correo electrónico</label>
                                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" placeholder="Correo Electrónico" required disabled>
                                </div>

                                <div class="cuadro">
                                <!-- Campo para el teléfono -->
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" placeholder="Teléfono" required>
                                </div>

                                <div class="cuadro">    
                                <!-- Campo para la dirección -->
                                    <label for="direccion">Dirección</label>
                                    <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion']); ?>" placeholder="Dirección" required>
                                </div>

                            </div>

                            <div class="input-group">               
                                <div class="cuadro">             
                                <!-- Campo de texto para el rut, deshabilitado para no permitir edición -->
                                    <label for="rut">RUT</label>
                                    <input type="text" id="rut" name="rut" value="<?php echo htmlspecialchars($usuario['rut']); ?>" placeholder="RUT" required disabled>
                                </div>

                                <!-- Campo para el cargo -->
                                    <div class="cuadro">
                                        <label for="cargo">Cargo</label>
                                        <input type="text" id="cargo" name="cargo" value="<?php echo htmlspecialchars($usuario['cargo']); ?>" placeholder="Cargo" required>
                                    </div>

                                    <div class="cuadro">
                                    <!-- Campo para el rol, deshabilitado para edición -->
                                        <label for="rol">Rol</label>
                                        <input type="text" id="rol" name="rol" value="<?php echo htmlspecialchars($usuario['rol']); ?>" placeholder="Rol" required disabled>
                                    </div>

                            </div>

                            <!-- Grupo de entrada para la contraseña -->
                            <div class="input-group">
                                <div class="cuadro">
                                    <label for="password">Contraseña</label>
                                    <!-- Container para la contraseña -->
                                    <div class="password-container">
                                    <!-- Campo para la nueva contraseña -->
                                    <input type="password" id="password" name="password" placeholder="Nueva Contraseña">
                                    <!-- Botón para mostrar/ocultar la contraseña -->
                                         <button type="button" onclick="togglePasswordVisibility('password')">Mostrar</button>
                                    </div>
                                </div>
                            </div>


                            <!-- Mensaje de error para la contraseña -->
                            <div id="password-error" class="error_mensaje"></div>

                            <div class="input-group">
                                 <div class="cuadro">
                                    <!-- Grupo de entrada para la confirmación de la contraseña -->
                                    <label for="confirm_password">Confirmar Contraseña</label>
                                    <!-- container para la confirmación de la contraseña -->
                                    <div class="password-container">
                                        <!-- Campo para repetir la nueva contraseña -->
                                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repetir Nueva Contraseña">
                                        <!-- Botón para mostrar/ocultar la contraseña -->
                                        <button type="button" onclick="togglePasswordVisibility('confirm_password')">Mostrar</button>
                                    </div>
                                 </div>
                            </div>
                            
                            <!-- Mensaje de error para la confirmación de la contraseña -->
                            <div id="confirm-password-error" class="error_mensaje"></div>

                            <div class="button-group">
                                <!-- Grupo de botones -->
                                <!-- Botón para regresar -->
                                <button id="BotonAtras" type="button" onclick="history.back()">Atrás</button>

                                <!-- Botón para guardar los cambios -->
                                <button id="BotonGuardar" type="submit">Guardar Cambios</button>
                            </div>
                    </form>

                    <!-- Verifica si se ha pasado el parámetro 'success' en la URL --> 
                    <?php if (isset($_GET['success'])): ?>
                        <!-- Mensaje de éxito -->
                        <p>Perfil actualizado exitosamente.</p> 
                    <?php endif; ?>
            </div>
        
    <!-- TITULO ARCHIVO JS -->

            <!-- Enlace al archivo JavaScript para editar perfil -->
            <script src="/js/inicio_sesion/registro/editar_perfil.js?v=<?= time() ?>"></script> 

            <!-- Enlace al archivo JavaScript para la funcionalidad de inicio -->
            <script src="/js/inicio_sesion/inicio_principal/inicio_roles.js?v=<?= time() ?>"></script> 

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
     -------------------------------------- FIN ITred Spa editar_perfil .PHP ------------------------------------
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

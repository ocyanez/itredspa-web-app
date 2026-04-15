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
     ------------------------------------- INICIO ITred Spa usuarios_contenido .PHP ----------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

<?php
// establece la conexión a la base de datos trazabil_ingreso_ventas_bd con el usuario root
$mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->
<?php

// Inicia la sesión si aún no está activa (evita errores si ya fue iniciada antes)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Extrae variables de sesión del usuario actual logueado
$correo          = $_SESSION['correo']   ?? ''; // correo del usuario
$username        = $_SESSION['username'] ?? ''; // nombre de usuario
$nombre          = $_SESSION['nombre']   ?? ''; // nombre real
$apellido        = $_SESSION['apellido'] ?? ''; // apellido
$telefono        = $_SESSION['telefono'] ?? ''; // número de contacto
$direccion       = $_SESSION['direccion'] ?? ''; // dirección
$cargoSesion     = $_SESSION['cargo']    ?? ''; // cargo 
$rutSesion       = $_SESSION['rut']      ?? ''; // RUT chileno
$rolSesion       = strtolower(trim($_SESSION['rol'] ?? '')); // rol del usuario (normalizado)
$usuarioSesionId = intval($_SESSION['usuario_id'] ?? 0); // ID numérico del usuario

// Si no hay rol definido en sesión, redirige al login (protección contra acceso directo)
if ($rolSesion === '') {
  header("Location: /ingreso_ventas.php");
  exit();
}

  // Verifica si el usuario actual puede gestionar el rol objetivo
  function puedeGestionar(string $rolSesion, string $rolObjetivo): bool
  {
    $rolSesion   = strtolower(trim($rolSesion)); // normaliza el rol del actor
    $rolObjetivo = strtolower(trim($rolObjetivo));  // normaliza el rol del objetivo
    // superadmin puede gestionar cualquier rol
    if ($rolSesion === 'superadmin') return true;
    // admin puede gestionar ciertos roles incluyendo a otros admin
    if ($rolSesion === 'admin') {
      // permitir que admin gestione también a otros admin
      return in_array($rolObjetivo, ['admin', 'distribuidor', 'usuario_final', 'bodega'], true);
    }
    // otros roles no pueden gestionar
    return false;
  }

  // Obtiene el rol de un usuario por su ID
  function rolDeUsuario(mysqli $db, int $id): ?string
    {
      $stmt = $db->prepare("SELECT rol FROM usuario WHERE id=?");
      if (!$stmt) return null;
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->bind_result($rol);
      $ok = $stmt->fetch();
      $stmt->close();
      // devuelve el rol normalizado si existe, o null si no se encontró
      return $ok ? strtolower(trim($rol)) : null;
  }
    
  // Verifica si existe al menos otro superadmin (evita eliminar si hay otro superadmin)
  function hayOtroSuperadmin(mysqli $db, int $excluirId = 0): bool
  {
    if ($excluirId > 0) {
        // excluye el ID actual si se está evaluando una eliminación o edición
        $stmt = $db->prepare("SELECT COUNT(*) FROM usuario WHERE rol='superadmin' AND id<>?");
        $stmt->bind_param('i', $excluirId);
    } else {
        // cuenta todos los superadmin sin exclusión
        $stmt = $db->prepare("SELECT COUNT(*) FROM usuario WHERE rol='superadmin'");
    }
    if (!$stmt) return false;
      $stmt->execute();
      $stmt->bind_result($c);
      $stmt->fetch();
      $stmt->close();
    // devuelve true si hay al menos uno (distinto del excluido)
    return $c > 0;
  }
    
  // Traduce el rol técnico a un cargo legible para mostrar en la interfaz
  function cargoPorRol(string $rol): string
  {
    switch (strtolower($rol)) {
      case 'superadmin':
        return 'super administrador';
      case 'admin':
        return 'administrador';
      case 'distribuidor':
        return 'distribuidor';
      case 'usuario_final':
        return 'usuario_final';
      case 'bodega':
        return 'bodega';
      default:
        return 'usuario_final';
    }
  }


// Carga el sistema de logging para registrar acciones como creación, edición o eliminación de usuarios
require_once __DIR__ . '/../seguridad/log_registros.php';

// Inicializa la variable de error para mostrar mensajes en el formulario
$error_mensaje = '';
// Si viene un parámetro GET con clave "error", se asigna un mensaje legible según el tipo
if (isset($_GET['error'])) {
  $error_mensaje = match ($_GET['error']) {
    'rut_invalido'             => 'El RUT proporcionado no es válido.',
    'rol_invalido'             => 'Rol seleccionado no es válido.',
    'contraseñas_no_coinciden' => 'Las contraseñas no coinciden.',
    'usuario_existente'        => 'El usuario/correo/RUT ya están en uso.',
    'correo_fallido'           => 'Problemas con el correo invalido. Es probable que no se haya registrado.',
    default                    => 'Ocurrió un error. Intenta nuevamente.',
  };
}

//Eliminar usuario (POST)

  // Este bloque se ejecuta solo si el formulario fue enviado por POST y se presionó el botón "eliminar"
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['eliminar'])) {
    // Se obtiene el ID del usuario a eliminar desde el formulario. Si no viene, se asigna 0 por defecto.
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    // Solo se continúa si el ID es válido mayor a 0
    if ($id > 0) {

      // Evitar que se pueda auto-eliminarse
      if ($usuarioSesionId > 0 && $id === $usuarioSesionId) {
        echo "<script>alert('No puedes eliminar tu propia cuenta.');</script>";
        echo '<script>window.location.href="/php/ingreso_ventas/menu.php?pagina=usuarios";</script>';
        exit();
      }
      // Se obtiene el rol del usuario que se quiere eliminar
      $rolObjetivo = rolDeUsuario($mysqli, $id);
      // Si no se encuentra el usuario (rol nulo), se muestra alerta y se redirige
      if ($rolObjetivo === null) {
        echo "<script>alert('Usuario no encontrado.');</script>";
        echo '<script>window.location.href="/php/ingreso_ventas/menu.php?pagina=usuarios";</script>';
        exit();
      }
      // Permisos
      if (!puedeGestionar($rolSesion, $rolObjetivo)) {
        echo "<script>alert('No autorizado para eliminar este rol.');</script>";
        echo '<script>window.location.href="/php/ingreso_ventas/menu.php?pagina=usuarios";</script>';
        exit();
      }
      // Proteger último superadmin para que no se pueda eliminar
      if ($rolObjetivo === 'superadmin' && !hayOtroSuperadmin($mysqli, $id)) {
        echo "<script>alert('No puedes eliminar al último superadmin.');</script>";
        echo '<script>window.location.href="/php/ingreso_ventas/menu.php?pagina=usuarios";</script>';
        exit();
      }
      // Log y eliminar, se registra en el log quién fue eliminado y por quién
      $sel = $mysqli->prepare("SELECT nombre, apellido, username, correo, rol FROM usuario WHERE id=?");
      if ($sel) {
        $sel->bind_param("i", $id);
        $sel->execute();
        $res = $sel->get_result();
        // Si se encuentra el usuario, se guarda la info en el log
        if ($res && $res->num_rows === 1) {
          $u = $res->fetch_assoc();
          app_log('delete', 'usuario', "Eliminación de usuario: {$u['nombre']} {$u['apellido']}", ['email' => $u['correo'], 'role' => $u['rol'], 'actor' => $_SESSION['username'] ?? '']);
        }
        $sel->close();
      }
      // Finalmente, se elimina el usuario de la base de datos
      $del = $mysqli->prepare("DELETE FROM usuario WHERE id=?");
      if ($del) {
        $del->bind_param("i", $id);
        $del->execute();
        $del->close();
      }
      // Redirección final al listado de usuarios
      echo '<script>window.location.href="/php/ingreso_ventas/menu.php?pagina=usuarios";</script>';
      exit();
    }
  }

//Editar usuario
if (isset($_GET['pagina']) && $_GET['pagina'] === 'editar_usuario' && isset($_GET['id'])) {
  $edit_id = intval($_GET['id']);

  // Normalizar parámetro corto: ?pagina=usuarios&editar_id=123  ->  ?pagina=editar_usuario&id=123
  if (isset($_GET['editar_id'])) {
    // Si quieres sólo mapear cuando no se pasó 'pagina', utiliza la condición siguiente:
    // if (!isset($_GET['pagina'])) { $_GET['pagina'] = 'editar_usuario'; $_GET['id'] = intval($_GET['editar_id']); }

    // Recomiendo mapear también cuando se está en la lista (pagina=usuarios)
    if (!isset($_GET['pagina']) || $_GET['pagina'] === 'usuarios') {
      $_GET['pagina'] = 'editar_usuario';
      $_GET['id']     = intval($_GET['editar_id']);
    }
  }


    // Si se llama como editar de otro usuario: ?pagina=editar_perfil&id=123
    if (isset($_GET['id'])) {
      $edit_id = intval($_GET['id']);

      // validar permisos: solo superadmin/admin según tu lógica
      $rolObjetivo = rolDeUsuario($mysqli, $edit_id);
      if ($rolObjetivo === null || !puedeGestionar($rolSesion, $rolObjetivo)) {
        echo "<div class='error_mensaje'>No autorizado para editar este usuario.</div>";
        exit;
      }

      // obtener datos del usuario
      $stmt = $mysqli->prepare("SELECT * FROM usuario WHERE id=?");
      $stmt->bind_param('i', $edit_id);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
        // ahora reutiliza el formulario de editar_perfil rellenando valores desde $user
      } else {
        echo "<div class='error_mensaje'>Usuario no encontrado.</div>";
        exit;
      }
    }



  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    // ----- Procesar edición -----

    $nombre    = $_POST['nombre']    ?? '';
    $apellido  = $_POST['apellido']  ?? '';
    $usernameU = $_POST['username']  ?? '';
    $correoU   = $_POST['correo']    ?? '';
    $telefonoU = $_POST['telefono']  ?? '';
    $direccionU = $_POST['direccion'] ?? '';
    $rutU      = $_POST['rut']       ?? '';
    $rolNuevo  = strtolower(trim($_POST['rol'] ?? ''));

    // Password opcional
    $pass  = $_POST['password']         ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';

    // Rol actual del objetivo
    $rolActual = rolDeUsuario($mysqli, $edit_id);
    if ($rolActual === null) {
      echo "<div class='error_mensaje'>Usuario no encontrado.</div>";
      exit();
    }
    // ¿Puedo gestionar al objetivo?
    if (!puedeGestionar($rolSesion, $rolActual)) {
      echo "<div class='error_mensaje'>No autorizado para modificar a este usuario.</div>";
      exit();
    }
    // ¿Qué roles puedo asignar?
    $permitidosAsignar = [
      'superadmin' => ['superadmin', 'admin', 'distribuidor', 'usuario_final', 'bodega'],
      'admin'      => ['admin', 'distribuidor', 'usuario_final', 'bodega'],
    ];
    if (!in_array($rolNuevo, $permitidosAsignar[$rolSesion] ?? [], true)) {
      echo "<div class='error_mensaje'>No autorizado para asignar el rol seleccionado.</div>";
      exit();
    }
    // No degradar al último superadmin
    if ($rolActual === 'superadmin' && $rolNuevo !== 'superadmin' && !hayOtroSuperadmin($mysqli, $edit_id)) {
      echo "<div class='error_mensaje'>No puedes degradar al último superadmin.</div>";
      exit();
    }
    // Cargo definido por servidor
    $cargoNuevo = cargoPorRol($rolNuevo);

    // Si viene password -> actualizar también password
    if (($pass !== '' || $pass2 !== '')) {
      if ($pass !== $pass2) {
        echo "<div class='error_mensaje'>Las contraseñas no coinciden.</div>";
        exit();
      }
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $update_sql = "UPDATE usuario SET nombre=?, apellido=?, username=?, correo=?, telefono=?, direccion=?, rol=?, cargo=?, rut=?, password=? WHERE id=?";
      $update_stmt = $mysqli->prepare($update_sql);
      if ($update_stmt) {
        $update_stmt->bind_param(
          "ssssssssssi",
          $nombre,
          $apellido,
          $usernameU,
          $correoU,
          $telefonoU,
          $direccionU,
          $rolNuevo,
          $cargoNuevo,
          $rutU,
          $hash,
          $edit_id
        );
        $update_stmt->execute();
        $update_stmt->close();
        app_log('update', 'usuario', "Edición de usuario: $nombre $apellido (password actualizado)", ['email' => $correoU, 'new_role' => $rolNuevo, 'actor' => $_SESSION['username'] ?? '']);
        echo '<script>window.location.href = "/php/ingreso_ventas/menu.php?pagina=usuarios&busqueda=";</script>';
        exit();
      } else {
        echo "<div class='error_mensaje'>Error al preparar la consulta de edición: " . $mysqli->error . "</div>";
        exit();
      }
    }

    // Sin password -> update normal

    // Se arma la consulta SQL para actualizar los datos del usuario
    $update_sql = "UPDATE usuario SET nombre=?, apellido=?, username=?, correo=?, telefono=?, direccion=?, rol=?, cargo=?, rut=? WHERE id=?";
    $update_stmt = $mysqli->prepare($update_sql);
    if ($update_stmt) {
       // Se vinculan los valores a la consulta preparada
      $update_stmt->bind_param("sssssssssi", $nombre, $apellido, $usernameU, $correoU, $telefonoU, $direccionU, $rolNuevo, $cargoNuevo, $rutU, $edit_id);
       // Se ejecuta la actualización
      $update_stmt->execute();
      $update_stmt->close();
      // Se registra en el log que se editó el usuario
      app_log('update', 'usuario', "Edición de usuario: $nombre $apellido", ['email' => $correoU, 'new_role' => $rolNuevo, 'actor' => $_SESSION['username'] ?? '']);
      // Redirección al listado de usuarios después de editar
      echo '<script>window.location.href = "/php/ingreso_ventas/menu.php?pagina=usuarios&busqueda=";</script>';
      exit();
    } else {
      // Si falla la preparación de la consulta, se muestra un mensaje de error
      echo "<div class='error_mensaje'>Error al preparar la consulta de edición: " . $mysqli->error . "</div>";
      exit();
    }
  } else {
    // Renderizar formulario edición (SIN crear otra página)

    // Se prepara la consulta para obtener los datos del usuario a editar
    $get_stmt = $mysqli->prepare("SELECT * FROM usuario WHERE id=?");
    if ($get_stmt) {
      $get_stmt->bind_param("i", $edit_id);
      $get_stmt->execute();
      $result = $get_stmt->get_result();
      // Si se encuentra el usuario, se guarda su información en $user
      if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // opciones de rol para el actor actual
        $opciones = ($rolSesion === 'superadmin')
          ? ['superadmin', 'admin', 'distribuidor', 'usuario_final', 'bodega']
          : (($rolSesion === 'admin') ? ['admin', 'distribuidor', 'usuario_final', 'bodega'] : []);
?>

        <!-- REVISION PARA LIMPIEZA -->

        <!-- Sección del formulario de edición de usuario, accesible para superadmin y admin --> <!-- revision -->
        <div class="container" id="editar-usuario" style="max-width: 900px; margin: 24px auto;">
          <h2 style="text-align:center; color: black;">EDITAR USUARIO</h2>

          <!-- Formulario que envía los datos por POST al backend para editar el usuario con el ID correspondiente -->
          <form id="editarUsuarioForm" method="POST" class="form-styled" action="/php/inicio_sesion/superadmin/superadmin.php?pagina=editar_usuario&id=<?= $edit_id ?>" onsubmit="return validateEditForm(this)">            <input type="hidden" name="editar_usuario" value="1">
          <!-- Campo oculto que indica que se está editando un usuario -->  
          <input type="hidden" name="editar_usuario" value="1">

            <!-- Primera fila: nombre, apellido y nombre de usuario -->
            <div class="input-group">
              <div class="recuadro">
                <div class="cuadro">
                  <label for="edit_nombre">Nombre</label>
                  <input id="edit_nombre" type="text" name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" placeholder="Nombre" required>
                </div>
              </div>

              <div class="recuadro">
                <div class="cuadro">
                  <label for="apellido">Apellido</label>
                  <input type="text" name="apellido" value="<?= htmlspecialchars($user['apellido']) ?>" placeholder="Apellido" required>
                </div>
              </div>

              <div class="recuadro">
                <div class="cuadro">
                  <label for="username">Nombre de usuario</label>
                  <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" placeholder="Usuario" required>
                </div>
              </div>
            </div>

            <!-- Segunda fila: correo, teléfono y dirección -->
            <div class="input-group">
              <div class="recuadro">
                <div class="cuadro">
                  <label for="correo">Correo electrónico</label>
                  <input type="email" name="correo" value="<?= htmlspecialchars($user['correo']) ?>" placeholder="Correo" required>
                </div>
              </div>

              <div class="recuadro">
                <div class="cuadro">
                  <label for="telefono">Teléfono</label>
                  <input type="tel" name="telefono" value="<?= htmlspecialchars($user['telefono']) ?>" placeholder="Teléfono" required>
                </div>
              </div>

              <div class="recuadro">
                <div class="cuadro">
                  <label for="direccion">Dirección</label>
                  <input type="text" name="direccion" value="<?= htmlspecialchars($user['direccion']) ?>" placeholder="Dirección" required>
                </div>
              </div>
            </div>

             <!-- Tercera fila: RUT, cargo y rol -->
            <div class="input-group">
              <div class="recuadro">
                <div class="cuadro">
                  <label for="rut">RUT</label>
                  <!-- Si el usuario tiene rol "admin", el campo se vuelve de solo lectura -->
                  <input type="text" name="rut" value="<?= htmlspecialchars($user['rut']) ?>" placeholder="RUT" required
                    <?= ($user['rol'] === 'admin') ? 'readonly' : '' ?>>
                  <div id="rut-error" class="error_mensaje"></div>
                </div>
              </div>

              <!-- El cargo se muestra como referencia, no se puede editar directamente -->
              <div class="recuadro">
                <div class="cuadro">
                  <label for="cargo">Cargo (no editable)</label>
                  <input type="text" name="cargo" value="<?= htmlspecialchars($user['cargo']) ?>" placeholder="Cargo" readonly>
                </div>
              </div>

              <!-- Selector de rol con opciones dinámicas según el rol del usuario actual -->
              <div class="recuadro">
                <div class="cuadro">
                  <label for="rol">Rol</label>
                  <select name="rol" required>
                    <?php foreach ($opciones as $opt): ?>
                      <option value="<?= $opt ?>" <?= ($user['rol'] === $opt ? 'selected' : '') ?>>
                        <?= ucfirst(str_replace('_', ' ', $opt)) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <!-- Campo para ingresar nueva contraseña -->
            <div class="cuadro">
              <div class="recuadro">
                <label for="password">Nueva Contraseña</label>
                <div class="password-container">
                  <input type="password" id="password" name="password" placeholder="Nueva Contraseña" required>
                  <button type="button" onclick="togglePasswordVisibilityEdit(this)">Mostrar</button>
                </div>
                <div id="edit-password-error" class="error_mensaje"></div>
              </div>
            </div>

            <!-- Campo para confirmar la nueva contraseña -->
            <div class="cuadro">
              <div class="recuadro">
                <label for="confirm_password">Repetir Nueva Contraseña</label>
                <div class="password-container">
                  <input type="password" id="confirm_password" name="confirm_password" placeholder="Repetir Nueva Contraseña" required>
                  <button type="button" onclick="togglePasswordVisibilityEdit(this)">Mostrar</button>
                </div>
                <div id="edit-confirm-password-error" class="error_mensaje"></div>
              </div>
            </div>

            <!-- Botón para enviar el formulario -->
            <div class="button-group">
              <button type="submit">Guardar Cambios</button>
            </div>
          </form>
        </div>

        <!-- Asegura que las funciones JS necesarias estén disponibles cuando este formulario se renderiza en modo edición -->

<?php
      // Si no se encuentra el usuario, se muestra un mensaje de error
      } else {
        echo "<div class='error_mensaje'>Usuario no encontrado.</div>";
      }
      // Se cierra la consulta preparada
      $get_stmt->close();
    } else {
      // Si falla la preparación de la consulta, se muestra el error correspondiente
      echo "<div class='error_mensaje'>Error al preparar la consulta: " . $mysqli->error . "</div>";
    }
  }
  // Se detiene la ejecución para evitar que se renderice contenido adicional
  return;
}


?>
<!-- TITULO HTML -->

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- CSS principal de esta vista (usa la misma ruta que validaste con 200 OK) -->
  <link rel="stylesheet" href="/css/inicio_sesion/superadmin/usuarios_contenido.css?v=<?= time() ?>">
  <!-- Íconos para distintos dispositivos y tamaños -->
  <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
  <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
  <link rel="manifest" href="/imagenes/favicon/site.webmanifest">

  <title>Gestión de Usuarios</title>
</head>


<body>
    <!-- TITULO BODY-->


    <!-- TITULO GESTION DE USUARIOS -->

        <!-- Contenedor principal que agrupa todo el contenido de la vista -->
        <div class="contenedor-principal">
          <!-- apartado de usuarios  -->
          <h1>GESTIÓN DE USUARIOS</h1>

    <!-- TITULO REGISTRO DE USUARIOS -->

          <!-- Sección del formulario de registro -->
          <div class="container" id="registro">
            <h2>Registro</h2>

            <!-- Si hay un mensaje de error, se muestra aquí -->
            <?php if (!empty($error_mensaje)): ?>
              <div class="error_mensaje"><?= htmlspecialchars($error_mensaje) ?></div>
            <?php endif; ?>

            <!-- Formulario de registro de usuario -->
            <form id="registroForm" action="/php/inicio_sesion/registro.php" method="POST" onsubmit="return validateRegistroForm(this)">
            <!-- Generación de token CSRF si no existe en la sesión -->  
            <?php if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

              <!-- Grupo de campos: nombre, apellido, nombre de usuario -->
              <div class="input-group">
                <!-- Campo para el nombre -->
                <div class="cuadro">
                  <label for="nombre">Nombre</label>
                  <input type="text" name="nombre" placeholder="Nombre" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$">
                </div>
                <!-- Campo para el apellido -->
                <div class="input-group">
                  <div class="cuadro">
                    <label for="nombre">Apellido</label>
                    <input type="text" name="apellido" placeholder="Apellido" required pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$">
                  </div>
                </div>
                <!-- Campo para el nombre de usuario -->
                <div class="input-group">
                  <div class="cuadro">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" name="username" placeholder="Nombre de usuario" required>
                  </div>
                </div>
              </div>

              <!-- Grupo de campos: correo, teléfono, dirección -->
              <div class="input-group">
                <!-- Campo para el correo electrónico, deshabilitado para edición -->
                <div class="cuadro">
                  <label for="correo">Correo electrónico</label>
                  <input type="email" name="correo" placeholder="Correo electrónico" required>
                </div>

                <!-- Campo para el teléfono -->
                <div class="cuadro">
                  <label for="telefono">Teléfono</label>
                  <input type="tel" name="telefono" placeholder="Teléfono" required pattern="^[0-9]+$" maxlength="12">
                </div>

                <!-- Campo para la dirección -->
                <div class="cuadro">
                  <label for="direccion">Dirección</label>
                  <input type="text" name="direccion" placeholder="Dirección" required>
                </div>
              </div>

              <!-- Campo para el RUT -->
              <div class="input-group">
                <!-- Campo de texto para el rut, deshabilitado para no permitir edición -->
                <div class="cuadro">
                  <label for="rut">RUT</label>
                  <input type="text" id="rut" name="rut" placeholder="RUT" maxlength="15" pattern="^[0-9\.\-kK]{1,15}$" required>
                </div>
              </div>

              <!-- Contenedor para mostrar errores relacionados con el RUT -->
              <div id="rut-error" class="error_mensaje"></div>

              <!-- Grupo de campos: cargo y rol -->
              <div class="input-group">
                <!-- Campo para el cargo -->
                <div class="cuadro">
                  <label for="cargo">Cargo</label>
                  <input type="text" name="cargo" placeholder="Cargo" required>
                </div>

                <!-- Campo para el rol -->
                <div class="cuadro">
                  <label for="rol">Rol</label>
                  <select name="rol" required>
                    <option value="" disabled selected>Selecciona un rol</option>

                    <!-- Opciones disponibles para superadmin -->
                    <?php if ($rolSesion === 'superadmin'): ?>
                      <option value="superadmin">Super Admin</option>
                      <option value="admin">Admin</option>
                      <option value="distribuidor">Distribuidor</option>
                      <option value="usuario_final">Usuario Final</option>
                      <option value="bodega">Bodega</option>

                    <!-- Opciones disponibles para admin -->
                    <?php elseif ($rolSesion === 'admin'): ?>
                      <!-- permitir a admin crear/seleccionar otros admin -->
                      <option value="admin">Admin</option>
                      <option value="distribuidor">Distribuidor</option>
                      <option value="usuario_final">Usuario Final</option>
                      <option value="bodega">Bodega</option>
                    <?php endif; ?>
                  </select>
                </div>
              </div>


              <!-- Campo para la contraseña -->
              <div class="input-group">

                <div class="cuadro">
                  <label for="password">Contraseña</label>
                  <!-- Container para la contraseña -->
                  <div class="password-container">
                    <!-- Campo para la nueva contraseña -->
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <!-- Botón para mostrar/ocultar la contraseña -->
                    <button type="button" onclick="togglePasswordVisibility(this)">Mostrar</button>
                  </div>
                </div>
              </div>

              <!-- Mensaje de error para la contraseña -->
              <div id="password-error" class="error_mensaje"></div>

              <!-- Campo para confirmar la contraseña -->
              <div class="input-group">
                <div class="cuadro">
                  <!-- Grupo de entrada para la confirmación de la contraseña -->
                  <label for="confirm_password">Confirmar Contraseña</label>
                  <!-- container para la confirmación de la contraseña -->
                  <div class="password-container">
                    <!-- Campo para repetir la nueva contraseña -->
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required>
                    <!-- Botón para mostrar/ocultar la contraseña -->
                    <button type="button" onclick="togglePasswordVisibility(this)">Mostrar</button>
                  </div>
                </div>
              </div>

              <!-- Contenedor para mostrar errores de confirmación -->
              <div id="confirm-password-error" class="error_mensaje"></div>

              <!-- Botón para enviar el formulario -->
              <button type="submit" id="botonRegistrar">Registrarse</button>
            </form>
          </div>

  <?php

    //TITULO BÚSQUEDA PARA LISTA DE USUARIOS
        // Se obtiene el término de búsqueda desde la URL, si existe
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
        // Se obtienen datos de sesión necesarios para aplicar restricciones
        $rolSesion = $_SESSION['rol'] ?? '';
        $correo = $_SESSION['correo'] ?? '';

        // Si el usuario en sesión es admin, se debe excluir a los superadmin de la lista
        $excluirSuperadmin = ($rolSesion === 'admin');

        // Base SQL sin filtro inicial
        $sql = "SELECT id, nombre, apellido, username, correo, telefono, direccion, rol, cargo, rut
                FROM usuario
                WHERE 1=1";

          // Si el usuario en sesión es admin, excluir superadmin
          if ($excluirSuperadmin) {
            $sql .= " AND LOWER(rol) <> 'superadmin'";
          }

          // Si hay búsqueda, añadir los filtros
          $parametros = [];
          if (mb_strlen($busqueda) >= 1) {
            $sql .= " AND (
              rut LIKE ? OR nombre LIKE ? OR apellido LIKE ? OR username LIKE ?
              OR correo LIKE ? OR telefono LIKE ? OR direccion LIKE ? OR rol LIKE ? OR cargo LIKE ?
            )";

            // Se prepara el término con comodines para búsqueda parcial
            $term = '%' . $busqueda . '%';
            // Se crea un arreglo con el mismo término repetido para cada campo
            $parametros = array_fill(0, 9, $term);
          }

          // Ejecuta la consulta
          $stmt = $mysqli->prepare($sql);

          // Si hay parámetros de búsqueda, se vinculan a la consulta
          if (!empty($parametros)) {
            $stmt->bind_param(str_repeat('s', count($parametros)), ...$parametros);
          }

          // Se ejecuta la consulta
          $stmt->execute();
          // Se obtiene el resultado para procesarlo más adelante
          $resultado = $stmt->get_result();
        ?>

    <!--TITULO LISTA DE USUARIOS -->

      <!-- Contenedor principal de la tabla de usuarios -->
      <div class="container-tabla">
        <h2>Lista de Usuarios</h2>

        <!-- Formulario de búsqueda de usuarios -->
      <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">

    <form action="" method="get" class="busqueda-form" style="margin-bottom: 0;">
        <input type="text" name="busqueda" placeholder="Buscar Usuario" value="<?= htmlspecialchars($busqueda) ?>">
        <button type="submit" class="boton-buscar" role="button" tabindex="0">
            <div class="titulo_cuadro">&nbsp;</div>
            <div class="input-borrar" style="justify-content:center; gap:8px;">
                <img src="/imagenes/ingreso_ventas/consultar_productos/buscar_img5.png"
                    alt="Buscar" style="width:50px;height:50px;">
                <span class="texto-acciones">Buscar</span>
            </div>
        </button>
    </form>

    <a href="/php/inicio_sesion/superadmin/descargar_usuarios.php?busqueda=<?= urlencode($busqueda) ?>" class="btn-excel-link">
        <button type="button" class="btn-excel">
            <img src="/imagenes/inicio_sesion/superadmin/superadmin_img2.png" alt="Icono Excel" class="btn-excel-icon">
            <span>Descargar Lista</span>
        </button>
    </a>

</div>
        <br>
        

        <!-- Contenedor donde se muestran los resultados de la búsqueda -->
        <div id="resultados">

            <!-- Si hay resultados, se muestra la tabla -->
            <?php if ($resultado && $resultado->num_rows > 0): ?>
              <div class="tabla-responsiva">
                <table class="tabla-estilo">
                  <thead>
                    <tr>
                      <th>RUT</th>
                      <th>Nombre</th>
                      <th>Apellido</th>
                      <th>Usuario</th>
                      <th>Correo</th>
                      <th>Teléfono</th>
                      <th>Dirección</th>
                      <th>Rol</th>
                      <th>Cargo</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = $resultado->fetch_assoc()):
                      // Rol del usuario en la fila actual
                      $rolFila = strtolower($row['rol']);
                      // Verifica si el usuario en sesión puede gestionar al usuario de esta fila
                      $puede   = puedeGestionar($rolSesion, $rolFila);
                      // Verifica si el usuario en sesión está viendo su propia cuenta
                      $esMismo = ($correo !== '' && $correo === $row['correo']);
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($row['rut']) ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['apellido']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['correo']) ?></td>
                        <td><?= htmlspecialchars($row['telefono']) ?></td>
                        <td><?= htmlspecialchars($row['direccion']) ?></td>
                        <td><?= htmlspecialchars($row['rol']) ?></td>
                        <td><?= htmlspecialchars($row['cargo']) ?></td>
                        <td>

                          <!-- Acciones disponibles según el rol del usuario en sesión -->
                          <?php if ($rolSesion === 'superadmin'): ?>
                            <?php if ($esMismo): ?>
                              <!-- Si es el mismo usuario, puede editar su perfil -->
                              <a href="?pagina=editar_perfil" class="edit-link">Editar Perfil</a>
                              <!-- Verificación adicional para proteger al último superadmin -->
                              <?php if (!($rolFila === 'superadmin' && !hayOtroSuperadmin($mysqli, intval($row['id'])))): ?>
                              <!-- No se muestra acción adicional si es el último superadmin -->
                              <?php endif; ?>

                            <?php elseif ($puede): ?>
                              <!-- Redirige al apartado que maneja la edición del usuario pasando el id  -->
                              <a href="?pagina=editar_perfil&id=<?= urlencode($row['id']); ?>" class="edit-link">Editar</a>
                              <form action="/php/ingreso_ventas/menu.php?pagina=usuarios" method="post" class="inline-form">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']); ?>">
                                <button type="submit" name="eliminar" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</button>
                              </form>
                            <?php endif; ?>


                          <?php elseif ($rolSesion === 'admin'): ?>
                            <?php if ($esMismo): ?>
                              <!-- Si es el mismo usuario, puede editar su perfil -->
                              <a href="?pagina=editar_perfil" class="edit-link">Editar Perfil</a>
                              <?php elseif ($puede): ?>
                              <!-- Redirige al apartado que maneja la edición del usuario pasando el id -->
                              <a href="?pagina=editar_perfil&id=<?= urlencode($row['id']); ?>" class="edit-link">Editar</a>
                              <form action="/php/ingreso_ventas/menu.php?pagina=usuarios" method="post" class="inline-form">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']); ?>">
                                <button type="submit" name="eliminar" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</button>
                              </form>
                            <?php endif; ?>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <!-- Si no hay resultados, se muestra un mensaje informativo -->
            <?php else: ?>
              <div class="tabla-placeholder" style="padding:14px; text-align:center; border:1px dashed var(--color-borde); border-radius:8px;">
                No se encontraron usuarios para “<?= htmlspecialchars($busqueda) ?>”.
              </div>
            <?php endif; ?>

        </div>
      </div>


    <!-- TITULO ARCHIVO JS -->

      <!-- JS compartidos (los mismos que usa admin para estilos/validaciones) -->
      <script src="/js/inicio_sesion/formulario_registro.js?v=<?= time() ?>"></script>
      <script src="/js/inicio_sesion/seguridad/log_registros.js?v=<?= time() ?>"></script>
      <script src="/js/inicio_sesion/registro/editar_perfil.js?v=<?= time() ?>"></script>
      <script src="/js/inicio_sesion/seguridad/validarRut.js?v=<?= time() ?>"></script>
      <script src="/js/inicio_sesion/superadmin/usuarios_contenido.js?v=<?= time() ?>"></script>

      <!-- Enlace al archivo JavaScript para usuarios contenido -->
      <script src="/js/inicio_sesion/superadmin/usuarios_contenido.js?v=<?= time() ?>"></script>

    <!--fin del contenedor principal -->
  </div> 
    
        <!-- Muestra la contraseña del formulario de Registro de usuarios -->
        <script>
            // toggle robusto: acepta el botón (this) o un id (compatibilidad)
            function togglePasswordVisibility(target) {
              let btn = null;
              let input = null;

              // Si nos pasan el botón (this)
              if (typeof target === 'object' && target !== null) {
                if (target.tagName === 'BUTTON') {
                  btn = target;
                  const container = btn.closest('.password-container') || btn.parentElement;
                  input = container && container.querySelector('input[type="password"], input[type="text"]');
                } else if (target.tagName === 'INPUT') {
                  input = target;
                  btn = input.parentElement && input.parentElement.querySelector('button');
                }
              }

              // Si pasan id o name como string
              if (!input && typeof target === 'string') {
                input = document.getElementById(target) || document.querySelector(`[name="${target}"]`);
                if (input) btn = input.parentElement && input.parentElement.querySelector('button');
              }

              if (!input) return;

              const wasPassword = input.type === 'password';
              input.type = wasPassword ? 'text' : 'password';

              if (btn) btn.textContent = wasPassword ? 'Ocultar' : 'Mostrar';
              try {
                input.focus();
              } catch (e) {}
            }
        </script>

        <script>
            function togglePasswordVisibilityEdit(btn) {
              if (!btn || btn.tagName !== 'BUTTON') return;
              // buscar el formulario de edición (si existe) y asegurarnos de actuar solo dentro de él
              const formEdit = btn.closest('#editar-usuario') || document.getElementById('editar-usuario');
              // buscar input dentro del mismo .password-container que el botón
              const container = btn.closest('.password-container');
              const input = container ? container.querySelector('input[type="password"], input[type="text"]') : null;
              if (!input) return;
              const wasPassword = input.type === 'password';
              input.type = wasPassword ? 'text' : 'password';
              btn.textContent = wasPassword ? 'Ocultar' : 'Mostrar';
              try {
                input.focus();
              } catch (e) {}
            }
        </script>

</body>
</html>
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
     -------------------------------------- FIN ITred Spa usuarios_contenido .PHP ------------------------------------
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
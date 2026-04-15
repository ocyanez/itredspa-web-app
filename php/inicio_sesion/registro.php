<?php
/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/

/* ------------------------------------------------------------------------------------------------------------
   ------------------------------------- INICIO ITred Spa registro .PHP ---------------------------------------
   ------------------------------------------------------------------------------------------------------------ */

/* Higiene de salida: evita que CUALQUIER eco/espacio rompa headers/redirect */
if (!headers_sent()) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    ob_start();
}

    /* ------------------------
    -- INICIO CONEXION BD --
    ------------------------ */

        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
        $mysqli->set_charset("utf8mb4");
        // include auditoría global (no debe imprimir nada)
        @include_once __DIR__ . '/seguridad/log_registros.php';

    /* ---------------------
    -- FIN CONEXION BD --
    --------------------- */

session_start();

/* TITULO INCLUIR PHPMailer PARA ENVÍO DE CORREOS */

    // Carga las clases principales de PHPMailer para envío de correos
    require __DIR__ . '/utilidad/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/utilidad/PHPMailer/src/SMTP.php';
    require __DIR__ . '/utilidad/PHPMailer/src/Exception.php';

/* TITULO INCLUIR PROTECCIÓN CSRF */

    // Activa la protección CSRF para prevenir ataques de falsificación de formularios
    require_once __DIR__ . '/seguridad/csrf_middleware.php';
    csrf_protect();

/* TITULO INCLUIR FUNCIÓN DE VALIDACIÓN DEL RUT */

    // Incluye la función para validar el formato y dígito verificador del RUT chileno
    require_once __DIR__ . '/seguridad/validarRut.php';

/* TITULO IMPORTAR CLASES PHPMailer */

    // Importa las clases necesarias del espacio de nombres de PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

/* TITULO DEFINIR VISTA ACTUAL */

    // Define la vista actual como punto de retorno en caso de error
    $vista_actual = '/php/inicio_sesion/inicio_sesion.php';

/* TITULO MANEJO DEL ENVÍO DEL FORMULARIO DE REGISTRO */

    // Si el formulario fue enviado por método POST, comienza el procesamiento
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Asegura que la conexión a la base de datos use codificación UTF-8
    $mysqli->set_charset("utf8mb4");

    // Datos del formulario
    $nombre           = $_POST['nombre']           ?? '';
    $apellido         = $_POST['apellido']         ?? '';
    $username         = $_POST['username']         ?? '';
    $correo           = $_POST['correo']           ?? '';
    $password         = $_POST['password']         ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telefono         = $_POST['telefono']         ?? '';
    $direccion        = $_POST['direccion']        ?? '';
    $rol              = $_POST['rol']              ?? '';
    $cargo            = $_POST['cargo']            ?? '';
    $rut              = $_POST['rut']              ?? '';

/* TITULO DETERMINACIÓN DE LA VISTA SEGÚN EL ROL DEL USUARIO */

    // Rutas según rol vigente en sesión (no del nuevo usuario)
    $rolPagina = $_SESSION['rol'] ?? 'usuario';
    if ($rolPagina === 'superadmin')      { $vista_actual = 'superadmin/superadmin.php'; }
    elseif ($rolPagina === 'admin')       { $vista_actual = 'superadmin/admin.php'; }
    elseif ($rolPagina === 'supervisor')  { $vista_actual = 'superadmin/supervisor.php'; }
    elseif ($rolPagina === 'superusuario'){ $vista_actual = 'superadmin/superusuario.php'; }

    // Seguridad: sólo ciertos usuarios pueden asignar el rol 'admin'
    $actorRol = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';
    $rol_solicitado = strtolower(trim($rol ?? ''));
    $roles_permitidos = ['superadmin','admin','distribuidor','usuario_final','bodega'];
    if (!in_array($rol_solicitado, $roles_permitidos, true)) {
        ob_end_clean();
        header("Location: $vista_actual?error=rol_invalido", true, 303);
        exit;
    }

    if ($rol_solicitado === 'admin' && !in_array($actorRol, ['admin','superadmin'], true)) {
        // Sólo admin o superadmin pueden crear cuentas con rol 'admin'
        ob_end_clean();
        header("Location: $vista_actual?error=rol_invalido", true, 303);
        exit;
    }

/* TITULO VALIDACIÓN DEL RUT */

    // Validación RUT
    if (!validarRUT($rut)) {
        ob_end_clean();
        header("Location: $vista_actual?error=rut_invalido", true, 303);
        exit;
    }
    $rutFormateado = formatearRUT($rut);

/* TITULO HASHEO DE CONTRASEÑA */

    // Hash de contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

/* TITULO VERIFICACIÓN DE EXISTENCIA DE USUARIO */

    // Verificar existencia de username/correo/rut
    $sql_check = "SELECT COUNT(*) AS total FROM usuario WHERE username=? OR correo=? OR rut=?";
    if ($stmt_check = $mysqli->prepare($sql_check)) {
        $stmt_check->bind_param("sss", $username, $correo, $rutFormateado);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();
        $stmt_check->close();

        if (!empty($row_check['total'])) {
            ob_end_clean();
            if (in_array($rolPagina, ['superadmin','admin','supervisor','superusuario'], true)) {
                header("Location: /php/ingreso_ventas/renderizar_menu.php?pagina=usuarios&error=usuario_existente", true, 303);
            } else {
                header("Location: $vista_actual?error=usuario_existente", true, 303);
            }
            exit;
        }
    } else {
        ob_end_clean();
        header("Location: $vista_actual?error=prep_check", true, 303);
        exit;
    }

/* TITULO GENERACIÓN DE CÓDIGO DE VERIFICACIÓN */

    // Generar código y guardar registro temporal
    $verification_code = mt_rand(100000, 999999);
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['registro_temporal'] = [
        'nombre'          => $nombre,
        'apellido'        => $apellido,
        'username'        => $username,
        'correo'          => $correo,
        'hashed_password' => $hashed_password,
        'telefono'        => $telefono,
        'direccion'       => $direccion,
        'cargo'           => $cargo,
        'rol'             => $rol,
        'rut'             => $rutFormateado,
        'expira'          => time() + 15*60  // 15 minutos
    ];

/* TITULO ENVÍO DE CORREO DE VERIFICACIÓN */

    // Enviar correo con PHPMailer (sin ECHO)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.trazabilidad-segma.cl';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'trazabil@trazabilidad-segma.cl';
        $mail->Password   = 'E&0Qwa1RW7S[';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('trazabil@trazabilidad-segma.cl', 'SEGMA');
        $mail->addAddress($correo, $nombre);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Código de Verificación';

        $mail->Body = '
            <html><head><meta charset="UTF-8"></head><body>
            <h2>Código de Verificación - SEGMA</h2>
            <p>Hola '.htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8').',</p>
            <p>Tu código de verificación es: <strong>'.$verification_code.'</strong></p>
            <p>Este código es válido por 15 minutos.</p>
            <p>Saludos,<br>Equipo SEGMA</p>
            </body></html>';
        $mail->AltBody = 'Hola '.$nombre.', tu código de verificación es: '.$verification_code;

        $mail->send();

        // Redirección PRG a verificación (sin imprimir nada)
        ob_end_clean();
        header("Location: /php/inicio_sesion/seguridad/verificacion_codigo.php", true, 303);
        exit;

    } catch (Exception $e) {
        // En fallo de email, redirige con error
        ob_end_clean();
        $msg = rawurlencode('correo_fallido');
        header("Location: $vista_actual?error={$msg}", true, 303);
        exit;
    }
}

/* -------------------------------
   -- INICIO CIERRE CONEXION BD --
   ------------------------------- */

        // $mysqli->close();

/* ----------------------------
   -- FIN CIERRE CONEXION BD --
   ---------------------------- */

/* ------------------------------------------------------------------------------------------------------------
   -------------------------------------- FIN ITred Spa registro .PHP -----------------------------------------
   ------------------------------------------------------------------------------------------------------------ */

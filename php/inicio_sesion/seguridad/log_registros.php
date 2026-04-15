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
     ------------------------------------- INICIO ITred Spa log_registros.php .PHP ------------------------------
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
    // php/inicio_sesion/seguridad/log_registros.php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    date_default_timezone_set('America/Santiago');

    // El TXT queda junto al archivo, en /seguridad/log/log_registros.txt
    define('LOG_FILE_PATH', __DIR__ . '/log/log_registros.txt');
    // Clave para HMAC de integridad de logs.
    // IMPORTANTE: reemplaza este valor por una clave segura en producción.
    if (!defined('LOG_SECRET')) {
      define('LOG_SECRET', 'REPLACE_THIS_WITH_A_STRONG_RANDOM_KEY_CHANGE_ME');
    }

    /* ---------- utilidades ---------- */
    function sanitize_for_log($s) {
      if ($s === null) return '';
      return str_replace(["\r","\n"], ['\\r','\\n'], htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'));
    }
    function determine_browser($ua) {
      if (strpos($ua,'MSIE')!==false || strpos($ua,'Trident')!==false) return 'Internet Explorer';
      if (strpos($ua,'Firefox')!==false) return 'Mozilla Firefox';
      if (strpos($ua,'Chrome')!==false && strpos($ua,'Chromium')===false)  return 'Google Chrome';
      if (strpos($ua,'Opera Mini')!==false || strpos($ua,'OPR')!==false || strpos($ua,'Opera')!==false) return 'Opera';
      if (strpos($ua,'Safari')!==false && strpos($ua,'Chrome')===false)  return 'Safari';
      return 'Desconocido';
    }
    function determine_os($ua) {
      if (strpos($ua,'Windows')!==false)   return 'Windows';
      if (strpos($ua,'Macintosh')!==false) return 'Macintosh';
      if (strpos($ua,'Android')!==false)   return 'Android';
      if (strpos($ua,'iPhone')!==false)    return 'iPhone';
      if (strpos($ua,'iPad')!==false)      return 'iPad';
      if (strpos($ua,'Linux')!==false)     return 'Linux';
      return 'Desconocido';
    }
    function initialize_logger_dir() {
      $dir = dirname(LOG_FILE_PATH);
      if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    }

    /* ---------- funciones de auditoría importadas de auditor.php ---------- */
    function aud_nav($ua) {
      if (stripos($ua,'MSIE')!==false || stripos($ua,'Trident')!==false) return 'Internet Explorer';
      if (stripos($ua,'Firefox')!==false) return 'Mozilla Firefox';
      if (stripos($ua,'OPR')!==false || stripos($ua,'Opera Mini')!==false) return 'Opera';
      if (stripos($ua,'Chrome')!==false) return 'Google Chrome';
      if (stripos($ua,'Safari')!==false) return 'Safari';
      return 'Desconocido';
    }
    function aud_os($ua) {
      if (stripos($ua,'Windows')!==false)   return 'Windows';
      if (stripos($ua,'Macintosh')!==false) return 'Macintosh';
      if (stripos($ua,'Android')!==false)   return 'Android';
      if (stripos($ua,'iPhone')!==false)    return 'iPhone';
      if (stripos($ua,'iPad')!==false)      return 'iPad';
      if (stripos($ua,'Linux')!==false)     return 'Linux';
      return 'Desconocido';
    }
    function aud_json($arr) {
      if ($arr === null) return null;
      // Evita errores por UTF-8
      return json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * Registra un evento de auditoría en la base de datos (si se proporciona $mysqli) y/o
     * sirve como helper para registrar auditoría. Mantiene la firma original.
     */
    function log_event($mysqli, $accion, $entidad, $entidad_id=null, $detalles=null, $antes=null, $despues=null, $severidad='INFO') {
      if (session_status() === PHP_SESSION_NONE) session_start();

      $user_id = isset($_SESSION['id'])         ? $_SESSION['id']         : null;
      $username= isset($_SESSION['username'])   ? $_SESSION['username']   : null;
      $rol     = isset($_SESSION['rol'])        ? $_SESSION['rol']        : null;

      $ip        = $_SERVER['REMOTE_ADDR'] ?? null;
      $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';
      $navegador = aud_nav($ua);
      $so        = aud_os($ua);

      $sql = "INSERT INTO auditoria
              (fecha_hora,user_id,username,rol,ip,navegador,so,accion,entidad,entidad_id,detalles,antes,despues,severidad)
              VALUES (NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?)";
      $stmt = $mysqli->prepare($sql);
      if (!$stmt) return;

      $det = aud_json($detalles);
      $ant = aud_json($antes);
      $des = aud_json($despues);

      $stmt->bind_param(
        "issssssssisss",
        $user_id, $username, $rol, $ip, $navegador, $so,
        $accion, $entidad, $entidad_id, $det, $ant, $des, $severidad
      );
      $stmt->execute();
      $stmt->close();

      // Fallback opcional a archivo si falla la BD:
      // if (!$stmt) file_put_contents(__DIR__.'/log_fallback.txt', date('c')." $accion/$entidad $entidad_id\n", FILE_APPEND);
    }

    function aud_diff($old, $new) {
      $a = []; $b = [];
      foreach ($new as $k=>$v) {
        $ov = array_key_exists($k,$old) ? $old[$k] : null;
        if ($ov !== $v) { $a[$k] = $ov; $b[$k] = $v; }
      }
      return ['antes'=>$a, 'despues'=>$b];
    }
    /* ---------- fin funciones de auditoría ---------- */

    /* ---------- API de logging recomendada ---------- */
    /**
     * app_log('create'|'update'|'delete'|'login'|'logout'|'import'|'export'|'warn'|'error',
     *         'venta'|'cliente'|'usuario'|'respaldo'|...,
     *         'detalle legible',
     *         ['cualquier'=>'dato extra'])
     */
    function app_log($accion, $entidad, $detalle = '', array $extras = []) {
      initialize_logger_dir();
      $ip   = $_SERVER['REMOTE_ADDR']       ?? 'Desconocida';
      $ua   = $_SERVER['HTTP_USER_AGENT']   ?? '';
      $nav  = determine_browser($ua);
      $so   = determine_os($ua);
      $user = $_SESSION['username']         ?? 'SinSesion';
      $rol  = $_SESSION['rol']              ?? 'SinRol';
      $email = $_SESSION['correo']         ?? '';

      // Formato legible esperado por el equipo / proyecto externo
      $time = date('d-m-Y H:i:s');
      $accion_norm = strtolower($accion);

      if (in_array($accion_norm, ['login','inicio','inicio_sesion','iniciar','inicio de sesión','inicio de sesion'], true)) {
        // Mensaje especial para inicio de sesión (coincide con ejemplo)
        $line = sprintf(
          "%s - %s: El usuario '%s' con rol '%s' y correo '%s' ha Inicio de sesión al usuario '%s' realizando los siguientes cambios: %s. Navegador: %s, Sistema Operativo: %s",
          $time,
          $ip,
          sanitize_for_log($user),
          sanitize_for_log($rol),
          sanitize_for_log($email),
          sanitize_for_log($entidad),
          sanitize_for_log($detalle),
          $nav,
          $so
        );
      } elseif (in_array($accion_norm, ['logout','cierre de sesión','cerrar sesion','logout'], true)) {
        $line = sprintf(
          "%s - %s: El usuario '%s' con rol '%s' y correo '%s' ha cerrado sesión. Navegador: %s, Sistema Operativo: %s",
          $time,
          $ip,
          sanitize_for_log($user),
          sanitize_for_log($rol),
          sanitize_for_log($email),
          $nav,
          $so
        );
      } else {
        // Mensaje genérico para otras acciones
        $line = sprintf(
          "%s - %s: El usuario '%s' con rol '%s' y correo '%s' ha realizado la acción '%s' sobre '%s'. Detalles: %s. Navegador: %s, Sistema Operativo: %s",
          $time,
          $ip,
          sanitize_for_log($user),
          sanitize_for_log($rol),
          sanitize_for_log($email),
          sanitize_for_log($accion),
          sanitize_for_log($entidad),
          sanitize_for_log($detalle),
          $nav,
          $so
        );
      }

      if (!empty($extras)) {
        $json = json_encode($extras, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $line .= ' | data=' . $json;
      }

      // Calcular HMAC y escribir en archivo
      $hmac = hash_hmac('sha256', $line, LOG_SECRET);
      @file_put_contents(LOG_FILE_PATH, $line . ' | hmac=' . $hmac . PHP_EOL, FILE_APPEND);
    }

    /* ---------- compatibilidad con llamadas antiguas ---------- */
    function logger($action, $target_user = null, $changes = null) {
      // Compatibilidad: mappear algunos textos en español a tipos usados por app_log
      $map = [
        'Cierre de sesión' => 'logout',
        'Inicio de sesión' => 'login',
        'inicio_sesion' => 'login',
        'inicio sesion' => 'login'
      ];
      $tipo = $map[$action] ?? $action;
      // Si $changes es un array u objeto, lo serializamos en JSON legible
      if (is_array($changes) || is_object($changes)) {
        $changes = json_encode($changes, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
      }
      app_log($tipo, $target_user ?? '', $changes ?? '', ['actor_action' => $action]);
    }

    // --- Endpoint HTTP consolidado para acciones y clicks ---
    // Si se accede directamente por POST se procesan dos modos:
    // - label=...  -> registra click: app_log('click', label, ...)
    // - accion=... -> compatibilidad con llamadas anteriores (creado, eliminado, inicio_sesion, etc.)
    if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Responder JSON
        header('Content-Type: application/json; charset=utf-8');

        // Requerir sesión válida
        if (empty($_SESSION['username'])) {
          http_response_code(403);
          echo json_encode(['ok' => false, 'error' => 'No autenticado']);
          exit;
        }

        // Verificar token CSRF
        $csrf_ok = false;
        if (!empty($_POST['csrf']) && !empty($_SESSION['csrf_token'])) {
          if (hash_equals($_SESSION['csrf_token'], $_POST['csrf'])) $csrf_ok = true;
        }
        // También aceptar header X-Requested-With (pero CSRF es preferible)
        if (!$csrf_ok) {
          http_response_code(403);
          echo json_encode(['ok' => false, 'error' => 'CSRF inválido']);
          exit;
        }

        $label = $_POST['label'] ?? null;
        if ($label) {
          $label = sanitize_for_log($label);
          // Mensaje legible: usamos app_log para mantener formato consistente
          app_log('click', $label, "El usuario '" . ($_SESSION['username'] ?? 'SinSesion') . "' ha hecho click en " . $label);
          echo json_encode(['ok' => true]);
          exit;
        }

        $accion = $_POST['accion'] ?? null;
        if ($accion) {
          $accion = sanitize_for_log($accion);
          $acciones_validas = ['creado', 'eliminado', 'Cierre de sesión', 'actualizado', 'inicio_sesion', 'inicio sesion', 'login'];
          if (!in_array($accion, $acciones_validas, true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Acción no válida.']);
            exit;
          }
          $target_user = $_POST['target_user'] ?? null;
          $changes = $_POST['changes'] ?? null;
          logger($accion, $target_user, $changes);
          echo json_encode(['ok' => true]);
          exit;
        }

        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Faltan parámetros']);
        exit;
      }
    }
    ?>
    <script src="/js/ingreso_ventas/renderizar_menu.js?v=<?= time() ?>"></script>

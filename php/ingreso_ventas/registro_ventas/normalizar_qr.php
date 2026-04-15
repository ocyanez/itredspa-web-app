<?php
/*
 Sitio Web Creado por ITred Spa.
 Dirección: Guido Reni #4190
 Pedro Aguirre Cerda - Santiago - Chile
 contacto@itred.cl o itred.spa@gmail.com
 https://www.itred.cl
 Creado, Programado y Diseñado por ITred Spa.
 BPPJ
*/

/* -------------------------------------------------------------------------------------------------------------
   ------------------------------------- INICIO ITred Spa normalizar_qr .PHP -----------------------------------
   ------------------------------------------------------------------------------------------------------------- */

//-- ---------------------
//   -- FIN CONEXION BD --
//   --------------------- 

// establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
$mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");

//-- ---------------------
//   -- FIN CONEXION BD --
//   --------------------- 


$titulo = "Normalizar QR";

// establecer política de errores y logging para depuración
error_reporting(E_ALL);
ini_set('display_errors', '0');
$__normalizar_log = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'normalizar_qr_errors.log';
ini_set('error_log', $__normalizar_log);

// convertir errores a excepciones 
set_error_handler(function($errno, $errstr, $errfile, $errline){
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// comprobar conexión a MySQL y responder si falla
if ($mysqli->connect_errno) {
    error_log("normalizar_qr.php: MySQL connection failed: " . $mysqli->connect_error);
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(500);
    echo "<script>if(parent&&parent.phpRespuesta){parent.phpRespuesta('error',{msg:'db_connect_failed'});}</script>";
    exit;
}

        //  para perfiles (respuesta). Se ejecutan antes del HTML.
        if (php_sapi_name() !== 'cli') {
            if (session_status() === PHP_SESSION_NONE) session_start();
        }

        // esencial para el funcionamiento de la página
            if (isset($_REQUEST['action'])) {
            try {
                header('Content-Type: text/html; charset=utf-8');
                // esto sirve para limpiar la salida 
                if (function_exists('ob_start')) { @ob_start(); @ob_clean(); }
                $action = $_REQUEST['action']; // obtiene el valor de action

                // devuelve info del log y últimas líneas para diagnóstico
                if ($action === 'debug_info') {
                    // solo usuarios autenticados pueden ver el log
                    if (!isset($_SESSION['correo']) && $userId === null) {
                        http_response_code(403);
                        echo "<script>if(parent&&parent.phpRespuesta){parent.phpRespuesta('error',{msg:'forbidden'});}</script>";
                        exit;
                    }
                    $logPath = $__normalizar_log ?? null;// esto se usa para obtener el log 
                    $tail = null; // esto se usa para obtener las últimas líneas del log
                    if ($logPath && file_exists($logPath)) {// si el log existe
                        $tail = trim(shell_exec('tail -n 200 ' . escapeshellarg($logPath) . ' 2>/dev/null')); // obtiene las últimas líneas del log
                        if ($tail === null) {// si no se pudo obtener el log 
                            
                            $content = @file_get_contents($logPath);
                            if ($content !== false) $tail = implode("\n", array_slice(explode("\n", $content), -200));
                        }
                    }
                    $tailEscaped = addslashes($tail ?? '');// esto se usa para que las comillas no interfieran con el JavaScript
                    echo "<script>if(parent&&parent.phpRespuesta){parent.phpRespuesta('success',{log_path:'".addslashes($logPath)."',tail:'$tailEscaped'});}</script>";
                    exit;
                }

            //  para leer datos POST
            function readPostData() {
                return $_POST ?: [];
            }

            //  comprobar si una columna existe en la tabla qr_profiles
            function columnExists($mysqli, $col) {
                $col = $mysqli->real_escape_string($col);
                $res = $mysqli->query("SHOW COLUMNS FROM qr_profiles LIKE '" . $col . "'");
                if ($res === false) return false;
                return $res->num_rows > 0;
            }
            // Genera un nombre único para el perfil basado en timestamp y caracteres aleatorios
            function generar_nombre_perfil_unico($mysqli) {
                $intentos = 0;
                $max_intentos = 10;
                
                do {
                    // Formato: PERFIL_YYYYMMDD_HHMMSS_XXXX (donde XXXX son 4 caracteres aleatorios)
                    $fecha = date('Ymd_His');
                    $aleatorio = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
                    $nombre = "PERFIL_{$fecha}_{$aleatorio}";
                    
                    // Verificar que no exista
                    $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM qr_profiles WHERE name = ?");
                    $stmt->bind_param('s', $nombre);
                    $stmt->execute();
                    $resultado = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    if ($resultado['total'] == 0) {
                        return $nombre; // Nombre único encontrado
                    }
                    
                    $intentos++;
                    usleep(1000); // Pequeña pausa para cambiar el timestamp
                    
                } while ($intentos < $max_intentos);
                
                // Fallback con más aleatoriedad
                return "PERFIL_" . date('Ymd_His') . "_" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            }


            // Convertir array PHP a formato JavaScript 
            function arrayToJS($arr) { 
                if (empty($arr)) return '[]';// esta linea es para que no devuelva null
                if (!is_array($arr)) return "'" . addslashes($arr) . "'";// esto es para que el array no se convierta en string 
                $isAssoc = array_keys($arr) !== range(0, count($arr) - 1); // esto sirve para que la clave sea un string
                
                if ($isAssoc) {// si el array es asociativo es decir que tiene claves
                    $parts = []; //crea un array para guardar las partes del array
                    foreach ($arr as $k => $v) { //recorre el array
                        $key = addslashes($k); //escapa las comillas o caracteres especiales
                        if (is_array($v)) {
                            $parts[] = "'$key': " . arrayToJS($v); //si el valor es un array lo convierte a JavaScript
                        } elseif (is_null($v)) {  // si el valor es null lo convierte a JavaScript
                            $parts[] = "'$key': null";
                        } elseif (is_numeric($v)) { // si el valor es un numero lo convierte a JavaScript
                            $parts[] = "'$key': $v";
                        } else {
                            $val = addslashes($v ?? ''); //escapa las comillas o caracteres especiales
                            $parts[] = "'$key': '$val'";
                        }
                    }
                    return '{' . implode(',', $parts) . '}'; //devuelve el array convertido a JavaScript    
                } else { // si el array no es asociativo es decir que no tiene claves
                    $parts = [];
                    foreach ($arr as $v) {
                        if (is_array($v)) {//si el valor es un array lo convierte a JavaScript
                            $parts[] = arrayToJS($v); 
                        } elseif (is_null($v)) {// si el valor es null lo convierte a JavaScript
                            $parts[] = 'null';
                        } elseif (is_numeric($v)) {// si el valor es un numero lo convierte a JavaScript
                            $parts[] = $v;
                        } else {
                            $val = addslashes($v ?? ''); //escapa las comillas o caracteres especiales
                            $parts[] = "'$val'";
                        }
                    }
                    return '[' . implode(',', $parts) . ']'; //devuelve el array convertido a JavaScript
                }
            }

            // esta funcion es para responder a lo que se le envio anteriormente para que pueda mostrar un mensaje o datos
            function responderJS($resultado, $datos = []) {
                $datosJS = arrayToJS($datos);
                echo "<script>if(parent&&parent.phpRespuesta){parent.phpRespuesta('$resultado',$datosJS);}</script>"; // envia el resultado y los datos al iframe
                exit;
            }

            // obtener user id si existe en session
            $userId = isset($_SESSION['id']) ? intval($_SESSION['id']) : (isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null);

            if ($action === 'save_profile' && ($_SERVER['REQUEST_METHOD'] === 'POST')) {
                // . Leer y validar datos básicos
                $data = readPostData();
                if (!isset($data['name']) || !isset($data['payload'])) {
                    http_response_code(400); 
                    responderJS('error', ['msg' => 'invalid_input']);
                }

                //  Preparar variables (Limpieza)
                // Usamos el $userId 
                $name       = substr(trim($data['name']), 0, 150);
                $payload    = $data['payload'];
                
                // Si existen _order, tokenMap o signature, los guardamos como string asi se llaman estos campos en la base de datos
                $ord        = !empty($data['_order']) ? $data['_order'] : null;
                $token_map  = !empty($data['_tokenMap']) ? $data['_tokenMap'] : null;
                $signature  = !empty($data['signature']) ? substr($data['signature'], 0, 64) : null;

                //   Consulta unica
                // esto debe coincidir con la tabla: mete todo de una vez.
                $sql = "INSERT INTO qr_profiles 
                        (user_id, name, payload, signature, ord, token_map, times_used, last_used, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW(), NOW()) 
                        ON DUPLICATE KEY UPDATE 
                            payload = VALUES(payload),
                            signature = VALUES(signature),
                            ord = VALUES(ord),
                            token_map = VALUES(token_map),
                            times_used = times_used + 1, 
                            last_used = NOW(),
                            updated_at = NOW()";

                $stmt = $mysqli->prepare($sql);

                if ($stmt) {
                    // Tipos: i=integer, s=string. (numeros y letras)
                    // user_id, name, payload, signature, ord, token_map -> Total 6 variables
                    $stmt->bind_param('isssss', $userId, $name, $payload, $signature, $ord, $token_map);
                    
                    if ($stmt->execute()) {
                        responderJS('success', ['mode' => 'exact_match']);
                    } else {
                        // Si falla, te avisa el error real en lugar de intentar borrar datos
                        http_response_code(500);
                        responderJS('error', ['msg' => $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    // Error en la escritura de la consulta SQL
                    http_response_code(500);
                    responderJS('error', ['msg' => $mysqli->error]);
                }
                
                exit;
            }
            if ($action === 'lista_de_perfiles') {
                // Listar perfiles globales (user_id IS NULL) O del usuario actual
                if ($userId !== null) {
                    $sql = "SELECT id, name, signature, times_used, last_used, created_at, updated_at 
                            FROM qr_profiles 
                            WHERE user_id IS NULL OR user_id = ? 
                            ORDER BY times_used DESC, updated_at DESC";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param('i', $userId);
                } else {
                    // Usuario no autenticado: solo perfiles globales
                    $sql = "SELECT id, name, signature, times_used, last_used, created_at, updated_at 
                            FROM qr_profiles 
                            WHERE user_id IS NULL 
                            ORDER BY times_used DESC, updated_at DESC";
                    $stmt = $mysqli->prepare($sql);
                }
                
                if (!$stmt) { 
                    http_response_code(500); 
                    responderJS('error', ['msg' => $mysqli->error]); 
                }
                
                $stmt->execute();
                $res = $stmt->get_result();
                $rows = [];
                while ($r = $res->fetch_assoc()) {
                    $rows[] = $r;
                }
                $stmt->close();
                
                responderJS('success', ['profiles' => $rows]); 
            }

            if ($action === 'obtener_el_perfil') {
                // Puede buscar por 'name' o por 'signature'
                $name = isset($_GET['name']) ? substr(trim($_GET['name']), 0, 150) : null;
                $signature = isset($_GET['signature']) ? substr(trim($_GET['signature']), 0, 64) : null;
                
                if (!$name && !$signature) {
                    http_response_code(400);
                    responderJS('error', ['msg' => 'missing_name_or_signature']);
                }
                
                $selectCols = ['id', 'user_id', 'name', 'payload', 'ord', 'token_map', 'signature', 'times_used', 'last_used', 'created_at', 'updated_at'];
                
                if ($signature) {
                    // Buscar por firma (perfiles globales o del usuario)
                    if ($userId !== null) {
                        $sql = "SELECT " . implode(', ', $selectCols) . " FROM qr_profiles WHERE signature = ? AND (user_id IS NULL OR user_id = ?) LIMIT 1";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param('si', $signature, $userId);
                    } else {
                        $sql = "SELECT " . implode(', ', $selectCols) . " FROM qr_profiles WHERE signature = ? AND user_id IS NULL LIMIT 1";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param('s', $signature);
                    }
                } else {
                    // Buscar por nombre (perfiles globales o del usuario)
                    if ($userId !== null) {
                        $sql = "SELECT " . implode(', ', $selectCols) . " FROM qr_profiles WHERE name = ? AND (user_id IS NULL OR user_id = ?) LIMIT 1";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param('si', $name, $userId);
                    } else {
                        $sql = "SELECT " . implode(', ', $selectCols) . " FROM qr_profiles WHERE name = ? AND user_id IS NULL LIMIT 1";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param('s', $name);
                    }
                }
                
                if (!$stmt) { 
                    http_response_code(500); 
                    responderJS('error', ['msg' => $mysqli->error]); 
                }
                
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $stmt->close();
                
                if (!$row) { 
                    http_response_code(404); 
                    responderJS('error', ['msg' => 'not_found']); 
                }
                
                responderJS('success', ['profile' => $row]); 
            }

            if ($action === 'delete_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = readPostData();
                if (!isset($data['name'])) { http_response_code(400); responderJS('error', ['msg' => 'missing_name']); }
                $name = substr(trim($data['name']),0,150);
                if ($userId === null) { http_response_code(403); responderJS('error', ['msg' => 'not_authenticated']); }
                $sql = "DELETE FROM qr_profiles WHERE user_id = ? AND name = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('is', $userId, $name);
                if (!$stmt) { http_response_code(500); responderJS('error', ['msg' => $mysqli->error]); }
                $stmt->execute();
                responderJS('success', []); 
            }
            // Guardar el producto escaneado y normalizado con los datos del cliente
            // guarda el perfil automáticamente de forma global
            if ($action === 'save_normalized_data' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = readPostData();
                
                if (!isset($data['sku'])) {
                    http_response_code(400); 
                    responderJS('error', ['msg' => 'missing_sku']);
                }

                // Variables del Producto
                $sku       = $data['sku'] ?? '';
                $producto  = $data['producto'] ?? '';
                $cantidad  = intval($data['cantidad'] ?? 0);
                $lote      = $data['lote'] ?? '';     
                
                // Fechas
                $fechaFab  = (!empty($data['fechaFab']) && $data['fechaFab'] !== 'undefined') ? $data['fechaFab'] : null;
                $fechaVenc = (!empty($data['fechaVenc']) && $data['fechaVenc'] !== 'undefined') ? $data['fechaVenc'] : null;
                
                $n_serie_ini = intval($data['serieIni'] ?? 0);
                $n_serie_fin = intval($data['serieFin'] ?? 0);

                // Corrección del RUT
                $rutRaw = $data['rut'] ?? '';
                $rutLimpio = str_replace(['.', ' '], '', $rutRaw);
                $rut = !empty($rutLimpio) ? $rutLimpio : NULL;

                $numero_fact = !empty($data['numero_fact']) ? $data['numero_fact'] : null;
                $fecha_despacho = !empty($data['fecha_despacho']) ? $data['fecha_despacho'] : date('Y-m-d H:i:s');

                // guardar tabla venta
                $sql_venta = "INSERT INTO venta 
                        (rut, numero_fact, fecha_despacho, sku, producto, cantidad, lote, fecha_fabricacion, fecha_vencimiento, n_serie_ini, n_serie_fin) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_venta = $mysqli->prepare($sql_venta);
                $id_venta = null;
                $venta_guardada = false;
                
                if ($stmt_venta) {
                    $stmt_venta->bind_param('sssssisssii', 
                        $rut, 
                        $numero_fact, 
                        $fecha_despacho, 
                        $sku, 
                        $producto, 
                        $cantidad, 
                        $lote, 
                        $fechaFab, 
                        $fechaVenc, 
                        $n_serie_ini, 
                        $n_serie_fin
                    );
                    
                    if ($stmt_venta->execute()) {
                        $id_venta = $stmt_venta->insert_id;
                        $venta_guardada = true;
                    }
                    $stmt_venta->close();
                }
                
                if (!$venta_guardada) {
                    http_response_code(500);
                    responderJS('error', ['msg' => 'error_guardando_venta']);
                    exit;
                }

                //  guardar perfil automaticamente 
                $perfil_guardado = false;
                $nombre_perfil = null;
                
                // Solo guardar perfil si viene la firma (signature) - indica que hay un mapeo de tokens
                $signature = !empty($data['signature']) ? substr($data['signature'], 0, 64) : null;
                
                if ($signature) {
                    // Verificar si ya existe un perfil con esta firma (evitar duplicados)
                    $stmt_check = $mysqli->prepare("SELECT id, name FROM qr_profiles WHERE signature = ? LIMIT 1");
                    $stmt_check->bind_param('s', $signature);
                    $stmt_check->execute();
                    $perfil_existente = $stmt_check->get_result()->fetch_assoc();
                    $stmt_check->close();
                    
                    if ($perfil_existente) {
                        // Ya existe un perfil con esta firma, solo actualizar times_used
                        $stmt_update = $mysqli->prepare("UPDATE qr_profiles SET times_used = times_used + 1, last_used = NOW() WHERE id = ?");
                        $stmt_update->bind_param('i', $perfil_existente['id']);
                        $stmt_update->execute();
                        $stmt_update->close();
                        
                        $nombre_perfil = $perfil_existente['name'];
                        $perfil_guardado = true;
                    } else {
                        // No existe, crear perfil nuevo con nombre aleatorio
                        $nombre_perfil = generar_nombre_perfil_unico($mysqli);
                        
                        $payload   = !empty($data['payload']) ? $data['payload'] : '';
                        $ord       = !empty($data['_order']) ? $data['_order'] : null;
                        $token_map = !empty($data['_tokenMap']) ? $data['_tokenMap'] : null;
                        
                        // user_id = NULL para que sea global
                        $sql_perfil = "INSERT INTO qr_profiles 
                                (user_id, name, payload, signature, ord, token_map, times_used, last_used, created_at, updated_at) 
                                VALUES (NULL, ?, ?, ?, ?, ?, 1, NOW(), NOW(), NOW())";
                        
                        $stmt_perfil = $mysqli->prepare($sql_perfil);
                        
                        if ($stmt_perfil) {
                            $stmt_perfil->bind_param('sssss', $nombre_perfil, $payload, $signature, $ord, $token_map);
                            if ($stmt_perfil->execute()) {
                                $perfil_guardado = true;
                            }
                            $stmt_perfil->close();
                        }
                    }
                }

                // Responder con éxito
                responderJS('success', [
                    'id' => $id_venta,
                    'perfil_guardado' => $perfil_guardado ? 'si' : 'no',
                    'nombre_perfil' => $nombre_perfil
                ]);
                exit;
            }

            //  Verificar si existe perfil para una firma 
            if ($action === 'check_profile_signature') {
                // Limpiamos cualquier salida previa
                if (function_exists('ob_clean')) { @ob_clean(); }
                
                // Recibimos la firma que envia ventas.js
                $sig = isset($_GET['signature']) ? $_GET['signature'] : '';
                
                if ($sig) {
                    // Buscamos en la base de datos si esa firma existe
                    // Seleccionamos el 'token_map' que es la instrucción de ordenamiento
                    $sql = "SELECT token_map FROM qr_profiles WHERE signature = ? LIMIT 1";
                    $stmt = $mysqli->prepare($sql);
                    $stmt->bind_param('s', $sig);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    
                    if ($fila = $res->fetch_assoc()) {
                        // Si existe, imprimimos "encontrado" y el mapa separado por un pipe
                        // Ejemplo: encontrado|0:sku|1:producto|2:cantidad
                        echo "ENCONTRADO|" . $fila['token_map'];
                    } else {
                        echo "NO_EXISTE";
                    }
                    $stmt->close();
                } else {
                    echo "ERROR_FIRMA";
                }
                exit; // Importante: Terminamos aquí para no cargar el HTML de abajo
            }
                // acción no reconocida
                http_response_code(400); responderJS('error', ['msg' => 'unknown_action']);
            } catch (Throwable $e) {
                // registrar detalle y devolver un error genérico
                error_log('normalizar_qr.php exception: ' . $e->getMessage() . " in " . $e->getFile() . ':' . $e->getLine());
                header('Content-Type: text/html; charset=utf-8');
                http_response_code(500);
                echo "<script>if(parent&&parent.phpRespuesta){parent.phpRespuesta('error',{msg:'exception_logged'});}</script>";
                exit;
            }
        }
    ?>
     
    
<!-- TITULO HTML -->
 
<!DOCTYPE html>
<html lang="us">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ingreso_ventas</title>
    <!-- llama al archivo css que contiene los estilos de la página -->
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/normalizar_qr.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
</head>
<!-- TITULO BODY -->
<body class="ingreso-ventas">

    <!-- CONTENEDOR PRINCIPAL PARA ESTILOS DE PLANTILLA -->
    <div class="contenedor-principal">
    <!-- main content (izquierda) -->
    <div class="main-content">

    <!-- TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN -->

    <?php
        $mysqli->set_charset("utf8");
        if (session_status() === PHP_SESSION_NONE) {
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

    <!-- TITULO CABECERA -->

        <!-- centrar contiene el título principal y enlace hacia la página de ventas -->
        <h1>Normalizar Códigos</h1>
                
        <!-- contiene la cabecera de la pagina, que incluye el logotipo y el título principal -->


    <!-- TITULO ESCANEOS -->

        <!-- Llama a la función que procesa el contenido del código QR -->
        <!-- sin codigo-->

        <!-- div para el botón para activar la cámara del dispositivo y escanear el código QR -->
        <div id="boton_escaner">
            <div class="grupo-escaner">
                <!-- botón para iniciar el escáner de QR con cámara -->
                <button id="btnEscanear" class="centrar" type="button" onclick="iniciarScanner()">ESCANEAR CON CAMARA</button>
                <!-- botón para iniciar el escáner de QR con pistola lectora -->
                <button id="btnPistola" class="centrar" type="button" onclick="iniciarPistola()">ESCANEAR CON PISTOLA</button>
            </div>
            <div class="grupo-detener">
                <!-- botón para detener el escáner -->
                <button id="btnDetener" class="centrar" type="button" onclick="detenerScanner()">DETENER</button>
            </div>
        </div>

        <!-- Sección de acciones para datos normalizados (se muestra después de escanear) -->
        <div id="seccion_acciones" style="display: none; margin-top: 20px;">
            <!-- Botón para guardar los datos normalizados en la base de datos -->
            <div class="grupo-acciones" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                <button id="btnGuardarNormalizado" class="centrar" type="button">GUARDAR NORMALIZADO</button>
            </div>
            
            <!-- ELIMINADO: Sección de gestión de perfiles -->
            <!-- Los perfiles ahora se guardan automáticamente al guardar el normalizado -->
        </div>

    <!-- TITULO LECTOR QR -->

        <!-- contenedor donde se renderiza el lector QR, inicialmente oculto -->
         <!-- la clase del lectorQR, no quitar las dimensiones del estilo para lograr el scanner correctamente -->
        <div id="lectorQR" style="width: 400px; height: 300px; display: none;"></div> 
        <!-- Div para mostrar mensajes de feedback -->
        <div id="mensaje_feedback" style="display: none;"></div>
        
        <!-- Campo oculto para pistola QR -->
        <input type="hidden" id="inputQR" autocomplete="off" />
        
        <input type="hidden" id="data_rut_cliente" value="<?= htmlspecialchars($_REQUEST['rut_cliente'] ?? '') ?>">
        
        <input type="hidden" id="data_num_factura" value="<?= htmlspecialchars($_REQUEST['num_factura'] ?? '') ?>">
        
        <input type="hidden" id="data_fecha_despacho" value="<?= htmlspecialchars($_REQUEST['fecha_despacho'] ?? '') ?>">
        
        <input type="hidden" id="data_productos_previos" value="<?= htmlspecialchars($_REQUEST['productos_previos'] ?? '') ?>">
        
        <!-- Iframe oculto para comunicacion con servidor este es el cartero que nos dice que actualizar de la pagina para no usar ajax -->
        <iframe name="commIframe" id="commIframe" style="display:none;"></iframe>
    <!-- TITULO TABLA DATOS CRUDOS DEL QR -->
        <div class="contenedor_tabla">
            <table id="tablaDatosCrudos" style="display: none;">
                <thead>
                    <tr id="headerCrudos">
                        <!-- Los encabezados se generan dinámicamente -->
                    </tr>
                </thead>
                <tbody id="bodyCrudos">
                    <!-- Los datos crudos se insertan aquí -->
                </tbody>
            </table>
        </div>
    <!-- TITULO INFORMACION TABLA DE PRODUCTOS -->
        <div class=contenedor_tabla>
            <table id="tablaProductosHeader" style="display: none;">
                <!-- Encabezado de la tabla que define las columnas de la tabla productos -->
                <thead>
                    <!-- etiqueta de inicio de fila -->
                    <tr>
                        <!-- columna de nombre SKU en la fila tipo cabecera -->
                        <th>SKU</th>
                        <!-- columna de nombre PRODUCTO en la fila tipo cabecera -->
                        <th>PRODUCTO</th>
                        <!-- columna de nombre CANTIDAD en la fila tipo cabecera -->
                        <th>CANTIDAD</th>
                        <!-- columna de nombre LOTE en la fila tipo cabecera -->
                        <th>LOTE</th>
                        <!-- columna de nombre FECHA DE FABRICACION en la fila tipo cabecera -->
                        <th>FECHA DE FABRICACION</th>
                        <!-- columna de nombre FECHA DE VENCIMIENTO en la fila tipo cabecera -->
                        <th>FECHA DE VENCIMIENTO</th>
                        <!-- columna de nombre SERIE DE INICIO en la fila tipo cabecera -->
                        <th>SERIE DE INICIO</th>
                        <!-- columna de nombre SERIE DE TERMINO en la fila tipo cabecera -->
                        <th>SERIE DE TERMINO</th>
                    </tr>
                </thead>

            <!-- TITULO CUERPO DINAMICO DE TABLA -->

                <!-- se añaden productos a la tabla después de leer el QR mediante JavaScript con la id tablaProductos -->
                <tbody id="tablaProductos">

                <!-- en esta parte se añaden de forma dinámica las filas de productos al momento de escanear el QR -->
                </tbody>
            </table>
        </div>


    </div> <!-- FIN main-content -->

    <!-- TITULO ARCHIVO JS -->
     
        <!-- llama al archivo javascript que contiene funciones necesarias para el escaneo y guardado -->
        <script src="/js/ingreso_ventas/registro_ventas/normalizar_qr.js?v=<?= time() ?>"></script>
        <!-- invoca el lector de códigos QR desde html5-qrcode -->
        <script src="/js/ingreso_ventas/registro_ventas/html5-qrcode.min.js?v=<?= time() ?>" type="text/javascript"></script>

    </div> <!-- FIN CONTENEDOR PRINCIPAL -->

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
     -------------------------------------- FIN ITred Spa normalizar_qr .PHP ------------------------------------
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
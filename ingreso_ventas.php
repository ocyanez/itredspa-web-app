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
     -------------------------------------- FIN ITred Spa ingreso_ventas .PHP -----------------------------------
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


<!-- TITULO HTML -->

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <!-- Codificación de caracteres en UTF-8 para soportar acentos y caracteres especiales -->
        <meta charset="UTF-8" />
        <!-- Configura el viewport para diseño responsive -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <!-- Evita que el navegador almacene en caché -->
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <!-- Refuerzo para evitar caché en navegadores antiguos -->
        <meta http-equiv="Pragma" content="no-cache" />
        <!-- Indica que el contenido ya expiró -->
        <meta http-equiv="Expires" content="0" />
        <!-- Título que aparece en la pestaña del navegador -->
        <title>SISTEMA DE TRAZABILIDAD DE SEGMA</title>
        <!-- carga el archivo de estilos CSS para la página de el inicio de sesion principal -->
        <link rel="stylesheet" href="css/ingreso_ventas.css?v=<?= time() ?>">
        <!-- Íconos para dispositivos móviles y navegadores -->
        <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
        <!-- Ícono clásico para navegadores -->
        <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
        <!-- Archivo de configuración para apps web progresivas -->
        <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    </head>
    
    <!-- TITULO BODY -->    

        <!-- Inicio del cuerpo del documento con atributo personalizado para JS -->
        <body data-page="ingreso-ventas">

    <!-- TITULO VARIABLES -->

            <?php
                // Establece codificación UTF-8 para la conexión
                $mysqli->set_charset("utf8");
                
                // Consulta para obtener el logo personalizado desde la base de datos
                $res = $mysqli->query("SELECT logo FROM personalizacion WHERE id=1");
                $config = $res ? $res->fetch_assoc() : [];
                $rawLogo = !empty($config['logo']) ? $config['logo'] : '';
                
                // Ruta raíz del servidor
                $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                
                // Logo por defecto si no hay personalización
                $logoUrl = '/imagenes/ingreso_venta_img1.png';

                // Si hay logo personalizado, se valida su existencia en distintas rutas
                if (!empty($rawLogo)) {
                    if (strpos($rawLogo, '/') === 0) {
                        $full = $docRoot . $rawLogo;
                        if (file_exists($full) && is_file($full)) {
                            // Se agrega versión según fecha de modificación
                            $logoUrl = $rawLogo . '?v=' . filemtime($full);
                        } else {
                            $logoUrl = $rawLogo;
                        }
                    } else {
                        // Se prueban rutas comunes donde podría estar el logo
                        $c1 = '/imagenes/ingreso_ventas/registro_ventas/' . $rawLogo;
                        $c2 = '/imagenes/ingreso_ventas/' . $rawLogo;
                        $c3 = '/imagenes/' . $rawLogo;
                        foreach ([$c1,$c2,$c3] as $cand) {
                            $full = $docRoot . $cand;
                            if (file_exists($full) && is_file($full)) { $logoUrl = $cand . '?v=' . filemtime($full); break; }
                        }
                    }
                }

            ?>

    <!-- TITULO CONTENIDO PRINCIPAL -->


        <!-- Contenedor principal que agrupa el logo y el formulario -->
        <div class="login-container">

    <!-- TITULO LOGO -->
            <!-- Logo SEGMA -->
            <div class="logo-section">

                <!-- Contenedor del logo y su versión -->
                <div class="logo-segma">

                    <!-- Imagen del logo -->
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="SEGMA Logo" class="logo-image">
                    
                    <!-- Texto de versión debajo del logo -->
                    <h2 class="logo-version"> Versión 2 </h2>
                </div>
            </div> 
        
    <!-- TITULO FORMULARIO -->
            <!-- Formulario de Login -->
            <div class="login-section">
                <!-- Título del formulario -->
                <h2 class="login-title">Iniciar sesión</h2>
                
                <!-- Formulario que envía datos al backend -->
                <form action="php/inicio_sesion/login.php" method="POST" class="login-form">
                    <?php
                    // Genera token CSRF si no existe para proteger el formulario
                    session_start();
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
                    }
                    ?>
                    <!-- Campo oculto con token CSRF -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <!-- Campo de correo electrónico -->
                    <div class="input-group">
                        <input type="email" name="correo" placeholder="Email" required autocomplete="email" class="login-input">
                    </div>
                    
                    <!-- Campo de contraseña -->
                    <div class="input-group">
                        <input type="password" name="password_login" placeholder="Password" required autocomplete="current-password" class="login-input">
                    </div>

                    <!-- Botón para enviar el formulario -->
                    <button type="submit" class="login-button">
                        Iniciar Sesión
                    </button>

                    <!-- Sección para registro de nuevos usuarios -->
                    <div class="register-section">
                        <p class="register-text">¿No tienes una cuenta?</p>
                        <a href="php/inicio_sesion/formulario_registro.php" class="register-link">
                            <span class="register-icon">👤</span>
                            Regístrate aquí
                            <span class="register-arrow">→</span>
                        </a>
                    </div>

                    <!-- Mensaje de error si hay problemas al iniciar sesión -->
                    <?php if (isset($_GET['error'])): ?>
                        <div class="error-message">
                            <?php 
                            switch($_GET['error']) {
                                case 'invalid_credentials':
                                    echo 'Credenciales inválidas';
                                    break;
                                case 'csrf_error':
                                    echo 'Error de seguridad. Inténtalo de nuevo.';
                                    break;
                                default:
                                    echo 'Error al iniciar sesión';
                                }
                            ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    <!-- TITULO ARCHIVO JS -->

        <!-- Enlace al archivo JavaScript para la funcionalidad del inicio de sesión -->
        <script src="js/ingreso_ventas.js"></script>

    </body>
</html>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <?php 
        // Cierra la conexión a la base de datos
        $mysqli->close();
    ?> 

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa ingreso_ventas .PHP -----------------------------------
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

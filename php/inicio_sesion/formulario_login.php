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
     ------------------------------------- INICIO ITred Spa formulario_login .PHP -------------------------------
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/inicio_sesion/formulario_login.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    <title>Formulario</title>
</head>

<body>

            <?php
        // TITULO INICIALIZACIÓN Y PROTECCIÓN CSRF

            include_once 'seguridad/csrf_middleware.php';

        // TITULO REDIRECCIÓN SEGÚN EL ROL DEL USUARIO

            $mysqli->set_charset("utf8");
            // Verifica si la sesión tiene variables de correo (usuario autenticado)
            if (isset($_SESSION['correo'])) {
                header("Location: /php/ingreso_ventas/renderizar_menu.php");
                exit();
            }

        // TITULO GENERACIÓN DEL TOKEN CSRF

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            ?>
            <div class="login-container">
        <!-- TITULO LOGO -->
         
                <?php
                    // Mostrar logo personalizado si existe
                    $res = $mysqli->query("SELECT logo FROM personalizacion WHERE id=1");
                    $cfg = $res ? $res->fetch_assoc() : [];
                    $rawLogo = $cfg['logo'] ?? '';
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                    $logoUrl = '/imagenes/ingreso_venta_img1.png';
                    if (!empty($rawLogo)) {
                        if (strpos($rawLogo, '/') === 0) {
                            $full = $docRoot . $rawLogo;
                            if (file_exists($full) && is_file($full)) {
                                $logoUrl = $rawLogo . '?v=' . filemtime($full);
                            } else {
                                $logoUrl = $rawLogo;
                            }
                        } else {
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
                <div class="logo-section">
                    <div class="logo-segma">
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="SEGMA Logo" class="logo-image">
                    </div>
                </div>

        <!-- TITULO FORMULARIO DE INICIO DE SESIÓN -->


                    <div class="login-section">
                        <h2 class="login-title">Iniciar sesión</h2>

                        <form id="loginForm" action="/php/inicio_sesion/login.php" method="POST">
                            <div id="loginError" class="error_mensaje" style="display:block;">
                                Correo electrónico o contraseña incorrecta. Por favor, intenta nuevamente.
                            </div>

                            <div class="input-group">
                                <input type="email" name="correo" placeholder="Correo electrónico" required autocomplete="email" class="login-input">
                            </div>

                            <div class="input-group">
                                <input type="password" name="password_login" placeholder="Contraseña" required autocomplete="current-password" class="login-input">
                            </div>  

                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <?php if (!empty($error_mensaje)): ?>
                                <div class="error_mensaje"><?php echo $error_mensaje; ?></div>
                            <?php endif; ?>

                            <button type="submit" class="login-button">Iniciar sesión</button>
                    
                        </form>

                        <!-- Enlace para ir a registro -->
                        <div class="register-section">
                            <p class="register-text" id="linkToRegistro" style="display: block;">¿No tienes una cuenta?</p>
                                <a href="/php/inicio_sesion/formulario_registro.php" class="register-link">Regístrate aquí
                                <span class="register-arrow">→</span>
                                </a>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>

        <!-- TITULO ARCHIVO JS -->

            <script src="/js/inicio_sesion/formulario_login.js?v=<?= time() ?>"></script>

</body>
</html>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

<!-- <?php 
    // $mysqli->close();
?> -->

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa formulario_login .PHP ---------------------------------
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

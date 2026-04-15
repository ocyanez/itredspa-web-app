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
     ------------------------------------- INICIO ITred Spa formulario_registro .PHP ----------------------------
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
    <!-- Vinculación del archivo CSS -->
    <link rel="stylesheet" href="/css/inicio_sesion/formulario_registro.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    <title>Página de Registro</title>
</head>

        <!-- TITULO BODY -->

    <body>

            <?php
        // TITULO INICIALIZACIÓN Y PROTECCIÓN CSRF
                
                // Incluye el middleware CSRF para asegurar la protección CSRF en los formularios
                    include_once 'seguridad/csrf_middleware.php';

        // TITULO GENERACIÓN DEL TOKEN CSRF

                // Genera un token CSRF si no existe uno en la sesión
                    if (empty($_SESSION['csrf_token'])) {
                        // Genera un token de 64 caracteres y lo guarda en la sesión
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }

        // TITULO REDIRECCIÓN SEGÚN EL ROL DEL USUARIO
                    $mysqli->set_charset("utf8");
                // Verifica si la sesión tiene variables de correo y rol (usuario autenticado)
                    if (isset($_SESSION['correo']) && isset($_SESSION['rol'])) {
                        // Redirige según el rol del usuario
                            if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'supervisor') {
                                // Si el usuario es un admin, redirige a la página principal de roles
                                    header("Location: inicio_principal/inicio_roles.php");
                                exit();
                            } else {
                            // Si el usuario tiene un rol común, redirige a la página de inicio general
                                header("Location: inicio_principal/inicio.php");
                            exit();
                        }
                    }
            ?>

            <!-- contenedor principal-->
            <div class="register-container">
                <!-- Logo SEGMA -->
                <?php
                    // consulta el logo personalizado desde la base de datos
                    $res = $mysqli->query("SELECT logo FROM personalizacion WHERE id=1");
                    // obtiene el resultado como arreglo asociativo o vacío si falla
                    $cfg = $res ? $res->fetch_assoc() : [];
                    // obtiene el nombre del archivo de logo (si existe)
                    $rawLogo = $cfg['logo'] ?? '';
                    // obtiene la ruta raíz del servidor sin la barra final
                    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                    // valor por defecto del logo si no hay uno personalizado
                    $logoUrl = '/imagenes/ingreso_venta_img1.png';
                    // si hay un logo definido en la base de datos
                    if (!empty($rawLogo)) {
                        if (strpos($rawLogo, '/') === 0) {
                            $full = $docRoot . $rawLogo;
                            if (file_exists($full) && is_file($full)) {
                                $logoUrl = $rawLogo . '?v=' . filemtime($full);
                            } else {
                                $logoUrl = $rawLogo;
                            }
                        } else {
                            // si es una ruta relativa, se prueban tres ubicaciones posibles
                            $c1 = '/imagenes/ingreso_ventas/registro_ventas/' . $rawLogo;
                            $c2 = '/imagenes/ingreso_ventas/' . $rawLogo;
                            $c3 = '/imagenes/' . $rawLogo;
                            // se recorre cada ruta candidata hasta encontrar un archivo válido
                            foreach ([$c1,$c2,$c3] as $cand) {
                                $full = $docRoot . $cand;
                                if (file_exists($full) && is_file($full)) { $logoUrl = $cand . '?v=' . filemtime($full); break; }
                            }
                        }
                    }
                ?>
                <!-- Contenedor del logo de la empresa -->
                <div class="logo-section">
                    <div class="logo-segma">
                        <!-- muestra el logo cargado dinámicamente desde php -->
                        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="SEGMA Logo" class="logo-image">
                    </div>
                </div>

                <!-- Formulario de Registro -->
                <div class="register-section">
                    <h2 class="register-title">Crear cuenta</h2>
                    
                    <!-- Comienzo del formulario de registro -->
                    <form id="registroForm" action="/php/inicio_sesion/registro.php" method="POST" class="register-form">

                    <!-- Mensaje de error para registro -->
                        <div id="registroError" class="error_mensaje" style="display:none;"></div>

                    <!-- Token csrf oculto y vista post-registro -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="vista" value="inicio_sesion.php"> <!-- Campo oculto para redirigir después del registro -->

                        <div class="form-columns"> 
                            <div class="form-column">
                            <!-- Información Personal -->
                                <div class="form-section">
                                    <h3 class="section-title"><span class="section-icon">👤</span>Información Personal</h3>
                                    <div class="input-row">
                                        <div class="input-group">
                                            <input type="text" name="nombre" placeholder="Nombre" required="" class="register-input">
                                        </div>
                                        <div class="input-group">
                                            <input type="text" name="apellido" placeholder="Apellido" required="" class="register-input">
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <input type="text" id="rut" name="rut" placeholder="RUT (ej: 12345678-9)" required="" class="register-input">
                                    </div>
                                </div>

                                <!-- Información de Cuenta -->
                                <div class="form-section">
                                    <h3 class="section-title"><span class="section-icon">🔐</span>Información de Cuenta</h3>
                                    
                                    <div class="input-group">
                                        <input type="text" name="username" placeholder="Nombre de usuario" required="" class="register-input">
                                    </div>

                                    <div class="input-group">
                                        <input type="email" name="correo" placeholder="Correo electrónico" required="" class="register-input">
                                    </div>
                                </div>
                            </div>

                            <!-- Apartado derecho que contiene la seguridad y contacto -->
                            <div class="form-column">    
                                <!-- Seguridad -->
                                <div class="form-section">
                                    <h3 class="section-title"><span class="section-icon">🔒</span>Seguridad</h3>
            
                                    <div class="input-group password-container">
                                        <input type="password" id="password" name="password" placeholder="Contraseña (mín. 8 caracteres)" required="" class="register-input">
                                    </div>

                                    <!-- Mensaje de error para la contraseña -->
                                    <div id="password-error" class="error_mensaje"></div>

                                    <div class="input-group password-container">
                                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmar contraseña" required="" class="register-input">
                                    </div>

                                    <!-- Mensaje de error para la confirmación de contraseña -->
                                    <div id="confirm-password-error" class="error_mensaje"></div>
                                </div>

                                <!-- Información de Contacto -->
                                <div class="form-section">
                                    <h3 class="section-title"><span class="section-icon">📞</span>Información de Contacto</h3>
                                    
                                    <div class="input-group">
                                        <input type="text" name="telefono" placeholder="Teléfono (ej: +56 9 1234 5678)" required="" class="register-input">
                                    </div>

                                    <div class="input-group">
                                        <input type="text" name="direccion" placeholder="Dirección completa" required="" class="register-input">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Campos ocultos para cargo y rol -->
                        <input type="hidden" name="cargo" value="usuario_final">
                        <input type="hidden" name="rol" value="usuario_final">

                        <!-- Botón de registro -->
                        <div class="button-container">
                            <button type="submit" class="register-button">
                                <span class="button-icon">✨</span>Crear mi cuenta<span class="button-arrow">→</span>
                            </button>
                        </div>
                    </form>

                    <!-- Mensaje de error global -->
                    <div id="error_mensaje" class="error_mensaje"></div>

                    <!-- Enlace para alternar a inicio de sesión -->
                    <div class="login-section-link">
                        <p class="login-text">¿Ya tienes una cuenta?</p>
                        <a href="/ingreso_ventas.php" class="login-link">
                            <span class="login-icon">🔑</span>
                            Iniciar sesión
                            <span class="login-arrow">←</span>
                        </a>
                    </div>
                </div>
            </div>

        <!-- TITULO ARCHIVO JS -->

             <script src="/js/inicio_sesion/formulario_registro.js?v=<?= time() ?>"></script>

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
     -------------------------------------- FIN ITred Spa formulario_registro .PHP ------------------------------
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

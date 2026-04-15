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
     ------------------------------------- INICIO ITred Spa menu .PHP -------------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

     <?php
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    // include auditoría global (ahora centralizada en log_registros.php)
    require_once __DIR__ . '/../inicio_sesion/seguridad/log_registros.php';
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->
<?php
// Detectar si es modo previsualización (sin menú)
$esPreview = isset($_GET['preview']) && $_GET['preview'] == '1';
?>

 <?php
// Preparamos variables para detectar diferencias en facturas
$hay_diferencias = false;
$filas_facturas_malas = [];

// Consulta SQL para obtener solo las facturas con diferencias
$sql_facturas_malas = "
SELECT 
    f.id,
    f.codigo_producto AS SKU,
    f.n_factura,
    f.nombre_empresa,
    f.descripcion_producto,
    f.cantidad_producto AS cantidad_factura,
    COALESCE(v_sum.total_ingresado, 0) AS cantidad_ingresada
FROM factura f
LEFT JOIN (
    SELECT 
        numero_fact,
        sku,
        SUM(cantidad) AS total_ingresado
    FROM venta
    GROUP BY numero_fact, sku
) v_sum 
    ON TRIM(f.codigo_producto) = TRIM(v_sum.sku) 
    AND CAST(f.n_factura AS CHAR) = TRIM(v_sum.numero_fact)
WHERE f.cantidad_producto != COALESCE(v_sum.total_ingresado, 0)
";

$resultado_facturas = $mysqli->query($sql_facturas_malas);

if ($resultado_facturas) {
    while ($fila = $resultado_facturas->fetch_assoc()) {
        $hay_diferencias = true;
        $filas_facturas_malas[] = $fila;
    }
}

$total_facturas_malas = count($filas_facturas_malas);

?>

<!--TITULO HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>index - Panel principal</title>
    <!-- llama al archivo css que contiene los estilos para la página del menú -->
    <link rel="stylesheet" href="/css/ingreso_ventas/renderizar_menu.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/estilos_personalizados.css?<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

    <!-- TITULO BODY -->
<body>

    <!-- TITULO VARIABLES -->

        <?php
            // Configuramos la conexión para que use UTF-8
            $mysqli->set_charset("utf8");

            session_start();

            $username = $_SESSION['username'] ?? 'username'; 
            $nombre = $_SESSION['nombre'] ?? 'nombre';
            $apellido = $_SESSION['apellido'] ?? 'apellido';

            // Generar token CSRF si no existe
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }

            // Detectar si es superadmin
            $rol = $_SESSION['rol'] ?? '';
            $esSuperadmin = ($rol === 'superadmin');

            // Permitir solo admin y superadmin
            if (!isset($_SESSION['correo']) || !in_array($rol, ['admin', 'superadmin','usuario_final','distribuidor', 'bodega'])) {
                header("Location: /ingreso_ventas.php");
                exit();
            }


            // Personalización de logo
            $res = $mysqli->query("SELECT logo FROM personalizacion WHERE id=1");
            $config = $res ? $res->fetch_assoc() : [];
            $rawLogo = !empty($config['logo']) ? $config['logo'] : '';

            // Resolve logo path: support stored web-relative paths (starting with '/') or plain filenames.
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
            $candidates = [];
            if (!empty($rawLogo) && strpos($rawLogo, '/') === 0) {
                // already a web-relative path like /imagenes/ingreso_ventas/registro_ventas/logo_xxx.png
                $candidates[] = $rawLogo;
            } elseif (!empty($rawLogo)) {
                // try common locations where logos may be stored
                $candidates[] = '/imagenes/ingreso_ventas/' . $rawLogo;
                $candidates[] = '/imagenes/ingreso_ventas/' . $rawLogo;
                $candidates[] = '/imagenes/' . $rawLogo;
            }

            // always include the default fallback
            $candidates[] = '/imagenes/ingreso_venta_img1.png';

            // Variable para guardar el logo encontrado
            $found = null;
            foreach ($candidates as $rel) {
                // Construimos la ruta completa
                $full = $docRoot . $rel;
                if (file_exists($full) && is_file($full)) { $found = [$rel, $full]; break; }
            }

            // Si no se encontró ningún logo
            if ($found === null) {
                // Usamos el logo por defecto
                $rel = '/imagenes/ingreso_venta_img1.png';
                $found = [$rel, $docRoot . $rel]; // Guardamos ruta relativa y completa
            }

            // URL del logo con versión para evitar caché
            $logoUrl = $found[0] . '?v=' . (file_exists($found[1]) ? filemtime($found[1]) : time());
            $logo = basename($found[0]);

            // Página seleccionada
            $pagina = $_GET['pagina'] ?? 'ingreso_ventas';
        ?>

    <!-- TITULO NAVEGACION SUPERIOR -->
<?php if (!$esPreview): ?>            
        <!-- Barra de navegación superior -->
        <header class="top-header">
            <!-- Contenedor alineado a la izquierda dentro del header -->
            <div class="header-left">
                <!-- Botón hamburguesa para abrir/cerrar el sidebar en responsive-->
                <button class="hamburger-btn" onclick="toggleSidebar()">☰</button>
                <div class="breadcrumb">
                    <span>Administración</span>
                    <span>></span>
                    <span><?php 
                        // Array que define los títulos de cada página según su clave
                        $breadcrumbTitles = [
                            'ventas' => 'Ingreso Bodega',
                            'factura' => 'Ingreso Factura',
                            'ingreso_datos' => 'Ingreso Datos', 
                            'generar_qr' => 'Generar QR',
                            'normalizar_qr' => 'Normalizar QR',
                            'buscar' => 'Buscar',
                            'usuarios' => 'Usuarios',
                            'plantilla' => 'Plantilla',
                            'respaldo' => 'Respaldo',
                            'ingreso_ventas' => 'Dashboard'
                        ];
                        // Mostramos el título correspondiente a la página actual
                        echo $breadcrumbTitles[$pagina] ?? 'Dashboard';
                    ?></span>
                </div>
                 <!-- Sección de Alerta de Facturas Malas -->
                <div class="seccion-alerta-facturas">
                    <button id="btn_alerta_facturas"
                        onclick="window.location.href='renderizar_menu.php?pagina=factura&mostrar_facturas_malas=1'"
                        class="btn-alerta-facturas <?= $hay_diferencias ? 'alerta-activa-intermitente' : '' ?>">

                        <?= $hay_diferencias ? '⚠️ Ver Facturas con Problemas (' . $total_facturas_malas . ')' : '✅ Sin Problemas de Facturas' ?>
                    </button>
                </div>
            </div>

    <!-- TITULO NAVBAR -->
 
            <!-- Contenedor al lado derecha dentro del header -->            
            <div class="header-right">
                <!-- Bloque que muestra el nombre del usuario -->
                <span class="username" style="margin-right:16px;"> 
                    <div style="display: flex;">
                        <!-- Contenedor flexible para avatar del usuario -->
                        <img class="user-avatar" src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000%20." alt="mono">

                            <?php
                            function repararCaracteres($texto) {
                            // Reemplaza secuencias rotas comunes por sus equivalentes correctos
                            $reparaciones = [
                                'Ã±' => 'ñ',
                                'Ã¡' => 'á',
                                'Ã©' => 'é',
                                'Ã­' => 'í',
                                'Ã³' => 'ó',
                                'Ãº' => 'ú',
                                'Ã' => 'Á',
                                'Ã‰' => 'É',
                                'Ã“' => 'Ó',
                                'Ãš' => 'Ú',
                                'Ã‘' => 'Ñ',
                                'Â'  => '', // elimina caracteres invisibles
                            ];
                            // Aplica las reparaciones al texto
                            return strtr($texto, $reparaciones);
                            }

                            // Obtiene y repara el nombre desde sesión
                            $nombre = repararCaracteres($_SESSION['nombre'] ?? 'Nombre');
                            // Obtiene y repara el apellido desde sesión
                            $apellido = repararCaracteres($_SESSION['apellido'] ?? 'Apellido');
                            ?>
                            <!-- Contenedor para mostrar el nombre completo -->
                            <div class="username-text">
                            <!-- Imprime nombre y apellido -->
                            <?= $nombre . ' ' . $apellido ?> 
                            </div>

                    </div>
                </span>
                <form action="/php/inicio_sesion/inicio_principal/logout.php" method="post" style="display:inline;">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </div>
        </header>

        <!-- Contenedor de tabla de facturas malas -->
        <div id="contenedor_facturas_malas" class="contenedor-facturas-malas" style="display: none;">

            <div class="cabecera-facturas-malas">
                <h2>Facturas con Diferencias de Ingreso</h2>

                <div class="acciones-facturas-malas">
                    <button onclick="alternar_vista_facturas()" class="btn-volver">
                        Volver
                    </button>

                    <a href="renderizar_menu.php?pagina=ventas" class="btn-modificar">
                        Ir a ventas
                    </a>
                </div>
            </div>
            <form id="form-facturas-malas">
            <table class="tabla-facturas-malas">
                <thead>
                    <tr>
                        <th>N° Factura</th>
                        <th>SKU</th>
                        <th>Nombre Empresa</th>
                        <th>Producto</th>
                        <th>Cantidad Facturada</th>
                        <th>Cantidad Ingresada</th>
                        <th>Diferencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filas_facturas_malas as $fila): ?>
                        <?php $diferencia = $fila['cantidad_factura'] - $fila['cantidad_ingresada']; ?>

                        <tr data-id="<?= (int)$fila['id'] ?>">

                            <input type="hidden" name="id[]" value="<?= (int)$fila['id'] ?>">

                            <td>
                                <input name="n_factura[]" class="campo-editable"
                                    value="<?= htmlspecialchars($fila['n_factura']) ?>" readonly>
                            </td>

                            <td>
                                <input name="codigo_producto[]" class="campo-editable"
                                    value="<?= htmlspecialchars($fila['SKU']) ?>" readonly>
                            </td>

                            <td>
                                <input name="nombre_empresa[]" class="campo-editable"
                                    value="<?= htmlspecialchars($fila['nombre_empresa']) ?>" readonly>
                            </td>

                            <td>
                                <input name="descripcion_producto[]" class="campo-editable"
                                    value="<?= htmlspecialchars($fila['descripcion_producto']) ?>" readonly>
                            </td>

                            <td><?= $fila['cantidad_factura'] ?></td>
                            <td><?= $fila['cantidad_ingresada'] ?></td>
                            <td><?= $diferencia ?></td>

                            <td>
                               <a href="renderizar_menu.php?pagina=ingreso_datos&abrir_tab=factura&id_editar=<?= (int)$fila['id'] ?>" 
                                class="btn-modificar" 
                                >   Modificar 
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>
            </form>
        </div>


        <!-- Menú lateral -->
        <aside class="sidebar" id="sidebar">
            <!-- Encabezado de la barra lateral -->
            <div class="sidebar-header">
                <a href="/php/ingreso_ventas/renderizar_menu.php?pagina=ingreso_ventas" class="sidebar-logo-link" title="Inicio">
                    <img src="<?= $logoUrl ?>" alt="Logo SEGMA" class="sidebar-logo">
                </a>
                <?php /* Removed the small app name — logo only now */ ?>
            </div>
            
            <nav class="sidebar-nav">
                <!-- Sección de Capacitaciones (similar a la imagen) -->
                <div class="nav-section">
                    <!-- Ítem de menú, activo si la página es ingreso_ventas -->
                    <div class="nav-item <?= $pagina === 'ingreso_ventas' ? 'active' : '' ?>">
                        <span class="nav-icon">📊</span>
                        <!-- Enlace al Dashboard -->
                        <a href="?pagina=ingreso_ventas" class="nav-link">Dashboard</a> 
                    </div>
                </div>

                <!-- Sección principal de opciones -->
                <?php if ($esSuperadmin): ?> <!-- Si el usuario es superadmin -->
                    <!-- Bloque de navegación -->
                    <div class="nav-section">
                        <div class="nav-item <?= $pagina === 'ingreso_datos' ? 'active' : '' ?>">
                            <span class="nav-icon">📝</span>
                            <a href="?pagina=ingreso_datos" class="nav-link">Ingreso Datos</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'ventas' ? 'active' : '' ?>">
                            <span class="nav-icon">💰</span>
                            <a href="?pagina=ventas" class="nav-link">Ingreso Bodega</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'generar_qr' ? 'active' : '' ?>">
                            <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span>
                            <a href="?pagina=generar_qr" class="nav-link">Generar QR</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'normalizar_qr' ? 'active' : '' ?>">
                            <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span>
                            <a href="?pagina=normalizar_qr" class="nav-link">Normalizar QR</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'buscar' ? 'active' : '' ?>">
                            <span class="nav-icon">🔍</span>
                            <a href="?pagina=buscar" class="nav-link">Buscar</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'usuarios' ? 'active' : '' ?>">
                            <span class="nav-icon">👥</span>
                            <a href="?pagina=usuarios" class="nav-link">Usuarios</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'plantilla' ? 'active' : '' ?>">
                            <span class="nav-icon">📄</span>
                            <a href="?pagina=plantilla" class="nav-link">Plantilla</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'respaldo' ? 'active' : '' ?>">
                            <span class="nav-icon">💾</span>
                            <a href="?pagina=respaldo" class="nav-link">Generar Respaldo</a>
                        </div>
                    </div>
                <!-- Si el usuario es admin -->
                <?php elseif ($rol === 'admin'): ?>
                    <div class="nav-section">
                        <div class="nav-item <?= $pagina === 'ingreso_datos' ? 'active' : '' ?>">
                            <span class="nav-icon">📝</span>
                            <a href="?pagina=ingreso_datos" class="nav-link">Ingreso Datos</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'ventas' ? 'active' : '' ?>">
                            <span class="nav-icon">💰</span>
                            <a href="?pagina=ventas" class="nav-link">Ingreso Bodega</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'generar_qr' ? 'active' : '' ?>">
                            <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span>
                            <a href="?pagina=generar_qr" class="nav-link">Generar QR</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'buscar' ? 'active' : '' ?>">
                            <span class="nav-icon">🔍</span>
                            <a href="?pagina=buscar" class="nav-link">Buscar</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'usuarios' ? 'active' : '' ?>">
                            <span class="nav-icon">👥</span>
                            <a href="?pagina=usuarios" class="nav-link">Usuarios</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'respaldo' ? 'active' : '' ?>">
                            <span class="nav-icon">💾</span>
                            <a href="?pagina=respaldo" class="nav-link">Generar Respaldo</a>
                        </div>
                    </div>
                <!-- Si el usuario es distribuidor -->
                <?php elseif ($rol === 'distribuidor'): ?>
                    <div class="nav-section">
                        <div class="nav-item <?= $pagina === 'buscar' ? 'active' : '' ?>">
                            <span class="nav-icon">🔍</span>
                            <a href="?pagina=buscar" class="nav-link">Buscar</a>
                        </div>
                    </div>
                <!-- Si el usuario es usuario final -->
                <?php elseif ($rol === 'usuario_final'): ?>
                    <div class="nav-section">
                        <div class="nav-item <?= $pagina === 'buscar' ? 'active' : '' ?>">
                            <span class="nav-icon">🔍</span>
                            <a href="?pagina=buscar" class="nav-link">Buscar</a>
                        </div>
                    </div>
                <!-- Si el usuario es bodega -->
                <?php elseif ($rol === 'bodega'): ?>
                    <div class="nav-section">
                        <div class="nav-item <?= $pagina === 'ventas' ? 'active' : '' ?>">
                            <span class="nav-icon">💰</span>
                            <a href="?pagina=ventas" class="nav-link">Ingreso Ventas</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'normalizar_qr' ? 'active' : '' ?>">
                            <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span>
                            <a href="?pagina=normalizar_qr" class="nav-link">Normalizar QR</a>
                        </div>
                        <div class="nav-item <?= $pagina === 'buscar' ? 'active' : '' ?>">
                            <span class="nav-icon">🔍</span>
                            <a href="?pagina=buscar" class="nav-link">Buscar</a>
                        </div>
                    </div>
                <?php endif; ?>
            </nav>
        </aside>
<?php endif; ?>

        <!-- Contenido principal -->
        <main class="main-content <?= $esPreview ? 'preview-mode' : '' ?>">
            <!-- Contenedor del contenido dinámico -->
            <div class="content-wrapper">
                <?php
                // Según el valor de $pagina, se carga el archivo correspondiente
                switch ($pagina) {
                    case 'factura':
                        include(__DIR__ . '/registro_ventas/factura.php');
                        break;
                    case 'ventas':
                        include(__DIR__ . '/registro_ventas/ventas.php');
                        break;
                    case 'ingreso_datos':
                        include(__DIR__ . '/ingreso_clientes/ingreso_datos.php');
                        break;
                    case 'generar_qr':
                        include(__DIR__ . '/consultar_productos/generar_qr.php');
                        break;
                    case 'normalizar_qr':
                        include(__DIR__ . '/registro_ventas/normalizar_qr.php');
                        break;
                    case 'buscar':
                        include(__DIR__ . '/consultar_productos/buscar.php');
                        break;
                    case 'plantilla':
                        include(__DIR__ . '/registro_ventas/plantilla.php');
                        break;
                    // Si la página es usuarios
                    case 'usuarios':
                        // Si es superadmin → superadmin.php, si es admin → admin.php
                        if ($esSuperadmin) {
                            $accesoDesdeMenu = true; // Marca que el acceso viene desde el menú  
                            include(__DIR__ . '/../inicio_sesion/superadmin/superadmin.php');
                        } else {
                            $accesoDesdeMenu = true; // Marca que el acceso viene desde el menú  
                            include(__DIR__ . '/../inicio_sesion/superadmin/admin.php');
                        }
                    break;
                    case 'editar_perfil':
                        include(__DIR__ . '/../inicio_sesion/registro/editar_perfil.php');
                        break;
                    case 'editar_usuario':
                        include(__DIR__ . '/../inicio_sesion/registro/editar_usuario.php');
                        break;
                    case 'respaldo':
                        include(__DIR__ . '/respaldo/generar_respaldo.php');
                        break;
                    default:
                        include (__DIR__ . '/../inicio_sesion/inicio_principal/inicio.php');
                }
                ?>
            </div>
        </main>

    <!-- TITULO JS -->

        <!-- Enlace al archivo JavaScript para la funcionalidad del inicio de sesión -->
        <script>
            // Token CSRF disponible para el JS
            window.ITRED_CSRF = '<?= $_SESSION['csrf_token'] ?>';
        </script>


        <script src="/js/ingreso_ventas/renderizar_menu.js?v=<?= time() ?>"></script>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const params = new URLSearchParams(window.location.search);

            if (
                params.get("pagina") === "factura" &&
                params.get("mostrar_facturas_malas") === "1"
            ) {
                if (typeof alternar_vista_facturas === "function") {
                    alternar_vista_facturas();
                }
            }
        });
        </script>

</body>
</html>


<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <?php 
        // Cierra la conexión a la base de datos
        // $mysqli->close();
    ?>

<!-- ----------------------------
        -- FIN CIERRE CONEXION BD --
        ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa menu .PHP ---------------------------------------------
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

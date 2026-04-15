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
     ------------------------------------- INICIO ITred Spa plantilla .PHP --------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD -- 
     ------------------------ --> 

     <?php
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<!-- TITULO MAPEO SECCION -->

<?php
// Mapeo sección -> ID
$mapa_secciones = [
    'pagina_inicio' => 1,
    'crear_usuario' => 2,
    'dashboard' => 3,
    'ingreso_factura' => 4,
    'ingreso_productos' => 5,
    'ingreso_datos' => 6,
    'generar_qr' => 7,
    'normalizar_qr' => 8,
    'buscar' => 9,
    'usuarios' => 10,
    'plantilla' => 11,
    'generar_respaldo' => 12
];

// Cargar todos los estilos de todas las secciones
$estilos_secciones = [];
$res_all = $mysqli->query("SELECT * FROM personalizacion WHERE id BETWEEN 1 AND 12 ORDER BY id");
while ($row = $res_all->fetch_assoc()) {
    $estilos_secciones[$row['id']] = $row;
}
?>

<!-- TITULO HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>plantilla</title>
    <!-- estilos del módulo -->
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/plantilla.css?v=<?= time() ?>">
    <!-- css vista previa -->
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/vista_previa.css?v=<?= time() ?>">
    <!-- variables personalizadas (generadas por el guardado) -->
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/estilos_personalizados.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
</head>
<body id="body" class="plantilla">

<?php
    $mysqli->set_charset("utf8");
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN
    $Url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $esSuperadmin = strpos($Url, 'superadmin.php') !== false;

    if (!$esSuperadmin && !isset($_SESSION['correo'])) {
        $archivo = '/ingreso_ventas.php';
        header("Location: ".$archivo);
        exit();
    }

    // TITULO OBTENER ESTILOS
    $res = $mysqli->query("SELECT * FROM personalizacion WHERE id=1");
    $config = $res ? $res->fetch_assoc() : [];

    // Documento root (para comprobar archivos en disco)
    $DOCROOT = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

    $colorPrincipal    = $config['color_principal']     ?? '#768293'; /* ver a que apartados afecta */
    $colorFondo        = $config['color_fondo']         ?? '#ffffff';
    $colorTexto        = $config['color_texto']         ?? '#000000';
    $colorBorde        = $config['color_borde']         ?? '#000000';
    $colorBoton        = $config['color_boton']         ?? '#ffffff';
    $colorBotonTexto   = $config['color_boton_texto']   ?? '#000000';
    $colorBotonHover   = $config['color_boton_hover']   ?? '#dee3e9'; 
    $colorCampos       = $config['color_campos']        ?? '#ffffff';
    $colorTextoCampos  = $config['color_texto_campos']  ?? '#919191';
    
    // Variables para personalización de bordes de botones
    $botonBordeEstilo  = $config['borde_estilo']  ?? 'solid';
    $botonBordeGrosor  = $config['borde_ancho']  ?? '2';
    $botonBordeColor   = $config['borde_color']   ?? '#94a3b8';
    $botonBordeRadio   = $config['borde_radio']   ?? '5';
    $seccionesBordesActivas = $config['secciones_bordes_activas'] ?? '';
    
    // Variables para botones específicos de cada sección
    $botonesEspecificos = $config['botones_especificos_activos'] ?? '';
    
    // Variable para tipo de fuente
    $tipoFuente = $config['tipo_fuente'] ?? 'monospace';
    // Variable para tamaño de fuente
    $tamanoFuente = $config['tamano_fuente'] ?? '14';
    
    // Obtener fondo interior de la BD
    $fondoInteriorBD = $config['fondo_interior'] ?? '';
    
    // Obtener fondo general de la BD
    $fondoBD = $config['fondo'] ?? '';
    
    // Logo guardado (ruta)
    // Resolve logo: prefer saved value, fallback to canonical ingreso_venta_img1.png
    $logoRaw = $config['logo'] ?? '';
    $imgDirRel = '/imagenes/ingreso_ventas/registro_ventas/';
    $logoUrl = '';
    if (!empty($logoRaw)) {
        if (strpos($logoRaw, '/') === 0) {
            // web-relative path stored
            $full = $DOCROOT . $logoRaw;
            if (file_exists($full) && is_file($full)) {
                $logoUrl = $logoRaw . '?v=' . filemtime($full);
            } else {
                $logoUrl = $logoRaw;
            }
        } else {
            // nombre del archivo guardado
            $cands = [
                '/imagenes/ingreso_ventas/registro_ventas/' . $logoRaw,
                '/imagenes/ingreso_ventas/' . $logoRaw,
                '/imagenes/' . $logoRaw
            ];
            foreach ($cands as $c) {
                $full = $DOCROOT . $c;
                if (file_exists($full) && is_file($full)) { $logoUrl = $c . '?v=' . filemtime($full); break; }
            }
        }
    }
    // Final fallback: canonical root logo
    if (empty($logoUrl)) {
        $rootLogo = '/imagenes/ingreso_venta_img1.png';
        $fullRoot = $DOCROOT . $rootLogo;
        $logoUrl = $rootLogo . '?v=' . (file_exists($fullRoot) ? filemtime($fullRoot) : time());
    }

    // Obtener imagen de fondo (si está definida en el CSS personalizado)
    $cssFile = $DOCROOT . '/css/ingreso_ventas/registro_ventas/estilos_personalizados.css';
    $fondoUrl = '';
    $fondoInteriorUrl = '';
    $colorFondoInterior = $config['color_fondo_interior'] ?? '#ffffff';
    
    // Si hay fondo general en la BD, usarlo directamente
    if (!empty($fondoBD)) {
        // Si es una ruta relativa, agregar al cache
        if (strpos($fondoBD, '/') === 0) {
            $fullF = $DOCROOT . $fondoBD;
            if (file_exists($fullF) && is_file($fullF)) {
                $fondoUrl = $fondoBD . '?v=' . filemtime($fullF);
            } else {
                $fondoUrl = $fondoBD;
            }
        } else {
            $fondoUrl = $fondoBD;
        }
    }
    
    // Si hay fondo interior en la BD, usarlo directamente
    if (!empty($fondoInteriorBD)) {
        // Si es una ruta relativa, agregar al cache
        if (strpos($fondoInteriorBD, '/') === 0) {
            $fullFI = $DOCROOT . $fondoInteriorBD;
            if (file_exists($fullFI) && is_file($fullFI)) {
                $fondoInteriorUrl = $fondoInteriorBD . '?v=' . filemtime($fullFI);
            } else {
                $fondoInteriorUrl = $fondoInteriorBD;
            }
        } else {
            $fondoInteriorUrl = $fondoInteriorBD;
        }
    }
    
    if (file_exists($cssFile) && is_file($cssFile)) {
        $cssContent = file_get_contents($cssFile);
        // Solo buscar fondo general en CSS si no se encontró en la BD
        if (empty($fondoUrl) && preg_match("/body\\s*\\{[^}]*background-image:\\s*url\\(['\"]?(.*?)['\"]?\\)/i", $cssContent, $m)) {
            $candidate = $m[1];
            // Si la ruta es relativa al documento, agregar cache-busting
            if (strpos($candidate, '/') === 0) {
                $full = $DOCROOT . $candidate;
                if (file_exists($full) && is_file($full)) {
                    $fondoUrl = $candidate . '?v=' . filemtime($full);
                } else {
                    $fondoUrl = $candidate;
                }
            } else {
                $fondoUrl = $candidate;
            }
        }
        // Solo buscar en CSS si no se encontró en la BD para fondo contenido
        if (empty($fondoInteriorUrl) && preg_match("/\\.opciones\\s*\\{[^}]*background-image:\\s*url\\(['\"]?(.*?)['\"]?\\)/i", $cssContent, $mi)) {
            $cand2 = $mi[1];
            if (strpos($cand2, '/') === 0) {
                $full2 = $DOCROOT . $cand2;
                if (file_exists($full2) && is_file($full2)) {
                    $fondoInteriorUrl = $cand2 . '?v=' . filemtime($full2);
                } else {
                    $fondoInteriorUrl = $cand2;
                }
            } else {
                $fondoInteriorUrl = $cand2;
            }
        }
        // buscar variable --color-fondo-interior en :root
        if (preg_match('/--color-fondo-interior:\s*(#?[A-Za-z0-9#\(\)%,\.\s-]+);/i', $cssContent, $mv)) {
            $colorFondoInterior = trim($mv[1]);
        }
    }
?>
<!-- TITULO CABECERA PRINCIPAL -->
<header class="cabecera_principal">
    <div class="logo_contenedor">
        <img id="logo_preview_cabecera" src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
    </div>
    <nav class="menu_secciones">
        <button type="button" class="btn_seccion active" data-seccion="pagina_inicio">página inicio</button>
        <button type="button" class="btn_seccion" data-seccion="crear_usuario">crear usuario</button>
        <button type="button" class="btn_seccion" data-seccion="dashboard">dashboard</button>
        <button type="button" class="btn_seccion" data-seccion="ingreso_factura">ingreso factura</button>
        <button type="button" class="btn_seccion" data-seccion="ingreso_productos">ingreso productos</button>
        <button type="button" class="btn_seccion" data-seccion="ingreso_datos">ingreso datos</button>
        <button type="button" class="btn_seccion" data-seccion="generar_qr">generar qr</button>
        <button type="button" class="btn_seccion" data-seccion="normalizar_qr">normalizar qr</button>
        <button type="button" class="btn_seccion" data-seccion="buscar">buscar</button>
        <button type="button" class="btn_seccion" data-seccion="usuarios">usuarios</button>
        <button type="button" class="btn_seccion" data-seccion="plantilla">plantilla</button>
        <button type="button" class="btn_seccion" data-seccion="generar_respaldo">generar respaldo</button>
    </nav>
</header>

<div class="contenedor_principal">
    <form id="formPersonalizar" enctype="multipart/form-data" method="POST" action="/php/ingreso_ventas/registro_ventas/guardar_plantilla.php">
        
        <input type="hidden" name="seccion_activa" id="seccion_activa" value="pagina_inicio">
        <input type="hidden" name="seccion_id" id="seccion_id" value="1">
        
        <div class="panel_herramientas">

            <!-- TITULO PALETA COLORES -->

            <!-- caja1 colores -->
            <div class="caja_herramienta caja_colores">
                <div class="paleta_colores">
                    <div class="fila_colores">
                        <button type="button" class="color_circulo seleccionado" data-color="#000000" style="background:#000000; border: 3px solid #0066ff;"></button>
                        <button type="button" class="color_circulo" data-color="#434343" style="background:#434343;"></button>
                        <button type="button" class="color_circulo" data-color="#666666" style="background:#666666;"></button>
                        <button type="button" class="color_circulo" data-color="#999999" style="background:#999999;"></button>
                        <button type="button" class="color_circulo" data-color="#b7b7b7" style="background:#b7b7b7;"></button>
                        <button type="button" class="color_circulo" data-color="#cccccc" style="background:#cccccc;"></button>
                        <button type="button" class="color_circulo" data-color="#d9d9d9" style="background:#d9d9d9;"></button>
                        <button type="button" class="color_circulo" data-color="#efefef" style="background:#efefef;"></button>
                        <button type="button" class="color_circulo" data-color="#f3f3f3" style="background:#f3f3f3;"></button>
                        <button type="button" class="color_circulo" data-color="#ffffff" style="background:#ffffff;"></button>
                        <label class="color_circulo color_personalizado" title="Color personalizado">
                            <input type="color" id="color_custom" value="#ff0000">
                            <span class="icono_mas">+</span>
                        </label>
                    </div>
                    <div class="fila_colores">
                        <button type="button" class="color_circulo" data-color="#ff0000" style="background:#ff0000;"></button>
                        <button type="button" class="color_circulo" data-color="#ff9900" style="background:#ff9900;"></button>
                        <button type="button" class="color_circulo" data-color="#ffff00" style="background:#ffff00;"></button>
                        <button type="button" class="color_circulo" data-color="#00ff00" style="background:#00ff00;"></button>
                        <button type="button" class="color_circulo" data-color="#00ffff" style="background:#00ffff;"></button>
                        <button type="button" class="color_circulo" data-color="#0000ff" style="background:#0000ff;"></button>
                        <button type="button" class="color_circulo" data-color="#9900ff" style="background:#9900ff;"></button>
                        <button type="button" class="color_circulo" data-color="#ff00ff" style="background:#ff00ff;"></button>
                        <button type="button" class="color_circulo" data-color="#ff6666" style="background:#ff6666;"></button>
                        <button type="button" class="color_circulo" data-color="#e6b8af" style="background:#e6b8af;"></button>
                    </div>
                    <div class="fila_colores">
                        <button type="button" class="color_circulo" data-color="#cc0000" style="background:#cc0000;"></button>
                        <button type="button" class="color_circulo" data-color="#e69138" style="background:#e69138;"></button>
                        <button type="button" class="color_circulo" data-color="#f1c232" style="background:#f1c232;"></button>
                        <button type="button" class="color_circulo" data-color="#6aa84f" style="background:#6aa84f;"></button>
                        <button type="button" class="color_circulo" data-color="#45818e" style="background:#45818e;"></button>
                        <button type="button" class="color_circulo" data-color="#3d85c6" style="background:#3d85c6;"></button>
                        <button type="button" class="color_circulo" data-color="#674ea7" style="background:#674ea7;"></button>
                        <button type="button" class="color_circulo" data-color="#a64d79" style="background:#a64d79;"></button>
                        <button type="button" class="color_circulo" data-color="#85200c" style="background:#85200c;"></button>
                        <button type="button" class="color_circulo" data-color="#783f04" style="background:#783f04;"></button>
                    </div>
                </div>
                <button type="button" id="btn_modo_seleccion" onclick="toggle_modo_seleccion()" style="margin-bottom:10px; padding:8px 12px; cursor:pointer; border-radius:5px; border:1px solid #ccc;"> Modo: Global</button>
                <button type="button" id="btn_modo_arrastrar" onclick="toggle_modo_arrastrar()" style="margin-bottom:10px; margin-left:5px; padding:8px 12px; cursor:pointer; border-radius:5px; border:1px solid #ccc;"> Arrastrar</button>
                <p class="etiqueta_colores">Colores</p>
                
                <input type="hidden" name="color_fondo" id="color_fondo" value="<?= htmlspecialchars($colorFondo) ?>">
                <input type="hidden" name="color_fondo_interior" id="color_fondo_interior" value="<?= htmlspecialchars($colorFondoInterior) ?>">
                <input type="hidden" name="color_principal" id="color_principal" value="<?= htmlspecialchars($colorPrincipal) ?>">
                <input type="hidden" name="color_texto" id="color_texto" value="<?= htmlspecialchars($colorTexto) ?>">
                <input type="hidden" name="color_borde" id="color_borde" value="<?= htmlspecialchars($colorBorde) ?>">
                <input type="hidden" name="color_boton" id="color_boton" value="<?= htmlspecialchars($colorBoton) ?>">
                <input type="hidden" name="color_boton_texto" id="color_boton_texto" value="<?= htmlspecialchars($colorBotonTexto) ?>">
                <input type="hidden" name="color_boton_hover" id="color_boton_hover" value="<?= htmlspecialchars($colorBotonHover) ?>">
                <input type="hidden" name="color_campos" id="color_campos" value="<?= htmlspecialchars($colorCampos) ?>">
                <input type="hidden" name="color_texto_campos" id="color_texto_campos" value="<?= htmlspecialchars($colorTextoCampos) ?>">
                
                <div class="selector_destino">
                    <label>Aplicar a:</label>
                    <select id="destino_color">
                        <option value="color_fondo">Fondo</option>
                        <option value="color_fondo_interior">Fondo interior</option>
                        <option value="color_texto">Texto</option>
                        <option value="color_borde">Bordes</option>
                        <option value="color_boton">Botón</option>
                        <option value="color_boton_texto">Texto botón</option>
                        <option value="color_boton_hover">Hover botón</option>
                        <option value="color_campos">Campos</option>
                        <option value="color_texto_campos">Texto campos</option>
                    </select>
                </div>
            </div>
            
            <!-- TITULO TIPO DE FUENTE -->
            <div class="caja_herramienta caja_tipografia">
                <select name="tipo_fuente" id="tipo_fuente">
                    <option value="monospace" <?= ($tipoFuente == 'monospace') ? 'selected' : '' ?>>Monospace</option>
                    <option value="Arial" <?= ($tipoFuente == 'Arial') ? 'selected' : '' ?>>Arial</option>
                    <option value="Helvetica" <?= ($tipoFuente == 'Helvetica') ? 'selected' : '' ?>>Helvetica</option>
                    <option value="Times New Roman" <?= ($tipoFuente == 'Times New Roman') ? 'selected' : '' ?>>Times New Roman</option>
                    <option value="Georgia" <?= ($tipoFuente == 'Georgia') ? 'selected' : '' ?>>Georgia</option>
                    <option value="Verdana" <?= ($tipoFuente == 'Verdana') ? 'selected' : '' ?>>Verdana</option>
                    <option value="Courier New" <?= ($tipoFuente == 'Courier New') ? 'selected' : '' ?>>Courier New</option>
                    <option value="Tahoma" <?= ($tipoFuente == 'Tahoma') ? 'selected' : '' ?>>Tahoma</option>
                    <option value="Trebuchet MS" <?= ($tipoFuente == 'Trebuchet MS') ? 'selected' : '' ?>>Trebuchet MS</option>
                </select>
                <select name="tamano_fuente" id="tamano_fuente">
                    <?php for($i = 10; $i <= 24; $i += 2): ?>
                    <option value="<?= $i ?>" <?= ($tamanoFuente == $i) ? 'selected' : '' ?>><?= $i ?>px</option>
                    <?php endfor; ?>
                </select>
                <div class="opciones_texto">
                    <button type="button" class="btn_formato" data-formato="bold" title="Negrita">N</button>
                    <button type="button" class="btn_formato" data-formato="italic" title="Cursiva">I</button>
                    <button type="button" class="btn_formato" data-formato="underline" title="Subrayado">S</button>
                </div>
            </div>
            
            <!-- TITULO BORDES DE BOTONES -->
            <div class="caja_herramienta caja_botones">
                <select name="borde_estilo" id="borde_estilo">
                    <option value="solid" <?= ($botonBordeEstilo == 'solid') ? 'selected' : '' ?>>Sólido</option>
                    <option value="dashed" <?= ($botonBordeEstilo == 'dashed') ? 'selected' : '' ?>>Guiones</option>
                    <option value="dotted" <?= ($botonBordeEstilo == 'dotted') ? 'selected' : '' ?>>Puntos</option>
                    <option value="double" <?= ($botonBordeEstilo == 'double') ? 'selected' : '' ?>>Doble</option>
                    <option value="none" <?= ($botonBordeEstilo == 'none') ? 'selected' : '' ?>>Sin borde</option>
                </select>
                <div class="control_rango">
                    <label>Grosor</label>
                    <input type="range" name="borde_ancho" id="borde_ancho" min="0" max="10" value="<?= htmlspecialchars($botonBordeGrosor) ?>">
                    <span id="borde_ancho_valor"><?= htmlspecialchars($botonBordeGrosor) ?>px</span>
                </div>
                <div class="control_rango">
                    <label>Radio</label>
                    <input type="range" name="borde_radio" id="borde_radio" min="0" max="30" value="<?= htmlspecialchars($botonBordeRadio) ?>">
                    <span id="borde_radio_valor"><?= htmlspecialchars($botonBordeRadio) ?>px</span>
                </div>
                
                <!-- Nuevos cambios -->    
                 

                        <button type="button" id="btnAgregarBotonPreview"
                            style="padding:8px 14px; background:var(--color-principal); color:#fff;
                        border:none; border-radius:6px; cursor:pointer;">
                        Agregar botón a la vista previa
                        </button>
 
            
                <!-- Fin nuevos cambios -->




            </div>
        </div>
        
        <div class="area_vista_previa">
            <div class="vista_previa_grande" id="vista_previa_grande">
                <iframe id="iframe_preview" src="/ingreso_ventas.php" frameborder="0"></iframe>
            </div>
        </div>
        
        <div class="botones_accion">
            <button type="submit" class="btn_restaurar" name="restaurar" value="1">🔄 Restaurar</button>
            <button type="submit" class="btn_guardar">💾 Guardar</button>
        </div>
    </form>
</div>

<!-- TITULO ARCHIVO JS -->
<script src="/js/ingreso_ventas/registro_ventas/plantilla.js?v=<?= time() ?>"></script>
<script src="/js/ingreso_ventas/registro_ventas/vista_previa.js?v=<?= time() ?>"></script>
<script>
// Tabs simples
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        const target = btn.getAttribute('data-target');
        document.querySelectorAll('.tab-section').forEach(sec => {
            sec.style.display = (sec.id === target) ? 'block' : 'none';
        });
    });
});

// Script para debugging y funciones de respaldo
console.log('=== INICIALIZANDO PLANTILLA ===');

// Función de diagnóstico que puedes llamar desde la consola
function probarFuentes() {
    console.log(' FUENTES Y TAMAÑOS');
    var selectFuente = document.getElementById('tipo_fuente');
    var selectTamano = document.getElementById('tamano_fuente');
    
    console.log('Selector fuente:', selectFuente);
    console.log('Selector tamaño:', selectTamano);
    
    if (selectFuente) {
        console.log(' Selector de fuente encontrado');
        console.log('Fuente actual:', selectFuente.value);
        console.log('Opciones disponibles:', Array.from(selectFuente.options).map(o => o.value));
    } else {
        console.log(' No se encontró el selector de fuente');
        return false;
    }
    
    if (selectTamano) {
        console.log(' Selector de tamaño encontrado');
        console.log('Tamaño actual:', selectTamano.value + 'px');
        console.log('Opciones disponibles:', Array.from(selectTamano.options).map(o => o.value));
    } else {
        console.log(' No se encontró el selector de tamaño');
        return false;
    }
    
    // Probar funciones del archivo JS externo
    console.log('Función aplicarFuentePreview:', typeof window.aplicarFuentePreview);
    console.log('Función aplicarTamanoFuentePreview:', typeof window.aplicarTamanoFuentePreview);
    
    return true;
}

// Función manual para probar cambio de fuente
function testFuenteManual() {
    const selectFuente = document.getElementById('tipoFuente');
    if (!selectFuente) return false;
    
    const fuente = selectFuente.value;
    console.log('Probando fuente manualmente:', fuente);
    
    // Aplicar directamente
    document.body.style.fontFamily = fuente;
    document.body.setAttribute('data-font', fuente);
    
    console.log('Fuente aplicada manualmente a body');
    return true;
}

// Función manual para probar cambio de tamaño
function testTamanoManual() {
    const selectTamano = document.getElementById('tamanoFuente');
    if (!selectTamano) return false;
    
    const tamano = selectTamano.value;
    console.log('Probando tamaño manualmente:', tamano + 'px');
    
    // Aplicar directamente
    document.body.style.fontSize = tamano + 'px';
    
    console.log('Tamaño aplicado manualmente a body');
    return true;
}

// Hacer funciones disponibles globalmente
window.probarFuentes = probarFuentes;
window.testFuenteManual = testFuenteManual;
window.testTamanoManual = testTamanoManual;

// Diagnóstico automático cuando se carga
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, ejecutando diagnóstico...');
    setTimeout(probarFuentes, 500);

    // Asegurar que la vista por defecto del editor y la vista previa coincidan
});
</script>
<script>
// Mapeo sección -> ID
var mapa_secciones = {
    'pagina_inicio': 1,
    'crear_usuario': 2,
    'dashboard': 3,
    'ingreso_factura': 4,
    'ingreso_productos': 5,
    'ingreso_datos': 6,
    'generar_qr': 7,
    'normalizar_qr': 8,
    'buscar': 9,
    'usuarios': 10,
    'plantilla': 11,
    'generar_respaldo': 12
};

// Estilos cargados desde BD
var estilos_bd = {
<?php foreach ($estilos_secciones as $id => $estilos): ?>
    <?= $id ?>: {
        color_fondo: '<?= addslashes($estilos['color_fondo'] ?? '#ffffff') ?>',
        color_fondo_interior: '<?= addslashes($estilos['color_fondo_interior'] ?? '#ffffff') ?>',
        color_texto: '<?= addslashes($estilos['color_texto'] ?? '#000000') ?>',
        color_borde: '<?= addslashes($estilos['color_borde'] ?? '#000000') ?>',
        color_boton: '<?= addslashes($estilos['color_boton'] ?? '#ffffff') ?>',
        color_boton_texto: '<?= addslashes($estilos['color_boton_texto'] ?? '#000000') ?>',
        color_boton_hover: '<?= addslashes($estilos['color_boton_hover'] ?? '#dee3e9') ?>',
        color_campos: '<?= addslashes($estilos['color_campos'] ?? '#ffffff') ?>',
        color_texto_campos: '<?= addslashes($estilos['color_texto_campos'] ?? '#919191') ?>',
        tipo_fuente: '<?= addslashes($estilos['tipo_fuente'] ?? 'monospace') ?>',
        tamano_fuente: '<?= addslashes($estilos['tamano_fuente'] ?? '14') ?>',
        borde_ancho: '<?= addslashes($estilos['borde_ancho'] ?? '2') ?>',
        borde_estilo: '<?= addslashes($estilos['borde_estilo'] ?? 'solid') ?>',
        borde_radio: '<?= addslashes($estilos['borde_radio'] ?? '5') ?>'
    },
<?php endforeach; ?>
};

// Estilos modificados en memoria (sin guardar)
var estilos_modificados = {};

// Sección activa actual
var seccion_activa = 'pagina_inicio';

// Obtener estilos de una sección (modificados o de BD)
function obtener_estilos_seccion(seccion) {
    var id = mapa_secciones[seccion];
    if (estilos_modificados[id]) {
        return estilos_modificados[id];
    }
    return estilos_bd[id] || estilos_bd[1];
}

// Guardar estilo en memoria para la sección activa
function guardar_estilo_memoria(propiedad, valor) {
    var id = mapa_secciones[seccion_activa];
    if (!estilos_modificados[id]) {
        estilos_modificados[id] = Object.assign({}, estilos_bd[id]);
    }
    estilos_modificados[id][propiedad] = valor;
}

// Cargar estilos en los controles cuando cambia de sección
function cargar_estilos_en_controles(seccion) {
    var estilos = obtener_estilos_seccion(seccion);
    
    document.getElementById('color_fondo').value = estilos.color_fondo;
    document.getElementById('color_fondo_interior').value = estilos.color_fondo_interior;
    document.getElementById('color_texto').value = estilos.color_texto;
    document.getElementById('color_borde').value = estilos.color_borde;
    document.getElementById('color_boton').value = estilos.color_boton;
    document.getElementById('color_boton_texto').value = estilos.color_boton_texto;
    document.getElementById('color_boton_hover').value = estilos.color_boton_hover;
    document.getElementById('color_campos').value = estilos.color_campos;
    document.getElementById('color_texto_campos').value = estilos.color_texto_campos;
    document.getElementById('tipo_fuente').value = estilos.tipo_fuente;
    document.getElementById('tamano_fuente').value = estilos.tamano_fuente;
    document.getElementById('borde_ancho').value = estilos.borde_ancho;
    document.getElementById('borde_estilo').value = estilos.borde_estilo;
    document.getElementById('borde_radio').value = estilos.borde_radio;
    
    // Actualizar displays
    document.getElementById('borde_ancho_valor').textContent = estilos.borde_ancho + 'px';
    document.getElementById('borde_radio_valor').textContent = estilos.borde_radio + 'px';
}

// Interceptar cambio de sección
document.querySelectorAll('.btn_seccion').forEach(function(btn) {
    btn.addEventListener('click', function() {
        seccion_activa = btn.getAttribute('data-seccion');
        document.getElementById('seccion_id').value = mapa_secciones[seccion_activa];
        document.getElementById('seccion_activa').value = seccion_activa;
        cargar_estilos_en_controles(seccion_activa);
    });
});
</script>
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
     -------------------------------------- FIN ITred Spa plantilla .PHP ----------------------------------------
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

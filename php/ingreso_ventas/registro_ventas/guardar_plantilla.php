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

/*  ------------------------------------------------------------------------------------------------------------
    ---------------------------------- INICIO ITred Spa guardar_plantilla .PHP ---------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// ------------------------
// -- INICIO CONEXION BD --
// ------------------------
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");

$mysqli->set_charset("utf8mb4");

// Rutas base
$BACK_URL  = "/php/inicio_sesion/superadmin/superadmin.php?pagina=plantilla";
$DOCROOT   = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

// Carpetas destino
$IMG_DIR   = $DOCROOT . "/imagenes/ingreso_ventas/registro_ventas/"; // logos
$CSS_FILE  = $DOCROOT . "/css/ingreso_ventas/registro_ventas/estilos_personalizados.css";

// Valores por defecto para "Restaurar"
$defaults = [
    'color_principal'    => '#94a3b8',
    'color_fondo'        => '#ffffff',
    'color_texto'        => '#000000',
    'color_borde'        => '#94a3b8',
    'color_boton'        => '#94a3b8',
    'color_boton_texto'  => '#ffffffff',
    'color_boton_hover'  => 'rgba(130, 135, 139, 1)',
    'color_campos'       => '#ffffff',
    'color_texto_campos' => '#ffffffff',
    'color_texto_titulos' => '#000000',
    'boton-borde-color' => 'rgba(0, 0, 0, 0)',
    // Default logo: use the shared ingreso_venta_img1 as the canonical restore image
    'logo'               => '/imagenes/ingreso_venta_img1.png',
    'fondo'              => '',
    'fondo_interior'     => '',
    'color_fondo_interior' => '#ffffff',
    'borde_estilo' => 'solid',
    'borde_ancho' => '2',
    'borde_color'  => '#333333',
    'borde_radio'  => '5',
    'secciones_bordes_activas' => '',
    'botones_especificos_activos' => '',
    'tipo_fuente' => 'arial',
    'tamano_fuente' => '14'
];

// Sanitizador HEX #RRGGBB
function hex_or_default($val, $fallback) {
    $val = trim((string)$val);
    return preg_match('/^#([A-Fa-f0-9]{6})$/', $val) ? $val : $fallback;
}


/* TITULO OBTENCION DE DATOS POST */

$isRestore = false;
if (isset($_POST['restaurar'])) {
    $data = $defaults;
    $isRestore = true;
} else {
    $data = [];
    $data['color_principal']    = hex_or_default($_POST['color_principal']    ?? '', $defaults['color_principal']);
    $data['color_fondo']        = hex_or_default($_POST['color_fondo']        ?? '', $defaults['color_fondo']);
    $data['color_texto']        = hex_or_default($_POST['color_texto']        ?? '', $defaults['color_texto']);
    $data['color_borde']        = hex_or_default($_POST['color_borde']        ?? '', $defaults['color_borde']);
    $data['color_boton']        = hex_or_default($_POST['color_boton']        ?? '', $defaults['color_boton']);
    $data['color_boton_texto']  = hex_or_default($_POST['color_boton_texto']  ?? '', $defaults['color_boton_texto']);
    $data['color_boton_hover']  = hex_or_default($_POST['color_boton_hover']  ?? '', $defaults['color_boton_hover']);
    $data['color_campos']       = hex_or_default($_POST['color_campos']       ?? '', $defaults['color_campos']);
    $data['color_texto_campos'] = hex_or_default($_POST['color_texto_campos'] ?? '', $defaults['color_texto_campos']);
    $data['color_fondo_interior'] = hex_or_default($_POST['color_fondo_interior'] ?? '', '#ffffff');
    $data['color_texto_titulos'] = hex_or_default($_POST['color_texto_titulos'] ?? '', $defaults['color_texto_titulos']);


    // Variable para tipo de fuente
    $data['tipo_fuente'] = $_POST['tipo_fuente'] ?? $defaults['tipo_fuente'];
    
    // Variable para tamaño de fuente
    $data['tamano_fuente'] = max(10, min(24, (int)($_POST['tamano_fuente'] ?? $defaults['tamano_fuente'])));

    // Variables para bordes de botones
    $data['borde_estilo'] = $_POST['borde_estilo'] ?? $defaults['borde_estilo'];
    $data['borde_ancho'] = max(0, min(10, (int)($_POST['borde_ancho'] ?? $defaults['borde_ancho'])));
    $data['borde_color'] = hex_or_default($_POST['borde_color'] ?? '', $defaults['borde_color']);
    $data['borde_radio'] = max(0, min(50, (int)($_POST['borde_radio'] ?? $defaults['borde_radio'])));

    // Secciones donde aplicar bordes (mantenemos compatibilidad hacia atrás)
    $seccionesSeleccionadas = $_POST['secciones_bordes'] ?? [];
    $seccionesValidas = ['ingreso_ventas', 'ingreso_datos', 'generar_qr', 'buscar', 'usuarios', 'plantilla', 'generar_respaldo'];
    $seccionesFiltradas = array_intersect($seccionesSeleccionadas, $seccionesValidas);
    $data['secciones_bordes_activas'] = implode(',', $seccionesFiltradas);

    // Botones específicos donde aplicar bordes
    $botonesSeleccionados = $_POST['botones_especificos'] ?? [];
    $botonesValidos = [
        // Menú principal
        'menu_ingreso_ventas','menu_ingreso_datos','menu_generar_qr','menu_buscar','menu_usuarios','menu_plantilla','menu_respaldo',
        // Ingreso Ventas
        'ingreso_ventas_escanear_camara', 'ingreso_ventas_escanear_pistola', 'ingreso_ventas_detener', 'ingreso_ventas_guardar',
        // Ingreso Datos
        'ingreso_datos_manual', 'ingreso_datos_descargar_plantilla_clientes', 'ingreso_datos_subir_plantilla_clientes', 
        'ingreso_datos_descargar_plantilla_ventas', 'ingreso_datos_subir_plantilla_ventas',
        // Generar QR
        'generar_qr_generar', 'generar_qr_imprimir', 'generar_qr_descargar',
        // Buscar
        'buscar_buscar', 'buscar_nueva_busqueda', 'buscar_descargar_excel', 'buscar_descargar_pdf', 'buscar_imprimir_pdf',
        // Usuarios
        'usuarios_buscar', 'usuarios_crear', 'usuarios_editar', 'usuarios_eliminar', 'usuarios_guardar_cambios',
        // Plantilla
        'plantilla_guardar', 'plantilla_restaurar',
        // Generar Respaldo
        'respaldo_excel_simple', 'respaldo_sql', 'respaldo_csv_completo', 'respaldo_descargar'
    ];
    $botonesFiltrados = array_intersect($botonesSeleccionados, $botonesValidos);
    $data['botones_especificos_activos'] = implode(',', $botonesFiltrados);

    // Validar estilo de borde
    $estilos_validos = ['solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset', 'none'];
    if (!in_array($data['borde_estilo'], $estilos_validos)) {
        $data['borde_estilo'] = $defaults['borde_estilo'];
    }

    // Logo actual como base
    $data['logo'] = $defaults['logo'];
    $resLogo = $mysqli->query("SELECT logo FROM personalizacion WHERE id=1");
    if ($resLogo && $row = $resLogo->fetch_assoc()) {
        $data['logo'] = $row['logo'] ?: $defaults['logo'];
    }

    // Fondo actual como base
    $data['fondo'] = $defaults['fondo'];
    $resFondo = $mysqli->query("SELECT fondo FROM personalizacion WHERE id=1");
    if ($resFondo && $rowF = $resFondo->fetch_assoc()) {
        $data['fondo'] = $rowF['fondo'] ?: $defaults['fondo'];
    }
    
    // Fondo interior actual como base
    $data['fondo_interior'] = $defaults['fondo_interior'];
    $resFondoInterior = $mysqli->query("SELECT fondo_interior FROM personalizacion WHERE id=1");
    if ($resFondoInterior && $rowFI = $resFondoInterior->fetch_assoc()) {
        $data['fondo_interior'] = $rowFI['fondo_interior'] ?: $defaults['fondo_interior'];
    }
}

// Si se solicitó restaurar, asegurar que el archivo por defecto exista en la carpeta destino
if ($isRestore) {
    if (!is_dir($IMG_DIR)) { @mkdir($IMG_DIR, 0775, true); }
    // No need to copy legacy menu_img1 files; we now use the canonical root logo
    // ensure the directory exists (in case other assets are saved there later)
    if (!is_dir($IMG_DIR)) { @mkdir($IMG_DIR, 0775, true); }
    // forzar el valor en $data -> use canonical ingreso_venta_img1.png (web-relative)
    $data['logo'] = '/imagenes/ingreso_venta_img1.png';
    $data['fondo'] = '';
}


/* TITULO SUBIDA DE ARCHIVO  */

// Si estamos en modo RESTAURAR, ignorar cualquier archivo subido en el formulario.
if (empty($isRestore) || !$isRestore) {
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($IMG_DIR)) { @mkdir($IMG_DIR, 0775, true); }

        // validar imagen por MIME real
        $info = getimagesize($_FILES['logo']['tmp_name']);
        if ($info !== false) {
            $ext = '.png';
            if ($info['mime'] === 'image/jpeg') $ext = '.jpg';
            if ($info['mime'] === 'image/webp') $ext = '.webp';
            if ($info['mime'] === 'image/png')  $ext = '.png';

            $nuevoLogo = 'logo_' . time() . '_' . mt_rand(1000,9999) . $ext;
            $destino   = $IMG_DIR . $nuevoLogo;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $destino)) {
                @chmod($destino, 0644);
                // guardar ruta web-relativa para consistencia con fondo/fondo_interior
                $data['logo'] = '/imagenes/ingreso_ventas/registro_ventas/' . $nuevoLogo; // sólo si se movió correctamente
            }
            // si falla el move, se conserva $data['logo'] (el anterior)
        }
    }
}

/* Manejo de imagen de fondo */
if (empty($isRestore) || !$isRestore) {
    if (!empty($_FILES['fondo']['name']) && $_FILES['fondo']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($IMG_DIR)) { @mkdir($IMG_DIR, 0775, true); }
        $info = getimagesize($_FILES['fondo']['tmp_name']);
        if ($info !== false) {
            $ext = '.png';
            if ($info['mime'] === 'image/jpeg') $ext = '.jpg';
            if ($info['mime'] === 'image/webp') $ext = '.webp';
            if ($info['mime'] === 'image/png')  $ext = '.png';

            $nuevoFondo = 'fondo_' . time() . '_' . mt_rand(1000,9999) . $ext;
            $destinoF   = $IMG_DIR . $nuevoFondo;

            if (move_uploaded_file($_FILES['fondo']['tmp_name'], $destinoF)) {
                @chmod($destinoF, 0644);
                // guardar ruta relativa a public root, por ejemplo: /imagenes/ingreso_ventas/registro_ventas/fondo_xxx.png
                $data['fondo'] = '/imagenes/ingreso_ventas/registro_ventas/' . $nuevoFondo;
            }
        }
    }
}

/* Manejo de imagen de fondo interior */
if (empty($isRestore) || !$isRestore) {
    if (!empty($_FILES['fondo_interior']['name']) && $_FILES['fondo_interior']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($IMG_DIR)) { @mkdir($IMG_DIR, 0775, true); }
        $info = getimagesize($_FILES['fondo_interior']['tmp_name']);
        if ($info !== false) {
            $ext = '.png';
            if ($info['mime'] === 'image/jpeg') $ext = '.jpg';
            if ($info['mime'] === 'image/webp') $ext = '.webp';
            if ($info['mime'] === 'image/png')  $ext = '.png';

            $nuevoFondoInterior = 'fondo_interior_' . time() . '_' . mt_rand(1000,9999) . $ext;
            $destinoFI   = $IMG_DIR . $nuevoFondoInterior;

            if (move_uploaded_file($_FILES['fondo_interior']['tmp_name'], $destinoFI)) {
                @chmod($destinoFI, 0644);
                // guardar ruta relativa a public root
                $data['fondo_interior'] = '/imagenes/ingreso_ventas/registro_ventas/' . $nuevoFondoInterior;
            }
        }
    }
}

/* TITULO ACTUALIZAR EN  BD */

// Debug: Verificar datos antes de guardar
error_log("DEBUG - Tipo de fuente: " . $data['tipo_fuente']);
error_log("DEBUG - Tamaño de fuente: " . $data['tamano_fuente']);

$sql = "UPDATE personalizacion SET
    color_principal=?,
    color_fondo=?,
    logo=?,
    color_texto=?,
    color_borde=?,
    color_boton=?,
    color_boton_texto=?,
    color_boton_hover=?,
    color_campos=?,
    color_texto_campos=?,
    fondo=?,
    fondo_interior=?,
    color_fondo_interior=?,
    tipo_fuente=?,
    tamano_fuente=?,
    borde_estilo=?,
    borde_ancho=?,
    borde_color=?,
    borde_radio=?,
    secciones_bordes_activas=?,
    botones_especificos_activos=?
WHERE id=1";

$ok = false;
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param(
        "sssssssssssssssssssss",
        $data['color_principal'],
        $data['color_fondo'],
        $data['logo'],
        $data['color_texto'],
        $data['color_borde'],
        $data['color_boton'],
        $data['color_boton_texto'],
        $data['color_boton_hover'],
        $data['color_campos'],
        $data['color_texto_campos'],
        $data['fondo'],
        $data['fondo_interior'],
        $data['color_fondo_interior'],
        $data['tipo_fuente'],
        $data['tamano_fuente'],
        $data['borde_estilo'],
        $data['borde_ancho'],
        $data['borde_color'],
        $data['borde_radio'],
        $data['secciones_bordes_activas'],
        $data['botones_especificos_activos']
    );
    $ok = $stmt->execute();
    $stmt->close();
}

/* TITULO ACTUALIZAR CSS DE VARIABLES (:root) */
$rootVars = ":root {
    --color-principal: {$data['color_principal']};
    --color-fondo: {$data['color_fondo']};
    --color-texto: {$data['color_texto']};
    --color-borde: {$data['color_borde']};
    --color-boton: {$data['color_boton']};
    --color-boton-texto: {$data['color_boton_texto']};
    --color-boton-hover: {$data['color_boton_hover']};
    --color-campos: {$data['color_campos']};
    --color-texto-campos: {$data['color_texto_campos']};
    --color-texto-titulos: {$data['color_texto_titulos']};
    --color-fondo-interior: {$data['color_fondo_interior']};
    --tipo-fuente: {$data['tipo_fuente']};
    --boton-borde-estilo: {$data['borde_estilo']};
    --boton-borde-grosor: {$data['borde_ancho']}px;
    --boton-borde-color: {$data['borde_color']};
    --boton-borde-radio: {$data['borde_radio']}px;
}";

$cssDir = dirname($CSS_FILE);
if (!is_dir($cssDir)) { @mkdir($cssDir, 0775, true); }

if (file_exists($CSS_FILE)) {
    $css = file_get_contents($CSS_FILE);
    $css = preg_replace('/:root\s*{[^}]*}/', $rootVars, $css);
    if ($css === null) $css = $rootVars;
} else {
    $css = $rootVars;
}
// Agregar o actualizar regla para el fondo general (aplicado al body)
$fondoGeneralBackground = "";
if (!empty($data['fondo'])) {
    // asegurar que la ruta sea adecuada para CSS (absolute desde la webroot)
    $url = $data['fondo'];
    $fondoGeneralBackground = "\nbody { background-image: url('{$url}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; }\n";
    $fondoGeneralBackground .= "\nmain, .main-content { background-color: transparent !important; }\n";
    $fondoGeneralBackground .= "\nh1 { background-image: none !important; }\n";
} else if (!empty($data['color_fondo']) && $data['color_fondo'] !== '#ffffff') {
    // Si no hay imagen pero hay color de fondo diferente al blanco
    $fondoGeneralBackground = "\nbody { background-color: {$data['color_fondo']} !important; }\n";
}

// Agregar o actualizar regla para el contenedor de contenido específico (aplicado al main)
$contenidoBackground = "";
if (!empty($data['fondo_interior'])) {
    $urlInterior = $data['fondo_interior'];
    $contenidoBackground = "\nmain { background-image: url('{$urlInterior}'); background-size: cover; background-position: center; background-repeat: no-repeat; border-radius: 8px; }\n";
    // También aplicar la imagen directamente a .opciones para compatibilidad
    // con la lógica de lectura en plantilla.php (busca .opciones { background-image: ... })
    // Usamos background-color en lugar de la shorthand `background:` para no
    // sobrescribir la `background-image` con el color.
    $contenidoBackground .= "\n.opciones { background-image: url('{$urlInterior}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-color: rgba(255, 255, 255, 0.85) !important; }\n";
} else if (!empty($data['color_fondo_interior']) && $data['color_fondo_interior'] !== '#ffffff') {
    // Si no hay imagen pero hay color de fondo interior diferente al blanco
    $contenidoBackground = "\nmain { background-color: {$data['color_fondo_interior']} !important; }\n";
    $contenidoBackground .= "\n.opciones { background: rgba(255, 255, 255, 0.85) !important; }\n";
}

// Remover reglas previas específicas
$css = preg_replace('/body\s*{[^}]*background-image:[^}]*}/i', '', $css);
$css = preg_replace('/body\s*{[^}]*background-color:[^}]*}/i', '', $css);
$css = preg_replace('/main\s*{[^}]*background-image:[^}]*}/i', '', $css);
$css = preg_replace('/main\s*{[^}]*background-color:[^}]*}/i', '', $css);
$css = preg_replace('/\.opciones\s*{[^}]*background-image:[^}]*}/i', '', $css);
$css = preg_replace('/h1\s*{[^}]*background-image:[^}]*}/i', '', $css);
$css = preg_replace('/\.navbar,\s*nav\s*{[^}]*background-image:[^}]*}/i', '', $css);

// Remover reglas previas de fuente
$css = preg_replace('/\/\* FUENTE PERSONALIZADA INICIO \*\/.*?\/\* FUENTE PERSONALIZADA FIN \*\//s', '', $css);

// Agregar fuente personalizada con fallbacks apropiados
$fontFamily = $data['tipo_fuente'];
$fontRule = "";

// Definir las reglas de fuente con fallbacks apropiados
switch($fontFamily) {
    case 'monospace':
        $fontRule = "monospace";
        break;
    case 'Arial':
        $fontRule = "Arial, Helvetica, sans-serif";
        break;
    case 'Helvetica':
        $fontRule = "Helvetica, Arial, sans-serif";
        break;
    case 'Times New Roman':
        $fontRule = "'Times New Roman', Times, serif";
        break;
    case 'Georgia':
        $fontRule = "Georgia, 'Times New Roman', serif";
        break;
    case 'Verdana':
        $fontRule = "Verdana, Geneva, sans-serif";
        break;
    case 'Courier New':
        $fontRule = "'Courier New', Courier, monospace";
        break;
    case 'Impact':
        $fontRule = "Impact, Arial Black, sans-serif";
        break;
    case 'Comic Sans MS':
        $fontRule = "'Comic Sans MS', cursive";
        break;
    case 'Trebuchet MS':
        $fontRule = "'Trebuchet MS', Helvetica, sans-serif";
        break;
    case 'Tahoma':
        $fontRule = "Tahoma, Geneva, sans-serif";
        break;
    case 'Gaming Pixel':
        $fontRule = "'Courier New', 'Lucida Console', Monaco, 'Bitstream Vera Sans Mono', monospace";
        break;
    case 'Persona Style':
        $fontRule = "'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif";
        break;
    default:
        $fontRule = $fontFamily . ", sans-serif";
}

$tamanoFuente = $data['tamano_fuente'];

$estiloFuente = "\n\n/* FUENTE PERSONALIZADA INICIO */\n";
$estiloFuente .= "body, html {\n";
$estiloFuente .= "    font-family: {$fontRule} !important;\n";
$estiloFuente .= "}\n";
$estiloFuente .= "body {\n";
$estiloFuente .= "    --font-type: '{$fontFamily}';\n";
$estiloFuente .= "    --font-size: '{$tamanoFuente}px';\n";
$estiloFuente .= "}\n";
$estiloFuente .= "body[data-font=\"{$fontFamily}\"] {\n";
$estiloFuente .= "    /* Estilos especiales activados */\n";
$estiloFuente .= "}\n";
$estiloFuente .= "/* Se evita forzar tamaño global para no afectar toda la página por accidente */\n";
$estiloFuente .= "input, select, textarea, button {\n";
$estiloFuente .= "    font-family: {$fontRule} !important;\n";
$estiloFuente .= "}\n";
$estiloFuente .= "/* FUENTE PERSONALIZADA FIN */\n\n";

// Remover reglas previas de botones personalizados
$css = preg_replace('/\/\* BOTONES PERSONALIZADOS INICIO \*\/.*?\/\* BOTONES PERSONALIZADOS FIN \*\//s', '', $css);
// Remover reglas previas de textos personalizados
$css = preg_replace('/\/\* TEXTOS PERSONALIZADOS INICIO \*\/.*?\/\* TEXTOS PERSONALIZADOS FIN \*\//s', '', $css);

// Agregar estilos personalizados para botones en secciones específicas
$estilosBotones = "\n\n/* BOTONES PERSONALIZADOS INICIO */\n";

// Cargar configuración por sección/botón (staging desde el editor)
$stagedRaw = $_POST['staged_bordes'] ?? '';
$stagedCfg = [];
if (!empty($stagedRaw)) {
    $tmp = json_decode($stagedRaw, true);
    if (is_array($tmp)) { $stagedCfg = $tmp; }
}

// Generar estilos para botones específicos
// 1) Si hay configuración staged (por sección), se usa en preferencia
if (!empty($stagedCfg)) {
    $estilosBotones .= "/* === BORDES POR SECCIÓN (STAGED) === */\n";
    // Mapear botones a selectores reales (mismo mapa usado abajo)
    $mapeoBotones = [
        // Menú principal (se aplica sólo tras Guardar; no en vivo)
        'menu_ingreso_ventas'   => '.btnMenu[data-page="ventas"]',
        'menu_ingreso_datos'    => '.btnMenu[data-page="ingreso_datos"]',
        'menu_generar_qr'       => '.btnMenu[data-page="generar_qr"]',
        'menu_buscar'           => '.btnMenu[data-page="buscar"]',
        'menu_usuarios'         => '.btnMenu[data-page="usuarios"]',
        'menu_plantilla'        => '.btnMenu[data-page="plantilla"]',
        'menu_respaldo'         => '.btnMenu[data-page="respaldo"]',
        'buscar_buscar' => '.recuadro.boton-buscar#btnBuscar',
        'buscar_nueva_busqueda' => 'button#btnLimpiarFiltros.nueva-busqueda',
        'buscar_descargar_excel' => 'button#btnExcel.boton_accion',
        'buscar_descargar_pdf' => 'form#formPDF button.boton_accion',
        'buscar_imprimir_pdf' => 'iframe#iframePDF + button.boton_accion',
        'ingreso_ventas_escanear_camara' => '#btnEscanear',
        'ingreso_ventas_escanear_pistola' => '#btnPistola',
        'ingreso_ventas_detener' => '#btnDetener',
        'ingreso_ventas_guardar' => '#btnGuardar',
        'ingreso_datos_manual' => 'button#imagen_clientes_manual',
        'ingreso_datos_descargar_plantilla_clientes' => '#exportar_datos_excel button.boton-sin-estilo',
        'ingreso_datos_subir_plantilla_clientes' => '#datos_excel button.boton-sin-estilo',
        'ingreso_datos_descargar_plantilla_ventas' => '#descargar_ventas button.boton-sin-estilo',
        'ingreso_datos_subir_plantilla_ventas' => '#ventas_excel button.boton-sin-estilo',
        'generar_qr_generar' => 'button.botonguardar[onclick*="generarQR"]',
        'generar_qr_imprimir' => 'button.botonguardar[onclick*="imprimirQR"]',
        'generar_qr_descargar' => 'a#descargarQR button.botonguardar',
        'usuarios_buscar' => 'form.busqueda-form button[type="submit"]',
        'usuarios_crear' => 'button#botonRegistrar',
        'usuarios_editar' => 'a.edit-link',
        'usuarios_eliminar' => 'button[name="eliminar"]',
        'usuarios_guardar_cambios' => 'button[type="submit"]:contains("Guardar Cambios")',
        'plantilla_guardar' => '.botonguardar',
        'plantilla_restaurar' => '.botonrestaurar',
        'respaldo_excel_simple' => '#btn-excel-simple',
        'respaldo_sql' => '#btn-sql',
        'respaldo_csv_completo' => '#btn-excel',
        'respaldo_descargar' => '#descargarBtn'
    ];

    foreach ($stagedCfg as $seccion => $porBoton) {
        if (!is_array($porBoton)) continue;
        foreach ($porBoton as $botonKey => $style) {
            if (!isset($mapeoBotones[$botonKey])) continue;
            $selector = $mapeoBotones[$botonKey];
            $bw = isset($style['borderWidth']) ? (int)$style['borderWidth'] : (int)$data['borde_ancho'];
            $bs = in_array(($style['borderStyle'] ?? $data['borde_estilo']), ['solid','dashed','dotted','double','groove','ridge','inset','outset','none'])
                ? ($style['borderStyle'] ?? $data['borde_estilo']) : $data['borde_estilo'];
            $bc = preg_match('/^#([A-Fa-f0-9]{6})$/', ($style['borderColor'] ?? $data['borde_color']))
                ? ($style['borderColor'] ?? $data['borde_color']) : $data['borde_color'];
            $br = isset($style['borderRadius']) ? (int)$style['borderRadius'] : (int)$data['borde_radio'];

            $estilosBotones .= "/* Sección: {$seccion} | Botón: {$botonKey} */\n";
            $estilosBotones .= "{$selector} {\n";
            $estilosBotones .= "    border: {$bw}px {$bs} {$bc} !important;\n";
            $estilosBotones .= "    border-radius: {$br}px !important;\n";
            $estilosBotones .= "    box-sizing: border-box !important;\n";
            $estilosBotones .= "}\n\n";

            $estilosBotones .= "{$selector}:hover {\n";
            $estilosBotones .= "    border-color: {$data['color_boton_hover']} !important;\n";
            $estilosBotones .= "}\n\n";
        }
    }

} elseif (!empty($data['botones_especificos_activos'])) {
    // 2) Backward compatibility: aplicar el estilo global a los botones marcados
    $botonesActivos = explode(',', $data['botones_especificos_activos']);
    $selectoresBotones = [];
    
    // Mapear botones específicos a sus selectores CSS reales
    $mapeoBotones = [
        // Buscar - Los botones reales de buscar.php con selectores específicos
        'buscar_buscar' => '.recuadro.boton-buscar#btnBuscar',
        'buscar_nueva_busqueda' => 'button#btnLimpiarFiltros.nueva-busqueda',
        'buscar_descargar_excel' => 'button#btnExcel.boton_accion',
        'buscar_descargar_pdf' => 'form#formPDF button.boton_accion',
        'buscar_imprimir_pdf' => 'iframe#iframePDF + button.boton_accion',
        
        // Ingreso Ventas - Los botones reales de ventas.php
        'ingreso_ventas_escanear_camara' => '#btnEscanear',
        'ingreso_ventas_escanear_pistola' => '#btnPistola',
        'ingreso_ventas_detener' => '#btnDetener',
        'ingreso_ventas_guardar' => '#btnGuardar',
        
        // Ingreso Datos - Los 5 botones reales de ingreso_datos.php
        'ingreso_datos_manual' => 'button#imagen_clientes_manual',
        'ingreso_datos_descargar_plantilla_clientes' => '#exportar_datos_excel button.boton-sin-estilo',
        'ingreso_datos_subir_plantilla_clientes' => '#datos_excel button.boton-sin-estilo',
        'ingreso_datos_descargar_plantilla_ventas' => '#descargar_ventas button.boton-sin-estilo',
        'ingreso_datos_subir_plantilla_ventas' => '#ventas_excel button.boton-sin-estilo',
        
        // Generar QR - Los botones reales de generar_qr.php
        'generar_qr_generar' => 'button.botonguardar[onclick*="generarQR"]',
        'generar_qr_imprimir' => 'button.botonguardar[onclick*="imprimirQR"]',
        'generar_qr_descargar' => 'a#descargarQR button.botonguardar',
        
        // Usuarios - Los botones reales de usuarios_contenido.php
        'usuarios_buscar' => 'form.busqueda-form button[type="submit"]',
        'usuarios_crear' => 'button#botonRegistrar',
        'usuarios_editar' => 'a.edit-link',
        'usuarios_eliminar' => 'button[name="eliminar"]',
        'usuarios_guardar_cambios' => 'button[type="submit"]:contains("Guardar Cambios")',
        
        // Plantilla - Los botones reales que funcionan
        'plantilla_guardar' => '.botonguardar',
        'plantilla_restaurar' => '.botonrestaurar',
        
        // Respaldo - Los botones reales de generar_respaldo.php
        'respaldo_excel_simple' => '#btn-excel-simple',
        'respaldo_sql' => '#btn-sql',
        'respaldo_csv_completo' => '#btn-excel',
        'respaldo_descargar' => '#descargarBtn'
    ];
    
    foreach ($botonesActivos as $boton) {
        if (isset($mapeoBotones[$boton])) {
            $selectoresBotones[] = $mapeoBotones[$boton];
        }
    }
    
    // SISTEMA ESPECÍFICO CON SELECTORES SIMPLES
    if (!empty($botonesActivos)) {
        $estilosBotones .= "/* === BORDES PARA BOTONES ESPECÍFICOS === */\n";
        $estilosBotones .= "/* Botones seleccionados: " . implode(', ', $botonesActivos) . " */\n\n";
        
        // Crear CSS específico para cada botón seleccionado
        foreach ($botonesActivos as $boton) {
            if (isset($mapeoBotones[$boton])) {
                $selector = $mapeoBotones[$boton];
                
                $estilosBotones .= "/* Botón: {$boton} */\n";
                $estilosBotones .= "{$selector} {\n";
                $estilosBotones .= "    border: {$data['borde_ancho']}px {$data['borde_estilo']} {$data['borde_color']} !important;\n";
                $estilosBotones .= "    border-radius: {$data['borde_radio']}px !important;\n";
                $estilosBotones .= "    box-sizing: border-box !important;\n";
                $estilosBotones .= "}\n\n";
                
                $estilosBotones .= "{$selector}:hover {\n";
                $estilosBotones .= "    border-color: {$data['color_boton_hover']} !important;\n";
                $estilosBotones .= "}\n\n";
            }
        }
        
        // ADICIONAL: Si hay "buscar_buscar" seleccionado, usar selector más amplio
        if (in_array('buscar_buscar', $botonesActivos)) {
            $estilosBotones .= "/* Selector adicional para botón Buscar */\n";
            $estilosBotones .= "div.boton-buscar, div[id='btnBuscar'], .recuadro.boton-buscar {\n";
            $estilosBotones .= "    border: {$data['borde_ancho']}px {$data['borde_estilo']} {$data['borde_color']} !important;\n";
            $estilosBotones .= "    border-radius: {$data['borde_radio']}px !important;\n";
            $estilosBotones .= "    box-sizing: border-box !important;\n";
            $estilosBotones .= "}\n\n";
        }
        

    }
} else {
    $estilosBotones .= "/* No hay botones específicos seleccionados para aplicar bordes personalizados */\n";
}

$estilosBotones .= "/* BOTONES PERSONALIZADOS FIN */\n\n";

// ===================== TEXTOS PERSONALIZADOS =====================
$estilosTextos = "\n\n/* TEXTOS PERSONALIZADOS INICIO */\n";
$stagedTextRaw = $_POST['staged_textos'] ?? '';
$stagedText = [];
if (!empty($stagedTextRaw)) {
    $tmpT = json_decode($stagedTextRaw, true);
    if (is_array($tmpT)) { $stagedText = $tmpT; }
}

// Mapa categorías -> selectores reales en la app
$mapCategorias = [
    'texto_h1' => 'h1',
    'texto_h2' => 'h2',
    'texto_menu' => '.btnMenu',
    'texto_cabeceras' => 'h3, h4, .section-title, .cabecera',
    'texto_campos' => 'label, input, select, textarea'
];

foreach ($stagedText as $cat => $style) {
    if (!isset($mapCategorias[$cat])) continue;
    $sel = $mapCategorias[$cat];
    $props = [];
    if (isset($style['color']) && preg_match('/^#([A-Fa-f0-9]{6})$/', $style['color'])) {
        $props[] = "color: {$style['color']} !important;";
    }
    if (isset($style['size'])) {
        $size = max(10, min(32, (int)$style['size']));
        $props[] = "font-size: {$size}px !important;";
    }
    if (isset($style['weight'])) {
        $weight = max(100, min(900, (int)$style['weight']));
        $props[] = "font-weight: {$weight} !important;";
    }
    // Nuevas propiedades: italic (boolean) y decoration (string) — mantener compatibilidad con antiguos flags
    if (isset($style['italic']) && $style['italic']) {
        $props[] = "font-style: italic !important;";
    }
    // Soporte legacy: 'underline' / 'strike' booleans
    $textDecs = [];
    if (!empty($style['decoration']) && is_string($style['decoration'])) {
        // Esperamos valores como: 'none', 'underline', 'line-through', 'underline line-through'
        $parts = preg_split('/\s+/', trim($style['decoration']));
        foreach ($parts as $p) {
            if (in_array($p, ['underline','line-through'])) $textDecs[] = $p;
        }
        if (in_array('none', $parts) && empty($textDecs)) {
            $textDecs = ['none'];
        }
    } else {
        if (isset($style['underline']) && $style['underline']) { $textDecs[] = 'underline'; }
        if (isset($style['strike']) && $style['strike']) { $textDecs[] = 'line-through'; }
    }
    if (!empty($textDecs)) {
        // Si la única opción es 'none', usarlo; si hay decoraciones múltiples, unir con espacio
        $props[] = "text-decoration: " . implode(' ', $textDecs) . " !important;";
    }
    if (!empty($props)) {
        $estilosTextos .= "/* Categoria: {$cat} */\n";
        $estilosTextos .= "{$sel} { " . implode(' ', $props) . " }\n\n";
    }
}

$estilosTextos .= "/* TEXTOS PERSONALIZADOS FIN */\n\n";

$css = trim($css) . "\n\n" . $fondoGeneralBackground . $contenidoBackground . $estiloFuente . $estilosBotones . $estilosTextos;

// DEBUG: Guardar información de debug
$debugInfo = "\n\n/* DEBUG INFO:\n";
$debugInfo .= "Botones activos: " . print_r($botonesActivos, true) . "\n";
$debugInfo .= "Selectores generados: " . print_r($selectoresBotones, true) . "\n";
$debugInfo .= "Estilos botones length: " . strlen($estilosBotones) . "\n";
$debugInfo .= "*/\n";

$css .= $debugInfo;

@file_put_contents($CSS_FILE, $css);


// Redireccion

$redirect = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $BACK_URL;
// Registrar actualización de plantilla en el log
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../../../inicio_sesion/seguridad/log_registros.php';
if (function_exists('app_log')) {
    app_log('update', 'personalizacion', 'Actualización de plantilla de registro_ventas', ['actor' => $_SESSION['username'] ?? '']);
}
header("Location: " . $redirect);
exit;

    ?>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->
     
    <?php
    // Cierra la conexión a la base de datos
    //$mysqli->close();
    ?>

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa guardar_plantilla .PHP ------------------------------------
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

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
   -------------------------------------- INICIO ITred Spa vista_previa .PHP -----------------------------------
   ------------------------------------------------------------------------------------------------------------- */

// Asegurar que tenemos las variables de configuración
if (!isset($config)) {
    $config = [];
} 
// Estos colores definen la apariencia general del sitio
$colorPrincipal    = $config['color_principal']     ?? '#94a3b8'; // Color principal del tema (gris azulado si no hay otro)
$colorFondo        = $config['color_fondo']         ?? '#ffffff';// Color de fondo de la página (blanco)
$colorTexto        = $config['color_texto']         ?? '#000000';// Color del texto normal (negro)
$colorBorde        = $config['color_borde']         ?? '#000000';// color del borde
$colorBoton        = $config['color_boton']         ?? '#94a3b8'; // Color de botones de previsualizacion
$colorBotonTexto   = $config['color_boton_texto']   ?? '#000000'; //color del texto del boton
$colorBotonHover   = $config['color_boton_hover']   ?? 'rgba(130, 135, 139, 1)'; //color de cuando se pasa por encima con el mause
$colorCampos       = $config['color_campos']        ?? '#ffffff'; // color de los campos
$colorTextoCampos  = $config['color_texto_campos']  ?? '#ffffff';// color de el texto de los campos
$colorTextoTitulos = $config['color_texto_titulos'] ?? '#000000'; //color de los titulos


$botonBordeEstilo  = $config['borde_estilo']  ?? 'solid'; // tipo de linea
$botonBordeGrosor  = $config['borde_ancho']  ?? '2'; // que tan grueso es el borde
$botonBordeColor   = $config['borde_color']   ?? 'rgba(0, 0, 0, 0)'; //color de el borde
$botonBordeRadio   = $config['borde_radio']   ?? '5';// redondeo de las esquinas

$tipoFuente = $config['tipo_fuente'] ?? 'arial'; // tipo de letra
$tamanoFuente = $config['tamano_fuente'] ?? '14'; //tamaño de la letra
$colorFondoInterior = $config['color_fondo_interior'] ?? '#ffffff'; //color de fondo de el interior

// Obtener URLs de imágenes si existen
$logoUrl = $logoUrl ?? '/imagenes/ingreso_venta_img1.png';
$fondoUrl = $fondoUrl ?? '';
$fondoInteriorUrl = $fondoInteriorUrl ?? '';
?>

<!--TITULO VISTA PREVIA -->
    <div id="vistaPrevia">
        <h3>Vista Previa</h3>
        <!-- Estado inicial vacío -->
        <div id="preview-empty" class="preview-empty">
            <div style="
                text-align: center;
                padding: 60px 20px;
                color: #888;
                background: #f8f9fa;
                border-radius: 8px;
                border: 2px dashed #ddd;
            ">
                <div style="font-size: 3rem; margin-bottom: 15px;">👁️</div>
                <h4 style="margin: 0 0 10px 0; color: #666;">Selecciona una sección para previsualizar</h4>
                <p style="margin: 0; font-size: 0.9rem;">Haz clic en "Fondos", "Textos" o "Botones" para ver la vista previa en tiempo real</p>
            </div>
        </div>

    <!--    <div class="iframe-overlay"></div>  capa que bloquea clics --> 
    <!-- Vista previa para Inicio Sesión -->
    <div id="preview-fondos" class="preview-section" style="display: none;">
        <div class="preview-container">
            <h4 class="preview-title">🎨 Vista Previa de Inicio Sesión</h4>
            <div class="preview-frame-wrapper">
                <div class="preview-frame-scale">

                    <iframe id="previewFrame" src="/ingreso_ventas.php" loading="lazy">
                    </iframe>
                    <div class="iframe-click-blocker"></div>
                </div>
            </div>
        </div>
    </div>


    <!-- Vista previa para Textos -->
    <div id="preview-textos" class="preview-section" style="display: none;">
        <div class="preview-container">
            <h4>📝 Vista Previa de Textos</h4>
            <div class="preview-demo" style="
                background: white;
                border-radius: 8px;
                border: 2px solid #ddd;
                padding: 20px;
            ">
                <div style="margin-bottom:16px;">
                    <h1 style="margin:0 0 6px 0;">Ejemplo H1</h1>
                    <h2 style="margin:0 0 10px 0;">Ejemplo H2</h2>
                </div>
                <div style="margin-bottom: 20px;">
                    <h5 style="
                        color: <?= htmlspecialchars($colorTexto) ?>;
                        font-family: <?= htmlspecialchars($tipoFuente) ?>;
                        font-size: <?= (int)$tamanoFuente + 4 ?>px;
                        margin: 0 0 10px 0;
                    ">Título Principal</h5>
                    <p style="
                        color: <?= htmlspecialchars($colorTexto) ?>;
                        font-family: <?= htmlspecialchars($tipoFuente) ?>;
                        font-size: <?= htmlspecialchars($tamanoFuente) ?>px;
                        margin: 0 0 15px 0;
                        line-height: 1.5;
                    ">
                        Este es un párrafo de ejemplo que muestra cómo se ve el texto con la configuración actual.
                    </p>
                </div>

                <div class="preview-menu" style="margin: 12px 0 16px; display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="button" class="btnMenu" style="padding:8px 14px; border:1px solid #ddd; border-radius:20px; background:#94a3b8;">Menú: Ventas</button>
                    <button type="button" class="btnMenu" style="padding:8px 14px; border:1px solid #ddd; border-radius:20px; background:#94a3b8;">Menú: Datos</button>
                    <button type="button" class="btnMenu" style="padding:8px 14px; border:1px solid #ddd; border-radius:20px; background:#94a3b8;">Menú: Buscar</button>
                </div>
                

                <div style="margin-bottom: 15px;">
                    <label style="
                        display: block;
                        margin-bottom: 5px;
                        color: <?= htmlspecialchars($colorTexto) ?>;
                        font-family: <?= htmlspecialchars($tipoFuente) ?>;
                        font-size: <?= htmlspecialchars($tamanoFuente) ?>px;
                        font-weight: bold;
                    ">Campo de texto:</label>
                    <input type="text" 
                           placeholder="Texto de ejemplo"
                           style="
                               background-color: <?= htmlspecialchars($colorCampos) ?>;
                               color: <?= htmlspecialchars($colorTextoCampos) ?>;
                               border: 1px solid <?= htmlspecialchars($colorBorde) ?>;
                               border-radius: 4px;
                               padding: 8px 12px;
                               font-family: <?= htmlspecialchars($tipoFuente) ?>;
                               font-size: <?= htmlspecialchars($tamanoFuente) ?>px;
                               width: 100%;
                               box-sizing: border-box;
                           ">
                </div>

                <div style="
                    background: rgba(0,123,255,0.1);
                    border-left: 4px solid #94a3b8;
                    padding: 10px;
                    border-radius: 4px;
                    font-size: 0.85rem;
                ">
                    <strong>Configuración actual:</strong><br>
                    Fuente: <?= htmlspecialchars($tipoFuente) ?> | 
                    Tamaño: <?= htmlspecialchars($tamanoFuente) ?>px | 
                    Color: <?= htmlspecialchars($colorTexto) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista previa para Botones -->
    <div id="preview-botones" class="preview-section" style="display: none;">
        <div class="preview-container">
            <h4>🔘 Vista Previa de Botones</h4>
            <div class="preview-demo" style="
                background: white;
                border-radius: 8px;
                border: 2px solid #ddd;
                padding: 20px;
            ">
                <div style="margin-bottom: 20px;">
                    <h5 style="margin: 0 0 15px 0; color: #333;">Ejemplos de Botones</h5>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                        <button style="
                            background-color: <?= htmlspecialchars($colorBoton) ?>;
                            color: <?= htmlspecialchars($colorBotonTexto) ?>;
                            border: 1px solid transparent;
                            border-radius: 4px;
                            padding: 10px 20px;
                            font-family: <?= htmlspecialchars($tipoFuente) ?>;
                            font-size: <?= htmlspecialchars($tamanoFuente) ?>px;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='rgba(130, 135, 139, 0.425)'"
                        onmouseout="this.style.backgroundColor='<?= htmlspecialchars($colorBoton) ?>'"
                        >� Guardar</button>
                        
                        <button style="
                            background-color: <?= htmlspecialchars($colorBoton) ?>;
                            color: <?= htmlspecialchars($colorBotonTexto) ?>;
                            border: 1px solid transparent;
                            border-radius: 4px;
                            padding: 10px 20px;
                            font-family: <?= htmlspecialchars($tipoFuente) ?>;
                            font-size: <?= htmlspecialchars($tamanoFuente) ?>px;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='rgba(130, 135, 139, 0.425)'"
                        onmouseout="this.style.backgroundColor='<?= htmlspecialchars($colorBoton) ?>'"
                        >� Cancelar</button>

                        <button style="
                            background-color: <?= htmlspecialchars($colorBoton) ?>;
                            color: <?= htmlspecialchars($colorBotonTexto) ?>;
                            border: 1px solid transparent;
                            border-radius: 4px;
                            padding: 10px 20px;
                            font-family: <?= htmlspecialchars($tipoFuente) ?>;
                            font-size: <?= htmlspecialchars($tamanoFuente) ?>px;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        "
                        onmouseover="this.style.backgroundColor='rgba(130, 135, 139, 0.425)'"
                        onmouseout="this.style.backgroundColor='<?= htmlspecialchars($colorBoton) ?>'"
                        >🔍 Buscar</button>
                    </div>
                </div>

                <div style="
                    background: rgba(40,167,69,0.1);
                    border-left: 4px solid #94a3b8;
                    padding: 10px;
                    border-radius: 4px;
                    font-size: 0.85rem;
                ">
                    <strong>Propiedades actuales:</strong><br>
                    Fondo: <?= htmlspecialchars($colorBoton) ?> | 
                    Texto: <?= htmlspecialchars($colorBotonTexto) ?> | 
                    Hover: <?= htmlspecialchars($colorBotonHover) ?><br>
                    Borde: <?= htmlspecialchars($botonBordeGrosor) ?>px <?= htmlspecialchars($botonBordeEstilo) ?> <?= htmlspecialchars($botonBordeColor) ?> | 
                    Radio: <?= htmlspecialchars($botonBordeRadio) ?>px
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script para manejar el cambio de tabs y mostrar vista previa correspondiente
document.addEventListener('DOMContentLoaded', function() {
    // Función para mostrar/ocultar secciones de vista previa
    function showPreviewSection(sectionName) {
        // Ocultar todas las secciones
        document.querySelectorAll('.preview-section').forEach(section => {
            section.style.display = 'none';
        });

        // Ocultar estado vacío
        const emptyState = document.getElementById('preview-empty');
        if (emptyState) {
            emptyState.style.display = 'none';
        }

        // Mostrar la sección correspondiente
        const targetSection = document.getElementById('preview-' + sectionName);
        if (targetSection) {
            targetSection.style.display = 'block';
        }
    }

    // Agregar listeners a los botones de tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            // Ocultar todas las secciones
            document.querySelectorAll('.preview-section').forEach(section => { section.style.display = 'none'; });
            // Mostrar solo la sección correspondiente
            if (target === 'tab-fondos') { showPreviewSection('fondos'); }
            else if (target === 'tab-textos') { showPreviewSection('textos'); }
            else if (target === 'tab-botones') { showPreviewSection('botones'); }
        });
    });

    // Al cargar, mostrar inmediatamente la sección que corresponde a la pestaña activa
    // para evitar mostrar otra sección primero. Si no hay .tab-btn.active, forzar 'fondos'.
    (function initPreviewFromActiveTab() {
        var activeBtn = document.querySelector('.tab-btn.active');
        var target = activeBtn ? activeBtn.getAttribute('data-target') : 'tab-fondos';
        if (target === 'tab-fondos') {
            showPreviewSection('fondos');
        } else if (target === 'tab-textos') {
            showPreviewSection('textos');
        } else if (target === 'tab-botones') {
            showPreviewSection('botones');
        } else {
            // fallback
            showPreviewSection('fondos');
        }
    })();
});

/*
 Sitio Web Creado por ITred Spa.
 Dirección: Guido Reni #4190
 Pedro Aguirre Cerda - Santiago - Chile
 contacto@itred.cl o itred.spa@gmail.com
 https://www.itred.cl
 Creado, Programado y Diseñado por ITred Spa.s
 BPPJ
*/

/* -------------------------------------------------------------------------------------------------------------
   ----------------------------------------- FIN ITred Spa vista_previa .PHP -----------------------------------
   ------------------------------------------------------------------------------------------------------------- */
</script>

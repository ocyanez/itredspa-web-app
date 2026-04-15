<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<?php // Fragmento: Editor de Textos (usar dentro de plantilla.php con variables ya preparadas)
if(!defined('EDITOR_TEXTO_ASSETS')): define('EDITOR_TEXTO_ASSETS', true); ?>
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/editor_texto.css?v=<?= time() ?>">
    <script defer src="/js/ingreso_ventas/registro_ventas/editor_texto.js?v=<?= time() ?>"></script>
<?php endif; ?>
    <div class="seccion-config">
        <div class="seccion-titulo">📝 Editor de Textos</div>
        <!-- Campo oculto para staging de estilos por categoría de texto -->
        <input type="hidden" name="staged_textos" id="staged_textos" value='<?= isset($staged_textos) ? htmlspecialchars($staged_textos, ENT_QUOTES, "UTF-8") : "{}" ?>'>
            
            <label>
                Color general:
                <input type="color" name="color_texto" id="colorTexto" value="<?= $colorTexto ?>">
                <span class="color" id="colorTextoPreview" style="background:<?= $colorTexto ?>">&nbsp;</span>
            </label>
            
            <label>
                Tipo de fuente: 
                <select name="tipo_fuente" id="tipoFuente" value="<?= $tipoFuente ?>">
                    <option value="monospace" <?= $tipoFuente == 'monospace' ? 'selected' : '' ?>>Monospace (Por defecto)</option>
                    <option value="Arial" <?= $tipoFuente == 'Arial' ? 'selected' : '' ?>>Arial</option>
                    <option value="Times New Roman" <?= $tipoFuente == 'Times New Roman' ? 'selected' : '' ?>>Times New Roman (Serif)</option>
                    <option value="Georgia" <?= $tipoFuente == 'Georgia' ? 'selected' : '' ?>>Georgia (Serif elegante)</option>
                    <option value="Verdana" <?= $tipoFuente == 'Verdana' ? 'selected' : '' ?>>Verdana (Redondeada)</option>
                    <option value="Courier New" <?= $tipoFuente == 'Courier New' ? 'selected' : '' ?>>Courier New (Máquina de escribir)</option>
                    <option value="Impact" <?= $tipoFuente == 'Impact' ? 'selected' : '' ?>>Impact (Negrita)</option>
                    <option value="Comic Sans MS" <?= $tipoFuente == 'Comic Sans MS' ? 'selected' : '' ?>>Comic Sans MS (Informal)</option>
                    <option value="Trebuchet MS" <?= $tipoFuente == 'Trebuchet MS' ? 'selected' : '' ?>>Trebuchet MS (Moderna)</option>
                    <option value="Tahoma" <?= $tipoFuente == 'Tahoma' ? 'selected' : '' ?>>Tahoma (Compacta)</option>
                    <option value="Gaming Pixel" <?= $tipoFuente == 'Gaming Pixel' ? 'selected' : '' ?>>Gaming Pixel (Retro)</option>
                    <option value="Persona Style" <?= $tipoFuente == 'Persona Style' ? 'selected' : '' ?>>Persona Style (Elegante)</option>
                </select>
            </label>

            <label>
                Tamaño de fuente:
                <select name="tamano_fuente" id="tamanoFuente" value="<?= $tamanoFuente ?? '14' ?>">
                    <option value="10" <?= ($tamanoFuente ?? '14') == '10' ? 'selected' : '' ?>>10px (Muy pequeño)</option>
                    <option value="12" <?= ($tamanoFuente ?? '14') == '12' ? 'selected' : '' ?>>12px (Pequeño)</option>
                    <option value="14" <?= ($tamanoFuente ?? '14') == '14' ? 'selected' : '' ?>>14px (Normal)</option>
                    <option value="16" <?= ($tamanoFuente ?? '14') == '16' ? 'selected' : '' ?>>16px (Grande)</option>
                    <option value="18" <?= ($tamanoFuente ?? '14') == '18' ? 'selected' : '' ?>>18px (Muy grande)</option>
                    <option value="20" <?= ($tamanoFuente ?? '14') == '20' ? 'selected' : '' ?>>20px (Extra grande)</option>
                    <option value="22" <?= ($tamanoFuente ?? '14') == '22' ? 'selected' : '' ?>>22px (Enorme)</option>
                    <option value="24" <?= ($tamanoFuente ?? '14') == '24' ? 'selected' : '' ?>>24px (Máximo)</option>
                </select>
            </label>

            <label>
                Peso de fuente:
                <select id="pesoFuente" name="peso_fuente">
                    <option value="400">Normal</option>
                    <option value="500">Medio</option>
                    <option value="600">Seminegrita</option>
                    <option value="700">Negrita</option>
                </select>
            </label>

            <div style="display:flex; gap:12px; align-items:center; margin-top:8px; flex-wrap:wrap;">
                <label style="display:flex; flex-direction:column; font-size:.95rem;">
                    Decoración:
                    <select id="decTextDecoration" name="dec_text_decoration" style="margin-top:6px;">
                        <option value="none">Ninguna</option>
                        <option value="underline">Subrayado</option>
                        <option value="line-through">Tachado</option>
                        <option value="underline line-through">Subrayado + Tachado</option>
                    </select>
                </label>
            </div>

            <hr style="margin:16px 0; border:none; border-top:1px solid #e5e7eb;">
            <div style="font-weight: 600; margin: 6px 0 10px;">🎯 Estilos por categoría</div>
            <p style="font-size: .85rem; color:#666; margin-top:-6px;">Elige arriba Color, Tamaño y Peso; luego aplica a cada categoría con los botones de abajo. Se guarda al presionar "Guardar".</p>

            <?php
            // Categorías disponibles para texto
            $categoriasTexto = [
                'texto_h1' => 'Títulos H1',
                'texto_h2' => 'Títulos H2',
                'texto_menu' => 'Botones de Menú',
                'texto_cabeceras' => 'Cabeceras (H3/H4 y secciones)',
                'texto_campos' => 'Campos (input/select/textarea/label)'
            ];
            $mapaVariablesTexto = [
                'texto_h1' => '--color-texto-titulos',
                'texto_h2' => '--color-texto-titulos',
                'texto_menu' => '--color-boton',
                'texto_cabeceras' => '--color-texto-campos',
                'texto_campos' => '--color-texto'
            ];
            ?>

            <div style="display:grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap: 10px;">
                <?php foreach($categoriasTexto as $catKey => $catLabel): 
                    $cssVar = $mapaVariablesTexto[$catKey] ?? '--color-texto';
                    ?>
                <div class="cat-texto" data-categoria="<?= htmlspecialchars($catKey, ENT_QUOTES, 'UTF-8') ?>" style="padding:10px; border:1px solid #e5e7eb; border-radius:6px; background:#fff;">
                    <div style="font-weight:600; margin-bottom:8px; color:#111827;"><?= $catLabel ?></div>
                    <div class="acciones-seccion" style="display:flex; gap:8px;">
                        <button type="button" class="btn-aplicar-texto" data-categoria="<?= htmlspecialchars($catKey, ENT_QUOTES, 'UTF-8') ?>" style="padding:6px 10px; background: var(--color-principal); color:#fff; border:none; border-radius:4px; cursor:pointer;">Aplicar sección</button>
                        <button type="button" class="btn-limpiar-texto" data-categoria="<?= htmlspecialchars($catKey, ENT_QUOTES, 'UTF-8') ?>" style="padding:6px 10px; background:#f0f0f0; color:#333; border:1px solid #ddd; border-radius:4px; cursor:pointer;">Limpiar sección</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
    </div>

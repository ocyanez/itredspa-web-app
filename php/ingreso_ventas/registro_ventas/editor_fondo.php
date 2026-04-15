<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->


<!-- ---------------------------------------------------------------------------------------------------------------
     ------------------------------------- INICIO ITred Spa editor_fondo .PHP --------------------------------------
     --------------------------------------------------------------------------------------------------------------- -->


<?php // Fragmento: Editor de Fondos / Imágenes (se asume variables ya definidas en plantilla.php )
// Carga de CSS y JS propio si no ha sido cargado aún (permite uso standalone o por include múltiple)
if(!defined('EDITOR_FONDO_ASSETS')): define('EDITOR_FONDO_ASSETS', true); ?>
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/editor_fondo.css?v=<?= time() ?>">
    <script defer src="/js/ingreso_ventas/registro_ventas/editor_fondo.js?v=<?= time() ?>"></script>
<?php endif; ?>

            <!-- Sección de Colores de Fondo -->
            <div class="seccion-config" id="editor-fondos">
                <div class="seccion-titulo">🎨 Editor de Fondos</div>
                
                <div class="grupo-colores-fondos">
                    <label>
                        General: 
                        <input type="color" name="color_fondo" id="colorFondo" value="<?= $colorFondo ?>">
                        <span class="color" id="colorFondoPreview" style="background:<?= $colorFondo ?>">&nbsp;</span>
                    </label>

                    <label>
                        Contenido:
                        <input type="color" name="color_fondo_interior" id="colorFondoInterior" value="<?= htmlspecialchars($colorFondoInterior) ?>">
                        <span class="color" id="colorFondoInteriorPreview" style="background:<?= htmlspecialchars($colorFondoInterior) ?>">&nbsp;</span>
                    </label>

                    <label>
                        Cabeceras:
                        <input type="color" name="color_principal" id="colorPrincipal" value="<?= $colorPrincipal ?>">
                        <span class="color" id="colorPrincipalPreview" style="background:<?= $colorPrincipal ?>">&nbsp;</span>
                    </label>

                    <label>
                        Campos:
                        <input type="color" name="color_campos" id="colorCampos" value="<?= $colorCampos ?>">
                        <span class="color" id="colorCamposPreview" style="background:<?= $colorCampos ?>">&nbsp;</span>
                    </label>
                </div>
                <div class="seccion-config" id="editor-imagenes">
                <div class="seccion-titulo">🖼️ Imágenes</div>
                
                <h1 for="logo">Logo:</h1>
                <input type="file" name="logo" id="logo" accept="image/*">
                <div id="logoPreviewContainer">
                    <img id="logoPreview" alt="Vista previa de logo" src="<?= htmlspecialchars($logoUrl) ?>">
                </div>

                <h1 for="fondo">Fondo general:</h1>
                <input type="file" name="fondo" id="fondo" accept="image/*">
                <div id="fondoPreviewContainer">
                    <?php if (!empty($fondoUrl)): ?>
                        <img id="fondoPreview" alt="Vista previa fondo" src="<?= htmlspecialchars($fondoUrl) ?>">
                    <?php else: ?>
                        <div id="fondoPreview" style="color:#666; font-size:0.8em;">Sin imagen</div>
                    <?php endif; ?>
                </div>

                <h1 for="fondo_interior">Fondo contenido:</h1>
                <input type="file" name="fondo_interior" id="fondo_interior" accept="image/*">
                <div id="fondoInteriorPreviewContainer">
                    <?php if (!empty($fondoInteriorUrl)): ?>
                        <img id="fondoInteriorPreview" alt="Vista previa fondo interior" src="<?= htmlspecialchars($fondoInteriorUrl) ?>">
                    <?php else: ?>
                        <div id="fondoInteriorPreview" style="color:#666; font-size:0.8em;">Sin imagen</div>
                    <?php endif; ?>
                </div>
            </div>
            </div>
          <!-- Fin fragmento fondos -->


<!-- ---------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa editor_fondo .PHP ----------------------------------------
     --------------------------------------------------------------------------------------------------------------- -->

<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->
<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<?php // Fragmento: Editor de Botones (usar dentro de plantilla.php)
if(!defined('EDITOR_BOTON_ASSETS')): define('EDITOR_BOTON_ASSETS', true); ?>
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/editor_boton.css?v=<?= time() ?>">
    <script defer src="/js/ingreso_ventas/registro_ventas/editor_boton.js?v=<?= time() ?>"></script>
<?php endif; ?>
<div class="seccion-config">
    <div class="seccion-titulo">🔲 Editor de Botones</div>
    <!-- Campo oculto para guardar la configuración por botón/sección antes de Guardar -->
    <input type="hidden" name="staged_bordes" id="staged_bordes" value='<?= isset($staged_bordes) ? htmlspecialchars($staged_bordes, ENT_QUOTES, "UTF-8") : "{}" ?>'>

    <!-- Contenedor de notificaciones (toasts) -->
    <div id="toast-container" aria-live="polite" aria-atomic="true" style="
        position: fixed; top:0; left:0; width:100%; z-index:2147483647; display:flex; flex-direction:column; align-items:center; gap:12px;
        padding: max(14px, env(safe-area-inset-top)) 12px 12px; pointer-events:none;">
    </div>
    <style>
        .toast-msg { 
            min-width:300px; max-width:640px; width:fit-content; padding:18px 24px 16px; border-radius:14px; color:#fff; font-size:1rem;
            font-family: system-ui, Arial, sans-serif; box-shadow:0 10px 34px -6px rgba(0,0,0,.4),0 3px 10px rgba(0,0,0,.25);
            opacity:0; transform: translateY(-18px) scale(.95); display:flex; align-items:flex-start; gap:18px; line-height:1.35;
            position:relative; overflow:hidden; transition:opacity .45s cubic-bezier(.4,0,.2,1), transform .55s cubic-bezier(.4,0,.2,1);
            backdrop-filter: blur(10px) saturate(170%); -webkit-backdrop-filter: blur(10px) saturate(170%); pointer-events:auto;
            border:1px solid rgba(255,255,255,0.22);
        }
        @media (prefers-reduced-motion:reduce){ .toast-msg { transition:none; transform:none!important; } }
        .toast-msg.show { opacity:1; transform:translateY(0) scale(1); }
        .toast-msg.hide { opacity:0; transform:translateY(-8px) scale(.94); }
        .toast-msg.info { background:linear-gradient(135deg,#2196f3 0%,#1565c0 100%); }
        .toast-msg.warn { background:linear-gradient(135deg,#ff9800 0%,#ef6c00 100%); }
        .toast-msg.error { background:linear-gradient(135deg,#e53935 0%,#b71c1c 100%); }
        .toast-msg.success { background:linear-gradient(135deg,#43a047 0%,#1b5e20 100%); }
        .toast-msg:before { content:""; position:absolute; inset:0; background:radial-gradient(circle at 30% 20%,rgba(255,255,255,.25),rgba(255,255,255,0)); opacity:.25; pointer-events:none; }
        .toast-close { cursor:pointer; font-weight:600; padding:0 6px; line-height:1; font-size:16px; }
        .toast-progress { position:absolute; left:0; bottom:0; height:4px; background:rgba(255,255,255,.78); width:100%; animation:toast-progress linear forwards; }
        @keyframes toast-progress { from { width:100%; } to { width:0; } }
    </style>
            
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
        <label>
            Color del borde:
            <input type="color" name="borde_color" id="botonBordeColor" value="<?= $botonBordeColor ?>">
            <span class="color" id="botonBordeColorPreview" style="background:<?= $botonBordeColor ?>">&nbsp;</span>
        </label>

        <label>
            Estilo del borde: 
            <select name="borde_estilo" id="botonBordeEstilo">
                <option value="solid" <?= $botonBordeEstilo === 'solid' ? 'selected' : '' ?>>Sólido</option>
                <option value="dashed" <?= $botonBordeEstilo === 'dashed' ? 'selected' : '' ?>>Discontinuo</option>
                <option value="dotted" <?= $botonBordeEstilo === 'dotted' ? 'selected' : '' ?>>Punteado</option>
                <option value="double" <?= $botonBordeEstilo === 'double' ? 'selected' : '' ?>>Doble</option>
                <option value="groove" <?= $botonBordeEstilo === 'groove' ? 'selected' : '' ?>>Acanalado</option>
                <option value="ridge" <?= $botonBordeEstilo === 'ridge' ? 'selected' : '' ?>>Cesta</option>
                <option value="inset" <?= $botonBordeEstilo === 'inset' ? 'selected' : '' ?>>Hundido</option>
                <option value="outset" <?= $botonBordeEstilo === 'outset' ? 'selected' : '' ?>>Elevado</option>
                <option value="none" <?= $botonBordeEstilo === 'none' ? 'selected' : '' ?>>Sin borde</option>
            </select>
        </label>

        <label>
            Grosor del borde:
            <input type="range" name="borde_ancho" id="botonBordeGrosor" 
                    min="0" max="10" step="1" value="<?= $botonBordeGrosor ?>">
            <span id="botonBordeGrosorValue"><?= $botonBordeGrosor ?>px</span>
        </label>

        <label>
            Radio del borde:
            <input type="range" name="borde_radio" id="botonBordeRadio" 
                    min="0" max="50" step="1" value="<?= $botonBordeRadio ?>">
            <span id="botonBordeRadioValue"><?= $botonBordeRadio ?>px</span>
        </label>
        <label style="font-weight: bold; margin-bottom: 15px; display: block; color: var(--color-principal); font-size: 1rem;">
            🎯 Seleccionar Botones Específicos por Sección:
        </label>
        <p style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">
            Elige exactamente qué botones de cada sección quieres que tengan los bordes personalizados:
        </p>
                
        <?php
        $botonesActivos = explode(',', $botonesEspecificos);
        $botonesActivos = array_filter($botonesActivos);
                
        $botonesDisponibles = [
            'Menú' => [
                'menu_ingreso_ventas' => 'Menú: Ingreso Ventas',
                'menu_ingreso_datos' => 'Menú: Ingreso Datos',
                'menu_generar_qr' => 'Menú: Generar QR',
                'menu_buscar' => 'Menú: Buscar',
                'menu_usuarios' => 'Menú: Usuarios',
                'menu_plantilla' => 'Menú: Plantilla',
                'menu_respaldo' => 'Menú: Generar Respaldo'
            ],
            'Ingreso Ventas' => [
                'ingreso_ventas_escanear_camara' => 'Escanear con Cámara',
                'ingreso_ventas_escanear_pistola' => 'Escanear con Pistola',
                'ingreso_ventas_detener' => 'Detener',
                'ingreso_ventas_guardar' => 'Guardar Datos'
            ],
            'Ingreso Datos' => [
                'ingreso_datos_manual' => 'Ingreso clientes manual',
                'ingreso_datos_descargar_plantilla_clientes' => 'Descargar plantilla de ingreso',
                'ingreso_datos_subir_plantilla_clientes' => 'Subir plantilla de ingreso',
                'ingreso_datos_descargar_plantilla_ventas' => 'Descargar plantilla de ventas',
                'ingreso_datos_subir_plantilla_ventas' => 'Subir plantilla de ventas'
            ],
            'Generar QR' => [
                'generar_qr_generar' => 'Generar QR',
                'generar_qr_imprimir' => 'Imprimir QR',
                'generar_qr_descargar' => 'Descargar QR'
            ],
            'Buscar' => [
                'buscar_buscar' => 'Buscar',
                'buscar_nueva_busqueda' => 'Nueva Búsqueda',
                'buscar_descargar_excel' => 'Descargar Excel',
                'buscar_descargar_pdf' => 'Descargar PDF',
                'buscar_imprimir_pdf' => 'Imprimir PDF'
            ],
            'Usuarios' => [
                'usuarios_buscar' => 'Buscar',
                'usuarios_crear' => 'Registrarse',
                'usuarios_editar' => 'Editar Usuario',
                'usuarios_eliminar' => 'Eliminar Usuario',
            ],
            'Plantilla' => [
                'plantilla_guardar' => 'Guardar',
                'plantilla_restaurar' => 'Restaurar'
            ],
            'Generar Respaldo' => [
                'respaldo_excel_simple' => 'Excel Simple',
                'respaldo_sql' => 'SQL',
                'respaldo_csv_completo' => 'CSV Completo (.zip)',
                'respaldo_descargar' => 'Descargar'
            ]
        ];
                
        foreach ($botonesDisponibles as $seccion => $botones): ?>
            <div class="seccion-botones" data-seccion="<?= htmlspecialchars($seccion, ENT_QUOTES, 'UTF-8') ?>" style="margin-bottom: 12px; background: white; border-radius: 6px; border: 1px solid #e0e0e0; overflow: hidden;">
                <div class="seccion-header" style="display:flex; justify-content:space-between; align-items:center; padding:8px 10px; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button type="button" class="toggle-seccion" aria-expanded="false" style="width:28px; height:28px; border-radius:6px; border:1px solid #ddd; background-color: #94A3B8 !important; cursor:pointer;">
                            +
                        </button>
                        <span style="background: var(--color-principal); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight:700;">
                            <?= $seccion ?>
                        </span>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <button type="button" class="btn-aplicar-seccion" data-seccion="<?= htmlspecialchars($seccion, ENT_QUOTES, 'UTF-8') ?>" 
                                style="padding:6px 10px; background: var(--color-principal); color:white; border:none; border-radius:4px; cursor:pointer; font-size:0.85rem;">
                            Aplicar
                        </button>
                        <button type="button" class="btn-limpiar-seccion" data-seccion="<?= htmlspecialchars($seccion, ENT_QUOTES, 'UTF-8') ?>" 
                                style="padding:6px 10px; background:#f0f0f0; color:#333; border:1px solid #ddd; border-radius:4px; cursor:pointer; font-size:0.85rem;">
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="seccion-body" style="padding:10px; display:none;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <?php foreach ($botones as $key => $nombre): 
                            $checked = in_array($key, $botonesActivos) ? 'checked' : '';
                        ?>
                            <label style="display: flex; align-items: center; gap: 6px; margin: 3px 0; font-size: 0.85rem; cursor: pointer; padding: 6px; border-radius: 6px; transition: background 0.2s;">
                                <input type="checkbox" name="botones_especificos[]" value="<?= $key ?>" <?= $checked ?> 
                                        style="margin: 0; accent-color: var(--color-principal);">
                                <span><?= $nombre ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
                
        <div style="margin-top: 15px; padding: 10px; background: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107; border-radius: 4px;">
            <small style="color: #856404; font-weight: 500;">
                💡 <strong>Tip:</strong> Solo los botones marcados serán afectados por los estilos de borde. 
                Los demás mantendrán su apariencia original.
            </small>
        </div>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: rgba(var(--color-principal-rgb, 0,86,179), 0.05); border-radius: 8px; border: 1px solid var(--color-borde);">
        
    </div>
</div>
<!-- Fin fragmento botones -->
<script>
// Toggle simple for collapsible sections in editor_boton
(function(){
    document.querySelectorAll('.seccion-botones').forEach(function(wrapper){
        var btn = wrapper.querySelector('.toggle-seccion');
        var body = wrapper.querySelector('.seccion-body');
        if (!btn || !body) return;
        btn.addEventListener('click', function(){
            var expanded = btn.getAttribute('aria-expanded') === 'true';
            if (expanded) {
                body.style.display = 'none';
                btn.setAttribute('aria-expanded','false');
                btn.textContent = '+';
            } else {
                body.style.display = 'block';
                btn.setAttribute('aria-expanded','true');
                btn.textContent = '−';
            }
        });
    });
})();
</script>

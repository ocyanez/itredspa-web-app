/* Editor Botones JS - sincroniza sliders y preview */
(function(){
  const rangoGrosor = document.getElementById('botonBordeGrosor');
  const valorGrosor = document.getElementById('botonBordeGrosorValue');
  const rangoRadio = document.getElementById('botonBordeRadio');
  const valorRadio = document.getElementById('botonBordeRadioValue');
  const color = document.getElementById('botonBordeColor');
  const estilo = document.getElementById('botonBordeEstilo');
  const stagedInput = document.getElementById('staged_bordes');
  // Reubicar contenedor de toasts al body para asegurar overlay global
  (function ensureToastContainerRoot(){
    const tc = document.getElementById('toast-container');
    if (tc && tc.parentElement !== document.body) {
      document.body.appendChild(tc);
    }
  })();

  function syncRange(rango, label, suf='px'){
    if(!rango || !label) return;
    const upd = ()=> label.textContent = rango.value + suf;
    rango.addEventListener('input', upd);
    rango.addEventListener('change', upd);
    upd();
  }
  syncRange(rangoGrosor, valorGrosor);
  syncRange(rangoRadio, valorRadio);

  // Vista previa dinámica EN VIVO: se actualiza en cada cambio de controles, sin necesidad de marcar checkboxes
  function livePreviewUpdate(){
    const vistaPrevia = document.getElementById('vistaPrevia');
    if(!vistaPrevia) return;
    showRelevantPreview('botonBorde');
    const bw = rangoGrosor ? rangoGrosor.value : 2;
    const bs = estilo ? estilo.value : 'solid';
    const bc = color ? color.value : '#000';
    const br = rangoRadio ? rangoRadio.value : 5;

    // Botones dentro de la vista previa
    const botones = vistaPrevia.querySelectorAll('button');
    botones.forEach(btn => {
      btn.style.border = `${bw}px ${bs} ${bc}`;
      btn.style.borderRadius = `${br}px`;
    });
  }

  // Función para mostrar la vista previa relevante (copiada de plantilla.js)
  function showRelevantPreview(id) {
    // Ocultar estado vacío
    const emptyState = document.getElementById('preview-empty');
    if (emptyState) {
      emptyState.style.display = 'none';
    }

    // Ocultar todas las secciones
    document.querySelectorAll('.preview-section').forEach(section => {
      section.style.display = 'none';
    });

    // Mostrar la sección relevante
    let targetSection = null;
    
    if (id.includes('Fondo') || id.includes('colorFondo')) {
      targetSection = document.getElementById('preview-fondos');
    } else if (id.includes('Texto') || id.includes('colorTexto') || id === 'tipoFuente' || id === 'tamanoFuente') {
      targetSection = document.getElementById('preview-textos');
    } else if (id.includes('Boton') || id.includes('colorBoton') || id.includes('Borde')) {
      targetSection = document.getElementById('preview-botones');
    }

    if (targetSection) {
      targetSection.style.display = 'block';
      console.log('Mostrando vista previa:', targetSection.id);
    }
  }  // Función de mapeo idéntica a la de plantilla.js
  function getSelectorsForButtonType(buttonType) {
    const selectorMap = {
      // Ingreso Ventas
  // Menú
  // Preferir data-page si existe; fallback nth-of-type
  'menu_ingreso_ventas': '.btnMenu[data-page="ventas"], .btnMenu:nth-of-type(1)',
  'menu_ingreso_datos': '.btnMenu[data-page="ingreso_datos"], .btnMenu:nth-of-type(2)',
  'menu_generar_qr': '.btnMenu[data-page="generar_qr"], .btnMenu:nth-of-type(3)',
  'menu_buscar': '.btnMenu[data-page="buscar"], .btnMenu:nth-of-type(4)',
  'menu_usuarios': '.btnMenu[data-page="usuarios"], .btnMenu:nth-of-type(5)',
  'menu_plantilla': '.btnMenu[data-page="plantilla"], .btnMenu:nth-of-type(6)',
  'menu_respaldo': '.btnMenu[data-page="respaldo"], .btnMenu:nth-of-type(7)',

  // Ingreso Ventas
      'ingreso_ventas_escanear_camara': '.boton_escanear_camara, .btn-camera',
      'ingreso_ventas_escanear_pistola': '.boton_escanear_pistola, .btn-scanner',
      'ingreso_ventas_detener': '.boton_detener, .btn-stop',
      'ingreso_ventas_guardar': '.boton_guardar_ventas, .btn-save',
      
      // Ingreso Datos
      'ingreso_datos_manual': '.boton_ingreso_manual, .btn-manual',
      'ingreso_datos_descargar_plantilla_clientes': '.boton_descargar_clientes, .btn-download-clients',
      'ingreso_datos_subir_plantilla_clientes': '.boton_subir_clientes, .btn-upload-clients',
      'ingreso_datos_descargar_plantilla_ventas': '.boton_descargar_ventas, .btn-download-sales',
      'ingreso_datos_subir_plantilla_ventas': '.boton_subir_ventas, .btn-upload-sales',
      
      // Generar QR
      'generar_qr_generar': '.boton_generar_qr, .btn-generate-qr',
      'generar_qr_imprimir': '.boton_imprimir_qr, .btn-print-qr',
      'generar_qr_descargar': '.boton_descargar_qr, .btn-download-qr',
      
      // Buscar
      'buscar_buscar': '.boton_buscar, .btn-search',
      'buscar_nueva_busqueda': '.boton_nueva_busqueda, .btn-new-search',
      'buscar_descargar_excel': '.boton_descargar_excel, .btn-download-excel',
      'buscar_descargar_pdf': '.boton_descargar_pdf, .btn-download-pdf',
      'buscar_imprimir_pdf': '.boton_imprimir_pdf, .btn-print-pdf',
      
      // Usuarios
      'usuarios_buscar': '.boton_buscar_usuarios, .btn-search-users',
      'usuarios_crear': '.boton_crear_usuario, .btn-create-user',
      'usuarios_editar': '.boton_editar_usuario, .btn-edit-user',
      'usuarios_eliminar': '.boton_eliminar_usuario, .btn-delete-user',
      
      // Plantilla
      'plantilla_guardar': '.botonguardar, .btn-save-template',
      'plantilla_restaurar': '.botonrestaurar, .btn-restore-template',
      
      // Generar Respaldo
      'respaldo_excel_simple': '.boton_excel_simple, .btn-excel-simple',
      'respaldo_sql': '.boton_sql, .btn-sql',
      'respaldo_csv_completo': '.boton_csv_completo, .btn-csv-complete',
      'respaldo_descargar': '.boton_descargar_respaldo, .btn-download-backup'
    };
    
    return selectorMap[buttonType] || null;
  }
  
  // SOLO aplicar preview cuando hay checkboxes seleccionados - no automáticamente
  const checkboxes = document.querySelectorAll("input[name='botones_especificos[]']");
  // Antes: dependía de checkboxes; ahora mantenemos soporte si el usuario marca/deselecciona
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', livePreviewUpdate);
  });

  // Helpers para staging por sección
  function getStaged() {
    try { return stagedInput ? JSON.parse(stagedInput.value || '{}') : {}; } catch(e) { return {}; }
  }
  function setStaged(obj) {
    if (stagedInput) stagedInput.value = JSON.stringify(obj);
  }
  function styleFromControls() {
    return {
      borderColor: color ? color.value : '#000000',
      borderStyle: estilo ? estilo.value : 'solid',
      borderWidth: rangoGrosor ? parseInt(rangoGrosor.value,10) : 2,
      borderRadius: rangoRadio ? parseInt(rangoRadio.value,10) : 4
    };
  }
  function applyStyleToButtons(buttonSelectors, style, root=document) {
    const { borderWidth, borderStyle, borderColor, borderRadius } = style;
    const nodes = root.querySelectorAll(buttonSelectors);
    nodes.forEach(btn => {
      btn.style.border = `${borderWidth}px ${borderStyle} ${borderColor}`;
      btn.style.borderRadius = `${borderRadius}px`;
    });
  }
  function clearStyleFromButtons(buttonSelectors, root=document) {
    const nodes = root.querySelectorAll(buttonSelectors);
    nodes.forEach(btn => {
      btn.style.removeProperty('border');
      btn.style.removeProperty('border-radius');
    });
  }

  // ================= TOAST / NOTIFICACIONES =================
  function showToast(message, type='info', ttl=4000) {
    const container = document.getElementById('toast-container');
    if (!container) { console.warn('No toast container'); return; }

    // Limitar máximo de toasts visibles
    const maxToasts = 5;
    const existing = container.querySelectorAll('.toast-msg');
    if (existing.length >= maxToasts) {
      // remover el más antiguo (primero)
      existing[0].classList.add('hide');
      setTimeout(()=> existing[0].remove(), 350);
    }

    const el = document.createElement('div');
    el.className = `toast-msg ${type}`;
    el.setAttribute('role','status');
    el.innerHTML = `
      <span style="flex:1;">${message}</span>
      <span class="toast-close" title="Cerrar">×</span>
      <span class="toast-progress" style="animation-duration:${ttl}ms"></span>
    `;
    container.appendChild(el);
    requestAnimationFrame(()=> el.classList.add('show'));

    const close = ()=>{
      el.classList.add('hide');
      setTimeout(()=> el.remove(), 400);
    };
    el.querySelector('.toast-close').addEventListener('click', close);
    setTimeout(close, ttl + 50);
  }
  // Exponer globalmente para otros editores
  if (typeof window !== 'undefined') {
    window.showToast = showToast;
  }

  // Acción: Aplicar por sección
  document.querySelectorAll('.btn-aplicar-seccion').forEach(btn => {
    btn.addEventListener('click', () => {
      const seccion = btn.getAttribute('data-seccion');
      const contenedor = btn.closest('.seccion-botones');
  // status-seccion eliminado del HTML; mantener código robusto sin él
  const status = null;
      const checks = contenedor ? contenedor.querySelectorAll("input[name='botones_especificos[]']:checked") : [];
      if (!checks || checks.length === 0) {
        if (status) { status.textContent = 'Sin botones seleccionados.'; status.style.color = '#b08800'; }
        showToast('Selecciona al menos un botón de la sección ' + seccion + ' antes de aplicar.', 'warn');
        return;
      }
      const st = getStaged();
      st[seccion] = st[seccion] || {};
      const style = styleFromControls();
      // Guardar estilo por cada botón marcado dentro de la sección
      checks.forEach(cb => {
        st[seccion][cb.value] = style;
      });
      setStaged(st);

      // Vista previa: SOLO aplicar estilos inmediatos si no son botones del menú
      const vistaPrevia = document.getElementById('vistaPrevia');
      checks.forEach(cb => {
        if (cb.value.startsWith('menu_')) return; // No aplicar en vivo a menú; se verá tras Guardar
        const sel = getSelectorsForButtonType(cb.value);
        if (sel && vistaPrevia) {
          applyStyleToButtons(sel, style, vistaPrevia);
        }
      });

  // status eliminado (antes mostraba 'Aplicado.')
      showToast('Bordes aplicados a sección ' + seccion + ' (' + checks.length + ' botón(es)).', 'success');
    });
  });

  // Acción: Limpiar por sección (solo staging y vista previa)
  document.querySelectorAll('.btn-limpiar-seccion').forEach(btn => {
    btn.addEventListener('click', () => {
      const seccion = btn.getAttribute('data-seccion');
      const contenedor = btn.closest('.seccion-botones');
  // status-seccion eliminado
  const status = null;
      const checks = contenedor ? contenedor.querySelectorAll("input[name='botones_especificos[]']") : [];
      const st = getStaged();
      if (st[seccion]) delete st[seccion];
      setStaged(st);

      // Limpiar solo vista previa (menú no se aplica en vivo)
      const vistaPrevia = document.getElementById('vistaPrevia');
      checks.forEach(cb => {
        if (cb.value.startsWith('menu_')) return; // nada que limpiar en vivo
        const sel = getSelectorsForButtonType(cb.value);
        if (sel && vistaPrevia) clearStyleFromButtons(sel, vistaPrevia);
      });
  // status eliminado (antes mostraba 'Limpia.')
      showToast('Se limpió la configuración temporal de la sección ' + seccion + '.', 'info');
    });
  });

  // Limpiar estilos de borde al cargar la página
  document.addEventListener('DOMContentLoaded', function() {
    console.log('=== LIMPIANDO ESTILOS AL CARGAR ===');
    
    // VERIFICAR SI HAY CHECKBOXES MARCADOS AL CARGAR
    const checkboxesMarcados = document.querySelectorAll("input[name='botones_especificos[]']:checked");
    console.log('Checkboxes marcados al cargar la página:', checkboxesMarcados.length);
    checkboxesMarcados.forEach(cb => console.log('Checkbox marcado:', cb.value));
    
    const vistaPrevia = document.getElementById('vistaPrevia');
    if (vistaPrevia) {
      const botonesVista = vistaPrevia.querySelectorAll('button');
      console.log('Botones encontrados para limpiar al cargar:', botonesVista.length);
      botonesVista.forEach(btn => {
        if(btn){
          btn.style.removeProperty('border');
          btn.style.removeProperty('border-width');
          btn.style.removeProperty('border-style');
          btn.style.removeProperty('border-color');
          btn.style.removeProperty('border-radius');
        }
      });
    }
    
    // FORZAR LIMPIEZA DE VARIABLES CSS AL CARGAR
    console.log('Limpiando variables CSS --boton-borde-* al cargar');
    document.documentElement.style.removeProperty('--boton-borde-color');
    document.documentElement.style.removeProperty('--boton-borde-estilo');
    document.documentElement.style.removeProperty('--boton-borde-grosor');
    document.documentElement.style.removeProperty('--boton-borde-radio');
  });
  
  // Ahora sí: vista previa en vivo en cada cambio de control
  ['input','change'].forEach(evt => {
    if(color) color.addEventListener(evt, livePreviewUpdate);
    if(estilo) estilo.addEventListener(evt, livePreviewUpdate);
    if(rangoGrosor) rangoGrosor.addEventListener(evt, livePreviewUpdate);
    if(rangoRadio) rangoRadio.addEventListener(evt, livePreviewUpdate);
  });
  // Inicializar preview al cargar
  livePreviewUpdate();
})();

/* Editor Textos JS - previews y cambios de texto SOLO en vista previa, con staging por categoría */
(function(){
  // Asegurar soporte de Toasts aunque no esté cargado editor_boton.js
  (function ensureToasts(){
    if (!document.getElementById('toast-container')) {
      const tc = document.createElement('div');
      tc.id = 'toast-container';
      tc.setAttribute('aria-live','polite');
      tc.setAttribute('aria-atomic','true');
      tc.style.position = 'fixed';
      tc.style.top = '20px';
      tc.style.right = '20px';
      tc.style.zIndex = '2147483647';
      tc.style.display = 'flex';
      tc.style.flexDirection = 'column';
      tc.style.gap = '8px';
      tc.style.pointerEvents = 'none';
      document.body.appendChild(tc);
    }
    if (typeof window !== 'undefined' && typeof window.showToast !== 'function') {
      window.showToast = function(message, type='info', ttl=3000){
        const container = document.getElementById('toast-container');
        if (!container) return alert(message);
        // limitar cantidad
        const existing = container.querySelectorAll('.toast-msg');
        if (existing.length >= 5) { existing[0].remove(); }
        const el = document.createElement('div');
        el.className = 'toast-msg ' + type;
        el.style.pointerEvents = 'auto';
        el.style.background = type==='success' ? '#16a34a' : type==='warn' ? '#b45309' : type==='error' ? '#dc2626' : '#374151';
        el.style.color = '#fff';
        el.style.padding = '10px 12px';
        el.style.borderRadius = '8px';
        el.style.boxShadow = '0 6px 20px rgba(0,0,0,.25)';
        el.style.display = 'flex';
        el.style.alignItems = 'center';
        el.style.gap = '12px';
        el.innerHTML = `<span style="flex:1">${message}</span>`;
        const btn = document.createElement('button');
        btn.textContent = '×';
        btn.title = 'Cerrar';
        btn.style.background = 'transparent';
        btn.style.border = 'none';
        btn.style.color = 'inherit';
        btn.style.fontSize = '18px';
        btn.style.cursor = 'pointer';
        el.appendChild(btn);
        container.appendChild(el);
        const close = ()=>{ try { el.remove(); } catch(_){} };
        btn.addEventListener('click', close);
        setTimeout(close, ttl);
      };
    }
  })();
  const colorMap = [
    ['colorTexto','colorTextoPreview']
  ];
  colorMap.forEach(([inputId, previewId]) => {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if(input && preview){
      const update = () => {
        preview.style.background = input.value;
        // También actualizar la vista previa principal
        updateVistaPrevia(inputId, input.value);
      };
      input.addEventListener('input', update);
      input.addEventListener('change', update);
    }
  });

  const tipoFuente = document.getElementById('tipoFuente');
  const tamanoFuente = document.getElementById('tamanoFuente');
  const pesoFuente = document.getElementById('pesoFuente');
  const stagedInput = document.getElementById('staged_textos');
  const decTextDecoration = document.getElementById('decTextDecoration');

  // Controles de estilo por categoría
  // Ahora el peso viene del control superior
  
  if(tipoFuente || tamanoFuente || pesoFuente){
    const applyPreview = () => {
      // Aplicar únicamente a la vista previa, NO a la plantilla
      const vistaPrevia = document.getElementById('vistaPrevia');
      if(vistaPrevia){
        // Mostrar sección textos por claridad
        showRelevantPreview('colorTexto');
        // Para evitar que reglas globales con !important anulen la vista previa,
        // aplicamos en el contenedor con prioridad 'important'.
        const target = vistaPrevia.querySelector('#preview-textos .preview-demo') || vistaPrevia;
        if(tipoFuente) {
          // Aplicar a contenedor y a todos los hijos para sobrepasar reglas específicas (ej. button, input)
          target.style.setProperty('font-family', tipoFuente.value, 'important');
          const elementos = target.querySelectorAll('*:not(script):not(style)');
          elementos.forEach(el => el.style.setProperty('font-family', tipoFuente.value, 'important'));
        }
        if(tamanoFuente) {
          // Base en contenedor; la categoría puede ajustar tamaños específicos si se desea
          target.style.setProperty('font-size', tamanoFuente.value + 'px', 'important');
        }
        // Nota: No aplicamos peso global en la vista previa para evitar afectar títulos u otras categorías.
        // El peso se aplica solo al usar "Aplicar sección" por categoría.
      }
    };
    ['input','change'].forEach(evt => {
      if(tipoFuente) tipoFuente.addEventListener(evt, applyPreview);
      if(tamanoFuente) tamanoFuente.addEventListener(evt, applyPreview);
      if(pesoFuente) pesoFuente.addEventListener(evt, applyPreview);
    });
    applyPreview();
  }

  // Función para actualizar SOLO la vista previa principal
  function updateVistaPrevia(inputId, value) {
    const vistaPrevia = document.getElementById('vistaPrevia');
    if (!vistaPrevia) return;

    // Mostrar la vista previa de textos automáticamente
    showRelevantPreview(inputId);

    if (inputId === 'colorTexto') {
      const textElements = vistaPrevia.querySelectorAll('h5, p, label, strong');
      textElements.forEach(el => el.style.color = value);
    }
    // Se eliminó el control de color en campos y bordes en el editor de texto
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
  }

  // Utils staging
  function getStaged(){
    try { return stagedInput ? JSON.parse(stagedInput.value || '{}') : {}; } catch(e){ return {}; }
  }
  function setStaged(obj){ if(stagedInput) stagedInput.value = JSON.stringify(obj); }

  // Vista previa por categoría (no afecta menú real)
  function applyCategoryPreview(catKey){
    const vistaPrevia = document.getElementById('vistaPrevia');
    if(!vistaPrevia) return;
    // Mostrar pestaña de Textos para que el usuario vea el efecto
    showRelevantPreview('colorTexto');
  const color = document.getElementById('colorTexto')?.value || '#000';
  const size = tamanoFuente ? parseInt(tamanoFuente.value,10) : 14;
  const weight = pesoFuente ? parseInt(pesoFuente.value,10) : 400;

    // Mapear catKey a selectores dentro de la vista previa
    const map = {
      'texto_h1': 'h1',
      'texto_h2': 'h2',
      'texto_menu': '.btnMenu, .preview-menu button, .preview-menu .btn',
      'texto_cabeceras': 'h3, h4, .section-title, .cabecera',
      'texto_campos': 'label, input, select, textarea'
    };
    const sel = map[catKey];
    if(!sel) return;
    const nodes = vistaPrevia.querySelectorAll(sel);
    if (!nodes || nodes.length === 0) {
      if (window.showToast) window.showToast('No hay elementos de "' + catKey.replace('texto_','') + '" en esta vista previa.', 'warn');
      return;
    }
    nodes.forEach(el => {
      // Usar prioridad 'important' para prevalecer sobre reglas globales
      el.style.setProperty('color', color, 'important');
      el.style.setProperty('font-size', size + 'px', 'important');
      el.style.setProperty('font-weight', String(weight), 'important');
      if(decTextDecoration) el.style.setProperty('text-decoration', decTextDecoration.value === 'none' ? 'none' : decTextDecoration.value, 'important');
    });
  }

  // Eventos: aplicar/limpiar por categoría (staging)
  document.querySelectorAll('.btn-aplicar-texto').forEach(btn => {
    btn.addEventListener('click', () => {
      const cat = btn.getAttribute('data-categoria');
      // Tomar valores actuales de los controles superiores
      const currColor = document.getElementById('colorTexto')?.value || '#000';
      const currSize = tamanoFuente ? parseInt(tamanoFuente.value,10) : 14;
      const currWeight = pesoFuente ? parseInt(pesoFuente.value,10) : 400;
      const st = getStaged();
      st[cat] = { 
        color: currColor,
        size: currSize,
        weight: currWeight,
        decoration: decTextDecoration ? decTextDecoration.value : 'none'
      };
      setStaged(st);
      applyCategoryPreview(cat);
      // Feedback con toast
  if(window.showToast){ window.showToast('Estilo aplicado a ' + cat.replace('texto_','') + '.', 'success'); }
    });
  });

  document.querySelectorAll('.btn-limpiar-texto').forEach(btn => {
    btn.addEventListener('click', () => {
      const cat = btn.getAttribute('data-categoria');
      const st = getStaged();
      if(st[cat]) delete st[cat];
      setStaged(st);
      // Limpiar estilos de la vista previa
      applyCategoryPreview(cat); // re-aplica con valores actuales; para limpiar del todo:
      const vistaPrevia = document.getElementById('vistaPrevia');
      if(vistaPrevia){
        const map = {
          'texto_h1': 'h1',
          'texto_h2': 'h2',
          'texto_menu': '.btnMenu, .preview-menu .btn',
          'texto_cabeceras': 'h3, h4, .section-title, .cabecera',
          'texto_campos': 'label, input, select, textarea'
        };
        const sel = map[cat];
        if(sel){
          vistaPrevia.querySelectorAll(sel).forEach(el => {
            el.style.removeProperty('color');
            el.style.removeProperty('font-size');
            el.style.removeProperty('font-weight');
            if(decFontStyle) el.style.removeProperty('font-style');
            if(decTextDecoration) el.style.removeProperty('text-decoration');
          });
        }
      }
  if(window.showToast){ window.showToast('Estilo limpiado de ' + cat.replace('texto_','') + '.', 'info'); }
    });
  });

})();

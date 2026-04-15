/* Editor Fondos JS - actualiza previews de colores e imágenes SOLO en vista previa */
(function(){
  const map = [
    ['colorFondo','colorFondoPreview'],
    ['colorFondoInterior','colorFondoInteriorPreview'], 
    ['colorPrincipal','colorPrincipalPreview'],
    ['colorCampos','colorCamposPreview']
  ];
  map.forEach(([inputId,previewId])=>{
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if(input && preview){
      // Actualizar solo el pequeño cuadrado de color preview
      input.addEventListener('input', ()=>{ 
        preview.style.background = input.value; 
        // Actualizar la vista previa principal en vivo (sin esperar blur)
        try { updateVistaPrevia(inputId, input.value); } catch(e) { /* silent */ }
      });
      input.addEventListener('change', ()=>{ 
        preview.style.background = input.value; 
        // También actualizar la vista previa principal si existe (redundante pero seguro)
        updateVistaPrevia(inputId, input.value);
      });
    }
  });

  // Función para actualizar SOLO la vista previa principal
  function updateVistaPrevia(inputId, value) {
    const vistaPrevia = document.getElementById('vistaPrevia');
    if (!vistaPrevia) return;

    // Normalizar inputId que puede venir como name (p. ej. color_principal) a una clave tipo id
    const normalize = (s) => (s || '').toString().replace(/_/g, '').toLowerCase();
    const normId = normalize(inputId);
    // Mostrar la vista previa relevante automáticamente
    showRelevantPreview(normId);

    if (normId === 'colorfondo') {
      const fondoElements = vistaPrevia.querySelectorAll('.preview-demo');
      fondoElements.forEach(el => el.style.backgroundColor = value);
    }
    if (normId === 'colorfondointerior') {
      const fondoInteriorElements = vistaPrevia.querySelectorAll('.preview-demo > div');
      fondoInteriorElements.forEach(el => el.style.backgroundColor = value);
    }
    if (normId === 'colorcampos') {
      const campos = vistaPrevia.querySelectorAll('input[type="text"]');
      campos.forEach(el => el.style.backgroundColor = value);
      // También actualizar campo de tabla de ejemplo si existe
      const tableField = vistaPrevia.querySelector('.preview-table-field');
      if (tableField) {
        tableField.style.background = value;
        // intentar obtener color de texto de input correspondiente
        const colorTextoCamposInput = document.getElementById('colorTextoCampos') || document.querySelector('input[name="color_texto_campos"]');
        const colorTextoCampos = colorTextoCamposInput ? colorTextoCamposInput.value : null;
        if (colorTextoCampos) tableField.style.color = colorTextoCampos;
        tableField.textContent = `Campo de formulario (fondo: ${value}) — { ejemplo }`;
      }
    }
    
    if (normId === 'colorprincipal') {
      const header = vistaPrevia.querySelector('.preview-table-header');
      if (header) {
        header.style.background = value;
        // obtener color de texto principal si existe
        const colorTextoInput = document.querySelector('input[name="color_texto"]') || null;
        const colorTexto = colorTextoInput ? colorTextoInput.value : '#ffffff';
        header.style.color = colorTexto;
        header.textContent = `Cabecera de ejemplo — ${value}`;
      }
    }
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
    // Normalizar a minúsculas y sin guiones bajos para comparar con normId
    const key = (id || '').toString().replace(/_/g, '').toLowerCase();

    // Triggers for fondos (incluye también cambios en colorPrincipal / colorCampos)
    if (key.includes('fondo') || key === 'fondo' || key === 'fondointerior' || key.includes('colorprincipal') || key.includes('colorcampos')) {
      targetSection = document.getElementById('preview-fondos');
    }
    // Triggers for textos (incluye colortexto, tipo/tamano fuente)
    else if (
      key.includes('texto') || key.includes('colortexto') || key.includes('colortextocampos') || key.includes('tipofuente') || key.includes('tamano')
    ) {
      targetSection = document.getElementById('preview-textos');
    }
    // Triggers for botones
    else if (key.includes('boton') || key.includes('colorboton') || key.includes('borde')) {
      targetSection = document.getElementById('preview-botones');
    }

    if (targetSection) {
      targetSection.style.display = 'block';
      console.log('Mostrando vista previa:', targetSection.id);
    }
  }

  function handleFile(inputId,imgId){
    const input = document.getElementById(inputId);
    const img = document.getElementById(imgId);
    if(!input) return;
    input.addEventListener('change', e => {
      const file = e.target.files && e.target.files[0];
      if(!file) return;
      const reader = new FileReader();
      reader.onload = ev => {
        if(img){
          if(img.tagName === 'IMG') { img.src = ev.target.result; }
          else { img.innerHTML = ''; const im = document.createElement('img'); im.src = ev.target.result; im.style.maxWidth='140px'; im.style.maxHeight='90px'; im.style.objectFit='contain'; img.appendChild(im); }
        }
      };
      reader.readAsDataURL(file);
    });
  }
  handleFile('logo','logoPreview');
  handleFile('fondo','fondoPreview');
  handleFile('fondo_interior','fondoInteriorPreview');
})();

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa editar_perfil .JS --------------------------------------
    --------------------------------------------------------------------------------------------------------------- */



// TITULO BODY

  // SIN CODIGO

// TITULO CONTAINER PRINCIPAL

  // SIN CODIGO
    
// TITULO FORMULARIO DE EDICIÓN

  // Función para validar las contraseñas ingresadas en el formulario de edición
  function validarContraseñas() {
    // obtiene el campo de contraseña
    var passwordEl = document.getElementById("password");
    // obtiene el campo de confirmación
    var confirmEl  = document.getElementById("confirm_password");
    // valor de la contraseña
    var password = passwordEl ? passwordEl.value : '';
    // valor de la confirmación
    var confirm_password = confirmEl ? confirmEl.value : '';

    // contenedor para mostrar error de contraseña
    var passwordError = document.getElementById("password-error");
    // contenedor para mostrar error de confirmación
    var confirmPasswordError = document.getElementById("confirm-password-error");

     // mensaje de error para contraseña
    var errorMessage = "";
    // mensaje de error para confirmación
    var confirmErrorMessage = "";

    // validaciones individuales usando expresiones regulares
    var hasUpperCase = /[A-Z]/.test(password);
    var hasLowerCase = /[a-z]/.test(password);
    var hasNumber = /\d/.test(password);
    var hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(password);

    // si la contraseña es muy corta o no cumple con los requisitos, mostrar mensaje
    if (password.length > 0 && (password.length < 8 || !(hasUpperCase && hasLowerCase && hasNumber && hasSpecial))) {
        errorMessage = "La contraseña debe tener mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número y un carácter especial.";
    }

    // si las contraseñas no coinciden, mostrar mensaje
    if (password.length > 0 && password !== confirm_password) {
        confirmErrorMessage = "Las contraseñas no coinciden.";
    }

    // mostrar los mensajes de error en pantalla si existen los contenedores
    if (passwordError) passwordError.textContent = errorMessage;
    if (confirmPasswordError) confirmPasswordError.textContent = confirmErrorMessage;

    // devuelve true si no hay errores (y si no se ingresó nueva contraseña)
    if (password.length === 0) return true;
    return errorMessage === "" && confirmErrorMessage === "";
  }


   // Insert, validación en tiempo real para editar perfil (prefijo ep_ para evitar colisiones) ---
 function ep_setFieldError(el, msg) {
   if (!el) return;
   const name = el.id || el.name || ('f' + Math.random().toString(36).slice(2));
   // busca el contenedor de error
   let err = document.getElementById(name + '-error');
   // si no existe, lo crea y lo inserta después del campo
   if (!err) {
     err = document.createElement('div');
     err.id = name + '-error';
     err.className = 'error_mensaje field-error';
     err.style.color = 'red';
     err.style.fontSize = '0.9em';
     err.style.marginTop = '4px';
     el.insertAdjacentElement('afterend', err);
   }
   // muestra el mensaje de error
   err.textContent = msg || '';
 }

 // limpia el mensaje de error del campo indicado
 function ep_clearFieldError(el) {
   if (!el) return;
   const name = el.id || el.name;
   const err = name ? document.getElementById(name + '-error') : null;
   if (err) err.textContent = '';
 }

 // valida si el email tiene formato correcto
 function ep_validarEmail(email) {
   return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((email||'').trim());
 }

 // valida si el teléfono tiene entre 7 y 11 dígitos, opcionalmente con +
 function ep_validarTelefono(tel) {
   return /^\+?[0-9]{7,11}$/.test((tel||'').trim());
 }

 // valida si la dirección tiene al menos 3 caracteres y contiene algún número
 function ep_validarDireccion(dir) {
   if (!dir) return false;
   const v = dir.trim();
   return v.length >= 3 && /\d+/.test(v);
 }

 
  // valida si el rut chileno es correcto (con cálculo de dígito verificador)
  function validarRUT(rut) {
    if (!rut && rut !== 0) return false;
    // normalizar: quitar puntos, guiones y espacios, y pasar a mayúscula
    const r = String(rut).replace(/\./g, '').replace(/-/g, '').replace(/\s+/g, '').toUpperCase();
    // mínimo 2 caracteres (1 cifra + dígito verificador)
    if (!/^\d{1,9}[0-9K]$/.test(r)) return false;
    const cuerpo = r.slice(0, -1);
    const dv = r.slice(-1);

    // calcular dígito verificador
    let suma = 0;
    let multiplicador = 2;
    for (let i = cuerpo.length - 1; i >= 0; i--) {
      suma += parseInt(cuerpo.charAt(i), 10) * multiplicador;
      multiplicador = (multiplicador === 7) ? 2 : multiplicador + 1;
    }
    const resto = suma % 11;
    const dvCalculado = 11 - resto;
    let dvEsperado;
    if (dvCalculado === 11) dvEsperado = '0';
    else if (dvCalculado === 10) dvEsperado = 'K';
    else dvEsperado = String(dvCalculado);

    // compara el dígito esperado con el ingresado
    return dvEsperado === dv;
  }

  // envoltorio seguro para validar rut (evita errores si hay excepciones)
  function ep_validarRutWrapper(rut) {
  try { return validarRUT(rut); } catch (e) { return false; }
}


  // valida un campo según su nombre y muestra errores si no cumple las reglas
 function ep_validateFieldByName(name, el, opts = { strict: false }) {
  // si no hay elemento, no hay nada que validar
   if (!el) return true;
   const v = (el.value || '').trim();
   switch (name) {
     case 'nombre':
     case 'apellido':
      // si está vacío y es validación estricta, mostrar error
       if (v === '') { if (opts.strict) ep_setFieldError(el, 'Requerido.'); return false; }
       // si no cumple con letras y espacios (2 a 50 caracteres), mostrar error
       if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}$/.test(v)) { ep_setFieldError(el, 'Solo letras (2-50).'); return false; }
       ep_clearFieldError(el); return true;
     case 'username':
       if (v === '') { if (opts.strict) ep_setFieldError(el, 'Requerido.'); return false; }
       if (v.length < 3) { ep_setFieldError(el, 'Mínimo 3 caracteres.'); return false; }
       ep_clearFieldError(el); return true;
     case 'correo':
     case 'email':
       if (v === '') { if (opts.strict) ep_setFieldError(el, 'Requerido.'); return false; }
       if (!ep_validarEmail(v)) { ep_setFieldError(el, 'Correo inválido.'); return false; }
       ep_clearFieldError(el); return true;
     case 'telefono':
       if (v === '') { ep_clearFieldError(el); return false; }
       if (!/^\+?[0-9]*$/.test(v)) { ep_setFieldError(el, 'Sólo números o + al inicio.'); return false; }
       if (opts.strict && !ep_validarTelefono(v)) { ep_setFieldError(el, 'Teléfono inválido (7-11 dígitos, opcional +).'); return false; }
       ep_clearFieldError(el); return true;
     case 'direccion':
       if (v === '') { if (opts.strict) ep_setFieldError(el, 'Requerido.'); return false; }
       if (!ep_validarDireccion(v)) { ep_setFieldError(el, 'Dirección debe incluir número y al menos 3 caracteres.'); return false; }
       ep_clearFieldError(el); return true;
     case 'rut':
       if (v === '') { if (opts.strict) ep_setFieldError(el, 'Requerido.'); return false; }
       if (!/^[0-9\.\-kK]+$/.test(v)) { ep_setFieldError(el, 'Formato RUT inválido.'); return false; }
       if (opts.strict && !ep_validarRutWrapper(v)) { ep_setFieldError(el, 'RUT inválido.'); return false; }
       ep_clearFieldError(el); return true;
     case 'password':
       // reutiliza validarContraseñas: solo comprueba complejidad mínima en tiempo real
       if (v === '') { ep_clearFieldError(el); return true; }
       const hasUpper = /[A-Z]/.test(v), hasLower = /[a-z]/.test(v), hasNum = /\d/.test(v), hasSpec = /[!@#\$%\^&\*\(\)_+\-=\[\]\{\};':"\\|,.<>\/\?]+/.test(v);
       if (v.length < 8 || !(hasUpper && hasLower && hasNum && hasSpec)) { ep_setFieldError(el, 'Contraseña: 8+, mayúscula, minúscula, número y especial.'); return false; }
       ep_clearFieldError(el); return true;
     case 'confirm_password':
       const form = el.closest('form');
       if (!form) { ep_clearFieldError(el); return true; }
       const pwd = form.querySelector('input[name="password"], #password');
       const pv = pwd ? (pwd.value || '').trim() : '';
       if (pv === '' && v === '') { ep_clearFieldError(el); return true; }
       if (pv !== v) { ep_setFieldError(el, ''); return true; }
       ep_clearFieldError(el); return true;
     default:
       ep_clearFieldError(el);
       return true;
   }
 }

 // Valida todos los campos al enviar el formulario
 function ep_validateAll(form) {
  // lista de campos que deben validarse
   const names = ['nombre','apellido','username','correo','telefono','direccion','rut','password','confirm_password'];
   let ok = true;
   let first = null;
   // recorrer cada campo por nombre
   names.forEach(n => {
    // buscar el campo por atributo name o por ID
     const el = form.querySelector(`[name="${n}"], #${n}`);
     // validar el campo con validación estricta
     const res = ep_validateFieldByName(n, el, { strict: true });
     if (!res) {
       ok = false;
       if (!first && el) first = el;
     }
   });
   // si hay errores, enfocar el primer campo inválido
   if (!ok && first && typeof first.focus === 'function') first.focus();
   return ok;
 }

 // Valida cada campo mientras el usuario escribe o sale del campo
 function ep_attachRealtimeValidationToForm(form) {
   if (!form) return;
   form.addEventListener('input', function (e) {
     const t = e.target;
     if (!t || !t.name) return;
     // normalizar email
     const name = t.name === 'email' ? 'correo' : t.name;
     // si es teléfono, limpiar entrada permitida (+ al inicio)
     if (t.name === 'telefono') {
       let v = t.value || '';
       const hasPlus = v.startsWith('+');
       v = v.replace(/[^0-9+]/g, '');
       v = (hasPlus ? '+' : '') + v.replace(/\+/g, '');
       if (v.startsWith('+')) v = v.slice(0, 12); else v = v.slice(0, 11);
       t.value = v;
     }
     ep_validateFieldByName(name, t, { strict: false });
     // si es password, además ejecutar validarContraseñas para mensajes existentes
     if (name === 'password' || name === 'confirm_password') {
       validarContraseñas();
     }
   }, true);

   // validar cuando el usuario sale del campo (blur)
   form.addEventListener('blur', function (e) {
     const t = e.target;
     if (!t || !t.name) return;
     const name = t.name === 'email' ? 'correo' : t.name;
     ep_validateFieldByName(name, t, { strict: true });
   }, true);
 }




  // Verifica si todos los campos del formulario son válidos sin mostrar mensajes.
  function ep_checkValidity(form) {
    if (!form) return true;
     // lista de campos a validar
    const names = ['nombre','apellido','username','correo','email','telefono','direccion','rut','password','confirm_password'];
    // recorrer cada campo
    for (const n of names) {
      // buscar el campo por name o ID
      const el = form.querySelector(`[name="${n}"], #${n}`);
      if (!el) continue;
      // usa strict:false para no mostrar mensajes al comprobar estado para el botón
      const ok = ep_validateFieldByName(n, el, { strict: false });
      if (!ok) return false;
    }
    // comprobar contraseñas (si existe la función)
    if (typeof validarContraseñas === 'function') {
      
      try {
        if (!validarContraseñas()) return false; // si falla la validación, no es válido
      } catch (e) {
        console.error('Error en validarContraseñas:', e); // mostrar error en consola
        return false;
      }
    } else {
      // validación manual si no existe la función
      const pwd = form.querySelector('input[name="password"]');
      const cp  = form.querySelector('input[name="confirm_password"]');
      if (pwd && cp) {
        const pv = (pwd.value||'').trim(), cv = (cp.value||'').trim();
        if (pv && pv !== cv) return false;
      }
    }
    return true;
  }

  // Activa o desactiva el botón de envío según la validez del formulario.
  function updateSubmitState(form) {
    if (!form) return;
    // buscar todos los botones de tipo submit
    const ok = ep_checkValidity(form);
    const submits = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    // recorrer cada botón y actualizar su estado
    submits.forEach(s => {
      s.disabled = !ok;
      if (!ok) s.setAttribute('aria-disabled','true'); else s.removeAttribute('aria-disabled');
    });
  }

  // Modifica ep_attachRealtimeValidationToForm para actualizar estado del submit en cada cambio
  function ep_attachRealtimeValidationToForm(form) {
    if (!form) return;
    // inputs to validate
    const fields = form.querySelectorAll('input[name], textarea[name], select[name]');
    fields.forEach(el => {
      const name = el.name || el.id;
      if (!name) return;
      // skip rut explicitly only if you don't want real-time rut validation
      // if (name.toLowerCase() === 'rut') return; 
      const handler = function () {
        ep_validateFieldByName(name, el, { strict: false });
        updateSubmitState(form);
      };
      el.addEventListener('input', handler);
      el.addEventListener('blur', handler);
    });

    // submit interceptor en captura para bloquear envío si hay errores
    form.addEventListener('submit', function (ev) {
      const ok = ep_checkValidity(form) && (typeof validarContraseñas === 'function' ? validarContraseñas() : true);
      if (!ok) {
        ev.preventDefault();
        try { ev.stopImmediatePropagation(); } catch(e){}
        updateSubmitState(form);
        try { if (window.showAlert) window.showAlert('error','Corrige los errores del formulario.'); } catch(e){}
        return false;
      }
      return true;
    }, true);

    // inicializar estado del submit al montar
    updateSubmitState(form);
  }

  // conectar al DOMContentLoaded existente: buscar formulario dentro de #editar-usuario
  document.addEventListener('DOMContentLoaded', function () {
    try {
      const form = document.querySelector('#editar-usuario form') || document.querySelector('#editar-usuario') || document.querySelector('#editarPerfilForm') || document.querySelector('form.form-styled');
      if (form && form.tagName === 'FORM') {
        // adjunta validación en tiempo real y control de submit
        ep_attachRealtimeValidationToForm(form);
      }
    } catch (e) { /* silencioso */ }
  });









  // conectar al DOMContentLoaded existente: buscar formulario dentro de #editar-usuario
  document.addEventListener('DOMContentLoaded', function () {
    try {
      const form = document.querySelector('#editar-usuario form') || document.querySelector('#editar-usuario') || document.querySelector('form.form-styled');
      if (form && form.tagName === 'FORM') {
        // interceptar submit para validar todo antes (si no quieres bloquear, puedes quitar)
        form.addEventListener('submit', function (ev) {
          // si hay contraseña vacía, validarContraseñas ya permite envío; usamos ep_validateAll para resto
          if (!ep_validateAll(form) || !validarContraseñas()) {
            ev.preventDefault();
            try { if (window.showAlert) window.showAlert('error','Corrige los errores en el formulario.'); } catch(e) {}
          }
        });
        ep_attachRealtimeValidationToForm(form);
      }
    } catch (e) { /* silencioso */ }
  });
  // --- FIN INSERT ---


    








  // Función para agregar el evento de validación en tiempo real al cargar el documento
  document.addEventListener('DOMContentLoaded', function () {
  // seleccionar el formulario por id (agregado en PHP)
  const form = document.getElementById('editarPerfilForm') || document.querySelector('form');
  if (!form) return;

  // adjuntar validación en tiempo real (funciones ep_* definidas previamente)
  if (typeof ep_attachRealtimeValidationToForm === 'function') ep_attachRealtimeValidationToForm(form);

  // interceptar submit para validar todo antes de enviar
  form.addEventListener('submit', function (ev) {
    // validar campos y contraseñas
    const okFields = typeof ep_validateAll === 'function' ? ep_validateAll(form) : true;
    const okPass  = typeof validarContraseñas === 'function' ? validarContraseñas() : true;
    if (!okFields || !okPass) {
      ev.preventDefault();
      // mostrar alerta global si existe
      try { if (window.showAlert) window.showAlert('error', 'Corrige los errores del formulario.'); } catch(e){}
      // enfocar primer error (ep_validateAll ya enfoca)
      return false;
    }
    // permitir submit si todo ok
    return true;
  });

  // asegurar toggles para botones que llaman togglePasswordVisibility(this)
  // si la función no existe en el scope, crear una ligera:
  if (typeof togglePasswordVisibility !== 'function') {
    window.togglePasswordVisibility = function (btn) {
      if (!btn || btn.tagName !== 'BUTTON') return;
      const container = btn.closest('.password-container');
      const input = container && container.querySelector('input[type="password"], input[type="text"]');
      if (!input) return;
      const was = input.type === 'password';
      input.type = was ? 'text' : 'password';
      btn.textContent = was ? 'Ocultar' : 'Mostrar';
      try { input.focus(); } catch(e) {}
    };
  }
});










    
  // Función para validar el submit del formulario en base a la validación de contraseñas
  function validatePasswords() {
    // Obtener el valor de la contraseña
    var password = document.getElementById("password").value;

    // Si no se ingresó una nueva contraseña, no se valida
    if (password.length === 0) {
        return true;
    }

    // Validar las contraseñas solo si se ingresó una nueva contraseña
    if (!validarContraseñas()) {
        // Mostrar una alerta si hay errores y retornar false para detener el envío del formulario
        alert("Por favor, corrige los errores en las contraseñas antes de continuar.");
        return false;
    }

    // Si no hay errores, retornar true para permitir el envío del formulario
    return true;
  }

  // Función para alternar la visibilidad de la contraseña
  function togglePasswordVisibility(inputId) {
    // Obtiene el elemento de entrada (input) por su ID
    var input = document.getElementById(inputId);
    
    // Obtiene el siguiente elemento hermano del input, que debería ser el botón de alternar visibilidad
    var button = input.nextElementSibling;
    
    // Verifica el tipo actual del input
    if (input.type === "password") {
      // Si el input es de tipo contraseña, cambia a tipo texto y actualiza el texto del botón
      input.type = "text";
    } else {
      // Si el input es de tipo texto, cambia a tipo contraseña y actualiza el texto del botón
      input.type = "password";
    }
  }


  
// TITULO ARCHIVO JS

  // SIN CODIGO
    


/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa editar_perfil .JS -------------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
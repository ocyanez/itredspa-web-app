// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa formulario_registro .JS --------------------------------
    --------------------------------------------------------------------------------------------------------------- */

// TITULO INICIALIZACIÓN Y PROTECCIÓN CSRF

    // SIN CODIGO



// TITULO GENERACIÓN DEL TOKEN CSRF

    // SIN CODIGO



// TITULO REDIRECCIÓN SEGÚN EL ROL DEL USUARIO

    // SIN CODIGO


    
// TITULO BODY

    // SIN CODIGO

    

// TITULO FORMULARIO DE REGISTRO

    // Función para validar el RUT chileno
    function validarRUT(rut) {
        // Limpia el RUT para eliminar puntos y guión
        rut = rut.replace(/[.-]/g, "");
        
        // Verifica la longitud mínima y máxima
        if (rut.length < 8 || rut.length > 9) {
            return false;
        }

        // Separa el cuerpo y el dígito verificador
        var cuerpo = rut.slice(0, -1);
        var dv = rut.slice(-1).toUpperCase();

        // Valida que el cuerpo sea numérico
        if (!/^\d+$/.test(cuerpo)) {
            return false;
        }

        // Calcula el dígito verificador esperado
        var suma = 0;
        var multiplo = 2;

        for (var i = cuerpo.length - 1; i >= 0; i--) {
            suma += multiplo * parseInt(cuerpo.charAt(i), 10);
            multiplo = multiplo < 7 ? multiplo + 1 : 2;
        }

        var dvEsperado = 11 - (suma % 11);
        dvEsperado = dvEsperado === 11 ? "0" : dvEsperado === 10 ? "K" : dvEsperado.toString();

        return dv === dvEsperado;
    }

    // Función para manejar el evento de envío del formulario
    function manejarSubmit(event) {
        // Obtener el formulario y el elemento de error
        var form = event.target;
        var rutInput = form.querySelector('#rut') || form.querySelector('[name="rut"]');
        var registroError = document.getElementById('registroError');

        // Limpiar mensajes previos
        if (registroError) {
            registroError.style.display = 'none';
            registroError.textContent = '';
        }

        // Comprobar si el campo RUT fue encontrado en el formulario
        if (!rutInput) {
            console.error('Campo RUT no encontrado en el formulario.');
            return; // Salir de la función si el campo no se encuentra
        }

        // Obtener y limpiar el valor del RUT
        var rut = rutInput.value.trim();

        // Validar RUT antes de permitir el envío
        if (!validarRUT(rut)) {
            event.preventDefault();
            if (registroError) {
                registroError.textContent = 'RUT inválido. Por favor verifique e intente nuevamente.';
                registroError.style.display = 'block';
            } else {
                alert('RUT inválido. Por favor verifique e intente nuevamente.');
            }
            rutInput.focus();
            return false;
        }

        // Validar contraseñas (si corresponde) antes de permitir el envío
        if (!validatePasswords()) {
            event.preventDefault();
            return false;
        }
    }

    // Espera a que todo el contenido del DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function () {
        // Obtener el formulario de registro utilizando su ID
        var formularioRegistro = document.getElementById('registroForm');
        
        // Verificar si se encontró el formulario de registro
        if (formularioRegistro) {
            console.log("Formulario de registro encontrado."); // Mensaje de confirmación en la consola
            // Agregar un manejador de evento para el envío del formulario
            formularioRegistro.addEventListener('submit', manejarSubmit);
        } else {
            // Mensaje de error si no se encuentra el formulario
            console.error("No se encontró el formulario de registro con el ID 'registroForm'.");
        }
    });

    // Función para validar las contraseñas
    function validarContraseñas() {
        // Obtener los valores de los campos de contraseña y confirmación de contraseña
        var password = document.getElementById("password").value;
        var confirm_password = document.getElementById("confirm_password").value;

        // Obtener elementos de mensaje de error para la contraseña y la confirmación de contraseña
        var passwordError = document.getElementById("password-error");
        var confirmPasswordError = document.getElementById("confirm-password-error");

        // Inicializar variables para mensajes de error
        var errorMessage = "";
        var confirmErrorMessage = "";

        // Verifica si la contraseña cumple con los criterios de complejidad
        var hasUpperCase = /[A-Z]/.test(password); // Verifica si hay al menos una mayúscula
        var hasLowerCase = /[a-z]/.test(password); // Verifica si hay al menos una minúscula
        var hasNumber = /\d/.test(password); // Verifica si hay al menos un número
        var hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(password); // Verifica si hay al menos un carácter especial

        // Verifica si la longitud de la contraseña es adecuada y cumple con los criterios de complejidad
        if (password.length > 0 && (password.length < 8 || !(hasUpperCase && hasLowerCase && hasNumber && hasSpecial))) {
            errorMessage = "La contraseña debe tener mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número y un carácter especial.";
        }

        // Verifica si las contraseñas coinciden, solo si se ha ingresado una nueva contraseña
        if (password.length > 0 && password !== confirm_password) {
            confirmErrorMessage = "Las contraseñas no coinciden.";
        }

        // Muestra los mensajes de error si hay algún error encontrado
        passwordError.textContent = errorMessage;
        confirmPasswordError.textContent = confirmErrorMessage;

        // Retorna true si no hay errores o si no se ingresó una nueva contraseña, false si hay errores
        return errorMessage === "" && confirmErrorMessage === "";
    }

    // Función para agregar el evento de validación en tiempo real al cargar el documento
    document.addEventListener('DOMContentLoaded', function () {
        // Obtener los elementos de contraseña y confirmación de contraseña
        var password = document.getElementById("password");
        var confirm_password = document.getElementById("confirm_password");

        // Agregar eventos de entrada para validar contraseñas en tiempo real, solo si se ha ingresado una nueva contraseña
        password.addEventListener('input', validarContraseñas);
        confirm_password.addEventListener('input', validarContraseñas);
    });

    // Función para validar en el submit del formulario
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


        // crea/actualiza mensaje de error junto al input
    function setFieldError(el, msg) {
      if (!el) return;
      const name = el.id || el.name || ('f' + Math.random().toString(36).slice(2));
      let err = document.getElementById(name + '-error');
      if (!err) {
        err = document.createElement('div');
        err.id = name + '-error';
        err.className = 'field-error';
        err.style.color = 'red';
        err.style.fontSize = '0.9em';
        err.style.marginTop = '4px';
        el.insertAdjacentElement('afterend', err);
      }
      err.textContent = msg || '';
    }

    // limpia error inline
    function clearFieldError(el) {
      if (!el) return;
      const name = el.id || el.name;
      const err = name ? document.getElementById(name + '-error') : null;
      if (err) err.textContent = '';
    }

    // email simple
    function validarEmailJS(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((email||'').trim());
    }

    // teléfono permite + al inicio y 7-11 dígitos
    function validarTelefonoJS(tel) {
      return /^\+?[0-9]{7,11}$/.test((tel||'').trim());
    }

    // dirección: mínimo 3 chars y debe contener al menos un número
    function validarDireccionJS(dir) {
      if (!dir) return false;
      const v = dir.trim();
      return v.length >= 3 && /\d+/.test(v);
    }

    // wrapper de RUT existente: usar validarRUT definida en este archivo
    function validarRutWrapper(rut) {
      try { return validarRUT(rut); } catch (e) { return false; }
    }

    // validación por nombre de campo (strict en blur / submit)
    function validateFieldByName(name, el, opts = { strict: false }) {
      if (!el) return true;
      const v = (el.value || '').trim();
      switch (name) {
        case 'nombre':
        case 'apellido':
          if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
          if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}$/.test(v)) { setFieldError(el, 'Solo letras (2-50).'); return false; }
          clearFieldError(el); return true;
        case 'username':
          if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
          if (v.length < 3) { setFieldError(el, 'Mínimo 3 caracteres.'); return false; }
          clearFieldError(el); return true;
        case 'correo':
        case 'email':
          if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
          if (!validarEmailJS(v)) { setFieldError(el, 'Correo inválido.'); return false; }
          clearFieldError(el); return true;
        case 'telefono':
          if (v === '') { clearFieldError(el); return false; }
          if (!/^\+?[0-9]*$/.test(v)) { setFieldError(el, 'Sólo números o + al inicio.'); return false; }
          if (opts.strict && !validarTelefonoJS(v)) { setFieldError(el, 'Teléfono inválido (7-11 dígitos, opcional + al inicio).'); return false; }
          clearFieldError(el); return true;
        case 'direccion':
          if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
          if (!validarDireccionJS(v)) { setFieldError(el, 'Dirección debe incluir número y tener al menos 3 caracteres.'); return false; }
          clearFieldError(el); return true;
        case 'rut':
          if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
          if (!/^[0-9\.\-kK]+$/.test(v)) { setFieldError(el, 'Formato RUT inválido.'); return false; }
          if (opts.strict && !validarRutWrapper(v)) { setFieldError(el, 'RUT inválido.'); return false; }
          clearFieldError(el); return true;
        case 'password':
          if (v === '') { clearFieldError(el); return false; }
          // misma lógica que validateContraseñas (mínimo 8, mayúscula, minúscula, número, especial)
          const hasUpper = /[A-Z]/.test(v);
          const hasLower = /[a-z]/.test(v);
          const hasNum = /\d/.test(v);
          const hasSpec = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(v);
          if (v.length < 8 || !(hasUpper && hasLower && hasNum && hasSpec)) {
            setFieldError(el, 'La contraseña debe tener 8+, mayúscula, minúscula, número y carácter especial.');
            return false;
          }
          clearFieldError(el); return true;
        case 'confirm_password':
          // comparar con password del mismo formulario
          const form = el.closest('form');
          if (!form) { clearFieldError(el); return true; }
          const pwd = form.querySelector('input[name="password"], #password');
          const pv = pwd ? (pwd.value || '').trim() : '';
          if (pv === '' && v === '') { clearFieldError(el); return true; }
          if (pv !== v) { setFieldError(el, '*'); return false; }
          clearFieldError(el); return true;
        default:
          clearFieldError(el);
          return true;
      }
    }

    // valida todos los campos antes del submit
    function validateAll(form) {
      const names = ['nombre','apellido','username','correo','telefono','direccion','rut','password','confirm_password'];
      let ok = true;
      let firstErrEl = null;
      names.forEach(name => {
        const el = form.querySelector(`[name="${name}"], #${name}`);
        const res = validateFieldByName(name, el, { strict: true });
        if (!res) {
          ok = false;
          if (!firstErrEl && el) firstErrEl = el;
        }
      });
      if (!ok && firstErrEl && typeof firstErrEl.focus === 'function') firstErrEl.focus();
      return ok;
    }

    // evita enviar si hay errores: integra con manejarSubmit
    const _origManejarSubmit = typeof manejarSubmit === 'function' ? manejarSubmit : null;
    function manejarSubmitConValidacion(event) {
      const form = event.target;
      // limpiar mensajes previos
      // (manejador original ya limpia registroError; mantenemos eso)
      if (!validateAll(form)) {
        event.preventDefault();
        // opcional: mostrar alerta global simple
        try { if (window.showAlert) window.showAlert('error','Corrige errores del formulario.'); } catch(e) {}
        return false;
      }
      // si pasó validación, llamar manejador original (si existe) para continuar con RUT/contraseñas ya implementadas
      if (_origManejarSubmit) {
        return _origManejarSubmit.call(null, event);
      }
      return true;
    }

    // adjunta validación en tiempo real al formulario
    function attachRealtimeValidationToForm(form) {
      if (!form) return;
      // delegación de input/blur en el form
      form.addEventListener('input', function (e) {
        const t = e.target;
        if (!t || !t.name) return;
        // normalizar nombre correo/email
        const name = t.name === 'email' ? 'correo' : t.name;
        validateFieldByName(name, t, { strict: false });
        // si es teléfono, permitir solo + al inicio y dígitos en el input (suavizar entrada)
        if (t.name === 'telefono') {
          let v = t.value || '';
          const hasPlus = v.startsWith('+');
          v = v.replace(/[^0-9+]/g, '');
          v = (hasPlus ? '+' : '') + v.replace(/\+/g, '');
          if (v.startsWith('+')) v = v.slice(0, 12); else v = v.slice(0, 11);
          t.value = v;
        }
      }, true);
      form.addEventListener('blur', function (e) {
        const t = e.target;
        if (!t || !t.name) return;
        const name = t.name === 'email' ? 'correo' : t.name;
        validateFieldByName(name, t, { strict: true });
      }, true);
    }

    // inicializar en DOMContentLoaded (este archivo ya usa event listener: se agregan handlers extra)
    document.addEventListener('DOMContentLoaded', function () {
      // intentar obtener el formulario principal de registro
      const formularioRegistro = document.getElementById('registroForm') || document.querySelector('form[action*="/registro.php"]');
      if (formularioRegistro) {
        // reemplaza el listener submit existente por nuestro manejador que valida antes
        formularioRegistro.removeEventListener('submit', manejarSubmit); // puede no existir sin error
        formularioRegistro.addEventListener('submit', manejarSubmitConValidacion);
        // attach realtime
        attachRealtimeValidationToForm(formularioRegistro);
      }

      // también adjuntar a posibles formularios dinámicos (si carga modal) - observar el body y enganchar cuando aparece registroForm
      const mo = new MutationObserver(function (mutations) {
        mutations.forEach(m => {
          m.addedNodes.forEach(node => {
            if (node.nodeType !== 1) return;
            const f = node.querySelector && (node.querySelector('#registroForm') || node.querySelector('form[action*="/registro.php"]'));
            if (f) {
              f.removeEventListener('submit', manejarSubmit);
              f.addEventListener('submit', manejarSubmitConValidacion);
              attachRealtimeValidationToForm(f);
            }
          });
        });
      });
      mo.observe(document.body, { childList: true, subtree: true });
    });



// TITULO ENLACE PARA ALTERNAR A INICIO DE SESIÓN

    // SIN CODIGO



// TITULO ARCHIVO JS

    // SIN CODIGO



/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa formulario_registro .JS -------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

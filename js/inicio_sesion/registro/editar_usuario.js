// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa editar_usuario .JS -------------------------------------
    --------------------------------------------------------------------------------------------------------------- */

// TITULO INICIAR SESIÓN Y VERIFICAR ACCESO

  // Variable que almacena el nombre de usuario actual obtenido desde PHP
  var current_user = "<?php echo $username; ?>"; 
  // Espera a que el DOM esté completamente cargado
  document.addEventListener("DOMContentLoaded", function () {
    // Selecciona todos los elementos que son botones específicos
      var botones = document.querySelectorAll(
        ".nav-button, #botonEditarPerfil, #botonEditar, #botonEliminar, #botonRegistrar, #BotonAtras, #BotonGuardar"
      );

      // Agrega controladores de eventos de clic a todos los botones seleccionados
      botones.forEach(function (boton) {
        boton.addEventListener("click", function () {
          // Obtiene el texto del botón (acción realizada)
          var accion = boton.textContent.trim();

          // Llama a la función para registrar la actividad con la acción actual
          registrarActividad(accion);
        });
      });
    


// TITULO COMPROBACIÓN DE ROLES Y ACCESO A PÁGINAS

  // SIN CODIGO
    


// TITULO COMPROBACIÓN DEL ID DEL USUARIO EN GET

  // SIN CODIGO
    


// TITULO ACTUALIZACIÓN DEL USUARIO

  // Función para registrar la actividad en el servidor
    function registrarActividad(accion) {
      // Crea una nueva solicitud XMLHttpRequest
      var xhr = new XMLHttpRequest();

      // Configura la solicitud POST para enviar datos a log_registros.php
      xhr.open("POST", "log_registros.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      // Define la función que maneja la respuesta del servidor
      xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
          console.log(xhr.responseText); // Muestra la respuesta del servidor en la consola
        }
      };

      // Prepara los datos a enviar: acción y usuario actual
      var data =
        "accion=" +
        encodeURIComponent(accion) +
        "&current_user=" +
        encodeURIComponent(current_user);

      // Envía la solicitud con los datos preparados
      xhr.send(data);
    }
  });
    
    /* --- INSERT: validación en tiempo real (prefijo ep_) --- */

    // Muestra mensaje de error en un campo
    function ep_setFieldError(el, msg) {
      if (!el) return;
      el.classList.add('ep-has-error'); // marca visualmente el campo como inválido
      let hint = el.nextElementSibling;
      // si no existe o no tiene la clase esperada, crear el span de error
      if (!hint || !hint.classList || !hint.classList.contains('ep-field-error')) {
        hint = document.createElement('span');
        hint.className = 'ep-field-error';
        el.parentNode && el.parentNode.insertBefore(hint, el.nextSibling); // insertar después del campo
      }
      hint.textContent = msg || ''; // mostrar el mensaje de error
    }

    // Limpia el mensaje de error de un campo
    function ep_clearFieldError(el) {
      if (!el) return; // si no hay elemento, salir
      el.classList.remove('ep-has-error'); // quitar clase de error
      const hint = el.nextElementSibling; // buscar el mensaje de error
      // si existe y tiene la clase esperada, limpiar su contenido
      if (hint && hint.classList && hint.classList.contains('ep-field-error')) hint.textContent = '';
    }

    // Valida formato de correo electrónico
    function ep_validarEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((email||'').trim());
    }

    // Valida número de teléfono
    function ep_validarTelefono(tel) {
      const v = (tel||'').trim();
      // acepta opcionalmente + al inicio, seguido de 7 a 11 dígitos
      return /^\+?[0-9]{7,11}$/.test(v); 
    }

    // Valida dirección con número
    function ep_validarDireccion(dir) {
      if (!dir) return false;
      const v = dir.trim();
      return v.length >= 3 && /\d+/.test(v); // mínimo 3 caracteres y debe incluir número
    }


    // Valida campo según su nombre
    function ep_validateFieldByName(name, el, opts = { strict: false }) {
      if (!el) return true;
      const v = (el.value || '').trim();
      switch (name) {
        case 'nombre':
        case 'apellido':
          if (v === '') { if (opts.strict) ep_setFieldError(el, 'Requerido.'); return false; }
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
          if (v === '') { ep_clearFieldError(el); return true; }
          if (!/^\+?[0-9]*$/.test(v)) { ep_setFieldError(el, 'Sólo números o + al inicio.'); return false; }
          if (opts.strict && !ep_validarTelefono(v)) { ep_setFieldError(el, 'Teléfono inválido (7-11 dígitos).'); return false; }
          ep_clearFieldError(el); return true;
        case 'direccion':
          if (v === '') { if (opts.strict) ep_setFieldError(el, 'Requerido.'); return false; }
          if (!ep_validarDireccion(v)) { ep_setFieldError(el, 'Dirección debe incluir número y al menos 3 caracteres.'); return false; }
          ep_clearFieldError(el); return true;
        case 'password':
          if (v === '') { ep_clearFieldError(el); return true; } // no obligatorio al editar
          const hasUpper = /[A-Z]/.test(v), hasLower = /[a-z]/.test(v), hasNum = /\d/.test(v), hasSpec = /[!@#\$%\^&\*\(\)_+\-=\[\]{};':"\\|,.<>\/?]+/.test(v);
          if (v.length < 8 || !(hasUpper && hasLower && hasNum && hasSpec)) { ep_setFieldError(el, 'Contraseña: 8+, mayúscula, minúscula, número y especial.'); return false; }
          ep_clearFieldError(el); return true;
        case 'confirm_password':
          const form = el.closest('form');
          if (!form) { ep_clearFieldError(el); return true; }
          const pwd = form.querySelector('input[name="password"], #password');
          const pv = pwd ? (pwd.value || '').trim() : '';
          if (pv === '' && v === '') { ep_clearFieldError(el); return true; }
          if (pv !== v) { ep_setFieldError(el, 'Las contraseñas no coinciden.'); return false; }
          ep_clearFieldError(el); return true;
        default:
          ep_clearFieldError(el); // si no se reconoce el campo, limpiar cualquier error
          return true;
      }
    }

    // Valida todos los campos al enviar el formulario 
    function ep_validateAll(form) {
      if (!form) return true;
      // lista de campos que deben validarse
      const names = ['nombre','apellido','username','correo','email','telefono','direccion','password','confirm_password'];
      let ok = true;
      let first = null;
      // recorrer cada campo por nombre
      names.forEach(n => {
        // busca el campo por name o ID
        const el = form.querySelector(`[name="${n}"], #${n}`); 
        // valida con modo estricto
        const res = ep_validateFieldByName(n, el, { strict: true });
        if (!res) {
          ok = false; // si falla, marcar como inválido
          if (!first && el) first = el; // guardar el primer campo inválido para enfocar
        }
      });
      // si hay errores, enfocar el primer campo inválido
      if (!ok && first && typeof first.focus === 'function') first.focus();
      // devuelve true si todo está validado correctamente
      return ok;
    }
    // Valida mientras el usuario escribe o sale de un campo
    function ep_attachRealtimeValidationToForm(form) {
      if (!form) return;
      // inputs to validate
      const fields = form.querySelectorAll('input[name], textarea[name], select[name]');
      fields.forEach(el => {
        const name = el.name || el.id;
        if (!name) return;
        // omitir validación para el campo 'rut'
        if (name.toLowerCase() === 'rut') return;
        // función de validación en tiempo real
        const handler = function () { ep_validateFieldByName(name, el, { strict: false }); };
        // validar al escribir y al salir del campo
        el.addEventListener('input', handler);
        el.addEventListener('blur', handler);
      });

      // submit interceptor en captura para bloquear envío si hay errores
      form.addEventListener('submit', function (ev) {
        const ok = ep_validateAll(form);
        if (!ok) {
          ev.preventDefault();
          try { ev.stopImmediatePropagation(); } catch(e) {}
          return false;
        }
        return true;
      }, true);
    }

    // intenta conectar cuando el DOM carga (si ya existe otro DOMContentLoaded no interfiere)
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.querySelector('#editarUsuarioForm') || document.querySelector('form[action*="editar_usuario"], form#editar-usuario, form');
      if (!form) return; // si no se encuentra ningún formulario, salir
      ep_attachRealtimeValidationToForm(form); // conectar validación en tiempo real
    });



// TITULO CONSULTA SQL PARA OBTENER LOS DATOS DEL USUARIO

  // SIN CODIGO
    


// TITULO BODY

  // SIN CODIGO
    


// TITULO INCLUYE EL ARCHIVO PHP PARA GESTIONAR LA SESIÓN

  // SIN CODIGO
    


// TITULO BARRA DE NAVEGACIÓN

  // SIN CODIGO
    


// TITULO CENTRO DE LA NAVEGACIÓN CON BOTONES

  // SIN CODIGO
    


// TITULO FORMULARIO PARA CERRAR SESIÓN

  // SIN CODIGO
    


// TITULO CONTAINER PRINCIPAL PARA EDITAR USUARIO

  // SIN CODIGO
    


// TITULO FORMULARIO DE EDITAR USUARIO

  // SIN CODIGO
    


// TITULO ARCHIVO JS

  // SIN CODIGO
    


/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa editar_usuario .JS ------------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
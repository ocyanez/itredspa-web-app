/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/

/*  ------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa usuarios_contenido .JS ------------------------------
    ------------------------------------------------------------------------------------------------------------  */

// TITULO HTML

    // SIN FUNCION

// TITULO BODY

    // SIN FUNCION

// TITULO GESTION DE USUARIOS

    // SIN FUNCION

// TITULO REGISTRO DE USUARIOS

    // Espera a que el documento esté completamente cargado antes de ejecutar el código
    document.addEventListener('DOMContentLoaded', function () {
            // Validación para nombre y apellido: solo letras y espacios
            ['nombre', 'apellido'].forEach(function(id) {
                // itera sobre los nombres de campo
                // selecciona el input por atributo name
                const input = document.querySelector(`input[name="${id}"]`);
                // si el elemento existe en la página
                if (input) {
                    // añade listener para cada pulsación
                    input.addEventListener('input', function () {
                        // elimina caracteres no permitidos
                        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
                    });
                }
            });

        // Teléfono: sólo números y opcional + al inicio
            // Validación para teléfono: sólo números y un + al inicio
            const telInput = document.querySelector('input[name="telefono"]');
            // si existe el campo teléfono
            if (telInput) {
                // aseguro que el atributo pattern exista y coincida con la validación JS
                try {
                  telInput.setAttribute('pattern', '\\+?[0-9]{7,11}');
                } catch (e) {
                  // no crítico si falla
                }

                // función para normalizar el valor (quita espacios invisibles y normaliza signos)
                function normalizeTelValue(v) {
                  if (!v) return '';
                  // eliminar espacios normales y NBSP
                  v = v.replace(/\s+/g, '').replace(/\u00A0/g, '');
                  // permitir sólo dígitos y +
                  const hasPlus = v.startsWith('+');
                  v = v.replace(/[^0-9+]/g, '');
                  v = (hasPlus ? '+' : '') + v.replace(/\+/g, '');
                  // limitar longitud: + + 11 dígitos => max 12 chars (incluye +)
                  if (v.startsWith('+')) v = v.slice(0, 12); else v = v.slice(0, 11);
                  return v;
                }

                // escucha cambios en el input y normaliza en tiempo real
                telInput.addEventListener('input', function () {
                    this.value = normalizeTelValue(this.value || '');
                    // limpiar mensaje de validación personalizado cuando el usuario corrige
                    try { this.setCustomValidity(''); } catch (e) {}
                });

                // al perder foco, asegurar normalización final
                telInput.addEventListener('blur', function () {
                    this.value = normalizeTelValue(this.value || '');
                });

                // mostrar un mensaje claro en caso de invalid (reemplaza el mensaje nativo)
                telInput.addEventListener('invalid', function (e) {
                    // si el campo está vacío, dejar que el required lo maneje (si aplica)
                    if (!this.value || this.value.trim() === '') return;
                    // asignar mensaje de ayuda
                    try {
                      this.setCustomValidity('Teléfono inválido. Use +569XXXXXXXX o 9XXXXXXXX (7-11 dígitos).');
                    } catch (err) {}
                });

                // al escribir, borrar cualquier mensaje de custom validity
                telInput.addEventListener('change', function () { try { this.setCustomValidity(''); } catch (e) {} });
            }

        // RUT: formato en tiempo real con puntos y guion (máx 10 caracteres), preservando caret
            // Helper: formatea un RUT para display (ej: 12345678k -> 12.345.678-K)
            function formatRutForDisplay(raw) {
                if (!raw) return '';
                let clean = raw.toString().replace(/[^0-9kK]/g, '').toUpperCase();
                // Limitar a 9 dígitos numéricos + 1 dígito verificador = 10
                if (clean.length > 10) clean = clean.slice(0, 10);
                if (clean.length <= 1) return clean;
                // último carácter como dígito verificador
                const ver = clean.slice(-1);
                // resto como número
                let num = clean.slice(0, -1);
                // agrega puntos cada 3 dígitos desde la derecha
                num = num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return num + '-' + ver;
            }

            // Función que aplica el formato al input y mantiene el cursor en su lugar
            function attachRutFormatter(el) {
                if (!el) return;
                // evitar adjuntar dos veces
                if (el._rutFormatterAttached) return;
                el._rutFormatterAttached = true;

                el.addEventListener('input', function (e) {
                    const input = this;
                    const prevVal = input.value;
                    const prevPos = input.selectionStart;

                    // obtiene los caracteres válidos antes del cursor
                    const rawBeforeCursor = prevVal.slice(0, prevPos).replace(/[^0-9kK]/g, '').toUpperCase();

                    // aplica el nuevo formato
                    const newVal = formatRutForDisplay(prevVal);
                    input.value = newVal;

                    // restaurar posición del cursor según caracteres válidos antes
                    let count = 0;
                    let newPos = newVal.length;
                    for (let i = 0; i < newVal.length; i++) {
                        const ch = newVal.charAt(i);
                        if (/\d|K/.test(ch)) count++;
                        if (count >= rawBeforeCursor.length) {
                            newPos = i + 1;
                            break;
                        }
                    }
                    if (rawBeforeCursor.length === 0) newPos = 0;
                    try { input.setSelectionRange(newPos, newPos); } catch (err) {}
                });
            }

            // Intentar adjuntar al elemento por id y/o por name
            const rutById = document.getElementById('rut');
            if (rutById) attachRutFormatter(rutById);
            else {
                const rutByName = document.querySelector('input[name="rut"]');
                if (rutByName) attachRutFormatter(rutByName);
            }
    });

    // Función para mostrar u ocultar la contraseña en un campo input
    function togglePasswordVisibility(id) {
        // obtiene el elemento input por id
        var input = document.getElementById(id);
        // si el elemento existe
        if (input) {
            // si está en modo password
            if (input.type === "password") {
                // cambia a texto para mostrar contraseña
                input.type = "text";
            } else {
                // vuelve a ocultar la contraseña
                input.type = "password";
            }
        }
    }


    /* Inserta la validación cliente para registro y edición */
    (function () {
      // Muestra un mensaje de error debajo del campo
      function setFieldError(input, msg) {
        if (!input) return;
        // genera un id único si el campo no tiene uno
        const id = input.id || input.name || ('f' + Math.random().toString(36).slice(2));
        // busca si ya existe el contenedor de error
        let err = document.getElementById(id + '-error');
        // si no existe, lo crea y lo inserta justo después del input
        if (!err) {
          err = document.createElement('div');
          err.id = id + '-error';
          err.className = 'field-error';
          err.style.color = 'red';
          err.style.fontSize = '0.9em';
          err.style.marginTop = '4px';
          input.insertAdjacentElement('afterend', err);
        }
        // muestra el mensaje de error
        err.textContent = msg || '';
      }

      // Limpia el mensaje de error de un campo
      function clearFieldError(input) {
        if (!input) return;
        const id = input.id || input.name;
        const err = id ? document.getElementById(id + '-error') : null;
        if (err) err.textContent = '';
      }

      // Valida si un RUT chileno es correcto
      function validarRutJS(rut) {
        if (!rut) return false;
        // limpia puntos y guiones, y convierte a mayúsculas
        const s = rut.replace(/\./g, '').replace(/-/g, '').toUpperCase();
        if (s.length < 2) return false;
        // dígito verificador
        const dv = s.slice(-1);
        // parte numérica
        const nums = s.slice(0, -1);
        let sum = 0, mul = 2;
        // calcula el dígito verificador esperado
        for (let i = nums.length - 1; i >= 0; i--) {
          sum += parseInt(nums[i], 10) * mul;
          mul = mul === 7 ? 2 : mul + 1;
        }
        const res = 11 - (sum % 11);
        const dvCalc = res === 11 ? '0' : (res === 10 ? 'K' : String(res));
        return dvCalc === dv;
      }

      // Expresiones regulares para validar nombre y correo
      const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}$/u;
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      // Función principal para validar el formulario de registro
      function validateRegistroForm(form) {
        let ok = true;
        const get = name => form.querySelector(`[name="${name}"]`);
        // lista de campos a validar
        const campos = ['nombre','apellido','username','correo','telefono','direccion','rut','cargo','rol','password','confirm_password','csrf_token'];
        // limpia errores previos
        campos.forEach(n => clearFieldError(get(n)));
        // obtiene cada campo del formulario
        const nombre = get('nombre'), apellido = get('apellido'), username = get('username'),
          correo = get('correo'), telefono = get('telefono'), direccion = get('direccion'),
          rut = get('rut'), cargo = get('cargo'), rol = get('rol'), password = get('password'),
          confirm = get('confirm_password'), csrf = get('csrf_token');

        // validaciones campo por campo
        if (!nombre || !nombreRegex.test(nombre.value.trim())) { setFieldError(nombre, 'Nombre inválido (2-50 letras).'); ok = false; }
        if (!apellido || !nombreRegex.test(apellido.value.trim())) { setFieldError(apellido, 'Apellido inválido (2-50 letras).'); ok = false; }
        if (!username || username.value.trim().length < 3) { setFieldError(username, 'Usuario inválido (mín 3 caracteres).'); ok = false; }
        if (!correo || !emailRegex.test(correo.value.trim())) { setFieldError(correo, 'Correo inválido.'); ok = false; }
        if (!telefono || !/^\+?[0-9]{7,11}$/.test(telefono.value.trim())) { setFieldError(telefono, 'Teléfono inválido (7-11 dígitos, opcional + al inicio).'); ok = false; }
        if (!direccion || direccion.value.trim().length < 3) { setFieldError(direccion, 'Dirección inválida.'); ok = false; }
        else if (!/\d+/.test(direccion.value)) { setFieldError(direccion, 'La dirección debe incluir el número (ej. 123).'); ok = false; }
        if (!rut || !/^[0-9\.\-kK]+$/.test(rut.value.trim()) || !validarRutJS(rut.value.trim())) { setFieldError(rut, 'RUT inválido.'); ok = false; }
        if (!cargo || cargo.value.trim() === '') { setFieldError(cargo, 'Cargo requerido.'); ok = false; }
        if (!rol || rol.value === '') { setFieldError(rol, 'Rol requerido.'); ok = false; }
        if (!password || password.value.length < 8 || !/[A-Z]/.test(password.value) || !/[0-9]/.test(password.value)) { setFieldError(password, 'Contraseña: min 8, 1 mayúscula y 1 número.'); ok = false; }
        if (!confirm || password.value !== confirm.value) { setFieldError(confirm, 'Las contraseñas no coinciden.'); ok = false; }
        if (!csrf || csrf.value.length < 8) { alert('Token CSRF inválido. Recarga la página.'); ok = false; }

        // si hay errores, enfoca el primer campo con error
        if (!ok) {
          const first = form.querySelector('.field-error');
          if (first) {
            const id = first.id.replace('-error','');
            const el = document.getElementById(id) || form.querySelector(`[name="${id}"]`);
            if (el && typeof el.focus === 'function') el.focus();
          }
        }
        // devuelve true si todo está validado correctamente
        return ok;
      }

    // Función para validar el formulario de edición de usuario  
    function validateEditForm(form) {
        let ok = true;
        // Función auxiliar para obtener elementos tanto por name como por id
        const get = name => {
            return form.querySelector(`#edit-${name}`) || form.querySelector(`[name="${name}"]`);
        };
        
        // Limpiar errores previos
        ['nombre','apellido','username','correo','telefono','direccion','rut','rol','password','confirm_password'].forEach(n => {
            const el = get(n);
            // borra el mensaje de error si existe
            if (el) clearFieldError(el);
            // Limpiar también los divs de error específicos para contraseñas
            if (n === 'password') {
                const errorDiv = form.querySelector('#edit-password-error');
                if (errorDiv) errorDiv.textContent = '';
            }
            if (n === 'confirm_password') {
                const errorDiv = form.querySelector('#edit-confirm-password-error');
                if (errorDiv) errorDiv.textContent = '';
            }
        });

        // Obtiene todos los campos del formulario
        const nombre = get('nombre'),
              apellido = get('apellido'),
              username = get('username'),
              correo = get('correo'),
              telefono = get('telefono'),
              direccion = get('direccion'),
              rut = get('rut'),
              rol = get('rol'),
              password = get('password'),
              confirm = get('confirm_password');

        // Validaciones de campos básicos
        if (!nombre || !nombreRegex.test(nombre.value.trim())) {
            setFieldError(nombre, 'Nombre inválido.');
            ok = false;
        }
        if (!apellido || !nombreRegex.test(apellido.value.trim())) {
            setFieldError(apellido, 'Apellido inválido.');
            ok = false;
        }
        if (!username || username.value.trim().length < 3) {
            setFieldError(username, 'Usuario inválido.');
            ok = false;
        }
        if (!correo || !emailRegex.test(correo.value.trim())) {
            setFieldError(correo, 'Correo inválido.');
            ok = false;
        }
        if (!telefono || !/^\+?[0-9]{7,11}$/.test(telefono.value.trim())) {
            setFieldError(telefono, 'Teléfono inválido (7-11 dígitos, opcional + al inicio).');
            ok = false;
        }
        if (!direccion || direccion.value.trim().length < 3) {
            setFieldError(direccion, 'Dirección inválida.');
            ok = false;
        }

        // Validación del RUT con formato y dígito verificador
        if (!rut || !/^[0-9\.\-kK]+$/.test(rut.value.trim()) || !validarRutJS(rut.value.trim())) {
            // Buscar div de error fijo en el modal
            const rutErrorDiv = form.querySelector('#rut-error');
            if (rutErrorDiv) {
                rutErrorDiv.textContent = 'RUT inválido.';
            } else {
                setFieldError(rut, 'RUT inválido.');
            }
            ok = false;
        }

        // Validación especial para contraseñas
        if (password && (password.value !== '' || (confirm && confirm.value !== ''))) {
            // Validar requisitos de contraseña el largo,mayúsculas, números
            if (password.value.length < 8 || !/[A-Z]/.test(password.value) || !/[0-9]/.test(password.value)) {
                const errorDiv = form.querySelector('#edit-password-error') || form.querySelector('#password-error');
                if (errorDiv) {
                    errorDiv.textContent = 'La contraseña debe tener mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número y un carácter especial.';
                }
                ok = false;
            }
            
            // Validar que las contraseñas coincidan
            if (!confirm || password.value !== confirm.value) {
                const errorDiv = form.querySelector('#edit-confirm-password-error') || form.querySelector('#confirm-password-error');
                if (errorDiv) {
                    errorDiv.textContent = 'Las contraseñas no coinciden.';
                }
                ok = false;
            }
        }

        // devuelve true si todo está validado correctamente
        return ok;
    }

      // Asocia la validación al formulario de registro
      function attachRegistroHandler() {
        // busca el formulario por id o por acción en la URL
        const form = document.getElementById('registroForm') || document.querySelector('form[action*="/registro.php"]');
        if (!form) return;
        // al enviar el formulario, valida los campos
        form.addEventListener('submit', function (e) {
          // si hay errores, evita el envío
          if (!validateRegistroForm(form)) e.preventDefault();
        });
        // al escribir en cualquier campo, limpia el mensaje de error
        form.addEventListener('input', function (ev) { clearFieldError(ev.target); });
      }

      // Asocia validaciones específicas al campo de dirección
      function attachDireccionHandlers() {
        // formulario principal
        const dir = document.querySelector('input[name="direccion"], textarea[name="direccion"]');
        if (!dir) return;

        // permitir solo caracteres comunes en dirección (letras, números, espacios y signos .,#-ºª)
        dir.addEventListener('input', function () {
          const before = this.value;
          this.value = before.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s\.,#\-ºª]/g, '');
          // limpiar error al corregir
          if (/\d+/.test(this.value)) clearFieldError(this);
        });

        // validar al perder foco
        dir.addEventListener('blur', function () {
          if (!this.value || this.value.trim().length < 3) {
            setFieldError(this, 'Dirección inválida.');
          } else if (!/\d+/.test(this.value)) {
            setFieldError(this, 'La dirección debe incluir el número (ej. 123).');
          } else {
            clearFieldError(this);
          }
        });

        // opcional: validar también al pegar contenido
        dir.addEventListener('paste', function () {
          setTimeout(() => {
            if (/\d+/.test(this.value)) clearFieldError(this);
          }, 10);
        });
      }


      // Detecta clics en cualquier parte del documento para mostrar/ocultar contraseñas
      document.addEventListener('click', function (e) {
      // busca si el clic fue sobre un botón
      const btn = e.target.closest('button');
      if (!btn) return;
      
      // si el botón define un handler inline, no duplicamos (dejamos que el inline lo maneje)
      if (btn.hasAttribute('onclick')) return;

      // busca si el botón está dentro de un contenedor de contraseña
      const container = btn.closest('.password-container');
      if (!container) return; // no es un toggle
      
      // evita que el botón haga otra acción por defecto
      e.preventDefault();
      
      // busca el input de tipo password o text dentro del contenedor
      const input = container.querySelector('input[type="password"], input[type="text"]');
      if (!input) return;
      
      // cambia el tipo de input y el texto del botón
      const wasPassword = input.type === 'password';
      input.type = wasPassword ? 'text' : 'password';
      btn.textContent = wasPassword ? 'Ocultar' : 'Mostrar';
      
      // vuelve a enfocar el input después del cambio
      try { input.focus(); } catch (err) {}
    }, true);


    // Insert de función global y delegado para toggle en formulario de editar
    window.togglePasswordVisibilityEdit = function (btn) {
      // si no hay botón o no es un <button>, no hacemos nada
      if (!btn || btn.tagName !== 'BUTTON') return;
      
      // dentro del contenedor, busca el input que puede ser tipo password o text
      const container = btn.closest('.password-container');
      const input = container ? container.querySelector('input[type="password"], input[type="text"]') : null;
      if (!input) return;
      
      // verifica si el input está en modo oculto (password)
      const wasPassword = input.type === 'password';

      // cambia el tipo del input para mostrar u ocultar la contraseña
      input.type = wasPassword ? 'text' : 'password';

      // actualiza el texto del botón según el estado
      btn.textContent = wasPassword ? 'Ocultar' : 'Mostrar';
      
      // vuelve a enfocar el input después del cambio
      try { input.focus(); } catch (e) {}
    };

    // Detecta clics en botones dentro del formulario de edición o en modales
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('button');
      if (!btn) return;
      // Sólo actuamos si está dentro del formulario de edición o dentro del modal
      const inEditForm = !!btn.closest('#editar-usuario');
      const inModal = !!btn.closest('#modal-editar') || !!btn.closest('#modal-body') || !!btn.closest('.modal-overlay');
      if (!inEditForm && !inModal) return;

      // Si el botón ya tiene onclick inline que llama a la función, no duplicar
      if (btn.hasAttribute('onclick')) return;

      // Si está dentro de .password-container, hacemos toggle (fallback)
      const container = btn.closest('.password-container');
      if (!container) return;
      e.preventDefault();

      const input = container.querySelector('input[type="password"], input[type="text"]');
      if (!input) return;

      const wasPassword = input.type === 'password';
      input.type = wasPassword ? 'text' : 'password';
      btn.textContent = wasPassword ? 'Ocultar' : 'Mostrar';
      try { input.focus(); } catch (err) {}
    }, true);

        // llamar desde el init
        document.addEventListener('DOMContentLoaded', function () {
            attachRegistroHandler(); // activa validación en formulario de registro
            observeModalForms(); // activa observación de formularios en modales
            attachDireccionHandlers(); // activa validación en campo de dirección
            attachRealtimeValidation();  // activa validación en tiempo real mientras se escribe
        });



        // Activa la validación en tiempo real para campos del formulario
        function attachRealtimeValidation() {
        // Regla que permite nombres y apellidos con solo letras y espacios (mínimo 2, máximo 50 caracteres)
        const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}$/u;
        // Regla que permite validar correos electrónicos
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Función que valida un campo según su nombre
        function validateFieldByName(name, el, opts = { strict: false }) {
          // si no hay elemento, no hay nada que validar
          if (!el) return true;
          // valor limpio del campo
          const v = (el.value || '').trim();

          switch (name) {
            case 'nombre':
            case 'apellido':
              if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
              if (!nombreRegex.test(v)) { setFieldError(el, 'Solo letras (2-50).'); return false; }
              clearFieldError(el); return true;

            case 'username':
              if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
              if (v.length < 3) { setFieldError(el, 'Mínimo 3 caracteres.'); return false; }
              clearFieldError(el); return true;

            case 'correo':
            case 'email':
              if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
              if (!emailRegex.test(v)) { setFieldError(el, 'Correo inválido.'); return false; }
              clearFieldError(el); return true;

            case 'telefono':
              // permitir + al inicio y números; en tiempo real permitir incompletos
              if (v === '') { clearFieldError(el); return false; }
              if (!/^\+?[0-9]*$/.test(v)) { setFieldError(el, 'Sólo números o + al inicio.'); return false; }
              if (opts.strict && !/^\+?[0-9]{7,11}$/.test(v)) { setFieldError(el, 'Teléfono inválido (7-11 dígitos, opcional + al inicio).'); return false; }
              clearFieldError(el); return true;

            case 'direccion':
              if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
              if (v.length < 3) { setFieldError(el, 'Demasiado corta.'); return false; }
              if (!/\d+/.test(v)) { if (opts.strict) setFieldError(el, 'Debe incluir número (ej. 123).'); return false; }
              clearFieldError(el); return true;

            case 'rut':
              if (v === '') { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
              // aceptar mientras formato esté en progreso; comprobar checksum sólo si tiene al menos 2 chars
              if (!/^[0-9\.\-kK]+$/.test(v)) { setFieldError(el, 'Formato RUT inválido.'); return false; }
              if (opts.strict || v.replace(/[^0-9kK]/g,'').length >= 2) {
                if (!validarRutJS(v)) { setFieldError(el, 'RUT inválido.'); return false; }
              }
              clearFieldError(el); return true;

            case 'password':
              if (v === '') { clearFieldError(el); return false; }
              if (v.length < 8) { setFieldError(el, 'Requiere al menos 8 caracteres.'); return false; }
              if (!/[A-Z]/.test(v)) { setFieldError(el, 'Requiere al menos 1 mayúscula.'); return false; }
              if (!/[0-9]/.test(v)) { setFieldError(el, 'Requiere al menos 1 número.'); return false; }
              clearFieldError(el); return true;

            case 'confirm_password':
            case 'confirm-password':
              // comparar con password del mismo formulario (si existe)
              const form = el.closest('form');
              if (!form) { clearFieldError(el); return true; }
              const pwd = form.querySelector('input[name="password"]');
              const pv = pwd ? (pwd.value || '').trim() : '';
              if (v === '' && pv === '') { clearFieldError(el); return true; }
              if (pv !== v) { setFieldError(el, '*'); return false; }
              clearFieldError(el); return true;

            case 'cargo':
            case 'rol':
              if (!v) { if (opts.strict) setFieldError(el, 'Requerido.'); return false; }
              clearFieldError(el); return true;

            default:
              // campos no contemplados: limpiar error al escribir
              clearFieldError(el);
              return true;
          }
        }

        // delegación de eventos: input -> validación ligera, blur -> validación estricta
        // Cuando el usuario escribe en un campo (input), se hace una validación ligera
        document.addEventListener('input', function (e) {
          const t = e.target;
          // si no hay campo con nombre, no se valida
          if (!t || !t.name) return;
          // validación sin exigir todo
          validateFieldByName(t.name, t, { strict: false });
        }, true);

        // Cuando el usuario sale de un campo (blur), se hace una validación estricta
        document.addEventListener('blur', function (e) {
          const t = e.target;
          if (!t || !t.name) return;
          // validación completa
          validateFieldByName(t.name, t, { strict: true });
        }, true);

        // Cuando el usuario pega contenido en un campo, se valida después de un pequeño delay
        document.addEventListener('paste', function (e) {
          const t = e.target;
          if (!t || !t.name) return;
          // espera 10ms antes de validar
          setTimeout(() => validateFieldByName(t.name, t, { strict: false }), 10);
        });

       // Al cargar la página, se validan todos los campos que ya tienen valores
        document.querySelectorAll('input[name],textarea[name],select[name]').forEach(function (el) {
          // validación ligera inicial
          if (el.name) validateFieldByName(el.name, el, { strict: false });
        });
      }

      // Observa los formularios que se abren dentro de modales y les aplica validación
      function observeModalForms() {
        const modalBody = document.getElementById('modal-body');
        if (!modalBody) return;
        // Crea un observador que detecta cuando se agregan nodos al modal
        const mo = new MutationObserver(function (mutations) {
          mutations.forEach(m => {
            m.addedNodes.forEach(node => {
              // solo elementos HTML
              if (node.nodeType !== 1) return;
              // busca un formulario dentro del nodo agregado
              const form = node.querySelector('form.form-styled') || node.querySelector('form');
              if (!form) return;
              // al enviar el formulario, se valida
              form.addEventListener('submit', function (e) {
                 // si hay errores, no se envía
                if (!validateEditForm(form)) e.preventDefault();
              });
              // al escribir en cualquier campo, se limpia el error
              form.addEventListener('input', function (ev) { clearFieldError(ev.target); });
            });
          });
        });
        // activa el observador sobre el cuerpo del modal
        mo.observe(modalBody, { childList: true, subtree: true });
      }

      // Al cargar la página, se activan los handlers principales
      document.addEventListener('DOMContentLoaded', function () {
        attachRegistroHandler(); // activa validación en formulario de registro
        observeModalForms(); // activa validación en formularios dentro de modales
      });
    })();



/* TITULO BÚSQUEDA PARA LISTA DE USUARIOS */

    // Busca el formulario de búsqueda en la página
    const busquedaForm = document.querySelector('.busqueda-form');

    if (busquedaForm) {
    // Cuando el usuario envía el formulario
    busquedaForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // obtiene el término de búsqueda y lo limpia
        const termino = (this.busqueda.value || '').trim();
        // busca el contenedor donde se mostrarán los resultados
        const destino = document.getElementById('resultados');

        // si no existe el contenedor, muestra error en consola
        if (!destino) {
        console.error('No existe #resultados en la página.');
        return;
        }

        // Reglas: permitir consultas de 1 o más caracteres
        if (termino.length < 1) {
        destino.innerHTML = `
            <div class="tabla-placeholder" style="padding:14px; text-align:center; border:1px dashed var(--color-borde); border-radius:8px;">
            Ingresa <strong>uno o más caracteres</strong> y presiona <strong>Buscar</strong> para ver resultados.
            </div>
        `;
        return;
        }

        // construye la URL para hacer la búsqueda en el backend
        const url = `/php/inicio_sesion/superadmin/usuarios_contenido.php?busqueda=${encodeURIComponent(termino)}`;

        // hace la solicitud al servidor
        fetch(url)
        // convierte la respuesta en texto HTML
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            // convierte el HTML en documento
            const doc    = parser.parseFromString(html, 'text/html');
            // busca el nuevo contenido
            const nuevo  = doc.querySelector('#resultados');

            // si no se encuentra el contenedor de resultados, muestra mensaje de error
            if (!nuevo) {
            destino.innerHTML = `
                <div class="tabla-placeholder" style="padding:14px; text-align:center; border:1px dashed var(--color-borde); border-radius:8px;">
                Error al cargar resultados. Intenta nuevamente.
                </div>
            `;
            return;
            }

            // reemplaza el contenido actual con los nuevos resultados
            destino.innerHTML = nuevo.innerHTML;
        })
        .catch(err => {
          // si ocurre un error en la solicitud, muestra mensaje de error
            console.error('Error al obtener resultados de búsqueda:', err);
            destino.innerHTML = `
            <div class="tabla-placeholder" style="padding:14px; text-align:center; border:1px dashed var(--color-borde); border-radius:8px;">
                Error al cargar resultados. Revisa tu conexión.
            </div>
            `;
        });
    });
    } else {
    // si no se encuentra el formulario, muestra advertencia en consola
    console.warn('Formulario de búsqueda (.busqueda-form) no encontrado.');
    }

// TITULO LISTA DE USUARIOS

    // SIN FUNCION

// TITULO ARCHIVO JS

    // SIN FUNCION


/*  --------------------------------------------------------------------------------------------------------------
    ---------------------------------------- FIN ITred Spa usuarios_contenido .JS --------------------------------
    --------------------------------------------------------------------------------------------------------------  */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

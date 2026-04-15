// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa formulario_login .JS -----------------------------------
    --------------------------------------------------------------------------------------------------------------- */

// TITULO INICIALIZACIÓN Y PROTECCIÓN CSRF

    // SIN CODIGO



// TITULO REDIRECCIÓN SEGÚN EL ROL DEL USUARIO

    // SIN CODIGO

    

// TITULO GENERACIÓN DEL TOKEN CSRF

    // SIN CODIGO

    

// TITULO FORMULARIO DE INICIO DE SESIÓN

    // Función para mostrar el formulario de login o registro según el parámetro recibido
    function mostrarFormulario(formulario) {
        // Obtener los formularios por su ID
        const loginForm = document.getElementById('loginForm');
        const registroForm = document.getElementById('registroForm');

        // Obtener los enlaces por su ID
        const enlacesLogin = document.getElementById('enlacesLogin');
        const linkToRegistro = document.getElementById('linkToRegistro');
        const loginBoton = document.getElementById('loginBoton');

        // Verificar que ambos formularios y enlaces existan
        if (!loginForm || !registroForm || !enlacesLogin || !linkToRegistro) {
            console.error('No se encontraron los formularios o enlaces. Verifica los IDs en tu HTML.'); // Mensaje de error si no se encuentran
            return; // Salir de la función si no se encuentran
        }

        // Ocultar ambos formularios
        loginForm.style.display = 'none';
        registroForm.style.display = 'none';
        loginBoton.style.display = 'none'; // Ocultar el botón de login

        // Ocultar ambos enlaces
        enlacesLogin.style.display = 'none';
        linkToRegistro.style.display = 'none';

        // Mostrar el formulario correspondiente
        if (formulario === 'loginForm') {
            loginForm.style.display = 'block'; // Mostrar el formulario de login
            linkToRegistro.style.display = 'block'; // Mostrar el enlace para ir a registro
            loginBoton.style.display = 'block'; // Mostrar el botón de login
        } else if (formulario === 'registroForm') {
            registroForm.style.display = 'block'; // Mostrar el formulario de registro
            enlacesLogin.style.display = 'block'; // Mostrar el enlace para ir a login
        }
    }

    function mostrarError(elementId, message) {
        // Obtener el elemento del div correspondiente al ID proporcionado
        const errorDiv = document.getElementById(elementId);

        // Verificar si el div de error existe
        if (errorDiv) {
            // Establecer el mensaje de error en el div
            errorDiv.textContent = message;

            // Mostrar el div que contiene el mensaje de error
            errorDiv.style.display = 'block'; // Muestra el div con el error
        }
    }

    // Mostrar mensaje de error segun el tipo de error presentado
    function obtenerMensajeDeError(errorCode) {
        // Comenzar una estructura de control switch para evaluar el código de error
        switch (errorCode) {
            // Caso para el error de inicio de sesión: correo o contraseña incorrectos
            case '1':
                // Retornar el mensaje correspondiente
                return 'Correo electrónico o contraseña incorrecta. Por favor, intenta nuevamente.';
            
            // Caso para el error de inicio de sesión: correo no registrado o inválido
            case '2':
                // Retornar el mensaje correspondiente
                return 'Correo electrónico no registrado o inválido. Por favor, verifica y vuelve a intentar.';
            
            // Caso para el error de inicio de sesión: demasiados intentos fallidos
            case '3':
                // Retornar el mensaje correspondiente
                return 'Demasiados intentos fallidos. Por favor, intenta nuevamente en 15 minutos.';
            
            // Caso para el error de CAPTCHA incorrecto
            case '4':
                // Retornar el mensaje correspondiente
                return 'Código CAPTCHA incorrecto. Por favor, intenta nuevamente.';

            // Caso para errores de registro: RUT inválido
            case 'rut_invalido':
                // Retornar el mensaje correspondiente
                return 'El RUT proporcionado no es válido. Por favor, verifica e ingresa un RUT válido.';
            
            // Caso para errores de registro: rol no válido
            case 'rol_invalido':
                // Retornar el mensaje correspondiente
                return 'Rol seleccionado no es válido. Por favor, selecciona un rol correcto.';
            
            // Caso para errores de registro: las contraseñas no coinciden
            case 'contraseñas_no_coinciden':
                // Retornar el mensaje correspondiente
                return 'Las contraseñas no coinciden. Por favor, inténtalo de nuevo.';
            
            // Caso para errores de registro: usuario ya existente
            case 'usuario_existente':
                // Retornar el mensaje correspondiente
                return 'El nombre de usuario, correo electrónico o RUT ya están en uso.';

            // Caso por defecto para cualquier otro error no reconocido
            default:
                // Retornar un mensaje genérico de error
                return 'Ocurrió un error. Por favor, intenta nuevamente.';
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Mostrar el formulario de inicio de sesión por defecto
        mostrarFormulario('loginForm');

        // Añadir eventos a los enlaces para mostrar formularios
        // Evento para mostrar el formulario de inicio de sesión al hacer clic en el enlace correspondiente
        document.getElementById('showLoginForm').addEventListener('click', function (e) {
            e.preventDefault();
            mostrarFormulario('loginForm');
        });

        // Evento para mostrar el formulario de registro al hacer clic en el enlace correspondiente
        document.getElementById('showRegistroForm').addEventListener('click', function (e) {
            e.preventDefault();
            mostrarFormulario('registroForm');
        });

        // Mostrar el mensaje de error en el formulario correspondiente si hay un parámetro de error en la URL
        const errorParam = new URLSearchParams(window.location.search).get('error');
        if (errorParam) {
            if (['1', '2', '3', '4'].includes(errorParam)) {
                // Si el error está relacionado con el inicio de sesión
                mostrarFormulario('loginForm');
                mostrarError('loginError', obtenerMensajeDeError(errorParam));
            } else {
                // Si el error está relacionado con el registro
                mostrarFormulario('registroForm');
                mostrarError('registroError', obtenerMensajeDeError(errorParam));
            }

            // Eliminar el parámetro 'error' de la URL sin recargar la página
            const url = new URL(window.location);
            url.searchParams.delete('error');
            window.history.replaceState({}, document.title, url.pathname);
        }
    });


// TITULO CAPTCHA ALFANUMÉRICO

    // SIN CODIGO

    

// TITULO CAPTCHA DE IMÁGENES (OCULTO POR DEFECTO)

    // SIN CODIGO



// TITULO ENLACE PARA REGISTRO

    // SIN CODIGO



// TITULO ARCHIVO JS

    // SIN CODIGO



/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa formulario_login .JS ----------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/

/*
------------------------------------------------------------------------------------------------------------
------------------------------------- INICIO ITred Spa log_registros .JS -----------------------------------
------------------------------------------------------------------------------------------------------------
*/

// TITULO DEFINICIÓN DE CONSTANTES

  // SIN CODIGO


// TITULO CONTROLADORES DE EVENTOS

  // Carga los eventos DOM
    document.addEventListener("DOMContentLoaded", function () {
      // Selecciona todos los elementos que son botones específicos
      var botones = document.querySelectorAll(
          ".nav-button, #botonEditarPerfil, #botonEditar, #botonEliminar, #botonRegistrar, #BotonAtras, #BotonGuardar"
      );

      // Agrega controladores de eventos de clic a todos los botones seleccionados
      botones.forEach(function (boton) {
          boton.addEventListener("click", function () {
              // Obtiene el texto del botón (acción realizada) o utiliza un atributo data-action
              var accion = boton.getAttribute('data-action') || boton.textContent.trim();

              // Obtiene información adicional si está disponible
              var targetUser = boton.getAttribute('data-target-user') || ''; // Usuario objetivo
              var changes = boton.getAttribute('data-changes') || ''; // Cambios realizados

              // Llama a la función para registrar la actividad con la acción actual
              registrarActividad(accion, targetUser, changes);
          });
      });

    // TITULO REGISTRAR ACTIVIDAD

    // Función para registrar la actividad en el servidor
    function registrarActividad(accion, targetUser = '', changes = '') {
        // Crea una nueva solicitud XMLHttpRequest
        var xhr = new XMLHttpRequest();
        // Configura la solicitud POST para enviar datos a log_registros.php
        xhr.open("POST", `/php/inicio_sesion/seguridad/log_registros.php`, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Define la función que maneja la respuesta del servidor
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log(xhr.responseText); // Muestra la respuesta del servidor en la consola
            }
        };

        // Prepara los datos a enviar: acción, target_user y changes
        var data = "accion=" + encodeURIComponent(accion) +
                  "&target_user=" + encodeURIComponent(targetUser) +
                  "&changes=" + encodeURIComponent(changes);

        // Envía la solicitud con los datos preparados
        xhr.send(data);
    }
  });
  

/*  --------------------------------------------------------------------------------------------------------------
    ---------------------------------------- FIN ITred Spa log_registros .JS -------------------------------------
    --------------------------------------------------------------------------------------------------------------  */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ


// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa inicio_roles .JS ---------------------------------------
    --------------------------------------------------------------------------------------------------------------- */

// TITULO BODY

  // SIN CODIGO


// TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN

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



// TITULO OBTENER DATOS DEL USUARIO DESDE LA SESIÓN

  // SIN CODIGO



// TITULO DEFINIR LA RUTA DE LA PÁGINA DE USUARIOS SEGÚN EL ROL

  // SIN CODIGO



// TITULO BARRA DE NAVEGACIÓN EDITOR_LOGIN

  // SIN CODIGO



// TITULO BARRA DE NAVEGACIÓN GENERAL

  // SIN CODIGO



// TITULO CONTENEDOR PRINCIPAL DEL CONTENIDO

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
          // Muestra la respuesta del servidor en la consola
          console.log(xhr.responseText);
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



// TITULO ARCHIVO JS

  // SIN CODIGO



/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa inicio_roles .JS --------------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
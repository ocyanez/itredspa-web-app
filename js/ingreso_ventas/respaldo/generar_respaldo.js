// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa descargar_respaldo .JS ---------------------------------
    --------------------------------------------------------------------------------------------------------------- */


// TITULO BODY 

    // SIN CODIGO

// TITULO CONTENEDOR PRINCIPAL PARA ESTILOS DE PLANTILLA 

    // SIN CODIGO

// TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN 

    // SIN CODIGO

// TITULO RESPALDO 

// Primera definición de la función seleccionar
function seleccionar(formato) {
    // Desmarcar todos los botones primero
    document.querySelectorAll('.formato-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    // Marcar el botón seleccionado
    document.getElementById('btn-' + formato).classList.add('selected');
    
    // Establecer el valor del formato oculto
    document.getElementById('formato').value = formato;
    
    // Habilitar el botón de descarga
    document.getElementById('descargarBtn').disabled = false;
}
    // Función básica para seleccionar formato si no existe en el archivo JS externo
    function seleccionar(formato) {
    // Remover clase activa de todos los botones
            document.querySelectorAll('.formato-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Agregar clase activa al botón seleccionado
            document.getElementById('btn-' + formato).classList.add('active');
            
            // Establecer el formato en el input hidden
            document.getElementById('formato').value = formato;
            
            // Habilitar el botón de descarga
            document.getElementById('descargarBtn').disabled = false;
        }
        
        // Función toggleMenu básica para evitar errores
        function toggleMenu() {
            // Función vacía o redirigir según necesites
            console.log('toggleMenu called');
        }

        // Tercera función seleccionar
        function seleccionar(formato) {
            // Remover clase activa de todos los botones
            document.querySelectorAll('.formato-btn').forEach(btn => {
                btn.classList.remove('activo');
            });
            
            // Agregar clase activa al botón seleccionado
            if (formato === 'sql') {
                document.getElementById('btn-sql').classList.add('activo');
            } else if (formato === 'excel_simple') {
                document.getElementById('btn-excel-simple').classList.add('activo');
            } else if (formato === 'excel') {
                document.getElementById('btn-excel').classList.add('activo');
            }
            
            // Establecer el formato seleccionado
            document.getElementById('formato').value = formato;
            
            // Habilitar el botón de descarga
            document.getElementById('descargarBtn').disabled = false;
        }


// TITULO ARCHIVO JS

    // SIN CODIGO    


/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa descargar_respaldo .JS --------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
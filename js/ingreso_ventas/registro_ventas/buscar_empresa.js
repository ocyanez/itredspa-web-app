// validaciones_factura.js
// Responsabilidad: Validar el RUT ingresado contra la lista cargada.

function verificarRutEmpresa() {
    // Obtenemos los campos por sus IDs originales
    var inputRut = document.getElementById('rutEmpresa');
    var inputNombre = document.getElementById('nombreEmpresa');

    // Validación de existencia de elementos
    if(!inputRut || !inputNombre) return;

    // 1. Normalizar lo que escribió el usuario (Sin puntos, Mayúsculas)
    var rutUsuario = inputRut.value.trim().toUpperCase();
    var rutParaBuscar = rutUsuario.replace(/\./g, '').replace(/\s/g, ''); 

    // 2. Buscar en la variable global (generada por el PHP)
    if (typeof baseDatosEmpresas !== 'undefined' && baseDatosEmpresas.hasOwnProperty(rutParaBuscar)) {
        
        // === ENCONTRADO ===
        var nombreEncontrado = baseDatosEmpresas[rutParaBuscar];

        // A. Autocompletar
        inputNombre.value = nombreEncontrado;
        
        // B. Bloquear campo (Feedback visual)
        inputNombre.readOnly = true;
        inputNombre.style.backgroundColor = "#e9ecef"; 

        // C. Alerta al usuario
        // Usamos un pequeño timeout para asegurar que el navegador renderice el cambio de color primero
        setTimeout(function() {
            alert("⚠️ ATENCIÓN:\nEl RUT " + rutUsuario + " ya está registrado a nombre de:\n\n'" + nombreEncontrado + "'.\n\nEl sistema ha cargado el nombre automáticamente.");
        }, 100);

    } else {
        // === NO ENCONTRADO (RUT NUEVO) ===
        // Si el campo estaba bloqueado previamente, lo liberamos
        if (inputNombre.readOnly) {
            inputNombre.readOnly = false;
            inputNombre.style.backgroundColor = "white"; 
            inputNombre.value = ""; // Limpiamos para permitir escribir
        }
    }
}

// Asignar el evento al cargar el DOM
document.addEventListener("DOMContentLoaded", function() {
    var inputRut = document.getElementById('rutEmpresa');
    if (inputRut) {
        // 'blur' se activa al salir del input (clic fuera o Tab)
        inputRut.addEventListener('blur', verificarRutEmpresa);
    }
});
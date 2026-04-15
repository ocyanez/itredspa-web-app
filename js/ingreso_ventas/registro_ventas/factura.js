// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa factura .JS --------------------------------------------
    --------------------------------------------------------------------------------------------------------------- */

// TITULO HTML

    // SIN FUNCION

// TITULO BODY

    // SIN FUNCION

// TITULO DATOS DE FACTURA

    function prepararEnvio() {
        const filas = document.querySelectorAll('#tabla-productos-body tr');
        
        // Le decimos al PHP cuántos productos van
        const inputTotal = document.getElementById('totalProductos');
        if(inputTotal) {
            inputTotal.value = filas.length;
        }

        // Recorremos cada fila para ponerle nombre ordenado a los inputs
        // Así el PHP los puede leer con el ciclo for($i...)
        filas.forEach((fila, index) => {
            // Asignar nombres: producto_0_codigo, producto_1_codigo, etc.
            const inputCodigo = fila.querySelector('.codigo');
            if(inputCodigo) inputCodigo.name = 'producto_' + index + '_codigo';

            const inputDesc = fila.querySelector('.descripcion');
            if(inputDesc) inputDesc.name = 'producto_' + index + '_descripcion';

            const inputCant = fila.querySelector('.cantidad');
            if(inputCant) inputCant.name = 'producto_' + index + '_cantidad';

            const inputPrecio = fila.querySelector('.precio');
            if(inputPrecio) inputPrecio.name = 'producto_' + index + '_precio';

            const inputAdic = fila.querySelector('.impuestoAdic');
            if(inputAdic) inputAdic.name = 'producto_' + index + '_adicional';

            const inputDescVal = fila.querySelector('.descuento');
            if(inputDescVal) inputDescVal.name = 'producto_' + index + '_descuento';
        });

        return true; // Deja que el formulario se envíe
    }

    document.addEventListener('DOMContentLoaded', function() {
    // 1. Buscamos el elemento puente
    const puente = document.getElementById('mensajeServidor');

    // 2. Si existe, significa que PHP mandó un mensaje
    if (puente) {
        // Leemos el mensaje del atributo data-texto
        const mensaje = puente.getAttribute('data-texto');
        
        if (mensaje) {
            // Mostramos la alerta
            alert(mensaje);

            // Limpiamos la URL (quitamos ?mensaje=... y ?id_editar=...)
            // window.location.pathname devuelve la URL sin los parámetros ?...
            window.location.href = window.location.pathname;
        }
    }
});

      // vista previa del logo al seleccionar archivo
      document.getElementById('logoCliente').addEventListener('change', function () {
      // obtiene el primer archivo seleccionado
      const file = this.files[0];
      if (file) {
          // crea lector de archivos
          const reader = new FileReader();
          reader.onload = function (e) {
          // muestra la imagen en el contenedor de vista previa
          document.getElementById('previewLogo').innerHTML = `<img src="${e.target.result}" alt="Logo cliente">`;
          };
          // convierte el archivo a base64
          reader.readAsDataURL(file);
      }
      });


// TITULO PRODUCTOS DE FACTURA

    // función para agregar una nueva fila de producto a la tabla
    function agregarFilaProducto() {
      const tbody = document.getElementById('tabla-productos-body');
      const fila = document.createElement('tr');

      // estructura de la fila con inputs para cada dato del producto
      fila.innerHTML = `
        <td><input type="text" class="codigo"></td>
         <td class="producto-cell">
            <input type="text"
                  class="descripcion autocomplete-producto"
                  placeholder="Buscar producto...">
            <div class="autocomplete-list"></div>
        </td>
        <td><input type="number" class="cantidad" placeholder="0"></td>
        <td><input type="text" class="precio" placeholder="0"></td>
        <td><input type="number" class="impuestoAdic" placeholder="0"></td>
        <td><input type="number" class="descuento" placeholder="0"></td>
        <td class="valor">0</td>

      `;

      // agrega la fila a la tabla
      tbody.appendChild(fila);
    }


    // Bloquea letras en campos de precio
    document.addEventListener('keydown', function (e) {
      if (e.target.classList.contains('precio')) {
        const tecla = e.key;

        // si no es número ni tecla permitida, se bloquea
        const permitidos = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', '.'];
        const esNumero = /^[0-9]$/.test(tecla);

        if (!esNumero && !permitidos.includes(tecla)) {
          e.preventDefault();
        }
      }
    });


    // elimina la última fila de la tabla de productos
    function eliminarUltimaFila() {
      const tbody = document.getElementById('tabla-productos-body');
      if (tbody.rows.length > 0) {
        // elimina la última fila
        tbody.deleteRow(tbody.rows.length - 1);
        // recalcula los totales
        calcularTotales(); 
      }
    }


    // formatea número con puntos de miles (ej: 10000 → "10.000")
    function formatMilesConPunto(num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // convierte string con puntos a número (ej: "10.000" → 10000)
    function parseCLP(str) {
      return parseFloat(str.replace(/\./g, '')) || 0;
    }


  // Cerrar dropdown al hacer click fuera
  document.addEventListener("click", function (e) {
      document.querySelectorAll(".autocomplete-list").forEach(list => {
          if (!list.parentElement.contains(e.target)) {
              list.innerHTML = "";
          }
      });
  });

    // TITULO DE TOTALES

    // calcula los totales de la factura: neto, iva, adicional y total
    function calcularTotales() {
      let neto = 0; // acumulador del valor neto de todos los productos
      let impuestoAdicional = 0; // acumulador del impuesto adicional total

      // Recorre todas las filas de la tabla de productos
      document.querySelectorAll('#tabla-productos-body tr').forEach(row => {
        const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0; // cantidad del producto
        const precioStr = row.querySelector('.precio').value; // precio como string con puntos
        const precio = parseCLP(precioStr); // convierte precio a número
        const imptoAdic = parseFloat(row.querySelector('.impuestoAdic').value) || 0; // porcentaje de impuesto adicional
        const descuento = parseFloat(row.querySelector('.descuento').value) || 0; // porcentaje de descuento

        // calcula valor base (cantidad * precio)
        let valor = cantidad * precio;
        // aplica descuento al valor
        valor -= valor * (descuento / 100);
        // calcula impuesto adicional
        const adicional = valor * (imptoAdic / 100);

        // muestra el valor calculado en la celda correspondiente, formateado con puntos de miles
        row.querySelector('.valor').textContent = formatMilesConPunto(Math.round(valor));
        // acumula el neto y el impuesto adicional
        neto += valor;
        impuestoAdicional += adicional;
      });

      // calcula IVA (19% del neto)
      const iva = neto * 0.19;
      // calcula el total final (neto + IVA + impuesto adicional)
      const total = neto + iva + impuestoAdicional;

      // actualiza los campos de totales
      document.getElementById('valorNeto').value = formatMilesConPunto(Math.round(neto));
      document.getElementById('iva').value = formatMilesConPunto(Math.round(iva));
      document.getElementById('impuestoAdicional').value = formatMilesConPunto(Math.round(impuestoAdicional));
      document.getElementById('totalFactura').value = formatMilesConPunto(Math.round(total));
    }

    // formatea el campo precio mientras se escribe y actualiza totales
    document.addEventListener('input', function (e) {
      // Si el campo modificado es precio, formatea con puntos de miles
      if (e.target.classList.contains('precio')) {
        const raw = e.target.value.replace(/\./g, ''); // elimina puntos
        if (!isNaN(raw)) {
          e.target.value = formatMilesConPunto(raw); // vuelve a formatear con puntos
        }
      }

      // recalcula totales si se modifica cantidad, precio, impuesto o descuento
      if (
        // Si el campo modificado es cantidad
        e.target.classList.contains('cantidad') || 
        // O si es precio
        e.target.classList.contains('precio') || 
        // O si es impuesto adicional
        e.target.classList.contains('impuestoAdic') || 
        // O si es descuento
        e.target.classList.contains('descuento')
      ) {
        calcularTotales(); // vuelve a calcular totales en tiempo real
      }
    });




    // formatea el rut de la empresa mientras se escribe
    const rutInput = document.getElementById('rutEmpresa');

    rutInput.addEventListener('input', function (e) {
      // limpia caracteres no válidos
        let raw = e.target.value.replace(/[^0-9kK]/g, '').toUpperCase();

        // Separar cuerpo y dígito verificador
        let cuerpo = raw.slice(0, 8); // solo números
        // solo 1 carácter (K o número)
        let dv = raw.slice(8, 9);    

        // Si el dígito verificador no es válido, lo eliminamos
        if (dv && !/^[0-9K]$/.test(dv)) {
        dv = '';
        }

        // Formatear cuerpo con puntos
        cuerpo = cuerpo.replace(/^(\d{1,2})(\d{3})(\d{3})$/, '$1.$2.$3');

        // Unir con guión si hay dígito verificador
        e.target.value = dv ? `${cuerpo}-${dv}` : cuerpo;
    });

    rutInput.addEventListener('keydown', function (e) {
        const key = (e.key || '').toUpperCase();

        // Permitir navegación y edición
        const permitidos = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
        if (permitidos.includes(key)) return;

        // Evitar que se escriba K antes de la posición 9
        const valorActual = rutInput.value.replace(/[^0-9kK]/g, '').toUpperCase();
        if (key === 'K' && valorActual.length < 8) {
        e.preventDefault();
        }

        // Limitar a 9 caracteres útiles (8 números + 1 verificador)
        if (/^[0-9K]$/.test(key) && valorActual.length >= 9) {
        e.preventDefault();
        }
    });




 // Función principal para guardar la factura
function guardarFactura(botonGuardar) {
    calcularTotales();

    const nombre = document.getElementById('nombreEmpresa').value.trim();
    const giro = document.getElementById('giroEmpresa').value.trim();
    const rut = document.getElementById('rutEmpresa').value.trim().replace(/\./g, '');
    const factura = document.getElementById('numeroFactura').value.trim();

    const filas = document.querySelectorAll('#tabla-productos-body tr');
    const datos = new FormData();

    if (!nombre || !rut || !factura || filas.length === 0) {
        alert("Completa todos los campos obligatorios y al menos un producto.");
        return;
    }

    datos.append('nombreEmpresa', nombre);
    datos.append('giroEmpresa', giro);
    datos.append('rutEmpresa', rut);
    datos.append('numeroFactura', factura);

    let numProducto = 0;
    filas.forEach(fila => {
        const codigo = fila.querySelector('.codigo')?.value.trim();
        const descripcion = fila.querySelector('.descripcion')?.value.trim();
        const cantidad = fila.querySelector('.cantidad')?.value.trim();
        const precio = fila.querySelector('.precio')?.value.trim();
        const adicional = fila.querySelector('.impuestoAdic')?.value.trim();
        const descuento = fila.querySelector('.descuento')?.value.trim();

        if (codigo && descripcion) {
            datos.append(`producto_${numProducto}_codigo`, codigo);
            datos.append(`producto_${numProducto}_descripcion`, descripcion);
            datos.append(`producto_${numProducto}_cantidad`, cantidad);
            datos.append(`producto_${numProducto}_precio`, precio);
            datos.append(`producto_${numProducto}_adicional`, adicional);
            datos.append(`producto_${numProducto}_descuento`, descuento);
            numProducto++;
        }
    });

    datos.append('totalProductos', numProducto);

    const logoInput = document.getElementById('logoCliente');
    const logoFile = logoInput.files[0];
    if (logoFile) {
        datos.append('logoEmpresa', logoFile);
    }

    // Manejo seguro del botón (por si acaso no se pasa el argumento)
    let textoOriginal = 'Guardar';
    if (botonGuardar) {
        textoOriginal = botonGuardar.textContent;
        botonGuardar.textContent = 'Guardando...';
        botonGuardar.disabled = true;
    }

    fetch('/php/ingreso_ventas/registro_ventas/procesar_factura.php', {
        method: 'POST',
        body: datos
    })
    .then(res => res.text())
    .then(info => {
        console.log("Respuesta:", info);
        
        
        // Convertimos todo a minúsculas para evitar problemas con mayúsculas
        const resp = info.toLowerCase();

        // Buscamos palabras clave más flexibles ("exito", "guardada", "correctamente")
        if (resp.includes('exito') || resp.includes('guardada') || resp.includes('correctamente')) {
            alert('Factura guardada exitosamente'); 
        } else {
            alert('Error al guardar factura: ' + info);
        }
        
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al conectar con el servidor: ' + error.message);
    })
    .finally(() => {
        if (botonGuardar) {
            botonGuardar.textContent = textoOriginal;
            botonGuardar.disabled = false;
        }
    });
}




// Función que controla el botón de alerta: alterna entre formulario y tabla de problemas
function toggleAlertaMenu() {
    const menu = document.getElementById("menuAlerta"); 
    const facturaContenedor = document.querySelector(".factura-contenedor");
    const guardarBox = document.querySelector(".guardar-box");
    
    const isVisible = menu.style.display === "block"; 
    
    if (isVisible) {
        // Volver al formulario
        menu.style.display = "none";
        facturaContenedor.style.display = "block";
        guardarBox.style.display = "flex";
    } else {
        // Mostrar solo tabla de problemas
        menu.style.display = "block";
        facturaContenedor.style.display = "none";
        guardarBox.style.display = "none";
    }
}




// TITULO JS

    // SIN FUNCION



/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa factura .JS -------------------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

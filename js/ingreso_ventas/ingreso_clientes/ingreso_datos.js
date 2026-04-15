// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  --------------------------------------------------------------------------------------------------------------
    -------------------------------------- INICIO ITred Spa ingreso_datos .JS ------------------------------------
    -------------------------------------------------------------------------------------------------------------- */


// TITULO HTML

    // Función para buscar productos en la tabla por SKU o Nombre
    function buscarProducto() {
        // Declaramos las variables necesarias
        var input, filter, table, tr, tdSku, tdNom, i, txtValueSku, txtValueNom;
        input = document.getElementById("buscador_producto");
        filter = input.value.toUpperCase();
        table = document.getElementById("tabla_productos");
        tr = table.getElementsByTagName("tr");
        // Recorremos todas las filas de la tabla y ocultamos las que no coinciden con la búsqueda
        for (i = 0; i < tr.length; i++) {
            // Columna 0 es SKU, Columna 1 es Nombre
            tdSku = tr[i].getElementsByTagName("td")[0];
            tdNom = tr[i].getElementsByTagName("td")[1];
            // Si la fila tiene datos en las columnas SKU o Nombre
            if (tdSku || tdNom) {
                txtValueSku = tdSku.textContent || tdSku.innerText;
                txtValueNom = tdNom.textContent || tdNom.innerText;
                // Comprobamos si el texto coincide con el filtro de búsqueda
                if (txtValueSku.toUpperCase().indexOf(filter) > -1 || txtValueNom.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }       
        }
    }

    // Definimos la función "peticionIframe". Es nuestro mensajero privado.
    // Recibe la dirección (url) a donde ir y las opciones (qué llevar).
    function peticionIframe(url, opciones = {}) {
    // Creamos una "promesa". Es como decirle al resto del código: "Espérame aquí, volveré con datos".
    return new Promise((resolve, reject) => {
        
        // Creamos un nombre único (como un número de ticket) para no mezclar envíos si hacemos varios a la vez.
        const idUnico = 'iframe_req_' + Date.now() + Math.floor(Math.random() * 10000);
        
        // Creamos una etiqueta <iframe> en la memoria. Es como una mini-ventana de navegador.
        const iframe = document.createElement('iframe');
        iframe.name = idUnico; // Le ponemos el nombre único.
        iframe.id = idUnico;   // También le ponemos el ID único.
        iframe.style.display = 'none'; // ¡Importante! La hacemos invisible para que no estorbe en la pantalla.
        document.body.appendChild(iframe); // La pegamos en la página para que empiece a existir.

        // Esta parte se activa SOLA cuando el mensajero vuelve (cuando la ventanita termina de cargar).
        iframe.onload = function() {
            try {
                // Intentamos mirar dentro de la ventanita invisible.
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                
                // Leemos todo el texto que el servidor escribió en esa ventanita.
                const respuestaTexto = doc.body.innerText || doc.body.textContent;
                
                // Preparamos el paquete de respuesta para engañar al código y que crea que usó Ajax normal.
                const respuestaSimulada = {
                    ok: true,      // Decimos "todo salió bien".
                    status: 200,   // Código de éxito.
                    text: () => Promise.resolve(respuestaTexto) // Entregamos el texto que leímos.
                };
                
                // Cumplimos la promesa: "¡Aquí tienes los datos que pediste!".
                resolve(respuestaSimulada);

                // Limpieza esperamos un segundo y luego borramos la ventanita invisible para no dejar basura.
                setTimeout(() => { 
                    if(document.body.contains(iframe)) document.body.removeChild(iframe); 
                }, 1000);

            } catch (err) {
                // Si algo sale mal leyendo la ventana, avisamos del error.
                console.error("Error leyendo iframe:", err);
                reject(err);
            }
        };

        // Si la ventanita falla al cargar (error de internet), avisamos.
        iframe.onerror = function() {
            reject(new Error("Error de red via Iframe"));
        };

        // Revisamos si queremos PEDIR datos (GET) o ENVIAR datos (POST).
        // Si no especificamos, asumimos que es GET.
        const metodo = (opciones.method || 'GET').toUpperCase();

        if (metodo === 'GET') {
            // si es get Solo le decimos a la ventanita "ve a esta dirección".
            iframe.src = url;
        } else if (metodo === 'POST') {
            // si es post Necesitamos enviar un paquete.
            
            // Creamos un formulario invisible (como un sobre de carta).
            const form = document.createElement('form');
            form.target = idUnico; // Le decimos: "El destino de este sobre es la ventanita invisible que creamos antes".
            form.method = 'POST';  // Método de envío.
            form.action = url;     // Dirección del servidor.
            form.style.display = 'none'; // Ocultamos el formulario.
            form.enctype = "multipart/form-data"; // Esto permite enviar archivos (como Excel) si es necesario.

            // si los datos vienen empaquetados como FormData (usado para archivos y formularios complejos)
            if (opciones.body && opciones.body instanceof FormData) {
                // revisamos cada dato dentro del paquete.
                for (let [clave, valor] of opciones.body.entries()) {
                    // si el dato NO es un archivo (es texto simple)...
                    if (!(valor instanceof File)) {
                        // creamos una cajita oculta (input) dentro del formulario.
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = clave; // nombre del dato (ej: "rut").
                        input.value = valor; // valor del dato (ej: "123456").
                        form.appendChild(input); // metemos la cajita en el sobre.
                    }
                }
            } 
            // si los datos vienen como lista simple de URL
            else if (opciones.body && opciones.body instanceof URLSearchParams) {
                 // hacemos lo mismo crear cajitas ocultas para cada dato.
                 opciones.body.forEach((valor, clave) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = clave;
                    input.value = valor;
                    form.appendChild(input);
                });
            }

            // pegamos el formulario en la página (aunque sea invisible) para poder enviarlo.
            document.body.appendChild(form);
            // los archivos no se pueden copiar por seguridad. 
            // así que si nos dieron el ID del archivo original, lo "robamos" prestado un momento.
            if (opciones.fileInputId) {
                const originalFileInput = document.getElementById(opciones.fileInputId);
                if (originalFileInput) {
                    // Creamos una copia vacía del selector de archivos para dejarla en la pantalla.
                    const clone = originalFileInput.cloneNode(true);
                    // Ponemos la copia donde estaba el original.
                    originalFileInput.parentNode.insertBefore(clone, originalFileInput);
                    // Le ponemos el nombre correcto al original.
                    originalFileInput.name = opciones.fileInputName || originalFileInput.name;
                    // Movemos el original (que tiene el archivo) dentro de nuestro formulario oculto.
                    form.appendChild(originalFileInput);
                }
            }

            // enviamos el formulario
            form.submit();
            
            // Después de 2 segundos, borramos el formulario oculto.
            // El archivo original se perderá aquí, pero como ya se envió, no importa
            setTimeout(() => { if(document.body.contains(form)) document.body.removeChild(form); }, 2000);
        }
    });
}

    // Función para cambiar entre las pestañas de Productos, Clientes y Ventas
   function cambiarPestana(nombreTab) {
    // 1. Ocultar todos los contenidos
    const contenidos = document.getElementsByClassName('contenido-pestana');
    for (let i = 0; i < contenidos.length; i++) {
        contenidos[i].style.display = 'none';
    }

    // 2. Quitar la clase 'activa' de todos los botones
    const botones = document.getElementsByClassName('btn-pestana');
    for (let i = 0; i < botones.length; i++) {
        botones[i].classList.remove('activa');
    }

    // 3. Mostrar el contenido seleccionado
    const tabSeleccionado = document.getElementById('seccion-' + nombreTab);
    if (tabSeleccionado) {
        tabSeleccionado.style.display = 'block';
    }

    // 4. Activar el botón correcto (SEGÚN TU NUEVO ORDEN HTML)
    let index = -1;

    if (nombreTab === 'clientes')  index = 0; // Ahora Clientes es el primero
    if (nombreTab === 'productos') index = 1; // Productos el segundo
    if (nombreTab === 'factura')   index = 2; // Factura el tercero
    if (nombreTab === 'ventas')    index = 3; // Ventas el cuarto

    // Solo activamos si encontramos el índice correcto
    if (index !== -1 && botones[index]) {
        botones[index].classList.add('activa');
    }
}

// TITULO BODY

    // SIN FUNCION

// TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN

    // SIN FUNCION

// TITULO DE INGRESO DE DATOS

    // SIN FUNCION

// TITULO INGRESO FACTURA

    // SIN FUNCION

// TITULO INGRESO PRODUCTOS

    function registrar_producto(event) {
        // 1. Evitamos que la página se recargue
        event.preventDefault();

        // 2. Capturamos el formulario y el botón
        const form = document.getElementById('formularioProducto');
        const boton = form.querySelector('button[type="submit"]');
        const textoOriginal = boton.textContent;

        // 3. Cambiamos el texto del botón para dar feedback visual
        boton.textContent = 'Guardando...';
        boton.disabled = true;

        // 4. Preparamos los datos
        const formData = new FormData(form);

        // 5. Enviamos los datos al PHP usando tu función 'peticionIframe'
        peticionIframe('/php/ingreso_ventas/registro_ventas/ingreso_productos.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(texto => {
            // El PHP responde: "OK|Mensaje" o "ERROR|Mensaje"
            const partes = texto.trim().split('|');
            const estado = partes[0];
            const mensaje = partes[1] || texto; // Si no hay pipe, muestra todo el texto

            if (estado === 'OK') {
                alert(mensaje); // Mensaje de éxito
                form.reset();   // Limpia los campos para agregar otro
            } else {
                alert('Error: ' + mensaje); // Mensaje de error
            }
        })
        .catch(error => {
            console.error(error);
            alert('Error de conexión al intentar guardar el producto.');
        })
        .finally(() => {
            // 6. Restauramos el botón pase lo que pase
            boton.textContent = textoOriginal;
            boton.disabled = false;
        });

        return false;
    }

// TITULO LISTADO PRODUCTOS

   // 1. Abrir el modal y rellenar los datos
    function abrir_modal_producto(id, sku, nombre) {
        // Rellenamos los inputs con la info de la fila seleccionada
        document.getElementById('idProductoEditar').value = id;
        document.getElementById('skuProductoEditar').value = sku;
        document.getElementById('nombreProductoEditar').value = nombre;
        
        // Mostramos el modal
        document.getElementById('modalEditarProducto').style.display = 'block';
    }

    // 2. Cerrar el modal
    function cerrar_modal_producto() {
        document.getElementById('modalEditarProducto').style.display = 'none';
    }

    // 3. Guardar cambios (Botón "Guardar")
    function guardar_cambios_producto() {
        // A. Capturamos los valores para validarlos
        var sku = document.getElementById('skuProductoEditar').value;
        var nombre = document.getElementById('nombreProductoEditar').value;

        // B. Validación simple (Igual que en clientes)
        if (sku.trim() === '' || nombre.trim() === '') {
            alert("Por favor, ingrese el SKU y el Nombre.");
            return; // Detiene la función si falta algo
        }

        // C. ENVIAR EL FORMULARIO
        // Esto hace lo mismo que un botón submit: envía los datos a 'procesar_producto.php'
        // y recarga la página para mostrar los cambios.
        document.getElementById('formEditarProducto').submit();
    }

    // Cerrar modal al hacer clic afuera (Para que se sienta igual al de clientes)
    window.onclick = function(event) {
        var modal = document.getElementById('modalEditarProducto');
        var modalC = document.getElementById('modalEditarCliente');
        if (event.target == modal) {
            modal.style.display = "none";
        }
        if (event.target == modalC) {
            modalC.style.display = "none";
        }
    }

    

// TITULO INGRESO CLIENTES

    // Maneja el registro de clientes con validación en vivo y envío limpio al servidor.
    document.addEventListener('DOMContentLoaded', function () {
        // Validación y formato de campos
        const rutInput = document.getElementById('rut'); // campo para ingresar el RUT
        const nombreInput = document.getElementById('nombre'); // campo para ingresar el nombre
        const form = document.getElementById('formularioRegistro'); // formulario principal de registro

        // Formatea el RUT mientras se escribe
        rutInput.addEventListener('input', function () {
            // Quitamos todo lo que no sea número o 'k/K'
            let rut = rutInput.value.replace(/[^0-9kK]/g, '');

            // Si se pasa de los 10 caracteres, lo corta para que no se extienda de los 10 caracteres
            if (rut.length > 10) rut = rut.slice(0, 10);

            // Si ya hay más de un carácter, separamos el cuerpo del RUT y el dígito verificador
            if (rut.length > 1) {
                const cuerpo = rut.slice(0, -1); // Todo menos el último carácterv
                let dv = rut.slice(-1); // Último carácter (el verificador)

                // Si el cuerpo tiene letras o símbolos raros, los limpiamos
                if (/[^0-9]/.test(cuerpo)) rut = cuerpo.replace(/[^0-9]/g, '');
                // Si el dígito verificador no es válido, lo borramos
                if (!/^[0-9kK]$/.test(dv)) dv = '';

                // Le ponemos puntos al cuerpo cada 3 dígitos para que se vea mejor y se muestre como un RUT
                const cuerpoFormateado = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                // Y lo mostramos con guion y dígito verificador (si lo hay)
                rutInput.value = dv ? `${cuerpoFormateado}-${dv.toLowerCase()}` : cuerpoFormateado;
            } else {
                // Si el RUT es muy corto, solo mostramos los números
                rutInput.value = rut.replace(/[^0-9]/g, '');
            }
        });

        // Validamos el nombre para que solo se puedan escribir letras y espacios// Valida el nombre
        nombreInput.addEventListener('input', function () {
            // Permitimos solo letras (incluyendo acentos) y espacios
            let nombre = nombreInput.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            nombreInput.value = nombre;
        });

        // Justo antes de enviar el formulario, le quitamos los puntos al RUT para que se guarde limpio en la base de datos
        form.addEventListener('submit', function (event) {
            // Quitamos los puntos del RUT para que se guarde limpio en la base de datos
            const rawRut = rutInput.value.replace(/\./g, '');
            rutInput.value = rawRut;
        });
    });

    // Función para registrar cliente
    function registrar_cliente(event) {
        // Evitamos que el formulario se mande como lo haría normalmente
        event.preventDefault();

        // Tomamos los datos del formulario
        const formData = new FormData(document.getElementById('formularioRegistro'));
        // Agarramos el botón de enviar y guardamos su texto original
        const botonEnviar = event.target.querySelector('button[type="submit"]');
        const textoOriginal = botonEnviar.textContent;

        // Cambiamos el texto del botón para que diga "Registrando..." y lo desactivamos
        botonEnviar.textContent = 'Registrando...';
        botonEnviar.disabled = true;

        // Enviamos los datos al servidor usando la funcion creada peticioniframe
        peticionIframe('/php/ingreso_ventas/ingreso_clientes/registrar_cliente.php', {
            method: 'POST', // Método de envío
            body: formData  // Los datos del formulario que capturaste arriba
        })
        // Aquí empieza la espera de la respuesta
        .then(res => res.text()) // Convertimos la respuesta a texto
        .then(info => {
            //  validando si dice OK o Error
            if (!info || !info.trim()) {
                alert('La respuesta del servidor está vacía.');
                return;
            }

            // Parseamos la respuesta en texto plano (formato: ESTADO|MENSAJE)
            const partes = info.trim().split('|');
            const estado = partes[0] || '';
            const mensaje = partes[1] || '';

            // Si el servidor dice que todo salió bien mostramos el mensaje de éxito
            if (estado === 'OK') {
                alert(mensaje || 'Cliente registrado exitosamente');
                document.getElementById('formularioRegistro').reset();
            } else {
                // Si hubo algún error, lo mostramos
                alert('Error al registrar cliente: ' + (mensaje || 'Error desconocido'));
            }
        })
        // Si algo falla en la conexión, mostramos el siguiente error que es "Error al conectar con el servidor"
        .catch(error => {
            console.error('Error:', error);
            alert('Error al conectar con el servidor: ' + error.message);
        })
        // Finalmente restauramos el botón a su estado original
        .finally(() => {
            botonEnviar.textContent = textoOriginal;
            botonEnviar.disabled = false;
        });
        // Por si acaso, evitamos que el formulario se mande de forma tradicional
        return false;
    }   


// TITULO PLANTILLAS DE EXCEL

    // Esta función se ejecuta cuando el usuario carga un archivo Excel con datos de clientes.
    function cargar_cliente_excel(event) {
        // Evita que el formulario se envíe de forma tradicional
        event.preventDefault(); 
        
        // Capturamos el formulario que contiene el archivo y otros datos
        const form = document.getElementById('datos_excel');
        // Creamos un objeto FormData con todos los campos del formulario, incluyendo el archivo
        const formData = new FormData(form);
        
        // Capturamos el input de archivo para manipular su comportamiento temporalmente
        const fileInput = document.getElementById('archivo_excel');
        // Guardamos el evento original del input para restaurarlo después
        const originalOnChange = fileInput.onchange;
        // Desactivamos temporalmente el evento onchange para evitar que se dispare durante el procesamiento
        fileInput.onchange = null;
        
        // Enviamos los datos al backend usando peticionIframe para subir el archivo con método POST
        peticionIframe('/php/ingreso_ventas/ingreso_clientes/cargar_datos.php', {
            method: 'POST',
            body: formData,
            // Le decimos el ID del input del archivo en tu HTML ('archivo_excel').
            // Esto le permite a la función ir a buscar el archivo real para enviarlo.
            fileInputId: 'archivo_excel', 
            fileInputName: 'archivo_excel' 
        })
        // Esperamos la respuesta como texto...
        .then(respuesta => respuesta.text())
        .then(informacion => {
            // Parseamos la respuesta en texto plano (formato: OK|nuevas|existentes|errores o ERROR|mensaje)
            const partes = informacion.trim().split('|');
            const estado = partes[0] || '';

            // Si el backend indica éxito, mostramos un resumen con los resultados del análisis
            if (estado === 'OK') {
                const filasNuevas = partes[1] || '0';
                const filasExistentes = partes[2] || '0';
                const filasErrores = partes[3] || '0';
                alert(`Archivo Analizado y Procesado.\nFilas nuevas cargadas: ${filasNuevas}.\nFilas ya existentes: ${filasExistentes}.\nFilas con errores: ${filasErrores}.`);
            } else {
                // Si el backend responde con error, mostramos el mensaje de error
                alert('Error al cargar clientes: ' + (partes[1] || 'Error desconocido'));
            }
        })
        // Si falla la conexión o mostramos error general
        .catch(error => {
            console.error('Error:', error);
            alert('Error al conectar con el servidor: ' + error.message);
        })
        .finally(() => {
            // Restauramos el comportamiento original del input
            fileInput.onchange = originalOnChange;
            // Limpiamos el input para permitir volver a subir el mismo archivo si es necesario
            fileInput.value = '';
        });
        // evitamos que el formulario recargue la página
        return false;
    }

    // Plantilla de ventas 

    // funcion para cargar las ventas de excel, esta funcion se ejecuta cuando se cargan los archivos excel
    function cargar_ventas_excel(event) {
        // Evita que el formulario se envíe normalmente
        event.preventDefault(); 
        
        // Obtenemos el formulario y sus datos
        const form = document.getElementById('ventas_excel');
        const formData = new FormData(form);
        
        // Guardamos y desactivamos temporalmente el onchange del input
        const fileInput = document.getElementById('archivo_excel_ventas');
        const originalOnChange = fileInput.onchange;
        fileInput.onchange = null; // Deshabilita el onchange temporalmente
        
        // Enviamos los datos al backend
        peticionIframe('/php/ingreso_ventas/ingreso_clientes/cargar_ventas.php', {
            method: 'POST',
            body: formData,
            // Aquí el ID es diferente porque es el input de ventas.
            fileInputId: 'archivo_excel_ventas',
            fileInputName: 'archivo_excel_ventas'
        })
        // recibimos la respuesta como texto
        .then(respuesta => respuesta.text())
        .then(informacion => {
            // Parseamos la respuesta en texto plano (formato: OK|nuevas|existentes|errores o ERROR|mensaje)
            const partes = informacion.trim().split('|');
            const estado = partes[0] || '';

            // Si el backend indica éxito, mostramos un resumen con los resultados del análisis
            if (estado === 'OK') {
                const filasNuevas = partes[1] || '0';
                const filasExistentes = partes[2] || '0';
                const filasErrores = partes[3] || '0';
                alert(`Archivo Analizado y Procesado.\nFilas nuevas cargadas: ${filasNuevas}.\nFilas ya existentes: ${filasExistentes}.\nFilas con errores: ${filasErrores}.`);
            } else {
                // Si el backend responde con error, mostramos el mensaje de error
                alert('Error al cargar clientes: ' + (partes[1] || 'Error desconocido'));
            }
        })
        // Si ocurre un error de red o conexión, lo mostramos en consola y alertamos al usuario
        .catch(error => {
            console.error('Error:', error);
            alert('Error al conectar con el servidor: ' + error.message);
        })
        
        .finally(() => {
            // Restauramos el evento onchange original del input
            fileInput.onchange = originalOnChange;
            // Limpiamos el valor del input para permitir volver a subir el mismo archivo si es necesario
            fileInput.value = '';
        });
        // Retornamos false para evitar que el formulario recargue la página
        return false;
    }


   // funcion para cargar facturas usando tu sistema especial de envio
    function cargar_facturas_excel(event) {
        // si la accion viene de un boton, esto evita que la pagina se recargue sola
        if(event) event.preventDefault(); 

        // guardamos el nombre del casillero donde se sube el archivo
        const fileInputId = 'archivo_excel_factura_top';
        // buscamos ese casillero en la pagina para poder leer lo que tiene
        const fileInput = document.getElementById(fileInputId);

        // si no encontramos el casillero o esta vacio, nos detenemos aqui
        if (!fileInput || fileInput.files.length === 0) return;

        // preparamos una caja virtual vacia para guardar los datos a enviar
        const formData = new FormData();
        // metemos el archivo que elegiste dentro de esa caja
        formData.append('archivo_excel', fileInput.files[0]);

        // buscamos la imagen del boton que esta justo antes del casillero
        const btnImagen = fileInput.previousElementSibling; 
        // si encontramos la imagen, entramos para cambiarle el aspecto
        if(btnImagen) {
            // guardamos como se veia antes para no perderlo
            btnImagen.dataset.originalOpacity = btnImagen.style.opacity || '1';
            // la ponemos medio transparente para que se note que esta pensando
            btnImagen.style.opacity = '0.5';
        }

        // guardamos la funcion que vigila los cambios para usarla despues
        const originalOnChange = fileInput.onchange;
        // apagamos esa vigilancia un momento para que no moleste durante el proceso
        fileInput.onchange = null;

        // enviamos la caja con el archivo al servidor usando tu funcion especial
        peticionIframe('/php/ingreso_ventas/ingreso_clientes/cargar_factura.php', {
            // indicamos que vamos a enviar informacion
            method: 'POST',
            // aqui va la caja con el archivo
            body: formData,
            // le decimos cual es el id del casillero que estamos usando
            fileInputId: fileInputId, 
            // y tambien el nombre del casillero
            fileInputName: 'archivo_excel' 
        })
        // cuando el servidor responda, convertimos el mensaje a texto simple
        .then(respuesta => respuesta.text()) 
        // ahora leemos esa informacion que llego
        .then(informacion => {
            // cortamos el texto donde haya barras para separar los datos
            const partes = informacion.trim().split('|');
            // la primera parte nos dice si todo salio bien o mal
            const estado = partes[0] || '';

            // si dice ok significa que funciono perfecto
            if (estado === 'OK') {
                // guardamos cuantos se crearon correctamente
                const insertados = partes[1] || '0';
                // guardamos cuantos fallaron
                const errores = partes[3] || '0'; 

                // mostramos una ventana avisando que termino el proceso
                alert(`✅ Proceso finalizado.\n\nFacturas creadas: ${insertados}\nErrores: ${errores}`);

                // buscamos el recuadro donde se ven las facturas
                const iframe = document.querySelector('#seccion-factura iframe');
                // si existe, lo recargamos para que aparezcan los datos nuevos
                if(iframe) iframe.src = iframe.src; 

            } else {
                // si salio mal, mostramos el error que nos mando el servidor
                alert('❌ Error al cargar facturas: ' + (partes[1] || 'Error desconocido'));
                // anotamos el error en la consola por si acaso
                console.error('Log del servidor:', informacion);
            }
        })
        // si falla la conexion o el codigo tiene problemas, caemos aqui
        .catch(error => {
            // anotamos el error tecnico
            console.error('Error JS:', error);
            // avisamos al usuario que hubo un problema de conexion
            alert('Error de conexión o ejecución: ' + error.message);
        })
        // esto se hace siempre al final, haya funcionado o no
        .finally(() => {
            // si teniamos la imagen, la dejamos como estaba al principio
            if(btnImagen) btnImagen.style.opacity = btnImagen.dataset.originalOpacity;
            
            // volvemos a encender la vigilancia de cambios
            fileInput.onchange = originalOnChange; 
            // limpiamos el casillero para poder subir otro archivo si queremos
            fileInput.value = ''; 
        });

        // terminamos y evitamos cualquier otra accion automatica
        return false;
    }

    // funcion para cargar PRODUCTOS usando tu sistema especial
    function cargar_productos_excel(event) {
        // evitamos recarga
        if(event) event.preventDefault(); 

        
        const fileInputId = 'archivo_excel_prod';
        const fileInput = document.getElementById(fileInputId);

        if (!fileInput || fileInput.files.length === 0) {
            alert("Por favor seleccione un archivo Excel primero.");
            return;
        }

        const formData = new FormData();
        formData.append('archivo_excel', fileInput.files[0]);

        // Efecto visual en el botón 
        const btnImagen = fileInput.previousElementSibling; 
        if(btnImagen && btnImagen.tagName === 'IMG') {
            btnImagen.dataset.originalOpacity = btnImagen.style.opacity || '1';
            btnImagen.style.opacity = '0.5';
        }

        const originalOnChange = fileInput.onchange;
        fileInput.onchange = null;

       
        peticionIframe('/php/ingreso_ventas/ingreso_clientes/cargar_productos.php', {
            method: 'POST',
            body: formData,
            fileInputId: fileInputId, 
            fileInputName: 'archivo_excel' 
        })
        .then(respuesta => respuesta.text()) 
        .then(informacion => {
            const partes = informacion.trim().split('|');
            const estado = partes[0] || '';

            if (estado === 'OK') {
                const insertados = partes[1] || '0';
                const errores = partes[3] || '0'; 

                alert(`✅ Carga de Productos finalizada.\n\nInsertados/Actualizados: ${insertados}\nErrores: ${errores}`);

                
            } else {
                alert('❌ Error al cargar productos: ' + (partes[1] || 'Error desconocido'));
                console.error('Log del servidor:', informacion);
            }
        })
        .catch(error => {
            console.error('Error JS:', error);
            alert('Error de conexión o ejecución: ' + error.message);
        })
        .finally(() => {
            if(btnImagen && btnImagen.tagName === 'IMG') btnImagen.style.opacity = btnImagen.dataset.originalOpacity;
            fileInput.onchange = originalOnChange; 
            fileInput.value = ''; 
        });

        return false;
    }

// TITULO ELIMINAR CLIENTE O DATOS

    //formulario para buscar clientes por rut y mostrar el cliente automaticamente
    document.addEventListener('DOMContentLoaded', () => {
    // Capturamos los campos del formulario de eliminación
    const rutInput = document.querySelector('#formularioEliminar input[name="rut"]');
    const nombreInput = document.querySelector('#formularioEliminar input[name="nombre"]');

    // Si el formulario no tiene los campos esperados, salimos sin ejecutar nada para evitar romper el flujo
    if (!rutInput || !nombreInput) return;

    // Formatea el RUT mientras se escribe
    rutInput.addEventListener('input', () => {
        // Eliminamos cualquier carácter que no sea número o K
        let raw = rutInput.value.replace(/[^\dkK]/gi, '');

        // Limitamos el largo a 10 caracteres sin puntos ni guión
        if (raw.length > 10) {
            raw = raw.slice(0, 10);
        }

        // Contamos cuántas veces aparece la letra K puede ser mayúscula o minúscula
        const kCount = (raw.match(/k/gi) || []).length;
        // Si hay más de una K, las eliminamos todas
        if (kCount > 1) {
            raw = raw.replace(/k/gi, '');
        }
        // Si hay una sola K pero no está al final, también la eliminamos
        if (kCount === 1 && raw.slice(-1).toUpperCase() !== 'K') {
            raw = raw.replace(/k/gi, '');
        }
        // Si el RUT tiene menos de 2 caracteres, aún no aplicamos el formato con puntos y guión
        if (raw.length < 2) {
            rutInput.value = raw;
            return;
        }
        // Separamos el cuerpo del RUT y el dígito verificador (DV)
        const cuerpo = raw.slice(0, -1);
        // convertimos el DV a mayúscula por consistencia
        const dv = raw.slice(-1).toUpperCase(); 

        // Agregamos puntos cada 3 dígitos desde el final para mostrar el RUT con formato visual estándar
        const cuerpoFormateado = cuerpo
        .split('') // Separamos el string en caracteres individuales
        .reverse() // Invertimos el orden para contar desde el final
        // Recorremos los caracteres y agregamos puntos cada 3 dígitos
        .reduce((acc, char, i) => {
            return char + ((i > 0 && i % 3 === 0) ? '.' : '') + acc;
        }, '');

        // Actualizamos el campo con el formato final por ejemplo: 00.000.000-K
        rutInput.value = `${cuerpoFormateado}-${dv}`;
    });

    // Apartado de búsqueda automática al salir del campo para buscar el cliente
    rutInput.addEventListener('blur', async function () {
        const rutFormateado = this.value.trim();
        // si el campo está vacío, no hacemos nada
        if (!rutFormateado) return;

        // Limpia el formato para enviar solo números y DV
        const rutSinFormato = rutFormateado.replace(/[^\dkK]/gi, '');

        // Consultamos al backend con el RUT limpio
        // Intentamos conectar con el servidor
        try {
            // Llamamos a peticionIframe.
            // Usamos "await" para detener el código aquí hasta que el mensajero vuelva con la respuesta.
            const response = await peticionIframe(`/php/ingreso_ventas/registro_ventas/buscar_cliente.php?rut=${encodeURIComponent(rutSinFormato)}&formato=nuevo`);
            
            // Obtenemos el texto que trajo el mensajero.
            const text = await response.text();
            const partes = text.trim().split('|');
            const estado = partes[0] || '';

        // Si el cliente existe, mostramos su nombre en el campo correspondiente
        if (estado === 'DATOS' && partes[1]) {
            nombreInput.value = partes[1];
        // Si el backend responde con error explícito, lo mostramos
        } else if (estado === 'ERROR') {
            alert(partes[1] || 'Cliente no encontrado');
        // Si no se encuentra el cliente, mostramos mensaje genérico
        } else {
            alert('Cliente no encontrado');
        }

        // Si existe una función para validar los campos, la ejecutamos
        if (typeof verificarCampos === 'function') {
            verificarCampos();
        }

        } catch (error) {
        // Si ocurre un error de red lo mostramos
        console.error('Error al buscar el cliente:', error);
        alert('Hubo un error al buscar el cliente.');
        }
    });
    });

    //Funcion para eliminar al cliente

    



// TITULO LISTADO DE CLIENTES

    function buscarCliente() {
    // Obtener el valor del input
    var input = document.getElementById("buscador_cliente");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("tabla_clientes");
    var tr = table.getElementsByTagName("tr");

    // Recorrer todas las filas de la tabla
    for (var i = 0; i < tr.length; i++) {
        // Obtenemos todas las celdas de la fila actual
        var tds = tr[i].getElementsByTagName("td");

        // CASO 1: Es una fila de ENCUESTA o HEADER (Th) -> La ignoramos (generalmente está en thead, pero por si acaso)
        if (tds.length === 0) continue;

        // CASO 2: Es una fila de CLIENTE (Tiene Nombre y RUT, o sea, más de 1 columna)
        if (tds.length > 1) {
        var tdNombre = tds[0];
        var tdRut = tds[1];

        if (tdNombre || tdRut) {
            var txtValueNombre = tdNombre.textContent || tdNombre.innerText;
            var txtValueRut = tdRut.textContent || tdRut.innerText;

            // Si coincide con el nombre O el RUT
            if (txtValueNombre.toUpperCase().indexOf(filter) > -1 || txtValueRut.toUpperCase().indexOf(filter) > -1) {
            tr[i].style.display = ""; // Mostrar
            } else {
            tr[i].style.display = "none"; // Ocultar
            }
        }
        } 
        // caso3: es una fila de letra (a, b, c...) -> Tiene solo 1 columna
        else if (tds.length === 1) {
            // Si hay algo escrito en el buscador, ocultamos las letras separadoras para que se vea limpia la lista
            if (filter.length > 0) {
                tr[i].style.display = "none"; 
            } else {
                // Si el buscador está vacío, volvemos a mostrar las letras
                tr[i].style.display = ""; 
            }
            }
        }
    }

// TITULO BOX PLANILLA CLIENTE

    // SIN FUNCION

// TITULO MODAL EDITAR CLIENTE




    // Abre el modal de edición de cliente y carga los datos actuales en los campos del formulario
    function abrir_modal_cliente(rut, nombre) {
        // Asigna el RUT al campo oculto del formulario
        document.getElementById('rutCliente').value = rut;
        // Asigna el nombre actual al campo editable
        document.getElementById('nombreCliente').value = nombre;
        // Muestra el modal con display flex para centrarlo visualmente
        document.getElementById('modalEditarCliente').style.display = 'flex';
    }

    // Cierra el modal de edición ocultándolo
    function cerrar_modal_cliente() {
        document.getElementById('modalEditarCliente').style.display = 'none';
    }

    // Valida el formulario y envía los cambios al backend
    function guardar_cambios_cliente() {
        // Obtiene el RUT desde el campo oculto
        const rut = document.getElementById('rutCliente').value;
        // Obtiene el nombre ingresado y elimina espacios al inicio y final
        const nombre = document.getElementById('nombreCliente').value.trim();

        // Validación: el nombre no puede estar vacío ni contener caracteres no permitidos
        if (!nombre || !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
            alert('El nombre no puede estar vacío ni contener caracteres inválidos.');
            // Detiene la ejecución si la validación falla
            return;
        }

        // Prepara los datos en formato URL codificado para enviar por POST
        const params = new URLSearchParams();
        params.append('rut', rut);
        params.append('nombre', nombre);

        // Envío de datos al backend usando iframe con método POST
        peticionIframe('/php/ingreso_ventas/ingreso_clientes/editar_cliente_razonsocial.php', {
            method: 'POST',
            // Enviamos los parámetros que preparaste antes (rut y nombre).
            body: params 
        })
        // Obtenemos la respuesta como texto plano
        .then(res => res.text())
        .then(text => {
            // Parseamos la respuesta en texto plano (formato: ESTADO|MENSAJE)
            const partes = text.trim().split('|');
            const estado = partes[0] || '';
            const mensaje = partes[1] || '';

            // Si la actualización fue exitosa, mostramos mensaje y cerramos el modal
            if (estado === 'OK') {
                alert(mensaje || 'Cliente actualizado correctamente.');
                cerrar_modal_cliente();
                // Recargamos la página para reflejar los cambios
                window.location.reload();
            } else {
                // Si hubo un error en la actualización, mostramos el mensaje del backend
                alert(mensaje || 'Error al actualizar cliente.');
            }
        })
        // Captura errores de red
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un error al guardar los cambios.');
        });
    }


            // Envío asíncrono del formulario de eliminación  y recarga la página actual
            // También envuelve la función registrar_cliente para recargar la página tras el registro
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('formularioEliminar');
                const btnSolo = document.getElementById('btnSolo');
                const btnTodo = document.getElementById('btnTodo');

                // Envía el formulario  y tras completarse recarga la página actual
                async function eliminar_cliente(action) {
                    // Asegura que el formulario exista
                    if (!form) return;

                    // --- NUEVO: LIMPIEZA DE RUT ANTES DE ENVIAR ---
                    // Buscamos el input del RUT y le quitamos puntos y guiones visualmente
                    const rutInput = form.querySelector('input[name="rut"]');
                    if (rutInput) {
                        // Reemplaza todo lo que no sea número o k/K por vacío
                        rutInput.value = rutInput.value.replace(/[^0-9kK]/g, '').toUpperCase();
                    }
                    

                    // Buscamos (o creamos) un input oculto con name="accion"
                    let accionInput = form.querySelector('input[name="accion"]');
                    if (!accionInput) {
                        accionInput = document.createElement('input');
                        accionInput.type = 'hidden';
                        accionInput.name = 'accion';
                        form.appendChild(accionInput);
                    }
                    accionInput.value = action;

                    const formData = new FormData(form);
                    
                    // Determinamos la URL destino
                    const destino = (form.action && form.action.trim()) ? form.action : window.location.href;

                    // Envía los datos al servidor
                    try {
                        // Usamos peticionIframe con "await" para esperar a que termine.
                        await peticionIframe(destino, {
                            method: 'POST',
                            body: formData // Enviamos los datos del formulario de eliminar.
                        });

                        // Cuando termine, recargamos la página para ver los cambios.
                        window.location.reload();
                    } catch (err) {
                        alert('Error al procesar la solicitud: ' + (err && err.message ? err.message : err));
                    }
                }
                //Evento para el boton eliminar solo cliente
                if (btnSolo) {
                    btnSolo.addEventListener('click', function (e) {
                        e.preventDefault(); // Evita envío inmediato
                        if (confirm('¿Está seguro de borrar este cliente?')) {
                            eliminar_cliente('solo');
                        }
                    });
                }
                //Evento para el boton eliminar cliente y datos de venta
                if (btnTodo) {
                    btnTodo.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (confirm('¿Está seguro de que desea borrar al cliente y todos sus datos de venta?')) {
                            eliminar_cliente('todo');
                        }
                    });
                }

                // --- Manejo para recargar la página tras registrar un cliente ---
                const formRegistro = document.getElementById('formularioRegistro');

                // Si existe la función declarada en el JS externo, la envolvemos para recargar al terminar
                if (typeof window.registrar_cliente === 'function') {
                    const origRegistrar = window.registrar_cliente;
                    window.registrar_cliente = function (event) {
                        try {
                            const result = origRegistrar.call(this, event);

                            // Si la función retorna una promesa (async), esperarla
                            if (result && typeof result.then === 'function') {
                                result.then(() => {
                                    // Pequeña espera para que el servidor procese y el backend establezca el flash
                                    setTimeout(() => window.location.reload(), 200);
                                }).catch(err => {
                                    console.error('Error en registrar_cliente:', err);
                                });
                            } else {
                                // Si no es async, recargamos pasados 500ms para dejar tiempo a procesos sincrónicos
                                setTimeout(() => window.location.reload(), 500);
                            }
                            
                            return result;
                        // Captura cualquier excepción y la muestra en consola    
                        } catch (err) {
                            console.error('Excepción en wrapper registrar_cliente:', err);
                            throw err;
                        }
                    };
                
                } else if (formRegistro) {
                    // Si no hay función, añadimos un manejador que envía y recarga
                    formRegistro.addEventListener('submit', async function (e) {
                        e.preventDefault();

                        // Validación mínima: el navegador ya valida los campos 'required'
                        if (!confirm('¿Registrar este cliente?')) return;

                        const fd = new FormData(formRegistro);
                        // Envía los datos al servidor
                       // Intentamos enviar.
                        try {
                            //  Usamos peticionIframe con "await".
                            await peticionIframe(window.location.href, {
                                method: 'POST',
                                body: fd // Los datos del formulario.
                            });
                            // Recargamos la página al terminar.
                            window.location.reload();
                        // Si falla, mostramos alerta.
                        } catch (err) {
                            alert('Error al registrar el cliente: ' + (err && err.message ? err.message : err));
                        }
                    });
                }
            });

// TITULO ARCHIVO JS

   document.addEventListener("DOMContentLoaded", function() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('abrir_tab') === 'factura') {
                    cambiarPestana('factura');
                }
            });



/*  --------------------------------------------------------------------------------------------------------------
    ---------------------------------------- FIN ITred Spa ingreso_datos .JS -------------------------------------
    -------------------------------------------------------------------------------------------------------------- */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ


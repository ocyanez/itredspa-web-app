// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/* --------------------------------------------------------------------------------------------------------------
    -------------------------------------- INICIO ITred Spa ventas .JS -------------------------------------------
    -------------------------------------------------------------------------------------------------------------- */
// Variable global definida al inicio para evitar errores
var metasGlobales = [];

// paara no usra ajax se uso esta practica  
// Lo que hace es crear una pequeña ventana oculta (iframe) en el fondo.
// Si queremos pedir datos (GET), le decimos a esa ventana que vaya a la dirección (URL).
// Si queremos enviar datos (POST), llenamos un formulario oculto y lo enviamos a esa ventana.
// Cuando la ventana termina de cargar, leemos lo que escribió el servidor (PHP) y te lo entregamos.
function peticionIframe(url, opciones = {}) {
    // Retornamos una "promesa", que es como decirle al código: "Espera aquí hasta que el mensajero vuelva".
    return new Promise((resolve, reject) => {
        // Creamos un nombre único para no confundirnos si hacemos varias cosas a la vez
        const idUnico = 'iframe_req_' + Date.now() + Math.floor(Math.random() * 10000);
        
        // Creamos la ventana oculta (iframe)
        const iframe = document.createElement('iframe');
        iframe.name = idUnico; // Le ponemos nombre
        iframe.id = idUnico;
        iframe.style.display = 'none'; // La ocultamos para que el usuario no la vea
        document.body.appendChild(iframe); // La agregamos a la página

        // Esto se ejecuta cuando el mensajero vuelve (cuando el iframe termina de cargar)
        iframe.onload = function() {
            try {
                // Entramos a la ventana oculta y leemos lo que dice
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                // Obtenemos solo el texto puro
                const respuestaTexto = doc.body.innerText || doc.body.textContent;
                
                // Preparamos la respuesta para que tu código crea que es normal
                const respuestaSimulada = {
                    ok: true,
                    status: 200,
                    // Esta función .text() es la que usabas antes, así que la simulamos
                    text: () => Promise.resolve(respuestaTexto)
                };
                
                // ¡Listo! Entregamos la respuesta
                resolve(respuestaSimulada);

                // Limpieza: Borramos la ventana oculta después de un segundo para no llenar de basura la memoria
                setTimeout(() => { 
                    if(document.body.contains(iframe)) document.body.removeChild(iframe); 
                }, 1000);

            } catch (err) {
                console.error("Error leyendo iframe:", err);
                reject(err);
            }
        };

        // Si algo falla en la red
        iframe.onerror = function() {
            reject(new Error("Error de red via Iframe"));
        };

        // Verificamos si es para pedir (GET) o enviar (POST)
        const metodo = (opciones.method || 'GET').toUpperCase();

        if (metodo === 'GET') {
            // Si es GET, solo cambiamos la dirección de la ventana oculta
            iframe.src = url;
        } else if (metodo === 'POST') {
            // Si es POST, creamos un formulario fantasma
            const form = document.createElement('form');
            form.target = idUnico; // Le decimos que se envíe a nuestra ventana oculta
            form.method = 'POST';
            form.action = url;
            form.style.display = 'none'; // Oculto
            
            // Si hay datos para enviar, creamos las casillas (inputs) ocultas
            if (opciones.body && opciones.body instanceof URLSearchParams) {
                opciones.body.forEach((valor, clave) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = clave;
                    input.value = valor;
                    form.appendChild(input);
                });
            }
            document.body.appendChild(form);
            form.submit(); // Enviamos el formulario
            
            // Borramos el formulario fantasma después de un ratito
            setTimeout(() => { if(document.body.contains(form)) document.body.removeChild(form); }, 500);
        }
    });
}

// TITULO HTML

    // SIN FUNCION

// TITULO BODY

    // SIN FUNCION

// TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN

    // SIN FUNCION

// TITULO CABECERA

//esto sirve para convertir un string en un objeto lo suamos para que el string que viene de normalizar_qr
function stringToObject(str) {
    if (!str || typeof str !== 'string') return {};
    var obj = {};
    // Separamos por punto y coma
    var pares = str.split(';');
    for (var i = 0; i < pares.length; i++) {
        var par = pares[i];
        var idx = par.indexOf(':');
        if (idx > 0) {
            var k = par.substring(0, idx).trim();
            var v = par.substring(idx + 1).trim();
            obj[k] = v || '';
        }
    }
    return obj;
}


    // Función para abrir el modal y cargar los datos de la venta
    function abrirModal() {
        // Mostrar el modal
        const modal = document.getElementById('modalEditarVenta');
        modal.style.display = 'flex';
    }

    // función para cerrar el modal
    function cerrarModal() {
        const modal = document.getElementById('modalEditarVenta');
        modal.style.display = 'none';
    }

    // Busca una venta desde el modal por SKU o Lote
    function buscarVentaModal() {
        const valor = document.getElementById('modalBuscar').value.trim();
        if (!valor) {
            alert('Ingrese un SKU o Lote para buscar.');
            return;
        }

        // Aquí usamos nuestro mensajero oculto (peticionIframe) 
        // Buscar la venta por SKU o Lote
        peticionIframe(`/php/ingreso_ventas/registro_ventas/buscar_venta.php?valor=${encodeURIComponent(valor)}`)
            // esperamos el texto plano (ahora viene del iframe)
            .then(res => res.text()) 
            .then(textoRespuesta => {
                console.log("Respuesta del servidor:", textoRespuesta);

                // Si el servidor no devuelve nada o devuelve un error plano
                if (!textoRespuesta || textoRespuesta.trim() === '' || textoRespuesta.includes('Error')) {
                    alert('No se encontró una venta con ese SKU o Lote.');
                    return;
                }

                // Usamos tu función existente para convertir el string "clave:valor;" a objeto
                const data = stringToObject(textoRespuesta);

                // Verificamos si se creó el objeto correctamente (debe tener al menos un ID)
                if (data && data.id) {
                    document.getElementById('modalId').value = data.id;
                    document.getElementById('lote').value = data.lote;
                    document.getElementById('sku').value = data.sku;
                    document.getElementById('modalRut').value = data.rut;
                    document.getElementById('modalNombre').value = data.nombre;
                    document.getElementById('modalNumeroFact').value = data.numero_fact;
                    document.getElementById('modalFechaActual').value = data.fecha_despacho || data.fechaActual; 
                    document.getElementById('producto').value = data.producto;
                    document.getElementById('cantidad').value = data.cantidad;
                    document.getElementById('fechaFabricacion').value = data.fecha_fabricacion;
                    document.getElementById('serieInicio').value = data.n_serie_ini;
                    document.getElementById('serieFin').value = data.n_serie_fin;
                } else {
                    alert('Los datos recibidos no tienen el formato correcto.');
                }
            })
            .catch(err => {
                alert('Error al buscar la venta.');
                console.error(err);
            });
    }

// Función para guardar los cambios
    function guardarCambios() {
        // Obtener los valores del modal
        const id = document.getElementById('modalId').value;
        const rut = document.getElementById('modalRut').value;
        const nombre = document.getElementById('modalNombre').value;
        const numeroFact = document.getElementById('modalNumeroFact').value;
        const fechaActual = document.getElementById('modalFechaActual').value;
        const sku = document.getElementById('sku').value;
        const producto = document.getElementById('producto').value;
        const cantidad = document.getElementById('cantidad').value;
        const lote = document.getElementById('lote').value;
        const fechaFabricacion = document.getElementById('fechaFabricacion').value;
        const serieInicio = document.getElementById('serieInicio').value;
        const serieFin = document.getElementById('serieFin').value;

        // Validar que no haya campos vacíos
        if (!rut || !nombre || !numeroFact || !fechaActual || !producto || !cantidad || !fechaFabricacion || !serieInicio || !serieFin) {
            alert('Por favor, completa todos los campos antes de guardar.');
            return;
        }

        
        const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        const numeroRegex = /^\d+$/;

        if (!nombreRegex.test(nombre)) {
            alert('El nombre no debe contener caracteres numéricos y/o especiales.');
            return;
        }
        if (!numeroRegex.test(numeroFact) || numeroFact.length > 21) {
            alert('El número de factura solo puede contener caracteres numéricos.');
            return;
        }
        if (!numeroRegex.test(serieInicio)) {
            alert('La serie de inicio solo puede contener caracteres numéricos.');
            return;
        }
        if (!numeroRegex.test(serieFin)) {
            alert('La serie de término solo puede contener caracteres numéricos.');
            return;
        }

        //  Preparar datos 
        const datos = new URLSearchParams();
        datos.append('id', id);
        datos.append('rut', rut);
        datos.append('nombre', nombre);
        datos.append('numeroFact', numeroFact);
        datos.append('fechaActual', fechaActual);
        datos.append('sku', sku);
        datos.append('producto', producto);
        datos.append('cantidad', cantidad);
        datos.append('lote', lote);
        datos.append('fechaFabricacion', fechaFabricacion);
        datos.append('serieInicio', serieInicio);
        datos.append('serieFin', serieFin);

        //  Aquí usamos el mensajero oculto en modo post (envío de datos)
        // Enviar los datos al servidor
        peticionIframe('/php/ingreso_ventas/registro_ventas/actualizar_venta.php', {
            method: 'POST',
            // Aquí van los datos del formulario oculto
            body: datos 
        })
        // Esperamos texto plano
        .then(response => response.text()) 
        .then(textoRespuesta => {
            // Asumimos que el PHP responde "OK" o "exito" si todo salió bien
            if (textoRespuesta.trim() === 'OK' || textoRespuesta.includes('exito')) {
                alert('Cambios guardados correctamente.');
                cerrarModal();
                location.reload();
            } else {
                alert('Error al guardar los cambios: ' + textoRespuesta);
            }
        })
        .catch(error => {
            alert('Error de conexión al guardar los cambios.');
            console.error(error);
        });
    }



// TITULO FORMULARIO INFORMACION DESPACHO

    // Al cargar la ventana, limpia todos los inputs
    window.onload = function () {
        // Selecciona todos los campos de entrada y los limpia
        document.querySelectorAll('input').forEach(input => {
            input.value = '';
        });

        // Si tienes campos específicos, puedes limpiarlos directamente
        document.getElementById('numero_factu').value = '';

    };

    var facturaValidada = false; 

   // al cargar la pagina, preparamos las herramientas
    document.addEventListener('DOMContentLoaded', function () {
    // referencias a los campos de la pantalla
    const rutInput = document.getElementById('rut');
    const numeroFactInput = document.getElementById('numero_factu');
    const nombreInput = document.getElementById('nombre');
    const fechaActualInput = document.getElementById('fecha_actual');
    
    // referencias a los botones
    const btnEscanear = document.getElementById('btnEscanear');
    const btnPistola = document.getElementById('btnPistola');
    const btnManual = document.getElementById('btnManual');
    const btnDetener = document.getElementById('btnDetener');

    // BLOQUEO DE ENVÍO AUTOMÁTICO POR ENTER (Para evitar guardados fantasma de la pistola)
    document.addEventListener('DOMContentLoaded', function() {
        // Buscamos el formulario
        const form = document.getElementById('formularioInfo');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // ESTO ES VITAL: Detiene el envío automático
                console.log("Envío de formulario por ENTER bloqueado.");
            });
        }

        // Opcional: Evitar que el enter haga nada en los inputs de texto
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Bloquea el enter

                }
            });
        });
    });

    // referencia a la lista desplegable de facturas
    const listaFacturas = document.getElementById('lista_facturas_sugeridas');

    if (numeroFactInput && rutInput) {

      numeroFactInput.addEventListener('input', function() {
            const valor = this.value.trim();
            
            // Si está vacío (lo borraste todo)
            if (valor === '') {
                // Llamamos a la nueva función que arregla todo
                resetearInterfazCompleta();
            }
        });
        
        // escuchamos el evento 'blur' (cuando sales de la casilla)
        // usamos 'async' para poder esperar las respuestas del servidor
        numeroFactInput.addEventListener('blur', async function () {
            
            // tomamos los valores escritos y quitamos espacios
            const facturaVal = this.value.trim();
            const rutVal = rutInput.value.trim();

            // si la factura o el rut estan vacios, no hacemos nada aun
            if (facturaVal === '' || rutVal === '') return;

            // logica de la fecha automatica (tu codigo original)
            try {
                if (nombreInput && nombreInput.value.trim() !== '' && fechaActualInput && fechaActualInput.value.trim() === '') {
                    const now = new Date();
                    const pad = n => n < 10 ? '0' + n : n;
                    const local = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                    fechaActualInput.value = local;
                }
            } catch (e) { console.error(e); }

            
            try {
                // Usamos peticionIframe para preguntar si existe la factura
                // hacemos la llamada al archivo php que valida
                const respValidar = await peticionIframe(`/php/ingreso_ventas/registro_ventas/validar_factura.php?rut=${encodeURIComponent(rutVal)}&factura=${encodeURIComponent(facturaVal)}`);
                const textoValidar = await respValidar.text();
                
                // si el php responde "NO_EXISTE", lanzamos la alerta inmediata
                if (textoValidar.trim() !== "EXISTE") {
                    // semaforo rojo
                    facturaValidada = false; 
                    
                    // alerta en pantalla
                    alert(`ALTO:\nLa factura N° ${facturaVal} no existe o no pertenece al RUT ${rutVal}.\n\nPor favor, vaya a "Ingreso Factura" para crearla primero.`);
                    
                    // pintamos rojo y borramos el numero malo
                    numeroFactInput.style.backgroundColor = "#ffe6e6"; 
                    numeroFactInput.value = ""; 
                    
                    // limpiamos la tabla de productos por si acaso
                    const tablaBody = document.getElementById('tablaProductos');
                    if(tablaBody) tablaBody.innerHTML = "";
                    
                    // terminamos aqui, no buscamos productos
                    return; 
                }

                // si llegamos aqui, es porque SI EXISTE (semaforo verde)
                facturaValidada = true;
                // cargar el panel visual de metas
                cargarPanelMetas(rutVal, facturaVal);
                numeroFactInput.style.backgroundColor = "#e6fffa"; // verde suave
                console.log("Factura validada correctamente.");

            } catch (error) {
                console.error("Error en validacion:", error);
                // si falla internet, paramos
                return; 
            }

            // si paso la validación buscamos productos visualizar
            try {
                // referencias a elementos de la tabla
                const tablaHeader = document.getElementById('tablaProductosHeader');
                const tablaBody = document.getElementById('tablaProductos');
                const botonGuardar = document.getElementById('boton');
                
            
                // llamamos al php buscador
                const respBuscar = await peticionIframe(`/php/ingreso_ventas/registro_ventas/buscar_productos_por_factura.php?factura=${encodeURIComponent(facturaVal)}&rut=${encodeURIComponent(rutVal.replace(/\./g, ''))}`);
                const textoBuscar = await respBuscar.text();

                // limpiamos tabla
                if(tablaBody) tablaBody.innerHTML = ""; 

                // si no hay productos previos, ocultamos cabecera y salimos
                if (!textoBuscar || textoBuscar.trim() === '') {
                    if(tablaHeader) tablaHeader.style.display = 'none';
                    if(botonGuardar) botonGuardar.style.display = 'none';
                    return; 
                }

                // si hay productos, los dibujamos
                const productos = textoBuscar.split('||');
                let productosAgregados = 0;

                productos.forEach(prodString => {
                    if (prodString.trim() !== '') {
                        const data = stringToObject(prodString);
                        if (data.sku && data.sku.trim() !== "") {
                            const datosParaTabla = [
                                data.sku || '', data.producto || '', data.cantidad || '', 
                                data.lote || '', data.fecha_fabricacion || '', data.fecha_vencimiento || '', 
                                data.n_serie_ini || '0', data.n_serie_fin || '0'
                            ];
                            // llamamos a tu funcion global agregarFilaProducto
                            if(typeof agregarFilaProducto === 'function') {
                                agregarFilaProducto(datosParaTabla, 'AUTO_PHP', false, 'bd'); 
                                productosAgregados++;
                            }
                        }
                    }
                });

                if (productosAgregados > 0) {
                    if(tablaHeader) tablaHeader.style.display = 'table';
                    if(botonGuardar) botonGuardar.style.display = 'flex';
                    if(typeof mostrar_mensaje_feedback === 'function') {
                        mostrar_mensaje_feedback("Productos cargados automáticamente.", "exitoso");
                    }
                }

            } catch (err) {
                console.error("Error buscando productos:", err);
            }
        });
    }

    // cargar lista de facturas al escribir rut 
    if (rutInput && listaFacturas) {
        rutInput.addEventListener('blur', function() {
            const rutValor = this.value.trim();
            if (rutValor.length < 3) return; 
            facturaValidada = false;
            listaFacturas.innerHTML = '';
            // peticionIframe para buscar lista de facturas
            peticionIframe(`/php/ingreso_ventas/registro_ventas/buscar_facturas_por_rut.php?rut=${encodeURIComponent(rutValor)}`)
                .then(r => r.text())
                .then(texto => {
                    if (texto && texto.trim() !== '') {
                        const facturas = texto.split('|');
                        facturas.forEach(num => {
                            const op = document.createElement('option');
                            op.value = num;
                            listaFacturas.appendChild(op);
                        });
                    }
                }).catch(e => console.error(e));
        });
    }

        // Actualiza el input datetime-local si está vacío o su valor tiene más de 60 segundos
        function setFechaActualIfStale(force = false) {
            try {
                // si no existe el input, salir
                if (!fechaActualInput) return;
                // fecha y hora actual
                const now = new Date();

                // Si se fuerza o el campo está vacío, escribir la fecha actual
                if (force || !fechaActualInput.value.trim()) {
                    const pad = n => n < 10 ? '0' + n : n;
                    fechaActualInput.value = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                    return;
                }
                // parse existing datetime-local value
                const existing = new Date(fechaActualInput.value);
                if (isNaN(existing.getTime())) {
                    // si no se puede parsear, reescribir
                    const pad = n => n < 10 ? '0' + n : n;
                    fechaActualInput.value = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                    return;
                }
                // Calcular diferencia en milisegundos
                const diffMs = now - existing;
                // mayor a 60 segundos
                if (diffMs > 60000) { 
                    const pad = n => n < 10 ? '0' + n : n;
                    fechaActualInput.value = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                }
            } catch (e) {
                console.error('setFechaActualIfStale error', e);
            }
        }

        // función para mostrar u ocultar los botones de escanear y detener
        function verificarCampos() {
        const mostrar = nombreInput.value.trim() !== "";
        btnEscanear.style.display = mostrar ? "inline-block" : "none";
        if (btnPistola) btnPistola.style.display = mostrar ? "inline-block" : "none";
        if (btnManual) btnManual.style.display = mostrar ? "inline-block" : "none";
        btnDetener.style.display = mostrar ? "inline-block" : "none";

            // Si los botones se muestran, desplaza la pantalla hacia ellos
            if (mostrar) {
                document.getElementById('boton_escaner').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // evento que formatea el RUT mientras se escribe
        rutInput.addEventListener('input', function () {
            // elimina todos los caracteres no numéricos excepto "k" o "K"
            let rut = rutInput.value.replace(/[^0-9kK]/g, '');

            // limita la longitud total a 9 caracteres (8 numéricos + 1 dígito verificador)
            if (rut.length > 9) {
                rut = rut.slice(0, 9);
            }

            // si hay más de un carácter, separa la parte numérica y el dígito verificador
            if (rut.length > 1) {
                const cuerpo = rut.slice(0, -1); // parte numérica
                let dv = rut.slice(-1); // último carácter (dígito verificador)

                // asegura que la parte numérica no tenga letras
                if (/[^0-9]/.test(cuerpo)) {
                    rut = cuerpo.replace(/[^0-9]/g, '');
                }

                // valida que el dígito verificador sea correcto
                if (!/^[0-9kK]$/.test(dv)) {
                    dv = '';
                }

                // formatea el cuerpo con puntos y combina con el dígito verificador
                const cuerpoFormateado = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                rutInput.value = dv ? `${cuerpoFormateado}-${dv.toLowerCase()}` : cuerpoFormateado;
            } else {
                // si hay un solo carácter, permite solo números
                rutInput.value = rut.replace(/[^0-9]/g, '');
            }
        });

        // evento que limpia y limita el número de documento
        numeroFactInput.addEventListener('input', function () {
            // elimina caracteres no numéricos
            this.value = this.value.replace(/\D/g, '');

            // limita a un máximo de 9 caracteres
            if (this.value.length > 20) {
                this.value = this.value.slice(0, 20);
            }
        });

    // Si el usuario ingresa número de factura y ya existe nombre, asignar fecha
    // Evento al salir del campo (blur) Número de Factura
    numeroFactInput.addEventListener('blur', function () {
        
        // logica de la fecha
        try {
            if (this.value.trim() !== '' && nombreInput && nombreInput.value.trim() !== '' && fechaActualInput && fechaActualInput.value.trim() === '') {
                const now = new Date();
                const pad = n => n < 10 ? '0' + n : n;
                const local = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                fechaActualInput.value = local;
            }
        } catch (e) { console.error(e); }

        
        const numFactura = this.value.trim();
        const rutCliente = document.getElementById('rut').value.replace(/\./g, '').trim();

        // referencias a elementos de la tabla
        const tablaHeader = document.getElementById('tablaProductosHeader');
        const tablaBody = document.getElementById('tablaProductos');
        const botonGuardar = document.getElementById('boton');

        // si el campo esta vacio limpiamos  y ocultamos todo
        if (numFactura === "") {
            // Borrar filas
            tablaBody.innerHTML = ""; 
            // Ocultar cabecera
            tablaHeader.style.display = 'none'; 
            // Ocultar botón
            botonGuardar.style.display = 'none'; 
            return;
        }

        // si hay factura buscamos
        peticionIframe(`/php/ingreso_ventas/registro_ventas/buscar_productos_por_factura.php?factura=${encodeURIComponent(numFactura)}&rut=${encodeURIComponent(rutCliente)}`)
            .then(res => res.text())
            .then(texto => {
                console.log("Respuesta del servidor:", texto);

                // limpiar siempre la tabla antes de llenar
                tablaBody.innerHTML = ""; 
                
                // si no hay respuestao es error ocultar y salir
                if (!texto || texto.trim() === '') {
                    tablaHeader.style.display = 'none';
                    botonGuardar.style.display = 'none';
                    // avisar que no existe alert("No se encontraron productos para esta factura."); 
                    return; 
                }

                // si llegamos aqui e spor que si hay datos
                const productos = texto.split('||');
                let productosAgregados = 0;

                productos.forEach(prodString => {
                    if (prodString.trim() !== '') {
                        const data = stringToObject(prodString);

                        // validacion extra solo agregar si hay sku
                        if (data.sku && data.sku.trim() !== "") {
                            const datosParaTabla = [
                                data.sku || '',
                                data.producto || '',
                                data.cantidad || '',
                                data.lote || '',
                                data.fecha_fabricacion || '',
                                data.fecha_vencimiento || '',
                                data.n_serie_ini || '0',
                                data.n_serie_fin || '0'
                            ];

                            agregarFilaProducto(datosParaTabla, 'AUTO_PHP', false);
                            productosAgregados++;
                        }
                    }
                });

                // solo mostrar la tabla si se agregaron productos reales
                if (productosAgregados > 0) {
                    tablaHeader.style.display = 'table';
                    botonGuardar.style.display = 'flex';
                    mostrar_mensaje_feedback("Productos cargados automáticamente.", "exitoso");
                } else {
                    // si el servidor devolvió algo pero no eran productos válidos
                    tablaHeader.style.display = 'none';
                    botonGuardar.style.display = 'none';
                }
            })
            .catch(err => {
                console.error("Error buscando productos:", err);
            });
    });

        // evento al salir del campo RUT para buscar cliente
        rutInput.addEventListener('blur', async function () {
            const rut = this.value.trim();
            if (!rut) return;

            try {
                // peticionIframe para buscar cliente
                const response = await peticionIframe(`/php/ingreso_ventas/registro_ventas/buscar_cliente.php?rut=${encodeURIComponent(rut)}`);
                const text = await response.text(); 

                // Se asume que PHP devuelve algo como: "nombre:Juan Perez;ok:true"
                const data = stringToObject(text);

                // Validamos si llegó nombre 
                if (data && data.nombre) {
                    nombreInput.value = data.nombre;

                    // Asignar fecha y hora actual
                    try {
                        if (fechaActualInput && fechaActualInput.value.trim() === '') {
                            const now = new Date();
                            const pad = n => n < 10 ? '0' + n : n;
                            const local = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                            fechaActualInput.value = local;
                        }
                    } catch (e) {
                        console.error('Error asignando fecha_actual:', e);
                    }
                } else {
                    // Si el texto devuelto es explícitamente un error o no trae nombre
                    if(text.includes('Error') || (data && data.msg)) {
                        alert(data.msg || text);
                    } else {
                        alert('Cliente no encontrado');
                    }
                }
                verificarCampos();

            } catch (error) {
                console.error('Error al buscar el cliente:', error);
                alert('Hubo un error de conexión al buscar el cliente.');
            }
        });

        // actualizar fecha al enfocar RUT o Número de factura si está vacía o antigua
        if (rutInput) rutInput.addEventListener('focus', function () { setFechaActualIfStale(false); });
        if (numeroFactInput) numeroFactInput.addEventListener('focus', function () { setFechaActualIfStale(false); });

        // ejecuta verificación inicial por si hay datos cargados
        verificarCampos();
        recibirDatosDesdeNormalizar();
    });


    // normalizar series
    const LONG_SERIE = 8; // longitud deseada para Serie Inicio / Serie Fin

    function normalizarSeries(ini, fin, long = LONG_SERIE) {
        let s1 = (ini ?? '').toString().trim();
        let s2 = (fin ?? '').toString().trim();

        // Si están vacías o traen algo no numérico, forzar "0"
        if (!/^\d+$/.test(s1)) s1 = '0';
        if (!/^\d+$/.test(s2)) s2 = '0';

        // Comparar como número y, si vienen invertidas, intercambiarlas
        const n1 = Number(s1), n2 = Number(s2);
        if (!isNaN(n1) && !isNaN(n2) && n1 > n2) [s1, s2] = [s2, s1];

        // Rellenar con ceros a la izquierda
        s1 = s1.padStart(long, '0');
        s2 = s2.padStart(long, '0');

        return [s1, s2];
    }


// TITULO ESCANEOS

    // Detiene el escáner del QR activo
    function detenerScanner(qr) {
        if (!qrScanner) {
            // aviso si no existe escáner en ejecución
            console.warn("No hay escáner activo para detener");
            return;
        }

        // Detener el escáner y limpiar recursos
        qrScanner.stop().then(() => {
            console.log("Escáner detenido por usuario");
            return qrScanner.clear();
        }).then(() => {
            qrScanner = null;
            escaneoRealizado = false;
            document.getElementById("lectorQR").style.display = "none";
        }).catch(err => {
            console.error("Error al detener escáner por usuario:", err);
        });
    }

    // Iniciar modo pistola para escanear QR
    function iniciarPistola() {
        const inputQR = document.getElementById('inputQR');
        if (inputQR) {
            inputQR.focus(); // Enfocar el campo oculto
            mostrar_mensaje_feedback("Modo pistola activado. Escanee el código QR con la pistola.");
            console.log('Pistola QR activada, campo enfocado');
        } else {
            console.error('No se encontró el campo inputQR');
        }
    }

    // Normaliza caracteres mal mapeados por la pistola
    function normalizarEntradaPistola(str) {
        if (!str) return str;

        const mapEspecifico = {
            ')': '(',   // paréntesis inicial
            "'": '-',   // guion medio
            '=': ')'    // paréntesis final
        };

        // Aplica el mapeo específico carácter por carácter
        let fixed = str.replace(/[)\'=]/g, ch => mapEspecifico[ch] || ch);

        // Normalización adicional si la pistola está en layout US
        // y el PC en ES-LA. Descomenta si ves más símbolos cambiados:

        const mapUSaES = {
            ';': 'ñ', ':': 'Ñ', "'": 'á', '"': 'é',
            '[': '´', ']': '+', '{': '¨', '}': '*',
            '\\': 'ç', '|': 'Ç'
        };
        fixed = fixed.replace(/[;:"[\]{}\\|]/g, ch => mapUSaES[ch] || ch);

        return fixed;
    }

    
    // Función para iniciar el ingreso manual 
    async function iniciarManual() {
        // buscamos la cajita que es el input en la pantalla donde se escribe el numero de factura
        const numFactInput = document.getElementById('numero_factu');
        // buscamos la cajita donde esta el rut
        const rutInput = document.getElementById('rut');
        // tomamos lo que se escribio en la factura y le quitamos los espacios vacios de los lados
        const numFact = numFactInput.value.trim();
        // hacemos lo mismo con el rut
        const rutVal = rutInput.value.trim();
    
        // validacion de seguridad, preguntamos ¿la casilla de factura esta vacia?
        if (!numFact) {
            // si esta vacia, mostramos un aviso y paramos aqui
            alert("Ingrese el número de factura primero.");
            return;
        }
        // consultamos al servidor hacemos una llamada al archivo php enviandole el rut y la factura para ver si existen
        // usamos await para obligar al programa a esperar la respuesta antes de seguir
        try {
            // Consultamos al servidor
            const response = await peticionIframe(`/php/ingreso_ventas/registro_ventas/validar_factura.php?rut=${encodeURIComponent(rutVal)}&factura=${encodeURIComponent(numFact)}`);
            // leemos la respuesta que nos dio el servidor en formato texto
            const texto = await response.text();
            
            // si no existe, bloqueamos
            if (texto.trim() !== "EXISTE") {
                // lanzamos una alerta de error avisando que la factura es falsa o incorrecta
                alert(` ALTO:\nLa factura N° ${numFact} no está registrada para el RUT ${rutVal}.\nDebe crearla primero en "Ingreso Factura".`);
                // pintamos la casilla de rojo para que se note el error
                numFactInput.style.backgroundColor = "#ffe6e6"; // Rojo
                // ponemos el cursor en la casilla para que el usuario corrija
                numFactInput.focus();
                // return significa detente aqui, no hagas nada mas
                return; 
            }
            // Si existe, pintar casilla verde
            numFactInput.style.backgroundColor = "#e6fffa"; 
            
        } catch (error) {
            // si falla el internet o el servidor se cae, mostramos el error en la consola
            console.error("Error validando:", error);
            // avisamos al usuario que hubo un problema de conexion
            alert("Error de conexión al validar factura.");
            return;
        }

        // creacion de la fila
        try {
            // mostrar encabezado de la tabla y el botón guardar
            const header = document.getElementById('tablaProductosHeader');
            const boton = document.getElementById('boton');
            // si existen, los hacemos visibles (display table y flex)
            if (header) header.style.display = 'table';
            if (boton) boton.style.display = 'flex';

            // Evitar duplicados preguntamos ¿ya existe una fila de escritura manual abierta?
            if (document.getElementById('manual_row_temp')) {
                // si ya existe, la buscamos
                const existing = document.getElementById('manual_row_temp');
                // buscamos el primer espacio para escribir dentro de esa fila
                const firstInput = existing.querySelector('input');
                // ponemos el cursor ahi y terminamos, para no abrir dos filas al mismo tiempo
                if (firstInput) firstInput.focus();
                return;
            }

            // Obtener la tabla de productos
            const tabla = document.getElementById('tablaProductos');
            if (!tabla) return;

            // Crear nueva fila temporal
            const fila = document.createElement('tr');
            fila.id = 'manual_row_temp';

            // Lista de placeholders
            const placeholders = ['SKU', 'PRODUCTO', 'CANTIDAD', 'LOTE', 'FECHA FABRICACION', 'FECHA VENCIMIENTO', 'SERIE INICIO', 'SERIE FIN'];
            
            // Crear 8 celdas con inputs
            for (let i = 0; i < 8; i++) {
                const td = document.createElement('td');
                const inp = document.createElement('input');
                inp.type = 'text';
                inp.placeholder = placeholders[i];
                inp.dataset.idx = i;
                inp.style.width = '100%';
                td.appendChild(inp);
                fila.appendChild(td);
            }

            
            const tdAcc = document.createElement('td');
            
            // Botón Agregar
            const btnAdd = document.createElement('button');
            btnAdd.type = 'button';
            btnAdd.textContent = 'Agregar';
            // Llama a tu función original
            btnAdd.addEventListener('click', guardarManual); 
            
            // Botón Cancelar
            const btnCancel = document.createElement('button');
            btnCancel.type = 'button';
            btnCancel.textContent = 'Cancelar';
            btnCancel.style.marginLeft = '6px';
            // Llama a tu función original
            btnCancel.addEventListener('click', cerrarModalManual); 
            
            tdAcc.appendChild(btnAdd);
            tdAcc.appendChild(btnCancel);
            fila.appendChild(tdAcc);
           

            tabla.appendChild(fila);

            // seleccionamos todas las cajitas que acabamos de crear
        const inputs = fila.querySelectorAll('input');
        const inputSku = inputs[0];      // la primera es el SKU
        const inputProducto = inputs[1]; // la segunda es el NOMBRE DEL PRODUCTO

        // le ponemos una "oreja" al SKU: cuando el usuario se salga de la casilla (evento blur)...
        inputSku.addEventListener('blur', async function() {
            // tomamos lo que escribió el usuario
            const skuBuscado = this.value.trim();
            
            // si no escribió nada, no hacemos nada
            if(!skuBuscado) return;

            // le damos una pista visual al usuario de que estamos pensando
            const placeholderOriginal = inputProducto.placeholder;
            inputProducto.placeholder = "Buscando...";
            inputProducto.value = ""; // limpiamos por si habia algo antes

            try {
                // preguntamos a la base de datos: "¿Oye, en esta factura y este rut, existe este sku?"
                // usamos el mismo archivo validar_factura.php porque ese ya nos devuelve el nombre
                const url = `/php/ingreso_ventas/registro_ventas/validar_factura.php?rut=${encodeURIComponent(rutVal)}&factura=${encodeURIComponent(numFact)}&sku=${encodeURIComponent(skuBuscado)}`;
                
                const res = await peticionIframe(url);
                const txt = await res.text();
                const limpio = txt.trim();

                // el php nos responde algo como: "50|Jugo de Naranja" (Cantidad | Nombre)
                if (limpio.includes('|')) {
                    const partes = limpio.split('|');
                    const nombreEncontrado = partes[1]; // tomamos la segunda parte que es el nombre
                    
                    if (nombreEncontrado) {
                        // ¡EUREKA! Ponemos el nombre en la casilla de producto automaticamente
                        inputProducto.value = nombreEncontrado; 
                        console.log("Autocompletado nombre:", nombreEncontrado);
                    }
                } else {
                    // si la respuesta no tiene el palito (|), es un error o no existe
                    console.warn("SKU no encontrado en factura para autocompletar");
                    inputProducto.placeholder = "No encontrado en factura"; // avisamos en el fondo gris
                }

            } catch (e) {
                console.error("Error intentando autocompletar:", e);
            } finally {
                // si no encontramos nada, devolvemos el texto gris a "PRODUCTO"
                if(!inputProducto.value) inputProducto.placeholder = placeholderOriginal;
            }
        });

            // Lógica de fechas y ceros 
            
            const inputFab = inputs[4]; 
            const inputVenc = inputs[5]; 
            
            if (inputFab && inputVenc) {
                inputFab.addEventListener('change', function() {
                    const fechaTexto = this.value;
                    if (typeof validarYFormatearFecha === 'function') {
                        const fechaObj = validarYFormatearFecha(fechaTexto);
                        if (fechaObj && fechaObj.anio) {
                            const nuevoAnio = parseInt(fechaObj.anio) + 5;
                            const mes = String(fechaObj.mes).padStart(2, '0');
                            const dia = String(fechaObj.dia).padStart(2, '0');

                            if (fechaTexto.includes('/')) {
                                inputVenc.value = `${nuevoAnio}/${mes}/${dia}`;
                            } else { 
                                inputVenc.value = `${nuevoAnio}-${mes}-${dia}`;
                            }
                        }
                    }
                });
            }

            const inputCant = inputs[2]; 
            const inputIni = inputs[6];  
            const inputFin = inputs[7];  

            function aplicarCeros(inputElement) {
                let valor = inputElement.value.trim();
                if (valor && /^\d+$/.test(valor)) {
                    inputElement.value = valor.padStart(8, '0');
                }
            }

            if (inputCant && inputIni && inputFin) {
                inputIni.addEventListener('blur', function() { aplicarCeros(this); });
                inputFin.addEventListener('blur', function() { aplicarCeros(this); });
            }

            fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const first = fila.querySelector('input'); 
            if (first) first.focus();

        } catch (e) { 
            console.error('iniciarManual error', e); 
        }
    }

    // Guarda los datos ingresados manualmente en la tabla de productos
    function guardarManual() {
        try {
            // obtiene la fila temporal
            const fila = document.getElementById('manual_row_temp');
            // si no existe, salir
            if (!fila) return;
            // obtiene todas las celdas
            const tds = fila.querySelectorAll('td');
            const vals = [];
            // recorrer las 8 celdas y extraer valores de los inputs
            for (let i = 0; i < 8; i++) {
                const inp = tds[i].querySelector('input');
                vals.push(inp ? inp.value.trim() : '');
            }
            // desestructurar valores en variables
            const [sku, producto, cantidad, lote, fechaFab, fechaVenc, serieIni, serieFin] = vals;

            // validación de campos obligatorios
            if (!sku || !producto || !cantidad || !lote) {
                alert('Por favor completa SKU, Producto, Cantidad y Lote.');
                return;
            }

            // intentar formatear fechas de fabricación y vencimiento
            let fFab = fechaFab;
            try { const vf = validarYFormatearFecha(fechaFab); if (vf && vf.fechaFormateada) fFab = vf.fechaFormateada; } catch (e) {}
            let fVenc = fechaVenc;
            try { const vv = validarYFormatearFecha(fechaVenc); if (vv && vv.fechaFormateada) fVenc = vv.fechaFormateada; } catch (e) {}

            const [sIni, sFin] = normalizarSeries(serieIni || '0', serieFin || '0');

            const datosParaTabla = [sku, producto, cantidad, lote, fFab || '', fVenc || '', sIni, sFin];
            agregarFilaProducto(datosParaTabla, 'MANUAL', false);

            // Remover fila temporal
            fila.remove();

            // Asegurar que el botón guardar global esté visible
            const boton = document.getElementById('boton'); if (boton) boton.style.display = 'flex';
        } catch (e) { console.error('guardarManual error', e); alert('No se pudo agregar la fila manual.'); }

        
    }

    // Cierra el modal manual eliminando la fila temporal y ocultando header/botón si no quedan filas
    function cerrarModalManual() {
        try {
            // obtiene la fila temporal
            const fila = document.getElementById('manual_row_temp');
            if (fila) fila.remove();
            // Si no quedan filas, ocultar header y botón
            const filas = document.querySelectorAll('#tablaProductos tr');
            if (!filas || filas.length === 0) {
                const header = document.getElementById('tablaProductosHeader'); if (header) header.style.display = 'none';
                const boton = document.getElementById('boton'); if (boton) boton.style.display = 'none';
            }
        } catch (e) { console.error('cerrarModalManual error', e); }
    }

        // Normaliza y valida los datos recibidos desde el código QR
        // Normaliza y valida los datos recibidos desde el código QR
    function filtros(qrCodeMessage) {
        console.log("Datos antes del filtro:", qrCodeMessage);
        
             // 1. Decodificar caracteres de URL (convertir %2C en comas, %20 en espacios, etc.)
        try {
            qrCodeMessage = decodeURIComponent(qrCodeMessage);
        } catch (e) {
            console.warn("No se pudo decodificar URI componente, usando original.");
        }

        // 2. Si es una URL (empieza con http o https), quedarse solo con los datos finales
        if (qrCodeMessage.startsWith('http')) {
            console.log("Detectada URL, limpiando cabecera...");
            // Generalmente los datos están después de un signo '=' (ej: ...php?data=SKU,PROD...)
            if (qrCodeMessage.includes('=')) {
                // Toma todo lo que esté después del último '='
                qrCodeMessage = qrCodeMessage.substring(qrCodeMessage.lastIndexOf('=') + 1);
            } 
            // Si no hay '=', probamos si están después de un '?'
            else if (qrCodeMessage.includes('?')) {
                qrCodeMessage = qrCodeMessage.substring(qrCodeMessage.lastIndexOf('?') + 1);
            }
        }
        
        // limpieza avanzada (para quitar caracteres basura de la pistola)
        let normalizado = qrCodeMessage
            .replace(/\r?\n+/g, ',') 
            .replace(/[\u002C\uFF0C\u3001]/g, ',') 
            .replace(/[\u00A0\u2007\u202F]/g, ' ') 
            .replace(/\s*,\s*/g, ',') 
            .replace(/C/g, ',') 
            .replace(/\u0081C(?!0)/g, ',') 
            .replace(/(?<=\d)C(?=\d)/g, ',') 
            .replace(/(\d)0(\d{2})-(\d{4})/g, '$1$2-$3'); 

        // Separamos por coma y quitamos espacios vacíos
        let datos = normalizado.split(/[,|\t]+/).map(s => s.trim()).filter(Boolean);
        console.log("Datos separados y limpios:", datos);

        // Si no hay datos, devolvemos vacío
        if (datos.length === 0) return [];

        let campos = [];
        let errores = [];
        let cantidadFromProduct = false;

        try {
            // --- CAMBIO AQUÍ: VALIDACIÓN HÍBRIDA ---
            
            // CASO 1: Es un código simple (solo SKU)
            if (datos.length === 1) {
                // Devolvemos el único dato y salimos de la función. 
                // No sigue ejecutando lo de abajo.
                return [datos[0].trim()]; 
            }

            // CASO 2: Es un código compuesto (SKU, Producto, etc...)
            // Si el código llega acá, es porque datos.length >= 2, así que sigue tu lógica normal:

            // validar SKU (Posición 0)
            if (datos[0] && /[a-zA-Z0-9]/.test(datos[0])) {
                let sku = datos[0].trim();
                if (sku.length > 20) {
                    const base = sku.slice(0, 20);
                    const resto = sku.slice(20);
                    if (resto.startsWith(base)) sku = base;
                }
                campos.push(sku);
            } else {
                errores.push("El SKU no es válido.");
                throw new Error("El SKU no es válido.");
            }

            // validar Producto (Posición 1)
            if (datos[1] && datos[1].trim() !== '') {
                const productoRaw = datos[1].trim();
                const m = productoRaw.match(/^(.+?)\s*-\s*(\d+)\s*$/);
                if (m) {
                    campos.push(m[1].trim()); // Nombre
                    campos.push(m[2].trim()); // Cantidad extraída
                    cantidadFromProduct = true;
                } else {
                    campos.push(productoRaw);
                }
            } else {
                errores.push("El nombre del producto está vacío.");
                throw new Error("El producto no es válido.");
            }

            // logica compleja de Fecha y Cantidad
            let i = 2;
            let fechaEncontrada = '';
            
            while (i < datos.length) {
                const termino = datos[i].trim();

                if (/^\d+$/.test(termino) && campos.length < 3 && !cantidadFromProduct) {
                    campos.push(termino); // Agregamos Cantidad
                }
                else if ((/[-/]/.test(termino) || (termino.startsWith('0') && termino.length > 2)) && !fechaEncontrada) {
                    fechaEncontrada = termino;
                }
                else if (/^\d+$/.test(termino)) {
                    campos.push(termino);
                }
                
                i++;
            }

            // retornamos lo que logramos rescatar del complejo
            return campos; 

        } catch (error) {
            console.error("Error en filtros:", error);
            if (errores.length > 0) {
                throw new Error(errores.join(", "));
            }
            throw error;
        }
    }
    
    const skuDiccionario = {}; 

    let timeoutPistola = null;
    // sistema de perfiles (comunicación con servidor) ===
    var _retornos_pendientes = {};
    var _id_retorno = 0;
    var _id_retorno_actual = null;
    var PERFIL_ENDPOINT = '/php/ingreso_ventas/registro_ventas/normalizar_qr.php';

    // función que php llama cuando responde via iframe
    function phpRespuesta(resultado, datos) {
        var funcion_retorno = _retornos_pendientes[_id_retorno_actual];
        if (funcion_retorno) {
            delete _retornos_pendientes[_id_retorno_actual];
            funcion_retorno(resultado, datos);
        }
    }

    // obtener o crear iframe de comunicación
    function obtener_iframe_comunicacion() {
        var iframe = document.getElementById('commIframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'commIframe';
            iframe.name = 'commIframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        return iframe;
    }

// obtener datos del servidor via GET
function obtener_del_servidor(accion, parametros, funcion_retorno) {
    var iframe = obtener_iframe_comunicacion();
    var url = PERFIL_ENDPOINT + '?action=' + encodeURIComponent(accion);
    for (var clave in parametros) {
        if (parametros.hasOwnProperty(clave)) {
            url += '&' + encodeURIComponent(clave) + '=' + encodeURIComponent(parametros[clave]);
        }
    }
    url += '&t=' + Date.now();

    _id_retorno++;
    _id_retorno_actual = _id_retorno;
    _retornos_pendientes[_id_retorno] = funcion_retorno;

    var id_tiempo_espera = _id_retorno;
    setTimeout(function() {
        if (_retornos_pendientes[id_tiempo_espera]) {
            delete _retornos_pendientes[id_tiempo_espera];
            funcion_retorno('error', {msg: 'timeout'});
        }
    }, 5000);

    iframe.src = url;
}

// convertir cadena a arreglo
function cadena_a_arreglo(cadena) {
    if (!cadena || typeof cadena !== 'string') return [];
    return cadena.split('|');
}


// 1Calcula el tipo de dato (Igualando la lógica de Normalizar)
function calcular_tipo_token(token) {
    if (token == null) return 'O';
    var t = String(token).trim();
    if (!t) return 'O';

    // probamos fecha antes que número para que coincida con la firma guardada
    try { if (validarYFormatearFecha(t)) return 'D'; } catch (e) {}
    
    if (/^\d+$/.test(t)) return 'N';
    if (/[A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(t)) return 'A';
    
    return 'O';
}

function firma_desde_tokens(tokens) {
    var tipos = (tokens || []).map(calcular_tipo_token);
    return tokens.length + '|' + tipos.join('');
}

// pedir lista de perfiles usando tu sistema 'obtener_del_servidor'
function listar_perfiles_ventas() {
    return new Promise(function(resolve) {
        // Usamos 'lista_de_perfiles' que es lo que el PHP entiende
        obtener_del_servidor('lista_de_perfiles', {}, function(resultado, datos) {
            if (resultado === 'success' && datos && datos.profiles) {
                resolve(datos.profiles);
            } else {
                console.warn("Lista vacía o error:", datos);
                resolve([]);
            }
        });
    });
}

// pedir detalle de un perfil usando tu sistema
function obtener_perfil_ventas(nombre) {
    return new Promise(function(resolve) {
        obtener_del_servidor('obtener_el_perfil', { name: nombre }, function(resultado, datos) {
            if (resultado === 'success' && datos) {
                // a veces el perfil viene directo en 'datos' o dentro de 'datos.profile'
                var perfil = datos.profile || datos; 
                
                // asegurar que token_map sea array
                if (perfil.token_map && typeof perfil.token_map === 'string') {
                    perfil.token_map = cadena_a_arreglo(perfil.token_map);
                }
                resolve(perfil);
            } else {
                resolve(null);
            }
        });
    });
}

// busqueda principal (Lo que llama procesarQR)
async function buscar_perfil_por_firma_ventas(firma) {
    console.log("--> Buscando perfil para firma:", firma);
    
    //  obtener la lista
    var perfiles = await listar_perfiles_ventas();
    
    if (!perfiles || perfiles.length === 0) {
        console.log("--> BD devolvió lista vacía.");
        return null;
    }

    // Buscar coincidencia
    // el php devuelve objetos completos en la lista, así que podemos buscar directo
    // sin hacer una segunda petición por cada uno.
    var coincidencia = null;

    for (var i = 0; i < perfiles.length; i++) {
        var p = perfiles[i];
        // Comparamos la firma que viene de la BD con la calculada
        if (p && String(p.signature).trim() === String(firma).trim()) {
            console.log("--> ¡COINCIDENCIA ENCONTRADA!: " + p.name);
            
            // Si el objeto de la lista ya tiene el mapa, lo usamos
            if (p.token_map) {
                // Si viene como string, lo convertimos
                if (typeof p.token_map === 'string') p.token_map = cadena_a_arreglo(p.token_map);
                return p;
            }
            
            // Si no tiene el mapa, pedimos el detalle completo
            return await obtener_perfil_ventas(p.name);
        }
    }

    console.log("--> Ningún perfil coincide con la firma.");
    return null;
}

// aplicar el perfil a los datos
function aplicar_perfil_a_datos_ventas(perfil, tokens_crudos) {
    if (!perfil || !perfil.token_map) return null;
    
    var mapa = perfil.token_map;
    var mapeado = {};

    tokens_crudos.forEach(function(valor, i) {
        var campo = mapa[i];
        if (campo && campo !== 'null' && !campo.startsWith('_unm')) {
            mapeado[campo] = valor;
        }
    });

    return [
        mapeado.sku || '',
        mapeado.producto || '',
        mapeado.cantidad || '',
        mapeado.lote || '',
        mapeado.fechaFab || mapeado.fecha_fabricacion || '',
        mapeado.fechaVenc || mapeado.fecha_vencimiento || '',
        mapeado.serieIni || mapeado.serie_ini || '0',
        mapeado.serieFin || mapeado.serie_fin || '0'
    ];
}
    // evento para escuchar cuando la pistola QR envía datos
    document.addEventListener('DOMContentLoaded', function () {
        const inputQR = document.getElementById('inputQR');

        if (inputQR) {
            // Escuchar el evento "input" o "keyup" para detectar cambios en el campo
            inputQR.addEventListener('input', function () {
                clearTimeout(timeoutPistola); // Limpiar el temporizador anterior

                // Configurar un nuevo temporizador
                timeoutPistola = setTimeout(() => {
                    const qrData = this.value.trim(); // Obtener el valor del campo

                    if (qrData) {
                        console.log('Datos recibidos de pistola:', qrData);
                        // aqui  quiero poner el codigo que me diste 

                        // Normalizar entrada de la pistola usando helper y correcciones adicionales
                        try {
                            // Usa la función general de normalización definida arriba
                            let qrDataProcesado = normalizarEntradaPistola(qrData);

                            // Reemplazar guiones bajos por espacios (muchas pistolas envían '_' en vez de espacio)
                            qrDataProcesado = qrDataProcesado.replace(/_+/g, ' ');

                            // Reemplazar '-' por '/' cuando está entre caracteres alfanuméricos (ej: M-L -> M/L)
                            qrDataProcesado = qrDataProcesado.replace(/(?<=\w)-(?=\w)/g, '/');

                            // Mapear caracteres comunes de teclado 
                            qrDataProcesado = qrDataProcesado.replace(/[;:'"[\]{}\\|]/g, function (match) {
                                const mapa = { ';': 'ñ', ':': 'Ñ', "'": 'á', '"': 'é', '[': '´', ']': '+', '{': '¨', '}': '*', '\\': 'ç', '|': 'Ç' };
                                return mapa[match] || match;
                            });

                            console.log('Datos pistola normalizados:', qrDataProcesado);
                            // Procesar los datos normalizados
                            procesarQR(qrDataProcesado); 
                        } catch (e) {
                            console.error('Error normalizando datos de pistola:', e);
                            // Fallback: procesar crudo si falla la normalización
                            procesarQR(qrData); 
                        }
                        // Limpiar el campo para el próximo escaneo
                        this.value = ''; 
                    }
                // Esperar después de que la pistola termine de escribir
                }, 600); 
            });
        }
    });

    // variables globales para que ambas funciones las compartan
    let qrScanner = null;
    let escaneoRealizado = false;
    // Variable global para almacenar el identificador del timeout
    let timeoutFeedback = null; 
    // Función para mostrar mensajes de feedback
    function mostrar_mensaje_feedback(message, type = 'exitoso') {
        const feedbackDiv = document.getElementById('mensaje_feedback');
        feedbackDiv.style.display = 'none';

        feedbackDiv.textContent = message;
        // Limpiar clases existentes
        feedbackDiv.className = ''; 
        // Añadir clase de tipo (exitoso, error, alerta)
        feedbackDiv.classList.add(type); 
        feedbackDiv.style.display = 'block';
        feedbackDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Cancelar el timeout anterior si existe
        if (timeoutFeedback) {
            clearTimeout(timeoutFeedback);
        }

        // Configurar un nuevo timeout
        timeoutFeedback = setTimeout(() => {
            // Aplicar la clase para desvanecer
            feedbackDiv.classList.add('oculto'); 
            setTimeout(() => {
                // Ocultar completamente después de la transición
                feedbackDiv.style.display = 'none'; 
            // Tiempo de la transición (0.5s)
            }, 500); 
        // Tiempo antes de ocultar (4s)
        }, 4000); 
    }

    // Función que muestra el RAW del QR en un textarea editable para permitir corrección manual
    function mostrarRawYEditar(qrRaw) {
        try {
            // Evitar crear múltiples contenedores
            let cont = document.getElementById('rawScannerContainer');
            if (!cont) {
                cont = document.createElement('div');
                cont.id = 'rawScannerContainer';
                cont.style.border = '1px solid #e0e0e0';
                cont.style.padding = '10px';
                cont.style.marginTop = '12px';
                cont.style.background = '#fff';
                cont.style.boxShadow = '0 1px 4px rgba(0,0,0,0.06)';

                const title = document.createElement('div');
                title.textContent = 'Datos RAW del escaneo (corrija y presione "Procesar")';
                title.style.fontWeight = '600';
                title.style.marginBottom = '6px';
                cont.appendChild(title);

                const ta = document.createElement('textarea');
                ta.id = 'rawScannerTextarea';
                ta.style.width = '100%';
                ta.style.minHeight = '80px';
                ta.style.boxSizing = 'border-box';
                ta.style.padding = '8px';
                cont.appendChild(ta);

                const btnWrap = document.createElement('div');
                btnWrap.style.marginTop = '8px';

                const procesar = document.createElement('button');
                procesar.id = 'rawProcesarBtn';
                procesar.type = 'button';
                procesar.textContent = 'Procesar';
                procesar.style.marginRight = '8px';
                procesar.className = 'btn-procesar-raw';
                btnWrap.appendChild(procesar);

                const agregarProv = document.createElement('button');
                agregarProv.id = 'rawAgregarProvBtn';
                agregarProv.type = 'button';
                agregarProv.textContent = 'Agregar provisional';
                agregarProv.style.marginRight = '8px';
                agregarProv.className = 'btn-agregar-prov';
                btnWrap.appendChild(agregarProv);

                const descartar = document.createElement('button');
                descartar.id = 'rawDescartarBtn';
                descartar.type = 'button';
                descartar.textContent = 'Descartar';
                descartar.className = 'btn-descartar-raw';
                btnWrap.appendChild(descartar);

                cont.appendChild(btnWrap);

                // Insertar después de la tabla de productos si existe, si no, al final del body
                const tabla = document.getElementById('tablaProductos');
                if (tabla && tabla.parentNode) tabla.parentNode.insertBefore(cont, tabla.nextSibling);
                else document.body.appendChild(cont);

                // Event listeners
                procesar.addEventListener('click', function () {
                    const edited = document.getElementById('rawScannerTextarea').value.trim();
                    if (!edited) {
                        mostrar_mensaje_feedback('El texto está vacío. Ingrese los datos raw para procesar.', 'alerta');
                        return;
                    }
                    // Ocultar el contenedor antes de re-procesar
                    cont.style.display = 'none';
                    try {
                        // Reutilizar la función existente para procesar QR (acepta string raw)
                        procesarQR(edited);
                    } catch (e) {
                        console.error('Error re-procesando QR editado:', e);
                        mostrar_mensaje_feedback('Error al procesar los datos editados.', 'error');
                        cont.style.display = 'block';
                    }
                });

                descartar.addEventListener('click', function () {
                    cont.style.display = 'none';
                    mostrar_mensaje_feedback('Escaneo descartado.', 'alerta');
                });
                // Agregar provisional: crear fila incompleta para edición posterior
                agregarProv.addEventListener('click', function () {
                    const raw = document.getElementById('rawScannerTextarea').value.trim();
                    if (!raw) {
                        mostrar_mensaje_feedback('El raw está vacío, no se puede agregar provisional.', 'alerta');
                        return;
                    }
                    // Intento sencillo de extracción por comas para rellenar algunos campos
                    const tokens = raw.split(',').map(t => t.trim());
                    const sku = tokens[0] || '';
                    const producto = tokens[1] || '';
                    const cantidad = tokens[2] || '';
                    const lote = tokens[3] || '';
                    const datosParaTabla = [sku, producto, cantidad, lote, '', '', '0', '0'];
                    try {
                        agregarFilaProducto(datosParaTabla, raw, true);
                        mostrar_mensaje_feedback('Fila agregada como provisional. Edítala antes de guardar.', 'alerta');
                        cont.style.display = 'none';
                    } catch (e) {
                        console.error('Error agregando provisional:', e);
                        mostrar_mensaje_feedback('No se pudo agregar la fila provisional.', 'error');
                    }
                });
            }

            // Poner el raw dentro del textarea y mostrar
            const taExist = document.getElementById('rawScannerTextarea');
            if (taExist) taExist.value = qrRaw;
            cont.style.display = 'block';
            cont.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (e) {
            console.error('mostrarRawYEditar error', e);
        }
    }

    // Helper: agrega una fila a la tabla de productos y una fila visual ONLY-READ de RAW
    // Comprueba si una fila tiene los campos obligatorios completos
    function isFilaCompleta(fila) {
        try {
            const columnas = fila.querySelectorAll('td');
            if (!columnas || columnas.length < 8) return false;
            const sku = columnas[0].innerText.trim();
            const cantidad = columnas[2].innerText.trim();
            const lote = columnas[3].innerText.trim();
            const fechaFab = columnas[4].innerText.trim();
            const fechaVenc = columnas[5].innerText.trim();
            const serieIni = columnas[6].innerText.trim();
            const serieFin = columnas[7].innerText.trim();
            if (!sku) return false;
            if (!cantidad) return false;
            if (!lote) return false;
            if (!fechaFab || fechaFab === 'undefined') return false;
            if (!fechaVenc || fechaVenc === 'undefined') return false;
            if (!serieIni) return false;
            if (!serieFin) return false;
            return true;
        } catch (e) {
            console.error('isFilaCompleta error', e);
            return false;
        }
    }

    // Agrega una nueva fila a la tabla de productos con los datos proporcionados
    function agregarFilaProducto(datosParaTabla, rawString, incomplete, origen) {
        // si no nos dicen el origen, asumimos que es 'nuevo' (escaneado recien)
        if (!origen) origen = 'nuevo';
        if (incomplete === undefined) incomplete = false;
        
        console.log("=== agregarFilaProducto ===");
        console.log("Datos:", datosParaTabla);
        console.log("Raw:", rawString);

        if (origen === 'nuevo' || origen === 'MANUAL' || origen === 'DESDE_NORMALIZAR') {
            const skuCheck = datosParaTabla[0]; // El SKU está en la posición 0
            const cantCheck = datosParaTabla[2]; // La Cantidad está en la posición 2
            
            // Llamamos a la función calculadora
            verificarExcesoVisual(skuCheck, cantCheck);
        }
        
        try {
            var tablaProductos = document.getElementById("tablaProductos");
            if (!tablaProductos) {
                console.error('No se encontró #tablaProductos');
                return;
            }

            // Crear nueva fila
            var fila = document.createElement('tr');
            var filaId = 'prod_row_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
            fila.id = filaId;
            
            // etiquetamos la fila aqui guardamos si es 'bd' o 'nuevo' en el HTML
            fila.setAttribute('data-origen', origen);

            // Si viene de la BD, le ponemos un color diferente (gris suave) para diferenciarlo visualmente
            if (origen === 'bd') {
                fila.style.backgroundColor = '#f0f0f0'; // gris claro
                fila.style.color = '#555'; // texto gris oscuro
            }

            // Crear celdas a partir del array
            datosParaTabla.forEach(function(dato, index) {
                var celda = document.createElement('td');
                celda.textContent = dato || '';
                fila.appendChild(celda);
            });

            // Celda de acciones
            var celdaAcciones = document.createElement('td');

           // Botón Quitar
            var btnQuitar = document.createElement('button');
            btnQuitar.type = 'button';
            btnQuitar.textContent = 'Eliminar';
            btnQuitar.className = 'btn-quitar';
            
            btnQuitar.addEventListener('click', function() {
                // Confirmación
                if (!confirm('¿Eliminar de la lista?')) return;
                
                // Si es un producto nuevo, solo lo borramos de la pantalla
                if (origen === 'nuevo') {
                    var skuCelda = fila.querySelector('td'); 
                    var sku = skuCelda ? skuCelda.textContent.trim() : '';
                    if (sku && typeof skuDiccionario !== 'undefined') delete skuDiccionario[sku];
                    fila.remove();
                    actualizarVisualesPanel();
                    // actualizar checklist visual si existe
                    if (typeof dibujarChecklist === 'function') dibujarChecklist();
                    return;
                }

                // Si es de la BD, hay que borrarlo de la base de datos realmente
                var skuCelda = fila.querySelector('td'); 
                var sku = skuCelda ? skuCelda.textContent.trim() : '';
                var rut = document.getElementById('rut').value;
                var factura = document.getElementById('numero_factu').value;

                const datos = new URLSearchParams();
                datos.append('rut', rut);
                datos.append('factura', factura);
                datos.append('sku', sku);

                peticionIframe('/php/ingreso_ventas/registro_ventas/eliminar_producto.php', {
                    method: 'POST',
                    body: datos
                })
                .then(res => res.text())
                .then(respuesta => {
                    if (respuesta.trim() === 'OK') {
                        if (sku && typeof skuDiccionario !== 'undefined') delete skuDiccionario[sku];
                        fila.remove();
                        // actualizar checklist visual si existe
                        if (typeof dibujarChecklist === 'function') dibujarChecklist();
                    } else {
                        alert('Error al eliminar: ' + respuesta);
                    }
                });
            });
            celdaAcciones.appendChild(btnQuitar);

            // Botón Modificar 
            var btnModificar = document.createElement('button');
            btnModificar.type = 'button';
            btnModificar.textContent = 'Modificar';
            btnModificar.className = 'btn-modificar';
            btnModificar.style.marginLeft = '6px';
            
            var editing = false;
            var originalValues = [];

            btnModificar.addEventListener('click', function() {
                var columnas = fila.querySelectorAll('td');
                
                if (!editing) {
                    // Comenzar edición
                    originalValues = [];
                    for (var i = 0; i < columnas.length - 1; i++) {
                        var td = columnas[i];
                        originalValues[i] = td.textContent;
                        var input = document.createElement('input');
                        input.type = 'text';
                        input.value = td.textContent;
                        input.style.width = '100%';
                        td.textContent = '';
                        td.appendChild(input);
                    }
                    editing = true;
                    btnModificar.textContent = 'Guardar';
                } else {
                    // Guardar cambios
                    for (var i = 0; i < columnas.length - 1; i++) {
                        var td = columnas[i];
                        var inp = td.querySelector('input');
                        td.textContent = inp ? inp.value.trim() : '';
                    }
                    editing = false;
                    btnModificar.textContent = 'Modificar';

                    // Si editamos un producto viejo, ahora lo tratamos como 'nuevo'
                    // para que el sistema sepa que tiene que guardar los cambios en la BD al final
                    if (origen === 'bd') {
                        origen = 'nuevo';
                        fila.setAttribute('data-origen', 'nuevo');
                        // le quitamos el color gris
                        fila.style.backgroundColor = '#fff'; 
                        fila.style.color = '#000';
                        console.log("Producto editado marcado como 'nuevo' para actualización.");
                    }
                }
                actualizarVisualesPanel();
            });
            
            celdaAcciones.appendChild(btnModificar);

            fila.appendChild(celdaAcciones);

            if (incomplete) {
                fila.dataset.incomplete = 'true';
                fila.style.background = '#fff7f7';
            }

            tablaProductos.appendChild(fila);
            
            
            actualizarVisualesPanel(); 
            // Le dice al sistema: "Acabo de meter un producto nuevo, ¡cuenta todo otra vez y actualiza el cuadro azul!"

        } catch (e) {
            console.error('agregarFilaProducto error:', e);
        }
    }

    // Función para validar y formatear fecha
    function validarYFormatearFecha(fechaStr) {
        if (fechaStr == null) return null;
        const original = String(fechaStr).trim();
        if (!original) return null;

        // normalizar separadores y palabras comunes
        let s = original.replace(/\u00A0/g, ' ').trim();
        s = s.replace(/[._\s\\\-\u2013]+/g, '/').replace(/\/+/g, '/').replace(/\s+de\s+/gi, '/');

        // Funciones auxiliares
        const toInt = v => parseInt(String(v).replace(/^0+/, ''), 10) || 0;
        const isValid = (y, m, d) => {
            y = Number(y); m = Number(m); d = Number(d);
            if (!Number.isFinite(y) || !Number.isFinite(m) || !Number.isFinite(d)) return false;
            if (y < 1000 || y > 9999) return false;
            const dt = new Date(y, m - 1, d);
            return dt.getFullYear() === y && (dt.getMonth() + 1) === m && dt.getDate() === d;
        };
        // construye objeto con fecha formateada
        const build = (y, m, d) => ({
            anio: Number(y),
            mes: String(m).padStart(2, '0'),
            dia: String(d).padStart(2, '0'),
            fechaFormateada: `${String(y).padStart(4,'0')}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`
        });

        // Intentar parse directo (ISO y variantes)
        const dtDirect = new Date(s);
        if (!isNaN(dtDirect.getTime()) && dtDirect.getFullYear() >= 1000 && dtDirect.getFullYear() <= 9999) {
            return build(dtDirect.getFullYear(), dtDirect.getMonth() + 1, dtDirect.getDate());
        }

        // Patrones comunes
        let m;
        // MM/YYYY -> día = 01
        m = s.match(/^(\d{1,2})\/(\d{4})$/);
        if (m && isValid(m[2], m[1], 1)) return build(m[2], m[1], 1);

        // DD/MM/YYYY
        m = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
        if (m && isValid(m[3], m[2], m[1])) return build(m[3], m[2], m[1]);

        // YYYY/MM/DD o YYYY-MM-DD
        m = s.match(/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/);
        if (m && isValid(m[1], m[2], m[3])) return build(m[1], m[2], m[3]);

        // Solo YYYY
        m = s.match(/^(\d{4})$/);
        if (m && isValid(m[1], 1, 1)) return build(m[1], 1, 1);

        //  Sólo dígitos: YYYYMMDD, DDMMYYYY, MMYYYY, YYYYMM, YYYY
        const digits = original.replace(/\D/g, '');
        if (/^\d+$/.test(digits)) {
            if (digits.length === 8) {
                const y1 = digits.slice(0,4), m1 = digits.slice(4,6), d1 = digits.slice(6,8);
                if (isValid(y1,m1,d1)) return build(y1,m1,d1);
                const d2 = digits.slice(0,2), m2 = digits.slice(2,4), y2 = digits.slice(4,8);
                if (isValid(y2,m2,d2)) return build(y2,m2,d2);
            } else if (digits.length === 6) {
                const mm = digits.slice(0,2), yyyy = digits.slice(2,6);
                if (isValid(yyyy, mm, 1)) return build(yyyy, mm, 1);
                const yyyy2 = digits.slice(0,4), mm2 = digits.slice(4,6);
                if (isValid(yyyy2, mm2, 1)) return build(yyyy2, mm2, 1);
            } else if (digits.length === 4) {
                if (isValid(digits, 1, 1)) return build(digits, 1, 1);
            }
        }

        // Buscar subcadenas que parezcan fecha
        m = original.match(/(\d{1,2}\/\d{4})/);
        if (m) return validarYFormatearFecha(m[1]);
        m = original.match(/(\d{1,2}\/\d{1,2}\/\d{4})/);
        if (m) return validarYFormatearFecha(m[1]);
        m = original.match(/(\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2})/);
        if (m) return validarYFormatearFecha(m[1]);

        return null;
    }

// Variable global para bloquear escaneos múltiples
var _scanLockVentas = false;


function procesar_qr_con_datos(mensaje_qr, datos) {
    console.log("=== INICIO procesar_qr_con_datos ===");
    
    try {
        // Asegurar que el array tenga al menos 8 espacios
        while (datos.length < 8) {
            datos.push('');
        }

        // Procesamiento de Fechas 
        var fecha_fab = datos[4] ? datos[4].trim() : '';
        var fecha_formateada = '';
        var fecha_venc_formateada = '';

        // Intentar formatear fecha fabricación
        if (fecha_fab && typeof validarYFormatearFecha === 'function') {
            try {
                var parsed = validarYFormatearFecha(fecha_fab);
                if (parsed && parsed.fechaFormateada) {
                    fecha_formateada = parsed.fechaFormateada;
                    // Calcular vencimiento (ej: +5 años)
                    var av = parsed.anio + 5;
                    fecha_venc_formateada = av + '-' + parsed.mes + '-' + parsed.dia;
                }
            } catch (e) { console.warn("Error fecha fab:", e); }
        }

        // Si no hay fecha fab, intentar con fecha vencimiento
        if (!fecha_formateada && datos[5] && typeof validarYFormatearFecha === 'function') {
            try {
                var fecha_venc = datos[5].trim();
                var parsedV = validarYFormatearFecha(fecha_venc);
                if (parsedV && parsedV.fechaFormateada) {
                    fecha_venc_formateada = parsedV.fechaFormateada;
                    // Calcular fabricación (-5 años)
                    var af = parsedV.anio - 5;
                    fecha_formateada = af + '-' + parsedV.mes + '-' + parsedV.dia;
                }
            } catch (e) { console.warn("Error fecha venc:", e); }
        }

        // --- Normalización de Series ---
        var sIni = datos[6] || '0';
        var sFin = datos[7] || '0';
        if (typeof normalizarSeries === 'function') {
            var series = normalizarSeries(sIni, sFin, (typeof LONG_SERIE !== 'undefined' ? LONG_SERIE : 8));
            sIni = series[0];
            sFin = series[1];
        }

        // Preparar Datos para la Tabla
        var filaDatos = [
            (datos[0] || '').trim(),
            (datos[1] || '').trim(),
            (datos[2] || '').trim(),
            (datos[3] || '').trim(),
            fecha_formateada,
            fecha_venc_formateada,
            sIni,
            sFin
        ];

        // Validaciones de Interfaz
        var header = document.getElementById("tablaProductosHeader");
        var btn = document.getElementById("boton");
        if (header) header.style.display = "table";
        if (btn) btn.style.display = "flex";

        // Eliminamos la pregunta de confirmación 
        // Simplemente registramos el SKU para control interno, pero dejamos pasar el duplicado
        if (typeof skuDiccionario !== 'undefined') {
            var sku = filaDatos[0];
            if (sku) {
                skuDiccionario[sku] = true;
            }
        }

        // Agregar a la Tabla directamente
        if (typeof agregarFilaProducto === 'function') {
            agregarFilaProducto(filaDatos, mensaje_qr, false, 'nuevo');
            mostrar_mensaje_feedback("Producto agregado (acumulando).", "exitoso");
            
            // Scroll automático
            var tabla = document.getElementById("tablaProductos");
            if (tabla) tabla.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            console.error("Error: Función agregarFilaProducto no encontrada");
        }

    } catch (ex) {
        console.error("Excepción en procesar_qr_con_datos:", ex);
        mostrar_mensaje_feedback("Error interno al procesar datos: " + ex.message, "error");
    }
}

// Función para procesar QR con validaciones de factura y Búsqueda Inteligente
// Función para procesar QR con validaciones de factura y Búsqueda Inteligente
async function procesarQR(qrCodeMessage) {
    console.log("=== INICIO PROCESAMIENTO INTELIGENTE ===");
    // Limpieza básica inicial
    const codigo = qrCodeMessage.trim();
    
    // 1. Validar que existan los inputs obligatorios antes de empezar
    const rutInput = document.getElementById('rut');
    const factInput = document.getElementById('numero_factu');
    
    if (!rutInput.value || !factInput.value) {
        alert("Primero debe ingresar RUT y N° de Factura antes de escanear.");
        return;
    }

    try {
        let skuCandidato = "";
        let datosOrdenados = null;
        
        // 2. Obtener tokens CRUDOS (separados por coma, tab, pipe, etc.)
        let tokensRaw = separarDatosQR(codigo);
        
        // 3. Obtener tokens LIMPIOS (Filtros estrictos)
        let tokensLimpios = [];
        try {
            tokensLimpios = filtros(codigo); 
        } catch (e) {
            console.warn("Filtro estricto no aplicable (posible QR simple o formato nuevo):", e.message);
            tokensLimpios = []; 
        }

        // Detectar tipo de código
        const esURL = codigo.startsWith('http') && (codigo.includes('sku=') || codigo.includes('ver_producto='));
        const tieneSeparadores = tokensRaw.length > 1;

        // --- LÓGICA DE DECISIÓN ---

        if (esURL) {
            // CASO 1: Es una URL
            try {
                const urlObj = new URL(codigo);
                skuCandidato = urlObj.searchParams.get('sku') || urlObj.searchParams.get('ver_producto');
            } catch (e) { console.error("Error procesando URL:", e); }
        }
        else if (tieneSeparadores) {
            // CASO 2: QR COMPLEJO (Tiene separadores)
            // Intentamos buscar un perfil guardado en la base de datos
            const firma = crearFirmaQR(codigo);
            console.log("Buscando perfil con firma:", firma);
            
            const respuestaPerfil = await new Promise(resolve => {
                 peticionIframe(`/php/ingreso_ventas/registro_ventas/normalizar_qr.php?action=check_profile_signature&signature=${encodeURIComponent(firma)}`)
                 .then(r => r.text()).then(txt => resolve(txt)).catch(() => resolve(""));
            });

            if (respuestaPerfil.includes("ENCONTRADO|")) {
                console.log("¡Perfil ENCONTRADO! Aplicando mapa...");
                const mapaStr = respuestaPerfil.replace("ENCONTRADO|", "").trim();
                const mapaIndices = mapaAObjeto(mapaStr);
                
                // Preparamos el array ordenado según el perfil
                datosOrdenados = ['', '', '', '', '', '', '', ''];
                if (mapaIndices['sku'] !== undefined) datosOrdenados[0] = tokensRaw[mapaIndices['sku']];
                if (mapaIndices['producto'] !== undefined) datosOrdenados[1] = tokensRaw[mapaIndices['producto']];
                if (mapaIndices['cantidad'] !== undefined) datosOrdenados[2] = tokensRaw[mapaIndices['cantidad']];
                if (mapaIndices['lote'] !== undefined) datosOrdenados[3] = tokensRaw[mapaIndices['lote']];
                if (mapaIndices['fechaFab'] !== undefined) datosOrdenados[4] = tokensRaw[mapaIndices['fechaFab']];
                if (mapaIndices['fechaVenc'] !== undefined) datosOrdenados[5] = tokensRaw[mapaIndices['fechaVenc']];
                if (mapaIndices['serieIni'] !== undefined) datosOrdenados[6] = tokensRaw[mapaIndices['serieIni']];
                if (mapaIndices['serieFin'] !== undefined) datosOrdenados[7] = tokensRaw[mapaIndices['serieFin']];
                
                // Limpieza final de espacios
                datosOrdenados = datosOrdenados.map(d => (d || '').trim());
                skuCandidato = datosOrdenados[0]; 
            } else {
                // Si NO hay perfil, intentamos usar el primer dato limpio como candidato
                console.warn("Perfil NO encontrado. Usando primer token limpio.");
                if (tokensLimpios.length > 0) skuCandidato = tokensLimpios[0];
            }
        }
        else {
            // CASO 3: QR SIMPLE (Sin separadores)
            skuCandidato = codigo;
        }

        // --- LÓGICA DE REDIRECCIÓN A NORMALIZAR ---
        
        // Si después de todo no tenemos SKU, y es complejo, mandamos a normalizar DE INMEDIATO
        if (!skuCandidato || skuCandidato.trim() === "") {
            if (tieneSeparadores) {
                 console.warn("QR Complejo sin perfil -> Redirigiendo a Normalizar");
                 // CAMBIO: true para forzar y evitar bloqueo del navegador
                 redirigirANormalizar(codigo, "Formato desconocido", true); 
                 return;
            }
            alert("No se pudo leer datos del código.");
            return;
        }

        // --- VALIDACIÓN CON BASE DE DATOS ---
        
        console.log(`Validando SKU Principal: ${skuCandidato}`);
        
        const consultarBD = async (skuTest) => {
            const r = await peticionIframe(`/php/ingreso_ventas/registro_ventas/validar_factura.php?rut=${encodeURIComponent(rutInput.value)}&factura=${encodeURIComponent(factInput.value)}&sku=${encodeURIComponent(skuTest)}`);
            return await r.text();
        };

        let respuesta = await consultarBD(skuCandidato);
        let esValido = (respuesta.trim() === "SKU_EXISTE_EN_FACTURA" || /^\d+\|/.test(respuesta.trim()) || /^\d+$/.test(respuesta.trim()));

        // BÚSQUEDA INTELIGENTE
        if (!esValido && !datosOrdenados && tokensLimpios && tokensLimpios.length > 0) {
            console.warn("Validación directa falló. Iniciando escaneo profundo...");
            for (let i = 0; i < tokensLimpios.length; i++) {
                let tokenTest = tokensLimpios[i].trim();
                if (tokenTest === skuCandidato || tokenTest.length < 1) continue; 
                
                let respTest = await consultarBD(tokenTest);
                if (respTest.trim() === "SKU_EXISTE_EN_FACTURA" || /^\d+\|/.test(respTest.trim()) || /^\d+$/.test(respTest.trim())) {
                    console.log("¡SKU Encontrado en posición alternativa!");
                    skuCandidato = tokenTest; 
                    respuesta = respTest;      
                    esValido = true;
                    mostrar_mensaje_feedback("Producto encontrado (Smart Search).", "exitoso");
                    break; 
                }
            }
        }

        // --- RESULTADO FINAL ---

        if (esValido) {
             // Caso especial: Validó pero era complejo y sin perfil -> Mejor normalizar
             if (tieneSeparadores && !datosOrdenados) {
                 console.warn("SKU validado pero formato complejo sin perfil. Sugiriendo normalizar.");
                 // CAMBIO: true para forzar y asegurar que se guarde el perfil
                 redirigirANormalizar(codigo, "SKU identificado (" + skuCandidato + "), pero el formato requiere configuración.", true);
                 return;
             }

             // ÉXITO
             mostrar_mensaje_feedback("Producto validado y procesado.", "exitoso");
             if (datosOrdenados) {
                 procesar_qr_con_datos(codigo, datosOrdenados);
             } else {
                 buscarDetallesYAgregar(skuCandidato, codigo);
             }

        } else {
            // FALLO: El SKU no existe en la factura o no es válido
            if (tieneSeparadores) {
                 // CAMBIO IMPORTANTE:
                 // Si es complejo y falló, forzamos (true) la redirección.
                 // Antes estaba en 'false' y el navegador bloqueaba la pregunta.
                 console.log("Redirigiendo forzosamente a normalizar por fallo de validación.");
                 redirigirANormalizar(codigo, "SKU no encontrado tras búsqueda inteligente", true);
            } else {
                 alert(` ALTO:\nEl producto escaneado (SKU: ${skuCandidato}) NO corresponde a la factura.\nRevise si es el producto correcto.`);
            }
        }

    } catch (errGlobal) {
        console.error("Error Fatal en procesarQR:", errGlobal);
        alert("Error procesando código: " + errGlobal.message);
    }
}

// Función auxiliar necesaria para el caso simple (URL o SKU solo)
function buscarDetallesYAgregar(sku, qrRaw) {
     peticionIframe(`/php/ingreso_ventas/registro_ventas/buscar_venta.php?valor=${encodeURIComponent(sku)}`)
        .then(res => res.text())
        .then(txt => {
            const data = stringToObject(txt);
            // Armamos la fila
            const fila = [
                sku, 
                data.producto || '', 
                data.cantidad || '', 
                data.lote || '', 
                data.fecha_fabricacion || '', 
                data.fecha_vencimiento || '', 
                data.n_serie_ini || '0', 
                data.n_serie_fin || '0'
            ];
            procesar_qr_con_datos(qrRaw, fila);
        });
}

//  TITULO LECTOR QR

    // Función para iniciar el escáner QR
    function iniciarScanner() {
        if (qrScanner) {
            console.warn("El escáner ya está activo");
            return;
        }

        escaneoRealizado = false;
        
        const lector = document.getElementById("lectorQR");
        lector.style.display = "block";
        lector.scrollIntoView({ behavior: "smooth", block: "center" });
        
        const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
        qrScanner = new Html5Qrcode("lectorQR");

        const calculateQrboxSize = () => {
            const minQrboxSize = 300;
            const maxQrboxSize = 500;
            const idealRatio = 0.7;
            const containerWidth = lector.offsetWidth;
            const containerHeight = lector.offsetHeight;
            let size = Math.min(containerWidth, containerHeight) * idealRatio;
            size = Math.max(minQrboxSize, Math.min(size, maxQrboxSize));
            return { width: size, height: size };
        };
        
        let qrboxConfig = calculateQrboxSize();

        qrScanner.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: qrboxConfig,
                aspectRatio: isIOS ? 1.333333 : 1.0,
                formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
            },
            async qrCodeMessage => {
                if (escaneoRealizado) {
                    console.log('Escaneo ignorado - ya procesado anteriormente');
                    return;
                }
                
                escaneoRealizado = true;
                
                console.log('QR detectado:', qrCodeMessage);
                
                try {
                    await qrScanner.stop();
                    await qrScanner.clear();
                    qrScanner = null;
                    lector.style.display = "none";
                    console.log('Escáner detenido correctamente');
                } catch (err) {
                    console.error('Error deteniendo escáner:', err);
                    qrScanner = null;
                }
                
                console.log('Iniciando procesamiento del QR...');
                await procesarQR(qrCodeMessage);
            },
            // Silenciar errores de búsqueda
            errorMessage => {
                
            }
        ).catch(err => {
            console.error("Error iniciando escáner:", err);
            mostrar_mensaje_feedback("Error al iniciar cámara.", "error");
        });
    }

// esto es para redirigir a normalizar qr con los qr escaneados
function redirigirANormalizar(qrRaw, motivo = "", forzar = false) {
    // Si no es forzado preguntamos.
    if (!forzar) {
        const mensaje = `El código escaneado requiere verificación.\nMotivo: ${motivo}\n\n¿Ir a "Normalizar QR"?`;
        // Si dice que no, no hacemos nada.
        if (!confirm(mensaje)) return; 
    }

    // Datos actuales
    const rut = document.getElementById('rut')?.value || ''; 
    const factura = document.getElementById('numero_factu')?.value || '';
    const fecha = document.getElementById('fecha_actual')?.value || '';

    // Productos previos (para no perderlos)
    const productosExistentes = [];
    document.querySelectorAll('#tablaProductos tr').forEach(fila => {
        const c = fila.querySelectorAll('td');
        if (c.length >= 8) {
            // Formato simple texto: sku:valor|prod:valor etc
            const s = `sku:${c[0].innerText}|producto:${c[1].innerText}|cantidad:${c[2].innerText}|lote:${c[3].innerText}|fechaFab:${c[4].innerText}|fechaVenc:${c[5].innerText}|serieIni:${c[6].innerText}|serieFin:${c[7].innerText}`;
            productosExistentes.push(s);
        }
    });

    // crear formulario oculto (post)
    const form = document.createElement('form');
    form.method = 'POST';
    // Enviamos el código QR en la URL (GET) para que la otra página lo vea
    // Y el resto de datos pesados los enviamos por POST (ocultos).
    form.action = `/php/ingreso_ventas/renderizar_menu.php?pagina=normalizar_qr&raw_data=${encodeURIComponent(qrRaw)}`; 
    form.style.display = 'none';

    const addField = (name, val) => {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = name;
        i.value = val;
        form.appendChild(i);
    };

    // Estos van por POST
    addField('rut_cliente', rut);
    addField('num_factura', factura);
    addField('fecha_despacho', fecha);
    addField('desde_normalizar', '1');

    if (productosExistentes.length > 0) {
        // Encriptar base64 para seguridad básica en el transporte
        addField('productos_previos', btoa(encodeURIComponent(productosExistentes.join('||'))));
    }

    document.body.appendChild(form);
    form.submit();
}

// TITULO INFORMACION TABLA DE PRODUCTOS

    // SIN FUNCION

// TITULO CUERPO DINAMICO DE TABLA

    // SIN FUNCION


// TITULO BOTONES GUARDAR

    // Función para guardar los datos del formulario
    async function guardarDatos() {
        // Obtiene los valores de los campos del formulario
        const rut = document.getElementById('rut').value.replace(/\./g, '').trim();
        const numeroFact = document.getElementById('numero_factu').value.trim();
        const fechaActual = document.getElementById('fecha_actual').value.trim();

        // Valida que los campos obligatorios estén llenos entonces preguntamos si falta alguno de los tres datos importantes de arriba
        if (!rut || !numeroFact || !fechaActual) {
            // si falta algo, mostramos un mensaje de alerta y paramos todo aqui
            mostrar_mensaje_feedback('Por favor, completa todos los campos obligatorios.', "alerta");
            return;
        }

        // Obtiene los datos de la tabla de productos entonces creamos una lista vacia donde iremos metiendo los productos validos
        const filas = document.querySelectorAll('#tablaProductos tr');
        
        // aqui creamos dos listas separadas:
        // lista 1: para revisar que la suma total (viejos + nuevos) este correcta segun la factura
        const productosParaValidar = {};
        // lista 2: solo los productos nuevos que acabas de escanear, para enviarlos al servidor y no duplicar los viejos
        const productosParaGuardar = [];

        // empezamos a revisar fila por fila, una por una
        for (let index = 0; index < filas.length; index++) {
            const fila = filas[index];
            // revisamos si esta fila esta marcada como incompleta por algun error anterior
            if (fila.dataset && fila.dataset.incomplete === 'true') {
                 // si esta incompleta, avisamos al usuario y no dejamos guardar
                mostrar_mensaje_feedback(`Existen filas incompletas (fila ${index + 1}). Edítalas antes de guardar.`, 'error');
                return;
            }

            // preguntamos si esta fila es 'bd' (vieja) o 'nuevo' (recien escaneada)
            const origen = fila.getAttribute('data-origen') || 'nuevo';
            const columnas = fila.querySelectorAll('td');

            // si la fila tiene menos columnas de las necesarias, la saltamos
            if (columnas.length < 8) continue;
            
            // guardamos los datos leyendo el texto que hay en cada columna
            let sku, producto, cantidad, lote, f_fab, f_venc, s_ini, s_fin;

            // esta funcion sirve para leer el valor, ya sea que este escrito en texto o dentro de una cajita input
            const getVal = (idx) => {
                const el = columnas[idx];
                return el.querySelector('input') ? el.querySelector('input').value : el.innerText;
            };

            // leemos cada dato y le quitamos los espacios de los lados
            sku = getVal(0).trim();
            producto = getVal(1).trim();
            cantidad = getVal(2).trim();
            lote = getVal(3).trim();
            f_fab = getVal(4).trim();
            f_venc = getVal(5).trim();
            s_ini = getVal(6).trim();
            s_fin = getVal(7).trim();

            // 1. agregamos el producto a la lista de validacion
            // convertimos la cantidad a numero para poder sumar
            const cantNum = parseInt(cantidad.replace(/\./g, '')) || 0;
            // si es la primera vez que vemos este sku, lo guardamos
            if (!productosParaValidar[sku]) {
                productosParaValidar[sku] = { cantidad: 0, nombre: producto };
            }
            // sumamos la cantidad a lo que ya teniamos (asi juntamos viejos y nuevos)
            productosParaValidar[sku].cantidad += cantNum;

            // 2. agregamos a la lista de guardar solo si es un producto nuevo
            if (origen === 'nuevo') {
                // guardamos todos los detalles para enviarlos al servidor despues
                productosParaGuardar.push({
                    sku: sku, producto: producto, cantidad: cantidad, lote: lote,
                    fecha_fabricacion: f_fab, fecha_vencimiento: f_venc,
                    n_serie_ini: s_ini, n_serie_fin: s_fin
                });
            }
        }

        // paso a: validar que los totales sean correctos
        // guardamos el rut en una variable para usarla en la validacion
        const rutLimpio = rut; 
        // revisamos cada producto de la lista de validacion
        for (const [sku, info] of Object.entries(productosParaValidar)) {
            // preguntamos al servidor si la cantidad total (viejos + nuevos) coincide con la factura
            const esValido = await validarCantidadFactura(rutLimpio, numeroFact, sku, info.cantidad, info.nombre);
            // si el servidor nos dice que no cuadra, detenemos todo
            if (!esValido) return; 
        }

        // paso b: guardar solo los nuevos en la base de datos
        // si la lista de nuevos esta vacia, significa que no escaneaste nada nuevo
        if (productosParaGuardar.length === 0) {
            // avisamos que todo esta bien pero no hay nada que guardar
            mostrar_mensaje_feedback('Validación correcta. No hay datos nuevos que guardar.', "exitoso");
            return;
        }

        // si llegamos aquí, todo es válido. procedemos a guardar asi que preparamos un paquete de datos para enviarlo al servidor
        const datosEnviar = new URLSearchParams();
        datosEnviar.append('rut', rut);
        datosEnviar.append('numeroFact', numeroFact);
        datosEnviar.append('fechaActual', fechaActual);

        productosParaGuardar.forEach((prod, index) => {
            datosEnviar.append(`productos[${index}][sku]`, prod.sku);
            datosEnviar.append(`productos[${index}][producto]`, prod.producto);
            datosEnviar.append(`productos[${index}][cantidad]`, prod.cantidad);
            datosEnviar.append(`productos[${index}][lote]`, prod.lote);
            datosEnviar.append(`productos[${index}][fecha_fabricacion]`, prod.fecha_fabricacion);
            datosEnviar.append(`productos[${index}][fecha_vencimiento]`, prod.fecha_vencimiento);
            datosEnviar.append(`productos[${index}][n_serie_ini]`, prod.n_serie_ini);
            datosEnviar.append(`productos[${index}][n_serie_fin]`, prod.n_serie_fin);
        });

        // intentamos enviar el paquete al servidor para que lo guarde en la base de datos
        try {
            // hacemos el envio usando la tecnica del iframe oculto (metodo post)
            const response = await peticionIframe(`/php/ingreso_ventas/registro_ventas/guardar_datos.php`, {
                method: 'POST',
                body: datosEnviar
            });
            // leemos lo que nos respondio el servidor
            const textoRespuesta = await response.text();
            
            // si el servidor nos respondio ok o exito
            if (textoRespuesta.trim() === "OK" || textoRespuesta.includes("exito")) {
                // mostramos un mensaje verde de felicidad
                mostrar_mensaje_feedback('Datos nuevos guardados correctamente.', "exitoso");
                // recargamos la pagina despues de 2 segundos
                setTimeout(() => location.reload(), 2000);
            } else {
                // si el servidor respondio otra cosa, es un error, asi que lo lanzamos
                throw new Error(textoRespuesta);
            }
        } catch (error) {
            // si algo fallo en el envio, mostramos el mensaje de error
            mostrar_mensaje_feedback(`Error al guardar: ${error.message}`, "error");
        }
    }

// TTITULO PANEL ESTADO FACTURA

// Función para mover el contenido cuando aparece el panel
// Calcula la resta (Meta - Escaneado) y dibuja el cuadro
function actualizarVisualesPanel() {
    const contenedor = document.getElementById('contenido-resumen');
    const panel = document.getElementById('panel-resumen');
    
    if (!contenedor) return;

    // 1. Sumar todo lo que hay en la tabla actualmente
    let conteoActual = {};
    const filas = document.querySelectorAll('#tablaProductos tr');
    
    filas.forEach(f => {
        const c = f.querySelectorAll('td');
        if (c.length > 2) {
            const sku = c[0].textContent.trim();
            const cant = parseInt(c[2].textContent.replace(/\./g, '')) || 0;
            if (!conteoActual[sku]) conteoActual[sku] = 0;
            conteoActual[sku] += cant;
        }
    });

    // 2. Construimos la tabla HTML con las 5 columnas solicitadas
    let html = `
    <table class="tabla-visual-resumen">
        <thead>
            <tr>
                <th style="width: 15%;">SKU</th>
                <th style="width: 35%;">PRODUCTO</th>
                <th style="width: 15%; text-align: center;">CANTIDAD FACTURA</th>
                <th style="width: 15%; text-align: center;">CANTIDAD INGRESADA</th>
                <th style="width: 20%; text-align: center;">ESTADO</th>
            </tr>
        </thead>
        <tbody>`;

    metasGlobales.forEach(item => {
        const sku = item.sku;
        const meta = parseInt(item.meta);
        const actual = conteoActual[sku] || 0;
        
        // Lógica del Estado (Ticket o X)
        let icono = '';
        let claseFila = ''; // Para colorear la fila suavemente si quieres

        //  CASO 1: SE PASÓ DE LA CANTIDAD (NUEVO)
        if (actual > meta) {
            const sobra = actual - meta;
            // Rojo fuerte y fondo rojizo para alertar
            icono = `<span style="color: #dc3545; font-weight:bold; font-size: 14px;">⚠️ +${sobra} Excedido</span>`;
            claseFila = 'background-color: #fff0f0; border-left: 4px solid #dc3545;'; 
        } 
        // CASO 2: ES EXACTAMENTE LA CANTIDAD (LISTO)
        else if (actual === meta) {
            // Verde (Solo si es exacto)
            icono = '<span style="color: #28a745; font-size: 18px;">✔ Listo</span>';
            claseFila = 'background-color: #e6fffa;'; 
        } 
        // CASO 3: FALTA CANTIDAD
        else if (actual > 0) {
            // Naranja
            icono = '<span style="color: #f39c12; font-size: 18px;">⏳ Falta</span>';
        } 
        // CASO 4: NO HA EMPEZADO
        else {
            // Rojo suave / Pendiente
            icono = '<span style="color: #e74c3c; font-size: 18px;">X Pendiente</span>';
        }

        html += `
            <tr style="${claseFila}">
                <td style="font-weight: bold; font-size: 12px;">${sku}</td>
                <td style="font-size: 12px;">${item.nombre}</td>
                <td style="text-align: center; font-weight: bold;">${meta}</td>
                <td style="text-align: center; font-weight: bold; color: #444;">${actual}</td>
                <td style="text-align: center;">${icono}</td>
            </tr>`;
    });

    html += `</tbody></table>`;
    contenedor.innerHTML = html;

    // --- Lógica de visualización del panel ---
    if (metasGlobales.length > 0) {
        if (panel) {
            panel.classList.remove('panel-resumen-oculto');
            panel.style.display = 'block';
        }
        document.body.classList.add('con-panel-activo');
    } else {
        if (panel) {
            panel.classList.add('panel-resumen-oculto');
            panel.style.display = 'none';
        }
        document.body.classList.remove('con-panel-activo'); 
    }
}

// Buscar esta función en tu archivo y reemplazarla con esta versión:

function cargarPanelMetas(rut, factura) {
    const panel = document.getElementById('panel-resumen');
    const contenedorPrincipal = document.querySelector('.contenedor-principal');
    
    // Usamos peticionIframe 
    peticionIframe(`/php/ingreso_ventas/registro_ventas/obtener_metas.php?rut=${encodeURIComponent(rut)}&factura=${encodeURIComponent(factura)}`)
    .then(res => res.text())
    .then(texto => {
        console.log("Metas recibidas:", texto);
        metasGlobales = []; // Limpiar lista anterior

        if (texto && texto.trim() !== "") {
            // Separamos por || (cada producto)
            const items = texto.split('||');
            
            items.forEach(itemStr => {
                // Usamos tu funcion stringToObject para convertir "clave:valor"
                const obj = stringToObject(itemStr); 
                if (obj.sku) {
                    metasGlobales.push({
                        sku: obj.sku,
                        nombre: obj.nombre || 'Producto',
                        meta: parseInt(obj.meta) || 0
                    });
                }
            });
            
            // Mostrar panel y dibujar
            if(panel) {
                panel.classList.remove('panel-resumen-oculto');
                panel.style.display = 'block';
                
                // *** AGREGAR CLASE PARA MOVER CONTENIDO (solo en desktop) ***
                if (contenedorPrincipal && window.innerWidth > 1024) {
                    contenedorPrincipal.classList.add('panel-visible');
                }
                
                actualizarVisualesPanel();
            }
        } else {
            // Si no hay datos, ocultar
            if(panel) {
                panel.classList.add('panel-resumen-oculto');
                panel.style.display = 'none';
                
                
                if (contenedorPrincipal) {
                    contenedorPrincipal.classList.remove('panel-visible');
                }
            }
        }
    })
    .catch(e => console.error("Error cargando metas:", e));
}


// funcion de limpieza para cuando se borre una factura en ingreso ventas vuelva a la normalidad
function resetearInterfazCompleta() {
    // escribe un mensaje interno osea en la consola para avisar que empezo la limpieza
    console.log("Ejecutando limpieza forzada de interfaz...");
    
    // revisa si existe la lista de metas y la vacia es decir que limpia variables globales 
    if (typeof metasGlobales !== 'undefined') {
        // borra todos los datos de la lista de metas
        metasGlobales = [];
    }
    
    // quitar la clase del body que mueve el contenido osea le quita al cuerpo de la pagina la etiqueta que movia todo a la derecha
    document.body.classList.remove('con-panel-activo');
    
    // limpiamos el estilo inline si existiera, para no bloquear el css futuro y busca el contenedor principal de la pagina
    const contenedorPrincipal = document.querySelector('.contenedor-principal');
    // si encuentra el contenedor, hace lo siguiente
    if (contenedorPrincipal) {
        // le quita la etiqueta que lo hacia visible junto al panel
        contenedorPrincipal.classList.remove('panel-visible');
        // borra cualquier margen manual a la izquierda para que no moleste
        contenedorPrincipal.style.marginLeft = '';
    }
    
    // busca el cuadro o panel de resumen en la pagina para ocultar el panel
    const panel = document.getElementById('panel-resumen');
    // si el panel existe, entra aqui
    if(panel) {
        // oculta el panel para que no se vea en la pantalla
        panel.style.display = 'none'; 
        // le pone una etiqueta extra para asegurar que se mantenga oculto
        panel.classList.add('panel-resumen-oculto');
    }
    
    // busca la parte de la tabla donde van los productos para limpiar tabla de productos
    const tablaBody = document.getElementById('tablaProductos');
    // si la encuentra borra todo lo que tenga escrito adentro
    if(tablaBody) tablaBody.innerHTML = "";
    // busca la cabecera de la tabla que son los titulos de las columnas
    const tablaHeader = document.getElementById('tablaProductosHeader');
    // si la encuentra, la oculta para que no se vea sola sin datos
    if(tablaHeader) tablaHeader.style.display = 'none';
    // busca el boton de guardar
    const botonGuardar = document.getElementById('boton');
    // si lo encuentra, lo oculta porque no hay nada que guardar
    if(botonGuardar) botonGuardar.style.display = 'none';
    
    // busca la casilla donde se escribe el numero de factura
    const facturaInput = document.getElementById('numero_factu');
    // si la encuentra, le quita cualquier color de fondo (como rojo o verde)
    if(facturaInput) facturaInput.style.backgroundColor = ""; 
    // revisa si existe la variable que dice si la factura es valida
    if (typeof facturaValidada !== 'undefined') {
        // la marca como falsa para empezar de cero
        facturaValidada = false;
    }
    
    // revisa si existe el diccionario de productos
    if (typeof skuDiccionario !== 'undefined') {
        // recorre cada producto que estaba guardado en el diccionario
        for (let key in skuDiccionario) {
            // borra el producto del diccionario para que no se repita
            delete skuDiccionario[key];
        }
    }
    // escribe un mensaje final confirmando que ya termino de limpiar todo en la consola la que se ve dando inspeccionar o F12
    console.log("Limpieza completa.");
}

// TITULO ARCHIVO JS

    // SIN FUNCION


async function validarCantidadFactura(rut, numeroFactura, sku, cantidadIngresada, nombreIngresado = null) {
  try {
    // 1. Consultamos al servidor los datos reales
    const res = await peticionIframe(`/php/ingreso_ventas/registro_ventas/validar_factura.php?rut=${encodeURIComponent(rut)}&factura=${encodeURIComponent(numeroFactura)}&sku=${encodeURIComponent(sku)}`);
    const textoRespuesta = await res.text();
    const respuestaLimpia = textoRespuesta.trim();

    // Validacion: si el sku no existe en la factura
    if (respuestaLimpia.startsWith("ERROR") || respuestaLimpia === "" || respuestaLimpia === "NO_EXISTE") {
        alert(`ERROR DE PRODUCTO:\nEl código SKU ${sku} no aparece en la factura Nº ${numeroFactura}.\nRevise si es el producto correcto.`);
        return false;
    }

    // Separamos la respuesta "CANTIDAD|NOMBRE"
    const partes = respuestaLimpia.split('|');
    const cantidadEsperada = parseInt(partes[0], 10); 
    const nombreEnFactura = partes[1] || ""; 

    if (isNaN(cantidadEsperada)) {
        console.error("Error de lectura:", respuestaLimpia);
        return false;
    }

    // Validacion de nombre (si aplica)
    if (nombreIngresado) {
        const nombreReal = String(nombreEnFactura).trim().toLowerCase();
        const nombreUsuario = String(nombreIngresado).trim().toLowerCase();
        if (nombreReal.length > 0 && nombreReal !== nombreUsuario) {
             alert(`NOMBRE INCORRECTO DETECTADO\n\nEl SKU ${sku} pertenece a: "${nombreEnFactura}"\n\nUsted ingresó: "${nombreIngresado}"`);
             return false; 
        }
    }

    // === AQUÍ ESTÁ EL CAMBIO QUE PEDISTE ===

    // CASO 1: Si es MAYOR a la factura -> BLOQUEAR (Return FALSE)
    if (cantidadIngresada > cantidadEsperada) {
         alert(`⛔ ERROR: EXCESO DE CANTIDAD\n\nProducto: ${sku}\nTotal en Factura: ${cantidadEsperada}\nTotal Ingresado: ${cantidadIngresada}\n\nNo puedes guardar más productos de los que existen en la factura.`);
         return false; 
    } 
    
    // CASO 2: Si es MENOR a la factura -> PERMITIR (Return TRUE)
    // Esto es para que puedas ir ingresando de a poco.
    else if (cantidadIngresada < cantidadEsperada) {
         console.log(`Aviso: Guardando cantidad parcial (${cantidadIngresada}/${cantidadEsperada}) para ${sku}.`);
         return true; 
    }

    // CASO 3: Si es IGUAL -> PERMITIR (Return TRUE)
    return true;

  } catch (e) {
    console.error('Error en validacion:', e);
    return false;
  }
}
function recibirDatosDesdeNormalizar() {
    try {
        var params = new URLSearchParams(window.location.search);
        
        console.log('recibirDatosDesdeNormalizar - Todos los params:', Array.from(params.entries()));
        
        // Verificar si viene desde normalizar
        if (params.get('desde_normalizar') !== '1') {
            console.log('No viene desde normalizar');
            return false;
        }
        
        console.log('SÍ viene desde normalizar');
        
        // Recuperar datos del cliente/factura
        var rutCliente = params.get('rut_cliente') || '';
        var numFactura = params.get('num_factura') || '';
        var fechaDespacho = params.get('fecha_despacho') || '';
        
        console.log('Datos recibidos:', {
            rut: rutCliente,
            factura: numFactura,
            fecha: fechaDespacho
        });
        
        // Rellenar campos del formulario - ESPERAR a que existan
        setTimeout(function() {
            var rutInput = document.getElementById('rut');
            var nombreInput = document.getElementById('nombre');
            var numeroFactInput = document.getElementById('numero_factu');
            var fechaActualInput = document.getElementById('fecha_actual');
            
            console.log('Elementos encontrados:', {
                rut: !!rutInput,
                nombre: !!nombreInput,
                factura: !!numeroFactInput,
                fecha: !!fechaActualInput
            });
            
            if (rutInput && rutCliente) {
                rutInput.value = rutCliente;
                console.log('RUT asignado:', rutCliente);
                
                // Disparar blur para buscar nombre
                setTimeout(function() { 
                    rutInput.dispatchEvent(new Event('blur', { bubbles: true }));
                    console.log('Evento blur disparado en RUT');
                }, 200);
            }

            if (numeroFactInput && numFactura) {
                numeroFactInput.value = numFactura;
                console.log('Número de factura asignado:', numFactura);
            }
            
            if (fechaActualInput && fechaDespacho) {
                fechaActualInput.value = fechaDespacho;
                console.log('Fecha asignada:', fechaDespacho);
            }

            if (rutCliente && numFactura) {
                console.log("Cargando panel de estado restaurado...");
                cargarPanelMetas(rutCliente, numFactura);
            }
            
        }, 300); // Esperar 300ms para que el DOM esté listo
        
        // Restaurar productos previos primero
        var productosPreviosBase64 = params.get('productos_previos');
        if (productosPreviosBase64) {
            try {
                var productosString = decodeURIComponent(atob(productosPreviosBase64));
                var productosArray = productosString.split('||');
                
                console.log('Restaurando ' + productosArray.length + ' productos previos');
                
                // Mostrar tabla
                var header = document.getElementById('tablaProductosHeader');
                var boton = document.getElementById('boton');
                if (header) header.style.display = 'table';
                if (boton) boton.style.display = 'flex';
                
                // Agregar cada producto previo
                productosArray.forEach(function(productoStr) {
                    if (!productoStr || productoStr.trim() === '') return;
                    
                    var campos = {};
                    var pares = productoStr.split('|');
                    
                    pares.forEach(function(par) {
                        var separador = par.indexOf(':');
                        if (separador > 0) {
                            var clave = par.substring(0, separador);
                            var valor = par.substring(separador + 1);
                            campos[clave] = valor;
                        }
                    });
                    
                    var datosParaTabla = [
                        campos.sku || '',
                        campos.producto || '',
                        campos.cantidad || '',
                        campos.lote || '',
                        campos.fechaFab || '',
                        campos.fechaVenc || '',
                        campos.serieIni || '00000000',
                        campos.serieFin || '00000000'
                    ];
                    
                    agregarFilaProducto(datosParaTabla, 'RESTAURADO', false);
                });
                
            } catch (e) {
                console.error('Error restaurando productos previos:', e);
            }
        }
        
        // Leer datos del producto normalizado
        var datosBase64 = params.get('datos_normalizados');
        if (datosBase64) {
            try {
                var cadenaManual = decodeURIComponent(atob(datosBase64));
                var datosNormalizados = stringToObject(cadenaManual);
                
                console.log('Datos normalizados recibidos:', datosNormalizados);
                
                setTimeout(function() {
                    agregarProductoDesdeNormalizar(datosNormalizados);
                }, 500);
                
            } catch (e) {
                console.error('Error procesando datos normalizados:', e);
            }
        }
        
        // Limpiar URL
        var urlLimpia = window.location.pathname;
        if (window.location.search.includes('pagina=')) {
            var p = params.get('pagina');
            if (p) urlLimpia += '?pagina=' + p;
        }
        window.history.replaceState({}, document.title, urlLimpia);
        
        mostrar_mensaje_feedback('Datos restaurados correctamente.', 'exitoso');
        return true;
        
    } catch (e) {
        console.error('Error general recibiendo datos:', e);
        return false;
    }
}

async function agregarProductoDesdeNormalizar(datos) {
    try {
        // Mostrar la tabla y el botón guardar
        var header = document.getElementById('tablaProductosHeader');
        var boton = document.getElementById('boton');
        if (header) header.style.display = 'table';
        if (boton) boton.style.display = 'flex';
        
        // Preparar datos para la tabla
        var datosParaTabla = [
            datos.sku || '',
            datos.producto || '',
            datos.cantidad || '',
            datos.lote || '',
            datos.fechaFab || '',
            datos.fechaVenc || '',
            datos.serieIni || '00000000',
            datos.serieFin || '00000000'
        ];
        
        // Verificar SKU duplicado
        var skuKey = (datos.sku || '').trim();
        if (skuKey && skuDiccionario[skuKey]) {
            if (!confirm('Este SKU ya está en la tabla. ¿Agregar de todas formas?')) {
                return;
            }
        } else if (skuKey) {
            skuDiccionario[skuKey] = true;
        }
        
        // Agregar fila a la tabla usando la función existente
        agregarFilaProducto(datosParaTabla, 'DESDE_NORMALIZAR', false);
        
        console.log('Producto agregado desde normalizar:', datosParaTabla);
        
        // Scroll hacia la tabla
        var tabla = document.getElementById('tablaProductos');
        if (tabla) {
            tabla.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        // --- NUEVO: VALIDACIÓN AUTOMÁTICA INMEDIATA ---
        // Tomamos los datos del formulario que se acaban de rellenar
        var rut = document.getElementById('rut').value.replace(/\./g, '').trim();
        var factura = document.getElementById('numero_factu').value.trim();
        var cantidad = parseInt(datos.cantidad) || 0;
        var nombreProd = datos.producto || '';

        if (rut && factura && skuKey) {
            console.log("Validando automáticamente producto normalizado...");
            // Llamamos a la función que consulta a la BD y lanza las alertas
            // No necesitamos hacer nada con el true/false aquí, porque la función ya tiene los alerts dentro.
            await validarCantidadFactura(rut, factura, skuKey, cantidad, nombreProd);
        }

    } catch (e) {
        console.error('Error agregando producto desde normalizar:', e);
        mostrar_mensaje_feedback('Error al agregar el producto normalizado', 'error');
    }
}

//  Función auxiliar de fechas (identica a normalizar_qr.js)
function validarYFormatearFechaHelper(fechaStr) {
    // si no mandaron ninguna fecha no hacemos nada y salimos
    if (fechaStr == null) return null;
    // convertimos lo que llego a texto y le quitamos los espacios de las orillas 
    var original = String(fechaStr).trim();
    // si despues de limpiar quedo vacio nos salimos
    if (!original) return null;
    // quitamos espacios raros que a veces traen los textos copiados 
    var s = original.replace(/\u00A0/g, ' ').trim();
    // se cambian a puntos, guiones o espacios por una barra inclinada para que sea uniforme
    s = s.replace(/[._\s\\\-\u2013]+/g, '/').replace(/\/+/g, '/').replace(/\s+de\s+/gi, '/');
    // esto es una herramienta pequeña para verificar si los numeros forman una fecha real 
    var isValid = function(y, m, d) {
        // convertimos el año, mes y dia a numeros 
        y = Number(y); m = Number(m); d = Number(d);
        // revisamos si son numeros de verdad
        if (!Number.isFinite(y) || !Number.isFinite(m) || !Number.isFinite(d)) return false;
        // se revisa que el año sea logico
        if (y < 1000 || y > 9999) return false;
        // se crea una fecha en el sistema para ver si existe
        var dt = new Date(y, m - 1, d);
        // para confirmar que sistema haya entendido la misma fecha que se le dio
        return dt.getFullYear() === y && (dt.getMonth() + 1) === m && dt.getDate() === d;
    };
    
    // Validaciones rápidas
    var m;
    // para ver si la fecha viene como dia/mes/año, DD/MM/YYYY
    m = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (m && isValid(m[3], m[2], m[1])) return true;
    // YYYY-MM-DD
    m = s.match(/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/);
    if (m && isValid(m[1], m[2], m[3])) return true;
    
    return false;
}

// calcular tipo de dato (Sincronizado con normalizar)
function calcularTipoDato(texto) {
    if (texto == null) return 'O';
    const t = String(texto).trim();
    if (!t) return 'O';
    
    // N: Número
    if (/^\d+$/.test(t)) return 'N';
    
    // D: Fecha (Usamos validarYFormatearFecha para ser consistentes con normalizar)
    if (typeof validarYFormatearFecha === 'function') {
        if (validarYFormatearFecha(t)) return 'D';
    } else {
        // Fallback si no existe la función (aunque debería)
        if (t.includes('/') || t.includes('-') || (t.startsWith('20') && t.length===10)) return 'D'; 
    }
    
    // A: Texto (Letras)
    if (/[A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(t)) return 'A';
    
    // O: Otro
    return 'O';
}

// Separar datos (Sincronizado)
function separarDatosQR(raw) {
    if (!raw) return [];
    // Mismo regex que normalizar: comas, pipes, tabs, saltos de linea
    return String(raw).split(/[,|\t\n]+/).map(s => s.trim()).filter(Boolean);
}

// Crear Firma (La Huella Digital)
function crearFirmaQR(rawString) {
    // Usamos separarDatosQR (crudo), NO filtros (limpio)
    const tokens = separarDatosQR(rawString); 
    const tipos = tokens.map(calcularTipoDato);
    const firma = tokens.length + '|' + tipos.join('');
    console.log("Firma generada en Ventas (Raw):", firma);
    return firma;
}

// Convertir respuesta del servidor a objeto
function mapaAObjeto(mapaStr) {
    var resultado = {};
    if(!mapaStr || mapaStr === 'NULL') return resultado;
    
    // Limpieza: quitar comillas o corchetes si vienen de un stringify
    mapaStr = mapaStr.replace(/'/g, "").replace(/"/g, "").replace(/^\[|\]$/g, "");
    
    // Separamos por  (|) o coma (,)
    var items = mapaStr.split('|'); 
    if (items.length <= 1 && mapaStr.includes(',')) items = mapaStr.split(',');

    // Detectamos si viene con el formato "0:sku" o solo "sku"
    var tieneDosPuntos = items.some(function(it) { return it.includes(':'); });

    items.forEach(function(item, indexReal) {
        var campo = '';
        var indiceToken = indexReal;

        if (tieneDosPuntos) {
            // Caso A: Formato "0:sku"
            var partes = item.split(':');
            if (partes.length === 2) {
                indiceToken = parseInt(partes[0].trim());
                campo = partes[1].trim();
            }
        } else {
            // Caso B: Formato directo "sku"
            // La posición en la lista ES el índice del token
            campo = item.trim();
        }

        // Guardamos la instrucción: "Para llenar 'campo', usa el token número 'indiceToken'"
        if (campo && campo !== 'null' && campo !== 'undefined' && !campo.startsWith('_unmapped')) {
            resultado[campo] = indiceToken;
        }
    });
    
    console.log("Mapa interpretado:", resultado);
    return resultado;
}


// Función para calcular y alertar excesos en tiempo real
function verificarExcesoVisual(skuNuevo, cantidadNueva) {
    // 1. Buscamos cuál es la meta para este SKU en la lista que ya cargamos
    // (metasGlobales se llena cuando validas la factura al principio)
    if (typeof metasGlobales === 'undefined' || metasGlobales.length === 0) return;

    const metaData = metasGlobales.find(m => m.sku === skuNuevo);
    
    // Si el producto no está en la lista de metas, no hacemos nada (o ya saltará otra alerta)
    if (!metaData) return;

    const cantidadMeta = parseInt(metaData.meta) || 0;
    const cantNueva = parseInt(cantidadNueva) || 0;

    // 2. Sumamos lo que YA tenemos en la tabla visualmente para este SKU
    let cantidadEnTabla = 0;
    const filas = document.querySelectorAll('#tablaProductos tr');
    
    filas.forEach(f => {
        const c = f.querySelectorAll('td');
        if (c.length > 2) {
            const skuFila = c[0].textContent.trim();
            // Si el SKU coincide, sumamos su cantidad
            if (skuFila === skuNuevo) {
                cantidadEnTabla += parseInt(c[2].textContent.replace(/\./g, '')) || 0;
            }
        }
    });

    // 3. Calculamos el Total Hipotético (Lo que había + Lo que estás metiendo)
    const totalAcumulado = cantidadEnTabla + cantNueva;

    // 4. Si el total supera la meta, lanzamos la alerta matemática
    if (totalAcumulado > cantidadMeta) {
        const diferencia = totalAcumulado - cantidadMeta;
        
        alert(`⚠️ ALERTA DE EXCESO\n\n` +
              `Producto: ${skuNuevo}\n` +
              `Meta Factura: ${cantidadMeta}\n` +
              `Ya ingresados: ${cantidadEnTabla}\n` +
              `Intentando ingresar: ${cantNueva}\n\n` +
              `⛔ Te estás pasando por: ${diferencia} unidad(es).`);
    }
}

/* --------------------------------------------------------------------------------------------------------------
    ---------------------------------------- FIN ITred Spa ventas .JS --------------------------------------------
    -------------------------------------------------------------------------------------------------------------- */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
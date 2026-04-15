// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa buscar .JS ---------------------------------------------
    --------------------------------------------------------------------------------------------------------------- */

// TITULO HTML

// SIN CODIGO



// TITULO BODY

// SIN CODIGO



// TITULO RECUADROS Y CUADRO DE BUSQUEDA
// Normalización de rol proveniente de PHP
const ROL = (window.usuarioRol || '').toString().trim().toLowerCase();
const ES_USUARIO_FINAL = (ROL === 'usuario_final');
const ES_DISTRIBUIDOR = (ROL === 'distribuidor');
const PUEDE_EDITAR = (ROL === 'admin' || ROL === 'superadmin');



let latestRequestId = 0;

    function mostrar_tabla_de_resultados() {
        const cont = document.getElementById('contenedorTabla');
        if (!cont) return;
        cont.classList.add('loading');
        let loader = document.getElementById('tabla-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'tabla-loader';
            loader.textContent = 'Cargando resultados...';
            loader.style.padding = '10px';
            loader.style.textAlign = 'center';
            loader.style.color = '#444';
            cont.insertBefore(loader, cont.firstChild);
        }
        const tabla = cont.querySelector('table');
        if (tabla) tabla.style.opacity = '0.4';
        cont.style.display = 'block';
    }

    function ocultar_tabla_de_resultados() {
        const cont = document.getElementById('contenedorTabla');
        if (!cont) return;
        const loader = document.getElementById('tabla-loader');
        if (loader) loader.remove();
        const tabla = cont.querySelector('table');
        if (tabla) tabla.style.opacity = '1';
        cont.classList.remove('loading');
    }





// Evento que se ejecuta cuando el DOM está completamente cargado
document.addEventListener('DOMContentLoaded', function () {
    const buscarSkuInput = document.getElementById('buscar_sku');
    const buscarSerialInput = document.getElementById('buscar_serial');
    const buscarLoteInput = document.getElementById('buscar_lote');
    const buscarRutInput = document.getElementById('buscar_rut');
    const buscarClienteInput = document.getElementById('buscar_cliente');
    const buscarFactInput = document.getElementById('buscar_factura');
    const fechaDesdeInput = document.getElementById('fecha_desde');
    const fechaHastaInput = document.getElementById('fecha_hasta');

    let filtroSku = '';
    let filtroSerial = '';
    let filtroLote = '';
    let filtroRut = '';
    let filtroNombre = '';
    let filtroFact = '';
    let filtroFechaDesde = '';
    let filtroFechaHasta = '';


    if (buscarSkuInput) {
        buscarSkuInput.addEventListener('input', function () {
        let sku = buscarSkuInput.value.replace(/[^a-zA-Z0-9]/g, '');
        if (sku.length > 20) sku = sku.slice(0, 20);
        buscarSkuInput.value = sku;
        filtroSku = sku.trim();
        // Cuando se cambia el SKU, pedimos al servidor el rango de series para este SKU
        solicitar_rango_series(filtroSku);
        });
    }
    if (buscarSerialInput) {
        buscarSerialInput.addEventListener('input', function () {
            let serial = buscarSerialInput.value.replace(/[^0-9]/g, '');
            if (serial.length > 8) serial = serial.slice(0, 8);
            buscarSerialInput.value = serial;
            filtroSerial = buscarSerialInput.value.trim();
            // validar en cliente si ya tenemos rango
            validar_series_rango(filtroSerial);

        });
    }

    if (buscarLoteInput) {
        buscarLoteInput.addEventListener('input', function () {
            let lote = buscarLoteInput.value.replace(/[^0-9]/g, '');
            if (lote.length > 8) lote = lote.slice(0, 8);
            buscarLoteInput.value = lote;
            filtroLote = buscarLoteInput.value.trim();

        });
    }

    if (buscarRutInput) {
        //  formatea un RUT (ej: 12345678k -> 12.345.678-K)
        function formato_vista_rut(raw) {
            if (!raw) return '';
            // eliminar todo lo que no sea dígito o K/k
            let clean = raw.toString().replace(/[^0-9kK]/g, '').toUpperCase();
            // limitar largo máximo (hasta 10 como regla existente)
            if (clean.length > 10) clean = clean.slice(0, 10);
            if (clean.length <= 1) return clean;
            // separar verificador (último char)
            const ver = clean.slice(-1);
            let num = clean.slice(0, -1);
            // aplicar puntos cada 3 desde la derecha
            num = num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return num + '-' + ver;
        }

        buscarRutInput.addEventListener('input', function (e) {
            const el = this;
            // posición del cursor antes de formatear
            const prevVal = el.value;
            const prevPos = el.selectionStart;

            // contar cuántos caracteres válidos había antes del cursor
            const rawBeforeCursor = prevVal.slice(0, prevPos).replace(/[^0-9kK]/g, '').toUpperCase();

            // obtener nuevo valor formateado
            const newVal = formato_vista_rut(prevVal);

            el.value = newVal;

            // restaurar posición del cursor intentando mantener la misma cantidad de caracteres "válidos" antes
            let count = 0;
            let newPos = newVal.length;
            for (let i = 0; i < newVal.length; i++) {
                const ch = newVal.charAt(i);
                if (/\d|K/.test(ch)) count++;
                if (count >= rawBeforeCursor.length) {
                    // poner cursor justo después de este índice
                    newPos = i + 1;
                    break;
                }
            }
            // si rawBeforeCursor está vacío, colocar al inicio
            if (rawBeforeCursor.length === 0) newPos = 0;
            try { el.setSelectionRange(newPos, newPos); } catch (err) { }

            // actualizar filtro con la versión normalizada (sin puntos ni guion)
            filtroRut = normalizar_rut(el.value);
        });
    }

    if (buscarClienteInput) {
        buscarClienteInput.addEventListener('input', function () {
            let nombre = buscarClienteInput.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            buscarClienteInput.value = nombre;
            filtroNombre = buscarClienteInput.value.trim();

            // Validación activa: si hay texto y es menor a 3 letras (sin espacios), marcar como inválido
            const nombreSinEspacios = filtroNombre.replace(/\s+/g, '');
            if (filtroNombre && nombreSinEspacios.length > 0 && nombreSinEspacios.length < 3) {
                buscarClienteInput.setCustomValidity('El nombre debe tener al menos 3 letras.');
                buscarClienteInput.reportValidity();
            } else {
                buscarClienteInput.setCustomValidity('');
            }



        });
    }

    if (buscarFactInput) {
        buscarFactInput.addEventListener('input', function () {
            let factura = buscarFactInput.value.replace(/[^0-9]/g, '');
            if (factura.length > 20) factura = factura.slice(0, 20);
            buscarFactInput.value = factura;
            filtroFact = buscarFactInput.value.trim();

        });
    }

    if (fechaDesdeInput) {
        fechaDesdeInput.addEventListener('change', function () {
            filtroFechaDesde = fechaDesdeInput.value;

        });
    }

    if (fechaHastaInput) {
        fechaHastaInput.addEventListener('change', function () {
            filtroFechaHasta = fechaHastaInput.value;

        });
    }


        const contInicial = document.getElementById('contenedorTabla');
    const tablaVentasInicial = document.getElementById('tablaVentas');
    if (contInicial) contInicial.style.display = 'none';
    if (tablaVentasInicial) tablaVentasInicial.innerHTML = '';
    // inicializar valores ocultos (por si el usuario escribió antes de que el listener se adjunte)
    // (no inicializaciones adicionales para hidden fields; valores se copian justo antes del submit)
    const btnBuscar = document.getElementById('btnBuscar');
    if (btnBuscar) {
        btnBuscar.addEventListener('click', function () {
            // Bloquear si no hay filtros
            if (!window.tieneFiltrosActivos()) {
                alert(ES_USUARIO_FINAL
                    ? 'Para buscar, ingresa SKU y Número de Serie.'
                    : 'Ingresa al menos un filtro antes de buscar.');
                // Mantener la tabla oculta si estaba oculta
                const cont = document.getElementById('contenedorTabla');
                if (cont) cont.style.display = 'none';
                // Limpiar cuerpo de tabla por si quedó algo mostrado
                const tb = document.getElementById('tablaVentas');
                if (tb) tb.innerHTML = '';
                return;
            }

            // Relee filtros y ejecuta búsqueda
            const sku = document.getElementById('buscar_sku')?.value.trim() || '';
            const lote = document.getElementById('buscar_lote')?.value.trim() || '';
            const rut = document.getElementById('buscar_rut')?.value.trim() || '';
            const nombre = document.getElementById('buscar_cliente')?.value.trim() || '';
            const factura = document.getElementById('buscar_factura')?.value.trim() || '';
            const fdesde = document.getElementById('fecha_desde')?.value || '';
            const fhasta = document.getElementById('fecha_hasta')?.value || '';
            const serial = document.getElementById('buscar_serial')?.value.trim() || '';

            // Validación del nombre: mínimo 3 letras (sin contar espacios) si se ingresó
            const nombreSinEspaciosCheck = nombre.replace(/\s+/g, '');
            if (nombre && nombreSinEspaciosCheck.length > 0 && nombreSinEspaciosCheck.length < 3) {
                alert('El nombre debe tener al menos 3 letras para buscar.');
                return;
            }

            // Validación final: si es usuario_final, asegúrate de que SKU y serial estén presentes y el serial esté en rango
            if (ES_USUARIO_FINAL) {
                if (!sku || !serial) {
                    alert('Usuario final: debe ingresar SKU y N° de serie.');
                    return;
                }
                // si tenemos rango conocido, validar antes de enviar
                if (window._rangoSeries && window._rangoSeries[sku]) {
                    const r = window._rangoSeries[sku];
                    const sVal = parseInt(serial, 10);
                    if (isNaN(sVal) || sVal < r.min || sVal > r.max) {
                        alert(`La serie ${serial} está fuera del rango permitido (${r.min} - ${r.max}).`);
                        return;
                    }
                }
            }

            cargar_ventas(sku, lote, rut, nombre, factura, fdesde, fhasta, serial);
            console.log('Llamado cargar_ventas con:', { sku, lote, rut, nombre, factura, fdesde, fhasta, serial }); // DEBUG

            const cont = document.getElementById('contenedorTabla');
            if (cont && cont.style.display === 'none') cont.style.display = 'block';
        });
    }
    // Exponer globalmente para que otros handlers (form submit, imprimir) puedan usarla
    window.tieneFiltrosActivos = function () {
        const sku = document.getElementById('buscar_sku')?.value.trim() || '';
        const lote = document.getElementById('buscar_lote')?.value.trim() || '';
        const rut = document.getElementById('buscar_rut')?.value.trim() || '';
        const nombre = document.getElementById('buscar_cliente')?.value.trim() || '';
        const factura = document.getElementById('buscar_factura')?.value.trim() || '';
        const fdesde = document.getElementById('fecha_desde')?.value || '';
        const fhasta = document.getElementById('fecha_hasta')?.value || '';
        const serial = document.getElementById('buscar_serial')?.value.trim() || '';

        // Regla: usuario_final debe buscar con SKU + Serial. Otros, basta con cualquier filtro.
        if (ES_USUARIO_FINAL) {
            return (sku !== '' && serial !== '');
        }

        // Para distribuidor: verificar si hay filtros pero sin restricción aquí
        // La validación del RUT se hará en la función cargar_ventas
        if (ES_DISTRIBUIDOR) {
            // Nombre cuenta como filtro sólo si tiene al menos 3 letras (sin espacios)
            const nombreSinEspacios = nombre.replace(/\s+/g, '');
            const nombreActivo = (nombreSinEspacios.length >= 3);
            return [sku, lote, rut, factura, fdesde, fhasta].some(v => v !== '') || nombreActivo;
        }

        // Nombre cuenta como filtro sólo si tiene al menos 3 letras (sin espacios)
        const nombreSinEspacios = nombre.replace(/\s+/g, '');
        const nombreActivo = (nombreSinEspacios.length >= 3);
        return [sku, lote, rut, factura, fdesde, fhasta].some(v => v !== '') || nombreActivo;
    }

    // iconos de borrar
    document.querySelectorAll(".icono-borrar").forEach(function (icono) {
        icono.addEventListener("click", function () {
            let input = this.closest(".recuadro, .recuadro_grande, .input-fecha, div")?.querySelector("input");
            if (input) {
                input.value = "";
                if (input.id === "fecha_desde") filtroFechaDesde = "";
                if (input.id === "fecha_hasta") filtroFechaHasta = "";
                if (input.id === "buscar_serial") filtroSerial = "";
            }
            filtroSku = buscarSkuInput?.value.trim() || "";
            filtroLote = buscarLoteInput?.value.trim() || "";
            filtroRut = buscarRutInput?.value.trim() || "";
            filtroFact = buscarFactInput?.value.trim() || "";
            filtroNombre = buscarClienteInput?.value.trim() || "";

        });
    });

    // Cache simple de rangos por SKU
    window._rangoSeries = window._rangoSeries || {};

    // Solicita al servidor el rango de series para un SKU y lo guarda en cache
    function solicitar_rango_series(sku) {
        if (!sku) return;
        if (window._rangoSeries[sku]) return;
        fetch(`/php/ingreso_ventas/consultar_productos/obtener_rango_series.php?sku=${encodeURIComponent(sku)}`)
            .then(r => r.text())
            .then(texto => {
                // Espera formato: "success|min|max" o "error|mensaje"
                const partes = texto.trim().split('|');
                if (partes[0] === 'success' && partes.length >= 3) {
                    const min = parseInt(partes[1], 10);
                    const max = parseInt(partes[2], 10);
                    if (!isNaN(min) && !isNaN(max)) {
                        window._rangoSeries[sku] = { min: Math.min(min, max), max: Math.max(min, max) };
                    }
                }
            })
            .catch(err => {
                console.warn('No se pudo obtener rango de series para SKU', sku, err);
            });
    }

    // Valida que el serial esté en el rango conocido para el SKU actual. Muestra alerta si está fuera.
    function validar_series_rango(serial) {
        if (!serial) return true;
        const sku = document.getElementById('buscar_sku')?.value.trim() || '';
        if (!sku) return true; // sin SKU no validamos
        const cache = window._rangoSeries[sku];
        if (!cache) return true; // aún no sabemos el rango
        const sVal = parseInt(serial, 10);
        if (isNaN(sVal)) return false;
        if (sVal < cache.min || sVal > cache.max) {
            // muestra alerta pero no borra automáticamente; evita que el usuario continúe
            // si desea, puedes limpiar el input: document.getElementById('buscar_serial').value = '';
            // Mostrar mensaje amigable
            console.warn(`Serie ${serial} fuera de rango ${cache.min}-${cache.max}`);
            return false;
        }
        return true;
    }

    // Para distribuidores, no hacer carga inicial automática
    // Solo recargar si no es distribuidor
    if (!ES_DISTRIBUIDOR) {
        reargar_tabla_actual();
    }

    // Lógica específica para distribuidor
    if (ES_DISTRIBUIDOR) {
        // Obtener RUT del distribuidor desde atributo o variable global
        let rutDistribuidorOriginal = window.rutDistribuidor || buscarRutInput?.getAttribute('data-rut-distribuidor') || '';

        if (rutDistribuidorOriginal) {
            // Normalizar el RUT del distribuidor para comparaciones
            window.rutDistribuidorNormalizado = normalizar_rut(rutDistribuidorOriginal);

            // Guardar el RUT original para uso posterior pero NO precargar el campo
            window.rutDistribuidor = rutDistribuidorOriginal;

            if (buscarRutInput) {
                buscarRutInput.setAttribute('data-rut-distribuidor', rutDistribuidorOriginal);
                buscarRutInput.setAttribute('data-rut-normalizado', window.rutDistribuidorNormalizado);
                buscarRutInput.title = `Debe ingresar su RUT: ${rutDistribuidorOriginal}`;
                // Limpiar campo al cargar para que esté vacío inicialmente
                buscarRutInput.value = '';
            }
        }

        // Prevenir manipulación del campo RUT para distribuidores
        if (buscarRutInput) {
            // Validar RUT al perder el foco (blur) - no bloquear escritura
            buscarRutInput.addEventListener('blur', function () {
                const rutEscrito = normalizar_rut(this.value);
                if (rutEscrito && rutEscrito !== window.rutDistribuidorNormalizado) {
                    // La validación se hará al momento de buscar, no aquí
                }
            });
        }
    }
});




// TITULO GENERAR EXCEL
document.getElementById('formExcel').addEventListener('submit', function (e) {
    // Si no hay filtros, bloquear descarga
    if (!window.tieneFiltrosActivos()) {
        e.preventDefault();
        alert('Para descargar Excel debes aplicar al menos un filtro.');
        return;
    }
    // ANTES de enviar el form, copiamos todos los filtros al hidden correcto
    // Para distribuidores, usar RUT normalizado si está disponible
    const rutInput = document.getElementById('buscar_rut');
    let rutParaEnvio = '';
    if (ES_DISTRIBUIDOR && rutInput) {
        const rutNormalizado = rutInput.getAttribute('data-rut-normalizado');
        rutParaEnvio = rutNormalizado || normalizar_rut(rutInput.value) || rutInput.value.trim();
    } else {
        rutParaEnvio = rutInput ? normalizar_rut(rutInput.value) : '';
    }

    document.getElementById('excel_sku').value = document.getElementById('buscar_sku')?.value.trim() || '';
    document.getElementById('excel_lote').value = document.getElementById('buscar_lote')?.value.trim() || '';
    document.getElementById('excel_rut').value = rutParaEnvio;
    document.getElementById('excel_nombre').value = document.getElementById('buscar_cliente')?.value.trim() || '';
    document.getElementById('excel_factura').value = document.getElementById('buscar_factura')?.value.trim() || '';
    document.getElementById('excel_fecha_desde').value = document.getElementById('fecha_desde')?.value.trim() || '';
    document.getElementById('excel_fecha_hasta').value = document.getElementById('fecha_hasta')?.value.trim() || '';

    // Serial visible --> serial oculto para excel
    document.getElementById('excel_serial').value = document.getElementById('buscar_serial')?.value.trim() || '';
    // no hacemos e.preventDefault() porque queremos que el form se envíe de forma normal (POST)
});




// TITULO GENERAR PDF
const formPDF = document.getElementById('formPDF');

formPDF.addEventListener('submit', function (e) {
    // Si no hay filtros, bloquear descarga
    if (!window.tieneFiltrosActivos()) {
        e.preventDefault();
        alert('Para descargar PDF debes aplicar al menos un filtro.');
        // evitar envío
        return;
    }
    // Para distribuidores, usar RUT normalizado si está disponible
    const rutInput = document.getElementById('buscar_rut');
    let rutParaEnvio = '';
    if (ES_DISTRIBUIDOR && rutInput) {
        const rutNormalizado = rutInput.getAttribute('data-rut-normalizado');
        rutParaEnvio = rutNormalizado || normalizar_rut(rutInput.value) || rutInput.value.trim();
    } else {
        rutParaEnvio = rutInput ? normalizar_rut(rutInput.value) : '';
    }

    document.getElementById('input_sku').value = document.getElementById('buscar_sku')?.value.trim() || "";
    document.getElementById('input_lote').value = document.getElementById('buscar_lote')?.value.trim() || "";
    document.getElementById('input_rut').value = rutParaEnvio;
    document.getElementById('input_cliente').value = document.getElementById('buscar_cliente')?.value.trim() || "";
    document.getElementById('input_numero_fact').value = document.getElementById('buscar_factura')?.value.trim() || "";
    document.getElementById('input_fecha_desde').value = document.getElementById('fecha_desde')?.value || "";
    document.getElementById('input_fecha_hasta').value = document.getElementById('fecha_hasta')?.value || "";
    document.getElementById('input_serial').value = document.getElementById('buscar_serial')?.value.trim() || "";
});



// TITULO IMPRIMIR PDF 

// Obtiene el elemento iframe para imprimir el PDF
// Variable global para evitar ejecuciones múltiples
let imprimiendo = false;

function imprimir_pdf() {
    // Prevenir ejecuciones múltiples
    if (imprimiendo) {
        return;
    }

    // bloquear impresión si no hay filtros
    if (!window.tieneFiltrosActivos()) {
        alert('Para imprimir debes aplicar al menos un filtro.');
        return;
    }

    imprimiendo = true;
    // Usar la técnica mejorada que maneja iOS y PC correctamente
    impprimir_pdf_con_tecnica_qr();
}

// Función que replica exactamente la lógica exitosa de generar_qr.js para iOS
function impprimir_pdf_con_tecnica_qr() {
    // Validar que haya filtros activos
    if (!window.tieneFiltrosActivos()) {
        alert('Para imprimir debes aplicar al menos un filtro.');
        imprimiendo = false; // Resetear bandera
        return;
    }

    const fp = document.getElementById('formPDF');
    if (!fp) {
        alert('Error: No se encontró el formulario en la página.');
        imprimiendo = false; // Resetear bandera
        return;
    }

    // Copiar filtros actuales a campos ocultos (igual que la función original)
    const rutInput = document.getElementById('buscar_rut');
    let rutParaEnvio = '';
    if (ES_DISTRIBUIDOR && rutInput) {
        const rutNormalizado = rutInput.getAttribute('data-rut-normalizado');
        rutParaEnvio = rutNormalizado || normalizar_rut(rutInput.value) || rutInput.value.trim();
    } else {
        rutParaEnvio = rutInput ? normalizar_rut(rutInput.value) : '';
    }

    // IMPORTANTE: Usar los nombres de campos que espera generar_pdf.php
    document.getElementById('input_sku').value = document.getElementById('buscar_sku')?.value || "";
    document.getElementById('input_lote').value = document.getElementById('buscar_lote')?.value || "";
    document.getElementById('input_rut').value = rutParaEnvio;
    document.getElementById('input_cliente').value = document.getElementById('buscar_cliente')?.value || "";
    document.getElementById('input_numero_fact').value = document.getElementById('buscar_factura')?.value || "";
    document.getElementById('input_fecha_desde').value = document.getElementById('fecha_desde')?.value || "";
    document.getElementById('input_fecha_hasta').value = document.getElementById('fecha_hasta')?.value || "";
    document.getElementById('input_serial').value = document.getElementById('buscar_serial')?.value.trim() || "";

    // También crear campos adicionales con los nombres exactos que espera el PHP
    const camposExtras = [
        { name: 'sku', value: document.getElementById('buscar_sku')?.value || "" },
        { name: 'lote', value: document.getElementById('buscar_lote')?.value || "" },
        { name: 'rut', value: rutParaEnvio },
        { name: 'nombre', value: document.getElementById('buscar_cliente')?.value || "" },
        { name: 'numero_fact', value: document.getElementById('buscar_factura')?.value || "" },
        { name: 'fecha_desde', value: document.getElementById('fecha_desde')?.value || "" },
        { name: 'fecha_hasta', value: document.getElementById('fecha_hasta')?.value || "" },
        { name: 'buscar_serial', value: document.getElementById('buscar_serial')?.value.trim() || "" }
    ];

    // Eliminar campos extra anteriores si existen
    camposExtras.forEach(campo => {
        const existente = document.getElementById('extra_' + campo.name);
        if (existente) existente.remove();
    });

    // Crear campos extra con los nombres que espera el PHP
    camposExtras.forEach(campo => {
        if (campo.value) { // Solo agregar si tiene valor
            const input = document.createElement('input');
            input.type = 'hidden';
            input.id = 'extra_' + campo.name;
            input.name = campo.name;
            input.value = campo.value;
            fp.appendChild(input);
        }
    });

    // Detectar si es dispositivo móvil/iOS
    const esIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

    if (esIOS) {
        // Para iOS: Usar imprimir_directo.php (HTML) y abrir en misma ventana

        const formDatos = new FormData(fp);

        // Agregar campos adicionales al FormData para iOS
        formDatos.set('sku', document.getElementById('buscar_sku')?.value || "");
        formDatos.set('lote', document.getElementById('buscar_lote')?.value || "");
        formDatos.set('rut', rutParaEnvio);
        formDatos.set('nombre', document.getElementById('buscar_cliente')?.value || "");
        formDatos.set('numero_fact', document.getElementById('buscar_factura')?.value || "");
        formDatos.set('fecha_desde', document.getElementById('fecha_desde')?.value || "");
        formDatos.set('fecha_hasta', document.getElementById('fecha_hasta')?.value || "");
        formDatos.set('buscar_serial', document.getElementById('buscar_serial')?.value.trim() || "");

        // Usar imprimir_directo.php y reemplazar contenido actual
        fetch('/php/ingreso_ventas/consultar_productos/imprimir_directo.php', {
            method: 'POST',
            body: formDatos
        })
            .then(respuesta => {
                if (!respuesta.ok) throw new Error('Respuesta de red no OK: ' + respuesta.status);
                return respuesta.text();
            })
            .then(htmlContent => {
                // Modificar el HTML para agregar botón de volver y mejorar auto-impresión
                const htmlModificado = htmlContent.replace(
                    '<script>',
                    `<style>
                    .ios-back-button {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: #007AFF;
                        color: white;
                        border: none;
                        padding: 12px 20px;
                        border-radius: 25px;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                        box-shadow: 0 2px 10px rgba(0,122,255,0.3);
                        z-index: 1000;
                    }
                    @media print {
                        .ios-back-button { display: none !important; }
                    }
                </style>
                <script>
                    // Agregar botón de volver para iOS
                    window.addEventListener('load', function() {
                        const backBtn = document.createElement('button');
                        backBtn.className = 'ios-back-button';
                        backBtn.innerHTML = '← Volver';
                        backBtn.onclick = function() {
                            window.history.back();
                        };
                        document.body.appendChild(backBtn);
                    });
                </script>
                <script>`
                );

                // Reemplazar toda la página con el contenido de impresión
                document.open();
                document.write(htmlModificado);
                document.close();

                // Resetear bandera
                setTimeout(() => {
                    imprimiendo = false;
                }, 1500);
            })
            .catch(error => {
                console.error('Error al generar HTML para impresión iOS:', error);
                alert('Error al generar el documento para impresión.');
                imprimiendo = false;
            });
    } else {
        // Para PC/Desktop: usar exactamente el método original sin complicaciones
        imprimir_pdf_desde_php();
    }
}

// Imprimir pdf para celular (función original mantenida como fallback)
function imprimir_pdf_desde_php() {
    // Validar que haya filtros activos
    if (!window.tieneFiltrosActivos()) {
        alert('Para imprimir debes aplicar al menos un filtro.');
        imprimiendo = false; // Resetear bandera
        return;
    }

    const fp = document.getElementById('formPDF');
    if (!fp) {
        alert('Error: No se encontró el formulario en la página.');
        imprimiendo = false; // Resetear bandera
        return;
    }

    // Copiar filtros actuales a campos ocultos
    const rutInput = document.getElementById('buscar_rut');
    let rutParaEnvio = '';
    if (ES_DISTRIBUIDOR && rutInput) {
        const rutNormalizado = rutInput.getAttribute('data-rut-normalizado');
        rutParaEnvio = rutNormalizado || normalizar_rut(rutInput.value) || rutInput.value.trim();
    } else {
        rutParaEnvio = rutInput ? normalizar_rut(rutInput.value) : '';
    }

    document.getElementById('input_sku').value = document.getElementById('buscar_sku')?.value || "";
    document.getElementById('input_lote').value = document.getElementById('buscar_lote')?.value || "";
    document.getElementById('input_rut').value = rutParaEnvio;
    document.getElementById('input_cliente').value = document.getElementById('buscar_cliente')?.value || "";
    document.getElementById('input_numero_fact').value = document.getElementById('buscar_factura')?.value || "";
    document.getElementById('input_fecha_desde').value = document.getElementById('fecha_desde')?.value || "";
    document.getElementById('input_fecha_hasta').value = document.getElementById('fecha_hasta')?.value || "";
    document.getElementById('input_serial').value = document.getElementById('buscar_serial')?.value.trim() || "";

    // También asegurar que los campos tengan los nombres que espera el PHP
    const camposExtras = [
        { name: 'sku', value: document.getElementById('buscar_sku')?.value || "" },
        { name: 'lote', value: document.getElementById('buscar_lote')?.value || "" },
        { name: 'rut', value: rutParaEnvio },
        { name: 'nombre', value: document.getElementById('buscar_cliente')?.value || "" },
        { name: 'numero_fact', value: document.getElementById('buscar_factura')?.value || "" },
        { name: 'fecha_desde', value: document.getElementById('fecha_desde')?.value || "" },
        { name: 'fecha_hasta', value: document.getElementById('fecha_hasta')?.value || "" },
        { name: 'buscar_serial', value: document.getElementById('buscar_serial')?.value.trim() || "" }
    ];

    // Eliminar campos extra anteriores si existen
    camposExtras.forEach(campo => {
        const existente = fp.querySelector(`input[name="${campo.name}"]`);
        if (existente && existente.id && existente.id.startsWith('extra_')) {
            existente.remove();
        }
    });

    // Crear campos extra con los nombres que espera el PHP
    camposExtras.forEach(campo => {
        if (campo.value) { // Solo agregar si tiene valor
            const input = document.createElement('input');
            input.type = 'hidden';
            input.id = 'extra_' + campo.name;
            input.name = campo.name;
            input.value = campo.value;
            fp.appendChild(input);
        }
    });

    // Crear formulario temporal que apunte a imprimir_directo.php (HTML para PC)
    const temp = document.createElement('form');
    temp.method = 'POST';
    temp.action = '/php/ingreso_ventas/consultar_productos/imprimir_directo.php';
    temp.target = '_blank';
    temp.style.display = 'none';

    // Copiar todos los campos del formPDF
    const formData = new FormData(fp);
    for (const [key, value] of formData.entries()) {
        if (value instanceof File) continue;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        temp.appendChild(input);
    }

    // Enviar y limpiar
    document.body.appendChild(temp);
    temp.submit();
    setTimeout(() => {
        document.body.removeChild(temp);
        imprimiendo = false; // Resetear bandera después del envío
    }, 1000);
}

document.addEventListener('DOMContentLoaded', function () {
    // Event listener para formulario de impresión
    const formImprimir = document.getElementById('form-imprimir');
    if (formImprimir) {
        formImprimir.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevenir submit normal
            imprimir_pdf();
        });
    }

    const selector = document.getElementById('accionSelector');
    if (!selector) return;

    selector.addEventListener('change', function () {
        const valor = selector.value;

        // Verificar filtros activos primero
        if (!window.tieneFiltrosActivos()) {
            alert('Debes aplicar al menos un filtro antes de continuar.');
            selector.value = '';
            return;
        }

        if (valor === 'imprimir') {
            // Usar la misma técnica exitosa de generar_qr.js para iOS
            impprimir_pdf_con_tecnica_qr();
        }
        else if (valor === 'excel') {
            // Copiar filtros y enviar
            copiarFiltrosAExcel();
            document.getElementById('formExcel').submit();
        }
        else if (valor === 'pdf') {
            // Copiar filtros y enviar
            copiarFiltrosAPDF();
            document.getElementById('formPDF').submit();
        }

        // Resetear el selector
        selector.value = '';
    });
});

// Selector de acciones solo en móviles
document.addEventListener('DOMContentLoaded', function () {
    const selector = document.getElementById('accionSelector');
    if (selector) {
        selector.addEventListener('change', function () {
            const opcion = selector.value;
            // Helpers locales: copiar filtros a inputs ocultos (misma lógica que en submit handlers)
            function copiarFiltrosAExcel() {
                const rutInput = document.getElementById('buscar_rut');
                let rutParaEnvio = '';
                if (ES_DISTRIBUIDOR && rutInput) {
                    const rutNormalizado = rutInput.getAttribute('data-rut-normalizado');
                    rutParaEnvio = rutNormalizado || normalizar_rut(rutInput.value) || rutInput.value.trim();
                } else {
                    rutParaEnvio = rutInput ? normalizar_rut(rutInput.value) : '';
                }

                document.getElementById('excel_sku').value = document.getElementById('buscar_sku')?.value.trim() || '';
                document.getElementById('excel_lote').value = document.getElementById('buscar_lote')?.value.trim() || '';
                document.getElementById('excel_rut').value = rutParaEnvio;
                document.getElementById('excel_nombre').value = document.getElementById('buscar_cliente')?.value.trim() || '';
                document.getElementById('excel_factura').value = document.getElementById('buscar_factura')?.value.trim() || '';
                document.getElementById('excel_fecha_desde').value = document.getElementById('fecha_desde')?.value.trim() || '';
                document.getElementById('excel_fecha_hasta').value = document.getElementById('fecha_hasta')?.value.trim() || '';
                document.getElementById('excel_serial').value = document.getElementById('buscar_serial')?.value.trim() || '';
            }

            function copiarFiltrosAPDF() {
                const rutInput = document.getElementById('buscar_rut');
                let rutParaEnvio = '';
                if (ES_DISTRIBUIDOR && rutInput) {
                    const rutNormalizado = rutInput.getAttribute('data-rut-normalizado');
                    rutParaEnvio = rutNormalizado || normalizar_rut(rutInput.value) || rutInput.value.trim();
                } else {
                    rutParaEnvio = rutInput ? normalizar_rut(rutInput.value) : '';
                }

                document.getElementById('input_sku').value = document.getElementById('buscar_sku')?.value.trim() || "";
                document.getElementById('input_lote').value = document.getElementById('buscar_lote')?.value.trim() || "";
                document.getElementById('input_rut').value = rutParaEnvio;
                document.getElementById('input_cliente').value = document.getElementById('buscar_cliente')?.value.trim() || "";
                document.getElementById('input_numero_fact').value = document.getElementById('buscar_factura')?.value.trim() || "";
                document.getElementById('input_fecha_desde').value = document.getElementById('fecha_desde')?.value || "";
                document.getElementById('input_fecha_hasta').value = document.getElementById('fecha_hasta')?.value || "";
                document.getElementById('input_serial').value = document.getElementById('buscar_serial')?.value.trim() || "";
            }

            if (opcion === "excel") {
                if (!window.tieneFiltrosActivos()) { alert('Para descargar Excel debes aplicar al menos un filtro.'); selector.selectedIndex = 0; return; }
                copiarFiltrosAExcel();
                const btnExcel = document.getElementById('btnExcel');
                if (btnExcel) btnExcel.click();
                else {
                    const fx = document.getElementById('formExcel');
                    if (fx && typeof fx.requestSubmit === 'function') fx.requestSubmit();
                    else if (fx) fx.submit();
                }
            } else if (opcion === "pdf") {
                if (!window.tieneFiltrosActivos()) { alert('Para descargar PDF debes aplicar al menos un filtro.'); selector.selectedIndex = 0; return; }
                copiarFiltrosAPDF();
                const fp = document.getElementById('formPDF');
                if (fp) {
                    // Hacemos POST con fetch y forzamos descarga del blob recibido
                    const data = new FormData(fp);
                    // Asegurarnos de que no se envíe por el navegador
                    (async () => {
                        try {
                            const resp = await fetch(fp.action, { method: 'POST', body: data });
                            if (!resp.ok) throw new Error('Estado: ' + resp.status);
                            const blob = await resp.blob();

                            // Intentar leer nombre de archivo desde headers
                            let filename = 'reporte.pdf';
                            const cd = resp.headers.get('Content-Disposition') || '';
                            const m = cd.match(/filename\*=UTF-8''(.+)|filename="?([^";]+)"?/);
                            if (m) filename = decodeURIComponent(m[1] || m[2]);

                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                        } catch (err) {
                            console.error('Error descargando PDF:', err);
                            alert('No se pudo descargar el PDF. Se abrirá en nueva pestaña como fallback.');
                            // Fallback: abrir en nueva pestaña
                            const f = document.createElement('form');
                            f.method = 'POST';
                            f.action = fp.action;
                            f.target = '_blank';
                            f.style.display = 'none';
                            for (const pair of data.entries()) {
                                const i = document.createElement('input');
                                i.type = 'hidden';
                                i.name = pair[0];
                                i.value = pair[1];
                                f.appendChild(i);
                            }
                            document.body.appendChild(f);
                            f.submit();
                            setTimeout(() => document.body.removeChild(f), 1000);
                        }
                    })();
                }
            } else if (opcion === "imprimir") {
                impprimir_pdf_con_tecnica_qr();
            }
            selector.selectedIndex = 0;
        });
    }
});


// TITULO TABLA DE VENTAS 

// SIN CODIGO



// TITULO CUERPO HISTORICO DE LA TABLA 

// Función para normalizar el formato del RUT
function normalizar_rut(rut) {
    if (!rut) return '';
    // Remover espacios, puntos y convertir a string
    let rutLimpio = rut.toString().replace(/[\s.]/g, '').trim();
    return rutLimpio;
}

// Función para cargar los datos filtrados
function cargar_ventas(sku = '', lote = '', rut = '', nombre = '', factura = '', fechaDesde = '', fechaHasta = '', serial = '') {
    const myRequestId = ++latestRequestId; // identifica esta petición

    // Normalizar el RUT antes de enviarlo
    const rutNormalizado = normalizar_rut(rut);

    // Validación específica para distribuidores
    if (ES_DISTRIBUIDOR) {
        if (rutNormalizado && rutNormalizado !== window.rutDistribuidorNormalizado) {
            alert('Solo tienes acceso a la información del RUT asociado a esta cuenta.');
            const buscarRutInput = document.getElementById('buscar_rut');
            if (buscarRutInput) buscarRutInput.value = '';
            return;
        }
        filtroSku = sku;
        filtroLote = lote;
        filtroRut = window.rutDistribuidorNormalizado;
        filtroNombre = nombre;
        filtroFact = factura;
        filtroFechaDesde = fechaDesde;
        filtroFechaHasta = fechaHasta;
    } else {
        filtroSku = sku;
        filtroLote = lote;
        filtroRut = rutNormalizado;
        filtroNombre = nombre;
        filtroFact = factura;
        filtroFechaDesde = fechaDesde;
        filtroFechaHasta = fechaHasta;
    }

    const rutFinal = ES_DISTRIBUIDOR ? window.rutDistribuidorNormalizado : rutNormalizado;
    let url = `/php/ingreso_ventas/consultar_productos/obtener_ventas.php?sku=${encodeURIComponent(sku)}&lote=${encodeURIComponent(lote)}&rut=${encodeURIComponent(rutFinal)}&nombre=${encodeURIComponent(nombre)}&numero_fact=${encodeURIComponent(factura)}&fecha_desde=${encodeURIComponent(fechaDesde)}&fecha_hasta=${encodeURIComponent(fechaHasta)}`;
    if (ES_USUARIO_FINAL) url += `&buscar_serial=${encodeURIComponent(serial)}`;

    // Preparar UI: limpiar tabla inmediatamente y mostrar loader
    const tablaVentas = document.getElementById('tablaVentas');
    const cont = document.getElementById('contenedorTabla');
    if (tablaVentas) tablaVentas.innerHTML = '';
    if (cont) cont.style.display = 'none';
    mostrar_tabla_de_resultados();

    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.text();
        })
        .then(htmlRespuesta => {
            if (myRequestId !== latestRequestId) throw { name: 'StaleResponse' };

            if (!tablaVentas) {
                console.error('No se encontró el elemento con id "tablaVentas".');
                return;
            }

            // Insertar directamente el HTML recibido del servidor
            tablaVentas.innerHTML = htmlRespuesta.trim();

            if (cont) cont.style.display = 'block';
        })
        .catch(err => {
            if (err && err.name === 'StaleResponse') {
                return;
            }
            console.error('Error en cargar_ventas:', err);
            if (tablaVentas) {
                tablaVentas.innerHTML = '<tr><td colspan="13">Error al obtener resultados.</td></tr>';
            }
            if (cont) cont.style.display = 'block';
        })
        .finally(() => {
            if (myRequestId === latestRequestId) ocultar_tabla_de_resultados();
        });
}

// TITULO MODAL MODIFICAR
function para_modal_modificar(iniRaw, finRaw) {
    const iniStr = (iniRaw ?? '').toString();
    const finStr = (finRaw ?? '').toString();

    const iniDigits = iniStr.replace(/\D/g, '');
    const finDigits = finStr.replace(/\D/g, '');

    // ancho objetivo: al menos 7, no más de 8
    let width = Math.max(7, iniDigits.length, finDigits.length);
    width = Math.min(8, width);

    // serie inicio: si es numérica 100%, acolchamos; si no, mostramos tal cual
    const iniPadded = /^\d+$/.test(iniStr) ? iniStr.padStart(width, '0') : iniStr;

    // serie fin: si contiene dígitos, reemplazamos SOLO el tramo numérico por la versión acolchada.
    const finPaddedDigits = finDigits ? finDigits.padStart(width, '0') : '';
    const finPadded = finDigits ? finStr.replace(/\d+/, finPaddedDigits) : finStr;

    return { iniPadded, finPadded };
}
// Función para abrir el modal y cargar los datos de la venta
function abrir_modal(id, venta) {
    // Mostrar el modal
    const modal = document.getElementById('modalEditarVenta');
    modal.style.display = 'flex';

    // Cargar los datos de la venta en el formulario del modal
    document.getElementById('id').value = venta.id || '';
    document.getElementById('sku').value = venta.sku || '';
    document.getElementById('rut').value = venta.rut || '';
    document.getElementById('nombre').value = venta.nombre || '';
    document.getElementById('numeroDoc').value = venta.numero_fact || '';

    // Normaliza la fecha al formato YYYY-MM-DDTHH:MM (sin segundos)
    (function () {
        let fd = venta.fecha_despacho ? String(venta.fecha_despacho).replace(' ', 'T') : '';
        const m = fd.match(/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})/);
        if (m) fd = m[1];
        else if (fd.length >= 16) fd = fd.slice(0, 16);
        else fd = '';
        document.getElementById('fechaDespacho').value = fd || '';
    })();

    document.getElementById('producto').value = venta.producto || '';
    document.getElementById('cantidad').value = venta.cantidad || '';
    document.getElementById('lote').value = venta.lote || '';
    document.getElementById('fechaFabricacion').value = venta.fecha_fabricacion || '';
    document.getElementById('fechaVencimiento').value = venta.fecha_vencimiento || '';

    // --- padding de series SOLO para mostrar en el modal ---
    const { iniPadded, finPadded } = para_modal_modificar(venta.n_serie_ini, venta.n_serie_fin);
    document.getElementById('serieInicio').value = iniPadded || '';
    document.getElementById('serieFin').value = finPadded || '';
}

// función para cerrar el modal
function cerrar_modal() {
    const modal = document.getElementById('modalEditarVenta');
    modal.style.display = 'none';
}

// Función para guardar los cambios
function guardar_cambios() {
    // Obtener los valores de los campos del formulario
    const id = document.getElementById('id').value;
    const sku = document.getElementById('sku').value;
    const rut = document.getElementById('rut').value;
    const nombre = document.getElementById('nombre').value;
    const numeroDoc = document.getElementById('numeroDoc').value;
    const fechaDespachoRaw = document.getElementById('fechaDespacho').value;
    // forzar formato YYYY-MM-DDTHH:MM (datetime-local) o cadena vacía
    const fechaDespacho = fechaDespachoRaw ? String(fechaDespachoRaw).slice(0, 16) : '';
    const producto = document.getElementById('producto').value;
    const cantidad = document.getElementById('cantidad').value;
    const lote = document.getElementById('lote').value;
    const fechaFabricacion = document.getElementById('fechaFabricacion').value;
    const fechaVencimiento = document.getElementById('fechaVencimiento').value;
    const serieInicio = document.getElementById('serieInicio').value;
    const serieFin = document.getElementById('serieFin').value;

    // Expresiones regulares para validación
    const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    const numeroRegex = /^\d+$/;
    

    // Validaciones de los campos

    // Validación del nombre
    if (!nombreRegex.test(nombre)) {
        alert('El nombre no debe contener caracteres numéricos y/o especiales.');
        return;
    }

    // Validación del numero de documento
    if (!numeroRegex.test(numeroDoc) || numeroDoc.length > 21) {
        alert('El número de documento solo puede contener caracteres numéricos.');
        return;
    }

    // Validacion del lote
    if (!numeroRegex.test(lote)) {
        alert('El lote solo puede contener caracteres numéricos.');
        return;
    }

    // Validación de la serie de inicio
    if (!numeroRegex.test(serieInicio)) {
        alert('La serie de inicio solo puede contener caracteres numéricos.');
        return;
    }

    // Validación de la serie de fin
    if (!numeroRegex.test(serieFin)) {
        alert('La serie de término solo puede contener caracteres numéricos.');
        return;
    }

    // Validación de fecha de fabricación
    if (!fechaFabricacion) {
        alert('La fecha de fabricación no puede quedar vacía.');
        return;
    }

    // Validación de fecha de vencimiento
    if (!fechaVencimiento) {
        alert('La fecha de vencimiento no puede quedar vacía.');
        return;
    }

    // URL del archivo PHP que procesa los cambios
    const params = new URLSearchParams();
    params.append('id', id);
    params.append('sku', sku);
    params.append('rut', rut);
    params.append('nombre', nombre);
    params.append('numero_fact', numeroDoc);
    params.append('fecha_despacho', fechaDespacho);
    params.append('producto', producto);
    params.append('cantidad', cantidad);
    params.append('lote', lote);
    params.append('fecha_fabricacion', fechaFabricacion);
    params.append('fecha_vencimiento', fechaVencimiento);
    params.append('n_serie_ini', serieInicio);
    params.append('n_serie_fin', serieFin);

    fetch(`/php/ingreso_ventas/consultar_productos/guardar_cambios.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString(),
    })
        // Convierte la respuesta a texto plano
        .then(respuesta => respuesta.text())
        // Procesa la respuesta del servidor
        .then(texto => {
            // Espera formato: "success|mensaje" o "error|mensaje"
            const partes = texto.trim().split('|');
            const estado = partes[0];
            const mensaje = partes.slice(1).join('|') || texto;
            
            if (estado === 'success') {
                alert(mensaje);
                cerrar_modal();
                try { reargar_tabla_actual(); } catch (e) { console.warn('Recarga tras guardar falló:', e); }
            } else {
                alert(mensaje);
            }
        })
        // Si hay un error en la solicitud, muestra un mensaje de error
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un error al guardar los cambios.');
        });
}


// TITULO MODAL ELIMINACION

// Función para abrir el modal de eliminación
function abrir_modal_eliminacion(id) {
    // Se define la constante de modal que encuentra lo que debe mostrar por id
    const modaleliminacion = document.getElementById('modalEliminarVenta');
    // Cambia el estado de hidden a block, para que pueda verse el modal
    modaleliminacion.style.display = 'flex';

    // Obtiene el id de venta para poder eliminarla posteriormente
    const inputId = document.getElementById('idVentaEliminar');
    if (inputId) {
        // Define el id segun la fila
        inputId.value = id;
    } else {
        console.error("No se encontró el input oculto con id 'idVentaEliminar'");
    }
}

// Función para abrir modal desde botón con data-venta
function abrir_modal_desde_boton(boton) {
    const dataStr = boton.getAttribute('data-venta');
    const venta = {};
    
    // Parsear el string "clave:valor,clave:valor"
    dataStr.split(',').forEach(par => {
        const separador = par.indexOf(':');
        if (separador > -1) {
            const clave = par.substring(0, separador);
            const valor = par.substring(separador + 1);
            venta[clave] = valor;
        }
    });
    
    abrir_modal(venta.id, venta);
}


// función para cerrar el modal
function cerrar_modal_eliminacion() {
    // Se define la constante de modal que encuentra lo que debe mostrar por id
    const modaleliminacion = document.getElementById('modalEliminarVenta');
    // Cambia el estado de block a none, para "cerrar" el modal
    modaleliminacion.style.display = 'none';
}


// Función para confirmar y eliminar una venta
function eliminar_venta_confirmada() {
    const id = document.getElementById('idVentaEliminar').value;
    // Si no se encuentrla id arroja una alerta
    if (!id) {
        alert('No se encontró el ID de la venta.');
        return;
    }
    // Obtiene la direccion del archivo php que contiene la query para eliminar ventas
    fetch(`/php/ingreso_ventas/eliminar_ventas/eliminar_filas.php`, {
        // Define el metodo de envio del formulario
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}`,
    })
        // Obtiene el id en texto
        .then(respuesta => respuesta.text())
        // Ejecuta la funcion en eliminar_filas.php
        .then(data => {
            // Arroja una alerta notificando que se elimino correctamente la venta
            alert('Venta eliminada correctamente.');
            // Ejecuta la funcion para cerrar el modal
            cerrar_modal_eliminacion();
            // Recargar la tabla usando helper común
            try { reargar_tabla_actual(); } catch (e) { console.warn('Recarga tras eliminar falló:', e); }

        })
        //Si ocurre un error lo notifica mediante una alerta
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un error al eliminar la venta.');
        });

}

// Helper para recargar la tabla con los filtros actuales
function reargar_tabla_actual() {
    const skuAct = document.getElementById('buscar_sku')?.value.trim() || '';
    const loteAct = document.getElementById('buscar_lote')?.value.trim() || '';
    const rutAct = document.getElementById('buscar_rut')?.value.trim() || '';
    const nombreAct = document.getElementById('buscar_cliente')?.value.trim() || '';
    const facturaAct = document.getElementById('buscar_factura')?.value.trim() || '';
    const fdesdeAct = document.getElementById('fecha_desde')?.value || '';
    const fhastaAct = document.getElementById('fecha_hasta')?.value || '';
    const serialAct = document.getElementById('buscar_serial')?.value.trim() || '';
    
        // Si no hay filtros activos, no cargar nada — limpiar/ocultar la tabla
    if (typeof window.tieneFiltrosActivos === 'function' && !window.tieneFiltrosActivos()) {
        const cont = document.getElementById('contenedorTabla');
        const tablaVentas = document.getElementById('tablaVentas');
        if (tablaVentas) tablaVentas.innerHTML = '';
        if (cont) cont.style.display = 'none';
        return;
    }

    cargar_ventas(skuAct, loteAct, rutAct, nombreAct, facturaAct, fdesdeAct, fhastaAct, serialAct);
    
}


// TITULO ARCHIVO JS

// SIN CODIGO



/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa buscar .JS --------------------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

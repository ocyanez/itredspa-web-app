// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  --------------------------------------------------------------------------------------------------------------
    -------------------------------------- INICIO ITred Spa normalizar_qr .JS ------------------------------------
    -------------------------------------------------------------------------------------------------------------- */


    // esto es para la comunicacion con el servidor
    let llamadas_pendientes = {}; // esto es para que el servidor pueda llamar a la funcion phpRespuesta
    let id_llamada_pendiente = 0; // esto es para que el servidor pueda llamar a la funcion phpRespuesta
    let id_llamada_actual = null; // esto es para que el servidor pueda llamar a la funcion phpRespuesta

    // Esta función recibe la respuesta del servidor cuando termina de procesar
    // El servidor llama a esta función automáticamente cuando tiene lista la información
    // para actualizar la pagina en tiempo real
    function phpRespuesta(resultado, datos) {
        
        // Mostramos en la consola qué respondió el servidor (para revisar si hay problemas)
        console.log('PHP respondio:', resultado, datos);
        
        // Buscamos la función que estaba esperando esta respuesta
        const llamar_de_vuelta = llamadas_pendientes[id_llamada_actual];
        
        // Si encontramos una función esperando
        if (llamar_de_vuelta) {
            
            // Borramos la solicitud de la lista de espera porque ya llegó
            delete llamadas_pendientes[id_llamada_actual];
            
            // Ejecutamos la función con el resultado y los datos que llegaron
            llamar_de_vuelta(resultado, datos);
        }
    }

    // Esta función busca o crea una ventanita invisible para enviar datos al servidor 
    // esto nos dice que actualizar de la pagina es basicamente un mensajero 
    function getCommIframe() {
        
        // Buscamos si ya existe la ventanita invisible
        let iframe = document.getElementById('commIframe');
        
        // Si no existe, la creamos desde cero
        if (!iframe) {
            iframe = document.createElement('iframe'); // creamos la ventanita
            iframe.id = 'commIframe';                  // le ponemos un nombre para encontrarla después
            iframe.name = 'commIframe';                // otro nombre que necesita el formulario
            iframe.style.display = 'none';             // la hacemos invisible porque el usuario no necesita verla
            document.body.appendChild(iframe);         // la agregamos a la página
        }
        
        // Devolvemos la ventanita (ya sea la que encontramos o la que creamos)
        return iframe;
    }

    // Esta función envía datos al servidor usando un formulario invisible 
    // el formulario nos dice que actualizar de la pagina para que paresca que se actualiza en tiempo real
    // pero solo se actualiza lo que nesecitamos
    function enviarAlServidor(action, datos, llamar_de_vuelta) {
        
        // Obtenemos la ventanita invisible que usamos para comunicarnos
        const iframe = getCommIframe();
        
        // Creamos un formulario nuevo 
        const form = document.createElement('form');
        
        // Configuramos que el formulario envíe datos con el método POST
        form.method = 'POST';
        
        // Ponemos la dirección del servidor y le decimos qué acción queremos hacer
        form.action = PROFILE_ENDPOINT + '?action=' + encodeURIComponent(action);
        
        // Hacemos que el formulario envíe los datos a la ventanita invisible
        form.target = 'commIframe';
        
        // Ocultamos el formulario porque el usuario no necesita verlo
        form.style.display = 'none';

        // Ahora recorremos todos los datos que queremos enviar
        for (let key in datos) {
            
            // Verificamos que el dato realmente pertenezca a nuestro paquete
            if (datos.hasOwnProperty(key)) {
                
                // Creamos un campo invisible para cada dato
                const input = document.createElement('input');
                
                // Lo hacemos de tipo oculto para que no se vea
                input.type = 'hidden';
                
                // Le ponemos el nombre del dato (por ejemplo: "sku", "producto")
                input.name = key;
                
                // Si el dato es una lista o un paquete de datos, lo convertimos a texto
                if (typeof datos[key] === 'object' && datos[key] !== null) {
                    input.value = matriz_a_cadena(datos[key]);
                } else {
                    // Si es un dato simple, lo ponemos directamente (o vacío si no existe)
                    input.value = datos[key] !== null && datos[key] !== undefined ? datos[key] : '';
                }
                
                // Agregamos este campo al formulario
                form.appendChild(input);
            }
        }

        // Aumentamos el contador para identificar esta solicitud
        id_llamada_pendiente++;
        
        // Guardamos cuál es la solicitud actual
        id_llamada_actual = id_llamada_pendiente;
        
        // Guardamos la función que se ejecutará cuando el servidor responda
        llamadas_pendientes[id_llamada_pendiente] = llamar_de_vuelta;

        // Guardamos el número de esta solicitud para el temporizador
        const id_timeout = id_llamada_pendiente;
        
        // Configuramos una alarma: si el servidor no responde en 10 segundos, marcamos error
        setTimeout(function() {
            
            // Revisamos si todavía estamos esperando esta respuesta
            if (llamadas_pendientes[id_timeout]) {
                
                // Borramos la solicitud porque ya no la esperamos
                delete llamadas_pendientes[id_timeout];
                
                // Avisamos que hubo un error de tiempo agotado
                llamar_de_vuelta('error', {msg: 'timeout'});
            }
        }, 10000); // 10000 milisegundos = 10 segundos

        // Agregamos el formulario a la página
        document.body.appendChild(form);
        
        // Enviamos el formulario (esto manda los datos al servidor)
        form.submit();
        
        // Quitamos el formulario de la página porque ya no lo necesitamos
        document.body.removeChild(form);
    }

    // Esta función pide datos al servidor pregunta que debe actualizar en la pagina
    function obtenerDelServidor(action, params, llamar_de_vuelta) {
        
        // Obtenemos la ventanita invisible para comunicarnos
        const iframe = getCommIframe();
        
        // Empezamos a armar la dirección web con la acción que queremos
        let url = PROFILE_ENDPOINT + '?action=' + encodeURIComponent(action);
        
        // Agregamos cada parámetro a la dirección web
        for (let key in params) {
            
            // Verificamos que el parámetro sea válido
            if (params.hasOwnProperty(key)) {
                
                // Agregamos el parámetro a la dirección (ejemplo: &nombre=valor)
                url += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
            }
        }
        
        // Agregamos la hora actual para evitar que el navegador use una respuesta vieja guardada
        url += '&t=' + Date.now();

        // Aumentamos el contador de solicitudes
        id_llamada_pendiente++;
        
        // Guardamos cuál es la solicitud actual
        id_llamada_actual = id_llamada_pendiente;
        
        // Guardamos la función que se ejecutará cuando llegue la respuesta
        llamadas_pendientes[id_llamada_pendiente] = llamar_de_vuelta;

        // Guardamos el número para el temporizador de espera
        const id_timeout = id_llamada_pendiente;
        
        // Si el servidor no responde en 10 segundos, marcamos error
        setTimeout(function() {
            
            // Verificamos si todavía esperamos esta respuesta
            if (llamadas_pendientes[id_timeout]) {
                
                // Ya no la esperamos más
                delete llamadas_pendientes[id_timeout];
                
                // Avisamos que se agotó el tiempo
                llamar_de_vuelta('error', {msg: 'timeout'});
            }
        }, 10000);

        // Hacemos que la ventanita invisible vaya a esa dirección
        // Esto hace que el servidor nos envíe la información
        iframe.src = url;
    }

// Procesa QR que viene desde ventas.js via URL
function procesarQRDesdeURL() {
    try {
        var params = new URLSearchParams(window.location.search);
        var datos_sin_procesar = params.get('raw_data');
        
        // Capturar y guardar datos del cliente
        var rutCliente = params.get('rut_cliente') || '';
        var numFactura = params.get('num_factura') || '';
        var fechaDespacho = params.get('fecha_despacho') || '';
        
        // Guardar en variable global para uso posterior
        if (rutCliente || numFactura || fechaDespacho) {
            window._datosCliente = {
                rut: rutCliente,
                factura: numFactura,
                fecha: fechaDespacho
            };
            console.log('Datos del cliente guardados:', window._datosCliente);
        }
        
        // Si no hay raw_data, no hacer nada (uso normal de normalizar_qr)
        if (!datos_sin_procesar || datos_sin_procesar.trim() === '') {
            return false;
        }
        
        console.log('QR recibido desde Ingreso de Productos:', datos_sin_procesar);
        
        // Mostrar datos crudos en la tabla superior
        mostrarDatosCrudos(datos_sin_procesar);
        
        // Procesar el QR (esto llena la tabla principal y activa el panel)
        procesarQR(datos_sin_procesar);
        
        return true;
    } catch (e) {
        console.error('Error procesando QR desde URL:', e);
        return false;
    }
}

    // Convertir array/objeto a string 
    function matriz_a_cadena(obj) {
        if (Array.isArray(obj)) {
            return obj.map(function(v) {
                // Convertir null/undefined a string vacío explícitamente
                if (v === null || v === undefined) return '';
                if (typeof v === 'object' && v !== null) return matriz_a_cadena(v);
                return String(v);
            }).join('|');
        } else if (typeof obj === 'object' && obj !== null) {
            let parts = [];
            for (let k in obj) {
                if (obj.hasOwnProperty(k)) {
                    let v = obj[k];
                    if (v === null || v === undefined) v = '';
                    else if (typeof v === 'object' && v !== null) v = matriz_a_cadena(v);
                    else v = String(v);
                    parts.push(k + ':' + v);
                }
            }
            return parts.join(';');
        }
        return String(obj !== null && obj !== undefined ? obj : '');
    }

    // Parsear string de vuelta a array
    function cadena_a_matriz(str) {
        if (!str || typeof str !== 'string') return [];
        return str.split('|');
    }

    // Parsear string de vuelta a objeto
    function cadena_a_objeto(str) {
        if (!str || typeof str !== 'string') return {};
        const obj = {};
        str.split(';').forEach(function(pair) {
            const idx = pair.indexOf(':');
            if (idx > 0) {
                const k = pair.substring(0, idx);
                const v = pair.substring(idx + 1);
                obj[k] = v || '';
            }
        });
        return obj;
    }


    /*normalizar series */ 
    const LONG_SERIE = 8; // longitud deseada para Serie Inicio / Serie Fin

    // Esta función arregla los números de serie para que tengan el formato correcto
    function normalizarSeries(ini, fin, long) {
        
        // Si no nos dicen qué largo deben tener, usamos 8 dígitos
        if (long === undefined) long = LONG_SERIE;
        
        // Tomamos el primer número de serie y le quitamos espacios en blanco
        let s1 = (ini !== null && ini !== undefined ? ini : '').toString().trim();
        
        // Hacemos lo mismo con el segundo número de serie
        let s2 = (fin !== null && fin !== undefined ? fin : '').toString().trim();

        // Si el primer número tiene letras o está vacío, lo ponemos en cero
        if (!/^\d+$/.test(s1)) s1 = '0';
        
        // Si el segundo número tiene letras o está vacío, lo ponemos en cero
        if (!/^\d+$/.test(s2)) s2 = '0';

        // Convertimos los textos a números para poder compararlos
        const n1 = Number(s1), n2 = Number(s2);
        
        // Si el primer número es mayor que el segundo, los intercambiamos
        // Esto es porque la serie de inicio debe ser menor que la de fin
        if (!isNaN(n1) && !isNaN(n2) && n1 > n2) {
            var temp = s1; // guardamos el primero temporalmente
            s1 = s2;       // el primero ahora es el segundo
            s2 = temp;     // el segundo ahora es el que guardamos
        }

        // Agregamos ceros al inicio hasta completar el largo deseado
        // Por ejemplo: "123" con largo 8 se convierte en "00000123"
        s1 = s1.padStart(long, '0');
        s2 = s2.padStart(long, '0');

        // Devolvemos ambos números ya corregidos en un paquete
        return [s1, s2];
    }

    // Devuelve la fecha de hoy en formato YYYY-MM-DD
    function dia_hoy() {
        const d = new Date(); // fecha actual
        return d.toISOString().slice(0, 10); // devuelve la fecha en formato YYYY-MM-DD
    }

// Esta función limpia y ordena los datos de un producto escaneado
// Convierte cualquier formato (lista o paquete de datos) a un formato estándar
// - si un número viene vacío, lo convierte a '0'
// - si una fecha viene vacía, la convierte a '0000-00-00' (formato que entiende la base de datos)
// - si la fecha de vencimiento es incorrecta, la deja vacía para calcularla después
    function normalizar_entrada(entry) {
        // Lista de los 8 campos que debe tener cada producto
        const keys = ['sku', 'producto', 'cantidad', 'lote', 'fechaFab', 'fechaVenc', 'serieIni', 'serieFin'];
        let obj = {}; // Aquí guardaremos el producto ya limpio y ordenado
        
        // Revisamos qué tipo de dato nos llegó y lo convertimos a nuestro formato
        if (Array.isArray(entry)) {
            // Si llegó como lista (ejemplo: ['ABC123', 'Agua', '10', ...])
            // Recorremos cada campo y le asignamos el valor de la posición correspondiente
            keys.forEach(function(k, i) { 
                obj[k] = entry[i] != null ? String(entry[i]).trim() : ''; // Si tiene valor lo limpiamos, si no ponemos vacío
            });
        } else if (entry && typeof entry === 'object') {
            // Si llegó como paquete de datos (ejemplo: {sku: 'ABC123', producto: 'Agua', ...})
            // Recorremos cada campo y copiamos su valor
            keys.forEach(function(k) { 
                obj[k] = entry[k] != null ? String(entry[k]).trim() : ''; // Si tiene valor lo limpiamos, si no ponemos vacío
            });
        } else {
            // Si no llegó nada útil, creamos un producto vacío con todos los campos en blanco
            keys.forEach(function(k) { obj[k] = ''; });
        }

        // Ahora limpiamos los campos que deben ser números: cantidad, lote, serie inicio y serie fin
        ['cantidad', 'lote', 'serieIni', 'serieFin'].forEach(function(k) {
            let v = obj[k]; // Obtenemos el valor actual del campo
            
            // Si el campo está vacío o dice 'null', lo ponemos en cero
            if (!v || (v.toLowerCase && v.toLowerCase() === 'null') || v === 'undefined' || v === '') {
                obj[k] = '0';
                return; // Pasamos al siguiente campo
            }
            
            // Quitamos todo lo que no sea número (letras, símbolos, espacios, etc.)
            const digits = String(v).replace(/\D+/g, '');
            obj[k] = digits.length > 0 ? digits : '0'; // Si quedaron números los guardamos, si no ponemos cero
        });

        // Ahora procesamos las fechas
        var fechaFabValida = null;  // Aquí guardaremos la fecha de fabricación si es correcta
        var fechaVencValida = null; // Aquí guardaremos la fecha de vencimiento si es correcta
        
        // procesamos la fecha de fabricación
        var vFab = obj['fechaFab']; // Obtenemos lo que vino en fecha de fabricación
        
        // Verificamos si la fecha está vacía o es inválida
        if (!vFab || vFab === '0' || vFab === '0000-00-00' || (vFab.toLowerCase && vFab.toLowerCase() === 'null') || vFab === '') {
            obj['fechaFab'] = '0000-00-00'; // La ponemos en el formato vacío que entiende la base de datos
        } else {
            // Intentamos convertir la fecha a un formato correcto (año-mes-día)
            var parsedFab = validarYFormatearFecha(vFab);
            
            if (parsedFab && parsedFab.fechaFormateada) {
                // La fecha es válida, la guardamos formateada
                obj['fechaFab'] = parsedFab.fechaFormateada;
                fechaFabValida = new Date(parsedFab.fechaFormateada); // También la guardamos como fecha real para comparar después
            } else {
                // La fecha no se pudo interpretar, la dejamos en ceros
                obj['fechaFab'] = '0000-00-00';
            }
        }
        
        // procesamos la fecha de vencimiento
        var vVenc = obj['fechaVenc']; // Obtenemos lo que vino en fecha de vencimiento
        
        // Verificamos si la fecha está vacía o es inválida
        if (!vVenc || vVenc === '0' || vVenc === '0000-00-00' || (vVenc.toLowerCase && vVenc.toLowerCase() === 'null') || vVenc === '') {
            fechaVencValida = null; // La marcamos como inválida para revisarla después
        } else {
            // Intentamos convertir la fecha a un formato correcto
            var parsedVenc = validarYFormatearFecha(vVenc);
            
            if (parsedVenc && parsedVenc.fechaFormateada) {
                // La fecha es válida
                fechaVencValida = new Date(parsedVenc.fechaFormateada); // La guardamos como fecha real para comparar
                obj['fechaVenc'] = parsedVenc.fechaFormateada; // La guardamos formateada
            } else {
                fechaVencValida = null; // No se pudo interpretar, la marcamos como inválida
            }
        }
        
        // verificamos si hay que calcular la fecha de vencimiento
        // solo podemos calcularla si tenemos una fecha de fabricación válida
        if (fechaFabValida && obj['fechaFab'] !== '0000-00-00') {
            var necesitaCalcular = false; // bandera para saber si debemos calcular la fecha
            
            // caso 1: la fecha de vencimiento está vacía o en ceros
            if (!fechaVencValida || obj['fechaVenc'] === '0000-00-00') {
                necesitaCalcular = true;
            }
            // caso 2: la fecha de vencimiento es anterior o igual a la de fabricación (imposible en la vida real)
            else if (fechaVencValida && fechaVencValida <= fechaFabValida) {
                necesitaCalcular = true;
                console.log('Fecha vencimiento menor o igual a fabricación, recalculando...'); // Avisamos en consola
            }
            
            // Si necesitamos calcular, dejamos la fecha vacía
            // (Se calculará después cuando se envíe a ventas, sumando 5 años a la fabricación)
            if (necesitaCalcular) {
                obj['fechaVenc'] = '';
            }
        } else {
            // No hay fecha de fabricación válida
            // Si tampoco hay fecha de vencimiento válida, la ponemos en ceros
            if (!fechaVencValida) {
                obj['fechaVenc'] = '0000-00-00';
            }
        }

        // verificamos el codigo y nombre del producto
        // si el codigo del producto (sku) está vacío, lo ponemos como '0'
        if (!obj.sku || obj.sku === '') obj.sku = '0';
        
        // si el nombre del producto está vacío, lo ponemos como '0'
        if (!obj.producto || obj.producto === '') obj.producto = '0';

        // devolvemos el producto ya limpio y ordenado con todos sus campos
        return obj;
    }

/*perfiles de normalisacion*/ 

    // Endpoint absoluto donde están los handlers PHP para perfiles.
    const PROFILE_ENDPOINT = '/php/ingreso_ventas/registro_ventas/normalizar_qr.php';
    
    // Listar perfiles del usuario
    function lista_perfiles() { 
        return new Promise(function(resolve, reject) { 
            obtenerDelServidor('lista_de_perfiles', {}, function(resultado, datos) { // obtenerDelServidor es una funcion que se encarga de obtener los perfiles del usuario
                if (resultado === 'success' && datos && datos.profiles) { // si el resultado es success y datos y datos.profiles
                    // Convertir profiles si viene como string a array
                    let profiles = datos.profiles;
                    if (typeof profiles === 'string') {
                        profiles = cadena_a_matriz(profiles);
                    }
                    resolve(profiles);
                } else {  // si no es success o no hay datos o no hay profiles
                    reject(new Error(datos && datos.msg ? datos.msg : 'Error listando perfiles'));
                }
            });
        });
    }

    // Buscar un perfil conocido por su nombre. Devuelve el perfil completo (obtener_este_perfil) o null
    function encontrar_perfil_por_firma(signature) {
        if (!signature) return Promise.resolve(null);
        return lista_perfiles().then(function(profiles) {
            const match = (profiles || []).find(function(p) {
                return p && p.signature && String(p.signature) === String(signature);
            });
            if (!match) return null;
            // Recuperar perfil completo con el orden que se le asigno
            return obtener_este_perfil(match.name).catch(function(e) {
                console.warn('encontrar_perfil_por_firma: fallo al obtener perfil completo, retornando metadatos', e);
                return match;
            });
        }).catch(function(e) { // si falla, devolver null
            console.warn('encontrar_perfil_por_firma error', e);
            return null;
        });
    }

    // Obtener un perfil por nombre
    function obtener_este_perfil(name) {
        return new Promise(function(resolve, reject) {
            obtenerDelServidor('obtener_el_perfil', {name: name}, function(resultado, datos) {
                if (resultado === 'success' && datos && datos.profile) {
                    const profile = datos.profile;
                    // Convertir campos serializados de vuelta a arrays si vienen como string el orden el mapeo y el payload 
                    if (profile.ord && typeof profile.ord === 'string') {
                        profile.ord = cadena_a_matriz(profile.ord);
                    }
                    if (profile.token_map && typeof profile.token_map === 'string') {
                        profile.token_map = cadena_a_matriz(profile.token_map);
                    }
                    if (profile.payload && typeof profile.payload === 'string') {
                        profile.payload = cadena_a_objeto(profile.payload);
                    }
                    console.log('Perfil cargado desde servidor:', profile);
                    resolve(profile);  // si todo esta bien, devolver el perfil
                } else {
                    reject(new Error(datos && datos.msg ? datos.msg : 'Perfil no encontrado'));
                }
            });
        });
    }

    // Esta función mira un dato y nos dice qué tipo es: Número, Fecha, Texto u Otro
    function computeTokenType(token) {
        
        // Si el dato está vacío o no existe, lo marcamos como "0"
        if (token == null) return 'O';
        
        // Limpiamos espacios en blanco del dato
        const t = String(token).trim();
        
        // Si después de limpiar quedó vacío, es "0"
        if (!t) return 'O';
        
        // Si el dato solo tiene números (0-9), es un "Número"
        if (/^\d+$/.test(t)) return 'N';
        
        // Intentamos ver si es una fecha válida
        try {
            if (validarYFormatearFecha(t)) return 'D'; // "D" de Date (Fecha)
        } catch (e) {} // si falla, seguimos probando otras opciones
        
        // Si tiene letras (incluyendo acentos y ñ), es "Alfabético" (texto)
        if (/[A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(t)) return 'A';
        
        // Si no es ninguno de los anteriores, es "Otro"
        return 'O';
    }

// Esta función crea una "huella digital" del código QR
// Sirve para reconocer QRs que tienen el mismo formato
function huella_digital_qr(tokens) {
    
    // Miramos cada pedazo del QR y determinamos su tipo (N, D, A, O)
    const types = (tokens || []).map(computeTokenType);
    
    // La huella es: cantidad de pedazos + los tipos juntos
    // Ejemplo: un QR con 5 pedazos donde hay Número, Texto, Número, Fecha, Número
    // quedaría como: "5|NANDN"
    return tokens.length + '|' + types.join('');
}

    // Esta función corta el texto del QR en pedazos separados ejemplo: "123,456|789" -> ["123", "456", "789"]
    function separar_datos(raw) {
        
        // Si no hay texto, devolvemos una lista vacía
        if (!raw) return [];
        
        // Cortamos el texto cada vez que encontramos: coma, barra vertical, tabulación o salto de línea
        // Luego quitamos espacios de cada pedazo y eliminamos los que quedaron vacíos
        return String(raw).split(/[,|\t\n]+/).map(function(s) { // si se quieren añadir mas separadores añadirlos aqui
            return s.trim(); // quitar espacios al inicio y final
        }).filter(Boolean);  // eliminar pedazos vacíos
    }

    // Esta función busca si existe un perfil guardado que coincida con el código escaneado
    // Si lo encuentra, ordena los datos automáticamente según ese perfil
    // Esto evita que el usuario tenga que arrastrar los datos manualmente cada vez
    function aplicar_perfil_a_datos(qrCodeMessage, datos) {
        // Cortamos el texto del código en pedazos separados por comas, barras, etc.
        var rawTokens = separar_datos(qrCodeMessage);
        
        // Creamos una "huella digital" del código basada en la cantidad y tipo de cada pedazo
        // Por ejemplo: "8|NNDNNNDA" significa 8 pedazos donde N=número, D=fecha, A=texto
        var sig = huella_digital_qr(rawTokens);
        
        // Buscamos en la base de datos si existe un perfil con esta misma huella
        return encontrar_perfil_por_firma(sig).then(function(matched) {
            
            // Si no encontramos ningún perfil que coincida, avisamos y terminamos
            if (!matched) return { matched: false };
            
            // Sí encontramos un perfil, lo guardamos para usarlo
            var profile = matched;
            
            // Guardamos los pedazos del código para procesarlos
            var tokenArr = rawTokens;
            
            // Buscamos el mapa de tokens del perfil (dice qué pedazo va en qué campo)
            // El mapa puede venir con diferentes nombres según cómo se guardó
            var tokenMap = Array.isArray(profile._tokenMap) ? profile._tokenMap :          // Primero buscamos como _tokenMap
                        (Array.isArray(profile.token_map) ? profile.token_map :          // Si no, como token_map
                        (Array.isArray(profile.tokenMap) ? profile.tokenMap : null));    // Si no, como tokenMap
            
            // Buscamos el orden de columnas del perfil (cómo se muestran en la tabla)
            var order = Array.isArray(profile._order) ? profile._order :    // Primero buscamos como _order
                    (Array.isArray(profile.ord) ? profile.ord : null);   // Si no, como ord

            // Aquí guardaremos los datos ya ordenados según el perfil
            var mapped = {};
            
            // Ahora asignamos cada pedazo del código al campo que le corresponde
            if (tokenMap && Array.isArray(tokenMap) && tokenMap.length > 0) {
                // opcion 1: Usamos el mapa de tokens del perfil
                // Recorremos cada pedazo y lo asignamos al campo que indica el mapa
                tokenArr.forEach(function(t, i) { 
                    var f = tokenMap[i];        // Obtenemos el nombre del campo para este pedazo
                    if (f) mapped[f] = t;       // Si tiene campo asignado, guardamos el valor
                });
            } else if (order && Array.isArray(order) && order.length > 0) {
                // opcion 2: Si no hay mapa, usamos el orden de columnas como guía
                tokenArr.forEach(function(t, i) { 
                    var f = order[i];           // Obtenemos el campo según la posición en el orden
                    if (f) mapped[f] = t;       // Guardamos el valor en ese campo
                });
            } else {
                // opcion 3: Si no hay nada, usamos el orden predeterminado de los campos
                var defaultFields = ['sku','producto','cantidad','lote','fechaFab','fechaVenc','serieIni','serieFin'];
                tokenArr.forEach(function(t, i) { 
                    var f = defaultFields[i];   // Obtenemos el campo según la posición estándar
                    if (f) mapped[f] = t;       // Guardamos el valor en ese campo
                });
            }

            // Ahora copiamos los valores ordenados al arreglo de datos original
            // Usamos un bloque de protección por si algo falla
            try {
                if (mapped.sku) datos[0] = mapped.sku;              // Posición 0: código del producto
                if (mapped.producto) datos[1] = mapped.producto;    // Posición 1: nombre del producto
                if (mapped.cantidad) datos[2] = mapped.cantidad;    // Posición 2: cantidad de unidades
                if (mapped.lote) datos[3] = mapped.lote;            // Posición 3: número de lote
                if (mapped.fechaFab) datos[4] = mapped.fechaFab;    // Posición 4: fecha de fabricación
                if (mapped.fechaVenc) datos[5] = mapped.fechaVenc;  // Posición 5: fecha de vencimiento
                if (mapped.serieIni) datos[6] = mapped.serieIni;    // Posición 6: serie de inicio
                if (mapped.serieFin) datos[7] = mapped.serieFin;    // Posición 7: serie de término
            } catch (e) { 
                // Si algo falla al copiar los datos, lo avisamos en consola pero continuamos
                console.warn('Error aplicando mapeo de perfil al array datos:', e); 
            }

            // Guardamos el perfil encontrado en una variable global
            // Esto permite que otras funciones lo usen más adelante (por ejemplo para mostrar la tabla)
            window._perfilDetectado = profile;
            console.log('Perfil detectado y guardado:', profile.name || 'sin nombre');

            // Si el perfil tiene un orden de columnas, lo mostramos en consola
            // La aplicación visual se hace después en otra función (continuarProcesarQR)
            try {
                if (order && Array.isArray(order)) {
                    console.log('Orden del perfil:', order);
                }
            } catch (e) { 
                // Si algo falla al procesar el orden, lo avisamos pero continuamos
                console.warn('No se pudo procesar el orden del perfil:', e); 
            }

            // Devolvemos un paquete indicando que sí encontramos perfil, junto con sus datos
            return { matched: true, profile: profile, order: order };
            
        }).catch(function(e) {
            // Si ocurre cualquier error durante la búsqueda, lo avisamos y decimos que no hubo coincidencia
            console.warn('aplicar_perfil_a_datos error', e);
            return { matched: false };
        });
    }

    // Detecta si un token de producto termina con un SKU pegado y lo separa.
    // Devuelve { producto, sku } (sku puede ser null si no se detecta)
    function dividir_producto_de_sku(token) {
        if (!token) return { producto: token, sku: null };
        const t = token.trim();
        // patrón típico: 1-6 letras seguido de 2-12 dígitos (ajustable según tus SKUs)
        const skuRegex = /([A-Za-z]{1,6}\d{2,12})$/;
        const match = t.match(skuRegex);
        if (match) {
            const sku = match[1];
            const product = t.slice(0, match.index).replace(/[\.\-_\/\s]+$/,'').trim();
            return { producto: product || '', sku: sku };
        }
        // fallback: separar por último separador (. espacio - /) y comprobar si la parte final es alfanumérica corta
        const parts = t.split(/[\.\s\-\/]+/);
        const last = parts[parts.length - 1];
        if (/^[A-Za-z0-9]{3,12}$/.test(last) && parts.length > 1) {
            const sku = last;
            const product = parts.slice(0, -1).join(' ').trim();
            return { producto: product || '', sku: sku };
        }
        return { producto: t, sku: null };
    }

    // esta funcion sirve para distinguir cantidad vs lote
    function distingir_cantidad_de_lote(s) {
        if (!s) return false;
        if (!/^\d+$/.test(s)) return false;
        // cantidades razonables: 0..9999 (ajustable según tu dominio)
        const n = parseInt(s, 10);
        return n >= 0 && n <= 9999 && String(n).length <= 4;
    }

    function normalizar_cadena_numerica(s) {
        return s ? String(s).replace(/\D+/g, '').replace(/^0+/, '') || '0' : '0';
    }

    // Detecta una cantidad pegada al final del nombre de producto, p.ej. "Producto X 3", "Producto3" o "Producto (3)"
    // Devuelve { producto, cantidad } (cantidad puede ser null)
    function separar_producto_y_cantidad(token) {
        if (!token) return { producto: token, cantidad: null };
        let t = token.trim();

        // 1) patrón entre paréntesis o corchetes: "Producto (3)"
        let m = t.match(/[\(\[]\s*(\d{1,6})\s*[\)\]]\s*$/);
        if (m) {
            const cantidadRaw = normalizar_cadena_numerica(m[1]);
            if (distingir_cantidad_de_lote(cantidadRaw)) {
                const producto = t.slice(0, m.index).trim().replace(/[\.\-_\/\s]+$/,'');
                return { producto: producto || '', cantidad: cantidadRaw };
            }
        }

        // 2) patrón con unidad o sufijo tipo "3kg", "3 unidades", " x3"
        m = t.match(/(?:^|\s|x)\s*(\d{1,6})\s*(kg|g|gr|grs|unidades|uds|u|pz|pzas|l|ml|x)?\s*$/i);
        if (m) {
            const cantidadRaw = normalizar_cadena_numerica(m[1]);
            if (distingir_cantidad_de_lote(cantidadRaw)) {
                const producto = t.slice(0, t.length - m[0].length).trim().replace(/[\.\-_\/\s]+$/,'');
                return { producto: producto || '', cantidad: cantidadRaw };
            }
        }

        // 3) número al final: aceptar sólo si hay un separador explícito (espacio, guión, punto, slash)
        m = t.match(/(\d{1,6})\s*$/);
        if (m) {
            const cantidadRaw = normalizar_cadena_numerica(m[1]);
            // localizar inicio del match para revisar el carácter anterior
            const start = t.length - m[0].length;
            const prevChar = start > 0 ? t.charAt(start - 1) : '';
            // si el carácter anterior NO es un separador aceptado (espacio, -, ., _, /, (, [)
            // entonces probablemente el número está pegado al nombre/sku y no debe tratarse como cantidad
            if (prevChar && !prevChar.match(/[\s\-\._\/\(\[]/)) {
                // considerar como parte del nombre -> no es cantidad
            } else {
                if (distingir_cantidad_de_lote(cantidadRaw)) {
                    const producto = t.slice(0, start).trim().replace(/[\.\-_\/\s]+$/,'');
                    return { producto: producto || '', cantidad: cantidadRaw };
                }
            }
        }

        // 4) patrón "3x Producto" o "3 x Producto" -> cantidad al inicio
        m = t.match(/^(\d{1,6})\s*[xX]\s+(.+)$/);
        if (m) {
            const cantidadRaw = normalizar_cadena_numerica(m[1]);
            if (distingir_cantidad_de_lote(cantidadRaw)) {
                const producto = (m[2] || '').trim().replace(/[\.\-_\/\s]+$/,'');
                return { producto: producto || '', cantidad: cantidadRaw };
            }
        }

        // Si no se detecta cantidad, devolver token original como producto
        return { producto: t, cantidad: null };
    }


// Construir perfil basándose EXCLUSIVAMENTE en la tabla principal y el mapeo de arrastre
function construir_perfil_desde_tabla() {
    var data = window._lastScan;
    if (!data) return null;

    var raw = data.raw || (Array.isArray(data) ? data.join(',') : null);
    if (!raw) return null;

    var tokens = separar_datos(raw);
    var perfil = {};

    // Obtener el orden actual de las columnas en la tabla
    var orden_tabla = [];
    var encabezados = document.querySelectorAll('#tablaProductosHeader th');
    encabezados.forEach(function(th) {
        if (th.dataset.field) orden_tabla.push(th.dataset.field);
    });

    // Obtener los valores actuales escritos en la tabla (payload)
    var payload_tabla = obtener_datos_tabla_principal();

    // Construir el orden del perfil  - inicializar con strings vacíos para guardar el orden
    var token_map = [];
    for (var i = 0; i < tokens.length; i++) {
        token_map.push('');
    }
    var campos_ya_asignados = {};

    // Primero, aplicar los mapeos manuales (los que el usuario arrastró)
    if (window._tokenToFieldMap && Object.keys(window._tokenToFieldMap).length > 0) {
        for (var indice_str in window._tokenToFieldMap) {
            if (window._tokenToFieldMap.hasOwnProperty(indice_str)) {
                var indice = parseInt(indice_str, 10);
                var campo_destino = window._tokenToFieldMap[indice_str];
                
                if (!isNaN(indice) && indice >= 0 && indice < tokens.length && campo_destino) {
                    token_map[indice] = campo_destino;
                    campos_ya_asignados[campo_destino] = true;
                }
            }
        }
        console.log('Mapeos manuales aplicados:', window._tokenToFieldMap);
    }

    // Para los tokens NO mapeados, inferir el campo comparando valores
    tokens.forEach(function(tokenValue, idx) {
        // verificar string vacío, no null
        if (token_map[idx] && token_map[idx] !== '') return; // Ya tiene asignación manual
        
        var valorToken = String(tokenValue).trim();
        
        // Buscar en qué campo de la tabla está este valor
        for (var campo in payload_tabla) {
            if (campo === '_order') continue;
            if (campos_ya_asignados[campo]) continue;
            
            var valorCampo = String(payload_tabla[campo] || '').trim();
            
            // Comparar valores (normalizando ceros a la izquierda, fechas y mayúsculas/minúsculas)
            var tokenNorm = valorToken.replace(/^0+/, '').toLowerCase() || '0';
            var campoNorm = valorCampo.replace(/^0+/, '').toLowerCase() || '0';

            if (valorToken.toLowerCase() === valorCampo.toLowerCase() || tokenNorm === campoNorm) {
                token_map[idx] = campo;
                campos_ya_asignados[campo] = true;
                console.log('Token[' + idx + '] "' + valorToken + '" inferido a campo: ' + campo);
                break;
            }
        }
        
        // Si aún no tiene mapeo, marcar para no perder posición =====
        if (!token_map[idx] || token_map[idx] === '') {
            token_map[idx] = '_unmapped_' + idx;
            console.log('Token[' + idx + '] "' + valorToken + '" sin mapeo, marcado como _unmapped');
        }
    });

    console.log('orden final del perfil:', token_map);

    perfil.payload = payload_tabla;
    perfil._order = orden_tabla;
    perfil._tokenMap = token_map;
    perfil.signature = huella_digital_qr(tokens);

    return perfil;
}


/*aqui definimos las variables locales para el funcionamiento del codigo*/
    var qrScanner = null;
    var escaneoRealizado = false;
    var timeoutFeedback = null; // Variable global para almacenar el identificador del timeout
    var timeoutPistola = null;

// TITULO HTML

    // SIN CODIGO

// TITULO BODY

    // SIN CODIGO

// TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN

    // SIN CODIGO

// TITULO CABECERA

    // SIN CODIGO

// TITULO ESCANEOS

// Modificar la función guardar_ultimo_escaneo para mostrar el QR
function guardar_ultimo_escaneo(data) {
    try {
        window._lastScan = data;
        console.log('Último escaneo guardado:', data);
    } catch (error) {
        console.error('Error guardando último escaneo:', error);
    }
}

// inicia cuando se carga el documento es decir la pagina 
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando...');
    var btn_guardar_normalizado = document.getElementById('btnGuardarNormalizado');
    if (btn_guardar_normalizado) {
        btn_guardar_normalizado.addEventListener('click', function() {
            guardar_normalizado_con_perfil();
        });
    }
    
    // inicializar headers de la tabla
    objetivos_de_arrastre();

    setTimeout(function() {
        procesarQRDesdeURL();
    }, 300);

});

//esto es para mostrar la pantalla de volver a ventas
function mostrar_pantalla_volver_a_ventas(payload) {
    // Verificar si ya existe el modal y eliminarlo
    var modalExistente = document.getElementById('modalVolverVentas');
    if (modalExistente) {
        modalExistente.remove();
    }
    
    // Crear la pantalla
    var modal = document.createElement('div');
    modal.id = 'modalVolverVentas';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;justify-content:center;align-items:center;z-index:9999;';
    
    var contenido = document.createElement('div');
    contenido.style.cssText = 'background:#fff;padding:30px;border-radius:10px;text-align:center;max-width:400px;width:90%;box-shadow:0 4px 20px rgba(0,0,0,0.3);';
    
    // Icono de pregunta
    var icono = document.createElement('div');
    icono.innerHTML = '&#10067;'; // Emoji de pregunta
    icono.style.cssText = 'font-size:50px;margin-bottom:15px;';
    
    // Este es el título de la pantallita de confirmación
    var titulo = document.createElement('h3');
    titulo.textContent = '¿Desea volver a Ingreso de Productos?';
    titulo.style.cssText = 'margin:0 0 15px 0;color:#333;font-size:18px;';
    
    // Este es el contenedor de botones
    var botones = document.createElement('div');
    botones.style.cssText = 'display:flex;gap:15px;justify-content:center;';
    
    // Botón SÍ
    var btnSi = document.createElement('button');
    btnSi.textContent = 'Sí, volver';
    btnSi.style.cssText = 'padding:12px 30px;background:var(--color-boton);color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:16px;font-weight:bold;';
    btnSi.addEventListener('click', function() {
        volverAVentasConDatos(payload);
    });
    btnSi.addEventListener('mouseover', function() { this.style.background = 'var(--color-boton-hover)'; });
    btnSi.addEventListener('mouseout', function() { this.style.background = 'var(--color-boton)'; });
    
    // Botón NO
    var btnNo = document.createElement('button');
    btnNo.textContent = 'No, continuar aquí';
    btnNo.style.cssText = 'padding:12px 30px;background:#dc3545;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:16px;font-weight:bold;';
    btnNo.addEventListener('click', function() {
        modal.remove();
    });
    btnNo.addEventListener('mouseover', function() { this.style.background = '#c82333'; });
    btnNo.addEventListener('mouseout', function() { this.style.background = '#dc3545'; });
    
    // aqui ensamblamos los botones y el contenido de la pantallita de guardar normalizado
    botones.appendChild(btnSi);
    botones.appendChild(btnNo);
    contenido.appendChild(titulo);
    contenido.appendChild(botones);
    modal.appendChild(contenido);
    
    // Cerrar al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    document.body.appendChild(modal);
}

// Esto sirve para volver a la pantalla de ventas con los datos 
// normalizar.js - Reemplaza la función volverAVentasConDatos

function volverAVentasConDatos(payload) {
    // 1. Validaciones del producto actual (el que acabas de arreglar)
    if (!payload.sku || payload.sku === '' || payload.sku === '0') {
        mostrar_mensaje_feedback('El SKU es obligatorio.', 'error');
        return;
    }
    if (!payload.producto || payload.producto === '' || payload.producto === '0') {
        mostrar_mensaje_feedback('El PRODUCTO es obligatorio.', 'error');
        return;
    }
    if (!payload.cantidad || payload.cantidad === '' || payload.cantidad === '0') {
        mostrar_mensaje_feedback('La CANTIDAD es obligatoria.', 'error');
        return;
    }
    if (!payload.lote || payload.lote === '' || payload.lote === '0') {
        mostrar_mensaje_feedback('El LOTE es obligatorio.', 'error');
        return;
    }

    // recuperar datos del cliente y productos anteriores
    // usamos los IDs de los inputs ocultos que estan en el php. es la forma más segura.
    var rutCliente = document.getElementById('data_rut_cliente') ? document.getElementById('data_rut_cliente').value : '';
    var numFactura = document.getElementById('data_num_factura') ? document.getElementById('data_num_factura').value : '';
    var fechaDespacho = document.getElementById('data_fecha_despacho') ? document.getElementById('data_fecha_despacho').value : '';
    var productosPreviosBase64 = document.getElementById('data_productos_previos') ? document.getElementById('data_productos_previos').value : '';

    console.log('Regresando a ventas con:', { rut: rutCliente, factura: numFactura, previos: productosPreviosBase64 ? 'Si' : 'No' });

    // prepara el producto actual nornalizado
    var datosNormalizados = {
        sku: payload.sku || '',
        producto: payload.producto || '',
        cantidad: payload.cantidad || '',
        lote: payload.lote || '',
        fechaFab: payload.fechaFab || '',
        fechaVenc: payload.fechaVenc || '',
        serieIni: payload.serieIni || '',
        serieFin: payload.serieFin || ''
    };

    // Calcular vencimiento si falta (lógica de +5 años)
    if (datosNormalizados.fechaFab && (!datosNormalizados.fechaVenc || datosNormalizados.fechaVenc === '')) {
        var fechaFabParsed = validarYFormatearFecha(datosNormalizados.fechaFab);
        if (fechaFabParsed && fechaFabParsed.fechaFormateada) {
            var baseDate = new Date(fechaFabParsed.fechaFormateada);
            baseDate.setFullYear(baseDate.getFullYear() + 5);
            var y = baseDate.getFullYear();
            var m = String(baseDate.getMonth() + 1).padStart(2, '0');
            var d = String(baseDate.getDate()).padStart(2, '0');
            datosNormalizados.fechaVenc = y + '-' + m + '-' + d;
        }
    }

    // construye la url de regreso
    var urlParams = new URLSearchParams();

    // Datos Cliente
    urlParams.set('rut_cliente', rutCliente);
    urlParams.set('num_factura', numFactura);
    urlParams.set('fecha_despacho', fechaDespacho);

    // Producto Nuevo (Normalizado)
    var cadenaDatos = matriz_a_cadena(datosNormalizados);
    urlParams.set('datos_normalizados', btoa(encodeURIComponent(cadenaDatos)));

    // Productos Antiguos (Mantener historial)
    if (productosPreviosBase64) {
        urlParams.set('productos_previos', productosPreviosBase64);
    }

    // Bandera para que ventas.js sepa que debe procesar esto
    urlParams.set('desde_normalizar', '1');

    // redirecciona
    var urlDestino = '/php/ingreso_ventas/renderizar_menu.php?pagina=ventas&' + urlParams.toString();
    window.location.href = urlDestino;
}

// inicializar arrastre esto es para ordenar las columnas 
function objetivos_de_arrastre() {
    asegurar_campos_encabezado();
    var headers = document.querySelectorAll('#tablaProductosHeader thead th');
    
    headers.forEach(function(th) {
        if (th.dataset.dropInit) return;
        
        th.addEventListener('dragover', function(e) {
            e.preventDefault();
            try { e.dataTransfer.dropEffect = 'copy'; } catch (err) {}
        });
        
        th.addEventListener('drop', function(e) {
            e.preventDefault();
            var token = e.dataTransfer.getData('text/plain');
            if (!token) return;
            
            var field = th.dataset.field;
            if (field) {
                // Actualizar la celda correspondiente en la tabla
                var tabla = document.getElementById('tablaProductos');
                if (tabla) {
                    var celda = tabla.querySelector('td[data-field="' + field + '"]');
                    if (celda) {
                        celda.textContent = token;
                        mostrar_mensaje_feedback('Token asignado a ' + field, 'exitoso');
                    }
                }
            }
        });
        
        th.dataset.dropInit = '1';
    });
}
    // funcion para detener el escaner apenas se escanea el qr
    function detenerScanner() {
        //si no hay escaner activo no hace nada
        if (!qrScanner) {
            console.warn("No hay escaner activo para detener");
            return;
        }
        // detener el escaner cuando se escanea el qr
        qrScanner.stop().then(function() {
            console.log("Escaner detenido por usuario");
            //limpiar el escaner para que cuando escanemos otro qr no se repita el anterior
            return qrScanner.clear();
        }).then(function() {
            //ocultar el lector de qr
            qrScanner = null;
            escaneoRealizado = false;
            document.getElementById("lectorQR").style.display = "none";
        }).catch(function(err) {
            console.error("Error al detener escaner por usuario:", err);
        });
    }
    
// Configura las celdas de la tabla como receptore de arrastres      
function destinos_de_caida() {
    var tabla_productos = document.getElementById('tablaProductos');
    if (!tabla_productos) return;

    var celdas = tabla_productos.querySelectorAll('td');

    celdas.forEach(function(td) {
        // Evitar configurar múltiples veces
        if (td.dataset.dropConfigured) return;
        td.dataset.dropConfigured = '1';

        // Permitir drop (arrastrar sobre la celda)
        td.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            this.style.backgroundColor = '#e6f7ff';
            this.style.outline = '2px dashed #1890ff';
        });

        // Al salir del área de la celda
        td.addEventListener('dragleave', function(e) {
            this.style.backgroundColor = '';
            this.style.outline = '';
        });

        // Al soltar el elemento (drop)
        td.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Restaurar estilo visual
            this.style.backgroundColor = '';
            this.style.outline = '';

            // Obtener los datos transferidos
            var valor_arrastrado = e.dataTransfer.getData('text/plain');
            var indice_token_str = e.dataTransfer.getData('tokenIndex'); 
            var origen = e.dataTransfer.getData('source');
            var campo_destino = this.dataset.field; // El campo de la celda donde soltaste

            if (valor_arrastrado) {
                var valorLimpio = valor_arrastrado.trim();
                
                // 1. Escribir el valor en la celda donde soltaste
                this.textContent = valorLimpio;

                // === LÓGICA DE MAPEO PARA PERFIL ===
                if ((origen === 'raw_token' || origen === 'tabla_crudos') && indice_token_str !== null && indice_token_str !== '') {
                    var indice = parseInt(indice_token_str, 10);
                    if (!window._tokenToFieldMap) window._tokenToFieldMap = {};
                    window._tokenToFieldMap[parseInt(indice, 10)] = campo_destino;
                    console.log('Asignación guardada en mapa: Token [' + indice + '] -> ' + campo_destino);
                }

                if (campo_destino === 'fechaFab') {
                    console.log("Soltado en fecha de fabricación. Valor:", valorLimpio);
                    
                    // Usamos la función auxiliar para entender la fecha que arrastraste
                    var fechaObj = validarYFormatearFecha(valorLimpio);

                    if (fechaObj && fechaObj.fechaFormateada) {
                        // Crear objeto fecha
                        var date = new Date(fechaObj.fechaFormateada);
                        // Sumar 5 años
                        date.setFullYear(date.getFullYear() + 5);

                        // Formatear la nueva fecha (YYYY-MM-DD)
                        var y = date.getFullYear();
                        var m = String(date.getMonth() + 1).padStart(2, '0');
                        var d = String(date.getDate()).padStart(2, '0');
                        var fechaVencCalculada = y + '-' + m + '-' + d;

                        // Buscar la celda vecina "fechaVenc" en la misma fila
                        var fila = this.parentElement;
                        var celdaVenc = fila.querySelector('td[data-field="fechaVenc"]');

                        if (celdaVenc) {
                            // Escribir la fecha calculada automáticamente
                            celdaVenc.textContent = fechaVencCalculada;
                            
                            // Efecto visual verde para que sepas que cambió
                            celdaVenc.style.backgroundColor = '#d4edda';
                            setTimeout(function(){ celdaVenc.style.backgroundColor = ''; }, 1000);
                            
                            console.log("--> Fecha vencimiento calculada y aplicada:", fechaVencCalculada);
                            
                            // Actualizar memoria interna
                            if (window._lastScan) {
                                window._lastScan.fechaVenc = fechaVencCalculada;
                            }
                        }
                    } else {
                        console.warn("La fecha arrastrada no es válida para calcular vencimiento");
                    }
                }

                var self = this;
                setTimeout(function() { self.style.backgroundColor = ''; }, 500);

                console.log('Drop en tabla: "' + valorLimpio + '" -> ' + (campo_destino || 'sin_campo'));
                
                if (typeof mostrar_mensaje_feedback === 'function') {
                    mostrar_mensaje_feedback('Valor asignado a ' + (campo_destino || 'celda'));
                }
            }
        });
    });
}
// Configura las celdas de la tabla para ser editables y arrastrables
function sincronizacion_arrastre_tabla() {
    var tabla = document.getElementById('tablaProductos');
    if (!tabla) return;
    
    var celdas = tabla.querySelectorAll('td[data-field]');
    
    celdas.forEach(function(td) {
        var fieldName = td.dataset.field;
        if (!fieldName) return;
        
        // hacer la celda editable
        if (!td.hasAttribute('contenteditable')) {
            td.setAttribute('contenteditable', 'true');
            td.style.cursor = 'text';
            
            // Prevenir salto de línea con Enter
            td.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.blur();
                }
            });
        }
        
        // hacer la celda arrastrable
        if (!td.dataset.dragConfigured) {
            td.draggable = true;
            td.dataset.dragConfigured = '1';
            
            td.addEventListener('dragstart', function(e) {
                var valor = this.textContent.trim();
                var field = this.dataset.field;
                
                e.dataTransfer.setData('text/plain', valor);
                e.dataTransfer.setData('sourceField', field);
                e.dataTransfer.setData('source', 'tabla_principal');
                e.dataTransfer.effectAllowed = 'copy';
                
                this.style.opacity = '0.5';
                console.log('Arrastrando desde tabla principal:', field, '=', valor);
            });
            
            td.addEventListener('dragend', function() {
                this.style.opacity = '1';
            });
        }
    });
    
    console.log('Celdas de tabla configuradas para edición y arrastre');
}

// Observa cambios en fechaFab de la tabla principal y recalcula fechaVenc si es necesario
function autocalcular_fecha_vencimiento() {
    var tabla = document.getElementById('tablaProductos');
    if (!tabla) return;
    
    // Crear un MutationObserver para detectar cambios en las celdas
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'characterData' || mutation.type === 'childList') {
                var target = mutation.target;
                var celda = target.nodeType === 3 ? target.parentElement : target; // Si es texto, obtener el padre
                
                if (celda && celda.dataset && celda.dataset.field === 'fechaFab') {
                    recalcularFechaVencSiNecesario();
                }
            }
        });
    });
    
    // Observar cambios en el tbody
    observer.observe(tabla, {
        childList: true,
        subtree: true,
        characterData: true
    });
    
    console.log('Observer de fechaFab configurado');
}

// Recalcula fechaVenc si está vacía o es menor a fechaFab
function recalcularFechaVencSiNecesario() {
    var tabla = document.getElementById('tablaProductos');
    if (!tabla) return;
    
    var celdaFechaFab = tabla.querySelector('td[data-field="fechaFab"]');
    var celdaFechaVenc = tabla.querySelector('td[data-field="fechaVenc"]');
    
    if (!celdaFechaFab || !celdaFechaVenc) return;
    
    var valorFechaFab = (celdaFechaFab.textContent || '').trim();
    var valorFechaVenc = (celdaFechaVenc.textContent || '').trim();
    
    // Validar fechaFab
    var parsedFab = validarYFormatearFecha(valorFechaFab);
    if (!parsedFab || !parsedFab.fechaFormateada || valorFechaFab === '0000-00-00') {
        return; // No hay fecha de fabricación válida
    }
    
    var fechaFabDate = new Date(parsedFab.fechaFormateada);
    var necesitaRecalcular = false;
    
    // Verificar si fechaVenc necesita recalcularse
    if (necesitaRecalcular) {
        // 1. Sumar 5 años a la fecha de fabricación
        var nuevaFecha = new Date(fechaFabDate);
        nuevaFecha.setFullYear(nuevaFecha.getFullYear() + 5);
        
        // 2. Formatear la nueva fecha a YYYY-MM-DD
        var y = nuevaFecha.getFullYear();
        var m = String(nuevaFecha.getMonth() + 1).padStart(2, '0');
        var d = String(nuevaFecha.getDate()).padStart(2, '0');
        var fechaFinal = y + '-' + m + '-' + d;
        
        // 3. Escribir el resultado automáticamente en la celda
        celdaFechaVenc.textContent = fechaFinal;
        
        console.log('Fecha vencimiento recalculada a +5 años:', fechaFinal);
        
        // 4. Actualizar la memoria interna para que se guarde bien
        if (window._lastScan) {
            window._lastScan.fechaVenc = fechaFinal;
        }
    }
}

// Devuelve el payload en el orden actual definido por el usuario en el contenedor 
// Función para extraer los datos directamente de la tabla principal (#tablaProductos)
// Reemplaza la dependencia del panel lateral
function obtener_datos_tabla_principal() {
    var tabla = document.getElementById('tablaProductos');
    var fila = tabla.querySelector('tr'); // Asumimos que hay una fila activa de edición
    
    // Si no hay fila, retornamos un objeto vacío o null
    if (!fila) return {};

    var payload = {};
    var celdas = fila.querySelectorAll('td');

    // Recorremos las celdas de la fila
    celdas.forEach(function(celda) {
        var campo = celda.dataset.field; // Obtenemos el nombre del campo (sku, producto, etc.)
        if (campo) {
            // Guardamos el texto limpio de la celda en el objeto
            payload[campo] = (celda.textContent || '').trim();
        }
    });

    // Añadimos el orden visual actual basándonos en los encabezados de la tabla
    var orden_actual = [];
    var encabezados = document.querySelectorAll('#tablaProductosHeader th');
    encabezados.forEach(function(th) {
        if (th.dataset.field) {
            orden_actual.push(th.dataset.field);
        }
    });
    
    payload._order = orden_actual;

    return payload;
}

    // Asegura que las columnas del header tengan data-field asignados (primera vez)
    function asegurar_campos_encabezado() {
        const header = document.querySelector('#tablaProductosHeader thead tr');
        if (!header) return;
        const ths = Array.from(header.children);
        const defaultOrder = ['sku', 'producto', 'cantidad', 'lote', 'fechaFab', 'fechaVenc', 'serieIni', 'serieFin'];
        ths.forEach(function(th, idx) {
            if (!th.dataset || !th.dataset.field) {
                const field = defaultOrder[idx] || 'col_' + idx;
                th.dataset.field = field;
            }
        });
    }

    // Reordena el encabezado de la tabla según el 'order' 
    function aplicar_orden_al_encabezado(order) {
        const theadRow = document.querySelector('#tablaProductosHeader thead tr');
        if (!theadRow || !Array.isArray(order)) return;
        asegurar_campos_encabezado();
        const currentThs = Array.from(theadRow.querySelectorAll('th'));
        const map = {};
        currentThs.forEach(function(th) { map[th.dataset.field] = th; });

        // Títulos por defecto para los campos
        const titles = {
            sku: 'SKU', producto: 'PRODUCTO', cantidad: 'CANTIDAD', lote: 'LOTE', fechaFab: 'FECHA DE FABRICACION', fechaVenc: 'FECHA DE VENCIMIENTO', serieIni: 'SERIE DE INICIO', serieFin: 'SERIE DE TERMINO'
        };

        // Limpia el encabezado actual
        while (theadRow.firstChild) theadRow.removeChild(theadRow.firstChild);

        // Apende las ordenadas en el orden solicitado
        order.forEach(function(field) {
            if (map[field]) {
                theadRow.appendChild(map[field]);
            } else {
                const th = document.createElement('th');
                th.dataset.field = field;
                th.textContent = titles[field] || field;
                theadRow.appendChild(th);
            }
        });

        // Apende los que sobraron al final para mantener el orden original
        Object.keys(map).forEach(function(field) {
            if (!order.includes(field)) theadRow.appendChild(map[field]);
        });
    }

    function iniciarPistola() {
        const inputQR = document.getElementById('inputQR');
        if (inputQR) {
            inputQR.focus(); // Enfocar el campo oculto
            mostrar_mensaje_feedback("Modo pistola activado. Escanee el codigo QR con la pistola.");
            console.log('Pistola QR activada, campo enfocado');
        } else {
            console.error('No se encontro el campo inputQR');
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
        var fixed = str.replace(/[)\'=]/g, function(ch) { return mapEspecifico[ch] || ch; });

        // (Opcional) Normalización adicional 

        const mapUSaES = {
            ';': 'n', ':': 'N', "'": 'a', '"': 'e',
            '[': '', ']': '+', '{': '', '}': '*',
            '\\': 'c', '|': 'C'
        };
        fixed = fixed.replace(/[;:"[\]{}\\|]/g, function(ch) { return mapUSaES[ch] || ch; });

        return fixed;
    }

    function filtros(qrCodeMessage) {

        console.log("Datos antes del filtro:", qrCodeMessage);
        // Normalizar los datos del QR
        var normalizado = qrCodeMessage
            .replace(/\r?\n+/g, ',') // Reemplazar saltos de línea por comas
            .replace(/[\u002C\uFF0C\u3001]/g, ',') // Normalizar comas
            .replace(/[\u00A0\u2007\u202F]/g, ' ') // Normalizar espacios no estándar
            .replace(/\s*,\s*/g, ',') // Eliminar espacios alrededor de comas
            .replace(/�C/g, ',') // Reemplazar caracteres no válidos por comas
            // 4. \u0081C  coma, pero no si es \u0081C0 (queremos preservar C0)
            .replace(/\u0081C(?!0)/g, ',')
            // 5. Solo transformar C que esté **entre dígitos** en coma
            .replace(/(\d)C(\d)/g, '$1,$2')
            // 6. Fecha con cero extra: 004-2025  04/2025
            .replace(/(\d)0(\d{2})-(\d{4})/g, '$1$2-$3');

        var datos = normalizado.split(',');

        // Caso CSV/planilla común: si tenemos al menos 7 tokens y coinciden con el formato
        // Mapear directamente para evitar heurísticas que puedan desplazar/romper campos.
        if (Array.isArray(datos) && datos.length >= 7) {
            const a0 = (datos[0] || '').toString().trim();
            const a1 = (datos[1] || '').toString().trim();
            const a2 = (datos[2] || '').toString().trim();
            const a3 = (datos[3] || '').toString().trim();
            const a4 = (datos[4] || '').toString().trim();
            const a5 = (datos[5] || '').toString().trim();
            const a6 = (datos[6] || '').toString().trim();

            // Detectores simples: producto contiene letras, cantidad y lote son numéricos,
            // y la fecha parece una fecha (YYYY-MM-DD o con / o con 6-8 dígitos).
            const productoLooksLikeText = /[A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(a1);
            const cantidadIsNum = /^\d+$/.test(a2);
            const loteIsNum = /^\d+$/.test(a3);
            const fechaLooksLike = /\d{4}-\d{2}-\d{2}|\d{1,2}\/\d{1,2}\/\d{4}|^\d{6,8}$/.test(a4);

            if (productoLooksLikeText && cantidadIsNum && loteIsNum && fechaLooksLike) {
                // Formatear fecha si es posible
                var fechaForm = a4;
                try {
                    const vf = validarYFormatearFecha(a4);
                    if (vf && vf.fechaFormateada) fechaForm = vf.fechaFormateada;
                } catch (e) { /* ignore */ }

                // Construir arreglo final con 8 posiciones (llenamos con vacíos si faltan)
                const final = [a0, a1, normalizar_cadena_numerica(a2), normalizar_cadena_numerica(a3), fechaForm, a5 || '', a6 || '', ''];
                return final;
            }
        }

        console.log("Caracteres especiales remplazados:", datos);


        // Validar y corregir los datos
        var campos = [];
        var errores = [];

        try {
            // Validar que existan los campos necesarios
            if (!datos[0] || !datos[1] || !datos[2] || !datos[3]) {
                if (datos.length <= 1) {
                    // QR muy corto o código de barras - mostrar mensaje y retornar vacío
                    mostrar_mensaje_feedback("Escaneo no reconocido - revisa los datos manualmente.", "alerta");
                    return ['', '', '', '', '', '', '', ''];
                }
                errores.push("Faltan campos obligatorios en los datos del QR. Porfavor vuelva a escanear el codigo QR.");
                // Mostrar feedback, pero intentar devolver lo que haya para revisar/normalizar
                mostrar_mensaje_feedback("Errores en los datos del QR: " + errores.join(", "), "error");
                console.error("Faltan campos obligatorios:", datos);
                // Continuar y construir campos parciales
            }


            // Validar SKU (primer campo, debe ser numérico)

            if (/[a-zA-Z0-9]/.test(datos[0])) {
                var sku = datos[0].trim();
                if (sku.length > 20) {
                    const base = sku.slice(0, 20); // Los primeros 20 dígitos
                    const resto = sku.slice(20);  // El resto del SKU

                    // Verificar si el resto es una repetición del base
                    const repetido = resto.startsWith(base);
                    if (repetido) {
                        console.warn('El SKU contiene una repeticion: ' + sku + '. Se corta a: ' + base);
                        sku = base; // Cortar el SKU a los primeros 8 dígitos
                    }
                }
                campos.push(sku);
            } else {
                // si es que el sku no es valido, se toma el valor bruto
                const skuFallback = (datos[0] || '').trim();
                campos.push(skuFallback);
                errores.push("El SKU no es valido.");
                console.warn("SKU invalido, usando fallback:", skuFallback);
            }
            // Validar Producto (segundo campo). Intentar detectar SKU o cantidad pegados al final.
            const prodCandidateRaw = (datos[1] || '').trim();
            // Separar SKU si viene pegado al final del producto
            const splitSku = dividir_producto_de_sku(prodCandidateRaw);
            var productText = splitSku.producto || prodCandidateRaw;

            // si el token en posición 1 es numérico (no parece producto)
            // y el token en posición 2 existe y contiene letras, entonces probablemente
            // el verdadero nombre de producto está en datos[2] y datos[1] es lote/sku.
            // En ese caso, usaremos datos[2] como producto y ajustaremos más abajo el inicio
            // del procesamiento del resto de tokens para no volver a concatenarlo.
            var _tokensStartIndex = 2; // por defecto comenzamos a procesar desde datos[2]
            const nextToken = (datos[2] || '').toString().trim();
            if (!/[A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(prodCandidateRaw) && /[A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(nextToken)) {
                // adoptar datos[2] como nombre de producto
                productText = nextToken;
                _tokensStartIndex = 3; // saltar el token usado como producto
                // si splitSku detectó un sku en prodCandidateRaw y el SKU principal está vacío, conservarlo
                // (esto se maneja más abajo al asignar campos[0])
            }
            // Si detectamos SKU y el SKU principal (campos[0]) está vacío o inválido, asignarlo
            if (splitSku.sku) {
                const existingSku = (campos[0] || '').toString().trim();
                if (!existingSku || existingSku === '' || existingSku === prodCandidateRaw) {
                    campos[0] = splitSku.sku;
                }
            }

            // Separar cantidad si viene pegada al final del producto
            const splitQty = separar_producto_y_cantidad(productText);
            if (splitQty.cantidad) {
                // Sólo asignar cantidad si aún no existe una cantidad detectada
                if (!campos[2] || campos[2] === '') {
                    campos[2] = splitQty.cantidad;
                }
                productText = splitQty.producto || '';
            }

            // Validar que el producto tenga letras; si no, usar fallback
            if (/[a-zA-ZÁÉÍÓÚáéíóúÑñ]/.test(productText)) {
                campos.push(productText.trim());
            } else {
                const productoFallback = productText.trim();
                campos.push(productoFallback);
                errores.push("El producto no es valido.");
                console.warn("Producto invalido, usando fallback:", productoFallback);
            }

        // Procesar el resto de tokens sin mutar el arreglo original para evitar desplazamientos
        // tokensRest contendrá los términos a partir del índice _tokensStartIndex
        const tokensRest = (datos.slice(_tokensStartIndex) || []).map(function(t) { return (t || '').toString().trim(); }).filter(function(t) { return t !== ''; });

            // Pistas heurísticas: cantidad (cantidad) suele ser un número seguido por otro número (serie inicio/fin o lote),
            // lote suele ser numérico, fechas contienen '/' o '-' o son 6/8 dígitos, series son numéricas.
            var idxRest = 0;
            while (idxRest < tokensRest.length) {
                const termino = tokensRest[idxRest];

                

                // Si parece una fecha (contiene / o - o es una cadena larga de dígitos), tratar como fecha
                if (/[-\/]/.test(termino) || /^\d{6,8}$/.test(termino) || /^\d{4}$/.test(termino)) {
                    // validarYFormatearFecha está declarada más abajo y devuelve objeto con fechaFormateada
                    const vf = validarYFormatearFecha(termino);
                    if (vf && vf.fechaFormateada) {
                        if (!campos[4] || campos[4] === '') campos[4] = vf.fechaFormateada;
                        idxRest++;
                        continue;
                    }
                }

                // Si es solo dígitos
                if (/^\d+$/.test(termino)) {
                    // Si no hay cantidad y el siguiente token también es numérico, este término probablemente sea la cantidad
                    if ((!campos[2] || campos[2] === '') && tokensRest[idxRest + 1] && /^\d+$/.test(tokensRest[idxRest + 1])) {
                        campos[2] = termino; // cantidad
                        idxRest++;
                        continue;
                    }

                    // Si no hay lote asignado, asignar este número como lote (fallback razonable)
                    if ((!campos[3] || campos[3] === '') && termino.length >= 1 && termino.length <= 20) {
                        campos[3] = termino;
                        idxRest++;
                        continue;
                    }

                    // Si llegamos aquí, puede ser serie inicio/fin
                    if ((!campos[5] || campos[5] === '') ) {
                        campos[5] = termino;
                        idxRest++;
                        continue;
                    } else if ((!campos[6] || campos[6] === '')) {
                        campos[6] = termino;
                        idxRest++;
                        continue;
                    } else {
                        // Multiples tokens numéricos inesperados; guardar en errores pero no mutar
                        errores.push('Token numerico extra detectado: ' + termino);
                        idxRest++;
                        continue;
                    }
                }

                // Si no cumple ninguna heurística, marcar como inválido y continuar
                errores.push('Termino invalido detectado: ' + termino);
                console.warn("Termino invalido detectado y saltado:", termino);
                idxRest++;
            }



            // Validar Lote (ahora es el cuarto campo: index 3)
            // Si tokensRest ya asignó campos[3] lo respetamos; si no, usamos el valor bruto datos[2] como fallback
            if (!campos[3] || campos[3] === '') {
                if (/^\d+$/.test(datos[2] || '')) {
                    campos[3] = (datos[2] || '').trim();
                } else {
                    const loteFallback = (datos[2] || '').trim();
                    campos[3] = loteFallback;
                    if (loteFallback) errores.push("El lote no es valido.");
                    console.warn("Lote invalido o no detectado, usando fallback:", loteFallback);
                }
            }

            // Validar Fecha de Fabricación (cuarto campo, debe ser una fecha válida)
            // Si já fue detectada en tokensRest (campos[4]) la respetamos; si no, intentamos normalizar datos[3]
            var fechaFabricacion = (datos[4] !== null && datos[4] !== undefined ? datos[4] : '').toString().trim();

            // Si el cuarto término no parece una fecha válida, buscar en los demás términos cualquier token con formato de fecha

            // Si la fecha está solo en dígitos, aplicar normalizaciones suaves pero no fallar aquí:
            if (/^\d+$/.test(fechaFabricacion)) {
                if (fechaFabricacion.length === 8) {
                    // DDMMYYYY -> DD/MM/YYYY
                    fechaFabricacion = fechaFabricacion.slice(0, 2) + '/' + fechaFabricacion.slice(2, 4) + '/' + fechaFabricacion.slice(4);
                } else if (fechaFabricacion.length === 6) {
                    // MMYYYY -> 01/MM/YYYY
                    fechaFabricacion = '01/' + fechaFabricacion.slice(0, 2) + '/' + fechaFabricacion.slice(2);
                } else if (fechaFabricacion.length === 4) {
                    // YYYY -> 01/01/YYYY
                    fechaFabricacion = '01/01/' + fechaFabricacion;
                } // otros largos se mantienen sin lanzar error
            }

            // Pasar la fecha (posible candidata) para su validación posterior (ahora index 4)
            // Si ya fue detectada en tokensRest (campos[4]) la respetamos; si no, intentamos validar/formatear la candidata
            if (!campos[4] || campos[4] === '') {
                const vf2 = validarYFormatearFecha(fechaFabricacion);
                if (vf2 && vf2.fechaFormateada) {
                    campos[4] = vf2.fechaFormateada;
                } else {
                    campos[4] = fechaFabricacion;
                }
            }

            // Validar Serie de Inicio (ahora index 5) - no sobrescribir si ya fue detectada por tokensRest
            if ((!campos[5] || campos[5] === '') && datos[4]) {
                campos[5] = (/^[0-9]+$/.test(datos[4]) ? datos[4].trim() : (datos[4] || '').trim());
            }

            // Validar Serie de Fin (ahora index 6) - no sobrescribir si ya fue detectada por tokensRest
            if ((!campos[6] || campos[6] === '') && datos[5]) {
                campos[6] = (/^[0-9]+$/.test(datos[5]) ? datos[5].trim() : (datos[5] || '').trim());
            }

            // Antes de devolver, si hubo errores, mostrar feedback pero devolver campos parciales para que el flujo
            // permita revisar y normalizar.
            if (errores.length > 0) {
                try { mostrar_mensaje_feedback("Errores en los datos del QR: " + errores.join(", "), "error"); } catch (e) { /* ignore */ }
                console.log("Campos procesados (parciales):", campos);
                console.log("Errores detectados:", errores.length > 0 ? errores : "Ninguno");
            }

        // Asegurar formato final con 8 elementos: sku, producto, cantidad, lote, fechaFab, serieIni, serieFin
        const final = [ (campos[0]||''), (campos[1]||''), (campos[2]||''), (campos[3]||''), (campos[4]||''), (campos[5]||''), (campos[6]||''), (campos[7]||'') ];
        return final;
        } catch (error) {
            // En caso de excepciones inesperadas, loguear y devolver datos parciales
            console.error('filtros error inesperado', error);
            try { mostrar_mensaje_feedback('Error procesando datos del QR', 'error'); } catch (e) { }
            return campos;
        }

    }

// Muestra los datos crudos del QR en la tabla superior con las mismas columnas que la tabla principal
function mostrarDatosCrudos(qrCodeMessage) {
    var tablaCrudos = document.getElementById('tablaDatosCrudos');
    var headerCrudos = document.getElementById('headerCrudos');
    var bodyCrudos = document.getElementById('bodyCrudos');
    
    if (!tablaCrudos || !headerCrudos || !bodyCrudos) return;
    
    // Tokenizar el QR crudo
    var tokens = String(qrCodeMessage).split(/[,|\t\n]+/).map(function(s) { 
        return s.trim(); 
    }).filter(Boolean);
    
    // Columnas estándar (mismas que la tabla principal)
    var columnasEstandar = [
        { field: 'sku', titulo: 'SKU' },
        { field: 'producto', titulo: 'PRODUCTO' },
        { field: 'cantidad', titulo: 'CANTIDAD' },
        { field: 'lote', titulo: 'LOTE' },
        { field: 'fechaFab', titulo: 'FECHA DE FABRICACION' },
        { field: 'serieIni', titulo: 'SERIE DE INICIO' },
        { field: 'serieFin', titulo: 'SERIE DE TERMINO' }
    ];
    
    // Limpiar tabla anterior
    headerCrudos.innerHTML = '';
    bodyCrudos.innerHTML = '';
    
    // Crear encabezados con las mismas columnas que la tabla principal
    columnasEstandar.forEach(function(col) {
        var th = document.createElement('th');
        th.textContent = col.titulo;
        th.dataset.field = col.field;
        th.style.minWidth = '100px';
        headerCrudos.appendChild(th);
    });
    
    // Crear fila con los datos crudos, rellenando con '0' o '0000-00-00' donde falten
    var fila = document.createElement('tr');
    
    columnasEstandar.forEach(function(col, index) {
        var td = document.createElement('td');
        td.dataset.field = col.field;
        td.dataset.tokenIndex = index;
        
        // Obtener valor del token o usar valor por defecto
        var valorToken = (tokens[index] !== undefined && tokens[index] !== '') ? tokens[index] : null;
        
        // Asignar valor por defecto según el tipo de campo
        if (valorToken === null || valorToken === '') {
            if (col.field === 'fechaVenc') {
                td.textContent = ''; // Fecha vencimiento vacía, no mostrar 0000-00-00
            } else if (col.field === 'fechaFab') {
                td.textContent = '0000-00-00';
            } else {
                td.textContent = '0';
            }
        } else {
            td.textContent = valorToken;
        }
        
        td.title = col.titulo + ': ' + td.textContent;
        td.draggable = true;
        td.style.cursor = 'grab';
        
        // Hacer arrastrables los tokens crudos
        td.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.textContent.trim());
            e.dataTransfer.setData('tokenIndex', this.dataset.tokenIndex);
            e.dataTransfer.setData('sourceField', this.dataset.field);
            e.dataTransfer.setData('source', 'tabla_crudos');
            e.dataTransfer.effectAllowed = 'copy';
            this.style.opacity = '0.5';
        });
        
        td.addEventListener('dragend', function() {
            this.style.opacity = '1';
        });
        
        fila.appendChild(td);
    });
    
    bodyCrudos.appendChild(fila);
    tablaCrudos.style.display = 'table';
}

    // Evento para escuchar cuando la pistola QR envía datos
    document.addEventListener('DOMContentLoaded', function () {
        var inputQR = document.getElementById('inputQR');

        if (inputQR) {
            // Escuchar el evento "input" o "keyup" para detectar cambios en el campo
            inputQR.addEventListener('input', function () {
                clearTimeout(timeoutPistola); // Limpiar el temporizador anterior
                var self = this;

                // Configurar un nuevo temporizador
                timeoutPistola = setTimeout(function() {
                    var qrData = self.value.trim(); // Obtener el valor del campo

                    if (qrData) {
                        console.log('Datos recibidos de pistola:', qrData);
                        // aqui  quiero poner el codigo que me diste 

                        // Normalizar entrada de la pistola usando helper y correcciones adicionales
                        try {
                            // Usa la función general de normalización definida arriba
                            var qrDataProcesado = normalizarEntradaPistola(qrData);

                            // Reemplazar guiones bajos por espacios (muchas pistolas envían '_' en vez de espacio)
                            qrDataProcesado = qrDataProcesado.replace(/_+/g, ' ');

                            // Reemplazar '-' por '/' cuando está entre caracteres alfanuméricos (ej: M-L -> M/L)
                            qrDataProcesado = qrDataProcesado.replace(/(\w)-(\w)/g, '$1/$2');

                            // Mapear caracteres comunes de teclado US->ES si quedan
                            qrDataProcesado = qrDataProcesado.replace(/[;:'"[\]{}\\|]/g, function (match) {
                                var mapa = { ';': 'n', ':': 'N', "'": 'a', '"': 'e', '[': '', ']': '+', '{': '', '}': '*', '\\': 'c', '|': 'C' };
                                return mapa[match] || match;
                            });

                            console.log('Datos pistola normalizados:', qrDataProcesado);
                            procesarQR(qrDataProcesado); // Procesar los datos normalizados
                        } catch (e) {
                            console.error('Error normalizando datos de pistola:', e);
                            procesarQR(qrData); // Fallback: procesar crudo si falla la normalización
                        }

                        self.value = ''; // Limpiar el campo para el próximo escaneo
                    }
                }, 600); // Esperar 300 ms después de que la pistola termine de escribir
            });
        }

        autocalcular_fecha_vencimiento();

    });

    // Función para mostrar mensajes de feedback
    function mostrar_mensaje_feedback(message, type) {
        if (type === undefined) type = 'exitoso';
        var feedbackDiv = document.getElementById('mensaje_feedback');
        feedbackDiv.style.display = 'none';

        feedbackDiv.textContent = message;
        feedbackDiv.className = ''; // Limpiar clases existentes
        feedbackDiv.classList.add(type); // Añadir clase de tipo (exitoso, error, alerta)
        feedbackDiv.style.display = 'block';

        // Cancelar el timeout anterior si existe
        if (timeoutFeedback) {
            clearTimeout(timeoutFeedback);
        }

        // Configurar un nuevo timeout
        timeoutFeedback = setTimeout(function() {
            feedbackDiv.classList.add('oculto'); // Aplicar la clase para desvanecer
            setTimeout(function() {
                feedbackDiv.style.display = 'none'; // Ocultar completamente después de la transición
            }, 500); // Tiempo de la transición (0.5s)
        }, 3000); // Tiempo antes de ocultar (4s)
    }

    // Función para validar y formatear fecha
    function validarYFormatearFecha(fechaStr) {
        if (fechaStr == null) return null;
        var original = String(fechaStr).trim();
        if (!original) return null;

        // normalizar separadores y palabras comunes
        var s = original.replace(/\u00A0/g, ' ').trim();
        s = s.replace(/[._\s\\\-\u2013]+/g, '/').replace(/\/+/g, '/').replace(/\s+de\s+/gi, '/');

        var toInt = function(v) { return parseInt(String(v).replace(/^0+/, ''), 10) || 0; };
        var isValid = function(y, m, d) {
            y = Number(y); m = Number(m); d = Number(d);
            if (!Number.isFinite(y) || !Number.isFinite(m) || !Number.isFinite(d)) return false;
            if (y < 1000 || y > 9999) return false;
            var dt = new Date(y, m - 1, d);
            return dt.getFullYear() === y && (dt.getMonth() + 1) === m && dt.getDate() === d;
        };
        var build = function(y, m, d) {
            return {
                anio: Number(y),
                mes: String(m).padStart(2, '0'),
                dia: String(d).padStart(2, '0'),
                fechaFormateada: String(y).padStart(4, '0') + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0')
            };
        };

        // 1) Intentar parse directo (ISO y variantes)
        var dtDirect = new Date(s);
        if (!isNaN(dtDirect.getTime()) && dtDirect.getFullYear() >= 1000 && dtDirect.getFullYear() <= 9999) {
            return build(dtDirect.getFullYear(), dtDirect.getMonth() + 1, dtDirect.getDate());
        }

        // 2) Patrones comunes
        var m;
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

        // 3) Sólo dígitos: YYYYMMDD, DDMMYYYY, MMYYYY, YYYYMM, YYYY
        var digits = original.replace(/\D/g, '');
        if (/^\d+$/.test(digits)) {
            if (digits.length === 8) {
                var y1 = digits.slice(0, 4), m1 = digits.slice(4, 6), d1 = digits.slice(6, 8);
                if (isValid(y1, m1, d1)) return build(y1, m1, d1);
                var d2 = digits.slice(0, 2), m2 = digits.slice(2, 4), y2 = digits.slice(4, 8);
                if (isValid(y2, m2, d2)) return build(y2, m2, d2);
            } else if (digits.length === 6) {
                var mm = digits.slice(0, 2), yyyy = digits.slice(2, 6);
                if (isValid(yyyy, mm, 1)) return build(yyyy, mm, 1);
                var yyyy2 = digits.slice(0, 4), mm2 = digits.slice(4, 6);
                if (isValid(yyyy2, mm2, 1)) return build(yyyy2, mm2, 1);
            } else if (digits.length === 4) {
                if (isValid(digits, 1, 1)) return build(digits, 1, 1);
            }
        }

        // 4) Buscar subcadenas que parezcan fecha
        m = original.match(/(\d{1,2}\/\d{4})/);
        if (m) return validarYFormatearFecha(m[1]);
        m = original.match(/(\d{1,2}\/\d{1,2}\/\d{4})/);
        if (m) return validarYFormatearFecha(m[1]);
        m = original.match(/(\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2})/);
        if (m) return validarYFormatearFecha(m[1]);

        return null;
    }


// Función para procesar QR
async function procesarQR(qrCodeMessage) {
    // Tokenizar y calcular firma
    var rawTokens = separar_datos(qrCodeMessage);
    var sig = huella_digital_qr(rawTokens);
    
    console.log('Tokens:', rawTokens);
    console.log('Signature:', sig);
    
    // Buscar perfil por firma
    try {
        var perfilEncontrado = await encontrar_perfil_por_firma(sig);
        
        if (perfilEncontrado) {
            console.log(' Perfil encontrado:', perfilEncontrado.name || 'sin nombre');
            window._perfilDetectado = perfilEncontrado;
            
            // Aplicar el perfil a los tokens crudos
            var datosDelPerfil = aplicarPerfilATokensCrudos(rawTokens, perfilEncontrado);
            
            if (datosDelPerfil) {
                console.log('Datos mapeados desde perfil:', datosDelPerfil);
                continuarProcesarQR(qrCodeMessage, datosDelPerfil);
                return;
            }
        }
    } catch (e) {
        console.warn('Error buscando perfil:', e);
    }
    
    // Si no hay perfil válido, usar filtros() para intentar parsear
    console.log(' No hay perfil válido, usando filtros()');
    window._perfilDetectado = null;
    
    // Aplicar filtros para intentar extraer datos
    var datos = filtros(qrCodeMessage);
    
    // Si filtros() no devolvió nada útil, crear array vacío para que el usuario arrastre manualmente
    if (datos === undefined || datos === null) {
        console.log('filtros() no pudo procesar, creando datos vacíos para arrastre manual');
        datos = ['', '', '', '', '', '', '', ''];
    }
    
    // Continuar procesando (esto creará la tabla principal)
    continuarProcesarQR(qrCodeMessage, datos);
}

    // Aplica un perfil a los tokens crudos
    function aplicarPerfilATokensCrudos(tokens, perfil) {
        // Obtener token_map (orden de campos) del perfil
        var tokenMap = perfil._tokenMap || perfil.token_map || [];
        
        if (typeof tokenMap === 'string') {
            tokenMap = cadena_a_matriz(tokenMap);
        }
        
        console.log('orden del perfil:', tokenMap);
        
        if (!Array.isArray(tokenMap) || tokenMap.length === 0) {
            console.warn('El perfil no tiene token_map válido');
            return null;
        }
        
        // Crear objeto mapeado
        var mapeado = {};
        tokens.forEach(function(valorToken, indice) {
            var campo = tokenMap[indice];
            // ===== CORRECCIÓN: Ignorar campos vacíos o marcados como _unmapped =====
            if (campo && campo !== 'null' && campo !== '' && campo !== 'undefined' && !campo.startsWith('_unmapped')) {
                mapeado[campo] = valorToken;
                console.log('Token[' + indice + '] "' + valorToken + '" -> ' + campo);
            }
        });
        
        console.log('Objeto mapeado:', mapeado);
        
        // Construir array en orden estándar
        var resultado = [
            mapeado.sku || mapeado.SKU || '',
            mapeado.producto || mapeado.PRODUCTO || '',
            mapeado.cantidad || mapeado.CANTIDAD || '',
            mapeado.lote || mapeado.LOTE || '',
            mapeado.fechaFab || mapeado.FECHAFAB || '',
            mapeado.fechaVenc || mapeado.FECHAVENC || '',
            mapeado.serieIni || mapeado.SERIEINI || '0',
            mapeado.serieFin || mapeado.SERIEFIN || '0'
        ];
        
        console.log('Resultado del perfil:', resultado);
        
        // Verificar que tengamos al menos SKU o producto
        if (!resultado[0] && !resultado[1]) {
            console.warn('El perfil no generó SKU ni Producto');
            return null;
        }
        
        return resultado;
    }

    // Función para continuar procesando el QR
    function continuarProcesarQR(qrCodeMessage, datos) {
        // Mostrar los datos crudos en la tabla superior
        mostrarDatosCrudos(qrCodeMessage);
        
        // Mostrar el header de la tabla principal
        var th = document.getElementById("tablaProductosHeader");
        if (th) th.style.display = "table";
        
        // Limpiar la tabla principal
        var tablaProductos = document.getElementById("tablaProductos");
        if (tablaProductos) {
            tablaProductos.innerHTML = '';
        }
        
        // Verificar si hay un perfil detectado
        var perfilAplicar = window._perfilDetectado || null;
        
        // ===== CORRECCIÓN PRINCIPAL =====
        // Si NO hay perfil, dejar la tabla vacía y salir
        if (!perfilAplicar) {
            console.log('No hay perfil detectado - tabla principal vacía');
            
            // Guardar último escaneo (para poder guardar perfil después)
            guardar_ultimo_escaneo({
                raw: qrCodeMessage,
                sku: '',
                producto: '',
                cantidad: '',
                lote: '',
                fechaFab: '',
                fechaVenc: '',
                serieIni: '',
                serieFin: ''
            });
            
            // Crear fila vacía con celdas editables para que el usuario pueda arrastrar
            var ordenColumnas = ['sku', 'producto', 'cantidad', 'lote', 'fechaFab', 'fechaVenc', 'serieIni', 'serieFin'];
            var fila = document.createElement('tr');
            
            ordenColumnas.forEach(function(campo) {
                var td = document.createElement('td');
                td.dataset.field = campo;
                td.contentEditable = 'true';
                td.style.cursor = 'text';
                td.textContent = ''; // VACÍO - el usuario debe arrastrar desde la tabla de arriba
                
                // Configurar como drop target
                td.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'copy';
                    this.style.backgroundColor = '#e6f7ff';
                    this.style.outline = '2px dashed #1890ff';
                });
                
                td.addEventListener('dragleave', function(e) {
                    this.style.backgroundColor = '';
                    this.style.outline = '';
                });
                
                td.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.style.backgroundColor = '';
                    this.style.outline = '';
                    
                    var valor = e.dataTransfer.getData('text/plain');
                    var indiceToken = e.dataTransfer.getData('tokenIndex');
                    var origen = e.dataTransfer.getData('source');
                    var campoDestino = this.dataset.field;
                    
                    if (valor) {
                        this.textContent = valor.trim();
                        
                        // Guardar mapeo para el perfil
                        if ((origen === 'raw_token' || origen === 'tabla_crudos') && indiceToken !== '') {
                            if (!window._tokenToFieldMap) window._tokenToFieldMap = {};
                            window._tokenToFieldMap[parseInt(indiceToken, 10)] = campoDestino;
                            console.log('Mapeo guardado: Token[' + indiceToken + '] -> ' + campoDestino);
                        }
                        
                        // Efecto visual
                        var self = this;
                        this.style.backgroundColor = '#d4edda';
                        setTimeout(function() { self.style.backgroundColor = ''; }, 500);
                        
                        mostrar_mensaje_feedback('Valor asignado a ' + campoDestino, 'exitoso');
                        
                        // Actualizar _lastScan con el nuevo valor
                        if (window._lastScan) {
                            window._lastScan[campoDestino] = valor.trim();
                        }
                    }
                });
                
                fila.appendChild(td);
            });
            
            tablaProductos.appendChild(fila);
            
            // Mostrar botones de acción
            var seccionAcciones = document.getElementById('seccion_acciones');
            if (seccionAcciones) seccionAcciones.style.display = 'block';
            
            // Configurar drag & drop
            setTimeout(function() {
                destinos_de_caida();
                sincronizacion_arrastre_tabla();
            }, 100);
            
            mostrar_mensaje_feedback('Este qr no tiene perfil - Arrastra los datos de la tabla inferior y guarda el normalizado');
            return;
        }
        
        // si hay perfil, aplicarlo 
        console.log('Aplicando perfil:', perfilAplicar.name);
        
        // Asegurar estructura estándar de 8 campos
        for (var i = 0; i < 8; i++) {
            if (!datos[i]) datos[i] = '';
        }
        if (!datos[6]) datos[6] = '0';
        if (!datos[7]) datos[7] = '0';

        console.log("Datos procesados con perfil:", datos);

        var ordenColumnas = ['sku', 'producto', 'cantidad', 'lote', 'fechaFab', 'fechaVenc', 'serieIni', 'serieFin'];
        
        // Obtener el orden del perfil
        var ordenPerfil = perfilAplicar._order || perfilAplicar.ord || [];
        if (typeof ordenPerfil === 'string') {
            ordenPerfil = cadena_a_matriz(ordenPerfil);
        }
        if (ordenPerfil.length > 0) {
            ordenColumnas = ordenPerfil;
        }
        
        console.log('Orden del perfil:', ordenColumnas);
        
        // Aplicar orden al header
        aplicar_orden_al_encabezado(ordenColumnas);
        
        // Seleccionar el perfil en el dropdown
        var nombrePerfil = perfilAplicar.name || '';
        if (nombrePerfil) {
            var selector = document.getElementById('selProfiles');
            if (selector) {
                for (var i = 0; i < selector.options.length; i++) {
                    if (selector.options[i].value === nombrePerfil) {
                        selector.selectedIndex = i;
                        console.log('Perfil seleccionado en dropdown:', nombrePerfil);
                        break;
                    }
                }
            }
        }
        
        // Limpiar perfil detectado
        window._perfilDetectado = null;
        
        // Crear objeto con los datos
        var datosObj = {
            sku: datos[0] || '',
            producto: datos[1] || '',
            cantidad: datos[2] || '',
            lote: datos[3] || '',
            fechaFab: datos[4] || '',
            fechaVenc: datos[5] || '',
            serieIni: datos[6] || '0',
            serieFin: datos[7] || '0'
        };
        
        // Crear fila respetando el orden de columnas
        var fila = document.createElement('tr');
        
        ordenColumnas.forEach(function(campo) {
            var td = document.createElement('td');
            td.dataset.field = campo;
            td.contentEditable = 'true';
            td.style.cursor = 'text';
            td.textContent = datosObj[campo] || '';
            
            // Configurar drag desde la celda
            td.draggable = true;
            td.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('text/plain', this.textContent.trim());
                e.dataTransfer.setData('sourceField', this.dataset.field);
                e.dataTransfer.setData('source', 'tabla_principal');
                e.dataTransfer.effectAllowed = 'copy';
                this.style.opacity = '0.5';
            });
            td.addEventListener('dragend', function() {
                this.style.opacity = '1';
            });
            
            fila.appendChild(td);
        });
        
        tablaProductos.appendChild(fila);
        
        // Guardar último escaneo
        guardar_ultimo_escaneo({
            raw: qrCodeMessage,
            sku: datos[0] || '',
            producto: datos[1] || '',
            cantidad: datos[2] || '',
            lote: datos[3] || '',
            fechaFab: datos[4] || '',
            fechaVenc: datos[5] || '',
            serieIni: datos[6] || '0',
            serieFin: datos[7] || '0'
        });
        
        // Mostrar botones de acción
        var seccionAcciones = document.getElementById('seccion_acciones');
        if (seccionAcciones) seccionAcciones.style.display = 'block';
        
        // Configurar drag & drop en las celdas
        setTimeout(function() {
            destinos_de_caida();
            sincronizacion_arrastre_tabla();
        }, 100);
        
        mostrar_mensaje_feedback('Perfil "' + nombrePerfil + '" aplicado automáticamente', 'exitoso');
    }

// TITULO LECTOR QR

var _scanLock = false; // Variable global para bloquear escaneos duplicados

function iniciarScanner() {
    if (qrScanner) {// si el escaner ya esta activo
        console.warn("El escaner ya esta activo");
        return;
    }

    escaneoRealizado = false;
    _scanLock = false; // Resetear el bloqueo

    var lector = document.getElementById("lectorQR"); // obtener el elemento del lector
    lector.style.display = "block"; // mostrar el lector
    lector.scrollIntoView({ behavior: "smooth", block: "center" }); // centrar el lector
    var isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent); // verificar si es ios
    qrScanner = new Html5Qrcode("lectorQR"); // iniciar el escaner

    // Función para calcular el tamaño de la cajita del qr dinámicamente
    var calcular_tamaño_qr = function() {
        var minQrboxSize = 300; // tamaño minimo de la cajita
        var maxQrboxSize = 500; // tamaño maximo de la cajita
        var idealRatio = 0.7; // ratio ideal de la cajita

        var containerWidth = lector.offsetWidth; // obtener el ancho del contenedor
        var containerHeight = lector.offsetHeight; // obtener el alto del contenedor

        var size = Math.min(containerWidth, containerHeight) * idealRatio;
        size = Math.max(minQrboxSize, Math.min(size, maxQrboxSize)); // asegurarse de que el tamaño esté dentro del rango

        return { width: size, height: size };
    };

    var qrboxConfig = calcular_tamaño_qr(); // calcular el tamaño del qrbox

    qrScanner.start(
        { facingMode: "environment" },
        {
            fps: 10, // frames por segundo
            qrbox: qrboxConfig, // tamaño del qrbox
            aspectRatio: isIOS ? 1.333333 : 1.0, // ratio del lector
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,// formato del qr
            ]
        },
        function(qrCodeMessage) {
            // Verificar si ya estamos procesando un escaneo
            if (_scanLock) {
                console.log('Escaneo ignorado - ya se está procesando uno');
                return;
            }
            _scanLock = true;
            //capturar frame al detener el scaner para con esto mostrar el qr
            

            var datos = filtros(qrCodeMessage);
            if (datos === undefined) {// si los datos son undefined
                _scanLock = false;
                return;
            }

            // Detener el escáner INMEDIATAMENTE para que no se repitan los escaneos
            escaneoRealizado = true;
            if (qrScanner) {// si el escaner existe
                qrScanner.stop().then(function() { // detener el escaner
                    console.log("Escaner detenido tras lectura exitosa");
                    return qrScanner.clear(); // limpiar el escaner
                }).then(function() {
                    qrScanner = null; // limpiar el escaner
                    lector.style.display = "none"; // ocultar el lector
                }).catch(function(err) {
                    console.error("Error deteniendo escaner:", err);
                    qrScanner = null; // limpiar el escaner
                });
            }

            // Procesar los datos
            aplicar_perfil_a_datos(qrCodeMessage, datos).catch(function(e) { 
                console.warn('Autodeteccion de perfil por signature fallo (camera):', e); 
            }).then(function() {
                continuarProcesarQR(qrCodeMessage, datos);
                _scanLock = false;
            });
        },
        function(errorMessage) {
            // Esto se llama constantemente mientras busca QR
            // No hacer nada aquí para no llenar la consola
        }
    ).catch(function(err) {
        console.error("Error iniciando escaner:", err);
        _scanLock = false;
        lector.style.display = "none";
        mostrar_mensaje_feedback("Error al iniciar el escaner. Verifica los permisos de camara.", "error");
    });
}



// esto es para que el arrastre funcione en moviles
(function() {
    var elementoArrastrado = null; // aqui guardamos el elemento que estamos arrastrando
    var datosArrastre = {};// aqui el texto o datos que lleva el elemento arrastrado
    var cajita = null; // la cajita que flota siguiendo el dedo
    var ejeX = 0; // este es el eje x de el dedo
    var ejeY = 0; // este es el eje y de el dedo

    // Esta función crea la etiqueta flotante que sigue al dedo para que el usuario vea lo que arrastra
    function crear_cajita(texto) {
        var div = document.createElement('div'); //Crea un contenedor nuevo
        div.id = 'touch-cajita'; //Le ponemos nombre para encontrarlo luego
        div.textContent = texto.length > 20 ? texto.substring(0, 20) + '...' : texto;// Si el texto es muy largo, lo cortamos y ponemos "..." para que no ocupe toda la pantalla
        div.style.cssText = 'position:fixed;z-index:99999;pointer-events:none;box-shadow:0 4px 12px rgba(0,0,0,0.3);transform:translate(-50%,-50%);white-space:nowrap;';
        document.body.appendChild(div); //Lo agregamos a la página
        return div;// Devolvemos la cajita creada para usarla
    }

    // Esta función elimina la cajita flotante cuando soltamos el dedo
    function eliminar_cajita() {
        var p = document.getElementById('touch-cajita');
        if (p) p.remove(); // Si existe, la eliminamos
        cajita = null;//borramos la referencia en memoria
    }

    //para ver qué hay debajo de la cajita flotante, la escondemos un milisegundo
    function obtener_elemento_de_abajo(x, y) {
        if (cajita) cajita.style.display = 'none'; // Ocultar cajita temporalmente
        var elem = document.elementFromPoint(x, y);// Ver qué elemento está en esas coordenadas
        if (cajita) cajita.style.display = 'block'; // Mostrar cajita de nuevo
        return elem; // Devolver el elemento encontrado (la celda de la tabla)
    }

    // Esta función decide si el lugar donde está el dedo es válido para "soltar" el dato
    function esDropTarget(elem) {
        if (!elem) return false; // Si no hay elemento, no es válido
        // Celdas de tabla principal
        if (elem.tagName === 'TD' && elem.closest('#tablaProductos')) return true;
        // Celdas de tabla de datos crudos (para arrastrar desde ahí)
        if (elem.tagName === 'TD' && elem.closest('#tablaDatosCrudos')) return true;
        // Items del panel lateral
        if (elem.classList && elem.classList.contains('draggable-item')) return true;
        // Inputs dentro del panel
        if (elem.tagName === 'INPUT' && elem.closest('.draggable-item')) return true;
        // Headers de tabla
        if (elem.tagName === 'TH') return true;
        return false;
    }

    // Se activa apenas el usuario pone el dedo sobre un elemento
    function mivimiento_dedo_iniciar(e) {
        var target = e.target;
        
        // Solo permitir arrastre desde celdas de tablas
        var celdaCrudos = target.closest('#tablaDatosCrudos td');
        var celdaPrincipal = target.closest('#tablaProductos td');

        // Si tocó fuera de las celdas permitidas, no hacemos nada (cancelamos)
        if (!celdaCrudos && !celdaPrincipal) return;
        
        var celda = celdaCrudos || celdaPrincipal; // Obtenemos la celda que tocó
        var texto = celda.textContent.trim(); // Obtenemos el texto de la celda
        
        if (!texto || texto === '') return; // Si la celda está vacía, no hay nada que arrastrar, cancelamos
        
        // Prevenir scroll mientras arrastramos
        e.preventDefault();
        
        elementoArrastrado = celda; // Guardamos la celda que estamos arrastrando
        
        // Guardar datos del arrastre
        datosArrastre = {
            texto: texto,
            tokenIndex: celda.dataset.tokenIndex || '', // Guardamos índices
            sourceField: celda.dataset.field || '', // Guardamos el nombre del campo de origen
            source: celdaCrudos ? 'tabla_crudos' : 'tabla_principal' // Guardamos de qué tabla proviene
        };
        
        // Posición inicial del touch
        var touch = e.touches[0];
        ejeX = touch.clientX;
        ejeY = touch.clientY;
        
        // Crear cajita visual y le ponemos la posicion de el dedo
        cajita = crear_cajita(texto);
        cajita.style.left = ejeX + 'px';
        cajita.style.top = ejeY + 'px';
        
        // Marcar celda origen
        celda.style.opacity = '0.5';
    
        console.log('Touch start:', datosArrastre);
    }

    // Se ejecuta constantemente mientras el dedo se desliza por la pantalla
    function movimiendo_dedo(e) {
        if (!elementoArrastrado || !cajita) return;
        
        e.preventDefault();
        
        var touch = e.touches[0];
        
        // Mover cajita
        cajita.style.left = touch.clientX + 'px';
        cajita.style.top = touch.clientY + 'px';
        
        // Resaltar elemento bajo el dedo
        var elemBajo = obtener_elemento_de_abajo(touch.clientX, touch.clientY);
        
        // Quitar resaltado anterior
        document.querySelectorAll('.touch-hover').forEach(function(el) {
            el.classList.remove('touch-hover');
            el.style.backgroundColor = '';
            el.style.outline = '';
        });
        
        // Resaltar nuevo target
        if (esDropTarget(elemBajo)) {
            elemBajo.classList.add('touch-hover');
            elemBajo.style.backgroundColor = '#e6f7ff';
        }
    }

    // Soltar elemento
    function movimiento_dedo_soltar(e) {
        if (!elementoArrastrado) return;
        
        // Restaurar celda origen
        elementoArrastrado.style.opacity = '1';
        elementoArrastrado.style.backgroundColor = '';
        
        // Obtener posición final
        var touch = e.changedTouches[0];
        var elemDestino = obtener_elemento_de_abajo(touch.clientX, touch.clientY);
        
        // Quitar resaltados
        document.querySelectorAll('.touch-hover').forEach(function(el) {
            el.classList.remove('touch-hover');
            el.style.backgroundColor = '';
            el.style.outline = '';
        });
        
        // Procesar drop si es un target válido
        if (elemDestino && esDropTarget(elemDestino)) {
            procesar_arrastre(elemDestino, datosArrastre);
        }
        
        // Limpiar
        eliminar_cajita();
        elementoArrastrado = null;
        datosArrastre = {};
    }

    // Procesar el drop en el elemento destino
    function procesar_arrastre(destino, datos) {
        var valor = datos.texto;
        var tokenIndex = datos.tokenIndex;
        var source = datos.source;
        var sourceField = datos.sourceField;
        
        console.log('Touch drop:', valor, 'en', destino.tagName, destino.dataset);
        
        // arraste en la tabla principal
        if (destino.tagName === 'TD' && destino.closest('#tablaProductos')) {
            destino.textContent = valor;
            var campoDestino = destino.dataset.field;
            
            // Guardar mapeo si viene de tabla crudos
            if (source === 'tabla_crudos' && tokenIndex !== '') {
                if (!window._tokenToFieldMap) window._tokenToFieldMap = {};
                window._tokenToFieldMap[parseInt(tokenIndex, 10)] = campoDestino;
                console.log('Mapeo guardado: Token[' + tokenIndex + '] -> ' + campoDestino);
            }
            
            // Efecto visual
            destino.style.backgroundColor = '#d4edda';
            setTimeout(function() { destino.style.backgroundColor = ''; }, 500);
            
            if (typeof mostrar_mensaje_feedback === 'function') {
                mostrar_mensaje_feedback('Valor asignado a ' + campoDestino, 'exitoso');
            }
        }
        
        // arrastre en el panel 
        else if (destino.tagName === 'INPUT' && destino.closest('.draggable-item')) {
            destino.value = valor;
            destino.dispatchEvent(new Event('input', { bubbles: true }));
            
            var item = destino.closest('.draggable-item');
            var campoPanel = item ? item.dataset.field : '';
            
            // Guardar mapeo
            if (source === 'tabla_crudos' && tokenIndex !== '' && campoPanel) {
                if (!window._tokenToFieldMap) window._tokenToFieldMap = {};
                window._tokenToFieldMap[parseInt(tokenIndex, 10)] = campoPanel;
            }
            
            // Efecto visual
            destino.style.borderColor = '#28a745';
            setTimeout(function() { destino.style.borderColor = ''; }, 500);
            
            if (typeof mostrar_mensaje_feedback === 'function') {
                mostrar_mensaje_feedback('Valor asignado a ' + campoPanel, 'exitoso');
            }
        }
        
        // DROP EN DRAGGABLE-ITEM (contenedor)
        else if (destino.classList && destino.classList.contains('draggable-item')) {
            var input = destino.querySelector('input');
            if (input) {
                input.value = valor;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                
                var campoItem = destino.dataset.field;
                
                if (source === 'tabla_crudos' && tokenIndex !== '' && campoItem) {
                    if (!window._tokenToFieldMap) window._tokenToFieldMap = {};
                    window._tokenToFieldMap[parseInt(tokenIndex, 10)] = campoItem;
                }
                
                if (typeof mostrar_mensaje_feedback === 'function') {
                    mostrar_mensaje_feedback('Valor asignado a ' + campoItem, 'exitoso');
                }
            }
        }
    }

    // con esto cancelamos el arrastre
    function cancelacion_de_arrastre() {
        if (elementoArrastrado) {
            elementoArrastrado.style.opacity = '1';
            elementoArrastrado.style.backgroundColor = '';
        }
        eliminar_cajita();
        elementoArrastrado = null;
        datosArrastre = {};
        
        document.querySelectorAll('.touch-hover').forEach(function(el) {
            el.classList.remove('touch-hover');
            el.style.backgroundColor = '';
            el.style.outline = '';
        });
    }

    // Inicializar eventos touch en las tablas
    function inicializar_tocar_arrastrar_soltar() {
        // Tabla de datos crudos
        var tablaCrudos = document.getElementById('tablaDatosCrudos');
        if (tablaCrudos) {
            tablaCrudos.addEventListener('touchstart', mivimiento_dedo_iniciar, { passive: false });
            tablaCrudos.addEventListener('touchmove', movimiendo_dedo, { passive: false });
            tablaCrudos.addEventListener('touchend', movimiento_dedo_soltar, { passive: false });
            tablaCrudos.addEventListener('touchcancel', cancelacion_de_arrastre, { passive: false });
        }
        
        // Tabla principal
        var tablaPrincipal = document.getElementById('tablaProductos');
        if (tablaPrincipal) {
            tablaPrincipal.addEventListener('touchstart', mivimiento_dedo_iniciar, { passive: false });
            tablaPrincipal.addEventListener('touchmove', movimiendo_dedo, { passive: false });
            tablaPrincipal.addEventListener('touchend', movimiento_dedo_soltar, { passive: false });
            tablaPrincipal.addEventListener('touchcancel', cancelacion_de_arrastre, { passive: false });
        }
        
        console.log('Touch drag & drop inicializado');
    }

    // Reinicializar cuando se agregan nuevas filas
    function observarCambiosTablas() {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    // Pequeño delay para asegurar que el DOM esté listo
                    setTimeout(inicializar_tocar_arrastrar_soltar, 100);
                }
            });
        });
        
        var tablaCrudos = document.getElementById('bodyCrudos');
        var tablaPrincipal = document.getElementById('tablaProductos');
        
        if (tablaCrudos) {
            observer.observe(tablaCrudos, { childList: true, subtree: true });
        }
        if (tablaPrincipal) {
            observer.observe(tablaPrincipal, { childList: true, subtree: true });
        }
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            inicializar_tocar_arrastrar_soltar();
            observarCambiosTablas();
        });
    } else {
        inicializar_tocar_arrastrar_soltar();
        observarCambiosTablas();
    }

})();

// TITULO INFORMACION TABLA DE PRODUCTOS

    // SIN CODIGO

// TITULO CUERPO DINAMICO DE TABLA

    // SIN CODIGO

// TITULO BOTONES GUARDAR

// Guarda los datos normalizados Y el perfil automáticamente
function guardar_normalizado_con_perfil() {
    // Validar que la tabla tenga datos
    var payload = obtener_datos_tabla_principal();
    // esto es para verificar que la tabla tenga datos
    if (!payload || Object.keys(payload).length === 0) {
        mostrar_mensaje_feedback('No hay datos en la tabla para guardar', 'error');
        return;
    }
    
    // Validaciones
    //validar el sku
    if (!payload.sku || payload.sku === '' || payload.sku === '0') {
        mostrar_mensaje_feedback('El SKU es obligatorio', 'error');
        return;
    }
    //validar el producto
    if (!payload.producto || payload.producto === '' || payload.producto === '0') {
        mostrar_mensaje_feedback('El PRODUCTO es obligatorio', 'error');
        return;
    }
    //validar la cantidad
    if (!payload.cantidad || payload.cantidad === '' || payload.cantidad === '0') {
        mostrar_mensaje_feedback('La CANTIDAD es obligatoria', 'error');
        return;
    }
    //validar el lote
    if (!payload.lote || payload.lote === '' || payload.lote === '0') {
        mostrar_mensaje_feedback('El LOTE es obligatorio', 'error');
        return;
    }
    
    // Calcular fechaVenc si está vacía
    if (payload.fechaFab && (!payload.fechaVenc || payload.fechaVenc === '')) {
        var fecha_fab_parsed = validarYFormatearFecha(payload.fechaFab);
        if (fecha_fab_parsed && fecha_fab_parsed.fechaFormateada) {
            var fecha_base = new Date(fecha_fab_parsed.fechaFormateada);
            fecha_base.setFullYear(fecha_base.getFullYear() + 5);
            var anio = fecha_base.getFullYear();
            var mes = String(fecha_base.getMonth() + 1).padStart(2, '0');
            var dia = String(fecha_base.getDate()).padStart(2, '0');
            payload.fechaVenc = anio + '-' + mes + '-' + dia;
        }
    }
    
    // Obtener datos del cliente
    var rut_cliente = '';
    var num_factura = '';
    var fecha_despacho = '';
    
    if (window._datosCliente) {
        rut_cliente = window._datosCliente.rut || '';
        num_factura = window._datosCliente.factura || '';
        fecha_despacho = window._datosCliente.fecha || '';
    }
    
    // Fallback desde URL
    var params = new URLSearchParams(window.location.search);
    rut_cliente = rut_cliente || params.get('rut_cliente') || '';
    num_factura = num_factura || params.get('num_factura') || '';
    fecha_despacho = fecha_despacho || params.get('fecha_despacho') || '';
    
    // Construir datos del perfil
    var perfil = construir_perfil_desde_tabla();
    
    // Preparar datos para enviar
    var datos_enviar = {
        sku: payload.sku || '',
        producto: payload.producto || '',
        cantidad: payload.cantidad || '0',
        lote: payload.lote || '',
        fechaFab: payload.fechaFab || '',
        fechaVenc: payload.fechaVenc || '',
        serieIni: payload.serieIni || '0',
        serieFin: payload.serieFin || '0',
        rut: rut_cliente,
        numero_fact: num_factura,
        fecha_despacho: fecha_despacho,
        // Datos del perfil para guardar automáticamente
        signature: perfil ? perfil.signature : '',
        payload: perfil ? matriz_a_cadena(perfil.payload || {}) : '',
        _order: perfil ? matriz_a_cadena(perfil._order || []) : '',
        _tokenMap: perfil ? matriz_a_cadena(perfil._tokenMap || []) : ''
    };
    
    console.log('Enviando datos normalizados con perfil:', datos_enviar);
    
    // Mostrar indicador de carga
    mostrar_mensaje_feedback('Guardando...', 'alerta');
    
    enviarAlServidor('save_normalized_data', datos_enviar, function(resultado, respuesta) {
        if (resultado === 'success') {
            var mensaje = 'Datos guardados correctamente';
            if (respuesta.perfil_guardado === 'si') {
                mensaje += '. Perfil "' + respuesta.nombre_perfil + '" guardado automáticamente.';
            }
            mostrar_mensaje_feedback(mensaje, 'exitoso');
            
            // Preguntar si desea volver a ventas
            setTimeout(function() {
                mostrar_pantalla_volver_a_ventas(payload);
            }, 1500);
        } else {
            var msg_error = respuesta && respuesta.msg ? respuesta.msg : 'Error desconocido';
            mostrar_mensaje_feedback('Error guardando: ' + msg_error, 'error');
        }
    });
}

// TITULO ARCHIVO JS

    // SIN CODIGO
/*  --------------------------------------------------------------------------------------------------------------
    ---------------------------------------- FIN ITred Spa normalizar_qr .JS -------------------------------------
    -------------------------------------------------------------------------------------------------------------- */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ  
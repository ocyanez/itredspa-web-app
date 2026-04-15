// sitio web creado por itred spa.
// direccion: guido reni #4190
// pedro aguirre cerda - santiago - chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// creado, programado y diseñado por itred spa.
// bppj 

//  -------------------------------------------------------------------------------------------------------------
//  -------------------------------------- inicio itred spa generar_qr .js --------------------------------------
//  ------------------------------------------------------------------------------------------------------------- 

// TITULO HTML

    // esta bandera te dice si el qr dinámico está prendido (true) o apagado (false)
    var qrDinamicoActivo = false;
    
    // este relojito espera un ratito antes de actualizar el qr mientras escribes
    // así no se redibuja a cada letra que tecleas
    var timeoutActualizacion = null;
    
    // aquí guardamos el tamaño que el usuario eligió para su etiqueta (en centímetros)
    // por defecto son 6cm de ancho y 6cm de alto
    var dimensionesCustom = { ancho: 6, alto: 6 };

    // carga la lista del historial de productos guardados al iniciar la página
    // llama a la función que lee del navegador
    var listaProductos  = []
    cargarListaDesdeBD();

// TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN 

    //  sin función 
    
    

// TITULO FORMULARIO GENERADOR QR

    
    // tiene todas las funciones matemáticas necesarias para dibujar el qr 

    var GENERADOR_QR = {
        
        // esta lista dice cuántos caracteres caben en cada versión de qr
        // versión 1 cabe 14, versión 2 cabe 26, y así sucesivamente
        capacidadesPorVersion: [0, 14, 26, 42, 62, 84, 106, 122, 152, 180, 213],
        
        // estos son números mágicos que se usan para hacer corrección de errores
        // cada número en estas listas ayuda a recuperar el qr si está rayado o manchado
        polinomiosGeneradores: {
            7: [87, 229, 146, 149, 238, 102, 21],
            10: [251, 67, 46, 61, 118, 70, 64, 94, 32, 45],
            15: [29, 196, 111, 163, 112, 74, 10, 105, 105, 139, 132, 151, 32, 134, 26],
            16: [59, 13, 104, 189, 68, 209, 30, 8, 163, 65, 41, 229, 98, 50, 36, 59],
            17: [119, 66, 83, 120, 119, 22, 197, 83, 249, 41, 143, 134, 85, 53, 125, 99, 79],
            18: [239, 251, 183, 113, 149, 175, 199, 215, 240, 220, 73, 82, 173, 75, 32, 67, 217, 146],
            20: [152, 185, 240, 5, 111, 99, 6, 220, 112, 150, 69, 36, 187, 22, 228, 198, 121, 121, 165, 174],
            22: [89, 179, 131, 176, 182, 244, 19, 189, 69, 40, 28, 137, 29, 123, 67, 253, 86, 218, 230, 26, 145, 245],
            24: [122, 118, 169, 70, 178, 237, 216, 102, 115, 150, 229, 73, 130, 72, 61, 43, 206, 1, 237, 247, 127, 217, 144, 117],
            26: [246, 51, 183, 4, 136, 98, 199, 152, 77, 56, 206, 24, 145, 40, 209, 117, 233, 42, 135, 68, 70, 144, 146, 77, 43, 94],
            28: [252, 9, 28, 13, 18, 251, 208, 150, 103, 174, 100, 41, 167, 12, 247, 56, 117, 119, 233, 127, 181, 100, 121, 147, 176, 74, 58, 197],
            30: [212, 246, 77, 73, 195, 192, 75, 98, 5, 70, 103, 177, 22, 217, 138, 51, 181, 246, 72, 25, 18, 46, 228, 74, 216, 195, 11, 106, 130, 150]
        },
       
        // estas dos tablas empiezan vacías pero luego se llenan con cálculos especiales
        // son como diccionarios matemáticos para hacer multiplicaciones raras que necesita el qr
        tablaLogaritmos: null, 
        tablaAntilogaritmos: null,
        
        // <!-- función para inicializar tablas matemáticas -->
        // esta función prepara las tablitas matemáticas que se necesitan
        inicializarTablasGalois: function() {
            // si ya están hechas, no las vuelve a hacer (ahorra tiempo)
            if (this.tablaLogaritmos !== null) return;
            
            // creamos espacios para 256 números en cada tabla
            this.tablaLogaritmos = new Array(256);
            this.tablaAntilogaritmos = new Array(256);
            
            // empezamos con el número 1
            var valor = 1;
            // vamos llenando las tablas con 255 números calculados
            for (var exponente = 0; exponente < 255; exponente++) {
                // guardamos el valor en la posición del exponente
                this.tablaAntilogaritmos[exponente] = valor;
                // guardamos el exponente en la posición del valor
                this.tablaLogaritmos[valor] = exponente;
                // multiplicamos por 2 para el siguiente
                valor *= 2;
                // si se pasa de 255, hacemos un ajuste matemático especial
                if (valor >= 256) valor ^= 285; // el ^= es como una resta mágica
            }
            // el último número se repite con el primero
            this.tablaAntilogaritmos[255] = this.tablaAntilogaritmos[0];
        },
        
       
        // esta función multiplica dos números de forma especial para códigos qr
        multiplicarGalois: function(a, b) {
            // si alguno es cero, el resultado es cero (obvio)
            if (a === 0 || b === 0) return 0;
            // usamos las tablas para hacer la multiplicación mágica
            // sumamos los logaritmos, dividimos entre 255 y sacamos el antilogaritmo
            return this.tablaAntilogaritmos[(this.tablaLogaritmos[a] + this.tablaLogaritmos[b]) % 255];
        },
        
        
        // esta función decide qué tan grande debe ser el qr según cuánto texto tiene
        obtenerVersion: function(longitudDatos) {
            // probamos desde la versión 1 hasta la 10
            for (var version = 1; version <= 10; version++) {
                // si el texto cabe en esta versión, la devolvemos
                if (this.capacidadesPorVersion[version] >= longitudDatos) return version;
            }
            // si el texto es muy largo, usamos la versión 10 (la más grande)
            return 10;
        },
        
        
        // esta función calcula cuántos cuadritos tendrá el qr de lado
        // la fórmula es: 17 + (versión × 4)
        obtenerTamanioMatriz: function(version) {
            return 17 + (version * 4);
        },
        
        
        // esta función convierte tu texto a ceros y unos que el qr puede entender
        codificarDatos: function(texto, version) {
            // aquí guardaremos todos los ceros y unos
            var bits = [];
            // sacamos cuántos bits podemos meter en total
            var capacidadTotal = this.capacidadesPorVersion[version];
            
            // primero ponemos 4 bits especiales que dicen "aquí empieza el texto"
            bits.push(0, 1, 0, 0);
            
            // luego ponemos cuántas letras tiene el texto (en 8 bits)
            var longitudTexto = texto.length;
            // vamos bit por bit del número más grande al más chico
            for (var i = 7; i >= 0; i--) bits.push((longitudTexto >> i) & 1);
            
            // ahora convertimos cada letra del texto a 8 bits
            for (var j = 0; j < texto.length; j++) {
                // sacamos el código numérico de la letra (ej: 'a' = 97)
                var codigoCaracter = texto.charCodeAt(j);
                // convertimos ese número a 8 bits
                for (var k = 7; k >= 0; k--) bits.push((codigoCaracter >> k) & 1);
            }
            
            // si nos sobra espacio, rellenamos con ceros
            while (bits.length < capacidadTotal * 8) bits.push(0);
            
            // ahora agrupamos los bits de 8 en 8 para hacer bytes
            var bytes = [];
            // vamos de 8 en 8
            for (var b = 0; b < bits.length; b += 8) {
                var byte = 0;
                // juntamos los 8 bits en un número
                for (var bit = 0; bit < 8; bit++) byte = (byte << 1) | bits[b + bit];
                bytes.push(byte);
            }
            // devolvemos la lista de bytes
            return bytes;
        },
        
       
        // esta función calcula datos extra que ayudan a leer el qr aunque esté dañado
        calcularCorreccionErrores: function(datos, version) {
            // primero preparamos las tablas matemáticas
            this.inicializarTablasGalois();
            
            // decidimos cuántos bytes extra necesitamos según la versión
            var bytesCorreccion = [0, 10, 16, 26, 18, 24, 16, 18, 22, 22, 26][version];
            // sacamos los números mágicos para esta cantidad de corrección
            var polinomioGenerador = this.polinomiosGeneradores[bytesCorreccion] || this.polinomiosGeneradores[20];
            
            // copiamos los datos originales
            var coeficientes = datos.slice();
            // agregamos espacios vacíos al final para los bytes de corrección
            for (var j = 0; j < bytesCorreccion; j++) coeficientes.push(0);
            
            // ahora viene la magia matemática
            // para cada dato original
            for (var k = 0; k < datos.length; k++) {
                var coeficiente = coeficientes[k];
                // si no es cero, hacemos operaciones con él
                if (coeficiente !== 0) {
                    // mezclamos con cada número del polinomio
                    for (var l = 0; l < polinomioGenerador.length; l++) {
                        // hacemos una suma especial (xor) con multiplicación galois
                        coeficientes[k + l + 1] ^= this.multiplicarGalois(coeficiente, this.tablaAntilogaritmos[polinomioGenerador[l]]);
                    }
                }
            }
            // devolvemos solo la parte de corrección que calculamos
            return coeficientes.slice(datos.length);
        },
        
        
        // esta función crea una cuadrícula vacía del tamaño que necesitamos
        crearMatriz: function(tamanio) {
            var matriz = [];
            // creamos fila por fila
            for (var fila = 0; fila < tamanio; fila++) {
                matriz[fila] = [];
                // en cada fila creamos todas las columnas vacías (null)
                for (var columna = 0; columna < tamanio; columna++) matriz[fila][columna] = null;
            }
            return matriz;
        },
        
        // esta función dibuja los cuadrados grandes de las esquinas
        // esos cuadrados ayudan al escáner a encontrar el qr
        dibujarPatronBusqueda: function(matriz, filaInicio, columnaInicio) {
            // recorremos un cuadrado de 7x7
            for (var fila = 0; fila < 7; fila++) {
                for (var columna = 0; columna < 7; columna++) {
                    // pintamos de negro el borde y el cuadrado del centro
                    var esNegro = (fila === 0 || fila === 6 || columna === 0 || columna === 6 || (fila >= 2 && fila <= 4 && columna >= 2 && columna <= 4));
                    // ponemos 1 si es negro, 0 si es blanco
                    matriz[filaInicio + fila][columnaInicio + columna] = esNegro ? 1 : 0;
                }
            }
        },
        
    
        // esta función dibuja líneas blancas alrededor de los cuadrados grandes
        // son como un marco de separación
        dibujarSeparadores: function(matriz, tamanio) {
            // recorremos 8 posiciones
            for (var i = 0; i < 8; i++) {
                // si la posición está vacía, la pintamos de blanco (0)
                // separador horizontal arriba izquierda
                if (matriz[7] && matriz[7][i] === null) matriz[7][i] = 0;
                // separador vertical arriba izquierda
                if (matriz[i] && matriz[i][7] === null) matriz[i][7] = 0;
                // separador horizontal arriba derecha
                if (matriz[7] && matriz[7][tamanio - 8 + i] === null) matriz[7][tamanio - 8 + i] = 0;
                // separador vertical arriba derecha
                if (matriz[i] && matriz[i][tamanio - 8] === null) matriz[i][tamanio - 8] = 0;
                // separador horizontal abajo izquierda
                if (matriz[tamanio - 8] && matriz[tamanio - 8][i] === null) matriz[tamanio - 8][i] = 0;
                // separador vertical abajo izquierda
                if (matriz[tamanio - 8 + i] && matriz[tamanio - 8 + i][7] === null) matriz[tamanio - 8 + i][7] = 0;
            }
        },
        
        // esta función dibuja las líneas punteadas que conectan los cuadrados grandes
        // son como líneas de reloj que van intercalando negro-blanco
        dibujarPatronesTemporalizacion: function(matriz, tamanio) {
            // vamos desde el 8 hasta antes del último cuadrado
            for (var i = 8; i < tamanio - 8; i++) {
                // alternamos: 0, 1, 0, 1, 0, 1...
                var valor = (i + 1) % 2;
                // dibujamos la línea horizontal
                if(matriz[6]) matriz[6][i] = valor;
                // dibujamos la línea vertical
                if(matriz[i]) matriz[i][6] = valor;
            }
        },
        
     
        // esta función pone un punto negro fijo que siempre va en el qr
        // es obligatorio según las reglas del formato qr
        dibujarModuloOscuro: function(matriz, version) {
             // calculamos en qué fila va según la versión
             var fila = (4 * version) + 9;
             // lo ponemos en la columna 8
             if (matriz[fila]) matriz[fila][8] = 1;
        },
        
        // esta función reserva espacio para información técnica del qr
        // ahí va la versión y el nivel de corrección de errores
        reservarAreaFormato: function(matriz, tamanio) {
             // reservamos 9 posiciones arriba y a la izquierda
             for (var i=0; i<9; i++) { 
                 if(matriz[8]) matriz[8][i] = 0; 
                 if(matriz[i]) matriz[i][8] = 0; 
             }
             // reservamos 8 posiciones abajo y a la derecha
             for (var i=0; i<8; i++) { 
                 if(matriz[8]) matriz[8][tamanio-1-i] = 0; 
                 if(matriz[tamanio-1-i]) matriz[tamanio-1-i][8] = 0; 
             }
        },
        
        // esta función pone los datos reales en la cuadrícula
        // va llenando los espacios vacíos con los bits de información
        colocarDatos: function(matriz, datos, bytesCorreccion, tamanio) {
            // juntamos los datos originales con la corrección de errores
            var todosLosBytes = datos.concat(bytesCorreccion);
            var todosLosBits = [];
            
            // convertimos todos los bytes a una larga lista de bits
            for (var i = 0; i < todosLosBytes.length; i++) {
                // sacamos cada bit del byte (de izquierda a derecha)
                for (var bit = 7; bit >= 0; bit--) {
                    todosLosBits.push((todosLosBytes[i] >> bit) & 1);
                }
            }
            
            // empezamos en el primer bit
            var indiceBit = 0;
            // empezamos subiendo
            var direccionArriba = true; 
            
            // recorremos la matriz en zigzag de derecha a izquierda
            for (var columna = tamanio - 1; columna > 0; columna -= 2) {
                // saltamos la columna 6 que es de temporalización
                if (columna === 6) columna = 5; 
                // recorremos toda la altura
                for (var contador = 0; contador < tamanio; contador++) {
                    // si vamos arriba, empezamos desde abajo; si vamos abajo, desde arriba
                    var fila = direccionArriba ? (tamanio - 1 - contador) : contador;
                    // ponemos bits en dos columnas (la actual y la de al lado)
                    for (var desplazamiento = 0; desplazamiento < 2; desplazamiento++) {
                        var columnaActual = columna - desplazamiento;
                        // si el espacio está vacío (null), ponemos un bit
                        if (matriz[fila][columnaActual] === null) {
                            var valorBit = 0;
                            // si todavía tenemos bits que poner, los ponemos
                            if (indiceBit < todosLosBits.length) {
                                valorBit = todosLosBits[indiceBit];
                                indiceBit++;
                            }
                            matriz[fila][columnaActual] = valorBit;
                        }
                    }
                }
                // cambiamos de dirección para el siguiente par de columnas
                direccionArriba = !direccionArriba;
            }
        },
        
        // esta función aplica un patrón para equilibrar los puntos negros y blancos
        // esto hace que el qr sea más fácil de leer para el escáner
        aplicarMascara: function(matriz, tamanio) {
             // recorremos toda la matriz
             for(var f=0; f<tamanio; f++){
                 for(var c=0; c<tamanio; c++){
                     // solo cambiamos los puntos que son datos (no los patrones fijos)
                     if (this.esModuloDatos(f, c, tamanio)) {
                         // si la suma de fila + columna es par, invertimos el color
                         if ((f + c) % 2 === 0) {
                             matriz[f][c] ^= 1; // el ^= invierte: 0 se vuelve 1, 1 se vuelve 0
                         }
                     }
                 }
             }
        },
        
        
        // esta función verifica si un punto es parte de los datos o de los dibujitos fijos
        esModuloDatos: function(f, c, tamanio) {
            // si está en la esquina superior izquierda, es patrón fijo
            if (f < 9 && c < 9) return false;
            // si está en la esquina superior derecha, es patrón fijo
            if (f < 9 && c >= tamanio - 8) return false;
            // si está en la esquina inferior izquierda, es patrón fijo
            if (f >= tamanio - 8 && c < 9) return false;
            // si está en las líneas de temporalización, es patrón fijo
            if (f == 6 || c == 6) return false;
            // si llegó aquí, es un punto de datos
            return true; 
        },
        
        
        // esta función escribe información técnica en los espacios reservados
        // le dice al escáner qué versión es y qué nivel de corrección tiene
        escribirInformacionFormato: function(matriz, tamanio) {
            // esta secuencia de bits es estándar para formato qr
            var bitsFormato = [1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0]; 
            // escribimos los primeros 6 bits arriba a la izquierda
            for(var i=0; i<6; i++) matriz[8][i] = bitsFormato[i];
            // escribimos los bits 6, 7 y 8
            matriz[8][7] = bitsFormato[6]; 
            matriz[8][8] = bitsFormato[7]; 
            matriz[7][8] = bitsFormato[8];
            // escribimos los bits del 9 al 14 bajando por la izquierda
            for(var i=9; i<15; i++) matriz[14-i][8] = bitsFormato[i];
            // escribimos bits en la parte superior derecha
            for(var i=0; i<8; i++) matriz[8][tamanio-1-i] = bitsFormato[7+i]; 
            // escribimos bits en la parte inferior derecha
            for(var i=0; i<7; i++) matriz[tamanio-1-i][8] = bitsFormato[i]; 
        },
        
        
        // esta es la función jefa que coordina todo el proceso
        // llama a todas las demás funciones en orden para crear el qr completo
        generar: function(texto) {
            // si no hay texto, no hacemos nada
            if (!texto || texto.length === 0) return null;
            // si el texto es muy largo, lo cortamos a 500 caracteres
            if (texto.length > 500) texto = texto.substring(0, 500);
            
            // decidimos qué versión usar según el largo del texto
            var version = this.obtenerVersion(texto.length);
            // calculamos el tamaño de la cuadrícula
            var tamanio = this.obtenerTamanioMatriz(version);
            // creamos la cuadrícula vacía
            var matriz = this.crearMatriz(tamanio);
            
            // dibujamos los tres cuadrados grandes de las esquinas
            this.dibujarPatronBusqueda(matriz, 0, 0); // arriba izquierda
            this.dibujarPatronBusqueda(matriz, 0, tamanio - 7); // arriba derecha
            this.dibujarPatronBusqueda(matriz, tamanio - 7, 0); // abajo izquierda
            // dibujamos los marcos blancos alrededor de los cuadrados
            this.dibujarSeparadores(matriz, tamanio); 
            // dibujamos las líneas punteadas
            this.dibujarPatronesTemporalizacion(matriz, tamanio); 
            // ponemos el punto negro obligatorio
            this.dibujarModuloOscuro(matriz, version); 
            // reservamos espacio para información técnica
            this.reservarAreaFormato(matriz, tamanio); 
            
            // convertimos el texto a bytes
            var datosCodficados = this.codificarDatos(texto, version); 
            // calculamos los bytes de corrección de errores
            var bytesCorreccion = this.calcularCorreccionErrores(datosCodficados, version); 
            
            // ponemos todos los datos en la matriz
            this.colocarDatos(matriz, datosCodficados, bytesCorreccion, tamanio); 
            // aplicamos el patrón de equilibrio
            this.aplicarMascara(matriz, tamanio); 
            // escribimos la información de formato
            this.escribirInformacionFormato(matriz, tamanio); 
            
            // devolvemos la matriz completa y su tamaño
            return { matriz: matriz, tamanio: tamanio }; 
        },
        
        
        // esta función toma la matriz de números y la dibuja en un canvas html
        // convierte los 0s y 1s en cuadraditos blancos y negros visibles
        dibujarEnCanvas: function(canvas, texto, tamanioPixel) {
            // obtenemos las herramientas de dibujo del canvas
            var contexto = canvas.getContext('2d');
            // importante: desactivar el suavizado para que los pixeles sean nítidos
            contexto.imageSmoothingEnabled = false;

            // borramos todo lo que había antes
            contexto.clearRect(0, 0, canvas.width, canvas.height);
            
            // si hay texto para dibujar
            if (texto) {
                try {
                    // generamos la matriz del qr
                    var resultado = this.generar(texto);
                    // si algo salió mal, paramos
                    if(!resultado) return false;
                    
                    // sacamos la matriz y su tamaño
                    var matriz = resultado.matriz;
                    var tamanioMatriz = resultado.tamanio;
                    // dejamos un margen de 6 cuadritos alrededor (zona tranquila obligatoria)
                    var margen = 6;
                    
                    // calculamos el tamaño final en píxeles reales
                    var tamanioTotal = (tamanioMatriz + margen * 2) * tamanioPixel;
                    
                    // asignamos tamaño al canvas (sin estilo css que lo estire)
                    canvas.width = tamanioTotal;
                    canvas.height = tamanioTotal;
                    
                    // pintamos todo de blanco primero (el fondo)
                    contexto.fillStyle = '#FFFFFF';
                    contexto.fillRect(0, 0, tamanioTotal, tamanioTotal);
                    
                    // ahora dibujamos los cuadraditos negros
                    contexto.fillStyle = '#000000';
                    // recorremos toda la matriz
                    for (var fila = 0; fila < tamanioMatriz; fila++) {
                        for (var columna = 0; columna < tamanioMatriz; columna++) {
                            // si el valor es 1, dibujamos un cuadradito negro
                            if (matriz[fila][columna] === 1) {
                                contexto.fillRect((columna + margen) * tamanioPixel, (fila + margen) * tamanioPixel, tamanioPixel, tamanioPixel);
                            }
                        }
                    }
                    // todo salió bien
                    return true;
                } catch (e) {
                    // si hubo algún error, lo mostramos en la consola
                    console.error(e);
                    return false;
                }
            }
            // no había texto, así que no dibujamos nada
            return false;
        }
    };

//  TITULO QR GENERADO

    // esta función prende o apaga el modo de qr dinámico (que se actualiza mientras escribes)
    function toggleQRDinamico() {
        // buscamos el botón que activa el qr dinámico
        var boton = document.getElementById('btnQRDinamico');
        // buscamos el botón normal de generar
        var botonGenerar = document.getElementById('btnGenerarQR') || document.querySelector('.botonguardar[type="submit"]');
        
        // cambiamos el estado: si estaba apagado lo prendemos, si estaba prendido lo apagamos
        qrDinamicoActivo = !qrDinamicoActivo;
        
        // si lo acabamos de prender
        if (qrDinamicoActivo) {
            // creamos el panel donde se verá el qr en tiempo real
            crearContenedorDinamico();
            // activamos la escucha de los campos (para detectar cuando escribes)
            agregarListenersCampos();
            
            // escondemos el botón normal porque ahora usamos el dinámico
            if (botonGenerar) botonGenerar.style.display = 'none';
            
            // cambiamos el texto y color del botón
            boton.innerHTML = '⏹ detener QR Dinámico';
            boton.style.backgroundColor = '#dc3545'; // rojo
            boton.style.color = '#ffffff'; // blanco
            
            // esperamos un poquito y dibujamos el qr de inmediato
            setTimeout(function() { 
                actualizarQRDinamico(); 
                renderizarListaProductos(); 
            }, 100);
            
        } else {
            // si lo acabamos de apagar volvemos a la normalidad
            boton.innerHTML = 'Crear QR Dinámico';
            boton.style.backgroundColor = ''; 
            boton.style.color = '';
            if (botonGenerar) botonGenerar.style.display = 'inline-block';
            
            // escondemos el panel del qr dinámico
            var contenedor = document.getElementById('contenedorQRDinamico');
            if (contenedor) contenedor.style.display = 'none';
            // dejamos de escuchar los campos
            removerListenersCampos();
        }
    }


// TITULO CONTENEDOR DEL FORMULARIO 

    // esta función crea el panel visual donde se ve el qr dinámico en tiempo real
   function crearContenedorDinamico() {
        // buscamos si ya existe el contenedor
        var contenedor = document.getElementById('contenedorQRDinamico');
        
        // si no existe, lo creamos desde cero
        if (!contenedor) {
            contenedor = document.createElement('div');
            contenedor.id = 'contenedorQRDinamico';
            // Estilos del contenedor principal
            contenedor.style.cssText = 'margin-top:20px; display:flex; flex-wrap:wrap; gap:20px; width: 100%; box-sizing: border-box;';
            
            // AQUÍ ESTÁ EL HTML DEL PANEL. FÍJATE EN EL BOTÓN "GUARDAR DATOS"
            contenedor.innerHTML = `
                <div style="flex: 2; min-width: 300px; width: 100%; border: 2px dashed #007cba; padding: 20px; border-radius: 10px; background: #f9f9f9; box-sizing: border-box;">
                    <h3 style="text-align:center; color:#007cba; margin-top:0;">Editor de Etiqueta</h3>
                    
                    <div style="display:flex; justify-content:center; margin-bottom: 20px; width: 100%;">
                        <div id="etiquetaVisual" style="background:white; padding:10px; border:1px solid #ccc; box-shadow:0 2px 5px rgba(0,0,0,0.1); max-width: 100%; overflow-x: auto;">
                            
                            <div id="productoLabelDinamico" style="width:100%; max-width:500px; font-family:monospace; display:flex; flex-direction:column; word-wrap: break-word;">
                                <div style="font-size:16px; font-weight:bold; margin-bottom:5px;">
                                    ITEM: <span id="sku_dinamico"></span> <span id="producto_dinamico"></span>
                                </div>
                                <div style="font-size:16px; font-weight:bold; margin-bottom:5px;">
                                    CANT: <span id="cantidad_dinamico"></span> | LOTE: <span id="lote_dinamico"></span>
                                </div>
                                
                                <div style="display:flex; justify-content:center; align-items:center; margin-bottom:15px; margin-top:10px;">
                                    <div id="contenedorQRPro" style="display:flex; justify-content:center; margin-top:10px;"></div>
                                </div>
                                
                                <div style="text-align:center; font-size:12px; font-weight:bold; margin-bottom:5px;" id="fecha_dinamico"></div>
                                
                                <div style="text-align:center; margin-top:10px;">
                                    <svg id="barcodeDinamico"></svg>
                                    <div id="sku_barcode_dinamico" style="font-size:12px; font-weight:bold;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="mensajeQRDinamico" style="text-align:center; margin-bottom:10px; color:#666; font-size:11px;"></div>

                    <div style="text-align:center;">
                        <button class="botonguardar" type="submit" form="formularioQR" onclick="return prepararDatosParaGuardar()" style="background-color:#28a745; width:100%; margin-bottom:5px; color:white; padding:10px; border:none; border-radius:5px; cursor:pointer;">Guardar Datos</button>
                        
                        <div style="display:flex; gap:5px; margin-top: 5px; flex-wrap: wrap;">
                            <button class="botonguardar" onclick="descargarEtiquetaDinamica()" style="flex:1; min-width: 100px;">Descargar</button>
                            <button class="botonguardar" onclick="imprimirQR()" style="flex:1; min-width: 100px;">Imprimir</button>
                        </div>
                    </div>
                </div>

                <div style="flex: 1; min-width: 300px; border: 1px solid #ddd; padding: 20px; border-radius: 10px; background: #fff; height: fit-content; box-sizing: border-box;">
                    <h3 style="color:#333; margin-top:0; font-size:16px;"> Historial De QR (<span id="contadorProductos">0</span>)</h3>
                    <div id="listaProductosContainer" style="max-height: 500px; overflow-y: auto; padding-right:5px;"></div>
                    <div style="margin-top:10px; text-align:right;">
                        <button type="button" onclick="limpiarHistorial()" style="font-size:11px; color:#dc3545; background:none; border:none; cursor:pointer; text-decoration:underline;">Borrar todo</button>
                    </div>
                </div>
            `;
            
            var formulario = document.getElementById('contenedorQr') || document.getElementById('formularioQR');
            if (formulario && formulario.parentNode) {
                if (formulario.nextSibling) formulario.parentNode.insertBefore(contenedor, formulario.nextSibling);
                else formulario.parentNode.appendChild(contenedor);
            }
        }
        contenedor.style.display = 'flex';
    }

//  TITULO QR GENERADO

  // esta función actualiza la imagen del qr y los textos en tiempo real mientras escribes
  // Variable para controlar la espera al escribir
    var timeoutActualizacion;

    function actualizarQRDinamico() {
        // Si la casilla de "QR Dinámico" no está activada, no hacemos nada
        // (Asegúrate de tener esa variable definida o quita esta línea si siempre quieres generar)
        if (typeof qrDinamicoActivo !== 'undefined' && !qrDinamicoActivo) return;

        if (timeoutActualizacion) clearTimeout(timeoutActualizacion);
        
        timeoutActualizacion = setTimeout(function() {
            
            
            var inputSku = document.getElementById('sku');
            var skuRaw = inputSku ? inputSku.value.trim() : ''; 
            var skuParaUrl = encodeURIComponent(skuRaw);

            
            // Esto llena los datos escritos arriba del QR
            var getVal = function(id) { var el = document.getElementById(id); return el ? el.value.trim() : ''; };
            
            if(document.getElementById('sku_dinamico')) document.getElementById('sku_dinamico').textContent = skuRaw; 
            if(document.getElementById('producto_dinamico')) document.getElementById('producto_dinamico').textContent = ' ' + getVal('producto');
            if(document.getElementById('cantidad_dinamico')) document.getElementById('cantidad_dinamico').textContent = ' ' + getVal('cantidad');
            if(document.getElementById('lote_dinamico')) document.getElementById('lote_dinamico').textContent = getVal('lote');
            if(document.getElementById('lote_dinamico')) document.getElementById('lote_dinamico').textContent = getVal('lote');
            if(document.getElementById('fecha_dinamico')) document.getElementById('fecha_dinamico').textContent = getVal('fecha_fabricacion');
            

            
            if (skuRaw && typeof JsBarcode !== 'undefined') {
                try { 
                    JsBarcode('#barcodeDinamico', skuRaw, { format: 'CODE128', lineColor: '#000', width: 2, height: 40, displayValue: false, margin:0 }); 
                } catch (e) {} 
            }

            
            var contenedor = document.getElementById("contenedorQRPro");
            
            // Limpiamos el QR anterior para que no se acumulen
            contenedor.innerHTML = ""; 

            if (!skuRaw) return; // Si no hay SKU, no dibujamos nada

           
            // Preparamos el contenido del QR
            var dominio = "https://www.itred.cl"; 
            var ruta = "/php/ingreso_ventas/consultar_productos/ver_producto.php"; 
            var contenidoQR = dominio + ruta + "?sku=" + skuParaUrl;

        // Limpiamos el QR anterior
        contenedor.innerHTML = ""; 

      
        contenedor.style.backgroundColor = "white"; 
        contenedor.style.padding = "20px"; // Este es el borde blanco necesario para escanear
        contenedor.style.display = "inline-block"; // Para que se ajuste al tamaño

        // CREAMOS EL NUEVO QR
        try {
            new QRCode(contenedor, {
                text: contenidoQR,
                width: 180,      
                height: 180,
                colorDark : "#000000",
                colorLight : "#ffffff",
                
                // Mantenemos Nivel L para que sea limpio (pocos puntos)
                correctLevel : QRCode.CorrectLevel.L 
            });

            if(mensaje) {
                mensaje.textContent = 'QR Optimizado Listo';
                mensaje.style.color = '#28a745';
            }
        } catch (e) {
            console.error("Error generando QR Pro:", e);
        }

        }, 100); // Espera 100ms después de dejar de escribir
    }

    // esta función se llama al presionar el botón guardar
    window.prepararDatosParaGuardar = function() {
        // 1. Obtener datos
        var sku = document.getElementById("sku").value.trim();
        var producto = document.getElementById("producto").value.trim();
        var lote = document.getElementById("lote").value.trim();
        var fecha = document.getElementById("fecha_fabricacion").value; // Capturamos fecha para validar

        // 2. Validación
        if (!sku || !producto || !lote) {
            alert("⚠️ Por favor complete SKU, Producto y Lote.");
            return false; // Detiene el envío
        }

        // 3. Guardar en la lista visual de la derecha 
        if(typeof guardarProductoEnListaVisual === 'function') {
            guardarProductoEnListaVisual(true); 
        }

        // 4. PREPARAR EL ENVÍO AL SERVIDOR (AQUÍ ESTABA EL ERROR)
        var formulario = document.getElementById("formularioQR") || document.querySelector("form");
        
        if(formulario) {
           
            formulario.action = ""; 
            formulario.method = "POST";
            
            return true; // Permitir envío
        }
        
        return false;
    };

  
    window.prepararDatosParaGuardarEstatico = function() {
        // 1. Obtener valores de los campos
        var sku = document.getElementById("sku").value.trim();
        var producto = document.getElementById("producto").value.trim().replace(/,/g, '.');
        var cantidad = document.getElementById("cantidad").value.trim();
        var lote = document.getElementById("lote").value.trim();
        var fecha = document.getElementById("fecha_fabricacion").value; 
        var serieInicio = document.getElementById("serie_inicio").value.trim();
        var serieFinal = document.getElementById("serie_final").value.trim();

        // 2. Validar campos obligatorios
        if (!sku || !producto || !lote || !fecha || !serieInicio || !serieFinal) {
            alert("⚠️ Por favor, complete todos los campos antes de guardar.");
            return false; 
        }

        // 3. Confirmar acción (TAL CUAL TU CÓDIGO ANTIGUO)
        if(!confirm("¿Está seguro de guardar este producto en la base de datos?")) {
            return false; 
        }

        // 4. Formatear datos
        // Formatear fecha a AAAA-MM-DD
        // (Si la fecha ya viene del input date, suele estar lista, pero dejamos tu logica)
        var fechaFormateada = fecha; 
        if(fecha.includes("-")) {
             const partes = fecha.split("-");
             // Aseguramos que sea YYYY-MM-DD
             if(partes[0].length === 4) { fechaFormateada = `${partes[0]}-${partes[1]}-${partes[2]}`; }
        }

        const datosQR = [sku, producto, cantidad, lote, fechaFormateada, serieInicio, serieFinal].join(",");

        // 5. Asignar datos al input hidden
        var inputData = document.getElementById("data");
        if(inputData) {
            inputData.value = datosQR;
        } else {
            console.error("No se encontró input hidden data");
            return false;
        }

        // 6. ASEGURAR QUE SE ENVÍA A LA MISMA PÁGINA (Para ver el QR)
        var formulario = document.getElementById("formularioQR") || document.querySelector("form");
        if(formulario) {
            formulario.action = window.location.href; 
            formulario.method = "POST";
        }

        // 7. Retornar true para que el botón submit haga su trabajo
        return true; 
    };

    window.generarQR = function(event) {
        // Llamamos a la función con el nombre nuevo
        return prepararDatosParaGuardarEstatico(); 
    };

    

    // Función para eliminar UN producto usando el archivo externo
    window.eliminarProducto = function(skuEliminar) {
        
        if(!confirm('¿Estás seguro de eliminar el SKU ' + skuEliminar + ' de la Base de Datos?')) {
            return;
        }

        // Creamos un formulario invisible
        var form = document.createElement("form");
        form.method = "POST";
        // Lo enviamos al archivo que acabas de crear
        form.action = "/php/ingreso_ventas/consultar_productos/borrar_historial_qr.php"; 

        // Le pasamos el SKU que queremos borrar
        var inputSku = document.createElement("input");
        inputSku.type = "hidden";
        inputSku.name = "sku";
        inputSku.value = skuEliminar;

        form.appendChild(inputSku);
        document.body.appendChild(form);
        
        // Enviamos (esto recargará la página)
        form.submit();
    };

    // Función para borrar TODO el historial
    window.limpiarHistorial = function() {
        
        if(!confirm("¿⚠️ ESTÁS SEGURO DE BORRAR TODO EL HISTORIAL DE LA BASE DE DATOS?")) {
            return;
        }

        var form = document.createElement("form");
        form.method = "POST";
        form.action = "/php/ingreso_ventas/consultar_productos/borrar_historial_qr.php";

        // Le enviamos la señal "todo = si"
        var inputTodo = document.createElement("input");
        inputTodo.type = "hidden";
        inputTodo.name = "todo";
        inputTodo.value = "si";

        form.appendChild(inputTodo);
        document.body.appendChild(form);
        form.submit();
    }

    
    function cargarListaDesdeBD() {
      
        var texto = (typeof DATOS_HISTORIAL_RAW !== 'undefined') ? DATOS_HISTORIAL_RAW : "";

        if(!texto || texto.trim() === "") {
            listaProductos = [];
            renderizarListaProductos();
            return;
        }
        
        var lista = [];
        var lineas = texto.split(";;;");
        
        for(var i=0; i<lineas.length; i++) {
            if(lineas[i] && lineas[i].trim() !== "") {
                var campos = lineas[i].split("|||");
                if(campos.length >= 5) {
                    lista.push({ 
                        sku: campos[0], 
                        nombre: campos[1], 
                        cantidad: campos[2], 
                        lote: campos[3], 
                        fecha: campos[4] 
                    });
                }
            }
        }
        listaProductos = lista;
        renderizarListaProductos();
    }

    // Esta función pone los datos en el formulario para editar
    window.cargarProductoEnFormulario = function(skuBuscado) {
        var prod = null;
        for(var i=0; i<listaProductos.length; i++) { 
            if(listaProductos[i].sku === skuBuscado) { 
                prod = listaProductos[i]; 
                break; 
            } 
        }
        
        if (prod) {
            document.getElementById("sku").value = prod.sku;
            document.getElementById("producto").value = prod.nombre;
            document.getElementById("cantidad").value = prod.cantidad;
            document.getElementById("lote").value = prod.lote;
            document.getElementById("fecha_fabricacion").value = prod.fecha;
            
            if(!qrDinamicoActivo) toggleQRDinamico();
            else actualizarQRDinamico();
            
            document.getElementById('formularioQR').scrollIntoView({behavior: 'smooth'});
            
            // Opcional: Avisar al usuario
            // alert("Edita los datos y presiona Guardar. Se sobrescribirá la información.");
        }
    };

    // Función visual (No cambia)
    function renderizarListaProductos() {
        var container = document.getElementById("listaProductosContainer");
        var contador = document.getElementById("contadorProductos");
        
        if (!container) return;
        
        container.innerHTML = "";
        if(contador) contador.textContent = listaProductos.length;

        for(var i=0; i<listaProductos.length; i++) {
            var prod = listaProductos[i];
            
            var card = document.createElement("div");
            card.style.cssText = "border-bottom:1px solid #eee; padding:10px; background:#fff; display:flex; justify-content:space-between; align-items:center;";
            
            card.innerHTML = `
                <div style="flex:1;">
                    <div style="font-weight:bold; color:#007cba;">${prod.sku}</div>
                    <div style="font-size:12px; color:#333;">${prod.nombre}</div>
                    <div style="font-size:11px; color:#666;">
                        Cant: ${prod.cantidad} | Lote: ${prod.lote}
                    </div>
                </div>
                <div style="display:flex; gap:5px;">
                    <button onclick="cargarProductoEnFormulario('${prod.sku}')" type="button" style="cursor:pointer; background:#6c757d; border:none; padding:5px 8px; border-radius:4px;" title="Editar">✏️</button>
                    <button onclick="eliminarProducto('${prod.sku}')" type="button" style="cursor:pointer; background:#6c757d; color:white; border:none; padding:5px 8px; border-radius:4px;" title="Eliminar">🗑️</button>
                </div>
            `;
            container.appendChild(card);
        }
    }

    // esta función carga los datos de un producto del historial al formulario para editarlo
    window.cargarProductoEnFormulario = function(skuBuscado) {
        // buscamos el producto en la lista
        var prod = null;
        for(var i=0; i<listaProductos.length; i++) { 
            if(listaProductos[i].sku === skuBuscado) { 
                prod = listaProductos[i]; 
                break; 
            } 
        }
        
        // si encontramos el producto
        if (prod) {
            // llenamos todos los campos del formulario con sus datos
            document.getElementById("sku").value = prod.sku;
            document.getElementById("producto").value = prod.nombre;
            document.getElementById("cantidad").value = prod.cantidad;
            document.getElementById("lote").value = prod.lote;
            document.getElementById("fecha_fabricacion").value = prod.fecha;
            
            // si el qr dinámico está apagado, lo prendemos
            if(!qrDinamicoActivo) toggleQRDinamico();
            // si ya estaba prendido, actualizamos el qr
            else actualizarQRDinamico();
            
            // hacemos scroll hacia el formulario para que se vea
            document.getElementById('formularioQR').scrollIntoView({behavior: 'smooth'});
        }
    };

    

    // esta función agrega escuchas a los campos del formulario
    // para que el qr se actualice automáticamente mientras escribes
    function agregarListenersCampos() {
        // lista de todos los campos que queremos escuchar
        var campos = ['sku', 'producto', 'cantidad', 'lote', 'fecha_fabricacion', 'serie_inicio', 'serie_final'];
        // recorremos cada campo
        campos.forEach(function(c) {
            // buscamos el elemento en la página
            var el = document.getElementById(c);
            // si existe
            if(el) { 
                // agregamos escucha para cuando escriben (input)
                el.addEventListener('input', actualizarQRDinamico); 
                // agregamos escucha para cuando cambian (change)
                el.addEventListener('change', actualizarQRDinamico); 
            }
        });
    }
    
    // esta función quita las escuchas de los campos
    function removerListenersCampos() {
        // lista de campos
        var campos = ['sku', 'producto', 'cantidad', 'lote', 'fecha_fabricacion', 'serie_inicio', 'serie_final'];
        // recorremos cada campo
        campos.forEach(function(c) {
            // buscamos el elemento
            var el = document.getElementById(c);
            // si existe
            if(el) { 
                // quitamos la escucha de input
                el.removeEventListener('input', actualizarQRDinamico); 
                // quitamos la escucha de change
                el.removeEventListener('change', actualizarQRDinamico); 
            }
        });
    }

//  TITULO IMPRIMIR Y DESCARGAR

    // TITULO BOTON DESCARGAR

        // esta función descarga la etiqueta completa como imagen png
        window.descargarEtiquetaDinamica = function(e) {
            // si viene de un evento, prevenimos que haga su acción normal
            if(e) e.preventDefault();
            // buscamos el elemento que contiene toda la etiqueta
            var el = document.getElementById('productoLabelDinamico');
            // si no existe, paramos
            if(!el) return;
            
            // actualizamos el qr una última vez antes de descargar
            actualizarQRDinamico();
            
            // esperamos 150 milisegundos para que termine de dibujarse
            setTimeout(function() {
                // verificamos que la librería html2canvas esté cargada
                if(typeof html2canvas === 'undefined') { 
                    alert('Espere...'); 
                    return; 
                }
                
                // convertimos el html a imagen usando html2canvas
                html2canvas(el, { 
                    scale: 3, // aumentamos calidad 3 veces
                    backgroundColor: "#ffffff" // fondo blanco
                }).then(function(canvas) {
                    // creamos un link invisible para descargar
                    var link = document.createElement('a');
                    // nombre del archivo con el sku
                    link.download = 'QR_' + document.getElementById('sku').value + '.png';
                    // convertimos el canvas a imagen png
                    link.href = canvas.toDataURL("image/png");
                    // agregamos el link a la página
                    document.body.appendChild(link);
                    // hacemos click automático para descargar
                    link.click();
                    // quitamos el link de la página
                    document.body.removeChild(link);
                });
            }, 150);
        }

        // 1. FUNCIÓN PARA IMPRIMIR EL QR ESTÁTICO
        window.imprimirQREstatico = function() {
            // Buscamos la etiqueta generada por PHP
            var elemento = document.getElementById('productoLabel');
            
            if (!elemento) {
                alert("❌ No hay QR generado para imprimir. Por favor genere uno primero.");
                return;
            }

            // Abrimos una ventana nueva para imprimir limpio
            var win = window.open('', '_blank');
            win.document.write('<html><head><title>Imprimir QR</title>');
            // Copiamos los estilos básicos para que se vea igual
            win.document.write('<style>');
            win.document.write('body { font-family: sans-serif; display: flex; justify-content: center; padding: 20px; }');
            win.document.write('.producto-label { border: 2px solid #000; padding: 10px; width: 300px; text-align: center; }');
            win.document.write('.negrita { font-weight: bold; }');
            win.document.write('img { max-width: 100%; }');
            win.document.write('</style>');
            win.document.write('</head><body>');
            
            // Pegamos el contenido del QR
            win.document.write(elemento.outerHTML);
            
            win.document.write('</body></html>');
            win.document.close();

            // Esperamos un poquito para que carguen las imágenes y mandamos imprimir
            setTimeout(function() {
                win.focus();
                win.print();
                win.close();
            }, 500);
        };

        // 2. FUNCIÓN PARA DESCARGAR EL QR ESTÁTICO
        window.descargarQREstatico = function() {
            var elemento = document.getElementById('productoLabel');
            
            if (!elemento) {
                alert("❌ No hay QR generado para descargar.");
                return;
            }

            // Verificamos si html2canvas está cargado (es la librería que toma la foto)
            if(typeof html2canvas === 'undefined') {
                alert("⚠️ Error: La librería html2canvas no está cargada.");
                return;
            }

            // Tomamos la "foto" del elemento
            html2canvas(elemento, { 
                scale: 3, // Mejor calidad
                backgroundColor: "#ffffff"
            }).then(function(canvas) {
                // Creamos un enlace temporal para bajar la imagen
                var link = document.createElement('a');
                // Usamos el SKU para el nombre del archivo
                var skuVal = document.getElementById('sku_impresion') ? document.getElementById('sku_impresion').innerText : 'qr';
                link.download = 'QR_' + skuVal + '.png';
                link.href = canvas.toDataURL("image/png");
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        };


    // TITULO BOTON IMPRIMIR 

        // esta función abre una ventana de impresión con la etiqueta
        window.imprimirQR = function(e) {
            // si viene de un evento, prevenimos su acción normal
            if(e) e.preventDefault();
            
            // obtenemos el canvas del qr
            var canvas = document.getElementById('canvasQRDinamico');
            // convertimos el canvas a imagen en formato base64
            var qrImgSrc = canvas.toDataURL("image/png");
            // sacamos el sku
            var sku = document.getElementById("sku").value;
            
            // abrimos una ventana nueva en blanco
            var win = window.open('', '_blank');
            // escribimos el inicio del html
            win.document.write('<html><head><title>Imprimir</title></head><body>');
            // creamos un div centrado con borde
            win.document.write('<div style="text-align:center; font-family:monospace; border:1px solid #ccc; padding:20px; width:400px;">');
            // escribimos el título con el sku
            win.document.write('<h3>ITEM: '+sku+'</h3>');
            // insertamos la imagen del qr
            win.document.write('<img src="' + qrImgSrc + '" style="width:250px; image-rendering:pixelated;"/>');
            // agregamos espacios
            win.document.write('<br><br>');
            
            // si existe la librería de códigos de barras
            if(typeof JsBarcode !== 'undefined') {
                // creamos un svg para el código de barras
                win.document.write('<svg id="barcodePrint"></svg>');
                // cargamos la librería en la ventana nueva
                win.document.write('<script src="../../programas/JsBarcode.all.min.js"></script>');
                // script que genera el código de barras y luego imprime
                win.document.write('<script>JsBarcode("#barcodePrint", "'+sku+'", {format:"CODE128", displayValue:true, fontSize:14}); window.onload = function(){ window.print(); window.close(); }</script>');
            } else {
                // si no hay librería, solo ponemos el sku en texto
                win.document.write('<strong>'+sku+'</strong>');
                // script que imprime y cierra
                win.document.write('<script>window.onload = function(){ window.print(); window.close(); }</script>');
            }
            // cerramos el div
            win.document.write('</div>');
            // cerramos el html
            win.document.write('</body></html>');
            // cerramos el documento para que se renderice
            win.document.close();
        }
        
        // función  de generar ar 
        function generarQR() {
            // si los datos están listos para guardar
            if(prepararDatosParaGuardar()) {
                // preguntamos si quiere guardar en la base de datos
                return confirm("¿Guardar en la base de datos?");
            }
            // si faltaban datos, no guardamos
            return false;
        }
        

        // esta función carga scripts externos (librerías) de forma dinámica
        function loadScript(url, callback) {
            // creamos un elemento script
            var script = document.createElement('script');
            // le ponemos la url de donde descargar
            script.src = url;
            // cuando termine de cargar, ejecutamos el callback
            script.onload = callback;
            // agregamos el script al head de la página
            document.head.appendChild(script);
        }
        
        // esta función se ejecuta cuando la página termina de cargar
        document.addEventListener("DOMContentLoaded", function () {
            // buscamos el campo sku
            var sku = document.getElementById("sku");
            // si existe y ya tiene algo escrito (modo edición)
            if (sku && sku.value.trim() !== "") 
                // activamos el qr dinámico automáticamente
                toggleQRDinamico();
            
            // cargamos la librería html2canvas para poder descargar imágenes
            loadScript('../../programas/html2canvas.min.js', function() { 
                console.log('Librería lista.'); 
            });
        });

//  -------------------------------------------------------------------------------------------------------------
//  -------------------------------------- FIN itred spa generar_qr .js -----------------------------------------
//  -------------------------------------------------------------------------------------------------------------

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 
// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  --------------------------------------------------------------------------------------------------------------
    -------------------------------------- INICIO ITred Spa plantilla .JS ----------------------------------------
    -------------------------------------------------------------------------------------------------------------- */

//TITULO MAPEO SECCION

    // sin funcion

// TITULO HTML
    
    // sin funcion


// TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN

    // sin funcion

// TITULO OBTENER ESTILOS

    // sin funcion


// TITULO CABECERA PRINCIPAL

    // sin funcion


// TITULO PALETA COLORES

// funcion para no mezclarvariables con otras partes
(function() {
    // variable que guarda el color negro como el seleccioando por defecto
    var color_seleccionado = '#000000';
    // busca la lista despplegable de que cosa vamos a pintar
    var destino_color = document.getElementById('destino_color');
    // esto busca el el selector de colores personalizado (el circulito con varios colores)
    var color_custom = document.getElementById('color_custom');
    // busca todos los circulos de colores predefinidos menos el personalizado
    var circulos = document.querySelectorAll('.color_circulo:not(.color_personalizado)');
    // crea una accion para resalatar el circulo que elegimos
    function marcar_circulo_seleccionado(color) {
        // recorre uno por uno todos los circulos de colores
        circulos.forEach(function(c) {
            // le quita la marca de seleccionado a todos 
            c.classList.remove('seleccionado');
            // pone un borde gris normal
            c.style.border = '2px solid #e0e0e0';
            // si el color del ciruclo es igual al que buscamos 
            if (c.getAttribute('data-color') === color) {
                // le pone la marca seleccionada
                c.classList.add('seleccionado');
                // pone un borde azul mas grueso 
                c.style.border = '3px solid #0066ff';
            }
        });
    }
    
    // crea la accion principal para pintar cosas
    function aplicar_color(color) {
        // guarada el color que se eligio en la memoria
        color_seleccionado = color;
        // llama a la aacion de arriba para resaltar el borde del ciruclo del color 
        marcar_circulo_seleccionado(color);
        // si no sabe que cosa pintar se detiene aqui
        if (!destino_color) return;
        // obtiene el nombre de lo que vamos a pintar ejemplo fondo
        var destino = destino_color.value;
        // busca el campo d etetxo oculto correspondiente
        var input_destino = document.getElementById(destino);
        // si encuentra ese campo oculto
        if (input_destino) {
            // le guarda el valor del color para enviarlo despues
            input_destino.value = color;
        }
        
        // Guardar en memoria para la sección activa
        if (typeof guardar_estilo_memoria === 'function') {
            // guarda el cambio para que no se pierda
            guardar_estilo_memoria(destino, color);
        }
        // busca el cuadrito de muestra de color
        var preview_color = document.getElementById('preview_color');
        // si existe ese cuadrito
        if (preview_color) {
            // lo pinta del color elegido
            preview_color.style.backgroundColor = color;
        }
        
        // modo individual: aplicar solo al elemento seleccionado
        if (window.modo_seleccion_individual && typeof window.obtener_elemento_seleccionado === 'function') {
            // busca cual fue la cosita que se selecciono con el click
            var el = window.obtener_elemento_seleccionado();
            // si hay algo seleccionado
            if (el) {
                // revisa que tipo de cambio que uno quiere hacer
                switch(destino) {
                    // si es fondo
                    case 'color_fondo':
                    // o fondo interior
                    case 'color_fondo_interior':
                    // o color de botonb
                    case 'color_boton':
                    // o color de campos/inputs/cajas de texto
                    case 'color_campos':
                        // pinta el fondo a ese elemento unico
                        el.style.backgroundColor = color;
                        // termina aqui 
                        break;
                    // si es color de letra
                    case 'color_texto':
                    //  o el color de letra pero de boton
                    case 'color_boton_texto':
                    // o si es color de texto de las cajas de texto
                    case 'color_texto_campos':
                        
                        el.style.color = color;
                        break;
                    case 'color_borde':
                        el.style.borderColor = color;
                        break;
                }
                return; // No aplicar globalmente
            }
        }
        
        // MODO GLOBAL: aplicar a todos los elementos
        switch(destino) {
            case 'color_fondo':
                aplicar_estilo_iframe('body', 'backgroundColor', color);
                break;
            case 'color_fondo_interior':
                aplicar_estilo_iframe('main, .contenedor, .container, .opciones', 'backgroundColor', color);
                break;
            case 'color_texto':
                aplicar_estilo_iframe('body, p, span, label, h1, h2, h3, h4, h5, h6, td, th, a', 'color', color);
                break;
            case 'color_borde':
                aplicar_estilo_iframe('button, .btn, input, select, textarea, table, th, td', 'borderColor', color);
                break;
            case 'color_boton':
                aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'backgroundColor', color);
                break;
            case 'color_boton_texto':
                aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'color', color);
                break;
            case 'color_boton_hover':
                break;
            case 'color_campos':
                aplicar_estilo_iframe('input:not([type="submit"]):not([type="button"]), textarea, select', 'backgroundColor', color);
                break;
            case 'color_texto_campos':
                aplicar_estilo_iframe('input:not([type="submit"]):not([type="button"]), textarea, select', 'color', color);
                break;
        }
    }
    
    circulos.forEach(function(circulo) {
        circulo.addEventListener('click', function() {
            var color = circulo.getAttribute('data-color');
            aplicar_color(color);
        });
    });
    
    if (color_custom) {
        color_custom.addEventListener('input', function() {
            aplicar_color(color_custom.value);
        });
    }
    
    if (destino_color) {
        destino_color.addEventListener('change', function() {
            var destino = destino_color.value;
            var input_destino = document.getElementById(destino);
            if (input_destino && input_destino.value) {
                marcar_circulo_seleccionado(input_destino.value);
            }
        });
    }
})();
(function() {
    var input_logo = document.getElementById('input_logo');
    var logo_preview = document.getElementById('logo_preview_cabecera');
    
    if (input_logo && logo_preview) {
        input_logo.addEventListener('change', function(e) {
            var archivo = e.target.files[0];
            if (!archivo) return;
            
            var lector = new FileReader();
            lector.onload = function(evento) {
                logo_preview.src = evento.target.result;
            };
            lector.readAsDataURL(archivo);
        });
    }
    
    var input_fondo = document.getElementById('input_fondo');
    if (input_fondo) {
        input_fondo.addEventListener('change', function(e) {
            var archivo = e.target.files[0];
            if (!archivo) return;
            
            var lector = new FileReader();
            lector.onload = function(evento) {
                aplicar_estilo_iframe('body', 'backgroundImage', 'url(' + evento.target.result + ')');
                aplicar_estilo_iframe('body', 'backgroundSize', 'cover');
                aplicar_estilo_iframe('body', 'backgroundPosition', 'center');
            };
            lector.readAsDataURL(archivo);
        });
    }
    
    var input_fondo_interior = document.getElementById('input_fondo_interior');
    if (input_fondo_interior) {
        input_fondo_interior.addEventListener('change', function(e) {
            var archivo = e.target.files[0];
            if (!archivo) return;
            
            var lector = new FileReader();
            lector.onload = function(evento) {
                aplicar_estilo_iframe('main, .contenedor, .container', 'backgroundImage', 'url(' + evento.target.result + ')');
                aplicar_estilo_iframe('main, .contenedor, .container', 'backgroundSize', 'cover');
            };
            lector.readAsDataURL(archivo);
        });
    }
})();

(function() {
    var colores = [
        'color_fondo',
        'color_fondo_interior',
        'color_principal',
        'color_texto',
        'color_borde',
        'color_boton',
        'color_boton_texto',
        'color_boton_hover',
        'color_campos',
        'color_texto_campos'
    ];
    
    function aplicar_color_input(id, valor) {
        var preview_color = document.getElementById('preview_color');
        
        if (id === 'color_fondo') {
            aplicar_estilo_iframe('body', 'backgroundColor', valor);
            if (preview_color) preview_color.style.backgroundColor = valor;
        }
        
        if (id === 'color_fondo_interior') {
            aplicar_estilo_iframe('main, .contenedor, .container, .opciones', 'backgroundColor', valor);
        }
        
        if (id === 'color_texto') {
            aplicar_estilo_iframe('body, p, span, label, h1, h2, h3, h4, h5, h6, td, th', 'color', valor);
        }
        
        if (id === 'color_boton') {
            aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'backgroundColor', valor);
        }
        
        if (id === 'color_boton_texto') {
            aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'color', valor);
        }
        
        if (id === 'color_borde') {
            aplicar_estilo_iframe('button, .btn, input, select, textarea, table, th, td', 'borderColor', valor);
        }
        
        if (id === 'color_campos') {
            aplicar_estilo_iframe('input, textarea, select', 'backgroundColor', valor);
        }
        
        if (id === 'color_texto_campos') {
            aplicar_estilo_iframe('input, textarea, select', 'color', valor);
        }
        
        if (id === 'color_principal') {
            aplicar_estilo_iframe('a, .enlace, .link', 'color', valor);
            aplicar_estilo_iframe('.cabecera, header, nav', 'backgroundColor', valor);
        }
    }
    
    colores.forEach(function(id) {
        var input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function() {
                aplicar_color_input(id, input.value);
            });
        }
    });
    
    var btns_seccion = document.querySelectorAll('.btn_seccion');
    var iframe_preview = document.getElementById('iframe_preview');

    var rutas_secciones = {
        'pagina_inicio': '/php/ingreso_ventas/renderizar_menu.php?pagina=ingreso_ventas&preview=1',
        'crear_usuario': '/php/ingreso_ventas/renderizar_menu.php?pagina=usuarios&preview=1',
        'dashboard': '/php/ingreso_ventas/renderizar_menu.php?pagina=ingreso_ventas&preview=1',
        'ingreso_factura': '/php/ingreso_ventas/renderizar_menu.php?pagina=factura&preview=1',
        'ingreso_productos': '/php/ingreso_ventas/renderizar_menu.php?pagina=ventas&preview=1',
        'ingreso_datos': '/php/ingreso_ventas/renderizar_menu.php?pagina=ingreso_datos&preview=1',
        'generar_qr': '/php/ingreso_ventas/renderizar_menu.php?pagina=generar_qr&preview=1',
        'normalizar_qr': '/php/ingreso_ventas/renderizar_menu.php?pagina=normalizar_qr&preview=1',
        'buscar': '/php/ingreso_ventas/renderizar_menu.php?pagina=buscar&preview=1',
        'usuarios': '/php/ingreso_ventas/renderizar_menu.php?pagina=usuarios&preview=1',
        'plantilla': '/php/ingreso_ventas/renderizar_menu.php?pagina=plantilla&preview=1',
        'generar_respaldo': '/php/ingreso_ventas/renderizar_menu.php?pagina=respaldo&preview=1'
    };

    btns_seccion.forEach(function(btn) {
        btn.addEventListener('click', function() {
            btns_seccion.forEach(function(b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            
            var seccion = btn.getAttribute('data-seccion');
            var input_seccion = document.getElementById('seccion_activa');
            if (input_seccion) {
                input_seccion.value = seccion;
            }
            
            if (iframe_preview && rutas_secciones[seccion]) {
                iframe_preview.src = rutas_secciones[seccion];
            }
        });
    });
    
    if (iframe_preview) {
        iframe_preview.addEventListener('load', function() {
            aplicar_todos_estilos();
        });
    }
})();

// TITULO FORMA DE BOTONES
(function() {
    var borde_ancho = document.getElementById('borde_ancho');
    var borde_ancho_valor = document.getElementById('borde_ancho_valor');
    var borde_radio = document.getElementById('borde_radio');
    var borde_radio_valor = document.getElementById('borde_radio_valor');
    var borde_estilo = document.getElementById('borde_estilo');
    
    function aplicar_bordes() {
        var preview_boton = document.getElementById('preview_boton');
        
        var ancho = borde_ancho ? borde_ancho.value : '2';
        var radio = borde_radio ? borde_radio.value : '5';
        var estilo = borde_estilo ? borde_estilo.value : 'solid';
        var color = document.getElementById('color_borde');
        var color_valor = color ? color.value : '#000000';
        
        // modo individual: aplicar solo al elemento seleccionado
        if (window.modo_seleccion_individual && typeof window.obtener_elemento_seleccionado === 'function') {
            var el = window.obtener_elemento_seleccionado();
            if (el) {
                el.style.borderWidth = ancho + 'px';
                el.style.borderStyle = estilo;
                el.style.borderColor = color_valor;
                el.style.borderRadius = radio + 'px';
                
                // Guardar en memoria
                if (typeof guardar_estilo_memoria === 'function') {
                    if (borde_ancho) guardar_estilo_memoria('borde_ancho', borde_ancho.value);
                    if (borde_estilo) guardar_estilo_memoria('borde_estilo', borde_estilo.value);
                    if (borde_radio) guardar_estilo_memoria('borde_radio', borde_radio.value);
                }
                return; // No aplicar globalmente
            }
        }
        
        // MODO GLOBAL: aplicar a todos
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'borderWidth', ancho + 'px');
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'borderStyle', estilo);
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'borderColor', color_valor);
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'borderRadius', radio + 'px');
        
        if (preview_boton) {
            preview_boton.style.borderWidth = ancho + 'px';
            preview_boton.style.borderStyle = estilo;
            preview_boton.style.borderColor = color_valor;
            preview_boton.style.borderRadius = radio + 'px';
            preview_boton.style.backgroundColor = document.getElementById('color_boton') ? document.getElementById('color_boton').value : '#ffffff';
        }
        
        // Guardar en memoria
        if (typeof guardar_estilo_memoria === 'function') {
            if (borde_ancho) guardar_estilo_memoria('borde_ancho', borde_ancho.value);
            if (borde_estilo) guardar_estilo_memoria('borde_estilo', borde_estilo.value);
            if (borde_radio) guardar_estilo_memoria('borde_radio', borde_radio.value);
        }
    }
    
    if (borde_ancho) {
        borde_ancho.addEventListener('input', function() {
            if (borde_ancho_valor) {
                borde_ancho_valor.textContent = borde_ancho.value + 'px';
            }
            aplicar_bordes();
        });
    }
    
    if (borde_radio) {
        borde_radio.addEventListener('input', function() {
            if (borde_radio_valor) {
                borde_radio_valor.textContent = borde_radio.value + 'px';
            }
            aplicar_bordes();
        });
    }
    
    if (borde_estilo) {
        borde_estilo.addEventListener('change', aplicar_bordes);
    }

    
// nuevos cambios


var btnAgregarPreview = document.getElementById('btnAgregarBotonPreview');
var iframe_preview = document.getElementById('iframe_preview');
var contadorBotonesPreview = 0;

if (btnAgregarPreview && iframe_preview) {
    btnAgregarPreview.addEventListener('click', function () {

        var doc;
        try {
            doc = iframe_preview.contentDocument || iframe_preview.contentWindow.document;
        } catch (e) {
            console.log('No se pudo acceder al iframe');
            return;
        }
        if (!doc) return;

        contadorBotonesPreview++;

        var btn = doc.createElement('button');
        btn.type = 'button';
        btn.textContent = 'Botón ' + contadorBotonesPreview;
        btn.className = 'btn btn-preview-dinamico';

        // Estilos base
        btn.style.position = 'relative';
        btn.style.left = '60px';
        btn.style.top = '60px';
        btn.style.padding = '8px 14px';

        // Aplicar bordes actuales del editor
        var ancho = borde_ancho ? borde_ancho.value : '2';
        var radio = borde_radio ? borde_radio.value : '5';
        var estilo = borde_estilo ? borde_estilo.value : 'solid';
        var color = document.getElementById('color_borde');
        var color_valor = color ? color.value : '#000';

        btn.style.borderWidth = ancho + 'px';
        btn.style.borderStyle = estilo;
        btn.style.borderColor = color_valor;
        btn.style.borderRadius = radio + 'px';

        // Color de fondo del botón
        var color_boton = document.getElementById('color_boton');
        if (color_boton) {
            btn.style.backgroundColor = color_boton.value;
        }

        // Insertar en el body del iframe
        doc.body.appendChild(btn);

        // Si el modo arrastrar está activo, hacerlo arrastrable
        if (window.modo_arrastrar && typeof configurar_arrastre_iframe === 'function') {
            configurar_arrastre_iframe();
        }

        if (window.showToast) {
            window.showToast('Botón agregado a la vista previa.', 'success');
        }
    });
}



// fin nuevos cambios

    
    window.aplicar_bordes_global = aplicar_bordes;
})();

// TITULO TIPO DE FUENTE
(function() {
    var tipo_fuente = document.getElementById('tipo_fuente');
    var tamano_fuente = document.getElementById('tamano_fuente');
    
    function aplicar_tipografia() {
        var preview_tipografia = document.getElementById('preview_tipografia');
        
        var fuente = tipo_fuente ? tipo_fuente.value : 'monospace';
        var tamano = tamano_fuente ? tamano_fuente.value : '14';
        
        // modo individual: aplicar solo al elemento seleccionado
        if (window.modo_seleccion_individual && typeof window.obtener_elemento_seleccionado === 'function') {
            var el = window.obtener_elemento_seleccionado();
            if (el) {
                el.style.fontFamily = fuente;
                el.style.fontSize = tamano + 'px';
                
                // Guardar en memoria
                if (typeof guardar_estilo_memoria === 'function') {
                    if (tipo_fuente) guardar_estilo_memoria('tipo_fuente', tipo_fuente.value);
                    if (tamano_fuente) guardar_estilo_memoria('tamano_fuente', tamano_fuente.value);
                }
                return; // No aplicar globalmente
            }
        }
        
        // MODO GLOBAL: aplicar a todos
        aplicar_estilo_iframe('body, p, span, label, h1, h2, h3, h4, h5, h6, button, input, select, textarea, td, th, a', 'fontFamily', fuente);
        aplicar_estilo_iframe('body, p, span, label, td, th, a, input, select, textarea', 'fontSize', tamano + 'px');
        
        if (preview_tipografia) {
            preview_tipografia.style.fontFamily = fuente;
            preview_tipografia.innerHTML = '<span style="font-size:' + tamano + 'px; display:flex; align-items:center; justify-content:center; height:100%;">Aa</span>';
        }
        
        // Guardar en memoria
        if (typeof guardar_estilo_memoria === 'function') {
            if (tipo_fuente) guardar_estilo_memoria('tipo_fuente', tipo_fuente.value);
            if (tamano_fuente) guardar_estilo_memoria('tamano_fuente', tamano_fuente.value);
        }
    }
    
    if (tipo_fuente) {
        tipo_fuente.addEventListener('change', aplicar_tipografia);
    }
    
    if (tamano_fuente) {
        tamano_fuente.addEventListener('change', aplicar_tipografia);
    }
    
    var btns_formato = document.querySelectorAll('.btn_formato');
    btns_formato.forEach(function(btn) {
        btn.addEventListener('click', function() {
            btn.classList.toggle('active');
            var formato = btn.getAttribute('data-formato');
            
            // modo individual
            if (window.modo_seleccion_individual && typeof window.obtener_elemento_seleccionado === 'function') {
                var el = window.obtener_elemento_seleccionado();
                if (el) {
                    if (formato === 'bold') {
                        el.style.fontWeight = btn.classList.contains('active') ? 'bold' : 'normal';
                    }
                    if (formato === 'italic') {
                        el.style.fontStyle = btn.classList.contains('active') ? 'italic' : 'normal';
                    }
                    if (formato === 'underline') {
                        el.style.textDecoration = btn.classList.contains('active') ? 'underline' : 'none';
                    }
                    return;
                }
            }
            
            // MODO GLOBAL
            if (formato === 'bold') {
                var peso = btn.classList.contains('active') ? 'bold' : 'normal';
                aplicar_estilo_iframe('body, p, span, label', 'fontWeight', peso);
            }
            if (formato === 'italic') {
                var estilo = btn.classList.contains('active') ? 'italic' : 'normal';
                aplicar_estilo_iframe('body, p, span, label', 'fontStyle', estilo);
            }
            if (formato === 'underline') {
                var decoracion = btn.classList.contains('active') ? 'underline' : 'none';
                aplicar_estilo_iframe('body, p, span, label', 'textDecoration', decoracion);
            }
        });
    });
    
    window.aplicar_tipografia_global = aplicar_tipografia;
})();

// esta funcion aplica los estilos a la previsualizacion
function aplicar_estilo_iframe(selector, propiedad, valor) {
    var iframe = document.getElementById('iframe_preview');
    if (!iframe) return;
    
    try {
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        if (!doc) return;
        
        var elementos = doc.querySelectorAll(selector);
        elementos.forEach(function(el) {
            el.style[propiedad] = valor;
        });
    } catch (e) {
        console.log('No se pudo acceder al iframe:', e.message);
    }
}

function aplicar_todos_estilos() {
    var color_fondo = document.getElementById('color_fondo');
    var color_fondo_interior = document.getElementById('color_fondo_interior');
    var color_texto = document.getElementById('color_texto');
    var color_boton = document.getElementById('color_boton');
    var color_boton_texto = document.getElementById('color_boton_texto');
    var color_borde = document.getElementById('color_borde');
    var color_campos = document.getElementById('color_campos');
    var color_texto_campos = document.getElementById('color_texto_campos');
    var tipo_fuente = document.getElementById('tipo_fuente');
    var tamano_fuente = document.getElementById('tamano_fuente');
    var borde_ancho = document.getElementById('borde_ancho');
    var borde_radio = document.getElementById('borde_radio');
    var borde_estilo = document.getElementById('borde_estilo');
    
    if (color_fondo) {
        aplicar_estilo_iframe('body', 'backgroundColor', color_fondo.value);
    }
    if (color_fondo_interior) {
        aplicar_estilo_iframe('main, .contenedor, .container, .opciones', 'backgroundColor', color_fondo_interior.value);
    }
    if (color_texto) {
        aplicar_estilo_iframe('body, p, span, label, h1, h2, h3, h4, h5, h6, td, th', 'color', color_texto.value);
    }
    if (color_boton) {
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'backgroundColor', color_boton.value);
    }
    if (color_boton_texto) {
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'color', color_boton_texto.value);
    }
    if (color_borde) {
        aplicar_estilo_iframe('button, .btn, input, select, textarea, table, th, td', 'borderColor', color_borde.value);
    }
    if (color_campos) {
        aplicar_estilo_iframe('input, textarea, select', 'backgroundColor', color_campos.value);
    }
    if (color_texto_campos) {
        aplicar_estilo_iframe('input, textarea, select', 'color', color_texto_campos.value);
    }
    if (tipo_fuente) {
        aplicar_estilo_iframe('body, p, span, label, h1, h2, h3, h4, h5, h6, button, input, select, textarea, td, th, a', 'fontFamily', tipo_fuente.value);
    }
    if (tamano_fuente) {
        aplicar_estilo_iframe('body, p, span, label, td, th, a, input, select, textarea', 'fontSize', tamano_fuente.value + 'px');
    }
    if (borde_ancho && borde_estilo && borde_radio && color_borde) {
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'borderWidth', borde_ancho.value + 'px');
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'borderStyle', borde_estilo.value);
        aplicar_estilo_iframe('button, .btn, input[type="submit"], input[type="button"]', 'borderRadius', borde_radio.value + 'px');
    }
}


//funcion para seleccionar elementos individualmente
(function() {
    var elemento_seleccionado = null;
    var selector_elemento = null;
    
    // Crear indicador visual de elemento seleccionado
    function crear_indicador() {
        var indicador = document.getElementById('indicador_elemento');
        if (!indicador) {
            indicador = document.createElement('div');
            indicador.id = 'indicador_elemento';
            indicador.style.cssText = 'position:fixed; bottom:20px; left:20px; background:#333; color:#fff; padding:10px 15px; border-radius:8px; font-size:14px; z-index:9999; display:none; max-width:300px;';
            document.body.appendChild(indicador);
        }
        return indicador;
    }
    
    // Mostrar qué elemento está seleccionado
    function mostrar_indicador(texto) {
        var indicador = crear_indicador();
        indicador.innerHTML = '<strong>Seleccionado:</strong> ' + texto + '<br><small>Elige un color para aplicar</small>';
        indicador.style.display = 'block';
    }
    
    // Ocultar indicador
    function ocultar_indicador() {
        var indicador = document.getElementById('indicador_elemento');
        if (indicador) indicador.style.display = 'none';
    }
    
    // Obtener identificador único del elemento
    function obtener_identificador(el) {
        if (el.id) {
            return { tipo: 'id', valor: el.id, descripcion: '#' + el.id };
        }
        if (el.className && typeof el.className === 'string') {
            var clases = el.className.split(' ').filter(function(c) { return c.trim() !== ''; });
            if (clases.length > 0) {
                var texto = el.textContent ? el.textContent.trim().substring(0, 20) : '';
                return { 
                    tipo: 'clase_texto', 
                    clase: clases[0], 
                    texto: texto,
                    descripcion: '.' + clases[0] + (texto ? ' ("' + texto + '")' : '')
                };
            }
        }
        var texto = el.textContent ? el.textContent.trim().substring(0, 20) : '';
        var tag = el.tagName.toLowerCase();
        return { 
            tipo: 'tag_texto', 
            tag: tag, 
            texto: texto,
            descripcion: tag + (texto ? ' ("' + texto + '")' : '')
        };
    }
    
    // Obtener tipo de elemento para el selector de destino
    function obtener_tipo_destino(el) {
        var tag = el.tagName.toLowerCase();
        if (tag === 'button' || (tag === 'input' && (el.type === 'submit' || el.type === 'button'))) {
            return 'boton';
        }
        if (tag === 'input' || tag === 'textarea' || tag === 'select') {
            return 'campo';
        }
        if (tag === 'body') {
            return 'fondo';
        }
        if (tag === 'label' || tag === 'p' || tag === 'span' || tag === 'h1' || tag === 'h2' || tag === 'h3' || tag === 'h4' || tag === 'td' || tag === 'th') {
            return 'texto';
        }
        if (tag === 'div' || tag === 'section' || tag === 'main') {
            return 'contenedor';
        }
        return 'otro';
    }
    
    // Aplicar color al elemento seleccionado
    window.aplicar_color_elemento_individual = function(color, propiedad) {
        if (!elemento_seleccionado) return false;
        
        try {
            if (propiedad === 'fondo' || propiedad === 'backgroundColor') {
                elemento_seleccionado.style.backgroundColor = color;
            } else if (propiedad === 'texto' || propiedad === 'color') {
                elemento_seleccionado.style.color = color;
            } else if (propiedad === 'borde' || propiedad === 'borderColor') {
                elemento_seleccionado.style.borderColor = color;
            }
            return true;
        } catch(e) {
            console.log('Error aplicando color:', e);
            return false;
        }
    };
    
    // Configurar listeners en el iframe
    function configurar_iframe_click() {
        var iframe = document.getElementById('iframe_preview');
        if (!iframe) return;
        
        iframe.addEventListener('load', function() {
            try {
                var doc = iframe.contentDocument || iframe.contentWindow.document;
                if (!doc) return;
                
                doc.addEventListener('click', function(e) {
                    // Si está en modo selección individual
                    if (!window.modo_seleccion_individual) return;
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var el = e.target;
                    
                    // Quitar resaltado anterior
                    if (elemento_seleccionado) {
                        elemento_seleccionado.style.outline = '';
                    }
                    
                    // Seleccionar nuevo elemento
                    elemento_seleccionado = el;
                    selector_elemento = obtener_identificador(el);
                    
                    // Resaltar elemento
                    el.style.outline = '3px solid #0066ff';
                    
                    // Mostrar indicador
                    var tipo = obtener_tipo_destino(el);
                    mostrar_indicador(selector_elemento.descripcion + ' <em>(' + tipo + ')</em>');
                    
                    console.log('Elemento seleccionado:', selector_elemento);
                });
                
            } catch(e) {
                console.log('No se pudo configurar click en iframe:', e);
            }
        });
    }
    
    // Toggle modo selección individual
    window.modo_seleccion_individual = false;
    
    window.toggle_modo_seleccion = function() {
        window.modo_seleccion_individual = !window.modo_seleccion_individual;
        var btn = document.getElementById('btn_modo_seleccion');
        
        if (window.modo_seleccion_individual) {
            if (btn) {
                btn.textContent = ' Modo: Individual';
                btn.style.backgroundColor = '#76f790ff';
                btn.style.color = '#fff';
            }
            console.log('Modo selección individual ACTIVADO');
        } else {
            if (btn) {
                btn.textContent = ' Modo: Global';
                btn.style.backgroundColor = '';
                btn.style.color = '';
            }
            // Limpiar selección
            if (elemento_seleccionado) {
                elemento_seleccionado.style.outline = '';
            }
            elemento_seleccionado = null;
            selector_elemento = null;
            ocultar_indicador();
            console.log('Modo selección individual DESACTIVADO');
        }
    };
    
    // Obtener elemento seleccionado actual
    window.obtener_elemento_seleccionado = function() {
        return elemento_seleccionado;
    };
    
    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        configurar_iframe_click();
        
        // Reconfigurar cuando cambia el iframe
        var iframe = document.getElementById('iframe_preview');
        if (iframe) {
            iframe.addEventListener('load', function() {
                configurar_iframe_click();
            });
        }
    });
    
    // También configurar inmediatamente si ya existe el iframe
    configurar_iframe_click();
})();

// el codigo de aqui en adelante es para arrastrar elementos
(function() {
    var elemento_arrastrado = null;
    var offset_x = 0;
    var offset_y = 0;
    var posicion_original = null;
    
    // Toggle modo arrastrar
    window.modo_arrastrar = false;
    
    window.toggle_modo_arrastrar = function() {
        window.modo_arrastrar = !window.modo_arrastrar;
        var btn = document.getElementById('btn_modo_arrastrar');
        
        if (window.modo_arrastrar) {
            if (btn) {
                btn.textContent = 'Arrastrar';
                btn.style.backgroundColor = '#28a745';
                btn.style.color = '#fff';
            }
            configurar_arrastre_iframe();
            console.log('Modo arrastrar ACTIVADO');
        } else {
            if (btn) {
                btn.textContent = 'Arrastrar';
                btn.style.backgroundColor = '';
                btn.style.color = '';
            }
            desactivar_arrastre_iframe();
            console.log('Modo arrastrar DESACTIVADO');
        }
    };
    
    function configurar_arrastre_iframe() {
        var iframe = document.getElementById('iframe_preview');
        if (!iframe) return;
        
        try {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            if (!doc) return;
            
            // Agregar estilos para elementos arrastrables
            var style = doc.getElementById('estilos_arrastre');
            if (!style) {
                style = doc.createElement('style');
                style.id = 'estilos_arrastre';
                style.textContent = '\
                    .arrastrando { \
                        opacity: 0.8; \
                        cursor: grabbing !important; \
                        z-index: 9999 !important; \
                        touch-action: none; \
                    } \
                    .arrastrable-activo { \
                        cursor: grab; \
                        outline: 2px dashed #28a745 !important; \
                        touch-action: none; \
                        -webkit-user-select: none; \
                        -moz-user-select: none; \
                        user-select: none; \
                    } \
                ';
                doc.head.appendChild(style);
            }
            
            // Marcar elementos como arrastrables
            var elementos = doc.querySelectorAll('button, .btn, input, select, textarea, img, h1, h2, h3, label, p, span, div, table');
            elementos.forEach(function(el) {
                el.classList.add('arrastrable-activo');
            });
            
            // Eventos de MOUSE (PC)
            doc.addEventListener('mousedown', iniciar_arrastre);
            doc.addEventListener('mousemove', mover_elemento);
            doc.addEventListener('mouseup', soltar_elemento);
            
            // Eventos de TOUCH (Móvil)
            doc.addEventListener('touchstart', iniciar_arrastre_touch, { passive: false });
            doc.addEventListener('touchmove', mover_elemento_touch, { passive: false });
            doc.addEventListener('touchend', soltar_elemento);
            
        } catch(e) {
            console.log('Error configurando arrastre:', e);
        }
    }
    
    function desactivar_arrastre_iframe() {
        var iframe = document.getElementById('iframe_preview');
        if (!iframe) return;
        
        try {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            if (!doc) return;
            
            // Quitar estilos
            var elementos = doc.querySelectorAll('.arrastrable-activo');
            elementos.forEach(function(el) {
                el.classList.remove('arrastrable-activo');
            });
            
            // Remover eventos MOUSE
            doc.removeEventListener('mousedown', iniciar_arrastre);
            doc.removeEventListener('mousemove', mover_elemento);
            doc.removeEventListener('mouseup', soltar_elemento);
            
            // Remover eventos TOUCH
            doc.removeEventListener('touchstart', iniciar_arrastre_touch);
            doc.removeEventListener('touchmove', mover_elemento_touch);
            doc.removeEventListener('touchend', soltar_elemento);
            
        } catch(e) {
            console.log('Error desactivando arrastre:', e);
        }
    }
    
    //funciones de para arrastrar con el mouse     
    function iniciar_arrastre(e) {
        if (!window.modo_arrastrar) return;
        
        var el = e.target;
        
        // Ignorar body y html
        if (el.tagName === 'BODY' || el.tagName === 'HTML') return;
        
        e.preventDefault();
        
        elemento_arrastrado = el;
        
        // Guardar posición original
        var rect = el.getBoundingClientRect();
        var computed = window.getComputedStyle(el);
        
        posicion_original = {
            position: computed.position,
            left: computed.left,
            top: computed.top
        };
        
        // Calcular offset del click respecto al elemento
        offset_x = e.clientX - rect.left;
        offset_y = e.clientY - rect.top;
        
        // Preparar elemento para mover
        if (computed.position === 'static') {
            el.style.position = 'relative';
        }
        
        el.classList.add('arrastrando');
    }
    
    function mover_elemento(e) {
        if (!elemento_arrastrado || !window.modo_arrastrar) return;
        
        e.preventDefault();
        
        // Calcular nueva posición relativa
        var parent_rect = elemento_arrastrado.offsetParent ? 
            elemento_arrastrado.offsetParent.getBoundingClientRect() : 
            { left: 0, top: 0 };
        
        var nuevo_left = e.clientX - offset_x - parent_rect.left;
        var nuevo_top = e.clientY - offset_y - parent_rect.top;
        
        elemento_arrastrado.style.left = nuevo_left + 'px';
        elemento_arrastrado.style.top = nuevo_top + 'px';
    }
    
    //funciones de para arrastrar con el movil 
    function iniciar_arrastre_touch(e) {
        if (!window.modo_arrastrar) return;
        if (!e.touches || e.touches.length === 0) return;
        
        var touch = e.touches[0];
        
        // Obtener elemento tocado
        var iframe = document.getElementById('iframe_preview');
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        var el = doc.elementFromPoint(touch.clientX, touch.clientY);
        
        if (!el) return;
        
        // Ignorar body y html
        if (el.tagName === 'BODY' || el.tagName === 'HTML') return;
        
        e.preventDefault();
        
        elemento_arrastrado = el;
        
        // Guardar posición original
        var rect = el.getBoundingClientRect();
        var computed = window.getComputedStyle(el);
        
        posicion_original = {
            position: computed.position,
            left: computed.left,
            top: computed.top
        };
        
        // Calcular offset del touch respecto al elemento
        offset_x = touch.clientX - rect.left;
        offset_y = touch.clientY - rect.top;
        
        // Preparar elemento para mover
        if (computed.position === 'static') {
            el.style.position = 'relative';
        }
        
        el.classList.add('arrastrando');
    }
    
    function mover_elemento_touch(e) {
        if (!elemento_arrastrado || !window.modo_arrastrar) return;
        if (!e.touches || e.touches.length === 0) return;
        
        e.preventDefault();
        
        var touch = e.touches[0];
        
        // Calcular nueva posición relativa
        var parent_rect = elemento_arrastrado.offsetParent ? 
            elemento_arrastrado.offsetParent.getBoundingClientRect() : 
            { left: 0, top: 0 };
        
        var nuevo_left = touch.clientX - offset_x - parent_rect.left;
        var nuevo_top = touch.clientY - offset_y - parent_rect.top;
        
        elemento_arrastrado.style.left = nuevo_left + 'px';
        elemento_arrastrado.style.top = nuevo_top + 'px';
    }
    
    // funcion compartida mause y touch esto es para soltar el elemento arrastrado 
    function soltar_elemento(e) {
        if (!elemento_arrastrado) return;
        
        elemento_arrastrado.classList.remove('arrastrando');
        
        // Guardar posición final
        var id = obtener_id_elemento(elemento_arrastrado);
        var left = elemento_arrastrado.style.left;
        var top = elemento_arrastrado.style.top;
        
        console.log('Elemento movido:', id, 'Posición:', left, top);
        
        // Guardar en memoria para la sección
        if (typeof window.guardar_posicion_elemento === 'function') {
            window.guardar_posicion_elemento(id, left, top);
        }
        
        elemento_arrastrado = null;
    }
    
    function obtener_id_elemento(el) {
        if (el.id) return 'id:' + el.id;
        if (el.className && typeof el.className === 'string') {
            var clase = el.className.split(' ')[0];
            var texto = el.textContent ? el.textContent.trim().substring(0, 15) : '';
            return 'clase:' + clase + ':' + texto;
        }
        return 'tag:' + el.tagName.toLowerCase();
    }
    
    // guardar posiciones en memoria 
    
    window.posiciones_elementos = {};
    
    window.guardar_posicion_elemento = function(id_elemento, left, top) {
        var seccion = typeof seccion_activa !== 'undefined' ? seccion_activa : 'pagina_inicio';
        
        if (!window.posiciones_elementos[seccion]) {
            window.posiciones_elementos[seccion] = {};
        }
        
        window.posiciones_elementos[seccion][id_elemento] = {
            left: left,
            top: top,
            position: 'relative'
        };
        
        console.log('Posición guardada:', seccion, id_elemento, left, top);
    };
    
    // Restaurar posiciones cuando cambia de sección
    window.restaurar_posiciones_seccion = function(seccion) {
        var posiciones = window.posiciones_elementos[seccion];
        if (!posiciones) return;
        
        var iframe = document.getElementById('iframe_preview');
        if (!iframe) return;
        
        try {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            if (!doc) return;
            
            for (var id_elemento in posiciones) {
                var pos = posiciones[id_elemento];
                var el = null;
                
                if (id_elemento.indexOf('id:') === 0) {
                    var id = id_elemento.replace('id:', '');
                    el = doc.getElementById(id);
                }
                
                if (el) {
                    el.style.position = pos.position;
                    el.style.left = pos.left;
                    el.style.top = pos.top;
                }
            }
        } catch(e) {
            console.log('Error restaurando posiciones:', e);
        }
    };
    
    // Reconfigurar cuando cambia el iframe
    var iframe = document.getElementById('iframe_preview');
    if (iframe) {
        iframe.addEventListener('load', function() {
            if (window.modo_arrastrar) {
                configurar_arrastre_iframe();
            }
            // Restaurar posiciones de la sección
            var seccion = typeof seccion_activa !== 'undefined' ? seccion_activa : 'pagina_inicio';
            window.restaurar_posiciones_seccion(seccion);
        });
    }
})();

// TITULO ARCHIVO JS
//sin funcion

/*  --------------------------------------------------------------------------------------------------------------
    ---------------------------------------- FIN ITred Spa plantilla .JS -----------------------------------------
    -------------------------------------------------------------------------------------------------------------- */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
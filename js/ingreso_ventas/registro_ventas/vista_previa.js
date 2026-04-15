// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  --------------------------------------------------------------------------------------------------------------
    -------------------------------------- INICIO ITred Spa vista_previa .JS -------------------------------------
    -------------------------------------------------------------------------------------------------------------- */

class VistaPrevia {
    // Se ejecuta automáticamente al crear la vista previa
    constructor() {
        this.init();  // Iniciamos todo
    }

    init() {
        // Prepara la vista previa para funcionar
        this.enlazar_eventos();// Conectamos los eventos del formulario
        this.actualizar_tiempo();// Mostramos la hora actual
        
        setInterval(() => this.actualizar_tiempo(), 1000);// actualizamos la hora cada segundo
    } 

    // Conecta todos los campos del formulario para detectar cambios
    enlazar_eventos() {
        const form = document.getElementById('formPersonalizar');
        if (!form) return; // Si no hay formulario, no hacemos nada

        // Observar todos los inputs del formulario
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.actualizar_vista_previa());
            input.addEventListener('input', () => this.actualizar_vista_previa());
        });

        // Detectamos cuando suben imágenes de fondo
        const fileFondo = document.querySelector('input[name="fondo"]');
        const fileFondoInterior = document.querySelector('input[name="fondo_interior"]');
        if (fileFondo) {
            fileFondo.addEventListener('change', () => this.actualizar_vista_seccion_fondos());
        }
        if (fileFondoInterior) {
            fileFondoInterior.addEventListener('change', () => this.actualizar_vista_seccion_fondos());
        }
    }
    // Muestra la hora actual en la vista previa
    actualizar_tiempo() {
        const timeElement = document.getElementById('last-update');
        if (timeElement) {
            timeElement.textContent = new Date().toLocaleTimeString(); // Formato: "14:30:45"
        }
    }
    // Actualiza toda la vista previa
    actualizar_vista_previa() {
        this.actualizar_tiempo();
        this.actualizar_vista_seccion_fondos();
        this.actualizar_textos();
        this.actualizar_botones();
    }
    // Actualiza la vista previa de la sección "Fondos"
    actualizar_vista_seccion_fondos() {
        const previewFondos = document.getElementById('preview-fondos');
        if (!previewFondos) return;

        // Obtener valores de los inputs de fondo
        const colorFondo = this.obtener_valor_de_entrada('input[name="color_fondo"]') || '#ffffff';
        const colorFondoInterior = this.obtener_valor_de_entrada('input[name="color_fondo_interior"]') || '#ffffff';
        // Actualizar la muestra de fondos
        // Nota: en la plantilla el contenedor de muestra usa la clase .preview-demo
        const sample = previewFondos.querySelector('.preview-demo');
        if (sample) {
            // aplicar color de fondo general
            sample.style.backgroundColor = colorFondo;

            // aplicar imagen de fondo general si el input file tiene un DataURL (ver más abajo)
            // por ahora dejamos color aplicado y luego intentamos leer cualquier archivo seleccionado

            const interiorDiv = sample.querySelector('div');
            if (interiorDiv) {
                interiorDiv.style.backgroundColor = colorFondoInterior;
                interiorDiv.innerHTML = `<strong>Fondo Interior:</strong> ${colorFondoInterior}`;
            }

            const fondoP = sample.querySelector('p');
            if (fondoP) {
                fondoP.innerHTML = `<strong>Fondo General:</strong> ${colorFondo}`;
            }
        }

        // Manejar inputs file para mostrar la imagen seleccionada en la vista previa
        const fileFondo = document.querySelector('input[name="fondo"]');
        const fileFondoInterior = document.querySelector('input[name="fondo_interior"]');

        // Helper para leer archivo y aplicar como background-image
        const aplicar_archivo_como_fondo = (fileInput, targetElSelector, isInterior = false) => {
            if (!fileInput) return;
            const file = fileInput.files && fileInput.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const url = e.target.result;
                    if (isInterior) {
                                // aplicar al div interior dentro de la demo
                                const interior = previewFondos.querySelector('.preview-demo > div');
                                if (interior) {
                                    // limpiar contenido textual para que la imagen no quede tapada
                                    interior.innerHTML = '';
                                    interior.style.backgroundImage = `url('${url}')`;
                                    interior.style.backgroundSize = 'cover';
                                    interior.style.backgroundPosition = 'center';
                                }
                    } else {
                        const demo = previewFondos.querySelector('.preview-demo');
                        if (demo) {
                            demo.style.backgroundImage = `url('${url}')`;
                            demo.style.backgroundSize = 'cover';
                            demo.style.backgroundPosition = 'center';
                        }
                    }
                } catch (err) {
                    // fall back silencioso
                    console.error('Error aplicando imagen a la vista previa:', err);
                }
            };
            reader.readAsDataURL(file);
        };

        // Aplicar si hay archivos seleccionados (también soporta que editor_fondo.js ya ponga img src)
        if (fileFondo && fileFondo.files && fileFondo.files.length) {
            aplicar_archivo_como_fondo(fileFondo, '.preview-demo', false);
        } else {
            // Si no hay archivo seleccionado, intentar usar cualquier imagen ya cargada en el preview (servidor)
            const imgPreview = document.getElementById('fondoPreview');
            if (imgPreview && imgPreview.tagName === 'IMG' && imgPreview.src) {
                const demo = previewFondos.querySelector('.preview-demo');
                if (demo) {
                    demo.style.backgroundImage = `url('${imgPreview.src}')`;
                    demo.style.backgroundSize = 'cover';
                    demo.style.backgroundPosition = 'center';
                }
            }
        }

        if (fileFondoInterior && fileFondoInterior.files && fileFondoInterior.files.length) {
            aplicar_archivo_como_fondo(fileFondoInterior, '.preview-demo > div', true);
        } else {
            const imgPreviewInt = document.getElementById('fondoInteriorPreview');
            let srcInt = null;
            if (imgPreviewInt) {
                if (imgPreviewInt.tagName === 'IMG' && imgPreviewInt.src) srcInt = imgPreviewInt.src;
                else {
                    // si el contenedor tiene una imagen hija, tomar su src
                    const childImg = imgPreviewInt.querySelector && imgPreviewInt.querySelector('img');
                    if (childImg && childImg.src) srcInt = childImg.src;
                }
            }
            const interior = previewFondos.querySelector('.preview-demo > div');
            if (srcInt && interior) {
                interior.innerHTML = '';
                interior.style.backgroundImage = `url('${srcInt}')`;
                interior.style.backgroundSize = 'cover';
                interior.style.backgroundPosition = 'center';
            } else if (interior) {
                // no hay imagen: remover cualquier background previo
                interior.style.backgroundImage = '';
            }
        }

        // Actualizar tabla de ejemplo (cabecera y campo) si existe en la sección de fondos
        try {
            const tableHeader = previewFondos.querySelector('.preview-table-header');
            const tableField = previewFondos.querySelector('.preview-table-field');
            const colorPrincipal = this.obtener_valor_de_entrada('input[name="color_principal"]') || this.obtener_valor_de_entrada('input#colorPrincipal') || '#00008b';
            const colorCampos = this.obtener_valor_de_entrada('input[name="color_campos"]') || this.obtener_valor_de_entrada('input#colorCampos') || '#ffffff';
            const colorTexto = this.obtener_valor_de_entrada('input[name="color_texto"]') || '#000000';
            const colorTextoCampos = this.obtener_valor_de_entrada('input[name="color_texto_campos"]') || '#919191';

            if (tableHeader) {
                tableHeader.style.background = colorPrincipal;
                tableHeader.style.color = colorTexto;
                tableHeader.textContent = `Cabecera de ejemplo — ${colorPrincipal}`;
            }
            if (tableField) {
                tableField.style.background = colorCampos;
                tableField.style.color = colorTextoCampos;
                tableField.textContent = `Campo de formulario (fondo: ${colorCampos}) — { ejemplo }`;
            }
        } catch (err) {
            // no crítico
            console.error('Error actualizando tabla en fondos:', err);
        }
    }

    actualizar_textos() {
        const previewTextos = document.getElementById('preview-textos');
        if (!previewTextos) return;

        // Obtener valores de los inputs de texto
        const colorTexto = this.obtener_valor_de_entrada('input[name="color_texto"]') || '#000000';
        const colorCampos = this.obtener_valor_de_entrada('input[name="color_campos"]') || '#ffffff';
        const colorTextoCampos = this.obtener_valor_de_entrada('input[name="color_texto_campos"]') || '#919191';
        const tipoFuente = this.obtener_valor_de_entrada('select[name="tipo_fuente"]') || 'monospace';
        const tamanoFuente = this.obtener_valor_de_entrada('input[name="tamano_fuente"]') || '14';

        // Actualizar la muestra de textos
        const textDiv = previewTextos.querySelector('.preview-text');
        if (textDiv) {
            textDiv.style.fontFamily = tipoFuente;
            textDiv.style.fontSize = tamanoFuente + 'px';
            textDiv.style.color = colorTexto;
            textDiv.style.backgroundColor = colorCampos;
            
            textDiv.innerHTML = `
                <strong>Ejemplo de Texto Principal</strong><br>
                Fuente: ${tipoFuente}<br>
                Tamaño: ${tamanoFuente}px<br>
                Color: ${colorTexto}
            `;
        }

        // Actualizar input de ejemplo
        const input = previewTextos.querySelector('input[type="text"]');
        if (input) {
            input.style.backgroundColor = colorCampos;
            input.style.color = colorTextoCampos;
            input.style.fontFamily = tipoFuente;
            input.style.fontSize = tamanoFuente + 'px';
        }

        // (La tabla de muestra de cabecera/campo está en la sección Fondos y se actualiza desde actualizar_vista_seccion_fondos())
    }

    actualizar_botones() {
        const previewBotones = document.getElementById('preview-botones');
        if (!previewBotones) return;

        // Obtener valores de los inputs de botones
        const colorBoton = this.obtener_valor_de_entrada('input[name="color_boton"]') || '#ffffff';
        const colorBotonTexto = this.obtener_valor_de_entrada('input[name="color_boton_texto"]') || '#000000';
        const colorBotonHover = this.obtener_valor_de_entrada('input[name="color_boton_hover"]') || '#0066ff';
        const bordeEstilo = this.obtener_valor_de_entrada('select[name="borde_estilo"]') || 'solid';
        const bordeGrosor = this.obtener_valor_de_entrada('input[name="borde_ancho"]') || '2';
        const bordeColor = this.obtener_valor_de_entrada('input[name="borde_color"]') || '#333333';
        const bordeRadio = this.obtener_valor_de_entrada('input[name="borde_radio"]') || '5';
        const tipoFuente = this.obtener_valor_de_entrada('select[name="tipo_fuente"]') || 'monospace';
        const tamanoFuente = this.obtener_valor_de_entrada('input[name="tamano_fuente"]') || '14';

        // Actualizar botón de ejemplo
        const button = previewBotones.querySelector('.preview-button');
        if (button) {
            button.style.backgroundColor = colorBoton;
            button.style.color = colorBotonTexto;
            button.style.border = `${bordeGrosor}px ${bordeEstilo} ${bordeColor}`;
            button.style.borderRadius = bordeRadio + 'px';
            button.style.fontFamily = tipoFuente;
            button.style.fontSize = tamanoFuente + 'px';

            // Actualizar eventos hover
            button.onmouseover = () => button.style.backgroundColor = colorBotonHover;
            button.onmouseout = () => button.style.backgroundColor = colorBoton;
        }

        // Actualizar información de propiedades
        const infoDiv = previewBotones.querySelector('div[style*="margin-top: 10px"]');
        if (infoDiv) {
            infoDiv.innerHTML = `
                <strong>Propiedades del botón:</strong><br>
                • Fondo: ${colorBoton}<br>
                • Texto: ${colorBotonTexto}<br>
                • Hover: ${colorBotonHover}<br>
                • Borde: ${bordeGrosor}px ${bordeEstilo} ${bordeColor}<br>
                • Radio: ${bordeRadio}px
            `;
        }
    }

    obtener_valor_de_entrada(selector) {
        const input = document.querySelector(selector);
        return input ? input.value : null;
    }
}

// Inicializar cuando el documento es decir la pagina esté lista
document.addEventListener('DOMContentLoaded', function() {
    new VistaPrevia();
});

// También manejar cambios de tabs para mostrar/ocultar secciones relevantes
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            // Ocultar todas las secciones de preview
            document.querySelectorAll('#vistaPrevia .preview-section').forEach(section => {
                section.style.display = 'none';
            });

            // Mostrar solo la sección relevante según el tab activo
            if (target === 'tab-fondos') {
                const el = document.getElementById('preview-fondos');
                if (el) { el.style.display = 'block'; }
            } else if (target === 'tab-textos') {
                const el = document.getElementById('preview-textos');
                if (el) { el.style.display = 'block'; }
            } else if (target === 'tab-botones') {
                const el = document.getElementById('preview-botones');
                if (el) { el.style.display = 'block'; }
            }
        });
    });
});

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  --------------------------------------------------------------------------------------------------------------
    ----------------------------------------- FIN ITred Spa vista_previa .JS -------------------------------------
    -------------------------------------------------------------------------------------------------------------- */
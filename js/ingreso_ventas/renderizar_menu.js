// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ 

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa menu .JS -----------------------------------------------
    --------------------------------------------------------------------------------------------------------------- */

// TITULO HTML

    // SIN CODIGO

// TITULO BODY

    // SIN CODIGO

// TITULO VARIABLES

    // SIN CODIGO
    
// TITULO NAVBAR

    // SIN CODIGO

// TITULO BARRA DE NAVEGACION Y SIDEBAR

// Variables globales
let isSidebarOpen = true;

    // Función principal para alternar el sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const hamburgerBtn = document.querySelector('.hamburger-btn');
        
        if (sidebar) {
            if (window.innerWidth <= 1172) {
                // En móviles: mostrar/ocultar sidebar
                sidebar.classList.toggle('show');
                isSidebarOpen = sidebar.classList.contains('show');
                
                if (hamburgerBtn) {
                    hamburgerBtn.innerHTML = isSidebarOpen ? '✕' : '☰';
                    hamburgerBtn.setAttribute('aria-label', isSidebarOpen ? 'Cerrar menú' : 'Abrir menú');
                }
            } else {
                // En desktop: colapsar sidebar y ajustar contenido
                document.body.classList.toggle('sidebar-hidden');
                isSidebarOpen = !document.body.classList.contains('sidebar-hidden');
                
                if (hamburgerBtn) {
                    hamburgerBtn.innerHTML = isSidebarOpen ? '☰' : '☰';
                }
            }
        }
    }

    // Cerrar sidebar cuando se hace clic fuera de él (solo móviles)
    function setupSidebarHandlers() {
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1172) {
                const sidebar = document.getElementById('sidebar');
                const hamburgerBtn = document.querySelector('.hamburger-btn');
                
                // Si el sidebar está abierto y el clic fue fuera de él
                if (sidebar && sidebar.classList.contains('show') && 
                    !sidebar.contains(e.target) && 
                    !hamburgerBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                    hamburgerBtn.innerHTML = '☰';
                    hamburgerBtn.setAttribute('aria-label', 'Abrir menú');
                    isSidebarOpen = false;
                }
            }
        });
        
        // Manejar resize de ventana
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 1172 && sidebar) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-hidden');
                isSidebarOpen = true;
            }
        });
    }

    // Inicializar handlers cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', setupSidebarHandlers);

    
    // Envia un POST al endpoint para registrar clicks en el menu.
    function sendClickLabel(label) {
        try {
            var body = 'label=' + encodeURIComponent(label);
            if (window.ITRED_CSRF) body += '&csrf=' + encodeURIComponent(window.ITRED_CSRF);
            fetch('/php/inicio_sesion/seguridad/log_registros.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
                body: body
            }).catch(function(e){ /* no bloquear UI */ });
        } catch (e) {
            // swallow
        }
    }

    // Atacha listeners una vez cargado el DOM
    document.addEventListener('DOMContentLoaded', function(){
        // Enlaces del menú lateral (clase .nav-link)
        document.querySelectorAll('.nav-link').forEach(function(link){
            link.addEventListener('click', function(ev){
                var label = (link.textContent || link.innerText || 'menu').trim();
                sendClickLabel(label);
                
                // En móviles, cerrar el sidebar al hacer clic en un enlace
                if (window.innerWidth <= 1172) {
                    const sidebar = document.getElementById('sidebar');
                    const hamburgerBtn = document.querySelector('.hamburger-btn');
                    if (sidebar && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        if (hamburgerBtn) {
                            hamburgerBtn.innerHTML = '☰';
                            hamburgerBtn.setAttribute('aria-label', 'Abrir menú');
                        }
                        isSidebarOpen = false;
                    }
                }
            });
        });

        // Botones de menú antiguos (clase .btnMenu) - para compatibilidad
        document.querySelectorAll('.btnMenu').forEach(function(b){
            b.addEventListener('click', function(ev){
                var label = (b.textContent || b.innerText || 'menu').trim();
                sendClickLabel(label);
            });
        });

        // Botón cerrar sesión
        document.querySelectorAll('.logout-btn, .nav-button').forEach(function(b){
            b.addEventListener('click', function(ev){
                sendClickLabel('Cerrar sesión');
            });
        });

        // Elementos con atributo data-log-label (flexible)
        document.querySelectorAll('[data-log-label]').forEach(function(el){
            el.addEventListener('click', function(){
                sendClickLabel(el.getAttribute('data-log-label'));
            });
        });

        // Mejorar la funcionalidad de los nav-items completos
        document.querySelectorAll('.nav-item').forEach(function(item){
            item.addEventListener('click', function(ev){
                // Si el clic no fue en el enlace, simular clic en el enlace
                if (ev.target === item || ev.target.classList.contains('nav-icon')) {
                    const link = item.querySelector('.nav-link');
                    if (link && ev.target !== link) {
                        ev.preventDefault();
                        link.click();
                    }
                }
            });
        });

    });

    // Vista Facturas con Problemas

        function alternar_vista_facturas() {
            const contenedorFacturas = document.getElementById('contenedor_facturas_malas');
            const mainContent = document.querySelector('.main-content');
            const breadcrumb = document.querySelector('.breadcrumb');
            const alerta = document.querySelector('.seccion-alerta-facturas');

            if (!contenedorFacturas || !mainContent) return;

            const estaVisible = contenedorFacturas.style.display === 'block';

            if (estaVisible) {
                //  Volver al dashboard
                contenedorFacturas.style.display = 'none';
                mainContent.style.display = '';
                if (breadcrumb) breadcrumb.style.display = '';
                if (alerta) alerta.style.display = '';
            } else {
                //  Mostrar vista de facturas malas
                contenedorFacturas.style.display = 'block';
                mainContent.style.display = 'none';
                if (breadcrumb) breadcrumb.style.display = 'none';
                if (alerta) alerta.style.display = 'none';
            }
        }

    // funcion boton modificar
    document.addEventListener("click", async function (e) {

    const btn = e.target.closest(".btn-modificar");
    if (!btn) return;

    const fila = btn.closest("tr");
    const form = document.getElementById("form-facturas-malas");
    const inputs = fila.querySelectorAll(".campo-editable");

    const editando = btn.classList.contains("editando");

    // 🔓 ACTIVAR EDICIÓN
    if (!editando) {

        // bloquear todo
        document.querySelectorAll(".campo-editable").forEach(i => {
            i.setAttribute("readonly", true);
            i.classList.remove("input-activo");
        });

        document.querySelectorAll(".btn-modificar").forEach(b => {
            b.textContent = "Modificar";
            b.classList.remove("editando");
        });

        // activar solo esta fila
        inputs.forEach(i => {
            i.removeAttribute("readonly");
            i.classList.add("input-activo");
        });

        btn.textContent = "Guardar";
        btn.classList.add("editando");
        return;
    }

    // 💾 GUARDAR
    if (!confirm("¿Está seguro de guardar los cambios?")) return;

    const formData = new FormData(form);

    try {
        const id = fila.dataset.id;
        formData.append("id_editado", id);
        const res = await fetch('/php/ingreso_ventas/registro_ventas/guardar_factura.php', {
            method: "POST",
            body: formData
        });

        const text = await res.text();
        console.log("RESPUESTA SERVIDOR:", text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            alert("Respuesta inválida del servidor");
            return;
        }


       if (data.msg) {
            alert(data.msg);
        }


        // volver a estado normal
        inputs.forEach(i => {
            i.setAttribute("readonly", true);
            i.classList.remove("input-activo");
        });

        btn.textContent = "Modificar";
        btn.classList.remove("editando");


    } catch (err) {
        console.error(err);
        alert("Error de conexión");
    }
});








    
/*  ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa menu .JS ----------------------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

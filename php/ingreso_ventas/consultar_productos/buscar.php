<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<!-- ------------------------------------------------------------------------------------------------------------
     ------------------------------------- INICIO ITred Spa buscar .PHP -----------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

     <?php
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<!-- TITULO HTML -->

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>buscar</title>
        <!-- carga el archivo de estilos CSS para la página de buscar -->
        <link rel="stylesheet" href="/css/ingreso_ventas/consultar_productos/buscar.css?v=<?= time() ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
        <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
        <link rel="manifest" href="/imagenes/favicon/site.webmanifest"> 
        
    </head>

<!-- TITULO BODY -->
 
    <body class="buscar">

    <!-- CONTENEDOR PRINCIPAL PARA ESTILOS DE PLANTILLA -->
    <div class="contenedor-principal">

    <!-- TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN -->

        <?php
            $mysqli->set_charset("utf8");
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Construir la URL completa
            $Url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            // Verificar si la URL contiene 'superadmin.php'
            $esSuperadmin = strpos($Url, 'superadmin.php') !== false;
            // Verifica si el usuario ha iniciado sesión
            if (!$esSuperadmin && !isset($_SESSION['correo'])) {
                // Si el usuario no ha iniciado sesión, redirige a la página de inicio
                $archivo = '/ingreso_ventas.php';
                header("Location: ".$archivo);
                exit();
            }

        ?>


    <h1>TABLA DE VENTAS</h1>
    <!--Selector de acciones  -->
    <div class="acciones-movil">
    <select id="accionSelector">
        <option value="">-- Selecciona una acción --</option>
        <option value="excel">Descargar Excel</option>
        <option value="pdf"> Descargar PDF</option>
        <option value="imprimir"> Imprimir PDF</option>
    </select>
    </div>

<!-- TITULO RECUADROS Y CUADRO DE BUSQUEDA -->

    <!-- contenedor principal que agrupa los recuadros de búsqueda y los formularios de pdf -->
    <div id="fila_superior">
        <div class="recuadro_acciones">

    <!-- TITULO GENERAR EXCEL -->

            <!-- contenedor que agrupa los botones de acción para generar e imprimir PDF -->
            <div class="botones_acciones">
                <!-- Excel -->
                <form action="/php/ingreso_ventas/consultar_productos/generar_excel.php"
                        method="post" id="formExcel" class="accion-form">
                    <input type="hidden" name="sku" id="excel_sku">
                    <input type="hidden" name="lote" id="excel_lote">
                    <input type="hidden" name="rut" id="excel_rut">
                    <input type="hidden" name="nombre" id="excel_nombre">
                    <input type="hidden" name="numero_fact" id="excel_factura">
                    <input type="hidden" name="fecha_desde" id="excel_fecha_desde">
                    <input type="hidden" name="fecha_hasta" id="excel_fecha_hasta">
                    <input type="hidden" name="buscar_serial" id="excel_serial">

                    <button type="submit" id="btnExcel" class="boton_accion">
                    <img src="/imagenes/ingreso_ventas/consultar_productos/buscar_img7.png"
                        alt="Descargar Excel"
                        id="imagen_excel" class="icono-accion">
                    <span class="texto-acciones">Descargar Excel</span>
                    </button>
                </form>
                <hr class="linea-acciones">          
                <!-- PDF -->
                <form action="/php/ingreso_ventas/consultar_productos/generar_pdf.php"
                        method="post" id="formPDF" class="accion-form">
                    <input type="hidden" name="sku" id="input_sku">
                    <input type="hidden" name="buscar_serial" id="input_serial">
                    <input type="hidden" name="lote" id="input_lote">
                    <input type="hidden" name="rut" id="input_rut">
                    <input type="hidden" name="nombre" id="input_cliente">
                    <input type="hidden" name="numero_fact" id="input_numero_fact">
                    <input type="hidden" id="input_fecha_desde" name="fecha_desde">
                    <input type="hidden" id="input_fecha_hasta" name="fecha_hasta">

                    <button type="submit" class="boton_accion">
                    <img src="/imagenes/ingreso_ventas/consultar_productos/buscar_img6.png"
                        alt="Descargar PDF"
                        id="imagen_ver" class="icono-accion">
                    <span class="texto-acciones">Descargar PDF</span>
                    </button>
                </form>
                    <hr class="linea-acciones">
                <!-- Imprimir -->
                <form method="post" class="accion-form" id="form-imprimir">
                <iframe id="iframePDF" style="visibility:hidden; width:1px; height:1px; border:0; position:absolute; left:-9999px;"></iframe>
                    <button type="submit" class="boton_accion">
                    <img src="/imagenes/ingreso_ventas/consultar_productos/buscar_img8.png"
                        alt="Imprimir PDF"
                        id="imagen_imprimir" class="icono-accion">
                    <span class="texto-acciones">Imprimir PDF</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <div id="fila_inferior">

        <?php
        $rol = $_SESSION['rol'] ?? '';
        if ($rol === 'usuario_final') {
        ?>
            <!-- recuadro para buscar por número de SKU -->
            <div class="recuadro">
                <!-- título del recuadro -->
                <div class="titulo_cuadro">Codigo SKU</div>
                <div class="input-borrar">
                    <!-- campo de entrada para buscar por número de SKU -->
                    <input class="cuadro_busqueda" type="text" name="buscar_sku" id="buscar_sku" placeholder="Buscar: N° SKU">
                </div>
            </div>

            <!-- recuadro para buscar por número de serie -->
            <div class="recuadro">
                <!-- título del recuadro -->
                <div class="titulo_cuadro">Número de Serie</div>
                <div class="input-borrar">
                    <!-- nuevo campo de entrada para número de serie -->
                    <input class="cuadro_busqueda" type="text" name="buscar_serial" id="buscar_serial" placeholder="Buscar: N° Serie">
                    
                </div>
            </div>
            <!-- Botón Buscar en la misma fila de filtros -->
            <div class="boton-buscar" id="btnBuscar" role="button" tabindex="0">
                <div class="input-borrar" style="justify-content:center;">
                    <img src="/imagenes/ingreso_ventas/consultar_productos/buscar_img5.png"
                        alt="Buscar" style="width:50px;height:50px;">
                    <span class="texto-acciones">Buscar</span>
                </div>
            </div>
            <!-- NUEVA BÚSQUEDA: botón encima del recuadro Buscar (usuario_final) -->
            <div style="display:flex; justify-content:flex-start; margin:8px 0 6px 0;">
                <button id="btnLimpiarFiltros" type="button" class="nueva-busqueda">
                    <img src="/imagenes/ingreso_ventas/consultar_productos/buscar_img1.png" alt="Nueva búsqueda">
                    <span>Nueva búsqueda</span>
                </button>
            </div>
            
        <?php
        } else {
            // ...código original para otros roles...
        ?>

            
            
            <!-- recuadro para buscar por número de SKU -->
             <div class="input-group">
                <div class="recuadro">
                    <div class="titulo_cuadro">Codigo SKU</div>
                    <div class="input-borrar">
                        <input class="cuadro_busqueda" type="text" name="buscar_sku" id="buscar_sku" placeholder="Buscar: N° SKU">
                        
                    </div>
                </div>
            
                <!-- recuadro para buscar por número de lote -->
                <div class="recuadro">
                    <div class="titulo_cuadro">Número de Lote</div>
                    <div class="input-borrar">
                        <input class="cuadro_busqueda" type="text" name="buscar_lote" id="buscar_lote" placeholder="Buscar: N° Lote">
                        
                    </div>
                </div>
            </div> 
            
            <div class="input-group">
                <div class="recuadro">
                    <div class="titulo_cuadro">Rut de cliente</div>
                    <div class="input-borrar">
                        <input class="cuadro_busqueda" type="text" name="buscar_rut" id="buscar_rut" placeholder="Buscar: RUT Cliente"
                            <?php if ($rol === 'distribuidor'): ?>
                            data-rut-distribuidor="<?= htmlspecialchars($_SESSION['rut'] ?? '') ?>"
                            title="Ingrese su RUT: <?= htmlspecialchars($_SESSION['rut'] ?? '') ?>"
                            <?php endif; ?>>
                    </div>
                </div>
            

                <?php if ((isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'distribuidor')) || (isset($esSuperadmin) && $esSuperadmin)): ?>
                <div class="recuadro">
                    <div class="titulo_cuadro">Nombre de cliente</div>
                    <div class="input-borrar">
                        <input class="cuadro_busqueda" type="text" name="buscar_cliente" id="buscar_cliente" placeholder="Buscar: Nombre Cliente">
                        
                    </div>
                </div>
                <?php endif; ?>
            </div> 


            <div class="input-group">
                <div class="recuadro">
                    <div class="titulo_cuadro">Número de Factura</div>
                    <div class="input-borrar">
                        <input class="cuadro_busqueda" type="text" name="buscar_factura" id="buscar_factura" placeholder="Buscar: N° Factura">
                        
                    </div>
                </div>
                <div class="recuadro_grande">
                    <div style="min-height: 93px;" class="titulo_cuadro" id="titulo_fecha_toggle" role="button" tabindex="0" aria-controls="fecha_contenido">
                        <span>Buscar por Fecha de Despacho</span>
                        <button type="button" class="toggle-fecha" aria-expanded="false" aria-label="Mostrar u ocultar fechas">▾</button>
                    </div>
                    <div class="input-fecha" id="fecha_contenido">
                        <!-- Popper arrow -->
                        <div class="popper-arrow" data-popper-arrow></div>
                        <label for="fecha_desde">Desde:</label>
                        <div style="display: flex; align-items: center; gap: 4px; width: 100%;">
                            <input type="date" id="fecha_desde" name="fecha_desde" class="cuadro_busqueda date-select">
                        </div>
                        <label for="fecha_hasta">Hasta:</label>
                        <div style="display: flex; align-items: center; gap: 4px; width: 100%;">
                            <input type="date" id="fecha_hasta" name="fecha_hasta" class="cuadro_busqueda date-select">
                        </div>
                    </div>
                </div>
            </div>
            <!-- NUEVA BÚSQUEDA: botón encima del recuadro Buscar (otros roles) -->
            <!-- Botón Buscar en la misma fila de filtros -->
            <div style="display:flex; justify-content:flex-start; margin:8px 0 6px 0;">
                <button type="button" class="boton-buscar" id="btnBuscar">
                    <div class="input-borrar">
                        <img src="/imagenes/ingreso_ventas/consultar_productos/buscar_img5.png"
                            alt="Buscar" style="width:50px;height:50px;">
                        <span class="texto-acciones">Buscar</span>
                    </div>
                </button>
            </div>
            <div style="display:flex; justify-content:flex-start; margin:8px 0 6px 0;">
                <button id="btnLimpiarFiltros" type="button" class="nueva-busqueda">
                    <img src="/imagenes/ingreso_ventas/consultar_productos/buscar_img1.png" alt="Nueva búsqueda">
                    <span>Nueva búsqueda</span>
                </button>
            </div>
        <?php } ?>
    </div>


<!-- TITULO TABLA DE VENTAS -->
    <div class="tabla-responsiva" id="contenedorTabla" style="display:none;">
        <!-- tabla con encabezados para mostrar el historial de ventas -->
        <table>
            <!-- encabezado de la tabla que define las columnas de la tabla de ventas -->
            <thead>
                <tr>
                <?php if ($rol === 'usuario_final'): ?>
                    <th>SKU</th>
                    <th>NÚMERO DE SERIE</th>
                    <th>FECHA DE DESPACHO</th>
                    <th>PRODUCTO</th>
                    <th>CANTIDAD</th>
                    <th>LOTE</th>
                    <th>FECHA DE FABRICACION</th>
                    <th>FECHA DE VENCIMIENTO</th>
                <?php else: ?>
                    <th>SKU</th>
                    <th>RUT</th>
                    <th>NOMBRE CLIENTE</th>
                    <th>NUMERO DE FACTURA</th>
                    <th>FECHA DE DESPACHO</th>
                    <th>PRODUCTO</th>
                    <th>CANTIDAD</th>
                    <th>LOTE</th>
                    <th>FECHA DE FABRICACION</th>
                    <th>FECHA DE VENCIMIENTO</th>
                    <th>SERIE DE INICIO</th>
                    <th>SERIE DE TERMINO</th>
                    <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'superadmin')): ?>
                        <th>MODIFICAR VENTA</th>
                    <?php endif; ?>
                <?php endif; ?>
                </tr>
            </thead>
            
    <!-- TITULO MODAL MODIFICAR -->

            <!-- Modal para editar venta -->
            <div id="modalEditarVenta" class="modal">
                <!-- Clase que hace que el modal haga de contenedor para los campos del formulario -->
                <div class="modal-contenido">
                <!-- Cruz en la esquina superior para cerrar el modal -->
                    <span class="cerrar" onclick="cerrar_modal()">&times;</span>
                    <!-- Título del modal que indica la acción de editar venta -->
                    <h2>EDITAR VENTA</h2>
                    <!-- Formulario para editar los datos de la venta, con campos prellenados y validaciones -->
                    <form id="formEditarVenta">
                        <!-- Campo oculto para almacenar el ID de la venta que se va a editar -->
                        <input type="hidden" name="id" id="id">
                        
                        <!-- Campos de entrada para editar los datos de la venta -->

                        <!-- Campo de entrada para el SKU, que se llena automáticamente y es de solo lectura -->
                        <label for="sku">SKU:</label>
                        <input type="text" name="sku" id="sku" required readonly>

                        <!-- Campo de entrada para el número de lote, que se llena automáticamente y es de solo lectura -->
                        <label for="lote">Lote:</label>
                        <input type="text" name="lote" id="lote" required readonly>
                        
                        <!-- Campo de entrada para el RUT del cliente, que se llena automáticamente y es de solo lectura -->
                        <label for="rut">RUT:</label>
                        <input type="text" name="rut" id="rut" readonly>
                        
                        <!-- Campo de entrada para el nombre del cliente, con validación para aceptar solo letras y espacios -->
                        <label for="nombre">Nombre de Cliente:</label>
                        <input type="text" name="nombre" id="nombre" required pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+">
                        
                        <!-- Campo de entrada para el número de factura, con validación para aceptar solo números -->
                        <label for="numero_fact">Número de Factura:</label>
                        <input type="text" name="numero_fact" id="numeroDoc" required pattern="\d{1,9}" maxlength="20">
                        
                        <!-- Campo de entrada para la fecha de despacho, con formato de fecha y hora -->
                        <label for="fecha_despacho">Fecha de Despacho:</label>
                        <input type="datetime-local" name="fecha_despacho" id="fechaDespacho" required>
                        
                        <!-- Campo de entrada para el nombre del producto, con validación para aceptar solo letras y espacios -->
                        <label for="producto">Producto:</label>
                        <input type="text" name="producto" id="producto" required maxlength="50">

                        <label for="cantidad">Cantidad:</label>
                        <input type="number" name="cantidad" id="cantidad" required min="1" max="1000000">
                        
                        <!-- Campo de entrada para la fecha de fabricación, con formato de fecha -->
                        <label for="fecha_fabricacion">Fecha de Fabricación:</label>
                        <input type="date" name="fecha_fabricacion" id="fechaFabricacion" required>
                        
                        <!-- Campo de entrada para la fecha de vencimiento, con formato de fecha -->
                        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
                        <input type="date" name="fecha_vencimiento" id="fechaVencimiento" required>

                        <!-- Campo de entrada para la serie de inicio, con validación para aceptar solo números -->
                        <label for="n_serie_ini">Serie de Inicio:</label>
                        <input type="text" name="n_serie_ini" id="serieInicio" required pattern="\d+" maxlength="8">
                        
                        <!-- Campo de entrada para la serie de término, con validación para aceptar solo números -->
                        <label for="n_serie_fin">Serie de Término:</label>
                        <input type="text" name="n_serie_fin" id="serieFin" required pattern="\d+" maxlength="8">
                        
                        <!-- Botón para guardar los cambios realizados en la venta -->
                        <button type="button" onclick="guardar_cambios()">Guardar Cambios</button>
                    </form>
                </div>
            </div>


    <!-- TITULO MODAL ELIMINACION -->

        <!-- Modal para confirmar eliminación de venta con el id utilizado con el javascript-->
        <div id="modalEliminarVenta" class="modaleliminacion">
            <!-- Clase que hace que el modal haga de contenedor para los botones-->
            <div class="modal-contenido-eliminacion">
                <!-- Cruz en la esquina superior para cerrar el modal -->
                <span class="cerrareliminacion" onclick="cerrar_modalEliminacion()">&times;</span>
                <!-- Texto indicativo de la operacion -->
                <div class="modal-texto-eliminacion">
                <h2>¿Estás seguro que deseas eliminar esta venta?</h2>
                </div>
                <!-- Campo oculto en el que se almacena el id que recibe el formulario -->
                <input type="hidden" id="idVentaEliminar">
                <!-- Contenedor de los botones con las funciones -->
                <div class="botones-confirmacion eliminacion">
                    <!-- Boton que ejecuta la eliminacion de la venta  -->
                    <button class="modal-boton-si" onclick="eliminar_venta_confirmada()">Sí</button>
                    <!-- Boton que ejecuta el cierre del modal  -->
                    <button class="modal-boton-no" onclick="cerrar_modal_eliminacion()">No</button>
                </div>
            </div>
        </div>

        <!-- TITULO CUERPO HISTORICO DE LA TABLA -->

            <!-- sección del cuerpo de la tabla donde se mostrarán los registros de ventas -->
            <tbody id="tablaVentas">
                <!-- en esta parte se muestran los datos de las ventas desde la base de datos -->
            </tbody>
        </table>
    </div>

<!-- TITULO ARCHIVO JS -->
    
    <script>
        // Rol tomado SOLO de la sesión (no por URL)
        var usuarioRol = "<?= strtolower(trim($_SESSION['rol'] ?? '')) ?>";
        // Conveniencia si en algún lado usas usuarioRolFinal
        var usuarioRolFinal = (usuarioRol === 'usuario_final') ? 'usuario_final' : '';
        // RUT del distribuidor para usar en JS
        <?php if ($rol === 'distribuidor'): ?>
        var rutDistribuidor = "<?= htmlspecialchars($_SESSION['rut'] ?? '') ?>";
        <?php endif; ?>
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const btnLimpiar = document.getElementById('btnLimpiarFiltros');
        if (!btnLimpiar) return;
        btnLimpiar.addEventListener('click', function(){
            // lista de inputs de filtro conocidos
            const ids = ['buscar_sku','buscar_lote','buscar_rut','buscar_cliente','buscar_factura','buscar_serial','fecha_desde','fecha_hasta'];
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    // Para distribuidor, también limpiar el RUT - debe volver a digitarlo
                    if (el.type === 'date' || el.type === 'text') el.value = '';
                    else el.value = '';
                }
            });
            // ocultar tabla de resultados
            const cont = document.getElementById('contenedorTabla');
            if (cont) cont.style.display = 'none';

            // No disparar la búsqueda vacía (evita el mensaje de 'Ingresa al menos un filtro')
            // En su lugar enfocamos el primer campo de filtro y mostramos la tabla oculta
            const primer = document.getElementById('buscar_sku') || document.getElementById('buscar_rut') || document.getElementById('buscar_lote');
            if (primer) primer.focus();
        });
    });
    </script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script>
    // Toggle para el bloque de fechas (desplegable) usando Popper.js
    document.addEventListener('DOMContentLoaded', function(){
        var toggleBtn = document.querySelector('.toggle-fecha');
        var fechaContenido = document.getElementById('fecha_contenido');
        var titulo = document.getElementById('titulo_fecha_toggle');
        if (!toggleBtn || !fechaContenido || !titulo) return;

        // inicializamos Popper al abrir por primera vez
        var popperInstance = null;
        function createPopper(){
            if (window.Popper && !popperInstance){
                var arrowEl = fechaContenido.querySelector('[data-popper-arrow]');
                popperInstance = Popper.createPopper(titulo, fechaContenido, {
                    placement: 'bottom-start',
                    modifiers: [
                        { name: 'offset', options: { offset: [0, 8] } },
                        { name: 'preventOverflow', options: { boundary: 'viewport' } },
                        { name: 'flip', options: { fallbackPlacements: ['top-start','bottom-start'] } },
                        { name: 'arrow', options: { element: arrowEl, padding: 6 } }
                    ]
                });
            }
        }

        function setExpanded(state){
            toggleBtn.setAttribute('aria-expanded', state ? 'true' : 'false');
            toggleBtn.textContent = state ? '▴' : '▾';
            if (state) {
                fechaContenido.classList.add('open');
                createPopper();
                if (popperInstance) popperInstance.update();
            } else {
                fechaContenido.classList.remove('open');
            }
        }

        // alternar al hacer click en el botón o en el header completo
        toggleBtn.addEventListener('click', function(e){
            var expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
            setExpanded(!expanded);
        });
        if (titulo){
            titulo.addEventListener('click', function(e){
                if (e.target === toggleBtn) return;
                var expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                setExpanded(!expanded);
            });
            titulo.addEventListener('keydown', function(e){
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    var expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                    setExpanded(!expanded);
                }
            });
        }

        // actualizar posición si la ventana cambia de tamaño
        window.addEventListener('resize', function(){
            if (popperInstance) popperInstance.update();
        });

        // cerrar con Escape
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') {
                setExpanded(false);
            }
        });
    });
    </script>




    <!-- carga el archivo de funciones JavaScript necesarias para el filtrado y manejo de tabla -->
    <script src="/js/ingreso_ventas/consultar_productos/buscar.js?v=<?= time() ?>"></script>
    
    </div> <!-- FIN CONTENEDOR PRINCIPAL -->
    
    </body>
    </html>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <!-- <?php 
        // Cierra la conexión a la base de datos
        // $mysqli->close();
    ?> -->

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa buscar .PHP -------------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

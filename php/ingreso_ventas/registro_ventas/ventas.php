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
     ------------------------------------- INICIO ITred Spa ventas .PHP -----------------------------------------
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
<html lang="us">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ingreso_ventas</title>
    <!-- llama al archivo css que contiene los estilos de la página -->
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/ventas.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
</head>
<!-- TITULO BODY -->

<body class="ingreso-ventas">

    <!-- Contenedor principal para estilos de plantilla -->
    <div class="contenedor-principal">

    <!-- TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN -->
    <?php
        $mysqli->set_charset("utf8");
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $Url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $esSuperadmin = strpos($Url, 'superadmin.php') !== false;
        
        if (!$esSuperadmin && !isset($_SESSION['correo'])) {
            $archivo = '/ingreso_ventas.php';
            header("Location: ".$archivo);
            exit();
        }
    ?>

    <!-- NUEVO: Contenedor que SÍ se moverá con el panel -->
    <div class="contenedor-movil">
        
        <!-- TITULO CABECERA -->
        <h1>INGRESO BODEGA</h1>
        <div id="cabecera"></div>
        
        <!-- TITULO FORMULARIO INFORMACION DESPACHO -->
        <div class="ingreso_informacion">
            <form id="formularioInfo" class="formulario">
                <div class="campo_formulario">
                    <label for="rut">Rut:</label>
                    <input type="text" name="rut" id="rut" placeholder="Ingrese RUT">
                    <input type='text' id='inputQR' style='position:absolute; left:-9999px;' autofocus>
                </div>

                <div class="campo_formulario">
                    <label for="fname">Nombre Cliente:</label>
                    <input type="text" name="nombre" id="nombre" readonly placeholder="Nombre aparecerá automaticamente">
                </div>

                <div class="campo_formulario">
                    <label for="fname">Número de Factura:</label>
                    <input type="text" name="numero_factu" id="numero_factu" placeholder="Ingrese N° de Folio">
                </div>
                
                <div class="campo_formulario">
                    <label for="fecha_actual">Fecha de Despacho:</label>
                    <input type="datetime-local" name="fecha_actual" id="fecha_actual" readonly>
                </div>
            </form>
        </div>

        <!-- TITULO ESCANEOS -->
        <div id="boton_escaner">
            <div class="grupo-escaner">
                <button id="btnEscanear" class="centrar" style="display: none;" onclick="iniciarScanner()">ESCANEAR CON CÁMARA</button>
                <button id="btnPistola" class="centrar" style="display: none;" type="button" onclick="iniciarPistola()">ESCANEAR CON PISTOLA</button>
                <button id="btnManual" class="centrar" style="display: none;" type="button" onclick="iniciarManual()">INGRESO MANUAL</button>
            </div>
            <div class="grupo-detener">
                <button id="btnDetener" class="centrar" style="display: none;" onclick="detenerScanner()">DETENER</button>
            </div>
        </div>

        <!-- TITULO LECTOR QR -->
        <div id="lectorQR" style="width: 400px; height: 300px; display: none;"></div> 
        <div id="mensaje_feedback" style="display: none;"></div>
        
    </div> <!-- FIN contenedor-movil -->

    <!-- LA TABLA QUEDA FUERA para que NO se mueva -->
    <!-- TITULO INFORMACION TABLA DE PRODUCTOS -->
    <div class="contenedor_tabla">
        <table id="tablaProductosHeader" style="display: none;">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>PRODUCTO</th>
                    <th>CANTIDAD</th>
                    <th>LOTE</th>
                    <th>FECHA DE FABRICACION</th>
                    <th>FECHA DE VENCIMIENTO</th>
                    <th>SERIE DE INICIO</th>
                    <th>SERIE DE TERMINO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody id="tablaProductos"></tbody>
        </table>
    </div>

    <!-- TITULO BOTONES GUARDAR -->
    <div id="boton" style="display: none;">
        <button id="btnGuardar" class="centrar" onclick="guardarDatos()">GUARDAR DATOS</button>
    </div>
    
    <!-- TITULO PANEL ESTADO FACTURA -->
    <div id="panel-resumen" class="panel-resumen-oculto">
        <div class="panel-resumen-titulo">Estado de Factura</div>
        <div id="contenido-resumen"></div>
    </div>

    <!-- TITULO ARCHIVO JS -->
    <script src="/js/ingreso_ventas/registro_ventas/ventas.js?v=<?= time() ?>"></script>
    <script src="/js/ingreso_ventas/registro_ventas/html5-qrcode.min.js?v=<?= time() ?>" type="text/javascript"></script>

    </div> <!-- termino del contenedor principal -->
</body>

    </div> <!-- termino del contenedor principal -->

      
</html>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <?php 
        // cierra la conexión con la base de datos
        // $mysqli->close();
        ?>

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa ventas .PHP -------------------------------------------
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

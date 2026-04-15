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
     ------------------------------------- INICIO ITred Spa generar_qr .PHP -------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

     <?php
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sku'])) {
    
    // recibimos y limpiamos los datos que vienen del formulario, real_escape_string previene ataques de seguridad (sql injection)
    $sku = $mysqli->real_escape_string($_POST['sku']);
    // guardamos el nombre del producto limpio
    $producto = $mysqli->real_escape_string($_POST['producto']);
    // convertimos la cantidad a número entero
    $cantidad = intval($_POST['cantidad']);
    // se guarda el lote de manera limpia
    $lote = $mysqli->real_escape_string($_POST['lote']);
    // guardamos la fecha de fabricación limpia
    $fecha = $mysqli->real_escape_string($_POST['fecha_fabricacion']);
    // se guarda número de serie inicial limpio
    $serie_ini = $mysqli->real_escape_string($_POST['serie_inicio']);
    // guardamos el número de serie final limpio
    $serie_fin = $mysqli->real_escape_string($_POST['serie_final']);


    // 1. Preparamos las fechas antes de escribir la orden SQL
        $fecha_raw = $_POST['fecha_fabricacion'];
        
        if (!empty($fecha_raw)) {
            // Si el usuario puso fecha:
            $clean_fab = $mysqli->real_escape_string($fecha_raw);
            
            // Calculamos vencimiento (+5 años)
            $clean_venc = date('Y-m-d', strtotime($clean_fab . " + 5 years"));
            
            // IMPORTANTE: Le ponemos comillas simples aquí para la base de datos
            $sql_fab  = "'$clean_fab'";
            $sql_venc = "'$clean_venc'";
        } else {
            // Si está vacío: Escribimos NULL (sin comillas)
            $sql_fab  = "NULL";
            $sql_venc = "NULL";
        }

        // 2. Borramos historial anterior de este SKU
        $mysqli->query("DELETE FROM historial_qr WHERE sku = '$sku'");
        
        // 3. Insertamos (Fíjate que usamos las variables $sql_... que ya traen las comillas o el NULL)
        $sql_hist = "INSERT INTO historial_qr (sku, producto, cantidad, lote, fecha_fabricacion, fecha_vencimiento, fecha_registro) 
                     VALUES ('$sku', '$producto', '$cantidad', '$lote', $sql_fab, $sql_venc, NOW())";
        
        if(!$mysqli->query($sql_hist)){
             // Si falla, mostramos el error técnico
             echo "<script>console.log('Error SQL Historial: " . $mysqli->error . "');</script>";
        } else {
             // Confirmación en consola
             echo "<script>console.log('Historial guardado. Fab: $sql_fab, Venc: $sql_venc');</script>";
        }


     // verificamos si ya existe un producto con este sku en la base de datos hacemos una consulta para buscar el sku
    $check = $mysqli->query("SELECT id FROM venta WHERE sku = '$sku'");
    // si encontramos al menos un registro (num_rows mayor a 0)
    if ($check->num_rows > 0) {
        // el producto ya existe, entonces actualizamos sus datos y creamos la consulta de actualización (update)
        $sql = "UPDATE venta SET 
                producto='$producto', cantidad='$cantidad', lote='$lote', 
                fecha_fabricacion='$fecha', n_serie_ini='$serie_ini', n_serie_fin='$serie_fin', fecha_despacho=NOW() 
                WHERE sku='$sku'";
        // mensaje que mostraremos si todo sale bien
        $msg = "Producto Actualizado Correctamente";
    } else {
        // el producto no existe, entonces insertamos uno nuevo y creamos la consulta de inserción (insert)
        $sql = "INSERT INTO venta (sku, producto, cantidad, lote, fecha_fabricacion, n_serie_ini, n_serie_fin, fecha_despacho) 
                VALUES ('$sku', '$producto', '$cantidad', '$lote', '$fecha', '$serie_ini', '$serie_fin', NOW())";
        // mensaje que mostraremos si todo sale bien
        $msg = "Producto Guardado Correctamente";
    }
    // ejecutamos la consulta (ya sea update o insert)
    if ($mysqli->query($sql)) {
        // si todo salió bien, mostramos una alerta con el mensaje
        echo "<script>alert('$msg');</script>";
    } else {
        // si hubo un error, mostramos una alerta con el error
        echo "<script>alert('Error BD: " . $mysqli->error . "');</script>";
    }
}
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<!-- TITULO HTML -->

    <!DOCTYPE html>
    <html lang="es">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <head>
        <meta charset="UTF-8">
        <title>generar_qr</title>
        <!-- llama al archivo css que contiene los estilos para la página del menú -->
        <link rel="stylesheet" href="../../css/ingreso_ventas/consultar_productos/generar_qr.css?v=<?= time() ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
        <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
        <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
    </head>
    <body class="generar-qr">

    <!--contenedor principal para estilos de plantilla -->
    <div class="contenedor-principal">

<!-- TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN -->

    <?php
        // configuramos la base de datos para que use caracteres utf8 (tildes, ñ, etc.)
        $mysqli->set_charset("utf8");
        // verificamos si no hay una sesión activa
        if (session_status() === PHP_SESSION_NONE) {
            // iniciamos la sesión para poder usar variables de sesión
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

<!-- TITULO FORMULARIO GENERADOR QR -->
    
    <!-- contenedor principal de la pagina -->
    <div class="contenedor">
        
    <!-- contenedor del titulo de la pagina -->
    <div class="centrar">
        <h1>GENERADOR DE CÓDIGO QR</h1>
    </div>

    <!-- TITULO CONTENEDOR DEL FORMULARIO -->
        
        <!-- contenedor que envuelve todo el formulario -->
        <div id="contenedorQr">
            <!-- formulario que envía datos con método post a esta misma página -->
            <form id="formularioQR" method="POST" action="">
                
                <!-- Campo 1: SKU -->
                <div class="recuadro qr-recuadro">
                     <!-- etiqueta del campo con su respectivo nombre -->
                    <div class="titulo_cuadro">SKU:</div>
                    <!-- contenedor del input con su botón de borrar -->
                    <div class="input-borrar">
                        <!-- cuadro de texto con cosas requeridas para rellenar -->
                        <input class="cuadro_busqueda" type="text" name="sku" id="sku" placeholder="Ingrese SKU" required maxlength="20" 
                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').slice(0,20);">
                    </div>
                </div>
                
                <!-- Campo 2: Producto -->
                <div class="recuadro qr-recuadro">
                    <!-- div para el titulo del campo en este caso nombre -->
                    <div class="titulo_cuadro">NOMBRE:</div>
                    <!-- contenedor del input con su botón de borrar -->
                    <div class="input-borrar">
                        <!-- cuadro de texto con cosas requeredias para rellenar -->
                        <input class="cuadro_busqueda" type="text" name="producto" id="producto" placeholder="Ingrese nombre del producto" required>
                    </div>
                </div>
                <!-- Campo 3: Cantidad -->
                <div class="recuadro qr-recuadro">
                    <!-- div para el titulo del campo cantidad -->
                    <div class="titulo_cuadro">CANTIDAD:</div>
                    <!-- contenedor del input con su botón de borrar -->
                    <div class="input-borrar">
                        <!-- cuadro de texto donde se rellena con cosas requeridas -->
                        <input class="cuadro_busqueda" type="text" name="cantidad" id="cantidad" placeholder="Ingrese cantidad" required>
                    </div>
                </div>

                <!-- Campo 4: Lote -->
                <div class="recuadro qr-recuadro">
                    <!-- titulo para el campo que se va rellenar en este caso lote -->
                    <div class="titulo_cuadro">LOTE:</div>
                    <!-- contenedor del input con su botón de borrar -->
                    <div class="input-borrar">
                        <!-- cuadro de texto donde se rellena osea se escribe con cosas requeridas -->
                        <input class="cuadro_busqueda" type="number" name="lote" id="lote" placeholder="Ingrese número de lote" required maxlength="11" oninput="if(this.value.length>11)this.value=this.value.slice(0,11);">
                    </div>
                </div>

                <!-- Campo 5: Fecha de fabricación -->
                <div class="recuadro qr-recuadro">
                    <!-- div para titulo del campo fecha fabricación -->
                    <div class="titulo_cuadro">FECHA FABRICACIÓN:</div>
                    <!-- contenedor del input con su botón de borrar -->
                    <div class="input-borrar">
                        <!-- cuadro de texto donde se rellena con validaciones -->
                        <input class="cuadro_busqueda" type="date" name="fecha_fabricacion" id="fecha_fabricacion" required>
                    </div>
                </div>

                <!-- Campo 6: Numero de Serie de inicio -->
                <div class="recuadro qr-recuadro">
                    <!-- div para titulo del campo rango serie inicial -->
                    <div class="titulo_cuadro">RANGO SERIE INICIAL:</div>
                    <!-- contenedor del input con su botón de borrar -->
                    <div class="input-borrar">
                        <!-- cuadro de texto donde se rellena con validacioes -->
                        <input class="cuadro_busqueda" type="number" name="serie_inicio" id="serie_inicio" placeholder="Ingrese numero de serie inicial" required maxlength="8" oninput="if(this.value.length>8)this.value=this.value.slice(0,8);">
                    </div>
                </div>

                <!-- Campo 7: Numero de Serie final -->
                <div class="recuadro qr-recuadro">
                    <!-- div cpara titulo del campo rango serie final -->
                    <div class="titulo_cuadro">RANGO SERIE FINAL:</div>
                    <!-- contenedor del input con su botón de borrar -->
                    <div class="input-borrar">
                        <!-- cuadro de texto donde se rellena con validacioes -->
                        <input class="cuadro_busqueda" type="number" name="serie_final" id="serie_final" placeholder="Ingrese numero de serie final" required maxlength="8" oninput="if(this.value.length>8)this.value=this.value.slice(0,8);">
                    </div>
                </div>

                <!-- Campo oculto que contendrá el texto final para el QR -->
                <input type="hidden" name="data" id="data" required>
                <!-- Botón para activar QR dinámico -->
                <button class="boton-dinamico" type="button" id="btnQRDinamico" onclick="toggleQRDinamico()">
                    Crear QR Dinámico
                </button>
                <!-- Botón para generar QR -->
           
            </form>
        </div>
    


<!-- TITULO QR GENERADO -->

        
    <?php
        // Incluye la librería PHP QR Code
        require_once __DIR__ . '/../../../programas/phpqrcode/qrlib.php';
    ?>
    <!-- Verifica si se ha enviado el formulario y si el campo de texto no está vacío -->
    <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['data'])):
        $datos = explode(',', $_POST['data']);
    ?>
    <h1>CÓDIGO QR GENERADO</h1>

        <div class="contenedor-label">
            <div class="producto-label" id="productoLabel">
                <!-- Item y nombre del producto -->
                <div class="seccion-item">
                    <div class="linea-item">
                        <span class="negrita">ITEM: </span>
                        <span class="negrita codigo-item" id="sku_impresion"><?php echo htmlspecialchars($_POST['sku'] ?? ''); ?></span>
                        <span class="negrita producto-nombre" id="producto_impresion"> <?php echo htmlspecialchars($_POST['producto'] ?? '')?></span>
                        <span class="cantidad negrita" id="cantidad_impresion"> <?php echo htmlspecialchars($_POST['cantidad'] ?? '')?></span>
                    </div>
                </div>
                <!-- Número de lote -->
                <div class="seccion-lote">
                    <span class="negrita">BATCH NO.: </span>
                    <span class="negrita valor-lote" id="lote_impresion"><?php echo htmlspecialchars($_POST['lote'] ?? ''); ?></span>
                </div>

                <!-- Fecha y código QR -->
                <div class="seccion-qr">
                    <div class="fecha-vencimiento negrita" id="fecha_fabricacion_impresion"><?php echo htmlspecialchars($datos['3'] ?? ''); ?></div>
                    <div class="qr-code">
                        <?php
                                $tamanio = 3;
                                $level = 'L';
                                $frameSize = 1;
                                $contenido = $_POST['data'];

                                // Captura la salida de la imagen directamente
                                ob_start();
                                QRcode::png($contenido, null, $level, $tamanio, $frameSize);
                                $imageData = ob_get_contents();
                                ob_end_clean();

                                // Convierte la imagen binaria en base64 para insertarla en un src
                                $base64 = base64_encode($imageData);

                                // Muestra el código QR en pantalla sin guardarlo como archivo
                                echo '<img id="qrImagen" src="data:image/png;base64,' . $base64 . '" alt="Código QR">';
                            ?>
                    </div>
                </div>

                <!-- código de barras -->
                <div class="seccion-baja">
                    <div class="logo negrita">SEGMA</div>
                    <div class="seccion-codigoBarras">
                        <div class="barcode">
                            <!-- Simulación de código de barras -->
                            <svg id="barcode"></svg>
                        </div>
                        <div class="barcode-numero negrita"><?php echo htmlspecialchars($_POST['sku'] ?? ''); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <input type="hidden" id="sku_barcode" value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>">

        <!-- TITULO IMPRIMIR Y DESCARGAR -->

        
            <div id="acciones">
                <label for="tipoImpresora">Tipo de impresión:</label>
                <select id="tipoImpresora" onchange="manejarTipoImpresora()">
                    <option value="normal">Impresora normal (6x6 cm)</option>
                    <option value="fiscal">Impresora fiscal (3x3 cm)</option>
                    <option value="etiqueta">Impresora de etiquetas (2x2 cm)</option>
                    <option value="rectangular">Formato rectangular (15x10 cm)</option>
                    <option value="custom">Personalizado</option>
                </select>
                <div id="dimensionesCustom">
                    <h4>Dimensiones Personalizadas:</h4>
                    <div class="alto-ancho">
                        <div>
                            <label for="anchoCustom">Ancho (cm):</label>
                            <input type="number" id="anchoCustom" min="2" max="20" step="0.1" value="6" placeholder="6.0">
                        </div>
                        <div>
                            <label for="altoCustom">Alto (cm):</label>
                            <input type="number" id="altoCustom" min="2" max="20" step="0.1" value="6" placeholder="6.0">
                        </div>
                        <div>
                            <button type="button" onclick="aplicarDimensionesCustom(event)" id="aplicar_dimension">
                                Aplicar
                            </button>
                        </div>
                    </div>
                    <small id="mensaje_dimension">
                        Las dimensiones se ajustarán automáticamente para mantener la proporción de la etiqueta (El minimo es 6 cm)
                    </small>
            </div>

            <!-- TITULO BOTON IMPRIMIR -->

                <button class="botonguardar" onclick="imprimirQREstatico()">Imprimir QR</button>

            <!-- TITULO BOTON DESCARGAR -->

            <a id="descargarQR" download="qr_generado.png">
                <button class="botonguardar" type="button" onclick="descargarQREstatico()">Descargar QR</button>
            </a>
        </div>
            <?php 
                endif; 
            ?>
        </div>
        </div>
    
    <script>
    <?php 
        // crea una cajita vacía para guardar el texto final más tarde
        $texto_final = "";
        // revisa si estamos conectados a la base de datos y que no haya errores
        if (isset($mysqli) && !$mysqli->connect_error) {
            // escribe la orden para pedir los últimos 50 registros guardados en el historial
            $sql_hist = "SELECT * FROM historial_qr ORDER BY id DESC LIMIT 50";
            // envía esa orden a la base de datos y guarda lo que responda
            $result_hist = $mysqli->query($sql_hist);
            // crea una lista vacía para ir anotando cada producto que encontremos
            $array_lineas = [];
            // si la base de datos respondió con información, entra aquí
            if ($result_hist) {
                // empieza a repasar cada producto encontrado uno por uno
                while($row = $result_hist->fetch_assoc()) {
                    // hace una lista de símbolos molestos (como comillas o enters) que queremos borrar
                    $limpiar = array('"', "'", "\r", "\n");
                    // limpia el sku quitando los símbolos molestos
                    $sku  = str_replace($limpiar, " ", $row['sku']);
                    // limpia el nombre del producto 
                    $prod = str_replace($limpiar, " ", $row['producto']);
                    // limpia la cantidad
                    $cant = str_replace($limpiar, " ", $row['cantidad']);
                    // limpia el lote
                    $lote = str_replace($limpiar, " ", $row['lote']);
                    // limpia la fecha de fabricación
                    $fecha = str_replace($limpiar, " ", $row['fecha_fabricacion']);
                    // arma la línea con el formato que entiende el JS y la agrega a la lista
                    $array_lineas[] = $sku . "|||" . $prod . "|||" . $cant . "|||" . $lote . "|||" . $fecha;
                }
            }
            // une todas las líneas con el separador que entiende el JS
            $texto_final = implode(";;;", $array_lineas);
        }

        // imprime el código JS que define la variable global con todo el texto del historial
        echo 'var DATOS_HISTORIAL_RAW = "' . $texto_final . '";';
    ?>
    
    console.log("Historial cargado (Reutilizando conexión):", DATOS_HISTORIAL_RAW);
</script>
    <script src="../../programas/qrcode.min.js?v=<?= time() ?>"></script>
    <script src="../../js/ingreso_ventas/consultar_productos/generar_qr.js?v=<?= time() ?>"></script>
    <script src="../../programas/JsBarcode.all.min.js?v=<?= time() ?>"></script>
        <!-- codigo para que deje imprimir los QR en celular, tablet etc -->
        <iframe id="printFrame" style="display:none;"></iframe>

    </div> <!-- FIN CONTENEDOR PRINCIPAL -->
    
    </body>
    </html>


<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <?php 
        // Cierra la conexión a la base de datos
        // $mysqli->close();
    ?>

<!-- ----------------------------
        -- FIN CIERRE CONEXION BD --
        ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa generar_qr .PHP --------------------------------------------
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

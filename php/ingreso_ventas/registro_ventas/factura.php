
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
     ------------------------------------- INICIO ITred Spa factura .PHP ----------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->


<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

    <?php
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
        // Establecer el charset a utf8mb4
        $mysqli->set_charset("utf8mb4");
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<?php
// Inicializamos variables para cuando sea edición para la factura
$estamos_editando = false;
$datos_editar = [];
$id_factura = '';
// Verificamos si estamos en modo edición
if (isset($_GET['id_editar'])) {
    $id_factura = $_GET['id_editar'];
    $estamos_editando = true;

    // Buscamos la factura específica
    $consulta_editar = $mysqli->query("SELECT * FROM factura WHERE id = '$id_factura'");
    if ($consulta_editar && $consulta_editar->num_rows > 0) {
        $datos_editar = $consulta_editar->fetch_assoc();
    }
}

// Preparamos variables para detectar diferencias y guardar resultados
$detalleDiferencias = [];
$hayDiferencias = false; // Bandera que indica si hay problemas entre lo facturado y lo ingresado
$rows = []; // Aquí se guardarán las filas que devuelva la consulta

// Consulta SQL corregida para obtener solo las facturas con diferencias
// Usa subconsulta para evitar multiplicación de cantidades por el JOIN
$sql = "
SELECT 
    f.n_factura,
    f.nombre_empresa,
    f.descripcion_producto,
    f.codigo_producto,
    f.cantidad_producto AS cantidad_factura,
    COALESCE(v_sum.total_ingresado, 0) AS cantidad_ingresada
FROM factura f
LEFT JOIN (
    SELECT 
        numero_fact,
        sku,
        SUM(cantidad) AS total_ingresado
    FROM venta
    GROUP BY numero_fact, sku
) v_sum 
    ON f.codigo_producto = v_sum.sku 
    AND f.n_factura = v_sum.numero_fact
WHERE f.cantidad_producto != COALESCE(v_sum.total_ingresado, 0)
";

// Prepara la consulta
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die('Error en prepare: ' . $mysqli->error);
}

// Ejecuta la consulta
$stmt->execute();
// Obtiene los resultados
$res = $stmt->get_result();

// Recorre cada fila devuelta por la base de datos
while ($row = $res->fetch_assoc()) {
    // Si hay al menos una fila, hay diferencias
    $hayDiferencias = true;
    // Guardamos solo las facturas malas
    $rows[] = $row;
}
?>


<!-- TITULO HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- Titulo del apartado de factura -->
    <title>Ingreso de Factura</title>

    <!-- carga el archivo de estilos CSS para la página de factura -->
    <link rel="stylesheet" href="/css/ingreso_ventas/registro_ventas/factura.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
</head>
    <!-- TITULO BODY -->

<body>
    

<!-- previsualización -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
    

<!-- TITULO DATOS DE FACTURA -->

        <!-- Contenedor principal -->
        <div class="titulo-con-alerta">
            <!-- Titulo principal -->
            <h1 class="titulo-principal">INGRESO DE FACTURA</h1>

        </div>
    <!-- formulario para la factura con todos sus respectivos campos -->
    <form id="formFactura" action="/php/ingreso_ventas/registro_ventas/procesar_factura.php" method="POST" enctype="multipart/form-data" onsubmit="return prepararEnvio()">
        <div class="factura-contenedor">
            <!-- Cabecera con logo y datos de la empresa -->
            <div class="fila-cabecera">
                 <!-- Logo del cliente con vista previa -->
                <div class="box box-logo">
                    <!-- mesnaje de tecxto que sale en el campo  -->
                    <label>Logo cliente</label>
                    <!-- Vista previa del logo -->
                    <div id="previewLogo">
                        <!-- llamada a la imagen de un logo -->
                        <img src="/imagenes/ingreso_ventas/registro_ventas/logo_segma.png" alt="Logo Segma" style="max-width: 150px; max-height: 150px;">
                    </div>
                    <!-- Input para subir el logo -->
                    <input type="file" accept="image/*" id="logoCliente" name="logoCliente">
                </div>

                <!-- Nombre y giro de la empresa -->
                <div class="box-nombre">
                    <!-- mensaje de texto que aparece en el campo  -->
                    <label>Nombre de la empresa:</label>
                    <!-- Input para el nombre de la empresa -->
                    <input type="text" id="nombreEmpresa" name="nombreEmpresa" placeholder="Ingrese el nombre de la empresa" 
                    value="<?php echo $estamos_editando ? ($datos_editar['nombre_empresa'] ?? '') : ''; ?>">
                    <!-- mensaje de texto que aparece en el campo  -->
                    <label>Giro de la empresa:</label>
                    <!-- Textarea osea espacio donde escribir para el giro de la empresa -->
                    <textarea id="giroEmpresa" name="giroEmpresa" rows="3" placeholder="Ingrese el giro de la empresa"><?php echo $estamos_editando ? ($datos_editar['giro_empresa'] ?? '') : ''; ?></textarea>
                </div>

                <!-- RUT y número de factura de la empresa -->
                <div class="box box-rut">
                    <!-- mensaje de texto que aparece en el campo  -->
                    <label>RUT de la empresa:</label>
                    <!-- Input para el RUT de la empresa -->
                   <input type="text" id="rutEmpresa" name="rutEmpresa" placeholder="Ingrese el rut de la empresa" 
                    value="<?php echo $estamos_editando ? ($datos_editar['rut_empresa'] ?? '') : ''; ?>">
                    <!-- mensaje que aparece en campo -->
                    <label>N° factura:</label>
                    <!-- Input para el número de factura -->
                    <input type="number" id="numeroFactura" name="numeroFactura" maxlength="10" placeholder="Ingrese el número de factura" 
                    value="<?php echo $estamos_editando ? ($datos_editar['n_factura'] ?? '') : ''; ?>">
                </div>
            </div>

    <!-- TITULO PRODUCTOS DE FACTURA -->
        
                <!-- Tabla de productos -->
                <div class="tabla-productos">
                <!-- tabla con los datos de producto para el formulario  -->
                <table class="tabla-estilo">
                    <!-- lista con los respectivos campos -->
                    <thead>
                    <tr>
                        <th class="th-codigo">SKU</th>
                        <th class="th-descripcion">Producto</th>
                        <th class="th-cantidad">Cantidad</th>
                        <th class="th-precio">Precio</th>
                        <th class="th-adicional">% Impto Adic.</th>
                        <th class="th-descuento">% Desc.</th>
                        <th class="th-valor">Valor</th>
                    </tr>
                    </thead>
                    <!-- tabla que seria para editar cuando alguien quier emodificar una factura -->
                    <tbody id="tabla-productos-body">
                    <!-- Fila inicial editable para ingresar un producto -->
                    <tr>
                        <!-- Input para el código del producto -->
                        <td>
                            <input type="text" class="codigo" name="producto_0_codigo"
                                value="<?php echo $estamos_editando ? ($datos_editar['codigo_producto'] ?? '') : ''; ?>">
                        </td>
                        <!-- Input para la descripción del producto -->
                        <td class="producto-cell">
                            <input type="text" class="descripcion autocomplete-producto" name="producto_0_descripcion"
                                placeholder="Buscar producto..."
                                value="<?php echo $estamos_editando ? ($datos_editar['descripcion_producto'] ?? '') : ''; ?>">
                            <div class="autocomplete-list"></div>
                        </td>
                        <!-- Input para la cantidad del producto -->
                        <td>
                            <input type="number" class="cantidad" name="producto_0_cantidad" placeholder="0"
                                value="<?php echo $estamos_editando ? ($datos_editar['cantidad_producto'] ?? '') : ''; ?>">
                        </td>
                        <!-- Input para el precio del producto -->
                        <td>
                            <input type="text" class="precio" name="producto_0_precio" placeholder="0"
                                value="<?php echo $estamos_editando ? ($datos_editar['precio_producto'] ?? '') : ''; ?>">
                        </td>
                        <!-- Input para el impuesto adicional del producto -->
                        <td>
                            <input type="number" class="impuestoAdic" name="producto_0_adicional" placeholder="0"
                                value="<?php echo $estamos_editando ? ($datos_editar['impacto_producto'] ?? 0) : ''; ?>">
                        </td>
                        <!-- Input para el descuento del producto -->
                        <td>
                            <input type="number" class="descuento" name="producto_0_descuento" placeholder="0"
                                value="<?php echo $estamos_editando ? ($datos_editar['descuento_producto'] ?? 0) : ''; ?>">
                        </td>
                        <!-- Valor calculado del producto -->
                        <td class="valor">
                            <?php echo $estamos_editando ? ($datos_editar['valor_producto'] ?? 0) : '0'; ?>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Botones para agregar o eliminar filas -->
                <button type="button" onclick="agregarFilaProducto()" class="btn-agregar">Agregar producto</button>
                <button type="button" onclick="eliminarUltimaFila()" class="btn-eliminar">Eliminar producto</button>
                </div>


    <!-- TITULO DE TOTALES -->

                <!-- Totales de la factura -->
                <div class="totales-wrapper">
                    <div class="totales-box">
                            <!-- Monto neto sin impuestos -->
                            <div class="totales-linea">
                                <label>MONTO NETO: $</label>
                                <input type="text" id="valorNeto" readonly>
                            </div>
                            <!-- IVA calculado automáticamente -->
                            <div class="totales-linea">
                                <label>I.V.A 19%: $</label>
                                <input type="text" id="iva" readonly>
                            </div>
                            <!-- Impuesto adicional si aplica -->
                            <div class="totales-linea">
                                <label>IMPUESTO ADICIONAL: $</label>
                                <input type="text" id="impuestoAdicional" readonly>
                            </div>
                            <!-- Total final de la factura -->
                            <div class="totales-linea">
                                <label>TOTAL: $</label>
                                <input type="text" id="totalFactura" readonly>
                            </div>
                    </div>
                </div>
        </div>

        <!-- Botón guardar -->
       <div class="guardar-box">
            <!-- Botón guardar -->
            <input type="hidden" name="accion" value="<?php echo $estamos_editando ? 'editar' : 'crear'; ?>">
            <!-- ID oculto de la factura -->
            <input type="hidden" name="id_oculto_factura" value="<?php echo $id_factura; ?>">
            <!-- Total de productos -->
            <input type="hidden" name="totalProductos" id="totalProductos" value="1">
            <!-- boton de guardar -->
            <button type="submit" class="btn-guardar">
                <!-- boton guardar cambios cuando estamos editando -->
                <?php echo $estamos_editando ? 'Guardar Cambios' : 'Guardar'; ?>
            </button>
            <!-- Botón de ayuda -->
            <button type="button" class="boton-ayuda" data-tooltip="Si ya guardo sus datos...">?</button>

        </div>
        
            
    </div>
</form>
    <!-- Mensaje de servidor -->
    <?php if (isset($_GET['mensaje'])): ?>
        <div id="mensajeServidor" 
             data-texto="<?php echo htmlspecialchars($_GET['mensaje']); ?>" 
             style="display: none;">
        </div>
    <?php endif; ?>
    <!-- TITULO JS -->    

        <!-- Script JavaScript para lógica de la factura -->
        <script src="/js/ingreso_ventas/registro_ventas/factura.js?v=<?= time() ?>"></script>
        
        <!-- Script JavaScript para lógica de buscar los productos -->
        <script src="/js/ingreso_ventas/registro_ventas/buscar_productos.js?v=<?= time() ?>"></script>

        
        <?php include(__DIR__ . '/buscar_empresa.php'); ?>
       
        
        <script>
            // Limpia la alerta después de imprimir
            sessionStorage.removeItem('alerta_ingreso');
        </script>

        
</body>
</html>


<!-- ------------------------------------------------------------------------------------------------------------
     ------------------------------------- FIN ITred Spa factura .PHP -------------------------------------------
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

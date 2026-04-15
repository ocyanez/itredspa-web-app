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
     ------------------------------------- INICIO ITred Spa ingreso_datos .PHP ----------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

    <?php
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
        // Establecer el charset a utf8mb4
        $mysqli->set_charset("utf8mb4");
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
    <title>ventas</title>
    <!-- carga el archivo de estilos CSS para la página de ventas -->
    <link rel="stylesheet" href="/css/ingreso_ventas/ingreso_clientes/ingreso_datos.css?v=<?= time() ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
</head>

    <!-- TITULO BODY-->

        <!-- cuerpo principal del archivo -->   
        <body class="ingreso-datos">

        <!-- agrupa todos los bloques visibles del ingreso de clientes -->
        <div class="contenedor-principal">

    <!-- TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN -->

                <?php
                $mysqli->set_charset("utf8");
                // Detecta modo superadmin por URL
                $esSuperadmin = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'superadmin.php') !== false);

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // Generar token CSRF si no existe
                if (empty($_SESSION['csrf_token'])) {
                    try {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } catch (Exception $e) {
                        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
                    }
                }

                // Verifica si el usuario ha iniciado sesión
                if (!isset($esSuperadmin) && !isset($_SESSION['correo'])) {
                    // Si el usuario no ha iniciado sesión y no está en la vista superadmin, redirige a la página de inicio
                    header("Location: ../../ingreso_ventas.php");
                    exit();
                }

                // Manejo del formulario de eliminación de cliente por RUT
                $eliminar_mensaje = null;
                // Opcional: hacer que mysqli lance excepciones para facilitar rollback
                // mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form']) && $_POST['form'] === 'eliminar_usuario') {
                    // Verificar token CSRF
                    $csrf_in = $_POST['csrf_token'] ?? '';
                    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_in)) {
                        $eliminar_mensaje = ['error' => 'Token CSRF inválido. Por seguridad recargue la página y vuelva a intentarlo.'];
                    } else {
                        // Acción para aplicar al boton de borrar (solo cliente o cliente y datos)
                        $accion = $_POST['accion'] ?? 'solo';

                        // Limpiar RUT (permitir números y K/k)
                        $rut_raw = $_POST['rut'] ?? '';
                        // Agregamos strtoupper() para que la 'k' siempre sea 'K' y coincida con la base de datos
                        // Limpiamos SOLO puntos y espacios, mantenemos el guion (-), y aseguramos mayúsculas
                        $rut_solo = strtoupper(str_replace(['.', ' '], '', trim($rut_raw)));

                        if ($rut_solo === '') {
                            $eliminar_mensaje = ['error' => 'RUT inválido.'];
                        } else {
                            //  prara verificar si el cliente existe en la BD 
                            $db_match = 0;
                            // Variable para ejemplo de RUT en BD si hay coincidencia parcial
                            $db_example = null;
                            // Buscar coincidencia exacta primero
                            if ($check_stmt = $mysqli->prepare("SELECT rut FROM cliente WHERE REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ? LIMIT 1")) {
                                // Enlazar y ejecutar
                                $check_stmt->bind_param('s', $rut_solo);
                                // Ejecutar consulta
                                $check_stmt->execute();
                                // Obtener resultado
                                $check_res = $check_stmt->get_result();
                                // Verificar si hay fila
                                if ($row_check = $check_res->fetch_assoc()) {
                                    // Coincidencia exacta encontrada
                                    $db_match = 1;
                                    // Guardar ejemplo de RUT en BD
                                    $db_example = $row_check['rut'];
                                }
                                // Cerrar statement
                                $check_stmt->close();
                            }
                            // si no hay coincidencia exacta, buscar parcial para ayudar a depurar
                            if ($db_match === 0) {
                                // Buscar coincidencia parcial 
                                if ($like_stmt = $mysqli->prepare("SELECT rut FROM cliente WHERE REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') LIKE CONCAT('%', ?, '%') LIMIT 1")) {
                                    // Enlazar y ejecutar
                                    $like_stmt->bind_param('s', $rut_solo);
                                    // Ejecutar consulta
                                    $like_stmt->execute();
                                    // Obtener resultado
                                    $like_res = $like_stmt->get_result();
                                    // Verificar si hay fila
                                    if ($row_like = $like_res->fetch_assoc()) {
                                        // Guardar ejemplo de RUT en BD
                                        $db_example = $row_like['rut'];
                                    }
                                    // Cerrar statement
                                    $like_stmt->close();
                                }
                            }

                            // Si no se encontró, añadimos información diagnostica al mensaje para depuración
                            if ($db_match === 0) {
                                // Información diagnóstica
                                $diagnostic = "Enviado: '" . $rut_raw . "' Normalizado: '" . $rut_solo . "'";
                                if ($db_example) $diagnostic .= " — Ejemplo en BD que coincide parcialmente: '" . $db_example . "'";
                                // No sobrescribimos todavía $eliminar_mensaje; lo usaremos más abajo si falta cliente
                            }
                            // Proceder a eliminar según la acción seleccionada
                            if ($accion === 'todo') {
                                // Borrar ventas y luego cliente en transacción
                                $mysqli->begin_transaction();
                                try {
                                    // Eliminar ventas relacionadas — intentamos en tablas comunes 'venta' y 'ventas'
                                    $ventas_afectadas = 0;
                                    // Lista de tablas a intentar eliminar
                                    $tables_to_try = ['venta'];
                                    // Agregar más tablas si es necesario
                                    foreach ($tables_to_try as $tname) {
                                        // Intentar eliminar usando columna 'rut'
                                        $sql_rut = "DELETE FROM $tname WHERE REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ?";
                                        // Preparar y ejecutar
                                        if ($stmt_try = $mysqli->prepare($sql_rut)) {
                                            // Enlazar y ejecutar
                                            $stmt_try->bind_param('s', $rut_solo);
                                            // ejecutar
                                            $stmt_try->execute();
                                            // Contar filas afectadas
                                            $ventas_afectadas += $stmt_try->affected_rows;
                                            // Cerrar statement
                                            $stmt_try->close();
                                        }

                                        // Intentar eliminar usando columna 'cliente_rut' (si existe)
                                        $sql_cliente_rut = "DELETE FROM $tname WHERE REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ?";
                                        // Preparar y ejecutar
                                        if ($stmt_try = $mysqli->prepare($sql_cliente_rut)) {
                                            // Enlazar y ejecutar
                                            $stmt_try->bind_param('s', $rut_solo);
                                            // ejecutar
                                            $stmt_try->execute();
                                            // Contar filas afectadas
                                            $ventas_afectadas += $stmt_try->affected_rows;
                                            // Cerrar statement
                                            $stmt_try->close();
                                        }
                                    }


                                    // Finalmente eliminar el cliente (normalizando RUT almacenado)
                                    if ($stmt = $mysqli->prepare("DELETE FROM cliente WHERE REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ?")) {
                                        // Enlazar y ejecutar
                                        $stmt->bind_param('s', $rut_solo);
                                        // Ejecutar
                                        $stmt->execute();
                                        // Contar filas afectadas
                                        $cliente_afectado = $stmt->affected_rows;
                                        // Cerrar statement
                                        $stmt->close();
                                    } else {
                                        // Error al preparar la consulta de eliminación (cliente)
                                        $cliente_afectado = 0;
                                    }
                                    // Confirmar transacción
                                    $mysqli->commit();
                                    // Preparar mensaje según resultado
                                    if ($cliente_afectado > 0) {
                                        // Éxito
                                        $eliminar_mensaje = ['success' => "Cliente y datos asociados eliminados correctamente. Cliente afectado: $cliente_afectado, ventas eliminadas: $ventas_afectadas."];
                                    } else {
                                        // mensaje de que no se encontró cliente
                                        $msg = 'No se encontró un cliente con ese RUT.';
                                        // Agregar diagnóstico si existe
                                        if (!empty($diagnostic)) $msg .= ' ' . $diagnostic;
                                        // Asignar mensaje de error
                                        $eliminar_mensaje = ['error' => $msg];
                                    }
                                // Capturar errores y hacer rollback
                                } catch (Exception $e) {
                                    // Hacer rollback en caso de error
                                    $mysqli->rollback();
                                    // Asignar mensaje de error
                                    $eliminar_mensaje = ['error' => 'Error al eliminar datos: ' . $e->getMessage()];
                                }
                            } else {
                                // Solo eliminar cliente - Primero poner RUT en NULL en ventas
                                $mysqli->begin_transaction();
                                try {
                                    // actualizar ventas poniendo rut = NULL
                                    if ($stmt_ventas = $mysqli->prepare("UPDATE venta SET rut = NULL WHERE REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ?")) {
                                        // enlazar y ejecutar
                                        $stmt_ventas->bind_param('s', $rut_solo);
                                        // ejecutar
                                        $stmt_ventas->execute();
                                        // contar filas afectadas
                                        $ventas_actualizadas = $stmt_ventas->affected_rows;
                                        // cerrar statement
                                        $stmt_ventas->close();
                                    }
                                    
                                    // eliminar el cliente
                                    if ($stmt = $mysqli->prepare("DELETE FROM cliente WHERE REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '') = ?")) {
                                        // enlazar y ejecutar
                                        $stmt->bind_param('s', $rut_solo);
                                        // ejecutar
                                        $stmt->execute();
                                        // contar filas afectadas
                                        $deleted_rows = $stmt->affected_rows;
                                        // cerrar statement
                                        $stmt->close();
                                    } else {
                                        // mostrar error al preparar la consulta
                                        throw new Exception('Error al preparar la consulta de eliminación (cliente).');
                                    }
                                    // confirmar
                                    $mysqli->commit();
                                    // preparar mensaje según resultado
                                    if ($deleted_rows > 0) {
                                        // mensaje de éxito
                                        $msg = "Cliente eliminado correctamente.";
                                        // agregar info de ventas actualizadas si aplica
                                        if ($ventas_actualizadas > 0) {
                                            // agregar info de ventas actualizadas
                                            $msg .= " Se actualizaron $ventas_actualizadas ventas (RUT puesto en NULL).";
                                        }
                                        // asignar mensaje de éxito
                                        $eliminar_mensaje = ['success' => $msg];
                                    } else {
                                        // mensaje de que no se encontró cliente
                                        $msg = 'No se encontró un cliente con ese RUT.';
                                        // Agregar diagnóstico si existe
                                        if (!empty($diagnostic)) $msg .= ' ' . $diagnostic;
                                        // asignar mensaje de error
                                        $eliminar_mensaje = ['error' => $msg];
                                    }
                                // capturar errores y hacer rollback
                                } catch (Exception $e) {
                                    // hacer rollback en caso de error
                                    $mysqli->rollback();
                                    // asignar mensaje de error
                                    $eliminar_mensaje = ['error' => 'Error al eliminar cliente: ' . $e->getMessage()];
                                }
                            }
                        }
                    }
                }

                // Si se procesó una acción de eliminación, guardar mensaje en sesión y redirigir (PRG)
                if ($eliminar_mensaje !== null) {
                    $_SESSION['flash_eliminar'] = $eliminar_mensaje;
                    // Redirigir al mismo URL con GET para evitar reenvío de formulario al recargar
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit();
                }

                ?>

    <!-- TITULO DE INGRESO DE DATOS -->
               
        <!-- título principal -->        
        <h1>INGRESO DE DATOS</h1>
        <!-- menú de pestañas de arriba donde se va ir cambiando -->
        <div class="menu-pestanas">
            <!-- boton de pestaña de ingreso de clientes -->
            <button class="btn-pestana" onclick="cambiarPestana('clientes')">Ingreso Clientes</button>
            <!-- boton de pestaña de ingreso de productos -->
            <button class="btn-pestana" onclick="cambiarPestana('productos')">Ingreso Productos</button>
            <!-- boton de pestaña de ingreso de factura -->
            <button class="btn-pestana" onclick="cambiarPestana('factura')">Ingreso Factura</button>
            <!-- boton de pestaña de ingreso de ventas -->
            <button class="btn-pestana" onclick="cambiarPestana('ventas')">Ingreso Ventas</button>
        </div>
    <!-- TITULO INGRESO FACTURA -->

     <!-- sección de ingreso de factura -->
     <div id="seccion-factura" class="contenido-pestana" style="display: none;">
            <!-- div con header para titulo de botones de lsoe xcel -->
            <div class="header-titulo-botones">
                <!-- div con grupo de botones -->
                <div class="grupo-botones-header">
                    <!-- botón para descargar plantilla -->
                    <form action="/php/ingreso_ventas/ingreso_clientes/exportar_excel.php" method="post" style="margin:0;">
                        <button type="submit" name="plantilla" value="facturas" class="boton-header-estilo" title="Descargar Plantilla">
                            <!-- imagen de descarga -->
                            <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img10.png" alt="Descargar">
                            <!-- texto para indicar que es descargar plantilla -->
                            <span>Descargar<br>Plantilla</span>
                        </button>
                    </form>
                    <!-- botón para subir plantilla -->
                    <form action="/php/ingreso_ventas/registro_ventas/cargar_factura.php" method="post" enctype="multipart/form-data" style="margin:0;">
                        <div class="boton-header-estilo" onclick="document.getElementById('archivo_excel_factura_top').click();" title="Subir Plantilla">
                            <!-- imagen de subir -->
                            <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img9.png" alt="Subir">
                            <!-- texto para indicar que es subir plantilla -->
                            <span>Subir<br>Plantilla</span>
                        </div>
                        <!-- input oculto para archivo excel -->
                        <input type="file" name="archivo_excel" id="archivo_excel_factura_top" 
                            accept=".xls,.xlsx" class="input-oculto" onchange="cargar_facturas_excel(event);">
                    </form>

                </div>
            </div>
            <!-- contenedor principal de la sección de factura -->
            <?php $id_editar = $_GET['id_editar'] ?? ''; ?>
            <!-- iframe para mostrar el formulario de factura cuando toca editar -->
            <iframe 
                src="/php/ingreso_ventas/registro_ventas/factura.php<?= $id_editar ? '?id_editar='.$id_editar : '' ?>" 
                style="width: 100%; height: 1100px; border: none;"
                title="Ingreso Factura">
            </iframe>

        </div>
        </div>
        </div>
    <!-- TITULO INGRESO PRODUCTOS -->

        <!-- sección de ingreso de productos -->
        <div id="seccion-productos" class="contenido-pestana" style="display: none;">
            <!-- botón de pestaña de ingreso de productos -->
            <div class="modulos-clientes">
                <!-- div con clase box-superior-wrapper para despues darle estilo  -->
                <div class="box-superior-wrapper">
                    <!-- div con clase box box-superior para despues darle estilo  -->
                    <div class="box box-superior">
                        <!-- titulo para la tabla d eingreso manual -->
                        <h2 class="titulo_g" style="text-align: center;">Ingreso productos manual</h2>
                        <!-- formulario para registrar productos manualmente -->
                        <form class="opciones" onsubmit="return registrar_producto(event)" method="post" id="formularioProducto" style="width: 324px; display: flex; flex-direction: column; gap: 10px;">
                            <!-- input oculto para el ID del producto -->
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <label for="prod_sku" style="width: 100px;">SKU:</label>
                                <input type="text" id="prod_sku" name="sku" placeholder="Ej: 123456" required style="width: 180px;">
                            </div>
                            <!-- input oculto para el nombre del producto -->
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <label for="prod_nombre" style="width: 100px;">Producto:</label>
                                <input type="text" id="prod_nombre" name="producto" placeholder="Ej: Bebida 3L" required style="width: 180px;">
                            </div>
                            <!-- input oculto para el precio del producto -->
                            <div class="group" style="margin-top: 10px;">
                                <button type="submit">Registrar</button>
                                <button type="button" class="boton-interrogacion" data-tooltip="Ingresa el SKU y el Nombre del producto.">?</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- contenedor de las opciones de descarga y subida de plantillas -->
                <div class="box-superior-wrapper">
                    <div class="box box-superior">
                        <!-- div para la cabecera de los titulos de los excel donde se decargan  -->
                        <div class="cabecera-doble">
                            <!-- titulo de descargar plantilla masiva -->
                            <h2 class="titulo-opcion mitad">Descargar plantilla productos Masiva</h2>
                            <!-- titulo de subir plantilla masiva -->
                            <h2 class="titulo-opcion mitad">Subir plantilla productos Masiva</h2>
                        </div>
                        <!-- formulario para subir archivo Excel -->
                        <div class="contenido-box">
                            <div class="imagenes-dobles">
                                <!-- imagen de descarga de plantilla -->
                                <div class="contenedor-img-texto">
                                    <!-- llamada al backend que hace la exportacion del excel -->
                                    <form action="/php/ingreso_ventas/ingreso_clientes/exportar_excel.php" method="post">
                                        <!-- botón de descarga -->
                                        <button type="submit" name="plantilla" value="productos" class="boton-sin-estilo">
                                            <!-- imagen de descarga -->
                                            <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img10.png" 
                                                alt="Descargar Plantilla Productos" 
                                                class="imagen-icon">
                                        </button>
                                    </form>
                                    <!-- botón de tutorial que muestra un mensaje de ayuda -->
                                    <button type="button" class="boton-tutorial" data-tooltip="Descarga el formato Excel para llenar tus productos masivamente.">?</button>
                                </div>
                                <!-- imagen de subida de plantilla -->
                                <div class="contenedor-img-texto">
                                    <form method="post" id="formExcelProductos" enctype="multipart/form-data">
                                        <!-- input oculto para el ID del producto -->
                                        <button type="button" class="boton-sin-estilo" onclick="document.getElementById('archivo_excel_prod').click();">
                                            <!-- imagen de subida -->
                                            <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img9.png" 
                                                 alt="Subir Plantilla Productos" 
                                                 class="imagen-icon">
                                        </button>
                                        <input type="file" name="archivo_excel" id="archivo_excel_prod" 
                                               accept=".xls,.xlsx" style="display: none;" 
                                               onchange="cargar_productos_excel(event);">
                                    </form>
                                    <!-- botón de tutorial que muestra un mensaje de ayuda -->
                                    <button type="button" class="boton-tutorial" data-tooltip="Sube el archivo Excel con tus productos al sistema.">?</button>
                                </div> </div> </div> </div> </div> </div> 

    <!-- TITULO LISTADO DE PRODUCTOS -->
         
        <!-- Modal para editar producto -->
        <div class="contenedor-listado-descarga" style="margin-top: 30px; width: 100%;">
                <!-- contenedeor que es una tabla para lista d eproductos -->
                <div class="container-tabla">
                    <!-- caja visual para la tabla de productos -->
                    <div class="titulo-buscador-flex">
                        <!-- titulo de la lista -->
                        <h2>LISTADO DE PRODUCTOS</h2>
                            <!-- buscador de productos -->
                            <div class="buscador-clientes">
                                <!-- Input para buscar productos -->
                                <input type="text" id="buscador_producto" onkeyup="buscarProducto()" placeholder="Buscar por producto o SKU...">
                            </div>
                    </div>
                    <!-- Separador de letras -->
                    <div id="listado-productos">
                        <!-- funcion php para obtener los productos -->
                        <?php
                        // Consulta para obtener los productos
                            $sql_prod = "SELECT id, sku, producto FROM producto ORDER BY producto ASC";
                            $res_prod = $mysqli->query($sql_prod);
                        ?>
                        <!-- funcion para mostrar los productos -->
                        <?php if ($res_prod && $res_prod->num_rows > 0): ?>
                            <!-- tabla responsiva -->
                            <div class="tabla-responsiva">
                                <!-- tabla de productos -->
                                <table class="tabla-estilo" id="tabla_productos">
                                    <!-- lista con los campos requeridos -->
                                    <thead>
                                        <tr>
                                            <th>SKU</th>
                                            <th>Producto</th>
                                            <th>Acción</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Separador de letras -->
                                        <?php while ($row = $res_prod->fetch_assoc()): ?>
                                            <tr>
                                                <!-- Separador de letras -->
                                                <td><?= htmlspecialchars($row['sku']) ?></td>
                                                <td><?= htmlspecialchars($row['producto']) ?></td>
                                                <td>
                                                <!-- Botón para editar producto -->
                                                <button class="btn-editar" 
                                                    type="button"
                                                    onclick="abrir_modal_producto('<?= $row['id'] ?>', '<?= htmlspecialchars($row['sku'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['producto'], ENT_QUOTES) ?>')">
                                                    Editar Producto
                                                </button>
                                                </td>
                                                    </tr>
                                        <?php endwhile; ?>
                                        </tbody>
                                </table>
                            </div>
                        <!-- Modal para editar producto -->
                         <?php else: ?>
                                <!-- tabbal con mensaje para cuando no haya un producto -->
                                <div class="tabla-placeholder" style="padding:14px; text-align:center; border:1px dashed var(--color-borde); border-radius:8px;">
                                     No se encontraron productos registrados.
                                </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    
                </div></div> </div></div></div>
        
    <!-- TITULO INGRESO CLIENTES -->
     
        <div id="seccion-clientes" class="contenido-pestana" style="display: none;">
            <!-- Clase para ingreso clientes manualmente -->
            <div class="modulos-clientes">
                <!-- Contenedor superior que agrupa el bloque de ingreso manual -->  
                <div class="box-superior-wrapper">
                    <!-- Caja visual con estilos superiores -->  
                    <div class="box box-superior">
                        <!-- Título del bloque de ingreso manual -->  
                        <h2 class="titulo_g" style="text-align: center;">Ingreso clientes manual</h2>
                        <!-- Formulario para registrar clientes manualmente -->  
                        <form class="opciones" onsubmit="return registrar_cliente(event)" method="post" id="formularioRegistro" style="width: 324px;">
                            
                            <!-- Campo para ingresar nombre o razón social -->
                            <label for="nombre">Nombre o Razón Social:</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Ingrese nombre o Razón Social" required>
                            
                            <!-- Campo para ingresar el RUT sin formato -->
                            <label for="rut">Rut:</label>
                            <input type="text" id="rut" name="rut" placeholder="Ingrese RUT sin puntos ni guion" required>
                            <!-- Grupo de botones: registrar y ayuda -->  
                            <div class="group">
                                <button type="submit">Registrar</button>
                                <!-- Botón de ayuda con tooltip explicativo -->
                                <button type="button" class="boton-interrogacion" data-tooltip="Para registrar clientes manualmente, complete el formulario previamente y haga clic en 'Registrar'.">?</button>
                            </div>
                        </form>
                    </div>
                </div>

    <!-- TITULO PLANTILLAS DE EXCEL -->

        <!-- Contenedor principal que agrupa las secciones de clientes y ventas -->
        <div class="box-superior-wrapper">
             <!-- Recuadro lateral izquierdo: opciones relacionadas con clientes -->
             <div class="box box-superior">
                 <!-- Cabecera con dos títulos: descargar y subir plantilla de clientes -->
                <div class="cabecera-doble">
                    <h2 class="titulo-opcion mitad">Descargar plantilla ingreso de clientes Masiva</h2>
                    <h2 class="titulo-opcion mitad">Subir plantilla ingreso de clientes Masiva</h2>
                </div>
                <!-- Contenido del bloque de clientes -->  
                <div class="contenido-box">
                    <!-- Contenedor para imagenes dobles--> 
                    <div class="imagenes-dobles">

                         <!-- Contenedor visual para descargar plantilla de clientes -->
                        <div class="contenedor-img-texto">
                            <!-- Formulario que envía la solicitud de descarga al backend -->
                            <form action="/php/ingreso_ventas/ingreso_clientes/exportar_excel.php" method="post" id="exportar_datos_excel">
                                <!-- Botón visual con imagen para descargar plantilla --> 
                                <button type="submit" name="plantilla" value="clientes" class="boton-sin-estilo">
                                    <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img10.png"
                                            alt="Exportar datos Excel" title="Exportar Clientes"
                                            class="imagen-icon imagen-exportar">
                                </button>
                            </form>
                            <!-- Botón de ayuda con explicación sobre la descarga -->
                            <button type="button" class="boton-tutorial" data-tooltip="Descarga la lista de clientes en formato Excel.">?</button>
                        </div>


                         <!-- Contenedor visual para subir plantilla de clientes -->
                        <div class="contenedor-img-texto">
                            <!-- Formulario para subir archivo Excel con clientes -->
                            <form method="post" id="datos_excel" enctype="multipart/form-data">
                                <!-- Botón visual que activa el input de archivo oculto -->
                                <button type="button" class="boton-sin-estilo" onclick="document.getElementById('archivo_excel').click();">
                                     <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img9.png"
                                            alt="Ingreso de clientes Excel" title="Ingreso de Clientes"
                                            class="imagen-icon imagen-planilla">
                                </button>
                                <!-- Input oculto para seleccionar archivo Excel -->
                                <input type="file" name="archivo_excel" id="archivo_excel"
                                    accept=".xls,.xlsx" required style="display: none;"
                                    onchange="cargar_cliente_excel(event);">
                            </form>
                            <!-- Botón de ayuda con explicación sobre la carga de clientes -->
                            <button type="button" class="boton-tutorial" data-tooltip="Para cargar muchos clientes use 'Ingreso de clientes por planilla - Excel' y seleccione el archivo .xls/.xlsx.">?</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- TITULO ELIMINAR CLIENTE O DATOS -->

                <!-- Contenedor para eliminar clientes o sus datos -->
                <div class="box-superior-wrapper">
                    <!-- Contenedor visual para el apartado de eliminación --> 
                    <div class="box box-superior">
                        <!-- Título del apartado de eliminar cliente -->
                        <h2 class="titulo_g" style="text-align: center;">Eliminar Cliente</h2>
                        <!-- Formulario para eliminar cliente, con confirmación previa -->
                        <form class="eliminar-usuario" method="post" id="formularioEliminar">
                            <!-- Campo oculto que indica el tipo de formulario enviado -->    
                            <input type="hidden" name="form" value="eliminar_usuario">
                            <!-- Token CSRF para proteger el formulario contra ataques --> 
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                
                                <!-- Campo para ingresar el RUT del cliente a eliminar -->
                                <label for="rut">Rut:</label>
                                <input type="text" id="rut" name="rut" placeholder="Ingrese RUT sin puntos ni guion" required>
                                
                                <!-- Campo para mostrar el nombre del cliente (se llena automáticamente) -->
                                <label for="nombre">Nombre o Razón Social:</label>
                                <input type="text" id="nombre" name="nombre" readonly placeholder="Nombre o razón social automático." required>
                            <!-- Botones para elegir tipo de eliminación -->
                            <div class="botones-horizontal">
                                <button type="submit" id="btnSolo" name="accion" value="solo">Solo Cliente</button>
                                <button type="submit" id="btnTodo" name="accion" value="todo">Cliente y Datos</button>
                                <!-- Botón de ayuda con explicación de las opciones de eliminación --> 
                                <button type="button" class="boton-ayuda" data-tooltip="Ingrese el RUT del cliente que desea eliminar y seleccione “Solo Cliente” para borrarlo o “Cliente y Datos” para eliminar todo.">?</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php
                // Mostrar mensaje (flash) si existe en sesión y luego eliminarlo
                $flash = $_SESSION['flash_eliminar'] ?? null;
                if ($flash) :
            ?>
                <!-- Si el mensaje es de éxito, lo mostramos en verde -->  
                <?php if (isset($flash['success'])): ?> 
                    <div class="mensaje-exito" style="color:green; padding:8px; margin-top:8px;">
                        <?= htmlspecialchars($flash['success']) ?>
                    </div>
                <?php else: ?>
                    <!-- Si el mensaje es de error, lo mostramos en rojo --> 
                    <div class="mensaje-error" style="color:#b00020; padding:8px; margin-top:8px;">
                        <?= htmlspecialchars($flash['error']) ?>
                    </div>
                <?php endif; ?>
            <?php
                // Eliminamos el mensaje flash para que no se repita
                unset($_SESSION['flash_eliminar']);
            endif;
            ?>        

    <!-- TITULO LISTADO DE CLIENTES -->

        <!-- Contenedor principal que agrupa listado y descarga -->
        <div class="contenedor-listado-descarga">
            
            <!-- Contenedor que muestra todos los clientes registrados -->
            <div class="container-tabla">
                <!-- Título principal "SOLO" de la tabla con los clientes-->
                    <div class="titulo-buscador-flex">
                        <h2>LISTADO DE CLIENTES</h2>
                        <!-- Buscador de clientes -->
                        <div class="buscador-clientes">
                            <!-- Input para buscar clientes -->
                            <input type="text" id="buscador_cliente" onkeyup="buscarCliente()" placeholder="Buscar por nombre o RUT...">
                        </div>
                    </div>
                    <!-- Contenedor donde se carga la tabla o el mensaje si no hay datos -->
                    <div id="listado-clientes">
                        <?php
                        // Traemos nombre y RUT de todos los clientes ordenados alfabéticamente
                        $consulta = "SELECT nombre, rut FROM cliente ORDER BY nombre ASC";
                        $resultado = $mysqli->query($consulta);
                        ?>
                        <!-- Si hay clientes, mostramos la tabla -->
                        <?php if ($resultado && $resultado->num_rows > 0): ?>
                            <!-- Contenedor que permite que la tabla se adapte a pantallas pequeñas -->
                            <div class="tabla-responsiva">
                                <!-- Tabla con estilos personalizados para mostrar los clientes -->
                                <table class="tabla-estilo" id="tabla_clientes">
                                    <thead>
                                        <tr>
                                            <!-- Encabezados de cada columna -->
                                            <th>Nombre o Razón Social</th>
                                            <th>RUT</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Variable para recordar la última letra vista
                                        $letra_actual = ''; 

                                        while ($row = $resultado->fetch_assoc()): 
                                            // Obtenemos la primera letra del nombre en mayúscula
                                            // Usamos mb_substr para que reconozca tildes (Á, É, etc.)
                                            $primera_letra = mb_strtoupper(mb_substr($row['nombre'], 0, 1, "UTF-8"));

                                            // Si la letra cambió respecto a la vuelta anterior, imprimimos la barra separadora
                                            if ($primera_letra != $letra_actual): 
                                                $letra_actual = $primera_letra;
                                        ?>
                                            <!-- Separador de letras -->
                                            <tr class="fila-letra">
                                                <!-- Separador de letras -->
                                                <td colspan="3"><?= htmlspecialchars($letra_actual) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    
                                        <tr>
                                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                                            <td><?= htmlspecialchars($row['rut']) ?></td>
                                            <td>
                                                <!-- Botón para editar cliente -->
                                                <button class="btn-editar" data-rut="<?= htmlspecialchars($row['rut'], ENT_QUOTES) ?>" data-nombre="<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>"onclick="abrir_modal_cliente(this.dataset.rut, this.dataset.nombre)">Editar Cliente</button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <!-- Si no hay clientes, mostramos un mensaje -->
                            <div class="tabla-placeholder" style="padding:14px; text-align:center; border:1px dashed var(--color-borde); border-radius:8px;">
                                No se encontraron clientes registrados.
                            </div>
                        <?php endif; ?>
                    </div>
            </div>

     <!-- TITULO BOX PLANILLA CLIENTE  -->

            <!-- Módulo para descargar la planilla de clientes registrados -->  
            <div class="planilla-clientes-lateral">
                <!-- Contenedor visual para la descarga -->
                <div class="box-planilla-lateral">
                    <!-- Contenedor que agrupa imagen -->
                    <div class="contenedor-img-texto-lateral">
                        <!-- Título del bloque de descarga --> 
                        <h2 class="titulo-descargar">Descargar listado clientes</h2>
                        <!-- Formulario que envía la solicitud de descarga al backend -->
                        <form action="/php/ingreso_ventas/ingreso_clientes/exportar_clientes_registrados.php" method="post" id="descargar_clientes_registrados">
                            <!-- Botón visual con imagen para descargar la planilla -->
                            <button type="submit" class="boton-sin-estilo">
                                <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img5.png"
                                    alt="Descargar clientes registrados"
                                    title="Descargar Clientes Registrados"
                                    class="imagen-clientes">
                            </button>
                            <!-- Botón de ayuda con explicación del proceso de descarga --><!-- comentar -->  
                            <div class="contenedor-boton-ayuda">
                                <button type="button" class="boton-ayuda1" data-tooltip="Descarga la planilla con todos los clientes en formato Excel.">?</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                        
         </div> 
        </div> <div id="seccion-ventas" class="contenido-pestana" style="display: none;">
             <div style="text-align:center; padding: 20px;">
            <div class="box-contenedor" style="padding-top: 20px;">
                 <!-- Recuadro lateral derecho: con el apartado de ventas -->
                <div class="box box-lateral">
                    <!-- Cabecera con dos títulos: descargar y subir plantilla de ventas --> 
                    <div class="cabecera-doble">
                        <h2 class="titulo-opcion mitad">Descargar plantilla ventas de clientes Masiva</h2>
                        <h2 class="titulo-opcion mitad">Subir plantilla ventas de clientes Masiva</h2>
                    </div>
                    <!-- Contenido del bloque de ventas -->
                    <div class="contenido-box">
                        <!-- Contenedor para imagenes dobles--> 
                        <div class="imagenes-dobles">
                            <!-- Bloque visual para descargar plantilla de ventas -->
                            <div class="contenedor-img-texto">
                                <!-- Formulario que envía la solicitud de descarga al backend -->  
                                <form action="/php/ingreso_ventas/ingreso_clientes/exportar_excel.php" method="post" id="descargar_ventas">
                                    <!-- Botón visual con imagen para descargar plantilla --> 
                                    <button type="submit" name="plantilla" value="ventas" class="boton-sin-estilo" data-target="tutorial-ventas">
                                        <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img9.png"
                                            alt="Descargar ventas Excel" title="Descargar Ventas"
                                            class="imagen-icon imagen-exportar">
                                    </button>
                                </form>
                                <!-- Botón de ayuda con explicación sobre la descarga de ventas -->
                                <button type="button" class="boton-tutorial" data-tooltip="Descarga ventas por planilla (usa la funcionalidad de Buscar)">?</button>
                            </div>

                            <!-- Contenedor visual para subir plantilla de ventas -->
                            <div class="contenedor-img-texto">
                                <!-- Formulario para subir archivo Excel con ventas --> 
                                <form method="post" id="ventas_excel" enctype="multipart/form-data">
                                    <!-- Botón visual que activa el input de archivo oculto -->
                                    <button type="button" class="boton-sin-estilo" data-target="tutorial-ventas" onclick="document.getElementById('archivo_excel_ventas').click();">
                                        <img src="/imagenes/ingreso_ventas/ingreso_clientes/ingreso_datos_img10.png"
                                            alt="Ingreso de ventas Excel" title="Ingreso de Ventas"
                                            class="imagen-icon imagen-ventas-planilla">
                                    </button>
                                    <!-- Input oculto para seleccionar archivo Excel -->
                                    <input type="file" name="archivo_excel" id="archivo_excel_ventas"
                                        accept=".xls,.xlsx" required style="display: none;"
                                        onchange="cargar_ventas_excel(event);">
                                </form>
                                <!-- Botón de ayuda con explicación sobre la carga de ventas -->
                                <button type="button" class="boton-tutorial" data-tooltip="Para cargar ventas masivas haga clic en la imagen y seleccione su archivo .xls/.xlsx.">?</button>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </div> 
    

     <!-- TITULO MODAL EDITAR CLIENTE -->

        <!-- Modal oculto que se muestra al hacer clic en el botón "Editar Cliente" -->
        <div id="modalEditarCliente" class="modal" style="display:none;">
            <!-- Contenido del modal -->
            <div class="modal-contenido">
                <!-- Botón para cerrar el modal -->
                <span class="cerrar" onclick="cerrar_modal_cliente()">&times;</span>
                <!-- Título del modal -->
                <h2>EDITAR CLIENTE O RAZÓN SOCIAL</h2>
                <!-- Formulario para editar los datos -->
                <form id="formEditarCliente">
                    <!-- Campo editable para el nombre o razón social -->
                    <div class="campo-horizontal">
                        <label for="nombre">Nombre o Razón Social:</label>
                        <input type="text" name="nombre" id="nombreCliente" required pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" maxlength="100">
                    </div>

                    <!-- Campo bloqueado para el RUT para que no se puede editar -->
                    <div class="campo-horizontal">
                        <label for="rut">RUT:</label>
                        <input type="text" name="rut" id="rutCliente" required disabled>
                    </div>
                    <!-- Botón para guardar los cambios -->
                    <button type="button" onclick="guardar_cambios_cliente()">Guardar Cambios</button>
                </form>
            </div>
        </div>
        
        <!-- Modal para editar producto -->
        <div id="modalEditarProducto" class="modal" style="display:none;">
        <!-- Contenido del modal -->
        <div class="modal-contenido">
            <!-- Botón para cerrar el modal -->
            <span class="cerrar" onclick="cerrar_modal_producto()">&times;</span>
            <!-- Título del modal -->
            <h2>EDITAR PRODUCTO</h2>
            <!-- Formulario para editar los datos del producto -->
            <form id="formEditarProducto" action="/php/ingreso_ventas/ingreso_clientes/procesar_producto.php" method="POST">
                <!-- Acción a realizar -->
                <input type="hidden" name="accion" value="editar_producto">
                <!-- Campo oculto para el ID del producto -->
                <input type="hidden" id="idProductoEditar" name="id">
                <!-- div para el capo sku -->
                <div class="campo-horizontal">
                    <!-- texto que aparece en el input de fondo -->
                    <label>SKU:</label>
                    <!-- campo donde se ingresa el sku -->
                    <input type="text" id="skuProductoEditar" name="sku" required>
                </div>
                <!-- div para el campo nombre -->
                <div class="campo-horizontal">
                    <!-- texto que aparece en el input de fondo -->
                    <label>Nombre:</label>
                    <!-- campo donde se ingresa el nombre -->
                    <input type="text" id="nombreProductoEditar" name="nombre" required>
                </div>
                <!-- Botón para guardar los cambios -->
                <button type="button" onclick="guardar_cambios_producto()">Guardar Cambios</button>
            </form>
        </div>
    </div>
    <!-- Input oculto para mensajes del servidor -->
    <input type="hidden" id="inputMensajeServidor" 
       value="<?php echo isset($_GET['mensaje_producto']) ? htmlspecialchars($_GET['mensaje_producto']) : ''; ?>">

    <!-- TITULO ARCHIVO JS -->

        <!-- carga el archivo de funciones JavaScript necesarias para el filtrado y manejo de tabla -->
        <script src="/js/ingreso_ventas/ingreso_clientes/ingreso_datos.js?v=<?= time() ?>"></script>
        
       
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
     -------------------------------------- FIN ITred Spa ingreso_datos .PHP ------------------------------------
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
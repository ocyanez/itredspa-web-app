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
     ------------------------------------- INICIO ITred Spa obtener_ventas .PHP ---------------------------------
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
<!-- TITULO CONFIGURACION HEADER -->

    <?php
    // Inicia sesión para obtener el rol
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $mysqli->set_charset("utf8");
    // define que la respuesta será en formato HTML
    header('Content-Type: text/html; charset=utf-8');
    ?>

<!-- TITULO VALIDACION INPUTS -->

    <?php
    // obtiene los parámetros de la consulta desde la URL
    $sku         = isset($_GET['sku']) ? $_GET['sku'] : '';
    $serial      = isset($_GET['buscar_serial']) ? $_GET['buscar_serial'] : '';
    $rut         = isset($_GET['rut']) ? $_GET['rut'] : '';
    $lote        = isset($_GET['lote']) ? $_GET['lote'] : '';  // <– solo se usará si el rol NO es usuario_final
    $nombre      = isset($_GET['nombre']) ? $_GET['nombre'] : '';
    $factura     = isset($_GET['numero_fact']) ? $_GET['numero_fact'] : '';
    $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
    $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
    $rol         = $_SESSION['rol'] ?? '';   // para decidir el tipo de filtro

    // Variables para permisos
    $esUsuarioFinal = ($rol === 'usuario_final');
    $puedeEditar = ($rol === 'admin' || $rol === 'superadmin');

    // consulta SQL para obtener los datos de la venta y el cliente
    $sql = "
        SELECT 
            v.id,
            v.rut, 
            c.nombre, 
            v.numero_fact, 
            v.fecha_despacho, 
            v.sku, 
            v.producto, 
            v.cantidad,
            v.lote, 
            v.fecha_fabricacion,
            v.fecha_vencimiento,
            v.n_serie_ini,
            v.n_serie_fin
        FROM 
            venta v
        LEFT JOIN 
            cliente c
        ON 
            v.rut = c.rut
        WHERE 1=1
    ";

    $params = [];
    $types  = "";

    // filtro SKU
    if (!empty($sku)) {
        // Si se busca por número de serie, exigir coincidencia exacta de SKU
        if (!empty($serial)) {
            $sql     .= " AND v.sku = ?";
            $params[] = $sku;
            $types   .= "s";
        } else {
            // Si solo se busca por SKU, exigir coincidencia exacta
            $sql     .= " AND v.sku = ?";
            $params[] = $sku;
            $types   .= "s";
        }
    }

    // SOLO si NO es usuario_final, seguir permitiendo búsqueda por lote
    if ($rol !== 'usuario_final' && !empty($lote)) {
        $sql     .= " AND v.lote = ?";
        $params[] = $lote;
        $types   .= "s";
    }

    // filtro RUT
    if (!empty($rut)) {
        $sql     .= " AND v.rut = ?";
        $params[] = $rut;
        $types   .= "s";
    }

    // filtro nombre: usar coincidencia por similitud (LIKE) case-insensitive
    if (!empty($nombre)) {
        // Si el frontend ya envía comodines (%) los respetamos;
        // si no, envolvemos para buscar subcadenas.
        $nombre_param = $nombre;
        if (strpos($nombre_param, '%') === false) {
            $nombre_param = '%' . $nombre_param . '%';
        }
        $sql     .= " AND LOWER(c.nombre) LIKE LOWER(?)";
        $params[] = $nombre_param;
        $types   .= "s";
    }

    // filtro factura
    if (!empty($factura)) {
        $sql     .= " AND v.numero_fact = ?";
        $params[] = $factura;
        $types   .= "s";
    }

    // filtro fecha
    if (!empty($fecha_desde)) {
        $sql     .= " AND v.fecha_despacho >= ?";
        $params[] = $fecha_desde." 00:00:00";
        $types   .= "s";
    }
    if (!empty($fecha_hasta)) {
        $sql     .= " AND v.fecha_despacho <= ?";
        $params[] = $fecha_hasta." 23:59:59";
        $types   .= "s";
    }

    // Filtrar por número de serie dentro del rango, si se recibe cualquier serial
    // Aplicar criterio serial SOLO si el rol es usuario_final
    if ($rol === 'usuario_final' && !empty($serial)) {
        // Aceptar rangos invertidos (n_serie_ini y n_serie_fin pueden estar al reves)
        // Usamos LEAST/GREATEST para garantizar que el BETWEEN funcione aunque estén invertidos
        $sql     .= " AND ? BETWEEN LEAST(v.n_serie_ini, v.n_serie_fin) AND GREATEST(v.n_serie_ini, v.n_serie_fin)";
        $params[] = $serial;
        $types   .= "s";
    }


    // orden final
    $sql .= " ORDER BY v.id DESC";
    ?>

<!-- TITULO PREPARACION SQL -->

    <?php
    // prepara la consulta SQL
    $stmt = $mysqli->prepare($sql);
    // verifica si hubo un error al preparar la consulta
    if ($stmt === false) {
        // devuelve un mensaje de error en formato HTML
        $colspan = $esUsuarioFinal ? '8' : ($puedeEditar ? '13' : '12');
        echo '<tr><td colspan="' . $colspan . '">Error al preparar la consulta: ' . htmlspecialchars($mysqli->error) . '</td></tr>';
        // termina la ejecución del script
        exit();
    }

    // verifica si hay parámetros para enlazar
    if (!empty($params)) {
        // enlaza los parámetros a la consulta
        $stmt->bind_param($types, ...$params); 
    }
    ?>

<!-- TITULO EJECUCION SQL -->

    <?php
    // ejecuta la consulta
    $stmt->execute();
    // obtiene el resultado de la consulta
    $result = $stmt->get_result();
    ?>

<!-- TITULO GENERACION HTML -->

    <?php    
    // verifica si se obtuvieron resultados
    if ($result->num_rows > 0) {
        // obtiene cada fila del resultado y genera HTML
        while ($venta = $result->fetch_assoc()) {
            echo '<tr>';
            
            if ($esUsuarioFinal) {
                // Vista para usuario_final (8 columnas)
                $serialPadded = str_pad($serial, 7, '0', STR_PAD_LEFT);
                
                echo '<td>' . htmlspecialchars($venta['sku']) . '</td>';
                echo '<td>' . $serialPadded . '</td>';
                echo '<td>' . htmlspecialchars($venta['fecha_despacho']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['producto']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['cantidad']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['lote']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['fecha_fabricacion']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['fecha_vencimiento'] ?? '') . '</td>';
            } else {
                // Vista para otros roles (12-13 columnas)
                $iniPad = str_pad($venta['n_serie_ini'] ?? '', 7, '0', STR_PAD_LEFT);
                $finPad = str_pad($venta['n_serie_fin'] ?? '', 7, '0', STR_PAD_LEFT);
                
                echo '<td>' . htmlspecialchars($venta['sku']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['rut']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['nombre'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($venta['numero_fact']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['fecha_despacho']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['producto']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['cantidad']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['lote']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['fecha_fabricacion']) . '</td>';
                echo '<td>' . htmlspecialchars($venta['fecha_vencimiento']) . '</td>';
                echo '<td>' . $iniPad . '</td>';
                echo '<td>' . $finPad . '</td>';
                
                // Columna de acciones solo para admin/superadmin
                if ($puedeEditar) {
                    // Crear string de datos para el modal (sin JSON)
                    $dataVenta = 'id:' . $venta['id'] .
                        ',sku:' . $venta['sku'] .
                        ',rut:' . $venta['rut'] .
                        ',nombre:' . ($venta['nombre'] ?? '') .
                        ',numero_fact:' . $venta['numero_fact'] .
                        ',fecha_despacho:' . $venta['fecha_despacho'] .
                        ',producto:' . $venta['producto'] .
                        ',cantidad:' . $venta['cantidad'] .
                        ',lote:' . $venta['lote'] .
                        ',fecha_fabricacion:' . $venta['fecha_fabricacion'] .
                        ',fecha_vencimiento:' . $venta['fecha_vencimiento'] .
                        ',n_serie_ini:' . $venta['n_serie_ini'] .
                        ',n_serie_fin:' . $venta['n_serie_fin'];
                    
                    echo '<td>';
                    echo '<button type="button" class="btn-modificar" data-venta="' . htmlspecialchars($dataVenta) . '" onclick="abrir_modal_desde_boton(this)">Modificar</button>';
                    echo '<button type="button" class="btn-modificar-eliminacion" onclick="abrir_modal_eliminacion(\'' . $venta['id'] . '\')">Eliminar</button>';
                    echo '</td>';
                }
            }
            
            echo '</tr>';
        }
    } else {
        // Mensaje cuando no hay resultados
        $colspan = $esUsuarioFinal ? '8' : ($puedeEditar ? '13' : '12');
        echo '<tr><td colspan="' . $colspan . '">Información no encontrada, revisa los datos.</td></tr>';
    }
    ?>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->
     
    <!-- <?php 
        // $mysqli->close();
        ?> -->

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa obtener_ventas .PHP -----------------------------------
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
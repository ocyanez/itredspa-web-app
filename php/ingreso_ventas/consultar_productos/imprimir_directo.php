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
     ------------------------------------- INICIO ITred Spa imprimir_directo .PHP -------------------------------
     ------------------------------------------------------------------------------------------------------------ -->


<?php

// TITULO CONFIGURACIÓN DE ERRORES

    // Mostrar errores durante desarrollo
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Log de errores
    error_log("imprimir_directo.php iniciando");


// TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN

    // Validación de sesión y permisos como en buscar.php
    session_start();
    if (!isset($_SESSION['correo'])) {
        header("Location: /ingreso_ventas.php");
        exit();
    }

    error_log("Intentando conexión a BD...");
            // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
            $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    if ($mysqli->connect_error) {
        error_log("Error de conexión: " . $mysqli->connect_error);
        die("Error de conexión a la base de datos: " . $mysqli->connect_error);
    }
    error_log("Conexión exitosa, configurando charset");
    $mysqli->set_charset("utf8");


// TITULO FUNCIÓN AUXILIAR PARA SEGURIDAD

    // Helper para escapar valores y evitar pasar null a htmlspecialchars (evita warnings deprecados)
    if (!function_exists('h')) {
        function h($v) {
            // Forzamos a string y usamos ENT_QUOTES + UTF-8 explícito
            return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
        }
    }

// TITULO DEBUG DE DATOS RECIBIDOS

    error_log("POST recibido: " . print_r($_POST, true));
    error_log("GET recibido: " . print_r($_GET, true));
    error_log("REQUEST recibido: " . print_r($_REQUEST, true));

// Validar que haya al menos un filtro
$tieneAlgunFiltro = false;
$campos = ['sku', 'lote', 'rut', 'nombre', 'numero_fact', 'fecha_desde', 'fecha_hasta', 'buscar_serial'];
foreach ($campos as $campo) {
    if (!empty($_POST[$campo])) {
        $tieneAlgunFiltro = true;
        error_log("Filtro encontrado en: " . $campo . " = " . $_POST[$campo]);
        break;
    }
}

if (!$tieneAlgunFiltro) {
    error_log("No se encontraron filtros - debug completo:");
    error_log("sku: '" . ($_POST['sku'] ?? 'VACIO') . "'");
    error_log("lote: '" . ($_POST['lote'] ?? 'VACIO') . "'");
    error_log("rut: '" . ($_POST['rut'] ?? 'VACIO') . "'");
    error_log("nombre: '" . ($_POST['nombre'] ?? 'VACIO') . "'");
    error_log("numero_fact: '" . ($_POST['numero_fact'] ?? 'VACIO') . "'");
    error_log("fecha_desde: '" . ($_POST['fecha_desde'] ?? 'VACIO') . "'");
    error_log("fecha_hasta: '" . ($_POST['fecha_hasta'] ?? 'VACIO') . "'");
    error_log("buscar_serial: '" . ($_POST['buscar_serial'] ?? 'VACIO') . "'");
    die("Debe ingresar al menos un filtro para imprimir.");
}

// Construir query similar a obtener_ventas.php
$where = [];
$params = [];
$types = "";

if (!empty($_POST['sku'])) {
    $where[] = "v.sku = ?";
    $params[] = $_POST['sku'];
    $types .= "s";
}

if (!empty($_POST['lote'])) {
    $where[] = "v.lote = ?";
    $params[] = $_POST['lote'];
    $types .= "s";
}

if (!empty($_POST['rut'])) {
    $where[] = "v.rut = ?";
    $params[] = $_POST['rut'];
    $types .= "s";
}

if (!empty($_POST['nombre'])) {
    $where[] = "c.nombre LIKE ?";
    $params[] = "%{$_POST['nombre']}%";
    $types .= "s";
}

if (!empty($_POST['numero_fact'])) {
    $where[] = "v.numero_fact = ?";
    $params[] = $_POST['numero_fact'];
    $types .= "s";
}

if (!empty($_POST['fecha_desde'])) {
    $where[] = "v.fecha_despacho >= ?";
    $params[] = $_POST['fecha_desde'] . " 00:00:00";
    $types .= "s";
}

if (!empty($_POST['fecha_hasta'])) {
    $where[] = "v.fecha_despacho <= ?";
    $params[] = $_POST['fecha_hasta'] . " 23:59:59";
    $types .= "s";
}

if (!empty($_POST['buscar_serial'])) {
    $serial = $_POST['buscar_serial'];
    $where[] = "(v.n_serie_ini <= ? AND v.n_serie_fin >= ?)";
    $params[] = $serial;
    $params[] = $serial;
    $types .= "ss";
}

error_log("Construyendo query...");
$sql = "SELECT 
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
    cliente c ON v.rut = c.rut
WHERE 1=1";

if (!empty($where)) {
    $sql .= " AND " . implode(" AND ", $where);
}
$sql .= " ORDER BY v.fecha_despacho DESC LIMIT 100"; // Limitamos por seguridad
error_log("Query final: " . $sql);
error_log("Tipos de parámetros: " . $types);
error_log("Parámetros: " . print_r($params, true));

$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
error_log("Ejecutando query...");
$stmt->execute();
$result = $stmt->get_result();

$num_resultados = $result->num_rows;
error_log("Query ejecutada - Número de resultados: " . $num_resultados);

if ($num_resultados == 0) {
    error_log("¡PROBLEMA! No hay resultados para la query");
    error_log("SQL final: " . $sql);
    error_log("Params: " . print_r($params, true));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Productos Despachados</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: white;
            color: #333;
        }
        
        h1 { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td { 
            border: 1px solid #ddd;
            padding: 8px; 
            text-align: center;
            font-size: 12px;
        }
        
        th { 
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .header-info {
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="header-info">
        <strong>Segma</strong><br>
        Fecha de impresión: <?php echo date('d/m/Y H:i:s'); ?> | 
        Total de registros: <?php echo $num_resultados; ?>
    </div>

    <h1>Listado de Productos Despachados</h1>
    
    <?php if ($num_resultados > 0): ?>
    <table>
        <thead>
            <tr>
                <?php if ($_SESSION['rol'] === 'usuario_final'): ?>
                <th>SKU</th>
                <th>NÚMERO DE SERIE</th>
                <th>FECHA DE DESPACHO</th>
                <th>PRODUCTO</th>
                <th>CANTIDAD</th>
                <th>LOTE</th>
                <th>FECHA DE FABRICACIÓN</th>
                <th>FECHA DE VENCIMIENTO</th>
                <?php else: ?>
                <th>SKU</th>
                <th>RUT</th>
                <th>NOMBRE CLIENTE</th>
                <th>NÚMERO DE FACTURA</th>
                <th>FECHA DE DESPACHO</th>
                <th>PRODUCTO</th>
                <th>CANTIDAD</th>
                <th>LOTE</th>
                <th>FECHA DE FABRICACIÓN</th>
                <th>FECHA DE VENCIMIENTO</th>
                <th>SERIE DE INICIO</th>
                <th>SERIE DE TÉRMINO</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): 
            // Pad serial numbers consistently
            $iniDigits = preg_replace('/\D/', '', $row['n_serie_ini'] ?? '');
            $finDigits = preg_replace('/\D/', '', $row['n_serie_fin'] ?? '');
            $width = max(7, strlen($iniDigits), strlen($finDigits));
            
            $iniPadded = $iniDigits ? str_pad($iniDigits, $width, '0', STR_PAD_LEFT) : $row['n_serie_ini'];
            $finPadded = $finDigits ? str_pad($finDigits, $width, '0', STR_PAD_LEFT) : $row['n_serie_fin'];
            
            // Calculate fecha_vencimiento if missing
            if (empty($row['fecha_vencimiento']) && !empty($row['fecha_fabricacion'])) {
                $d = new DateTime($row['fecha_fabricacion']);
                $d->modify('+5 years');
                $row['fecha_vencimiento'] = $d->format('Y-m-d');
            }
        ?>
            <tr>
                <?php if ($_SESSION['rol'] === 'usuario_final'): ?>
                <td><?php echo h($row['sku']); ?></td>
                <td><?php echo h($iniPadded); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['fecha_despacho'])); ?></td>
                <td><?php echo h($row['producto']); ?></td>
                <td><?php echo h($row['cantidad']); ?></td>
                <td><?php echo h($row['lote']); ?></td>
                <td><?php echo $row['fecha_fabricacion'] ? date('d/m/Y', strtotime($row['fecha_fabricacion'])) : '-'; ?></td>
                <td><?php echo $row['fecha_vencimiento'] ? date('d/m/Y', strtotime($row['fecha_vencimiento'])) : '-'; ?></td>
                <?php else: ?>
                <td><?php echo h($row['sku']); ?></td>
                <td><?php echo h($row['rut']); ?></td>
                <td><?php echo h($row['nombre'] ?? 'N/A'); ?></td>
                <td><?php echo h($row['numero_fact']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['fecha_despacho'])); ?></td>
                <td><?php echo h($row['producto']); ?></td>
                <td><?php echo h($row['cantidad']); ?></td>
                <td><?php echo h($row['lote']); ?></td>
                <td><?php echo $row['fecha_fabricacion'] ? date('d/m/Y', strtotime($row['fecha_fabricacion'])) : '-'; ?></td>
                <td><?php echo $row['fecha_vencimiento'] ? date('d/m/Y', strtotime($row['fecha_vencimiento'])) : '-'; ?></td>
                <td><?php echo h($iniPadded); ?></td>
                <td><?php echo h($finPadded); ?></td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">
            <h3>No se encontraron registros</h3>
            <p>No hay datos que coincidan con los criterios de búsqueda especificados.</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-print after a short delay to ensure rendering
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 1200); // Aumentar el delay para permitir que los estilos carguen
        });
    </script>
</body>
</html>
<?php
$mysqli->close();
?>

<!-- ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa imprimir_directo .PHP ----------------------------------
    ------------------------------------------------------------------------------------------------------------- -->
	
<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->
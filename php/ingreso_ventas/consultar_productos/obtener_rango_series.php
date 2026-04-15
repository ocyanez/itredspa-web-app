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
     ------------------------------------- INICIO ITred Spa obtener_rango_series .PHP ---------------------------
     ------------------------------------------------------------------------------------------------------------ -->


<?php
// Endpoint que devuelve el rango (min/max) de series para un SKU
// Formato de respuesta: "success|min|max" o "error|mensaje"
header('Content-Type: text/plain; charset=utf-8');

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");

if ($mysqli->connect_errno) {
    echo 'error|Error conexión BD';
    exit;
}
$mysqli->set_charset('utf8');

$sku = isset($_GET['sku']) ? trim($_GET['sku']) : '';
if ($sku === '') {
    echo 'error|SKU vacío';
    exit;
}

// Obtenemos el menor y mayor valor entre n_serie_ini y n_serie_fin para ese SKU
$sql = "SELECT LEAST(MIN(n_serie_ini), MIN(n_serie_fin)) AS serie_min, GREATEST(MAX(n_serie_ini), MAX(n_serie_fin)) AS serie_max FROM venta WHERE sku = ?";
$stmt = $mysqli->prepare($sql);

if ($stmt === false) {
    echo 'error|Error al preparar consulta';
    exit;
}

$stmt->bind_param('s', $sku);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

// Normalizar valores
$min = $row['serie_min'] ?? null;
$max = $row['serie_max'] ?? null;

if ($min === null && $max === null) {
    echo 'error|No hay series para este SKU';
    exit;
}

// Convertir a enteros
$min = ($min === null || $min === '') ? 0 : (int)$min;
$max = ($max === null || $max === '') ? 0 : (int)$max;

// Formato: success|min|max
echo 'success|' . $min . '|' . $max;

?>

<!-- ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa obtener_rango_series .PHP ------------------------------
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
<?php
/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/

/* ------------------------------------------------------------------------------------------------------------
   ------------------------------------- INICIO ITred Spa buscar_venta .PHP ---------------------------------
   ------------------------------------------------------------------------------------------------------------ */

// Limpiar cualquier salida anterior para asegurar que el texto llegue limpio
if (ob_get_length()) ob_clean();


//texto plano
header('Content-Type: text/plain; charset=utf-8');
header('Access-Control-Allow-Origin: *'); 

/* ------------------------
   -- INICIO CONEXION BD --
   ------------------------ */

$mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");

// Verificación de conexión
if ($mysqli->connect_errno) {
    echo "Error:Fallo conexion BD " . $mysqli->connect_error;
    exit;
}

$mysqli->set_charset("utf8");

/* ---------------------
   -- FIN CONEXION BD --
   --------------------- */

/* TITULO CONSULTA SQL */

// verifica si se ha enviado un valor por GET
$valor = $_GET['valor'] ?? '';

if ($valor === '') {
    echo "Error:Valor vacio";
    exit;
}

// prepara la consulta SQL para buscar en la tabla venta y cliente
$stmt = $mysqli->prepare("
    SELECT 
        venta.id,
        venta.rut,
        IFNULL(cliente.nombre, '') as nombre,
        venta.numero_fact,
        venta.fecha_despacho,
        venta.sku,
        venta.producto,
        venta.cantidad, 
        venta.lote,
        venta.fecha_fabricacion,
        venta.fecha_vencimiento,
        venta.n_serie_ini,
        venta.n_serie_fin
    FROM venta
    LEFT JOIN cliente ON venta.rut = cliente.rut
    WHERE venta.sku = ? OR venta.lote = ?
");

// verifica si la consulta se preparó correctamente
if (!$stmt) {
    echo "Error:Fallo en consulta SQL " . $mysqli->error;
    exit;
}

/* TITULO EJECUCION SQL */

// vincula los parámetros a la consulta SQL
// CAMBIO IMPORTANTE: Usamos "ss" (String, String) en lugar de "ii".
// Esto permite buscar SKUs o Lotes alfanuméricos correctamente.
$stmt->bind_param("ss", $valor, $valor);

// ejecuta la consulta SQL
$stmt->execute();

// obtiene el resultado de la consulta
$res = $stmt->get_result();

/* TITULO OBTENCION DE DATOS */

// Verificamos si hay resultados
if ($row = $res->fetch_assoc()) {
    // Construimos la respuesta manualmente en formato "clave:valor;"
    $salida = "";
    foreach ($row as $key => $val) {
        // Limpiamos el valor de caracteres que podrían romper el formato (como ; o enter)
        $valLimpio = str_replace([';', "\n", "\r"], ' ', $val ?? '');
        $salida .= "$key:$valLimpio;";
    }
    // Imprimimos la cadena final
    echo $salida;
} else {
    // Si no encuentra nada, no imprime nada (o puedes poner Error:No encontrado)
    // Tu JS entiende cadena vacía como "no encontrado"
    echo ""; 
}

/* -------------------------------
   -- INICIO CIERRE CONEXION BD --
   ------------------------------- */

$stmt->close();
$mysqli->close();

/* ----------------------------
   -- FIN CIERRE CONEXION BD --
   ---------------------------- */

/* ------------------------------------------------------------------------------------------------------------
   -------------------------------------- FIN ITred Spa buscar_venta .PHP -----------------------------------
   ------------------------------------------------------------------------------------------------------------ */

/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/
?>
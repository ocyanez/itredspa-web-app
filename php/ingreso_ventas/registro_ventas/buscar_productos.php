<?php

// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

// ------------------------------------------------------------------------------------------------------------
// ------------------------------------- INICIO ITred buscar_productos .PHP -----------------------------------
// ------------------------------------------------------------------------------------------------------------ 

// ------------------------
// -- INICIO CONEXION BD --
// ------------------------

$mysqli = new mysqli(
    "localhost",
    "trazabil_root",
    "Segma1@@",
    "trazabil_ingreso_ventas_bd_itred"
);

$mysqli->set_charset("utf8mb4");

// ---------------------
// -- FIN CONEXION BD --
// ---------------------

// TITULO BUSQUEDA PRODUCTOS 

$term = $_GET['q'] ?? '';

if (mb_strlen($term) < 2) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT producto
    FROM producto
    WHERE producto LIKE CONCAT('%', ?, '%')
    LIMIT 20
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $term);
$stmt->execute();

$res = $stmt->get_result();
$productos = [];

while ($row = $res->fetch_assoc()) {
    $productos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($productos);

// -------------------------------------------------------------------------------------------------------------
// -------------------------------------- FIN ITred Spa buscar_productos .PHP ----------------------------------
// -------------------------------------------------------------------------------------------------------------




    


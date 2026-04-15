<?php
// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ

// -----------------------------------------------------------------------------------------------------------------
// ------------------------------------- INICIO ITred Spa obtener_metas.php -----------------------------------
// -----------------------------------------------------------------------------------------------------------------
    
header('Content-Type: text/plain; charset=utf-8');

// conexion directa
$mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");

if ($mysqli->connect_errno) {
    echo ""; // si falla, devolvemos vacio
    exit;
}

// TITULO COONSULTA SQL

    $mysqli->set_charset("utf8");

    $rut = isset($_GET['rut']) ? $_GET['rut'] : '';
    $factura = isset($_GET['factura']) ? $_GET['factura'] : '';

    // Limpieza fuerte igual que en validar_factura para asegurar coincidencia
    $rut = $mysqli->real_escape_string(str_replace('.', '', $rut));
    $factura = $mysqli->real_escape_string($factura);

    // CORRECCIÓN: Usamos SUM() y GROUP BY para consolidar productos por SKU
    // Esto evita que si hay duplicados en la BD se muestren como lineas separadas o errores
    $sql = "SELECT codigo_producto, MAX(descripcion_producto) as nombre, SUM(cantidad_producto) as total_cantidad 
            FROM factura 
            WHERE rut_empresa = '$rut' AND n_factura = '$factura'
            GROUP BY codigo_producto";

    $resultado = $mysqli->query($sql);
    $lista_texto = [];

    if ($resultado) {
        while($fila = $resultado->fetch_assoc()) {
            // construimos el string: "sku:valor;meta:valor;nombre:valor"
            // quitamos puntos y comas del nombre para no romper el formato
            $nom = str_replace(array(';', '|', ':'), '', $fila['nombre']);
            
            // Usamos TRIM para asegurar que el SKU viaje limpio
            $sku_limpio = trim($fila['codigo_producto']);
            
            $item = "sku:" . $sku_limpio . 
                    ";meta:" . $fila['total_cantidad'] . 
                    ";nombre:" . $nom;
            
            $lista_texto[] = $item;
        }
    }

    // unimos todo con doble barra ||
    echo implode("||", $lista_texto);

    $mysqli->close();

// ------------------------------------------------------------------------------------------------------------
//      -------------------------------------- FIN ITred Spa obtener_metas .PHP ------------------------------
//      ------------------------------------------------------------------------------------------------------------ 
?>
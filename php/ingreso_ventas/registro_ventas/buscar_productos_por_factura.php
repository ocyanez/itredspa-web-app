<?php
//
// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
// 


// -----------------------------------------------------------------------------------------------------------------
// ------------------------------------- INICIO ITred Spa buscar_productos_por_factura .PHP ------------------------
// -----------------------------------------------------------------------------------------------------------------

header('Content-Type: text/plain; charset=utf-8');

// 1. Conexión a la Base de Datos
$mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
$mysqli->set_charset("utf8");

if ($mysqli->connect_error) {
    die("Error conexión: " . $mysqli->connect_error);
}

// recibe los datos
$factura = isset($_GET['factura']) ? $mysqli->real_escape_string($_GET['factura']) : '';
$rut = isset($_GET['rut']) ? $mysqli->real_escape_string($_GET['rut']) : '';

// Si no hay factura, no devolvemos nada
if ($factura == "") {
    echo ""; 
    exit;
}

// Quitamos los puntos para asegurarnos de comparar bien con la base de datos

$rut_limpio = str_replace('.', '', $rut);


// TITULO CONSULTA  SQL


    // Ahora filtramos por factura Y por el RUT del cliente  así evitamos traer productos de una factura que pertenezca a otro cliente.
    $sql = "SELECT * FROM venta WHERE numero_fact = '$factura' AND rut = '$rut_limpio'";



// TITULO FILTRAR 

    $resultado = $mysqli->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $lista_productos = [];

        while($fila = $resultado->fetch_assoc()) {
            // Construimos el string con formato: clave:valor;
            // Mapeamos exactamente las columnas de la base de datos a las claves que espera JS
            $datos = [];
            $datos[] = "sku:" . ($fila['sku'] ?? '');
            $datos[] = "producto:" . ($fila['producto'] ?? '');
            $datos[] = "cantidad:" . ($fila['cantidad'] ?? '');
            $datos[] = "lote:" . ($fila['lote'] ?? '');
            $datos[] = "fecha_fabricacion:" . ($fila['fecha_fabricacion'] ?? '');
            $datos[] = "fecha_vencimiento:" . ($fila['fecha_vencimiento'] ?? '');
            
            // Tus columnas se llaman n_serie_ini y n_serie_fin
            $datos[] = "n_serie_ini:" . ($fila['n_serie_ini'] ?? '0');
            $datos[] = "n_serie_fin:" . ($fila['n_serie_fin'] ?? '0');
            
            // Unimos los datos de UN producto con punto y coma
            $lista_productos[] = implode(";", $datos);
        }

      
        // devolvemos todos los productos separados por doble barra ||
        echo implode("||", $lista_productos);
        exit; //  Corta la ejecución aquí mismo para ignorar el footer

    } else {
        // Si no encuentra nada (o la factura es de otro cliente) devolvemos vacío
        echo ""; 
        exit; 
    }

    $mysqli->close();


    // -------------------------------------------------------------------------------------------------------------
    // -------------------------------------- FIN ITred Spa buscar_productos_por_factura .PHP ----------------------
    // ------------------------------------------------------------------------------------------------------------- 

// 
// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
// 
?>
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
     ------------------------------------- INICIO ITred Spa cargar_productos .PHP -------------------------------
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar productos</title>
</head>
<body>
<?php


// TITULO ERRORES PARA DEBUG

    // Mostrar errores para depuración
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: text/plain; charset=utf-8');


// TITULO IMPORTACIÓN DE LIBRERÍA EXCEL 
   
    // Cargar la librería PhpSpreadsheet
    require __DIR__ . '/../../../programas/vendor/autoload.php';
    // Uso de la clase IOFactory
    use PhpOffice\PhpSpreadsheet\IOFactory;

// TITULO FUNCIONES AUXILIARES

    // Función para salida segura de texto
    function salida_texto_segura($estado, $param1 = '', $param2 = '', $param3 = '') {
        // Salida en formato seguro
        echo $estado . '|' . $param1 . '|' . $param2 . '|' . $param3;
        exit;
    }

// TITULO PROCESO DE CARGA

    // Verificar si se ha subido un archivo
   if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] == 0) {
        // Ruta temporal del archivo subido
        $archivo_temp = $_FILES['archivo_excel']['tmp_name'];
        // Cargar el archivo Excel
        try {
            $spreadsheet = IOFactory::load($archivo_temp);
        // Capturar errores de lectura
        } catch (Throwable $e) {
            // Salida de error
            salida_texto_segura('ERROR', 'No se pudo leer el archivo Excel: ' . $e->getMessage());
        }
        // Obtener la hoja activa y convertir a array
        $sheet = $spreadsheet->getActiveSheet();
        // Leer todas las filas como un array asociativo
        $rows  = $sheet->toArray(null, true, true, true);
        // Inicializar contadores
        $filasInsertadas = 0;
        $filasErrores = 0;

        // Preparar la consulta SQL 
        $sql = "INSERT INTO producto (sku, producto) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE producto = VALUES(producto)";
        // Preparar la declaración
        $stmt = $mysqli->prepare($sql);
        // Verificar preparación
        if (!$stmt) {
            salida_texto_segura('ERROR', 'Error preparando consulta: ' . $mysqli->error);
        }

        // Recorrer filas (desde la 2 para saltar cabecera)
        for ($i = 2; $i <= count($rows); $i++) {
            $fila = $rows[$i];
            // Obtener datos de columnas A (SKU) y B (Nombre)
            $sku    = trim((string)($fila['A'] ?? ''));
            $nombre = trim((string)($fila['B'] ?? ''));
            
            // Validación mínima
            if ($sku === '' || $nombre === '') {
                continue; // Saltamos filas vacías
            }

            // Insertamos "ss" (string, string) - Solo SKU y Nombre
            $stmt->bind_param("ss", $sku, $nombre);
            // Ejecutar la declaración
            if ($stmt->execute()) {
                $filasInsertadas++;
            } else {
                $filasErrores++;
            }
        }
        // Cerrar la declaración
        $stmt->close();
        
        // Respuesta OK final
        salida_texto_segura('OK', $filasInsertadas, 0, $filasErrores);
    // Si no se recibió archivo
    } else {
        // Salida de error
        salida_texto_segura('ERROR', 'No se recibió el archivo o hubo error de subida.');
    }
?>

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
     -------------------------------------- FIN ITred Spa cargar_productos .PHP ---------------------------------
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

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
   ------------------------------------- INICIO ITred Spa buscar_cliente.PHP --------------------------------
   ------------------------------------------------------------------------------------------------------------ */

// Limpiar cualquier salida previa
if (ob_get_length()) ob_clean();

// Encabezado Texto plano
header('Content-Type: text/plain; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // CORS por si acaso

// Detectar qué formato usar (nuevo o antiguo)
// Si viene el parámetro 'formato=nuevo', usa ESTADO|MENSAJE, sino usa el antiguo
$formatoNuevo = isset($_GET['formato']) && $_GET['formato'] === 'nuevo';

// Función para arreglar acentos extraños
function repararCaracteres($texto) {
    $reparaciones = [
        'Ã±' => 'ñ', 'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã³' => 'ó', 'Ãº' => 'ú',
        'Ã' => 'Á', 'Ã‰' => 'É', 'Ã"' => 'Ó', 'Ãš' => 'Ú', 'Ã' => 'Ñ', 'Â' => ''
    ];
    return strtr($texto, $reparaciones);
}

// Configuración de errores: No mostrarlos en pantalla, solo log
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Validación de entrada
    if (!isset($_GET['rut'])) {
        echo $formatoNuevo ? "ERROR|Falta el RUT" : "Error: Falta el RUT";
        exit;
    }

    // Normalizar RUT (Quitar puntos y guiones para la búsqueda)
    $rut = trim($_GET['rut']);
    $rut_sin = str_replace(['.', '-'], '', strtoupper($rut));

    // Conexión a la base de datos
    $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
    
    if ($mysqli->connect_errno) {
        throw new Exception("Error de conexión BD");
    }
    $mysqli->set_charset('utf8mb4');

    // Consulta SQL
    $sql = "
        SELECT nombre 
        FROM cliente 
        WHERE TRIM(
            UPPER(
                REPLACE(
                    REPLACE(rut, '.', ''), '-', ''
                )
            )
        ) = ?
        LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error SQL: " . $mysqli->error);
    }

    $stmt->bind_param('s', $rut_sin);
    $stmt->execute();
    
    // Obtener resultado
    $resultado = $stmt->get_result();
    
    if ($row = $resultado->fetch_assoc()) {
        // RESPUESTA EXITOSA
        $nombre = repararCaracteres($row['nombre']);
        
        // Responde según el formato solicitado
        echo $formatoNuevo ? "DATOS|" . $nombre : "nombre:" . $nombre . ";";
        
    } else {
        // RESPUESTA NO ENCONTRADO
        echo $formatoNuevo ? "ERROR|Cliente no encontrado" : "Error:Cliente no encontrado";
    }

    $stmt->close();
    $mysqli->close();

} catch (Exception $e) {
    // Registrar el error real en el log del servidor
    error_log("Error en buscar_cliente.php: " . $e->getMessage());
    // Mostrar mensaje genérico al usuario
    echo $formatoNuevo ? "ERROR|Error interno del servidor" : "Error: Error interno del servidor";
}

/* ------------------------------------------------------------------------------------------------------------
   --------------------------------------- FIN ITred Spa buscar_cliente.PHP ----------------------------------
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
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

/*  ------------------------------------------------------------------------------------------------------------
    ---------------------------------- INICIO ITred Spa exportar_excel .PHP ------------------------------------
    ------------------------------------------------------------------------------------------------------------ */

// Desactivar la salida de errores en HTML para no romper el XLSX
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Cerrar y limpiar cualquier buffer previo que pueda existir
while (ob_get_level() > 0) { ob_end_clean(); }

// Evitar compresión de salida que a veces altera los bytes
if (ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off'); }

// Incluimos PhpSpreadsheet
require __DIR__ . '/../../../programas/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/* TITULO PLANTILLAS DE EXCEL */

    // Ruta base donde están las plantillas CSV
    $baseDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/documentacion/ejemplo_excel/';

    // Bandera enviada por el botón del formulario.
    $tipo = isset($_POST['plantilla']) ? strtolower(trim($_POST['plantilla'])) : 'clientes';

    // Devuelve el archivo más reciente que calce con un patrón o null si no hay.
    function archivoMasReciente(string $pattern): ?string {
        $files = glob($pattern, GLOB_NOSORT);
        if (!$files) return null;
        usort($files, fn($a,$b) => filemtime($b) <=> filemtime($a));
        return $files[0];
    }

    // Elegimos archivo y nombre de descarga según el tipo solicitado.
 // Elegimos archivo y nombre de descarga según el tipo solicitado.
    switch ($tipo) {
        case 'ventas':
            $file = archivoMasReciente($baseDir . 'ventas_*.csv') ?? ($baseDir . 'ventas.csv');
            $nombreDescarga = 'plantilla_ventas.xlsx';
            break;
        
        case 'productos':
            $file = archivoMasReciente($baseDir . 'productos_*.csv') ?? ($baseDir . 'productos.csv');
            $nombreDescarga = 'plantilla_productos.xlsx';
            break;

        case 'facturas':
            $file = archivoMasReciente($baseDir . 'facturas_*.csv') ?? ($baseDir . 'facturas.csv');
            $nombreDescarga = 'plantilla_facturas.xlsx';
            break;

        case 'clientes':
        default:
            $file = archivoMasReciente($baseDir . 'clientes_*.csv') ?? ($baseDir . 'clientes.csv');
            $nombreDescarga = 'plantilla_clientes.xlsx';
            break;
    }
    // Verificación de existencia
    if (!is_file($file)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "No se encontró la plantilla solicitada.";
        exit;
    }

    /* TITULO CONVERTIR CSV A XLSX */

    // Leemos el CSV con PhpSpreadsheet (NO crear Spreadsheet previo)
    $reader = IOFactory::createReader('Csv');
    $reader->setDelimiter(';');
    $reader->setEnclosure('"');
    $reader->setInputEncoding('UTF-8');
    $reader->setSheetIndex(0);
    $reader->setReadDataOnly(true);

    $spreadsheet = $reader->load($file);

    // Fijar ancho de columnas a 40
    $sheet = $spreadsheet->getActiveSheet();
    foreach ($sheet->getColumnIterator() as $column) {
        $colIndex = $column->getColumnIndex();
        $sheet->getColumnDimension($colIndex)->setWidth(24);
    }


    /* TITULO LOG (SIN IMPRIMIR NADA) */

    // Si quieres registrar la exportación, incluye el logger pero ANULANDO cualquier salida
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    ob_start(); // Capturar posibles ecos del include
    @include_once __DIR__ . '/../../inicio_sesion/seguridad/log_registros.php';
    $__logger_output = ob_get_clean(); // Desechar cualquier byte impreso

    if (function_exists('app_log')) {
        app_log('export', 'plantilla', "Descarga de plantilla: $nombreDescarga", [
            'tipo' => $tipo,
            'actor' => $_SESSION['username'] ?? ''
        ]);
    }

    /* TITULO DESCARGA XLSX */

    // Limpiar buffers por seguridad antes de enviar headers/binario
    while (ob_get_level() > 0) { ob_end_clean(); }

    // Headers estrictos para XLSX
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$nombreDescarga.'"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    header('Expires: 0');

    // Generamos salida
    $writer = new Xlsx($spreadsheet);
    // Evita pre-calcular fórmulas (ahorra tiempo y no afecta plantilla)
    $writer->setPreCalculateFormulas(false);

    // Enviar directamente al output
    $writer->save('php://output');
exit;

/*  ------------------------------------------------------------------------------------------------------------
    ----------------------------------- FIN ITred Spa exportar_excel .PHP --------------------------------------
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
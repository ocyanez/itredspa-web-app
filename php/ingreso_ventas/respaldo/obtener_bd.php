<?php
/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl    
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/
// ------------------------------------------------------------------------------------------------------------
// ------------------------------------- INICIO ITred Spa obtener_bd .PHP -------------------------------------
// ------------------------------------------------------------------------------------------------------------

/* ------------------------
   -- INICIO CONEXION BD --
   ------------------------ */
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
/* ---------------------
   -- FIN CONEXION BD --
   --------------------- */

// Librerías para generar Excel moderno (.xlsx)
require_once __DIR__ . '/../../../programas/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Cell\DataType;


/* TITULO FUNCIONES DE FORMATEO */

    // Convierte fecha a texto con día/mes/año y hora:minuto
    function fechaTexto($fecha) {
        if (!$fecha) return '';
        try { return (new DateTime($fecha))->format('d/m/Y H:i'); }
        catch (Exception $e) { return (string)$fecha; }
    }

    // Convierte fecha a texto solo con día/mes/año
    function fechaTextoSimple($fecha) {
        if (!$fecha) return '';
        try { return (new DateTime($fecha))->format('d/m/Y'); }
        catch (Exception $e) { return (string)$fecha; }
    }
    // Convierte cualquier valor a texto, si es null devuelve vacío
    function aTexto($v) { return $v === null ? '' : (string)$v; }

    // Rellena un número con ceros a la izquierda hasta la longitud indicada
    function padSerie($num, $len) {
        return str_pad(aTexto($num), max(1, (int)$len), "0", STR_PAD_LEFT);
    }

    // Fuerza que Excel lea el valor como texto en CSV (incluye notación Excel ="...")
    function csvAsText($s) {
        $s = aTexto($s);
        return '="'.str_replace('"','""',$s).'"';
    }

    // Para CSV: mantener ceros en campos numéricos (usa ="...") 
    function csvTextKeepZeros($s) {
        return csvAsText($s);
    }

    // Asegurar UTF-8 (preferente para CSV con BOM, Excel lo detecta) 
    function toUtf8($s) {
        if ($s === null) $s = '';
        $s = (string)$s;
        if (!function_exists('mb_detect_encoding')) return $s; // fallback simple
        if (!mb_detect_encoding($s, 'UTF-8', true)) {
            // Intentar convertir desde Latin-1 si no es UTF-8
            $s = mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
        }
        return $s;
    }

    // Escribe cabecera especial "sep=," para que Excel use coma como separador
    function csvWriteHeaderLine($handle) {
        // Indicar separador para Excel
        fputs($handle, "sep=,\r\n");
    }

    // Escribe una fila en CSV asegurando que cada campo esté en UTF-8
    function csvPutRowLatin1($handle, array $row) {
        $row = array_map('toUtf8', $row);
        fputcsv($handle, $row);
    }

    // Nota: Eliminada normalización a ASCII para preservar Ñ y tildes en exportes CSV

/* TITULO FUNCIÓN HELPER EXCEL */

    // Función para crear archivos Excel con formato y añadirlos al ZIP 
    function createExcelFile($zip, $filename, $headers, $data) {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Poner los encabezados en la primera fila
            $sheet->fromArray($headers, null, 'A1');
            
            // Marcar los encabezados en negrita
            $lastColumn = chr(65 + count($headers) - 1); // calcula última columna A=65, B=66, etc.
            $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
            
            // Escribir los datos fila por fila
            if (!empty($data)) {
                $rowIndex = 2;
                foreach ($data as $row) {
                    $colIndex = 0;
                    foreach ($row as $value) {
                        $colLetter = chr(65 + $colIndex); // columna en letras A, B, C, etc.
                        $sheet->setCellValueExplicit($colLetter . $rowIndex, toUtf8($value), DataType::TYPE_STRING);
                        $colIndex++;
                    }
                    $rowIndex++;
                }
            }
            
            // Ajustar ancho automático de columnas
            for ($i = 0; $i < count($headers); $i++) {
                $colLetter = chr(65 + $i);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }
            
            // Crear archivo temporal Excel
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer = new XlsxWriter($spreadsheet);
            $writer->save($tempFile);
            
            // Leer archivo temporal y añadirlo al ZIP
            $excelContent = file_get_contents($tempFile);
            $zip->addFromString($filename, $excelContent);
            
            // Borrar archivo temporal para Limpiarlo
            unlink($tempFile);
            
            return true;
        } catch (Exception $e) {
            // Registrar error si algo falla
            error_log("Error creando Excel: " . $e->getMessage());
            return false;
        }
    }

/* TITULO PROCESAR DESCARGAS */

    // Si llega un POST con 'formato', inicia el proceso de exportación
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['formato'])) {
        $formato = $_POST['formato'];

        // Registrar en logs la acción de exportación solicitada
        require_once $_SERVER['DOCUMENT_ROOT'].'/php/inicio_sesion/seguridad/log_registros.php';
        app_log('export','respaldo','Descarga solicitada', ['formato'=>$formato]);

        // Consultar ventas con datos de cliente y ordenar por fecha/id
        $sqlVentas = "
            SELECT v.sku, 
                v.rut, 
                CASE 
                    WHEN c.nombre IS NULL THEN 'Cliente no encontrado'
                    WHEN TRIM(c.nombre) = '' THEN 'Nombre vacío'
                    ELSE c.nombre
                END as nombre_cliente,
                v.numero_fact, 
                v.fecha_despacho, 
                v.producto, 
                v.lote,
                v.fecha_fabricacion, 
                v.fecha_vencimiento,
                v.n_serie_ini, 
                v.n_serie_fin, 
                v.id
            FROM venta v
            LEFT JOIN cliente c ON TRIM(v.rut) = TRIM(c.rut)
            ORDER BY v.fecha_despacho DESC, v.id DESC
        ";
        $resVentas = $mysqli->query($sqlVentas);
        $rowsVentas = [];
        $maxLenSeries = 0;
        $clientesFaltantes = 0;
        
        // Si la consulta devolvió resultados, procesamos cada fila de ventas
        if ($resVentas && $resVentas->num_rows > 0) {
            while ($r = $resVentas->fetch_assoc()) {
                // Solo log para casos problemáticos// Log de error si el cliente está vacío o no existe
                if (empty(trim($r['nombre_cliente'])) || 
                    $r['nombre_cliente'] === 'Cliente no encontrado' || 
                    $r['nombre_cliente'] === 'Nombre vacío') {
                    error_log("PROBLEMA - ID: " . $r['id'] . " | RUT: " . $r['rut'] . " | Nombre: '" . $r['nombre_cliente'] . "'");
                }
                // Guardar fila en arreglo de ventas
                $rowsVentas[] = $r;
                // Calcular longitud máxima de series para formateo
                $maxLenSeries = max(
                    $maxLenSeries,
                    strlen(aTexto($r['n_serie_ini'])),
                    strlen(aTexto($r['n_serie_fin']))
                );
                
                // Contar clientes faltantes para diagnóstico
                if ($r['nombre_cliente'] === 'Cliente no encontrado' || 
                    $r['nombre_cliente'] === 'Nombre vacío' ||
                    empty(trim($r['nombre_cliente']))) {
                    $clientesFaltantes++;
                }
            }
        }

/* TITULO EXCEL SIMPLE (HTML .xls “una sola hoja”) */

    // Si el formato pedido es 'excel_simple', generamos y enviamos un .xlsx directo al navegador
    if ($formato === 'excel_simple') {
        // Limpia cualquier salida previa para evitar romper el archivo descargado
        while (ob_get_level() > 0) { ob_end_clean(); }

        // Limpia cualquier salida previa para evitar romper el archivo descargado
        $filename = 'reporte_ventas_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Crea el libro Excel y toma la hoja activa donde escribiremos
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Define los encabezados de columnas que irán en la primera fila
        $headers = [
            'SKU','RUT','NOMBRE CLIENTE','NUMERO DE FACTURA',
            'FECHA DE DESPACHO','PRODUCTO','LOTE',
            'FECHA DE FABRICACION','FECHA DE VENCIMIENTO',
            'SERIE DE INICIO','SERIE DE TERMINO'
        ];
        // Pone los encabezados en negrita para que se distingan visualmente
        $sheet->fromArray($headers, null, 'A1');

        // Aplicar formato en negrita a la primera fila (headers)
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        // Empieza a escribir los datos desde la segunda fila
        $rowIndex = 2;
        if (!empty($rowsVentas)) {
            foreach ($rowsVentas as $row) {
                // Formatea las series con ceros a la izquierda para igualar longitudes
                $serieIni = str_pad((string)$row['n_serie_ini'], $maxLenSeries, '0', STR_PAD_LEFT);
                $serieFin = str_pad((string)$row['n_serie_fin'], $maxLenSeries, '0', STR_PAD_LEFT);

                // Escribe cada columna como texto explícito para conservar ceros y evitar autoformato de Excel
                $sheet->setCellValueExplicit('A'.$rowIndex, toUtf8(aTexto($row['sku'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('B'.$rowIndex, toUtf8(aTexto($row['rut'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C'.$rowIndex, toUtf8(aTexto($row['nombre_cliente'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('D'.$rowIndex, toUtf8(aTexto($row['numero_fact'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('E'.$rowIndex, toUtf8(fechaTexto($row['fecha_despacho'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('F'.$rowIndex, toUtf8(aTexto($row['producto'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('G'.$rowIndex, toUtf8(aTexto($row['lote'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('H'.$rowIndex, toUtf8(fechaTextoSimple($row['fecha_fabricacion'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('I'.$rowIndex, toUtf8(fechaTextoSimple($row['fecha_vencimiento'])), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('J'.$rowIndex, toUtf8($serieIni), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('K'.$rowIndex, toUtf8($serieFin), DataType::TYPE_STRING);

                // Avanza a la siguiente fila para continuar escribiendo
                $rowIndex++;
            }
        }

        // Ajusta automáticamente el ancho de las columnas para que el contenido se vea completo
        foreach (range('A','K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Define cabeceras HTTP para enviar el archivo como descarga .xlsx
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        // Escribe el Excel en la salida estándar y termina el script
        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        $mysqli->close();
        exit;
    }

/* TITULO EXCEL COMPLETO (ZIP con **CSV** en Latin-1) * Fechas como TEXTO ="..." para evitar ###### * Tildes OK en encabezados y datos */

    // Si el formato es 'excel', generamos múltiples reportes en Excel y los empaquetamos en un ZIP
    elseif ($formato === 'excel') {

        // Prepara el ZIP temporal con nombre único por fecha/hora
        $zip = new ZipArchive();
        $zipFilename = 'respaldo_ventas_' . date('Y-m-d_H-i-s') . '.zip';
        $zipTempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFilename;

        // Si el ZIP se pudo abrir/crear, comenzamos a agregar archivos
        if ($zip->open($zipTempPath, ZipArchive::CREATE) === TRUE) {

            // ===== 1) RESUMEN EJECUTIVO =====
            // Consulta totales de clientes, ventas y usuarios para el resumen general
            $total_clientes = $mysqli->query("SELECT COUNT(*) as total FROM cliente")->fetch_assoc()['total'] ?? 0;
            $total_ventas   = $mysqli->query("SELECT COUNT(*) as total FROM venta")->fetch_assoc()['total']   ?? 0;
            $total_usuarios = $mysqli->query("SELECT COUNT(*) as total FROM usuario")->fetch_assoc()['total'] ?? 0;

            // Arma encabezados y datos del resumen y lo agrega como Excel al ZIP
            $headers = ["RESPALDO DE SISTEMA DE VENTAS ITred Spa", ""];
            $data = [
                ["Fecha de Respaldo", date('d/m/Y H:i:s')],
                ["Base de Datos", "trazabil_ingreso_ventas_bd"],
                ["", ""],
                ["=== RESUMEN EJECUTIVO ===", ""],
                ["", ""],
                ["Total de Clientes", $total_clientes],
                ["Total de Ventas", $total_ventas],
                ["Total de Usuarios", $total_usuarios]
            ];
            createExcelFile($zip, 'resumen_ejecutivo.xlsx', $headers, $data);

            // ===== 2) REPORTE DE VENTAS =====
            // Define columnas y transforma las filas de ventas para el Excel
            $headers = ["SKU", "RUT", "NOMBRE CLIENTE", "NUMERO FACTURA", "FECHA DE DESPACHO", "PRODUCTO", "LOTE", "FECHA DE FABRICACION", "FECHA DE VENCIMIENTO", "SERIE DE INICIO", "SERIE DE TERMINO"];
            $data = [];

            // Recorre ventas formateando series y fechas antes de crear el Excel
            if (!empty($rowsVentas)) {
                foreach ($rowsVentas as $row) {
                    $serieIni = padSerie($row['n_serie_ini'], $maxLenSeries);
                    $serieFin = padSerie($row['n_serie_fin'], $maxLenSeries);

                    $data[] = [
                        aTexto($row['sku']),
                        aTexto($row['rut']),
                        aTexto($row['nombre_cliente']),
                        aTexto($row['numero_fact']),
                        fechaTexto($row['fecha_despacho']),
                        aTexto($row['producto']),
                        aTexto($row['lote']),
                        fechaTextoSimple($row['fecha_fabricacion']),
                        fechaTextoSimple($row['fecha_vencimiento']),
                        $serieIni,
                        $serieFin
                    ];
                }
            }
            createExcelFile($zip, 'reporte_ventas.xlsx', $headers, $data);

            // ===== 3) DIRECTORIO DE CLIENTES =====
            // Prepara un directorio con totales y primeras/últimas ventas por cliente
            $headers = ["RUT", "Nombre Completo", "Total Ventas", "Última Venta", "Primera Venta"];
            $data = [];

            // Consulta agregada por cliente y la vuelca en el Excel
            $clientes_query = "
                SELECT
                    c.rut as 'RUT',
                    c.nombre as 'Nombre Completo',
                    COUNT(v.id) as 'Total Ventas',
                    MAX(v.fecha_despacho) as 'Última Venta',
                    MIN(v.fecha_despacho) as 'Primera Venta'
                FROM cliente c
                LEFT JOIN venta v ON c.rut = v.rut
                GROUP BY c.rut, c.nombre
                ORDER BY c.nombre
            ";
            $res = $mysqli->query($clientes_query);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $data[] = [
                        aTexto($row['RUT']),
                        aTexto($row['Nombre Completo']),
                        aTexto($row['Total Ventas']),
                        fechaTexto($row['Última Venta']),
                        fechaTexto($row['Primera Venta'])
                    ];
                }
            }
            createExcelFile($zip, 'directorio_clientes.xlsx', $headers, $data);

            // ===== 4) ANALISIS POR PRODUCTOS =====
            // Genera métricas por producto: cantidad, clientes únicos y rangos de fecha
            $headers = ["Producto", "Cantidad Vendida", "Clientes Únicos", "Primera Venta", "Última Venta"];
            $data = [];

            // Consulta agregada por producto y la carga en el Excel
            $productos_query = "
                SELECT
                    v.producto as 'Producto',
                    COUNT(*) as 'Cantidad Vendida',
                    COUNT(DISTINCT v.rut) as 'Clientes Únicos',
                    MIN(v.fecha_despacho) as 'Primera Venta',
                    MAX(v.fecha_despacho) as 'Última Venta'
                FROM venta v
                GROUP BY v.producto
                ORDER BY COUNT(*) DESC
            ";
            $res = $mysqli->query($productos_query);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $data[] = [
                        aTexto($row['Producto']),
                        aTexto($row['Cantidad Vendida']),
                        aTexto($row['Clientes Únicos']),
                        fechaTexto($row['Primera Venta']),
                        fechaTexto($row['Última Venta'])
                    ];
                }
            }
            createExcelFile($zip, 'analisis_productos.xlsx', $headers, $data);

            // ===== 5) USUARIOS DEL SISTEMA =====
            // Exporta el catálogo de usuarios con datos básicos y rol
            $headers = ["ID", "Nombre", "Apellido", "Usuario", "Correo", "Teléfono", "Dirección", "Cargo", "Rol"];
            $data = [];

            // Consulta usuarios y vuelca filas tal cual al Excel
            $usuarios_query = "
                SELECT
                    id as 'ID',
                    nombre as 'Nombre',
                    apellido as 'Apellido',
                    username as 'Usuario',
                    correo as 'Correo',
                    telefono as 'Teléfono',
                    direccion as 'Dirección',
                    cargo as 'Cargo',
                    rol as 'Rol'
                FROM usuario
                ORDER BY rol, nombre
            ";
            $res = $mysqli->query($usuarios_query);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $data[] = array_values($row);
                }
            }
            createExcelFile($zip, 'usuarios_sistema.xlsx', $headers, $data);

            // ===== 6) REGISTRO INTENTOS LOGIN =====
            // Exporta los últimos intentos de inicio de sesión para auditoría rápida
            $headers = ["IP", "Correo", "Fecha/Hora Intento"];
            $data = [];

            // Consulta últimos intentos y los formatea con fecha legible
            $login_query = "
                SELECT
                    ip_address as 'IP',
                    correo as 'Correo',
                    hora_intento as 'Fecha/Hora Intento'
                FROM login_intentos
                ORDER BY hora_intento DESC
                LIMIT 50
            ";
            $res = $mysqli->query($login_query);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $data[] = [
                        aTexto($row['IP']),
                        aTexto($row['Correo']),
                        fechaTexto($row['Fecha/Hora Intento'])
                    ];
                }
            }
            createExcelFile($zip, 'registro_login.xlsx', $headers, $data);

            // Cierra el ZIP, limpia buffers y envía la descarga al navegador
            $zip->close();
            
            // Limpiar cualquier salida previa
            while (ob_get_level() > 0) { ob_end_clean(); }
            
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: public');
            header('Expires: 0');
            header('Content-Length: ' . filesize($zipTempPath));
            
            readfile($zipTempPath);
            @unlink($zipTempPath);
            $mysqli->close();
            exit;

        } else {
            // Si no logramos crear el ZIP, avisamos con un mensaje simple
            echo 'No se pudo crear el archivo ZIP.';
        }
    }

/* TITULO SQL (RESPALDO) */

    // Si el formato es 'sql', generamos un volcado SQL completo y lo enviamos como descarga
    elseif ($formato === 'sql') {

        // Limpia cualquier salida previa y prepara cabeceras para archivo .sql
        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/sql; charset=utf-8');
        $filename = 'respaldo_' . date('Y-m-d_H-i-s') . '.sql';
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Define tablas a respaldar y arma encabezado informativo del archivo
        $tablas = ['cliente', 'estilos_botones_menu', 'login_intentos', 'personalizacion', 'usuario', 'venta'];
        $sql = "-- Sitio Web Creado por ITred Spa.\n";
        $sql .= "-- Direccion: Guido Reni #4190\n";
        $sql .= "-- Pedro Aguirre Cerda - Santiago - Chile\n";
        $sql .= "-- contacto@itred.cl o itred.spa@gmail.com\n";
        $sql .= "-- https://www.itred.cl\n";
        $sql .= "-- Creado, Programado y Diseñado por ITred Spa.\n";
        $sql .= "-- BPPJ\n\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "START TRANSACTION;\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";
        $sql .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $sql .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $sql .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $sql .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
        $sql .= "--\n";
        $sql .= "-- Base de datos: `trazabil_ingreso_ventas_bd_itred`\n";
        $sql .= "--\n\n";
        $sql .= "-- ------------------------------------------------------------------------------------------------------------\n";
        $sql .= "-- ----------------------- INICIO ITred Spa Base de Datos trazabil_ingreso_ventas_bd .SQL --------------\n";
        $sql .= "-- ------------------------------------------------------------------------------------------------------------\n\n";
        $sql .= "-- --------------------------------------------------------\n\n";

        // Recorre cada tabla: agrega CREATE TABLE (sin índices) y el volcado de datos
        foreach ($tablas as $tabla) {
            $res = $mysqli->query("SHOW CREATE TABLE `$tabla`");
            if ($res) {
                // Obtiene CREATE TABLE y limpia índices/AUTO_INCREMENT para separar estructura de datos
                $createTable = $res->fetch_assoc();
                $sql .= "--\n";
                $sql .= "-- Estructura de tabla para la tabla `$tabla`\n";
                $sql .= "--\n\n";
                
                // Limpiar la estructura: remover PRIMARY KEY, UNIQUE KEY, KEY, AUTO_INCREMENT
                $createStatement = $createTable['Create Table'];
                
                // Dividir por líneas para procesar cada una
                $lines = explode("\n", $createStatement);
                $cleanLines = [];
                
                // Recorre cada línea del CREATE TABLE y limpia índices/constraints dejando solo definición de columnas
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    // Omite líneas de índices/constraints, mantiene definición de columnas
                    if (strpos($trimmedLine, 'PRIMARY KEY') === false && 
                        strpos($trimmedLine, 'UNIQUE KEY') === false && 
                        strpos($trimmedLine, 'KEY ') === false &&
                        strpos($trimmedLine, 'CONSTRAINT') === false) {
                        
                        // Quita AUTO_INCREMENT de columnas
                        $line = preg_replace('/\s+AUTO_INCREMENT/i', '', $line);
                        $cleanLines[] = $line;
                    }
                }
                
                // Reconstruye el CREATE TABLE limpio y corrige coma final antes de ')'
                $cleanedCreate = implode("\n", $cleanLines);
                // Quita AUTO_INCREMENT del final (ENGINE=... AUTO_INCREMENT=n)
                $cleanedCreate = preg_replace('/,(\s*\n\s*\))/', '$1', $cleanedCreate);
                
                // Remover AUTO_INCREMENT del ENGINE (ej: ENGINE=InnoDB AUTO_INCREMENT=156)
                $cleanedCreate = preg_replace('/\s+AUTO_INCREMENT=\d+/i', '', $cleanedCreate);
                
                $sql .= $cleanedCreate . ";\n\n";
            }
            $res = $mysqli->query("SELECT * FROM `$tabla`");
            if ($res && $res->num_rows > 0) {
                $cols = [];
                $colres = $mysqli->query("SHOW COLUMNS FROM `$tabla`");
                while ($col = $colres->fetch_assoc()) {
                    $cols[] = $col['Field'];
                }
                $sql .= "--\n";
                $sql .= "-- Volcado de datos para la tabla `$tabla`\n";
                $sql .= "--\n\n";
                $sql .= "INSERT INTO `$tabla` (`" . implode("`, `", $cols) . "`) VALUES\n";
                $rows = [];
                while ($row = $res->fetch_assoc()) {
                    // Prepara valores: NULL sin comillas, números sin comillas, texto escapado con comillas
                    $vals = array_map(function($v) use ($mysqli) {
                        if ($v === null) {
                            return "NULL";
                        } elseif (is_numeric($v) && !is_string($v)) {
                            // Es un número, no agregar comillas
                            return $v;
                        } elseif (is_numeric($v) && ctype_digit((string)$v)) {
                            // Es una cadena que contiene solo dígitos (como IDs)
                            return $v;
                        } else {
                            // Es texto, agregar comillas
                            return "'" . $mysqli->real_escape_string($v) . "'";
                        }
                    }, array_values($row));
                    $rows[] = "(" . implode(", ", $vals) . ")";
                }
                $sql .= implode(",\n", $rows) . ";\n\n";
                $sql .= "-- --------------------------------------------------------\n\n";
            } else {
                // Marca separación aunque no haya datos para mantener consistencia del archivo
                $sql .= "-- --------------------------------------------------------\n\n";
            }
        }
        
        // Agregar índices y AUTO_INCREMENT
        $sql .= "--\n";
        $sql .= "-- Índices para tablas volcadas\n";
        $sql .= "--\n\n";
        
        // Índices de cliente (usa RUT como clave primaria)
        $sql .= "--\n";
        $sql .= "-- Indices de la tabla `cliente`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `cliente`\n";
        $sql .= "  ADD PRIMARY KEY (`rut`);\n\n";
        
        // Índices de estilos_botones_menu (clave primaria y único por boton_id)
        $sql .= "--\n";
        $sql .= "-- Indices de la tabla `estilos_botones_menu`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `estilos_botones_menu`\n";
        $sql .= "  ADD PRIMARY KEY (`id`),\n";
        $sql .= "  ADD UNIQUE KEY `boton_id` (`boton_id`);\n\n";
        
        // Índices de personalizacion (ID como clave primaria)
        $sql .= "--\n";
        $sql .= "-- Indices de la tabla `personalizacion`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `personalizacion`\n";
        $sql .= "  ADD PRIMARY KEY (`id`);\n\n";
        
        // Índices de usuario (ID como clave primaria)
        $sql .= "--\n";
        $sql .= "-- Indices de la tabla `usuario`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `usuario`\n";
        $sql .= "  ADD PRIMARY KEY (`id`);\n\n";
        
        // Índices de venta (ID como PK y KEY por RUT para búsquedas)
        $sql .= "--\n";
        $sql .= "-- Indices de la tabla `venta`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `venta`\n";
        $sql .= "  ADD PRIMARY KEY (`id`),\n";
        $sql .= "  ADD KEY `rut` (`rut`);\n\n";
        
        // Restaura AUTO_INCREMENT por tabla después de insertar datos
        $sql .= "--\n";
        $sql .= "-- AUTO_INCREMENT de las tablas volcadas\n";
        $sql .= "--\n\n";
        
        // AUTO_INCREMENT de estilos_botones_menu
        $sql .= "--\n";
        $sql .= "-- AUTO_INCREMENT de la tabla `estilos_botones_menu`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `estilos_botones_menu`\n";
        $sql .= "  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;\n\n";
        
        // AUTO_INCREMENT de personalizacion
        $sql .= "--\n";
        $sql .= "-- AUTO_INCREMENT de la tabla `personalizacion`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `personalizacion`\n";
        $sql .= "  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;\n\n";
        
        // AUTO_INCREMENT de usuario
        $sql .= "--\n";
        $sql .= "-- AUTO_INCREMENT de la tabla `usuario`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `usuario`\n";
        $sql .= "  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;\n\n";
        
        // AUTO_INCREMENT de venta
        $sql .= "--\n";
        $sql .= "-- AUTO_INCREMENT de la tabla `venta`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `venta`\n";
        $sql .= "  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;\n\n";
        
        // Añade restricciones (FKs) tras los datos para evitar errores de integridad durante los INSERTs
        $sql .= "--\n";
        $sql .= "-- Restricciones para tablas volcadas\n";
        $sql .= "--\n\n";
        
        $sql .= "--\n";
        $sql .= "-- Filtros para la tabla `venta`\n";
        $sql .= "--\n";
        $sql .= "ALTER TABLE `venta`\n";
        $sql .= "  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`rut`) REFERENCES `cliente` (`rut`);\n";
        $sql .= "COMMIT;\n\n";
        
        // Cierra el bloque del respaldo con marcas de inicio/fin y restaura configuración de cliente
        $sql .= "-- ------------------------------------------------------------------------------------------------------------\n";
        $sql .= "-- ------------------------ FIN ITred Spa Base de Datos trazabil_ingreso_ventas_bd .SQL ---------------\n";
        $sql .= "-- ------------------------------------------------------------------------------------------------------------\n\n";
        
        $sql .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $sql .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $sql .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n\n";
        
        $sql .= "-- Sitio Web Creado por ITred Spa.\n";
        $sql .= "-- Direccion: Guido Reni #4190\n";
        $sql .= "-- Pedro Aguirre Cerda - Santiago - Chile\n";
        $sql .= "-- contacto@itred.cl o itred.spa@gmail.com\n";
        $sql .= "-- https://www.itred.cl\n";
        $sql .= "-- Creado, Programado y Diseñado por ITred Spa.\n";
        $sql .= "-- BPPJ\n";
        
        // Envía el contenido SQL al navegador y cierra la conexión
        echo $sql;
        $mysqli->close();
        exit;
    }
}

// ------------------------------------------------------------------------------------------------------------
// ------------------------------------- FIN ITred Spa obtener_bd .PHP ----------------------------------------
// ------------------------------------------------------------------------------------------------------------

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

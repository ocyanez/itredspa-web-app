<?php
/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.cl@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/

// <!-- ------------------------------------------------------------------------------------------------------------
//      ------------------------------------- INICIO ITred Spa cargar_factura .PHP ---------------------------------
//      ------------------------------------------------------------------------------------------------------------ -->

// TITULO ERRORES PARA DEBUG

    // activamos la visualización de errores para saber si algo falla
    ini_set('display_errors', 1);
    // también mostramos errores de inicio del sistema
    ini_set('display_startup_errors', 1);
    // le decimos al sistema que reporte absolutamente todo lo que pase
    error_reporting(E_ALL);

    // Limpiamos buffer para asegurar que solo salga nuestra respuesta
    while (ob_get_level() > 0) { ob_end_clean(); }

    // Forzamos la cabecera correcta
    header('Content-Type: text/plain; charset=utf-8');

// <!-- ---------------------
//      -- INICIO CONEXION BD --
//      --------------------- -->

    // nos conectamos a la base de datos con el usuario y contraseña
    $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    if ($mysqli->connect_error) {
        die('ERROR|Error de conexión a BD: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");

// <!-- ---------------------
//      -- FIN CONEXION BD --
//      --------------------- -->

// TITULO IMPORTACIÓN DE LIBRERÍA EXCEL 

    // cargamos las herramientas necesarias para leer archivos excel
    require __DIR__ . '/../../../programas/vendor/autoload.php';
    // usamos la herramienta especifica para manejar hojas de calculo
    use PhpOffice\PhpSpreadsheet\IOFactory;

// TITULO FUNCIONES AUXILIARES

    // funcion que limpia y ordena el rut quitando puntos y poniendo guion
    function rut_formatear($rut) {
        $rut = str_replace('.', '', $rut); 
        $rut = strtoupper(trim($rut));  
        if (strpos($rut, '-') !== false) return $rut; 
        if (strlen($rut) >= 2) {
            $cuerpo = substr($rut, 0, -1); 
            $dv = substr($rut, -1); 
            return $cuerpo . '-' . $dv;
        }
        return $rut;
    }

    // funcion para enviar la respuesta final al sistema de forma ordenada y segura
    function salida_texto_segura($estado, $param1 = '', $param2 = '', $param3 = '') {
        // Si hubo errores previos de PHP, los limpiamos para que el JS lea limpio
        // ob_clean(); 
        // Formato: estado|insertados|existentes|errores
        echo $estado . '|' . $param1 . '|' . $param2 . '|' . $param3;
        exit;
    }

// TITULO PROCESO DE CARGA DE ARCHIVO EXCEL

    // verificamos si enviaron un archivo excel y que no tenga errores
    if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] == 0) {
        // guardamos la ubicacion temporal del archivo que subieron
        $archivo_temp = $_FILES['archivo_excel']['tmp_name'];

        // Carga Excel con la librería
        try {
            // intentamos abrir el archivo excel subido
            $spreadsheet = IOFactory::load($archivo_temp);
        // si falla al abrir mandamos un error avisando que paso
        } catch (Throwable $e) {
            // mensaje de error detallado
            salida_texto_segura('ERROR', 'No se pudo leer el archivo Excel: ' . $e->getMessage());
        }
        // Obtenemos la hoja activa y la convertimos a un array
        $sheet = $spreadsheet->getActiveSheet();
        // Convertimos toda la hoja a un array osea lista asociativo que podamos leer
        $rows  = $sheet->toArray(null, true, true, true);

        // Contadores
        $filasInsertadas = 0;
        $filasErrores = 0;

       
        // Usamos 'sku' y 'producto' de las columnas del excel para productos
        $stmtCheckProd = $mysqli->prepare("SELECT id FROM producto WHERE sku = ? LIMIT 1");
        $stmtCreateProd = $mysqli->prepare("INSERT INTO producto (sku, producto) VALUES (?, ?)");

        // Preparar consulta de inserción de factura
        $sqlInsert = "INSERT INTO factura (
            n_factura, rut_empresa, nombre_empresa, giro_empresa, 
            codigo_producto, descripcion_producto, cantidad_producto, precio_producto, 
            valor_producto, neto_producto, iva_producto, total_producto,
            impuesto_producto, descuento_producto, impacto_producto
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0)";
        // Valores por defecto: impuesto, descuento e impacto en 0
        $stmtInsert = $mysqli->prepare($sqlInsert);
        // Verificar preparación
        if (!$stmtInsert) {
            // Si falla aquí, mostramos el error exacto de la BD
            salida_texto_segura('ERROR', 'Error preparando consulta Factura: ' . $mysqli->error);
        }

        // Recorrer filas (desde la 2)
        for ($i = 2; $i <= count($rows); $i++) {
            $fila = $rows[$i];

            /* MAPEO DE COLUMNAS
            A: N_FACTURA
            B: RUT_EMPRESA
            C: NOMBRE_EMPRESA
            D: GIRO
            E: SKU
            F: PRODUCTO
            G: CANTIDAD
            H: PRECIO
            */
            
            $n_factura = trim((string)($fila['A'] ?? ''));
            $rut_raw   = trim((string)($fila['B'] ?? ''));
            $nombre    = trim((string)($fila['C'] ?? ''));
            $giro      = trim((string)($fila['D'] ?? ''));
            $sku       = trim((string)($fila['E'] ?? ''));
            $desc      = trim((string)($fila['F'] ?? ''));
            $cant      = (float) str_replace(',', '.', trim((string)($fila['G'] ?? '0')));
            $precio    = (float) str_replace(',', '.', trim((string)($fila['H'] ?? '0')));

            // Validar datos mínimos
            if ($n_factura === '' || $rut_raw === '' || $sku === '') {
                continue; 
            }
             // Formatear rut
            $rut = rut_formatear($rut_raw);

            // Verificar si el producto existe
            if ($stmtCheckProd) {
                $stmtCheckProd->bind_param("s", $sku);
                $stmtCheckProd->execute();
                $stmtCheckProd->store_result();
                // Si no existe el producto, lo creamos
                if ($stmtCheckProd->num_rows === 0) {
                    // No existe, lo creamos
                    if ($stmtCreateProd && $desc !== '') {
                        $stmtCreateProd->bind_param("ss", $sku, $desc);
                        $stmtCreateProd->execute();
                    }
                }
            }

            // Cálculos
            // valor_producto = cantidad * precio
            $valor = $cant * $precio; 
            // neto_producto (asumimos igual si no hay desc)
            $neto  = $valor;         
            // iva_producto = 19% del neto 
            $iva   = round($neto * 0.19);
            // total_producto = neto + iva
            $total = $neto + $iva;

            // Insertar Factura
            // "ssssssdddddd" -> 6 strings, 6 doubles
            $stmtInsert->bind_param("ssssssdddddd", 
                $n_factura, $rut, $nombre, $giro, 
                $sku, $desc, $cant, $precio, 
                $valor, $neto, $iva, $total
            );
             // Ejecutar inserción
            if ($stmtInsert->execute()) {
                $filasInsertadas++;
            // Si falla la inserción contamos como error
            } else {
                $filasErrores++;
               
            }
        }

        // Cerrar todo
        if($stmtCheckProd) $stmtCheckProd->close();
        if($stmtCreateProd) $stmtCreateProd->close();

        $stmtInsert->close();

// <!-- -------------------------------
//      -- INICIO CIERRE CONEXION BD --
//      ------------------------------- -->
        
        // $mysqli->close();

//  <!-- ----------------------------
//      -- FIN CIERRE CONEXION BD --
//      ---------------------------- -->	       

        // Respuesta OK
        salida_texto_segura('OK', $filasInsertadas, 0, $filasErrores);

    } else {
        salida_texto_segura('ERROR', 'No se recibió el archivo o hubo error de subida.');
    }

// <!-- ------------------------------------------------------------------------------------------------------------
//     -------------------------------------- FIN ITred Spa cargar_factura.php -------------------------------------
//     ------------------------------------------------------------------------------------------------------------- -->
	
// <!--
// Sitio Web Creado por ITred Spa.
// Direccion: Guido Reni #4190
// Pedro Aguirre Cerda - Santiago - Chile
// contacto@itred.cl o itred.spa@gmail.com
// https://www.itred.cl
// Creado, Programado y Diseñado por ITred Spa.
// BPPJ
// -->
?>
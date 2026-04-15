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
    ---------------------------- INICIO ITred Spa exportar_clientes_registrados .PHP ---------------------------
    ------------------------------------------------------------------------------------------------------------ */


    // Cargamos la librería PhpSpreadsheet para crear archivos Excel
    require __DIR__ . '/../../../programas/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

   // Conectamos a la base de datos
    $conexion = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    $conexion->set_charset("utf8");

    // Si falla la conexión, detenemos el script con error 500
    if ($conexion->connect_error) {
        http_response_code(500);
        exit("Error de conexión: " . $conexion->connect_error);
    }

// CONSULTA EN LA BASE DE DATOS 

    // Consultamos todos los clientes ordenados por nombre
    $sql = "SELECT nombre, rut FROM cliente ORDER BY nombre ASC";
    $resultado = $conexion->query($sql);

    // Si no hay resultados, respondemos con código 204 (sin contenido)
    if (!$resultado || $resultado->num_rows === 0) {
        http_response_code(204); 
        exit("No hay clientes registrados.");
    }

// CREACION DE ARCHIVO EXCEL    

    // Creamos un nuevo archivo Excel
    $spreadsheet = new Spreadsheet();
    $hoja = $spreadsheet->getActiveSheet();
    $hoja->setTitle("Clientes Registrados");

    // Ajustamos el ancho de las columnas para que se vea bien
    $hoja->getColumnDimension('A')->setWidth(25.80);
    $hoja->getColumnDimension('B')->setWidth(11.73);

    // Escribimos los encabezados en la primera fila
    $hoja->setCellValue('A1', 'NOMBRE O RAZÓN SOCIAL:');
    $hoja->setCellValue('B1', 'RUT');

    // Aplicamos estilo al encabezado: negrita, centrado y línea inferior
    $estiloEncabezado = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ];
    $hoja->getStyle('A1:B1')->applyFromArray($estiloEncabezado);

    // Escribimos los datos de cada cliente a partir de la fila 2
    $fila = 2;
    while ($cliente = $resultado->fetch_assoc()) {
        $hoja->setCellValue("A{$fila}", $cliente['nombre']);
        $hoja->setCellValue("B{$fila}", $cliente['rut']);
        $fila++;
    }

    // Limpiamos cualquier salida previa para evitar errores en la descarga
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="clientes_registrados.xlsx"');
    header('Cache-Control: max-age=0');
    // Guardamos el archivo directamente en la salida (descarga)
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
exit;



/*  ------------------------------------------------------------------------------------------------------------
    ----------------------------- FIN ITred Spa exportar_clientes_registrados .PHP -----------------------------
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
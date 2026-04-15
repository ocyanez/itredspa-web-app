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
     ------------------------------------- INICIO ITred Spa ver_pdf .PHP ----------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

     <?php
        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd");
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<!-- TITULO CONFIGURACION PDF -->

     <?php
          // Ajusta la ruta de las fuentes para FPDF
          define('FPDF_FONTPATH', __DIR__ . '/../../../../fuentes/');
          // Este require llama al archivo fpdf.php para poder realizar tareas como descargar el pdf
          require('fpdf.php');
     ?>


<!-- TITULO CONSULTA SQL -->
 
     <?php
          $mysqli->set_charset("utf8");
          // Consulta SQL para obtener los datos de la tabla venta y cliente
          // Se seleccionan los campos necesarios de ambas tablas y se ordenan por el ID de venta en orden descendente
          $sql = "SELECT 
                    v.rut, 
                    c.nombre, 
                    v.numero_fact, 
                    v.fecha_despacho, 
                    v.sku, 
                    v.producto, 
                    v.lote, 
                    v.fecha_fabricacion,
                    v.n_serie_ini,
                    v.n_serie_fin
               FROM venta v
               JOIN cliente c ON v.rut = c.rut
               ORDER BY v.id DESC";

          // Ejecuta la consulta SQL y almacena el resultado en la variable $result
          $result = $mysqli->query($sql);
     ?>
     

<!-- TITULO CREACION PDF -->

     <?php
          // Crea un nuevo PDF en orientación horizontal (L), en milímetros (mm) y tamaño A3
          $pdf = new FPDF('L', 'mm', 'A3');
          // Agrega una nueva página al PDF
          $pdf->AddPage();
          
          // Establece la fuente Arial, negrita, tamaño 14 para el título
          $pdf->SetFont('Arial', 'B', 14);
          // Centra el título
          $pdf->Cell(0, 10, utf8_decode('Listado de Productos Despachados'), 0, 1, 'C');
          // Salto de línea de 5 mm
          $pdf->Ln(5);

          // Establece la fuente Arial, negrita, tamaño 10 para los encabezados
          $pdf->SetFont('Arial', 'B', 10);
          // Encabezados de la tabla
          $headers = ['RUT', 'Nombre Cliente', 'Numero Doc', 'Fecha Despacho', 'SKU', 'Producto', 'Lote', 'Fecha Fab.', 'Serie de Inicio', 'Serie de Termino'];
          // Anchos de las columnas respectivas
          $widths  = [25, 30, 50, 25, 35, 50, 21, 25, 30, 30];

          // Suma los anchos de las columnas para obtener el ancho total
          $totalWidth = array_sum($widths);
          // Obtiene el ancho de la página
          $pageWidth = $pdf->GetPageWidth();
          // Calcula la posición inicial para centrar la tabla
          $startX = ($pageWidth - $totalWidth) / 2;
          // Establece la posición X inicial para la tabla
          $pdf->SetX($startX);

          // Recorre los encabezados
          foreach ($headers as $i => $h) { 
          // Imprime cada encabezado con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[$i], 10, utf8_decode($h), 1, 0, 'C');
          }
          // Salto de línea para separar encabezados de datos
          $pdf->Ln();

          // Configura la fuente para los datos de la tabla
          $pdf->SetFont('Arial', '', 9);

          // Recorre cada fila de resultados
          while ($row = $result->fetch_assoc()) {
          // Establece la posición X inicial para cada fila
          $pdf->SetX($startX);
          // Imprime el RUT con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[0], 8, utf8_decode($row['rut']), 1, 0, 'C');
          // Imprime el nombre del cliente con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[1], 8, utf8_decode($row['nombre']), 1, 0, 'C');
          // Imprime el número de factura con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[2], 8, utf8_decode($row['numero_fact']), 1, 0, 'C');
          // Imprime la fecha de despacho con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[3], 8, utf8_decode($row['fecha_despacho']), 1, 0, 'C');
          // Imprime el SKU con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[4], 8, utf8_decode($row['sku']), 1, 0, 'C');
          // Imprime el producto con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[5], 8, utf8_decode($row['producto']), 1, 0, 'C');
          // Imprime el lote con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[6], 8, utf8_decode($row['lote']), 1, 0, 'C');
          // Imprime la fecha de fabricación con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[7], 8, utf8_decode($row['fecha_fabricacion']), 1, 0, 'C');
          // Imprime el número 1 con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[8], 8, utf8_decode($row['n_serie_ini']), 1, 0, 'C');
          // Imprime el número 2 con su respectivo ancho y alineación centrada
          $pdf->Cell($widths[9], 8, utf8_decode($row['n_serie_fin']), 1, 0, 'C');
          // Salto de línea para la siguiente fila
          $pdf->Ln();
          }

          // Devuelve el PDF al navegador, 'I' para mostrar en el navegador
          $pdf->Output('reporte.pdf', 'I');
          //exit;
     ?>


<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->
     
    <!-- <?php 
        // $mysqli->close();
        ?> -->

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa ver_pdf .PHP ------------------------------------------
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

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
   ------------------------------ INICIO ITred Spa descargar_usuarios .PHP ------------------------------------
   ------------------------------------------------------------------------------------------------------------ */

// TITULO CONFIGURACION INICIO DE SESION Y REPORTES DE ERROR

    // aquí se configura para que no se muestren errores en la pantalla del usuario si algo falla
    ini_set('display_errors', '0');

    // esto le dice al sistema que internamente sí reporte todos los errores aunque no los muestre
    error_reporting(E_ALL);

    // este bloque limpia la memoria temporal para asegurar que el archivo se descargue limpio sin basura
    while (ob_get_level() > 0) { ob_end_clean(); }

    // aquí se carga una herramienta externa necesaria para poder crear archivos excel
    require __DIR__ . '/../../../programas/vendor/autoload.php';

    // estas líneas indican que usaremos funciones específicas para crear hojas de cálculo y darles estilo
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    use PhpOffice\PhpSpreadsheet\Style\Fill;

// TITULO VERFICIACION INICIO DE SESION

    // aquí se verifica si ya hay una sesión iniciada y si no la hay se inicia una nueva para reconocer al usuario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // este bloque revisa si el usuario tiene un rol asignado y si no lo tiene lo expulsa a la página de inicio
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] === '') {
        header("Location: /ingreso_ventas.php");
        exit();
    }


// <!-- ------------------------
//      -- INICIO CONEXION BD --
//      ------------------------ -->

    // aquí se conecta a la base de datos usando el usuario y la contraseña del sistema
    $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");

    // si la conexión falla esto detiene todo y muestra un mensaje de error
    if ($mysqli->connect_error) {
        die("Error de conexión: " . $mysqli->connect_error);
    }

// <!-- ---------------------
//      -- FIN CONEXION BD --
//      --------------------- -->

// TITULO DESCARGA LISTA USUARIOS EXCEL

    // aquí se revisa si el usuario escribió algo en el buscador para filtrar la lista
    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

    // aquí se guarda qué tipo de rol tiene la persona que está usando el sistema
    $rolSesion = $_SESSION['rol'];

    // esta es la instrucción básica para pedir los datos de los usuarios a la base de datos
    $sql = "SELECT rut, nombre, apellido, username, correo, telefono, direccion, rol, cargo 
            FROM usuario 
            WHERE 1=1";

    // si el usuario es un administrador normal se agrega una regla para que no pueda ver al super administrador
    if ($rolSesion === 'admin') {
        $sql .= " AND LOWER(rol) <> 'superadmin'";
    }

    // aquí se prepara una lista vacía para guardar los datos de búsqueda
    $parametros = [];

    // si se escribió algo en el buscador se agrega una regla larga para buscar por nombre rut o correo
    if (mb_strlen($busqueda) >= 1) {
        $sql .= " AND (
            rut LIKE ? OR nombre LIKE ? OR apellido LIKE ? OR username LIKE ?
            OR correo LIKE ? OR telefono LIKE ? OR direccion LIKE ? OR rol LIKE ? OR cargo LIKE ?
        )";
        
        // aquí se define el término de búsqueda poniéndole símbolos de porcentaje a los lados
        $term = '%' . $busqueda . '%';
        
        // se rellena la lista de parámetros con el término de búsqueda repetido para cada campo
        $parametros = array_fill(0, 9, $term);
    }

// TITULO CREACION ARCHIVO EXCEL

    // aquí se prepara la consulta de forma segura para evitar hackeos
    $stmt = $mysqli->prepare($sql);

    // si había parámetros de búsqueda se agregan a la consulta preparada
    if (!empty($parametros)) {
        $stmt->bind_param(str_repeat('s', count($parametros)), ...$parametros);
    }

    // aquí se ejecuta la orden en la base de datos
    $stmt->execute();

    // aquí se obtienen los resultados de la búsqueda
    $resultado = $stmt->get_result();

    // aquí se crea un nuevo archivo de excel en blanco
    $spreadsheet = new Spreadsheet();

    // se selecciona la hoja activa de ese archivo excel
    $sheet = $spreadsheet->getActiveSheet();

    // se le pone el nombre lista de usuarios a la pestaña de la hoja
    $sheet->setTitle('Lista de Usuarios');

    // aquí se definen los títulos que irán en la primera fila como rut nombre apellido etc
    $headers = ['A'=>'RUT', 'B'=>'Nombre', 'C'=>'Apellido', 'D'=>'Usuario', 'E'=>'Correo', 'F'=>'Teléfono', 'G'=>'Dirección', 'H'=>'Rol', 'I'=>'Cargo'];

    // este ciclo recorre cada título para escribirlo en la primera fila
    foreach ($headers as $col => $text) {
        $sheet->setCellValue($col . '1', $text);
        
        // aquí se pone el texto de los títulos en negrita
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
        
        // aquí se le pone un fondo de color gris claro a los títulos para que destaquen
        $sheet->getStyle($col . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEEEEEE');
    }

    // se establece que los datos empezarán a escribirse desde la fila dos
    $fila = 2;

    // este ciclo va tomando uno por uno a cada usuario que se encontró en la base de datos
    while ($row = $resultado->fetch_assoc()) {
        // aquí se escribe el rut en la columna a
        $sheet->setCellValue('A' . $fila, $row['rut']);
        
        // aquí se escribe el nombre en la columna b
        $sheet->setCellValue('B' . $fila, $row['nombre']);
        
        // aquí se escribe el apellido en la columna c
        $sheet->setCellValue('C' . $fila, $row['apellido']);
        
        // aquí se escribe el nombre de usuario en la columna d
        $sheet->setCellValue('D' . $fila, $row['username']);
        
        // aquí se escribe el correo en la columna e
        $sheet->setCellValue('E' . $fila, $row['correo']);
        
        // aquí se escribe el teléfono en la columna f
        $sheet->setCellValue('F' . $fila, $row['telefono']);
        
        // aquí se escribe la dirección en la columna g
        $sheet->setCellValue('G' . $fila, $row['direccion']);
        
        // aquí se escribe el rol con la primera letra mayúscula en la columna h
        $sheet->setCellValue('H' . $fila, ucfirst($row['rol']));
        
        // aquí se escribe el cargo en la columna i
        $sheet->setCellValue('I' . $fila, $row['cargo']);
        
        // se aumenta el contador para pasar a la siguiente fila del excel
        $fila++;
    }

    // este bloque ajusta automáticamente el ancho de todas las columnas para que el texto se lea bien
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // aquí se crea el nombre que tendrá el archivo descargado incluyendo la fecha de hoy
    $nombreArchivo = 'lista_usuarios_' . date("Y-m-d") .

    // estas líneas le dicen al navegador que lo que viene es un archivo de excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    // le dicen al navegador que debe descargar el archivo con el nombre que definimos antes
    header('Content-Disposition: attachment;filename="' . $nombreArchivo . '.xlsx"');

    // evitan que el navegador guarde el archivo en memoria caché para que siempre sea nuevo
    header('Cache-Control: max-age=0');
    header('Expires: 0');

    // aquí se prepara el guardador en formato excel moderno
    $writer = new Xlsx($spreadsheet);

    // aquí se envía el archivo generado directamente al navegador para que se descargue
    $writer->save('php://output');

    // aquí se cierra la consulta segura
    $stmt->close();

// <!-- -------------------------------
//      -- INICIO CIERRE CONEXION BD --
//      ------------------------------- -->

    // aquí se cierra la conexión a la base de datos
    // $mysqli->close();

// <!-- ----------------------------
//      -- FIN CIERRE CONEXION BD --
//      ---------------------------- -->

    // finalmente se termina el proceso para que no se ejecute nada más
    exit;

// <!-- ------------------------------------------------------------------------------------------------------------
//     -------------------------------------- FIN ITred Spa descargar_usuarios .PHP --------------------------------
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
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

/*   ------------------------------------------------------------------------------------------------------------
     ------------------------------------- INICIO ITred Spa eliminar_filas .PHP ---------------------------------
     ------------------------------------------------------------------------------------------------------------ */

/*   ------------------------
     -- INICIO CONEXION BD --
     ------------------------ */

        // establece la conexión a la base de datos ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
        // include auditoría global (estandarizado como en los otros endpoints)
        require_once __DIR__ . '/../../inicio_sesion/seguridad/log_registros.php';

/*   ---------------------
     -- FIN CONEXION BD --
     --------------------- */


    // Define el tipo de contenido en el archivo
    header('Content-Type: application/json');
    $mysqli->set_charset("utf8");
    // Verifica si el formulario enviado está en post, 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verifica si existe id, y si no lo define como nulo
        $id = $_POST['id'] ?? null;

        // Si no existe un id, se envia el mensaje de error
        if ($id === null || $id === '') {
            echo json_encode(['success' => false, 'message' => 'El ID es obligatorio para eliminar la venta.'], JSON_INVALID_UTF8_IGNORE);
            // Termina el proceso
            exit;
        }

        // Consulta que se le hace a la base de datos para borrar la fila segun el id
        $query = "DELETE FROM venta WHERE id = ?";
        // Conexion a la base de datos
        $stmt = $mysqli->prepare($query);

        // Si falla la conexion envia un mensaje
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $mysqli->error], JSON_INVALID_UTF8_IGNORE);
            exit;
        }

        // Define el tipo de dato y la variable id
        $stmt->bind_param("i", $id);

        // Ejecuta la consulta y verifica el estado, y si es correcta muestra una alerta con el mensaje de exito
        if ($stmt->execute()) {

            // Si quieres saber si realmente se eliminó alguna fila:
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Venta eliminada correctamente.'], JSON_INVALID_UTF8_IGNORE);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró una venta con ese ID.'], JSON_INVALID_UTF8_IGNORE);
            }

            // Registrar en log con el ID correcto
            require_once $_SERVER['DOCUMENT_ROOT'].'/php/inicio_sesion/seguridad/log_registros.php';
            app_log('delete','venta','Eliminación de venta', ['id' => $id]);

        // Si la consulta falla muestra la alerta con un mensaje de error
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la venta: ' . $stmt->error], JSON_INVALID_UTF8_IGNORE);
        }

        $stmt->close();
    }

/*   -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- */

//    "<?php"
        // Cierra la conexión a la base de datos
        // $mysqli->close();
//    ?">"

/*   ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- */

/*   ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa eliminar_filas .PHP -----------------------------------
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

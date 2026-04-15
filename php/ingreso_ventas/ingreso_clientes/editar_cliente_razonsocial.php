<?php
/*
Sitio Web Creado por ITred Spa.
Dirección: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/

/* ------------------------------------------------------------------------------------------------------------
   ------------------------------ INICIO ITred Spa editar_cliente_razonsocial .PHP ----------------------------
   ------------------------------------------------------------------------------------------------------------ */

// Forzamos que la respuesta sea en formato texto plano con codificación UTF-8
header('Content-Type: text/plain; charset=utf-8');

//-- ------------------------
//   -- INICIO CONEXIÓN BD --
//   ------------------------ --

     // Conectamos a la base de datos MySQL con credenciales específicas
     $mysqli = new mysqli("localhost", "trazabil_root", "Segma1@@", "trazabil_ingreso_ventas_bd_itred");
     // Establecemos el charset UTF-8 para evitar problemas con acentos y caracteres especiales
     $mysqli->set_charset("utf8");

//-- ---------------------
//   -- FIN CONEXIÓN BD --
//   --------------------- --


     // Obtención de datos enviados por POST; si no existen, se asigna string vacío
     $rut = $_POST['rut'] ?? '';
     $nombre = $_POST['nombre'] ?? '';

     // Validamos que ambos campos estén presentes; si falta alguno, se devuelve error y se detiene el script
     if (!$rut || !$nombre) {
          echo 'ERROR|RUT o nombre faltante';
          exit;
     }

// TITULO CONSULTA SQL

     // Consulta SQL preparada para actualizar el nombre del cliente según su RUT
     $stmt = $mysqli->prepare("UPDATE cliente SET nombre = ? WHERE rut = ?");
     // Si falla la preparación de la consulta, se devuelve error
     if (!$stmt) {
          echo 'ERROR|Error al preparar consulta';
          exit;
     }

// TITULO PARAMETROS

     // Asociamos los parámetros a la consulta preparada de nombre y rut pero como strings
     $stmt->bind_param("ss", $nombre, $rut);
     // Ejecutamos la consulta y devolvemos el resultado en formato texto plano
     if ($stmt->execute()) {
          echo 'OK|Cliente actualizado correctamente';
     } else {
          echo 'ERROR|Error al ejecutar actualización';
     }

     // Cerramos la consulta preparada para liberar memoria y evitar bloqueos
     $stmt->close();

     
/* -------------------------------
   -- INICIO CIERRE CONEXION BD --
   ------------------------------- */

// $mysqli->close();

/* ----------------------------
   -- FIN CIERRE CONEXION BD --
   ---------------------------- */

/* ------------------------------------------------------------------------------------------------------------
   --------------------------------- FIN ITred Spa editar_cliente_razonsocial .PHP ----------------------------
   ------------------------------------------------------------------------------------------------------------ */

/*
Sitio Web Creado por ITred Spa.
Dirección: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/
?>
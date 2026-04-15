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
     ------------------------------------- INICIO ITred Spa verificar_intento .PHP ------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!-- ------------------------
     -- INICIO CONEXION BD --
     ------------------------ -->

    <?php
        // establece la conexión a la base de datos trazabil_ingreso_ventas_bd con el usuario root
        $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");
    ?>

<!-- ---------------------
     -- FIN CONEXION BD --
     --------------------- -->

<?php

// TITULO FUNCIÓN REGISTRAR_INTENTO

    $mysqli->set_charset("utf8");
    // Función para registrar el intento fallido en la base de datos
    function registrar_intento($mysqli, $ip_address, $correo) {
        // Preparamos la consulta SQL para registrar el intento fallido
        $stmt = $mysqli->prepare("INSERT INTO login_intentos (ip_address, correo, hora_intento) VALUES (?, ?, NOW())");

        // Vinculamos los valores
        $stmt->bind_param("ss", $ip_address, $correo);

        if ($stmt->execute()) {
            $stmt->close();
            // Intento registrado: también escribir en log de actividad (warn)
            if (session_status() === PHP_SESSION_NONE) { @session_start(); }
            @include_once __DIR__ . '/log_registros.php';
            if (function_exists('app_log')) {
                app_log('warn', 'security', "Intento fallido de login", ['ip' => $ip_address, 'correo' => $correo, 'actor' => $_SESSION['username'] ?? '']);
            }
            return true;
        } else {
            echo "Error al registrar intento: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }


// TITULO FUNCIÓN VERIFICAR_INTENTOS

    // Función para verificar el número de intentos fallidos
    function verificar_intentos($mysqli) {
        // Obtenemos la dirección IP del usuario que está intentando iniciar sesión.
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Definimos el número máximo de intentos permitidos dentro del intervalo de tiempo.
        $max_intentos = 3;
        
        // Variable para almacenar el número de intentos fallidos.
        $intentos = 0;

        // Preparamos una declaración SQL para contar los intentos fallidos desde la misma IP en los últimos 15 minutos.
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS intentos FROM login_intentos WHERE ip_address = ? AND hora_intento > (NOW() - INTERVAL 15 MINUTE)");
        
        // Asignamos el valor de la dirección IP al parámetro de la consulta.
        $stmt->bind_param("s", $ip_address);
        
        // Ejecutamos la declaración SQL.
        $stmt->execute();
        
        // Asignamos el resultado (el número de intentos) a la variable $intentos.
        $stmt->bind_result($intentos);
        
        // Obtenemos el valor del resultado.
        $stmt->fetch();
        
        // Cerramos la declaración para liberar los recursos.
        $stmt->close();

        // Comprobamos si el número de intentos es menor que el máximo permitido. Retornamos true si hay menos intentos que el límite.
        return $intentos < $max_intentos;
    }
?>

<!-- -------------------------------
     -- INICIO CIERRE CONEXION BD --
     ------------------------------- -->

    <!-- <?php 
        // Cierra la conexión a la base de datos
        // $mysqli->close();
    ?> -->

<!-- ----------------------------
     -- FIN CIERRE CONEXION BD --
     ---------------------------- -->

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa verificar_intento .PHP --------------------------------
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

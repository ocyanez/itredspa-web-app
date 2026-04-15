<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<!-- ------------------------------------------------------------------------------------------------------------
     ------------------------------------- INICIO ITred Spa validarRut .PHP -------------------------------------
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

// TITULO FUNCIÓN PARA VALIDAR EL RUT

    $mysqli->set_charset("utf8");
    //Función para validar el rut
        function validarRUT($rut) {
            // Quitar puntos y guiones del RUT para normalizar el formato
            $rut = str_replace(['.', '-'], '', $rut);

            // Verificar si el RUT tiene una longitud válida (entre 8 y 9 caracteres)
            if (strlen($rut) < 8 || strlen($rut) > 9) {
                return false; // Si no es válido, retorna false
            }

            // Separar el cuerpo del RUT y el dígito verificador (último carácter)
            $cuerpo = substr($rut, 0, -1); // Cuerpo del RUT (sin el dígito verificador)
            $dv = substr($rut, -1); // Dígito verificador

            // Inicializar variables para calcular el dígito verificador
            $suma = 0;
            $multiplicador = 2;

            // Recorrer el cuerpo del RUT de derecha a izquierda para calcular la suma ponderada
            for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
                $suma += $cuerpo[$i] * $multiplicador; // Multiplica cada dígito por el multiplicador y lo suma
                $multiplicador = ($multiplicador == 7) ? 2 : $multiplicador + 1; // Cambia el multiplicador (2-7 cíclicamente)
            }

            // Calcular el dígito verificador esperado
            $dv_calculado = 11 - ($suma % 11); // Resto de la división entre 11
            $dv_calculado = ($dv_calculado == 11) ? '0' : (($dv_calculado == 10) ? 'K' : $dv_calculado); // Asignar 0 o K si es necesario

            // Comparar el dígito verificador calculado con el ingresado (ignorando mayúsculas/minúsculas)
            return strtoupper($dv) == $dv_calculado;
        }

// TITULO FUNCIÓN PARA FORMATEAR EL RUT EN FORMATO ESTÁNDAR

    //Función para darle formato al rut
        function formatearRUT($rut) {
            // Quitar puntos y guiones del RUT para trabajar con el número limpio
            $rut = str_replace(['.', '-'], '', $rut);

            // Separar el cuerpo del RUT (números) y el dígito verificador (último carácter)
            $cuerpo = substr($rut, 0, -1);
            $dv = substr($rut, -1); // Último dígito, que es el dígito verificador

            // Formatear el cuerpo del RUT con puntos cada tres dígitos y añadir el guión antes del dígito verificador
            $rut_formateado = number_format($cuerpo, 0, ',', '.') . '-' . strtoupper($dv);

            // Devolver el RUT formateado
            return $rut_formateado;
        }

?>

<!-- ------------------------------------------------------------------------------------------------------------
     -------------------------------------- FIN ITred Spa validarRut .PHP ---------------------------------------
     ------------------------------------------------------------------------------------------------------------ -->

<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<?php

/* Sitio Web Creado por ITred Spa.
 Direccion: Guido Reni #4190
 Pedro Aguirre Cerda - Santiago - Chile
 contacto@itred.cl o itred.spa@gmail.com
 https://www.itred.cl
 Creado, Programado y Diseñado por ITred Spa.
 BPPJ 
*/

/*  ---------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa procesar_factura.php --------------------------------------------
    --------------------------------------------------------------------------------------------------------------- */


// Activa la visualización de errores PHP para saber qué errores pueden ocurrir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function volverConMensaje($mensaje) {
    $msg = urlencode($mensaje);
    // Redirige a factura.php pasando el mensaje por URL
    header("Location: factura.php?mensaje=$msg");
    exit();
}

// Solo ejecuta la lógica si la solicitud es de tipo POST al realizar el envío de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Establece la conexión a la base de datos
    $mysqli = new mysqli("localhost","trazabil_root","Segma1@@","trazabil_ingreso_ventas_bd_itred");

    // Configura el charset para soportar caracteres especiales como acentos, ñ, etc.
    $mysqli->set_charset("utf8mb4");

// TITULO FUNCION NORMALIZAR

    // Función para normalizar el RUT quita puntos, espacios y agrega guión si falta
    function normalizarRut($rut) {
        // Elimina los puntos del RUT
        $rut = str_replace('.', '', $rut); 
        // Quita espacios en blanco al inicio y final, y convierte a mayúsculas
        $rut = strtoupper(trim($rut));  
        // Si ya tiene guión, lo devuelve tal cual sin cambios
        if (strpos($rut, '-') !== false) return $rut; 
        // Si el RUT tiene al menos 2 caracteres, lo formatea
        if (strlen($rut) >= 2) {
            // Parte numérica del RUT, todo menos el último carácter
            $cuerpo = substr($rut, 0, -1); 
            // Dígito verificador, el último carácter
            $dv = substr($rut, -1); 
            // Une el cuerpo y el dígito con un guión entre ellos
            return $cuerpo . '-' . $dv;
        }
        // Si el RUT no cumple los requisitos, lo devuelve como está
        return $rut;
    }

// TITULO CAPTURA DE DATOS

    // capturamos si es Editar o Crear 
    $accion = $_POST['accion'] ?? 'crear';
    $id_editar = intval($_POST['id_oculto_factura'] ?? 0);

    // Obtiene el nombre de la empresa desde el formulario, si no existe pone vacío
    $nombre = $_POST['nombreEmpresa'] ?? '';
    // Obtiene el giro de la empresa desde el formulario, si no existe pone vacío
    $giro = $_POST['giroEmpresa'] ?? '';
    // Obtiene el RUT y lo normaliza para que tenga el formato correcto
    $rut = normalizarRut($_POST['rutEmpresa'] ?? '');
    // Obtiene el número de factura y lo convierte a número entero
    $n_factura = intval($_POST['numeroFactura'] ?? 0);

    // Obtiene la cantidad total de productos que fueron enviados y lo convierte a número entero
    $totalProductos = intval($_POST['totalProductos'] ?? 0);
    // Inicializa la variable logo en null, será llenada si se sube un archivo
    $logo = null;

    // Verifica si se subió un archivo de logo y si no hay errores en la subida
    if (isset($_FILES['logoEmpresa']) && $_FILES['logoEmpresa']['error'] === UPLOAD_ERR_OK) {
        // Lee el contenido del archivo logo y lo guarda como binario
        $logo = file_get_contents($_FILES['logoEmpresa']['tmp_name']);
    }
    //  Usar volverConMensaje en error de logo 
    if ($logo && strlen($logo) > 2 * 1024 * 1024) {
        volverConMensaje('Error: El logo es demasiado grande (Máx 2MB)');
    }

    //  Usar volverConMensaje en validación de campos ---
    if (!$nombre || !$rut || !$n_factura || $totalProductos === 0) {
        // Esto te devolverá a la factura y te mostrará la alerta en lugar de una pantalla blanca
        volverConMensaje('Error: Faltan campos obligatorios o productos (Verifique que ingresó productos)');
    }

    // Crea un array vacío donde se guardarán todos los productos
    $productos = [];
    // Recorre cada producto enviado desde el formulario
    for ($i = 0; $i < $totalProductos; $i++) {
        // Agrega un nuevo producto al array con todos sus datos
        $productos[] = [
            // Obtiene el código del producto número $i
            'codigo' => $_POST["producto_${i}_codigo"] ?? '',
            // Obtiene la descripción del producto número $i
            'descripcion' => $_POST["producto_${i}_descripcion"] ?? '',
            // Obtiene la cantidad del producto número $i
            'cantidad' => $_POST["producto_${i}_cantidad"] ?? '0',
            // Obtiene el precio del producto número $i
            'precio' => $_POST["producto_${i}_precio"] ?? '0',
            // Obtiene el impuesto adicional del producto número $i
            'adicional' => $_POST["producto_${i}_adicional"] ?? '0',
            // Obtiene el descuento del producto número $i
            'descuento' => $_POST["producto_${i}_descuento"] ?? '0'
        ];
    }

// TITULO INSERCION DE PRODUCTOS

    // Recorre cada producto para guardarlo en la base de datos
    foreach ($productos as $p) {

        // Obtiene el código del producto y elimina espacios al inicio y final
        $codigo = trim($p['codigo'] ?? '');
        // Obtiene la descripción del producto y elimina espacios al inicio y final
        $descripcion = trim($p['descripcion'] ?? '');
        // Obtiene la cantidad del producto y la convierte a número entero
        $cantidad = intval($p['cantidad']);
        // Obtiene el precio del producto, elimina los puntos de miles y lo convierte a número entero
        $precio = intval(str_replace('.', '', $p['precio']));
        // Obtiene el porcentaje de impuesto adicional y lo convierte a número entero
        $impacto = intval($p['adicional']);
        // Obtiene el porcentaje de descuento y lo convierte a número entero
        $descuento = intval($p['descuento']);

        // Validamos que tenga código y descripción
        if (!empty($codigo) && !empty($descripcion)) {
            
            //  Verificamos si existe usando el nombre real de tu columna: 'sku'
            $sqlCheck = "SELECT id FROM producto WHERE sku = ? LIMIT 1";
            $stmtCheck = $mysqli->prepare($sqlCheck);
            // Verificamos si hay errores en la preparación
            if ($stmtCheck) {
                $stmtCheck->bind_param("s", $codigo);
                $stmtCheck->execute();
                $stmtCheck->store_result();
                
                // Si no existe (0 filas)
                if ($stmtCheck->num_rows === 0) {
                    
                    // Insertamos usando las columnas reales: 'sku' y 'producto'
                    // Si no tienes columna de precio en la tabla producto, borra ", precio" y el ", ?" del final.
                    $sqlCreate = "INSERT INTO producto (sku, producto) VALUES (?, ?)";
                    $stmtCreate = $mysqli->prepare($sqlCreate);
                    // Verificamos si hay errores en la preparación
                    if ($stmtCreate) {
                        // "ss" -> string, string
                        $stmtCreate->bind_param("ss", $codigo, $descripcion);
                        $stmtCreate->execute();
                        $stmtCreate->close();
                    }
                }
                $stmtCheck->close();
            }
        }

        // Calcula el valor multiplicando cantidad por precio
        $valor = $cantidad * $precio;

        // Calcula los totales para este producto
        // El neto es el valor calculado
        $neto = $valor;
        // El IVA es el 19% del neto, redondeado a número entero
        $iva = round($neto * 0.19);
        // El impuesto adicional se calcula como el porcentaje del neto
        $impuesto = round($neto * ($impacto / 100));
        // El total es la suma del neto más el IVA más el impuesto adicional
        $total = $neto + $iva + $impuesto;
        // Si la acción es editar y se proporciona un ID válido
        if ($accion === 'editar' && $id_editar > 0) {
           
            // Crea la consulta SQL para actualizar la factura en la base de datos
            $sql = "UPDATE factura SET 
                    nombre_empresa=?, giro_empresa=?, rut_empresa=?, n_factura=?,
                    codigo_producto=?, descripcion_producto=?, cantidad_producto=?,
                    precio_producto=?, impacto_producto=?, descuento_producto=?, valor_producto=?,
                    neto_producto=?, iva_producto=?, impuesto_producto=?, total_producto=?
                    WHERE id=?";
            // Prepara la consulta SQL para que sea segura contra inyecciones
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) { volverConMensaje('Error SQL Update: ' . $mysqli->error); }
            // Vinculamos los parámetros (sin el logo todavía)
            $stmt->bind_param("ssssssiiisiiiiii", 
                $nombre, $giro, $rut, $n_factura,
                $codigo, $descripcion, $cantidad,
                $precio, $impacto, $descuento, $valor,
                $neto, $iva, $impuesto, $total, $id_editar
            );
            $stmt->execute();
            $stmt->close();

            // Actualizamos el logo SOLO si el usuario subió uno nuevo
            if ($logo !== null) {
                $sqlLogo = "UPDATE factura SET logo_empresa=? WHERE id=?";
                $stmtLogo = $mysqli->prepare($sqlLogo);
                $null = NULL;
                $stmtLogo->bind_param("bi", $null, $id_editar);
                $stmtLogo->send_long_data(0, $logo);
                $stmtLogo->execute();
                $stmtLogo->close();
            }

        } else {

        // Crea la consulta SQL para insertar la factura en la base de datos
        $sql = "INSERT INTO factura (
            nombre_empresa, giro_empresa, rut_empresa, logo_empresa, n_factura,
            codigo_producto, descripcion_producto, cantidad_producto,
            precio_producto, impacto_producto, descuento_producto, valor_producto,
            neto_producto, iva_producto, impuesto_producto, total_producto
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Prepara la consulta SQL para que sea segura contra inyecciones
        $stmt = $mysqli->prepare($sql);
        // Verifica si la preparación de la consulta fue exitosa
        if (!$stmt) { volverConMensaje('Error SQL Insert: ' . $mysqli->error); }

        // Crea una variable null para usarla en el logo
        $null = NULL;

        // Vincula los valores a los puntos de interrogación de la consulta SQL
        // s = cadena de texto, i = número entero, b = datos binarios
        $stmt->bind_param(
            "sssbsssiiisiiiii",
            $nombre, $giro, $rut, $null, $n_factura,
            $codigo, $descripcion, $cantidad,
            $precio, $impacto, $descuento, $valor,
            $neto, $iva, $impuesto, $total
        );

        // Si existe un logo, lo envía como dato binario a la posición 3 de la consulta
        if ($logo !== null) {
            $stmt->send_long_data(3, $logo);
        }

        // Ejecuta la consulta preparada en la base de datos
        $stmt->execute();
        // Verifica si hubo algún error durante la ejecución
        if ($stmt->error) { volverConMensaje('Error al guardar: ' . $stmt->error); }
        // Cierra la declaración preparada
        $stmt->close();
        }
    }

    // Mensaje Final (Éxito)
    if ($accion === 'editar') {
        // mensaje de exito al editar
        volverConMensaje('Factura editada correctamente');
    } else {
        // mensaje de exito al crear
        volverConMensaje('Factura guardada exitosamente');
    }

    } else {
        // Si entran sin POST
        header("Location: factura.php");
        exit();
    }



/* Sitio Web Creado por ITred Spa.
 Direccion: Guido Reni #4190
 Pedro Aguirre Cerda - Santiago - Chile
 contacto@itred.cl o itred.spa@gmail.com
 https://www.itred.cl
 Creado, Programado y Diseñado por ITred Spa.
 BPPJ 
*/

/*  ---------------------------------------------------------------------------------------------------------------
    ---------------------------------------- FIN ITred Spa procesar_factura.php --------------------------------------------
    --------------------------------------------------------------------------------------------------------------- */


?>
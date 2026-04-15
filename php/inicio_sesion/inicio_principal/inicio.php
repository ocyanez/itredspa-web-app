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
     ------------------------------------- INICIO ITred Spa inicio .PHP -----------------------------------------
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

<!-- esto nos sirve para obtener la ultima vez que se actualiza el proyecto -->
<?php

// Creamos una función que busca cuál fue el último archivo modificado en todo el proyecto
function obtener_ultima_modificacion_proyecto($directorio_raiz) {
    
    $ultima_modificacion = 0;      // Aquí guardaremos la fecha del archivo más reciente (empieza en cero)
    $archivo_mas_reciente = '';    // Aquí guardaremos el nombre del archivo más reciente (empieza vacío)
    
    // Creamos un "buscador" que recorrerá todas las carpetas y subcarpetas del proyecto
    $iterador = new RecursiveIteratorIterator(
        // Le decimos que empiece desde la carpeta raíz y que ignore los puntos (. y ..)
        new RecursiveDirectoryIterator($directorio_raiz, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST  // Primero revisa la carpeta, luego su contenido
    );
    
    // Recorremos uno por uno todos los archivos encontrados
    foreach ($iterador as $archivo) {
        
        // Verificamos si lo que encontramos es un archivo (no una carpeta)
        if ($archivo->isFile()) {
            
            $nombre = $archivo->getFilename();  // Obtenemos solo el nombre del archivo
            
            // Ignoramos archivos que se actualizan automáticamente y no nos interesan
            if ($nombre === 'error_log' ||           // Archivo de errores del servidor
                $nombre === '.htaccess' ||           // Archivo de configuración de Apache
                strpos($nombre, '.log') !== false || // Cualquier archivo de registro (.log)
                strpos($nombre, '.tmp') !== false || // Cualquier archivo temporal (.tmp)
                strpos($nombre, '.cache') !== false) { // Cualquier archivo de caché (.cache)
                continue;  // Saltamos este archivo y seguimos con el siguiente
            }
            
            $tiempo_archivo = $archivo->getMTime();  // Obtenemos la fecha de modificación del archivo
            
            // Si este archivo es más reciente que el último que guardamos...
            if ($tiempo_archivo > $ultima_modificacion) {
                $ultima_modificacion = $tiempo_archivo;        // Actualizamos la fecha más reciente
                $archivo_mas_reciente = $archivo->getPathname(); // Guardamos la ruta completa del archivo
            }
        }
    }
    
    // Devolvemos los resultados: la fecha y el nombre del archivo más reciente
    return [
        'timestamp' => $ultima_modificacion,  // La fecha en formato de computadora (timestamp)
        'archivo' => $archivo_mas_reciente    // La ruta completa del archivo
    ];
}

$raiz_proyecto = dirname(__FILE__, 4);  // Obtenemos la carpeta raíz del proyecto (4 niveles arriba de este archivo)

$resultado = obtener_ultima_modificacion_proyecto($raiz_proyecto);  // Ejecutamos la función y guardamos el resultado

$ultima_actualizacion_proyecto = $resultado['timestamp'];  // Extraemos la fecha de la última modificación
$archivo_modificado = $resultado['archivo'];               // Extraemos el nombre del archivo modificado

// Mostramos información de prueba (solo visible en el código fuente de la página)
echo "<!-- DEBUG: Archivo más reciente = " . $archivo_modificado . " -->";                          // Muestra qué archivo se modificó (en consola)
echo "<!-- DEBUG: Fecha = " . date('d/m/Y H:i:s', $ultima_actualizacion_proyecto) . " -->";         // Muestra cuándo se modificó
?>





<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/inicio_sesion/inicio_principal/inicio.css?v=<?= time() ?>">
    <script src="/js/inicio_sesion/inicio_principal/inicio.js?v=<?= time() ?>" defer></script>
    <title>Página de Inicio - Usuario</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/imagenes/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/imagenes/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/imagenes/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/imagenes/favicon/web-app-manifest-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/imagenes/favicon/web-app-manifest-512x512.png">
    <link rel="shortcut icon" href="/imagenes/favicon/favicon.ico">
    <link rel="manifest" href="/imagenes/favicon/site.webmanifest">
</head>

    <!-- TITULO BODY -->

    <body>
        <?php echo $debug_output; ?>

        <?php
            $mysqli->set_charset("utf8");
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

    // TITULO VERIFICAR SI EL USUARIO HA INICIADO SESIÓN
            
            // Construir la URL completa
            $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            // Verificar si la URL contiene 'superadmin.php'
            $esSuperadmin = strpos($currentUrl, 'superadmin.php') !== false;
            // Verifica si el usuario ha iniciado sesión
            if (!$esSuperadmin && !isset($_SESSION['correo'])) {
            // Si el usuario no ha iniciado sesión, redirige a la página de inicio
            $archivo = '/ingreso_ventas.php';
            header("Location: ".$archivo);
            exit();
        }

    // TITULO OBTENER DATOS DEL USUARIO DESDE LA SESIÓN

            // Obtener los datos del usuario desde la sesión
            $nombre = $_SESSION['nombre'] ?? null;
            $apellido = $_SESSION['apellido'] ?? null;
            $username = $_SESSION['username'] ?? null;
            $correo = $_SESSION['correo'] ?? null;
            $rol = $_SESSION['rol'] ?? null;
            
        
        ?>
    
    <!-- TITULO DASHBOARD -->

        <!-- Dashboard Principal -->
        <div class="dashboard">
            <!-- Este es el contenedor principal que divide la parte superior en dos secciones -->
            <div class="dashboard-top-container">
                
                <!-- Lado izquierdo: Logo, título y versión -->
                <!-- Sección izquierda donde mostramos la bienvenida y el logo de la empresa -->
                <div class="dashboard-info-left">
                    <h1 class="welcome-title">¡Bienvenidos al Sistema de Trazabilidad de SEGMA!</h1> <!-- Título principal de bienvenida -->
                    <img src="<?= $logoUrl ?>" alt="Logo SEGMA" class="logo"> <!-- Imagen del logo, la ruta viene de una variable PHP -->
                    <h2 class="version-text">Versión 2</h2> <!-- Muestra qué versión del sistema estamos usando -->
                </div>
                
                <!-- Lado derecho: Tarjetas de estadísticas -->
                <!-- Sección derecha donde mostramos las tarjetas con números importantes -->
                <div class="dashboard-stats-right">
                    <?php
                    // ---contamos cuántos clientes hay en el sistema

                    $total_clientes = 0;  // Empezamos en cero por si no hay clientes
                    
                    // Preguntamos a la base de datos: ¿cuántos clientes hay en total?
                    $total_clientes_result = $mysqli->query("SELECT COUNT(*) as total FROM cliente");
                    
                    // Si la consulta funcionó correctamente...
                    if ($total_clientes_result) {
                        $fila_clientes = $total_clientes_result->fetch_assoc();  // Obtenemos el resultado
                        $total_clientes = (int)$fila_clientes['total'];          // Guardamos el número como entero
                    }

                    // --- contamos cuántas facturas hay en el sistema

                    $total_facturas = 0;  // Empezamos en cero por si no hay facturas
                    
                    // Preguntamos a la base de datos: ¿cuántas facturas hay en total?
                    $total_facturas_result = $mysqli->query("SELECT COUNT(*) as total FROM factura");
                    
                    // Si la consulta funcionó correctamente...
                    if ($total_facturas_result) {
                        $fila_facturas = $total_facturas_result->fetch_assoc();  // Obtenemos el resultado
                        $total_facturas = (int)$fila_facturas['total'];          // Guardamos el número como entero
                    }
                    ?>

                    <!-- tarjeta 1: clientes registrados -->
                    <div class="stat-card">
                        <div class="stat-icon">👥</div> <!-- Icono de personas -->
                        <div class="stat-info">
                            <h3><?php echo number_format($total_clientes); ?></h3> <!-- Muestra el total de clientes con formato (ej: 1,234) -->
                            <p>Clientes Registrados</p> <!-- Texto que explica qué significa el número -->
                        </div>
                    </div>
                    
                    <!-- tarjeta 2: facturas registradas -->
                    <div class="stat-card">
                        <div class="stat-icon">🗂️</div> <!-- Icono de carpeta -->
                        <div class="stat-info">
                            <h3><?php echo number_format($total_facturas); ?></h3> <!-- Muestra el total de facturas con formato -->
                            <p>Facturas Registradas</p> <!-- Texto que explica qué significa el número -->
                        </div>
                    </div>

                    <!-- tarjeta 3: año actual -->
                    <div class="stat-card">
                        <div class="stat-icon">📊</div> <!-- Icono de gráfico -->
                        <div class="stat-info">
                            <h3><?php echo date('Y'); ?></h3> <!-- Muestra el año actual automáticamente (ej: 2025) -->
                            <p>Año Actual</p> <!-- Texto que explica qué significa el número -->
                        </div>
                    </div>
                    
                    <!-- tarjeta 4: facturas con problemas -->
                    <div class="stat-card <?= $hay_diferencias ? 'stat-card-alerta' : '' ?>">
                        <div class="stat-icon">⚠️</div> <!-- Icono de advertencia -->
                        <div class="stat-info">
                            <h3><?php echo number_format($total_facturas_malas); ?></h3> <!-- Muestra cuántas facturas tienen errores -->
                            <p>Facturas con Problemas</p> <!-- Texto que explica qué significa el número -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sección de Gráficos -->
    <div class="graficos-section">
    <h2>📊 Estadísticas y Gráficos</h2>
    
<!-- Contenedor tipo cuadrícula que organiza todos los gráficos -->
    <div class="graficos-grid">
        
        <!-- Gráfico 1: Top 10 Clientes con más ventas -->
        <div class="grafico-container">
            <h3>🏆 Top 10 Clientes con Más Ventas</h3> <!-- Título del gráfico con icono de trofeo -->
            <div class="grafico-barras" id="grafico_clientes"> <!-- Contenedor donde se dibujarán las barras -->
                <?php
                // Consulta SQL que busca los 10 clientes que más han comprado
                $sql_top_clientes = "
                    SELECT 
                        c.nombre,                        -- Nombre del cliente
                        c.rut,                           -- RUT del cliente
                        COUNT(v.id) as total_ventas,     -- Cuenta cuántas ventas tiene
                        SUM(v.cantidad) as total_productos -- Suma todos los productos que compró
                    FROM cliente c
                    INNER JOIN venta v ON c.rut = v.rut  -- Une clientes con sus ventas usando el RUT
                    GROUP BY c.rut, c.nombre             -- Agrupa por cada cliente
                    ORDER BY total_ventas DESC           -- Ordena de mayor a menor ventas
                    LIMIT 10                             -- Solo trae los primeros 10
                ";
                $resultado_clientes = $mysqli->query($sql_top_clientes); // Ejecuta la consulta
                
                $max_ventas = 0;       // Guardará el valor más alto para calcular proporciones
                $datos_clientes = [];  // Aquí almacenaremos todos los clientes encontrados
                
                // Si la consulta funcionó y hay resultados...
                if ($resultado_clientes && $resultado_clientes->num_rows > 0) {
                    
                    // Recorremos cada cliente encontrado
                    while ($fila = $resultado_clientes->fetch_assoc()) {
                        $datos_clientes[] = $fila;  // Guardamos el cliente en el arreglo
                        
                        // Si este cliente tiene más ventas que el máximo actual, actualizamos
                        if ($fila['total_ventas'] > $max_ventas) {
                            $max_ventas = $fila['total_ventas'];
                        }
                    }
                    
                    // Ahora dibujamos una barra por cada cliente
                    foreach ($datos_clientes as $index => $cliente) {
                        
                        // Calculamos qué tan larga será la barra (máximo 75% del ancho)
                        $porcentaje = ($max_ventas > 0) ? ($cliente['total_ventas'] / $max_ventas) * 75 : 0;
                        
                        // Cada barra tendrá un color diferente (azules que van cambiando)
                        $color = "hsl(" . (200 - ($index * 15)) . ", 70%, 50%)";
                        ?>
                        
                        <!-- Una fila de barra para este cliente -->
                        <div class="barra-item">
                            <!-- Nombre del cliente (máximo 20 caracteres, si es más largo pone ...) -->
                            <span class="barra-label" title="<?= htmlspecialchars($cliente['nombre']) ?>">
                                <?= htmlspecialchars(substr($cliente['nombre'], 0, 20)) ?><?= strlen($cliente['nombre']) > 20 ? '...' : '' ?>
                            </span>
                            <div class="barra-contenedor"> <!-- Contenedor de la barra -->
                                <!-- La barra coloreada con su ancho y color calculados -->
                                <div class="barra-relleno" style="width: <?= $porcentaje ?>%; background-color: <?= $color ?>;">
                                    <span class="barra-valor"><?= number_format($cliente['total_ventas']) ?></span> <!-- Número de ventas -->
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // Si no hay datos, mostramos un mensaje
                    echo '<p class="sin-datos">No hay datos de ventas disponibles</p>';
                }
                ?>
            </div>
        </div>

        <!-- Gráfico 2: Top 10 Productos más comprados -->
        <div class="grafico-container">
            <h3>📦 Top 10 Productos Más Comprados</h3> <!-- Título con icono de caja -->
            <div class="grafico-barras" id="grafico_productos"> <!-- Contenedor de barras de productos -->
                <?php
                // Consulta SQL que busca los 10 productos más vendidos
                $sql_top_productos = "
                    SELECT 
                        sku,                             -- Código único del producto
                        producto,                        -- Nombre del producto
                        SUM(cantidad) as total_cantidad  -- Suma todas las unidades vendidas
                    FROM venta
                    WHERE producto IS NOT NULL AND producto != ''  -- Solo productos con nombre válido
                    GROUP BY sku, producto               -- Agrupa por cada producto
                    ORDER BY total_cantidad DESC         -- Ordena de más vendido a menos
                    LIMIT 10                             -- Solo los primeros 10
                ";
                $resultado_productos = $mysqli->query($sql_top_productos); // Ejecuta la consulta
                
                $max_productos = 0;     // Guardará la cantidad más alta
                $datos_productos = [];  // Aquí almacenaremos todos los productos
                
                // Si la consulta funcionó y hay resultados...
                if ($resultado_productos && $resultado_productos->num_rows > 0) {
                    
                    // Recorremos cada producto encontrado
                    while ($fila = $resultado_productos->fetch_assoc()) {
                        $datos_productos[] = $fila;  // Guardamos el producto
                        
                        // Actualizamos el máximo si este producto vendió más
                        if ($fila['total_cantidad'] > $max_productos) {
                            $max_productos = $fila['total_cantidad'];
                        }
                    }
                    
                    // Dibujamos una barra por cada producto
                    foreach ($datos_productos as $index => $producto) {
                        
                        // Calculamos el ancho de la barra proporcionalmente
                        $porcentaje = ($max_productos > 0) ? ($producto['total_cantidad'] / $max_productos) * 75 : 0;
                        
                        // Colores verdes que van cambiando para cada producto
                        $color = "hsl(" . (120 + ($index * 20)) . ", 60%, 45%)";
                        ?>
                        
                        <!-- Fila de barra para este producto -->
                        <div class="barra-item">
                            <!-- Nombre del producto (máximo 20 caracteres) -->
                            <span class="barra-label" title="<?= htmlspecialchars($producto['producto']) ?>">
                                <?= htmlspecialchars(substr($producto['producto'], 0, 20)) ?><?= strlen($producto['producto']) > 20 ? '...' : '' ?>
                            </span>
                            <div class="barra-contenedor">
                                <!-- Barra coloreada con la cantidad vendida -->
                                <div class="barra-relleno" style="width: <?= $porcentaje ?>%; background-color: <?= $color ?>;">
                                    <span class="barra-valor"><?= number_format($producto['total_cantidad']) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // Mensaje si no hay productos
                    echo '<p class="sin-datos">No hay datos de productos disponibles</p>';
                }
                ?>
            </div>
        </div>

        <!-- Gráfico 3: Ventas por Mes (últimos 12 meses) -->
        <div class="grafico-container grafico-ancho"> <!-- Este gráfico ocupa más espacio (ancho) -->
            <h3>📅 Ventas por Mes (Últimos 12 Meses)</h3> <!-- Título con icono de calendario -->
            <div class="grafico-lineas" id="grafico_meses"> <!-- Contenedor del gráfico mensual -->
                <?php
                // Consulta SQL que cuenta las ventas de cada mes en el último año
                $sql_ventas_mes = "
                    SELECT 
                        DATE_FORMAT(fecha_despacho, '%Y-%m') as mes,  -- Formato año-mes para ordenar
                        CONCAT(
                            CASE MONTH(fecha_despacho)                -- Convierte el número de mes a nombre corto
                                WHEN 1 THEN 'Ene'
                                WHEN 2 THEN 'Feb'
                                WHEN 3 THEN 'Mar'
                                WHEN 4 THEN 'Abr'
                                WHEN 5 THEN 'May'
                                WHEN 6 THEN 'Jun'
                                WHEN 7 THEN 'Jul'
                                WHEN 8 THEN 'Ago'
                                WHEN 9 THEN 'Sep'
                                WHEN 10 THEN 'Oct'
                                WHEN 11 THEN 'Nov'
                                WHEN 12 THEN 'Dic'
                            END,
                            ' ',
                            YEAR(fecha_despacho)                      -- Añade el año al nombre
                        ) as mes_nombre,
                        COUNT(*) as total_ventas,                     -- Cuenta las ventas del mes
                        SUM(cantidad) as total_productos              -- Suma los productos del mes
                    FROM venta
                    WHERE fecha_despacho >= DATE_SUB(NOW(), INTERVAL 12 MONTH)  -- Solo últimos 12 meses
                    GROUP BY DATE_FORMAT(fecha_despacho, '%Y-%m')     -- Agrupa por mes
                    ORDER BY mes ASC                                  -- Ordena del más antiguo al más reciente
                ";
                $resultado_meses = $mysqli->query($sql_ventas_mes); // Ejecuta la consulta
                
                $max_mes = 0;      // Guardará el mes con más ventas
                $datos_meses = []; // Almacena todos los meses
                
                // Si hay datos de ventas por mes...
                if ($resultado_meses && $resultado_meses->num_rows > 0) {
                    
                    // Recorremos cada mes
                    while ($fila = $resultado_meses->fetch_assoc()) {
                        $datos_meses[] = $fila;  // Guardamos el mes
                        
                        // Buscamos cuál es el mes con más ventas
                        if ($fila['total_ventas'] > $max_mes) {
                            $max_mes = $fila['total_ventas'];
                        }
                    }
                    ?>
                    
                    <!-- Contenedor de barras verticales (columnas) -->
                    <div class="grafico-barras-vertical">
                        <?php foreach ($datos_meses as $mes):  // Recorremos cada mes
                            // Calculamos la altura de la barra (proporción del 100%) es decir que la barra se adapta segun la cantidad
                            $porcentaje = ($max_mes > 0) ? ($mes['total_ventas'] / $max_mes) * 100 : 0;
                        ?>
                            <!-- Una columna vertical para este mes -->
                            <div class="barra-vertical-item">
                                <div class="barra-vertical-contenedor">
                                    <!-- La barra que crece hacia arriba según las ventas -->
                                    <div class="barra-vertical-relleno" style="height: <?= $porcentaje ?>%;">
                                        <span class="barra-vertical-valor"><?= $mes['total_ventas'] ?></span> <!-- Número de ventas -->
                                    </div>
                                </div>
                                <span class="barra-vertical-label"><?= $mes['mes_nombre'] ?></span> <!-- Nombre del mes debajo -->
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php
                } else {
                    // Mensaje si no hay datos
                    echo '<p class="sin-datos">No hay datos de ventas por mes disponibles</p>';
                }
                ?>
            </div>
        </div>

        <!-- Gráfico 4: Top 5 Empresas por Monto Facturado -->
        <div class="grafico-container">
            <h3>💰 Top 5 Empresas por Monto Facturado</h3> <!-- Título con icono de dinero -->
            <div class="grafico-dona" id="grafico_empresas"> <!-- Contenedor del gráfico de dona -->
                <?php
                // Consulta SQL que busca las 5 empresas que más dinero han facturado
                $sql_empresas = "
                    SELECT 
                        nombre_empresa,                              -- Nombre de la empresa
                        SUM(total_producto) as total_facturado,      -- Suma todo el dinero facturado
                        COUNT(DISTINCT n_factura) as num_facturas    -- Cuenta cuántas facturas tiene
                    FROM factura
                    GROUP BY nombre_empresa                          -- Agrupa por empresa
                    ORDER BY total_facturado DESC                    -- Ordena de mayor a menor
                    LIMIT 5                                          -- Solo las primeras 5
                ";
                $resultado_empresas = $mysqli->query($sql_empresas); // Ejecuta la consulta
                
                $total_general = 0;    // Suma de todo el dinero de las 5 empresas
                $datos_empresas = [];  // Almacena las empresas
                
                // Colores para cada porción de la dona (azul, rojo, verde, amarillo, morado)
                $colores_empresas = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6'];
                
                // Si hay datos de empresas...
                if ($resultado_empresas && $resultado_empresas->num_rows > 0) {
                    
                    // Recorremos cada empresa
                    while ($fila = $resultado_empresas->fetch_assoc()) {
                        $datos_empresas[] = $fila;                     // Guardamos la empresa
                        $total_general += $fila['total_facturado'];    // Sumamos al total general
                    }
                    ?>
                    
                    <!-- Contenedor principal de la dona -->
                    <div class="dona-container">
                        <div class="dona-grafico"> <!-- Donde se dibuja el círculo -->
                            <?php 
                            $acumulado = 0;   // Porcentaje acumulado para saber dónde empieza cada color
                            $gradiente = "";  // Aquí armamos el código del degradado circular
                            
                            // Construimos el gradiente color por color
                            foreach ($datos_empresas as $index => $empresa) {
                                // Calculamos qué porcentaje del total representa esta empresa
                                $porcentaje = ($total_general > 0) ? ($empresa['total_facturado'] / $total_general) * 100 : 0;
                                $fin = $acumulado + $porcentaje;  // Dónde termina este color
                                
                                // Añadimos este color al gradiente (desde X% hasta Y%)
                                $gradiente .= $colores_empresas[$index] . " " . $acumulado . "% " . $fin . "%";
                                
                                // Si no es el último, añadimos una coma para separar
                                if ($index < count($datos_empresas) - 1) $gradiente .= ", ";
                                
                                $acumulado = $fin;  // El siguiente color empieza donde este termina
                            }
                            ?>
                            
                            <!-- El círculo de colores usando gradiente cónico -->
                            <div class="dona-circulo" style="background: conic-gradient(<?= $gradiente ?>);"></div>
                            
                            <!-- Centro blanco de la dona con el total -->
                            <div class="dona-centro">
                                <span class="dona-total">$<?= number_format($total_general, 0, ',', '.') ?></span> <!-- Monto total -->
                                <span class="dona-texto">Total</span> <!-- Texto "Total" -->
                            </div>
                        </div>
                        
                        <!-- Leyenda que explica cada color -->
                        <div class="dona-leyenda">
                            <?php foreach ($datos_empresas as $index => $empresa): 
                                // Calculamos el porcentaje de esta empresa
                                $porcentaje = ($total_general > 0) ? ($empresa['total_facturado'] / $total_general) * 100 : 0;
                            ?>
                                <!-- Una fila de la leyenda -->
                                <div class="leyenda-item" data-porcentaje="<?= number_format($porcentaje, 1) ?>%">
                                    <span class="leyenda-color" style="background-color: <?= $colores_empresas[$index] ?>;"></span> <!-- Cuadrito de color -->
                                    <span class="leyenda-nombre"><?= htmlspecialchars(substr($empresa['nombre_empresa'], 0, 15)) ?></span> <!-- Nombre corto -->
                                    <span class="leyenda-valor">$<?= number_format($empresa['total_facturado'], 0, ',', '.') ?></span> <!-- Monto -->
                                    <span class="leyenda-porcentaje"><?= number_format($porcentaje, 1) ?>%</span> <!-- Porcentaje -->
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php
                } else {
                    // Mensaje si no hay empresas
                    echo '<p class="sin-datos">No hay datos de empresas disponibles</p>';
                }
                ?>
            </div>
        </div>

        <!-- Gráfico 5: Resumen General -->
        <div class="grafico-container">
            <h3>📈 Resumen General</h3> <!-- Título con icono de gráfico -->
            <div class="resumen-cards"> <!-- Contenedor de las tarjetas de resumen -->
                <?php
                // Consulta el monto total de todas las facturas
                $sql_total_monto = "SELECT SUM(total_producto) as total FROM factura";
                $res = $mysqli->query($sql_total_monto);  // Ejecuta la consulta
                // Si hay resultado lo guarda, si no pone 0
                $total_monto = ($res && $fila = $res->fetch_assoc()) ? (int)$fila['total'] : 0;

                // Consulta cuántos lotes diferentes hay registrados
                $sql_lotes = "SELECT COUNT(DISTINCT lote) as total FROM venta WHERE lote IS NOT NULL";
                $res = $mysqli->query($sql_lotes);  // Ejecuta la consulta
                $total_lotes = ($res && $fila = $res->fetch_assoc()) ? (int)$fila['total'] : 0;

                // Cuenta cuántos clientes han comprado este mes
                $sql_clientes_activos = "
                    SELECT COUNT(DISTINCT v.rut) as total   -- Cuenta clientes únicos
                    FROM venta v
                    WHERE MONTH(v.fecha_despacho) = MONTH(NOW())   -- Del mes actual
                    AND YEAR(v.fecha_despacho) = YEAR(NOW())       -- Del año actual
                ";
                $res = $mysqli->query($sql_clientes_activos);  // Ejecuta la consulta
                $clientes_activos = ($res && $fila = $res->fetch_assoc()) ? (int)$fila['total'] : 0;
                
                // Busca el cliente que más dinero ha gastado este mes
                $sql_cliente_top = "
                    SELECT 
                        c.nombre,                                -- Nombre del cliente
                        SUM(f.total_producto) as total_facturado -- Suma de sus facturas
                    FROM cliente c
                    INNER JOIN venta v ON c.rut = v.rut          -- Une cliente con sus ventas
                    INNER JOIN factura f ON v.numero_fact = f.n_factura  -- Une con facturas
                    WHERE MONTH(v.fecha_despacho) = MONTH(NOW()) -- Solo este mes
                    AND YEAR(v.fecha_despacho) = YEAR(NOW())     -- Solo este año
                    GROUP BY c.rut, c.nombre                     -- Agrupa por cliente
                    ORDER BY total_facturado DESC                -- El que más gastó primero
                    LIMIT 1                                      -- Solo el número 1
                ";
                $res = $mysqli->query($sql_cliente_top);  // Ejecuta la consulta
                
                $cliente_top_nombre = "Sin datos";  // Valor por defecto si no hay datos
                $cliente_top_monto = 0;
                
                // Si encontramos al cliente top, guardamos sus datos
                if ($res && $fila = $res->fetch_assoc()) {
                    $cliente_top_nombre = $fila['nombre'];
                    $cliente_top_monto = (int)$fila['total_facturado'];
                }
                ?>
                
                <!-- Tarjeta 1: Monto total facturado -->
                <div class="resumen-card">
                    <div class="resumen-icono">💵</div> <!-- Icono de billete -->
                    <div class="resumen-numero">$<?= number_format($total_monto, 0, ',', '.') ?></div> <!-- Monto formateado -->
                    <div class="resumen-texto">Monto Facturado</div> <!-- Descripción -->
                </div>
                
                <!-- Tarjeta 2: Total de lotes -->
                <div class="resumen-card">
                    <div class="resumen-icono">🏷️</div> <!-- Icono de etiqueta -->
                    <div class="resumen-numero"><?= number_format($total_lotes) ?></div> <!-- Cantidad de lotes -->
                    <div class="resumen-texto">Lotes Registrados</div> <!-- Descripción -->
                </div>
                
                <!-- Tarjeta 3: Clientes activos del mes -->
                <div class="resumen-card">
                    <div class="resumen-icono">👥</div> <!-- Icono de personas -->
                    <div class="resumen-numero"><?= number_format($clientes_activos) ?></div> <!-- Cantidad de clientes -->
                    <div class="resumen-texto">Clientes Activos del Mes</div> <!-- Descripción -->
                </div>
                
                <!-- Tarjeta 4: Cliente que más ha comprado este mes -->
                <div class="resumen-card">
                    <div class="resumen-icono">🏆</div> <!-- Icono de trofeo -->
                    <div class="resumen-numero"><?= htmlspecialchars($cliente_top_nombre) ?></div> <!-- Nombre del cliente -->
                    <div class="resumen-texto">Cliente Top del Mes<br><small>$<?= number_format($cliente_top_monto, 0, ',', '.') ?></small></div> <!-- Título y monto -->
                </div>
            </div>
        </div>

    </div>

            <!-- Información del Sistema -->
            <div class="system-info">
                <!-- Fecha y hora de la última actualización -->
                <h2>Información del Sistema</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Última Actualización:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', $ultima_actualizacion_proyecto); ?></span>
                    </div>

                    <!-- Versión actual del sistema -->
                    <div class="info-item">
                        <span class="info-label">Versión del Sistema:</span>
                        <span class="info-value">SEGMA v2.0</span>
                    </div>

                    <!-- Estado operativo del sistema -->
                    <div class="info-item">
                        <span class="info-label">Estado del Sistema:</span>
                        <span class="info-value status-active">🟢 Activo</span>
                    </div>
                </div>
            </div>

            <!-- Contenedor de tabla de facturas malas (oculto por defecto) -->
            <div id="contenedor_facturas_malas" class="contenedor-facturas-malas" style="display: none;">
                <div class="cabecera-facturas-malas">
                    <h2>📋 Facturas con Diferencias de Ingreso</h2>
                    <button onclick="alternar_vista_facturas()" class="btn-volver">← Volver al Dashboard</button>
                </div>
                
                <table class="tabla-facturas-malas">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Nombre Empresa</th>
                            <th>Producto</th>
                            <th>Cantidad Facturada</th>
                            <th>Cantidad Ingresada</th>
                            <th>Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($filas_facturas_malas)): ?>
                            <?php foreach ($filas_facturas_malas as $fila): ?>
                                <?php $diferencia = $fila['cantidad_factura'] - $fila['cantidad_ingresada']; ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['n_factura']) ?></td>
                                    <td><?= htmlspecialchars($fila['nombre_empresa']) ?></td>
                                    <td><?= htmlspecialchars($fila['descripcion_producto']) ?></td>
                                    <td><?= htmlspecialchars($fila['cantidad_factura']) ?></td>
                                    <td><?= htmlspecialchars($fila['cantidad_ingresada']) ?></td>
                                    <td class="celda-diferencia"><?= $diferencia ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="sin-problemas">✅ No hay facturas con problemas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </body>

</html>

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
    -------------------------------------- FIN ITred Spa inicio  .PHP----------------------------------------
    ------------------------------------------------------------------------------------------------------------- -->
<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

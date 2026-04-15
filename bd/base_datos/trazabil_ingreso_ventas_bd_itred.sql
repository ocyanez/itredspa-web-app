-- Sitio Web Creado por ITred Spa.
-- Direccion: Guido Reni #4190
-- Pedro Aguirre Cerda - Santiago - Chile
-- contacto@itred.cl o itred.spa@gmail.com
-- https://www.itred.cl
-- Creado, Programado y Diseñado por ITred Spa.
-- BPPJ

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `trazabil_ingreso_ventas_bd_itred`
--

-- ------------------------------------------------------------------------------------------------------------
-- ----------------------- INICIO ITred Spa Base de Datos trazabil_ingreso_ventas_bd .SQL --------------
-- ------------------------------------------------------------------------------------------------------------

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `nombre` varchar(60) NOT NULL,
  `rut` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`nombre`, `rut`) VALUES
('pedro', '12234567-8'),
('admin', '12345678-9'),
('pepe', '13393048-5'),
('Harumi Sakamoto', '16821766-8'),
('kamaru', '18020499-7'),
('Colorin', '18427474-4'),
('Pancho', '20465253-8'),
('Geraldine', '20499462-5'),
('Boton', '20671947-8'),
('Luis', '20712207-6'),
('Cristobal Cere�o Espinozaaa', '20729963-4'),
('Cristian Carrasco Caba�a', '20880385-9'),
('lucas', '21060781-1'),
('ElColorin', '21201012-K'),
('farncis', '21274326-7'),
('Panchito', '21674002-5'),
('Takano Noriaki', '22459752-5'),
('Anderson', '23203024-0'),
('kamaru', '4225099-6'),
('Michael �ackson', '9267231-K');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estilos_botones_menu`
--

CREATE TABLE `estilos_botones_menu` (
  `id` int(11) NOT NULL,
  `boton_id` varchar(50) NOT NULL,
  `texto` varchar(100) NOT NULL,
  `background_color` varchar(20) DEFAULT '',
  `color` varchar(20) DEFAULT '',
  `border_style` varchar(20) DEFAULT 'solid',
  `border_width` varchar(20) DEFAULT '2px',
  `border_color` varchar(20) DEFAULT '',
  `border_radius` varchar(50) DEFAULT '4px',
  `width` varchar(20) DEFAULT '',
  `height` varchar(20) DEFAULT '100px',
  `font_size` varchar(20) DEFAULT '20px',
  `background_image` text DEFAULT '',
  `hover_image` text DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB=169 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `estilos_botones_menu`
--

INSERT INTO `estilos_botones_menu` (`id`, `boton_id`, `texto`, `background_color`, `color`, `border_style`, `border_width`, `border_color`, `border_radius`, `width`, `height`, `font_size`, `background_image`, `hover_image`, `created_at`, `updated_at`) VALUES
(1, 'ingreso-ventas', '\r\n                        \r\n                        \r\n                        \r\n                    ', '', '', '', '', 'rgb(0, 0, 0)', '', '', '', '20px', '', '', '2025-09-04 16:01:19', '2025-09-04 16:48:35'),
(2, 'ingreso-datos', '                     dfsdfsf                                                                        ', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 'inset', '2px', 'rgb(0, 0, 0)', '', '', '', '20px', '', '', '2025-09-04 16:01:19', '2025-09-04 16:58:45'),
(3, 'generar-qr', '\r\n                        \r\n                        Generar QR                                      ', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', '', '', 'rgb(0, 0, 0)', '8px', '160px', '60px', '20px', '', '', '2025-09-04 16:01:19', '2025-09-04 16:53:10'),
(4, 'buscar', 'Buscar', '', '', 'solid', '2px', '', '4px', '', '100px', '20px', '', '', '2025-09-04 16:01:19', '2025-09-04 16:01:19'),
(5, 'usuarios', 'Usuarios', '', '', 'solid', '2px', '', '4px', '', '100px', '20px', '', '', '2025-09-04 16:01:19', '2025-09-04 16:01:19'),
(6, 'plantilla', 'Plantilla', '', '', 'solid', '2px', '', '4px', '', '100px', '20px', '', '', '2025-09-04 16:01:19', '2025-09-04 16:01:19'),
(7, 'generar-respaldo', '\r\n                        Generar Respaldo                    ', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 'groove', '2px', 'rgb(0, 0, 0)', '4px', '', '100px', '20px', '', '', '2025-09-04 16:01:19', '2025-09-04 16:54:00'),
(8, '', '', '', '', 'solid', '2px', '', '4px', '', '100px', '20px', '', '', '2025-12-29 09:55:37', '2025-12-29 09:55:37'),
(9, 'btn_seccion_buscar', 'Buscar', '', '', 'solid', '2px', '', '4px', '', '100px', '20px', '', '', '2025-12-29 10:00:42', '2025-12-29 10:00:42'),
(10, 'btn_seccion_usuarios', 'Usuarios', '', '', 'solid', '2px', '', '4px', '', '100px', '20px', '', '', '2025-12-29 10:00:42', '2025-12-29 10:00:42'),
(11, 'btn_seccion_plantilla', 'Plantilla', '', '', 'solid', '2px', '', '4px', '', '100px', '20px', '', '', '2025-12-29 10:00:42', '2025-12-29 10:00:42'),
(12, 'btn_seccion_generar_respaldo', 'Generar respaldo', '', '', 'solid', '2px', '', '4px', '', '100px', '20px', '', '', '2025-12-29 10:00:42', '2025-12-29 10:00:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_intentos`
--

CREATE TABLE `login_intentos` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `hora_intento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `login_intentos`
--

INSERT INTO `login_intentos` (`id`, `ip_address`, `correo`, `hora_intento`) VALUES
(0, '::1', 'admin@gmail.com', '2025-06-06 14:53:14'),
(0, '::1', 'vendedor@gmail.cl', '2025-06-09 10:28:50'),
(0, '::1', 'vendedor@gmail.cl', '2025-06-09 10:32:06'),
(0, '::1', 'vendedor@gmail.cl', '2025-06-09 10:33:23'),
(0, '::1', 'admin@gmail.com', '2025-06-09 13:19:20'),
(0, '::1', 'admin@gmail.cl', '2025-06-09 13:20:00'),
(0, '::1', 'admin@gmail.com', '2025-08-14 15:46:16'),
(0, '::1', 'usuario@gmail.com', '2025-08-14 16:08:39'),
(0, '::1', 'x39qjlpw02@daouse.com', '2025-08-18 09:15:36'),
(0, '::1', 'dowkflhdps@mrotzis.com', '2025-08-18 09:15:41'),
(0, '190.5.32.160', 'Admin@gmail.com', '2025-08-22 16:02:34'),
(0, '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:23:39'),
(0, '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:24:16'),
(0, '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:24:32'),
(0, '190.5.32.160', 'admin1@gmail.com', '2025-08-22 16:29:00'),
(0, '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:40:14'),
(0, '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:40:53'),
(0, '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:43:53'),
(0, '186.40.65.156', 'admin@itred.cl', '2025-08-22 17:04:17'),
(0, '190.5.32.160', 'admin@itred.cl', '2025-08-22 17:06:24'),
(0, '190.5.32.160', 'admin@itred.cl', '2025-08-22 17:06:48'),
(0, '190.5.32.160', 'distribudor@itred.cl', '2025-08-22 17:07:07'),
(0, '186.40.65.156', 'admin@itred.cl', '2025-08-22 17:10:23'),
(0, '186.40.65.156', 'admin@itred.cl', '2025-08-22 17:10:40'),
(0, '38.165.230.130', 'admin@itred.cl', '2025-08-22 17:10:54'),
(0, '38.165.230.130', 'admin2@gmail.com', '2025-08-22 17:11:44'),
(0, '200.28.18.252', 'admin2@gmail.com', '2025-08-25 12:46:24'),
(0, '186.40.63.219', 'cvk8karlqc@jxpomup.com', '2025-08-25 12:52:05'),
(0, '186.40.63.219', 'superadmin@itred.cl', '2025-08-25 14:39:37'),
(0, '190.5.32.160', 'rnmaiql573@tormails.com', '2025-08-25 15:00:43'),
(0, '186.40.63.219', 'gfnnvzr447@tormails.com', '2025-08-25 15:12:33'),
(0, '186.40.63.219', 'admin_segma@segma.cl', '2025-08-25 15:13:39'),
(0, '186.40.63.219', 'rnmaiql573@tormails.com', '2025-08-25 15:28:54'),
(0, '190.5.32.160', 'admin@itred.cl', '2025-08-27 12:24:39'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-02 09:43:58'),
(0, '190.5.32.160', 'admin@gmail.com', '2025-09-02 11:11:29'),
(0, '190.5.32.160', 'admin@gmail.com', '2025-09-02 11:11:39'),
(0, '191.127.230.28', 'admin@gmail.com', '2025-09-02 12:08:28'),
(0, '190.5.32.160', 'distribuidor@itred.cl', '2025-09-02 12:51:25'),
(0, '190.5.32.160', 'distribuidor@itred.cl', '2025-09-02 12:51:33'),
(0, '190.5.32.160', 'distribuidor_segma@segma.cl', '2025-09-02 12:51:42'),
(0, '38.165.230.210', 'distribuidor_segma@segma.cl', '2025-09-02 13:05:54'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-03 15:20:16'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-03 15:20:22'),
(0, '190.5.32.160', 'admin_segma@segma.cl', '2025-09-03 16:18:44'),
(0, '181.203.31.165', 'admin@gmail.com', '2025-09-03 17:56:18'),
(0, '190.5.32.160', 'admin@gmail.com', '2025-09-04 09:53:33'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-04 11:16:00'),
(0, '190.5.32.160', 'admin@itred.cl', '2025-09-04 11:16:08'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-05 09:30:16'),
(0, '191.127.235.79', 'superadmin@itred.cl', '2025-09-05 09:47:06'),
(0, '191.127.235.79', 'distribuidor@itred.cl', '2025-09-05 14:50:59'),
(0, '186.40.64.202', 'superadmin@itred.cl', '2025-09-10 16:36:24'),
(0, '191.126.135.248', 'admin@gmail.com', '2025-09-11 10:57:46'),
(0, '191.126.140.109', 'admin@gmail.com', '2025-09-11 16:51:14'),
(0, '190.5.32.160', 'admin_segma@segma.cl', '2025-09-15 10:40:38'),
(0, '190.5.32.160', 'admin_segma@segma.cl', '2025-09-15 10:41:01'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-23 17:50:58'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-26 10:43:58'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-26 10:44:16'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-26 10:45:01'),
(0, '191.126.31.174', 'admin_segma@segma.cl', '2025-09-26 10:48:34'),
(0, '191.126.31.174', 'admin_segma@segma.cl', '2025-09-26 10:49:43'),
(0, '191.126.31.174', 'distribuidor@itred.cl', '2025-09-26 10:53:17'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-26 11:21:46'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-26 11:21:54'),
(0, '190.5.32.160', 'superadmin@itred.cl', '2025-09-26 11:21:59'),
(0, '190.20.107.18', 'superadmin@itred.cl', '2025-10-06 11:31:00'),
(0, '190.20.107.18', 'superadmin@itred.cl', '2025-10-07 12:04:34'),
(0, '190.20.107.18', 'superadmin@itred.cl', '2025-10-07 12:09:00'),
(0, '190.20.107.18', 'superadmin@itred.cl', '2025-10-07 12:09:29'),
(0, '190.114.32.243', 'gerardoyanezsalgado@gmail.com', '2025-10-10 14:46:34'),
(0, '190.114.32.243', 'gerardoyanezsalgado@gmail.com', '2025-10-10 14:48:12'),
(0, '190.114.32.243', 'gerardoyanezsalgado@gmail.com', '2025-10-10 14:49:28'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 09:10:02'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 10:14:04'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 10:15:23'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 10:16:12'),
(0, '190.20.116.101', 'admin_segma@segma.cl', '2025-10-13 10:30:12'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 10:34:14'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 10:35:04'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 10:45:33'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 11:09:04'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 11:14:08'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 11:22:49'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 11:35:28'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 12:02:29'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-13 12:02:49'),
(0, '190.20.116.101', 'admin@itred.cl', '2025-10-14 15:19:09'),
(0, '190.20.116.101', 'admin@itred.cl', '2025-10-14 15:36:19'),
(0, '190.20.116.101', 'superadmin@itred.cl', '2025-10-14 15:39:53'),
(0, '190.114.32.243', 'admin@itred.cl', '2025-10-14 18:10:37'),
(0, '190.114.32.243', 'superadmin@itred.cl', '2025-10-14 21:34:11'),
(0, '190.114.32.243', 'superadmin@itred.cl', '2025-10-14 21:34:27'),
(0, '190.114.32.243', 'superadmin@itred.cl', '2025-10-14 21:34:39'),
(0, '190.20.113.23', 'superadmin@itred.cl', '2025-10-15 15:31:03'),
(0, '190.20.113.23', 'superadmin@itred.cl', '2025-10-15 15:31:46'),
(0, '190.20.113.23', 'superadmin@itred.cl', '2025-10-15 15:44:32'),
(0, '190.20.113.23', 'uni24cute@gmail.com', '2025-10-16 09:29:25'),
(0, '190.20.113.23', 'uni24cute@gmail.com', '2025-10-16 09:54:51'),
(0, '190.20.113.23', 'distribuidor_segma@segma.cl', '2025-10-16 15:05:16'),
(0, '190.114.32.243', 'fopidih216@fixwap.com', '2025-10-16 15:19:44'),
(0, '190.114.32.243', 'fopidih216@fixwap.com', '2025-10-16 15:19:48'),
(0, '190.20.113.23', 'superadmin@itred.cl', '2025-10-16 15:31:18'),
(0, '190.114.32.243', 'superadmin@itred.cl', '2025-10-16 16:17:16'),
(0, '190.114.32.243', 'fopidih216@fixwap.com', '2025-10-16 16:34:11'),
(0, '190.114.32.243', 'fopidih216@fixwap.com', '2025-10-16 16:43:09'),
(0, '190.20.104.107', 'superadmin@itred.cl', '2025-10-20 11:54:10'),
(0, '190.20.109.212', 'superadmin@itred.cl', '2025-10-22 10:01:24'),
(0, '190.20.109.212', 'superadmin@itred.cl', '2025-10-22 10:08:31'),
(0, '190.20.109.212', 'superadmin@itred.cl', '2025-10-22 10:09:26'),
(0, '190.20.109.212', 'superadmin@itred.cl', '2025-10-22 10:35:16'),
(0, '190.20.109.212', 'superadmin@itred.cl', '2025-10-22 10:39:03'),
(0, '190.20.109.212', 'superadmin@itred.cl', '2025-10-22 10:47:47'),
(0, '190.114.43.87', 'dwdd@gmail.com', '2025-10-23 12:50:47'),
(0, '190.114.43.87', 'dwdd@gmail.com', '2025-10-23 12:53:13'),
(0, '190.20.124.167', 'superadmin@itred.cl', '2025-10-23 14:33:39'),
(0, '190.20.102.107', 'superadmin@itred.cl', '2025-10-27 11:14:52'),
(0, '181.43.242.190', 'superadmin@itred.cl', '2025-10-28 13:17:24'),
(0, '181.43.242.190', 'superadmin@itred.cl', '2025-10-28 13:17:37'),
(0, '181.43.242.190', 'superadmin@itred.cl', '2025-10-28 13:27:32'),
(0, '181.43.242.190', 'superadmin@itred.cl', '2025-10-28 16:19:48'),
(0, '181.43.242.190', 'superadmin@itred.cl', '2025-10-28 16:26:13'),
(0, '181.43.242.190', 'superadmin@itred.cl', '2025-10-30 16:16:46'),
(0, '190.20.100.25', 'superadmin@itred.cl', '2025-11-04 14:37:21'),
(0, '190.114.32.243', 'superadmin@itred.cl', '2025-11-06 09:19:35'),
(0, '190.114.32.243', 'yedip55911@nrlord.com', '2025-11-06 09:22:58'),
(0, '190.20.102.100', 'superadmin@itred.cl', '2025-11-13 08:59:12'),
(0, '190.20.127.230', 'superadmin@itred.cl', '2025-11-18 11:56:27'),
(0, '186.174.233.81', 'superadmin@itred.cl', '2025-11-18 15:13:35'),
(0, '190.46.188.107', 'superadmin@itred.cl', '2025-11-20 09:11:23'),
(0, '190.46.188.107', 'superadmin@itred.cl', '2025-11-20 09:16:59'),
(0, '190.46.188.107', 'superadmin@itred.cl', '2025-11-20 10:59:36'),
(0, '190.46.188.107', 'superadmin@itred.cl', '2025-11-20 10:59:50'),
(0, '190.46.188.107', 'gisow35817@okdeals.com', '2025-11-20 12:46:23'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-11-20 12:46:57'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-11-20 12:47:32'),
(0, '190.20.113.117', 'superadmin@itred.cl', '2025-11-21 14:23:28'),
(0, '190.20.98.212', 'superadmin@itred.cl', '2025-11-24 09:07:56'),
(0, '190.20.98.212', 'superadmin@itred.cl', '2025-11-24 09:08:05'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-11-24 11:39:39'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-11-26 10:24:25'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-11-26 12:14:56'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-11-26 12:15:16'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-11-28 11:42:44'),
(0, '186.67.142.170', 'admin_segma@segma.cl', '2025-11-28 16:56:12'),
(0, '186.67.142.170', 'admin_segma@segma.cl', '2025-11-28 16:56:30'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-12-02 09:03:39'),
(0, '186.175.143.228', 'gisow35817@okcdeals.com', '2025-12-02 11:47:23'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-12-10 14:17:59'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-12-11 12:22:42'),
(0, '186.175.53.198', 'gisow35817@okcdeals.com', '2025-12-11 17:34:17'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-12-12 12:24:57'),
(0, '190.163.240.201', 'kidaya1077@naqulu.com', '2025-12-19 10:03:33'),
(0, '190.46.188.107', 'gisow35817@okcdeals.com', '2025-12-26 17:33:05'),
(0, '190.163.240.201', 'kidaya1077@naqulu.com', '2026-01-08 09:34:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personalizacion`
--

CREATE TABLE `personalizacion` (
  `id` int(11) NOT NULL,
  `color_principal` varchar(7) NOT NULL DEFAULT '#00008b',
  `color_fondo` varchar(7) NOT NULL DEFAULT '#ffffff',
  `color_texto` varchar(7) NOT NULL DEFAULT '#000000',
  `color_borde` varchar(7) NOT NULL DEFAULT '#000000',
  `color_boton` varchar(7) NOT NULL DEFAULT '#ffffff',
  `color_boton_texto` varchar(7) NOT NULL DEFAULT '#000000',
  `color_boton_hover` varchar(7) NOT NULL DEFAULT '#0066ff',
  `color_campos` varchar(7) NOT NULL DEFAULT '#ffffff',
  `color_texto_campos` varchar(7) NOT NULL DEFAULT '#919191',
  `logo` varchar(255) DEFAULT 'ITRedLogo_img1.jpg',
  `fondo` varchar(255) DEFAULT '',
  `fondo_interior` varchar(255) DEFAULT '',
  `color_fondo_interior` varchar(7) DEFAULT '#ffffff',
  `borde_ancho` int(11) DEFAULT 2,
  `borde_estilo` varchar(20) DEFAULT 'solid',
  `borde_color` varchar(7) DEFAULT '#333333',
  `borde_radio` int(11) DEFAULT 5,
  `secciones_bordes_activas` text DEFAULT 'ingreso_ventas,ingreso_datos,generar_qr,buscar,usuarios,plantilla,generar_respaldo',
  `tipo_fuente` varchar(100) DEFAULT 'Arial',
  `tamano_fuente` int(11) DEFAULT 14,
  `botones_especificos_activos` text DEFAULT '',
  `elementos_especificos_activos` text DEFAULT '',
  `color_botones_menu` varchar(7) DEFAULT '#000000',
  `color_h1` varchar(7) DEFAULT '#000000',
  `color_h2` varchar(7) DEFAULT '#000000',
  `color_cabeceras_tabla` varchar(7) DEFAULT '#000000',
  `color_texto_titulo_input` varchar(7) DEFAULT '#000000',
  `color_texto_titulo_general` varchar(7) DEFAULT '#000000',
  `color_texto_titulo_img` varchar(7) DEFAULT '#000000',
  `color_texto_boton` varchar(7) DEFAULT '#000000',
  `color_texto_mensaje_error` varchar(7) DEFAULT '#ff0000',
  `color_texto_mensaje_exito` varchar(7) DEFAULT '#008000',
  `color_texto_placeholder` varchar(7) DEFAULT '#999999',
  `color_texto_label` varchar(7) DEFAULT '#000000',
  `color_texto_enlace` varchar(7) DEFAULT '#0066cc',
  `color_texto_menu` varchar(7) DEFAULT '#000000',
  `color_texto_submenu` varchar(7) DEFAULT '#666666',
  `color_texto_tabla_header` varchar(7) DEFAULT '#000000',
  `color_texto_tabla_td` varchar(7) DEFAULT '#000000',
  `color_texto_alerta` varchar(7) DEFAULT '#ff6600',
  `color_texto_info` varchar(7) DEFAULT '#0099cc',
  `color_texto_breadcrumb` varchar(7) DEFAULT '#666666',
  `color_texto_footer` varchar(7) DEFAULT '#666666',
  `textos_especificos_activos` text DEFAULT '',
  `color_menu_fondo` varchar(7) DEFAULT '#007bff',
  `color_menu_texto` varchar(7) DEFAULT '#ffffff',
  `color_menu_hover` varchar(7) DEFAULT '#0056b3',
  `aplicar_estilos_menu` text DEFAULT ''
) ENGINE=InnoDB=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `personalizacion`
--

INSERT INTO `personalizacion` (`id`, `color_principal`, `color_fondo`, `color_texto`, `color_borde`, `color_boton`, `color_boton_texto`, `color_boton_hover`, `color_campos`, `color_texto_campos`, `logo`, `fondo`, `fondo_interior`, `color_fondo_interior`, `borde_ancho`, `borde_estilo`, `borde_color`, `borde_radio`, `secciones_bordes_activas`, `tipo_fuente`, `tamano_fuente`, `botones_especificos_activos`, `elementos_especificos_activos`, `color_botones_menu`, `color_h1`, `color_h2`, `color_cabeceras_tabla`, `color_texto_titulo_input`, `color_texto_titulo_general`, `color_texto_titulo_img`, `color_texto_boton`, `color_texto_mensaje_error`, `color_texto_mensaje_exito`, `color_texto_placeholder`, `color_texto_label`, `color_texto_enlace`, `color_texto_menu`, `color_texto_submenu`, `color_texto_tabla_header`, `color_texto_tabla_td`, `color_texto_alerta`, `color_texto_info`, `color_texto_breadcrumb`, `color_texto_footer`, `textos_especificos_activos`, `color_menu_fondo`, `color_menu_texto`, `color_menu_hover`, `aplicar_estilos_menu`) VALUES
(1, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(2, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(3, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(4, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(5, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(6, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(7, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(8, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(9, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(10, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(11, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', ''),
(12, '#94a3b8', '#ffffff', '#000000', '#94a3b8', '#94a3b8', '#ffffff', 'rgba(13', '#ffffff', '#ffffff', '/imagenes/ingreso_venta_img1.png', '', '', '#ffffff', 2, 'solid', '#333333', 5, '', 'arial', 14, '', '', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#000000', '#ff0000', '#008000', '#999999', '#000000', '#0066cc', '#000000', '#666666', '#000000', '#000000', '#ff6600', '#0099cc', '#666666', '#666666', '', '#007bff', '#ffffff', '#0056b3', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `password` varchar(60) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `cargo` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `rut` varchar(12) NOT NULL
) ENGINE=InnoDB=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `apellido`, `username`, `correo`, `password`, `telefono`, `direccion`, `cargo`, `rol`, `rut`) VALUES
(1, 'superadmin', 'superadmin', 'superadmin', 'superadmin@itred.cl', '$2y$12$f7QW/zJJ7dyP3.ktOfZfU.aWhO35V922w0vQq5eMR6TX3I9aUb8Um', '+56972425972', 'guido reni 4190', 'super administrador', 'superadmin', '77.243.277-1'),
(2, 'admin', 'admin', 'admin', 'admin@itred.cl', '$2y$12$eXCPflFiXChcXzl6VjkKkeHg3g1OGQ1U73xEDvSOvRTIfdNW8pwv6', '+56972425972', 'guido reni 4190', 'administrador', 'admin', '77.243.277-1'),
(3, 'admin_segma', 'admin_segma', 'admin_segma', 'admin_segma@segma.cl', '$2y$12$bua70t1JHq2mttvzxsHcW.UzA97CdJVbk.lFoxsVCYt8vdvogqxhC', '+56900000000', 'sin direcci�n', 'administradora', 'admin', ''),
(4, 'distribuidor', 'distribuidor', 'distribuidor', 'distribuidor@itred.cl', '$2y$12$hSytLIPem.FUGxkZcBXsk.HTp2h3EKEoQlQnLl5d0dCHhdqirhv0G', '+56972425972', 'guido reni 4190', 'distribuidor', 'distribuidor', '77.243.277-1'),
(5, 'distribuidor_segma', 'distribuidor_segma', 'distribuidor_segma', 'distribuidor_segma@segma.cl', '$2y$12$fXC28DPSGQF5A19mVnAnXOuXQ.J2GsViav.kXqMefoihPS433wO0S', '+56900000000', 'sin direcci�n', 'distribuidor', 'distribuidor', ''),
(6, 'usuario_final', 'usuario_final', 'usuario_final', 'usuario_final@itred.cl', '$2y$12$jTVQ7x4Qlca7T8RlZVHi/.ys.ZBELYRS6dV1iwzVQ.LiCEArFIlmG', '+56972425972', 'guido reni 4190', 'usuario final', 'usuario_final', '77.243.277-1'),
(7, 'usuario_final_segma', 'usuario_final_segmaa', 'usuario_final_segma', 'usuario_final_segma@segma.cl', '$2y$12$Y2kS2F2vhicR0knIjaK.p.GIuhfc9CnbissciTmzgzHMNKPxdWCme', '+56900000000', 'sin direcci�n', 'usuario final', 'usuario_final', ''),
(144, 'sas', 'prueb', 'ddwq', 'solicay200@gamegta.com', '$2y$12$mu7Hzkecc0iI02fCnRLhAu.0eXFdcg6zvvpacnUcyFPRtAvPDEciq', '+569445454', 'sdss 43', 'usuario_final', 'usuario_final', '5.074.624-0'),
(145, 'geraldine', 'bastias', 'Gera.32', 'the.parkjimin.21@gmail.com', '$2y$12$XTis3UvLIPyOOB9FAkxNFOXnBAxDc6nTtLyUEJgV.fDR4jTSz/KFi', '+5699049330', 'jorge inostroza 504', 'usuario_final', 'usuario_final', '20.499.462-5'),
(154, 'sfsdfds', 'dfsdfd', 'sdfdsf', 'fopidih216@fixwap.com', '$2y$12$mBjCsn8Ug7lLHc5OIwT..OHRBTJ1nG9iX3T6lRiqTkRyB3kyyYMzC', 343543543, 'fd 34', 'usuario_final', 'usuario_final', '25.781.257-K'),
(155, 'Michaelñañ', 'ñarlosñ', 'carlos', 'yometo6000@elygifts.com', '$2y$12$ey1UMZAy9.gRsmlXNeXQA.4eiaMeJAGAfc20A3zm9MshFFazqnkUm', 435345435, 'fdgfd 4 5', 'sdsads', 'usuario_final', '9.267.231-K'),
(158, 'Ximena', 'Gonzalez', 'XimenaGonzalez', 'bacid47690@fergetic.com', '$2y$12$c1oVPErfkATJ660sUGRXsu8PykxtstBxPpWTIo36YQbAhjZJTl1Z6', 990493309, 'Almirante Latorre 1502', 'Bodega', 'bodega', '21.208.849-8'),
(159, 'proba', 'fdgdf', 'fdgfdg', 'devex25055@nyfhk.com', '$2y$12$d/3K0p4as80Rw03jhI7Gbu7Sjl6kLnBt8Xon7.R5U.l7O5cmp/.aO', '+56923432434', 'sds 1', 'aaa', 'bodega', '21.133.699-4'),
(160, 'javier', 'amarilla', 'pruebajavier', 'gisow35817@okcdeals.com', '$2y$12$b1t5cL66BDdt5JHEoa4luOGYFBpfO.eZLZOL6GZU0paHYOY3WjNVq', '+56809038121', 'rawson 559', 'ninguno', 'superadmin', '21.765.000-3'),
(161, 'Francisco', 'lazcano', 'pancho', 'kidaya1077@naqulu.com', '$2y$12$uQtbTm2XL1Ya/Xh6viHKQeoBOhFLvFMBcN7oKKMapvu06moiR3IPa', '+56809038122', 'rawson 559', 'ninguno', 'superadmin', '22.235.843-4'),
(162, 'Ian', 'Vergara', 'IanVergara', 'ianvergara2006@gmail.com', '$2y$12$rcgD6jI8GIactLs6btGTlunSCFmaRAlH4TfC8x7zTYFKO3N0a7tWC', '+56934121218', 'posoalmonte 1084', 'usuario_final', 'usuario_final', '22.134.271-2'),
(163, 'Octavio', 'Yañez', 'Octavio100', 'wexax14091@gamintor.com', '$2y$12$TY94GGTEwfxdfZLbUSBFiub6uRwPe9X5gZcUHewvxYYsCTY.bmokO', '+56945354234', 'rawson 559', 'ninguno', 'superadmin', '20.906.372-7'),
(164, 'Maykol', 'Vargas', 'Maykol23', 'maykolignacio18@gmail.com', '$2y$12$ZB1p829nMuybxIs6QDuOhuSShKeGo7pn8fGdv2vtxU1fWxEwYTyGa', '+56988585725', 'Calle estadio 31 Viña del mar ', 'usuario_final', 'usuario_final', '20.995.901-1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `id` int(11) NOT NULL,
  `rut` varchar(10) DEFAULT NULL,
  `numero_fact` varchar(20) DEFAULT NULL,
  `fecha_despacho` datetime DEFAULT NULL,
  `sku` varchar(20) DEFAULT NULL,
  `producto` varchar(200) DEFAULT NULL,
  `cantidad` int(255) DEFAULT NULL,
  `lote` bigint(11) DEFAULT NULL,
  `fecha_fabricacion` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `n_serie_ini` int(8) DEFAULT NULL,
  `n_serie_fin` int(8) DEFAULT NULL
) ENGINE=InnoDB=502 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`id`, `rut`, `numero_fact`, `fecha_despacho`, `sku`, `producto`, `cantidad`, `lote`, `fecha_fabricacion`, `fecha_vencimiento`, `n_serie_ini`, `n_serie_fin`) VALUES
(449, NULL, NULL, '2026-01-02 20:51:59', 2024003, 'Amoxicilina 250mg', 200, 11223, '2025-06-30', '2030-06-29', 3000, 3199),
(453, '20465253-8', 5678, '2026-01-05 10:03:00', 123, 'jugos', 10, 30, '2026-05-01', '2031-05-01', 1, 12),
(500, '20465253-8', 12345678, '2026-01-09 12:50:00', 567, 'Jugos', 10, 30, '2026-09-01', '2031-09-01', 1, 12);

-- --------------------------------------------------------

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`rut`);

--
-- Indices de la tabla `estilos_botones_menu`
--
ALTER TABLE `estilos_botones_menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `boton_id` (`boton_id`);

--
-- Indices de la tabla `personalizacion`
--
ALTER TABLE `personalizacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rut` (`rut`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `estilos_botones_menu`
--
ALTER TABLE `estilos_botones_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT de la tabla `personalizacion`
--
ALTER TABLE `personalizacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`rut`) REFERENCES `cliente` (`rut`);
COMMIT;

-- ------------------------------------------------------------------------------------------------------------
-- ------------------------ FIN ITred Spa Base de Datos trazabil_ingreso_ventas_bd .SQL ---------------
-- ------------------------------------------------------------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Sitio Web Creado por ITred Spa.
-- Direccion: Guido Reni #4190
-- Pedro Aguirre Cerda - Santiago - Chile
-- contacto@itred.cl o itred.spa@gmail.com
-- https://www.itred.cl
-- Creado, Programado y Diseñado por ITred Spa.
-- BPPJ

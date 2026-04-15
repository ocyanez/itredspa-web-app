-- Sitio Web Creado por ITred Spa.
-- Direccion: Guido Reni #4190
-- Pedro Aguirre Cerda - Santiago - Chile
-- contacto@itred.cl o itred.spa@gmail.com
-- https://www.itred.cl
-- Creado, Programado y Diseñado por ITred Spa.
-- BPPJ 


--  ---------------------------------------------------------------------------------------------------------------
--    ----------------------- INICIO ITred Spa trazabil_ingreso_ventas_bd_itred .db -------------------------------
--    ------------------------------------------------------------------------------------------------------------- 



-- Estructura para tabla `cliente`
DROP TABLE IF EXISTS `cliente`;
CREATE TABLE `cliente` (
  `nombre` varchar(60) NOT NULL,
  `rut` varchar(10) NOT NULL,
  PRIMARY KEY (`rut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Datos para la tabla `cliente`
INSERT INTO `cliente` (`nombre`, `rut`) VALUES
('pedro', '12234567-8'),
('admin', '12345678-9'),
('diego', '13345678-9'),
('pepe', '13393048-5'),
('Luis', '20712207-6'),
('Cristobal Cere�o Espinozaaa', '20729963-4'),
('Cristian Carrasco Caba�a', '20880385-9');


-- Estructura para tabla `venta`
DROP TABLE IF EXISTS `venta`;
CREATE TABLE `venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rut` varchar(10) DEFAULT NULL,
  `numero_fact` varchar(20) DEFAULT NULL,
  `fecha_despacho` datetime DEFAULT NULL,
  `sku` varchar(20) DEFAULT NULL,
  `producto` varchar(50) DEFAULT NULL,
  `lote` bigint(11) DEFAULT NULL,
  `fecha_fabricacion` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `n_serie_ini` int(8) DEFAULT NULL,
  `n_serie_fin` int(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rut` (`rut`),
  CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`rut`) REFERENCES `cliente` (`rut`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Datos para la tabla `venta`
INSERT INTO `venta` (`id`, `rut`, `numero_fact`, `fecha_despacho`, `sku`, `producto`, `lote`, `fecha_fabricacion`, `fecha_vencimiento`, `n_serie_ini`, `n_serie_fin`) VALUES
('82', '20729963-4', '123456789', '2024-04-11 14:45:00', '31401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '313588', '2025-04-01', '2029-04-01', '1023100', '1023256'),
('83', '20729963-4', '123456789', '2025-04-11 14:47:00', '41401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '413588', '2024-04-01', '2029-04-01', '1023100', '1023256'),
('84', '20729963-4', '123456789', '2022-04-11 15:08:00', '51401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '513588', '2025-04-01', '2029-04-01', '1023100', '1023256'),
('86', '20880385-9', '4092810', '2024-04-14 09:29:00', '71401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '713588', '2022-04-01', '2029-04-01', '1023100', '1023256'),
('117', '20880385-9', '987654321', '2023-04-11 17:06:00', '21401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '213588', '2024-04-01', '2029-04-01', '1023100', '1023256'),
('118', '20729963-4', '123456789', '2024-04-11 14:45:00', '31401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '313588', '2025-04-01', '2030-04-01', '1023100', '1023256'),
('120', '20729963-4', '123456789', '2022-04-11 15:08:00', '51401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '513588', '2025-04-01', '2030-04-01', '1023100', '1023256'),
('121', '20880385-9', '123456789', '2023-04-11 17:06:00', '61401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '613588', '2024-04-01', '2029-04-01', '1023100', '1023256'),
('122', '20880385-9', '4092810', '2024-04-14 09:29:00', '71401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '713588', '2022-04-01', '2027-04-01', '1023100', '1023256'),
('123', '20880385-9', '72151231', '2025-04-21 11:44:00', '81401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '813588', '2025-04-01', '2030-04-01', '1023100', '1023256'),
('124', '20712207-6', '123456788', '2025-04-21 11:44:00', '11401002', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '813588', '2025-04-01', '2030-04-01', '1023100', '1023256'),
('125', '20729963-4', '12345678', '2022-04-11 14:45:00', '11401003', 'CABO DE VIDA KING 1.8 MTS.MOSQ 107 Y ESCALA', '113588', '2024-04-01', '2031-04-01', '1023100', '1023256');


-- Estructura para tabla `usuario`
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `password` varchar(60) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `cargo` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `rut` varchar(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Datos para la tabla `usuario`
INSERT INTO `usuario` (`id`, `nombre`, `apellido`, `username`, `correo`, `password`, `telefono`, `direccion`, `cargo`, `rol`, `rut`) VALUES
('1', 'superadmin', 'superadmin', 'superadmin', 'superadmin@itred.cl', '$2y$12$JRWWbQEOU7Be6S9NJX3FQOATaaAmKIsly2cmMuJK8M9WUcxA/baxa', '+56972425972', 'guido reni 4190', 'super administrador', 'superadmin', '77.243.277-1'),
('2', 'admin', 'admin', 'admin', 'admin@itred.cl', '$2y$12$i3nNER.M4orio9p09Um5u.Zmlmm9REm.9JkBkZst8/HEThaSHKsI.', '+56972425972', 'guido reni 4190', 'administrador', 'admin', '77.243.277-1'),
('3', 'admin_segma', 'admin_segma', 'admin_segma', 'admin_segma@segma.cl', '$2y$12$bua70t1JHq2mttvzxsHcW.UzA97CdJVbk.lFoxsVCYt8vdvogqxhC', '+56900000000', 'sin direcci�n', 'administrador', 'admin', ''),
('4', 'distribuidor', 'distribuidor', 'distribuidor', 'distribuidor@itred.cl', '$2y$12$hSytLIPem.FUGxkZcBXsk.HTp2h3EKEoQlQnLl5d0dCHhdqirhv0G', '+56972425972', 'guido reni 4190', 'distribuidor', 'distribuidor', '77.243.277-1'),
('5', 'distribuidor_segma', 'distribuidor_segma', 'distribuidor_segma', 'distribuidor_segma@segma.cl', '$2y$12$fXC28DPSGQF5A19mVnAnXOuXQ.J2GsViav.kXqMefoihPS433wO0S', '+56900000000', 'sin direcci�n', 'distribuidor', 'distribuidor', ''),
('6', 'usuario_final', 'usuario_final', 'usuario_final', 'usuario_final@itred.cl', '$2y$12$jTVQ7x4Qlca7T8RlZVHi/.ys.ZBELYRS6dV1iwzVQ.LiCEArFIlmG', '+56972425972', 'guido reni 4190', 'usuario comun', 'usuario_final', '77.243.277-1'),
('7', 'usuario_final_segma', 'usuario_final_segma', 'usuario_final_segma', 'usuario_final_segma@segma.cl', '$2y$12$Y2kS2F2vhicR0knIjaK.p.GIuhfc9CnbissciTmzgzHMNKPxdWCme', '+56900000000', 'sin direcci�n', 'usuario comun', 'usuario_final', '');


-- Estructura para tabla `personalizacion`
DROP TABLE IF EXISTS `personalizacion`;
CREATE TABLE `personalizacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Datos para la tabla `personalizacion`
INSERT INTO `personalizacion` (`id`, `color_principal`, `color_fondo`, `color_texto`, `color_borde`, `color_boton`, `color_boton_texto`, `color_boton_hover`, `color_campos`, `color_texto_campos`, `logo`) VALUES
('1', '#00008b', '#ffffff', '#000000', '#000000', '#ffffff', '#000000', '#0066ff', '#ffffff', '#919191', 'menu_img1.png');


-- Estructura para tabla `login_intentos`
DROP TABLE IF EXISTS `login_intentos`;
CREATE TABLE `login_intentos` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `hora_intento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Datos para la tabla `login_intentos`
INSERT INTO `login_intentos` (`id`, `ip_address`, `correo`, `hora_intento`) VALUES
('0', '::1', 'admin@gmail.com', '2025-06-06 14:53:14'),
('0', '::1', 'vendedor@gmail.cl', '2025-06-09 10:28:50'),
('0', '::1', 'vendedor@gmail.cl', '2025-06-09 10:32:06'),
('0', '::1', 'vendedor@gmail.cl', '2025-06-09 10:33:23'),
('0', '::1', 'admin@gmail.com', '2025-06-09 13:19:20'),
('0', '::1', 'admin@gmail.cl', '2025-06-09 13:20:00'),
('0', '::1', 'admin@gmail.com', '2025-08-14 15:46:16'),
('0', '::1', 'usuario@gmail.com', '2025-08-14 16:08:39'),
('0', '::1', 'x39qjlpw02@daouse.com', '2025-08-18 09:15:36'),
('0', '::1', 'dowkflhdps@mrotzis.com', '2025-08-18 09:15:41'),
('0', '190.5.32.160', 'Admin@gmail.com', '2025-08-22 16:02:34'),
('0', '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:23:39'),
('0', '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:24:16'),
('0', '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:24:32'),
('0', '190.5.32.160', 'admin1@gmail.com', '2025-08-22 16:29:00'),
('0', '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:40:14'),
('0', '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:40:53'),
('0', '186.40.65.156', 'admin@gmail.com', '2025-08-22 16:43:53'),
('0', '186.40.65.156', 'admin@itred.cl', '2025-08-22 17:04:17'),
('0', '190.5.32.160', 'admin@itred.cl', '2025-08-22 17:06:24'),
('0', '190.5.32.160', 'admin@itred.cl', '2025-08-22 17:06:48'),
('0', '190.5.32.160', 'distribudor@itred.cl', '2025-08-22 17:07:07'),
('0', '186.40.65.156', 'admin@itred.cl', '2025-08-22 17:10:23'),
('0', '186.40.65.156', 'admin@itred.cl', '2025-08-22 17:10:40'),
('0', '38.165.230.130', 'admin@itred.cl', '2025-08-22 17:10:54'),
('0', '38.165.230.130', 'admin2@gmail.com', '2025-08-22 17:11:44'),
('0', '200.28.18.252', 'admin2@gmail.com', '2025-08-25 12:46:24'),
('0', '186.40.63.219', 'cvk8karlqc@jxpomup.com', '2025-08-25 12:52:05'),
('0', '186.40.63.219', 'superadmin@itred.cl', '2025-08-25 14:39:37'),
('0', '190.5.32.160', 'rnmaiql573@tormails.com', '2025-08-25 15:00:43'),
('0', '186.40.63.219', 'gfnnvzr447@tormails.com', '2025-08-25 15:12:33'),
('0', '186.40.63.219', 'admin_segma@segma.cl', '2025-08-25 15:13:39'),
('0', '186.40.63.219', 'rnmaiql573@tormails.com', '2025-08-25 15:28:54'),
('0', '190.5.32.160', 'admin@itred.cl', '2025-08-27 12:24:39'),
('0', '190.5.32.160', 'superadmin@itred.cl', '2025-09-02 09:43:58'),
('0', '190.5.32.160', 'admin@gmail.com', '2025-09-02 11:11:29'),
('0', '190.5.32.160', 'admin@gmail.com', '2025-09-02 11:11:39');

-- Sitio Web Creado por ITred Spa.
-- Direccion: Guido Reni #4190
-- Pedro Aguirre Cerda - Santiago - Chile
-- contacto@itred.cl o itred.spa@gmail.com
-- https://www.itred.cl
-- Creado, Programado y Diseñado por ITred Spa.
-- BPPJ 


--  ---------------------------------------------------------------------------------------------------------------
--    ----------------------- FIN ITred Spa trazabil_ingreso_ventas_bd_itred .db ----------------------------------
--    ------------------------------------------------------------------------------------------------------------- 
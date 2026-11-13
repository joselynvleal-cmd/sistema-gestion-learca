-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2026 a las 01:15:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_algoritmo_gestion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entidad_gestion`
--

CREATE TABLE `entidad_gestion` (
  `id_registro` int(11) NOT NULL,
  `id_usuario_responsable` int(11) DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `cedula_identidad` varchar(50) DEFAULT NULL,
  `datos_contacto` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `rif_cedula` varchar(30) NOT NULL,
  `razon_social` varchar(150) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `estado` varchar(20) DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `rif_cedula`, `razon_social`, `telefono`, `email`, `direccion`, `estado`, `fecha_registro`) VALUES
(3, 'J-08512817-4', 'Embutidos Arichuna, C.A', '0251-4205000', 'compras.servicios@arichuna.com', 'Carrera 1 C/C/ 4 Zona Industrial III Barquisimeto Edo. Lara', 'Activo', '2026-11-12 02:20:07'),
(4, 'J-00027241-7', 'Pastas Capri, C.A.', '0212-3830848', 'MMalvacia@pastascapri.com', 'Av. Carrizal Edif. Capri Piso PB OF Principal Zona Industrial Corralito Carrizal Estado Miranda', 'Activo', '2026-11-12 02:22:01'),
(5, 'J-30131018-7', 'Mondelez VZ, C.A.', '0416-7890123', 'Yelianne.Pineda@mdlz.com', 'Av. Francisco de Miranda Edif Seguros Venezuela Piso 2 Urb. Campo Alegre Caracas.', 'Activo', '2026-11-12 02:23:17'),
(6, 'J-07517358-9', 'Inversiones Porcinas, C.A.', '0251-4204300', 'kvalera@inporca.com', 'Ctra Yaritagua Barquisimeto Antiguo Central Yaritagua Casa S/N Sector San Rafael Yaritagua Yaracuy Zona Postal 3203', 'Activo', '2026-11-12 02:24:04'),
(8, 'J-50001890-8', 'Productora de Alimentos Serex, C.A', '0261-6149501', 'dtovar@gruposerex.com', 'Av. 9B Edif Banco Industrial Piso 10 OF S/N Sector 5 de Julio Maracaibo Zulia Zona Postal 4001', 'Activo', '2026-11-12 02:27:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Control total del sistema, gestión de usuarios y registros'),
(2, 'Empleado', 'Acceso operativo para registro y consulta de datos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(500) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `fecha_ingreso` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` tinyint(4) DEFAULT 1,
  `codigo_recuperacion` varchar(6) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `cedula`, `id_rol`, `username`, `email`, `password`, `cargo`, `fecha_ingreso`, `estado`, `codigo_recuperacion`, `token_expiracion`, `avatar`) VALUES
(1, 'V-8723764', 1, 'Juan Leal', 'joselynvleal@gmail.com', '$2y$10$fb/hlzzxxlMOh.ozy7QNM.Bqh/65pkRXLi2697PZvOKFmIghQw3Ei', 'Gerente General', '2009-07-08 04:00:00', 1, NULL, NULL, 'assets/img/avatars/1794524842_trabajo2.jpg'),
(11, 'V-17101709', 1, 'Josybet Rodriguez', 'josybet@gmail.com', '$2y$10$dBWF02Wi2Y69NAp3tUGWD.GkfrDz6WW.8JN0ZkdWZKc6yNUdsDHNS', 'Gerente Administrativo', '2009-09-09 04:00:00', 1, NULL, NULL, NULL),
(12, 'V-9854102', 2, 'Richard Arguelles', 'richard@gmail.com', '$2y$10$9hwnvDXKGjkSEW/klK/tROZCS10I8dTuIbqhKPnY2kslsuHGevGw.', 'Gerente Operaciones', '2009-10-12 04:00:00', 1, NULL, NULL, NULL),
(13, 'V-7452146', 2, 'Fidelia Leal', 'fidelia@gmail.com', '$2y$10$0KR2bToAOcqBwdpVyXNr0OrodYDbKt.Vi0zrCwzYDaIa9UbMaG1zG', 'Asistente Administrativo', '2026-11-12 04:00:00', 1, NULL, NULL, NULL),
(14, 'V-12156254', 2, 'Luis Lopez', 'lopez@gmail.com', '$2y$10$rocYuS/UA8be/o569cnN3uheULwlG8YLMRPr0IV0zVXmQA93b8l/m', 'Supervisor', '2026-11-12 04:00:00', 1, NULL, NULL, NULL),
(15, 'V-23452178', 2, 'Ydelbrando Perez', 'ydel@gmail.com', '$2y$10$edR5G7Gq17bC8bquVwgWMOPX1zqDb3mkzfCQf4HdcGPzRJYa6nMk6', 'Mecánico', '2026-11-12 04:00:00', 1, NULL, NULL, NULL),
(16, 'V-15789521', 2, 'Angello Berrios', 'angello@gmail.com', '$2y$10$iLRJOpSbkgY8D/ivYgdSau0qlTv9YG6..bdXTxl.bsmEzxNoUyua6', 'Ayudante Mecánico', '2026-11-12 04:00:00', 1, NULL, NULL, NULL),
(17, 'V-10125412', 2, 'Omar Lopez', 'omar@gmail.com', '$2y$10$1Ua63K9FUQ9zJRuTtXbRye.fLmMghfM/Ey6Lvyi93B4hZB7CvdiwO', 'Electricista', '2026-11-12 04:00:00', 1, NULL, NULL, NULL),
(18, 'V-29415869', 2, 'Jhonathan Berrios', 'jhonathan@gmail.com', '$2y$10$8Pdofk3gXiUkeVyFQ0eP.eONJnDx7TPv9wrJV0TqlE.IhIzUSLmui', 'Ayudante Electricista', '2026-11-12 04:00:00', 1, NULL, NULL, NULL),
(19, 'V-12548941', 2, 'Marcos Marchan', 'marcos@gmail.com', '$2y$10$p1k5iJG.D4dyJdpyH2nQ3OBlf64VvTWuflnqMT0WYIqHtMFGHG36i', 'Obrero', '2026-11-12 04:00:00', 1, NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `entidad_gestion`
--
ALTER TABLE `entidad_gestion`
  ADD PRIMARY KEY (`id_registro`),
  ADD UNIQUE KEY `cedula_identidad` (`cedula_identidad`),
  ADD KEY `id_usuario_responsable` (`id_usuario_responsable`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `rif_cedula` (`rif_cedula`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `entidad_gestion`
--
ALTER TABLE `entidad_gestion`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `entidad_gestion`
--
ALTER TABLE `entidad_gestion`
  ADD CONSTRAINT `entidad_gestion_ibfk_1` FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2026 at 01:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bd_abogados`
--

-- --------------------------------------------------------

--
-- Table structure for table `casos`
--

CREATE TABLE `casos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('Abierto','En Proceso','Cerrado') DEFAULT 'Abierto',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `casos`
--

INSERT INTO `casos` (`id`, `titulo`, `descripcion`, `estado`, `fecha_creacion`, `usuario_id`, `cliente_id`) VALUES
(1, 'Divorcio Mutuo Acuerdo', 'Caso de separación de bienes.', 'En Proceso', '2026-10-17 01:33:24', 2, 1),
(2, 'Auditoría Fiscal', 'Defensa ante requerimiento de impuestos.', 'Abierto', '2026-10-17 01:33:24', 2, 2),
(3, 'Robo En El Ara', 'Se han robado un pollo del Ara', 'En Proceso', '2026-10-21 23:32:44', 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `telefono`, `correo`) VALUES
(1, 'Carlos Mendoza', '555-1234', 'carlos@email.com'),
(2, 'Ana Gomez', '555-5678', 'ana@email.com'),
(3, 'Pepito Pereira', NULL, 'pepito@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `instalaciones`
--

CREATE TABLE `instalaciones` (
  `id_instalacion` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `costo_miembros` decimal(10,0) NOT NULL,
  `costo_invitados` decimal(10,0) NOT NULL,
  `inversion_inicial` decimal(10,0) NOT NULL,
  `mantenimiento_mensual` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `instalaciones`
--

INSERT INTO `instalaciones` (`id_instalacion`, `nombre`, `costo_miembros`, `costo_invitados`, `inversion_inicial`, `mantenimiento_mensual`) VALUES
(1, 'Tennis Court 1', 5, 25, 10000, 200),
(2, 'Tennis Court 2', 5, 25, 8000, 180),
(3, 'Badminton Court', 3, 15, 4000, 100),
(4, 'Swimming Pool', 10, 35, 25000, 500),
(5, 'Gym', 8, 20, 15000, 300),
(6, 'Squash Court', 4, 18, 6000, 150);

-- --------------------------------------------------------

--
-- Table structure for table `miembros`
--

CREATE TABLE `miembros` (
  `miemid` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `direccion` varchar(30) NOT NULL,
  `codigo_postal` int(11) NOT NULL,
  `telefono` varchar(10) NOT NULL,
  `recomendado` int(11) DEFAULT NULL,
  `fechaIngreso` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `miembros`
--

INSERT INTO `miembros` (`miemid`, `nombre`, `apellido`, `direccion`, `codigo_postal`, `telefono`, `recomendado`, `fechaIngreso`) VALUES
(1, 'Nancy', 'Dare', '6 Huntingdon Rd', 10383, '83377640', NULL, '2012-07-25 15:00:00'),
(2, 'David', 'Jones', '597 Gallatin St', 36362, '84453680', 1, '2012-08-06 14:30:00'),
(3, 'John', 'Hunt', '5 Burlington Rd', 54333, '89972069', 2, '2012-09-19 19:20:00'),
(4, 'Tim', 'Boothe', '3 Bloomsbury St', 234, '81143325', NULL, '2012-07-25 17:00:00'),
(5, 'Henrietta', 'Rumney', '3 Burlington Rd', 78533, '82298988', 1, '2012-09-05 13:42:35'),
(6, 'Janice', 'Joplette', '20 Crossing Road', 234, '83394247', 2, '2012-07-03 16:15:00'),
(7, 'Tim', 'Rownham', '23 Highway Way', 24323, '84469307', 4, '2012-07-03 20:30:00'),
(8, 'David', 'Farrell', '437 Granite Dr', 43532, '85575976', NULL, '2012-09-15 13:22:05'),
(9, 'Anne', 'Smith', '15 Park Street', 12345, '87712345', 5, '2012-10-01 15:00:00'),
(10, 'Robert', 'Brown', '40 Main Street', 54321, '88845678', NULL, '2012-10-05 18:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `miemid` int(11) NOT NULL,
  `id_instalacion` int(11) NOT NULL,
  `fecha_reserva` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `duracion` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `reservas`
--

INSERT INTO `reservas` (`id`, `miemid`, `id_instalacion`, `fecha_reserva`, `hora_inicio`, `duracion`) VALUES
(1, 5, 1, '2012-09-22', '11:00:00', '02:00:00'),
(2, 5, 2, '2012-09-22', '16:00:00', '02:00:00'),
(3, 5, 3, '2012-09-29', '17:00:00', '01:00:00'),
(4, 6, 4, '2012-09-28', '13:00:00', '01:00:00'),
(5, 3, 2, '2012-09-25', '09:00:00', '03:00:00'),
(6, 2, 3, '2012-09-26', '14:00:00', '02:00:00'),
(7, 1, 1, '2012-09-27', '10:00:00', '04:00:00'),
(8, 8, 2, '2012-09-29', '15:00:00', '02:00:00'),
(9, 9, 4, '2012-10-01', '12:00:00', '03:00:00'),
(10, 5, 5, '2012-10-02', '18:00:00', '02:00:00'),
(11, 2, 1, '2012-10-03', '11:00:00', '01:00:00'),
(12, 3, 3, '2012-10-04', '16:00:00', '02:00:00'),
(13, 6, 4, '2012-10-05', '10:00:00', '04:00:00'),
(14, 7, 5, '2012-10-06', '17:00:00', '03:00:00'),
(15, 9, 2, '2012-10-07', '09:00:00', '02:00:00'),
(16, 10, 1, '2012-10-08', '14:00:00', '01:00:00'),
(17, 1, 3, '2012-10-09', '11:00:00', '02:00:00'),
(18, 2, 4, '2012-10-10', '15:00:00', '03:00:00'),
(19, 3, 5, '2012-10-11', '18:00:00', '02:00:00'),
(20, 5, 6, '2012-10-12', '16:00:00', '01:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'cliente',
  `cliente_id` int(11) DEFAULT NULL,
  `pregunta_seguridad` varchar(150) DEFAULT NULL,
  `respuesta_seguridad` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `rol`, `cliente_id`, `pregunta_seguridad`, `respuesta_seguridad`) VALUES
(1, 'Dylan', 'admin@gmail.com', '$2y$10$L5jHv1VbVA92lCBrCsRlrOqBYwTkEPFndwoA5ACNTcLtGWkr3E0QC', 'admin', NULL, '¿Cuál es el nombre de la empresa?', '$2y$10$HMGUAfXcZyu9Q8IKKvZ50uIgSQIdX1G9Yxrbnxsft3iJCh9XZk0vO'),
(2, 'Dra. Maria Abogada', 'maria@bufete.com', '$2y$10$CCukrclyRAZu7IKkYYdHNuYvMF/TgJw/3K1giNsCrQV4UjuFqmuBK', 'abogado', NULL, '¿Cuál es el nombre de la empresa?', '$2y$10$ofjeTGrAPIdoLFNfvjF.AO2S6c3Zr9YRA7uvZ38F3PZ7J1k//dJLy'),
(3, 'Cristian', 'cristian@bufete.com', '$2y$10$ip7rx2L0Py1o6QgITjAjR.poDeFPJUYZoctmzyEHWk0GNYxeyA82a', 'abogado', NULL, '¿En qué ciudad naciste?', '$2y$10$AxW556ZFQ371mm81F6HSf.VYUwB1Xcu./MV4/jYqzwVNLalRv5zQm'),
(4, 'Pepito Pereira', 'pepito@gmail.com', '$2y$10$XPCDCqt7ASITfjcQ3dEYo.sgn1DUlog67AL2T5PUydTawYz7PGfty', 'cliente', 3, '¿Cuál es el nombre de tu primera mascota?', '$2y$10$SxbDYamxzTeDzZQWcpXF/OTT6uKc9FBuTuB8aaCjIwu8i4arOtady');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `casos`
--
ALTER TABLE `casos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD PRIMARY KEY (`id_instalacion`);

--
-- Indexes for table `miembros`
--
ALTER TABLE `miembros`
  ADD PRIMARY KEY (`miemid`);

--
-- Indexes for table `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `miemid` (`miemid`),
  ADD KEY `id_instalacion` (`id_instalacion`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_usuarios_cliente` (`cliente_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `casos`
--
ALTER TABLE `casos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `instalaciones`
--
ALTER TABLE `instalaciones`
  MODIFY `id_instalacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `miembros`
--
ALTER TABLE `miembros`
  MODIFY `miemid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `casos`
--
ALTER TABLE `casos`
  ADD CONSTRAINT `casos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `casos_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`miemid`) REFERENCES `miembros` (`miemid`),
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_instalacion`) REFERENCES `instalaciones` (`id_instalacion`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

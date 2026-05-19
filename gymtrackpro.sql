-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-04-2026 a las 05:40:35
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
-- Base de datos: `gymtrackpro`
--
CREATE DATABASE IF NOT EXISTS `gymtrackpro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
USE `gymtrackpro`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `id_alumno` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `perfil_fisico` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`perfil_fisico`)),
  `fecha_inscripcion` date DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `alumno`
--

INSERT INTO `alumno` (`id_alumno`, `nombre`, `apellido`, `email`, `fecha_nacimiento`, `telefono`, `perfil_fisico`, `fecha_inscripcion`, `estado`) VALUES
(1, 'Tomás', 'Aguirre', 'tomas.aguirre@gmail.com', '1998-03-12', '3624-111111', '{\"peso_kg\":82, \"altura_cm\":178, \"experiencia\":\"intermedio\", \"dias_disponibles\": 4, \"lesiones\":[],\"objetivo_secundario\":\"mejorar postura\"}', '2024-01-10', 'activo'),
(2, 'Camila', 'Romero', 'camila.romero@gmail.com', '2000-07-25', '3624-222222', '{\"peso_kg\":61, \"altura_cm\":163, \"experiencia\":\"principiante\", \"dias_disponibles\": 3, \"lesiones\":[\"lumbar\"], \"objetivo_secundario\":\"tonificar piernas\"}', '2024-02-14', 'activo'),
(3, 'Nicolás', 'Barrios', 'nicolas.barrios@gmail.com', '1995-11-30', '3624-333333', '{\"peso_kg\":95, \"altura_cm\":182, \"experiencia\":\"avanzado\", \"dias_disponibles\": 5, \"lesiones\":[\"rodilla derecha\"], \"objetivo_secundario\":\"competir amateur\"}', '2024-03-01', 'activo'),
(4, 'Florencia', 'Medina', 'florencia.medina@gmail.com', '2003-05-18', '3624-444444', '{\"peso_kg\":55, \"altura_cm\":160, \"experiencia\": \"principiante\", \"dias_disponibles\": 2, \"lesiones\": [], \"objetivo_secundario\":\"reducir estres\"}', '2024-04-20', 'inactivo'),
(5, 'Ezequiel', 'Leiva', 'ezequiel.leiva@gmail.com', '1990-09-08', '3624-555555', '{\"peso_kg\":78,\"altura_cm\":175, \"experiencia\":\"intermedio\", \"dias_disponibles\": 4, \"lesiones\": [\"hombro izquierdo\"], \"objetivo_secundario\":\"ganar resistencia\"}', '2024-05-05', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno_rutina`
--

CREATE TABLE `alumno_rutina` (
  `id_alumno` int(11) NOT NULL,
  `id_rutina` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` enum('activa','finalizada','pausada') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicio`
--

CREATE TABLE `ejercicio` (
  `id_ejercicio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `grupo_muscular` varchar(100) DEFAULT NULL,
  `tipo` enum('fuerza','cardio','movilidad','flexibilidad') DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `ejercicio`
--

INSERT INTO `ejercicio` (`id_ejercicio`, `nombre`, `grupo_muscular`, `tipo`, `descripcion`) VALUES
(1, 'Sentadilla', 'Cuadriceps / Glúteos', 'fuerza', 'Ejercicio compuesto de tren inferior. Barra en trapecio, descenso hasta paralelo.'),
(2, 'Press de banca', 'Pectoral/Triceps', 'fuerza', 'Empuje horizontal en banco plano con barra o mancuernas.'),
(3, 'Peso muerto', 'Isquiotibiales / Lumbar', 'fuerza', 'Levantamiento desde el suelo. Trabaja cadena posterior completa.'),
(4, 'Burpee', 'Cuerpo completo', 'cardio', 'Ejercicio funcional de alta intensidad. Combina plancha, flexión y salto.'),
(5, 'Estiramiento cadera', 'Cadera / Flexores', 'movilidad', 'Estiramiento dinámico de flexores de cadera en posición de lunge.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor`
--

CREATE TABLE `profesor` (
  `id_profesor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `profesor`
--

INSERT INTO `profesor` (`id_profesor`, `nombre`, `apellido`, `email`, `especialidad`) VALUES
(1, 'Marcos', 'Giménez', 'marcos.gimenez@gymtrack.com', 'Hipertrofia y fuerza'),
(2, 'Lucía', 'Paredes', 'lucia.paredes@gymtrack.com', 'Cardio y resistencia'),
(3, 'Sebastián', 'Ríos', 'sebastian.rios@gymtrack.com', 'Movilidad y rehabilitación'),
(4, 'Valentina', 'Sosa', 'valentina.sosa@gymtrack.com', 'CrossFit funcional'),
(5, 'Diego', 'Ferreyra', 'diego.ferreyra@gymtrack.com', 'Pérdida de peso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina`
--

CREATE TABLE `rutina` (
  `id_rutina` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel` enum('principiante','intermedio','avanzado') DEFAULT NULL,
  `duracion_min` int(11) DEFAULT NULL,
  `objetivo` enum('perdida_peso','hipertrofia','resistencia','movilidad') DEFAULT NULL,
  `id_profesor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `rutina`
--

INSERT INTO `rutina` (`id_rutina`, `nombre`, `descripcion`, `nivel`, `duracion_min`, `objetivo`, `id_profesor`) VALUES
(1, 'Fuerza Base', 'Rutina de introducción al entrenamiento con pesas. Énfasis en técnica.', 'principiante', 45, 'hipertrofia', 1),
(2, 'Quema Total', 'Circuito de alta intensidad orientado a la pérdida de grasa corporal.', 'intermedio', 60, 'perdida_peso', 5),
(3, 'Movilidad Activa', 'Sesión de movilidad articular y stretching dinámico para prevenir lesiones.', 'principiante', 30, 'movilidad', 3),
(4, 'Hipertrofia Avanzada', 'Rutina de volumen alto con técnicas de intensificación como drop sets.', 'avanzado', 75, 'hipertrofia', 1),
(5, 'Cardio Funcional', 'Combinación de ejercicios aeróbicos y funcionales para mejorar la resistencia.', 'intermedio', 50, 'resistencia', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutina_ejercicio`
--

CREATE TABLE `rutina_ejercicio` (
  `id_rutina` int(11) NOT NULL,
  `id_ejercicio` int(11) NOT NULL,
  `series` int(11) DEFAULT NULL,
  `repeticiones` int(11) DEFAULT NULL,
  `descanso_seg` int(11) DEFAULT NULL,
  `orden` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`id_alumno`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `alumno_rutina`
--
ALTER TABLE `alumno_rutina`
  ADD PRIMARY KEY (`id_alumno`,`id_rutina`),
  ADD KEY `id_rutina` (`id_rutina`);

--
-- Indices de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  ADD PRIMARY KEY (`id_ejercicio`);

--
-- Indices de la tabla `profesor`
--
ALTER TABLE `profesor`
  ADD PRIMARY KEY (`id_profesor`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `rutina`
--
ALTER TABLE `rutina`
  ADD PRIMARY KEY (`id_rutina`),
  ADD KEY `id_profesor` (`id_profesor`);

--
-- Indices de la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  ADD PRIMARY KEY (`id_rutina`,`id_ejercicio`),
  ADD KEY `id_ejercicio` (`id_ejercicio`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alumno_rutina`
--
ALTER TABLE `alumno_rutina`
  ADD CONSTRAINT `alumno_rutina_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`),
  ADD CONSTRAINT `alumno_rutina_ibfk_2` FOREIGN KEY (`id_rutina`) REFERENCES `rutina` (`id_rutina`);

--
-- Filtros para la tabla `rutina`
--
ALTER TABLE `rutina`
  ADD CONSTRAINT `rutina_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `profesor` (`id_profesor`);

--
-- Filtros para la tabla `rutina_ejercicio`
--
ALTER TABLE `rutina_ejercicio`
  ADD CONSTRAINT `rutina_ejercicio_ibfk_1` FOREIGN KEY (`id_rutina`) REFERENCES `rutina` (`id_rutina`),
  ADD CONSTRAINT `rutina_ejercicio_ibfk_2` FOREIGN KEY (`id_ejercicio`) REFERENCES `ejercicio` (`id_ejercicio`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

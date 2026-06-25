-- phpMyAdmin compatible — MariaDB / XAMPP
-- GymTrack Pro v2
-- Cambios vs versión anterior:
--   - Se elimina tabla profesor (sistema monoprofesor)
--   - Se elimina id_profesor de rutina
--   - Se agrega cant_dias en rutina
--   - Se agrega campo dia en rutina_ejercicio
--   - PK de rutina_ejercicio actualizada para soportar mismo ejercicio en distintos días
--   - orden cambia de VARCHAR a INT

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `gymtrackpro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
USE `gymtrackpro`;

-- --------------------------------------------------------
-- ALUMNO
-- --------------------------------------------------------
CREATE TABLE `alumno` (
  `id_alumno`         int(11)      NOT NULL AUTO_INCREMENT,
  `nombre`            varchar(100) NOT NULL,
  `apellido`          varchar(100) NOT NULL,
  `email`             varchar(150) NOT NULL,
  `fecha_nacimiento`  date         DEFAULT NULL,
  `telefono`          varchar(50)  DEFAULT NULL,
  `perfil_fisico`     longtext     CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`perfil_fisico`)),
  `fecha_inscripcion` date         DEFAULT NULL,
  `estado`            enum('activo','inactivo','suspendido') DEFAULT 'activo',
  PRIMARY KEY (`id_alumno`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `alumno` (`nombre`, `apellido`, `email`, `fecha_nacimiento`, `telefono`, `perfil_fisico`, `fecha_inscripcion`, `estado`) VALUES
('Tomás',     'Aguirre',  'tomas.aguirre@gmail.com',    '1998-03-12', '3624-111111', '{"peso_kg":82,"altura_cm":178,"experiencia":"intermedio","dias_disponibles":4,"lesiones":[],"objetivo_secundario":"mejorar postura"}',          '2024-01-10', 'activo'),
('Camila',    'Romero',   'camila.romero@gmail.com',    '2000-07-25', '3624-222222', '{"peso_kg":61,"altura_cm":163,"experiencia":"principiante","dias_disponibles":3,"lesiones":["lumbar"],"objetivo_secundario":"tonificar piernas"}', '2024-02-14', 'activo'),
('Nicolás',   'Barrios',  'nicolas.barrios@gmail.com',  '1995-11-30', '3624-333333', '{"peso_kg":95,"altura_cm":182,"experiencia":"avanzado","dias_disponibles":5,"lesiones":["rodilla derecha"],"objetivo_secundario":"competir amateur"}','2024-03-01', 'activo'),
('Florencia', 'Medina',   'florencia.medina@gmail.com', '2003-05-18', '3624-444444', '{"peso_kg":55,"altura_cm":160,"experiencia":"principiante","dias_disponibles":2,"lesiones":[],"objetivo_secundario":"reducir estres"}',           '2024-04-20', 'inactivo'),
('Ezequiel',  'Leiva',    'ezequiel.leiva@gmail.com',   '1990-09-08', '3624-555555', '{"peso_kg":78,"altura_cm":175,"experiencia":"intermedio","dias_disponibles":4,"lesiones":["hombro izquierdo"],"objetivo_secundario":"ganar resistencia"}','2024-05-05', 'activo');

-- --------------------------------------------------------
-- EJERCICIO
-- --------------------------------------------------------
CREATE TABLE `ejercicio` (
  `id_ejercicio`   int(11)      NOT NULL AUTO_INCREMENT,
  `nombre`         varchar(150) NOT NULL,
  `grupo_muscular` varchar(100) DEFAULT NULL,
  `tipo`           enum('fuerza','cardio','movilidad','flexibilidad') DEFAULT NULL,
  `descripcion`    text         DEFAULT NULL,
  PRIMARY KEY (`id_ejercicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `ejercicio` (`nombre`, `grupo_muscular`, `tipo`, `descripcion`) VALUES
('Sentadilla',           'Cuádriceps / Glúteos',   'fuerza',    'Ejercicio compuesto de tren inferior con barra en trapecio. Descenso hasta paralelo.'),
('Press de banca',       'Pectoral / Tríceps',     'fuerza',    'Empuje horizontal en banco plano con barra o mancuernas.'),
('Peso muerto',          'Isquiotibiales / Lumbar','fuerza',    'Levantamiento desde el suelo. Trabaja la cadena posterior completa.'),
('Burpee',               'Cuerpo completo',        'cardio',    'Ejercicio funcional de alta intensidad que combina plancha, flexión y salto.'),
('Estiramiento de cadera','Cadera / Flexores',     'movilidad', 'Estiramiento dinámico de flexores de cadera en posición de lunge.');

-- --------------------------------------------------------
-- RUTINA
-- --------------------------------------------------------
CREATE TABLE `rutina` (
  `id_rutina`    int(11)      NOT NULL AUTO_INCREMENT,
  `nombre`       varchar(150) NOT NULL,
  `descripcion`  text         DEFAULT NULL,
  `nivel`        enum('principiante','intermedio','avanzado') DEFAULT NULL,
  `duracion_min` int(11)      DEFAULT NULL,
  `objetivo`     enum('perdida_peso','hipertrofia','resistencia','movilidad') DEFAULT NULL,
  `cant_dias`    int(11)      NOT NULL DEFAULT 3,
  PRIMARY KEY (`id_rutina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `rutina` (`nombre`, `descripcion`, `nivel`, `duracion_min`, `objetivo`, `cant_dias`) VALUES
('Fuerza Base',          'Introducción al entrenamiento con pesas. Énfasis en técnica.',                          'principiante', 45, 'hipertrofia',  3),
('Quema Total',          'Circuito de alta intensidad orientado a la pérdida de grasa corporal.',                 'intermedio',   60, 'perdida_peso', 4),
('Movilidad Activa',     'Sesión de movilidad articular y stretching dinámico para prevenir lesiones.',           'principiante', 30, 'movilidad',    2),
('Hipertrofia Avanzada', 'Rutina de volumen alto con técnicas de intensificación como drop sets y superseries.',  'avanzado',     75, 'hipertrofia',  5),
('Cardio Funcional',     'Combinación de ejercicios aeróbicos y funcionales para mejorar la resistencia.',        'intermedio',   50, 'resistencia',  3);

-- --------------------------------------------------------
-- ALUMNO_RUTINA (historial)
-- --------------------------------------------------------
CREATE TABLE `alumno_rutina` (
  `id_alumno`    int(11) NOT NULL,
  `id_rutina`    int(11) NOT NULL,
  `fecha_inicio` date    NOT NULL,
  `fecha_fin`    date    DEFAULT NULL,
  `estado`       enum('activa','finalizada','pausada') DEFAULT 'activa',
  PRIMARY KEY (`id_alumno`, `id_rutina`, `fecha_inicio`),
  KEY `id_rutina` (`id_rutina`),
  CONSTRAINT `alumno_rutina_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumno`  (`id_alumno`) ON DELETE CASCADE,
  CONSTRAINT `alumno_rutina_ibfk_2` FOREIGN KEY (`id_rutina`) REFERENCES `rutina`  (`id_rutina`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `alumno_rutina` (`id_alumno`, `id_rutina`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(1, 1, '2024-01-10', '2024-02-10', 'finalizada'),
(1, 4, '2024-02-11', NULL,         'activa'),
(2, 1, '2024-02-14', NULL,         'activa'),
(3, 4, '2024-03-01', NULL,         'activa'),
(5, 2, '2024-05-05', NULL,         'activa');

-- --------------------------------------------------------
-- RUTINA_EJERCICIO
-- --------------------------------------------------------
CREATE TABLE `rutina_ejercicio` (
  `id_rutina`    int(11)     NOT NULL,
  `id_ejercicio` int(11)     NOT NULL,
  `dia`          varchar(20) NOT NULL,
  `series`       int(11)     DEFAULT NULL,
  `repeticiones` int(11)     DEFAULT NULL,
  `descanso_seg` int(11)     DEFAULT NULL,
  `orden`        int(11)     DEFAULT NULL,
  PRIMARY KEY (`id_rutina`, `id_ejercicio`, `dia`),
  KEY `id_ejercicio` (`id_ejercicio`),
  CONSTRAINT `rutina_ejercicio_ibfk_1` FOREIGN KEY (`id_rutina`)    REFERENCES `rutina`    (`id_rutina`)    ON DELETE CASCADE,
  CONSTRAINT `rutina_ejercicio_ibfk_2` FOREIGN KEY (`id_ejercicio`) REFERENCES `ejercicio` (`id_ejercicio`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `rutina_ejercicio` (`id_rutina`, `id_ejercicio`, `dia`, `series`, `repeticiones`, `descanso_seg`, `orden`) VALUES
-- Fuerza Base (3 días)
(1, 1, 'Dia 1', 3, 12, 90,  1),
(1, 2, 'Dia 1', 3, 10, 90,  2),
(1, 3, 'Dia 2', 3, 8,  120, 1),
(1, 5, 'Dia 2', 3, 10, 30,  2),
(1, 4, 'Dia 3', 4, 15, 30,  1),
-- Quema Total (4 días)
(2, 4, 'Dia 1', 4, 15, 30,  1),
(2, 1, 'Dia 1', 4, 20, 45,  2),
(2, 2, 'Dia 2', 3, 12, 60,  1),
(2, 3, 'Dia 3', 3, 10, 90,  1),
(2, 4, 'Dia 4', 5, 20, 20,  1),
-- Movilidad Activa (2 días)
(3, 5, 'Dia 1', 3, 10, 30,  1),
(3, 4, 'Dia 2', 2, 10, 30,  1),
-- Hipertrofia Avanzada (5 días)
(4, 1, 'Dia 1', 5, 8,  120, 1),
(4, 2, 'Dia 2', 5, 8,  120, 1),
(4, 3, 'Dia 3', 4, 6,  150, 1),
(4, 4, 'Dia 4', 4, 15, 30,  1),
(4, 5, 'Dia 5', 3, 12, 30,  1),
-- Cardio Funcional (3 días)
(5, 4, 'Dia 1', 5, 20, 20,  1),
(5, 1, 'Dia 2', 3, 15, 60,  1),
(5, 3, 'Dia 3', 3, 10, 90,  1);

COMMIT;

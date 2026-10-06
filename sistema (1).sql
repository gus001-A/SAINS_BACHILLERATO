-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-06-2026 a las 18:57:14
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
-- Base de datos: `sistema`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `sexo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `usuario_id`, `nombre`, `apellido_paterno`, `apellido_materno`, `fecha_nacimiento`, `telefono`, `sexo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrador', 'Principal', 'Sistema', '1990-01-01', '1234567890', 'M', '2026-05-19 00:09:53', '2026-05-19 00:09:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apoyo_preguntas`
--

CREATE TABLE `apoyo_preguntas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `examen` bigint(20) UNSIGNED NOT NULL,
  `pregunta` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `apoyo_preguntas`
--

INSERT INTO `apoyo_preguntas` (`id`, `examen`, `pregunta`, `created_at`, `updated_at`) VALUES
(188, 1, 6, NULL, NULL),
(189, 1, 7, NULL, NULL),
(190, 1, 8, NULL, NULL),
(191, 1, 9, NULL, NULL),
(192, 1, 10, NULL, NULL),
(193, 1, 11, NULL, NULL),
(194, 1, 12, NULL, NULL),
(195, 1, 13, NULL, NULL),
(196, 1, 14, NULL, NULL),
(197, 1, 15, NULL, NULL),
(198, 1, 16, NULL, NULL),
(199, 1, 17, NULL, NULL),
(200, 1, 18, NULL, NULL),
(201, 1, 19, NULL, NULL),
(202, 1, 20, NULL, NULL),
(203, 1, 21, NULL, NULL),
(204, 1, 22, NULL, NULL),
(205, 2, 23, NULL, NULL),
(206, 2, 24, NULL, NULL),
(207, 2, 25, NULL, NULL),
(208, 2, 26, NULL, NULL),
(209, 2, 27, NULL, NULL),
(210, 2, 28, NULL, NULL),
(211, 2, 29, NULL, NULL),
(212, 2, 30, NULL, NULL),
(213, 2, 31, NULL, NULL),
(214, 2, 32, NULL, NULL),
(215, 2, 33, NULL, NULL),
(216, 2, 34, NULL, NULL),
(217, 2, 35, NULL, NULL),
(218, 2, 36, NULL, NULL),
(219, 2, 37, NULL, NULL),
(220, 2, 38, NULL, NULL),
(221, 2, 39, NULL, NULL),
(222, 2, 40, NULL, NULL),
(223, 2, 41, NULL, NULL),
(224, 2, 42, NULL, NULL),
(225, 2, 43, NULL, NULL),
(226, 2, 44, NULL, NULL),
(227, 2, 45, NULL, NULL),
(228, 2, 46, NULL, NULL),
(229, 2, 47, NULL, NULL),
(230, 3, 48, NULL, NULL),
(231, 3, 49, NULL, NULL),
(232, 3, 50, NULL, NULL),
(233, 3, 51, NULL, NULL),
(234, 3, 52, NULL, NULL),
(235, 3, 53, NULL, NULL),
(236, 4, 54, NULL, NULL),
(237, 4, 55, NULL, NULL),
(238, 4, 56, NULL, NULL),
(239, 4, 57, NULL, NULL),
(240, 4, 58, NULL, NULL),
(241, 4, 59, NULL, NULL),
(242, 4, 60, NULL, NULL),
(243, 4, 61, NULL, NULL),
(244, 4, 62, NULL, NULL),
(245, 4, 63, NULL, NULL),
(246, 4, 64, NULL, NULL),
(247, 4, 65, NULL, NULL),
(248, 4, 66, NULL, NULL),
(249, 4, 67, NULL, NULL),
(250, 4, 68, NULL, NULL),
(251, 4, 69, NULL, NULL),
(252, 4, 70, NULL, NULL),
(253, 4, 71, NULL, NULL),
(254, 4, 72, NULL, NULL),
(255, 4, 73, NULL, NULL),
(256, 4, 74, NULL, NULL),
(257, 4, 75, NULL, NULL),
(258, 4, 76, NULL, NULL),
(259, 4, 77, NULL, NULL),
(260, 4, 78, NULL, NULL),
(261, 4, 79, NULL, NULL),
(262, 5, 80, NULL, NULL),
(263, 5, 81, NULL, NULL),
(264, 5, 82, NULL, NULL),
(265, 5, 83, NULL, NULL),
(266, 5, 84, NULL, NULL),
(267, 6, 85, NULL, NULL),
(268, 6, 86, NULL, NULL),
(269, 6, 87, NULL, NULL),
(270, 6, 88, NULL, NULL),
(271, 6, 89, NULL, NULL),
(272, 6, 90, NULL, NULL),
(273, 6, 91, NULL, NULL),
(274, 6, 92, NULL, NULL),
(275, 6, 93, NULL, NULL),
(276, 6, 94, NULL, NULL),
(277, 6, 95, NULL, NULL),
(278, 6, 96, NULL, NULL),
(279, 6, 97, NULL, NULL),
(280, 6, 98, NULL, NULL),
(281, 6, 99, NULL, NULL),
(282, 6, 100, NULL, NULL),
(283, 7, 6, NULL, NULL),
(284, 7, 10, NULL, NULL),
(285, 7, 15, NULL, NULL),
(286, 7, 23, NULL, NULL),
(287, 7, 28, NULL, NULL),
(288, 7, 33, NULL, NULL),
(289, 7, 40, NULL, NULL),
(290, 7, 48, NULL, NULL),
(291, 7, 51, NULL, NULL),
(292, 7, 54, NULL, NULL),
(293, 7, 58, NULL, NULL),
(294, 7, 64, NULL, NULL),
(295, 7, 70, NULL, NULL),
(296, 7, 80, NULL, NULL),
(297, 7, 83, NULL, NULL),
(298, 7, 85, NULL, NULL),
(299, 7, 87, NULL, NULL),
(300, 7, 90, NULL, NULL),
(301, 7, 95, NULL, NULL),
(302, 7, 99, NULL, NULL),
(303, 8, 7, NULL, NULL),
(304, 8, 12, NULL, NULL),
(305, 8, 18, NULL, NULL),
(306, 8, 24, NULL, NULL),
(307, 8, 29, NULL, NULL),
(308, 8, 35, NULL, NULL),
(309, 8, 42, NULL, NULL),
(310, 8, 49, NULL, NULL),
(311, 8, 52, NULL, NULL),
(312, 8, 55, NULL, NULL),
(313, 8, 60, NULL, NULL),
(314, 8, 65, NULL, NULL),
(315, 8, 72, NULL, NULL),
(316, 8, 81, NULL, NULL),
(317, 8, 84, NULL, NULL),
(318, 8, 86, NULL, NULL),
(319, 8, 89, NULL, NULL),
(320, 8, 92, NULL, NULL),
(321, 8, 96, NULL, NULL),
(322, 8, 98, NULL, NULL),
(323, 9, 8, NULL, NULL),
(324, 9, 13, NULL, NULL),
(325, 9, 19, NULL, NULL),
(326, 9, 25, NULL, NULL),
(327, 9, 30, NULL, NULL),
(328, 9, 37, NULL, NULL),
(329, 9, 44, NULL, NULL),
(330, 9, 48, NULL, NULL),
(331, 9, 53, NULL, NULL),
(332, 9, 56, NULL, NULL),
(333, 9, 61, NULL, NULL),
(334, 9, 67, NULL, NULL),
(335, 9, 74, NULL, NULL),
(336, 9, 80, NULL, NULL),
(337, 9, 82, NULL, NULL),
(338, 9, 87, NULL, NULL),
(339, 9, 90, NULL, NULL),
(340, 9, 93, NULL, NULL),
(341, 9, 97, NULL, NULL),
(342, 9, 100, NULL, NULL),
(343, 10, 9, NULL, NULL),
(344, 10, 14, NULL, NULL),
(345, 10, 20, NULL, NULL),
(346, 10, 26, NULL, NULL),
(347, 10, 32, NULL, NULL),
(348, 10, 38, NULL, NULL),
(349, 10, 46, NULL, NULL),
(350, 10, 50, NULL, NULL),
(351, 10, 52, NULL, NULL),
(352, 10, 57, NULL, NULL),
(353, 10, 62, NULL, NULL),
(354, 10, 68, NULL, NULL),
(355, 10, 76, NULL, NULL),
(356, 10, 81, NULL, NULL),
(357, 10, 83, NULL, NULL),
(358, 10, 86, NULL, NULL),
(359, 10, 91, NULL, NULL),
(360, 10, 94, NULL, NULL),
(361, 10, 98, NULL, NULL),
(362, 10, 99, NULL, NULL),
(373, 20, 24, NULL, NULL),
(374, 20, 25, NULL, NULL),
(375, 20, 28, NULL, NULL),
(376, 20, 31, NULL, NULL),
(377, 20, 32, NULL, NULL),
(378, 20, 33, NULL, NULL),
(379, 20, 37, NULL, NULL),
(380, 20, 38, NULL, NULL),
(381, 20, 41, NULL, NULL),
(382, 20, 47, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area_preguntas`
--

CREATE TABLE `area_preguntas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `area_preguntas`
--

INSERT INTO `area_preguntas` (`id`, `nombre`, `created_at`, `updated_at`) VALUES
(1, 'BIOLOGIA', NULL, NULL),
(2, 'FISICA', NULL, NULL),
(3, 'MATEMATICAS', NULL, NULL),
(4, 'QUIMICA', NULL, NULL),
(5, 'ESTADISTICA', NULL, NULL),
(6, 'PSICOLOGIA', NULL, NULL),
(7, 'CALCULO', NULL, NULL),
(8, 'DERECHO', NULL, NULL),
(9, 'HISTORIA', NULL, NULL),
(10, 'SOCIOLOGIA', NULL, NULL),
(11, 'ARTES', NULL, NULL),
(12, 'FILOSOFIA', NULL, NULL),
(13, 'LITERATURA', NULL, NULL),
(14, 'BASES PARA LA DOCENCIA', NULL, NULL),
(15, 'HEMISFERIO CEREBRAL', NULL, NULL),
(16, 'ORIENTACION VOCACIONAL', NULL, NULL),
(17, 'LENGUAJE ESCRITO', NULL, NULL),
(18, 'ESPANOL', NULL, NULL),
(19, 'PENSAMIENTO MATEMATICO', NULL, NULL),
(20, 'GEOGRAFIA', NULL, NULL),
(21, 'FORMACION CIVICA Y ETICA', NULL, NULL),
(22, 'ORIENTACION A RESULTADOS', NULL, NULL),
(23, 'INGLES', NULL, NULL),
(24, 'ANATOMIA', NULL, NULL),
(25, 'CULTURA GENERAL', NULL, NULL),
(26, 'ARITMETICA', NULL, NULL),
(27, 'IQ', NULL, NULL),
(28, 'HUMANIDADES', NULL, NULL),
(29, 'ECONOMIA', NULL, NULL),
(30, 'DISENO DE EXPERIENCIAS EDUCATIVAS', NULL, NULL),
(31, 'PROMOCION DEL APRENDIZAJE', NULL, NULL),
(32, 'PRODUCCION DE MATERIAL DEDACTICO', NULL, NULL),
(33, 'DOMINIO DE LA DISCIPLINA', NULL, NULL),
(34, 'PLANIFICACION DEL CURSO', NULL, NULL),
(35, 'AMBIENTES DE APRENDIZAJE', NULL, NULL),
(36, 'ESTRATEGIAS, METODOS Y TECNICAS DE ENSENANZA', NULL, NULL),
(37, 'MOTIVACION', NULL, NULL),
(38, 'EVALUACION', NULL, NULL),
(39, 'COMUNICACION Y GESTION', NULL, NULL),
(40, 'TECNOLOGIAS DE LA INFORMACION Y DE LA COMUNICACION', NULL, NULL),
(41, 'CONOCIMIENTO DEL APRENDIZAJE', NULL, NULL),
(42, 'MEJORA CONTINUA', NULL, NULL),
(43, 'RESPONSABILIDAD LEGAL Y ETICA', NULL, NULL),
(44, 'GESTION ESCOLAR', NULL, NULL),
(45, 'RESOLUCION DE CASOS EDUCATIVOS', NULL, NULL),
(46, 'CONOCIMIENTO DEL ESTUDIANTE', NULL, NULL),
(47, 'ESTRATEGIAS Y TECNICAS DE APRENDIZAJE', NULL, NULL),
(48, 'DESEMPENO DOCENTE EFICAZ', NULL, NULL),
(49, 'CONOCIMIENTOS BASICOS PARA DOCENTES', NULL, NULL),
(50, 'SITUACIONES DE APRENDIZAJE', NULL, NULL),
(51, 'MODELO HERMAN', NULL, NULL),
(52, 'MODELO DE FEDERAL Y SILVERMAN', NULL, NULL),
(53, 'MODELO DE KOLB', NULL, NULL),
(54, 'MODELO DE BANDLER Y GRINDER', NULL, NULL),
(55, 'MODELO DE INTELIGENCIA MULTIPLES DE GARDNER', NULL, NULL),
(56, 'PENSAMIENTO ANALITICO', NULL, NULL),
(57, 'COMPRENSION LECTORA', NULL, NULL),
(58, 'ADMINISTRACION', NULL, NULL),
(59, 'ENTORNO DE MEXICO : HISTORIA Y GEOGRAFIA', NULL, NULL),
(60, 'ESTILOS DE APRENDIZAJE', NULL, NULL),
(61, 'ESTRUCTURA DE LA LENGUA', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignatura`
--

CREATE TABLE `asignatura` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asignatura`
--

INSERT INTO `asignatura` (`id`, `nombre`, `created_at`, `updated_at`) VALUES
(1, 'BIOLOGIA', NULL, NULL),
(2, 'FISICA', NULL, NULL),
(3, 'MATEMATICAS', NULL, NULL),
(4, 'QUIMICA', NULL, NULL),
(5, 'ESTADISTICA', NULL, NULL),
(6, 'PSICOLOGIA', NULL, NULL),
(7, 'CALCULO', NULL, NULL),
(8, 'DERECHO', NULL, NULL),
(9, 'HISTORIA', NULL, NULL),
(10, 'SOCIOLOGIA', NULL, NULL),
(11, 'ARTES', NULL, NULL),
(12, 'FILOSOFIA', NULL, NULL),
(13, 'LITERATURA', NULL, NULL),
(14, 'BASES PARA LA DOCENCIA', NULL, NULL),
(15, 'HEMISFERIO CEREBRAL', NULL, NULL),
(16, 'ORIENTACION VOCACIONAL', NULL, NULL),
(17, 'LENGUAJE ESCRITO', NULL, NULL),
(18, 'ESPANOL', NULL, NULL),
(19, 'PENSAMIENTO MATEMATICO', NULL, NULL),
(20, 'GEOGRAFIA', NULL, NULL),
(21, 'FORMACION CIVICA Y ETICA', NULL, NULL),
(22, 'ORIENTACION A RESULTADOS', NULL, NULL),
(23, 'INGLES', NULL, NULL),
(24, 'ANATOMIA', NULL, NULL),
(25, 'CULTURA GENERAL', NULL, NULL),
(26, 'ARITMETICA', NULL, NULL),
(27, 'IQ', NULL, NULL),
(28, 'HUMANIDADES', NULL, NULL),
(29, 'ECONOMIA', NULL, NULL),
(30, 'DISENO DE EXPERIENCIAS EDUCATIVAS', NULL, NULL),
(31, 'PROMOCION DEL APRENDIZAJE', NULL, NULL),
(32, 'PRODUCCION DE MATERIAL DEDACTICO', NULL, NULL),
(33, 'DOMINIO DE LA DISCIPLINA', NULL, NULL),
(34, 'PLANIFICACION DEL CURSO', NULL, NULL),
(35, 'AMBIENTES DE APRENDIZAJE', NULL, NULL),
(36, 'ESTRATEGIAS, METODOS Y TECNICAS DE ENSENANZA', NULL, NULL),
(37, 'MOTIVACION', NULL, NULL),
(38, 'EVALUACION', NULL, NULL),
(39, 'COMUNICACION Y GESTION', NULL, NULL),
(40, 'TECNOLOGIAS DE LA INFORMACION Y DE LA COMUNICACION', NULL, NULL),
(41, 'CONOCIMIENTO DEL APRENDIZAJE', NULL, NULL),
(42, 'MEJORA CONTINUA', NULL, NULL),
(43, 'RESPONSABILIDAD LEGAL Y ETICA', NULL, NULL),
(44, 'GESTION ESCOLAR', NULL, NULL),
(45, 'RESOLUCION DE CASOS EDUCATIVOS', NULL, NULL),
(46, 'CONOCIMIENTO DEL ESTUDIANTE', NULL, NULL),
(47, 'ESTRATEGIAS Y TECNICAS DE APRENDIZAJE', NULL, NULL),
(48, 'DESEMPENO DOCENTE EFICAZ', NULL, NULL),
(49, 'CONOCIMIENTOS BASICOS PARA DOCENTES', NULL, NULL),
(50, 'SITUACIONES DE APRENDIZAJE', NULL, NULL),
(51, 'MODELO HERMAN', NULL, NULL),
(52, 'MODELO DE FEDERAL Y SILVERMAN', NULL, NULL),
(53, 'MODELO DE KOLB', NULL, NULL),
(54, 'MODELO DE BANDLER Y GRINDER', NULL, NULL),
(55, 'MODELO DE INTELIGENCIA MULTIPLES DE GARDNER', NULL, NULL),
(56, 'PENSAMIENTO ANALITICO', NULL, NULL),
(57, 'COMPRENSION LECTORA', NULL, NULL),
(58, 'ADMINISTRACIÓN', NULL, NULL),
(59, 'ENTORNO DE MEXICO : HISTORIA Y GEOGRAFIA', NULL, NULL),
(60, 'ESTILOS DE APRENDIZAJE', NULL, NULL),
(61, 'ESTRUCTURA DE LA LENGUA', NULL, NULL),
(62, 'Introducción a la programación', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

CREATE TABLE `carreras` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tronco_id` bigint(20) UNSIGNED NOT NULL,
  `id_asignatura_1` bigint(20) UNSIGNED NOT NULL,
  `id_asignatura_2` bigint(20) UNSIGNED DEFAULT NULL,
  `id_asignatura_3` bigint(20) UNSIGNED DEFAULT NULL,
  `calificacion_minima` decimal(5,2) DEFAULT NULL COMMENT 'Calificación mínima requerida para acceder a esta carrera (ej: 85.00)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carreras`
--

INSERT INTO `carreras` (`id`, `nombre`, `tronco_id`, `id_asignatura_1`, `id_asignatura_2`, `id_asignatura_3`, `calificacion_minima`, `created_at`, `updated_at`) VALUES
(1, 'INGENIERIA EN DESARROLLO RURAL', 1, 1, 2, 26, NULL, NULL, NULL),
(2, 'INGENIERIA EN FITOSANIDAD', 1, 1, 2, 26, NULL, NULL, NULL),
(3, 'INGENIERIA EN HORTICULTURA', 1, 1, 2, 26, NULL, NULL, NULL),
(4, 'INGENIERIA EN PRODUCCION ANIMAL', 1, 1, 2, 26, NULL, NULL, NULL),
(5, 'INGENIERIA EN PRODUCCION VEGETAL', 1, 1, 2, 26, NULL, NULL, NULL),
(6, 'LICENCIATURA EN CIENCIAS APLICADAS AL DEPORTE', 2, 1, 3, 4, NULL, NULL, NULL),
(7, 'LICENCIATURA EN COMUNICACION HUMANA', 2, 1, 3, 4, NULL, NULL, NULL),
(8, 'LICENCIATURA EN ENFERMERIA', 2, 1, 3, 4, NULL, NULL, NULL),
(9, 'LICENCIATURA EN FARMACIA', 2, 1, 3, 4, NULL, NULL, NULL),
(10, 'LICENCIATURA EN NUTRICION', 2, 1, 3, 4, NULL, NULL, NULL),
(11, 'LICENCIATURA EN PSICOLOGIA', 2, 1, 3, 4, NULL, NULL, NULL),
(12, 'MEDICO CIRUJANO', 2, 1, 3, 4, NULL, NULL, NULL),
(13, 'INGENIERIA ELECTRICA', 3, 3, 2, 4, NULL, NULL, NULL),
(14, 'INGENIERIA INDUSTRIAL', 3, 3, 2, 4, NULL, NULL, NULL),
(15, 'INGENIERIA MECANICA', 3, 3, 2, 4, NULL, NULL, NULL),
(16, 'INGENIERIA QUIMICA', 3, 3, 2, 4, NULL, NULL, NULL),
(17, 'LICENCIATURA EN CIENCIAS', 3, 3, 2, 4, NULL, NULL, NULL),
(18, 'QUIMICO INDUSTRIAL', 3, 3, 2, 4, NULL, NULL, NULL),
(19, 'CONTADOR PUBLICO', 5, 28, 13, 9, 89.00, NULL, NULL),
(20, 'LICENCIATURA EN ADMINISTRACION', 5, 28, 13, 9, NULL, NULL, NULL),
(21, 'LICENCIATURA EN ADMINISTRACION PUBLICA', 5, 28, 13, 9, NULL, NULL, NULL),
(22, 'LICENCIATURA EN CIENCIAS POLITICAS', 5, 28, 13, 9, NULL, NULL, NULL),
(23, 'LICENCIATURA EN DERECHO', 5, 28, 13, 9, NULL, NULL, NULL),
(24, 'LICENCIATURA EN ECONOMIA', 5, 28, 13, 9, NULL, NULL, NULL),
(25, 'LICENCIATURA EN INFORMATICA', 5, 28, 13, 9, NULL, NULL, NULL),
(26, 'LICENCIATURA EN RELACIONES PUBLICAS', 5, 28, 13, 9, NULL, NULL, NULL),
(27, 'LICENCIATURA EN SEGURIDAD CIUDADANA', 5, 28, 13, 9, NULL, NULL, NULL),
(28, 'LICENCIATURA EN SOCIOLOGIA', 5, 28, 13, 9, NULL, NULL, NULL),
(29, 'LICENCIATURA EN TRABAJO SOCIAL', 5, 28, 13, 9, NULL, NULL, NULL),
(30, 'LICENCIATURA EN TURISMO', 5, 28, 13, 9, NULL, NULL, NULL),
(31, 'LICENCIATURA EN ANTROPOLOGIA SOCIAL', 6, 13, 12, 9, NULL, NULL, NULL),
(32, 'LICENCIATURA EN ARQUITECTURA', 6, 13, 12, 9, NULL, NULL, NULL),
(33, 'LICENCIATURA EN ARTES', 6, 13, 12, 9, NULL, NULL, NULL),
(34, 'LICENCIATURA EN FILOSOFIA', 6, 13, 12, 9, NULL, NULL, NULL),
(35, 'LICENCIATURA EN GESTION Y COMUNICACION INTERCULTURAL', 6, 13, 12, 9, NULL, NULL, NULL),
(36, 'LICENCIATURA EN HISTORIA', 6, 13, 12, 9, NULL, NULL, NULL),
(37, 'LICENCIATURA EN LETRAS HISPANICAS', 6, 13, 12, 9, NULL, NULL, NULL),
(38, 'LICENCIATURA EN TEATRO', 6, 13, 12, 9, NULL, NULL, NULL),
(39, 'LICENCIATURA EN BIOLOGIA', 4, 1, 2, 4, 80.00, NULL, NULL),
(40, 'LICENCIATURA EN CIENCIAS AMBIENTALES', 4, 1, 2, 4, NULL, NULL, NULL),
(41, 'LICENCIATURA EN CIENCIAS DE LA EDUCACION', 7, 12, 13, 28, NULL, NULL, NULL),
(42, 'LICENCIATURA EN COMUNICACION Y TECNOLOGIA EDUCATIVA', 7, 12, 13, 28, NULL, NULL, NULL),
(43, 'LICENCIATURA EN DISENO', 7, 12, 13, 28, NULL, NULL, NULL),
(44, 'LICENCIATURA EN DOCENCIA', 7, 12, 13, 28, NULL, NULL, NULL),
(45, 'LICENCIATURA EN EDUCACION FISICA', 7, 12, 13, 28, NULL, NULL, NULL),
(46, 'LICENCIATURA EN ENSENANZA DEL FRANCES', 7, 12, 13, 28, NULL, NULL, NULL),
(47, 'LICENCIATURA EN ENSENANZA DEL INGLES', 7, 12, 13, 28, NULL, NULL, NULL),
(48, 'LICENCIATURA EN PSICOLOGIA', 7, 12, 13, 28, NULL, NULL, NULL),
(49, 'CIENCIAS DE LA COMUNICACION', 5, 28, 13, 9, 80.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases`
--

CREATE TABLE `clases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_asignatura` bigint(20) UNSIGNED NOT NULL,
  `num_clase` int(11) NOT NULL,
  `nombre_clase` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clases`
--

INSERT INTO `clases` (`id`, `id_asignatura`, `num_clase`, `nombre_clase`, `link`, `url`, `created_at`, `updated_at`) VALUES
(1, 17, 1, 'Sustantivos y adjetivos', 'https://vimeo.com/356512619', 'https://drive.google.com/file/d/1RgqJl-gAGH8UmShN6jJ_XUJx3Pry9eG2/view?usp=sharing', NULL, '2026-05-22 02:32:36'),
(2, 17, 2, 'Verbos y adverbios', 'https://vimeo.com/356516521', 'https://drive.google.com/file/d/1M6WcfTKTchUDXLrFmlauVHO-Z-dmsxTy/view?usp=sharing', '2026-05-22 02:32:14', '2026-05-22 02:32:14'),
(3, 17, 3, 'Preposición', 'https://vimeo.com/356523797', 'https://drive.google.com/file/d/1yMvdFZLBUBnJ5IikKrOo8egnrewfoGG6/view?usp=sharing', '2026-05-22 02:33:26', '2026-05-22 02:33:26'),
(4, 17, 4, 'Sujeto y predicado', 'https://vimeo.com/356684560', 'https://drive.google.com/file/d/1yMvdFZLBUBnJ5IikKrOo8egnrewfoGG6/view?usp=sharing', '2026-05-22 02:35:30', '2026-05-22 02:35:30'),
(5, 17, 5, 'Oración', 'https://vimeo.com/356691244', 'https://drive.google.com/file/d/1GpeWYIeeoTHiwb-VACmgUcQpveTXrJTQ/view?usp=sharing', '2026-05-22 02:37:46', '2026-05-22 02:37:46'),
(6, 17, 6, 'Pleonasmo', 'https://vimeo.com/356694756', 'https://drive.google.com/file/d/1SqycR5dDRYmUxajKnon-THWboJYHyVRD/view?usp=sharing', '2026-05-22 02:38:37', '2026-05-22 02:38:37'),
(7, 17, 7, 'Puntuación', 'https://vimeo.com/356696465', 'https://drive.google.com/file/d/156HEFjYjuKg6NvojJiteocW8ESWW9If0/view?usp=sharing', '2026-05-22 02:38:51', '2026-05-22 02:39:13'),
(8, 17, 8, 'Secuencia lógica, nexos y otros locuciones', 'https://vimeo.com/357879476', 'https://drive.google.com/file/d/1ojcEWwD0NbhlnVpCpGTQHZjmcP3HUNsZ/view?usp=sharing', '2026-05-22 02:39:41', '2026-05-22 02:39:41'),
(9, 17, 9, 'Heteronimos', 'https://vimeo.com/360674475', 'https://drive.google.com/file/d/1ZbrNuQ8BqsvFM4hi_AolFpCn-uI8cuj5/view?usp=sharing', '2026-05-22 02:40:12', '2026-05-22 02:40:12'),
(74, 61, 1, 'Clase 1 Verbos', 'https://vimeo.com/357903613', 'https://drive.google.com/file/d/1wCT-CZuxXLN86ttU_iaDaaCncuwEGN0_/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(75, 61, 2, 'Clase 2 Reglas ortograficas puntuación', 'https://vimeo.com/357906397', 'https://drive.google.com/file/d/1TjZtFOYvGhUrn6JNXNVDaKosz-TfWZkT/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(76, 61, 3, 'Clase 3 Reglas ortograficas acentuación 1', 'https://vimeo.com/357912078', 'https://drive.google.com/file/d/1FHdPbfzo6sILiNvmMHVrc_T9KDG0yWPG/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(77, 61, 4, 'Clase 4 Reglas ortograficas acentuación 2', 'https://vimeo.com/357916224', 'https://drive.google.com/file/d/1u-LgXvGs-k6cZiUhTJoqPnBqC2fiVqx6/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(78, 61, 5, 'Clase 5 Reglas ortograficas acentuación 3', 'https://vimeo.com/357918314', 'https://drive.google.com/file/d/1GzF50l18g9x_50oAqE-haVF0Eo_c_arF/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(79, 61, 6, 'Clase 6 relaciones semanticas', 'https://vimeo.com/357954862', 'https://drive.google.com/file/d/1EE89gJg9msFE1dbn1Af64yPhSHNND8WL/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(80, 61, 7, 'Clase 7 preposiciones y adverbios', 'https://vimeo.com/357919851', 'https://drive.google.com/file/d/1_8xhOlXlyIF4L54tU22_2ZmnDShX2Ddj/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(81, 61, 8, 'Clase 8 lógica textual1', 'https://vimeo.com/357879476', 'https://drive.google.com/file/d/1Vx27P_Gbrt4k3CWdQTX6CqjMsMb0KM7K/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(82, 61, 9, 'Clase 9 lógica textual 2', 'https://vimeo.com/357875876', 'https://drive.google.com/file/d/1hiCecu6lRsvMDWpRCRR2AiWbalgOmG49/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(83, 61, 10, 'Clase 10 grafias S- C -Z', 'https://vimeo.com/358084010', 'https://drive.google.com/file/d/1EpkdCpPeChvKZUwgfZaANYVMw3oMALkp/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(84, 61, 11, 'Clase 11 grafias G Y J', 'https://vimeo.com/358091055', 'https://drive.google.com/file/d/1rBFSCKBvPGRbgOgtK0XdUQl_PHplLvER/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(85, 61, 12, 'Clase 12 B Y V', 'https://vimeo.com/357893620', 'https://drive.google.com/file/d/1REOLySE4x_dNh-g90lulmVah7c7gPB70/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(86, 61, 13, 'Clase 13 R Y RR', 'https://vimeo.com/357892488', 'https://drive.google.com/file/d/1H5yRK1VuO8XKUxKm111XG87hg4cbZPJv/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(87, 57, 1, 'Clase introducción', 'https://vimeo.com/360944019', NULL, '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(88, 57, 2, 'Clase primera parte', 'https://vimeo.com/360944782', 'https://drive.google.com/file/d/1HYqrr3No3gErxJvBjIEYz0u-zggLyDL8/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(89, 57, 3, 'Clase segunda parte', 'https://vimeo.com/360948216', 'https://drive.google.com/file/d/1EXhNbWgLhF21BavnfM1nicrzWL_sIPrC/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(90, 57, 4, 'Clase tercera parte', 'https://vimeo.com/360948216', 'https://drive.google.com/file/d/14HY4hdwfWmicLtXZIixAAeIm6VP0XsJ9/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(91, 57, 5, 'Clase cuarta parte', 'https://vimeo.com/360942015', 'https://drive.google.com/file/d/1on9TDQ4SM3Gm16wkDA065MIDDVRBhJ9-/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(92, 57, 6, 'Clase quinta parte', 'https://vimeo.com/360946881', 'https://drive.google.com/file/d/1-giVu75jOpxHPVYK-oY4MpuvyEtIZARx/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(93, 57, 7, 'Clase sexta parte', 'https://vimeo.com/360951112', 'https://drive.google.com/file/d/18s0YXd9vzHquuyhDZgkf4qis8PLMbCj_/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(94, 57, 8, 'Clase septima parte', 'https://vimeo.com/360950002', 'https://drive.google.com/file/d/1y43xzulaSj8Dh1_VawcHPK8iXZx5R8fY/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(95, 23, 1, 'Clse 1 wh question', 'https://vimeo.com/383565235', 'https://drive.google.com/file/d/1uaTr5jvYTcuoGPwFmFj_9vCbPjGyUILG/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(96, 23, 2, 'Clase 2 simple present', 'https://vimeo.com/383566546', 'https://drive.google.com/file/d/1dI_hARtN0gn6cH9xgkAeCH8y1C2mKp_Z/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(97, 23, 3, 'Clase 3 Ver to be', 'https://vimeo.com/383567877', 'https://drive.google.com/file/d/1J-5nu1zH3Sf1utVUwC0NxmtvppUytmlT/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(98, 23, 4, 'Clase 4 expresiones comunes', 'https://vimeo.com/383568777', 'https://drive.google.com/file/d/1eBG67qEdGfD-3CAp182N9e8cVNtXzSfF/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(99, 23, 5, 'Clase 5 obligaciones', 'https://vimeo.com/383571152', 'https://drive.google.com/file/d/1HoJM_ET6YC5-e0vt759fHTaKwOmGtXSW/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(100, 23, 6, 'Clase 6 comparativos y superlativos', 'https://vimeo.com/383573853', 'https://drive.google.com/file/d/1giGlXuUar7NlK6fk7jXrcNVH8-miLAGs/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(101, 23, 7, 'Clase 7 restaurante', 'https://vimeo.com/383575547', 'https://drive.google.com/file/d/15MzMWtxeiHjpU9RfPaH_A92gkMrEQdeE/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(102, 23, 8, 'Clase 8 permisos', 'https://vimeo.com/383576658', 'https://drive.google.com/file/d/1uMbhBCcDQFvHdcAuzrhcIlXBudE5sv7E/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(103, 23, 9, 'Clase 9 futuro', 'https://vimeo.com/383577563', 'https://drive.google.com/file/d/1Ualp0fgOLZzwWraSos4nc0zrn3ezRfZT/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(104, 23, 10, 'Clase 10 Lecturas', 'https://vimeo.com/383582696', 'https://drive.google.com/file/d/1XfxSttqGY86iKHGd0urGQz5e6ySkqctf/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(105, 23, 11, 'Clase 11 Lecturas segunda parte', 'https://vimeo.com/383585281', 'https://drive.google.com/file/d/13QcXt56ZZm1VBdJlqHamDVlwUqJPiYdu/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(106, 23, 12, 'Clase 13 Información personal', 'https://vimeo.com/383588761', 'https://drive.google.com/file/d/1Kz57lTeyRsKChprMULn88xCc7HBvGAfg/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(107, 23, 13, 'Clase 14 used to', 'https://vimeo.com/383589865', 'https://drive.google.com/file/d/1QAG4lW-OvlH266WvXkmoy0XZmlgUzNIH/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(108, 23, 14, 'Clase 15 tiempos verbales', 'https://vimeo.com/383591378', NULL, '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(109, 56, 1, 'Clase introducción', 'https://vimeo.com/362617177', 'https://drive.google.com/file/d/1h73g4rvuOalE2g-lj5oEz6GFAu_7XjwU/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(110, 56, 2, 'Clase 1 Fundamentos del pensamiento analítico', 'https://vimeo.com/362619510', 'https://drive.google.com/file/d/1qb861YljsLKrDTUbTKIlKriSxmHLpuWk/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(111, 56, 3, 'Clase 2 Información textual', 'https://vimeo.com/362620103', 'https://drive.google.com/file/d/15uqDXR1Wajsl5-oROXkwfePb1suuEDDZ/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(112, 56, 4, 'Clase 3 Representaciones gráficas', 'https://vimeo.com/362620548', 'https://drive.google.com/file/d/1nAcOKCsljNIUmq7KjejBaAryXLmTPkt8/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(113, 56, 5, 'Clase 4 Relaciones analogicas', 'https://vimeo.com/362620937', 'https://drive.google.com/file/d/1aoiJ5MKCEiGrT0QVHngp53qYr3FBZKBh/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(114, 19, 1, 'Clase 1 Jerarquia de operaciones', 'https://vimeo.com/356291521', 'https://drive.google.com/file/d/1dIgtCyHeTC-NJm7cmyL1hK90TWHwEM7O/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(115, 19, 2, 'Clase 2 Operaciones elementales', 'https://vimeo.com/356298031', 'https://drive.google.com/file/d/1CslueQC44ycbZzIjGQ23iN_P9aTocEWr/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(116, 19, 3, 'Clase 3 Razones y proporcionalidad', 'https://vimeo.com/356300127', 'https://drive.google.com/file/d/1P6Re2YiqmR0FWXWfn4rmlwHTvFmc_w4h/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(117, 19, 4, 'Clase 4 Expresiones algebraicas', 'https://vimeo.com/356302079', 'https://drive.google.com/file/d/1DBiuimMMw2NXJBntUHAgGeeT0lPEdJVD/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(118, 19, 5, 'Clase 5 Productos notables', 'https://vimeo.com/356303421', 'https://drive.google.com/file/d/1CHq3IgrSJcUFtlwAwCqC0GYBOi51c7in/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(119, 19, 6, 'Clase 6 Factorización', 'https://vimeo.com/356304681', 'https://drive.google.com/file/d/13hHbMZyxzyPIQ0RqAYw33uvbICWzmARF/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(120, 19, 7, 'Clase 7 Ecuaciones lineales', 'https://vimeo.com/356308154', 'https://drive.google.com/file/d/1y8C8-kq9uYdroQinOZ-fV90vK_ovq7bN/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(121, 19, 8, 'Clase 8 Ecuaciones cuadraticas', 'https://vimeo.com/356309399', 'https://drive.google.com/file/d/1zL0M9vk9vlZivKR_k-JttbNPkGPHCdy0/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(122, 19, 9, 'Clase 9 Sistema de ecuaciones', 'https://vimeo.com/356310919', 'https://drive.google.com/file/d/1yQGJPBuuBomKLulcuCOaJrJxcQ0gl8un/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(123, 19, 10, 'Clase 10 Medidas de tendencia central', 'https://vimeo.com/356312480', 'https://drive.google.com/file/d/1VKYtRrKcFdZXQM9ZZtUlQpCuvqPjQrKU/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(124, 19, 11, 'Clase 11 Medidas de posisición', 'https://vimeo.com/356314301', 'https://drive.google.com/file/d/1O590rmZApWE-TxlLciUNRT3mrXDh2F9R/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(125, 19, 12, 'Clase 12 Probabilidad', 'https://vimeo.com/356315500', 'https://drive.google.com/file/d/1OltYA-Se7hcPTcIoFobVPZKNVDgLJlEC/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(126, 19, 13, 'Clase 13 Línea recta', 'https://vimeo.com/356317487', 'https://drive.google.com/file/d/1QnZMRH4uhGdQSCyT69uwD7OFIF839N12/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(127, 19, 14, 'Clase 14 Razonamiento geométrico', 'https://vimeo.com/356503500', 'https://drive.google.com/file/d/11rM8dHqfbOKC8T6EqLdVookTRDtxFczk/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(128, 19, 15, 'Clase 15 Resolución de triángulos', 'https://vimeo.com/356507308', 'https://drive.google.com/file/d/1G1Qj2uIuK_31nrpYaFybpimsHmFbFmpt/view?usp=sharing', '2026-05-21 21:14:01', '2026-05-21 21:14:01'),
(129, 26, 1, 'Introducción al álgebra', 'https://vimeo.com/356317487', 'https://docs.google.com/file/d/0B8x71XDp4ZBfaXZ6c1ctNjFqZmc/edit?resourcekey=0-zCgDhb1MFBaJlxeYzFWLgw', '2026-06-03 23:04:37', '2026-06-03 23:04:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cupones`
--

CREATE TABLE `cupones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `usuario_uso` bigint(20) UNSIGNED DEFAULT NULL,
  `usuario_genero` bigint(20) UNSIGNED NOT NULL,
  `estatus` varchar(255) NOT NULL DEFAULT 'activo',
  `fecha_genero` date NOT NULL,
  `fecha_uso` date DEFAULT NULL,
  `fecha_expiracion` date DEFAULT NULL,
  `tipo_descuento` varchar(255) NOT NULL,
  `valor_descuento` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cupones`
--

INSERT INTO `cupones` (`id`, `codigo`, `usado`, `usuario_uso`, `usuario_genero`, `estatus`, `fecha_genero`, `fecha_uso`, `fecha_expiracion`, `tipo_descuento`, `valor_descuento`, `created_at`, `updated_at`) VALUES
(1, 'PCLRWDGTRUTXJLZ', 0, NULL, 1, 'activo', '2026-05-18', NULL, '2026-05-30', 'cantidad_fija', 100.00, NULL, NULL),
(2, 'BPK3U458BBNKQBP', 0, NULL, 1, 'activo', '2026-05-18', NULL, '2026-05-22', 'porcentaje', 100.00, NULL, NULL),
(3, 'OJDRILK8JJBFXYZ', 1, 3, 1, 'activo', '2026-05-18', '2026-05-19', NULL, 'cantidad_fija', 200.00, NULL, NULL),
(4, 'OZD9NFEBD2HIWXU', 1, 2, 1, 'activo', '2026-05-22', '2026-05-22', '2026-05-30', 'porcentaje', 50.00, NULL, NULL),
(5, '1C6WSMPQRI3RJDY', 1, 4, 1, 'activo', '2026-05-22', '2026-05-22', '2026-05-30', 'porcentaje', 25.00, NULL, NULL),
(6, 'RAHHVX5O7T1M8AB', 1, 7, 1, 'activo', '2026-05-23', '2026-05-23', '2026-05-30', 'porcentaje', 50.00, NULL, NULL),
(7, 'THDKKZNWYGI0AVF', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-05', 'porcentaje', 30.00, NULL, NULL),
(8, 'LZY5FYXWFJH48ER', 0, NULL, 1, 'activo', '2026-05-30', NULL, NULL, 'cantidad_fija', 200.00, NULL, NULL),
(9, 'THY5VSYD8DO4F6L', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL),
(10, 'RS3TSOJOMYZJXUU', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL),
(11, 'LDOPFVIN53ULBSZ', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL),
(12, 'MSF18UETRULFKWT', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL),
(13, 'BXFC7WXXUTTST2A', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL),
(14, 'KPFOXP7JQM8IXPR', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL),
(15, 'DFIP1YBKXWPNHM2', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL),
(16, 'FEIM2XK3G2LW8YY', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL),
(17, '6LSSQIT3PQQYL06', 0, NULL, 1, 'activo', '2026-05-30', NULL, '2026-06-30', 'porcentaje', 10.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

CREATE TABLE `estudiante` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `paterno` varchar(255) NOT NULL,
  `materno` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `telefono_casa` varchar(20) DEFAULT NULL,
  `escuela_procedencia` bigint(20) UNSIGNED DEFAULT NULL,
  `cupon` varchar(50) DEFAULT NULL,
  `fecha_inscripcion` date NOT NULL,
  `plan_activo` tinyint(1) NOT NULL DEFAULT 0,
  `universidad_interes` bigint(20) UNSIGNED DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `usuario` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estudiante`
--

INSERT INTO `estudiante` (`id`, `nombre`, `paterno`, `materno`, `fecha_nacimiento`, `sexo`, `telefono`, `telefono_casa`, `escuela_procedencia`, `cupon`, `fecha_inscripcion`, `plan_activo`, `universidad_interes`, `foto`, `usuario`, `created_at`, `updated_at`) VALUES
(1, 'Luis', 'Loera', 'Montes de Oca', '2011-05-02', 'M', '7778909192', '7778909876', 262, 'OZD9NFEBD2HIWXU', '2026-05-18', 0, 1, NULL, 2, NULL, NULL),
(2, 'Gustavo', 'Loera', 'Martinez', '2004-07-07', 'M', '7778900102', '7771234533', 93, 'OJDRILK8JJBFXYZ', '2026-05-19', 1, 1, 'fotos_perfil/njqzNrNhldeuHG2Sot5nn4Cu3fsbi9j7VLYxy58A.png', 3, NULL, NULL),
(3, 'Fernando', 'Diaz', 'Diaz', '2000-02-15', 'M', '7778901222', '8801919191', 42, '1C6WSMPQRI3RJDY', '2026-05-22', 1, 3, NULL, 4, NULL, NULL),
(4, 'Arleth', 'Vega', 'Estrada', '2005-05-02', 'F', '7778900102', '7778909192', 92, 'RAHHVX5O7T1M8AB', '2026-05-23', 1, 2, NULL, 7, NULL, NULL),
(5, 'José', 'Cortés', 'Flores', '2012-05-23', 'M', '1782398172', '2198371928', 223, NULL, '2026-06-04', 0, 1, NULL, 9, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examen_generado`
--

CREATE TABLE `examen_generado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `numero_preguntas` int(11) NOT NULL,
  `tiempo` int(11) NOT NULL COMMENT 'Tiempo límite del examen en minutos',
  `tipo_examen` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `examen_generado`
--

INSERT INTO `examen_generado` (`id`, `numero_preguntas`, `tiempo`, `tipo_examen`, `created_at`, `updated_at`) VALUES
(1, 17, 34, 'Materia', '2026-05-21 21:42:10', '2026-05-21 21:42:10'),
(2, 25, 50, 'Materia', '2026-05-21 21:42:17', '2026-05-21 21:42:17'),
(3, 6, 12, 'Materia', '2026-05-21 21:42:38', '2026-05-21 21:42:38'),
(4, 26, 52, 'Materia', '2026-05-21 21:42:45', '2026-05-21 21:42:45'),
(5, 5, 10, 'Materia', '2026-05-21 21:42:51', '2026-05-21 21:42:51'),
(6, 16, 32, 'Materia', '2026-05-21 21:42:58', '2026-05-21 21:42:58'),
(7, 20, 40, 'Curso', '2026-05-21 21:43:05', '2026-05-21 21:43:05'),
(8, 20, 30, 'Simulación', '2026-05-21 21:43:11', '2026-05-21 21:43:11'),
(9, 20, 40, 'Simulación', '2026-05-21 21:43:17', '2026-05-21 21:43:17'),
(10, 20, 40, 'Simulación', '2026-05-21 21:43:25', '2026-05-21 21:43:25'),
(20, 10, 60, 'Curso', '2026-05-23 06:25:33', '2026-06-03 18:10:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examen_realizado`
--

CREATE TABLE `examen_realizado` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `estudiante` bigint(20) UNSIGNED NOT NULL,
  `fecha_inicio` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `tiempo` time DEFAULT NULL COMMENT 'Tiempo que tardó en resolverlo',
  `calificacion` decimal(5,2) DEFAULT NULL,
  `examen` bigint(20) UNSIGNED NOT NULL,
  `intento` int(11) NOT NULL DEFAULT 1,
  `respuestas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Almacena las respuestas del estudiante en formato JSON' CHECK (json_valid(`respuestas`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `examen_realizado`
--

INSERT INTO `examen_realizado` (`id`, `estudiante`, `fecha_inicio`, `hora_inicio`, `fecha_fin`, `hora_fin`, `tiempo`, `calificacion`, `examen`, `intento`, `respuestas`, `created_at`, `updated_at`) VALUES
(20, 2, '2026-06-01', '11:36:34', '2026-06-01', '11:37:27', '00:00:53', 70.00, 10, 2, '\"{\\\"respuestas\\\":[{\\\"pregunta_id\\\":9,\\\"respuesta\\\":\\\"Hermoso\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":14,\\\"respuesta\\\":\\\"El sol brilla en el cielo\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":20,\\\"respuesta\\\":\\\"Y\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":26,\\\"respuesta\\\":\\\"\\\\u00a1!\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":32,\\\"respuesta\\\":\\\"D\\\\u00edgamelo\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":38,\\\"respuesta\\\":\\\"Adem\\\\u00e1s\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":46,\\\"respuesta\\\":\\\"Carro\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":50,\\\"respuesta\\\":\\\"Lectura de barrido (skimming)\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":52,\\\"respuesta\\\":\\\"Sintetizar las ideas principales del texto\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":57,\\\"respuesta\\\":\\\"I don\'t like\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":62,\\\"respuesta\\\":\\\"I have to study\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":68,\\\"respuesta\\\":\\\"May I come in?\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":76,\\\"respuesta\\\":\\\"Yo juego f\\\\u00fatbol\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":81,\\\"respuesta\\\":\\\"Informaci\\\\u00f3n factual que sirve como base\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":83,\\\"respuesta\\\":\\\"Perro es adulto, cachorro es cr\\\\u00eda\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":86,\\\"respuesta\\\":\\\"Suma, resta, multiplicaci\\\\u00f3n, divisi\\\\u00f3n\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":91,\\\"respuesta\\\":\\\"Escribir como producto de factores\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":94,\\\"respuesta\\\":\\\"Una, ninguna o infinitas\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":98,\\\"respuesta\\\":\\\"La medida de la posibilidad de que ocurra un evento\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":99,\\\"respuesta\\\":\\\"y = mx + b\\\",\\\"estatus\\\":\\\"correcta\\\"}]}\"', NULL, NULL),
(21, 2, '2026-06-01', '12:20:59', '2026-06-01', '12:22:59', '00:02:00', 30.00, 7, 1, '\"{\\\"respuestas\\\":[{\\\"pregunta_id\\\":6,\\\"respuesta\\\":\\\"Correr\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":10,\\\"respuesta\\\":\\\"Y\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":15,\\\"respuesta\\\":\\\"Un adverbio\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":23,\\\"respuesta\\\":\\\"Juegan\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":28,\\\"respuesta\\\":\\\"Arbol\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":33,\\\"respuesta\\\":\\\"Sin\\\\u00f3nimos\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":40,\\\"respuesta\\\":\\\"Cozina\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":48,\\\"respuesta\\\":\\\"La \\\\u00faltima frase del texto\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":51,\\\"respuesta\\\":\\\"Conclusiones que sacamos al leer entre l\\\\u00edneas\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":54,\\\"respuesta\\\":\\\"Where\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":58,\\\"respuesta\\\":\\\"She is\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":64,\\\"respuesta\\\":\\\"More tall\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":70,\\\"respuesta\\\":\\\"I going to help you\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":80,\\\"respuesta\\\":\\\"Memorizar informaci\\\\u00f3n\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":83,\\\"respuesta\\\":\\\"Son ant\\\\u00f3nimos\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":85,\\\"respuesta\\\":\\\"24\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":87,\\\"respuesta\\\":\\\"Una comparaci\\\\u00f3n entre dos cantidades\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":90,\\\"respuesta\\\":\\\"a\\\\u00b2 + 2ab + b\\\\u00b2\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":95,\\\"respuesta\\\":\\\"El dato central\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":99,\\\"respuesta\\\":\\\"y = mx + b\\\",\\\"estatus\\\":\\\"correcta\\\"}]}\"', NULL, NULL),
(22, 2, '2026-06-02', '21:29:35', '2026-06-02', '21:36:32', '00:06:57', 60.00, 7, 2, '\"{\\\"respuestas\\\":[{\\\"pregunta_id\\\":6,\\\"respuesta\\\":\\\"Casa\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":10,\\\"respuesta\\\":\\\"Y\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":15,\\\"respuesta\\\":\\\"Un adverbio\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":23,\\\"respuesta\\\":\\\"Juegan\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":28,\\\"respuesta\\\":\\\"\\\\u00c1rbol\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":33,\\\"respuesta\\\":\\\"Ant\\\\u00f3nimos\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":40,\\\"respuesta\\\":\\\"Cozina\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":48,\\\"respuesta\\\":\\\"La idea m\\\\u00e1s importante que el autor quiere transmitir\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":51,\\\"respuesta\\\":\\\"Conclusiones que sacamos al leer entre l\\\\u00edneas\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":54,\\\"respuesta\\\":\\\"What\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":58,\\\"respuesta\\\":\\\"She is\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":64,\\\"respuesta\\\":\\\"The tallest\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":70,\\\"respuesta\\\":\\\"I going to help you\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":80,\\\"respuesta\\\":\\\"Capacidad para descomponer un problema en partes y analizarlas\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":83,\\\"respuesta\\\":\\\"Perro es adulto, cachorro es cr\\\\u00eda\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":85,\\\"respuesta\\\":\\\"14 (primero la multiplicaci\\\\u00f3n)\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":87,\\\"respuesta\\\":\\\"Una comparaci\\\\u00f3n entre dos cantidades\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":90,\\\"respuesta\\\":\\\"a\\\\u00b2 + 2ab + b\\\\u00b2\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":95,\\\"respuesta\\\":\\\"El dato que m\\\\u00e1s se repite\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":99,\\\"respuesta\\\":\\\"y = ax\\\\u00b2 + bx + c\\\",\\\"estatus\\\":\\\"incorrecta\\\"}]}\"', NULL, NULL),
(23, 2, '2026-06-03', '12:02:33', '2026-06-03', '12:05:04', '00:02:31', 40.00, 7, 3, '\"{\\\"respuestas\\\":[{\\\"pregunta_id\\\":6,\\\"respuesta\\\":\\\"Hermoso\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":10,\\\"respuesta\\\":\\\"Pero\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":15,\\\"respuesta\\\":\\\"Un adverbio\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":23,\\\"respuesta\\\":\\\"Ellos\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":28,\\\"respuesta\\\":\\\"arbol\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":33,\\\"respuesta\\\":\\\"Sin\\\\u00f3nimos\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":40,\\\"respuesta\\\":\\\"Cocina\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":48,\\\"respuesta\\\":\\\"La idea m\\\\u00e1s importante que el autor quiere transmitir\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":51,\\\"respuesta\\\":\\\"Conclusiones que sacamos al leer entre l\\\\u00edneas\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":54,\\\"respuesta\\\":\\\"Where\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":58,\\\"respuesta\\\":\\\"She is\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":64,\\\"respuesta\\\":\\\"More tall\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":70,\\\"respuesta\\\":\\\"I going to help you\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":80,\\\"respuesta\\\":\\\"Pensar sin l\\\\u00f3gica\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":83,\\\"respuesta\\\":\\\"Son sin\\\\u00f3nimos\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":85,\\\"respuesta\\\":\\\"14 (primero la multiplicaci\\\\u00f3n)\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":87,\\\"respuesta\\\":\\\"Un producto\\\",\\\"estatus\\\":\\\"incorrecta\\\"},{\\\"pregunta_id\\\":90,\\\"respuesta\\\":\\\"a\\\\u00b2 + 2ab + b\\\\u00b2\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":95,\\\"respuesta\\\":\\\"El promedio de un conjunto de datos\\\",\\\"estatus\\\":\\\"correcta\\\"},{\\\"pregunta_id\\\":99,\\\"respuesta\\\":\\\"y = mx + b\\\",\\\"estatus\\\":\\\"correcta\\\"}]}\"', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `interacciones_call_center`
--

CREATE TABLE `interacciones_call_center` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_usuario_contacta` bigint(20) UNSIGNED NOT NULL,
  `id_estudiante` bigint(20) UNSIGNED NOT NULL,
  `fecha_contacto` date NOT NULL,
  `hora_contacto` time NOT NULL,
  `nota` text NOT NULL,
  `tipo_contacto` varchar(50) NOT NULL DEFAULT 'llamada',
  `estado_seguimiento` varchar(50) NOT NULL DEFAULT 'pendiente',
  `proximo_contacto` datetime DEFAULT NULL,
  `motivo_contacto` varchar(255) NOT NULL,
  `resultado` varchar(255) NOT NULL DEFAULT 'no_contesto',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `interacciones_call_center`
--

INSERT INTO `interacciones_call_center` (`id`, `id_usuario_contacta`, `id_estudiante`, `fecha_contacto`, `hora_contacto`, `nota`, `tipo_contacto`, `estado_seguimiento`, `proximo_contacto`, `motivo_contacto`, `resultado`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, '2026-05-18', '12:37:42', 'Se comunico al estudiante para ofrecer nuestro servicio, el cliente solicito una prorroga para poder consultarlo', 'llamada', 'pendiente', '2026-05-22 15:37:00', 'Se contacto para ofrecer el curso', 'Se llego a un convenio para hablar del curso con sus padres', '2026-05-19 00:37:43', '2026-05-19 00:37:43', NULL),
(2, 1, 2, '2026-05-19', '17:09:00', 'FEOFFEO', 'email', 'en_proceso', '2026-05-28 21:09:00', 'FEOFEO', 'FOFEOF', '2026-05-20 05:09:38', '2026-05-20 06:03:48', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(4, '2026_05_12_164828_create_sessions_table', 1),
(5, '2026_05_15_183800_modify_examen_generado_table_add_created_at_and_fix_tiempo', 1),
(6, 'tabla_area_preguntas', 1),
(7, 'tabla_asignatura', 1),
(8, 'tabla_clases', 1),
(9, 'tabla_examen_generado', 1),
(10, 'tabla_preguntas', 1),
(11, 'tabla_preparatorias', 1),
(12, 'tabla_recursos_adicionales', 1),
(13, 'tabla_tronco', 1),
(14, 'tabla_usuario', 1),
(15, 'tabla_videos', 1),
(16, 'tabla_w_apoyo_preguntas', 1),
(17, 'tabla_w_carreras', 1),
(18, 'tabla_w_cupones', 1),
(19, 'tabla_w_universidades', 1),
(20, 'tabla_x_administrador', 1),
(21, 'tabla_x_callcenter', 1),
(22, 'tabla_x_estudiante', 1),
(23, 'tabla_x_examen_realizado', 1),
(24, 'tabla_x_pagos', 1),
(25, 'tabla_x_progreso_videos', 1),
(26, 'tabla_x_tiempo_estudio', 2),
(27, 'tabla_z_cupones_fechaexpiracion', 3),
(28, 'reset_password', 4),
(29, 'tabla_x_google', 5),
(30, 'tabla_z_modPreguntas', 6),
(31, 'tabla_z_modificacionCarrera', 7),
(32, 'tabla_z_mod_examenGenerado', 8),
(33, 'tabla_z_recursosAdicionales', 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_pago` enum('Bancario','Oxxo','Transferencia') NOT NULL,
  `alumno_pago` bigint(20) UNSIGNED NOT NULL,
  `fecha_pago` datetime NOT NULL,
  `monto_pago` decimal(10,2) NOT NULL,
  `estatus` varchar(255) NOT NULL DEFAULT 'pendiente',
  `referencia_pago` varchar(100) NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  `usuario_revision` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_aprueba` datetime DEFAULT NULL,
  `nota_usuario` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `tipo_pago`, `alumno_pago`, `fecha_pago`, `monto_pago`, `estatus`, `referencia_pago`, `comprobante`, `usuario_revision`, `fecha_aprueba`, `nota_usuario`, `created_at`, `updated_at`) VALUES
(4, 'Bancario', 2, '2026-05-20 00:00:00', 200.00, 'aprobado', 'referencia1', 'comprobantes/gustavo_loera_20260520_001821.png', 1, '2026-05-22 17:36:49', 'Se revisará el pago con la contadora', NULL, NULL),
(5, 'Transferencia', 1, '2026-05-22 17:03:58', 400.00, 'rechazado', 'TRA20260522000001740', NULL, 1, '2026-05-23 00:27:36', 'Comprobante ilegible o dañado', NULL, NULL),
(7, 'Transferencia', 3, '2026-05-22 18:21:02', 600.00, 'aprobado', 'TRA20260522000003474', 'comprobantes/comprobante_7_1779495703.jpeg', 1, '2026-05-22 20:31:40', 'PAGO REALIZADO\r\n\r\n[REVISIÓN REVERTIDA] El pago fue cambiado de aprobado a pendiente por admin@sistema.com el 22/05/2026 20:29', NULL, NULL),
(8, 'Transferencia', 4, '2026-05-23 00:43:02', 400.00, 'aprobado', 'TRA20260523000004222', 'comprobantes/comprobante_8_1779518683.jpg', 1, '2026-05-23 00:45:35', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('gvegaestrada@gmail.com', 'dnMODPpvVoM5i7CZT6TwU3VuhiFrOcYR4f8YCor65o3H31FerOOJlaLWEHfy', '2026-05-22 22:31:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_area` bigint(20) UNSIGNED NOT NULL,
  `pregunta` text NOT NULL,
  `respuesta_correcta` varchar(255) NOT NULL,
  `respuesta1` varchar(255) NOT NULL,
  `respuesta2` varchar(255) NOT NULL,
  `justificacion` text DEFAULT NULL COMMENT 'Justificación de la respuesta correcta',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id`, `id_area`, `pregunta`, `respuesta_correcta`, `respuesta1`, `respuesta2`, `justificacion`, `created_at`, `updated_at`) VALUES
(1, 26, '¿Cuanto es 2x2?', '4', '18', '22', 'La multiplicación tiene prioridad sobre la suma. Primero se calcula 2x2=4 y luego se suma 1+4+1? No: La operación es 2x2 = 4. Luego se suma el 1 inicial: 1+4=5. La pregunta original en la fila 1 dice \"¿Cuanto es 2x2?\" y la respuesta correcta es 4, por lo tanto la justificación es que 2 multiplicado por 2 es igual a 4.', NULL, NULL),
(2, 18, 'Escribe una palabra', 'Palabra', 'Correcta', 'Sin', 'La instrucción pide \"Escribe una palabra\". Cualquier palabra válida es correcta, pero \"Palabra\" es el ejemplo dado como respuesta correcta porque es una palabra genérica que cumple con el requisito.', NULL, NULL),
(3, 40, '¿Qué es Hardware?', 'Lo que se puede tocar', 'Lo que no se puede tocar', 'Ambos', 'Hardware se refiere a los componentes físicos de una computadora, como el teclado, monitor o disco duro, que se pueden tocar.', NULL, NULL),
(4, 9, '¿Qué es un sexenio?', '6 años', '8 años', '10 años', 'Un sexenio es un período de seis años. En México, por ejemplo, el presidente dura en su cargo un sexenio.', NULL, NULL),
(5, 6, '¿Qué son las emociones?', 'El como nos expresamos de lo que sentimos', 'Son cocos', 'Coca cola', 'Las emociones son reacciones psicofisiológicas que experimentamos ante estímulos y se expresan a través de sentimientos, conductas y respuestas fisiológicas.', NULL, NULL),
(6, 17, '¿Cuál de las siguientes palabras es un SUSTANTIVO?', 'Casa', 'Hermoso', 'Correr', 'Un sustantivo es una palabra que nombra a personas, animales, cosas o ideas. \"Casa\" nombra un lugar o cosa, mientras que \"hermoso\" es adjetivo y \"correr\" es verbo.', NULL, NULL),
(7, 17, '¿Qué función cumplen los adjetivos en una oración?', 'Calificar o describir al sustantivo', 'Nombrar acciones', 'Indicar tiempo', 'Los adjetivos cumplen la función de modificar, calificar o describir al sustantivo, añadiendo cualidades como tamaño, color, forma, etc.', NULL, NULL),
(8, 17, '¿Cuál es el verbo en la siguiente oración: \"María corre rápidamente\"?', 'Corre', 'María', 'Rápidamente', 'El verbo es la palabra que expresa acción, estado o proceso. En la oración \"María corre rápidamente\", la acción es \"corre\".', NULL, NULL),
(9, 17, '¿Qué palabra es un ADVERBIO?', 'Rápidamente', 'Hermoso', 'Mesa', 'Un adverbio modifica al verbo, adjetivo u otro adverbio. \"Rápidamente\" indica cómo se realiza la acción (modo), \"hermoso\" es adjetivo y \"mesa\" es sustantivo.', NULL, NULL),
(10, 17, '¿Cuál de las siguientes es una preposición?', 'A', 'Y', 'Pero', 'Las preposiciones son palabras invariables que sirven para relacionar términos. \"A\" es una preposición que indica dirección, tiempo o modo. \"Y\" y \"pero\" son conjunciones.', NULL, NULL),
(11, 17, '¿Qué función tienen las preposiciones?', 'Relacionar palabras indicando origen, destino, causa, etc.', 'Unir oraciones', 'Expresar emociones', 'Las preposiciones (a, ante, bajo, con, contra, de, desde, en, entre, hacia, hasta, para, por, según, sin, so, sobre, tras) relacionan palabras indicando origen, destino, causa, lugar, tiempo, etc.', NULL, NULL),
(12, 17, '¿Cuál es el SUJETO en la oración: \"El perro ladra fuerte\"?', 'El perro', 'Ladra', 'Fuerte', 'El sujeto es la persona, animal o cosa de la que se dice algo. En \"El perro ladra fuerte\", el sujeto es \"El perro\" porque es quien realiza la acción de ladrar.', NULL, NULL),
(13, 17, '¿Qué parte de la oración indica lo que se dice del sujeto?', 'El predicado', 'El sujeto', 'El verbo', 'El predicado es la parte de la oración que indica lo que se dice del sujeto. Contiene el verbo y los complementos.', NULL, NULL),
(14, 17, '¿Cuál de las siguientes es una oración completa?', 'El sol brilla en el cielo', 'Corriendo alegre', 'Muy feliz', 'Una oración completa debe tener al menos un verbo conjugado y sentido completo. \"El sol brilla en el cielo\" tiene verbo (brilla) y expresa una idea completa.', NULL, NULL),
(15, 17, '¿Qué elemento es indispensable en una oración?', 'Un verbo conjugado', 'Un adjetivo', 'Un adverbio', 'El verbo conjugado es el núcleo del predicado y es indispensable para que exista una oración, ya que sin verbo no hay predicado ni sentido completo.', NULL, NULL),
(16, 17, '¿Qué es un pleonasmo?', 'Uso de palabras redundantes que repiten un concepto', 'Falta de concordancia gramatical', 'Uso incorrecto de tildes', 'El pleonasmo es una figura retórica que consiste en usar palabras redundantes que repiten un concepto, como \"subir arriba\" o \"salir fuera\".', NULL, NULL),
(17, 17, '¿Cuál de las siguientes es un ejemplo de PLEONASMO?', 'Subir arriba', 'Casa grande', 'Correr rápido', '\"Subir arriba\" es pleonasmo porque \"subir\" ya implica ir hacia arriba, por lo que \"arriba\" es redundante.', NULL, NULL),
(18, 17, '¿Qué signo de puntuación se usa para indicar una pausa corta?', 'La coma', 'El punto', 'El punto y coma', 'La coma (,) se usa para indicar una pausa breve dentro de la oración, separar elementos enumerados, vocativos, etc. El punto indica una pausa mayor.', NULL, NULL),
(19, 17, '¿Qué signo se utiliza para expresar sorpresa o énfasis?', 'Signos de exclamación (¡!)', 'Signos de interrogación (¿?)', 'Comillas', 'Los signos de exclamación (¡!) se utilizan para expresar sorpresa, énfasis, alegría, enojo o cualquier emoción intensa en la escritura.', NULL, NULL),
(20, 17, '¿Cuál de los siguientes es un NEXO de causalidad?', 'Porque', 'Y', 'O', 'Los nexos de causalidad indican causa o motivo. \"Porque\" es un nexo causal que introduce la razón de algo. \"Y\" es copulativo, \"O\" es disyuntivo.', NULL, NULL),
(21, 17, '¿Qué palabra conecta ideas en esta oración: \"Estudió mucho, PERO no aprobó\"?', 'Pero', 'Estudió', 'Aprobó', 'El nexo \"pero\" conecta ideas opuestas o que contrastan. En la oración, une el esfuerzo (estudió) con el resultado inesperado (no aprobó).', NULL, NULL),
(22, 17, '¿Qué son los heterónimos?', 'Palabras que tienen la misma escritura pero diferente pronunciación y significado', 'Palabras con significado opuesto', 'Palabras que suenan igual pero se escriben diferente', 'Los heterónimos son palabras que tienen la misma escritura (ortografía) pero diferente significado, como \"vino\" (bebida) y \"vino\" (del verbo venir).', NULL, NULL),
(23, 61, '¿Cuál es el verbo en la oración: \"Ellos juegan en el parque\"?', 'Juegan', 'Ellos', 'Parque', 'El verbo es \"juegan\" porque expresa la acción que realizan \"ellos\". \"Ellos\" es el sujeto y \"parque\" es el lugar donde ocurre la acción.', NULL, NULL),
(24, 61, '¿En qué tiempo verbal está \"cantaré\"?', 'Futuro', 'Presente', 'Pasado', '\"Cantaré\" indica una acción que ocurrirá después del momento presente, por lo tanto está en tiempo futuro.', NULL, NULL),
(25, 61, '¿Dónde se coloca la coma en este enunciado? \"Hola cómo estás\"', 'Hola, ¿cómo estás?', 'Hola cómo, estás?', 'Hola cómo estás,', 'La coma se coloca después del saludo o vocativo para separarlo del resto de la oración. La forma correcta es \"Hola, ¿cómo estás?\"', NULL, NULL),
(26, 61, '¿Qué signo se usa para preguntar?', '¿?', '¡!', '...', 'El signo de interrogación (¿?) se usa para formular preguntas. Se abre con ¿ y se cierra con ?', NULL, NULL),
(27, 61, 'La palabra \"MÉDICO\" es una palabra...', 'Esdrújula (siempre lleva tilde)', 'Grave', 'Aguda', 'La palabra \"MÉDICO\" es grave o llana porque su sílaba tónica (la que suena más fuerte) es la penúltima \"DI\". Las palabras graves llevan tilde si NO terminan en N, S o vocal. Termina en O (vocal), por lo tanto NO lleva tilde. La afirmación \"siempre lleva tilde\" es falsa.', NULL, NULL),
(28, 61, '¿Cuál está correctamente acentuada?', 'Árbol', 'Arbol', 'arbol', '\"Árbol\" está correctamente acentuada porque es una palabra grave terminada en \"l\" (consonante que no es N ni S), por lo que debe llevar tilde en la primera \"a\".', NULL, NULL),
(29, 61, '¿Cuál de estas palabras lleva tilde por ser aguda terminada en N, S o vocal?', 'Canción', 'Casa', 'Libro', 'Las palabras agudas llevan tilde cuando terminan en N, S o vocal. \"Canción\" termina en N, por lo tanto lleva tilde en la última sílaba \"ción\".', NULL, NULL),
(30, 61, 'La palabra \"TAMBIÉN\" lleva tilde porque...', 'Es aguda terminada en N', 'Es grave', 'Es esdrújula', '\"También\" es una palabra aguda terminada en N, por lo tanto debe llevar tilde en la última sílaba \"bién\".', NULL, NULL),
(31, 61, '¿Cuál es la palabra GRAVE que NO lleva tilde?', 'Mesa', 'Césped', 'Árbol', 'Las palabras graves (o llanas) NO llevan tilde cuando terminan en N, S o vocal. \"Mesa\" es grave y termina en vocal \"a\", por lo tanto NO lleva tilde.', NULL, NULL),
(32, 61, 'Las palabras SOBREESDRÚJULAS siempre llevan tilde. ¿Cuál lo es?', 'Dígamelo', 'Casa', 'Perro', 'Las palabras sobreesdrújulas son aquellas cuya sílaba tónica está antes de la antepenúltima. Por regla ortográfica, SIEMPRE llevan tilde, como en \"dígamelo\".', NULL, NULL),
(33, 61, '¿Qué relación existe entre \"CALIENTE\" y \"FRÍO\"?', 'Antónimos', 'Sinónimos', 'Homófonos', '\"Caliente\" y \"frío\" tienen significados opuestos: uno indica temperatura alta y el otro baja. Por lo tanto son antónimos.', NULL, NULL),
(34, 61, '¿Cuál es sinónimo de \"ALEGRE\"?', 'Feliz', 'Triste', 'Enojado', 'Un sinónimo es una palabra que tiene un significado similar o idéntico. \"Feliz\" es sinónimo de \"alegre\", ambas expresan alegría o satisfacción.', NULL, NULL),
(35, 61, '¿Cuál de las siguientes es una PREPOSICIÓN?', 'Sin', 'Y', 'Aunque', '\"Sin\" es una preposición que indica carencia o falta. \"Y\" es una conjunción copulativa y \"aunque\" es una conjunción concesiva.', NULL, NULL),
(36, 61, '¿Qué palabra es un ADVERBIO DE LUGAR?', 'Aquí', 'Bien', 'Pronto', 'Los adverbios de lugar indican dónde ocurre la acción. \"Aquí\" responde a la pregunta \"¿dónde?\". \"Bien\" es modo, \"pronto\" es tiempo.', NULL, NULL),
(37, 61, '¿Qué permite la cohesión textual?', 'Conectar ideas correctamente', 'Escribir sin errores', 'Usar palabras bonitas', 'La cohesión textual es la propiedad que permite conectar las ideas y oraciones de un texto mediante recursos como conectores, pronombres, sinónimos, etc., para que sea fluido y comprensible.', NULL, NULL),
(38, 61, '¿Qué conector indica CONTRASTE?', 'Pero', 'Además', 'También', 'El conector \"pero\" (o \"sin embargo\", \"no obstante\") indica contraste u oposición entre dos ideas. \"Además\" y \"también\" indican adición.', NULL, NULL),
(39, 61, '¿Qué elemento da coherencia a un texto?', 'Que todas las ideas giren alrededor de un tema central', 'Que tenga muchas palabras', 'Que sea largo', 'La coherencia es la propiedad de un texto que garantiza que todas las ideas giren alrededor de un mismo tema central, organizadas lógicamente.', NULL, NULL),
(40, 61, '¿Cuál es la palabra correctamente escrita?', 'Cocina', 'Cosina', 'Cozina', '\"Cocina\" es la forma correcta. Las opciones \"cosina\" y \"cozina\" son incorrectas porque la \"c\" suave seguida de \"o\" se mantiene como \"c\", no como \"s\" o \"z\".', NULL, NULL),
(41, 61, '¿Qué letra usarías en \"felici_ dad\"?', 'Z', 'C', 'S', 'La palabra \"felicidad\" se escribe con Z porque el sufijo \"-ez\" cambia a \"-eza\" para formar sustantivos abstractos (feliz → felicidad, pero la raíz viene de \"feliz\" + \"idad\", y la \"z\" se mantiene en algunas formas). En este caso, la letra correcta es Z.', NULL, NULL),
(42, 61, '¿Cuál está escrita correctamente?', 'Gente', 'Jente', 'Gente', 'La forma correcta es \"Gente\" con G, ya que las palabras que comienzan con \"ge-\" o \"gi-\" llevan G. \"Jente\" es incorrecto.', NULL, NULL),
(43, 61, '¿Qué palabra lleva J?', 'Caja', 'Gato', 'Gema', '\"Caja\" se escribe con J porque es una palabra que lleva J. \"Gato\" y \"gema\" se escriben con G.', NULL, NULL),
(44, 61, '¿Cuál está correctamente escrita?', 'Bien', 'Vien', 'Vien', 'La forma correcta es \"Bien\" con B. \"Vien\" es incorrecto porque la palabra \"bien\" proviene del latín \"bene\" y se escribe con B.', NULL, NULL),
(45, 61, 'Después de N va...', 'V (envidia)', 'B (enbidia)', 'Ambas son válidas', 'Después de la letra N siempre se escribe V (no B). Ejemplos: envidia, invitación, enviar. \"Enbidia\" es incorrecto.', NULL, NULL),
(46, 61, '¿Cuál está correctamente escrita?', 'Carro', 'Caro', 'Carro', 'La forma correcta es \"Carro\" con doble R porque entre vocales y en posición intermedia, el sonido fuerte de R se escribe RR. \"Caro\" tiene un significado diferente (costoso o querido).', NULL, NULL),
(47, 61, 'Al inicio de la palabra se escribe...', 'R simple (ropa)', 'RR (rropa)', 'Ambas', 'Al inicio de palabra, el sonido fuerte de la R se escribe con una sola R (ejemplos: ropa, rata, rosa). Nunca se escribe RR al inicio.', NULL, NULL),
(48, 57, '¿Qué es la idea principal de un texto?', 'La idea más importante que el autor quiere transmitir', 'Un detalle secundario', 'La última frase del texto', 'La idea principal es el concepto central o mensaje más importante que el autor quiere transmitir. Es la columna vertebral del texto.', NULL, NULL),
(49, 57, '¿Qué permite hacer una LECTURA CRÍTICA?', 'Analizar y cuestionar lo que se lee', 'Leer más rápido', 'Memorizar todo', 'La lectura crítica permite analizar, cuestionar, evaluar y reflexionar sobre el contenido del texto, identificando sesgos, supuestos y argumentos.', NULL, NULL),
(50, 57, '¿Cómo se llama la técnica de leer rápidamente para captar lo esencial?', 'Lectura de barrido (skimming)', 'Lectura lenta', 'Subrayado', 'La lectura de barrido o skimming es una técnica de lectura rápida que consiste en pasar la vista por el texto para identificar la idea general o localizar información específica.', NULL, NULL),
(51, 57, '¿Qué son las inferencias?', 'Conclusiones que sacamos al leer entre líneas', 'Datos explícitos', 'Resúmenes', 'Las inferencias son conclusiones o interpretaciones que el lector deduce a partir de la información implícita en el texto, leyendo \"entre líneas\".', NULL, NULL),
(52, 57, '¿Para qué sirve hacer un resumen?', 'Sintetizar las ideas principales del texto', 'Alargar el contenido', 'Copiar el texto original', 'Un resumen sirve para sintetizar las ideas principales y secundarias más importantes de un texto, expresándolas con nuestras propias palabras y de forma concisa.', NULL, NULL),
(53, 57, '¿Qué significa que un texto sea COHERENTE?', 'Que todas sus partes se relacionan lógicamente', 'Que tiene buena ortografía', 'Que usa palabras bonitas', 'Un texto es coherente cuando todas sus partes (introducción, desarrollo, conclusión) se relacionan lógicamente entre sí y giran en torno a un tema central.', NULL, NULL),
(54, 23, '¿Cuál WH question se usa para preguntar por personas?', 'Who', 'What', 'Where', 'La WH question \"Who\" se usa para preguntar por personas. \"What\" es para cosas, \"Where\" para lugares.', NULL, NULL),
(55, 23, '¿Cómo preguntarías \"¿Dónde vives?\"', 'Where do you live?', 'What do you live?', 'Who do you live?', 'Para preguntar \"¿Dónde vives?\" se usa \"Where\" (lugar) + do (auxiliar) + you + live. La forma correcta es \"Where do you live?\"', NULL, NULL),
(56, 23, '¿Cuál es la forma correcta para HE/SHE/IT en Simple Present?', 'He plays', 'He play', 'He playing', 'En Simple Present, para HE/SHE/IT (tercera persona singular) se añade una \"s\" al verbo. \"He plays\" es la forma correcta.', NULL, NULL),
(57, 23, '¿Cómo se niega en Simple Present con I?', 'I don\'t like', 'I doesn\'t like', 'I not like', 'Para negar en Simple Present con I, se usa el auxiliar \"do not\" (don\'t) seguido del verbo en infinitivo. \"I don\'t like\" es la forma correcta.', NULL, NULL),
(58, 23, '¿Cuál es la forma correcta del verbo TO BE para \"SHE\"?', 'She is', 'She are', 'She am', 'El verbo TO BE para \"She\" (tercera persona singular femenina) es \"is\". \"She is\" es la forma correcta.', NULL, NULL),
(59, 23, '¿Cómo se dice \"Yo soy estudiante\"?', 'I am a student', 'I is a student', 'I are a student', 'Para decir \"Yo soy estudiante\" se usa el verbo TO BE \"am\" para I, seguido de \"a student\". La forma correcta es \"I am a student\".', NULL, NULL),
(60, 23, '¿Cómo saludas en la mañana?', 'Good morning', 'Good afternoon', 'Good night', 'Para saludar en la mañana se dice \"Good morning\". \"Good afternoon\" es para la tarde, \"Good night\" para despedirse o al acostarse.', NULL, NULL),
(61, 23, '¿Cómo dices \"Gracias\"?', 'Thank you', 'Please', 'Sorry', '\"Gracias\" en inglés se dice \"Thank you\". \"Please\" es por favor, \"Sorry\" es lo siento o disculpa.', NULL, NULL),
(62, 23, '¿Cómo se dice \"Tengo que estudiar\"?', 'I have to study', 'I has to study', 'I must to study', 'Para expresar obligación o necesidad se usa \"have to\". \"Tengo que estudiar\" se traduce como \"I have to study\".', NULL, NULL),
(63, 23, '¿Qué expresa MUST?', 'Obligación fuerte', 'Permiso', 'Posibilidad', 'El verbo modal \"MUST\" expresa una obligación fuerte, necesidad o certeza. Ejemplo: \"You must stop\" (Debes parar).', NULL, NULL),
(64, 23, '¿Cuál es el comparativo de \"TALL\"?', 'Taller', 'More tall', 'The tallest', 'El comparativo de adjetivos cortos (como \"tall\") se forma añadiendo \"-er\". \"Taller\" significa \"más alto\".', NULL, NULL),
(65, 23, '¿Cuál es el superlativo de \"BIG\"?', 'The biggest', 'Bigger', 'More big', 'El superlativo de adjetivos cortos se forma con \"the\" + adjetivo + \"-est\". \"Big\" duplica la G por ser CVC (consonante-vocal-consonante): \"the biggest\".', NULL, NULL),
(66, 23, '¿Cómo pides la cuenta?', 'Can I have the bill please?', 'Give me the money', 'I want food', 'Para pedir la cuenta en un restaurante se usa \"Can I have the bill, please?\" o simplemente \"The bill, please\".', NULL, NULL),
(67, 23, '¿Cómo preguntas \"¿Qué recomiendas?\"', 'What do you recommend?', 'What you recommend?', 'What recommend you?', 'Para preguntar \"¿Qué recomiendas?\" se usa \"What do you recommend?\" con el auxiliar \"do\" y el verbo en infinitivo.', NULL, NULL),
(68, 23, '¿Cómo pides permiso formalmente?', 'May I come in?', 'Can I come in?', 'I come in?', 'Para pedir permiso formalmente se usa \"May I...\". \"May I come in?\" es más formal y cortés que \"Can I come in?\"', NULL, NULL),
(69, 23, '¿Qué significa \"You can sit here\"?', 'Tú puedes sentarte aquí', 'Tú debes sentarte aquí', 'Tú no puedes sentarte aquí', '\"You can sit here\" significa \"Tú puedes sentarte aquí\". \"Can\" expresa permiso o capacidad.', NULL, NULL),
(70, 23, '¿Cómo expresas una decisión espontánea?', 'I will help you', 'I going to help you', 'I help you', 'Para expresar una decisión espontánea (tomada en el momento) se usa \"will\" + verbo. \"I will help you\" expresa una decisión repentina de ayudar.', NULL, NULL),
(71, 23, '¿Qué significa \"It\'s going to rain\"?', 'Va a llover', 'Llueve ahora', 'Llovió', '\"It\'s going to rain\" es la estructura \"going to\" para expresar un futuro planeado o inminente. Significa \"Va a llover\".', NULL, NULL),
(72, 23, '¿Cómo preguntas \"¿Cuál es el título del libro?\"', 'What is the title of the book?', 'Who is the book?', 'Where is the book?', 'Para preguntar \"¿Cuál es el título del libro?\" se usa \"What\" (qué/cuál) + is + the title of the book?', NULL, NULL),
(73, 23, '¿Qué significa \"The main character\"?', 'El personaje principal', 'El autor', 'El título', '\"The main character\" se traduce como \"el personaje principal\" de una historia, novela o película.', NULL, NULL),
(74, 23, '¿Cómo preguntas \"¿Cuántos años tienes?\"', 'How old are you?', 'How many years you have?', 'What age you?', 'Para preguntar la edad se usa la expresión fija \"How old are you?\" (¿Cuántos años tienes?).', NULL, NULL),
(75, 23, '¿Cómo dices \"Mi cumpleaños es el 10 de mayo\"?', 'My birthday is on May 10th', 'My birthday is in May 10', 'I birthday is May 10', 'Para decir una fecha con día específico se usa la preposición \"on\". \"My birthday is on May 10th\" es la forma correcta.', NULL, NULL),
(76, 23, '¿Qué significa \"I used to play soccer\"?', 'Yo solía jugar fútbol', 'Yo juego fútbol', 'Yo jugaré fútbol', '\"Used to\" + verbo se usa para hablar de hábitos o acciones del pasado que ya no se realizan. \"I used to play soccer\" significa \"Yo solía jugar fútbol\".', NULL, NULL),
(77, 23, '¿Cómo se niega \"used to\"?', 'I didn\'t use to', 'I used not to', 'I don\'t used to', 'Para negar \"used to\" se usa \"didn\'t use to\" (el auxiliar en pasado elimina la \"d\" de \"used\"). \"I didn\'t use to\" es la forma correcta.', NULL, NULL),
(78, 23, '¿Qué tiempo usas para acciones pasadas completadas?', 'Simple Past', 'Present Perfect', 'Past Continuous', 'El Simple Past (Pasado Simple) se usa para acciones pasadas completadas en un momento específico o que ya terminaron.', NULL, NULL),
(79, 23, '¿Cómo se forma el Present Continuous?', 'Am/is/are + verbo-ing', 'Verbo en pasado', 'Will + verbo', 'El Present Continuous se forma con el verbo TO BE (am/is/are) + el verbo principal con terminación -ing. Indica acciones en curso ahora.', NULL, NULL),
(80, 56, '¿Qué es el pensamiento analítico?', 'Capacidad para descomponer un problema en partes y analizarlas', 'Memorizar información', 'Pensar sin lógica', 'El pensamiento analítico es la capacidad para descomponer un problema complejo en partes más pequeñas, analizarlas individualmente y comprender las relaciones entre ellas.', NULL, NULL),
(81, 56, '¿Qué son los datos en un análisis?', 'Información factual que sirve como base', 'Opiniones personales', 'Suposiciones', 'Los datos son información factual, medible y verificable que sirve como base para el análisis, la toma de decisiones o la investigación.', NULL, NULL),
(82, 56, '¿Qué permite una representación gráfica?', 'Visualizar datos y encontrar patrones', 'Ocultar información', 'Complicar el análisis', 'Las representaciones gráficas (gráficas, diagramas, tablas) permiten visualizar datos de manera clara, identificar patrones, tendencias y relaciones que no son obvias en datos brutos.', NULL, NULL),
(83, 56, '¿Qué relación tienen un \"perro\" con un \"cachorro\"?', 'Perro es adulto, cachorro es cría', 'Son sinónimos', 'Son antónimos', 'Un perro adulto y un cachorro son la misma especie animal pero en diferentes etapas de desarrollo: \"cachorro\" es la cría o bebé del perro.', NULL, NULL),
(84, 56, 'Si A es a B como C es a ___, ¿qué es esto?', 'Una analogía', 'Un sinónimo', 'Una metáfora', 'Una analogía es una relación de semejanza o comparación entre dos pares de elementos. La estructura \"A es a B como C es a D\" expresa una analogía.', NULL, NULL),
(85, 19, '¿Cuál es el resultado de 2 + 3 × 4?', '14 (primero la multiplicación)', '20', '24', 'Por jerarquía de operaciones, la multiplicación (3×4=12) se resuelve antes que la suma, por lo tanto 2 + 12 = 14.', NULL, NULL),
(86, 19, '¿Cuál es la jerarquía correcta de operaciones?', 'Paréntesis, potencias, multiplicación/división, suma/resta', 'Suma, resta, multiplicación, división', 'De izquierda a derecha', 'La jerarquía correcta de operaciones es: 1) Paréntesis, 2) Potencias y raíces, 3) Multiplicación y división (de izquierda a derecha), 4) Suma y resta (de izquierda a derecha).', NULL, NULL),
(87, 19, '¿Qué es una razón?', 'Una comparación entre dos cantidades', 'Una suma', 'Un producto', 'Una razón es una comparación entre dos cantidades mediante una división o cociente, expresada como a:b o a/b.', NULL, NULL),
(88, 19, 'Si 2 manzanas cuestan $10, ¿cuánto cuesta una?', '$5 (regla de tres)', '$10', '$2', 'Mediante regla de tres simple: si 2 manzanas cuestan $10, entonces 1 manzana cuesta $10 ÷ 2 = $5.', NULL, NULL),
(89, 19, '¿Qué es una expresión algebraica?', 'Combinación de números y letras con operaciones', 'Solo números', 'Solo letras', 'Una expresión algebraica es una combinación de números, letras (variables) y operaciones matemáticas (suma, resta, multiplicación, división, potencias).', NULL, NULL),
(90, 19, '¿Cuál es el producto notable (a+b)²?', 'a² + 2ab + b²', 'a² + b²', 'a² - b²', 'El producto notable (a+b)² se desarrolla como a² + 2ab + b² (binomio al cuadrado = cuadrado del primero + doble producto + cuadrado del segundo).', NULL, NULL),
(91, 19, '¿Qué es factorizar?', 'Escribir como producto de factores', 'Sumar términos', 'Restar términos', 'Factorizar es escribir una expresión algebraica como producto de factores (expresiones más simples multiplicadas entre sí).', NULL, NULL),
(92, 19, '¿Qué es una ecuación lineal?', 'Ecuación de grado 1 (ax + b = 0)', 'Ecuación de grado 2', 'Ecuación sin incógnitas', 'Una ecuación lineal es una ecuación de grado 1, donde la variable tiene exponente 1. Tiene la forma ax + b = 0.', NULL, NULL),
(93, 19, '¿Qué forma tiene una ecuación cuadrática?', 'ax² + bx + c = 0', 'ax + b = 0', 'a/x + b = 0', 'Una ecuación cuadrática o de segundo grado tiene la forma estándar ax² + bx + c = 0, donde a, b y c son constantes y a ≠ 0.', NULL, NULL),
(94, 19, '¿Cuántas soluciones puede tener un sistema de ecuaciones lineales?', 'Una, ninguna o infinitas', 'Siembre una', 'Siempre dos', 'Un sistema de ecuaciones lineales puede tener: una solución (compatible determinado), ninguna solución (incompatible) o infinitas soluciones (compatible indeterminado).', NULL, NULL),
(95, 19, '¿Qué mide la media aritmética?', 'El promedio de un conjunto de datos', 'El dato central', 'El dato que más se repite', 'La media aritmética (o promedio) se calcula sumando todos los datos y dividiendo entre el número total de datos. Mide la tendencia central.', NULL, NULL),
(96, 19, '¿Qué es la mediana?', 'El valor central ordenando los datos', 'El promedio', 'El valor que más se repite', 'La mediana es el valor que ocupa la posición central cuando los datos están ordenados de menor a mayor.', NULL, NULL),
(97, 19, '¿Qué es la moda?', 'El valor que más se repite', 'El promedio', 'El valor central', 'La moda es el valor o valores que aparecen con mayor frecuencia en un conjunto de datos.', NULL, NULL),
(98, 19, '¿Qué es la probabilidad?', 'La medida de la posibilidad de que ocurra un evento', 'Un número seguro', 'Una estadística', 'La probabilidad es una medida numérica entre 0 y 1 que cuantifica la posibilidad de que ocurra un evento aleatorio.', NULL, NULL),
(99, 19, '¿Cuál es la ecuación de una línea recta?', 'y = mx + b', 'y = ax² + bx + c', 'x = y', 'La ecuación de una línea recta en su forma pendiente-ordenada es y = mx + b, donde m es la pendiente y b es la intersección con el eje Y.', NULL, NULL),
(100, 19, '¿Qué es un triángulo rectángulo?', 'El que tiene un ángulo de 90°', 'El que tiene tres lados iguales', 'El que tiene todos los ángulos agudos', 'Un triángulo rectángulo es aquel que tiene un ángulo interior de 90° (ángulo recto). Los lados que forman ese ángulo se llaman catetos y el lado opuesto es la hipotenusa.', NULL, NULL),
(101, 3, '¿Cuál es el valor de x?\r\n3x+2+1=0', '-1', '0', '2', 'Para resolver 3x+2+1=0, primero sumamos 2+1=3, quedando 3x+3=0. Luego restamos 3 en ambos lados: 3x = -3. Finalmente dividimos entre 3: x = -1. Por lo tanto, el valor de x es -1 mediante el despeje de la ecuación lineal.', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preparatorias`
--

CREATE TABLE `preparatorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `estado` varchar(100) NOT NULL,
  `municipio` varchar(100) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `ambito` varchar(255) DEFAULT NULL,
  `tipo` varchar(255) NOT NULL,
  `servicio` varchar(100) DEFAULT NULL,
  `clave` varchar(50) NOT NULL,
  `turno` varchar(255) DEFAULT NULL,
  `centro_educativo` varchar(255) NOT NULL,
  `direccion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `preparatorias`
--

INSERT INTO `preparatorias` (`id`, `estado`, `municipio`, `localidad`, `ambito`, `tipo`, `servicio`, `clave`, `turno`, `centro_educativo`, `direccion`, `created_at`, `updated_at`) VALUES
(1, 'PUEBLA', 'CHIGNAHUAPAN', 'RINCONADA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0559T', 'MATUTINO', 'JAIME SABINES', 'PLAZA PRINCIPAL', NULL, NULL),
(2, 'PUEBLA', 'IXTACAMAXTITLAN', 'TEXOCUIXPAN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0560I', 'MATUTINO', 'JUAN CRISOSTOMO BONILLA PEREZ', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(3, 'PUEBLA', 'HUEYTAMALCO', 'AYAHUALO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0561H', 'MATUTINO', 'JOSE VASCONCELOS', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(4, 'PUEBLA', 'XIUTETELCO', 'XALTIPAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0562G', 'MATUTINO', 'JOSE GALVEZ', 'CALLE GUADALUPE S/N', NULL, NULL),
(5, 'PUEBLA', 'GUADALUPE VICTORIA', 'GUADALUPE VICTORIA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0563F', 'MATUTINO', 'C.E. PROF. NICOLAS REYES ALEGRE', '3 SUR Y AVENIDA INDEPENDENCIA S/N', NULL, NULL),
(6, 'PUEBLA', 'CHICHIQUILA', 'HUAXCALECA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0564E', 'MATUTINO', 'HEROES DE CHAPULTEPEC', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(7, 'PUEBLA', 'CHICHIQUILA', 'JESUS MARIA ACATLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0565D', 'MATUTINO', 'DAVID ALFARO SIGUEIROS', 'AVENIDA SONORA S/N', NULL, NULL),
(8, 'PUEBLA', 'LAFRAGUA', 'CUAUHTEMOC', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0566C', 'MATUTINO', 'VICENTE SUAREZ FERRER', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(9, 'PUEBLA', 'GUADALUPE', 'SAN ANTONIO CHILTEPEC', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0568A', 'MATUTINO', 'FRANCISCO I. MADERO', 'PLAZA PRINCIPAL, CHILTEPEC', NULL, NULL),
(10, 'PUEBLA', 'GUADALUPE', 'MIXQUITEPEC', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0569Z', 'MATUTINO', 'MUCIO BRAVO HERRERA', 'MANUEL ALTAMIRANO S/N', NULL, NULL),
(11, 'PUEBLA', 'GENERAL FELIPE ANGELES', 'SANTIAGO TENANGO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0570P', 'MATUTINO', 'TIERRA Y LIBERTAD', 'AVENIDA CENTRAL S/N', NULL, NULL),
(12, 'PUEBLA', 'ACATZINGO', 'ACATZINGO DE HIDALGO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0571O', 'MATUTINO', 'C.E. GENERAL RODOLFO SANCHEZ TABOADA', 'PROLONGACION 5 DE MAYO S/N', NULL, NULL),
(13, 'PUEBLA', 'AJALPAN', 'CIUDAD DE AJALPAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0572N', 'MATUTINO', 'C.E. LIC. MELQUIADES MORALES FLORES', 'EJIDO DE TEOPUXCO', NULL, NULL),
(14, 'PUEBLA', 'AJALPAN', 'CHICHICAPA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0573M', 'MATUTINO', 'MELQUIADES MORALES FLORES', 'CALLE BENITO JUAREZ S/N', NULL, NULL),
(15, 'PUEBLA', 'AJALPAN', 'MAZATIANQUIXCO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0574L', 'MATUTINO', 'HECTOR AZAR', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(16, 'PUEBLA', 'AJALPAN', 'TECPANTZACOALCO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0575K', 'MATUTINO', 'JOAQUIN PAREDES COLIN', 'PLAZA PRINCIPAL', NULL, NULL),
(17, 'PUEBLA', 'AJALPAN', 'HUITZMALOC', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0576J', 'MATUTINO', 'JOSE VASCONCELOS', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(18, 'PUEBLA', 'ZOQUITLAN', 'ACATEPEC (SAN ANTONIO)', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0577I', 'MATUTINO', 'LEANDRO QUINTANILLA PASTOR', 'CERRO HIJADERO KILOMETRO 1.5', NULL, NULL),
(19, 'PUEBLA', 'PUEBLA', 'SAN MIGUEL CANOA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0578H', 'MATUTINO', 'CENTRO ESCOLAR CORONEL RAUL VELASCO DE SANTIAGO', 'TERCERA SECCION', NULL, NULL),
(20, 'PUEBLA', 'CUETZALAN DEL PROGRESO', 'TZINACAPAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0579G', 'MATUTINO', 'NETZAHUALCOYOTL', 'ENFRENTE DEL PANTEON DEL CARMEN', NULL, NULL),
(21, 'PUEBLA', 'CUETZALAN DEL PROGRESO', 'YOHUALICHAN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0580W', 'MATUTINO', 'YOHUALICHAN', 'CALZADA A YOHUALICHAN KILOMETRO . 1', NULL, NULL),
(22, 'PUEBLA', 'ZACAPOAXTLA', 'LAS LOMAS', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0581V', 'MATUTINO', 'FRAY PEDRO DE GANTE', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(23, 'PUEBLA', 'TLACHICHUCA', 'TLACHICHUCA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0583T', 'MATUTINO', 'C.E. LIC. MARIANO PIÐA OLAYA', 'AVENIDA 2 PONIENTE NUM. 212', NULL, NULL),
(24, 'PUEBLA', 'SAN ANDRES CHOLULA', 'SAN LUIS TEHUILOYOCAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0584S', 'MATUTINO', 'TEHUILOYOCAN', 'CALLE 5 DE MAYO NUM. 2047', NULL, NULL),
(25, 'PUEBLA', 'SAN NICOLAS BUENOS AIRES', 'EMILIO PORTES GIL', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0585R', 'MATUTINO', 'AMADO NERVO', 'CALLE 7 NORTE S/N.', NULL, NULL),
(26, 'PUEBLA', 'CAÐADA MORELOS', 'SAN JOSE IXTAPA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0586Q', 'MATUTINO', 'VICENTE SUAREZ FERRER', 'AVENIDA PRINCIPAL A SAN JOSE IXTAPA', NULL, NULL),
(27, 'PUEBLA', 'SAN JUAN ATZOMPA', 'SAN JUAN ATZOMPA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0588O', 'MATUTINO', 'MAXIMINO AVILA CAMACHO', 'CARRIL S/N', NULL, NULL),
(28, 'PUEBLA', 'IZUCAR DE MATAMOROS', 'IZUCAR DE MATAMOROS', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0589N', 'VESPERTINO', 'C.E. PRESIDENTE LAZARO CARDENAS', 'ALLENDE NUM. 300', NULL, NULL),
(29, 'PUEBLA', 'ACATLAN', 'HERMENEGILDO GALEANA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0590C', 'MATUTINO', 'NEZAHUALCOYOTL', 'PRINCIPAL S/N', NULL, NULL),
(30, 'PUEBLA', 'PUEBLA', 'HEROICA PUEBLA DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0592A', 'MATUTINO', 'GREGORIO DE GANTE', '143 B PONIENTE S/N', NULL, NULL),
(31, 'PUEBLA', 'SAN FELIPE TEPATLAN', 'JOJUPANGO (SAN MIGUEL JOJUPANGO)', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0593Z', 'MATUTINO', 'IGNACIO LOPEZ RAYON', 'PLAZA PRINCIPAL', NULL, NULL),
(32, 'PUEBLA', 'TZICATLACOYAN', 'SAN ANTONIO JUAREZ', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0595Y', 'MATUTINO', 'CARLOS CAMACHO ESPIRITU', 'PLAZA PRINCIPAL', NULL, NULL),
(33, 'PUEBLA', 'XICOTLAN', 'XICOTLAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0605O', 'MATUTINO', 'BACHILLERATO GENERAL MUNICIPAL', 'PLAZA PRINCIPAL', NULL, NULL),
(34, 'PUEBLA', 'TEHUITZINGO', 'LA NORIA HIDALGO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0606N', 'VESPERTINO', 'IZCOATL', 'PLAZA PRINCIPAL', NULL, NULL),
(35, 'PUEBLA', 'CAÐADA MORELOS', 'BUENA VISTA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0607M', 'MATUTINO', 'ANGEL MARIA GARIBAY KINTANA', 'CALLE PUEBLA S/N', NULL, NULL),
(36, 'PUEBLA', 'VENUSTIANO CARRANZA', 'SAN DIEGO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0608L', 'MATUTINO', 'EMILIANO ZAPATA', 'REVOLUCION NORTE NUM. 100', NULL, NULL),
(37, 'PUEBLA', 'PUEBLA', 'AGUA SANTA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0609K', 'MATUTINO', 'CARMEN SERDAN', '119 PONIENTE Y 11 SUR', NULL, NULL),
(38, 'PUEBLA', 'ZACATLAN', 'TLALIXTLIPA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0610Z', 'MATUTINO', 'CARMEN SERDAN ALATRISTE', 'PLAZA PRINCIPAL', NULL, NULL),
(39, 'PUEBLA', 'ZACATLAN', 'XONOTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0611Z', 'MATUTINO', 'BENITO JUAREZ', 'PLAZA PRINCIPAL', NULL, NULL),
(40, 'PUEBLA', 'ZACATLAN', 'AYEHUALULCO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0612Y', 'MATUTINO', 'JUAN CANDANEDO GONZALEZ', 'PROLONGACION SAN JUAN S/N', NULL, NULL),
(41, 'PUEBLA', 'PUEBLA', 'HEROICA PUEBLA DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0613X', 'MATUTINO', '2 DE ABRIL', '13 SUR NUM. 103', NULL, NULL),
(42, 'PUEBLA', 'SAN PEDRO CHOLULA', 'SAN FRANCISCO COAPA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0614W', 'MATUTINO', 'JAIME NUNO', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(43, 'PUEBLA', 'SAN PEDRO CHOLULA', 'CHOLULA DE RIVADAVIA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0615V', 'VESPERTINO', 'SARA MARIA BASAVE DE TOXQUI', '16 ORIENTE NUM. 9', NULL, NULL),
(44, 'PUEBLA', 'PUEBLA', 'HEROICA PUEBLA DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0616U', 'MATUTINO', 'FEDERICA M. BONILLA', '18 NORTE NUM. 601', NULL, NULL),
(45, 'PUEBLA', 'TLACUILOTEPEC', 'PAPALOCTIPAN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0617T', 'MATUTINO', 'VICENTE SUAREZ FERRER', 'PLAZA PRINCIPAL', NULL, NULL),
(46, 'PUEBLA', 'IXTACAMAXTITLAN', 'CUATEXMOLA (XONACATITLA)', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0618S', 'MATUTINO', 'NICOLAS BRAVO', 'PLAZA PRINCIPAL', NULL, NULL),
(47, 'PUEBLA', 'TEZIUTLAN', 'SAN JUAN ACATENO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0619R', 'MATUTINO', 'DAVID ALFARO SIQUEIROS', 'PLAZA PRINCIPAL', NULL, NULL),
(48, 'PUEBLA', 'PANTEPEC', 'AGUA LINDA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0620G', 'MATUTINO', 'JAIME SABINES', 'PLAZA PRINCIPAL', NULL, NULL),
(49, 'PUEBLA', 'CHICHIQUILA', 'EL PALMAR', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0621F', 'MATUTINO', 'OCTAVIO PAZ', 'PLAZA PRINCIPAL', NULL, NULL),
(50, 'PUEBLA', 'SAN ANDRES CHOLULA', 'SAN ANDRES CHOLULA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0622E', 'MATUTINO', 'ELENA GARRO', 'PROLONGACION CALZADA ZAVALETA PONIENTE NUM. 321', NULL, NULL),
(51, 'PUEBLA', 'RAFAEL LARA GRAJALES', 'MAXIMO SERDAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0623D', 'MATUTINO', 'VICENTE SUAREZ', 'AVENIDA ALVARO OBREGON S/N', NULL, NULL),
(52, 'PUEBLA', 'ALBINO ZERTUCHE', 'ACAXTLAHUACAN DE ALBINO ZERTUCHE', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0624C', 'MATUTINO', 'BACHILLERATO GENERAL', 'CALLE DE LA JUVENTUD S/N', NULL, NULL),
(53, 'PUEBLA', 'CHILA DE LA SAL', 'CHILA DE LA SAL', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0625B', 'MATUTINO', 'JUAN ESCUTIA', 'FRANCISCO VILLA S/N', NULL, NULL),
(54, 'PUEBLA', 'SAN SEBASTIAN TLACOTEPEC', 'ZACATEPEC DE BRAVO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0626A', 'MATUTINO', 'TZINTZINTEPETL', 'PLAZA PRINCIPAL', NULL, NULL),
(55, 'PUEBLA', 'TEPANCO DE LOPEZ', 'JOSE MARIA PINO SUAREZ', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0627Z', 'MATUTINO', 'DAVID ALFARO SIQUEIROS', 'TEODORO CASTILLO DE LA LUZ NUM. 1', NULL, NULL),
(56, 'PUEBLA', 'HUEYTLALPAN', 'ZITLALA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0628Z', 'MATUTINO', 'XOCHIQUETZAL', 'KILOMETRO 6 CAMINO A ZITLALA', NULL, NULL),
(57, 'PUEBLA', 'ZACAPOAXTLA', 'ZACAPOAXTLA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0629Y', 'MATUTINO', 'VASCO DE QUIROGA', 'PLAZA PRINCIPAL', NULL, NULL),
(58, 'PUEBLA', 'ESPERANZA', 'SANTA CATARINA LOS REYES', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0630N', 'MATUTINO', 'LIC. MELQUIADES MORALES FLORES', 'PLAZA PRINCIPAL', NULL, NULL),
(59, 'PUEBLA', 'TLAPACOYA', 'TLAMAYA GRANDE', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0631M', 'MATUTINO', 'LAZARO CARDENAS', 'PLAZA PRINCIPAL', NULL, NULL),
(60, 'PUEBLA', 'AHUACATLAN', 'XOCHICUAUTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0632L', 'MATUTINO', 'GABINO BARREDA', 'PLAZA PRINCIPAL', NULL, NULL),
(61, 'PUEBLA', 'XOCHITLAN DE VICENTE SUAREZ', 'ZOATECPAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0633K', 'MATUTINO', 'ENRIQUE ZAMORA PALAFOX', 'CALLE TAMANIZ S/N', NULL, NULL),
(62, 'PUEBLA', 'TLAPANALA', 'TEPAPAYECA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0634J', 'MATUTINO', 'CUITLAHUAC', 'CINCO DE MAYO S/N.', NULL, NULL),
(63, 'PUEBLA', 'TEHUACAN', 'SAN CRISTOBAL TEPETEOPAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0635I', 'MATUTINO', 'VICENTE LOMBARDO TOLEDANO', 'FRANCISCO VILLA S/N', NULL, NULL),
(64, 'PUEBLA', 'IXTACAMAXTITLAN', 'SANTA MARIA ZOTOLTEPEC', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0636H', 'MATUTINO', 'JUAN RULFO', 'PLAZA PRINCIPAL', NULL, NULL),
(65, 'PUEBLA', 'ATLIXCO', 'SAN JERONIMO COYULA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0640U', 'MATUTINO', 'HECTOR AZAR', 'CALLE AQUILES SERDAN 296', NULL, NULL),
(66, 'PUEBLA', 'TOCHTEPEC', 'SAN MARTIN CALTENCO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0641T', 'MATUTINO', 'OCTAVIO PAZ', 'PLAZA PRINCIPAL', NULL, NULL),
(67, 'PUEBLA', 'TEHUACAN', 'TEHUACAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0642S', 'MATUTINO', 'EMILIANO ZAPATA', '4 NORTE NUM. 2003', NULL, NULL),
(68, 'PUEBLA', 'SAN MARTIN TEXMELUCAN', 'SAN JUAN TUXCO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0644Q', 'MATUTINO', 'GUSTAVO DIAZ ORDAZ', 'INDEPENDENCIA S/N', NULL, NULL),
(69, 'PUEBLA', 'ESPERANZA', 'SAN JOSE CUYACHAPA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0645P', 'MATUTINO', 'JOSE VASCONCELOS', 'PLAZA PRINCIPAL S/N.', NULL, NULL),
(70, 'PUEBLA', 'IZUCAR DE MATAMOROS', 'IZUCAR DE MATAMOROS', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0646O', 'MATUTINO', 'OCTAVIO PAZ', 'CHAMIZAL NUM. 56', NULL, NULL),
(71, 'PUEBLA', 'PUEBLA', 'HEROICA PUEBLA DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0647N', 'VESPERTINO', 'PABLO NERUDA', '23 PONIENTE NUM. 1301', NULL, NULL),
(72, 'PUEBLA', 'PUEBLA', 'HEROICA PUEBLA DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0648M', 'MATUTINO', 'GABINO BARREDA', '3 SUR NUM. 904', NULL, NULL),
(73, 'PUEBLA', 'SAN ANDRES CHOLULA', 'SAN ANDRES CHOLULA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0649L', 'MATUTINO', 'BACH. GRAL. OFL. JOSE VASCONCELOS', 'CALLE FRANCISCO VILLA 204', NULL, NULL),
(74, 'PUEBLA', 'HUEHUETLAN EL GRANDE', 'SAN MIGUEL ATLAPULCO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0650A', 'MATUTINO', 'HEROES DEL 5 DE MAYO', 'CARRETERA PRINCIPAL A PUEBLA', NULL, NULL),
(75, 'PUEBLA', 'SAN PEDRO CHOLULA', 'SAN CRISTOBAL TEPONTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0651Z', 'VESPERTINO', 'YOLOT`SI', 'AVENIDA ALLENDE NUM. 28', NULL, NULL),
(76, 'PUEBLA', 'TECAMACHALCO', 'SANTIAGO ALSESECA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0652Z', 'MATUTINO', 'QUETZALCOATL', 'CARRETERA FEDERAL PUEBLA-TEHUACAN KILOMETRO 61 S/N', NULL, NULL),
(77, 'PUEBLA', 'ATEXCAL', 'SAN MARTIN ATEXCAL', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0653Y', 'MATUTINO', 'GEORGINA HUERTA DE DURAN', '3 ORIENTE S/N', NULL, NULL),
(78, 'PUEBLA', 'ACATENO', 'JILIAPAN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0654X', 'MATUTINO', 'JOSE VASCONCELOS', 'CAMINO VECINAL A ARROYO BLANCO', NULL, NULL),
(79, 'PUEBLA', 'HUAQUECHULA', 'TEZONTEOPAN DE BONILLA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0655W', 'MATUTINO', 'CELIA MARTINEZ MIRANDA', 'CALLE BENITO JUAREZ S/N', NULL, NULL),
(80, 'PUEBLA', 'PUEBLA', 'HEROICA PUEBLA DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0656V', 'MATUTINO', 'NICOLAS BRAVO', 'TUXTLA GUTIERREZ MZ 28 LT 18', NULL, NULL),
(81, 'PUEBLA', 'ACAJETE', 'SAN JERONIMO OCOTITLAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0657U', 'MATUTINO', 'CARLOS CAMACHO ESPIRITU', 'AVENIDA CHAPULTEPEC S/N', NULL, NULL),
(82, 'PUEBLA', 'CHIGNAHUAPAN', 'TENEXTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0658T', 'MATUTINO', 'NARCISO MENDOZA', 'PRAXEDIS GONZALEZ PEREZ NUM. 24', NULL, NULL),
(83, 'PUEBLA', 'CUAUTEMPAN', 'IXTOLCO DE MORELOS', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0659S', 'MATUTINO', 'JOSE GALVEZ', 'PLAZA PRINCIPAL', NULL, NULL),
(84, 'PUEBLA', 'CHIGNAUTLA', 'COAHUIXCO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0660H', 'MATUTINO', 'FRAY PEDRO DE GANTE', 'CALLE CARRANZA S/N', NULL, NULL),
(85, 'PUEBLA', 'TZICATLACOYAN', 'TZICATLACOYAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0661G', 'MATUTINO', 'JOSE ALFREDO MITRE PONCE', 'PLAZA PRINCIPAL', NULL, NULL),
(86, 'PUEBLA', 'TEZIUTLAN', 'ATOLUCA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0662F', 'MATUTINO', 'ALVARO GALVEZ Y FUENTES', 'CALLE EMILIANO ZAPATA SECCION 1A.', NULL, NULL),
(87, 'PUEBLA', 'TEZIUTLAN', 'MEXCALCUAUTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0663E', 'MATUTINO', 'ALVARO GALVEZ Y FUENTES', 'PLAZA PRINCIPAL', NULL, NULL),
(88, 'PUEBLA', 'TEZIUTLAN', 'TEZIUTLAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0664D', 'MATUTINO', 'NICOLAS BRAVO', 'PLAZA PRINCIPAL', NULL, NULL),
(89, 'PUEBLA', 'ZARAGOZA', 'ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0665C', 'MATUTINO', 'JOSE MARIA MORELOS Y PAVON', 'PROLONGACION DE BENITO JUAREZ S/N', NULL, NULL),
(90, 'PUEBLA', 'ZARAGOZA', 'ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0666B', 'MATUTINO', 'TIERRA Y LIBERTAD', 'PLAZA PRINCIPAL', NULL, NULL),
(91, 'PUEBLA', 'NOPALUCAN', 'EL RINCON CITLALTEPETL', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0668Z', 'MATUTINO', 'ANDRES SATURNINO ROSARIO', 'CAMINO A LA VENTA S/N', NULL, NULL),
(92, 'PUEBLA', 'ACATLAN', 'ILAMACINGO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0669Z', 'MATUTINO', 'OCTAVIO PAZ', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(93, 'PUEBLA', 'CHILA', 'FRANCISCO IBARRA RAMOS', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0670O', 'MATUTINO', 'FRAY PEDRO DE GANTE', 'FRANCISCO VILLA NUM. 9', NULL, NULL),
(94, 'PUEBLA', 'SAN SEBASTIAN TLACOTEPEC', 'VILLA DEL RIO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0671N', 'MATUTINO', 'VICENTE LOMBARDO TOLEDANO', 'CAMINO A NARANJASTITLA S/N', NULL, NULL),
(95, 'PUEBLA', 'CUETZALAN DEL PROGRESO', 'XALTIPAN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0672M', 'MATUTINO', 'HERMANOS SERDAN', 'CARETERA A REYES DE VALLARTA S/N', NULL, NULL),
(96, 'PUEBLA', 'ZACAPOAXTLA', 'EL MOLINO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0673L', 'MATUTINO', 'FRAY PEDRO DE GANTE', 'PLAZA PRINCIPAL', NULL, NULL),
(97, 'PUEBLA', 'HUEHUETLA', 'CHILOCOYO DEL CARMEN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0674K', 'MATUTINO', 'VICENTE SUAREZ', 'PLAZA PRINCIPAL', NULL, NULL),
(98, 'PUEBLA', 'ATZITZINTLA', 'PASO CARRETAS', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0675J', 'MATUTINO', 'IGNACIO ZARAGOZA', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(99, 'PUEBLA', 'TOCHIMILCO', 'SAN ANTONIO ALPANOCAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0676I', 'MATUTINO', 'CUAUHTEMOC', 'CALLE HERMITA S/N', NULL, NULL),
(100, 'PUEBLA', 'ATZITZIHUACAN', 'SAN JUAN TEJUPA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0677H', 'MATUTINO', 'HECTOR AZAR', 'CARRETERA ATZITZIHUACAN', NULL, NULL),
(101, 'PUEBLA', 'ZACATLAN', 'JILOTZINGO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0678G', 'MATUTINO', 'MANUEL M. FLORES', 'PLAZA PRINCIPAL', NULL, NULL),
(102, 'PUEBLA', 'AHUACATLAN', 'TLACOTEPEC (SAN MATEO)', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0679F', 'MATUTINO', 'TLATELOLCO', 'PLAZA PRINCIPAL', NULL, NULL),
(103, 'PUEBLA', 'HERMENEGILDO GALEANA', 'COYAY', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0680V', 'MATUTINO', 'NETZAHUALCOYOTL', 'PLAZA PRINCIPAL', NULL, NULL),
(104, 'PUEBLA', 'FRANCISCO Z. MENA', 'PALMA REAL DE ADENTRO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0681U', 'MATUTINO', 'JOSE IGNACIO GREGORIO COMONFORT', 'PLAZA PRINCIPAL', NULL, NULL),
(105, 'PUEBLA', 'FRANCISCO Z. MENA', 'COYOLITO (EL COYOL)', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0682T', 'MATUTINO', 'VICENTE SUAREZ FERRER', 'PLAZA PRINCIPAL', NULL, NULL),
(106, 'PUEBLA', 'XICOTEPEC', 'SANTA RITA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0683S', 'MATUTINO', 'FRIDA KAHLO', 'PLAZA PRINCIPAL', NULL, NULL),
(107, 'PUEBLA', 'HUAUCHINANGO', 'HUILACAPIXTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0684R', 'MATUTINO', 'BACHILLERATO GENERAL', 'PLAZA PRINCIPAL', NULL, NULL),
(108, 'PUEBLA', 'HONEY', 'CHILA DE JUAREZ', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0685Q', 'MATUTINO', 'BACHILLERATO GENERAL', 'PLAZA PRINCIPAL', NULL, NULL),
(109, 'PUEBLA', 'HUATLATLAUCA', 'TEPETZITZINTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0686P', 'MATUTINO', 'CULTURA MAYA', 'CALLE CUAUHTEMOC NUM. 103', NULL, NULL),
(110, 'PUEBLA', 'TLAOLA', 'XALTEPUXTLA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0687O', 'MATUTINO', 'LEONARDO DA VINCI', 'AVENIDA LAZARO CARDENAS S/N', NULL, NULL),
(111, 'PUEBLA', 'JALPAN', 'VISTA HERMOSA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0688N', 'MATUTINO', 'IGNACIO MANUEL ALTAMIRANO', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(112, 'PUEBLA', 'TLAOLA', 'XOCHINANACATLAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0689M', 'MATUTINO', 'VICENTE SUAREZ', 'PLAZA PRINCIPAL', NULL, NULL),
(113, 'PUEBLA', 'TLAOLA', 'TLALTEPANGO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0690B', 'MATUTINO', 'JOSE JOAQUIN FERNANDEZ DE LIZARDI', 'XOMEATL S/N', NULL, NULL),
(114, 'PUEBLA', 'JALPAN', 'EJIDO DE JALPAN (LA ZONA)', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0691A', 'MATUTINO', 'ELSA CORDOBA MORAN', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(115, 'PUEBLA', 'JALPAN', 'AGUA LINDA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0692Z', 'MATUTINO', 'SALVADOR DIAZ MIRON', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(116, 'PUEBLA', 'TEHUACAN', 'TEHUACAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0693Z', 'MATUTINO', 'FRAY PEDRO DE GANTE', 'LIBRAMIENTO SAN MARCOS S/N', NULL, NULL),
(117, 'PUEBLA', 'CUYOACO', 'CUYOACO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0694Y', 'MATUTINO', 'MANUEL ARELLANO ESPINOZA', 'AVENIDA PRINCIPAL S/N', NULL, NULL),
(118, 'PUEBLA', 'YEHUALTEPEC', 'SAN MIGUEL ZOZUTLA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0695X', 'MATUTINO', 'PROF. AURELIO FUENTES BOBADILLA', 'FRANCISCO I. MADERO NUM. 10', NULL, NULL),
(119, 'PUEBLA', 'XICOTEPEC', 'XICOTEPEC DE JUAREZ', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0696W', 'MATUTINO', 'CARLOS PEREYRA', 'AVENIDA PRIMAVERA S/N', NULL, NULL),
(120, 'PUEBLA', 'XICOTEPEC', 'XICOTEPEC DE JUAREZ', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0697V', 'VESPERTINO', 'TORIBIO DE BENAVENTE', 'CALLE AZALEAS NUM. 103', NULL, NULL),
(121, 'PUEBLA', 'SAN PEDRO CHOLULA', 'CHOLULA DE RIVADAVIA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0698U', 'MATUTINO', 'FORJADORES DE PUEBLA', 'RIO ATOYAC NUM. 113', NULL, NULL),
(122, 'PUEBLA', 'PANTEPEC', 'NUEVO CARRIZAL', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0699T', 'MATUTINO', 'RAFAEL RAMIREZ', 'CALLE PRINCIPAL S/N', NULL, NULL),
(123, 'PUEBLA', 'XICOTEPEC', 'NACTANCA GRANDE', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0701R', 'MATUTINO', 'OCTAVIO PAZ', 'CALLE NACTANCA CHICA S/N', NULL, NULL),
(124, 'PUEBLA', 'VICENTE GUERRERO', 'ALHUACA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0703P', 'MATUTINO', 'BACHILLERATO GENERAL', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(125, 'PUEBLA', 'PANTEPEC', 'LA CEIBA CHICA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0704O', 'MATUTINO', 'LUIS DONALDO COLOSIO MURRIETA', 'PLAZA PRINCIPAL', NULL, NULL),
(126, 'PUEBLA', 'ACTEOPAN', 'ACTEOPAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0705N', 'MATUTINO', 'TELPOCHCALLI', 'CALLE COLON S/N', NULL, NULL),
(127, 'PUEBLA', 'IXTACAMAXTITLAN', 'ATEXQUILLA CUAPAZOLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0706M', 'MATUTINO', 'TIERRA Y LIBERTAD', 'PLAZA PRINCIPAL', NULL, NULL),
(128, 'PUEBLA', 'IXTACAMAXTITLAN', 'TENTZONCUAHUIGTIC', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0707L', 'MATUTINO', 'MACUILXOCHITL', 'PLAZA PRINCIPAL', NULL, NULL),
(129, 'PUEBLA', 'PANTEPEC', 'EJIDO CAÐADA COLOTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0708K', 'MATUTINO', 'DIEGO RIVERA', 'PLAZA PRINCIPAL', NULL, NULL),
(130, 'PUEBLA', 'VENUSTIANO CARRANZA', 'SAN BARTOLO DEL ESCOBAL', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0709J', 'MATUTINO', 'BACHILLERATO GENERAL MUNICIPAL', 'PLAZA PRINCIPAL', NULL, NULL),
(131, 'PUEBLA', 'TLATLAUQUITEPEC', 'LA UNION', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0710Z', 'MATUTINO', 'FRIDA KAHLO', 'PLAZA PRINCIPAL', NULL, NULL),
(132, 'PUEBLA', 'ZAUTLA', 'SANTIAGO ZAUTLA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0711Y', 'MATUTINO', 'ISABEL DIAZ DE BARTLETT', 'VENUSTIANO CARRANZA S/N', NULL, NULL),
(133, 'PUEBLA', 'ZACATLAN', 'NANACAMILA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0712X', 'MATUTINO', 'FRIDA KAHLO', 'PLAZA PRINCIPAL', NULL, NULL),
(134, 'PUEBLA', 'TEHUACAN', 'TEHUACAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0713W', 'VESPERTINO', 'C.E. PRESIDENTE VENUSTIANO CARRANZA', '6 PONIENTE Y 2 NORTE', NULL, NULL),
(135, 'PUEBLA', 'IXTACAMAXTITLAN', 'HUIXCOLOTLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0714V', 'MATUTINO', 'JUAN ALDAMA', 'PLAZA PRINCIPAL', NULL, NULL),
(136, 'PUEBLA', 'PUEBLA', 'HEROICA PUEBLA DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0716T', 'MATUTINO', 'GABINO BARREDA', 'CALLE DEL NI¾O ARTILLERO S/N', NULL, NULL),
(137, 'PUEBLA', 'HUEJOTZINGO', 'HUEJOTZINGO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0717S', 'MATUTINO', 'HEROES DEL 5 DE MAYO', 'AVENIDA MIGUEL HIDALGO NUM. 818', NULL, NULL),
(138, 'PUEBLA', 'HUEYTAMALCO', 'HUEYTAMALCO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0718R', 'MATUTINO', 'DAVID ALFARO SIQUEIROS', 'LA CARAMBADA', NULL, NULL),
(139, 'PUEBLA', 'PAHUATLAN', 'ATLA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0719Q', 'MATUTINO', 'TELPOCHKALLI', 'CALLE PRINCIPAL S/N', NULL, NULL),
(140, 'PUEBLA', 'HUEJOTZINGO', 'SANTA MARIA NEPOPUALCO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0720F', 'MATUTINO', 'MARIO MOLINA', 'CALLE DE LA CRUZ S/N', NULL, NULL),
(141, 'PUEBLA', 'PUEBLA', 'LOS ANGELES TETELA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0721E', 'MATUTINO', 'RUFINO TAMAYO', '16 DE SEPTIEMBRE S/N', NULL, NULL),
(142, 'PUEBLA', 'CHIAUTZINGO', 'SAN ANTONIO TLATENCO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0722D', 'MATUTINO', 'NICOLAS BRAVO', 'AVENIDA CHIAUTZINGO S/N', NULL, NULL),
(143, 'PUEBLA', 'HUAUCHINANGO', 'CUACUILA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0723C', 'MATUTINO', 'RICARDO REYES MARQUEZ', 'PLAZA PRINCIPAL', NULL, NULL),
(144, 'PUEBLA', 'XICOTEPEC', 'VILLA AVILA CAMACHO (LA CEIBA)', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0724B', 'MATUTINO', 'BACHILLERATO GENERAL', 'PLAZA PRINCIPAL', NULL, NULL),
(145, 'PUEBLA', 'ZACATLAN', 'ZACATLAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0725A', 'MATUTINO', 'BENITO JUAREZ', 'PLAZA PRINCIPAL', NULL, NULL),
(146, 'PUEBLA', 'SAN PEDRO CHOLULA', 'SANTIAGO MOMOXPAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0726Z', 'VESPERTINO', 'NATALIA SERDAN ALATRISTE', 'CHOLULTECAS NORTE S/N', NULL, NULL),
(147, 'PUEBLA', 'PUEBLA', 'HEROICA PUEBLA DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0728Y', 'MATUTINO', 'BACHILLERATO GENERAL OFICIAL RAFAEL RAMIREZ', 'RETORNO RAFAEL MORENO S/N', NULL, NULL),
(148, 'PUEBLA', 'ATLIXCO', 'ATLIXCO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0729X', 'MATUTINO', 'ELENA GARRO', 'PLAZA PRINCIPAL S/N', NULL, NULL),
(149, 'PUEBLA', 'JOPALA', 'BUENOS AIRES', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0730M', 'MATUTINO', 'BACHILLERATO ESTATAL', 'PLAZA PRINCIPAL', NULL, NULL),
(150, 'PUEBLA', 'TEZIUTLAN', 'TEZIUTLAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0731L', 'VESPERTINO', 'C.E. PRESIDENTE MANUEL AVILA CAMACHO', 'AVENIDA HIDALGO NUM. 472', NULL, NULL),
(151, 'PUEBLA', 'NICOLAS BRAVO', 'AZUMBILLA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0732K', 'MATUTINO', 'NETZAHUALCOYOTL', 'PROLONGACION DE ELIZA CUEVAS S/N', NULL, NULL),
(152, 'PUEBLA', 'ACAJETE', 'APANGO DE ZARAGOZA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0733J', 'MATUTINO', 'DAVID ALFARO SIQUEIROS', 'PLAZA PRINCIPAL', NULL, NULL),
(153, 'PUEBLA', 'SAN FELIPE TEOTLALCINGO', 'SAN FELIPE TEOTLALCINGO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0734I', 'MATUTINO', 'CUAUHTEMOC', 'CUAUHTEMOC S/N', NULL, NULL),
(154, 'PUEBLA', 'TLACUILOTEPEC', 'PLAN DE AYALA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0735H', 'VESPERTINO', 'DIEGO RIVERA', 'PLAZA PRINCIPAL', NULL, NULL),
(155, 'PUEBLA', 'ZIHUATEUTLA', 'MAZACOATLAN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0736G', 'MATUTINO', 'QUETZALCOATL', 'CALLE HIDALGO', NULL, NULL),
(156, 'PUEBLA', 'ATEMPAN', 'APATAUYAN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0737F', 'MATUTINO', 'MANUEL AVILA CAMACHO', 'CALLE EL PEDREGAL S/N', NULL, NULL),
(157, 'PUEBLA', 'HUEYTAMALCO', 'SAN ANGEL CUAUXOCOTA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0738E', 'MATUTINO', 'JOSE CLEMENTE OROZCO', 'CARRETERA A HUEYTAMALCO-AYOTOXCO S/N', NULL, NULL),
(158, 'PUEBLA', 'HUEYTAMALCO', 'LIMONTITAN GRANDE', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0739D', 'MATUTINO', 'TIERRA Y LIBERTAD', 'PLAZA PRINCIPAL', NULL, NULL),
(159, 'PUEBLA', 'TEZIUTLAN', 'IXTICPAN', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0740T', 'MATUTINO', 'DIEGO RIVERA', 'PRIV. REFORMA NO. 1', NULL, NULL),
(160, 'PUEBLA', 'TEZIUTLAN', 'SAN SEBASTIAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '21EBH0741S', 'MATUTINO', 'VICENTE SUAREZ FERRER', 'CALLE 5 DE MAYO S/N', NULL, NULL),
(161, 'CHIHUAHUA', 'CHIHUAHUA', 'CHIHUAHUA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0178X', 'MATUTINO', 'INSTITUTO TECNOLOGICO DE CONTABILIDAD, MERCADOTECNIA Y ADMINISTRACION', 'ORTIZ DE CAMPOS NUM. 1308', NULL, NULL),
(162, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3411G', 'NOCTURNO', 'INSTITUTO ROSARIO CASTELLANOS', 'VENUSTIANO CARRANZA NUM. 1518', NULL, NULL),
(163, 'CHIHUAHUA', 'BALLEZA', 'PICHIQUE', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0129A', 'MATUTINO', 'TELEBACHILLERATO 86137', 'PICHIQUE', NULL, NULL),
(164, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3374T', 'VESPERTINO', 'BACHILLERATO DEL INSTITUTO SUPERIOR DE CIENCIAS DE CIUDAD JUAREZ', 'IGNACIO ZARAGOZA NUM. 1358-C', NULL, NULL),
(165, 'CHIHUAHUA', 'MANUEL BENAVIDES', 'MANUEL BENAVIDES', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0053B', 'VESPERTINO', 'MANUEL BENAVIDES 8653', 'SALON EJIDAL', NULL, NULL),
(166, 'CHIHUAHUA', 'COYAME DEL SOTOL', 'SANTIAGO DE COYAME', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0069C', 'MATUTINO', 'TORIBIO ORTEGA 8669', 'DEL PILAR Y JUAREZ', NULL, NULL),
(167, 'CHIHUAHUA', 'CHIHUAHUA', 'CHIHUAHUA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0231B', 'NOCTURNO', 'CENTRO DE BACHILLERATO BENJAMIN FRANKLIN', '5A NUM. 2816', NULL, NULL),
(168, 'CHIHUAHUA', 'CHIHUAHUA', 'CHIHUAHUA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0231B', 'MATUTINO', 'CENTRO DE BACHILLERATO BENJAMIN FRANKLIN', '5A NUM. 2816', NULL, NULL),
(169, 'CHIHUAHUA', 'GUACHOCHI', 'LAGUNA DE ABOREACHI', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0130Q', 'MATUTINO', 'TELEBACHILLERATO 86138', 'LAGUNA DE ABOREACHI', NULL, NULL),
(170, 'CHIHUAHUA', 'CHIHUAHUA', 'CHIHUAHUA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0233Z', 'MATUTINO', 'CENTRO DE BACHILLERATO BENJAMIN FRANKLIN', '5A NUM. 2816', NULL, NULL),
(171, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0253N', 'VESPERTINO', 'CULTURAL BACHILLERATO', 'AVENIDA DE LA RAZA NUM. 2315', NULL, NULL),
(172, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0253N', 'NOCTURNO', 'CULTURAL BACHILLERATO', 'AVENIDA DE LA RAZA NUM. 2315', NULL, NULL),
(173, 'CHIHUAHUA', 'JIMENEZ', 'LAGUNA DE PALOMAS (ESTACION CARRILLO)', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0126D', 'MATUTINO', 'GABRIELA MISTRAL 86139', 'ESTACION CARRILLO', NULL, NULL),
(174, 'CHIHUAHUA', 'GUADALUPE Y CALVO', 'EL ZAPOTE', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0131P', 'MATUTINO', 'AMADO NERVO 86140', 'EL ZAPOTE', NULL, NULL),
(175, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3615A', 'MATUTINO', 'INSTITUTO MIGUEL ALEMAN VALDEZ', 'CENTRO COMERCIAL DE LA AVENIDA JUAREZ PORVENIR NUM. 220 LOCAL 6,7', NULL, NULL),
(176, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0074B', 'MATUTINO', 'PREPARATORIA INSTITUTO PASO DEL NORTE', '18 DE MARZO NUM. 2575 ORIENTE', NULL, NULL),
(177, 'CHIHUAHUA', 'GUADALUPE Y CALVO', 'COLORADAS DE LOS CHAVEZ', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0133N', 'MATUTINO', 'TELEBACHILLERATO 86141', 'COLORADAS DE LOS CHAVEZ', NULL, NULL),
(178, 'CHIHUAHUA', 'GUADALUPE Y CALVO', 'EL SAUCITO DE ARAUJO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0134M', 'MATUTINO', 'TELEBACHILLERATO 86143', 'EL SAUCITO DE ARAUJO', NULL, NULL),
(179, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3605U', 'MATUTINO', 'PREPARATORIA COLEGIAL RIO GRANDE', 'MORA NUM. 6758', NULL, NULL),
(180, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3604V', 'NOCTURNO', 'INSTITUTO SAN FRANCISCO DE ASIS', 'GUATEMALA NUM. 729', NULL, NULL),
(181, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0250Q', 'MATUTINO', 'BACHILLERATO LICEO PRE UNIVERSITARIO', 'IGNACIO ZARAGOZA NUM. 737', NULL, NULL),
(182, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0256K', 'MATUTINO', 'BACHILLERATO SIERRA MADRE', 'CAMINO VIEJO A SAN JOSE NUM. 8971', NULL, NULL),
(183, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3378P', 'VESPERTINO', 'BACHILLERATO RODOLFO FIERRO', 'RODOLFO FIERRO S/N Y EJE VIAL JUAN GABRIEL', NULL, NULL),
(184, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0247C', 'MATUTINO', 'CENTRO DE BACHILLERATO ALFA Y OMEGA', 'MELQUIADES ALANIS NUM. 6564-B', NULL, NULL),
(185, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3382B', 'MATUTINO', 'INSTITUTO ROSARIO CASTELLANOS', 'VENUSTIANO CARRANZA NUM. 1518', NULL, NULL),
(186, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3648S', 'MATUTINO', 'PREPARATORIA CULTURAL COLOSIO', 'SIMONA BARBA NUM. 6301', NULL, NULL),
(187, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3647T', 'VESPERTINO', 'PREPARATORIA GRATUITA RAFAEL AGUILAR CASTRO', 'TLAXCALA Y ANAHUAC', NULL, NULL),
(188, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3403Y', 'MATUTINO', 'PREPARATORIA ALEJANDRO MAGNO', 'SANDIA NUM. 6080', NULL, NULL),
(189, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3671T', 'MATUTINO', 'INSTITUTO BENITO JUAREZ II', 'CUSTODIA DE LA REPUBLICA NUM. 1205', NULL, NULL),
(190, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3660N', 'MATUTINO', 'CENTRO DE BACHILLERATO NACIONES UNIDAS UNIDAD OASIS REVOLUCION', 'SANTOS ORTIZ NUM. 1820', NULL, NULL),
(191, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3410H', 'MATUTINO', 'INSTITUTO AGUILAS DE CHIHUAHUA', 'CARRETERA JUAREZ-PORVENIR NUM. 351', NULL, NULL),
(192, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3657Z', 'MATUTINO', 'COLEGIO OBREGON UNIDAD JUAREZ', 'TEXCOCO NUM. 4860', NULL, NULL),
(193, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3654C', 'MATUTINO', 'PREPARATORIA BENITO JUAREZ', 'PAKISTAN NUM. 6921', NULL, NULL),
(194, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3417A', 'VESPERTINO', 'PREPARATORIA PROFR RAYMUNDO D LOPEZ LOPEZ', 'CAMINO VIEJO A SAN JOSE S/N', NULL, NULL),
(195, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0208A', 'NOCTURNO', 'PREPARATORIA JUAN ALVAREZ', 'BARTOLOME DE LAS CASAS NUM. 139 SUR', NULL, NULL),
(196, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0216J', 'NOCTURNO', 'PREPARATORIA AZTLAN DE JUAREZ NOCTURNA', 'AVENIDA HENEQUEN NUM. 658', NULL, NULL),
(197, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3420O', 'MATUTINO', 'PREPARATORIA CHAPULTEPEC', 'PAVOREAL NUM. 1523', NULL, NULL),
(198, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0212N', 'MATUTINO', 'PREPARATORIA AZTLAN DE JUAREZ', 'AVENIDA HENEQUEN NUM. 658', NULL, NULL),
(199, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3406V', 'VESPERTINO', 'PREPARATORIA SEBASTIAN LERDO DE TEJADA', 'AVENIDA LERDO DE TEJADA NUM. 206 NORTE', NULL, NULL),
(200, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3422M', 'MATUTINO', 'PREPARATORIA PANAMERICANA TRANSCONTINENTAL', 'CARRETERA PANAMERICANA NUM. 5643', NULL, NULL),
(201, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3406V', 'MATUTINO', 'PREPARATORIA SEBASTIAN LERDO DE TEJADA', 'AVENIDA LERDO DE TEJADA NUM. 206 NORTE', NULL, NULL),
(202, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3388W', 'NOCTURNO', 'CENTRO DE BACHILLERATO NACIONES UNIDAS', 'CALZADA 5 DE FEBRERO NUM. 1621', NULL, NULL),
(203, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3407U', 'VESPERTINO', 'PREPARATORIA GABRIELA MISTRAL', 'IGNACIO ZARAGOZA NUM. 620', NULL, NULL),
(204, 'CHIHUAHUA', 'URUACHI', 'URUACHI', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0194O', 'VESPERTINO', 'JOSE MARIA PONCE DE LEON', 'URUACHI', NULL, NULL),
(205, 'CHIHUAHUA', 'BOCOYNA', 'CREEL', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBP0003Q', 'MATUTINO', 'BACHILLERATO NORMAL YERMO Y PARRES', 'TARAHUMARA NUM. 71', NULL, NULL),
(206, 'CHIHUAHUA', 'URIQUE', 'SAN RAFAEL', 'RURAL', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0187E', 'VESPERTINO', 'PREPARATORIA PARTICULAR DEL MAGISTERIO SAN RAFAEL', 'SAN RAFAEL', NULL, NULL),
(207, 'CHIHUAHUA', 'GUAZAPARES', 'TEMORIS', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0183I', 'VESPERTINO', 'PREPARATORIA PARTICULAR DEL MAGISTERIO DE TEMORIS', 'AVENIDA LOPEZ MATEOS NUM. 20', NULL, NULL),
(208, 'CHIHUAHUA', 'BOCOYNA', 'CREEL', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08SBC2161M', 'MATUTINO', 'PREPARATORIA POR COOPERACION 8413', 'RARAMURI NUM. 678 CREEL', NULL, NULL),
(209, 'CHIHUAHUA', 'BOCOYNA', 'BOCOYNA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0217I', 'MATUTINO', 'COMPLEJO EDUCACIONAL DE BACHILLERATO Y TECNICAS', 'FTO. 18311', NULL, NULL),
(210, 'CHIHUAHUA', 'CHINIPAS', 'CHINIPAS DE ALMADA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08SBC2164J', 'MATUTINO', 'PREPARATORIA POR COOPERACION 8415 CHINIPAS', 'JUAREZ Y CALLEJON MINA', NULL, NULL),
(211, 'CHIHUAHUA', 'BOCOYNA', 'SAN JUANITO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0175Z', 'VESPERTINO', 'PREPARATORIA ALTA DIXON', 'FRANCISCO I. MADERO S/N', NULL, NULL),
(212, 'CHIHUAHUA', 'URIQUE', 'LA LLUVIA DE ORO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0101V', 'MATUTINO', 'MOCTEZUMA 86101', 'CIENEGA LLUVIA DE ORO', NULL, NULL),
(213, 'CHIHUAHUA', 'URIQUE', 'LA REFORMA', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0136K', 'MATUTINO', 'TELEBACHILLERATO 86144', 'LA REFORMA', NULL, NULL),
(214, 'CHIHUAHUA', 'BATOPILAS', 'POLANCO (RANCHERIA MINERAL POLANCO)', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0067E', 'MATUTINO', 'NATALIA PORTILLO VEGA 8667', 'POLANCO', NULL, NULL),
(215, 'CHIHUAHUA', 'URIQUE', 'CEROCAHUI', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0009O', 'MATUTINO', 'ANDRES LARA 8608', 'CEROCAHUI', NULL, NULL),
(216, 'CHIHUAHUA', 'BATOPILAS', 'BATOPILAS', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0026E', 'MATUTINO', 'BATOPILAS 8625', 'BATOPILAS', NULL, NULL),
(217, 'CHIHUAHUA', 'OCAMPO', 'HUEVACHI', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0105R', 'MATUTINO', 'JESUS GARCIA 86105', 'HUEBACHI', NULL, NULL),
(218, 'CHIHUAHUA', 'BATOPILAS', 'ABOREACHI', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0106Q', 'MATUTINO', 'MANUEL GOMEZ MORIN 86106', 'ABOREACHI', NULL, NULL),
(219, 'CHIHUAHUA', 'CHINIPAS', 'MILPILLAS', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0025F', 'MATUTINO', 'LUIS DONALDO COLOSIO 8624', 'MILPILLAS', NULL, NULL),
(220, 'CHIHUAHUA', 'BATOPILAS', 'YOQUIVO', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0037K', 'MATUTINO', 'ABRAHAM GONZALEZ 8636', 'YOQUIVO', NULL, NULL),
(221, 'CHIHUAHUA', 'GUAZAPARES', 'GUAZAPARES', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0035M', 'VESPERTINO', 'MANUEL AMAYA RAMOS 8634', 'GUAZAPARES', NULL, NULL),
(222, 'CHIHUAHUA', 'BOCOYNA', 'PANALACHI', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0103T', 'MATUTINO', 'REVOLUCION MEXICANA 86103', 'PANALACHI', NULL, NULL),
(223, 'CHIHUAHUA', 'MAGUARICHI', 'MAGUARICHI', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0028C', 'MATUTINO', 'AGUSTIN MELGAR 8627', 'MAGUARICHI', NULL, NULL),
(224, 'CHIHUAHUA', 'BOCOYNA', 'BAHUINOCACHI', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0123G', 'MATUTINO', 'NETZAHUALCOYOTL 86128', 'BAHUINOCACHI', NULL, NULL),
(225, 'CHIHUAHUA', 'BOCOYNA', 'BOCOYNA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0081Y', 'MATUTINO', 'ADOLFO LOPEZ MATEOS 8679', 'BOCOYNA', NULL, NULL),
(226, 'CHIHUAHUA', 'BOCOYNA', 'SISOGUICHI', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0041X', 'MATUTINO', 'BATALLON DE SAN PATRICIO 8640', 'SISOGUICHI', NULL, NULL),
(227, 'CHIHUAHUA', 'CHINIPAS', 'IGNACIO VALENZUELA LAGARDA (LORETO)', 'RURAL', 'PUBLICO', 'BACHILLERATO GENERAL', '08STH0070S', 'MATUTINO', 'ISAAC NEWTON 8670', 'IGNACIO VALENZUELA (LORETO)', NULL, NULL),
(228, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3424K', 'MATUTINO', 'CENTRO EDUCATIVO GRAN VISION', 'MANUEL CLOUTHIER NUM. 8018', NULL, NULL),
(229, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0024U', 'MATUTINO', 'PREPARATORIA IBEROAMERICANA', 'BARTOLOME DE LAS CASAS NUM. 139 SUR', NULL, NULL),
(230, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0029P', 'MATUTINO', 'PREPARATORIA LUIS URIAS BELDERRAIN', 'JOAQUIN TERRAZAS NUM. 320', NULL, NULL),
(231, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0050S', 'VESPERTINO', 'PREPARATORIA HERMANOS ESCOBAR', 'LIBERTAD Y MARIANO VARELA NUM. 1903', NULL, NULL),
(232, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0061Y', 'MATUTINO', 'PREPARATORIA GRAL DE DIVISION FRANCISCO VILLA', 'INSURGENTES NUM. 766 ORIENTE', NULL, NULL),
(233, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0146E', 'MATUTINO', 'PREPARATORIA PARTICULAR CULTURAL', 'AVENIDA DE LA RAZA NUM. 2315', NULL, NULL),
(234, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0148C', 'VESPERTINO', 'PREPARATORIA JUAN DE LA BARRERA', 'SALTILLO NUM. 1186 SUR', NULL, NULL),
(235, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0205D', 'MATUTINO', 'INSTITUTO DE BACHILLERATO TERESA DE AVILA', 'AVENIDA COYOACAN NUM. 2736 ORIENTE', NULL, NULL),
(236, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0230C', 'VESPERTINO', 'BACHILLERATO JOSEFA VILLEGAS OROZCO', 'TETZALES NUM. 1820', NULL, NULL),
(237, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0240J', 'MATUTINO', 'CENTRO DE BACHILLERATO DOS NACIONES', 'CALZADA 5 DE FEBRERO NUM. 1621', NULL, NULL),
(238, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3651F', 'MATUTINO', 'BACHILLERATO DE LA UNIVERSIDAD INTERAMERICANA DEL NORTE CAMPUS CIUDAD JUAREZ', 'BOULEVARD TOMAS FERNANDEZ NUM. 8310', NULL, NULL),
(239, 'CHIHUAHUA', 'CHIHUAHUA', 'CHIHUAHUA', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08ECB0004F', 'MATUTINO', 'COLEGIO DE BACHILLERES PLANTEL 4', 'JOSE ANGEL VIZCAINO PEREZ Y PINO', NULL, NULL),
(240, 'CHIHUAHUA', 'CHIHUAHUA', 'CHIHUAHUA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH0242H', 'MATUTINO', 'BACHILLERATO CULTURAL VALLARTA', 'AVENIDA VALLARTA NUM. 5900', NULL, NULL),
(241, 'CHIHUAHUA', 'CASAS GRANDES', 'CASAS GRANDES', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3700Y', 'NOCTURNO', 'PREPARATORIA BILINGUE LINCOLN', 'AVENIDA ADOLFO LOPEZ MATEOS NUM. 100', NULL, NULL),
(242, 'CHIHUAHUA', 'SAUCILLO', 'SAUCILLO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08SBC2132R', 'VESPERTINO', 'ESCUELA PREPARATORIA FEDERAL POR COOPERACION OCTAVIO PAZ', 'INSURGENTES S/N', NULL, NULL),
(243, 'CHIHUAHUA', 'DELICIAS', 'DELICIAS', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08DBP0002M', 'MATUTINO', 'BACHILLERATO PEDAGOGICO', 'GUADALUPE Y VIRGINIA', NULL, NULL),
(244, 'CHIHUAHUA', 'DELICIAS', 'DELICIAS', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08DBP0002M', 'VESPERTINO', 'BACHILLERATO PEDAGOGICO', 'GUADALUPE Y VIRGINIA', NULL, NULL),
(245, 'CHIHUAHUA', 'DELICIAS', 'DELICIAS', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08SBC2024J', 'MATUTINO', 'ACTIVO 20-30 ALBERT EINSTEIN', 'AVENIDA CARLOS BLAKE NUM. 2100 Y 21 NORTE', NULL, NULL),
(246, 'CHIHUAHUA', 'DELICIAS', 'DELICIAS', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '08SBC2024J', 'VESPERTINO', 'ACTIVO 20-30 ALBERT EINSTEIN', 'AVENIDA CARLOS BLAKE NUM. 2100 Y 21 NORTE', NULL, NULL),
(247, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3688T', 'MATUTINO', 'PREPARATORIA MEXICO LIBRE UACJ', 'DE LA ROCA NUM. 4007', NULL, NULL),
(248, 'CHIHUAHUA', 'CAMARGO', 'SANTA ROSALIA DE CAMARGO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3644W', 'CONTINUO (TIEMPO COM', 'UNIVERSIDAD TEC MILENIO CAMARGO', 'AVENIDA JUAREZ ESQUINA FRANCISCO SARABIA NUM. 912', NULL, NULL),
(249, 'CHIHUAHUA', 'HIDALGO DEL PARRAL', 'HIDALGO DEL PARRAL', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3610F', 'CONTINUO (TIEMPO COM', 'UNIVERSIDAD TEC MILENIO PARRAL', 'IGNACIO PAEZ MORENO S/N', NULL, NULL),
(250, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3701X', 'MATUTINO', 'PREPARATORIA DE LA URN DE CIUDAD JUAREZ', 'CAMINO VIEJO A SAN JOSE NUM. 10051', NULL, NULL),
(251, 'CHIHUAHUA', 'JUAREZ', 'JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '08PBH3672S', 'MATUTINO', 'ESTUDIOS CORPORATIVOS INTEGRALES S.C. UACJ', 'SANTOS DEGOLLADO NUM. 120', NULL, NULL),
(252, 'DISTRITO FEDERAL', 'COYOACAN', 'COYOACAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09DAL0004O', 'DISCONTINUO', 'ACADEMIA DE LA DANZA MEXICANA', 'PROLONGACION XICOTENCATL NUM. 24', NULL, NULL),
(253, 'DISTRITO FEDERAL', 'COYOACAN', 'COYOACAN', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3075U', 'MATUTINO', 'ACADEMIA MODERNA', 'AMERICA NUM. 184', NULL, NULL),
(254, 'DISTRITO FEDERAL', 'BENITO JUAREZ', 'BENITO JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3069J', 'MATUTINO', 'AMADO NERVO', 'AVENIDA 3 NUM. 61', NULL, NULL),
(255, 'DISTRITO FEDERAL', 'IZTAPALAPA', 'IZTAPALAPA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PCB0014R', 'MATUTINO', 'ANGEL DE CAMPO', 'CHIHUAHUA NUM 44', NULL, NULL),
(256, 'DISTRITO FEDERAL', 'ALVARO OBREGON', 'ALVARO OBREGON', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0124S', 'MATUTINO', 'BACHILLERATO ALEXANDER BAIN, S.C.', 'LAS FLORES NUM. 497', NULL, NULL),
(257, 'DISTRITO FEDERAL', 'CUAUHTEMOC', 'CUAUHTEMOC', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3509Q', 'VESPERTINO', 'BACHILLERATO BASILIO RUEDA', 'MERIDA NUM. 50', NULL, NULL),
(258, 'DISTRITO FEDERAL', 'AZCAPOTZALCO', 'AZCAPOTZALCO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3497B', 'MATUTINO', 'BACHILLERATO BAUTISTA DAVID LIVINGSTONE', 'AVENIDA AZCAPOTZALCO NUM. 183', NULL, NULL),
(259, 'DISTRITO FEDERAL', 'TLALPAN', 'TLALPAN', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0059I', 'MATUTINO', 'BACHILLERATO C.C.H. COLEGIO MADRID, A.C.', 'PUENTE NUM. 224', NULL, NULL),
(260, 'DISTRITO FEDERAL', 'CUAJIMALPA DE MORELOS', 'CUAJIMALPA DE MORELOS', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3460O', 'MATUTINO', 'BACHILLERATO CENTRO EDUCATIVO EMMANUEL MOUNIER', 'AVENIDA JESUS DEL MONTE NUM. 101', NULL, NULL),
(261, 'DISTRITO FEDERAL', 'BENITO JUAREZ', 'BENITO JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0265R', 'MATUTINO', 'BACHILLERATO COLEGIO MARTINAK', 'DR. JOSE MARIA VERTIZ NUM. 936', NULL, NULL),
(262, 'DISTRITO FEDERAL', 'IZTAPALAPA', 'IZTAPALAPA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3546U', 'MATUTINO', 'BACHILLERATO COLEGIO RENE DESCARTES', 'VIVEROS NUM. 33', NULL, NULL),
(263, 'DISTRITO FEDERAL', 'IZTAPALAPA', 'IZTAPALAPA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3525H', 'DISCONTINUO', 'BACHILLERATO DE LA UNIVERSIDAD DE LA REPUBLICA MEXICANA', 'AVENIDA TLAHUAC NUM. 4761', NULL, NULL),
(264, 'DISTRITO FEDERAL', 'CUAUHTEMOC', 'CUAUHTEMOC', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3175T', 'MATUTINO', 'BACHILLERATO DE LA UNIVERSIDAD DEL VALLE DE MEXICO PLANTEL ROMA', 'MERIDA NUM. 33', NULL, NULL),
(265, 'DISTRITO FEDERAL', 'TLALPAN', 'TLALPAN', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3173V', 'MATUTINO', 'BACHILLERATO DE LA UNIVERSIDAD DEL VALLE DE MEXICO, PLANTEL TLALPAN', 'SAN JUAN DE DIOS NUM. 6', NULL, NULL),
(266, 'DISTRITO FEDERAL', 'CUAUHTEMOC', 'CUAUHTEMOC', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3179P', 'MATUTINO', 'BACHILLERATO DE LA UNIVERSIDAD TECNOLOGICA AMERICANA, S.C.', 'VIADUCTO PRESIDENTE MIGUEL ALEMAN NUM. 255', NULL, NULL),
(267, 'DISTRITO FEDERAL', 'TLALPAN', 'TLALPAN', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3528E', 'MATUTINO', 'BACHILLERATO GALILEO GALILEI', 'TENOSIQUE NUM. 379', NULL, NULL),
(268, 'DISTRITO FEDERAL', 'AZCAPOTZALCO', 'AZCAPOTZALCO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3516Z', 'VESPERTINO', 'BACHILLERATO GENERAL FRAY MATIAS DE CORDOVA', 'AVENIDA CENTENARIO NUM. 395-BIS', NULL, NULL),
(269, 'DISTRITO FEDERAL', 'GUSTAVO A. MADERO', 'GUSTAVO A. MADERO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3545V', 'MATUTINO', 'BACHILLERATO INGLES MAXWELL', 'SAN SEBASTIAN DE APARICIO NUM. 55', NULL, NULL),
(270, 'DISTRITO FEDERAL', 'ALVARO OBREGON', 'ALVARO OBREGON', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3463L', 'MATUTINO', 'BACHILLERATO ISABEL GRASSETEAU', 'ADRIAN BROWER NUM. 41', NULL, NULL),
(271, 'DISTRITO FEDERAL', 'CUAUHTEMOC', 'CUAUHTEMOC', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3494E', 'MATUTINO', 'BACHILLERATO ISABEL GRASSETEAU, PLANTEL SANTA MARIA', 'NARANJO NUM. 86', NULL, NULL);
INSERT INTO `preparatorias` (`id`, `estado`, `municipio`, `localidad`, `ambito`, `tipo`, `servicio`, `clave`, `turno`, `centro_educativo`, `direccion`, `created_at`, `updated_at`) VALUES
(272, 'DISTRITO FEDERAL', 'ALVARO OBREGON', 'ALVARO OBREGON', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3629C', 'MATUTINO', 'BACHILLERATO JESUS DE URQUIAGA', 'FRONTERA NUM. 40', NULL, NULL),
(273, 'DISTRITO FEDERAL', 'MIGUEL HIDALGO', 'MIGUEL HIDALGO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3418Z', 'MATUTINO', 'BACHILLERATO SARA ALARCON', 'LAGO ALBERTO NUM. 319', NULL, NULL),
(274, 'DISTRITO FEDERAL', 'GUSTAVO A. MADERO', 'GUSTAVO A. MADERO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09GBH0004Y', 'DISCONTINUO', 'BELISARIO DOMINGUEZ', 'AVENIDA LA CORONA NUM. 436 (DEPORTIVO CARMEN SERDAN)', NULL, NULL),
(275, 'DISTRITO FEDERAL', 'CUAUHTEMOC', 'CUAUHTEMOC', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3050L', 'MATUTINO', 'BERTHA VON GLUMER', 'PUEBLA NUM. 413-419', NULL, NULL),
(276, 'DISTRITO FEDERAL', 'MIGUEL HIDALGO', 'MIGUEL HIDALGO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09GBH0016C', 'DISCONTINUO', 'CARMEN SERDAN', 'LAGO XIMILPA NUM. 88', NULL, NULL),
(277, 'DISTRITO FEDERAL', 'MIGUEL HIDALGO', 'MIGUEL HIDALGO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09DBP0001M', 'MATUTINO', 'CEB NO. 1 \"MAESTRO MOISES SAENZ GARZA\"', 'AVENIDA MAESTRO RURAL NUM. 57-A', NULL, NULL),
(278, 'DISTRITO FEDERAL', 'MIGUEL HIDALGO', 'MIGUEL HIDALGO', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09DBP0001M', 'VESPERTINO', 'CEB NO. 1 \"MAESTRO MOISES SAENZ GARZA\"', 'AVENIDA MAESTRO RURAL NUM. 57-A', NULL, NULL),
(279, 'DISTRITO FEDERAL', 'ALVARO OBREGON', 'ALVARO OBREGON', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09DBP0002L', 'MATUTINO', 'CEB-4/2 LIC. JESUS REYES HEROLES', 'PROGRESO NUM. 23', NULL, NULL),
(280, 'DISTRITO FEDERAL', 'ALVARO OBREGON', 'ALVARO OBREGON', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09DBP0002L', 'VESPERTINO', 'CEB-4/2 LIC. JESUS REYES HEROLES', 'PROGRESO NUM. 23', NULL, NULL),
(281, 'DISTRITO FEDERAL', 'CUAUHTEMOC', 'CUAUHTEMOC', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09DAR0008E', 'MATUTINO', 'CEDART LUIS SPOTA SAAVEDRA', 'LONDRES NUM. 16, PISO 2', NULL, NULL),
(282, 'DISTRITO FEDERAL', 'IZTAPALAPA', 'IZTAPALAPA', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH8003I', 'MATUTINO', 'CENTRO CULTURAL ANAHUAC, S.C.', 'TRABAJADORES SOCIALES NUM. 223', NULL, NULL),
(283, 'DISTRITO FEDERAL', 'GUSTAVO A. MADERO', 'GUSTAVO A. MADERO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3227I', 'MATUTINO', 'CENTRO CULTURAL UNIVERSITARIO JUSTO SIERRA', 'AVENIDA ACUEDUCTO NUM. 914', NULL, NULL),
(284, 'DISTRITO FEDERAL', 'COYOACAN', 'COYOACAN', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09DAR0007F', 'MATUTINO', 'CENTRO DE EDUCACION ARTISTICA DIEGO RIVERA', 'CERRO DE LA ESTRELLA NUM. 120', NULL, NULL),
(285, 'DISTRITO FEDERAL', 'CUAUHTEMOC', 'CUAUHTEMOC', 'URBANA', 'PUBLICO', 'BACHILLERATO GENERAL', '09DAR0009D', 'MATUTINO', 'CENTRO DE EDUCACION ARTISTICA FRIDA KAHLO', 'PLAZA DE LA REPUBLICA NUM. 7', NULL, NULL),
(286, 'DISTRITO FEDERAL', 'TLALPAN', 'TLALPAN', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3238O', 'MATUTINO', 'CENTRO DE EDUCACION Y CULTURA AJUSCO (CEYCA)', 'FLOR SILVESTRE NUM. 30', NULL, NULL),
(287, 'DISTRITO FEDERAL', 'TLALPAN', 'TLALPAN', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3238O', 'VESPERTINO', 'CENTRO DE EDUCACION Y CULTURA AJUSCO (CEYCA)', 'FLOR SILVESTRE NUM. 30', NULL, NULL),
(288, 'DISTRITO FEDERAL', 'TLALPAN', 'TLALPAN', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3236Q', 'MATUTINO', 'CENTRO EDUCATIVO DEL SUR INSTITUTO GODWIN', 'CUITLAHUAC NUM. 15', NULL, NULL),
(289, 'DISTRITO FEDERAL', 'BENITO JUAREZ', 'BENITO JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0247B', 'MATUTINO', 'CENTRO EDUCATIVO JEAN PIAGET', 'RUBENS NUM. 38', NULL, NULL),
(290, 'DISTRITO FEDERAL', 'IZTACALCO', 'IZTACALCO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3694C', 'MATUTINO', 'CENTRO EDUCATIVO PLOTINO RHODAKANATY, BACHILLERATO', 'CALZADA DE LA VIGA NUM. 707', NULL, NULL),
(291, 'DISTRITO FEDERAL', 'CUAJIMALPA DE MORELOS', 'CUAJIMALPA DE MORELOS', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0276X', 'MATUTINO', 'CENTRO EDUCATIVO TOMAS MORO', 'MAGUEY NUM. 64', NULL, NULL),
(292, 'DISTRITO FEDERAL', 'GUSTAVO A. MADERO', 'GUSTAVO A. MADERO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH8064W', 'MATUTINO', 'CENTRO ESCOLAR ATOYAC', 'AVENIDA HENRY FORD NUM. 120', NULL, NULL),
(293, 'DISTRITO FEDERAL', 'GUSTAVO A. MADERO', 'GUSTAVO A. MADERO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3368H', 'MATUTINO', 'CENTRO ESCOLAR BENEMERITO DE LAS AMERICAS', 'CARRETERA TENAYUCA CHALMITA NUM. 828', NULL, NULL),
(294, 'DISTRITO FEDERAL', 'COYOACAN', 'COYOACAN', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0273Z', 'MATUTINO', 'CENTRO ESCOLAR HERMANOS REVUELTAS, S.C.', 'AVENIDA AZTECAS NUM. 142', NULL, NULL),
(295, 'DISTRITO FEDERAL', 'AZCAPOTZALCO', 'AZCAPOTZALCO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH3376Q', 'MATUTINO', 'CENTRO ESCOLAR LANCASTER A.C.', 'HACIENDA ESCOLASTICA NUM. 66 AMPLIACION PROVIDENCIA', NULL, NULL),
(296, 'DISTRITO FEDERAL', 'ALVARO OBREGON', 'ALVARO OBREGON', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0210O', 'MATUTINO', 'CENTRO UNIVERSITARIO ANGLO MEXICANO, S. C. (CUAM)', 'CALZADA DE LAS AGUILAS NUM. 350', NULL, NULL),
(297, 'DISTRITO FEDERAL', 'BENITO JUAREZ', 'BENITO JUAREZ', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0150Q', 'MATUTINO', 'CENTRO UNIVERSITARIO MEXICO, A.C.', 'NICOLAS SAN JUAN NUM. 728', NULL, NULL),
(298, 'DISTRITO FEDERAL', 'GUSTAVO A. MADERO', 'GUSTAVO A. MADERO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0091R', 'MATUTINO', 'CENTRO UNIVERSITARIO PATRIA', 'CARLOTA NUM. 68', NULL, NULL),
(299, 'DISTRITO FEDERAL', 'ALVARO OBREGON', 'ALVARO OBREGON', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0126Q', 'MATUTINO', 'CLAUDINA THEVENET', 'SUR 128 NUM. 15', NULL, NULL),
(300, 'DISTRITO FEDERAL', 'XOCHIMILCO', 'XOCHIMILCO', 'URBANA', 'PRIVADO', 'BACHILLERATO GENERAL', '09PBH0103F', 'MATUTINO', 'COLEGIO ALEMAN ALEXANDER VON HUMBOLDT', 'AVENIDA MEXICO NUM. 5501', NULL, NULL),
(301, 'MORELOS', 'Cuernavaca', 'Cuernavaca', 'URBANA', 'PUBLICO', 'BACHILLERATO TECNOLÓGICO', '21EBH0613X', 'MATUTINO', 'CBTis 198', 'Centro de Cuernavaca', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso_videos`
--

CREATE TABLE `progreso_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `estudiante_id` bigint(20) UNSIGNED NOT NULL,
  `video_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_visto` datetime DEFAULT NULL,
  `completado` tinyint(1) NOT NULL DEFAULT 0,
  `ultimo_segundo` varchar(20) DEFAULT NULL COMMENT 'Ej: 00:02:35',
  `veces_visto` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `progreso_videos`
--

INSERT INTO `progreso_videos` (`id`, `estudiante_id`, `video_id`, `fecha_visto`, `completado`, `ultimo_segundo`, `veces_visto`, `created_at`, `updated_at`) VALUES
(6, 2, 31, '2026-05-22 10:23:05', 0, '00:01:10', 1, NULL, NULL),
(7, 2, 6, '2026-05-22 16:52:31', 1, '00:01:40', 1, NULL, NULL),
(8, 4, 1, '2026-05-23 00:47:52', 0, '00:00:30', 1, NULL, NULL),
(9, 2, 1, '2026-06-02 16:29:21', 0, '00:00:10', 1, NULL, NULL),
(10, 1, 1, '2026-06-02 21:10:58', 0, '00:00:15', 1, NULL, NULL),
(11, 2, 7, '2026-06-02 21:14:05', 0, '00:00:10', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos_adicionales`
--

CREATE TABLE `recursos_adicionales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `materia_nombre` varchar(255) NOT NULL,
  `tema` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos_clase`
--

CREATE TABLE `recursos_clase` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_clase` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `tipo` enum('pdf','video_youtube','video_vimeo','video_drive','presentacion','documento','podcast','imagen','enlace','otros') NOT NULL DEFAULT 'otros',
  `url` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `recursos_clase`
--

INSERT INTO `recursos_clase` (`id`, `id_clase`, `titulo`, `tipo`, `url`, `descripcion`, `orden`, `created_at`, `updated_at`) VALUES
(1, 129, 'Libro de aritmética', 'pdf', 'https://docs.google.com/file/d/0B8x71XDp4ZBfaXZ6c1ctNjFqZmc/edit?resourcekey=0-zCgDhb1MFBaJlxeYzFWLgw', 'Libro de aritmética de Baldor', 2, '2026-06-03 23:04:37', '2026-06-04 16:20:00'),
(2, 129, 'Video de muestra', 'video_youtube', 'https://www.youtube.com/watch?v=ASL5qnZUUdY', 'Video de muestra 1', 3, '2026-06-03 23:04:37', '2026-06-04 16:20:00'),
(3, 1, 'Guía de estudio', 'pdf', 'https://drive.google.com/file/d/1EXhNbWgLhF21BavnfM1nicrzWL_sIPrC/view', 'Prueba de la guía de estudio :P', 0, '2026-06-04 16:56:11', '2026-06-04 16:56:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiempo_estudio`
--

CREATE TABLE `tiempo_estudio` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `estudiante_id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `segundos_estudiados` int(11) NOT NULL DEFAULT 0,
  `minutos_estudiados` int(11) NOT NULL DEFAULT 0,
  `horas_estudiadas` int(11) NOT NULL DEFAULT 0,
  `sesiones` int(11) NOT NULL DEFAULT 0,
  `ultima_actividad` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tiempo_estudio`
--

INSERT INTO `tiempo_estudio` (`id`, `estudiante_id`, `fecha`, `segundos_estudiados`, `minutos_estudiados`, `horas_estudiadas`, `sesiones`, `ultima_actividad`, `created_at`, `updated_at`) VALUES
(1, 2, '2026-05-21', 18120, 302, 5, 3, '2026-05-22 05:59:19', '2026-05-22 03:18:15', '2026-05-22 05:59:19'),
(2, 2, '2026-05-22', 105600, 1760, 29, 9, '2026-05-22 23:27:19', '2026-05-22 06:00:47', '2026-05-22 23:27:19'),
(3, 1, '2026-05-22', 1560, 26, 0, 4, '2026-05-23 00:02:14', '2026-05-22 22:51:41', '2026-05-23 00:02:14'),
(4, 3, '2026-05-22', 3480, 58, 0, 2, '2026-05-23 01:40:36', '2026-05-23 00:15:57', '2026-05-23 01:40:36'),
(5, 2, '2026-05-25', 240, 4, 0, 1, '2026-05-25 21:36:52', '2026-05-25 21:35:51', '2026-05-25 21:36:52'),
(6, 2, '2026-05-27', 780, 13, 0, 1, '2026-05-27 18:48:15', '2026-05-27 18:41:33', '2026-05-27 18:48:15'),
(7, 1, '2026-05-27', 480, 8, 0, 3, '2026-05-27 20:14:04', '2026-05-27 18:50:34', '2026-05-27 20:14:04'),
(8, 2, '2026-05-30', 2100, 35, 0, 1, '2026-05-30 19:08:37', '2026-05-30 18:34:35', '2026-05-30 19:08:37'),
(9, 2, '2026-06-01', 10020, 167, 2, 2, '2026-06-01 20:31:25', '2026-06-01 17:15:25', '2026-06-01 20:31:25'),
(10, 2, '2026-06-02', 15420, 257, 4, 3, '2026-06-03 03:36:20', '2026-06-02 18:39:35', '2026-06-03 03:36:20'),
(11, 1, '2026-06-02', 4800, 80, 1, 3, '2026-06-03 03:31:06', '2026-06-02 23:38:49', '2026-06-03 03:31:06'),
(12, 2, '2026-06-03', 2220, 37, 0, 2, '2026-06-03 18:51:46', '2026-06-03 18:00:23', '2026-06-03 18:51:46'),
(13, 2, '2026-06-04', 3000, 50, 0, 1, '2026-06-04 17:30:17', '2026-06-04 16:37:53', '2026-06-04 17:30:17'),
(14, 5, '2026-06-04', 8520, 142, 2, 4, '2026-06-05 05:53:03', '2026-06-05 00:35:07', '2026-06-05 05:53:03'),
(15, 5, '2026-06-05', 120, 2, 0, 1, '2026-06-05 06:01:26', '2026-06-05 06:00:26', '2026-06-05 06:01:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tronco`
--

CREATE TABLE `tronco` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tronco`
--

INSERT INTO `tronco` (`id`, `nombre`, `created_at`, `updated_at`) VALUES
(1, 'CIENCIAS AGROPECUARIAS', NULL, NULL),
(2, 'CIENCIAS DE LA SALUD', NULL, NULL),
(3, 'CIENCIAS E INGENIERIAS', NULL, NULL),
(4, 'CIENCIAS NATURALES', NULL, NULL),
(5, 'CIENCIAS SOCIALES', NULL, NULL),
(6, 'HUMANIDADES Y ARTES', NULL, NULL),
(7, 'PSICOLOGIA Y CIENCIAS DE LA EDUCACIÓN', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `universidades`
--

CREATE TABLE `universidades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `estado` varchar(100) NOT NULL,
  `municipio` varchar(100) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `carrera_id` bigint(20) UNSIGNED NOT NULL,
  `duracion` varchar(255) NOT NULL,
  `tipo` varchar(255) NOT NULL DEFAULT 'Pública',
  `clave` varchar(50) NOT NULL,
  `direccion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `universidades`
--

INSERT INTO `universidades` (`id`, `estado`, `municipio`, `localidad`, `carrera_id`, `duracion`, `tipo`, `clave`, `direccion`, `created_at`, `updated_at`) VALUES
(1, 'DISTRITO FEDERAL', 'Gustavo A. Madero', 'Sátelite', 39, '3 AÑOS', 'PRIVADA', 'Universidad Privada', 'Sin dirección conocida', NULL, NULL),
(2, 'CHIHUAHUA', 'Cuernavaca', 'Cuernavaca', 40, '4 AÑOS', 'PUBLICA', 'BUAP', 'SIN DIRECCIÓN REGISTRADA', NULL, NULL),
(3, 'PUEBLA', 'Puebla', 'Puebla', 33, '5 AÑOS', 'AUTONOMA', 'UAEL', 'Del Rincon', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `correo` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `contraseña` varchar(255) DEFAULT NULL,
  `rol` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `correo`, `google_id`, `contraseña`, `rol`, `created_at`, `updated_at`) VALUES
(1, 'admin@sistema.com', NULL, '$2y$10$VG/hG7Y6PUbOBSFYqJih/ux/RR5jvi/A0ZeEaV2TQcuYW2s2e/02G', 'Administrador', NULL, NULL),
(2, 'prueba@prueba.com', NULL, '$2y$10$bSVI7OtnFIc7LXSL1Z0iB.pGNilq2gbZ3DRM6qbTKaqwJnIKEjPuW', 'estudiante', NULL, NULL),
(3, 'gvegaestrada@gmail.com', '113535884961217147444', '$2y$10$2aC9A5UZoRTiV4PnNllQM.DVOzzbM2q0i19ejZ6bQWoXxlCjhtUE.', 'estudiante', NULL, NULL),
(4, 'gus.vega.estrada@gmail.com', NULL, '$2y$10$bl.FQElwwTuUuTKYiGVTHe3vXYROZlpnnwM7qwjh3RtZaS8r2QnA6', 'estudiante', NULL, NULL),
(7, 'arlethv259@gmail.com', '102775356930872964261', '$2y$10$h81YB9yHu4crhXAUh3UyvuwE3WmHwA/8NQJFPuiS7.rkvmJ3TN7BC', 'estudiante', NULL, NULL),
(8, 'gusvegaestrada13@gmail.com', '112134513915968447919', '$2y$10$RHdhqjgAgU8p4zPSVvRM9.R1fLyvWaI26EGJEuKze8UwCR1uluyxC', 'estudiante', NULL, NULL),
(9, 'gv71061@gmail.com', '106982457571478224901', '$2y$10$Tp6Jmjy436.e5JjGM442wubqygYj8QFhN7.ExACKav9Oo0BpyvZ1q', 'estudiante', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos`
--

CREATE TABLE `videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `materia` varchar(255) NOT NULL,
  `tema` varchar(255) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `duracion` time NOT NULL,
  `plan` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Plan normal, 1=Plan premium',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `videos`
--

INSERT INTO `videos` (`id`, `materia`, `tema`, `titulo`, `link`, `duracion`, `plan`, `created_at`, `updated_at`) VALUES
(1, 'LENGUAJE ESCRITO', 'Sustantivos y adjetivos', 'Sustantivos y adjetivos', 'https://vimeo.com/356512619', '03:55:00', 0, NULL, NULL),
(2, 'LENGUAJE ESCRITO', 'Verbos y Adverbios', 'Verbos y adverbios', 'https://vimeo.com/356516521', '08:25:00', 1, NULL, NULL),
(3, 'LENGUAJE ESCRITO', 'Preposición', 'Preposición', 'https://vimeo.com/356523797', '09:27:00', 1, NULL, NULL),
(4, 'LENGUAJE ESCRITO', 'Sujeto y predicado', 'Sujeto y predicado', 'https://vimeo.com/356684560', '17:09:00', 1, NULL, NULL),
(5, 'LENGUAJE ESCRITO', 'Oración', 'Oraciones', 'https://vimeo.com/356691244', '09:27:00', 1, NULL, NULL),
(6, 'LENGUAJE ESCRITO', 'Pleonasmo', 'Pleonasmo', 'https://vimeo.com/356694756', '04:19:00', 1, NULL, NULL),
(7, 'LENGUAJE ESCRITO', 'Puntuación', 'Puntuaciones', 'https://vimeo.com/356696465', '03:55:00', 1, NULL, NULL),
(8, 'LENGUAJE ESCRITO', 'Secuencia lógica', 'Secuencia lógica, nexos y otros locuciones', 'https://vimeo.com/357879476', '20:09:00', 1, NULL, NULL),
(9, 'LENGUAJE ESCRITO', 'Heteronimo', 'Heteronimos', 'https://vimeo.com/360674475', '00:01:45', 1, NULL, NULL),
(10, 'ESTRUCTURA DE LA LENGUA', 'Verbos', 'Verbos', 'https://vimeo.com/357903613', '00:00:00', 1, NULL, NULL),
(11, 'ESTRUCTURA DE LA LENGUA', 'Reglas ortograficas puntuacion', 'Reglas ortograficas puntuacion', 'https://vimeo.com/357906397', '00:00:00', 1, NULL, NULL),
(12, 'ESTRUCTURA DE LA LENGUA', 'Reglas ortograficas acentuacion 1', 'Reglas ortograficas acentuacion 1', 'https://vimeo.com/357912078', '00:00:00', 1, NULL, NULL),
(13, 'ESTRUCTURA DE LA LENGUA', 'Reglas ortograficas acentuacion 2', 'Reglas ortograficas acentuacion 2', 'https://vimeo.com/357916224', '00:00:00', 1, NULL, NULL),
(14, 'ESTRUCTURA DE LA LENGUA', 'Reglas ortograficas acentuacion 3', 'Reglas ortograficas acentuacion 3', 'https://vimeo.com/357918314', '00:00:00', 1, NULL, NULL),
(15, 'ESTRUCTURA DE LA LENGUA', 'relaciones semanticas', 'relaciones semanticas', 'https://vimeo.com/357954862', '00:00:00', 1, NULL, NULL),
(16, 'ESTRUCTURA DE LA LENGUA', 'preposiciones y adverbios', 'preposiciones y adverbios', 'https://vimeo.com/357919851', '00:00:00', 1, NULL, NULL),
(17, 'ESTRUCTURA DE LA LENGUA', 'lógica textual1', 'lógica textual1', 'https://vimeo.com/357879476', '00:00:00', 1, NULL, NULL),
(18, 'ESTRUCTURA DE LA LENGUA', 'lógica textual 2', 'lógica textual 2', 'https://vimeo.com/357875876', '00:00:00', 1, NULL, NULL),
(19, 'ESTRUCTURA DE LA LENGUA', 'grafias S- C -Z', 'grafias S- C -Z', 'https://vimeo.com/358084010', '00:00:00', 1, NULL, NULL),
(20, 'ESTRUCTURA DE LA LENGUA', 'grafias G Y J', 'grafias G Y J', 'https://vimeo.com/358091055', '00:00:00', 1, NULL, NULL),
(21, 'ESTRUCTURA DE LA LENGUA', 'B Y V', 'B Y V', 'https://vimeo.com/357893620', '00:00:00', 1, NULL, NULL),
(22, 'ESTRUCTURA DE LA LENGUA', 'R Y RR', 'R Y RR', 'https://vimeo.com/357892488', '00:00:00', 1, NULL, NULL),
(23, 'COMPRENSION LECTORA', 'introducción', 'introducción', 'https://vimeo.com/360944019', '00:00:00', 1, NULL, NULL),
(24, 'COMPRENSION LECTORA', 'primera parte', 'primera parte', 'https://vimeo.com/360944782', '00:00:00', 1, NULL, NULL),
(25, 'COMPRENSION LECTORA', 'segunda parte', 'segunda parte', 'https://vimeo.com/360948216', '00:00:00', 1, NULL, NULL),
(26, 'COMPRENSION LECTORA', 'tercera parte', 'tercera parte', 'https://vimeo.com/360948216', '00:00:00', 1, NULL, NULL),
(27, 'COMPRENSION LECTORA', 'cuarta parte', 'cuarta parte', 'https://vimeo.com/360942015', '00:00:00', 1, NULL, NULL),
(28, 'COMPRENSION LECTORA', 'quinta parte', 'quinta parte', 'https://vimeo.com/360946881', '00:00:00', 1, NULL, NULL),
(29, 'COMPRENSION LECTORA', 'sexta parte', 'sexta parte', 'https://vimeo.com/360951112', '00:00:00', 1, NULL, NULL),
(30, 'COMPRENSION LECTORA', 'septima parte', 'septima parte', 'https://vimeo.com/360950002', '00:00:00', 1, NULL, NULL),
(31, 'INGLES', 'wh question', 'wh question', 'https://vimeo.com/383565235', '00:02:00', 1, NULL, NULL),
(32, 'INGLES', 'simple present', 'simple present', 'https://vimeo.com/383566546', '00:00:00', 1, NULL, NULL),
(33, 'INGLES', 'Ver to be', 'Ver to be', 'https://vimeo.com/383567877', '00:00:00', 1, NULL, NULL),
(34, 'INGLES', 'expresiones comunes', 'expresiones comunes', 'https://vimeo.com/383568777', '00:00:00', 1, NULL, NULL),
(35, 'INGLES', 'obligaciones', 'obligaciones', 'https://vimeo.com/383571152', '00:00:00', 1, NULL, NULL),
(36, 'INGLES', 'comparativos y superlativos', 'comparativos y superlativos', 'https://vimeo.com/383573853', '00:00:00', 1, NULL, NULL),
(37, 'INGLES', 'restaurante', 'restaurante', 'https://vimeo.com/383575547', '00:00:00', 1, NULL, NULL),
(38, 'INGLES', 'permisos', 'permisos', 'https://vimeo.com/383576658', '00:00:00', 1, NULL, NULL),
(39, 'INGLES', 'futuro', 'futuro', 'https://vimeo.com/383577563', '00:00:00', 1, NULL, NULL),
(40, 'INGLES', 'Lecturas', 'Lecturas', 'https://vimeo.com/383582696', '00:00:00', 1, NULL, NULL),
(41, 'INGLES', 'Lecturas segunda parte', 'Lecturas segunda parte', 'https://vimeo.com/383585281', '00:00:00', 1, NULL, NULL),
(42, 'INGLES', 'Información personal', 'Información personal', 'https://vimeo.com/383588761', '00:00:00', 1, NULL, NULL),
(43, 'INGLES', 'used to', 'used to', 'https://vimeo.com/383589865', '00:00:00', 1, NULL, NULL),
(44, 'INGLES', 'tiempos verbales', 'tiempos verbales', 'https://vimeo.com/383591378', '00:00:00', 1, NULL, NULL),
(45, 'PENSAMIENTO ANALITICO', 'introducción', 'introducción', 'https://vimeo.com/362617177', '00:00:00', 1, NULL, NULL),
(46, 'PENSAMIENTO ANALITICO', 'Fundamentos del pensamiento analítico', 'Fundamentos del pensamiento analítico', 'https://vimeo.com/362619510', '00:00:00', 1, NULL, NULL),
(47, 'PENSAMIENTO ANALITICO', 'Información textual', 'Información textual', 'https://vimeo.com/362620103', '00:00:00', 1, NULL, NULL),
(48, 'PENSAMIENTO ANALITICO', 'Representaciones gráficas', 'Representaciones gráficas', 'https://vimeo.com/362620548', '00:00:00', 1, NULL, NULL),
(49, 'PENSAMIENTO ANALITICO', 'Relaciones analogicas', 'Relaciones analogicas', 'https://vimeo.com/362620937', '00:00:00', 1, NULL, NULL),
(50, 'PENSAMIENTO MATEMATICO', 'Jerarquia de operaciones', 'Jerarquia de operaciones', 'https://vimeo.com/356291521', '00:00:00', 1, NULL, NULL),
(51, 'PENSAMIENTO MATEMATICO', 'Operaciones elementales', 'Operaciones elementales', 'https://vimeo.com/356298031', '00:00:00', 1, NULL, NULL),
(52, 'PENSAMIENTO MATEMATICO', 'Razones y proporcionalidad', 'Razones y proporcionalidad', 'https://vimeo.com/356300127', '00:00:00', 1, NULL, NULL),
(53, 'PENSAMIENTO MATEMATICO', 'Expresiones algebraicas', 'Expresiones algebraicas', 'https://vimeo.com/356302079', '00:00:00', 1, NULL, NULL),
(54, 'PENSAMIENTO MATEMATICO', 'Productos notables', 'Productos notables', 'https://vimeo.com/356303421', '00:00:00', 1, NULL, NULL),
(55, 'PENSAMIENTO MATEMATICO', 'Factorización', 'Factorización', 'https://vimeo.com/356304681', '00:00:00', 1, NULL, NULL),
(56, 'PENSAMIENTO MATEMATICO', 'Ecuaciones lineales', 'Ecuaciones lineales', 'https://vimeo.com/356308154', '00:00:00', 1, NULL, NULL),
(57, 'PENSAMIENTO MATEMATICO', 'Ecuaciones cuadraticas', 'Ecuaciones cuadraticas', 'https://vimeo.com/356309399', '00:00:00', 1, NULL, NULL),
(58, 'PENSAMIENTO MATEMATICO', 'Sistema de ecuaciones', 'Sistema de ecuaciones', 'https://vimeo.com/356310919', '00:00:00', 1, NULL, NULL),
(59, 'PENSAMIENTO MATEMATICO', 'Medidas de tendencia central', 'Medidas de tendencia central', 'https://vimeo.com/356312480', '00:00:00', 1, NULL, NULL),
(60, 'PENSAMIENTO MATEMATICO', 'Medidas de posisición', 'Medidas de posisición', 'https://vimeo.com/356314301', '00:00:00', 1, NULL, NULL),
(61, 'PENSAMIENTO MATEMATICO', 'Probabilidad', 'Probabilidad', 'https://vimeo.com/356315500', '00:00:00', 1, NULL, NULL),
(62, 'PENSAMIENTO MATEMATICO', 'Línea recta', 'Línea recta', 'https://vimeo.com/356317487', '00:00:00', 1, NULL, NULL),
(63, 'PENSAMIENTO MATEMATICO', 'Razonamiento geométrico', 'Razonamiento geométrico', 'https://vimeo.com/356503500', '00:00:00', 1, NULL, NULL),
(64, 'PENSAMIENTO MATEMATICO', 'Resolución de triángulos', 'Resolución de triángulos', 'https://vimeo.com/356507308', '12:43:00', 0, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `administradores_usuario_id_index` (`usuario_id`);

--
-- Indices de la tabla `apoyo_preguntas`
--
ALTER TABLE `apoyo_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `apoyo_preguntas_examen_pregunta_unique` (`examen`,`pregunta`),
  ADD KEY `apoyo_preguntas_pregunta_foreign` (`pregunta`);

--
-- Indices de la tabla `area_preguntas`
--
ALTER TABLE `area_preguntas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignatura`
--
ALTER TABLE `asignatura`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carreras_tronco_id_foreign` (`tronco_id`),
  ADD KEY `carreras_id_asignatura_1_foreign` (`id_asignatura_1`),
  ADD KEY `carreras_id_asignatura_2_foreign` (`id_asignatura_2`),
  ADD KEY `carreras_id_asignatura_3_foreign` (`id_asignatura_3`);

--
-- Indices de la tabla `clases`
--
ALTER TABLE `clases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clases_id_asignatura_num_clase_unique` (`id_asignatura`,`num_clase`);

--
-- Indices de la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cupones_codigo_unique` (`codigo`),
  ADD KEY `cupones_usuario_uso_foreign` (`usuario_uso`),
  ADD KEY `cupones_usuario_genero_foreign` (`usuario_genero`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `estudiante_usuario_unique` (`usuario`),
  ADD KEY `estudiante_escuela_procedencia_foreign` (`escuela_procedencia`),
  ADD KEY `estudiante_universidad_interes_foreign` (`universidad_interes`);

--
-- Indices de la tabla `examen_generado`
--
ALTER TABLE `examen_generado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `examen_realizado`
--
ALTER TABLE `examen_realizado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `examen_realizado_estudiante_examen_intento_unique` (`estudiante`,`examen`,`intento`),
  ADD KEY `examen_realizado_examen_foreign` (`examen`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `interacciones_call_center`
--
ALTER TABLE `interacciones_call_center`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha_estado` (`fecha_contacto`,`estado_seguimiento`),
  ADD KEY `idx_estudiante` (`id_estudiante`),
  ADD KEY `idx_contacta` (`id_usuario_contacta`),
  ADD KEY `idx_fecha` (`fecha_contacto`),
  ADD KEY `idx_estado` (`estado_seguimiento`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pagos_referencia_pago_unique` (`referencia_pago`),
  ADD KEY `pagos_alumno_pago_foreign` (`alumno_pago`),
  ADD KEY `pagos_usuario_revision_foreign` (`usuario_revision`),
  ADD KEY `pagos_estatus_index` (`estatus`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `preguntas_id_area_foreign` (`id_area`);

--
-- Indices de la tabla `preparatorias`
--
ALTER TABLE `preparatorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `progreso_videos`
--
ALTER TABLE `progreso_videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `progreso_videos_estudiante_id_video_id_unique` (`estudiante_id`,`video_id`),
  ADD KEY `progreso_videos_video_id_foreign` (`video_id`),
  ADD KEY `progreso_videos_completado_index` (`completado`);

--
-- Indices de la tabla `recursos_adicionales`
--
ALTER TABLE `recursos_adicionales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `recursos_clase`
--
ALTER TABLE `recursos_clase`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recursos_clase_id_clase_index` (`id_clase`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `tiempo_estudio`
--
ALTER TABLE `tiempo_estudio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tiempo_estudio_estudiante_id_fecha_unique` (`estudiante_id`,`fecha`),
  ADD KEY `tiempo_estudio_estudiante_id_fecha_index` (`estudiante_id`,`fecha`),
  ADD KEY `tiempo_estudio_fecha_index` (`fecha`);

--
-- Indices de la tabla `tronco`
--
ALTER TABLE `tronco`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `universidades`
--
ALTER TABLE `universidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `universidades_clave_unique` (`clave`),
  ADD KEY `universidades_carrera_id_foreign` (`carrera_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_correo_unique` (`correo`),
  ADD UNIQUE KEY `usuario_google_id_unique` (`google_id`);

--
-- Indices de la tabla `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `videos_materia_tema_index` (`materia`,`tema`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `apoyo_preguntas`
--
ALTER TABLE `apoyo_preguntas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=383;

--
-- AUTO_INCREMENT de la tabla `area_preguntas`
--
ALTER TABLE `area_preguntas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `asignatura`
--
ALTER TABLE `asignatura`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `clases`
--
ALTER TABLE `clases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT de la tabla `cupones`
--
ALTER TABLE `cupones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `examen_generado`
--
ALTER TABLE `examen_generado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `examen_realizado`
--
ALTER TABLE `examen_realizado`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `interacciones_call_center`
--
ALTER TABLE `interacciones_call_center`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `preparatorias`
--
ALTER TABLE `preparatorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;

--
-- AUTO_INCREMENT de la tabla `progreso_videos`
--
ALTER TABLE `progreso_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `recursos_adicionales`
--
ALTER TABLE `recursos_adicionales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recursos_clase`
--
ALTER TABLE `recursos_clase`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tiempo_estudio`
--
ALTER TABLE `tiempo_estudio`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tronco`
--
ALTER TABLE `tronco`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `universidades`
--
ALTER TABLE `universidades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `videos`
--
ALTER TABLE `videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD CONSTRAINT `administradores_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `apoyo_preguntas`
--
ALTER TABLE `apoyo_preguntas`
  ADD CONSTRAINT `apoyo_preguntas_examen_foreign` FOREIGN KEY (`examen`) REFERENCES `examen_generado` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `apoyo_preguntas_pregunta_foreign` FOREIGN KEY (`pregunta`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD CONSTRAINT `carreras_id_asignatura_1_foreign` FOREIGN KEY (`id_asignatura_1`) REFERENCES `asignatura` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carreras_id_asignatura_2_foreign` FOREIGN KEY (`id_asignatura_2`) REFERENCES `asignatura` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `carreras_id_asignatura_3_foreign` FOREIGN KEY (`id_asignatura_3`) REFERENCES `asignatura` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `carreras_tronco_id_foreign` FOREIGN KEY (`tronco_id`) REFERENCES `tronco` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clases`
--
ALTER TABLE `clases`
  ADD CONSTRAINT `clases_id_asignatura_foreign` FOREIGN KEY (`id_asignatura`) REFERENCES `asignatura` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cupones`
--
ALTER TABLE `cupones`
  ADD CONSTRAINT `cupones_usuario_genero_foreign` FOREIGN KEY (`usuario_genero`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cupones_usuario_uso_foreign` FOREIGN KEY (`usuario_uso`) REFERENCES `usuario` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD CONSTRAINT `estudiante_escuela_procedencia_foreign` FOREIGN KEY (`escuela_procedencia`) REFERENCES `preparatorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `estudiante_universidad_interes_foreign` FOREIGN KEY (`universidad_interes`) REFERENCES `universidades` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `estudiante_usuario_foreign` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `examen_realizado`
--
ALTER TABLE `examen_realizado`
  ADD CONSTRAINT `examen_realizado_estudiante_foreign` FOREIGN KEY (`estudiante`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `examen_realizado_examen_foreign` FOREIGN KEY (`examen`) REFERENCES `examen_generado` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `interacciones_call_center`
--
ALTER TABLE `interacciones_call_center`
  ADD CONSTRAINT `fk_contacta_usuario` FOREIGN KEY (`id_usuario_contacta`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_estudiante_usuario` FOREIGN KEY (`id_estudiante`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_alumno_pago_foreign` FOREIGN KEY (`alumno_pago`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagos_usuario_revision_foreign` FOREIGN KEY (`usuario_revision`) REFERENCES `usuario` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_id_area_foreign` FOREIGN KEY (`id_area`) REFERENCES `area_preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `progreso_videos`
--
ALTER TABLE `progreso_videos`
  ADD CONSTRAINT `progreso_videos_estudiante_id_foreign` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progreso_videos_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recursos_clase`
--
ALTER TABLE `recursos_clase`
  ADD CONSTRAINT `recursos_clase_id_clase_foreign` FOREIGN KEY (`id_clase`) REFERENCES `clases` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tiempo_estudio`
--
ALTER TABLE `tiempo_estudio`
  ADD CONSTRAINT `tiempo_estudio_estudiante_id_foreign` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `universidades`
--
ALTER TABLE `universidades`
  ADD CONSTRAINT `universidades_carrera_id_foreign` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

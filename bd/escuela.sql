-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-06-2017 a las 01:43:20
-- Versión del servidor: 10.1.21-MariaDB
-- Versión de PHP: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `escuela`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `id_alu` int(11) NOT NULL,
  `ci_alu` int(9) NOT NULL,
  `ci_alu2` varchar(1) NOT NULL,
  `nom_alu` varchar(50) NOT NULL,
  `grado` int(2) NOT NULL,
  `seccion` varchar(2) NOT NULL,
  `periodo` varchar(5) NOT NULL,
  `sexo` varchar(15) NOT NULL,
  `retirado` int(1) NOT NULL,
  `borrado` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ambito`
--

CREATE TABLE `ambito` (
  `id_ambito` int(11) NOT NULL,
  `cod_ambito` int(3) NOT NULL,
  `nom_ambito` text CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `borrado` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `ambito`
--

INSERT INTO `ambito` (`id_ambito`, `cod_ambito`, `nom_ambito`, `borrado`) VALUES
(1, 1, 'Participa sin conflictos en trabajos de equipo.', 0),
(2, 2, 'Su relaciÃ³n con los demÃ¡s es a travÃ©s del diÃ¡logo', 0),
(4, 4, 'Utiliza buen vocabulario', 0),
(5, 5, 'Se integra con su grupo de pares', 0),
(6, 6, 'Resuelve sus conflictos respetando el conducto regular', 0),
(7, 7, 'Respeta y cuida los bienes de uso comÃºn', 0),
(8, 8, 'Se dirige en forma respetuosa a la comunidad escolar', 0),
(9, 9, 'Manifiesta una actitud deferente y respetuosa con sus pares y la comunidad.', 0),
(10, 10, 'Demuestra respeto en los actos cÃ­vicos y en la entonaciÃ³n del himno nacional', 0),
(11, 11, 'Acata las normas disciplinarias y de convivencia escolar', 0),
(12, 3, 'Se recrea de acuerdo a su edad', 0),
(13, 12, 'ActÃºa con responsabilidad en las actividades que se compromete', 0),
(14, 13, 'Asiste con puntualidad al inicio de la jornada escolar', 0),
(15, 14, 'Participa en las actividades programadas por el colegio', 0),
(16, 15, 'Cumple con sus trabajos y tareas', 0),
(17, 16, 'Mantiene al dÃ­a sus materias', 0),
(18, 17, 'Desarrolla el trabajo escolar en forma sistemÃ¡tica y continua', 0),
(19, 18, 'Consulta y busca soluciones frente a las dificultades pedagÃ³gicas', 0),
(20, 19, 'Desarrolla sus evaluaciones en forma honesta', 0),
(21, 20, 'Permite el buen desarrollo de la clase', 0),
(22, 21, 'Manifiesta preocupaciÃ³n y solidaridad con los demÃ¡s', 0),
(23, 22, 'Reconoce sus errores y trata de superarlos', 0),
(24, 23, 'Maneja sus emociones de acuerdo a su edad', 0),
(25, 24, 'Cuida de su higiene personal', 0),
(26, 25, 'Justifica eventualidades de no cumplimiento con su uniforme escolar', 0),
(27, 26, 'Cuida de su presentaciÃ³n personal', 0),
(28, 27, 'Se presenta con su cotona y/o delantal', 0),
(29, 28, 'Utiliza corte y/o peinado solicitado en el reglamento interno del establecimiento', 0),
(30, 29, 'Asiste a entrevistas convocadas por el establecimiento', 0),
(31, 30, 'Asiste a reuniones de apoderados', 0),
(32, 31, 'Cumple con las solicitudes del establecimiento (informes, especialistas,etc.)', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignatura`
--

CREATE TABLE `asignatura` (
  `id_asig` int(11) NOT NULL,
  `cod_asig` float NOT NULL,
  `nom_asig` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `g1` varchar(2) NOT NULL,
  `g2` varchar(2) NOT NULL,
  `g3` varchar(2) NOT NULL,
  `g4` varchar(2) NOT NULL,
  `g5` varchar(2) NOT NULL,
  `g6` varchar(2) NOT NULL,
  `g7` varchar(2) NOT NULL,
  `g8` varchar(2) NOT NULL,
  `borrado` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `asignatura`
--

INSERT INTO `asignatura` (`id_asig`, `cod_asig`, `nom_asig`, `g1`, `g2`, `g3`, `g4`, `g5`, `g6`, `g7`, `g8`, `borrado`) VALUES
(1, 10, 'LENGUAJE Y COMUNICACIÃ“N', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', '', '', 0),
(2, 20, 'MATEMÃTICA', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 0),
(3, 30, 'HISTORIA, GEOGRAFÃA Y CIENCIAS SOCIALES', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', '', '', 0),
(5, 50, 'ARTES VISUALES', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', '', '', 0),
(6, 60, 'MÃšSICA', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', '', '', 0),
(7, 70, 'EDUCACIÃ“N FÃSICA Y SALUD', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 0),
(10, 40, 'CIENCIAS NATURALES', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 0),
(12, 90, 'TECNOLOGÃA', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', '', '', 0),
(13, 100, 'RELIGIÃ“N', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 'SI', 0),
(14, 11, 'INGLÃ‰S', '', '', 'SI', 'SI', 'SI', 'SI', '', '', 0),
(15, 12, 'LENGUA Y LITERATURA', '', '', '', '', '', '', 'SI', 'SI', 0),
(16, 13, 'HISTORIA, GEOGRAFÃA Y CIENCIAS SOCIALES', '', '', '', '', '', '', 'SI', 'SI', 0),
(17, 14, 'IDIOMA EXTRANJERO INGLÃ‰S', '', '', '', '', '', '', 'SI', 'SI', 0),
(18, 51, 'EDUCACIÃ“N TECNOLÃ“GICA', '', '', '', '', '', '', 'SI', 'SI', 0),
(19, 61, 'EDUCACIÃ“N ARTÃSTICA', '', '', '', '', '', '', 'SI', 'SI', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_aud` int(11) NOT NULL,
  `ci_prof` int(9) NOT NULL,
  `ci_prof2` int(11) NOT NULL,
  `cod_reg` varchar(50) NOT NULL,
  `desc_reg` blob,
  `registro` varchar(50) NOT NULL,
  `fecha_reg` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso`
--

CREATE TABLE `curso` (
  `id_curso` int(11) NOT NULL,
  `cod_curso` varchar(11) NOT NULL,
  `ci_prof2` varchar(3) NOT NULL,
  `ci_prof` int(10) NOT NULL,
  `grado` int(2) NOT NULL,
  `sec` varchar(2) NOT NULL,
  `periodo` varchar(5) NOT NULL,
  `n_alu_curso` int(4) NOT NULL,
  `activo` int(2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imp`
--

CREATE TABLE `imp` (
  `id` int(11) NOT NULL,
  `ci_alu` int(9) NOT NULL,
  `ci_alu2` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `imp`
--

INSERT INTO `imp` (`id`, `ci_alu`, `ci_alu2`) VALUES
(1, 21111111, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id_notas` int(11) NOT NULL,
  `ci_alu` int(8) NOT NULL,
  `ci_alu2` varchar(2) NOT NULL,
  `cod_asig` int(2) NOT NULL,
  `cod_curso` varchar(8) NOT NULL,
  `periodo` varchar(8) NOT NULL,
  `semestre` int(1) NOT NULL,
  `nota_prom` float NOT NULL,
  `prom_final` float NOT NULL,
  `prom_sem` float NOT NULL,
  `prom_gen` float NOT NULL,
  `nota` float NOT NULL,
  `anot_n` int(3) NOT NULL,
  `anot_p` int(3) NOT NULL,
  `porc_asist` int(3) NOT NULL,
  `obs` text NOT NULL,
  `fecha` date NOT NULL,
  `final` int(1) NOT NULL,
  `semestral` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perso`
--

CREATE TABLE `perso` (
  `id_perso` int(11) NOT NULL,
  `ci_alu` int(9) NOT NULL,
  `ci_alu2` varchar(2) NOT NULL,
  `grado` int(2) NOT NULL,
  `seccion` varchar(2) NOT NULL,
  `periodo` int(5) NOT NULL,
  `cod_ambito` int(3) NOT NULL,
  `semestre` int(2) NOT NULL,
  `lit` varchar(5) NOT NULL,
  `fecha` date NOT NULL,
  `final` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prof`
--

CREATE TABLE `prof` (
  `id_prof` int(11) NOT NULL,
  `ci_prof` int(9) NOT NULL,
  `ci_prof2` varchar(1) NOT NULL,
  `nom_prof` varchar(30) NOT NULL,
  `dir_prof` varchar(150) NOT NULL,
  `tlf_prof` varchar(50) NOT NULL,
  `retirado` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `clave` varchar(32) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `nom_prof` varchar(80) NOT NULL,
  `cedula` int(11) NOT NULL,
  `nivel` varchar(20) NOT NULL,
  `pregunta` varchar(150) NOT NULL,
  `respuesta` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `ci_usu2` varchar(2) NOT NULL,
  `activo` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `clave`, `usuario`, `nom_prof`, `cedula`, `nivel`, `pregunta`, `respuesta`, `email`, `status`, `ci_usu2`, `activo`) VALUES
(2, 'ea58543279c3e5a17ea35ee2ef726b8f', 'salimisaac', 'SALIM ABI HASSAN', 25649340, '1', 'CUAL ES TU COLOR PREFERIDO', 'VERDE', 'salimabihassan@hotmail.com', 'Desconectado', '3', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`id_alu`);

--
-- Indices de la tabla `ambito`
--
ALTER TABLE `ambito`
  ADD PRIMARY KEY (`id_ambito`);

--
-- Indices de la tabla `asignatura`
--
ALTER TABLE `asignatura`
  ADD PRIMARY KEY (`id_asig`),
  ADD KEY `cod_asig` (`cod_asig`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_aud`);

--
-- Indices de la tabla `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id_curso`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Indices de la tabla `imp`
--
ALTER TABLE `imp`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id_notas`),
  ADD KEY `id_notas` (`id_notas`);

--
-- Indices de la tabla `perso`
--
ALTER TABLE `perso`
  ADD PRIMARY KEY (`id_perso`);

--
-- Indices de la tabla `prof`
--
ALTER TABLE `prof`
  ADD PRIMARY KEY (`id_prof`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alumno`
--
ALTER TABLE `alumno`
  MODIFY `id_alu` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `ambito`
--
ALTER TABLE `ambito`
  MODIFY `id_ambito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT de la tabla `asignatura`
--
ALTER TABLE `asignatura`
  MODIFY `id_asig` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_aud` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `curso`
--
ALTER TABLE `curso`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id_notas` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `perso`
--
ALTER TABLE `perso`
  MODIFY `id_perso` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `prof`
--
ALTER TABLE `prof`
  MODIFY `id_prof` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

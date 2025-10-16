<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\menu.php
header('Content-Type: text/html; charset=utf-8');
session_start();

// incluir validación de sesión y UTF-8
include __DIR__ . '/auth.php';

// luego incluir conexión a BD
include __DIR__ . '/conexiones.php';

// Salida UTF-8
header('Content-Type: text/html; charset=UTF-8');



$grado1 = $_SESSION["grado"] ?? '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es" dir="ltr">
<head>
<title>Menu Escuela Altas Cumbres</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="menu/menu_intranet.css" />
<style type="text/css">
body {
    background-image: url();
}
</style>
</head>
<body>
<table width="1458" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr>
    <td width="1450" height="360">
      <!--cintillo-->
      <div class="caja" align="center">
        <img src="imagenes/cintillo.png" width="1024" height="97"  />
      </div>
      <!--fin cintillo-->

      <!--Menu-->
      <div class="caja" align="center">
        <ul class="menu">
          <li><a class="inicio" href="menu.php">Inicio</a></li>
          <li>
            <a class="registrar">Registrar</a>
            <ul>
              <li><a class="documentos" href="registrar_Prof.php" target="cuerpo">Profesor &oacute; Usuario</a></li>
              <li><a class="documentos" href="registrar_curso.php" target="cuerpo">Curso</a></li>
              <li><a class="documentos" href="registrar_alumnos.php" target="cuerpo">Alumno</a></li>
              <li><a class="documentos" href="registrar_asignatura.php" target="cuerpo">Asignatura</a></li>
              <li><a class="documentos" href="registrar_director.php" target="cuerpo">Director</a></li>
              <li><a class="documentos" href="registrar_ambito.php" target="cuerpo">&Aacute;mbito</a></li>
            </ul>
          </li>
          <li>
            <a class="procesos">Procesos</a>
            <ul>
              <li><a class="documentos" href="registrar_notas1.php" target="cuerpo">Notas 1er Semestre</a></li>
              <li><a class="documentos" href="registrar_notas2.php" target="cuerpo">Notas 2do Semestre</a></li>
              <li><a class="documentos" href="registrar_inf_perso.php" target="cuerpo">Personalidad 1er Sem</a></li>
              <li><a class="documentos" href="registrar_inf_perso2.php" target="cuerpo">Personalidad 2do Sem</a></li>
              <li><a class="documentos" href="registrar_promocion.php" target="cuerpo">Promover Curso</a></li>
            </ul>
          </li>
          <li>
            <a class="ver">Consultas</a>
            <ul>
              <li><a class="documentos" href="registrar_notas1_anotaciones.php" target="cuerpo">Inf. Notas Semestral</a></li>
              <li><a class="documentos" href="registrar_inf_perso12.php" target="cuerpo">Inf. Personalidad</a></li>
              <li><a class="documentos" href="consulta_alumno_ci.php" target="cuerpo">Alumnos por C&eacute;dula</a></li>
              <li><a class="documentos" href="consulta_alumno_grado.php" target="cuerpo">Alumnos por Curso</a></li>
              <li><a class="documentos" href="consulta_asig_grado.php" target="cuerpo">Asignaturas por Curso</a></li>
              <li><a class="documentos" href="consulta_prof_ci.php" target="cuerpo">Profesor por C&eacute;dula</a></li>
              <li><a class="documentos" href="consulta_curso.php" target="cuerpo">Curso</a></li>
              <!-- Opciones deshabilitadas comentadas -->
            </ul>
          </li>
          <li>
            <a class="mantenimiento">Mantenimiento</a>
            <ul>
              <li><a class="documentos" href="eliminar_notas.php" target="cuerpo">Eliminar Notas</a></li>
              <li><a class="documentos" href="desbloquear_notas.php" target="cuerpo">Desbloquear Notas</a></li>
              <li><a class="documentos" href="desbloquear_perso.php" target="cuerpo">Desbloq. Personalidad</a></li>
              <li><a class="documentos" href="eliminar_perso.php" target="cuerpo">Eliminar Inf. Person.</a></li>
              <li><a class="documentos" href="Cambio_nivel.php" target="cuerpo">Nivel de Usuario</a></li>
              <li><a class="documentos" href="Cambio_status.php" target="cuerpo">Act. o Desact. Usuario</a></li>
              <li><a class="documentos" href="consulta_auditoria.php" target="cuerpo">Auditor&iacute;a</a></li>
              <li><a class="documentos" href="Manual.pdf" target="_new">Manual de Usuario</a></li>
              <li><a class="documentos" href="respaldo/index.php" target="_new">Respaldar BD</a></li>
              <li><a class="documentos" href="respaldo/estado_respaldos.php" target="cuerpo">Respaldos Autom&aacute;ticos</a></li>
            </ul>
          </li>
          <li>
            <a class="administrar">Papelera de Reciclaje</a>
            <ul>
              <li><a class="documentos" href="papelera_alumno.php" target="cuerpo">Alumno</a></li>
              <li><a class="documentos" href="papelera_PROF.php" target="cuerpo">Profesor</a></li>
              <li><a class="documentos" href="papelera_asig.php" target="cuerpo">Asignatura</a></li>
              <li><a class="documentos" href="papelera_ambito.php" target="cuerpo">Ámbito</a></li>
            </ul>
          </li>
          <li>
            <a class="cerrar_sesion" href="cerrar_sesion.php" target="_top">Cerrar Sesi&oacute;n</a>
          </li>
        </ul>
      </div>
      <!--fin Menu-->

      <!--cuerpo-->
      <div class="caja_cuerpo" align="center">
        <iframe src="inicio.php" name="cuerpo" width="100%" marginwidth="0" height="600px" marginheight="0" scrolling="Auto" frameborder="0" hspace="0" vspace="0" id="cuerpo"></iframe>
      </div>
      <!--fin cuerpo-->

      <!--Pie de Pagina-->
      <div class="caja" align="center">
        <span class="sombra">
          Escuela Altas Cumbres, Maipú. Todos los Derechos Reservados.
        </span>
      </div>
      <!--fin Pie de Pagina-->
    </td>
  </tr>
</table>
</body>
</html>
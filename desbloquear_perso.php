<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\desbloquear_perso.php
/**
 * MÓDULO: Desbloqueo de Informe de Personalidad
 *
 * Permite consultar y desbloquear los informes de personalidad bloqueados de los alumnos por curso, periodo y semestre.
 * 
 * Requisitos:
 *   - Sesión activa con: nombre_apellido, cedula, ci_usu2, grado, nivel_intranet
 *   - Base de datos 'escuela' con tablas: alumno, asignatura, curso, perso{periodo}
 *   - Soporte UTF-8 en la base de datos
 *
 * Versión: 1.0
 * PHP: 8.x
 * Autor: Sistema Escolar
 */

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');
$conexion = conectarse_escuela();
session_start();

$nivel = $_SESSION["nivel_intranet"] ?? '';
$semestre = $_POST['semestre'] ?? '';
$grado = $_POST['grado'] ?? '';
$guarda = $_POST['guarda'] ?? 0;
$periodo = $_POST['periodo'] ?? '';
$fecha_insc = date("d/m/Y");
$btnaccion = $_POST['btnaccion'] ?? '';
$total_perso2 = $_POST['total_perso2'] ?? 0;
$num2 = $_POST['num2'] ?? '';
$total_ambito = $_POST['total_ambito'] ?? '';

switch ($btnaccion) {
    case "Consultar":
        if (empty($grado)) {
            echo "<script>alert('Debe escoger un grado para continuar');</script>";
            break;
        }
        if (empty($periodo)) {
            echo "<script>alert('Debe escribir el periodo o año escolar');</script>";
            break;
        }
        if (empty($semestre)) {
            echo "<script>alert('Debe escoger el semestre a desbloquear para continuar');</script>";
            break;
        }
        $guarda = 0;
        $consulta_perso2 = "SELECT * FROM perso$periodo WHERE grado='$grado' AND periodo='$periodo' AND semestre='$semestre' AND final='1'";
        $resultado_perso2 = $conexion->query($consulta_perso2);
        $total_perso2 = $resultado_perso2 ? $resultado_perso2->num_rows : 0;
        if ($total_perso2 < 1) {
            echo "<script>alert('Este curso no tiene informe de personalidad registrado o no está bloqueado');</script>";
        }
        break;

    case "Desbloquear":
        if (empty($grado)) {
            echo "<script>alert('Debe escoger un grado para continuar');</script>";
            break;
        }
        if (empty($periodo)) {
            echo "<script>alert('Debe escribir el periodo o año escolar');</script>";
            break;
        }
        if (empty($semestre)) {
            echo "<script>alert('Debe escoger el semestre a desbloquear para continuar');</script>";
            break;
        }
        $guarda++;
        $consulta_perso2 = "SELECT * FROM perso$periodo WHERE grado='$grado' AND periodo='$periodo' AND semestre='$semestre' AND final='1'";
        $resultado_perso2 = $conexion->query($consulta_perso2);
        $total_perso2 = $resultado_perso2 ? $resultado_perso2->num_rows : 0;
        if ($total_perso2 < 1) {
            echo "<script>alert('Este curso no tiene informe de personalidad registrado o no está bloqueado');</script>";
        } else {
            $consulta2 = "UPDATE perso$periodo SET final='0' WHERE grado='$grado' AND periodo='$periodo' AND semestre='$semestre' AND final='1'";
            $conexion->query($consulta2);
            echo "<script>alert('Los datos fueron desbloqueados con éxito.');</script>";
        }
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>INFORME PERSONALIDAD 1ER SEMESTRE</title>
<style type="text/css">
body,td,th {
    font-family: Verdana, Geneva, sans-serif;
    font-size: 12px;
    text-align: center;
}
body {
    margin: 0;
}
.sombra{ text-shadow: 0.05em 0.05em 0.03em #000; }
.caja { box-shadow: 5px 5px 5px rgba(0,0,0,.5); padding: 5px; background:#F3F3F3 ; margin:5px; width:1024px; }
</style>
<script type="text/javascript">
var statSend = false;
function checkSubmit() {
    if (!statSend) {
        statSend = true;
        return true;
    } else {
        alert("El formulario ya se está enviando...");
        return false;
    }
}
</script>
</head>
<body>
<center>
  <form id="form1" name="form1" method="post" onsubmit="return checkSubmit();">
  <div class="caja">
    <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
      DESBLOQUEAR PERSONALIDAD
    </font>
    Fecha: <?php echo $fecha_insc; ?>
    <input name="fecha_insc2" type="hidden" id="fecha_insc2" value="<?php echo $fecha_insc; ?>" maxlength="150" size="20" />
  </div>
  <table width="537" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr align="left">
      <td height="26" colspan="9" bgcolor="#E9E9E9"><strong>INTRODUZCA LOS DATOS DEL CURSO: </strong></td>
    </tr>
    <tr align="left">
      <td width="1" height="16" align="right" bgcolor="#F9F9F9">&nbsp;</td>
      <td width="49" bgcolor="#F9F9F9"><strong>Curso:</strong></td>
      <td width="77" bgcolor="#F9F9F9">
        <select name="grado" size="1" id="grado">
          <option value=""></option>
          <?php
          $grados = [];
          if ($nivel == 0) {
              if ($_SESSION["grado"] == 1) $grados[] = 1;
              if ($_SESSION["grado"] == 2) $grados[] = 2;
              if ($_SESSION["grado"] == 3) $grados[] = 3;
              if ($_SESSION["grado"] == 4) $grados[] = 4;
              if ($_SESSION["grado"] == 5) $grados[] = 5;
              if ($_SESSION["grado"] == 6) $grados[] = 6;
              if ($_SESSION["grado"] == 7) $grados[] = 7;
              if ($_SESSION["grado"] == 8) $grados[] = 8;
          } else {
              $grados = range(1, 8);
          }
          foreach ($grados as $g) {
              $selected = ($grado == $g) ? "selected='selected'" : "";
              $suffix = ($g == 1) ? 'ro' : (($g == 2) ? 'do' : (($g == 3) ? 'ro' : 'to'));
              if ($g == 7) $suffix = 'mo';
              if ($g == 8) $suffix = 'vo';
              echo "<option value='$g' $selected>{$g}{$suffix}</option>";
          }
          ?>
        </select>
      </td>
      <td width="48" bgcolor="#F9F9F9"><strong>Periodo:</strong></td>
      <td width="87" bgcolor="#F9F9F9">
        <input name="periodo" type="text" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" size="10" maxlength="11" />
      </td>
      <th width="100" bgcolor="#F9F9F9">
        <strong>Semestre:</strong>
      </th>
      <td width="63" bgcolor="#F9F9F9">
        <select name="semestre" size="1" id="semestre">
          <option value=""></option>
          <option value="1"<?php if ($semestre == '1') echo "selected='selected'"; ?>>1ro</option>
          <option value="2"<?php if ($semestre == '2') echo "selected='selected'"; ?>>2do</option>
        </select>
      </td>
      <td width="36" bgcolor="#F9F9F9">&nbsp;</td>
      <td width="20" bgcolor="#F9F9F9">&nbsp;</td>
    </tr>
    <tr align="left">
      <td height="6" colspan="9" align="center" bgcolor="#E9E9E9">
        <input name="guarda" type="hidden" id="guarda" value="<?php echo $guarda; ?>" />
        <input name="total_perso2" type="hidden" id="total_perso2" value="<?php echo $total_perso2; ?>" />
        <input name="num2" type="hidden" id="num2" value="<?php echo $num2; ?>" />
        <input name="total_ambito" type="hidden" id="total_ambito" value="<?php echo $total_ambito; ?>" />
        <input type="submit" name="btnaccion" id="btnaccion" value="Consultar" />
      </td>
    </tr>
  </table>
  <?php if ($total_perso2 > 0) { ?>
    <table width="552" border="0" align="center" cellpadding="2" cellspacing="2">
      <tr>
        <th width="99" height="26" align="left" valign="middle" bgcolor="#FFFFFF">
          Número de Registro: <?php echo $total_perso2; ?>
        </th>
      </tr>
      <tr>
        <th height="46" align="left" valign="middle" bgcolor="#FFFFFF">
          <?php if ($guarda < 1) { ?>
            <input type="submit" name="btnaccion" id="btnaccion" value="Desbloquear" />
          <?php } ?>
        </th>
      </tr>
    </table>
  <?php } ?>
  </form>
</center>
</body>
</html>
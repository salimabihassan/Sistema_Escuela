<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\registrar_inf_perso12_imp.php

include("conexiones.php");
header('Content-Type: text/html; charset=ISO-8859-1');
error_reporting(0);
$conexion = conectarse_escuela();

$ci_alu = $_POST['ci_alu'] ?? '';
$ci_alu2 = $_POST['ci_alu2'] ?? '';
$nom_alu = $_POST['nom_alu'] ?? '';
$grado = $_POST['grado'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$fecha_insc = date("d-m-Y");
$btnaccion = $_POST['btnaccion'] ?? '';
$total_consultar = $_POST['total_consultar'] ?? '';
$total_periodo = $_POST['total_periodo'] ?? '';

$total = "";
for ($segundos = 1; $segundos <= 3; $segundos++) {
    sleep(1);
    $total = $segundos;
}

// Consulta de impresión
$consulta_consultar = "SELECT * FROM imp WHERE id='1'";
$resultado_consultar = $conexion->query($consulta_consultar);
$datos_consultar = $resultado_consultar->fetch_assoc();
$ci_alu = $datos_consultar['ci_alu'] ?? $ci_alu;
$ci_alu2 = $datos_consultar['ci_alu2'] ?? $ci_alu2;

// Consulta de alumno
$consulta_consultar = "SELECT * FROM alumno WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2'";
$resultado_consultar = $conexion->query($consulta_consultar);
$total_consultar = $resultado_consultar->num_rows;

if ($total_consultar > 0) {
    $datos_consultar = $resultado_consultar->fetch_assoc();
    $borrado = $datos_consultar['borrado'];
    if ($borrado == 0) {
        $nom_alu = $datos_consultar['nom_alu'];
        $grado = $datos_consultar['grado'];
        $periodo = $datos_consultar['periodo'];
        $id_alu = $datos_consultar['id_alu'];
        $cod_curso = $grado . $periodo;

        // Consulta curso
        $consulta_curso = "SELECT * FROM curso WHERE cod_curso='$cod_curso'";
        $resultado_curso = $conexion->query($consulta_curso);
        $datos_curso = $resultado_curso->fetch_assoc();
        $ci_prof = $datos_curso['ci_prof'] ?? '';
        $ci_prof2 = $datos_curso['ci_prof2'] ?? '';

        // Consulta profesor jefe
        $consulta_prof = "SELECT * FROM prof WHERE ci_prof='$ci_prof' AND ci_prof2='$ci_prof2'";
        $resultado_prof = $conexion->query($consulta_prof);
        $datos_prof = $resultado_prof->fetch_assoc();
        $nom_prof = $datos_prof['nom_prof'] ?? '';

        // Consulta dirección
        $consulta_dir = "SELECT * FROM prof WHERE director='1'";
        $resultado_dir = $conexion->query($consulta_dir);
        $datos_dir = $resultado_dir->fetch_assoc();
        $nom_dir = $datos_dir['nom_prof'] ?? '';

        // Primer semestre
        $consulta_perso = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=1 ORDER BY cod_ambito";
        $resultado_perso = $conexion->query($consulta_perso);
        $total_perso = $resultado_perso->num_rows;
        if ($total_perso > 0) {
            $t = 1;
            while ($datos_perso = $resultado_perso->fetch_assoc()) {
                $id_perso[$t] = $datos_perso['id_perso'];
                $cod_ambito[$t] = $datos_perso['cod_ambito'];
                $lit[$t] = $datos_perso['lit'];
                $id = $id_perso[$t];
                $consulta_imp2 = "UPDATE perso$periodo SET final='1' WHERE id_perso='$id'";
                $conexion->query($consulta_imp2);
                $t++;
            }
        } else {
            echo "<script>alert('Este Alumno no tiene Registrado un informe de personalidad en el Primer Semestre...!!!');</script>";
        }

        // Segundo semestre
        $consulta_perso2 = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=2 ORDER BY cod_ambito";
        $resultado_perso2 = $conexion->query($consulta_perso2);
        $total_perso2 = $resultado_perso2->num_rows;
        if ($total_perso2 > 0) {
            $t = 1;
            while ($datos_perso2 = $resultado_perso2->fetch_assoc()) {
                $id_perso2[$t] = $datos_perso2['id_perso'];
                $cod_ambito2[$t] = $datos_perso2['cod_ambito'];
                $lit2[$t] = $datos_perso2['lit'];
                $id = $id_perso2[$t];
                $consulta_imp2 = "UPDATE perso$periodo SET final='1' WHERE id_perso='$id'";
                $conexion->query($consulta_imp2);
                $t++;
            }
        } else {
            echo "<script>alert('Este Alumno no tiene Registrado un informe de personalidad en el Segundo Semestre...!!!');</script>";
        }
    } else {
        echo "<script>alert('Esta Cedula del Alumno fue eliminado de la Base de Datos, Para Recuperarlo Vaya a la Papelera de Reciclaje...!!!');</script>";
        $nom_alu = "";
        $id_alu = "";
        $grado = "";
        $periodo = "";
    }
} else {
    echo "<script>alert('Esta Cedula de Alumno no está registrada en el sistema...!!!');</script>";
    $nom_alu = "";
    $id_alu = "";
    $grado = "";
    $periodo = "";
}
$fecha = $fecha_insc;

function obtenerFechaEnLetra($fecha) {
    $dia = conocerDiaSemanaFecha($fecha);
    $num = date("j", strtotime($fecha));
    $anno = date("Y", strtotime($fecha));
    $meses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
    $mes = $meses[(date('m', strtotime($fecha)) * 1) - 1];
    return $dia . ', ' . $num . ' de ' . $mes . ' del ' . $anno;
}

function conocerDiaSemanaFecha($fecha) {
    $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
    $dia = $dias[date('w', strtotime($fecha))];
    return $dia;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>INFORME DE PERSONALIDAD 1RO Y 2DO SEMESTRE</title>
<style type="text/css">
body,td,th { font-family: Verdana, Geneva, sans-serif; font-size: 14px; }
body { margin:0; background-color: #FFF; }
.sombra{ text-shadow: 0.05em 0.05em 0.03em  #000; }
.caja { box-shadow: 5px 5px 5px rgba(0,0,0,.5); padding: 5px; background:#F3F3F3 ; margin:5px; width:1024px; }
</style>
</head>
<body bgcolor="#FFFFFF">
<center>
<form id="form1" name="form1" method="post" action="registrar_inf_perso12_imp.php">
<div>
  <table width="91%" height="102" border="0" cellpadding="2" cellspacing="2">
    <tr>
      <td width="178" rowspan="5" align="center" valign="middle"><img src="/Sistema_Escuela/imagenes/logo altas cumbres.jpg" width="178" height="89" /></td>
      <td width="471" height="18" align="left" valign="middle"><strong>Escuela B&aacute;sica Particular N&ordm; 2271</strong></td>
      <td width="334" align="right" valign="middle">&nbsp;</td>
    </tr>
    <tr>
      <td height="18" align="left"><strong>Colegio &quot;Altas Cumbres del Rosal&quot;</strong></td>
      <td width="334" align="right" valign="middle">&nbsp;</td>
    </tr>
    <tr>
      <td height="18" align="left"><strong>Fono 316 55 18</strong></td>
      <td width="334" align="right" valign="middle">&nbsp;</td>
    </tr>
    <tr>
      <td height="18" align="left"><strong>caltascumbres@gmail.com</strong></td>
      <td width="334" align="right" valign="middle">&nbsp;</td>
    </tr>
    <tr>
      <td height="18" align="left" valign="top"><strong>RBD: 26392-3</strong></td>
      <th width="334" align="left" valign="middle"><?php echo obtenerFechaEnLetra($fecha); ?>
        <input name="fecha_insc" type="hidden" id="fecha_insc" value="<?php echo $fecha_insc; ?>" maxlength="150" size="20" /></th>
    </tr>
  </table>
</div>
<table width="809" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr align="left">
    <td height="13" colspan="6" align="center" bgcolor="#E9E9E9"><font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">INFORME DE PERSONALIDAD</font></td>
  </tr>
  <tr align="left">
    <td height="13" colspan="6" bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
  <tr align="left">
    <td height="16" align="right" bgcolor="#F9F9F9"><strong>C&eacute;dula:</strong></td>
    <td height="16" bgcolor="#F9F9F9"><strong><?php echo number_format($ci_alu, 0, ' ', '.') . '-' . $ci_alu2; ?>
      <input name="ci_alu" type="hidden" value="<?php echo $ci_alu; ?>" />
      <input name="ci_alu2" type="hidden" value="<?php echo $ci_alu2; ?>" />
    </strong></td>
    <td height="16" bgcolor="#F9F9F9">&nbsp;</td>
    <td height="16" align="right" bgcolor="#F9F9F9"><strong>Nombre:</strong></td>
    <td height="16" colspan="2" bgcolor="#F9F9F9"><strong><?php echo $nom_alu; ?></strong></td>
  </tr>
  <tr align="left">
    <td width="136" height="6" align="right" bgcolor="#E9E9E9">&nbsp;</td>
    <td width="132" height="6" align="right" bgcolor="#E9E9E9"><strong>Curso:</strong></td>
    <td width="96" height="6" align="left" bgcolor="#E9E9E9"><strong>
      <?php
      if ($grado == '1') echo '1ro';
      if ($grado == '2') echo '2do';
      if ($grado == '3') echo '3ro';
      if ($grado == '4') echo '4to';
      if ($grado == '5') echo '5to';
      if ($grado == '6') echo '6to';
      if ($grado == '7') echo '7mo';
      if ($grado == '8') echo '8vo';
      ?>
    </strong></td>
    <td width="136" height="6" align="left" bgcolor="#E9E9E9"><strong>Periodo Escolar:</strong></td>
    <td width="136" height="6" align="left" bgcolor="#E9E9E9"><strong><?php echo $periodo; ?></strong></td>
    <td width="135" height="6" align="left" bgcolor="#E9E9E9"><strong>
      <input name="total_consultar" type="hidden" value="<?php echo $total_consultar; ?>" />
    </strong></td>
  </tr>
</table>
<p>
<?php
$consulta_ambito = "SELECT * FROM ambito WHERE borrado=0 ORDER BY cod_ambito";
$resultado_ambito = $conexion->query($consulta_ambito);
$total_ambito = $resultado_ambito->num_rows;
?>
</p>
<table align="center" cellpadding="2" cellspacing="2">
  <tr>
    <th colspan="3" align="center" valign="top" bgcolor="#E9E9E9"><h3><strong>&Aacute;MBITO/INDICADORES</strong></h3></th>
    <th width="72" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9"><h3><strong>1&ordm; SEM</strong></h3></th>
    <th width="72" colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9"><h3>2&ordm; SEM</h3></th>
  </tr>
  <?php
  $t = 1;
  $x = 0;
  $fila = 1;
  while ($datos_ambito = $resultado_ambito->fetch_assoc()) {
      $resto = $fila % 2;
      $color = ($resto == 0) ? "#FFFFFF" : "#CCEEFF";
      $fila++;
      $x++;
      if ($x == 1) {
          echo '<tr>
            <th colspan="3" align="center" valign="top" bgcolor="#E9E9E9"><strong>&Aacute;MBITO: RELACI&Oacute;N CON SUS PARES</strong></th>
            <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
          </tr>';
      }
      if ($x == 7) {
          echo '<tr>
            <th colspan="3" align="center" valign="top" bgcolor="#E9E9E9"><strong>&Aacute;MBITO: DISCIPLINARIO</strong></th>
            <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
          </tr>';
      }
      if ($x == 12) {
          echo '<tr>
            <th colspan="3" align="center" valign="top" bgcolor="#E9E9E9"><strong>&Aacute;MBITO: RESPONSABILIDAD</strong></th>
            <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
          </tr>';
      }
      if ($x == 17) {
          echo '<tr>
            <th colspan="3" align="center" valign="top" bgcolor="#E9E9E9"><strong>&Aacute;MBITO: TRABAJO EN AULA</strong></th>
            <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
          </tr>';
      }
      if ($x == 21) {
          echo '<tr>
            <th colspan="3" align="center" valign="top" bgcolor="#E9E9E9"><strong>&Aacute;MBITO: AFECTIVIDAD</strong></th>
            <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
          </tr>';
      }
      if ($x == 24) {
          echo '<tr>
            <th colspan="3" align="center" valign="top" bgcolor="#E9E9E9"><strong>&Aacute;MBITO: PRESENTACI&Oacute;N PERSONAL</strong></th>
            <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
          </tr>';
      }
      if ($x == 29) {
          echo '<tr>
            <th colspan="3" align="center" valign="top" bgcolor="#E9E9E9"><strong>&Aacute;MBITO: EN CUANTO A LOS PADRES Y/O APODERADOS</strong></th>
            <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
          </tr>';
      }
      echo '<tr bgcolor="' . $color . '">
        <th colspan="3" align="left" valign="top" nowrap="nowrap" bgcolor="' . $color . '">
          <input name="cod_ambito' . $t . '" type="hidden" value="' . htmlspecialchars($datos_ambito['cod_ambito']) . '" />
          <input name="ambito' . $t . '" type="hidden" value="' . htmlspecialchars($datos_ambito['nom_ambito']) . '" />
          <input name="ambito" type="hidden" value="' . htmlspecialchars($datos_ambito['cod_ambito']) . '" />
          ' . htmlspecialchars($datos_ambito['cod_ambito'] . '-. ' . $datos_ambito['nom_ambito']) . '
        </th>
        <th align="center" valign="top" nowrap="nowrap" bgcolor="' . $color . '">
          <input name="lit' . $t . '" type="hidden" value="' . htmlspecialchars($lit[$t] ?? '') . '" />
          ' . htmlspecialchars($lit[$t] ?? '') . '
        </th>
        <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="' . $color . '">
          <input name="lit2' . $t . '" type="hidden" value="' . htmlspecialchars($lit2[$t] ?? '') . '" />
          ' . htmlspecialchars($lit2[$t] ?? '') . '
        </th>
      </tr>';
      $t++;
  }
  ?>
  <tr>
    <th colspan="3" align="left" bgcolor="#FFFFFF">&nbsp;</th>
    <th colspan="22" align="right" bgcolor="#FFFFFF">&nbsp;</th>
  </tr>
  <tr>
    <th width="116" align="center" bgcolor="#E9E9E9"><strong>Indicadores</strong></th>
    <th width="104" align="center" bgcolor="#E9E9E9"><strong>Significado</strong></th>
    <th width="337" align="left" bgcolor="#FFFFFF">&nbsp;</th>
    <th colspan="22" rowspan="6" align="right" bgcolor="#FFFFFF">&nbsp;</th>
  </tr>
  <tr>
    <td align="center" bgcolor="#CCFFFF"><strong>S</strong></td>
    <td align="center" bgcolor="#CCFFFF"><strong>Siempre</strong></td>
    <th align="left" bgcolor="#FFFFFF">&nbsp;</th>
  </tr>
  <tr>
    <td align="center" bgcolor="#FFFFFF"><strong>F</strong></td>
    <td align="center" bgcolor="#FFFFFF"><strong>Frecuentemente</strong></td>
    <th align="left" bgcolor="#FFFFFF">&nbsp;</th>
  </tr>
  <tr>
    <td align="center" bgcolor="#CCFFFF"><strong>O</strong></td>
    <td align="center" bgcolor="#CCFFFF"><strong>Ocasionalmente</strong></td>
    <th align="left" bgcolor="#FFFFFF">&nbsp;</th>
  </tr>
  <tr>
    <td align="center" bgcolor="#FFFFFF"><strong>NO</strong></td>
    <td align="center" bgcolor="#FFFFFF"><strong>No observado</strong></td>
    <th rowspan="2" align="left" bgcolor="#FFFFFF">&nbsp;</th>
  </tr>
  <tr>
    <td align="center" bgcolor="#CCFFFF"><strong>N</strong></td>
    <td align="center" bgcolor="#CCFFFF"><strong>Nunca</strong></td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="763" border="0" cellpadding="2" cellspacing="2">
  <tr>
    <th align="center">______________________________</th>
    <th>&nbsp;</th>
    <th align="center">______________________________</th>
  </tr>
  <tr>
    <th align="center"><?php echo $nom_prof; ?></th>
    <th>&nbsp;</th>
    <th align="center"><?php echo $nom_dir; ?></th>
  </tr>
  <tr>
    <th height="22" align="center">PROFESOR JEFE</th>
    <th align="center">&nbsp;</th>
    <th align="center">DIRECCI&Oacute;N</th>
  </tr>
</table>
<p>&nbsp;</p>
</form>
</center>
</body>
</html>
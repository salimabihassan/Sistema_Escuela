<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\consulta_curso.php
include ("conexiones.php");
error_reporting(0);
header('Content-Type: text/html; charset=UTF-8');

// se envian los valores de las cajas de texto
$sec = $_POST['sec'] ?? '';
$grado = $_POST['grado'] ?? '';
$ci_prof = $_POST['ci_rep'] ?? '';
$cod_curso = $_POST['cod_curso'] ?? '';
$retirado = $_POST['retirado'] ?? '';
$formu = $_POST['formu'] ?? '';
$targe = $_POST['targe'] ?? '';
$nac = trim($_POST['nac'] ?? '');
$nac_rep = trim($_POST['nac_rep'] ?? '');
$total_representantes = $_POST['total_representantes'] ?? 0;
$button = $_POST['button'] ?? '';
$dir_pag = $_POST['dir_pag'] ?? 'consulta_curso.php';

// Función para formatear fecha
function fentrada($cambio){
    $uno = substr($cambio, 0, 4);
    $dos = substr($cambio, 5, 2);
    $tres = substr($cambio, 8, 2);
    $resul = ($tres . "/" . $dos . "/" . $uno);
    return $resul;
}

$conexion = conectarse_escuela();

switch ($button) {
    case "Buscar":
        if (empty($cod_curso)) {
            $consulta_representantes = "SELECT * FROM curso WHERE activo=1 ORDER BY cod_curso ASC";
        } else {
            $consulta_representantes = "SELECT * FROM curso WHERE cod_curso='$cod_curso' AND activo=1 ORDER BY cod_curso ASC";
        }
        $resultado_representantes = $conexion->query($consulta_representantes);
        $total_representantes = $resultado_representantes ? $resultado_representantes->num_rows : 0;
        if (!empty($cod_curso) && $total_representantes < 1) {
            echo "<script>alert('Este código curso no existe o está inactivo');</script>";
        }
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>CONSULTA DE CURSO</title>
<style type="text/css">
body,td,th {
    font-family: Verdana, Geneva, sans-serif;
    font-size: 12px;
}
body {
    margin-left: 0px;
    margin-top: 0px;
    margin-right: 0px;
    margin-bottom: 0px;
}
a:link, a:visited, a:hover, a:active {
    text-decoration: none;
}
.sombra{ text-shadow: 0.05em 0.05em 0.03em  #000; }
.caja { box-shadow: 5px 5px 5px rgba(0,0,0,.5); padding: 5px; background:#F3F3F3 ; margin:5px; width:1024px; }
</style>
<script type="text/javascript">
function Solo_Numerico(variable){
    Numer=parseInt(variable);
    if (isNaN(Numer)){
        return "";
    }
    return Numer;
}
function ValNumero(Control){
    Control.value=Solo_Numerico(Control.value);
}
</script>
</head>
<body>
<center>
<form id="form1" name="form1" method="post" action="<?php echo $dir_pag; ?>">
  <div class="caja" align="center">
    <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
      CONSULTA DE CURSO
    </font>
  </div>
  <p>&nbsp;</p>
  <table width="630" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr bgcolor="#CCCCCC">
      <td height="33" colspan="3" bgcolor="#D7D7D7"><strong>INTRODUZCA EL C&Oacute;DIGO DEL CURSO A CONSULTAR</strong></td>
    </tr>
    <tr>
      <td width="165" height="36" align="right"><strong>C&oacute;digo:</strong></td>
      <td width="120"><input name="cod_curso" type="text" id="cod_curso" value="<?php echo htmlspecialchars($cod_curso); ?>" maxlength="6" size="6" /></td>
      <td width="325"><input type="submit" name="button" id="button" value="Buscar" /></td>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td colspan="3" align="center" bgcolor="#D7D7D7"><strong>Nota: Para consultar todos los cursos activos deje caja de texto vacía y pulse el botón Buscar</strong></td>
    </tr>
  </table>
  <p>
    <?php if ($total_representantes > 0) { ?>
  </p>
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr>
      <td height="33" align="center" nowrap="nowrap" bgcolor="#F9F9F9"><strong>C&eacute;dula Prof.</strong></td>
      <td height="33" colspan="2" align="center" bgcolor="#F9F9F9"><strong>Nombre y Apellidos</strong></td>
      <td height="33" align="center" bgcolor="#F9F9F9"><strong>Curso</strong></td>
      <td width="40" height="33" align="center" bgcolor="#F9F9F9"><strong>Periodo Escolar</strong></td>
      <td align="center" bgcolor="#F9F9F9"><strong>N&ordm; de Alumnos Inscritos en el curso</strong></td>
    </tr>
    <?php
    $fila = 1;
    $i = 0;
    while ($datos_representantes = $resultado_representantes->fetch_assoc()) {
        $resto = $fila % 2;
        $color = ($resto == 0) ? "#FFFFFF" : "#CCCCCC";
        $fila++;
        $i++;
        $ci_prof2 = $datos_representantes['ci_prof2'];
        $ci_prof = $datos_representantes['ci_prof'];
        $ci_nac = $ci_prof . '-' . $ci_prof2;
        $grado = $datos_representantes['grado'];
        $periodo = $datos_representantes['periodo'];
        $cod = trim($datos_representantes['cod_curso']);

        // Obtener nombre del profesor
        $consulta_prof = "SELECT * FROM prof WHERE ci_prof2='$ci_prof2' AND ci_prof='$ci_prof'";
        $resultado_prof = $conexion->query($consulta_prof);
        $datos_prof = $resultado_prof->fetch_assoc();
        $nom_prof = $datos_prof['nom_prof'] ?? '';

        // Obtener número de alumnos inscritos
        $consulta_n_alu = "SELECT * FROM alumno WHERE grado='$grado' AND periodo='$periodo' AND borrado='0'";
        $resultado_n_alu = $conexion->query($consulta_n_alu);
        $total_n_alu = $resultado_n_alu ? $resultado_n_alu->num_rows : 0;
        ?>
        <tr bgcolor="<?php echo $color; ?>">
          <td width="101" height="36" align="right" nowrap="nowrap"><?php echo htmlspecialchars($ci_nac); ?></td>
          <td colspan="2" nowrap="nowrap">&nbsp;<?php echo htmlspecialchars($nom_prof); ?></td>
          <td width="73" align="center"><?php echo htmlspecialchars($grado); ?></td>
          <td align="center"><?php echo htmlspecialchars($periodo); ?></td>
          <td align="center"><?php echo $total_n_alu; ?></td>
        </tr>
    <?php } ?>
    <tr>
      <td height="28" colspan="6" align="center">&nbsp;</td>
    </tr>
    <tr>
      <td height="18" colspan="6" align="center">&nbsp;</td>
    </tr>
  </table>
  <?php } ?>
</form>
</center>
</body>
</html>
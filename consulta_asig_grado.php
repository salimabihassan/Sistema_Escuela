<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\consulta_asig_grado.php
include ("conexiones.php");
error_reporting(0);
header('Content-Type: text/html; charset=UTF-8');

// Recibir los valores del formulario
$grado = $_POST['grado'] ?? '';
$id_asig = $_POST['id_asig'] ?? '';
$cod_asig = $_POST['cod_asig'] ?? '';
$nom_asig = $_POST['nom_asig'] ?? '';
$button = $_POST['button'] ?? '';

// Función para formatear fecha
function fentrada($cambio){
    $uno = substr($cambio, 0, 4);
    $dos = substr($cambio, 5, 2);
    $tres = substr($cambio, 8, 2);
    $resul = ($tres . "/" . $dos . "/" . $uno);
    return $resul;
}

$conexion = conectarse_escuela();
$total_asig = 0;
$resultado_asig = false;

switch ($button) {
    case "Buscar":
        if ($grado == 1) {$consulta_asig = "SELECT * FROM asignatura WHERE g1='SI' and borrado=0 order by cod_asig";}
        elseif ($grado == 2) {$consulta_asig = "SELECT * FROM asignatura WHERE g2='SI' and borrado=0 order by cod_asig";}
        elseif ($grado == 3) {$consulta_asig = "SELECT * FROM asignatura WHERE g3='SI' and borrado=0 order by cod_asig";}
        elseif ($grado == 4) {$consulta_asig = "SELECT * FROM asignatura WHERE g4='SI' and borrado=0 order by cod_asig";}
        elseif ($grado == 5) {$consulta_asig = "SELECT * FROM asignatura WHERE g5='SI' and borrado=0 order by cod_asig";}
        elseif ($grado == 6) {$consulta_asig = "SELECT * FROM asignatura WHERE g6='SI' and borrado=0 order by cod_asig";}
        elseif ($grado == 7) {$consulta_asig = "SELECT * FROM asignatura WHERE g7='SI' and borrado=0 order by cod_asig";}
        elseif ($grado == 8) {$consulta_asig = "SELECT * FROM asignatura WHERE g8='SI' and borrado=0 order by cod_asig";}
        else {$consulta_asig = "SELECT * FROM asignatura WHERE borrado=0 ORDER BY cod_asig";}
        $resultado_asig = $conexion->query($consulta_asig);
        $total_asig = $resultado_asig ? $resultado_asig->num_rows : 0;
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>CONSULTA DE ASIGNATURAS POR GRADO</title>
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
<form id="form1" name="form1" method="post" action="consulta_asig_grado.php">
  <div class="caja" align="center">
    <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
      CONSULTA DE ASIGNATURAS POR GRADO
    </font>
  </div>
  <p>&nbsp;</p>
  <table width="409" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr bgcolor="#CCCCCC">
      <td height="33" colspan="5" bgcolor="#D7D7D7"><strong>INTRODUZCA EL GRADO  A CONSULTAR</strong></td>
    </tr>
    <tr>
      <td width="43" height="36" align="right">Curso:</td>
      <td width="63">
        <select name="grado" size="1" id="grado">
          <option value="" selected="selected"></option>
          <option value="1"<?php if ($grado=='1') echo "selected='selected'"; ?>>1ro</option>
          <option value="2"<?php if ($grado=='2') echo "selected='selected'"; ?>>2do</option>
          <option value="3"<?php if ($grado=='3') echo "selected='selected'"; ?>>3ro</option>
          <option value="4"<?php if ($grado=='4') echo "selected='selected'"; ?>>4to</option>
          <option value="5"<?php if ($grado=='5') echo "selected='selected'"; ?>>5to</option>
          <option value="6"<?php if ($grado=='6') echo "selected='selected'"; ?>>6to</option>
          <option value="7"<?php if ($grado=='7') echo "selected='selected'"; ?>>7mo</option>
          <option value="8"<?php if ($grado=='8') echo "selected='selected'"; ?>>8vo</option>
        </select>
      </td>
      <td width="54"><input type="submit" name="button" id="button" value="Buscar" /></td>
      <td width="183">N&ordm; de Asignatura del Curso:</td>
      <td width="34" align="center"><?php echo $total_asig; ?></td>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td colspan="5" align="center" valign="middle" bgcolor="#D7D7D7">
        <p><strong>Nota: Verifique los datos ante de pulsar el boton Buscar</strong></p>
      </td>
    </tr>
  </table>
  <?php if ($total_asig > 0) { ?>
  <table width="36%" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr>
      <td width="61" height="31" align="center" valign="middle" bgcolor="#D7D7D7"><strong>N&ordm;</strong></td>
      <td width="45" align="center" valign="middle" bgcolor="#D7D7D7"><strong>C&oacute;digo</strong></td>
      <td width="272" align="center" valign="middle" bgcolor="#D7D7D7"><strong>Nombre</strong></td>
    </tr>
    <?php
    $fila = 1;
    $i = 0;
    if ($button == 'Buscar' && $resultado_asig) {
      while ($datos_asig = $resultado_asig->fetch_assoc()) {
        $resto = $fila % 2;
        $color = ($resto == 0) ? "#FFFFFF" : "#CCEEFF";
        $fila++;
        $i++;
        ?>
        <tr>
          <td align="center" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><?php echo $i; ?></td>
          <td align="center" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><?php echo htmlspecialchars($datos_asig['cod_asig']); ?></td>
          <td align="left" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><?php echo htmlspecialchars($datos_asig['nom_asig']); ?></td>
        </tr>
      <?php } ?>
      <tr>
        <td colspan="3" align="center" nowrap="nowrap" bgcolor="#D7D7D7">
          <input type="button" value="Imprimir" onclick="window.print()" />
        </td>
      </tr>
      <tr>
        <td colspan="3" align="center" nowrap="nowrap" bgcolor="#D7D7D7">
          <strong>Nota: Verifique los datos antes de pulsar el boton Imprimir</strong>
        </td>
      </tr>
    <?php } ?>
  </table>
  <?php } ?>
</form>
</center>
</body>
</html>
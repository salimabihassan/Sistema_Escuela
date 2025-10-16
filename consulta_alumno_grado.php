<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\consulta_alumno_grado.php
// incluir validación de sesión y UTF-8
include __DIR__ . '/auth.php';

// luego incluir conexión a BD
include __DIR__ . '/conexiones.php';

// Configurar UTF-8
header('Content-Type: text/html; charset=UTF-8');

// Recibir datos del formulario
$sec = $_POST['sec'] ?? '';
$grado = $_POST['grado'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$dir_rep = $_POST['dir_rep'] ?? '';
$id_rep = $_POST['id_rep'] ?? '';
$retirado = $_POST['retirado'] ?? '';
$nac = trim($_POST['nac'] ?? '');
$button = $_POST['button'] ?? '';

// Función para formatear fecha
function fentrada($cambio) {
    $uno = substr($cambio, 0, 4);
    $dos = substr($cambio, 5, 2);
    $tres = substr($cambio, 8, 2);
    $resul = ($tres . "/" . $dos . "/" . $uno);
    return $resul;
}

$conexion = conectarse_escuela();

$total_alu = 0;
$resultado_alu = false;

switch ($button) {
    case "Buscar":
        if (!empty($periodo)) {
            $consulta_alu = "SELECT * FROM alumno WHERE grado='$grado' AND periodo='$periodo' AND retirado=0 AND borrado=0 ORDER BY nom_alu";
        } else {
            $consulta_alu = "SELECT * FROM alumno WHERE grado='$grado' AND retirado=0 AND borrado=0 ORDER BY nom_alu";
        }
        $resultado_alu = $conexion->query($consulta_alu);
        $total_alu = $resultado_alu ? $resultado_alu->num_rows : 0;
        break;
    case "Imprimir":
        // Acción de imprimir (puedes agregar lógica si lo necesitas)
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>CONSULTA DE ALUMNOS POR CURSO</title>
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
<form id="form1" name="form1" method="post" action="consulta_alumno_grado.php">
  <div class="caja" align="center">
    <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
      CONSULTA DE ALUMNOS POR CURSO
    </font>
  </div>
  <p>&nbsp;</p>
  <table width="475" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr bgcolor="#CCCCCC">
      <td height="33" colspan="7" bgcolor="#D7D7D7"><strong>INTRODUZCA EL GRADO Y LA SECCI&Oacute;N A CONSULTAR</strong></td>
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
      <td width="53">Periodo:</td>
      <td width="24"><input name="periodo" type="text" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" maxlength="5" size="4" /></td>
      <td width="54"><input type="submit" name="button" id="button" value="Buscar" /></td>
      <td width="162">N&ordm; de Alumnos del Curso:</td>
      <td width="32" align="center"><?php echo $total_alu; ?></td>
    </tr>
    <tr bgcolor="#CCCCCC">
      <td colspan="7" align="center" valign="middle" bgcolor="#D7D7D7">
        <p><strong>Nota: Verifique los datos ante de pulsar el boton Buscar</strong></p>
      </td>
    </tr>
  </table>
  <?php if ($total_alu > 0) { ?>
  <table width="42%" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr>
      <td width="18" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>N&ordm;</strong></h3></td>
      <td width="86" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Cédula</strong></h3></td>
      <td width="172" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Nombre</strong></h3></td>
      <td width="36" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Sexo</strong></h3></td>
      <td width="43" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Curso</strong></h3></td>
      <td width="73" height="33" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Periodo</strong></h3></td>
    </tr>
    <?php
    $fila = 1;
    $i = 0;
    if ($button == 'Buscar' && $resultado_alu) {
      while ($datos_alu = $resultado_alu->fetch_assoc()) {
        $resto = $fila % 2;
        $color = ($resto == 0) ? "#FFFFFF" : "#CCEEFF";
        $fila++;
        $i++;
        $sexo = $datos_alu['sexo'] == "M" ? "MASCULINO" : "FEMENINO";
        ?>
        <tr>
          <td align="center" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><h3><?php echo $i; ?></h3></td>
          <td align="center" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><h3><?php echo htmlspecialchars($datos_alu['ci_alu'] . '-' . $datos_alu['ci_alu2']); ?></h3></td>
          <td align="center" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><h3><?php echo htmlspecialchars($datos_alu['nom_alu']); ?></h3></td>
          <td align="center" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><h3><?php echo $sexo; ?></h3></td>
          <td align="center" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><h3><?php echo htmlspecialchars($datos_alu['grado']); ?></h3></td>
          <td align="center" valign="middle" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><h3><?php echo htmlspecialchars($datos_alu['periodo']); ?></h3></td>
        </tr>
      <?php } ?>
      <tr>
        <td colspan="6" align="center" nowrap="nowrap" bgcolor="#D7D7D7">
          <input type="button" value="Imprimir" onclick="window.print()" />
        </td>
      </tr>
      <tr>
        <td colspan="6" align="center" nowrap="nowrap" bgcolor="#D7D7D7">
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
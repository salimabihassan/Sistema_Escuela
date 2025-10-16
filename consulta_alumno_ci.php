<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\consulta_alumno_ci.php
include("conexiones.php");
error_reporting(0);
header('Content-Type: text/html; charset=UTF-8');

$ci_alu = $_POST['ci_alu'] ?? '';
$ci_alu2 = $_POST['ci_alu2'] ?? '';

$conexion = conectarse_escuela();

if (!empty($ci_alu)) {
    $consulta_alu = "SELECT * FROM alumno WHERE ci_alu2='$ci_alu2' AND ci_alu='$ci_alu' AND retirado=0 AND borrado=0 ORDER BY nom_alu ASC";
} else {
    $consulta_alu = "SELECT * FROM alumno WHERE retirado=0 AND borrado=0 ORDER BY nom_alu ASC";
}
$resultado_alu = $conexion->query($consulta_alu);
$total_alu = $resultado_alu ? $resultado_alu->num_rows : 0;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>CONSULTA DE ALUMNOS POR CÉDULA</title>
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
<form id="form1" name="form1" method="post" action="consulta_alumno_ci.php">
    <div class="caja" align="center">
        <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
            CONSULTA DE ALUMNO POR CÉDULA
        </font>
    </div>
    <table width="533" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <td height="33" colspan="4" align="center" bgcolor="#D7D7D7"><strong>INTRODUZCA LA CÉDULA DEL ALUMNO A CONSULTAR</strong></td>
        </tr>
        <tr>
            <td width="227" height="36" align="right"><strong>Cédula:</strong></td>
            <td width="54">
                <input name="ci_alu" type="text" id="ci_alu" value="<?php echo htmlspecialchars($ci_alu); ?>" onkeyup="return ValNumero(this);" maxlength="9" size="9" />
            </td>
            <td width="16">
                <input name="ci_alu2" type="text" id="ci_alu2" value="<?php echo htmlspecialchars($ci_alu2); ?>" maxlength="1" size="1" />
            </td>
            <td width="210">
                <input type="submit" name="button" id="button" value="Buscar" />
            </td>
        </tr>
        <tr>
            <td colspan="4" align="center" bgcolor="#D7D7D7">
                <strong>Nota: Para consultar todos los Alumnos activos deje caja de texto vacía y pulse el botón Buscar</strong>
            </td>
        </tr>
    </table>
    <p>&nbsp;</p>
    <table width="533" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <td width="18" height="26" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Nº</strong></h3></td>
            <td width="86" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Cédula</strong></h3></td>
            <td width="172" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Nombre</strong></h3></td>
            <td width="36" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Sexo</strong></h3></td>
            <td width="95" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Curso</strong></h3></td>
            <td width="88" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong>Periodo</strong></h3></td>
        </tr>
        <?php
        $fila = 1;
        $i = 0;
        while ($datos_alu = $resultado_alu->fetch_assoc()) {
            $resto = $fila % 2;
            $color = ($resto == 0) ? "#FFFFFF" : "#CCEEFF";
            $fila++;
            $i++;
            $sexo = $datos_alu['sexo'] == "M" ? "MASCULINO" : "FEMENINO";
            ?>
            <tr bgcolor="<?php echo $color; ?>">
                <td align="center" nowrap="nowrap"><h3><?php echo $i; ?></h3></td>
                <td align="center" nowrap="nowrap"><h3><?php echo htmlspecialchars($datos_alu['ci_alu'] . '-' . $datos_alu['ci_alu2']); ?></h3></td>
                <td align="center" nowrap="nowrap"><h3><?php echo htmlspecialchars($datos_alu['nom_alu']); ?></h3></td>
                <td align="center" nowrap="nowrap"><h3><?php echo $sexo; ?></h3></td>
                <td align="center" nowrap="nowrap"><h3><?php echo htmlspecialchars($datos_alu['grado']); ?></h3></td>
                <td align="center" valign="middle" nowrap="nowrap"><h3><?php echo htmlspecialchars($datos_alu['periodo']); ?></h3></td>
            </tr>
        <?php } ?>
        <tr>
            <td height="26" align="center" valign="middle" bgcolor="#D7D7D7"><h3><strong><?php echo $i; ?></strong></h3></td>
            <td height="26" colspan="2" align="left" valign="middle" bgcolor="#D7D7D7"><h3><strong>Total consultados</strong></h3></td>
            <td height="26" colspan="3" align="right" valign="middle" bgcolor="#D7D7D7">
                <input type="button" value="Imprimir" onclick="window.print()" />
            </td>
        </tr>
    </table>
    <p>&nbsp;</p>
</form>
</center>
</body>
</html>
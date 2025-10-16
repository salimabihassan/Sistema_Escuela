<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\consulta_prof_ci.php
include ("conexiones.php");
error_reporting(0);
header('Content-Type: text/html; charset=UTF-8');

$ci_prof = $_POST['ci_prof'] ?? '';
$ci_prof2 = $_POST['ci_prof2'] ?? '';
$conexion = conectarse_escuela();

if (!empty($ci_prof)) {
    $consulta_representantes = "SELECT * FROM prof WHERE ci_prof2='$ci_prof2' AND ci_prof='$ci_prof' AND retirado=0";
} else {
    $consulta_representantes = "SELECT * FROM prof WHERE retirado=0 ORDER BY ci_prof ASC";
}
$resultado_representantes = $conexion->query($consulta_representantes);
$total_representantes = $resultado_representantes ? $resultado_representantes->num_rows : 0;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>CONSULTA DE PROFESOR POR CÉDULA</title>
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
<form id="form1" name="form1" method="post" action="consulta_prof_ci.php">
    <div class="caja" align="center">
        <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
            CONSULTA DE PROFESOR POR CÉDULA
        </font>
    </div>
    <table width="533" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr bgcolor="#CCCCCC">
            <td height="33" colspan="4" bgcolor="#D7D7D7"><strong>INTRODUZCA LA CÉDULA DEL PROFESOR A CONSULTAR</strong></td>
        </tr>
        <tr>
            <td width="212" height="36" align="right"><strong>Cédula:</strong></td>
            <td width="54">
                <input name="ci_prof" type="text" id="ci_prof" value="<?php echo htmlspecialchars($ci_prof); ?>" onkeyup="return ValNumero(this);" maxlength="9" size="9" />
            </td>
            <td width="6">
                <input name="ci_prof2" type="text" id="ci_prof2" value="<?php echo htmlspecialchars($ci_prof2); ?>" maxlength="1" size="1" />
            </td>
            <td width="235">
                <input type="submit" name="button" id="button" value="Buscar" />
            </td>
        </tr>
        <tr bgcolor="#CCCCCC">
            <td colspan="4" align="center" bgcolor="#D7D7D7">
                <strong>Nota: Para consultar todos los Profesores activos deje caja de texto vacía y pulse el botón</strong>
            </td>
        </tr>
    </table>
    <p>&nbsp;</p>
    <table width="623" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <td width="20" height="26" align="center" valign="middle" bgcolor="#F9F9F9"><strong>Nº</strong></td>
            <td width="131" align="center" valign="middle" bgcolor="#F9F9F9"><strong>Cédula</strong></td>
            <td width="103" align="center" valign="middle" bgcolor="#F9F9F9"><strong>Nombre y Apellidos</strong></td>
            <td align="center" valign="middle" bgcolor="#F9F9F9"><strong>Dirección</strong></td>
            <td width="91" align="center" valign="middle" bgcolor="#F9F9F9"><strong>Teléfonos</strong></td>
        </tr>
        <?php
        $fila = 1;
        $i = 0;
        while ($datos_representantes = $resultado_representantes->fetch_assoc()) {
            $resto = $fila % 2;
            $color = ($resto == 0) ? "#FFFFFF" : "#CCCCCC";
            $fila++;
            $i++;
            $ci_prof = $datos_representantes['ci_prof'];
            $ci_prof2 = $datos_representantes['ci_prof2'];
            ?>
            <tr bgcolor="<?php echo $color; ?>">
                <td align="center"><?php echo $i; ?></td>
                <td align="center" nowrap="nowrap"><?php echo htmlspecialchars($ci_prof . '-' . $ci_prof2); ?></td>
                <td align="left" nowrap="nowrap">&nbsp;<?php echo htmlspecialchars($datos_representantes['nom_prof']); ?></td>
                <td align="center" valign="middle" nowrap="nowrap">&nbsp;<?php echo htmlspecialchars($datos_representantes['dir_prof']); ?></td>
                <td align="center" valign="middle" nowrap="nowrap">&nbsp;<?php echo htmlspecialchars($datos_representantes['tlf_prof']); ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td height="26" align="right" valign="middle" bgcolor="#F9F9F9"><strong><?php echo $i; ?></strong></td>
            <td height="26" colspan="2" align="left" valign="middle" bgcolor="#F9F9F9"><strong>Total consultados</strong></td>
            <td height="26" align="left" valign="middle" bgcolor="#F9F9F9">
                <input type="button" value="Imprimir" onclick="window.print()" />
            </td>
            <td height="26" align="left" valign="middle" bgcolor="#F9F9F9">&nbsp;</td>
        </tr>
    </table>
    <p>&nbsp;</p>
</form>
</center>
</body>
</html>
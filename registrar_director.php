<?php
include("conexiones.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

function validar_rut($rut, $digito_v) {
    if ($rut == "") return false;
    $x = 2;
    $sumatorio = 0;
    for ($i = strlen($rut) - 1; $i >= 0; $i--) {
        if ($x > 7) $x = 2;
        $sumatorio += ($rut[$i] * $x);
        $x++;
    }
    $digito = $sumatorio % 11;
    $digito = 11 - $digito;
    switch ($digito) {
        case 10: $digito = "k"; break;
        case 11: $digito = "0"; break;
    }
    return strtolower($digito_v) == $digito;
}

// Recibe los valores del formulario
$id_prof = $_POST['id_prof'] ?? '';
$ci_prof2 = trim($_POST['ci_prof2'] ?? '');
$ci_prof = $_POST['ci_prof'] ?? '';
$nom_prof = $_POST['nom_prof'] ?? '';
$tlf_prof = $_POST['tlf_prof'] ?? '';
$dir_prof = $_POST['dir_prof'] ?? '';
$retirado = $_POST['retirado'] ?? '';
$total_buscar = $_POST['total_buscar'] ?? '';
$i = $_POST['i'] ?? 0;
$button = $_POST['button'] ?? '';

$conexion = conectarse_escuela();

// Carga los datos del director (id_prof = 1) solo una vez
if ($i <= 0) {
    $i++;
    $stmt = $conexion->prepare("SELECT * FROM prof WHERE id_prof = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $datos = $result->fetch_assoc();
        $retirado = $datos['retirado'];
        $id_prof = $datos['id_prof'];
        $ci_prof = $datos['ci_prof'];
        $ci_prof2 = $datos['ci_prof2'];
        $nom_prof = $datos['nom_prof'];
        $dir_prof = $datos['dir_prof'];
        $tlf_prof = $datos['tlf_prof'];
    }
}

switch ($button) {


case "Actualizar":
    // 1. Validar RUT
    if (!validar_rut($ci_prof, $ci_prof2)) {
        echo "<script>alert('El Dígito verificador del Rut no es correcto, verifique y vuelva a intentar');</script>";
        break;
    }

    // 2. Buscar si el RUT existe en la tabla prof
    $stmt = $conexion->prepare("SELECT * FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
    $stmt->bind_param("ss", $ci_prof, $ci_prof2);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('El RUT no existe en la base de datos de profesores');</script>";
        break;
    }

    // 3. Si existe, asigna director=1 a ese registro y director=0 a cualquier otro
    $datos = $result->fetch_assoc();
    $id_prof = $datos['id_prof'];
    $nom_prof = $datos['nom_prof'];
    $dir_prof = $datos['dir_prof'];
    $tlf_prof = $datos['tlf_prof'];

    // Poner director=0 a todos los demás
    $conexion->query("UPDATE prof SET director = 0 WHERE director = 1");

    // Poner director=1 al seleccionado
    $stmt_upd = $conexion->prepare("UPDATE prof SET director = 1 WHERE id_prof = ?");
    $stmt_upd->bind_param("i", $id_prof);
    $stmt_upd->execute();

    echo "<script>alert('El profesor fue asignado como director correctamente');</script>";
    break;

}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>REGISTRAR DIRECTOR</title>
<style type="text/css">
/* ...estilos sin cambios... */
body,td,th { font-family: Verdana, Geneva, sans-serif; font-size: 12px; }
body { margin: 0; }
.sombra{ text-shadow: 0.05em 0.05em 0.03em  #000; }
.caja { 
    -webkit-box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    -moz-box-shadow: 5px 5px 4px rgba(0,0,0,.5);
    box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    padding: 5px;
    background:#F3F3F3 ;
    margin:5px;
    width:1024px;
}
</style>
<script type="text/javascript">
function Solo_Numerico(variable){
    var Numer = parseInt(variable);
    if (isNaN(Numer)){
        return "";
    }
    return Numer;
}
function ValNumero(Control){
    Control.value = Solo_Numerico(Control.value);
}
</script>
</head>
<body>
<center>
<form id="form1" name="form1" method="post" action="registrar_director.php">
    <div class="caja" align="center" >
        <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
        REGISTRAR DIRECTOR
        </font>
    </div>
    <table width="599" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr align="left">
            <td width="89" height="36">Cédula:</td>
            <td width="49">
                <input name="ci_prof" type="text" id="ci_prof" value="<?php echo htmlspecialchars($ci_prof);?>" onkeyup="return ValNumero(this);" maxlength="8" size="8" />
            </td>
            <td width="6">
                <input name="ci_prof2" type="text" id="ci_prof2" value="<?php echo htmlspecialchars($ci_prof2);?>" maxlength="1" size="1" />
            </td>
            <td width="429">
                <input name="id_prof" type="hidden" id="id_prof" value="<?php echo htmlspecialchars($id_prof);?>" maxlength="8" size="20" />
                <input name="retirado" type="hidden" id="retirado" value="<?php echo htmlspecialchars($retirado);?>" maxlength="8" size="20" />
                <input name="total_buscar" type="hidden" id="total_buscar" value="<?php echo htmlspecialchars($total_buscar);?>" maxlength="8" size="20" />
                <input name="i" type="hidden" id="i" value="<?php echo htmlspecialchars($i);?>" maxlength="8" size="20" />
            </td>
        </tr>
        <tr align="left">
            <td height="34" bgcolor="#E9E9E9">Nombre:</td>
            <td colspan="3" bgcolor="#E9E9E9">
                <input name="nom_prof" type="text" id="nom_prof" value="<?php echo htmlspecialchars($nom_prof);?>" size="50" maxlength="80" />
            </td>
        </tr>
        <tr align="left">
            <td height="32">Dirección</td>
            <td colspan="3">
                <input name="dir_prof" type="text" id="dir_prof" value="<?php echo htmlspecialchars($dir_prof);?>" size="80" maxlength="150" />
            </td>
        </tr>
        <tr align="left">
            <td height="34" bgcolor="#E9E9E9">Teléfonos</td>
            <td colspan="3" bgcolor="#E9E9E9">
                <input name="tlf_prof" type="text" id="tlf_prof" value="<?php echo htmlspecialchars($tlf_prof);?>" size="50" maxlength="50" />
            </td>
        </tr>
        <tr>
            <td height="49" colspan="4" align="center">
                <input type="submit" name="button" id="button" value="Actualizar" />
            </td>
        </tr>
        <tr>
            <td colspan="4" align="center" bgcolor="#E9E9E9"><strong>Nota: Verifique los datos antes de pulsar uno de los botones</strong></td>
        </tr>
    </table>
</form>
</center>
</body>
</html>
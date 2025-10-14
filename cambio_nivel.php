<?php  
// filepath: c:\xampp\htdocs\Sistema_Escuela\cambio_nivel.php
include("conexiones.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

$ci_prof2 = trim($_POST['ci_prof2'] ?? '');
$ci_prof = trim($_POST['ci_prof'] ?? '');
$nom_prof = $_POST['nom_prof'] ?? '';
$nivel = $_POST['nivel'] ?? '';
$button = $_POST['button'] ?? '';

switch ($button) {
    case "Guardar":
        if (empty($ci_prof2)) {
            echo "<script>alert('El Campo validación de Cédula no puede estar vacío');</script>";
            break;
        }
        if (empty($ci_prof)) {
            echo "<script>alert('Debe seleccionar Cédula válida...');</script>";
            break;
        }

        $conexion = conectarse_escuela();
        $consulta_buscar = $conexion->prepare("SELECT * FROM usuarios WHERE cedula = ? AND ci_usu2 = ?");
        $consulta_buscar->bind_param("ss", $ci_prof, $ci_prof2);
        $consulta_buscar->execute();
        $resultado_buscar = $consulta_buscar->get_result();
        $total_buscar = $resultado_buscar->num_rows;

        if ($total_buscar > 0) {
            $consulta_update = $conexion->prepare("UPDATE usuarios SET nivel = ? WHERE cedula = ? AND ci_usu2 = ?");
            $consulta_update->bind_param("sss", $nivel, $ci_prof, $ci_prof2);
            $consulta_update->execute();

            if ($consulta_update->affected_rows > 0) {
                // Auditoría
                $fecha_insc = date("d/m/Y");
                $cod_reg = $ci_prof . '-' . $ci_prof2;
                $ci_usu = $_SESSION["cedula"] ?? '';
                $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
                $desc_reg = 'Cambio Nivel de Acceso';
                $registro = 'Mantenimiento, Nivel de Usuario';
                $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

                $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
                $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
                $sql_aud->execute();

                echo "<script>alert('Actualización realizada con éxito');</script>";
            }
        }
        break;

    case "Buscar":
        if (empty($ci_prof2)) {
            echo "<script>alert('El Campo validación de Cédula no puede estar vacío');</script>";
            break;
        }
        if (empty($ci_prof)) {
            echo "<script>alert('Debe seleccionar Cédula válida...');</script>";
            break;
        }

        $conexion = conectarse_escuela();
        $consulta_buscar_usu = $conexion->prepare("SELECT * FROM usuarios WHERE cedula = ? AND ci_usu2 = ?");
        $consulta_buscar_usu->bind_param("ss", $ci_prof, $ci_prof2);
        $consulta_buscar_usu->execute();
        $resultado_buscar_usu = $consulta_buscar_usu->get_result();
        $total_buscar_usu = $resultado_buscar_usu->num_rows;

        if ($total_buscar_usu > 0) {
            $datos_buscar_usu = $resultado_buscar_usu->fetch_assoc();
            $consulta_buscar = $conexion->prepare("SELECT * FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
            $consulta_buscar->bind_param("ss", $ci_prof, $ci_prof2);
            $consulta_buscar->execute();
            $resultado_buscar = $consulta_buscar->get_result();
            $total_buscar = $resultado_buscar->num_rows;

            if ($total_buscar > 0) {
                $datos_buscar = $resultado_buscar->fetch_assoc();
                $retirado = $datos_buscar['retirado'];
                if ($retirado == 0) {
                    $nom_prof = $datos_buscar['nom_prof'];
                    $nivel = $datos_buscar_usu['nivel'];
                } else {
                    echo "<script>alert('Este Nro. de Cédula fue eliminado de la Base de Datos, Para Recuperarlo Vaya a la Papelera de Reciclaje...!!!');</script>";
                    $nom_prof = "";
                }
            } else {
                echo "<script>alert('Cédula No Registrada...!!!');</script>";
                $nom_prof = "";
            }
        } else {
            echo "<script>alert('Cédula No Registrada como usuario...!!!');</script>";
            $nom_prof = "";
            $nivel = "";
        }
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es" dir="ltr">
<head>
<META NAME="robots" CONTENT="NOINDEX,FOLLOW">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="botones.css" type="text/css" media="screen" />
<title>Registrar Usuarios</title>
<style type="text/css">
body {
    background-image: url();
    margin-left: 0px;
    margin-top: 0px;
    margin-right: 0px;
    margin-bottom: 0px;
}
a:link, a:visited, a:hover, a:active {
    color: #000;
    text-decoration: none;
}
body,td,th {
    font-family: Verdana, Geneva, sans-serif;
    font-size: 12px;
}
div.formulario {
    border: 1px solid #CCC;
    border-radius: 15px;
    padding: 10px;
    width:350px;
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
<br/><br/>
<div align="center">
    <div class="formulario">
        <form action="cambio_nivel.php" method="post" name="frmentrar" id="frmentrar">
            <table width="346" height="192" border="0" align="center" cellpadding="0" cellspacing="3">
                <tr>
                    <td height="60" colspan="4" align="center">
                        <img src="imagenes/logo altas cumbres.jpg" width="179" height="172" />
                    </td>
                </tr>
                <tr>
                    <td width="66" height="24" align="left" bgcolor="#F8F8F8"><em><strong>Cédula:</strong></em></td>
                    <td width="90" height="24" align="left" bgcolor="#F8F8F8">
                        <input name="ci_prof" type="text" id="ci_prof" value="<?php echo htmlspecialchars($ci_prof); ?>" onkeyup="return ValNumero(this);" maxlength="9" size="9" />
                    </td>
                    <td width="15" align="left" bgcolor="#F8F8F8">
                        <input name="ci_prof2" type="text" id="ci_prof2" value="<?php echo htmlspecialchars($ci_prof2); ?>" maxlength="1" size="1" />
                    </td>
                    <td width="160" align="left" bgcolor="#F8F8F8">
                        <input type="submit" name="button" id="button" value="Buscar" />
                    </td>
                </tr>
                <tr>
                    <td width="66" height="24" align="left" bgcolor="#F8F8F8"><em><strong>Nombre:</strong></em></td>
                    <td height="24" colspan="3" align="left" bgcolor="#F8F8F8">
                        <input name="nom_prof" type="text" id="nom_prof" value="<?php echo htmlspecialchars($nom_prof); ?>" size="35" maxlength="80" />
                    </td>
                </tr>
                <tr>
                    <td width="66" height="24" align="left" bgcolor="#F8F8F8"><em><strong>Nivel de Acceso:</strong></em></td>
                    <td height="24" colspan="3" align="left" bgcolor="#F8F8F8">
                        <select name="nivel" id="nivel">
                            <option value="" <?php if ($nivel=='') {echo "selected='selected'"; }?>></option>
                            <option value="1" <?php if ($nivel=='1') {echo "selected='selected'"; }?>>Administrador</option>
                            <option value="0" <?php if ($nivel=='0') {echo "selected='selected'"; }?>>Profesor</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td height="42" colspan="4" align="center" bgcolor="#F8F8F8">
                        <input type="submit" name="button" class="button medium red" value="Guardar" id="button" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
</body>
</html>
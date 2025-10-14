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

$id_prof = $_POST['id_prof'] ?? '';
$ci_prof2 = trim($_POST['ci_prof2'] ?? '');
$ci_prof = $_POST['ci_prof'] ?? '';
$nom_prof = $_POST['nom_prof'] ?? '';
$tlf_prof = $_POST['tlf_prof'] ?? '';
$dir_prof = $_POST['dir_prof'] ?? '';
$retirado = $_POST['retirado'] ?? '';
$total_buscar = $_POST['total_buscar'] ?? '';
$button = $_POST['button'] ?? '';

$conexion = conectarse_escuela();

switch ($button) {
    case "Registrar":
        $nom_prof = ucwords($nom_prof);
        $dir_prof = strtoupper($dir_prof);

        if (empty($ci_prof)) {
            echo "<script>alert('El campo Cédula no puede estar vacío...');</script>";
            break;
        }
        if (!validar_rut($ci_prof, $ci_prof2)) {
            echo "<script>alert('El Dígito verificador del Rut no es correcto, verifique y vuelva a intentar');</script>";
            break;
        }
        if (empty($nom_prof)) {
            echo "<script>alert('El campo Nombre no puede estar vacío...');</script>";
            break;
        }

        $stmt = $conexion->prepare("SELECT id_prof FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
        $stmt->bind_param("ss", $ci_prof, $ci_prof2);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt = $conexion->prepare("INSERT INTO prof (ci_prof, ci_prof2, nom_prof, dir_prof, tlf_prof) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $ci_prof, $ci_prof2, $nom_prof, $dir_prof, $tlf_prof);
            $stmt->execute();

            // Auditoría
            $fecha_insc = date("d/m/Y");
            $cod_reg = $ci_prof . '-' . $ci_prof2;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Registró';
            $registro = 'Registrar, Profesor';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $stmt_aud->execute();

            echo "<script>alert('Los datos del Profesor o Usuario fueron registrados con éxito');</script>";
        } else {
            echo "<script>alert('Este Nro. de cédula ya fue registrado...!!!');</script>";
        }
        break;

    case "Buscar":
        if (empty($ci_prof)) {
            echo "<script>alert('El campo Cédula no puede estar vacío...');</script>";
            break;
        }
        if (!validar_rut($ci_prof, $ci_prof2)) {
            echo "<script>alert('El Dígito verificador del Rut no es correcto, verifique y vuelva a intentar');</script>";
            break;
        }
        $stmt = $conexion->prepare("SELECT * FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
        $stmt->bind_param("ss", $ci_prof, $ci_prof2);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $datos = $result->fetch_assoc();
            $retirado = $datos['retirado'];
            if ($retirado == 0) {
                $id_prof = $datos['id_prof'];
                $ci_prof2 = $datos['ci_prof2'];
                $nom_prof = $datos['nom_prof'];
                $dir_prof = $datos['dir_prof'];
                $tlf_prof = $datos['tlf_prof'];
            } else {
                $id_prof = $nom_prof = $dir_prof = $tlf_prof = $retirado = "";
                echo "<script>alert('Este Nro. de Cédula fue eliminado de la Base de Datos, Para Recuperarlo Vaya a la Papelera de Reciclaje...!!!');</script>";
            }
        } else {
            $id_prof = $nom_prof = $dir_prof = $tlf_prof = "";
            echo "<script>alert('Cédula No Registrada...!!!');</script>";
        }
        break;

    case "Actualizar":
        if (!validar_rut($ci_prof, $ci_prof2)) {
            echo "<script>alert('El Dígito verificador del Rut no es correcto, verifique y vuelva a intentar');</script>";
            break;
        }
        $stmt = $conexion->prepare("SELECT id_prof FROM prof WHERE id_prof = ?");
        $stmt->bind_param("i", $id_prof);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $nom_prof = ucwords($nom_prof);
            $dir_prof = strtoupper($dir_prof);
            $stmt = $conexion->prepare("UPDATE prof SET ci_prof = ?, nom_prof = ?, tlf_prof = ?, dir_prof = ? WHERE id_prof = ?");
            $stmt->bind_param("ssssi", $ci_prof, $nom_prof, $tlf_prof, $dir_prof, $id_prof);
            $stmt->execute();

            // Auditoría
            $fecha_insc = date("d/m/Y");
            $cod_reg = $ci_prof . '-' . $ci_prof2;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Actualizó';
            $registro = 'Registrar, Profesor';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $stmt_aud->execute();

            echo "<script>alert('Los datos fueron actualizados con éxito...!!!');</script>";
        } else {
            $id_prof = $nom_prof = $dir_prof = $tlf_prof = "";
            echo "<script>alert('Cédula No Registrada...!!!');</script>";
        }
        break;

    case "Eliminar":
        if (!validar_rut($ci_prof, $ci_prof2)) {
            echo "<script>alert('El Dígito verificador del Rut no es correcto, verifique y vuelva a intentar');</script>";
            break;
        }
        $stmt = $conexion->prepare("SELECT id_prof, retirado FROM prof WHERE id_prof = ?");
        $stmt->bind_param("i", $id_prof);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $datos = $result->fetch_assoc();
            if ($datos['retirado'] == 0) {
                $stmt = $conexion->prepare("UPDATE prof SET retirado = 1 WHERE id_prof = ?");
                $stmt->bind_param("i", $id_prof);
                $stmt->execute();

                // Auditoría
                $fecha_insc = date("d/m/Y");
                $cod_reg = $ci_prof . '-' . $ci_prof2;
                $ci_usu = $_SESSION["cedula"] ?? '';
                $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
                $desc_reg = 'Eliminó';
                $registro = 'Registrar, Profesor';
                $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

                $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
                $stmt_aud->execute();

                echo "<script>alert('El registro fue eliminado con éxito...!!!');</script>";
            } else {
                echo "<script>alert('El representante fue retirado...!!!');</script>";
            }
        } else {
            $id_prof = $nom_prof = $dir_prof = $tlf_prof = "";
            echo "<script>alert('Cédula No Registrada...!!!');</script>";
        }
        break;

    case "Limpiar":
        $ci_prof = $ci_prof2 = $nom_prof = $tlf_prof = $dir_prof = $id_prof = $retirado = "";
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>REGISTRAR PROFESORES</title>
<style type="text/css">
/* ...estilos sin cambios... */
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
<form id="form1" name="form1" method="post" action="registrar_prof.php">
    <div class="caja" align="center" >
        <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
        REGISTRAR PROFESOR O USUARIO
        </font>
    </div>
    <table width="599" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr align="left">
            <td width="89" height="36">Cédula:</td>
            <td width="49">
                <input name="ci_prof" type="text" id="ci_prof" value="<?php echo htmlspecialchars($ci_prof);?>" maxlength="8" size="8" />
            </td>
            <td width="6">
                <input name="ci_prof2" type="text" id="ci_prof2" value="<?php echo htmlspecialchars($ci_prof2);?>" maxlength="1" size="1" />
            </td>
            <td width="429">
                <input name="id_prof" type="hidden" id="id_prof" value="<?php echo htmlspecialchars($id_prof);?>" maxlength="8" size="20" />
                <input name="retirado" type="hidden" id="retirado" value="<?php echo htmlspecialchars($retirado);?>" maxlength="8" size="20" />
                <input name="total_buscar" type="hidden" id="total_buscar" value="<?php echo htmlspecialchars($total_buscar);?>" maxlength="8" size="20" />
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
                <input type="submit" name="button" id="button" value="Registrar" />&nbsp;
                <input type="submit" name="button" id="button" value="Buscar" />&nbsp;
                <input type="submit" name="button" id="button" value="Actualizar" />&nbsp;
                <input type="submit" name="button" id="button" value="Eliminar" onclick="return confirm('¿Está seguro que desea eliminar el registro?');" />&nbsp;
                <input type="submit" name="button" id="button" value="Limpiar" />&nbsp;
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
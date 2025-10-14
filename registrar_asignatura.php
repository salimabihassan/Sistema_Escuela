<?php
// =======================================================
// Archivo: registrar_asignatura.php
// Autor: Salim I. Abi Hassan E.
// Fecha de diseño: 01-03-2017
// Fecha de actualización PHP: 13-10-2025 (PHP 8.2)
// Descripción: Registro, consulta, actualización y eliminación de asignaturas
// =======================================================

include("conexiones.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

// Variables de sesión y POST
$id_asig = $_POST['id_asig'] ?? '';
$cod_asig = intval($_POST['cod_asig'] ?? 0);
$nom_asig = $_POST['nom_asig'] ?? '';

$g1 = $_POST['g1'] ?? '';
$g2 = $_POST['g2'] ?? '';
$g3 = $_POST['g3'] ?? '';
$g4 = $_POST['g4'] ?? '';
$g5 = $_POST['g5'] ?? '';
$g6 = $_POST['g6'] ?? '';
$g7 = $_POST['g7'] ?? '';
$g8 = $_POST['g8'] ?? '';

$total_buscar = $_POST['total_buscar'] ?? '';
$button = $_POST['button'] ?? '';
$borrado = $_POST['borrado'] ?? '';

// Función para convertir fecha MySQL a formato chileno
function fentrada($cambio) {
    $uno = substr($cambio, 0, 4);
    $dos = substr($cambio, 5, 2);
    $tres = substr($cambio, 8, 2);
    return ($tres . "/" . $dos . "/" . $uno);
}

$conexion = conectarse_escuela();

switch ($button) {
    case "Registrar":
        if ($cod_asig <= 0) {
            echo "<script>alert('Debe escribir el código de la Asignatura');</script>";
            break;
        }
        if (empty($nom_asig)) {
            echo "<script>alert('Debe escribir el nombre de la asignatura');</script>";
            break;
        }
        $p = 0;
        foreach ([$g1, $g2, $g3, $g4, $g5, $g6, $g7, $g8] as $grado) {
            if ($grado == 'SI') $p++;
        }
        if ($p < 1) {
            echo "<script>alert('Debe escoger por lo menos un grado para la asignatura');</script>";
            break;
        }
        // Verificar si ya existe
        $stmt = $conexion->prepare("SELECT id_asig FROM asignatura WHERE cod_asig = ? AND borrado = 0");
        $stmt->bind_param("i", $cod_asig);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $nom_asig = mb_strtoupper(trim($nom_asig));
            $stmt = $conexion->prepare("INSERT INTO asignatura (cod_asig, nom_asig, g1, g2, g3, g4, g5, g6, g7, g8, borrado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("isssssssss", $cod_asig, $nom_asig, $g1, $g2, $g3, $g4, $g5, $g6, $g7, $g8);
            $stmt->execute();

            // Auditoría
            $fecha_insc = date("d/m/Y");
            $cod_reg = $cod_asig;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Registró';
            $registro = 'Registrar, Asignatura';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $stmt_aud->execute();

            echo "<script>alert('La Asignatura fue registrada con éxito');</script>";
        } else {
            echo "<script>alert('Este código de asignatura ya ha sido registrado. Para modificar sus datos, ubíquelo con el botón consulta, modifique los datos y luego oprima el botón actualizar o coloque otro código para una asignatura nueva');</script>";
        }
        break;

    case "Consultar":
        if ($cod_asig <= 0) {
            echo "<script>alert('Debe escribir el código de la asignatura a consultar');</script>";
            break;
        }
        $stmt = $conexion->prepare("SELECT * FROM asignatura WHERE cod_asig = ?");
        $stmt->bind_param("i", $cod_asig);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $datos = $result->fetch_assoc();
            $borrado = $datos['borrado'];
            if ($borrado == 0) {
                $id_asig = $datos['id_asig'];
                $nom_asig = $datos['nom_asig'];
                $g1 = $datos['g1'];
                $g2 = $datos['g2'];
                $g3 = $datos['g3'];
                $g4 = $datos['g4'];
                $g5 = $datos['g5'];
                $g6 = $datos['g6'];
                $g7 = $datos['g7'];
                $g8 = $datos['g8'];
            } else {
                echo "<script>alert('Este código de asignatura fue eliminado de la Base de Datos. Para recuperarlo vaya a la Papelera de Reciclaje.');</script>";
                $nom_asig = $g1 = $g2 = $g3 = $g4 = $g5 = $g6 = $g7 = $g8 = $id_asig = $borrado = "";
            }
        } else {
            echo "<script>alert('Código no registrado');</script>";
            $nom_asig = $g1 = $g2 = $g3 = $g4 = $g5 = $g6 = $g7 = $g8 = $id_asig = $borrado = "";
        }
        break;

    case "Eliminar":
        $stmt = $conexion->prepare("SELECT borrado FROM asignatura WHERE id_asig = ?");
        $stmt->bind_param("i", $id_asig);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $datos = $result->fetch_assoc();
            if ($datos['borrado'] == 0) {
                $stmt = $conexion->prepare("UPDATE asignatura SET borrado = 1 WHERE id_asig = ?");
                $stmt->bind_param("i", $id_asig);
                $stmt->execute();

                // Auditoría
                $fecha_insc = date("d/m/Y");
                $cod_reg = $cod_asig;
                $ci_usu = $_SESSION["cedula"] ?? '';
                $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
                $desc_reg = 'Eliminó';
                $registro = 'Registrar, Asignatura';
                $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

                $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
                $stmt_aud->execute();

                echo "<script>alert('La Asignatura fue eliminada con éxito');</script>";
                $cod_asig = $nom_asig = $g1 = $g2 = $g3 = $g4 = $g5 = $g6 = $g7 = $g8 = $id_asig = $borrado = "";
            } else {
                echo "<script>alert('La asignatura ya se había eliminado');</script>";
            }
        } else {
            echo "<script>alert('Código no registrado');</script>";
            $nom_asig = $g1 = $g2 = $g3 = $g4 = $g5 = $g6 = $g7 = $g8 = $id_asig = $borrado = "";
        }
        break;

    case "Actualizar":
        $stmt = $conexion->prepare("SELECT id_asig FROM asignatura WHERE id_asig = ?");
        $stmt->bind_param("i", $id_asig);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $nom_asig = mb_strtoupper(trim($nom_asig));
            $stmt = $conexion->prepare("UPDATE asignatura SET cod_asig = ?, nom_asig = ?, g1 = ?, g2 = ?, g3 = ?, g4 = ?, g5 = ?, g6 = ?, g7 = ?, g8 = ? WHERE id_asig = ?");
            $stmt->bind_param("isssssssssi", $cod_asig, $nom_asig, $g1, $g2, $g3, $g4, $g5, $g6, $g7, $g8, $id_asig);
            $stmt->execute();

            // Auditoría
            $fecha_insc = date("d/m/Y");
            $cod_reg = $cod_asig;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Actualizó';
            $registro = 'Registrar, Asignatura';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $stmt_aud->execute();

            echo "<script>alert('Los datos fueron actualizados con éxito');</script>";
        } else {
            echo "<script>alert('Código no registrado');</script>";
            $nom_asig = $g1 = $g2 = $g3 = $g4 = $g5 = $g6 = $g7 = $g8 = $id_asig = $borrado = "";
        }
        break;

    case "Limpiar":
        $cod_asig = $nom_asig = $g1 = $g2 = $g3 = $g4 = $g5 = $g6 = $g7 = $g8 = $id_asig = $borrado = "";
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>REGISTRAR ASIGNATURA</title>
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
<form id="form1" name="form1" method="post" action="registrar_asignatura.php">
    <div class="caja" align="center" >
        <font class="sombra" color="#00366C"  face="Arial Black, Gadget, sans-serif" size="5">
        REGISTRAR ASIGNATURA 
        </font>
    </div>
    <table width="636" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <td height="29" colspan="9" align="center" bgcolor="#E9E9E9"><strong>DATOS DE LA ASIGNATURA</strong></td>
        </tr>
        <tr>
            <td height="16" bgcolor="#F9F9F9">C&oacute;digo:</td>
            <td height="16" bgcolor="#F9F9F9">
                <input name="cod_asig" type="text" id="cod_asig" value="<?php echo htmlspecialchars($cod_asig);?>" onkeyup="return ValNumero(this);" maxlength="3" size="3" />
            </td>
            <td width="74" height="16" bgcolor="#F9F9F9">
                <input name="borrado" type="hidden" id="borrado" value="<?php echo htmlspecialchars($borrado);?>" maxlength="8" size="20" />
                <input name="total_buscar" type="hidden" id="total_buscar" value="<?php echo htmlspecialchars($total_buscar);?>" maxlength="8" size="20" />
                <input name="id_asig" type="hidden" id="id_asig" value="<?php echo htmlspecialchars($id_asig);?>" maxlength="11" size="20" />
                <input type="submit" name="button" id="button" value="Consultar" />
            </td>
            <td width="26" bgcolor="#F9F9F9">&nbsp;</td>
            <td height="16" bgcolor="#F9F9F9">&nbsp;</td>
            <td height="16" bgcolor="#F9F9F9">&nbsp;</td>
            <td height="16" bgcolor="#F9F9F9">&nbsp;</td>
            <td height="16" bgcolor="#F9F9F9">&nbsp;</td>
            <td height="16" bgcolor="#F9F9F9">&nbsp;</td>
        </tr>
        <tr bgcolor="#E9E9E9">
            <td height="16">Nombre:</td>
            <td height="16" colspan="4">
                <input name="nom_asig" type="text" id="nom_asig" value="<?php echo htmlspecialchars($nom_asig);?>" size="42" maxlength="50" />
            </td>
            <td height="16" colspan="4">&nbsp;</td>
        </tr>
        <tr>
            <td height="32" colspan="9" align="center"><strong>MARQUE LOS CURSOS QUE CURSAN ESTA ASIGNATURA</strong></td>
        </tr>
        <tr bgcolor="#E9E9E9">
            <td width="70" height="34" align="center" valign="middle">1ro</td>
            <td width="76" align="center" valign="middle">2do</td>
            <td colspan="2" align="center" valign="middle">3ro</td>
            <td width="96" align="center" valign="middle">4to</td>
            <td width="60" align="center" valign="middle">5to</td>
            <td width="57" height="34" align="center" valign="middle">6to</td>
            <td width="54" height="34" align="center" valign="middle">7mo</td>
            <td width="67" height="34" align="center" valign="middle">8vo</td>
        </tr>
        <tr>
            <td align="center" bgcolor="#FFFFFF">
                <input name="g1" type="checkbox" id="g1" value="SI"<?php if ($g1=='SI') echo " checked='checked'";?> />
            </td>
            <td align="center" bgcolor="#FFFFFF">
                <input name="g2" type="checkbox" id="g2" value="SI"<?php if ($g2=='SI') echo " checked='checked'";?> />
            </td>
            <td colspan="2" align="center" bgcolor="#FFFFFF">
                <input name="g3" type="checkbox" id="g3" value="SI"<?php if ($g3=='SI') echo " checked='checked'";?> />
            </td>
            <td align="center" bgcolor="#FFFFFF">
                <input name="g4" type="checkbox" id="g4" value="SI"<?php if ($g4=='SI') echo " checked='checked'";?> />
            </td>
            <td align="center" bgcolor="#FFFFFF">
                <input name="g5" type="checkbox" id="g5" value="SI"<?php if ($g5=='SI') echo " checked='checked'";?> />
            </td>
            <td align="center" bgcolor="#FFFFFF">
                <input name="g6" type="checkbox" id="g6" value="SI"<?php if ($g6=='SI') echo " checked='checked'";?> />
            </td>
            <td align="center" bgcolor="#FFFFFF">
                <input name="g7" type="checkbox" id="g7" value="SI"<?php if ($g7=='SI') echo " checked='checked'";?> />
            </td>
            <td align="center" bgcolor="#FFFFFF">
                <input name="g8" type="checkbox" id="g8" value="SI"<?php if ($g8=='SI') echo " checked='checked'";?> />
            </td>
        </tr>
        <tr>
            <td colspan="9" align="center" bgcolor="#FFFFFF">
                <input type="submit" name="button" id="button" value="Registrar" />
                <input type="submit" name="button" id="button" value="Actualizar" />
                <input type="submit" name="button" id="button" value="Eliminar" onclick="return confirm('¿Está seguro que desea eliminar el registro?');" />
                <input type="submit" name="button" id="button" value="Limpiar" />
            </td>
        </tr>
        <tr>
            <td colspan="9" align="center" bgcolor="#E9E9E9"><strong>Nota: Verifique los datos antes de pulsar uno de los botones</strong></td>
        </tr>
    </table>
</form>
</center>
</body>
</html>
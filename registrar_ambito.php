<?php
// =======================================================
// Archivo: registrar_ambito.php
// Autor: Salim I. Abi Hassan E.
// Fecha de diseño: 01-03-2017
// Fecha de actualización PHP: 13-10-2025 (PHP 8.2)
// Descripción: Registro, consulta, actualización y eliminación de ámbitos
// =======================================================

include("conexiones.php"); // Conexión a la base de datos
header('Content-Type: text/html; charset=utf-8'); // Codificación UTF-8
session_start(); // Iniciar sesión

// ===============================
// Variables de sesión y POST
// ===============================
$id_ambito = $_POST['id_ambito'] ?? '';
$id_amb2 = $_POST['id_amb2'] ?? '';
$cod_ambito = intval($_POST['cod_ambito'] ?? 0); // Forzar a entero
$nom_ambito = $_POST['nom_ambito'] ?? '';
$total_buscar = $_POST['total_buscar'] ?? '';
$button = $_POST['button'] ?? '';
$borrado = $_POST['borrado'] ?? '';

// ===============================
// Función para convertir fecha MySQL a formato chileno
// ===============================
function fentrada($cambio) {
    $uno = substr($cambio, 0, 4);
    $dos = substr($cambio, 5, 2);
    $tres = substr($cambio, 8, 2);
    return ($tres . "/" . $dos . "/" . $uno);
}

// ===============================
// Conexión a la base de datos
// ===============================
$conexion = conectarse_escuela();

// ===============================
// Consulta para cargar ámbito seleccionado
// ===============================
// Solo recarga los datos si NO se está actualizando
if ($id_amb2 && $button != "Actualizar") {
    $consulta_consultar = $conexion->prepare("SELECT * FROM ambito WHERE id_ambito = ?");
    $consulta_consultar->bind_param("i", $id_amb2);
    $consulta_consultar->execute();
    $resultado_consultar = $consulta_consultar->get_result();
    if ($resultado_consultar->num_rows > 0) {
        $datos_consultar = $resultado_consultar->fetch_assoc();
        $borrado = $datos_consultar['borrado'];
        if ($borrado == 0) {
            $cod_ambito = $datos_consultar['cod_ambito'];
            $nom_ambito = $datos_consultar['nom_ambito'];
            $id_ambito = $datos_consultar['id_ambito'];
        }
    }
}

// ===============================
// Lógica principal según el botón presionado
// ===============================
switch ($button) {
    case "Registrar":
        // Validar datos
        if ($cod_ambito <= 0) {
            echo "<script>alert('Debe escribir el código del Ámbito (número entero positivo)');</script>";
            break;
        }
        if (empty($nom_ambito)) {
            echo "<script>alert('Debe escribir la descripción del ámbito');</script>";
            break;
        }

        // Consultar si el ámbito ya existe (solo los no borrados)
        $consulta_consultar = $conexion->prepare("SELECT id_ambito FROM ambito WHERE cod_ambito = ? AND borrado = 0");
        $consulta_consultar->bind_param("i", $cod_ambito);
        $consulta_consultar->execute();
        $resultado_consultar = $consulta_consultar->get_result();

        if ($resultado_consultar->num_rows == 0) {
            $nom_ambito = trim($nom_ambito); // Mantener tildes y ñ
            $consulta_insert = $conexion->prepare("INSERT INTO ambito (cod_ambito, nom_ambito, borrado) VALUES (?, ?, 0)");
            $consulta_insert->bind_param("is", $cod_ambito, $nom_ambito);
            $consulta_insert->execute();

            // Auditoría
            $fecha_insc = date("d/m/Y");
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $cod_reg = $cod_ambito;
            $desc_reg = 'Registró';
            $registro = 'Registrar, Ambito';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $sql_aud->execute();

            echo "<script>alert('El ámbito fue registrado con éxito');</script>";
        } else {
            echo "<script>alert('Este código de ámbito ya ha sido registrado. Para modificar sus datos, ubíquelo con el botón consulta, modifique los datos y luego oprima el botón actualizar o coloque otro código para un ámbito nuevo');</script>";
        }
        break;

    case "Eliminar":
        $consulta_buscar = $conexion->prepare("SELECT * FROM ambito WHERE id_ambito = ?");
        $consulta_buscar->bind_param("i", $id_ambito);
        $consulta_buscar->execute();
        $resultado_buscar = $consulta_buscar->get_result();
        $datos_consultar = $resultado_buscar->fetch_assoc();
        $cod_ambito = $datos_consultar['cod_ambito'] ?? 0;

        if ($resultado_buscar->num_rows > 0) {
            if ($borrado == 0) {
                $consulta_update = $conexion->prepare("UPDATE ambito SET borrado = 1 WHERE id_ambito = ?");
                $consulta_update->bind_param("i", $id_ambito);
                $consulta_update->execute();

                // Auditoría
                $fecha_insc = date("d/m/Y");
                $ci_usu = $_SESSION["cedula"] ?? '';
                $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
                $cod_reg = $cod_ambito;
                $desc_reg = 'Eliminó';
                $registro = 'Registrar, Ambito';
                $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

                $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
                $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
                $sql_aud->execute();

                echo "<script>alert('Ámbito fue eliminado con éxito');</script>";
                $cod_ambito = "";
                $nom_ambito = "";
                $id_ambito = "";
                $borrado = "";
            } else {
                echo "<script>alert('Ámbito ya se había eliminado');</script>";
            }
        } else {
            echo "<script>alert('Código no registrado');</script>";
            $nom_ambito = "";
            $id_ambito = "";
            $borrado = "";
        }
        break;

    case "Actualizar":
        if (empty($id_ambito)) {
            echo "<script>alert('Debe seleccionar un ámbito para actualizar');</script>";
            break;
        }
        if ($cod_ambito <= 0 || empty($nom_ambito)) {
            echo "<script>alert('Código y nombre de ámbito son obligatorios');</script>";
            break;
        }
        $nom_ambito = trim($nom_ambito); // Mantener tildes y ñ
        $consulta_update = $conexion->prepare("UPDATE ambito SET cod_ambito = ?, nom_ambito = ? WHERE id_ambito = ?");
        $consulta_update->bind_param("isi", $cod_ambito, $nom_ambito, $id_ambito);

        if ($consulta_update->execute()) {
            if ($consulta_update->affected_rows > 0) {
                echo "<script>alert('Los datos fueron actualizados con éxito');</script>";
            } else {
                echo "<script>alert('La actualización no modificó ningún registro.');</script>";
            }
            // Auditoría
            $fecha_insc = date("d/m/Y");
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $cod_reg = $cod_ambito;
            $desc_reg = 'Actualizó';
            $registro = 'Registrar, Ambito';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $sql_aud->execute();
        } else {
            echo "<script>alert('Error al actualizar el ámbito: " . $consulta_update->error . "');</script>";
        }
        break;

    case "Limpiar":
        $cod_ambito = "";
        $nom_ambito = "";
        $id_ambito = "";
        $borrado = "";
        $id_amb2 = ""; // Limpiar también el select
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>REGISTRAR ÁMBITO</title>
<style type="text/css">
/* ...estilos sin cambios... */
</style>
<script type="text/javascript">
// ===============================
// Funciones JS para validación numérica
// ===============================
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
<form id="form1" name="form1" method="post" action="registrar_ambito.php">
    <div class="caja" align="center" >
        <font class="sombra" color="#00366C"  face="Arial Black, Gadget, sans-serif" size="5">
        REGISTRAR AMBITO 
        </font>
    </div>
    <table width="636" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <td height="29" colspan="4" align="center" bgcolor="#E9E9E9"><strong>DATOS DEL AMBITO</strong></td>
        </tr>
        <tr>
            <td width="95" height="16" bgcolor="#FFFFFF">C&oacute;digo:</td>
            <td width="18" height="16" bgcolor="#FFFFFF">
                <input name="cod_ambito" type="text" id="cod_ambito" value="<?php echo htmlspecialchars($cod_ambito);?>" onkeyup="return ValNumero(this);" maxlength="3" size="3" />
            </td>
            <td height="16" bgcolor="#FFFFFF">
                <input name="borrado" type="hidden" id="borrado" value="<?php echo htmlspecialchars($borrado);?>" maxlength="8" size="20" />
                <input name="total_buscar" type="hidden" id="total_buscar" value="<?php echo htmlspecialchars($total_buscar);?>" maxlength="8" size="20" />
                <input name="id_ambito" type="hidden" id="id_ambito" value="<?php echo htmlspecialchars($id_ambito);?>" maxlength="11" size="20" />
                <?php
                // ===============================
                // Consulta de ámbitos activos para el select
                // ===============================
                $consulta_amb = $conexion->prepare("SELECT * FROM ambito WHERE borrado=0 ORDER BY cod_ambito");
                $consulta_amb->execute();
                $resultado_amb = $consulta_amb->get_result();
                ?>
                <select name="id_amb2" id="cod_amb2" onChange="submit();">
                    <option value="0"></option>
                    <?php 
                    while ($row = $resultado_amb->fetch_assoc()) {
                        $selected = ($id_amb2 == $row['id_ambito']) ? "selected='selected'" : "";
                        echo "<option value='{$row['id_ambito']}' $selected>{$row['nom_ambito']}</option>";
                    }
                    ?>
                </select>
            </td>
            <td width="95" height="16" bgcolor="#FFFFFF">&nbsp;</td>
        </tr>
        <tr bgcolor="#E9E9E9">
            <td height="16">Nombre:</td>
            <td height="16" colspan="3">
                <textarea name="nom_ambito" cols="70" rows="3" id="nom_ambito"><?php echo htmlspecialchars($nom_ambito);?></textarea>
            </td>
        </tr>
        <tr>
            <td height="24" colspan="4" align="center" bgcolor="#FFFFFF">
                <input type="submit" name="button" id="button" value="Registrar" />
                <input type="submit" name="button" id="button" value="Actualizar" />
                <input type="submit" name="button" id="button" value="Eliminar" onclick="return confirm('¿Está seguro que desea eliminar el registro?');" />
                <input type="submit" name="button" id="button" value="Limpiar" />
            </td>
        </tr>
        <tr>
            <td colspan="4" align="center" bgcolor="#E9E9E9"><strong>Nota: Verifique los datos ante de pulsar uno de los botones</strong></td>
        </tr>
    </table>
</form>
</center>
</body>
</html>
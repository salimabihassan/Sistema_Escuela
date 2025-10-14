<?php
// =======================================================
// Archivo: registrar_curso.php
// Autor: Salim I. Abi Hassan E.
// Fecha de actualización PHP: 13-10-2025 (PHP 8.2)
// Descripción: Registro, consulta, actualización y eliminación de cursos
// =======================================================

include("conexiones.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

$conexion = conectarse_escuela();

// Variables POST
$id_curso = $_POST['id_curso'] ?? '';
$id_prof2 = $_POST['id_prof2'] ?? '';
$cod_curso = $_POST['cod_curso'] ?? '';
$ci_prof2 = trim($_POST['ci_prof2'] ?? '');
$ci_prof = $_POST['ci_prof'] ?? '';
$nom_prof = $_POST['nom_prof'] ?? '';
$grado = $_POST['grado'] ?? '';
$status = $_POST['status'] ?? '';
$turno = $_POST['turno'] ?? '';
$peri = $_POST['peri'] ?? '';
$button = $_POST['button'] ?? '';
$retirado = $_POST['retirado'] ?? '';

// Generar código de curso
if ($grado && $peri) {
    $cod_curso = strtoupper($grado . $peri);
}

// Buscar datos del profesor seleccionado
if ($id_prof2) {
    $stmt = $conexion->prepare("SELECT * FROM prof WHERE id_prof = ?");
    $stmt->bind_param("i", $id_prof2);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $datos = $result->fetch_assoc();
        $retirado = $datos['retirado'];
        if ($retirado == 0) {
            $ci_prof = $datos['ci_prof'];
            $ci_prof2 = $datos['ci_prof2'];
            
        }
    }
}

switch ($button) {
    case "Registrar":
        if (empty($cod_curso)) $cod_curso = strtoupper($grado . $peri);
        if (empty($ci_prof)) {
            echo "<script>alert('El campo Cédula no puede estar vacío...');</script>";
            break;
        }
        if (empty($ci_prof2)) {
            echo "<script>alert('Debe escribir el código de validación de la cédula');</script>";
            break;
        }
        if (empty($grado)) {
            echo "<script>alert('El campo grado no puede estar vacío...');</script>";
            break;
        }
        if (empty($peri)) {
            echo "<script>alert('El campo Periodo no puede estar vacío...');</script>";
            break;
        }

        // Verificar si el curso ya existe
        $stmt = $conexion->prepare("SELECT id_curso FROM curso WHERE cod_curso = ?");
        $stmt->bind_param("s", $cod_curso);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Verificar si el profesor ya tiene curso activo
            $stmt2 = $conexion->prepare("SELECT id_curso FROM curso WHERE ci_prof = ? AND ci_prof2 = ? AND activo = 1");
            $stmt2->bind_param("ss", $ci_prof, $ci_prof2);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            if ($result2->num_rows == 0) {
                // Verificar si el profesor existe
                $stmt3 = $conexion->prepare("SELECT id_prof FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
                $stmt3->bind_param("ss", $ci_prof, $ci_prof2);
                $stmt3->execute();
                $result3 = $stmt3->get_result();

                if ($result3->num_rows > 0) {
                    $stmt4 = $conexion->prepare("INSERT INTO curso (cod_curso, ci_prof2, ci_prof, grado, periodo, activo) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmt4->bind_param("sssss", $cod_curso, $ci_prof2, $ci_prof, $grado, $peri);
                    $stmt4->execute();

                    // Auditoría
                    $fecha_insc = date("d/m/Y");
                    $cod_reg = $cod_curso;
                    $ci_usu = $_SESSION["cedula"] ?? '';
                    $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
                    $desc_reg = 'Registró';
                    $registro = 'Registrar, Curso';
                    $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

                    $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
                    $stmt_aud->execute();

                    echo "<script>alert('El curso fue almacenado con éxito');</script>";
                } else {
                    echo "<script>alert('La cédula del profesor no está registrada...');</script>";
                }
            } else {
                echo "<script>alert('Este profesor ya tiene un curso asignado activo. Si quiere asignarle un curso nuevo, inactiva el anterior.');</script>";
            }
        } else {
            echo "<script>alert('Este código de curso ya fue registrado...');</script>";
        }
        break;

    case "Buscar":
        if (empty($cod_curso)) {
            echo "<script>alert('Debe seleccionar el grado, el periodo y luego oprimir el botón Generar');</script>";
            break;
        }
        $stmt = $conexion->prepare("SELECT * FROM curso WHERE cod_curso = ?");
        $stmt->bind_param("s", $cod_curso);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $datos = $result->fetch_assoc();
            $id_curso = $datos['id_curso'];
            $ci_prof2 = $datos['ci_prof2'];
            $ci_prof = $datos['ci_prof'];
            $grado = $datos['grado'];
            $peri = $datos['periodo'];
            $activo = $datos['activo'];
            $status = ($activo == 1) ? 'Curso Activo' : 'Curso Inactivo';

            // Buscar nombre del profesor
            $stmt2 = $conexion->prepare("SELECT id_prof FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
            $stmt2->bind_param("ss", $ci_prof, $ci_prof2);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if ($result2->num_rows > 0) {
                $datos2 = $result2->fetch_assoc();
                $id_prof2 = $datos2['id_prof'];
            }
        } else {
            $id_curso = $ci_prof = $ci_prof2 = $id_prof2 = $grado = $peri = "";
            echo "<script>alert('Código curso no registrado...');</script>";
        }
        break;

    case "Actualizar o Reactivar":
        if (empty($cod_curso)) $cod_curso = strtoupper($grado . $peri);
        if (empty($ci_prof)) {
            echo "<script>alert('El campo Cédula no puede estar vacío...');</script>";
            break;
        }
        if (empty($ci_prof2)) {
            echo "<script>alert('Debe escribir el código de validación de la cédula');</script>";
            break;
        }
        if (empty($grado)) {
            echo "<script>alert('El campo grado no puede estar vacío...');</script>";
            break;
        }
        if (empty($peri)) {
            echo "<script>alert('El campo Periodo no puede estar vacío...');</script>";
            break;
        }
        $stmt = $conexion->prepare("SELECT id_curso FROM curso WHERE cod_curso = ?");
        $stmt->bind_param("s", $cod_curso);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt2 = $conexion->prepare("UPDATE curso SET ci_prof = ?, ci_prof2 = ?, activo = 1 WHERE cod_curso = ?");
            $stmt2->bind_param("sss", $ci_prof, $ci_prof2, $cod_curso);
            $stmt2->execute();

            $status = 'Curso Activo';
            $fecha_insc = date("d/m/Y");
            $cod_reg = $cod_curso;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Actualizó';
            $registro = 'Registrar, Curso';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $stmt_aud->execute();

            echo "<script>alert('Los datos fueron actualizados con éxito...');</script>";
        } else {
            $id_curso = $id_prof2 = "";
            echo "<script>alert('Este código de curso no existe...');</script>";
        }
        break;

    case "Inactivar":
        $stmt = $conexion->prepare("SELECT id_curso FROM curso WHERE cod_curso = ?");
        $stmt->bind_param("s", $cod_curso);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt2 = $conexion->prepare("UPDATE curso SET activo = 0 WHERE cod_curso = ?");
            $stmt2->bind_param("s", $cod_curso);
            $stmt2->execute();

            $status = 'Curso Inactivo';
            $fecha_insc = date("d/m/Y");
            $cod_reg = $cod_curso;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Inactivó';
            $registro = 'Registrar, Curso';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $stmt_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $stmt_aud->execute();

            $id_curso = $cod_curso = $ci_prof2 = $ci_prof = $id_prof2 = $grado = $turno = $peri = "";
            echo "<script>alert('El curso fue inactivado con éxito...');</script>";
        } else {
            echo "<script>alert('Cédula no registrada...');</script>";
        }
        break;

    case "Limpiar":
        $id_curso = $cod_curso = $ci_prof2 = $ci_prof = $id_prof2 = $grado = $peri = "";
        break;

    case "Generar":
        if (empty($peri)) {
            echo "<script>alert('Debe llenar el campo Periodo para poder generar el código del curso');</script>";
            break;
        }
        $cod_curso = strtoupper($grado . $peri);
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>REGISTRAR CURSO</title>
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
<form id="form1" name="form1" method="post" action="registrar_curso.php">
  <div class="caja" align="center" >
    <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
      ASIGNAR CURSO 
    </font>
  </div>
  <table width="599" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr bgcolor="#F3F3F3">
      <td height="33" align="right">Curso:</td>
      <td height="33" align="left">
        <select name="grado" size="1" id="grado">
          <option value=" "></option>
          <?php for ($i = 1; $i <= 8; $i++): ?>
            <option value="<?php echo $i; ?>"<?php if ($grado == "$i") echo " selected='selected'"; ?>><?php echo $i . ($i == 1 ? "ro" : ($i == 2 ? "do" : ($i == 3 ? "ro" : ($i == 4 ? "to" : ($i == 5 ? "to" : ($i == 6 ? "to" : ($i == 7 ? "mo" : "vo"))))))); ?></option>
          <?php endfor; ?>
        </select>
      </td>
      <td align="left" bgcolor="#F3F3F3">Periodo Escolar: </td>
      <td colspan="3" align="left" bgcolor="#F3F3F3">
        <input name="peri" type="text" id="peri" value="<?php echo htmlspecialchars($peri); ?>" size="5" maxlength="4" />
      </td>
    </tr>
    <tr>
      <td width="100" height="36" align="left">Cédula Maestro:</td>
      <td align="left"><input name="ci_prof" type="text" id="ci_prof" value="<?php echo htmlspecialchars($ci_prof); ?>" maxlength="9" size="9" /></td>
      <td align="left">
        <input name="ci_prof2" type="text" id="ci_prof2" value="<?php echo htmlspecialchars($ci_prof2); ?>" maxlength="1" size="1" />
        <input name="retirado" type="hidden" id="retirado" value="<?php echo htmlspecialchars($retirado); ?>" maxlength="8" size="20" />
      </td>
      <td width="80" align="left">&nbsp;</td>
      <td width="69" align="left">&nbsp;</td>
      <td align="left">&nbsp;</td>
    </tr>
    <tr bgcolor="#F3F3F3">
      <td height="34" align="left">Nombre Maestro:</td>
      <td colspan="5" align="left" bgcolor="#F3F3F3">
        <?php
        $consulta_prof = $conexion->prepare("SELECT * FROM prof WHERE retirado = 0 ORDER BY nom_prof");
        $consulta_prof->execute();
        $resultado_prof = $consulta_prof->get_result();
        ?>
        <select name="id_prof2" id="id_prof" onChange="submit();">
          <option value="0"></option>
          <?php while ($row = $resultado_prof->fetch_assoc()): ?>
            <option value="<?php echo $row['id_prof']; ?>"<?php if ($id_prof2 == $row['id_prof']) echo " selected='selected'"; ?>><?php echo $row['nom_prof']; ?></option>
          <?php endwhile; ?>
        </select>
        <input name="id_prof" type="hidden" id="id_prof" value="<?php echo htmlspecialchars($id_prof2); ?>" maxlength="8" size="20" />
      </td>
    </tr>
    <tr>
      <td height="32" align="left">Código Curso:</td>
      <td width="63" align="right"><input name="cod_curso" type="text" id="cod_curso" value="<?php echo htmlspecialchars($cod_curso); ?>" size="7" maxlength="7" readonly /></td>
      <td width="117" align="right">&nbsp;</td>
      <td colspan="2" align="left">Estatus: <?php echo htmlspecialchars($status); ?></td>
      <td width="132" align="left">&nbsp;</td>
    </tr>
    <tr>
      <td height="41" colspan="6" align="center" bgcolor="#F3F3F3">
        <input type="submit" name="button" id="button" value="Registrar" />&nbsp;
        <input type="submit" name="button" id="button" value="Buscar" />&nbsp;
        <input type="submit" name="button" id="button" value="Actualizar o Reactivar" />&nbsp;
        <input type="submit" name="button" id="button" value="Inactivar" onclick="return confirm('¿Está seguro que desea inactivar el curso?');" />&nbsp;
        <input type="submit" name="button" id="button" value="Limpiar" />&nbsp;
      </td>
    </tr>
    <tr>
      <td colspan="6" align="center" bgcolor="#E9E9E9"><strong>Nota: Verifique los datos antes de pulsar uno de los botones</strong></td>
    </tr>
  </table>
</form>
</center>
</body>
</html>
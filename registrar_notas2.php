<?php
/**
 * MÓDULO: Registro de Notas del Segundo Semestre
 * 
 * Propósito:
 *   Permite a los docentes consultar, ingresar y actualizar las notas del segundo semestre
 *   de los alumnos, por curso, periodo y asignatura.
 * 
 * Flujo de uso:
 *   1. El usuario selecciona: grado, periodo y asignatura.
 *   2. Al hacer clic en "Consultar", se carga una tabla con los alumnos del curso.
 *      - Si ya existen notas, se muestran.
 *      - Si no, se muestra una tabla vacía para llenar.
 *   3. El usuario puede:
 *        - Ingresar/modificar notas
 *        - Hacer clic en "Calcular" para obtener promedios
 *        - Hacer clic en "Guardar" para persistir los cambios
 * 
 * Requisitos:
 *   - Sesión activa con: nombre_apellido, cedula, ci_usu2, grado, nivel_intranet
 *   - Base de datos 'escuela' con tablas: alumno, asignatura, curso, notas{periodo}
 *   - Soporte UTF-8 en la base de datos (ver instrucciones al final)
 * 
 * Versión: 1.0
 * PHP: 8.2.12
 * Autor: Sistema Escolar
 */

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$required_session = ['nombre_apellido', 'cedula', 'ci_usu2', 'grado', 'nivel_intranet'];
foreach ($required_session as $key) {
    if (!isset($_SESSION[$key])) {
        die("Acceso no autorizado. Inicie sesión.");
    }
}
$nivel = $_SESSION["nivel_intranet"];

function esc($str, $conn) {
    return mysqli_real_escape_string($conn, trim($str));
}

$conexion = conectarse_escuela();
if (!$conexion) {
    die("Error de conexión a la base de datos.");
}

$ci_alu = $ci_alu2 = $nom_alu = $grado = $guarda = $asig = $periodo = '';
$cod_asig = $prom_r = $btnaccion = '';
$total_alu = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ci_alu = $_POST['ci_alu'] ?? '';
    $ci_alu2 = $_POST['ci_alu2'] ?? '';
    $nom_alu = $_POST['nom_alu'] ?? '';
    $grado = isset($_POST['grado']) ? esc($_POST['grado'], $conexion) : '';
    $guarda = (int)($_POST['guarda'] ?? 0);
    $asig = isset($_POST['asig']) ? (int)$_POST['asig'] : 0;
    $periodo = isset($_POST['periodo']) ? esc($_POST['periodo'], $conexion) : '';
    $cod_asig = $_POST['cod_asig'] ?? '';
    $cod_curso = $grado . $periodo;
    $prom_r = $_POST['prom_r'] ?? [];
    $total_alu = (int)($_POST['total_alu'] ?? 0);
    $total_periodo = $_POST['total_periodo'] ?? '';
    $btnaccion = $_POST['btnaccion'] ?? '';
}

$fecha_insc = date("d/m/Y");

$nota = [];
$id_notas = [];
$prom = [];
$txtci_alu = [];
$txtci_alua = [];

if ($btnaccion === 'Calcular') {
    if (empty($asig)) {
        echo '<script>alert("Debe seleccionar una asignatura");</script>';
    } else {
        $consulta_asig = "SELECT cod_asig FROM asignatura WHERE id_asig = '$asig' AND borrado = 0";
        $resultado_asig = mysqli_query($conexion, $consulta_asig);
        if ($resultado_asig && mysqli_num_rows($resultado_asig) > 0) {
            $datos_asig = mysqli_fetch_array($resultado_asig);
            $cod_asig = $datos_asig['cod_asig'];
        } else {
            echo '<script>alert("Asignatura no válida");</script>';
            exit;
        }

        $y = 0;
        for ($t = 1; $t <= $total_alu; $t++) {
            $p = 0;
            $prom[$t] = 0;
            for ($i = 0; $i < 10; $i++) {
                $nota_val = floatval($_POST["nota{$t}{$i}"] ?? 0);
                $id_notas[$t][$i] = $_POST["id_notas{$t}{$i}"] ?? '';
                if ($nota_val >= 1 && $nota_val < 71) {
                    if ($nota_val > 7) $nota_val /= 10;
                    $p++;
                    $prom[$t] += $nota_val;
                } elseif ($nota_val >= 1) {
                    if ($y < 1) {
                        echo "<script>alert('Las notas no deben ser menor que 1 punto, ni exceder de 7 puntos, Nota Nro. {$t}{$i}');</script>";
                        $y++;
                    }
                }
                $nota[$t][$i] = ($nota_val < 1) ? "" : number_format($nota_val, 1, '.', '');
            }
            if ($p > 0) {
                $prom[$t] = number_format($prom[$t] / $p, 1, '.', '');
            } else {
                $prom[$t] = "";
            }
            $prom_r[$t] = $prom[$t];
            if ($cod_asig == 100 && $prom[$t] !== "") {
                $val = (float)$prom[$t];
                if ($val >= 1 && $val <= 3.9) $prom[$t] = 'I';
                elseif ($val >= 4 && $val <= 4.9) $prom[$t] = 'S';
                elseif ($val >= 5 && $val <= 5.9) $prom[$t] = 'B';
                elseif ($val >= 6 && $val <= 7) $prom[$t] = 'MB';
            }
        }
    }
}

switch ($btnaccion) {
    case "Consultar":
        if (empty($grado)) {
            echo '<script>alert("Debe Escoger un grado para continuar");</script>';
            break;
        }
        if (empty($periodo)) {
            echo '<script>alert("Debe Escribir el Periodo o Año Escolar");</script>';
            break;
        }
        $guarda = 0;

        $consulta_asig = "SELECT cod_asig FROM asignatura WHERE id_asig = '$asig' AND borrado = 0";
        $resultado_asig = mysqli_query($conexion, $consulta_asig);
        if (!$resultado_asig || mysqli_num_rows($resultado_asig) == 0) {
            echo '<script>alert("Asignatura no válida");</script>';
            break;
        }
        $datos_asig = mysqli_fetch_array($resultado_asig);
        $cod_asig = $datos_asig['cod_asig'];

        $cod_curso = $grado . $periodo;
        $consulta_nota = "SELECT * FROM notas{$periodo} WHERE cod_curso = '$cod_curso' AND cod_asig = '$cod_asig' AND semestre = '2'";
        $resultado_nota = mysqli_query($conexion, $consulta_nota);
        $total_nota = mysqli_num_rows($resultado_nota);

        if ($total_nota <= 0) {
            for ($t = 1; $t <= $total_alu; $t++) {
                for ($i = 0; $i < 10; $i++) {
                    $nota[$t][$i] = "";
                }
            }
            $consulta_curso = "SELECT * FROM curso WHERE grado = '$grado' AND periodo = '$periodo' AND activo = '1'";
            $resultado_curso = mysqli_query($conexion, $consulta_curso);
            if (mysqli_num_rows($resultado_curso) < 1) {
                echo '<script>alert("El Curso que introdujo no existe en la base de datos. Debe ir al módulo registrar curso y crearlo...");</script>';
                break;
            }
        } else {
            echo '<script>alert("Este Curso ya tiene registrado un corte de notas... Puede modificarlos y luego guardar.");</script>';
            $consulta_alu2 = "SELECT ci_alu, ci_alu2, nom_alu FROM alumno WHERE grado = '$grado' AND periodo = '$periodo' AND borrado = '0' ORDER BY nom_alu";
            $resultado_alu2 = mysqli_query($conexion, $consulta_alu2);
            $t = 1;
            while ($datos_alu2 = mysqli_fetch_array($resultado_alu2)) {
                $ci_a = $datos_alu2['ci_alu'];
                $ci_b = $datos_alu2['ci_alu2'];
                $consulta_nota2 = "SELECT * FROM notas{$periodo} WHERE cod_curso = '$cod_curso' AND cod_asig = '$cod_asig' AND semestre = '2' AND ci_alu = '$ci_a' AND ci_alu2 = '$ci_b' ORDER BY id_notas";
                $resultado_nota2 = mysqli_query($conexion, $consulta_nota2);
                $i = 0;
                while ($datos_nota2 = mysqli_fetch_array($resultado_nota2)) {
                    $nota[$t][$i] = $datos_nota2['nota'] != '' ? number_format((float)$datos_nota2['nota'], 1, '.', '') : '';
                    $prom[$t] = $datos_nota2['nota_prom'] != '' ? number_format((float)$datos_nota2['nota_prom'], 1, '.', '') : '';
                    $id_notas[$t][$i] = $datos_nota2['id_notas'];
                    if (++$i >= 10) break;
                }
                while ($i < 10) {
                    $nota[$t][$i] = "";
                    $id_notas[$t][$i] = "";
                    $i++;
                }
                $t++;
            }
            $total_alu = $t - 1;
        }
        break;

    case "Guardar":
        if (empty($grado) || empty($periodo) || empty($asig)) {
            echo '<script>alert("Complete todos los campos obligatorios: grado, periodo y asignatura.");</script>';
            break;
        }
        $consulta_asig = "SELECT cod_asig FROM asignatura WHERE id_asig = '$asig' AND borrado = 0";
        $resultado_asig = mysqli_query($conexion, $consulta_asig);
        if (!$resultado_asig || mysqli_num_rows($resultado_asig) == 0) {
            echo '<script>alert("Asignatura no válida");</script>';
            break;
        }
        $datos_asig = mysqli_fetch_array($resultado_asig);
        $cod_asig = $datos_asig['cod_asig'];
        $cod_curso = $grado . $periodo;
        $guarda++;

        $ci_usu = esc($_SESSION["cedula"], $conexion);
        $ci_usu2 = esc($_SESSION["ci_usu2"], $conexion);
        $FechaMySQL = date("Y-m-d");

        $x = 0;
        for ($t = 1; $t <= $total_alu; $t++) {
            $prom_t = $_POST["prom{$t}"] ?? '';
            $prom_r_t = $_POST["prom_r{$t}"] ?? '';
            $txtci_alu_t = esc($_POST["txtci_alu{$t}"] ?? '', $conexion);
            $txtci_alua_t = esc($_POST["txtci_alua{$t}"] ?? '', $conexion);

            for ($i = 0; $i < 10; $i++) {
                $nota_val = floatval($_POST["nota{$t}{$i}"] ?? 0);
                $id_nota = $_POST["id_notas{$t}{$i}"] ?? '';
                $nota_formateada = ($nota_val < 1) ? "NULL" : "'" . number_format($nota_val, 1, '.', '') . "'";
                $promedio_usar = ($cod_asig == 100) ? ($prom_r_t ?: 'NULL') : ($prom_t ?: 'NULL');
                $promedio_usar = is_numeric($promedio_usar) ? "'" . $promedio_usar . "'" : "NULL";

                if (!empty($id_nota)) {
                    $consulta_consultar2 = "SELECT final FROM notas{$periodo} WHERE id_notas = '" . esc($id_nota, $conexion) . "'";
                    $res2 = mysqli_query($conexion, $consulta_consultar2);
                    if ($res2 && mysqli_num_rows($res2) > 0) {
                        $row = mysqli_fetch_assoc($res2);
                        if ($row['final'] == 0) {
                            $upd = "UPDATE notas{$periodo} SET nota_prom = $promedio_usar, nota = $nota_formateada WHERE id_notas = '" . esc($id_nota, $conexion) . "'";
                            mysqli_query($conexion, $upd);
                        } else {
                            if ($x < 1) {
                                echo '<script>alert("Algunos registros no se pueden modificar porque ya se imprimió el informe...");</script>';
                                $x++;
                            }
                        }
                    }
                } else {
                    if ($nota_val >= 1) {
                        $insert = "INSERT INTO notas{$periodo} (ci_alu, ci_alu2, cod_asig, cod_curso, periodo, semestre, nota_prom, nota, fecha) 
                                   VALUES ('$txtci_alu_t', '$txtci_alua_t', '$cod_asig', '$cod_curso', '$periodo', '2', $promedio_usar, $nota_formateada, '$FechaMySQL')";
                        mysqli_query($conexion, $insert);
                    }
                }
            }
        }
        echo '<script>alert("Los datos fueron actualizados con éxito...!!!");</script>';
        break;
}

$consulta_alu = "";
$total_alu = 0;
if (!empty($grado) && !empty($periodo) && $asig > 0) {
    $consulta_alu = "SELECT ci_alu, ci_alu2, nom_alu FROM alumno WHERE grado = '$grado' AND periodo = '$periodo' AND borrado = '0' ORDER BY nom_alu";
    $resultado_alu = mysqli_query($conexion, $consulta_alu);
    $total_alu = mysqli_num_rows($resultado_alu);
} else {
    $resultado_alu = false;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>INFORME PARCIAL DE NOTAS 2do SEMESTRE</title>
<style type="text/css">
#apDiv1 { position:absolute; width:956px; height:302px; z-index:1; left: 169px; top: 180px; }
#apDiv2 { position:absolute; width:116px; height:47px; z-index:2; left: 201px; top: 182px; }
body,td,th { font-family: Verdana, Geneva, sans-serif; font-size: 12px; }
body { margin:0; }
a { text-decoration: none; }
.sombra{ text-shadow: 0.05em 0.05em 0.03em #000; }
.caja { 
    box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    padding: 5px;
    background:#F3F3F3;
    margin:5px;
    width:1024px;
}
</style>
<script>
function ValNumero(Control) {
    Control.value = Control.value.replace(/[^0-9.]/g, '');
}
function checkSubmit() {
    if (!window.statSend) {
        window.statSend = true;
        return true;
    } else {
        alert("El formulario ya se está enviando...");
        return false;
    }
}
</script>
</head>
<body>
<center>
<form id="form1" name="form1" method="post" onSubmit="return checkSubmit();">
<div class="caja">
  <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
    REGISTRAR NOTAS 2do SEMESTRE
  </font>
  Fecha: <?php echo htmlspecialchars($fecha_insc); ?>
  <input name="fecha_insc" type="hidden" value="<?php echo htmlspecialchars($fecha_insc); ?>" />
</div>
<table width="599" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr align="left">
    <td height="26" colspan="6" bgcolor="#E9E9E9"><strong>INTRODUZCA LOS DATOS DEL ALUMNO:</strong></td>
  </tr>
  <tr align="left">
    <td width="126" height="16" align="right" bgcolor="#F9F9F9"><strong>Curso:</strong></td>
    <td width="63" bgcolor="#F9F9F9">
      <select name="grado" size="1" onChange="submit();" id="grado">
        <option value=""></option>
        <?php
        $grados = [];
        if ($_SESSION["nivel_intranet"] == 0) {
            for ($g = 1; $g <= 8; $g++) {
                if ($_SESSION["grado"] == $g) $grados[] = $g;
            }
        } else {
            $grados = range(1, 8);
        }
        foreach ($grados as $g) {
            $selected = ($grado == $g) ? "selected='selected'" : "";
            $suffix = ($g == 1) ? 'ro' : (($g == 2) ? 'do' : (($g == 3) ? 'ro' : 'to'));
            if ($g == 7) $suffix = 'mo';
            if ($g == 8) $suffix = 'vo';
            echo "<option value='$g' $selected>{$g}{$suffix}</option>";
        }
        ?>
      </select>
    </td>
    <td bgcolor="#F9F9F9">&nbsp;</td>
    <td width="56" bgcolor="#F9F9F9"><strong>Periodo:</strong></td>
    <td width="60" bgcolor="#F9F9F9">
      <input name="periodo" type="text" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" size="10" maxlength="11" onkeyup="ValNumero(this);" />
    </td>
    <td width="148" bgcolor="#F9F9F9">
      <?php
      $consulta_grado = "";
      if ($grado >= 1 && $grado <= 2) $consulta_grado = "SELECT id_asig, nom_asig FROM asignatura WHERE g1='SI' AND borrado=0 ORDER BY cod_asig";
      elseif ($grado >= 3 && $grado <= 6) $consulta_grado = "SELECT id_asig, nom_asig FROM asignatura WHERE g3='SI' AND borrado=0 ORDER BY cod_asig";
      elseif ($grado >= 7) $consulta_grado = "SELECT id_asig, nom_asig FROM asignatura WHERE g7='SI' AND borrado=0 ORDER BY cod_asig";
      $resultado_grado = $consulta_grado ? mysqli_query($conexion, $consulta_grado) : false;
      ?>
    </td>
  </tr>
  <tr align="left">
    <td height="6" colspan="6" align="center" bgcolor="#E9E9E9">
      <strong>Asignatura:</strong>
      <select name="asig" id="asig" onChange="submit();">
        <option value="0"></option>
        <?php if ($resultado_grado):
            while ($row = mysqli_fetch_array($resultado_grado)) { ?>
              <option value="<?php echo $row['id_asig']; ?>" <?php if ($asig == $row['id_asig']) echo "selected='selected'"; ?>>
                <?php echo htmlspecialchars($row['nom_asig']); ?>
              </option>
        <?php } endif; ?>
      </select>
      <input type="submit" name="btnaccion" value="Consultar" />
    </td>
  </tr>
</table>
<?php if ($asig > 0 && $resultado_alu && $total_alu > 0): ?>
<table width="73%" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr>
    <td width="81" height="26" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Nro.</strong></td>
    <td colspan="3" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Cédula</strong></td>
    <td rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Nombre y Apellido</strong></td>
    <td colspan="10" align="center" bgcolor="#E9E9E9"><strong>Notas Segundo Semestre</strong></td>
    <td width="120" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Promedio</strong></td>
  </tr>
  <tr>
    <?php for ($col = 0; $col < 10; $col++): ?>
      <td align="center" bgcolor="#E9E9E9"><strong><?php echo $col; ?></strong></td>
    <?php endfor; ?>
  </tr>
  <?php
  $fila = 1;
  $t = 1;
  while ($datos_alu = mysqli_fetch_array($resultado_alu)) {
      $color = ($fila % 2 == 0) ? "#FFFFFF" : "#CCEEFF";
      $fila++;
      ?>
      <tr bgcolor="<?php echo $color; ?>">
        <td align="center"><?php echo $t; ?></td>
        <td colspan="3" align="left" nowrap="nowrap">
          <input type="hidden" name="txtci_alu<?php echo $t; ?>" value="<?php echo htmlspecialchars($datos_alu['ci_alu']); ?>" />
          <input type="hidden" name="txtci_alua<?php echo $t; ?>" value="<?php echo htmlspecialchars($datos_alu['ci_alu2']); ?>" />
          <?php echo htmlspecialchars($datos_alu['ci_alu'] . '-' . $datos_alu['ci_alu2']); ?>
        </td>
        <td align="left" nowrap="nowrap">
          <?php echo htmlspecialchars($datos_alu['nom_alu']); ?>
          <input type="hidden" name="txtnom_alu<?php echo $t; ?>" value="<?php echo htmlspecialchars($datos_alu['nom_alu']); ?>" />
        </td>
        <?php for ($i = 0; $i < 10; $i++): ?>
          <td align="left">
            <input name="nota<?php echo $t . $i; ?>" type="text" value="<?php echo isset($nota[$t][$i]) ? htmlspecialchars($nota[$t][$i]) : ''; ?>" size="4" maxlength="3" onkeyup="ValNumero(this);" />
            <input type="hidden" name="id_notas<?php echo $t . $i; ?>" value="<?php echo isset($id_notas[$t][$i]) ? htmlspecialchars($id_notas[$t][$i]) : ''; ?>" />
          </td>
        <?php endfor; ?>
        <td align="center">
          <input name="prom<?php echo $t; ?>" type="text" value="<?php echo isset($prom[$t]) ? htmlspecialchars($prom[$t]) : ''; ?>" size="4" maxlength="3" />
          <input name="prom_r<?php echo $t; ?>" type="hidden" value="<?php echo isset($prom_r[$t]) ? htmlspecialchars($prom_r[$t]) : ''; ?>" />
        </td>
      </tr>
      <?php $t++; ?>
  <?php } ?>
  <tr>
    <td height="12" colspan="5" align="center">
      <input name="guarda" type="hidden" value="<?php echo $guarda; ?>" />
      <input name="total_alu" type="hidden" value="<?php echo $total_alu; ?>" />
      <input name="total_periodo" type="hidden" value="<?php echo htmlspecialchars($total_periodo); ?>" />
    </td>
    <td height="12" colspan="11" align="center"></td>
  </tr>
  <tr>
    <td height="5" colspan="5" align="left" bgcolor="#F9F9F9">
      <input type="submit" name="btnaccion" value="Guardar" />
    </td>
    <td height="5" colspan="10" align="center" bgcolor="#F9F9F9"></td>
    <td height="5" align="center" bgcolor="#F9F9F9">
      <input type="submit" name="btnaccion" value="Calcular" />
    </td>
  </tr>
</table>
<?php endif; ?>
</form>
</center>
</body>
</html>
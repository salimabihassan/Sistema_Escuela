<?php
/**
 * MÓDULO: Eliminar Notas por Semestre
 * Propósito: Permite consultar y eliminar notas de un curso, asignatura y semestre específicos.
 * Requisitos: PHP 8.2.12+, conexión mysqli con utf8mb4
 */

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');

ini_set('display_errors', 1); // En producción, desactivar

session_start();

// Validar sesión
$required = ['nombre_apellido', 'cedula', 'ci_usu2', 'grado', 'nivel_intranet'];
foreach ($required as $key) {
    if (!isset($_SESSION[$key])) {
        die("Acceso no autorizado.");
    }
}
$nivel = $_SESSION["nivel_intranet"];

// Función de escape
function esc($str, $conn) {
    return mysqli_real_escape_string($conn, trim($str));
}

$conexion = conectarse_escuela();
if (!$conexion) die("Error de conexión.");

// Inicializar variables
$guardarP = $_POST['guardarP'] ?? '';
$grado = $_POST['grado'] ?? '';
$asig = (int)($_POST['asig'] ?? 0);
$semestre = $_POST['semestre'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$cod_asig = $_POST['cod_asig'] ?? '';
$cod_curso = $grado . $periodo;
$prom_r = $_POST['prom_r'] ?? [];
$fecha_insc = date("d/m/Y");
$btnaccion = $_POST['btnaccion'] ?? '';
$total_alu = (int)($_POST['total_alu'] ?? 0);
$total_periodo = $_POST['total_periodo'] ?? '';

// Inicializar arrays
$nota = $prom = $id_notas = [];
$sem = 0;

// Obtener estado de semestral si hay asignatura
if ($asig > 0) {
    $consulta_sem = "SELECT * FROM asignatura WHERE id_asig = '$asig'";
    $resultado_sem = mysqli_query($conexion, $consulta_sem);
    if ($resultado_sem && mysqli_num_rows($resultado_sem) > 0) {
        $datos_sem = mysqli_fetch_array($resultado_sem);
        
    }
}

switch ($btnaccion) {
    case "Consultar":
        if (empty($semestre)) {
            echo '<script>alert("Debe Escoger el numero de Semestre para continuar");</script>';
            break;
        }
        if (empty($grado)) {
            echo '<script>alert("Debe Escoger un grado para continuar");</script>';
            break;
        }
        if (empty($periodo)) {
            echo '<script>alert("Debe Escribir el Periodo o Año Escolar");</script>';
            break;
        }

        $consulta_asig = "SELECT cod_asig FROM asignatura WHERE id_asig = '$asig'";
        $resultado_asig = mysqli_query($conexion, $consulta_asig);
        if (!$resultado_asig || mysqli_num_rows($resultado_asig) == 0) {
            echo '<script>alert("Asignatura no válida");</script>';
            break;
        }
        $datos_asig = mysqli_fetch_array($resultado_asig);
        $cod_asig = $datos_asig['cod_asig'];

        $consulta_nota = "SELECT * FROM notas{$periodo} WHERE cod_curso = '$cod_curso' AND cod_asig = '$cod_asig' AND semestre = '$semestre'";
        $resultado_nota = mysqli_query($conexion, $consulta_nota);
        $total_nota = mysqli_num_rows($resultado_nota);

        if ($total_nota <= 0) {
            echo '<script>alert("El Curso que introdujo no posee notas registradas....");</script>';
            break;
        } else {
            $consulta_alu2 = "SELECT ci_alu, ci_alu2, nom_alu FROM alumno WHERE grado = '$grado' AND periodo = '$periodo' AND borrado = '0' ORDER BY nom_alu";
            $resultado_alu2 = mysqli_query($conexion, $consulta_alu2);
            $total_alu = mysqli_num_rows($resultado_alu2);
            if ($total_alu > 0) {
                $t = 1;
                while ($datos_alu2 = mysqli_fetch_array($resultado_alu2)) {
                    $ci_a = $datos_alu2['ci_alu'];
                    $ci_b = $datos_alu2['ci_alu2'];
                    $consulta_nota2 = "SELECT * FROM notas{$periodo} WHERE cod_curso = '$cod_curso' AND cod_asig = '$cod_asig' AND semestre = '$semestre' AND ci_alu = '$ci_a' AND ci_alu2 = '$ci_b' ORDER BY id_notas";
                    $resultado_nota2 = mysqli_query($conexion, $consulta_nota2);
                    $i = 0;
                    while ($datos_nota2 = mysqli_fetch_array($resultado_nota2)) {
                        $nota[$t][$i] = $datos_nota2['nota'];
                        $prom[$t] = $datos_nota2['nota_prom'];
                        $id_notas[$t][$i] = $datos_nota2['id_notas'];
                        $nota[$t][$i] = $nota[$t][$i] < 1 ? "" : number_format($nota[$t][$i], 1, '.', '');
                        $prom[$t] = $prom[$t] < 1 ? "" : number_format($prom[$t], 1, '.', '');
                        $prom_r[$t] = $prom[$t];
                        if ($cod_asig == 100) {
                            $val = (float)$prom[$t];
                            if ($val >= 1 && $val <= 3.9) $prom[$t] = 'I';
                            elseif ($val >= 4 && $val <= 4.9) $prom[$t] = 'S';
                            elseif ($val >= 5 && $val <= 5.9) $prom[$t] = 'B';
                            elseif ($val >= 6 && $val <= 7) $prom[$t] = 'MB';
                        }
                        if (++$i >= 10) break;
                    }
                    $t++;
                }
                $total_alu = $t - 1;
            }
        }
        break;

    case "Eliminar":
        if (empty($semestre)) {
            echo '<script>alert("Debe Escoger el numero de Semestre para continuar");</script>';
            break;
        }
        if (empty($grado)) {
            echo '<script>alert("Debe Escoger un grado para continuar");</script>';
            break;
        }
        if (empty($periodo)) {
            echo '<script>alert("Debe Escribir el Periodo o Año Escolar");</script>';
            break;
        }
        if (empty($asig)) {
            echo '<script>alert("Debe seleccionar una asignatura");</script>';
            break;
        }

        $consulta_asig = "SELECT cod_asig FROM asignatura WHERE id_asig = '$asig'";
        $resultado_asig = mysqli_query($conexion, $consulta_asig);
        if (!$resultado_asig || mysqli_num_rows($resultado_asig) == 0) {
            echo '<script>alert("Asignatura no válida");</script>';
            break;
        }
        $datos_asig = mysqli_fetch_array($resultado_asig);
        $cod_asig = $datos_asig['cod_asig'];

        $consulta_nota = "SELECT * FROM notas{$periodo} WHERE cod_curso = '$cod_curso' AND cod_asig = '$cod_asig' AND semestre = '$semestre'";
        $resultado_nota = mysqli_query($conexion, $consulta_nota);
        $total_nota = mysqli_num_rows($resultado_nota);

        if ($total_nota > 0) {
            $consulta_eliminar = "DELETE FROM notas{$periodo} WHERE cod_curso = '$cod_curso' AND cod_asig = '$cod_asig' AND semestre = '$semestre'";
            mysqli_query($conexion, $consulta_eliminar);

            // Auditoría
            $ci_usu = esc($_SESSION["cedula"], $conexion);
            $ci_usu2 = esc($_SESSION["ci_usu2"], $conexion);
            $desc_reg = "Elimino Notas Semestre Nro.{$semestre}";
            $registro = "Mantenimiento, Eliminar Notas";
            $FechaMySQL = date("Y-m-d");
            $sql_aud = "INSERT INTO auditoria(cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES ('$cod_curso cod_asig:$cod_asig', '$ci_usu', '$ci_usu2', '$desc_reg', '$registro', '$FechaMySQL')";
            mysqli_query($conexion, $sql_aud);

            echo '<script>alert("Los datos fueron Eliminados con exito...!!!");</script>';
        }
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>ELIMINAR NOTAS</title>
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
    Control.value = Control.value.replace(/[^0-9]/g, '');
}
var statSend = false;
function checkSubmit() {
    if (!statSend) {
        statSend = true;
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
    ELIMINAR NOTAS
  </font>
  Fecha: <?php echo htmlspecialchars($fecha_insc); ?>
  <input name="fecha_insc" type="hidden" value="<?php echo htmlspecialchars($fecha_insc); ?>" />
</div>

<table width="599" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr align="left">
    <td height="26" colspan="6" bgcolor="#E9E9E9"><strong>INTRODUZCA LOS DATOS DEL ALUMNO:</strong></td>
  </tr>
  <tr align="left">
    <td width="104" height="16" align="right" bgcolor="#F9F9F9"><strong>Curso:</strong></td>
    <td width="71" bgcolor="#F9F9F9">
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
            $suffix = match($g) {
                1 => 'ro', 2 => 'do', 3 => 'ro', 4 => 'to',
                5 => 'to', 6 => 'to', 7 => 'mo', default => 'vo'
            };
            $selected = ($grado == $g) ? "selected='selected'" : "";
            echo "<option value='$g' $selected>{$g}{$suffix}</option>";
        }
        ?>
      </select>
    </td>
    <td width="62" bgcolor="#F9F9F9">&nbsp;</td>
    <td width="61" bgcolor="#F9F9F9"><strong>Periodo:</strong></td>
    <td width="68" bgcolor="#F9F9F9">
      <input name="periodo" type="text" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" size="10" maxlength="11" onkeyup="ValNumero(this);" />
    </td>
    <td width="195" bgcolor="#F9F9F9">
      <?php
      $consulta_grado = "";
      if ($grado >= 1 && $grado <= 2) $consulta_grado = "SELECT id_asig, nom_asig FROM asignatura WHERE g1='SI' AND borrado=0 ORDER BY cod_asig";
      elseif ($grado >= 3 && $grado <= 6) $consulta_grado = "SELECT id_asig, nom_asig FROM asignatura WHERE g3='SI' AND borrado=0 ORDER BY cod_asig";
      elseif ($grado >= 7) $consulta_grado = "SELECT id_asig, nom_asig FROM asignatura WHERE g7='SI' AND borrado=0 ORDER BY cod_asig";

      $resultado_grado = $consulta_grado ? mysqli_query($conexion, $consulta_grado) : false;
      ?>
      <strong>Semestre:
        <select name="semestre" size="1" onChange="submit();" id="semestre">
          <option value=""></option>
          <option value="1"<?php if ($semestre == '1') echo " selected='selected'"; ?>>1ro</option>
          <option value="2"<?php if ($semestre == '2') echo " selected='selected'"; ?>>2do</option>
        </select>
      </strong>
    </td>
  </tr>
  <tr align="left">
    <td height="6" colspan="6" align="center" bgcolor="#E9E9E9">
      <strong>Asignatura:</strong>
      <select name="asig" id="asig" onChange="submit();">
        <option value="0"></option>
        <?php if ($resultado_grado): ?>
          <?php while ($row = mysqli_fetch_array($resultado_grado)): ?>
            <option value="<?php echo $row['id_asig']; ?>"<?php if ($asig == $row['id_asig']) echo " selected='selected'"; ?>>
              <?php echo htmlspecialchars($row['nom_asig']); ?>
            </option>
          <?php endwhile; ?>
        <?php endif; ?>
      </select>
      <input type="submit" name="btnaccion" value="Consultar" />
    </td>
  </tr>
</table>

<?php if ($asig > 0 && isset($total_alu) && $total_alu > 0): ?>
<?php
$consulta_alu = "SELECT ci_alu, ci_alu2, nom_alu FROM alumno WHERE grado = '$grado' AND periodo = '$periodo' AND borrado = '0' ORDER BY nom_alu";
$resultado_alu = mysqli_query($conexion, $consulta_alu);
?>
<table width="73%" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr>
    <td width="81" height="26" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Nro.</strong></td>
    <td colspan="3" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Cédula</strong></td>
    <td rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Nombre y Apellido</strong></td>
    <td colspan="10" align="center" bgcolor="#E9E9E9"><strong>Notas <?php echo $semestre; ?>er Semestre</strong></td>
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
            <input name="nota<?php echo $t . $i; ?>" type="text" value="<?php echo isset($nota[$t][$i]) ? htmlspecialchars($nota[$t][$i]) : ''; ?>" size="4" maxlength="3" readonly />
            <input type="hidden" name="id_notas<?php echo $t . $i; ?>" value="<?php echo isset($id_notas[$t][$i]) ? htmlspecialchars($id_notas[$t][$i]) : ''; ?>" />
          </td>
        <?php endfor; ?>
        <td align="center">
          <input name="prom<?php echo $t; ?>" type="text" value="<?php echo isset($prom[$t]) ? htmlspecialchars($prom[$t]) : ''; ?>" size="4" maxlength="3" readonly />
          <input name="prom_r<?php echo $t; ?>" type="hidden" value="<?php echo isset($prom_r[$t]) ? htmlspecialchars($prom_r[$t]) : ''; ?>" />
        </td>
      </tr>
      <?php $t++; ?>
  <?php } ?>

  <tr>
    <td height="12" colspan="5" align="center">
      <input name="total_alu" type="hidden" value="<?php echo $total_alu; ?>" />
      <input name="guardarP" type="hidden" value="<?php echo htmlspecialchars($guardarP); ?>" />
      <input name="total_periodo" type="hidden" value="<?php echo htmlspecialchars($total_periodo); ?>" />
    </td>
    <td height="12" colspan="11" align="center"></td>
  </tr>
  <tr>
    <td height="5" colspan="5" align="left" bgcolor="#F9F9F9">
      <input type="submit" name="btnaccion" value="Eliminar" />
    </td>
    <td height="5" colspan="10" align="center" bgcolor="#F9F9F9"></td>
    <td height="5" align="center" bgcolor="#F9F9F9"></td>
  </tr>
</table>
<?php endif; ?>
</form>
</center>
</body>
</html>
<?php
// =======================================================
// Archivo: desbloquear_notas.php
// Autor: Salim I. Abi Hassan E.
// Fecha de actualización PHP: 15-10-2025 (PHP 8.2)
// Descripción: Desbloquear notas de alumnos
// =======================================================

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
session_start();

$conexion = conectarse_escuela();

$nivel = $_SESSION["nivel_intranet"] ?? '';
$guardarP = $_POST['guardarP'] ?? '';
$grado = $_POST['grado'] ?? '';
$asig = $_POST['asig'] ?? '';
$semestre = $_POST['semestre'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$cod_asig = $_POST['cod_asig'] ?? '';
$cod_curso = $grado . $periodo;
$prom_r = $_POST['prom_r'] ?? '';
$fecha_insc = date("d/m/Y");
$btnaccion = $_POST['btnaccion'] ?? '';
$total_alu = $_POST['total_alu'] ?? '';
$total_periodo = $_POST['total_periodo'] ?? '';

$nota = $prom = $id_notas = [];

if ($btnaccion === "Consultar") {
    // Validar datos
    if (empty($semestre)) {
        echo "<script>alert('Debe Escoger el número de Semestre para continuar');</script>";
        $btnaccion = "";
    } elseif (empty($grado)) {
        echo "<script>alert('Debe Escoger un grado para continuar');</script>";
        $btnaccion = "";
    } elseif (empty($periodo)) {
        echo "<script>alert('Debe Escribir el Periodo o Año Escolar');</script>";
        $btnaccion = "";
    } else {
        // Consultar asignatura
        $consulta_sem = $conexion->prepare("SELECT * FROM asignatura WHERE id_asig = ?");
        $consulta_sem->bind_param("s", $asig);
        $consulta_sem->execute();
        $datos_sem = $consulta_sem->get_result()->fetch_assoc();
        $sem = $datos_sem['semestral'] ?? '';

        $consulta_asig = $conexion->prepare("SELECT * FROM asignatura WHERE id_asig = ?");
        $consulta_asig->bind_param("s", $asig);
        $consulta_asig->execute();
        $datos_asig = $consulta_asig->get_result()->fetch_assoc();
        $cod_asig = $datos_asig['cod_asig'] ?? '';

        $consulta_nota = $conexion->prepare("SELECT * FROM notas$periodo WHERE cod_curso = ? AND cod_asig = ? AND semestre = ? AND (semestral = '1' OR final = '1')");
        $consulta_nota->bind_param("sss", $cod_curso, $cod_asig, $semestre);
        $consulta_nota->execute();
        $resultado_nota = $consulta_nota->get_result();
        $total_nota = $resultado_nota->num_rows;

        if ($total_nota <= 0) {
            echo "<script>alert('El Curso que introdujo no posee notas Bloqueadas....');</script>";
            $btnaccion = "";
        } else {
            $consulta_curso = $conexion->prepare("SELECT * FROM curso WHERE grado = ? AND periodo = ? AND activo = '1'");
            $consulta_curso->bind_param("ss", $grado, $periodo);
            $consulta_curso->execute();
            $resultado_curso = $consulta_curso->get_result();
            $total_curso = $resultado_curso->num_rows;

            if ($total_curso < 1) {
                echo "<script>alert('El Curso que introdujo no existe en la base de datos. Debe ir al módulo registrar curso y crearlo....');</script>";
                $btnaccion = "";
            } else {
                $consulta_alu2 = $conexion->prepare("SELECT * FROM alumno WHERE grado = ? AND periodo = ? AND borrado = '0' ORDER BY nom_alu");
                $consulta_alu2->bind_param("ss", $grado, $periodo);
                $consulta_alu2->execute();
                $resultado_alu2 = $consulta_alu2->get_result();
                $total_alu2 = $resultado_alu2->num_rows;

                if ($total_alu2 > 0) {
                    $t = 1;
                    while ($datos_alu2 = $resultado_alu2->fetch_assoc()) {
                        $ci_alu[$t] = $datos_alu2['ci_alu'];
                        $ci_alua[$t] = $datos_alu2['ci_alu2'];

                        $consulta_nota2 = $conexion->prepare("SELECT * FROM notas$periodo WHERE cod_curso = ? AND cod_asig = ? AND semestre = ? AND ci_alu = ? AND ci_alu2 = ? ORDER BY id_notas");
                        $consulta_nota2->bind_param("sssss", $cod_curso, $cod_asig, $semestre, $ci_alu[$t], $ci_alua[$t]);
                        $consulta_nota2->execute();
                        $resultado_nota2 = $consulta_nota2->get_result();
                        $total_nota2 = $resultado_nota2->num_rows;

                        if ($total_nota2 > 0) {
                            $i = 0;
                            while ($datos_nota2 = $resultado_nota2->fetch_assoc()) {
                                $nota[$t][$i] = $datos_nota2['nota'];
                                $prom[$t] = $datos_nota2['nota_prom'];
                                $id_notas[$t][$i] = $datos_nota2['id_notas'];
                                $nota[$t][$i] = ($nota[$t][$i] < 1) ? "" : number_format($nota[$t][$i], 1, '.', '');
                                $prom[$t] = ($prom[$t] < 1) ? "" : number_format($prom[$t], 1, '.', '');
                                $prom_r[$t] = $prom[$t];
                                if ($cod_asig == 100) {
                                    if ($prom[$t] >= 1 && $prom[$t] <= 3.9) $prom[$t] = 'I';
                                    if ($prom[$t] >= 4 && $prom[$t] <= 4.9) $prom[$t] = 'S';
                                    if ($prom[$t] >= 5 && $prom[$t] <= 5.9) $prom[$t] = 'B';
                                    if ($prom[$t] >= 6 && $prom[$t] <= 7) $prom[$t] = 'MB';
                                }
                                $i++;
                            }
                        }
                        $t++;
                    }
                }
            }
        }
    }
}

if ($btnaccion === "Desbloquear") {
    // Validar datos
    if (empty($semestre)) {
        echo "<script>alert('Debe Escoger el número de Semestre para continuar');</script>";
        $btnaccion = "";
    } elseif (empty($grado)) {
        echo "<script>alert('Debe Escoger un grado para continuar');</script>";
        $btnaccion = "";
    } elseif (empty($periodo)) {
        echo "<script>alert('Debe Escribir el Periodo o Año Escolar');</script>";
        $btnaccion = "";
    } elseif (empty($asig)) {
        echo "<script>alert('Debe seleccionar una asignatura');</script>";
        $btnaccion = "";
    } else {
        $consulta_asig = $conexion->prepare("SELECT * FROM asignatura WHERE id_asig = ?");
        $consulta_asig->bind_param("s", $asig);
        $consulta_asig->execute();
        $datos_asig = $consulta_asig->get_result()->fetch_assoc();
        $cod_asig = $datos_asig['cod_asig'] ?? '';

        $consulta_nota = $conexion->prepare("SELECT * FROM notas$periodo WHERE cod_curso = ? AND cod_asig = ? AND semestre = ?");
        $consulta_nota->bind_param("sss", $cod_curso, $cod_asig, $semestre);
        $consulta_nota->execute();
        $resultado_nota = $consulta_nota->get_result();
        $total_nota = $resultado_nota->num_rows;

        if ($total_nota > 0) {
            $consulta_eliminar = $conexion->prepare("UPDATE notas$periodo SET final = '0', semestral = '0' WHERE cod_curso = ? AND cod_asig = ? AND semestre = ?");
            $consulta_eliminar->bind_param("sss", $cod_curso, $cod_asig, $semestre);
            $consulta_eliminar->execute();

            $fecha_insc = date("d/m/Y");
            $cod_reg = $cod_curso . 'cod_asig:' . $cod_asig;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Eliminó Notas Semestre Nro.' . $semestre;
            $registro = 'Mantenimiento, Eliminar Notas';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $sql_aud->execute();

            echo "<script>alert('Los datos fueron Desbloqueados con éxito...!!!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>INFORME PARCIAL DE NOTAS 1er SEMESTRE</title>
<style>
body, td, th {
    font-family: Verdana, Geneva, sans-serif;
    font-size: 13px;
}
body {
    margin: 0;
    background: #f7f7f7;
}
.caja {
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    padding: 12px;
    background: #F3F3F3;
    margin: 18px auto;
    width: 1024px;
    border-radius: 12px;
}
table {
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 18px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
th, td {
    border: 1px solid #cfd8dc;
    padding: 6px 4px;
    text-align: center;
}
th {
    background: #e9f0fb;
    font-weight: 600;
}
tr:nth-child(even) td {
    background: #f5f8fd;
}
.sombra {
    text-shadow: 1px 1px 2px #00366C;
}
</style>
<script>
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
<form id="form1" name="form1" method="post" onsubmit="return checkSubmit();">
    <div class="caja">
        <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
            DESBLOQUEAR NOTAS
        </font>
        Fecha: <?php echo htmlspecialchars($fecha_insc); ?>
        <input name="fecha_insc" type="hidden" id="fecha_insc" value="<?php echo htmlspecialchars($fecha_insc); ?>" maxlength="150" size="20" />
    </div>
    <table width="700" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <td colspan="6" bgcolor="#E9E9E9"><strong>INTRODUZCA LOS DATOS DEL ALUMNO:</strong></td>
        </tr>
        <tr>
            <td align="right" bgcolor="#F9F9F9"><strong>Curso:</strong></td>
            <td bgcolor="#F9F9F9">
                <select name="grado" id="grado" onchange="submit();">
                    <option value=""></option>
                    <?php
                    $grados = ['1'=>'1ro','2'=>'2do','3'=>'3ro','4'=>'4to','5'=>'5to','6'=>'6to','7'=>'7mo','8'=>'8vo'];
                    foreach ($grados as $g => $label) {
                        $selected = ($grado == $g) ? "selected='selected'" : "";
                        echo "<option value='$g' $selected>$label</option>";
                    }
                    ?>
                </select>
            </td>
            <td bgcolor="#F9F9F9"></td>
            <td bgcolor="#F9F9F9"><strong>Periodo:</strong></td>
            <td bgcolor="#F9F9F9">
                <input name="periodo" type="text" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" size="10" maxlength="11" onkeyup="return ValNumero(this);" />
            </td>
            <td bgcolor="#F9F9F9">
                <strong>Semestre:
                    <select name="semestre" id="semestre" onchange="submit();">
                        <option value=""></option>
                        <option value="1" <?php if ($semestre=='1') echo "selected='selected'"; ?>>1ro</option>
                        <option value="2" <?php if ($semestre=='2') echo "selected='selected'"; ?>>2do</option>
                    </select>
                </strong>
            </td>
        </tr>
        <tr>
            <td colspan="6" align="center" bgcolor="#E9E9E9">
                <input name="asig" type="hidden" id="asig" value="<?php echo htmlspecialchars($asig); ?>" />
                <strong>Asignatura:</strong>
                <select name="asig" id="asig" onchange="submit();">
                    <option value="0"></option>
                    <?php
                    if ($grado) {
                        if ($grado < 3) $consulta_grado = "SELECT * FROM asignatura WHERE g1='SI' and borrado=0 ORDER BY cod_asig";
                        elseif ($grado < 7) $consulta_grado = "SELECT * FROM asignatura WHERE g3='SI' and borrado=0 ORDER BY cod_asig";
                        else $consulta_grado = "SELECT * FROM asignatura WHERE g7='SI' and borrado=0 ORDER BY cod_asig";
                        $resultado_grado = $conexion->query($consulta_grado);
                        while ($row = $resultado_grado->fetch_assoc()) {
                            $selected = ($asig == $row['id_asig']) ? "selected='selected'" : "";
                            echo "<option value='{$row['id_asig']}' $selected>{$row['nom_asig']}</option>";
                        }
                    }
                    ?>
                </select>
                <input type="submit" name="btnaccion" id="btnaccion" value="Consultar" />
            </td>
        </tr>
    </table>
    <?php
    if (!empty($asig) && !empty($nota)) {
        $consulta_alu = $conexion->prepare("SELECT * FROM alumno WHERE grado = ? AND periodo = ? AND borrado = '0' ORDER BY nom_alu");
        $consulta_alu->bind_param("ss", $grado, $periodo);
        $consulta_alu->execute();
        $resultado_alu = $consulta_alu->get_result();
        $total_alu = $resultado_alu->num_rows;
        if ($total_alu > 0) {
    ?>
    <table width="90%" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <th rowspan="2">Nro.</th>
            <th colspan="3" rowspan="2">Cédula</th>
            <th rowspan="2">Nombre y Apellido</th>
            <th colspan="10">Notas Primer Semestre</th>
            <th rowspan="2">Promedio</th>
        </tr>
        <tr>
            <?php for ($i = 0; $i < 10; $i++) echo "<th>$i</th>"; ?>
        </tr>
        <?php
        $t = 1;
        while ($datos_alu = $resultado_alu->fetch_assoc()) {
            $color = ($t % 2 == 0) ? "#FFFFFF" : "#CCEEFF";
            echo "<tr bgcolor='$color'>";
            echo "<td align='center'>$t</td>";
            echo "<td colspan='3' align='left'><input type='hidden' name='txtci_alu$t' value='{$datos_alu['ci_alu']}' /><input type='hidden' name='txtci_alua$t' value='{$datos_alu['ci_alu2']}' />{$datos_alu['ci_alu']}-{$datos_alu['ci_alu2']}</td>";
            echo "<td align='left'>{$datos_alu['nom_alu']}<input type='hidden' name='txtnom_alu$t' value='{$datos_alu['nom_alu']}' /></td>";
            for ($i = 0; $i < 10; $i++) {
                $nota_val = $nota[$t][$i] ?? '';
                $id_val = $id_notas[$t][$i] ?? '';
                echo "<td align='left'><input name='nota{$t}{$i}' type='text' value='$nota_val' size='4' maxlength='3' onkeyup='return ValNumero(this);' /><input name='id_notas{$t}{$i}' type='hidden' value='$id_val' /></td>";
            }
            $prom_val = $prom[$t] ?? '';
            $prom_r_val = $prom_r[$t] ?? '';
            echo "<td align='center'><input name='prom$t' type='text' value='$prom_val' size='4' maxlength='3' /><input name='prom_r$t' type='hidden' value='$prom_r_val' /></td>";
            echo "</tr>";
            $t++;
        }
        ?>
        <tr>
            <td colspan="5" align="center"><input name="total_alu" type="hidden" value="<?php echo $total_alu; ?>" /></td>
            <td colspan="10"></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="5" align="left"><input type="submit" name="btnaccion" id="btnaccion" value="Desbloquear" /></td>
            <td colspan="10"></td>
            <td></td>
        </tr>
    </table>
    <?php
        }
    }
    ?>
</form>
</center>
</body>
</html>
<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\registrar_inf_perso2.php
// =======================================================
// Archivo: registrar_inf_perso2.php
// Autor: Sistema Escolar
// Fecha de actualización PHP: 15-10-2025 (PHP 8.2)
// Descripción: Informe de Personalidad 2do Semestre
// =======================================================

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
session_start();

$conexion = conectarse_escuela();
$nivel = $_SESSION["nivel_intranet"] ?? '';

$grado = $_POST['grado'] ?? '';
$guarda = $_POST['guarda'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$num = $_POST['num'] ?? '';
$num2 = $_POST['num2'] ?? '';
$fecha_insc = date("d/m/Y");
$btnaccion = $_POST['btnaccion'] ?? '';
$total_consultar = $_POST['total_consultar'] ?? 0;
$total_ambito = $_POST['total_ambito'] ?? 0;

$lit = [];
$id_perso = [];
$nom_alu = [];
$ci_alu = [];
$ci_alup = [];
$cod_ambito = [];
$ambito = [];
$consulta_perso = "";

$y = 0;
if ($num2 == 1) $y = 10;
if ($num2 == 2) $y = 8;
if ($num2 == 3) $y = 10;

for ($t = 0; $t < $total_ambito; $t++) {
    $cod_ambito[$t] = $_POST['cod_ambito' . $t] ?? '';
    for ($i = 0; $i < $total_consultar; $i++) {
        $lit[$t][$i] = strtoupper($_POST['lit' . $t . $i] ?? '');
        $nom_alu[$i] = $_POST['nom_alu' . $i] ?? '';
        $ci_alu[$i] = $_POST['ci_alu' . $i] ?? '';
        $ci_alup[$i] = $_POST['ci_alu2' . $i] ?? '';
        $lite = $lit[$t][$i];
        if (!in_array($lite, ["S", "F", "O", "NO", "N", ""])) {
            if (!isset($e)) $e = 0;
            if ($e < 1) {
                $e++;
                $c = $t + 1;
                $v = $i + 1;
                echo "<script>alert('Los Literales permitidos son: S, F, O, NO, N. Error en el campo numero: $c-$v');</script>";
            }
        }
    }
}

switch ($btnaccion) {
    case "Consultar":
        if (empty($grado)) {
            echo "<script>alert('Debe Escoger un grado para continuar');</script>";
            break;
        }
        if (empty($periodo)) {
            echo "<script>alert('Debe Escribir el Periodo o Año Escolar');</script>";
            break;
        }
        if (empty($num)) {
            echo "<script>alert('Debe seleccionar un número de Ámbito para continuar');</script>";
            break;
        }
        $guarda = '0';
        if ($num == 1) $consulta_ambito = "SELECT * FROM ambito WHERE cod_ambito<=11 ORDER BY cod_ambito";
        if ($num == 2) $consulta_ambito = "SELECT * FROM ambito WHERE cod_ambito>=12 AND cod_ambito<=20 ORDER BY cod_ambito";
        if ($num == 3) $consulta_ambito = "SELECT * FROM ambito WHERE cod_ambito>=21 ORDER BY cod_ambito";
        $resultado_ambito = $conexion->query($consulta_ambito);
        $total_ambito = $resultado_ambito->num_rows;

        $consulta_consultar = "SELECT * FROM alumno WHERE grado='$grado' AND periodo='$periodo' AND borrado='0' ORDER BY nom_alu";
        $resultado_consultar = $conexion->query($consulta_consultar);
        $total_consultar = $resultado_consultar->num_rows;

        $i = 0;
        while ($datos_consultar = $resultado_consultar->fetch_assoc()) {
            $nom_alu[$i] = $datos_consultar['nom_alu'];
            $ci_alu[$i] = $datos_consultar['ci_alu'];
            $ci_alup[$i] = $datos_consultar['ci_alu2'];
            $id_alu[$i] = $datos_consultar['id_alu'];
            $i++;
        }

        if ($num == 1) $consulta_perso = "SELECT * FROM perso$periodo WHERE grado='$grado' AND periodo='$periodo' AND semestre='2' AND cod_ambito<='11' ORDER BY id_perso";
        if ($num == 2) $consulta_perso = "SELECT * FROM perso$periodo WHERE grado='$grado' AND periodo='$periodo' AND semestre='2' AND cod_ambito>=12 AND cod_ambito<=20";
        if ($num == 3) $consulta_perso = "SELECT * FROM perso$periodo WHERE grado='$grado' AND periodo='$periodo' AND semestre='2' AND cod_ambito>=21";
        if (!empty($consulta_perso)) {
            $resultado_perso = $conexion->query($consulta_perso);
            $total_perso = $resultado_perso->num_rows;
        } else {
            $resultado_perso = false;
            $total_perso = 0;
        }

        if ($total_perso <= 0) {
            for ($t = 0; $t < $total_ambito; $t++) {
                for ($i = 0; $i < $total_consultar; $i++) {
                    $lit[$t][$i] = "";
                }
            }
        } else {
            echo "<script>alert('Este Curso ya tiene registrado el informe de personalidad en este número de Ámbito del segundo semestre, los datos guardados se mostrarán, si desea, puede modificarlos y luego oprima el botón Actualizar para guardar los cambios...!!!');</script>";
            $t = 0;
            $cod_ambitop = [];
            if ($num == 1) $consulta_ambito2 = "SELECT * FROM ambito WHERE cod_ambito<=11 ORDER BY cod_ambito";
            if ($num == 2) $consulta_ambito2 = "SELECT * FROM ambito WHERE cod_ambito>=12 AND cod_ambito<=20 ORDER BY cod_ambito";
            if ($num == 3) $consulta_ambito2 = "SELECT * FROM ambito WHERE cod_ambito>=21 ORDER BY cod_ambito";
            $resultado_ambito2 = $conexion->query($consulta_ambito2);
            while ($datos_ambito2 = $resultado_ambito2->fetch_assoc()) {
                $cod_ambitop[$t] = $datos_ambito2['cod_ambito'];
                $t++;
            }
            for ($t = 0; $t < $total_ambito; $t++) {
                for ($i = 0; $i < $total_consultar; $i++) {
                    $lit[$t][$i] = "";
                    $consulta_perso2 = "SELECT * FROM perso$periodo WHERE grado='$grado' AND periodo='$periodo' AND semestre='2' AND cod_ambito='{$cod_ambitop[$t]}' AND ci_alu='{$ci_alu[$i]}' AND ci_alu2='{$ci_alup[$i]}'";
                    if (!empty($consulta_perso2)) {
                        $resultado_perso2 = $conexion->query($consulta_perso2);
                        $datos_perso2 = $resultado_perso2->fetch_assoc();
                        $lit[$t][$i] = $datos_perso2['lit'] ?? '';
                        $id_perso[$t][$i] = $datos_perso2['id_perso'] ?? '';
                    } else {
                        $lit[$t][$i] = "";
                        $id_perso[$t][$i] = "";
                    }
                }
            }
        }
        break;

    case "Guardar":
        if (empty($grado)) {
            echo "<script>alert('Debe Escoger un grado para continuar');</script>";
            break;
        }
        if (empty($periodo)) {
            echo "<script>alert('Debe Escribir el Periodo o Año Escolar');</script>";
            break;
        }
        if (empty($num)) {
            echo "<script>alert('Debe seleccionar un número de Ámbito para continuar');</script>";
            break;
        }
        $guarda++;
        if ($num == 1) $consulta_ambito = "SELECT * FROM ambito WHERE cod_ambito<=11 ORDER BY cod_ambito";
        if ($num == 2) $consulta_ambito = "SELECT * FROM ambito WHERE cod_ambito>=12 AND cod_ambito<=20 ORDER BY cod_ambito";
        if ($num == 3) $consulta_ambito = "SELECT * FROM ambito WHERE cod_ambito>=21 ORDER BY cod_ambito";
        $resultado_ambito = $conexion->query($consulta_ambito);
        $total_ambito = $resultado_ambito->num_rows;

        $consulta_consultar = "SELECT * FROM alumno WHERE grado='$grado' AND periodo='$periodo' AND borrado='0' ORDER BY nom_alu";
        $resultado_consultar = $conexion->query($consulta_consultar);
        $total_consultar = $resultado_consultar->num_rows;

        $i = 0;
        while ($datos_consultar = $resultado_consultar->fetch_assoc()) {
            $nom_alu[$i] = $datos_consultar['nom_alu'];
            $ci_alu[$i] = $datos_consultar['ci_alu'];
            $ci_alup[$i] = $datos_consultar['ci_alu2'];
            $id_alu[$i] = $datos_consultar['id_alu'];
            $i++;
        }

        $fecha_insc = date("d/m/Y");
        $cod_reg = $grado . $periodo;
        $ci_usu = $_SESSION["cedula"] ?? '';
        $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
        $desc_reg = 'Actualizó';
        $registro = 'Procesos, Personalidad 2do Semestre';
        $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

        $sql_aud = "INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES ('$cod_reg','$ci_usu','$ci_usu2','$desc_reg','$registro','$FechaMySQL')";
        $conexion->query($sql_aud);

        for ($t = 0; $t < $total_ambito; $t++) {
            for ($i = 0; $i < $total_consultar; $i++) {
                $literal = strtoupper($_POST['lit' . $t . $i] ?? '');
                $id_perso_val = $_POST['id_perso' . $t . $i] ?? '';
                $cod = $_POST['cod_ambito' . $t] ?? '';
                $semestre = '2';
                $ci_alu1 = $ci_alu[$i];
                $ci_alu3 = $ci_alup[$i];
                $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

                if (!empty($id_perso_val)) {
                    $consulta_perso12 = "SELECT final FROM perso$periodo WHERE id_perso='$id_perso_val'";
                    $resultado_perso12 = $conexion->query($consulta_perso12);
                    $datos_sem = $resultado_perso12->fetch_assoc();
                    $sem = $datos_sem['final'] ?? 0;
                    if ($sem == 0) {
                        $consulta2 = "UPDATE perso$periodo SET lit='$literal' WHERE id_perso='$id_perso_val'";
                        $conexion->query($consulta2);
                    }
                } else {
                    if (!empty($literal)) {
                        $consulta_promover = "INSERT INTO perso$periodo (ci_alu, ci_alu2, grado, periodo, cod_ambito, semestre, lit, fecha) VALUES ('$ci_alu1','$ci_alu3','$grado','$periodo','$cod','$semestre','$literal','$FechaMySQL')";
                        $conexion->query($consulta_promover);
                    }
                }
            }
        }
        echo "<script>alert('Los datos fueron Actualizados con éxito...!!!');</script>";
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>INFORME PERSONALIDAD 2DO SEMESTRE</title>
<style type="text/css">
body,td,th { font-family: Verdana, Geneva, sans-serif; font-size: 9px; }
body { margin:0; }
.sombra{ text-shadow: 0.05em 0.05em 0.03em #000; }
.caja { box-shadow: 5px 5px 5px rgba(0,0,0,.5); padding: 5px; background:#F3F3F3; margin:5px; width:1024px; }
</style>
<script>
function checkSubmit() {
    if (!window.statSend) {
        window.statSend = true;
        return true;
    } else {
        alert("El formulario ya se está enviando...");
        return false;
    }
}
function escribe(msg) {
    let cont = 0;
    while ((cont < msg.length) && (cont < 15)) {
        let letra = msg.substring(cont, cont + 1);
        document.write(letra + "<br>");
        cont += 1;
    }
}
</script>
</head>
<body>
<center>
<form id="form1" name="form1" method="post" onsubmit="return checkSubmit();">
<div class="caja">
    <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
        INFORME DE PERSONALIDAD 2DO SEMESTRE
    </font>
    Fecha: <?php echo htmlspecialchars($fecha_insc); ?>
    <input name="fecha_insc2" type="hidden" value="<?php echo htmlspecialchars($fecha_insc); ?>" />
</div>
<table width="537" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr align="left">
        <td height="26" colspan="7" bgcolor="#E9E9E9"><strong>INTRODUZCA LOS DATOS DEL CURSO: </strong></td>
    </tr>
    <tr align="left">
        <td width="2" height="16" align="right" bgcolor="#F9F9F9">&nbsp;</td>
        <td width="42" bgcolor="#F9F9F9"><strong>Curso:</strong></td>
        <td width="63" bgcolor="#F9F9F9">
            <select name="grado" size="1" id="grado">
                <option value=""></option>
                <?php
                if ($nivel == 0) {
                    $g = $_SESSION["grado"] ?? '';
                    $grados = [$g];
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
        <td width="42" bgcolor="#F9F9F9"><strong>Periodo:</strong></td>
        <td width="60" bgcolor="#F9F9F9"><input name="periodo" type="text" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" size="10" maxlength="11" /></td>
        <th width="122" bgcolor="#F9F9F9">Número de Ámbito:</th>
        <td width="162" bgcolor="#F9F9F9">
            <select name="num" size="1" id="num">
                <option value=""></option>
                <option value="1" <?php if ($num == '1') echo "selected='selected'"; ?>>Ámbito del 1 al 11</option>
                <option value="2" <?php if ($num == '2') echo "selected='selected'"; ?>>Ámbito del 12 al 20</option>
                <option value="3" <?php if ($num == '3') echo "selected='selected'"; ?>>Ámbito del 21 al 31</option>
            </select>
        </td>
    </tr>
    <tr align="left">
        <td height="6" colspan="7" align="center" bgcolor="#E9E9E9">
            <input name="guarda" type="hidden" value="<?php echo htmlspecialchars($guarda); ?>" />
            <input name="total_consultar" type="hidden" value="<?php echo htmlspecialchars($total_consultar); ?>" />
            <input name="num2" type="hidden" value="<?php echo htmlspecialchars($num2); ?>" />
            <input name="total_ambito" type="hidden" value="<?php echo htmlspecialchars($total_ambito); ?>" />
            <input type="submit" name="btnaccion" value="Consultar" />
        </td>
    </tr>
</table>
<?php if ($total_consultar > 0 && isset($resultado_ambito)): ?>
<table width="552" border="1" align="center" cellpadding="2" cellspacing="2">
    <?php
    $fila = 1;
    $x = 0;
    $t = 0;
    $i = 0;
    while ($datos_ambito = $resultado_ambito->fetch_assoc()) {
        $resto = $fila % 2;
        $color = ($resto == 0) ? "#FFFFFF" : "#CCEEFF";
        $fila++;
        $x++;
        if (($x == 1) && ($num == 1)) {
            echo "<tr><th align='center' bgcolor='#E9E9E9'><h3>ÁMBITO: RELACIÓN CON SUS PARES</h3></th>";
            for ($i = 0; $i < $total_consultar; $i++) {
                echo "<th bgcolor='#FFFFFF'><script>escribe(\"" . htmlspecialchars($nom_alu[$i]) . "\")</script></th>";
            }
            echo "</tr>";
        }
        if (($x == 7) && ($num == 1)) {
            echo "<tr><th align='center' bgcolor='#E9E9E9'><h3>ÁMBITO: DISCIPLINARIO</h3></th>";
            for ($i = 0; $i < $total_consultar; $i++) {
                echo "<th bgcolor='#FFFFFF'></th>";
            }
            echo "</tr>";
        }
        if (($x == 1) && ($num == 2)) {
            echo "<tr><th align='center' bgcolor='#E9E9E9'><h3>ÁMBITO: RESPONSABILIDAD</h3></th>";
            for ($i = 0; $i < $total_consultar; $i++) {
                echo "<th bgcolor='#FFFFFF'><script>escribe(\"" . htmlspecialchars($nom_alu[$i]) . "\")</script></th>";
            }
            echo "</tr>";
        }
        if (($x == 6) && ($num == 2)) {
            echo "<tr><th align='center' bgcolor='#E9E9E9'><h3>ÁMBITO: TRABAJO EN AULA</h3></th>";
            for ($i = 0; $i < $total_consultar; $i++) {
                echo "<th bgcolor='#FFFFFF'></th>";
            }
            echo "</tr>";
        }
        if (($x == 1) && ($num == 3)) {
            echo "<tr><th align='center' bgcolor='#E9E9E9'><h3>ÁMBITO: AFECTIVIDAD</h3></th>";
            for ($i = 0; $i < $total_consultar; $i++) {
                echo "<th bgcolor='#FFFFFF'><script>escribe(\"" . htmlspecialchars($nom_alu[$i]) . "\")</script></th>";
            }
            echo "</tr>";
        }
        if (($x == 4) && ($num == 3)) {
            echo "<tr><th align='center' bgcolor='#E9E9E9'><h3>ÁMBITO: PRESENTACIÓN PERSONAL</h3></th>";
            for ($i = 0; $i < $total_consultar; $i++) {
                echo "<th bgcolor='#FFFFFF'></th>";
            }
            echo "</tr>";
        }
        if (($x == 9) && ($num == 3)) {
            echo "<tr><th align='center' bgcolor='#E9E9E9'><h3>ÁMBITO: EN CUANTO A LOS PADRES Y/O APODERADOS</h3></th>";
            for ($i = 0; $i < $total_consultar; $i++) {
                echo "<th bgcolor='#FFFFFF'></th>";
            }
            echo "</tr>";
        }
        echo "<tr>";
        echo "<td bgcolor='$color'><h3>
            <input name='ambito$t' type='hidden' value='" . htmlspecialchars($datos_ambito['nom_ambito']) . "' />
            <input name='cod_ambito$t' type='hidden' value='" . htmlspecialchars($datos_ambito['cod_ambito']) . "' />
            " . htmlspecialchars($datos_ambito['cod_ambito'] . '-. ' . $datos_ambito['nom_ambito']) . "
        </h3></td>";
        for ($i = 0; $i < $total_consultar; $i++) {
            echo "<th bgcolor='$color'>
                <input name='id_perso{$t}{$i}' type='hidden' value='" . htmlspecialchars($id_perso[$t][$i] ?? '') . "' />
                <input name='lit{$t}{$i}' type='text' value='" . htmlspecialchars($lit[$t][$i] ?? '') . "' size='1' maxlength='2' />
                <input name='nom_alu{$i}' type='hidden' value='" . htmlspecialchars($nom_alu[$i]) . "' />
                <input name='ci_alu{$i}' type='hidden' value='" . htmlspecialchars($ci_alu[$i]) . "' />
                <input name='ci_alu2{$i}' type='hidden' value='" . htmlspecialchars($ci_alup[$i]) . "' />
            </th>";
        }
        $t++;
        echo "</tr>";
    }
    ?>
    <tr>
        <th bgcolor="#FFFFFF"><?php if ($guarda < 1) { ?><input type="submit" name="btnaccion" value="Guardar" /><?php } ?></th>
        <?php for ($i = 0; $i < $total_consultar; $i++) { echo "<th bgcolor='#FFFFFF'></th>"; } ?>
    </tr>
</table>
<?php endif; ?>
</form>
</center>
</body>
</html>
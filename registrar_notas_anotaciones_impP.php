<?php
// =======================================================
// Archivo: registrar_notas_anotaciones_impP.php
// Autor: Salim I. Abi Hassan E.
// Fecha de actualización PHP: 13-10-2025 (PHP 8.2)
// Descripción: Informe de Notas Parcial (Primer Semestre)
// =======================================================

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
$conexion = conectarse_escuela();

// ===============================
// Variables del formulario y consulta imp
// ===============================
$fecha_insc = date("d-m-Y");
$consulta_imp = "SELECT * FROM imp WHERE id='1'";
$resultado_imp = $conexion->query($consulta_imp);
$datos_imp = $resultado_imp->fetch_assoc();

$ci_alu = $datos_imp['ci_alu'] ?? '';
$ci_alu2 = strtoupper($datos_imp['ci_alu2'] ?? '');
$obs = strtoupper($_POST['obs'] ?? '');

// ===============================
// Consulta de alumno
// ===============================
$consulta_consultar = $conexion->prepare("SELECT * FROM alumno WHERE ci_alu = ? AND ci_alu2 = ?");
$consulta_consultar->bind_param("ss", $ci_alu, $ci_alu2);
$consulta_consultar->execute();
$resultado_consultar = $consulta_consultar->get_result();
$total_consultar = $resultado_consultar->num_rows;

if ($total_consultar > 0) {
    $datos_consultar = $resultado_consultar->fetch_assoc();
    $borrado = $datos_consultar['borrado'];
    if ($borrado == 0) {
        $nom_alu = $datos_consultar['nom_alu'];
        $grado = $datos_consultar['grado'];
        $periodo = $datos_consultar['periodo'];
        $id_alu = $datos_consultar['id_alu'];

        // ===============================
        // Consulta de curso y profesor
        // ===============================
        $cod_curso = $grado . $periodo;
        $consulta_curso = $conexion->prepare("SELECT * FROM curso WHERE cod_curso = ?");
        $consulta_curso->bind_param("s", $cod_curso);
        $consulta_curso->execute();
        $datos_curso = $consulta_curso->get_result()->fetch_assoc();
        $ci_prof = $datos_curso['ci_prof'] ?? '';
        $ci_prof2 = $datos_curso['ci_prof2'] ?? '';

        $consulta_prof = $conexion->prepare("SELECT * FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
        $consulta_prof->bind_param("ss", $ci_prof, $ci_prof2);
        $consulta_prof->execute();
        $datos_prof = $consulta_prof->get_result()->fetch_assoc();
        $nom_prof = $datos_prof['nom_prof'] ?? '';

        $consulta_dir = $conexion->prepare("SELECT * FROM prof WHERE director = 1");
        $consulta_dir->execute();
        $datos_dir = $consulta_dir->get_result()->fetch_assoc();
        $nom_dir = $datos_dir['nom_prof'] ?? '';

        // ===============================
        // Consulta de asignaturas según grado
        // ===============================
        if ($grado < 3) {
            $consulta_sql2 = "SELECT * FROM asignatura WHERE g1='SI' AND borrado=0 ORDER BY cod_asig";
        } elseif ($grado > 2 && $grado < 7) {
            $consulta_sql2 = "SELECT * FROM asignatura WHERE g3='SI' AND borrado=0 ORDER BY cod_asig";
        } else {
            $consulta_sql2 = "SELECT * FROM asignatura WHERE g7='SI' AND borrado=0 ORDER BY cod_asig";
        }
        $resultado_sql2 = $conexion->query($consulta_sql2);
        $total_sql2 = $resultado_sql2->num_rows;

        // ===============================
        // Cargar notas del primer semestre
        // ===============================
        $nota = $id_notas = $prom = [];
        $prom_sem = 0;
        $s = 0;
        $t = 0;
        $anota_n = $anota_p = $porc_asist = $obs = "";
        $tabla_notas = "";

        while ($datos_sql2 = $resultado_sql2->fetch_assoc()) {
            $i = 0;
            $t++;
            $cod_asig = $datos_sql2['cod_asig'];
            $nom_asig = $datos_sql2['nom_asig'];
            $consulta_periodo2 = $conexion->prepare("SELECT * FROM notas$periodo WHERE ci_alu = ? AND ci_alu2 = ? AND periodo = ? AND semestre = '1' AND cod_asig = ? ORDER BY id_notas");
            $consulta_periodo2->bind_param("ssss", $ci_alu, $ci_alu2, $periodo, $cod_asig);
            $consulta_periodo2->execute();
            $resultado_periodo2 = $consulta_periodo2->get_result();
            $nota_val = "";
            $prom_val = "";
            while ($datos_periodo2 = $resultado_periodo2->fetch_assoc()) {
                $id_notas[$t][$i] = $datos_periodo2['id_notas'];
                $nota_val = $datos_periodo2['nota'] > 0 ? number_format($datos_periodo2['nota'], 1, '.', '') : "";
                $prom_val = $datos_periodo2['nota_prom'] > 0 ? number_format($datos_periodo2['nota_prom'], 1, '.', '') : "";
                $anota_n = $datos_periodo2['anot_n'] ?? "";
                $anota_p = $datos_periodo2['anot_p'] ?? "";
                $porc_asist = $datos_periodo2['porc_asist'] ?? "";
                $obs = $datos_periodo2['obs'] ?? "";
                $i++;
            }
            if ($cod_asig < 100 && $prom_val > 0) {
                $s++;
                $prom_sem += $prom_val;
            }
            if ($cod_asig == 100 && $prom_val !== "") {
                $val = $prom_val;
                $prom_val = match(true) {
                    $val >= 1 && $val <= 3.9 => 'I',
                    $val >= 4 && $val <= 4.9 => 'S',
                    $val >= 5 && $val <= 5.9 => 'B',
                    $val >= 6 && $val <= 7 => 'MB',
                    default => $prom_val
                };
            }
            // Construir la fila de la tabla de notas
            $tabla_notas .= "<tr>
                <td align='center' bgcolor='#FFFFFF'>{$nom_asig}</td>
                <td align='center' bgcolor='#FFFFFF'>{$nota_val}</td>
                <td align='center' bgcolor='#FFFFFF'>{$prom_val}</td>
            </tr>";
        }
        $prom_sem = ($s > 0) ? number_format($prom_sem / $s, 1, '.', '') : "";

    } else {
        echo "<script>alert('Esta Cédula del Alumno fue eliminado de la Base de Datos, Para Recuperarlo Vaya a la Papelera de Reciclaje...!!!');</script>";
        $nom_alu = $sexo = $id_alu = $grado = $periodo = "";
    }
} else {
    echo "<script>alert('Esta Cédula de Alumno no está registrada en el sistema...!!!');</script>";
    $nom_alu = $sexo = $id_alu = $grado = $periodo = "";
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>INFORME DE NOTAS PARCIAL</title>
<style type="text/css">
body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
.sombra { text-shadow: 1px 1px 2px #00366C; }
.caja { background-color: #E9E9E9; padding: 10px; border-radius: 8px; margin-bottom: 10px; }
table { border-collapse: collapse; }
th, td { border: 1px solid #999; padding: 4px; }
</style>
</head>
<body>
<center>
<form id="form1" name="form1" method="post" action="registrar_notas1_anotaciones.php">
    <!-- Leyendas y datos del colegio -->
    <table width="91%" border="0" cellpadding="2" cellspacing="2">
        <tr>
            <td rowspan="5" align="center"><img src="/sistema_Escuela/imagenes/logo altas cumbres.jpg" width="178" height="89" /></td>
            <td height="18" align="left"><strong>Escuela Básica Particular Nº 2271</strong></td>
            <td align="right">&nbsp;</td>
        </tr>
        <tr>
            <td height="18" align="left"><strong>Colegio "Altas Cumbres del Rosal"</strong></td>
            <td align="right">&nbsp;</td>
        </tr>
        <tr>
            <td height="18" align="left"><strong>Fono: 316 55 18</strong></td>
            <td align="right">&nbsp;</td>
        </tr>
        <tr>
            <td height="18" align="left"><strong>caltascumbres@gmail.com</strong></td>
            <td align="right">&nbsp;</td>
        </tr>
        <tr>
            <td height="18" align="left"><strong>RBD: 26392-3</strong></td>
            <th align="left"><?php echo date("d/m/Y"); ?>
                <input name="fecha_insc2" type="hidden" value="<?php echo htmlspecialchars($fecha_insc); ?>" />
            </th>
        </tr>
    </table>
    <!-- Datos del alumno -->
    <table width="1061" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr align="left">
            <td colspan="4" align="center" bgcolor="#E9E9E9">
                <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">INFORME DE NOTAS PARCIAL</font>
            </td>
        </tr>
        <tr align="left">
            <td colspan="4" align="center" bgcolor="#FFFFFF"><strong>DATOS DEL ALUMNO:</strong></td>
        </tr>
        <tr align="left">
            <th align="center" bgcolor="#E9E9E9"><h4>Cédula: <?php echo number_format($ci_alu, 0, ' ', '.') . '-' . $ci_alu2; ?></h4></th>
            <th align="center" bgcolor="#E9E9E9"><h4>Nombre: <?php echo htmlspecialchars($nom_alu); ?></h4></th>
            <th align="center" bgcolor="#E9E9E9"><h4>Curso: <?php
                $grados = ['1' => '1ro', '2' => '2do', '3' => '3ro', '4' => '4to', '5' => '5to', '6' => '6to', '7' => '7mo', '8' => '8vo'];
                echo $grados[$grado] ?? '';
            ?></h4></th>
            <th align="center" bgcolor="#E9E9E9"><h4>Periodo Escolar: <?php echo htmlspecialchars($periodo); ?></h4></th>
        </tr>
    </table>
    <!-- Tabla de notas -->
    <table width="700" border="1" align="center" cellpadding="2" cellspacing="2">
        <tr bgcolor="#E9E9E9">
            <th width="400" align="center">Asignatura</th>
            <th width="100" align="center">Nota</th>
            <th width="100" align="center">Promedio</th>
        </tr>
        <?php echo $tabla_notas; ?>
        <tr bgcolor="#E9E9E9">
            <th align="right">Promedio Semestral:</th>
            <th colspan="2" align="center"><?php echo $prom_sem; ?></th>
        </tr>
    </table>
    <!-- Leyendas -->
    <table width="700" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <td colspan="3" align="center" bgcolor="#E9E9E9"><strong>LEYENDA DE CALIFICACIONES</strong></td>
        </tr>
        <tr>
            <td align="center">I: Insuficiente (1.0 - 3.9)</td>
            <td align="center">S: Suficiente (4.0 - 4.9)</td>
            <td align="center">B: Bueno (5.0 - 5.9)</td>
            <td align="center">MB: Muy Bueno (6.0 - 7.0)</td>
        </tr>
    </table>
    <!-- Observaciones y firmas -->
    <table width="700" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
            <td colspan="2" bgcolor="#E9E9E9"><strong>Observaciones:</strong> <?php echo htmlspecialchars($obs); ?></td>
        </tr>
        <tr>
            <th align="center">______________________________</th>
            <th align="center">______________________________</th>
        </tr>
        <tr>
            <th align="center"><?php echo htmlspecialchars($nom_prof); ?><br>PROFESOR JEFE</th>
            <th align="center"><?php echo htmlspecialchars($nom_dir); ?><br>DIRECCIÓN</th>
        </tr>
    </table>
</form>
</center>
</body>
</html>
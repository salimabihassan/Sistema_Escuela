<?php
// =======================================================
// Archivo: registrar_notas_anotaciones_impS.php
// Autor: Salim I. Abi Hassan E.
// Fecha de actualización PHP: 15-10-2025 (PHP 8.2)
// Descripción: Informe de Notas Semestral
// =======================================================

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
$conexion = conectarse_escuela();

// Inicializar variables
$ci_alu = $ci_alu2 = $nom_alu = $grado = $periodo = $nom_prof = $nom_dir = $obs = "";
$prom_sem = $prom_sem2 = $prom_gen = $anota_n = $anota_p = $porc_asist = "";
$fecha_insc = date("d-m-Y");

// Obtener datos de imp
$consulta_imp = $conexion->prepare("SELECT * FROM imp WHERE id = 1");
$consulta_imp->execute();
$datos_imp = $consulta_imp->get_result()->fetch_assoc();
if ($datos_imp) {
    $ci_alu = $datos_imp['ci_alu'];
    $ci_alu2 = strtoupper($datos_imp['ci_alu2']);
}

// Consultar alumno
$consulta_alumno = $conexion->prepare("SELECT * FROM alumno WHERE ci_alu = ? AND ci_alu2 = ?");
$consulta_alumno->bind_param("ss", $ci_alu, $ci_alu2);
$consulta_alumno->execute();
$datos_alumno = $consulta_alumno->get_result()->fetch_assoc();

if ($datos_alumno && $datos_alumno['borrado'] == 0) {
    $nom_alu = $datos_alumno['nom_alu'];
    $grado = $datos_alumno['grado'];
    $periodo = $datos_alumno['periodo'];
    $id_alu = $datos_alumno['id_alu'];

    // Curso y profesor jefe
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

    // Director
    $consulta_dir = $conexion->prepare("SELECT * FROM prof WHERE director = 1");
    $consulta_dir->execute();
    $datos_dir = $consulta_dir->get_result()->fetch_assoc();
    $nom_dir = $datos_dir['nom_prof'] ?? '';

    // Asignaturas según grado
    if ($grado < 3) {
        $consulta_asig = $conexion->prepare("SELECT * FROM asignatura WHERE g1 = 'SI' AND borrado = 0 ORDER BY cod_asig");
    } elseif ($grado >= 3 && $grado < 7) {
        $consulta_asig = $conexion->prepare("SELECT * FROM asignatura WHERE g3 = 'SI' AND borrado = 0 ORDER BY cod_asig");
    } else {
        $consulta_asig = $conexion->prepare("SELECT * FROM asignatura WHERE g7 = 'SI' AND borrado = 0 ORDER BY cod_asig");
    }
    $consulta_asig->execute();
    $resultado_asig = $consulta_asig->get_result();

    // Notas primer semestre
    $notas1 = [];
    $prom_sem = 0;
    $s = 0;
    while ($asig = $resultado_asig->fetch_assoc()) {
        $cod_asig = $asig['cod_asig'];
        $nom_asig = $asig['nom_asig'];
        $consulta_nota = $conexion->prepare("SELECT * FROM notas$periodo WHERE ci_alu = ? AND ci_alu2 = ? AND periodo = ? AND semestre = '1' AND cod_asig = ? ORDER BY id_notas");
        $consulta_nota->bind_param("ssss", $ci_alu, $ci_alu2, $periodo, $cod_asig);
        $consulta_nota->execute();
        $res_nota = $consulta_nota->get_result();
        $notas = [];
        $prom = "";
        while ($row = $res_nota->fetch_assoc()) {
            $notas[] = ($row['nota'] > 0) ? number_format($row['nota'], 1, '.', '') : "";
            $prom = ($row['nota_prom'] > 0) ? number_format($row['nota_prom'], 1, '.', '') : "";
            $anota_n = $row['anot_n'] ?? "";
            $anota_p = $row['anot_p'] ?? "";
            $porc_asist = $row['porc_asist'] ?? "";
            $obs = $row['obs'] ?? "";
        }
        if ($cod_asig < 100 && $prom > 0) {
            $s++;
            $prom_sem += $prom;
        }
        if ($cod_asig == 100 && $prom !== "") {
            $val = $prom;
            $prom = match(true) {
                $val >= 1 && $val <= 3.9 => 'I',
                $val >= 4 && $val <= 4.9 => 'S',
                $val >= 5 && $val <= 5.9 => 'B',
                $val >= 6 && $val <= 7 => 'MB',
                default => $prom
            };
        }
        $notas1[] = [
            'cod_asig' => $cod_asig,
            'nom_asig' => $nom_asig,
            'notas' => $notas,
            'prom' => $prom
        ];
    }
    $prom_sem = ($s > 0) ? number_format($prom_sem / $s, 1, '.', '') : "";

    // Notas segundo semestre
    // Repetir consulta de asignaturas
    if ($grado < 3) {
        $consulta_asig = $conexion->prepare("SELECT * FROM asignatura WHERE g1 = 'SI' AND borrado = 0 ORDER BY cod_asig");
    } elseif ($grado >= 3 && $grado < 7) {
        $consulta_asig = $conexion->prepare("SELECT * FROM asignatura WHERE g3 = 'SI' AND borrado = 0 ORDER BY cod_asig");
    } else {
        $consulta_asig = $conexion->prepare("SELECT * FROM asignatura WHERE g7 = 'SI' AND borrado = 0 ORDER BY cod_asig");
    }
    $consulta_asig->execute();
    $resultado_asig2 = $consulta_asig->get_result();

    $notas2 = [];
    $prom_sem2 = 0;
    $g = 0;
    $prom_gen = 0;
    while ($asig = $resultado_asig2->fetch_assoc()) {
        $cod_asig = $asig['cod_asig'];
        $nom_asig = $asig['nom_asig'];
        $consulta_nota = $conexion->prepare("SELECT * FROM notas$periodo WHERE ci_alu = ? AND ci_alu2 = ? AND periodo = ? AND semestre = '2' AND cod_asig = ? ORDER BY id_notas");
        $consulta_nota->bind_param("ssss", $ci_alu, $ci_alu2, $periodo, $cod_asig);
        $consulta_nota->execute();
        $res_nota = $consulta_nota->get_result();
        $notas = [];
        $prom2 = "";
        while ($row = $res_nota->fetch_assoc()) {
            $notas[] = ($row['nota'] > 0) ? number_format($row['nota'], 1, '.', '') : "";
            $prom2 = ($row['nota_prom'] > 0) ? number_format($row['nota_prom'], 1, '.', '') : "";
        }
        if ($cod_asig < 100 && $prom2 > 0) {
            $prom_sem2 += $prom2;
            $pf = 0;
            if (isset($notas1[$g]['prom']) && $notas1[$g]['prom'] > 0) {
                $pf = ($notas1[$g]['prom'] + $prom2) / 2;
            } else {
                $pf = $prom2;
            }
            $notas2[] = [
                'cod_asig' => $cod_asig,
                'nom_asig' => $nom_asig,
                'notas' => $notas,
                'prom2' => $prom2,
                'prom_final' => ($pf > 0) ? number_format($pf, 1, '.', '') : ""
            ];
            if ($pf > 0) {
                $prom_gen += $pf;
                $g++;
            }
        } else {
            $notas2[] = [
                'cod_asig' => $cod_asig,
                'nom_asig' => $nom_asig,
                'notas' => $notas,
                'prom2' => $prom2,
                'prom_final' => $notas1[$g]['prom'] ?? ""
            ];
        }
        if ($cod_asig == 100 && $prom2 !== "") {
            $val = $prom2;
            $prom2 = match(true) {
                $val >= 1 && $val <= 3.9 => 'I',
                $val >= 4 && $val <= 4.9 => 'S',
                $val >= 5 && $val <= 5.9 => 'B',
                $val >= 6 && $val <= 7 => 'MB',
                default => $prom2
            };
        }
    }
    $prom_sem2 = ($g > 0) ? number_format($prom_sem2 / $g, 1, '.', '') : "";
    $prom_gen = ($g > 0) ? number_format($prom_gen / $g, 1, '.', '') : "";

    // Función para mostrar fecha en letra
    function obtenerFechaEnLetra($fecha) {
        $dia = conocerDiaSemanaFecha($fecha);
        $num = date("j", strtotime($fecha));
        $anno = date("Y", strtotime($fecha));
        $meses = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
        $mes = $meses[(date('m', strtotime($fecha)) * 1) - 1];
        return $dia . ', ' . $num . ' de ' . $mes . ' del ' . $anno;
    }
    function conocerDiaSemanaFecha($fecha) {
        $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
        $dia = $dias[date('w', strtotime($fecha))];
        return $dia;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>INFORME DE NOTAS SEMESTRAL</title>
<style>
body { font-family: 'Segoe UI', Arial, Helvetica, sans-serif; font-size: 14px; background: #f7f7f7; margin: 0; padding: 0; }
.caja { background-color: #e3eafc; padding: 18px 24px; border-radius: 12px; margin-bottom: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); width: 1024px; }
table { border-collapse: separate; border-spacing: 0; margin-bottom: 28px; width: 98%; background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
th, td { border: 1px solid #cfd8dc; padding: 8px 6px; text-align: center; }
th { background: #e9f0fb; font-weight: 600; }
tr:nth-child(even) td { background: #f5f8fd; }
.titulo { font-size: 22px; color: #00366C; font-weight: bold; margin-bottom: 10px; }
.leyenda { background: #e3eafc; border-radius: 8px; padding: 8px; margin-bottom: 12px; font-size: 13px; }
.firmas th { border: none; background: none; padding-top: 24px; font-size: 15px; }
.firmas .linea { border-top: 1px solid #00366C; width: 220px; margin: 0 auto 6px auto; }
@media print { body { background: #fff; } table { box-shadow: none; } }
</style>
</head>
<body>
<center>
<div class="caja">
    <img src="/Sistema_Escuela/imagenes/logo altas cumbres.jpg" width="178" height="89" style="float:left; margin-right:24px; border-radius:8px;" />
    <div style="text-align:left;">
        <div class="titulo">Escuela Básica Particular Nº 2271</div>
        <div><strong>Colegio "Altas Cumbres del Rosal"</strong></div>
        <div>Fono: 316 55 18 &nbsp; | &nbsp; caltascumbres@gmail.com</div>
        <div>RBD: 26392-3</div>
        <div style="margin-top:8px; font-size:13px; color:#555;">Fecha de emisión: <?php echo obtenerFechaEnLetra($fecha_insc); ?></div>
    </div>
    <div style="clear:both;"></div>
</div>

<table>
    <tr>
        <td colspan="4" class="titulo" style="background:#e3eafc;">INFORME DE NOTAS SEMESTRAL</td>
    </tr>
    <tr>
        <td colspan="4" style="background:#f5f8fd;"><strong>DATOS DEL ALUMNO</strong></td>
    </tr>
    <tr>
        <td><strong>Cédula:</strong> <?php echo number_format($ci_alu, 0, ' ', '.') . '-' . $ci_alu2; ?></td>
        <td><strong>Nombre:</strong> <?php echo htmlspecialchars($nom_alu); ?></td>
        <td><strong>Curso:</strong> <?php
            $grados = ['1' => '1ro', '2' => '2do', '3' => '3ro', '4' => '4to', '5' => '5to', '6' => '6to', '7' => '7mo', '8' => '8vo'];
            echo $grados[$grado] ?? '';
        ?></td>
        <td><strong>Periodo Escolar:</strong> <?php echo htmlspecialchars($periodo); ?></td>
    </tr>
</table>

<table>
    <tr>
        <th rowspan="2">Nro.</th>
        <th rowspan="2">Asignatura</th>
        <th colspan="10">Notas 1er Semestre</th>
        <th>Prom</th>
        <th rowspan="2"></th>
        <th colspan="10">Notas 2do Semestre</th>
        <th>Prom</th>
        <th rowspan="2"></th>
        <th rowspan="2">Prom Final</th>
    </tr>
    <tr>
        <?php for ($i = 0; $i < 10; $i++) echo "<th>$i</th>"; ?>
        <?php for ($i = 0; $i < 10; $i++) echo "<th>$i</th>"; ?>
    </tr>
    <?php
    $nro = 1;
    foreach ($notas1 as $idx => $asig1) {
        $asig2 = $notas2[$idx] ?? ['notas'=>[], 'prom2'=>'', 'prom_final'=>''];
        echo "<tr>";
        echo "<td>$nro</td>";
        echo "<td align='left'>{$asig1['cod_asig']}. {$asig1['nom_asig']}</td>";
        for ($i = 0; $i < 10; $i++) {
            echo "<td>" . ($asig1['notas'][$i] ?? '') . "</td>";
        }
        echo "<td><strong>{$asig1['prom']}</strong></td>";
        echo "<td></td>";
        for ($i = 0; $i < 10; $i++) {
            echo "<td>" . ($asig2['notas'][$i] ?? '') . "</td>";
        }
        echo "<td><strong>{$asig2['prom2']}</strong></td>";
        echo "<td></td>";
        echo "<td><strong>{$asig2['prom_final']}</strong></td>";
        echo "</tr>";
        $nro++;
    }
    ?>
    <tr>
        <td colspan="12" align="right" style="background:#e3eafc;">Promedio 1er Semestre</td>
        <td colspan="2" style="background:#ccffff;"><strong><?php echo $prom_sem; ?></strong></td>
        <td colspan="12" align="right" style="background:#e3eafc;">Promedio 2do Semestre</td>
        <td colspan="2" style="background:#ccffff;"><strong><?php echo $prom_sem2; ?></strong></td>
    </tr>
    <tr>
        <td colspan="2" style="background:#e3eafc;"><strong>Anotaciones</strong></td>
        <td colspan="3"></td>
        <td colspan="2" align="left"><strong>Negativas:</strong> <?php echo $anota_n; ?></td>
        <td colspan="2" align="left"><strong>Positivas:</strong> <?php echo $anota_p; ?></td>
        <td colspan="2" align="left"><strong>Asistencia:</strong> <?php echo $porc_asist; ?>%</td>
        <td colspan="10"></td>
        <td colspan="6" align="right" style="background:#e3eafc;"><strong>Promedio General</strong></td>
        <td colspan="2" style="background:#ccffff;"><strong><?php echo $prom_gen; ?></strong></td>
    </tr>
    <tr>
        <td colspan="30" align="left" style="background:#f5f8fd;"><strong>Observaciones:</strong> <?php echo htmlspecialchars($obs); ?></td>
    </tr>
</table>

<div class="leyenda">
    <strong>LEYENDA DE CALIFICACIONES:</strong>
    &nbsp; I: Insuficiente (1.0 - 3.9) &nbsp; | &nbsp;
    S: Suficiente (4.0 - 4.9) &nbsp; | &nbsp;
    B: Bueno (5.0 - 5.9) &nbsp; | &nbsp;
    MB: Muy Bueno (6.0 - 7.0)
</div>

<table class="firmas" style="margin-top:30px;">
    <tr>
        <th>
            <div class="linea"></div>
            <?php echo htmlspecialchars($nom_prof); ?><br>PROFESOR JEFE
        </th>
        <th>
            <div class="linea"></div>
            <?php echo htmlspecialchars($nom_dir); ?><br>DIRECCIÓN
        </th>
    </tr>
</table>
</center>
</body>
</html>
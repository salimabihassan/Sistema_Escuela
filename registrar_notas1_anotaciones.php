<?php
/**
 * MÓDULO: Informe de Notas con Anotaciones (1er y 2do Semestre)
 * Versión: 1.0 (PHP 8.2.12 compatible)
 * Autor: Sistema Escolar
 */
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("conexiones.php");
session_start();

// Validar sesión
$required = ['nombre_apellido', 'cedula', 'ci_usu2', 'grado', 'nivel_intranet'];
foreach ($required as $key) {
    if (!isset($_SESSION[$key])) {
        die("Acceso no autorizado.");
    }
}
$grado1 = $_SESSION["grado"];
$nivel_usuario = $_SESSION["nivel_intranet"];

// Función de escape
function esc($str, $conn) {
    return mysqli_real_escape_string($conn, trim($str));
}

$conexion = conectarse_escuela();
if (!$conexion) die("Error de conexión.");

// Inicializar variables
$ci_alu = $_POST['ci_alu'] ?? '';
$ci_alu2 = $_POST['ci_alu2'] ?? '';
$nom_alu = $_POST['nom_alu'] ?? '';
$grado = $_POST['grado'] ?? '';
$final = (int)($_POST['final'] ?? 0);
$periodo = $_POST['periodo'] ?? '';
$prom_sem = $_POST['prom_sem'] ?? '';
$prom_gen = $_POST['prom_gen'] ?? '';
$anota_n = $_POST['anota_n'] ?? '';
$anota_p = $_POST['anota_p'] ?? '';
$porc_asist = $_POST['porc_asist'] ?? '';
$obs = $_POST['obs'] ?? '';
$btnaccion = $_POST['btnaccion'] ?? '';

$fecha_insc = date("d/m/Y");

// Inicializar arrays
$nota = $mota = $prom = $prom2 = $prom_final = $id_notas = $id_notas2 = [];
$total_consultar = 0;

// ACCIONES
switch ($btnaccion) {
    case "Imprimir Inf Parc":
    case "Imprimir Inf Final":
    case "Imprimir Inf Sem":
        if (empty($ci_alu) || empty($ci_alu2)) {
            echo '<script>alert("Debe completar la cédula del estudiante");</script>';
            break;
        }
        $ci_alu2 = strtoupper($ci_alu2);
        $obs = strtoupper($obs);

        $consulta = "SELECT * FROM alumno WHERE ci_alu = '" . esc($ci_alu, $conexion) . "' AND ci_alu2 = '" . esc($ci_alu2, $conexion) . "'";
        $resultado = mysqli_query($conexion, $consulta);
        $total_consultar = mysqli_num_rows($resultado);

        if ($total_consultar > 0) {
            $datos = mysqli_fetch_array($resultado);
            if ($datos['borrado'] == 0) {
                $nom_alu = $datos['nom_alu'];
                $grado = $datos['grado'];
                $periodo = $datos['periodo'];

                // Verificar permisos
                if ($nivel_usuario == 0 && $grado != $grado1) {
                    $grado_texto = match($grado1) {
                        '1' => '1er', '2' => '2do', '3' => '3er', '4' => '4to',
                        '5' => '5to', '6' => '6to', '7' => '7mo', '8' => '8vo'
                    };
                    echo "<script>alert('Acceso solo al {$grado_texto} Grado');</script>";
                    $nom_alu = $grado = $periodo = "";
                    break;
                }

                // Cargar asignaturas
                if ($grado < 3) $consulta_asig = "SELECT * FROM asignatura WHERE g1='SI' AND borrado=0 ORDER BY cod_asig";
                elseif ($grado >= 3 && $grado <= 6) $consulta_asig = "SELECT * FROM asignatura WHERE g3='SI' AND borrado=0 ORDER BY cod_asig";
                else $consulta_asig = "SELECT * FROM asignatura WHERE g7='SI' AND borrado=0 ORDER BY cod_asig";

                $resultado_asig = mysqli_query($conexion, $consulta_asig);
                $total_sql2 = mysqli_num_rows($resultado_asig);

                if ($total_sql2 > 0) {
                    // PRIMER SEMESTRE
                    $s = 0; $prom_sem = 0; $t = 0;
                    while ($asig = mysqli_fetch_array($resultado_asig)) {
                        $t++;
                        $cod = $asig['cod_asig'];
                        $sql_notas = "SELECT * FROM notas{$periodo} WHERE ci_alu='" . esc($ci_alu, $conexion) . "' AND ci_alu2='" . esc($ci_alu2, $conexion) . "' AND periodo='" . esc($periodo, $conexion) . "' AND semestre='1' AND cod_asig='$cod' ORDER BY id_notas";
                        $res_notas = mysqli_query($conexion, $sql_notas);
                        $i = 0;
                        if (mysqli_num_rows($res_notas) > 0) {
                            while ($nota_data = mysqli_fetch_array($res_notas)) {
                                $id_notas[$t][$i] = $nota_data['id_notas'];
                                $nota[$t][$i] = $nota_data['nota'];
                                $prom[$t] = $nota_data['nota_prom'];
                                $anota_n = $nota_data['anot_n'] ?: "";
                                $anota_p = $nota_data['anot_p'] ?: "";
                                $porc_asist = $nota_data['porc_asist'] ?: "";
                                $obs = $nota_data['obs'];
                                $i++;
                            }
                        }
                        if ($cod < 100 && isset($prom[$t]) && $prom[$t] > 0) {
                            $s++;
                            $prom_sem += $prom[$t];
                        }
                        if ($cod == 100 && isset($prom[$t])) {
                            $val = $prom[$t];
                            $prom[$t] = match(true) {
                                $val >= 1 && $val <= 3.9 => 'I',
                                $val >= 4 && $val <= 4.9 => 'S',
                                $val >= 5 && $val <= 5.9 => 'B',
                                $val >= 6 && $val <= 7 => 'MB',
                                default => $prom[$t]
                            };
                        }
                    }
                    $prom_sem = ($s > 0) ? number_format($prom_sem / $s, 1, '.', '') : "";

                    // SEGUNDO SEMESTRE
                    $resultado_asig = mysqli_query($conexion, $consulta_asig);
                    $s2 = 0; $prom_sem2 = 0; $g = 0; $prom_gen = 0; $t = 0;
                    while ($asig = mysqli_fetch_array($resultado_asig)) {
                        $t++;
                        $cod = $asig['cod_asig'];
                        $sql_notas = "SELECT * FROM notas{$periodo} WHERE ci_alu='" . esc($ci_alu, $conexion) . "' AND ci_alu2='" . esc($ci_alu2, $conexion) . "' AND periodo='" . esc($periodo, $conexion) . "' AND semestre='2' AND cod_asig='$cod' ORDER BY id_notas";
                        $res_notas = mysqli_query($conexion, $sql_notas);
                        $i = 0;
                        if (mysqli_num_rows($res_notas) > 0) {
                            while ($nota_data = mysqli_fetch_array($res_notas)) {
                                $id_notas2[$t][$i] = $nota_data['id_notas'];
                                $mota[$t][$i] = $nota_data['nota'];
                                $prom2[$t] = $nota_data['nota_prom'];
                                $i++;
                            }
                        }
                        if ($cod < 100 && isset($prom2[$t]) && $prom2[$t] > 0) {
                            $s2++;
                            $prom_sem2 += $prom2[$t];
                            $pf = (isset($prom[$t]) && $prom[$t] > 0) ? ($prom[$t] + $prom2[$t]) / 2 : $prom2[$t];
                            $prom_final[$t] = $pf;
                            if ($pf > 0) {
                                $g++;
                                $prom_gen += $pf;
                            }
                        }
                        if (!isset($prom2[$t]) && $cod == 100) {
                            $prom_final[$t] = $prom[$t] ?? "";
                        }
                        if ($cod == 100 && isset($prom2[$t])) {
                            $val = $prom2[$t];
                            $prom2[$t] = match(true) {
                                $val >= 1 && $val <= 3.9 => 'I',
                                $val >= 4 && $val <= 4.9 => 'S',
                                $val >= 5 && $val <= 5.9 => 'B',
                                $val >= 6 && $val <= 7 => 'MB',
                                default => $prom2[$t]
                            };
                        }
                    }
                    $prom_sem2 = ($s2 > 0) ? number_format($prom_sem2 / $s2, 1, '.', '') : "";
                    $prom_gen = ($g > 0) ? number_format($prom_gen / $g, 1, '.', '') : "";

                    if ($btnaccion == "Imprimir Inf Parc" || $btnaccion == "Imprimir Inf Sem") {
                        $prom_gen = round($prom_gen, 1);
                        $prom_gen = substr((string)$prom_gen, 0, 3);
                    }

                    // Auditoría
                    $cod_reg = $grado . $periodo . 'ced_alu:' . $ci_alu . '-' . $ci_alu2;
                    $ci_usu = esc($_SESSION["cedula"], $conexion);
                    $ci_usu2 = esc($_SESSION["ci_usu2"], $conexion);
                    $desc_reg = match($btnaccion) {
                        "Imprimir Inf Parc" => "Imprimió Inf Notas Parcial",
                        "Imprimir Inf Sem" => "Imprimió Inf Notas Semestral",
                        default => "Imprimió Inf Notas Final"
                    };
                    $FechaMySQL = date("Y-m-d");
                    $sql_aud = "INSERT INTO auditoria(cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES ('$cod_reg', '$ci_usu', '$ci_usu2', '$desc_reg', 'Consultas, Inf. Notas Semestral', '$FechaMySQL')";
                    mysqli_query($conexion, $sql_aud);

                    // Actualizar tabla temporal
                    $upd_imp = "UPDATE imp SET ci_alu='" . esc($ci_alu, $conexion) . "', ci_alu2='" . esc($ci_alu2, $conexion) . "' WHERE id='1'";
                    mysqli_query($conexion, $upd_imp);

                    // Abrir impresión
                    $archivo = match($btnaccion) {
                        "Imprimir Inf Parc" => "registrar_notas_anotaciones_impP.php",
                        "Imprimir Inf Sem" => "registrar_notas_anotaciones_impS.php",
                        default => "registrar_notas_anotaciones_imp.php"
                    };
                    echo "<script>window.open('$archivo', 'ventana', 'width=385,height=180,top=0,left=0,status,toolbar=1,scrollbars,location');</script>";
                } else {
                    echo '<script>alert("No hay notas en el Primer Semestre");</script>';
                }
            } else {
                echo '<script>alert("Alumno eliminado");</script>';
                $nom_alu = $grado = $periodo = "";
            }
        } else {
            echo '<script>alert("Cédula no registrada");</script>';
            $nom_alu = $grado = $periodo = "";
        }
        break;

    case "Consultar":
        if (empty($ci_alu) || empty($ci_alu2)) {
            echo '<script>alert("Complete la cédula");</script>';
            break;
        }
        $ci_alu2 = strtoupper($ci_alu2);
        $obs = strtoupper($obs);

        $consulta = "SELECT * FROM alumno WHERE ci_alu = '" . esc($ci_alu, $conexion) . "' AND ci_alu2 = '" . esc($ci_alu2, $conexion) . "'";
        $resultado = mysqli_query($conexion, $consulta);
        $total_consultar = mysqli_num_rows($resultado);

        if ($total_consultar > 0) {
            $datos = mysqli_fetch_array($resultado);
            if ($datos['borrado'] == 0) {
                $nom_alu = $datos['nom_alu'];
                $grado = $datos['grado'];
                $periodo = $datos['periodo'];

                if ($nivel_usuario == 0 && $grado != $grado1) {
                    $grado_texto = match($grado1) {
                        '1' => '1er', '2' => '2do', '3' => '3er', '4' => '4to',
                        '5' => '5to', '6' => '6to', '7' => '7mo', '8' => '8vo'
                    };
                    echo "<script>alert('Acceso solo al {$grado_texto} Grado');</script>";
                    $nom_alu = $grado = $periodo = "";
                    $total_consultar = 0;
                    break;
                }

                // Cargar asignaturas
                if ($grado < 3) $consulta_asig = "SELECT * FROM asignatura WHERE g1='SI' AND borrado=0 ORDER BY cod_asig";
                elseif ($grado >= 3 && $grado <= 6) $consulta_asig = "SELECT * FROM asignatura WHERE g3='SI' AND borrado=0 ORDER BY cod_asig";
                else $consulta_asig = "SELECT * FROM asignatura WHERE g7='SI' AND borrado=0 ORDER BY cod_asig";

                $resultado_asig = mysqli_query($conexion, $consulta_asig);
                $total_sql2 = mysqli_num_rows($resultado_asig);

                if ($total_sql2 > 0) {
                    // PRIMER SEMESTRE
                    $s = 0; $prom_sem = 0; $t = 0;
                    while ($asig = mysqli_fetch_array($resultado_asig)) {
                        $t++;
                        $cod = $asig['cod_asig'];
                        $sql_notas = "SELECT * FROM notas{$periodo} WHERE ci_alu='" . esc($ci_alu, $conexion) . "' AND ci_alu2='" . esc($ci_alu2, $conexion) . "' AND periodo='" . esc($periodo, $conexion) . "' AND semestre='1' AND cod_asig='$cod' ORDER BY id_notas";
                        $res_notas = mysqli_query($conexion, $sql_notas);
                        $i = 0;
                        if (mysqli_num_rows($res_notas) > 0) {
                            while ($nota_data = mysqli_fetch_array($res_notas)) {
                                $id_notas[$t][$i] = $nota_data['id_notas'];
                                $nota[$t][$i] = $nota_data['nota'];
                                $prom[$t] = $nota_data['nota_prom'];
                                $anota_n = $nota_data['anot_n'] ?: "";
                                $anota_p = $nota_data['anot_p'] ?: "";
                                $porc_asist = $nota_data['porc_asist'] ?: "";
                                $obs = $nota_data['obs'];
                                $i++;
                            }
                        }
                        if ($cod < 100 && isset($prom[$t]) && $prom[$t] > 0) {
                            $s++;
                            $prom_sem += $prom[$t];
                        }
                        if ($cod == 100 && isset($prom[$t])) {
                            $val = $prom[$t];
                            $prom[$t] = match(true) {
                                $val >= 1 && $val <= 3.9 => 'I',
                                $val >= 4 && $val <= 4.9 => 'S',
                                $val >= 5 && $val <= 5.9 => 'B',
                                $val >= 6 && $val <= 7 => 'MB',
                                default => $prom[$t]
                            };
                        }
                    }
                    $prom_sem = ($s > 0) ? number_format($prom_sem / $s, 1, '.', '') : "";

                    // SEGUNDO SEMESTRE
                    $resultado_asig = mysqli_query($conexion, $consulta_asig);
                    $s2 = 0; $prom_sem2 = 0; $g = 0; $prom_gen = 0; $t = 0;
                    while ($asig = mysqli_fetch_array($resultado_asig)) {
                        $t++;
                        $cod = $asig['cod_asig'];
                        $sql_notas = "SELECT * FROM notas{$periodo} WHERE ci_alu='" . esc($ci_alu, $conexion) . "' AND ci_alu2='" . esc($ci_alu2, $conexion) . "' AND periodo='" . esc($periodo, $conexion) . "' AND semestre='2' AND cod_asig='$cod' ORDER BY id_notas";
                        $res_notas = mysqli_query($conexion, $sql_notas);
                        $i = 0;
                        if (mysqli_num_rows($res_notas) > 0) {
                            while ($nota_data = mysqli_fetch_array($res_notas)) {
                                $id_notas2[$t][$i] = $nota_data['id_notas'];
                                $mota[$t][$i] = $nota_data['nota'];
                                $prom2[$t] = $nota_data['nota_prom'];
                                $final = $nota_data['final'];
                                $i++;
                            }
                        }
                        if ($cod < 100 && isset($prom2[$t]) && $prom2[$t] > 0) {
                            $s2++;
                            $prom_sem2 += $prom2[$t];
                            if (isset($prom[$t]) && $prom[$t] > 0) {
                                $prom_final[$t] = ($prom[$t] + $prom2[$t]) / 2;
                            } else {
                                $prom_final[$t] = $prom2[$t];
                            }
                            if ($prom_final[$t] > 0) {
                                $g++;
                                $prom_gen += $prom_final[$t];
                            }
                        }
                        if (!isset($prom2[$t]) && $cod == 100) {
                            $prom_final[$t] = $prom[$t] ?? "";
                        }
                        if ($cod == 100 && isset($prom2[$t])) {
                            $val = $prom2[$t];
                            $prom2[$t] = match(true) {
                                $val >= 1 && $val <= 3.9 => 'I',
                                $val >= 4 && $val <= 4.9 => 'S',
                                $val >= 5 && $val <= 5.9 => 'B',
                                $val >= 6 && $val <= 7 => 'MB',
                                default => $prom2[$t]
                            };
                        }
                    }
                    $prom_sem2 = ($s2 > 0) ? number_format($prom_sem2 / $s2, 1, '.', '') : "";
                    $prom_gen = ($g > 0) ? number_format($prom_gen / $g, 1, '.', '') : "";
                    $prom_gen = round($prom_gen, 1);
                    $prom_gen = substr((string)$prom_gen, 0, 3);
                } else {
                    echo '<script>alert("No hay notas en el Primer Semestre");</script>';
                }
            } else {
                echo '<script>alert("Alumno eliminado");</script>';
                $nom_alu = $grado = $periodo = "";
                $total_consultar = 0;
            }
        } else {
            echo '<script>alert("Cédula no registrada");</script>';
            $nom_alu = $grado = $periodo = "";
            $total_consultar = 0;
        }
        break;

    case "Guardar":
        if (empty($ci_alu) || empty($ci_alu2)) {
            echo '<script>alert("Complete la cédula");</script>';
            break;
        }
        $obs = strtoupper($obs);
        $y = (int)($_POST['total_sql2'] ?? 0);
        for ($t = 1; $t <= $y; $t++) {
            $prom_final1 = $_POST["prom_final{$t}"] ?? '';
            for ($i = 0; $i < 10; $i++) {
                $id1 = $_POST["id_notas{$t}{$i}"] ?? '';
                $id2 = $_POST["id_notas2{$t}{$i}"] ?? '';
                if ($id1) {
                    $upd = "UPDATE notas{$periodo} SET prom_gen='" . esc($prom_gen, $conexion) . "', prom_sem='" . esc($prom_sem, $conexion) . "', anot_n='" . esc($anota_n, $conexion) . "', anot_p='" . esc($anota_p, $conexion) . "', prom_final='" . esc($prom_final1, $conexion) . "', obs='" . esc($obs, $conexion) . "', porc_asist='" . esc($porc_asist, $conexion) . "' WHERE id_notas='" . esc($id1, $conexion) . "'";
                    mysqli_query($conexion, $upd);
                }
                if ($id2) {
                    $upd2 = "UPDATE notas{$periodo} SET prom_gen='" . esc($prom_gen, $conexion) . "', prom_sem='" . esc($prom_sem, $conexion) . "', anot_n='" . esc($anota_n, $conexion) . "', anot_p='" . esc($anota_p, $conexion) . "', prom_final='" . esc($prom_final1, $conexion) . "', obs='" . esc($obs, $conexion) . "', porc_asist='" . esc($porc_asist, $conexion) . "' WHERE id_notas='" . esc($id2, $conexion) . "'";
                    mysqli_query($conexion, $upd2);
                }
            }
        }
        echo '<script>alert("Datos guardados con éxito");</script>';
        break;

    case "Desbloquear":
        if (empty($ci_alu) || empty($ci_alu2)) {
            echo '<script>alert("Complete la cédula");</script>';
            break;
        }
        $ci_alu2 = strtoupper($ci_alu2);
        $obs = strtoupper($obs);

        $consulta = "SELECT * FROM alumno WHERE ci_alu = '" . esc($ci_alu, $conexion) . "' AND ci_alu2 = '" . esc($ci_alu2, $conexion) . "'";
        $resultado = mysqli_query($conexion, $consulta);
        $total_consultar = mysqli_num_rows($resultado);

        if ($total_consultar > 0) {
            $datos = mysqli_fetch_array($resultado);
            if ($datos['borrado'] == 0) {
                $nom_alu = $datos['nom_alu'];
                $grado = $datos['grado'];
                $periodo = $datos['periodo'];

                if ($nivel_usuario == 0 && $grado != $grado1) {
                    $grado_texto = match($grado1) {
                        '1' => '1er', '2' => '2do', '3' => '3er', '4' => '4to',
                        '5' => '5to', '6' => '6to', '7' => '7mo', '8' => '8vo'
                    };
                    echo "<script>alert('Acceso solo al {$grado_texto} Grado');</script>";
                    $nom_alu = $grado = $periodo = "";
                    break;
                }

                // Cargar asignaturas
                if ($grado < 3) $consulta_asig = "SELECT * FROM asignatura WHERE g1='SI' AND borrado=0 ORDER BY cod_asig";
                elseif ($grado >= 3 && $grado <= 6) $consulta_asig = "SELECT * FROM asignatura WHERE g3='SI' AND borrado=0 ORDER BY cod_asig";
                else $consulta_asig = "SELECT * FROM asignatura WHERE g7='SI' AND borrado=0 ORDER BY cod_asig";

                $resultado_asig = mysqli_query($conexion, $consulta_asig);
                $total_sql2 = mysqli_num_rows($resultado_asig);

                if ($total_sql2 > 0) {
                    $x = 0;
                    // Primer semestre
                    while ($asig = mysqli_fetch_array($resultado_asig)) {
                        $cod = $asig['cod_asig'];
                        $sql_notas = "SELECT id_notas FROM notas{$periodo} WHERE ci_alu='" . esc($ci_alu, $conexion) . "' AND ci_alu2='" . esc($ci_alu2, $conexion) . "' AND periodo='" . esc($periodo, $conexion) . "' AND semestre='1' AND cod_asig='$cod'";
                        $res_notas = mysqli_query($conexion, $sql_notas);
                        while ($row = mysqli_fetch_array($res_notas)) {
                            $upd = "UPDATE notas{$periodo} SET final='0', semestral='0' WHERE id_notas='" . esc($row['id_notas'], $conexion) . "'";
                            mysqli_query($conexion, $upd);
                            if ($x < 1) {
                                echo '<script>alert("Notas desbloqueadas exitosamente");</script>';
                                $x++;
                            }
                        }
                    }
                    // Segundo semestre
                    $resultado_asig = mysqli_query($conexion, $consulta_asig);
                    while ($asig = mysqli_fetch_array($resultado_asig)) {
                        $cod = $asig['cod_asig'];
                        $sql_notas = "SELECT id_notas FROM notas{$periodo} WHERE ci_alu='" . esc($ci_alu, $conexion) . "' AND ci_alu2='" . esc($ci_alu2, $conexion) . "' AND periodo='" . esc($periodo, $conexion) . "' AND semestre='2' AND cod_asig='$cod'";
                        $res_notas = mysqli_query($conexion, $sql_notas);
                        while ($row = mysqli_fetch_array($res_notas)) {
                            $upd = "UPDATE notas{$periodo} SET final='0', semestral='0' WHERE id_notas='" . esc($row['id_notas'], $conexion) . "'";
                            mysqli_query($conexion, $upd);
                        }
                    }

                    // Auditoría
                    $cod_reg = $grado . $periodo . 'ced_alu:' . $ci_alu . '-' . $ci_alu2;
                    $ci_usu = esc($_SESSION["cedula"], $conexion);
                    $ci_usu2 = esc($_SESSION["ci_usu2"], $conexion);
                    $FechaMySQL = date("Y-m-d");
                    $sql_aud = "INSERT INTO auditoria(cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES ('$cod_reg', '$ci_usu', '$ci_usu2', 'Desbloqueó Notas', 'Consultas, Inf. Notas Semestral', '$FechaMySQL')";
                    mysqli_query($conexion, $sql_aud);
                }
            } else {
                echo '<script>alert("Alumno eliminado");</script>';
            }
        } else {
            echo '<script>alert("Cédula no registrada");</script>';
        }
        break;
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>INFORME DE NOTAS 1RO Y 2DO SEMESTRE</title>
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
function finalVentanas() {
    if(confirm('Una vez impresa las Notas, no se podrá modificar las notas, ¿Deseas continuar?')) {
        window.open("registrar_notas_anotaciones_imp.php", "ventana", "width=385,height=180,top=0,left=0,status,toolbar=1,scrollbars,location");
    } else {
        alert('Operación Cancelada');
    }
}
function semestralVentanas() {
    if(confirm('Una vez impresa las Notas, no se podrá modificar las notas, ¿Deseas continuar?')) {
        window.open("registrar_notas_anotaciones_impS.php", "ventana", "width=385,height=180,top=0,left=0,status,toolbar=1,scrollbars,location");
    } else {
        alert('Operación Cancelada');
    }
}
function ValNumero(Control) {
    Control.value = Control.value.replace(/[^0-9]/g, '');
}
</script>
</head>
<body>
<center>
<form method="post" action="registrar_notas1_anotaciones.php">
<div class="caja">
  <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
    INFORME DE NOTAS
  </font>
  Fecha: <?php echo htmlspecialchars($fecha_insc); ?>
  <input type="hidden" name="fecha_insc2" value="<?php echo htmlspecialchars($fecha_insc); ?>" />
</div>

<table width="599" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr><td height="26" colspan="7" bgcolor="#E9E9E9"><strong>LOS DATOS DEL ALUMNO:</strong></td></tr>
  <tr>
    <td width="53" bgcolor="#F9F9F9">Cédula:</td>
    <td width="53" bgcolor="#F9F9F9">
      <input name="ci_alu" type="text" value="<?php echo htmlspecialchars($ci_alu); ?>" onkeyup="ValNumero(this);" maxlength="8" size="9" />
    </td>
    <td bgcolor="#F9F9F9">
      <input name="ci_alu2" type="text" value="<?php echo htmlspecialchars($ci_alu2); ?>" onkeyup="ValNumero(this);" maxlength="1" size="1" />
    </td>
    <td width="54" bgcolor="#F9F9F9">
      <input type="submit" name="btnaccion" value="Consultar" />
    </td>
    <td colspan="2" bgcolor="#F9F9F9">Nombre:</td>
    <td width="99" bgcolor="#F9F9F9">
      <input name="nom_alu" type="text" value="<?php echo htmlspecialchars($nom_alu); ?>" size="50" maxlength="80" readonly />
    </td>
  </tr>
  <tr>
    <td height="6" colspan="2" align="right" bgcolor="#E9E9E9">Curso:</td>
    <td colspan="3" bgcolor="#E9E9E9">
      <input name="grado" type="text" value="<?php echo htmlspecialchars($grado); ?>" size="4" maxlength="4" readonly />
    </td>
    <td width="123" bgcolor="#E9E9E9">Periodo Escolar:</td>
    <td bgcolor="#E9E9E9">
      <input name="periodo" type="text" value="<?php echo htmlspecialchars($periodo); ?>" size="10" maxlength="11" readonly />
    </td>
  </tr>
</table>

<?php if ($total_consultar > 0): ?>
<?php
if ($grado < 3) $consulta_sql = "SELECT * FROM asignatura WHERE g1='SI' AND borrado=0 ORDER BY cod_asig";
elseif ($grado >= 3 && $grado <= 6) $consulta_sql = "SELECT * FROM asignatura WHERE g3='SI' AND borrado=0 ORDER BY cod_asig";
else $consulta_sql = "SELECT * FROM asignatura WHERE g7='SI' AND borrado=0 ORDER BY cod_asig";

$resultado_sql = mysqli_query($conexion, $consulta_sql);
$total_sql = mysqli_num_rows($resultado_sql);
?>
<table align="center" cellpadding="2" cellspacing="2">
  <tr>
    <th width="73" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Nro.</strong></th>
    <th colspan="7" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Asignatura</strong></th>
    <th colspan="10" align="center" bgcolor="#E9E9E9"><strong>Notas Primer Semestre</strong></th>
    <th width="35" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Prom</strong></th>
    <th width="4" rowspan="2" align="center" bgcolor="#FFFFFF">&nbsp;</th>
    <th colspan="10" align="center" bgcolor="#E9E9E9"><strong>Notas Segundo Semestre</strong></th>
    <th width="35" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Prom</strong></th>
    <th width="2" rowspan="2" align="center" bgcolor="#FFFFFF">&nbsp;</th>
    <th width="53" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Prom Final</strong></th>
  </tr>
  <tr>
    <?php for ($col = 0; $col < 10; $col++): ?>
      <th width="24" align="center" bgcolor="#E9E9E9"><strong><?php echo $col; ?></strong></th>
    <?php endfor; ?>
    <?php for ($col = 0; $col < 10; $col++): ?>
      <th width="24" align="center" bgcolor="#E9E9E9"><strong><?php echo $col; ?></strong></th>
    <?php endfor; ?>
  </tr>

  <?php
  $fila = 1;
  $t = 1;
  while ($datos_sql = mysqli_fetch_array($resultado_sql)) {
      $color = ($fila % 2 == 0) ? "#FFFFFF" : "#CCEEFF";
      $fila++;
      ?>
      <tr bgcolor="<?php echo $color; ?>">
        <th align="center"><?php echo $fila - 1; ?></th>
        <th colspan="7" align="left">
          <?php echo htmlspecialchars($datos_sql['cod_asig'] . '.- ' . $datos_sql['nom_asig']); ?>
          <input type="hidden" name="txtnom_asig<?php echo $t; ?>" value="<?php echo htmlspecialchars($datos_sql['nom_asig']); ?>" />
          <input type="hidden" name="txtcod_asig<?php echo $t; ?>" value="<?php echo htmlspecialchars($datos_sql['cod_asig']); ?>" />
          <input type="hidden" name="semestre" value="" />
          <input type="hidden" name="total_periodo" value="" />
          <input type="hidden" name="final" value="<?php echo htmlspecialchars($final); ?>" />
        </th>
        <?php for ($i = 0; $i < 10; $i++): ?>
          <th align="center" valign="middle">
            <?php echo isset($nota[$t][$i]) ? htmlspecialchars(number_format((float)$nota[$t][$i], 1, '.', '')) : ''; ?>
            <input type="hidden" name="id_notas<?php echo $t . $i; ?>" value="<?php echo isset($id_notas[$t][$i]) ? htmlspecialchars($id_notas[$t][$i]) : ''; ?>" />
            <input type="hidden" name="nota<?php echo $t . $i; ?>" value="<?php echo isset($nota[$t][$i]) ? htmlspecialchars($nota[$t][$i]) : ''; ?>" />
          </th>
        <?php endfor; ?>
        <th align="center" valign="middle"><strong><?php echo isset($prom[$t]) ? htmlspecialchars($prom[$t]) : ''; ?></strong></th>
        <th align="center" bgcolor="#FFFFFF">&nbsp;</th>
        <?php for ($i = 0; $i < 10; $i++): ?>
          <th align="center" valign="middle">
            <?php echo isset($mota[$t][$i]) ? htmlspecialchars(number_format((float)$mota[$t][$i], 1, '.', '')) : ''; ?>
            <input type="hidden" name="id_notas2<?php echo $t . $i; ?>" value="<?php echo isset($id_notas2[$t][$i]) ? htmlspecialchars($id_notas2[$t][$i]) : ''; ?>" />
            <input type="hidden" name="mota<?php echo $t . $i; ?>" value="<?php echo isset($mota[$t][$i]) ? htmlspecialchars($mota[$t][$i]) : ''; ?>" />
          </th>
        <?php endfor; ?>
        <th align="center" valign="middle"><strong><?php echo isset($prom2[$t]) ? htmlspecialchars($prom2[$t]) : ''; ?></strong></th>
        <th align="center" valign="middle" bgcolor="#FFFFFF">&nbsp;</th>
        <th align="center" valign="middle"><strong><?php echo isset($prom_final[$t]) ? htmlspecialchars($prom_final[$t]) : ''; ?></strong></th>
      </tr>
      <?php $t++; ?>
  <?php } ?>

  <tr>
    <th colspan="8" align="center">
      <input type="hidden" name="total_consultar" value="<?php echo $total_consultar; ?>" />
      <input type="hidden" name="total_sql" value="<?php echo $total_sql; ?>" />
      <input type="hidden" name="total_sql2" value="<?php echo $total_sql2; ?>" />
    </th>
    <th colspan="4" align="center" bgcolor="#E9E9E9">Promedio 1er Semestre</th>
    <th align="center" bgcolor="#CCFFFF"><strong><?php echo htmlspecialchars($prom_sem); ?></strong></th>
    <th align="center" bgcolor="#FFFFFF">&nbsp;</th>
    <th colspan="4" align="center" bgcolor="#E9E9E9">Promedio 2do Semestre</th>
    <th align="center" bgcolor="#CCFFFF"><strong><?php echo htmlspecialchars($prom_sem2); ?></strong></th>
    <th colspan="6" align="center" bgcolor="#E9E9E9"><h3><strong>Promedio General</strong></h3></th>
    <th align="center" bgcolor="#CCFFFF"><h3><strong><?php echo htmlspecialchars($prom_gen); ?></strong></h3></th>
  </tr>
  <tr>
    <th colspan="2" align="left" bgcolor="#E9E9E9">Negativas</th>
    <th colspan="2" align="left" bgcolor="#E9E9E9">
      <input name="anota_n" type="text" value="<?php echo htmlspecialchars($anota_n); ?>" size="4" maxlength="4" />
    </th>
    <th rowspan="2" align="left" bgcolor="#E9E9E9">Porcentaje Asistencia</th>
    <th width="18" rowspan="2" align="left" bgcolor="#E9E9E9">
      <input name="porc_asist" type="text" value="<?php echo htmlspecialchars($porc_asist); ?>" size="3" maxlength="3" />
    </th>
    <th width="15" rowspan="2" align="left" bgcolor="#E9E9E9">%</th>
    <th width="56" rowspan="2" align="left" bgcolor="#E9E9E9">&nbsp;</th>
    <th colspan="4" rowspan="2" align="left" bgcolor="#FFFFFF"><strong>Observaciones:</strong></th>
    <th colspan="21" rowspan="2" align="left" bgcolor="#FFFFFF">
      <textarea name="obs" cols="80" rows="3"><?php echo htmlspecialchars($obs); ?></textarea>
    </th>
  </tr>
  <tr>
    <th colspan="2" align="left" bgcolor="#E9E9E9">Positivas</th>
    <th colspan="2" align="left" bgcolor="#E9E9E9">
      <input name="anota_p" type="text" value="<?php echo htmlspecialchars($anota_p); ?>" size="4" maxlength="4" />
    </th>
  </tr>
  <tr>
    <th colspan="8" align="left" bgcolor="#FFFFFF">
      <?php if ($final == 0): ?>
        <input type="submit" name="btnaccion" value="Guardar" />
      <?php endif; ?>
    </th>
    <th colspan="10" align="center" bgcolor="#FFFFFF">
      <?php if ($nivel_usuario == 1): ?>
        <input type="submit" name="btnaccion" value="Desbloquear" />
      <?php endif; ?>
    </th>
    <th colspan="15" align="right" bgcolor="#FFFFFF">
      <input type="submit" name="btnaccion" value="Imprimir Inf Parc" />
      <input type="submit" name="btnaccion" value="Imprimir Inf Sem" onclick="semestralVentanas(); return false;" />
      <input type="submit" name="btnaccion" value="Imprimir Inf Final" onclick="finalVentanas(); return false;" />
    </th>
  </tr>
</table>
<?php endif; ?>
</form>
</center>
</body>
</html>
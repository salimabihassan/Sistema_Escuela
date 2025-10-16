<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\registrar_inf_perso12.php
// =======================================================
// Archivo: registrar_inf_perso12.php
// Autor: Sistema Escolar
// Fecha de actualización PHP: 15-10-2025 (PHP 8.2)
// Descripción: Informe de Personalidad 1ro y 2do Semestre
// =======================================================

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
session_start();

$conexion = conectarse_escuela();
$grado1 = $_SESSION["grado"] ?? '';
$nivel = $_POST['nivel'] ?? '';
$ci_alu = $_POST['ci_alu'] ?? '';
$ci_alu2 = $_POST['ci_alu2'] ?? '';
$nom_alu = $_POST['nom_alu'] ?? '';
$grado = $_POST['grado'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$fecha_insc = date("d/m/Y");
$btnaccion = $_POST['btnaccion'] ?? '';
$total_consultar = $_POST['total_consultar'] ?? 0;
$total_periodo = $_POST['total_periodo'] ?? 0;

$id_perso = [];
$cod_ambito = [];
$lit = [];
$id_perso2 = [];
$cod_ambito2 = [];
$lit2 = [];
$ambito = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los literales y ámbitos
    for ($t = 1; $t <= 31; $t++) {
        $id_perso[$t] = $_POST['id_perso' . $t] ?? '';
        $cod_ambito[$t] = $_POST['cod_ambito' . $t] ?? '';
        $lit[$t] = $_POST['lit' . $t] ?? '';
        $id_perso2[$t] = $_POST['id_perso2' . $t] ?? '';
        $cod_ambito2[$t] = $_POST['cod_ambito2' . $t] ?? '';
        $lit2[$t] = $_POST['lit2' . $t] ?? '';
    }
}

switch ($btnaccion) {
    case "Imprimir":
        $btnaccion = "";
        $consulta_perso = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=1 ORDER BY cod_ambito";
        $resultado_perso = $conexion->query($consulta_perso);
        $total_perso = $resultado_perso ? $resultado_perso->num_rows : 0;

        if ($total_perso > 0) {
            $consulta_imp = "UPDATE imp SET ci_alu='$ci_alu', ci_alu2='$ci_alu2' WHERE id='1'";
            $conexion->query($consulta_imp);

            $fecha_insc = date("d/m/Y");
            $cod_reg = $grado . $periodo . 'ced_alu:' . $ci_alu . '-' . $ci_alu2;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Imprimió';
            $registro = 'Procesos, Personalidad 1ro y 2do Semestre';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $sql_aud = "INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES ('$cod_reg','$ci_usu','$ci_usu2','$desc_reg','$registro','$FechaMySQL')";
            $conexion->query($sql_aud);

            $t = 1;
            while ($datos_perso = $resultado_perso->fetch_assoc()) {
                $id_perso[$t] = $datos_perso['id_perso'];
                $cod_ambito[$t] = $datos_perso['cod_ambito'];
                $lit[$t] = $datos_perso['lit'];
                $t++;
            }
        } else {
            echo "<script>alert('Este Alumno no tiene Registrado un informe de personalidad en el Primer Semestre...!!!');</script>";
        }

        $consulta_perso2 = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=2 ORDER BY cod_ambito";
        $resultado_perso2 = $conexion->query($consulta_perso2);
        $total_perso2 = $resultado_perso2 ? $resultado_perso2->num_rows : 0;

        if ($total_perso2 > 0) {
            $t = 1;
            while ($datos_perso2 = $resultado_perso2->fetch_assoc()) {
                $id_perso2[$t] = $datos_perso2['id_perso'];
                $cod_ambito2[$t] = $datos_perso2['cod_ambito'];
                $lit2[$t] = $datos_perso2['lit'];
                $t++;
            }
        }
        break;

    case "Consultar":
        if (empty($ci_alu)) {
            echo "<script>alert('Debe escribir el Nro de cedula del estudiante');</script>";
            break;
        }
        if ($ci_alu2 === '' || $ci_alu2 < 0) {
            echo "<script>alert('Debe escribir el Nro de validación de cedula del estudiante');</script>";
            break;
        }
        $ci_alu2 = strtoupper($ci_alu2);

        $consulta_consultar = "SELECT * FROM alumno WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2'";
        $resultado_consultar = $conexion->query($consulta_consultar);
        $total_consultar = $resultado_consultar ? $resultado_consultar->num_rows : 0;

        if ($total_consultar > 0) {
            $datos_consultar = $resultado_consultar->fetch_assoc();
            $borrado = $datos_consultar['borrado'];

            if ($borrado == 0) {
                $nom_alu = $datos_consultar['nom_alu'];
                $grado = $datos_consultar['grado'];

                if ($_SESSION["nivel_intranet"] == 0) {
                    if ($grado == $grado1) {
                        $periodo = $datos_consultar['periodo'];
                        $id_alu = $datos_consultar['id_alu'];
                        $borrado = $datos_consultar['borrado'];
                        $consulta_perso = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=1 ORDER BY cod_ambito";
                        $resultado_perso = $conexion->query($consulta_perso);
                        $total_perso = $resultado_perso ? $resultado_perso->num_rows : 0;
                    } else {
                        $grado2 = '';
                        if ($grado1 == '1') $grado2 = '1er';
                        if ($grado1 == '2') $grado2 = '2do';
                        if ($grado1 == '3') $grado2 = '3er';
                        if ($grado1 == '4') $grado2 = '4to';
                        if ($grado1 == '5') $grado2 = '5to';
                        if ($grado1 == '6') $grado2 = '6to';
                        if ($grado1 == '7') $grado2 = '7mo';
                        if ($grado1 == '8') $grado2 = '8vo';
                        echo "<script>alert('Esta Cedula del Alumno pertenece a otro grado a la cual usted no está autorizado...!!!, usted tiene acceso solo al $grado2 Grado');</script>";
                        $nom_alu = "";
                        $total_consultar = 0;
                        $grado = "";
                        $periodo = "";
                        break;
                    }
                } else {
                    $periodo = $datos_consultar['periodo'];
                    $id_alu = $datos_consultar['id_alu'];
                    $borrado = $datos_consultar['borrado'];
                    $consulta_perso = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=1 ORDER BY cod_ambito";
                    $resultado_perso = $conexion->query($consulta_perso);
                    $total_perso = $resultado_perso ? $resultado_perso->num_rows : 0;
                }

                if ($total_perso > 0) {
                    $t = 1;
                    while ($datos_perso = $resultado_perso->fetch_assoc()) {
                        $id_perso[$t] = $datos_perso['id_perso'];
                        $cod_ambito[$t] = $datos_perso['cod_ambito'];
                        $lit[$t] = $datos_perso['lit'];
                        $t++;
                    }
                } else {
                    echo "<script>alert('Este Alumno no tiene Registrado un informe de personalidad en el Primer Semestre...!!!');</script>";
                }

                $consulta_perso2 = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=2 ORDER BY cod_ambito";
                $resultado_perso2 = $conexion->query($consulta_perso2);
                $total_perso2 = $resultado_perso2 ? $resultado_perso2->num_rows : 0;

                if ($total_perso2 > 0) {
                    $t = 1;
                    while ($datos_perso2 = $resultado_perso2->fetch_assoc()) {
                        $id_perso2[$t] = $datos_perso2['id_perso'];
                        $cod_ambito2[$t] = $datos_perso2['cod_ambito'];
                        $lit2[$t] = $datos_perso2['lit'];
                        $t++;
                    }
                } else {
                    echo "<script>alert('Este Alumno no tiene Registrado un informe de personalidad en el Segundo Semestre...!!!');</script>";
                }
            } else {
                echo "<script>alert('Esta Cedula del Alumno fue eliminado de la Base de Datos, Para Recuperarlo Vaya a la Papelera de Reciclaje...!!!');</script>";
                $nom_alu = "";
                $id_alu = "";
                $grado = "";
                $periodo = "";
                break;
            }
        } else {
            echo "<script>alert('Esta Cedula de Alumno no está registrada en el sistema...!!!');</script>";
            $nom_alu = "";
            $id_alu = "";
            $grado = "";
            $periodo = "";
        }
        break;

    case "Desbloquear":
        if (empty($ci_alu)) {
            echo "<script>alert('Debe escribir el Nro de cedula del estudiante');</script>";
            break;
        }
        if (empty($ci_alu2)) {
            echo "<script>alert('Debe escribir el Nro de validación de cedula del estudiante');</script>";
            break;
        }

        $consulta_consultar = "SELECT * FROM alumno WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2'";
        $resultado_consultar = $conexion->query($consulta_consultar);
        $total_consultar = $resultado_consultar ? $resultado_consultar->num_rows : 0;

        if ($total_consultar > 0) {
            $datos_consultar = $resultado_consultar->fetch_assoc();
            $borrado = $datos_consultar['borrado'];

            if ($borrado == 0) {
                $nom_alu = $datos_consultar['nom_alu'];
                $grado = $datos_consultar['grado'];

                $fecha_insc = date("d/m/Y");
                $cod_reg = $grado . $periodo . 'ced_alu:' . $ci_alu . '-' . $ci_alu2;
                $ci_usu = $_SESSION["cedula"] ?? '';
                $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
                $desc_reg = 'Desbloqueó Inf Personalidad';
                $registro = 'Consultas, Inf. Personalidad';
                $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

                $sql_aud = "INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES ('$cod_reg','$ci_usu','$ci_usu2','$desc_reg','$registro','$FechaMySQL')";
                $conexion->query($sql_aud);

                if ($_SESSION["nivel_intranet"] == 0) {
                    if ($grado == $grado1) {
                        $periodo = $datos_consultar['periodo'];
                        $id_alu = $datos_consultar['id_alu'];
                        $borrado = $datos_consultar['borrado'];
                        $consulta_perso = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=1 ORDER BY cod_ambito";
                        $resultado_perso = $conexion->query($consulta_perso);
                        $total_perso = $resultado_perso ? $resultado_perso->num_rows : 0;
                    } else {
                        $grado2 = '';
                        if ($grado1 == '1') $grado2 = '1er';
                        if ($grado1 == '2') $grado2 = '2do';
                        if ($grado1 == '3') $grado2 = '3er';
                        if ($grado1 == '4') $grado2 = '4to';
                        if ($grado1 == '5') $grado2 = '5to';
                        if ($grado1 == '6') $grado2 = '6to';
                        if ($grado1 == '7') $grado2 = '7mo';
                        if ($grado1 == '8') $grado2 = '8vo';
                        echo "<script>alert('Esta Cedula del Alumno pertenece a otro grado a la cual usted no está autorizado...!!!, usted tiene acceso solo al $grado2 Grado');</script>";
                        $nom_alu = "";
                        $total_consultar = 0;
                        $grado = "";
                        $periodo = "";
                        break;
                    }
                } else {
                    $periodo = $datos_consultar['periodo'];
                    $id_alu = $datos_consultar['id_alu'];
                    $borrado = $datos_consultar['borrado'];
                    $consulta_perso = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=1 ORDER BY cod_ambito";
                    $resultado_perso = $conexion->query($consulta_perso);
                    $total_perso = $resultado_perso ? $resultado_perso->num_rows : 0;
                }

                if ($total_perso > 0) {
                    $t = 1;
                    while ($datos_perso = $resultado_perso->fetch_assoc()) {
                        $id_perso[$t] = $datos_perso['id_perso'];
                        $cod_ambito[$t] = $datos_perso['cod_ambito'];
                        $lit[$t] = $datos_perso['lit'];
                        $id = $id_perso[$t];
                        $consulta_imp3 = "UPDATE perso$periodo SET final='0' WHERE id_perso='$id'";
                        $conexion->query($consulta_imp3);
                        $t++;
                    }
                } else {
                    echo "<script>alert('Este Alumno no tiene Registrado un informe de personalidad en el Primer Semestre...!!!');</script>";
                }

                $consulta_perso2 = "SELECT * FROM perso$periodo WHERE ci_alu='$ci_alu' AND ci_alu2='$ci_alu2' AND periodo='$periodo' AND semestre=2 ORDER BY cod_ambito";
                $resultado_perso2 = $conexion->query($consulta_perso2);
                $total_perso2 = $resultado_perso2 ? $resultado_perso2->num_rows : 0;

                if ($total_perso2 > 0) {
                    $t = 1;
                    while ($datos_perso2 = $resultado_perso2->fetch_assoc()) {
                        $id_perso2[$t] = $datos_perso2['id_perso'];
                        $cod_ambito2[$t] = $datos_perso2['cod_ambito'];
                        $lit2[$t] = $datos_perso2['lit'];
                        $id = $id_perso2[$t];
                        $consulta_imp3 = "UPDATE perso$periodo SET final='0' WHERE id_perso='$id'";
                        $conexion->query($consulta_imp3);
                        if ($t < 2) {
                            echo "<script>alert('Las Notas fueron desbloqueadas Existosamente');</script>";
                        }
                        $t++;
                    }
                } else {
                    echo "<script>alert('Este Alumno no tiene Registrado un informe de personalidad en el Segundo Semestre...!!!');</script>";
                }
            } else {
                echo "<script>alert('Esta Cedula del Alumno fue eliminado de la Base de Datos, Para Recuperarlo Vaya a la Papelera de Reciclaje...!!!');</script>";
                $nom_alu = "";
                $id_alu = "";
                $grado = "";
                $periodo = "";
                break;
            }
        } else {
            echo "<script>alert('Esta Cedula de Alumno no está registrada en el sistema...!!!');</script>";
            $nom_alu = "";
            $id_alu = "";
            $grado = "";
            $periodo = "";
        }
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>INFORME DE PERSONALIDAD 1RO Y 2DO SEMESTRE</title>
<style type="text/css">
#apDiv1 {
    position:absolute;
    width:956px;
    height:302px;
    z-index:1;
    left: 169px;
    top: 180px;
}
#apDiv2 {
    position:absolute;
    width:116px;
    height:47px;
    z-index:2;
    left: 201px;
    top: 182px;
}
body,td,th {
    font-family: Verdana, Geneva, sans-serif;
    font-size: 12px;
}
body {
    margin-left: 0px;
    margin-top: 0px;
    margin-right: 0px;
    margin-bottom: 0px;
}
a:link, a:visited, a:hover, a:active {
    text-decoration: none;
}
.sombra{ text-shadow: 0.05em 0.05em 0.03em  #000; }
.caja { box-shadow: 5px 5px 5px rgba(0,0,0,.5); padding: 5px; background:#F3F3F3 ; margin:5px; width:1024px; }
</style>
<script>
function semestralVentanas() {
    window.open("registrar_inf_perso12_imp.php", "Informe", "width=385,height=180,top=0,left=0,status,toolbar=1,scrollbars,location");
}
function ValNumero(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
    return true;
}
</script>
</head>
<body>
<center>
<form id="form1" name="form1" method="post" action="registrar_inf_perso12.php">
<div class="caja">
    <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
        INFORME DE PERSONALIDAD
    </font>
    Fecha: <?php echo htmlspecialchars($fecha_insc); ?>
    <input name="fecha_insc2" type="hidden" id="fecha_insc2" value="<?php echo htmlspecialchars($fecha_insc); ?>" maxlength="150" size="20" />
</div>
<table width="599" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr align="left">
        <td height="26" colspan="7" bgcolor="#E9E9E9"><strong>LOS DATOS DEL ALUMNO: </strong></td>
    </tr>
    <tr align="left">
        <td width="53" height="16" bgcolor="#F9F9F9">Cédula:</td>
        <td width="53" bgcolor="#F9F9F9"><input name="ci_alu" type="text" id="ci_alu" value="<?php echo htmlspecialchars($ci_alu); ?>" onkeyup="return ValNumero(this);" maxlength="8" size="9" /></td>
        <td bgcolor="#F9F9F9"><input name="ci_alu2" type="text" id="ci_alu2" value="<?php echo htmlspecialchars($ci_alu2); ?>" onkeyup="return ValNumero(this);" maxlength="1" size="1" /></td>
        <td width="54" bgcolor="#F9F9F9"><input type="submit" name="btnaccion" id="btnaccion" value="Consultar" /></td>
        <td colspan="2" bgcolor="#F9F9F9">Nombre:</td>
        <td width="99" bgcolor="#F9F9F9"><input name="nom_alu" type="text" id="nom_alu" value="<?php echo htmlspecialchars($nom_alu); ?>" size="50" maxlength="80" readonly /></td>
    </tr>
    <tr align="left">
        <td height="6" colspan="2" align="right" bgcolor="#E9E9E9">Curso:</td>
        <td colspan="3" bgcolor="#E9E9E9"><input name="grado" type="text" id="grado" value="<?php echo htmlspecialchars($grado); ?>" size="4" maxlength="4" readonly /></td>
        <td width="123" bgcolor="#E9E9E9">Periodo Escolar:</td>
        <td bgcolor="#E9E9E9"><input name="periodo" type="text" id="periodo" value="<?php echo htmlspecialchars($periodo); ?>" size="10" maxlength="11" readonly />
            <input name="total_consultar" type="hidden" id="ambito6" value="<?php echo htmlspecialchars($total_consultar); ?>" /></td>
    </tr>
</table>
<p>
<?php if ($total_consultar > 0) { ?>
<?php
    $consulta_ambito = "SELECT * FROM ambito WHERE borrado=0 ORDER BY cod_ambito";
    $resultado_ambito = $conexion->query($consulta_ambito);
    $total_ambito = $resultado_ambito ? $resultado_ambito->num_rows : 0;
?>
</p>
<table align="center" cellpadding="2" cellspacing="2">
    <tr>
        <th width="159" align="center" valign="top" bgcolor="#E9E9E9"><strong>ÁMBITO/INDICADORES</strong></th>
        <th width="72" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9"><strong>1º SEM</strong></th>
        <th width="48" colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">2º SEM</th>
    </tr>
    <?php
    $x = 0;
    $t = 1;
    $fila = 1;
    while ($datos_ambito = $resultado_ambito->fetch_assoc()) {
        $resto = $fila % 2;
        $color = ($resto == 0) ? "#FFFFFF" : "#CCEEFF";
        $fila++;
        $x++;

        if ($x == 1) {
            echo '<tr>
                <th align="center" valign="top" bgcolor="#E9E9E9"><h3>ÁMBITO: RELACIÓN CON SUS PARES</h3></th>
                <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
                <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            </tr>';
        }
        if ($x == 7) {
            echo '<tr>
                <th align="center" valign="top" bgcolor="#E9E9E9"><h3>ÁMBITO: DISCIPLINARIO</h3></th>
                <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
                <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            </tr>';
        }
        if ($x == 12) {
            echo '<tr>
                <th align="center" valign="top" bgcolor="#E9E9E9"><h3>ÁMBITO: RESPONSABILIDAD</h3></th>
                <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
                <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            </tr>';
        }
        if ($x == 17) {
            echo '<tr>
                <th align="center" valign="top" bgcolor="#E9E9E9"><h3>ÁMBITO: TRABAJO EN AULA</h3></th>
                <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
                <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            </tr>';
        }
        if ($x == 21) {
            echo '<tr>
                <th align="center" valign="top" bgcolor="#E9E9E9"><h3>ÁMBITO: AFECTIVIDAD</h3></th>
                <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
                <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            </tr>';
        }
        if ($x == 24) {
            echo '<tr>
                <th align="center" valign="top" bgcolor="#E9E9E9"><h3>ÁMBITO: PRESENTACIÓN PERSONAL</h3></th>
                <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
                <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            </tr>';
        }
        if ($x == 29) {
            echo '<tr>
                <th align="center" valign="top" bgcolor="#E9E9E9"><h3>ÁMBITO: EN CUANTO A LOS PADRES Y/O APODERADOS</h3></th>
                <th align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
                <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="#E9E9E9">&nbsp;</th>
            </tr>';
        }
        echo '<tr bgcolor="' . $color . '">
            <th align="left" valign="top" nowrap="nowrap" bgcolor="' . $color . '">
                <input name="cod_ambito' . $t . '" type="hidden" value="' . htmlspecialchars($datos_ambito['cod_ambito']) . '" />
                <input name="ambito' . $t . '" type="hidden" value="' . htmlspecialchars($datos_ambito['nom_ambito']) . '" />
                <input name="ambito" type="hidden" value="' . htmlspecialchars($datos_ambito['cod_ambito']) . '" />
                ' . htmlspecialchars($datos_ambito['cod_ambito'] . '-. ' . $datos_ambito['nom_ambito']) . '
            </th>
            <th align="center" valign="top" nowrap="nowrap" bgcolor="' . $color . '">
                <input name="lit' . $t . '" type="hidden" value="' . htmlspecialchars($lit[$t] ?? '') . '" />
                ' . htmlspecialchars($lit[$t] ?? '') . '
            </th>
            <th colspan="24" align="center" valign="top" nowrap="nowrap" bgcolor="' . $color . '">
                <input name="lit2' . $t . '" type="hidden" value="' . htmlspecialchars($lit2[$t] ?? '') . '" />
                ' . htmlspecialchars($lit2[$t] ?? '') . '
            </th>
        </tr>';
        $t++;
    }
    ?>
    <tr>
        <th align="left" bgcolor="#FFFFFF"><?php if ($_SESSION["nivel_intranet"] == 1) { ?><input type="submit" name="btnaccion" id="btnaccion" value="Desbloquear" /><?php } ?></th>
        <th colspan="22" align="right" bgcolor="#FFFFFF">
            <input type="submit" name="btnaccion" id="btnaccion" value="Imprimir" onclick="if(confirm('Una vez impreso el Informe de Personalidad, no se podrá ser modificado, ¿Deseas continuar?')){semestralVentanas()}else{ alert('Operación Cancelada'); return false; }" />
        </th>
    </tr>
</table>
<?php } ?>
</form>
</center>
</body>
</html>
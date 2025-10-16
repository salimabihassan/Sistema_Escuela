<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\registrar_promocion.php
include("conexiones.php");
header('Content-Type: text/html; charset=utf-8');
error_reporting(0);
session_start();

$_SESSION["nombre_apellido"];
$_SESSION["cedula"];
$_SESSION["ci_usu2"];
$_SESSION["grado"];
$_SESSION["nivel_intranet"];
$nivel = $_SESSION["nivel_intranet"];

$conexion = conectarse_escuela();
$cod_curso = $_POST['cod_curso'] ?? '';
$tcurso = $_POST['tcurso'] ?? '';
$n_alu_curso = $_POST['n_alu_curso'] ?? '';
$grado = $_POST['grado'] ?? '';
$peri = $_POST['peri'] ?? '';
$fecha_insc = date("d/m/Y");
$cmbcursos = $_POST['cmbcursos'] ?? '';
$grado_prox = substr($cmbcursos, 0, 1);
$peri_prox = substr($cmbcursos, 1);
$btnaccion = $_POST['btnaccion'] ?? '';
$total_sql = $_POST['total_sql'] ?? 0;
$cod_curso = $grado . $peri;

$chkreprovar = [];
$txtci_promovera = [];
$txtced_promover = [];

if ($btnaccion == 'Promover') {
    for ($i = 1; $i <= $total_sql; $i++) {
        $chkreprovar[$i] = $_POST["chkreprovar" . $i] ?? '';
        $txtci_promovera[$i] = $_POST["txtci_promovera" . $i] ?? '';
        $txtced_promover[$i] = $_POST["txtced_promover" . $i] ?? '';
    }
}

switch ($btnaccion) {
    case "Consultar":
        $conexion = conectarse_escuela();
        $consulta_curso = "SELECT * FROM alumno WHERE grado='$grado' AND periodo='$peri' AND borrado='0'";
        $resultado_curso = $conexion->query($consulta_curso);
        $total_curso = $resultado_curso ? $resultado_curso->num_rows : 0;
        $n_alu_curso = $total_curso;
        break;

    case "Promover":
        if (empty($grado)) {
            echo "<script>alert('Debe seleccionar el curso Actual para continuar');</script>";
            break;
        }
        if (empty($peri)) {
            echo "<script>alert('Debe escribir el periodo Actual a promover para continuar');</script>";
            break;
        }
        if (empty($cmbcursos)) {
            echo "<script>alert('Debe escribir la Código del curso Próximo');</script>";
            break;
        }

        for ($i = 1; $i <= $total_sql; $i++) {
            $resultado_alumno = ($chkreprovar[$i] == "SI") ? "REPROBADO" : "APROBADO";
            $ci_alu = $txtced_promover[$i];
            $ci_alu2 = $txtci_promovera[$i];

            if ($resultado_alumno == "APROBADO") {
                $consulta_actualizar = "UPDATE alumno SET grado='$grado_prox', periodo='$peri_prox' WHERE ci_alu2='$ci_alu2' AND ci_alu='$ci_alu'";
            } else {
                $consulta_actualizar = "UPDATE alumno SET periodo='$peri_prox', grado='$grado' WHERE ci_alu2='$ci_alu2' AND ci_alu='$ci_alu'";
            }
            $conexion->query($consulta_actualizar);
        }

        $cod_curso = $grado . $peri;
        $consulta_actualizar_curso = "UPDATE curso SET activo=0 WHERE cod_curso='$cod_curso'";
        $conexion->query($consulta_actualizar_curso);

        if ($conexion->affected_rows > 0) {
            echo "<script>alert('Promoción Realizada ...!!!');</script>";
        }

        $fecha_insc = date("d/m/Y");
        $cod_reg = 'Cod_curso:' . $grado . $peri . ' Cod_Curso Prov:' . $grado_prox . $peri_prox;
        $ci_usu = $_SESSION["cedula"];
        $ci_usu2 = $_SESSION["ci_usu2"];
        $desc_reg = 'Promovió Curso';
        $registro = 'Registrar, Promover Curso';
        $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

        $sql_aud = "INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES ('$cod_reg','$ci_usu','$ci_usu2','$desc_reg','$registro','$FechaMySQL')";
        $conexion->query($sql_aud);
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>REGISTRAR PROMOCI&Oacute;N DE GRADO</title>
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
<script type="text/javascript">
function Solo_Numerico(variable){
    Numer=parseInt(variable);
    if (isNaN(Numer)){
        return "";
    }
    return Numer;
}
function ValNumero(Control){
    Control.value=Solo_Numerico(Control.value);
}
</script>
</head>
<body>
<center>
<form id="form1" name="form1" method="post" action="registrar_promocion.php">
   <div class="caja">
     <font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">
REGISTRAR PROMOCI&Oacute;N DE GRADO
     </font>
   Fecha:<?php echo $fecha_insc;?>
   <input name="fecha_insc2" type="hidden" id="fecha_insc2" value="<?php echo $fecha_insc;?>" maxlength="150" size="20" />
   </div>
    <table width="538" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr align="left">
      <td height="26" colspan="7" bgcolor="#E9E9E9"><strong>INTRODUZCA LOS DATOS DEL GRADO ACTUAL: </strong></td>
      </tr>
    <tr align="left">
      <td width="46" height="16" bgcolor="#F9F9F9">Curso:</td>
      <td width="63" bgcolor="#F9F9F9">
        <select name="grado" size="1" id="grado">
        <option value=""></option>
        <?php
        if ($_SESSION["nivel_intranet"] == 0) {
            for ($g = 1; $g <= 8; $g++) {
                if ($_SESSION["grado"] == $g) {
                    $selected = ($grado == (string)$g) ? "selected='selected'" : "";
                    $suffix = ($g == 1) ? 'ro' : (($g == 2) ? 'do' : (($g == 3) ? 'ro' : (($g == 4) ? 'to' : (($g == 5) ? 'to' : (($g == 6) ? 'to' : (($g == 7) ? 'mo' : 'vo'))))));
                    echo "<option value='$g' $selected>{$g}{$suffix}</option>";
                }
            }
        } else {
            for ($g = 1; $g <= 8; $g++) {
                $selected = ($grado == (string)$g) ? "selected='selected'" : "";
                $suffix = ($g == 1) ? 'ro' : (($g == 2) ? 'do' : (($g == 3) ? 'ro' : (($g == 4) ? 'to' : (($g == 5) ? 'to' : (($g == 6) ? 'to' : (($g == 7) ? 'mo' : 'vo'))))));
                echo "<option value='$g' $selected>{$g}{$suffix}</option>";
            }
        }
        ?>
      </select></td>
      <td width="52" bgcolor="#F9F9F9">Periodo Escolar:</td>
      <td width="60" bgcolor="#F9F9F9"><input name="peri" type="text" id="peri" value="<?php echo $peri;?>" size="4" maxlength="4" /></td>
      <td width="84" bgcolor="#F9F9F9"><input type="submit" name="btnaccion" id="btnaccion" value="Consultar" /></td>
      <td width="165" bgcolor="#F9F9F9">N&uacute;mero de Alumnos en el Curso:</td>
      <td width="24" bgcolor="#F9F9F9"><input name="n_alu_curso" type="text" id="n_alu_curso" value="<?php echo $n_alu_curso;?>" size="4" maxlength="4" readonly /></td>
      </tr>
    <tr align="left">
      <td height="6" align="right" bgcolor="#E9E9E9">&nbsp;</td>
      <td height="6" align="right" bgcolor="#E9E9E9">&nbsp;</td>
      <td colspan="2" bgcolor="#E9E9E9">&nbsp;</td>
      <td colspan="3" bgcolor="#E9E9E9">&nbsp;</td>
      </tr>
    </table>
    <p>
      <?php if ($n_alu_curso > 0) { ?>
    </p>
    <p>
    <table width="386" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr align="left">
      <td height="26" colspan="5" bgcolor="#E9E9E9"><strong>INTRODUZCA LOS DATOS DEL GRADO A PROMOVER: </strong></td>
      </tr>
    <tr align="left">
      <td height="16" colspan="2" bgcolor="#FFFFFF">C&oacute;digo Curso:</td>
      <td bgcolor="#FFFFFF">
        <select name="cmbcursos" onChange="submit();">
          <option value=""></option>
          <?php
          $period = $peri + 1;
          $coneccion = conectarse_escuela();
          if ($tcurso == 'SI') {
              $consulta_curso = "SELECT * FROM curso WHERE activo=1 ORDER BY cod_curso";
          } else {
              $consulta_curso = "SELECT * FROM curso WHERE activo=1 AND periodo='$period' ORDER BY cod_curso";
          }
          $resultado_curso = $coneccion->query($consulta_curso);
          while ($datos_curso = $resultado_curso->fetch_assoc()) {
              $codigo_curso = $datos_curso["cod_curso"];
              $seleccionado = ($cmbcursos == $codigo_curso) ? 'selected' : '';
              echo "<option value='$codigo_curso' $seleccionado>$codigo_curso</option>";
          }
          ?>
        </select></td>
      <td width="20" bgcolor="#FFFFFF"><input name="tcurso" type="checkbox" id="tcurso" onChange="submit();" value="SI" <?php if ($tcurso == 'SI') echo "checked='checked'"; ?> /></td>
      <td width="155" bgcolor="#FFFFFF">Mostrar Todos los Cursos</td>
      </tr>
    <tr align="left">
      <td width="57" height="6" bgcolor="#E9E9E9">Grado:</td>
      <td width="38" bgcolor="#E9E9E9"><input name="grado_prox" type="text" id="grado_prox" value="<?php echo $grado_prox;?>" size="4" maxlength="4" readonly /></td>
      <td width="84" bgcolor="#E9E9E9">Periodo Escolar:</td>
      <td colspan="2" bgcolor="#E9E9E9"><input name="peri_prox" type="text" id="peri_prox" value="<?php echo $peri_prox;?>" size="10" maxlength="11" readonly /></td>
    </tr>
    </table>
    <?php
    $consulta_sql = "SELECT * FROM alumno WHERE grado='$grado' AND periodo='$peri' AND retirado=0 AND borrado=0";
    $resultado_sql = $conexion->query($consulta_sql);
    $total_sql = $resultado_sql ? $resultado_sql->num_rows : 0;
    ?>
    <table width="40%" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr>
     <td height="26" colspan="2" align="center"><strong>Nro.</strong></td>
     <td width="157" align="center"><strong>C&eacute;dula</strong></td>
     <td width="159" align="center"><strong>Nombre</strong></td>
     <td width="67" align="center"><strong>Repitiente</strong></td>
    </tr>
    <?php
    $i = 0;
    $fila = 1;
    while ($datos_sql = $resultado_sql->fetch_assoc()) {
        $resto = $fila % 2;
        $color = ($resto == 0) ? "#FFFFFF" : "#CCEEFF";
        $fila++;
        $i++;
        ?>
        <tr bgcolor="<?php echo $color; ?>">
            <td height="34" colspan="2" align="center" bgcolor="<?php echo $color; ?>">
                <?php echo $i ?>
            </td>
            <td align="center" bgcolor="<?php echo $color; ?>">
                <?php echo $datos_sql['ci_alu'] . '-' . $datos_sql['ci_alu2']; ?>
                <input name="txtci_promovera<?php echo $i; ?>" type="hidden" id="txtci_promovera<?php echo $i; ?>" value="<?php echo $txtci_promovera[$i] = $datos_sql['ci_alu2'] ?>" />
                <input name="txtced_promover<?php echo $i; ?>" type="hidden" value="<?php echo $txtced_promover[$i] = $datos_sql['ci_alu'] ?>" />
            </td>
            <td align="left" nowrap="nowrap" bgcolor="<?php echo $color; ?>"> <?php echo $datos_sql['nom_alu'] ?></td>
            <td align="center" bgcolor="<?php echo $color; ?>">
                <input type="checkbox" name="chkreprovar<?php echo $i; ?>" <?php if (isset($chkreprovar[$i]) && $chkreprovar[$i] == 'SI') echo "checked='checked'"; ?> value="SI">
            </td>
        </tr>
    <?php } ?>
    <tr>
     <td height="26" colspan="5" align="center" bgcolor="#E9E9E9">
     <input type="submit" name="btnaccion" id="btnaccion" value="Promover" />
     <input name="total_sql" type="hidden" value="<?php echo $total_sql; ?>" />
     </td>
     </tr>
    </table>
    <?php } ?>
</form>
</center>
</body>
</html>
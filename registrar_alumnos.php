<?php
// =======================================================
// Archivo: registrar_alumnos.php
// Autor: Salim I. Abi Hassan E.
// Fecha de diseño: 01-03-2017
// Fecha de actualización PHP: 13-10-2025 (PHP 8.2)
// Descripción: Registro, consulta, actualización y eliminación de alumnos
// =======================================================

include("conexiones.php"); // Conexión a la base de datos
header('Content-Type: text/html; charset=utf-8'); // Codificación UTF-8
session_start(); // Iniciar sesión

// ===============================
// Variables de sesión y POST
// ===============================
$nivel = $_SESSION["nivel_intranet"] ?? '';
$grado1 = $_SESSION["grado"] ?? '';

// Captura de datos del formulario
$ci_alu = $_POST['ci_alu'] ?? '';
$ci_alu2 = strtoupper($_POST['ci_alu2'] ?? '');
$nom_alu = $_POST['nom_alu'] ?? '';
$grado = $_POST['grado'] ?? '';
$periodo = $_POST['periodo'] ?? '';
$fechamysql = trim($_POST['fechamysql'] ?? '');
$sexo = $_POST['sexo'] ?? '';
$id_alu = $_POST['id_alu'] ?? '';
$existe = $_POST['existe'] ?? '';
$total_buscar = $_POST['total_buscar'] ?? '';
$button = $_POST['button'] ?? '';
$borrado = $_POST['borrado'] ?? '';

// ===============================
// Función para validar el RUT chileno
// ===============================
function validar_rut($rut, $digito_v) {
    if ($rut == "") return false;
    $x = 2;
    $sumatorio = 0;
    for ($i = strlen($rut) - 1; $i >= 0; $i--) {
        if ($x > 7) $x = 2;
        $sumatorio += ($rut[$i] * $x);
        $x++;
    }
    $digito = $sumatorio % 11;
    $digito = 11 - $digito;
    switch ($digito) {
        case 10: $digito = "k"; break;
        case 11: $digito = "0"; break;
    }
    return strtolower($digito_v) == $digito;
}

// ===============================
// Función para convertir fecha MySQL a formato chileno
// ===============================
function fentrada($cambio) {
    $uno = substr($cambio, 0, 4);
    $dos = substr($cambio, 5, 2);
    $tres = substr($cambio, 8, 2);
    return ($tres . "/" . $dos . "/" . $uno);
}

// ===============================
// Conexión a la base de datos
// ===============================
$conexion = conectarse_escuela();

// ===============================
// Lógica principal según el botón presionado
// ===============================
switch ($button) {
    case "Registrar":
        // Validar datos
        if (empty($ci_alu)) {
            echo "<script>alert('Debe escribir el Nro de cedula del estudiante');</script>";
            break;
        }
        if ($ci_alu2 === '' || $ci_alu2 < 0) {
            echo "<script>alert('Debe escribir el Nro de validación de cedula del estudiante');</script>";
            break;
        }
        if (!validar_rut($ci_alu, $ci_alu2)) {
            echo "<script>alert('El Digito verificador del Rut no es correcto, verifique y vuelva a intentar');</script>";
            break;
        }
        if (empty($nom_alu)) {
            echo "<script>alert('Debe escribir el nombre del alumno');</script>";
            break;
        }
        if (empty($sexo)) {
            echo "<script>alert('Debe seleccionar una opción válida de sexo');</script>";
            break;
        }
        if (empty($grado)) {
            echo "<script>alert('Debe seleccionar el grado');</script>";
            break;
        }
        if (empty($periodo)) {
            echo "<script>alert('Debe escribir el Periodo o Año Escolar');</script>";
            break;
        }

        // Consultar si el alumno ya existe
        $consulta_consultar = $conexion->prepare("SELECT * FROM alumno WHERE ci_alu = ? AND ci_alu2 = ?");
        $consulta_consultar->bind_param("ss", $ci_alu, $ci_alu2);
        $consulta_consultar->execute();
        $resultado_consultar = $consulta_consultar->get_result();
        $total_consultar = $resultado_consultar->num_rows;

        if ($total_consultar == 0) {
            $nom_alu = ucwords($nom_alu);
            $consulta_insert = $conexion->prepare("INSERT INTO alumno (ci_alu, ci_alu2, nom_alu, sexo, grado, periodo) VALUES (?, ?, ?, ?, ?, ?)");
            $consulta_insert->bind_param("ssssss", $ci_alu, $ci_alu2, $nom_alu, $sexo, $grado, $periodo);
            $consulta_insert->execute();

            // Auditoría
            $fecha_insc = date("d/m/Y");
            $cod_reg = $ci_alu . '-' . $ci_alu2;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Registró';
            $registro = 'Registrar, Alumno';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $sql_aud->execute();

            echo "<script>alert('El alumno fue registrado con éxito');</script>";
        } else {
            echo "<script>alert('Este Nro. de cedula del estudiante ya ha sido registrado, para modificar sus datos, ubíquelo con el botón consulta, modifique los datos y luego oprima el botón actualizar');</script>";
        }
        break;

    case "Consultar":
        // Validar datos
        if (empty($ci_alu)) {
            echo "<script>alert('Debe escribir el Nro de cedula del estudiante');</script>";
            break;
        }
        if ($ci_alu2 === '' || $ci_alu2 < 0) {
            echo "<script>alert('Debe escribir el Nro de validación de cedula del estudiante');</script>";
            break;
        }
        if (!validar_rut($ci_alu, $ci_alu2)) {
            echo "<script>alert('El Digito verificador del Rut no es correcto, verifique y vuelva a intentar');</script>";
            break;
        }

        $consulta_consultar = $conexion->prepare("SELECT * FROM alumno WHERE ci_alu = ? AND ci_alu2 = ?");
        $consulta_consultar->bind_param("ss", $ci_alu, $ci_alu2);
        $consulta_consultar->execute();
        $resultado_consultar = $consulta_consultar->get_result();
        $total_consultar = $resultado_consultar->num_rows;

        if ($total_consultar > 0) {
            $datos_consultar = $resultado_consultar->fetch_assoc();
            $borrado = $datos_consultar['borrado'];
            if ($borrado == 0) {
                $grado = $datos_consultar['grado'];
                if ($nivel == 0) {
                    if ($grado == $grado1) {
                        $nom_alu = $datos_consultar['nom_alu'];
                        $sexo = $datos_consultar['sexo'];
                        $grado = $datos_consultar['grado'];
                        $periodo = $datos_consultar['periodo'];
                        $id_alu = $datos_consultar['id_alu'];
                        $borrado = $datos_consultar['borrado'];
                    } else {
                        $grado2 = $grado1 . 'º';
                        echo "<script>alert('Esta Cedula del Alumno pertenece a otro grado al cual usted no está autorizado...!!!, usted tiene acceso solo al $grado2 Grado');</script>";
                        $nom_alu = $sexo = $id_alu = $grado = $periodo = "";
                        break;
                    }
                } else {
                    $nom_alu = $datos_consultar['nom_alu'];
                    $sexo = $datos_consultar['sexo'];
                    $grado = $datos_consultar['grado'];
                    $periodo = $datos_consultar['periodo'];
                    $id_alu = $datos_consultar['id_alu'];
                    $borrado = $datos_consultar['borrado'];
                }
            } else {
                echo "<script>alert('Esta Cedula del Alumno fue eliminado de la Base de Datos, Para Recuperarlo Vaya a la Papelera de Reciclaje...!!!');</script>";
                $nom_alu = $sexo = $id_alu = $grado = $periodo = "";
                break;
            }
        } else {
            echo "<script>alert('Esta Cedula no está registrada en la base de datos...!!!');</script>";
            $nom_alu = $sexo = $id_alu = $grado = $periodo = "";
        }
        break;

    case "Eliminar":
        // Eliminar alumno (marcar como borrado)
        $consulta_buscar = $conexion->prepare("SELECT * FROM alumno WHERE id_alu = ?");
        $consulta_buscar->bind_param("s", $id_alu);
        $consulta_buscar->execute();
        $resultado_buscar = $consulta_buscar->get_result();
        $total_buscar = $resultado_buscar->num_rows;

        if ($total_buscar > 0) {
            if ($borrado == 0) {
                $consulta_update = $conexion->prepare("UPDATE alumno SET borrado = '1' WHERE id_alu = ?");
                $consulta_update->bind_param("s", $id_alu);
                $consulta_update->execute();

                // Auditoría
                $fecha_insc = date("d/m/Y");
                $cod_reg = $ci_alu . '-' . $ci_alu2;
                $ci_usu = $_SESSION["cedula"] ?? '';
                $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
                $desc_reg = 'Eliminó';
                $registro = 'Registrar, Alumno';
                $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

                $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
                $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
                $sql_aud->execute();

                echo "<script>alert('El Alumno fue eliminado con éxito...!!!');</script>";
                $ci_alu = $ci_alu2 = $nom_alu = $sexo = $id_alu = $grado = $periodo = "";
            } else {
                echo "<script>alert('El Alumno ya se había Eliminado...!!!');</script>";
            }
        } else {
            echo "<script>alert('Cédula No Registrada...!!!');</script>";
        }
        break;

    case "Actualizar":
        // Actualizar datos del alumno
        if (!validar_rut($ci_alu, $ci_alu2)) {
            echo "<script>alert('El Digito verificador del Rut no es correcto, verifique y vuelva a intentar');</script>";
            break;
        }
        $consulta_consultar = $conexion->prepare("SELECT * FROM alumno WHERE id_alu = ?");
        $consulta_consultar->bind_param("s", $id_alu);
        $consulta_consultar->execute();
        $resultado_consultar = $consulta_consultar->get_result();
        $total_consultar = $resultado_consultar->num_rows;

        if ($total_consultar > 0) {
            $nom_alu = ucwords($nom_alu);
            $consulta_update = $conexion->prepare("UPDATE alumno SET ci_alu = ?, ci_alu2 = ?, nom_alu = ?, sexo = ?, grado = ?, periodo = ? WHERE id_alu = ?");
            $consulta_update->bind_param("sssssss", $ci_alu, $ci_alu2, $nom_alu, $sexo, $grado, $periodo, $id_alu);
            $consulta_update->execute();

            // Auditoría
            $fecha_insc = date("d/m/Y");
            $cod_reg = $ci_alu . '-' . $ci_alu2;
            $ci_usu = $_SESSION["cedula"] ?? '';
            $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
            $desc_reg = 'Actualizó';
            $registro = 'Registrar, Alumno';
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $sql_aud->execute();

            echo "<script>alert('Los datos fueron actualizados con éxito...!!!');</script>";
        } else {
            echo "<script>alert('Cédula No Registrada...!!!');</script>";
        }
        break;

    case "Limpiar":
        // Limpiar formulario
        $nom_alu = $ci_alu = $ci_alu2 = $sexo = $id_alu = $grado = $periodo = "";
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>REGISTRAR ALUMNOS</title>
<!-- Aquí puedes agregar tus estilos y el resto del HTML -->
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
a:link {
	text-decoration: none;
}
a:visited {
	text-decoration: none;
}
a:hover {
	text-decoration: none;
}
a:active {
	text-decoration: none;
}

.sombra{ 
		text-shadow: 0.05em 0.05em 0.03em  #000;
	}
	
.caja { 
    -webkit-box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    -moz-box-shadow: 5px 5px 4px rgba(0,0,0,.5);
    box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    padding: 5px;
    background:#F3F3F3 ;
	margin:5px;
	width:1024px;
		
}	



</style>
</head>

<body>
<center>
 
  

<form id="form1" name="form1" method="post" action="registrar_alumnos.php">
 
 
    
   <!-- <strong>
    <font style="font-size:14px">
    <center>Registro Representante</center>
    </font>
    </strong>-->
    
    
    <div class="caja" align="center" >
<font class="sombra" color="#00366C"  face="Arial Black, Gadget, sans-serif" size="5">
REGISTRAR ALUMNOS 
   </font>
</div>
    
    <table width="599" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr>
      <td height="29" colspan="4" align="center" bgcolor="#E9E9E9"><strong>DATOS DEL ALUMNO</strong></td>
      </tr>
    <tr>
      <td width="107" height="16" bgcolor="#F9F9F9">C&eacute;dula:</td>
      <td width="66" align="left" bgcolor="#F9F9F9"><input name="ci_alu" type="text" id="ci_alu" value="<?php echo $ci_alu;?>"onkeyUp="return ValNumero(this);" maxlength="8" size="9" /></td>
      <td width="19" align="left" bgcolor="#F9F9F9"><input name="ci_alu2" type="text" id="ci_alu2" value="<?php echo $ci_alu2;?>"onkeyup="return ValNumero(this);" maxlength="1" size="1" /></td>
      <td width="381" align="left" bgcolor="#F9F9F9"><input name="borrado" type="hidden" id="borrado" value="<?php echo $borrado;?>" maxlength="8" size="20" />
        <input name="total_buscar" type="hidden" id="total_buscar" value="<?php echo $total_buscar;?>" maxlength="8" size="20" />
        <input name="id_alu" type="hidden" id="id_alu" value="<?php echo $id_alu;?>" maxlength="11" size="20" />
        <input type="submit" name="button" id="button" value="Consultar" /></td>
      
    </tr>
    <tr bgcolor="#E9E9E9">
      <td height="16">Nombre:</td>
      <td colspan="3" align="left"><input name="nom_alu" type="text" id="nom_alu" value="<?php echo $nom_alu;?>" size="50" maxlength="80" /></td>
    </tr>
    <tr>
      <td height="32">Sexo:</td>
      <td colspan="3" align="left"><select name="sexo" size="1" id="sexo">
        <option value=""> </option>
        <option value="M" <?php if ($sexo=='M') {echo "selected='selected'"; }?>>Masculino</option>
        <option value="F"<?php if ($sexo=='F') {echo "selected='selected'"; }?>>Femenino</option>
      </select></td>
      </tr>
    <tr bgcolor="#E9E9E9">
      <td height="34">Curso:</td>
      <td colspan="3" align="left"><select name="grado" size="1" onChange="submit();" id="grado">
        <option value="" 
        </option>
        <?php if (($_SESSION["nivel_intranet"])==0){	
		  if(($_SESSION["grado"])==1){?><option value="1"<?php if ($grado=='1') {echo "selected='selected'"; }?>>1ro</option> <?php }
		  if(($_SESSION["grado"])==2){?><option value="2"<?php if ($grado=='2') {echo "selected='selected'"; }?>>2do</option><?php }
        if(($_SESSION["grado"])==3){?><option value="3"<?php if ($grado=='3') {echo "selected='selected'"; }?>>3ro</option><?php }
        if(($_SESSION["grado"])==4){?><option value="4"<?php if ($grado=='4') {echo "selected='selected'"; }?>>4to</option><?php }
        if(($_SESSION["grado"])==5){?><option value="5"<?php if ($grado=='5') {echo "selected='selected'"; }?>>5to</option><?php }
        if(($_SESSION["grado"])==6){?><option value="6"<?php if ($grado=='6') {echo "selected='selected'"; }?>>6to</option><?php }
        if(($_SESSION["grado"])==7){?><option value="7"<?php if ($grado=='7') {echo "selected='selected'"; }?>>7mo</option><?php }
        if(($_SESSION["grado"])==8){?><option value="8"<?php if ($grado=='8') {echo "selected='selected'"; }?>>8vo</option><?php }
         }else{?><option value="1"<?php if ($grado=='1') {echo "selected='selected'"; }?>>1ro</option>
        <option value="2"<?php if ($grado=='2') {echo "selected='selected'"; }?>>2do</option>
        <option value="3"<?php if ($grado=='3') {echo "selected='selected'"; }?>>3ro</option>
        <option value="4"<?php if ($grado=='4') {echo "selected='selected'"; }?>>4to</option>
        <option value="5"<?php if ($grado=='5') {echo "selected='selected'"; }?>>5to</option>
        <option value="6"<?php if ($grado=='6') {echo "selected='selected'"; }?>>6to</option>
        <option value="7"<?php if ($grado=='7') {echo "selected='selected'"; }?>>7mo</option>
        <option value="8"<?php if ($grado=='8') {echo "selected='selected'"; }?>>8vo</option><?php }?>
         
      </select></td>
    </tr>
    <tr bgcolor="#E9E9E9">
      <td height="34" bgcolor="#FFFFFF">Periodo:</td>
      <td colspan="3" align="left" bgcolor="#FFFFFF"><input name="periodo" type="text" id="periodo" value="<?php echo $periodo;?>" maxlength="5" size="4" /></td>
    </tr>
    <tr>
      <td height="49" colspan="4" align="center" bgcolor="#E9E9E9">
        <input type="submit" name="button" id="button" value="Registrar" />&nbsp;&nbsp;
        <input type="submit" name="button" id="button" value="Actualizar" />&nbsp;
        <?php if($nivel==1){ ?><input type="submit" name="button" id="button" value="Eliminar" />&nbsp;<?php }?>
        <input type="submit" name="button" id="button" value="Limpiar" />&nbsp;</td>
    </tr>
    <tr>
      <td colspan="4" align="center" bgcolor="#FFFFFF"><strong>Nota: Verifique los datos ante de pulsar uno de los botones</strong></strong></td>
    </tr>
    </table>

    
    
  </form>
</center>
</body>
</html>

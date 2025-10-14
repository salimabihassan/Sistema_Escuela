<?php

session_start();
include("conexiones.php");
header('Content-Type: text/html; charset=ISO-8859-1');
error_reporting(0);

$encontrado = "NO";
$confirmado = "NO";
$txtcorreo = trim($_POST["txtcorreo"] ?? '');
$btnenviar = $_POST["btnenviar"] ?? '';
$txtrespuesta = trim($_POST["txtrespuesta"] ?? '');
$txtclave = trim($_POST["txtclave"] ?? '');
$txtconfirma = trim($_POST["txtconfirma"] ?? '');

if ($btnenviar == "Enviar") {
    $coneccion = conectarse_escuela();

    // Buscar usuario por correo
    $consulta = $coneccion->prepare("SELECT * FROM usuarios WHERE email = ?");
    $consulta->bind_param("s", $txtcorreo);
    $consulta->execute();
    $resultado = $consulta->get_result();
    $nroresultados = $resultado->num_rows;

    if ($nroresultados >= 1) {
        $registros = $resultado->fetch_assoc();
        $encontrado = "SI";
        $txtusuario = $registros['usuario'];
        $txtpregunta = $registros['pregunta'];
    } else {
        $txtcorreo = "";
        echo "<script>alert('Correo Invalido...!!!');</script>";
        $encontrado = "NO";
    }
}

if (!empty($txtrespuesta) && $encontrado == "SI") {
    $coneccion = conectarse_escuela();

    // Verificar respuesta secreta
    $consulta = $coneccion->prepare("SELECT * FROM usuarios WHERE respuesta = ? AND email = ?");
    $consulta->bind_param("ss", $txtrespuesta, $txtcorreo);
    $consulta->execute();
    $resultado = $consulta->get_result();
    $nroresultados = $resultado->num_rows;

    if ($nroresultados >= 1) {
        $registros = $resultado->fetch_assoc();
        $confirmado = "SI";
        $txtusuario = $registros['usuario'];
    } else {
        $txtrespuesta = "";
        echo "<script>alert('Respuesta Secreta Incorrecta...!!!');</script>";
        $confirmado = "NO";
    }
}

if (!empty($txtclave) && !empty($txtconfirma) && $confirmado == "SI") {
    if ($txtclave === $txtconfirma) {
        $nuevo_hash = password_hash($txtclave, PASSWORD_DEFAULT);
        $coneccion = conectarse_escuela();

        // Actualizar la contraseña
        $consulta = $coneccion->prepare("UPDATE usuarios SET clave = ? WHERE email = ?");
        $consulta->bind_param("ss", $nuevo_hash, $txtcorreo);
        $consulta->execute();

        if ($consulta->affected_rows >= 1) {
            $txtclave = "";
            $txtconfirma = "";
            $txtrespuesta = "";
            $confirmado = "NO";
            $encontrado = "NO";
            $txtcorreo = "";
            echo "<script>
                alert('Datos Actualizados Exitosamente...!!!');
                window.location.href = 'index.php';
            </script>";
            
        }
    } else {
        $txtclave = "";
        $txtconfirma = "";
        echo "<script>alert('Las Claves son Distintas...!!!');</script>";
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es" dir="ltr">
<head>
<META  NAME="robots" CONTENT="NOINDEX,FOLLOW">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="stylesheet" href="botones.css" type="text/css" media="screen" />
<title>Registrar Usuarios</title>
<style type="text/css">
<!--
body {
	background-image: url();
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
a:link {
	color: #000;
	text-decoration: none;
}
a:visited {
	text-decoration: none;
	color: #000;
}
a:hover {
	text-decoration: none;
	color: #000;
}
a:active {
	text-decoration: none;
	color: #000;
}
body,td,th {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
}

div.formulario {
border: 1px solid #CCC;
-moz-border-radius: 15px;
-webkit-border-radius: 15px;
padding: 10px;
width:350px;


} 
-->
</style>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<br/><br/>
		<div align="center">
        <div class="formulario" >
      <form action="validar_clave.php" method="post" name="frmentrar" target="_top" id="frmentrar" >
        
        <table width="350" height="240" border="0" align="center" cellpadding="0" cellspacing="3" >
          <tr>
            <td height="60" colspan="2" align="center" ><img src="imagenes/logo altas cumbres.jpg"width="290" height="171" /></td>
          </tr>
          
          <tr>
            <td width="170" height="24" align="left" bgcolor="#FFFFFF" ><em><strong>Correo Electronico:</strong></em></td>
            <td width="171" height="24" align="left" bgcolor="#FFFFFF"  >
            <input name="txtcorreo" type="text" size="25" maxlength="40" id="txtcorreo" value="<?php echo $txtcorreo; ?>" /></td>
          </tr>
            <tr>
			
			<?php  if ($encontrado=="SI") {  ?>
            
            <td width="170" height="24" align="left" bgcolor="#FFFFFF" >
            <em><strong>Pregunta secreta:</strong></em></td>
            <td width="171" height="24" align="left" bgcolor="#FFFFFF"  >
            <input name="txtpregunta" type="text" size="25" maxlength="40" id="txtpregunta" value="<?php echo $txtpregunta; ?>" />
            </td>
          </tr>
            <tr>
            <td width="170" height="24" align="left" bgcolor="#FFFFFF"  >
            <em><strong>Respuesta Secreta:</strong></em></td>
            <td width="171" height="24" align="left" bgcolor="#FFFFFF"  >
            <input name="txtrespuesta" type="text" size="25" maxlength="40" id="txtrespuesta" value="<?php echo $txtrespuesta; ?>" />
            </td>
          </tr>
          
           <?php  }  ?>
          
          <?php  if ($confirmado=="SI")  {  ?>
          
          <td width="170" height="24" align="left" bgcolor="#FFFFFF"  >
            <em><strong><font color="#000099">Usuario:</font></strong></em></td>
            <td width="171" height="24" align="left" bgcolor="#FFFFFF" >
            <input name="txtusuario" type="text" disabled="disabled" id="txtclave" value="<?php echo $txtusuario; ?>" size="25" maxlength="40" readonly />
            </td>
          </tr>
          
              <td width="170" height="24" align="left" bgcolor="#FFFFFF"  >
            <em><strong><font color="#000099">Nueva Contraseña:</font></strong></em></td>
            <td width="171" height="24" align="left" bgcolor="#FFFFFF" ><input name="txtclave" type="password" size="25" maxlength="40" id="txtclave" value="<?php echo $txtclave; ?>" /></td>
          </tr>
            <tr>
            <td width="170" height="24" align="left" bgcolor="#FFFFFF" >
            <em><strong><font color="#000099">Confirmar Contraseña:</font></strong></em></td>
            <td width="171" height="24" align="left" bgcolor="#FFFFFF" >
            <input name="txtconfirma" type="password" size="25" maxlength="40" id="txtconfirma" value="<?php echo $txtconfirma; ?>" />
            </td>
          </tr>
           <?php  }  ?>
          
          <tr>
            <td height="42" colspan="2" align="center" bgcolor="#FFFFFF" >
            <input type="submit" name="btnenviar" class="button medium red" value="Enviar" id="btnenviar" />
            </td>
          </tr>
        </table>
	</form>
</div>
</div>

<p align="center">
 
  
  <a href="index.php" target="_top">
<strong>[<font color="#000099">Volver</font> ]</strong></a>

 </p>

</body>
</html>
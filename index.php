<?php
session_start();
include("conexiones.php");
header('Content-Type: text/html; charset=utf-8');


$txtusuario = trim($_POST["txtusuario"] ?? '');
$txtclave = trim($_POST["txtclave"] ?? '');
$btnenviar = $_POST["btnenviar"] ?? '';
$nivel = $_POST["nivel"] ?? '';

if (!isset($_SESSION["sesion_intranet"])) {
    $_SESSION["sesion_intranet"] = "";
    $_SESSION["nombre_apellido"] = "";
    $_SESSION["usuario_intranet"] = "Restringido";
    $_SESSION["nivel_intranet"] = "No";
    $_SESSION["cedula"] = "0";
    $_SESSION["menu"] = "0";
}

if ($btnenviar === "Entrar ") {
    // Validación básica
    if ($txtusuario === '' || $txtclave === '') {
        echo "<script>alert('Debe ingresar usuario y contraseña');</script>";
    } else {
        $coneccion = conectarse_escuela();
		
		if ($coneccion->connect_error) {
			die("Error de conexión a la base de datos: " . $coneccion->connect_error);
		} else {
			// Opcional: mostrar mensaje solo para pruebas
			echo "Conexión exitosa";
}


        // Consulta segura con prepared statements
        $consulta_entrar = $coneccion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $consulta_entrar->bind_param("s", $txtusuario);
        $consulta_entrar->execute();
        $resultado_entrar = $consulta_entrar->get_result();

        if ($resultado_entrar->num_rows >= 1) {
            $datos_entrar = $resultado_entrar->fetch_assoc();
            $activo = $datos_entrar['activo'];

    if (password_verify($txtclave, $datos_entrar['clave'])) {
        echo "password_verify: VERIFICACIÓN EXITOSA<br>";
    } else {
        echo "password_verify: VERIFICACIÓN FALLIDA<br>";
    }

            // Verifica la contraseña usando password_verify
			
            if ($activo == 1 && password_verify($txtclave, $datos_entrar['clave'])) {
                $_SESSION["sesion_intranet"] = "Sesion Abierta";
                $_SESSION["nombre_apellido"] = trim($datos_entrar["nom_prof"]);
                $_SESSION["nivel_intranet"] = $datos_entrar["nivel"];
                $nivel = $datos_entrar['nivel'];
                $_SESSION["id_intranet"] = $datos_entrar["id"];
                $_SESSION["cedula"] = $datos_entrar["cedula"];
                $_SESSION["ci_usu2"] = $datos_entrar["ci_usu2"];
                $ci_prof = $_SESSION["cedula"];
                $ci_prof2 = $_SESSION["ci_usu2"];

                // Consulta segura para curso
                $consulta_entrar2 = $coneccion->prepare("SELECT * FROM curso WHERE ci_prof = ? AND ci_prof2 = ? AND activo = '1'");
                $consulta_entrar2->bind_param("ss", $ci_prof, $ci_prof2);
                $consulta_entrar2->execute();
                $resultado_entrar2 = $consulta_entrar2->get_result();

                if ($resultado_entrar2->num_rows >= 1) {
                    $datos_entrar2 = $resultado_entrar2->fetch_assoc();
                    $_SESSION["grado"] = $datos_entrar2["grado"];
                } else {
                    if ($nivel == 0) {
                        echo "<script>alert('ESTE USUARIO NO TIENE CURSO ASIGNADO O EL CURSO ASIGNADO ESTA INACTIVO, PONGASE EN CONTACTO CON EL ADMINISTRADOR DEL SISTEMA PARA QUE LE ASIGNE UN CURSO');</script>";
                    }
                }

                // Actualizar Estado
                $consulta_Estado = $coneccion->prepare("UPDATE usuarios SET status = 'conectado' WHERE cedula = ? AND ci_usu2 = ?");
                $consulta_Estado->bind_param("ss", $ci_prof, $ci_prof2);
                $consulta_Estado->execute();

                echo "<script>alert('BIENVENIDO AL SISTEMA " . $_SESSION["nombre_apellido"] . "');</script>";
                if ($nivel < 1) {
                    echo '<script>window.location.href="menu2.php";</script>';
                } else {
                    echo '<script>window.location.href="menu.php";</script>';
                }
            } else {
                echo "<script>alert('USUARIO INHABILITADO O CONTRASEÑA INCORRECTA.');</script>";
            }
        } else {
            echo "<script>alert('USUARIO NO REGISTRADO, CLAVE O CORREO ERRONEO... INTENTE DE NUEVO');</script>";
        }
    }
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es" dir="ltr">
<head>
<META  NAME="robots" CONTENT="NOINDEX,FOLLOW">
<LINK REL="SHORTCUT ICON" href="../imagenes/logo.png">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="botones.css" type="text/css" media="screen" />

<title>Acceso de Usuarios</title>
<style type="text/css">
<!--

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
	position: center;
	-webkit-box-shadow: 1px 2px 4px rgba(0,0,0,.5);
	-moz-box-shadow: 1px 2px 4px rgba(0,0,0,.5);
	box-shadow: 1px 2px 4px rgba(0,0,0,.5);
	padding: 10px;
	background: #F0F0F0;
	width:300px;
	


} 

body{
	background-image:url();
	background-repeat:repeat-x;
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-color: #CFF5FE;
	}
-->
</style>

<style type="text/css">
input{
    font-size:12px;
    font-family: Arial, helvetica;
    outline:none;
    transition: all 0.75s ease-in-out;
    -webkit-transition: all 0.75s ease-in-out;
    -moz-transition: all 0.75s ease-in-out;
    border-radius:3px;
    -webkit-border-radius:3px;
    -moz-border-radius:3px;
    border:1px solid rgba(0,0,0, 0.2);
    color:gray;
    background-color:#eee;
    padding: 2px;
}
 
input:focus {
    box-shadow: 0 0 10px #aaa;
    -webkit-box-shadow: 0 0 10px #aaa;
    -moz-box-shadow: 0 0 10px #aaa;
    border:1px solid #999;
    background-color:white;
	
}
</style>



<script language="JavaScript">
function actualizaReloj(){ 
marcacion = new Date() 
Hora = marcacion.getHours() 
Minutos = marcacion.getMinutes() 
Segundos = marcacion.getSeconds() 
if (Hora<=9)
Hora = "0" + Hora
if (Minutos<=9)
Minutos = "0" + Minutos
if (Segundos<=9)
Segundos = "0" + Segundos
var Dia = new Array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
var Mes = new Array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
var Hoy = new Date();
var Anio = Hoy.getFullYear();
var Fecha =Dia[Hoy.getDay()] + ", " + Hoy.getDate() + " de " + Mes[Hoy.getMonth()] + " de " + Anio + ", ";
 
var  Script,Total
/*Script = Fecha+ Hora + ":" + Minutos + ":" + Segundos;*/
Script =Hora + ":" + Minutos + ":" + Segundos;
Total = Script;

/* Capturamos una celda para mostrar el Reloj */
document.getElementById('Fecha_Reloj').innerHTML = Total;
document.getElementById('Fecha_Reloj').value=Total;

setTimeout("actualizaReloj()",1000) 
}
</script>


</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="actualizaReloj()">

<br/>


		<div align="center">
        
        <div class="formulario" >
      <form action="index.php" method="post" name="frmentrar"  id="frmentrar" >
        
        <table width="265" height="336" border="0" align="center" cellpadding="0" bordercolor="#000000">
          <tr>
            <td height="174" colspan="2" align="center" bgcolor="#FFFFFF" >
            <img src="imagenes/logo altas cumbres.jpg" width="253" height="171" />
            </td>
          </tr>
          <tr>
            <td height="24" align="left" bgcolor="#FFFFFF"  ><em><strong>Login</strong></em>:</td>
            <td height="24" align="center" bgcolor="#FFFFFF"  ><input name="txtusuario" type="text" size="30" maxlength="40" id="txtcorreo" /></td>
          </tr>
          <tr>
            <td width="89" height="22" align="left" bgcolor="#FFFFFF"  ><em><strong>Contrase&ntildea:</strong></em></td>
            <td width="170" height="22" align="center" bgcolor="#FFFFFF"  ><input name="txtclave" type="password" size="30" maxlength="40" id="txtclave" /></td>
          </tr>
          <tr>
            <td height="37" colspan="2" align="center" valign="middle" bgcolor="#FFFFFF" >
            <!--<input type="hidden" name="txthora" id="Fecha_Reloj" Fecha_Reloj/>-->
            
            <input type="submit" class="button medium red" name="btnenviar" value="Entrar " id="btnenviar" />
            <label for="nivel"></label>
            <input name="nivel" type="hidden" id="nivel" value="<?php echo $nivel;?>" /></td>
          </tr>
          
          
          
          <tr>
            <td height="26" colspan="2" align="center" bgcolor="#FFFFFF"  >
           <strong> Usuario Nuevo</strong>
           
           <strong>[</strong>
           <a href="cuenta_admin.php" target="_top">
           <font color="#000099"> <strong>Registrarse</strong></font>
           <strong>]</strong></a></td>
          </tr>
          <tr>
            <td height="37" colspan="2" align="center" valign="middle" bgcolor="#FFFFFF"  >
              <strong>   Recordar Contraseña </strong><br/>
              <strong>Haga Click</strong> 
                <a href="validar_clave.php" target="_top">
                <strong>[</strong>
                <font color="#000099"><strong> AQUI</strong></font>
            <strong>]</strong> </a></td>
          </tr>
        </table>
      </form>
        </div>
</div>

</body>
</html>
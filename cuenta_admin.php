<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\cuenta_admin.php
/**
 * MÓDULO: Administrador de Cuentas
 *
 * Permite registrar, buscar y limpiar cuentas de usuario para el sistema escolar.
 * 
 * Requisitos:
 *   - Sesión activa si aplica
 *   - Base de datos 'escuela' con tablas: usuarios, prof, auditoria
 *   - Soporte UTF-8 en la base de datos
 *
 * Versión: 1.0
 * PHP: 8.x
 * Autor: Sistema Escolar
 */

include("conexiones.php");
header('Content-Type: text/html; charset=UTF-8');

$txtcedula = trim($_POST['txtcedula'] ?? '');
$txtnombre_apellido = trim($_POST['txtnombre_apellido'] ?? '');
$txtunidad = trim($_POST['txtunidad'] ?? '');
$txtusuario = trim($_POST['txtusuario'] ?? '');
$txtclave = trim($_POST['txtclave'] ?? '');
$txtconfirma = trim($_POST['txtconfirma'] ?? '');
$txtemail = trim($_POST['txtemail'] ?? '');
$txtpregunta = strtoupper(trim($_POST['txtpregunta'] ?? ''));
$txtrespuesta = strtoupper(trim($_POST['txtrespuesta'] ?? ''));
$ci_prof2 = trim($_POST['ci_prof2'] ?? '');
$ci_usu = trim($_POST['ci_usu'] ?? '');
$cmdAccion = $_POST['btnboton'] ?? '';

$bloquear = "";

if ($cmdAccion == 'Registrarse') {
    if ($txtclave !== $txtconfirma) {
        echo "<script>alert('La Clave de confirmación es distinta...!!!');</script>";
    } else {
        $coneccion = conectarse_escuela();
        $fecha_insc = date("d/m/Y");
        $ci_usu = $txtcedula;
        $ci_usu2 = $ci_prof2;
        $cod_reg = $txtcedula . '-' . $ci_prof2;
        $desc_reg = 'Se Registró';
        $registro = 'Index, Registrarse';

        // Verificar si el usuario ya existe
        $consulta_consultar = $coneccion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $consulta_consultar->bind_param("s", $txtusuario);
        $consulta_consultar->execute();
        $resultado_consultar = $consulta_consultar->get_result();
        $total_consultar = $resultado_consultar->num_rows;

        if ($total_consultar <= 0) {
            $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));

            // Auditoría
            $sql_aud = $coneccion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
            $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
            $sql_aud->execute();

            // Hash seguro para la clave
            $txtclave_hash = password_hash($txtclave, PASSWORD_DEFAULT);

            // Registro de usuario
            $consulta_grabar = $coneccion->prepare("INSERT INTO usuarios (clave, usuario, nom_prof, cedula, nivel, pregunta, respuesta, email, status, ci_usu2, activo) VALUES (?, ?, ?, ?, '0', ?, ?, ?, 'desconectado', ?, '1')");
            $consulta_grabar->bind_param("ssssssss", $txtclave_hash, $txtusuario, $txtnombre_apellido, $txtcedula, $txtpregunta, $txtrespuesta, $txtemail, $ci_prof2);
            $consulta_grabar->execute();

            // Iniciar sesión con el usuario registrado
            $consulta_sesion = $coneccion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
            $consulta_sesion->bind_param("s", $txtusuario);
            $consulta_sesion->execute();
            $resultado_sesion = $consulta_sesion->get_result();
            $total_sesion = $resultado_sesion->num_rows;

            if ($total_sesion > 0) {
                $datos_sesion = $resultado_sesion->fetch_assoc();
                echo "<script>
                    alert('Cuenta creada exitosamente. BIENVENIDO " . htmlspecialchars($datos_sesion["nom_prof"]) . " Ingrese Usuario y Contraseña');
                    window.location.href = 'index.php';
                </script>";
            }
        } else {
            echo "<script>
                alert('Este Usuario " . htmlspecialchars($txtusuario) . " ya está en uso, por favor elija otro...!!!');
            </script>";
        }
    }
}

if ($cmdAccion == 'Buscar') {
    $coneccion = conectarse_escuela();
    $consulta_intranet = $coneccion->prepare("SELECT * FROM usuarios WHERE cedula = ? AND ci_usu2 = ?");
    $consulta_intranet->bind_param("ss", $txtcedula, $ci_prof2);
    $consulta_intranet->execute();
    $resultado_intranet = $consulta_intranet->get_result();
    $usuarios_encontrado = $resultado_intranet->num_rows;
    if ($usuarios_encontrado > 0) {
        $cmdAccion = 'Limpiar';
        echo "<script>
            alert('Usted ya posee una cuenta... siga las instrucciones para recordar Clave...!!!');
        </script>";
    } else {
        $consulta_prof = $coneccion->prepare("SELECT * FROM prof WHERE ci_prof2 = ? AND ci_prof = ?");
        $consulta_prof->bind_param("ss", $ci_prof2, $txtcedula);
        $consulta_prof->execute();
        $resultado_prof = $consulta_prof->get_result();
        $total_prof = $resultado_prof->num_rows;
        if ($total_prof >= 1) {
            $datos_prof = $resultado_prof->fetch_assoc();
            $txtnombre_apellido = $datos_prof['nom_prof'];
            $ci_prof2 = $datos_prof['ci_prof2'];
            $bloquear = "readonly='readonly'";
        } else {
            $cmdAccion = 'Limpiar';
            echo "<script>
                alert('Usted no ha sido Registrado, debe ponerse en contacto con el administrador del sistema para que lo agregue como usuario');
            </script>";
        }
    }
}

if ($cmdAccion == 'Limpiar') {
    $txtcedula = '';
    $ci_prof2 = '';
    $txtnombre_apellido = '';
    $txtunidad = '';
    $txtusuario = '';
    $txtclave = '';
    $txtconfirma = '';
    $txtemail = '';
    $txtpregunta = '';
    $txtrespuesta = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Administrador de Cuentas</title>
<script type="text/javascript">
function validar_cedula(formulario) {
  if(formulario.txtcedula.value == '') {
    alert('Ingrese Cédula');
    formulario.txtcedula.focus();
    return false;
  }
  if ((formulario.nac_prof.value.length < 2)) {
    alert('Seleccione nacionalidad');
    formulario.nac_prof.focus();
    return false;
  }
  if ((formulario.txtcedula.value.length < 5) || (formulario.txtcedula.value.length > 8)) {
    alert('Número Cédula no válido');
    formulario.txtcedula.focus();
    return false;
  }
  return true; 
}

function validar_carga(formulario) {
  if(formulario.txtcedula.value == '') {
    alert('Ingrese Cédula');
    formulario.txtcedula.focus();
    return false;
  }
  if(formulario.txtusuario.value == '') {
    alert('Ingrese su Usuario');
    formulario.txtusuario.focus();
    return false;
  }
  if(formulario.txtclave.value == '') {
    alert('Ingrese su Clave');
    formulario.txtclave.focus();
    return false;
  }
  if(formulario.txtconfirma.value != formulario.txtclave.value) {
    alert('La Clave de confirmación es distinta...!!!');
    formulario.txtconfirma.focus();
    return false;
  }
  if (!/^\w+([\.\-\_]?\w+)*@\w+([\.-]?\w+)*(\.\D{2,4})+$/.test(formulario.txtemail.value)) {
    alert("La dirección de email es incorrecta.");
    formulario.txtemail.focus();
    return false;
  }
  if(formulario.txtpregunta.value == '') {
    alert('Datos Requerido: Pregunta Secreta');
    formulario.txtpregunta.focus();
    return false;
  }
  if(formulario.txtrespuesta.value == '') {
    alert('Datos Requerido: Respuesta Secreta');
    formulario.txtrespuesta.focus();
    return false;
  }
  return true;
}

function validar_Solo_Numeros(e) { 
    var tecla = (document.all) ? e.keyCode : e.which; 
    if (tecla == 8) return true; 
    var patron = /\d/; 
    var te = String.fromCharCode(tecla); 
    return patron.test(te);
}
</script>
<style type="text/css">
div {
    border: 1px solid #CCC;
    border-radius: 15px;
    padding: 10px;
} 
body,td,th {
    font-family: Verdana, Geneva, sans-serif;
    font-size: 12px;
}
body {
    margin: 0;
}
a:link, a:visited, a:hover, a:active {
    color: #333;
    text-decoration: none;
}
</style>
</head>
<body>
<table width="700" border="0" align="center" cellpadding="0">
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
      <div align="center">
        <!-- Primer Formulario -->
        <form action="cuenta_admin.php" method="post" enctype="multipart/form-data" name="frmbuscar" onsubmit="return validar_cedula(this)">
          <table width="642" border="0" align="center" cellpadding="0">
            <tr>
              <td height="34" colspan="4" align="center" bgcolor="#B4ECFE">
                <font size="+1" color="#000099"><strong>Datos Personales</strong></font>
              </td>
            </tr>
            <tr>
              <td height="31" colspan="4" align="center" bgcolor="#B4ECFE">
                <strong><font color="#000099">Introduzca el Número de Cédula y presione el Botón Buscar</font></strong>
              </td>
            </tr>
            <tr>
              <td width="122">Cédula</td>
              <td width="62">
                <input name="txtcedula" type="text" id="txtcedula" value="<?php echo $txtcedula; ?>" size="9" maxlength="9" onkeypress="return validar_Solo_Numeros(event)" <?php echo $bloquear; ?> />
              </td>
              <td width="12">
                <input name="ci_prof2" type="text" id="ci_prof2" value="<?php echo $ci_prof2; ?>" size="1" maxlength="1" <?php echo $bloquear; ?> />
              </td>
              <td width="436">
                <input type="submit" name="btnboton" id="btnboton" value="Buscar" />
              </td>
            </tr>
            <?php if (!empty($txtcedula)) { ?>
            <tr>
              <td>Nombre y Apellido:</td>
              <td colspan="3">
                <input name="txtnombre_apellido" type="text" id="txtnombre_apellido" value="<?php echo $txtnombre_apellido; ?>" size="60" readonly />
              </td>
            </tr>
            <?php } ?>
          </table>
        </form>
        <!-- Fin del primer Formulario -->
        <br />
        <!-- Segundo Formulario -->
        <?php if (!empty($txtcedula)) { ?>
        <form action="cuenta_admin.php" target="_top" method="post" enctype="multipart/form-data" name="frmcarga" onsubmit="return validar_carga(this)">
          <table width="645" border="0" cellpadding="0">
            <tr>
              <td height="34" colspan="2" align="center" bgcolor="#B4ECFE">
                <font size="+1" color="#000099"><strong>Datos de Registro</strong></font>
              </td>
            </tr>
            <tr>
              <td width="187">Login:</td>
              <td width="435">
                <input name="txtusuario" type="text" id="txtusuario" value="<?php echo $txtusuario; ?>" />
                <input name="txtcedula" type="hidden" id="txtcedula" value="<?php echo $txtcedula; ?>" />
                <input name="ci_prof2" type="hidden" id="ci_prof2" value="<?php echo $ci_prof2; ?>" />
              </td>
            </tr>
            <tr>
              <td>Contraseña:</td>
              <td>
                <input name="txtclave" type="password" id="txtclave" value="<?php echo $txtclave; ?>" />
                <input name="txtnombre_apellido" type="hidden" id="txtnombre_apellido" value="<?php echo $txtnombre_apellido; ?>" />
              </td>
            </tr>
            <tr>
              <td>Confirma Contraseña:</td>
              <td>
                <input name="txtconfirma" type="password" id="txtconfirma" value="<?php echo $txtconfirma; ?>" />
                <input name="txtunidad" type="hidden" id="txtunidad" value="<?php echo $txtunidad; ?>" />
              </td>
            </tr>
            <tr>
              <td>Correo Electrónico:</td>
              <td>
                <input name="txtemail" type="text" id="txtemail" value="<?php echo $txtemail; ?>" size="60" />
              </td>
            </tr>
            <tr>
              <td>Pregunta Secreta:</td>
              <td>
                <input name="txtpregunta" type="text" id="txtpregunta" value="<?php echo $txtpregunta; ?>" size="60" />
              </td>
            </tr>
            <tr>
              <td>Respuesta:</td>
              <td>
                <input name="txtrespuesta" type="text" id="txtrespuesta" value="<?php echo $txtrespuesta; ?>" size="60" />
              </td>
            </tr>
            <tr>
              <td height="37" colspan="2" align="center">
                <input type="submit" name="btnboton" id="btnboton" value="Registrarse" />
              </td>
            </tr>
            <tr>
              <td colspan="2" align="center">&nbsp;</td>
            </tr>
          </table>
        </form>
        <p align="center"><strong>Nota:</strong> El sistema distingue entre Mayúsculas y Minúsculas</p>
        <?php } ?>
        <!-- Fin del Segundo Formulario -->
      </div>
    </td>
  </tr>
</table>
<p align="center">
  <a href="index.php" target="_top"><strong>[ <font color="#000099">Volver</font> ]</strong></a>
</p>
</body>
</html>
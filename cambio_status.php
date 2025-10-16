<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\cambio_status.php
/**
 * MÓDULO: Cambio de Estatus de Usuario
 *
 * Permite buscar y cambiar el estatus de acceso de los usuarios del sistema escolar.
 *
 * Requisitos:
 *   - Sesión activa
 *   - Base de datos 'escuela' con tablas: usuarios, prof, auditoria
 *   - Soporte UTF-8 en la base de datos
 *
 * Versión: 1.0
 * PHP: 8.x
 * Autor: Sistema Escolar
 */

// incluir validación de sesión y UTF-8
include __DIR__ . '/auth.php';

// luego incluir conexión a BD
include __DIR__ . '/conexiones.php';

// Configurar UTF-8
header('Content-Type: text/html; charset=UTF-8');


$ci_prof = $_POST['ci_prof'] ?? '';
$ci_prof2 = trim($_POST['ci_prof2'] ?? '');
$nom_prof = $_POST['nom_prof'] ?? '';
$nivel = $_POST['nivel'] ?? '';
$button = $_POST['button'] ?? '';

if ($button === "Guardar") {
    if (empty($ci_prof2)) {
        echo "<script>alert('El campo validación de Cédula no puede estar vacío');</script>";
    } elseif (empty($ci_prof)) {
        echo "<script>alert('Debe seleccionar Cédula válida');</script>";
    } else {
        $conexion = conectarse_escuela();
        $consulta_buscar = $conexion->prepare("SELECT * FROM usuarios WHERE cedula = ? AND ci_usu2 = ?");
        $consulta_buscar->bind_param("ss", $ci_prof, $ci_prof2);
        $consulta_buscar->execute();
        $resultado_buscar = $consulta_buscar->get_result();
        if ($resultado_buscar->num_rows > 0) {
            $consulta_update = $conexion->prepare("UPDATE usuarios SET activo = ? WHERE cedula = ? AND ci_usu2 = ?");
            $consulta_update->bind_param("sss", $nivel, $ci_prof, $ci_prof2);
            $consulta_update->execute();
            if ($consulta_update->affected_rows > 0) {
                $status = ($nivel == '1') ? 'Activo' : 'Desactivo';
                $fecha_insc = date("d/m/Y");
                $cod_reg = $ci_prof . '-' . $ci_prof2;
                $ci_usu = $_SESSION["cedula"] ?? '';
                $ci_usu2 = $_SESSION["ci_usu2"] ?? '';
                $desc_reg = 'Cambio Estatus de Acceso a ' . $status;
                $registro = 'Mantenimiento, Act. o Desact. Usuario';
                $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));
                $sql_aud = $conexion->prepare("INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) VALUES (?, ?, ?, ?, ?, ?)");
                $sql_aud->bind_param("ssssss", $cod_reg, $ci_usu, $ci_usu2, $desc_reg, $registro, $FechaMySQL);
                $sql_aud->execute();
                echo "<script>alert('Actualización realizada con éxito');</script>";
            }
        }
    }
}

if ($button === "Buscar") {
    if (empty($ci_prof2)) {
        echo "<script>alert('El campo validación de Cédula no puede estar vacío');</script>";
    } elseif (empty($ci_prof)) {
        echo "<script>alert('Debe seleccionar Cédula válida');</script>";
    } else {
        $conexion = conectarse_escuela();
        $consulta_buscar_usu = $conexion->prepare("SELECT * FROM usuarios WHERE cedula = ? AND ci_usu2 = ?");
        $consulta_buscar_usu->bind_param("ss", $ci_prof, $ci_prof2);
        $consulta_buscar_usu->execute();
        $resultado_buscar_usu = $consulta_buscar_usu->get_result();
        if ($resultado_buscar_usu->num_rows > 0) {
            $datos_buscar_usu = $resultado_buscar_usu->fetch_assoc();
            $consulta_buscar = $conexion->prepare("SELECT * FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
            $consulta_buscar->bind_param("ss", $ci_prof, $ci_prof2);
            $consulta_buscar->execute();
            $resultado_buscar = $consulta_buscar->get_result();
            if ($resultado_buscar->num_rows > 0) {
                $datos_buscar = $resultado_buscar->fetch_assoc();
                $retirado = $datos_buscar['retirado'];
                if ($retirado == 0) {
                    $nom_prof = $datos_buscar['nom_prof'];
                    $nivel = $datos_buscar_usu['activo'];
                } else {
                    echo "<script>alert('Este Nro. de Cédula fue eliminado de la Base de Datos, Para Recuperarlo vaya a la Papelera de Reciclaje...!!!');</script>";
                    $nom_prof = "";
                }
            } else {
                echo "<script>alert('Cédula no registrada...!!!');</script>";
                $nom_prof = "";
            }
        } else {
            echo "<script>alert('Cédula no registrada como usuario...!!!');</script>";
            $nom_prof = "";
            $nivel = "";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Registrar Usuarios</title>
<link rel="stylesheet" href="botones.css" type="text/css" media="screen" />
<style>
body {
    background: #f7f7f7;
    margin: 0;
    padding: 0;
    font-family: Verdana, Geneva, sans-serif;
    font-size: 13px;
}
a { color: #000; text-decoration: none; }
.formulario {
    border: 1px solid #CCC;
    border-radius: 15px;
    padding: 18px;
    width: 370px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    margin-top: 40px;
}
table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    background: #f8f8f8;
    border-radius: 8px;
    margin-bottom: 10px;
}
th, td {
    padding: 8px 6px;
    text-align: left;
}
img.logo {
    border-radius: 8px;
    margin-bottom: 10px;
}
.button {
    padding: 8px 18px;
    border-radius: 6px;
    border: none;
    background: #1976d2;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
}
.button:hover {
    background: #1565c0;
}
</style>
</head>
<body>
<div align="center">
    <div class="formulario">
        <form action="cambio_status.php" method="post" name="frmentrar" id="frmentrar">
            <table>
                <tr>
                    <td colspan="4" align="center">
                        <img src="/Sistema_Escuela/imagenes/logo altas cumbres.jpg" width="179" height="89" class="logo" alt="Logo Altas Cumbres" />
                    </td>
                </tr>
                <tr>
                    <td width="121"><strong>Cédula:</strong></td>
                    <td width="103">
                        <input name="ci_prof" type="text" id="ci_prof" value="<?php echo htmlspecialchars($ci_prof); ?>" maxlength="9" size="15" />
                    </td>
                    <td width="9">
                        <input name="ci_prof2" type="text" id="ci_prof2" value="<?php echo htmlspecialchars($ci_prof2); ?>" maxlength="1" size="1" />
                    </td>
                    <td width="105">
                        <input type="submit" name="button" class="button" value="Buscar" id="button" />
                    </td>
                </tr>
                <tr>
                    <td><strong>Nombre:</strong></td>
                    <td colspan="3">
                        <input name="nom_prof" type="text" id="nom_prof" value="<?php echo htmlspecialchars($nom_prof); ?>" size="30" maxlength="80" />
                    </td>
                </tr>
                <tr>
                    <td><strong>Estatus de Acceso:</strong></td>
                    <td colspan="3">
                        <select name="nivel" id="nivel">
                            <option value="" <?php if ($nivel=='') echo "selected"; ?>></option>
                            <option value="1" <?php if ($nivel=='1') echo "selected"; ?>>Activo</option>
                            <option value="0" <?php if ($nivel=='0') echo "selected"; ?>>Inactivo</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" align="center">
                        <input type="submit" name="button" class="button" value="Guardar" id="button" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
</body>
</html>
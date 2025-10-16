<?php


// luego incluir conexión a BD
include __DIR__ . '/conexiones.php';

// Inicializar variables
$encontrado = "NO";
$confirmado = "NO";
$txtcorreo = isset($_POST["txtcorreo"]) ? trim($_POST["txtcorreo"]) : '';
$btnenviar = isset($_POST["btnenviar"]) ? $_POST["btnenviar"] : '';
$txtrespuesta = isset($_POST["txtrespuesta"]) ? trim($_POST["txtrespuesta"]) : '';
$txtclave = isset($_POST["txtclave"]) ? trim($_POST["txtclave"]) : '';
$txtconfirma = isset($_POST["txtconfirma"]) ? trim($_POST["txtconfirma"]) : '';
$txtpregunta = '';
$txtusuario = '';

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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="NOINDEX,FOLLOW">

    <title>Validar Clave - Sistema Escuela</title>
    <style>
        /* Reset básico */
        * {
            box-sizing: border-box;
        }

        body, td, th {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        /* Enlaces */
        a {
            color: #000;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
            color: #000099;
        }

        /* Contenedor principal */
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Formulario */
        .formulario {
            border: 1px solid #CCC;
            border-radius: 15px;
            padding: 20px;
            width: 100%;
            max-width: 400px;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        /* Tabla del formulario */
        .form-table {
            width: 100%;
            border-collapse: collapse;
        }

        .form-table td {
            padding: 8px;
            vertical-align: middle;
        }

        /* Inputs */
        input[type="text"], 
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #000099;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 0, 153, 0.3);
        }

        /* Botón */
        .btn-submit {
            background-color: #000099;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-submit:hover {
            background-color: #000066;
        }

        /* Logo */
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        /* Etiquetas */
        .label {
            font-weight: bold;
            color: #333;
        }

        .label-blue {
            color: #000099;
        }

        /* Mensajes */
        .back-link {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="formulario">
            <div class="logo">
                <img src="imagenes/logo altas cumbres.jpg" alt="Logo Altas Cumbres" width="290" height="171">
            </div>
            
            <form action="validar_clave.php" method="post" name="frmentrar" id="frmentrar">
                <table class="form-table">
          
                    <tr>
                        <td class="label">Correo Electrónico:</td>
                        <td>
                            <input name="txtcorreo" type="text" maxlength="40" id="txtcorreo" 
                                   value="<?php echo htmlspecialchars($txtcorreo); ?>" required>
                        </td>
                    </tr>
                    
                    <?php if ($encontrado == "SI") { ?>
                    <tr>
                        <td class="label">Pregunta secreta:</td>
                        <td>
                            <input name="txtpregunta" type="text" maxlength="40" id="txtpregunta" 
                                   value="<?php echo htmlspecialchars($txtpregunta); ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Respuesta Secreta:</td>
                        <td>
                            <input name="txtrespuesta" type="text" maxlength="40" id="txtrespuesta" 
                                   value="<?php echo htmlspecialchars($txtrespuesta); ?>" 
                                   autocomplete="off" required>
                        </td>
                    </tr>
                    <?php } ?>
          
                    <?php if ($confirmado == "SI") { ?>
                    <tr>
                        <td class="label label-blue">Usuario:</td>
                        <td>
                            <input name="txtusuario" type="text" id="txtusuario" 
                                   value="<?php echo htmlspecialchars($txtusuario); ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="label label-blue">Nueva Contraseña:</td>
                        <td>
                            <input name="txtclave" type="password" maxlength="40" id="txtclave" 
                                   value="<?php echo htmlspecialchars($txtclave); ?>" 
                                   autocomplete="new-password" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label label-blue">Confirmar Contraseña:</td>
                        <td>
                            <input name="txtconfirma" type="password" maxlength="40" id="txtconfirma" 
                                   value="<?php echo htmlspecialchars($txtconfirma); ?>" 
                                   autocomplete="new-password" required>
                        </td>
                    </tr>
                    <?php } ?>
          
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 20px;">
                            <input type="submit" name="btnenviar" value="Enviar" id="btnenviar" class="btn-submit">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div class="back-link">
            <a href="index.php">
                <strong>[Volver]</strong>
            </a>
        </div>
    </div>
</body>
</html>
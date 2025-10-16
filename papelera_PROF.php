<?php
// incluir validación de sesión y UTF-8
include __DIR__ . '/auth.php';

// luego incluir conexión a BD
include __DIR__ . '/conexiones.php';

$conexion = conectarse_escuela();

// Inicializar variables de sesión
$nombre_apellido = isset($_SESSION["nombre_apellido"]) ? $_SESSION["nombre_apellido"] : '';
$cedula = isset($_SESSION["cedula"]) ? $_SESSION["cedula"] : '';
$ci_usu2 = isset($_SESSION["ci_usu2"]) ? $_SESSION["ci_usu2"] : '';

// Inicializar variables
$fecha_insc = date("d/m/Y");
$btnaccion = isset($_POST['btnaccion']) ? $_POST['btnaccion'] : '';
$total_sql = isset($_POST['total_sql']) ? intval($_POST['total_sql']) : 0;
$txtci_promover = isset($_POST['txtci_promover']) ? $_POST['txtci_promover'] : '';
$txtced_promover = isset($_POST['txtced_promover']) ? $_POST['txtced_promover'] : '';

// Arrays para manejar los datos del formulario
$chkreprovar = array();
$ci_promover_array = array();
$txtced_promover_array = array();

if ($btnaccion == 'Recuperar' && $total_sql > 0) {
    for ($i = 1; $i <= $total_sql; $i++) {
        $chkreprovar[$i] = isset($_POST["chkreprovar".$i]) ? $_POST["chkreprovar".$i] : '';
        $ci_promover_array[$i] = isset($_POST["ci_promover".$i]) ? $_POST["ci_promover".$i] : '';
        $txtced_promover_array[$i] = isset($_POST["txtced_promover".$i]) ? $_POST["txtced_promover".$i] : '';
    }
}





// Procesar la acción de recuperar
switch ($btnaccion) {
    case "Recuperar":
        if ($total_sql > 0) {
            for ($i = 1; $i <= $total_sql; $i++) {
                if (isset($chkreprovar[$i]) && $chkreprovar[$i] == "SI") {
                    $conexion = conectarse_escuela();
                    $ci_prof2 = mysqli_real_escape_string($conexion, $ci_promover_array[$i]);
                    $ci_prof = mysqli_real_escape_string($conexion, $txtced_promover_array[$i]);
                    
                    // Actualizar el registro del profesor
                    $consulta = "UPDATE prof SET retirado = 0 
                               WHERE ci_prof = ? AND ci_prof2 = ?";
                    
                    if ($stmt = mysqli_prepare($conexion, $consulta)) {
                        mysqli_stmt_bind_param($stmt, "ss", $ci_prof, $ci_prof2);
                        mysqli_stmt_execute($stmt);
                        $total = mysqli_stmt_affected_rows($stmt);
                        mysqli_stmt_close($stmt);
                        
                        // Registrar en auditoría
                        $fecha_insc = date("d/m/Y");
                        $ci_usu = $_SESSION["cedula"];
                        $ci_usu2_session = $_SESSION["ci_usu2"];
                        $cod_reg = $ci_prof . '-' . $ci_prof2;
                        $desc_reg = 'Recuperó';
                        $registro = 'Papelera, Profesor';
                        
                        $FechaMySQL = implode('-', array_reverse(explode('/', $fecha_insc)));
                        
                        $sql_aud = "INSERT INTO auditoria (cod_reg, ci_prof, ci_prof2, desc_reg, registro, fecha_reg) 
                                   VALUES (?, ?, ?, ?, ?, ?)";
                        
                        if ($stmt_aud = mysqli_prepare($conexion, $sql_aud)) {
                            mysqli_stmt_bind_param($stmt_aud, "ssssss", $cod_reg, $ci_usu, $ci_usu2_session, $desc_reg, $registro, $FechaMySQL);
                            mysqli_stmt_execute($stmt_aud);
                            mysqli_stmt_close($stmt_aud);
                        }
                    }
                }
            }
        }
        break;
}
?>






<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAPELERA DE RECICLAJE - PROFESOR</title>
    <style>
        /* Reset básico */
        body, td, th {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f5f5f5;
        }

        /* Contenedor principal */
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Enlaces */
        a {
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Clase para sombra de texto */
        .sombra {
            text-shadow: 0.05em 0.05em 0.03em #000;
        }

        /* Caja principal */
        .caja {
            box-shadow: 5px 5px 5px rgba(0, 0, 0, 0.5);
            padding: 20px;
            background: #F3F3F3;
            margin: 5px;
            width: 100%;
            max-width: 1024px;
            border-radius: 5px;
            text-align: center;
        }

        /* Título principal */
        .titulo-principal {
            color: #00366C;
            font-family: "Arial Black", Arial, sans-serif;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Tabla de datos */
        .tabla-datos {
            width: 60%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        .tabla-datos td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .tabla-datos th {
            padding: 8px;
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            border: 1px solid #ddd;
        }

        /* Botones */
        .btn-submit {
            background-color: #00366C;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-submit:hover {
            background-color: #004080;
        }

        /* Formulario */
        .form-container {
            width: 100%;
            max-width: 1200px;
        }

        /* Ocultar divs no utilizados */
        #apDiv1, #apDiv2 {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <form id="form1" name="form1" method="post" action="papelera_PROF.php" class="form-container">
            <div class="caja">
                <div class="titulo-principal sombra">
                    PAPELERA DE RECICLAJE DEL PROFESOR
                </div>
                <p>Fecha: <?php echo htmlspecialchars($fecha_insc); ?></p>
                <input name="fecha_insc2" type="hidden" id="fecha_insc2" value="<?php echo htmlspecialchars($fecha_insc); ?>">
            </div>
            <div>
                <?php
                $consulta_sql = "SELECT * FROM prof WHERE retirado = 1";
                $resultado_sql = mysqli_query($conexion, $consulta_sql);
                $total_sql = mysqli_num_rows($resultado_sql);
                ?>
            </div>

            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th colspan="2">Nro.</th>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Recuperar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    $fila = 1;
                    while ($datos_sql = mysqli_fetch_array($resultado_sql)) {
                        $resto = $fila % 2;
                        $color = ($resto == 0) ? "#FFFFFF" : "#CCEEFF";
                        $fila++;
                        $i++;
                    ?>
                        <tr style="background-color: <?php echo $color; ?>">
                            <td colspan="2" style="text-align: center;">
                                <?php echo $i; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php echo htmlspecialchars($datos_sql['ci_prof'] . '-' . $datos_sql['ci_prof2']); ?>
                                <input name="ci_promover<?php echo $i; ?>" type="hidden" 
                                       value="<?php echo htmlspecialchars($datos_sql['ci_prof2']); ?>">
                                <input name="txtced_promover<?php echo $i; ?>" type="hidden" 
                                       value="<?php echo htmlspecialchars($datos_sql['ci_prof']); ?>">
                            </td>
                            <td style="text-align: left;">
                                <?php echo htmlspecialchars($datos_sql['nom_prof']); ?>
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" name="chkreprovar<?php echo $i; ?>" 
                                       <?php if (isset($chkreprovar[$i]) && $chkreprovar[$i] == 'SI') echo 'checked="checked"'; ?> 
                                       value="SI">
                            </td>
                        </tr>
                    <?php } ?>


                    <tr>
                        <td colspan="5" style="text-align: center; padding: 15px;">
                            <input type="submit" name="btnaccion" id="btnaccion" value="Recuperar" class="btn-submit">
                            <input name="total_sql" type="hidden" value="<?php echo $total_sql; ?>">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</body>
</html>

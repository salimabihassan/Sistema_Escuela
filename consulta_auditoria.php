<?php
// filepath: c:\xampp\htdocs\Sistema_Escuela\consulta_auditoria.php
/**
 * consulta_auditoria.php
 * Consulta de auditoría por mes/año.
 *
 * - Usa auth.php para validación de sesión y UTF-8.
 * - Usa conexiones.php (mysqli) para consultas.
 * - Salida en UTF-8.
 * - Mantiene diseño visual original y sin duplicados.
 */

include_once __DIR__ . '/auth.php';
include_once __DIR__ . '/conexiones.php';

// Forzar salida UTF-8 (auth.php ya lo pone, pero se asegura aquí)
header('Content-Type: text/html; charset=UTF-8');

$mes     = trim($_POST['mes'] ?? '');
$ano     = trim($_POST['ano'] ?? '');
$button  = $_POST['button'] ?? '';

$rows = [];
$total_alu = 0;

$conexion = conectarse_escuela();

/**
 * Formatea fecha YYYY-MM-DD a DD/MM/YYYY
 */
function fentrada($cambio){
    if (empty($cambio)) return '';
    $d = date_create($cambio);
    return $d ? date_format($d, 'd/m/Y') : '';
}

if ($button === "Buscar") {
    // Año por defecto: año actual si no se indicó
    $year = (!empty($ano) && ctype_digit($ano)) ? $ano : date('Y');

    if ($mes !== '' && ctype_digit($mes)) {
        // Normalizar mes a 2 dígitos
        $m = str_pad((int)$mes, 2, '0', STR_PAD_LEFT);
        $fecha1 = "$year-$m-01";
        // calcular último día del mes
        $dt = new DateTime($fecha1);
        $dt->modify('last day of this month');
        $fecha2 = $dt->format('Y-m-d');

        $sql = "SELECT * FROM auditoria WHERE fecha_reg BETWEEN ? AND ? ORDER BY fecha_reg";
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("ss", $fecha1, $fecha2);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($r = $result->fetch_assoc()) {
                $rows[] = $r;
            }
            $stmt->close();
        }
    } else {
        // Si no hay mes válido, traer todos (ordenados)
        $sql = "SELECT * FROM auditoria ORDER BY fecha_reg";
        if ($result = $conexion->query($sql)) {
            while ($r = $result->fetch_assoc()) {
                $rows[] = $r;
            }
            $result->close();
        }
    }
    $total_alu = count($rows);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Auditoría por Mes</title>
<style type="text/css">
body,td,th { font-family: Verdana, Geneva, sans-serif; font-size: 12px; }
body { margin:0; }
.sombra{ text-shadow: 0.05em 0.05em 0.03em #000; }
.caja {
    -webkit-box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    -moz-box-shadow: 5px 5px 4px rgba(0,0,0,.5);
    box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    padding: 5px;
    background:#F3F3F3;
    margin:5px;
    width:1024px;
}
table { border-collapse: collapse; width: 80%; margin: 10px auto; }
th, td { border: 1px solid #DDD; padding: 6px; text-align: center; }
tr:nth-child(odd) { background: #CCEEFF; }
tr:nth-child(even) { background: #FFFFFF; }
.button { padding:6px 10px; border-radius:4px; background:#1976d2; color:#fff; border:none; cursor:pointer; }
.input-small { width:60px; }
</style>
<script type="text/javascript">
function Solo_Numerico(variable){
    var Numer = parseInt(variable);
    if (isNaN(Numer)) return "";
    return Numer;
}
function ValNumero(Control){
    Control.value = Solo_Numerico(Control.value);
}
</script>
</head>
<body>
<center>
  <form id="form1" name="form1" method="post" action="consulta_auditoria.php">
    <div class="caja" align="center">
      <font color="#00366C" size="5" face="Arial Black, Gadget, sans-serif">AUDITORÍA POR MES</font>
    </div>

    <table border="0" align="center" cellpadding="2" cellspacing="2">
      <tr bgcolor="#D7D7D7">
        <td colspan="7"><strong>INTRODUZCA EL MES Y EL AÑO A CONSULTAR</strong></td>
      </tr>
      <tr>
        <td align="right">Mes:</td>
        <td>
          <select name="mes" id="mes">
            <option value=""></option>
            <?php
            $nombresEsp = ['January'=>'Enero','February'=>'Febrero','March'=>'Marzo','April'=>'Abril','May'=>'Mayo','June'=>'Junio','July'=>'Julio','August'=>'Agosto','September'=>'Septiembre','October'=>'Octubre','November'=>'Noviembre','December'=>'Diciembre'];
            for ($m=1;$m<=12;$m++):
                $sel = ($mes == (string)$m) ? "selected" : "";
                $nombre = date("F", mktime(0,0,0,$m,1,2000));
                $label = $nombresEsp[$nombre] ?? $nombre;
            ?>
            <option value="<?php echo $m; ?>" <?php echo $sel; ?>><?php echo $label; ?></option>
            <?php endfor; ?>
          </select>
        </td>
        <td>Año:</td>
        <td><input name="ano" type="text" id="ano" value="<?php echo htmlspecialchars($ano); ?>" maxlength="5" size="4" onkeyup="ValNumero(this)" /></td>
        <td><input type="submit" name="button" id="button" class="button" value="Buscar" /></td>
        <td>Nº de Registros:</td>
        <td><?php echo $total_alu; ?></td>
      </tr>
      <tr bgcolor="#D7D7D7">
        <td colspan="7"><strong>Nota: Verifique los datos antes de pulsar el botón Buscar</strong></td>
      </tr>
    </table>

    <?php if ($total_alu > 0): ?>
    <table>
      <tr>
        <th>Nº</th>
        <th>Cédula Usuario</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Cod Registro</th>
        <th>Módulo</th>
        <th>Fecha</th>
      </tr>
      <?php
        $i = 0;
        // preparar statement para obtener nombre del prof si existe
        $stmt_prof = $conexion->prepare("SELECT nom_prof, retirado FROM prof WHERE ci_prof = ? AND ci_prof2 = ?");
        foreach ($rows as $datos_alu) {
            $i++;
            $ci_prof = $datos_alu['ci_prof'] ?? '';
            $ci_prof2 = $datos_alu['ci_prof2'] ?? '';
            $nom_prof = '';
            // obtener nombre del prof (si existe)
            if ($stmt_prof) {
                $stmt_prof->bind_param("ss", $ci_prof, $ci_prof2);
                $stmt_prof->execute();
                $res_prof = $stmt_prof->get_result();
                if ($rowp = $res_prof->fetch_assoc()) {
                    if (isset($rowp['retirado']) && $rowp['retirado'] == 0) {
                        $nom_prof = $rowp['nom_prof'] ?? '';
                    } else {
                        $nom_prof = '';
                    }
                }
                if ($res_prof) $res_prof->free();
            }
            $desc_reg = $datos_alu['desc_reg'] ?? '';
            $cod_reg  = $datos_alu['cod_reg']  ?? '';
            $registro = $datos_alu['registro'] ?? '';
            $fecha    = $datos_alu['fecha_reg'] ?? '';
            $fecha_fmt = $fecha ? date("d-m-Y", strtotime($fecha)) : '';
      ?>
      <tr>
        <td><?php echo $i; ?></td>
        <td><?php echo htmlspecialchars($ci_prof . '-' . $ci_prof2); ?></td>
        <td><?php echo htmlspecialchars($nom_prof); ?></td>
        <td><?php echo htmlspecialchars($desc_reg); ?></td>
        <td><?php echo htmlspecialchars($cod_reg); ?></td>
        <td><?php echo htmlspecialchars($registro); ?></td>
        <td><?php echo htmlspecialchars($fecha_fmt); ?></td>
      </tr>
      <?php } 
        if ($stmt_prof) $stmt_prof->close();
      ?>
      <tr>
        <td colspan="7" align="center"><input type="button" value="Imprimir" onclick="window.print()" class="button" /></td>
      </tr>
    </table>
    <?php endif; ?>

  </form>
</center>
</body>
</html>
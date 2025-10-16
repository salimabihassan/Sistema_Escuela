<?php
// incluir validación de sesión y UTF-8
include __DIR__ . '/../auth.php';

// Solo administradores pueden ver esta información
if ($_SESSION['nivel_intranet'] != 'administrador') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Respaldos Automáticos - Sistema Escuela</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .title {
            color: #00366C;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0.05em 0.05em 0.03em #000;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .status-item {
            margin-bottom: 10px;
            padding: 8px;
            border-left: 4px solid #00366C;
            background: #f8f9fa;
        }

        .status-success {
            border-left-color: #28a745;
            background: #d4edda;
        }

        .status-warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }

        .status-error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }

        .log-container {
            background: #212529;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
            margin: 15px 0;
        }

        .file-list {
            margin-top: 20px;
        }

        .file-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .file-info {
            flex-grow: 1;
        }

        .file-name {
            font-weight: bold;
            color: #00366C;
            margin-bottom: 4px;
        }

        .file-details {
            font-size: 12px;
            color: #666;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-quincenal {
            background: #007bff;
            color: white;
        }

        .badge-mensual {
            background: #28a745;
            color: white;
        }

        .btn {
            background-color: #00366C;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background-color: #004080;
        }

        .back-link {
            margin-top: 20px;
            text-align: center;
        }

        .back-link a {
            color: #00366C;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="title">ESTADO DE RESPALDOS AUTOMÁTICOS</h1>
        
        <?php
        // Información del sistema de respaldos automáticos
        $backup_dir = __DIR__ . '/backups';
        $log_file = __DIR__ . '/respaldo_automatico.log';
        
        // Verificar si es día de respaldo (con tolerancia)
        $hoy = (int)date('j');
        $ultimo_dia = (int)date('t');
        
        // Verificación directa
        $es_dia_exacto = ($hoy == 15 || $hoy == $ultimo_dia);
        
        // Verificación con tolerancia
        $en_tolerancia_quincenal = ($hoy >= 15 && $hoy <= 18);
        $en_tolerancia_mensual = ($hoy >= 1 && $hoy <= 3) || ($hoy >= 28);
        
        $es_dia_respaldo = $es_dia_exacto || $en_tolerancia_quincenal || $en_tolerancia_mensual;
        
        // Verificar si ya se creó respaldo hoy
        $fecha_hoy = date('Y-m-d');
        $respaldo_hoy = glob($backup_dir . '/respaldo_' . $fecha_hoy . '_*.sql');
        $ya_se_creo_hoy = !empty($respaldo_hoy);
        ?>
        
        <div class="info-box">
            <h3>Estado del Sistema de Respaldos</h3>
            
            <div class="status-item <?php echo $es_dia_respaldo ? 'status-success' : 'status-warning'; ?>">
                <strong>Fecha actual:</strong> <?php echo date('d/m/Y (j)'); ?> 
                - Último día del mes: <?php echo $ultimo_dia; ?>
                <br>
                <strong>Estado del día:</strong> 
                <?php if ($es_dia_exacto): ?>
                    ✓ Día EXACTO de respaldo automático
                <?php elseif ($en_tolerancia_quincenal): ?>
                    ⚡ En tolerancia QUINCENAL (días 15-18)
                <?php elseif ($en_tolerancia_mensual): ?>
                    ⚡ En tolerancia MENSUAL (días 1-3 o 28+)
                <?php else: ?>
                    ℹ No es día de respaldo automático
                <?php endif; ?>
                <br>
                <small style="color: #666;">
                    Tolerancia: Quincenales del 15-18, Mensuales días 1-3 o 28+
                </small>
            </div>
            
            <div class="status-item <?php echo $ya_se_creo_hoy ? 'status-success' : ($es_dia_respaldo ? 'status-warning' : ''); ?>">
                <strong>Respaldo de hoy:</strong>
                <?php if ($ya_se_creo_hoy): ?>
                    ✓ Ya se creó respaldo hoy (<?php echo basename($respaldo_hoy[0]); ?>)
                <?php elseif ($es_dia_respaldo): ?>
                    ⏳ Pendiente - Se creará en el próximo acceso al sistema
                <?php else: ?>
                    - No corresponde crear respaldo hoy
                <?php endif; ?>
            </div>
            
            <div class="status-item <?php echo is_dir($backup_dir) && is_writable($backup_dir) ? 'status-success' : 'status-error'; ?>">
                <strong>Directorio de respaldos:</strong> <?php echo htmlspecialchars($backup_dir); ?>
                <br>
                <strong>Estado:</strong>
                <?php if (!is_dir($backup_dir)): ?>
                    ✗ No existe
                <?php elseif (!is_writable($backup_dir)): ?>
                    ✗ Existe pero no es escribible
                <?php else: ?>
                    ✓ Existe y es escribible
                <?php endif; ?>
            </div>
        </div>
        
        <?php
        // Mostrar últimas líneas del log
        if (file_exists($log_file)):
            $log_lines = file($log_file, FILE_IGNORE_NEW_LINES);
            $ultimas_lineas = array_slice($log_lines, -20); // Últimas 20 líneas
        ?>
        <div>
            <h3>Últimas actividades del sistema (últimas 20 líneas):</h3>
            <div class="log-container">
                <?php foreach ($ultimas_lineas as $line): ?>
                    <div><?php echo htmlspecialchars($line); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php
        // Mostrar archivos de respaldo existentes
        $backup_files = glob($backup_dir . '/respaldo_*.sql');
        if (!empty($backup_files)):
            // Ordenar por fecha (más reciente primero)
            usort($backup_files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
        ?>
        <div class="file-list">
            <h3>Respaldos automáticos creados (<?php echo count($backup_files); ?> archivos):</h3>
            <?php foreach (array_slice($backup_files, 0, 10) as $file): ?>
                <?php
                $nombre = basename($file);
                $tipo = (strpos($nombre, '_quincenal_') !== false) ? 'quincenal' : 'mensual';
                ?>
                <div class="file-item">
                    <div class="file-info">
                        <div class="file-name">
                            <?php echo htmlspecialchars($nombre); ?>
                            <span class="badge badge-<?php echo $tipo; ?>"><?php echo strtoupper($tipo); ?></span>
                        </div>
                        <div class="file-details">
                            Tamaño: <?php echo number_format(filesize($file) / 1024, 2); ?> KB | 
                            Fecha: <?php echo date('d/m/Y H:i:s', filemtime($file)); ?>
                        </div>
                    </div>
                    <a href="backups/<?php echo htmlspecialchars(basename($file)); ?>" download class="btn">
                        Descargar
                    </a>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($backup_files) > 10): ?>
                <div style="text-align: center; margin-top: 15px; color: #666; font-size: 12px;">
                    <em>... y <?php echo count($backup_files) - 10; ?> archivos más</em>
                </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="info-box">
            <p><strong>No se encontraron respaldos automáticos.</strong></p>
            <p>Los respaldos se crearán automáticamente los días 15 y último de cada mes cuando los usuarios accedan al sistema.</p>
        </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h3>Información del Sistema de Respaldos Automáticos</h3>
            <ul>
                <li><strong>Frecuencia:</strong> Cada día 15 y último día del mes (con tolerancia)</li>
                <li><strong>Tolerancia Quincenal:</strong> Días 15-18 (si no existe respaldo quincenal del mes)</li>
                <li><strong>Tolerancia Mensual:</strong> Días 1-3 del mes siguiente o días 28+ del mes actual</li>
                <li><strong>Activación:</strong> Cuando cualquier usuario accede al sistema</li>
                <li><strong>Prevención duplicados:</strong> Solo un respaldo quincenal y uno mensual por período</li>
                <li><strong>Limpieza:</strong> Se mantienen los últimos 24 respaldos automáticamente</li>
                <li><strong>Ubicación:</strong> Carpeta /Respaldo/backups/ del servidor</li>
                <li><strong>Formato:</strong> respaldo_YYYY-MM-DD_tipo_HH-MM-SS.sql</li>
                <li><strong>Tipos:</strong> quincenal (día 15±3) y mensual (último día±3)</li>
            </ul>
        </div>
        
        <div class="back-link">
            <a href="../index.php">← Volver al Sistema</a> | 
            <a href="index.php">Ver Respaldos Manuales</a>
        </div>
    </div>
</body>
</html>
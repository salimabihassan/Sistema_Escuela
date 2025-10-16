<?php
// incluir validación de sesión y UTF-8
include __DIR__ . '/../auth.php';

// luego incluir conexión a BD
include __DIR__ . '/../conexiones.php';

// Configuración de respaldo
$db_config = [
    'host' => 'localhost',
    'name' => 'escuela',
    'user' => 'root',
    'pass' => ''
];

$mensaje = '';
$error = '';

// Procesar el respaldo si se solicita
if (isset($_POST['crear_respaldo'])) {
    try {
        $fecha = date("Y-m-d_H-i-s");
        
        // Definir directorio de respaldo (probar múltiples opciones)
        $backup_directories = [
            __DIR__,                                    // Directorio actual
            __DIR__ . '/backups',                      // Subdirectorio backups
            dirname(__DIR__) . '/backups',             // Directorio padre/backups
            sys_get_temp_dir() . '/escuela_backups'    // Directorio temporal del sistema
        ];
        
        $backup_dir = null;
        foreach ($backup_directories as $dir) {
            // Intentar crear el directorio si no existe
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            
            // Verificar si es escribible
            if (is_dir($dir) && is_writable($dir)) {
                $backup_dir = $dir;
                break;
            }
        }
        
        if (!$backup_dir) {
            throw new Exception("No se pudo encontrar un directorio escribible para los respaldos. Directorios intentados: " . implode(', ', $backup_directories));
        }
        
        // Verificar conectividad a la base de datos antes de continuar
        $conexion = conectarse_escuela();
        if (!$conexion) {
            throw new Exception("No se pudo conectar a la base de datos para verificar la conexión");
        }
        
        // Verificar que la base de datos existe
        $result = mysqli_query($conexion, "SHOW DATABASES LIKE '" . $db_config['name'] . "'");
        if (!$result || mysqli_num_rows($result) === 0) {
            throw new Exception("La base de datos '" . $db_config['name'] . "' no existe o no es accesible");
        }
        
        // Nombre del archivo SQL
        $sql_file = $backup_dir . '/' . $db_config['name'] . '_' . $fecha . '.sql';
        
        // Buscar la ruta de mysqldump
        $mysqldump_paths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',   // XAMPP Windows
            'C:/xampp/mysql/bin/mysqldump.exe',       // XAMPP Windows (slash normal)
            'C:\\wamp64\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe', // WAMP64
            'C:\\wamp\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe', // WAMP
            'mysqldump',                               // En PATH del sistema
            '/usr/bin/mysqldump',                      // Linux/Mac
            '/usr/local/bin/mysqldump'                 // Mac Homebrew
        ];
        
        $mysqldump_cmd = null;
        foreach ($mysqldump_paths as $path) {
            if (file_exists($path)) {
                $mysqldump_cmd = $path;
                break;
            }
        }
        
        // Si no se encuentra, intentar con el comando genérico como último recurso
        if (!$mysqldump_cmd) {
            // Verificar si mysqldump está en el PATH
            $test_output = [];
            $test_return = 0;
            exec('mysqldump --version 2>&1', $test_output, $test_return);
            
            if ($test_return === 0) {
                $mysqldump_cmd = 'mysqldump';
            } else {
                throw new Exception("No se pudo encontrar mysqldump. Rutas intentadas: " . implode(', ', $mysqldump_paths) . ". Salida del test: " . implode(' ', $test_output));
            }
        }
        
        // Crear comando mysqldump
        $password_part = !empty($db_config['pass']) ? '--password=' . $db_config['pass'] : '';
        
        // Para Windows, usar cmd /c para mejor compatibilidad con redirección
        if (PHP_OS_FAMILY === 'Windows') {
            $mysqldump_full_cmd = sprintf(
                '%s --host=%s --user=%s %s --opt --single-transaction --routines --triggers %s',
                escapeshellarg($mysqldump_cmd),
                $db_config['host'],
                $db_config['user'],
                $password_part,
                $db_config['name']
            );
            $command = sprintf('cmd /c "%s > %s"', $mysqldump_full_cmd, escapeshellarg($sql_file));
        } else {
            $command = sprintf(
                '%s --host=%s --user=%s %s --opt --single-transaction --routines --triggers %s > %s',
                escapeshellarg($mysqldump_cmd),
                escapeshellarg($db_config['host']),
                escapeshellarg($db_config['user']),
                $password_part,
                escapeshellarg($db_config['name']),
                escapeshellarg($sql_file)
            );
        }
        
        // Ejecutar el comando
        $output = [];
        $return_code = 0;
        exec($command, $output, $return_code);
        
        if ($return_code !== 0) {
            $error_details = "Error al crear el respaldo SQL:\n";
            $error_details .= "Comando ejecutado: " . $command . "\n";
            $error_details .= "Código de retorno: " . $return_code . "\n";
            $error_details .= "Salida del comando: " . implode("\n", $output) . "\n";
            $error_details .= "Directorio usado: " . $backup_dir . "\n";
            $error_details .= "Archivo SQL: " . $sql_file;
            throw new Exception($error_details);
        }
        
        // Verificar que el archivo SQL se creó
        if (!file_exists($sql_file) || filesize($sql_file) == 0) {
            throw new Exception("El archivo de respaldo está vacío o no se pudo crear");
        }
        
        $mensaje = "Respaldo SQL creado exitosamente: " . basename($sql_file);
        
        // Opcional: descargar automáticamente
        if (isset($_POST['descargar_auto'])) {
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . basename($sql_file) . '"');
            header('Content-Length: ' . filesize($sql_file));
            readfile($sql_file);
            unlink($sql_file); // Eliminar después de descargar
            exit;
        }
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        
        // Limpiar archivos temporales en caso de error
        if (isset($sql_file) && file_exists($sql_file)) {
            unlink($sql_file);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo de Base de Datos - Sistema Escuela</title>
    <style>
        /* Reset básico */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
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

        /* Panel principal */
        .backup-panel {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            margin-top: 20px;
        }

        /* Título */
        .title {
            color: #00366C;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0.05em 0.05em 0.03em #000;
        }

        /* Información del sistema */
        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            margin-bottom: 8px;
        }

        .info-item strong {
            color: #00366C;
        }

        /* Formulario */
        .backup-form {
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
        }

        /* Botones */
        .btn {
            background-color: #00366C;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 10px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #004080;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        /* Mensajes */
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        /* Lista de archivos */
        .file-list {
            margin-top: 20px;
            text-align: left;
        }

        .file-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 10px;
            margin-bottom: 10px;
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
        }

        .file-size {
            font-size: 12px;
            color: #666;
        }

        /* Enlaces */
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
        <div class="backup-panel">
            <h1 class="title">RESPALDO DE BASE DE DATOS</h1>
            
            <div class="info-box">
                <div class="info-item">
                    <strong>Base de datos:</strong> <?php echo htmlspecialchars($db_config['name']); ?>
                </div>
                <div class="info-item">
                    <strong>Servidor:</strong> <?php echo htmlspecialchars($db_config['host']); ?>
                </div>
                <div class="info-item">
                    <strong>Fecha actual:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                </div>
                <div class="info-item">
                    <strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION["nombre_apellido"] ?? 'No definido'); ?>
                </div>
                <div class="info-item">
                    <strong>Directorio actual:</strong> <?php echo htmlspecialchars(__DIR__); ?>
                    <br><small>Permisos: <?php echo is_writable(__DIR__) ? '✓ Escribible' : '✗ No escribible'; ?></small>
                </div>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="backup-form">
                <div class="form-group">
                    <p><strong>Crear respaldo completo de la base de datos</strong></p>
                    <p>Se incluirán todas las tablas, datos, procedimientos y triggers.</p>
                    <p style="color: #666; font-size: 12px;">
                        <em>Los respaldos se crean como archivos .sql sin compresión.</em>
                    </p>
                    
                    <?php
                    // Mostrar información de debug sobre directorios
                    $debug_dirs = [
                        'Directorio actual' => __DIR__,
                        'Directorio backups' => __DIR__ . '/backups',
                        'Directorio padre/backups' => dirname(__DIR__) . '/backups',
                        'Directorio temporal' => sys_get_temp_dir() . '/escuela_backups'
                    ];
                    
                    // Información sobre mysqldump
                    $mysqldump_paths = [
                        'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                        'C:/xampp/mysql/bin/mysqldump.exe',
                        'C:\\wamp64\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe',
                        'C:\\wamp\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe',
                        'mysqldump',
                        '/usr/bin/mysqldump',
                        '/usr/local/bin/mysqldump'
                    ];
                    ?>
                    <details style="margin: 10px 0; font-size: 12px;">
                        <summary style="cursor: pointer; color: #666;">Ver información de directorios</summary>
                        <div style="background: #f8f9fa; padding: 10px; margin-top: 5px; border-radius: 3px;">
                            <strong>Directorios:</strong>
                            <?php foreach ($debug_dirs as $name => $path): ?>
                                <div style="margin: 3px 0; margin-left: 10px;">
                                    <strong><?php echo $name; ?>:</strong> <?php echo htmlspecialchars($path); ?>
                                    <?php if (is_dir($path)): ?>
                                        - <?php echo is_writable($path) ? '✓ Escribible' : '✗ No escribible'; ?>
                                    <?php else: ?>
                                        - <em>No existe</em>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr style="margin: 10px 0;">
                            <strong>mysqldump:</strong>
                            <?php foreach ($mysqldump_paths as $path): ?>
                                <div style="margin: 3px 0; margin-left: 10px;">
                                    <?php echo htmlspecialchars($path); ?>
                                    <?php if (file_exists($path)): ?>
                                        - ✓ Existe
                                    <?php else: ?>
                                        - ✗ No existe
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr style="margin: 10px 0;">
                            <strong>Sistema:</strong>
                            <div style="margin: 3px 0; margin-left: 10px;">SO: <?php echo PHP_OS_FAMILY; ?></div>
                            <div style="margin: 3px 0; margin-left: 10px;">PHP: <?php echo PHP_VERSION; ?></div>
                            <div style="margin: 3px 0; margin-left: 10px; color: #666; font-size: 11px;">
                                <em>Los respaldos se crean como archivos .sql sin compresión</em>
                            </div>
                        </div>
                    </details>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="descargar_auto" name="descargar_auto" checked>
                    <label for="descargar_auto">Descargar automáticamente después de crear</label>
                </div>

                <div class="form-group">
                    <button type="submit" name="crear_respaldo" class="btn">
                        Crear Respaldo
                    </button>
                    <button type="button" onclick="window.close()" class="btn btn-secondary">
                        Cerrar
                    </button>
                </div>
            </form>

            <?php
            // Mostrar archivos de respaldo existentes
            $backup_files = [];
            $search_directories = [
                __DIR__,
                __DIR__ . '/backups',
                dirname(__DIR__) . '/backups',
                sys_get_temp_dir() . '/escuela_backups'
            ];
            
            foreach ($search_directories as $dir) {
                if (is_dir($dir)) {
                    // Buscar archivos SQL
                    $sql_files = glob($dir . '/' . $db_config['name'] . '_*.sql');
                    if ($sql_files) {
                        $backup_files = array_merge($backup_files, $sql_files);
                    }
                }
            }
            
            if (!empty($backup_files)):
                // Ordenar por fecha (más reciente primero)
                usort($backup_files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
            ?>
                <div class="file-list">
                    <h3>Respaldos anteriores:</h3>
                    <?php foreach(array_slice($backup_files, 0, 5) as $file): ?>
                        <div class="file-item">
                            <div class="file-info">
                                <div class="file-name"><?php echo basename($file); ?></div>
                                <div class="file-size">
                                    Tamaño: <?php echo number_format(filesize($file) / 1024, 2); ?> KB - 
                                    Fecha: <?php echo date('d/m/Y H:i:s', filemtime($file)); ?>
                                </div>
                            </div>
                            <a href="<?php echo basename($file); ?>" download class="btn" style="font-size: 12px; padding: 5px 10px;">
                                Descargar
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="back-link">
            <a href="../index.php">← Volver al Sistema</a>
        </div>
    </div>

    <script>
        // Confirmar antes de crear respaldo
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('¿Está seguro de que desea crear un respaldo de la base de datos?')) {
                e.preventDefault();
            }
        });

        // Auto-cerrar después de éxito (opcional)
        <?php if (!empty($mensaje) && isset($_POST['descargar_auto'])): ?>
        setTimeout(function() {
            if (confirm('Respaldo completado. ¿Desea cerrar esta ventana?')) {
                window.close();
            }
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
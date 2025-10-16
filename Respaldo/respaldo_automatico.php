<?php
/**
 * Sistema de Respaldo Automático
 * Se ejecuta los días 15 y último de cada mes
 * 
 * Uso: php respaldo_automatico.php
 * O configurar en el Programador de Tareas de Windows
 */

// Configuración
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Configuración de zona horaria
date_default_timezone_set('America/Caracas');

// Incluir conexión a la base de datos
require_once __DIR__ . '/../conexiones.php';

// Configuración de la base de datos
$db_config = [
    'host' => 'localhost',
    'username' => 'sali',
    'password' => '33258426',
    'name' => 'escuela'
];

// Directorio donde se guardarán los respaldos
$backup_dir = __DIR__ . '/backups';

// Crear directorio de respaldos si no existe
if (!is_dir($backup_dir)) {
    if (!mkdir($backup_dir, 0755, true)) {
        die("Error: No se pudo crear el directorio de respaldos: $backup_dir\n");
    }
}

// Verificar si es día de respaldo (15 o último día del mes)
function esDiaDeRespaldo() {
    $hoy = date('j'); // Día del mes sin ceros
    $ultimoDiaDelMes = date('t'); // Último día del mes
    
    return ($hoy == 15 || $hoy == $ultimoDiaDelMes);
}

// Función para escribir log
function escribirLog($mensaje) {
    $log_file = __DIR__ . '/respaldo_automatico.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $mensaje" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    echo $log_entry;
}

// Función para encontrar mysqldump
function encontrarMysqldump() {
    $posibles_rutas = [
        'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        'C:/xampp/mysql/bin/mysqldump.exe',
        'C:\\wamp64\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe',
        'C:\\wamp\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe',
        'mysqldump'
    ];
    
    foreach ($posibles_rutas as $ruta) {
        if (file_exists($ruta)) {
            return $ruta;
        }
    }
    
    // Intentar ejecutar mysqldump para ver si está en el PATH
    $output = [];
    $return_code = 0;
    exec('mysqldump --version 2>&1', $output, $return_code);
    
    if ($return_code === 0) {
        return 'mysqldump';
    }
    
    return null;
}

// Función para crear respaldo
function crearRespaldo($db_config, $backup_dir) {
    $mysqldump_path = encontrarMysqldump();
    
    if (!$mysqldump_path) {
        throw new Exception('No se encontró mysqldump en el sistema');
    }
    
    // Generar nombre del archivo
    $fecha = date('Y-m-d');
    $tipo = (date('j') == 15) ? 'quincenal' : 'mensual';
    $nombre_archivo = "respaldo_{$fecha}_{$tipo}.sql";
    $ruta_completa = $backup_dir . '/' . $nombre_archivo;
    
    // Comando mysqldump
    $comando = sprintf(
        '"%s" --host=%s --user=%s --password=%s --single-transaction --routines --triggers --complete-insert --add-drop-database --databases %s',
        $mysqldump_path,
        escapeshellarg($db_config['host']),
        escapeshellarg($db_config['username']),
        escapeshellarg($db_config['password']),
        escapeshellarg($db_config['name'])
    );
    
    // Ejecutar comando en Windows
    if (PHP_OS_FAMILY === 'Windows') {
        $comando_completo = 'cmd /c "' . $comando . '" > "' . $ruta_completa . '" 2>&1';
    } else {
        $comando_completo = $comando . ' > "' . $ruta_completa . '" 2>&1';
    }
    
    $output = [];
    $return_code = 0;
    exec($comando_completo, $output, $return_code);
    
    if ($return_code !== 0) {
        $error_msg = 'Error al ejecutar mysqldump. Código: ' . $return_code;
        if (!empty($output)) {
            $error_msg .= '. Salida: ' . implode(' ', $output);
        }
        throw new Exception($error_msg);
    }
    
    // Verificar que el archivo se creó y tiene contenido
    if (!file_exists($ruta_completa) || filesize($ruta_completa) == 0) {
        throw new Exception('El archivo de respaldo no se creó correctamente o está vacío');
    }
    
    return [
        'archivo' => $nombre_archivo,
        'ruta' => $ruta_completa,
        'tamaño' => filesize($ruta_completa)
    ];
}

// Función para limpiar respaldos antiguos (mantener solo los últimos 12)
function limpiarRespaldosAntiguos($backup_dir) {
    $archivos = glob($backup_dir . '/respaldo_*.sql');
    
    if (count($archivos) <= 12) {
        return 0; // No hay nada que limpiar
    }
    
    // Ordenar por fecha de modificación (más antiguos primero)
    usort($archivos, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    $eliminados = 0;
    $mantener = 12;
    $total = count($archivos);
    
    for ($i = 0; $i < ($total - $mantener); $i++) {
        if (unlink($archivos[$i])) {
            escribirLog("Respaldo antiguo eliminado: " . basename($archivos[$i]));
            $eliminados++;
        }
    }
    
    return $eliminados;
}

// EJECUCIÓN PRINCIPAL
try {
    escribirLog("=== INICIO RESPALDO AUTOMÁTICO ===");
    escribirLog("Sistema: " . PHP_OS_FAMILY . " - PHP: " . PHP_VERSION);
    escribirLog("Fecha actual: " . date('Y-m-d H:i:s'));
    escribirLog("Día del mes: " . date('j') . " (último día: " . date('t') . ")");
    
    // Verificar si es día de respaldo
    if (!esDiaDeRespaldo()) {
        escribirLog("Hoy no es día de respaldo automático (solo días 15 y último del mes)");
        escribirLog("=== FIN RESPALDO AUTOMÁTICO ===");
        exit(0);
    }
    
    escribirLog("✓ Es día de respaldo automático");
    
    // Verificar conexión a la base de datos
    $conexion = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['name']);
    
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . $conexion->connect_error);
    }
    
    escribirLog("✓ Conexión a la base de datos exitosa");
    $conexion->close();
    
    // Crear respaldo
    escribirLog("Iniciando creación de respaldo...");
    $resultado = crearRespaldo($db_config, $backup_dir);
    
    $tamaño_kb = round($resultado['tamaño'] / 1024, 2);
    escribirLog("✓ Respaldo creado exitosamente:");
    escribirLog("  - Archivo: {$resultado['archivo']}");
    escribirLog("  - Tamaño: {$tamaño_kb} KB");
    escribirLog("  - Ubicación: {$resultado['ruta']}");
    
    // Limpiar respaldos antiguos
    escribirLog("Limpiando respaldos antiguos...");
    $eliminados = limpiarRespaldosAntiguos($backup_dir);
    
    if ($eliminados > 0) {
        escribirLog("✓ Se eliminaron {$eliminados} respaldos antiguos");
    } else {
        escribirLog("✓ No hay respaldos antiguos que eliminar");
    }
    
    escribirLog("=== RESPALDO COMPLETADO EXITOSAMENTE ===");
    
    // Enviar notificación por email (opcional - comentado por defecto)
    /*
    $asunto = "Respaldo automático completado - " . date('Y-m-d');
    $mensaje = "Se ha creado exitosamente el respaldo automático de la base de datos.\n\n";
    $mensaje .= "Archivo: {$resultado['archivo']}\n";
    $mensaje .= "Tamaño: {$tamaño_kb} KB\n";
    $mensaje .= "Fecha: " . date('Y-m-d H:i:s');
    
    // mail('admin@escuela.com', $asunto, $mensaje);
    */
    
} catch (Exception $e) {
    escribirLog("ERROR: " . $e->getMessage());
    escribirLog("=== RESPALDO FALLÓ ===");
    
    // Enviar notificación de error por email (opcional - comentado por defecto)
    /*
    $asunto = "ERROR - Respaldo automático falló - " . date('Y-m-d');
    $mensaje = "El respaldo automático de la base de datos ha fallado.\n\n";
    $mensaje .= "Error: " . $e->getMessage() . "\n";
    $mensaje .= "Fecha: " . date('Y-m-d H:i:s');
    
    // mail('admin@escuela.com', $asunto, $mensaje);
    */
    
    exit(1);
}
?>
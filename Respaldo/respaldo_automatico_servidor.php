<?php
/**
 * Sistema de Respaldo Automático del Servidor
 * Se ejecuta automáticamente cuando usuarios acceden al sistema
 * los días 15 y último de cada mes
 */

// Configuración
date_default_timezone_set('America/Caracas');

// Configuración de la base de datos
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'name' => 'escuela'
];

// Directorio donde se guardarán los respaldos
$backup_dir = __DIR__ . '/backups';

/**
 * Verificar si es día de respaldo automático (con tolerancia)
 * Permite crear respaldos con algunos días de retraso
 */
function esDiaDeRespaldo() {
    $hoy = (int)date('j'); // Día del mes actual
    $ultimoDiaDelMes = (int)date('t'); // Último día del mes
    
    // Verificar si es exactamente día 15 o último día
    if ($hoy == 15 || $hoy == $ultimoDiaDelMes) {
        return true;
    }
    
    // Tolerancia para día 15: permitir hasta el día 18
    if ($hoy >= 15 && $hoy <= 18) {
        // Verificar si no se ha creado respaldo quincenal este mes
        if (!seCreoRespaldoQuincenalEsteMes()) {
            return true;
        }
    }
    
    // Tolerancia para último día: permitir hasta 3 días después del fin de mes
    // (esto cubriría los primeros días del mes siguiente)
    if ($hoy >= 1 && $hoy <= 3) {
        // Verificar si no se creó respaldo mensual el mes pasado
        if (!seCreoRespaldoMensualMesPasado()) {
            return true;
        }
    }
    
    return false;
}

/**
 * Verificar si ya se creó respaldo quincenal este mes
 */
function seCreoRespaldoQuincenalEsteMes() {
    global $backup_dir, $db_config;
    
    $mes_actual = date('Y-m');
    $patron = $backup_dir . '/respaldo_' . $mes_actual . '-*_quincenal_*.sql';
    $archivos = glob($patron);
    
    return !empty($archivos);
}

/**
 * Verificar si ya se creó respaldo mensual el mes pasado
 */
function seCreoRespaldoMensualMesPasado() {
    global $backup_dir, $db_config;
    
    // Calcular el mes pasado
    $mes_pasado = date('Y-m', strtotime('first day of last month'));
    $patron = $backup_dir . '/respaldo_' . $mes_pasado . '-*_mensual_*.sql';
    $archivos = glob($patron);
    
    return !empty($archivos);
}

/**
 * Verificar si ya se creó respaldo hoy
 */
function yaSeCreoRespaldoHoy($backup_dir, $db_name) {
    $fecha_hoy = date('Y-m-d');
    $patron = $backup_dir . '/respaldo_' . $fecha_hoy . '_*.sql';
    $archivos = glob($patron);
    
    return !empty($archivos);
}

/**
 * Escribir en el log de respaldos automáticos
 */
function escribirLogRespaldo($mensaje) {
    $log_file = __DIR__ . '/respaldo_automatico.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $mensaje" . PHP_EOL;
    
    // Mantener solo las últimas 100 líneas del log
    $lineas_existentes = [];
    if (file_exists($log_file)) {
        $lineas_existentes = file($log_file, FILE_IGNORE_NEW_LINES);
        if (count($lineas_existentes) >= 100) {
            $lineas_existentes = array_slice($lineas_existentes, -99);
        }
    }
    
    $lineas_existentes[] = $log_entry;
    file_put_contents($log_file, implode(PHP_EOL, $lineas_existentes));
}

/**
 * Encontrar la ruta de mysqldump
 */
function encontrarMysqldump() {
    $posibles_rutas = [
        'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        'C:/xampp/mysql/bin/mysqldump.exe',
        'C:\\wamp64\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe',
        'C:\\wamp\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe',
        '/usr/bin/mysqldump',
        '/usr/local/bin/mysqldump',
        'mysqldump'
    ];
    
    foreach ($posibles_rutas as $ruta) {
        if (file_exists($ruta)) {
            return $ruta;
        }
    }
    
    // Verificar si está en el PATH
    $output = [];
    $return_code = 0;
    exec('mysqldump --version 2>&1', $output, $return_code);
    
    if ($return_code === 0) {
        return 'mysqldump';
    }
    
    return null;
}

/**
 * Crear respaldo automático
 */
function crearRespaldoAutomatico($db_config, $backup_dir) {
    // Verificar que el directorio de respaldos existe
    if (!is_dir($backup_dir)) {
        if (!mkdir($backup_dir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de respaldos');
        }
    }
    
    // Verificar permisos de escritura
    if (!is_writable($backup_dir)) {
        throw new Exception('No hay permisos de escritura en el directorio de respaldos');
    }
    
    // Encontrar mysqldump
    $mysqldump_path = encontrarMysqldump();
    if (!$mysqldump_path) {
        throw new Exception('No se encontró mysqldump en el sistema');
    }
    
    // Determinar tipo de respaldo
    $hoy = (int)date('j');
    $ultimoDia = (int)date('t');
    
    // Lógica para determinar el tipo con tolerancia
    if ($hoy == 15 || ($hoy >= 15 && $hoy <= 18 && !seCreoRespaldoQuincenalEsteMes())) {
        $tipo = 'quincenal';
    } elseif ($hoy == $ultimoDia || ($hoy >= 1 && $hoy <= 3 && !seCreoRespaldoMensualMesPasado())) {
        $tipo = 'mensual';
    } else {
        $tipo = 'mensual'; // Por defecto
    }
    
    // Generar nombre del archivo
    $fecha = date('Y-m-d');
    $hora = date('H-i-s');
    $nombre_archivo = "respaldo_{$fecha}_{$tipo}_{$hora}.sql";
    $ruta_completa = $backup_dir . '/' . $nombre_archivo;
    
    // Verificar conexión a la base de datos
    $conexion = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['name']);
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . $conexion->connect_error);
    }
    $conexion->close();
    
    // Crear comando mysqldump
    $password_part = !empty($db_config['password']) ? '--password=' . escapeshellarg($db_config['password']) : '';
    
    if (PHP_OS_FAMILY === 'Windows') {
        $mysqldump_cmd = sprintf(
            '"%s" --host=%s --user=%s %s --single-transaction --routines --triggers --complete-insert --add-drop-database --databases %s',
            $mysqldump_path,
            escapeshellarg($db_config['host']),
            escapeshellarg($db_config['username']),
            $password_part,
            escapeshellarg($db_config['name'])
        );
        $comando_completo = sprintf('cmd /c "%s > %s" 2>&1', $mysqldump_cmd, escapeshellarg($ruta_completa));
    } else {
        $comando_completo = sprintf(
            '%s --host=%s --user=%s %s --single-transaction --routines --triggers --complete-insert --add-drop-database --databases %s > %s 2>&1',
            escapeshellarg($mysqldump_path),
            escapeshellarg($db_config['host']),
            escapeshellarg($db_config['username']),
            $password_part,
            escapeshellarg($db_config['name']),
            escapeshellarg($ruta_completa)
        );
    }
    
    // Ejecutar comando
    $output = [];
    $return_code = 0;
    exec($comando_completo, $output, $return_code);
    
    if ($return_code !== 0) {
        throw new Exception('Error al ejecutar mysqldump: ' . implode(' ', $output));
    }
    
    // Verificar que el archivo se creó correctamente
    if (!file_exists($ruta_completa) || filesize($ruta_completa) == 0) {
        throw new Exception('El archivo de respaldo no se creó correctamente o está vacío');
    }
    
    return [
        'archivo' => $nombre_archivo,
        'ruta' => $ruta_completa,
        'tamaño' => filesize($ruta_completa),
        'tipo' => $tipo
    ];
}

/**
 * Limpiar respaldos antiguos (mantener solo los últimos 24)
 */
function limpiarRespaldosAntiguos($backup_dir) {
    $archivos = glob($backup_dir . '/respaldo_*.sql');
    
    if (count($archivos) <= 24) {
        return 0;
    }
    
    // Ordenar por fecha de modificación (más antiguos primero)
    usort($archivos, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    $eliminados = 0;
    $mantener = 24;
    $total = count($archivos);
    
    for ($i = 0; $i < ($total - $mantener); $i++) {
        if (unlink($archivos[$i])) {
            $eliminados++;
        }
    }
    
    return $eliminados;
}

/**
 * Ejecutar respaldo automático si corresponde
 */
function ejecutarRespaldoSiCorresponde() {
    global $db_config, $backup_dir;
    
    try {
        // Solo ejecutar en días específicos (con tolerancia)
        if (!esDiaDeRespaldo()) {
            return null;
        }
        
        // Determinar qué tipo de respaldo se necesita
        $hoy = (int)date('j');
        $necesita_quincenal = ($hoy >= 15 && $hoy <= 18) && !seCreoRespaldoQuincenalEsteMes();
        $necesita_mensual = (($hoy >= 1 && $hoy <= 3) || $hoy >= 28) && !seCreoRespaldoMensualMesPasado();
        
        // Si ya se crearon ambos tipos este período, no hacer nada
        if (!$necesita_quincenal && !$necesita_mensual) {
            return null;
        }
        
        escribirLogRespaldo("=== INICIANDO RESPALDO AUTOMÁTICO ===");
        $motivo = '';
        if ($necesita_quincenal) {
            $motivo = "Respaldo quincenal (día {$hoy}, tolerancia 15-18)";
        } elseif ($necesita_mensual) {
            $motivo = "Respaldo mensual (día {$hoy}, tolerancia fin de mes)";
        }
        escribirLogRespaldo($motivo);
        
        // Crear respaldo
        $resultado = crearRespaldoAutomatico($db_config, $backup_dir);
        
        $tamaño_kb = round($resultado['tamaño'] / 1024, 2);
        escribirLogRespaldo("✓ Respaldo {$resultado['tipo']} creado: {$resultado['archivo']} ({$tamaño_kb} KB)");
        
        // Limpiar respaldos antiguos
        $eliminados = limpiarRespaldosAntiguos($backup_dir);
        if ($eliminados > 0) {
            escribirLogRespaldo("✓ Eliminados {$eliminados} respaldos antiguos");
        }
        
        escribirLogRespaldo("=== RESPALDO COMPLETADO EXITOSAMENTE ===");
        
        return $resultado;
        
    } catch (Exception $e) {
        escribirLogRespaldo("ERROR: " . $e->getMessage());
        escribirLogRespaldo("=== RESPALDO FALLÓ ===");
        return false;
    }
}

// Auto-ejecutar al incluir este archivo
$resultado_respaldo = ejecutarRespaldoSiCorresponde();
?>
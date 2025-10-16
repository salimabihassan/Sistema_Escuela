<?php
/**
 * Script de prueba para el sistema de respaldo automático
 * Simula que es día de respaldo para probar la funcionalidad
 */

// Configuración
date_default_timezone_set('America/Caracas');

// Incluir el archivo de respaldo automático pero modificando la función
include_once 'respaldo_automatico_servidor.php';

// Función de prueba que simula que es día de respaldo
function esDiaDeRespaldoPrueba() {
    return true; // Siempre devuelve true para la prueba
}

// Función de prueba que simula que no se ha creado respaldo hoy
function yaSeCreoRespaldoHoyPrueba($backup_dir, $db_name) {
    return false; // Siempre devuelve false para la prueba
}

echo "=== PRUEBA DEL SISTEMA DE RESPALDO AUTOMÁTICO ===\n";
echo "Fecha actual: " . date('Y-m-d H:i:s') . "\n";
echo "Directorio de respaldos: " . __DIR__ . '/backups' . "\n\n";

try {
    $db_config = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'name' => 'escuela'
    ];
    
    $backup_dir = __DIR__ . '/backups';
    
    // Verificar configuración
    echo "1. Verificando configuración...\n";
    
    // Verificar directorio
    if (!is_dir($backup_dir)) {
        if (!mkdir($backup_dir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de respaldos');
        }
        echo "   ✓ Directorio de respaldos creado\n";
    } else {
        echo "   ✓ Directorio de respaldos existe\n";
    }
    
    if (!is_writable($backup_dir)) {
        throw new Exception('No hay permisos de escritura en el directorio de respaldos');
    }
    echo "   ✓ Directorio es escribible\n";
    
    // Verificar conexión a la base de datos
    echo "2. Verificando conexión a la base de datos...\n";
    $conexion = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['name']);
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . $conexion->connect_error);
    }
    echo "   ✓ Conexión a la base de datos exitosa\n";
    $conexion->close();
    
    // Verificar mysqldump
    echo "3. Verificando mysqldump...\n";
    $mysqldump_path = encontrarMysqldump();
    if (!$mysqldump_path) {
        throw new Exception('No se encontró mysqldump en el sistema');
    }
    echo "   ✓ mysqldump encontrado en: $mysqldump_path\n";
    
    // Crear respaldo de prueba
    echo "4. Creando respaldo de prueba...\n";
    $resultado = crearRespaldoAutomatico($db_config, $backup_dir);
    
    if ($resultado) {
        $tamaño_kb = round($resultado['tamaño'] / 1024, 2);
        echo "   ✓ Respaldo creado exitosamente:\n";
        echo "     - Archivo: {$resultado['archivo']}\n";
        echo "     - Tamaño: {$tamaño_kb} KB\n";
        echo "     - Tipo: {$resultado['tipo']}\n";
        echo "     - Ruta: {$resultado['ruta']}\n";
        
        // Verificar contenido del archivo
        if (file_exists($resultado['ruta']) && filesize($resultado['ruta']) > 0) {
            echo "   ✓ Archivo creado correctamente y contiene datos\n";
            
            // Mostrar primeras líneas del archivo
            $handle = fopen($resultado['ruta'], 'r');
            $primera_linea = fgets($handle);
            fclose($handle);
            echo "     Primera línea del archivo: " . trim($primera_linea) . "\n";
        } else {
            echo "   ✗ El archivo está vacío o no se creó correctamente\n";
        }
    } else {
        echo "   ✗ No se pudo crear el respaldo\n";
    }
    
    // Verificar limpieza de archivos antiguos
    echo "5. Verificando sistema de limpieza...\n";
    $archivos_antes = glob($backup_dir . '/respaldo_*.sql');
    $eliminados = limpiarRespaldosAntiguos($backup_dir);
    $archivos_despues = glob($backup_dir . '/respaldo_*.sql');
    
    echo "   - Archivos antes: " . count($archivos_antes) . "\n";
    echo "   - Archivos eliminados: $eliminados\n";
    echo "   - Archivos después: " . count($archivos_despues) . "\n";
    
    echo "\n=== PRUEBA COMPLETADA EXITOSAMENTE ===\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "=== PRUEBA FALLÓ ===\n";
}
?>
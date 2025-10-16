<?php
/**
 * Prueba del sistema de respaldo con tolerancia de días
 */

// Configuración
date_default_timezone_set('America/Caracas');

// Incluir el sistema de respaldo
include_once 'respaldo_automatico_servidor.php';

echo "=== PRUEBA DE TOLERANCIA DEL SISTEMA DE RESPALDO ===\n";
echo "Fecha actual: " . date('Y-m-d (j)') . "\n";
echo "Último día del mes: " . date('t') . "\n\n";

// Simulaciones de diferentes días
$dias_prueba = [
    ['dia' => 14, 'descripcion' => 'Día 14 (antes del 15)'],
    ['dia' => 15, 'descripcion' => 'Día 15 (quincenal exacto)'],
    ['dia' => 16, 'descripcion' => 'Día 16 (tolerancia quincenal)'],
    ['dia' => 17, 'descripcion' => 'Día 17 (tolerancia quincenal)'],
    ['dia' => 18, 'descripcion' => 'Día 18 (límite tolerancia quincenal)'],
    ['dia' => 19, 'descripcion' => 'Día 19 (fuera de tolerancia)'],
    ['dia' => 30, 'descripcion' => 'Día 30 (cerca del fin de mes)'],
    ['dia' => 31, 'descripcion' => 'Día 31 (último día, si existe)'],
    ['dia' => 1, 'descripcion' => 'Día 1 del mes siguiente (tolerancia mensual)'],
    ['dia' => 2, 'descripcion' => 'Día 2 del mes siguiente (tolerancia mensual)'],
    ['dia' => 3, 'descripcion' => 'Día 3 del mes siguiente (límite tolerancia mensual)'],
    ['dia' => 4, 'descripcion' => 'Día 4 del mes siguiente (fuera de tolerancia)']
];

// Función de prueba que simula diferentes días
function probarDia($dia_simulado, $descripcion) {
    echo "\n--- PRUEBA: $descripcion ---\n";
    
    // Simular el día actual
    $fecha_original = date('Y-m-d');
    $año_mes = date('Y-m');
    
    // Crear fecha simulada
    if ($dia_simulado <= date('t')) {
        $fecha_simulada = $año_mes . '-' . sprintf('%02d', $dia_simulado);
    } else {
        echo "❌ Día $dia_simulado no existe en este mes\n";
        return;
    }
    
    // Simular las funciones de verificación
    echo "Simulando fecha: $fecha_simulada\n";
    
    // Verificar lógica manualmente
    $hoy = $dia_simulado;
    $ultimoDia = (int)date('t');
    
    $es_exacto_15 = ($hoy == 15);
    $es_ultimo_dia = ($hoy == $ultimoDia);
    $en_tolerancia_quincenal = ($hoy >= 15 && $hoy <= 18);
    $en_tolerancia_mensual = ($hoy >= 1 && $hoy <= 3) || ($hoy >= 28);
    
    echo "- ¿Es día 15 exacto?: " . ($es_exacto_15 ? "SÍ" : "NO") . "\n";
    echo "- ¿Es último día exacto?: " . ($es_ultimo_dia ? "SÍ" : "NO") . "\n";
    echo "- ¿En tolerancia quincenal (15-18)?: " . ($en_tolerancia_quincenal ? "SÍ" : "NO") . "\n";
    echo "- ¿En tolerancia mensual?: " . ($en_tolerancia_mensual ? "SÍ" : "NO") . "\n";
    
    // Simular verificación de respaldos existentes (asumiendo que no existen)
    $ya_existe_quincenal = false; // Simular que no existe
    $ya_existe_mensual = false;   // Simular que no existe
    
    $deberia_crear_respaldo = false;
    $tipo_respaldo = '';
    
    if ($es_exacto_15 || ($en_tolerancia_quincenal && !$ya_existe_quincenal)) {
        $deberia_crear_respaldo = true;
        $tipo_respaldo = 'QUINCENAL';
    } elseif ($es_ultimo_dia || ($en_tolerancia_mensual && !$ya_existe_mensual)) {
        $deberia_crear_respaldo = true;
        $tipo_respaldo = 'MENSUAL';
    }
    
    if ($deberia_crear_respaldo) {
        echo "✅ RESULTADO: Sí crearía respaldo $tipo_respaldo\n";
    } else {
        echo "❌ RESULTADO: NO crearía respaldo\n";
    }
}

// Ejecutar pruebas
foreach ($dias_prueba as $prueba) {
    // Solo probar días que existen en el mes actual
    if ($prueba['dia'] <= date('t') || $prueba['dia'] <= 3) {
        probarDia($prueba['dia'], $prueba['descripcion']);
    }
}

echo "\n=== RESUMEN DEL SISTEMA DE TOLERANCIA ===\n";
echo "RESPALDOS QUINCENALES:\n";
echo "- Día 15: Creación exacta\n";
echo "- Días 16-18: Tolerancia (solo si no existe respaldo quincenal del mes)\n";
echo "\nRESPALDOS MENSUALES:\n";
echo "- Último día del mes: Creación exacta\n";
echo "- Días 1-3 del mes siguiente: Tolerancia (solo si no existe respaldo mensual del mes anterior)\n";
echo "- Días 28+ del mes: También válidos para respaldo mensual\n";
echo "\nBENEFICIOS:\n";
echo "✅ No se pierden respaldos por fines de semana\n";
echo "✅ No se pierden respaldos si nadie usa el sistema un día específico\n";
echo "✅ Evita respaldos duplicados\n";
echo "✅ Flexibilidad sin comprometer la regularidad\n";

?>
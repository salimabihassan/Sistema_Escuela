# CONFIGURACIÓN DEL PROGRAMADOR DE TAREAS DE WINDOWS
# Para configurar el respaldo automático cada 15 y último día del mes

## OPCIÓN 1: Configuración Manual en el Programador de Tareas

### Pasos para configurar:
1. Abrir "Programador de tareas" (taskschd.msc)
2. Hacer clic en "Crear tarea básica..."
3. Configurar los siguientes datos:

**Información General:**
- Nombre: Respaldo Automático Base de Datos Escuela
- Descripción: Respaldo automático de la base de datos los días 15 y último de cada mes
- Ejecutar con los privilegios más altos: ✓

**Desencadenador:**
- Tipo: Diariamente
- Iniciar: (fecha actual)
- Hora: 02:00:00 (2:00 AM)
- Repetir cada: 1 día

**Acción:**
- Acción: Iniciar un programa
- Programa: C:\xampp\htdocs\Sistema_Escuela\Respaldo\ejecutar_respaldo.bat
- Argumentos: auto
- Iniciar en: C:\xampp\htdocs\Sistema_Escuela\Respaldo

**Condiciones:**
- Iniciar solo si el equipo está conectado a la corriente AC: ✗
- Iniciar la tarea solo si el equipo está inactivo: ✗
- Detener si el equipo deja de estar inactivo: ✗

**Configuración:**
- Permitir que la tarea se ejecute a petición: ✓
- Si la tarea programada ya se está ejecutando, aplicar la regla: No iniciar una nueva instancia

## OPCIÓN 2: Comando PowerShell para crear la tarea automáticamente

Ejecutar en PowerShell como Administrador:

```powershell
$action = New-ScheduledTaskAction -Execute "C:\xampp\htdocs\Sistema_Escuela\Respaldo\ejecutar_respaldo.bat" -Argument "auto" -WorkingDirectory "C:\xampp\htdocs\Sistema_Escuela\Respaldo"

$trigger = New-ScheduledTaskTrigger -Daily -At "02:00AM"

$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

Register-ScheduledTask -TaskName "Respaldo Automático Escuela" -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Description "Respaldo automático de la base de datos los días 15 y último de cada mes"
```

## OPCIÓN 3: Archivo XML para importar la tarea

También se puede crear un archivo XML con la configuración completa y importarlo al Programador de Tareas.

## VERIFICACIÓN

### Probar el respaldo manualmente:
1. Abrir CMD o PowerShell
2. Navegar a: cd "C:\xampp\htdocs\Sistema_Escuela\Respaldo"
3. Ejecutar: php respaldo_automatico.php

### Ver el log:
- El archivo de log se crea en: Respaldo/respaldo_automatico.log
- Contiene información detallada de cada ejecución

### Verificar archivos de respaldo:
- Los respaldos se guardan en: Respaldo/backups/
- Formato de nombre: respaldo_YYYY-MM-DD_quincenal.sql o respaldo_YYYY-MM-DD_mensual.sql

## NOTAS IMPORTANTES

1. **Días de ejecución**: El script se ejecuta diariamente pero solo crea respaldos los días 15 y último del mes
2. **Limpieza automática**: Mantiene solo los últimos 12 respaldos para ahorrar espacio
3. **Log detallado**: Registra toda la actividad en respaldo_automatico.log
4. **Compatibilidad**: Optimizado para XAMPP en Windows
5. **Seguridad**: Incluye validaciones y manejo de errores
6. **Notificaciones**: Se pueden habilitar notificaciones por email (comentadas por defecto)

## SOLUCIÓN DE PROBLEMAS

### Si el respaldo no se crea:
1. Verificar que XAMPP esté ejecutándose
2. Comprobar las credenciales de la base de datos
3. Revisar el archivo de log para errores específicos
4. Verificar permisos de escritura en la carpeta Respaldo/backups

### Si la tarea programada no se ejecuta:
1. Verificar que el Programador de Tareas esté habilitado
2. Comprobar que el usuario tenga permisos suficientes
3. Revisar el historial de la tarea en el Programador de Tareas
4. Probar la ejecución manual del archivo .bat
# SISTEMA DE RESPALDO AUTOMÁTICO - MANUAL DE FUNCIONAMIENTO

## 📋 RESUMEN DEL SISTEMA

El sistema de respaldo automático de la Base de Datos del Sistema Escuela ahora está **completamente implementado y funcional**. Se ejecuta automáticamente desde el servidor web sin necesidad de configuración en cada PC.

## 🚀 CARACTERÍSTICAS PRINCIPALES

### ✅ **Automatización Completa**
- **Activación**: Se ejecuta automáticamente cuando cualquier usuario accede al sistema
- **Frecuencia**: Solo los días 15 y último de cada mes
- **Límite**: Un respaldo máximo por día (evita duplicados)
- **Ubicación**: Se guarda en la carpeta `/Respaldo/backups/` del servidor

### ✅ **Formato de Archivos**
```
respaldo_YYYY-MM-DD_tipo_HH-MM-SS.sql

Ejemplos:
- respaldo_2025-10-15_quincenal_14-30-25.sql  (Día 15)
- respaldo_2025-10-31_mensual_02-15-10.sql    (Último día)
```

### ✅ **Gestión Inteligente**
- **Limpieza automática**: Mantiene solo los últimos 24 respaldos
- **Log detallado**: Registra toda la actividad en `respaldo_automatico.log`
- **Detección de errores**: Sistema robusto con manejo de excepciones
- **Verificación de integridad**: Valida que los archivos se creen correctamente

## 🔧 ARCHIVOS DEL SISTEMA

### Archivos Principales:
1. **`respaldo_automatico_servidor.php`** - Motor principal del sistema
2. **`estado_respaldos.php`** - Panel de administración (solo administradores)
3. **`auth.php`** - Modificado para incluir el sistema automático
4. **`menu.php`** - Actualizado con enlace a "Respaldos Automáticos"

### Archivos de Apoyo:
- **`prueba_respaldo.php`** - Script de pruebas y verificación
- **`INSTRUCCIONES_RESPALDO_AUTOMATICO.md`** - Documentación completa
- **Archivos heredados del sistema anterior** (para respaldos manuales)

## 📊 PANEL DE ADMINISTRACIÓN

Los administradores pueden acceder al panel de estado desde:
**Menú → Mantenimiento → Respaldos Automáticos**

### Información que muestra:
- ✅ Estado actual del sistema (si es día de respaldo)
- ✅ Si ya se creó respaldo hoy
- ✅ Estado del directorio de respaldos
- ✅ Últimas 20 líneas del log de actividad
- ✅ Lista de respaldos automáticos creados (últimos 10)
- ✅ Tamaños de archivos y fechas de creación
- ✅ Enlaces de descarga directa

## 🔍 MONITOREO Y LOGS

### Log Automático (`respaldo_automatico.log`):
```
[2025-10-31 02:00:15] === INICIANDO RESPALDO AUTOMÁTICO ===
[2025-10-31 02:00:15] Día 31 del mes - Es día de respaldo
[2025-10-31 02:00:18] ✓ Respaldo mensual creado: respaldo_2025-10-31_mensual_02-00-18.sql (58.4 KB)
[2025-10-31 02:00:18] ✓ Eliminados 2 respaldos antiguos
[2025-10-31 02:00:18] === RESPALDO COMPLETADO EXITOSAMENTE ===
```

### Limpieza del Log:
- Se mantienen solo las últimas 100 líneas automáticamente
- No requiere mantenimiento manual

## 🛡️ SEGURIDAD Y ROBUSTEZ

### Validaciones Implementadas:
- ✅ Verificación de permisos de directorio
- ✅ Conexión a base de datos antes de crear respaldo
- ✅ Validación de existencia de mysqldump
- ✅ Verificación de integridad del archivo creado
- ✅ Manejo de errores con logs detallados

### Compatibilidad:
- ✅ XAMPP en Windows (probado y funcionando)
- ✅ Detección automática de rutas de mysqldump
- ✅ Comandos optimizados para Windows/PowerShell
- ✅ Manejo de caracteres especiales y rutas con espacios

## 📅 CRONOGRAMA DE RESPALDOS CON TOLERANCIA

### Respaldos Quincenales:
- **Día exacto**: 15 de cada mes
- **Tolerancia**: Días 16, 17, 18 (solo si no se creó el día 15)
- **Archivo**: `respaldo_YYYY-MM-DD_quincenal_HH-MM-SS.sql`

### Respaldos Mensuales:
- **Día exacto**: Último día del mes (28, 29, 30 o 31)
- **Tolerancia**: Días 1, 2, 3 del mes siguiente (solo si no se creó al final del mes anterior)
- **Tolerancia anticipada**: Días 28+ del mes actual
- **Archivo**: `respaldo_YYYY-MM-DD_mensual_HH-MM-SS.sql`

### Ejemplo de calendario con tolerancia:
```
Enero:    15-18 (quincenal) + 28-31 (mensual) + Feb 1-3 (mensual tardío)
Febrero:  15-18 (quincenal) + 28-29 (mensual) + Mar 1-3 (mensual tardío)
Marzo:    15-18 (quincenal) + 28-31 (mensual) + Abr 1-3 (mensual tardío)
Abril:    15-18 (quincenal) + 28-30 (mensual) + May 1-3 (mensual tardío)
...y así sucesivamente
```

### 🎯 VENTAJAS DE LA TOLERANCIA:
- ✅ **Fines de semana cubiertos**: Si el día 15 cae en sábado, se crea el lunes 17
- ✅ **Días no laborables**: Si nadie usa el sistema un día específico, se recupera al día siguiente
- ✅ **Sin duplicados**: Solo se crea un respaldo quincenal y uno mensual por período
- ✅ **Flexibilidad total**: El sistema se adapta a patrones de uso reales

## 🚨 SOLUCIÓN DE PROBLEMAS

### Si no se crean respaldos:
1. **Verificar fecha**: Solo funciona días 15 y último del mes
2. **Revisar log**: Verificar `respaldo_automatico.log` para errores
3. **Permisos**: Confirmar que `/Respaldo/backups/` sea escribible
4. **Base de datos**: Verificar conexión en `conexiones.php`

### Si hay errores de mysqldump:
1. **Verificar XAMPP**: Asegurar que MySQL esté corriendo
2. **Rutas**: El sistema detecta automáticamente la ruta de mysqldump
3. **Credenciales**: Verificar usuario/contraseña en el script

## ✅ VERIFICACIÓN DEL SISTEMA

### Prueba manual (solo para verificación):
```bash
cd C:\xampp\htdocs\Sistema_Escuela\Respaldo
php -f prueba_respaldo.php
```

### Resultado esperado:
```
=== PRUEBA COMPLETADA EXITOSAMENTE ===
✓ Directorio creado y escribible
✓ Conexión a BD exitosa
✓ mysqldump encontrado
✓ Respaldo creado correctamente
```

## 🎯 BENEFICIOS DEL SISTEMA

1. **Cero configuración**: No requiere configurar nada en los PCs de usuarios
2. **Automático**: Los profesores no necesitan recordar hacer respaldos
3. **Confiable**: Sistema robusto con manejo de errores
4. **Monitoreado**: Log detallado de toda la actividad
5. **Eficiente**: Solo se ejecuta cuando corresponde
6. **Administrable**: Panel de control para administradores

## 📞 SOPORTE

El sistema está diseñado para funcionar sin intervención manual. En caso de problemas:

1. **Revisar el panel de estado**: Menú → Mantenimiento → Respaldos Automáticos
2. **Consultar el log**: Información detallada en `respaldo_automatico.log`
3. **Ejecutar prueba**: Usar `prueba_respaldo.php` para diagnóstico

---

**Estado del Sistema**: ✅ **OPERATIVO Y PROBADO**  
**Última actualización**: 16 de Octubre de 2025  
**Versión**: 1.0 - Completamente funcional
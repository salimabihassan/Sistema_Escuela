# 📚 Sistema de Gestión Escolar "Altas Cumbres del Rosal"

Sistema integral para la administración académica de la **Escuela Básica Particular Nº 2271** - Colegio "Altas Cumbres del Rosal" (RBD: 26392-3).

## 🎯 **Descripción**

Sistema web desarrollado en **PHP 8.2** y **MySQL** para la gestión completa de notas, informes de personalidad y administración académica de estudiantes de educación básica (1º a 8º grado).

## ✨ **Características Principales**

- 🔐 **Sistema de autenticación** con roles y permisos
- 📊 **Gestión de notas** por semestre y asignatura
- 👥 **Administración de usuarios** (estudiantes, profesores, directivos)
- 📋 **Informes académicos** y de personalidad
- 🗂️ **Papelera de reciclaje** para recuperación de datos
- 💾 **Sistema de respaldos automáticos**
- 🎨 **Interfaz moderna** y responsive

## 🏫 **Módulos del Sistema**

### 📝 **1. Gestión de Registros**
- **Estudiantes**: Registro, modificación y consulta de alumnos
- **Profesores**: Administración del personal docente
- **Asignaturas**: Configuración de materias por grado
- **Cursos**: Organización académica por período
- **Directivos**: Gestión de personal administrativo
- **Ámbitos**: Configuración de áreas de evaluación

### 📚 **2. Sistema de Notas**
- **Primer Semestre**: Registro y consulta de calificaciones
- **Segundo Semestre**: Gestión de notas del segundo período
- **Cálculo automático** de promedios y calificaciones finales
- **Informes de notas** con anotaciones personalizadas
- **Bloqueo/Desbloqueo** de períodos de calificación

### 👨‍🎓 **3. Informes de Personalidad**
- **Evaluación conductual** por semestre
- **Informes personalizados** por estudiante
- **Seguimiento del desarrollo** personal y social
- **Reportes impresos** con formato oficial

### 🔍 **4. Sistema de Consultas**
- **Búsqueda por cédula**: Información específica de estudiantes
- **Consultas por grado**: Listados de alumnos por curso
- **Asignaturas por curso**: Ver materias asignadas
- **Profesores por cédula**: Información del personal docente
- **Auditoría del sistema**: Registro de actividades

### 🛠️ **5. Herramientas de Mantenimiento**
- **Gestión de notas**: Eliminar, desbloquear períodos
- **Administración de usuarios**: Cambio de niveles y estados
- **Papelera de reciclaje**: Recuperación de datos eliminados
- **Auditoría completa**: Trazabilidad de operaciones
- **Respaldos automáticos**: Protección de datos

## 👥 **Roles y Permisos**

### 🔑 **Administrador**
- Acceso completo al sistema
- Gestión de usuarios y permisos
- Configuración general
- Respaldos y mantenimiento

### 👨‍🏫 **Profesor**
- Gestión de notas de sus asignaturas
- Consulta de información de estudiantes
- Generación de informes académicos
- Acceso limitado por grado asignado

### 👨‍💼 **Director**
- Supervisión académica
- Informes generales
- Administración de personal
- Consultas avanzadas

## 💻 **Requisitos Técnicos**

### **Servidor**
- **PHP**: 8.2 o superior
- **MySQL**: 5.7 o superior / MariaDB 10.4+
- **Apache**: 2.4 o superior
- **Extensiones PHP**: mysqli, session, gd

### **Cliente**
- **Navegadores**: Chrome, Firefox, Safari, Edge (versiones actuales)
- **JavaScript**: Habilitado
- **Resolución**: Mínima 1024x768

## 🚀 **Instalación**

### **1. Requisitos Previos**
```bash
# XAMPP (recomendado) o stack LAMP/WAMP
# PHP 8.2+, MySQL 5.7+, Apache 2.4+
```

### **2. Base de Datos**
```sql
-- Importar estructura desde bd/escuela.sql
mysql -u root -p escuela < bd/escuela.sql
```

### **3. Configuración**
```php
// Editar conexiones.php con datos de tu servidor
$host = "localhost";
$user = "tu_usuario";
$pass = "tu_contraseña";
$db = "escuela";
```

### **4. Permisos**
```bash
# Dar permisos de escritura a carpetas necesarias
chmod 755 Respaldo/backups/
chmod 644 *.php
```

## 📊 **Estructura de Base de Datos**

### **Tablas Principales**
- `alumno` - Información de estudiantes
- `prof` - Datos del personal docente
- `asignatura` - Materias y configuración académica
- `curso` - Organización por grados y períodos
- `notas{periodo}` - Calificaciones por período académico
- `usuarios` - Autenticación y permisos
- `auditoria` - Registro de actividades del sistema

### **Tablas de Apoyo**
- `ambito` - Áreas de evaluación
- `imp` - Configuración de informes
- `director` - Información directiva

## 🔧 **Funcionalidades Avanzadas**

### **💾 Sistema de Respaldos Automáticos**
- ⏰ **Programación**: Días 15 y último de cada mes
- 🔄 **Tolerancia**: Flexibilidad de 3 días para evitar pérdidas
- 🗃️ **Formato**: Archivos SQL completos
- 🧹 **Limpieza**: Mantiene últimos 24 respaldos automáticamente
- 📊 **Monitoreo**: Panel de estado para administradores

### **🗑️ Papelera de Reciclaje**
- **Estudiantes**: Recuperación de alumnos eliminados
- **Profesores**: Restaurar personal docente
- **Asignaturas**: Recuperar materias eliminadas
- **Ámbitos**: Restaurar áreas de evaluación

### **🔐 Sistema de Auditoría**
- **Trazabilidad completa** de todas las operaciones
- **Registro de cambios** en notas y datos críticos
- **Control de acceso** y sesiones de usuario
- **Reportes de actividad** por usuario y período

## 📱 **Características de la Interfaz**

- **📱 Responsive Design**: Adaptable a dispositivos móviles
- **🎨 Interfaz Moderna**: CSS3 con efectos visuales
- **⚡ Carga Rápida**: Optimizado para rendimiento
- **🖱️ Navegación Intuitiva**: Menús desplegables organizados
- **📋 Formularios Inteligentes**: Validación en tiempo real

## 📈 **Estadísticas del Sistema**

- **🏫 Grados Soportados**: 1º a 8º básico
- **👥 Usuarios Simultáneos**: Multiusuario
- **📊 Tipos de Informes**: 15+ formatos diferentes
- **🔄 Períodos Académicos**: Configurables por año
- **📝 Campos de Evaluación**: 30+ criterios de personalidad

## 🛡️ **Seguridad**

- **🔐 Autenticación robusta** con hash de contraseñas
- **🛡️ Protección SQL injection** con prepared statements
- **🔒 Control de sesiones** con timeout automático
- **📝 Validación de datos** en cliente y servidor
- **🔍 Auditoría completa** de acciones críticas

## 📞 **Soporte y Contacto**

**🏫 Institución**: Escuela Básica Particular Nº 2271  
**📧 Email**: ingsalimabihassan@gmail.com  
**📱 Teléfono**: +56 9 5042 0828  
**🆔 RBD**: 26392-3

## 📄 **Licencia**

Sistema propietario desarrollado específicamente para la Escuela Básica Particular "Altas Cumbres del Rosal".

## 🔄 **Historial de Versiones**

### **v2.0** (Octubre 2025)
- ✅ Migración completa a PHP 8.2
- ✅ Sistema de respaldos automáticos
- ✅ Interfaz modernizada
- ✅ Seguridad mejorada
- ✅ Optimización de rendimiento

### **v1.0** (2017)
- 🚀 Versión inicial del sistema
- 📚 Funcionalidades básicas de gestión académica

---

**🎓 Sistema desarrollado para la excelencia educativa de "Altas Cumbres del Rosal"**

<?php
/**
 * auth.php
 * Centraliza inicio de sesión y validación de acceso.
 *
 * Incluir en la parte superior de los archivos que requieren autenticación:
 *   include __DIR__ . '/auth.php';
 *
 * Requiere que el login establezca $_SESSION['sesion_intranet'] = true;
 * y las variables de sesión: cedula, ci_usu2, nombre_apellido, grado, nivel_intranet
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forzar UTF-8 en la salida HTML
header('Content-Type: text/html; charset=UTF-8');

// Verificar sesión activa (misma bandera usada por el login)
if (empty($_SESSION['sesion_intranet'])) {
    // No autorizado: redirigir al login
    header('Location: index.php');
    exit();
}

// Ejecutar respaldo automático si corresponde (silencioso)
// Solo se ejecuta los días 15 y último del mes, y una vez por día
if (file_exists(__DIR__ . '/Respaldo/respaldo_automatico_servidor.php')) {
    @include_once __DIR__ . '/Respaldo/respaldo_automatico_servidor.php';
}

// Normalizar variables de sesión usadas en la aplicación
$_SESSION['nombre_apellido']  = $_SESSION['nombre_apellido']  ?? '';
$_SESSION['cedula']           = $_SESSION['cedula']           ?? '';
$_SESSION['ci_usu2']          = $_SESSION['ci_usu2']          ?? '';
$_SESSION['grado']            = $_SESSION['grado']            ?? '';
$_SESSION['nivel_intranet']   = $_SESSION['nivel_intranet']   ?? '';

// Variables locales convenientes (opcional)
$nombre_apellido = $_SESSION['nombre_apellido'];
$cedula          = $_SESSION['cedula'];
$ci_usu2         = $_SESSION['ci_usu2'];
$grado_sesion    = $_SESSION['grado'];
$nivel_intranet  = $_SESSION['nivel_intranet'];
?>

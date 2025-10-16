<?php 
// filepath: c:\xampp\htdocs\Sistema_Escuela\cerrar sesion.php
include("conexiones.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

$coneccion = conectarse_escuela();
$cedula = $_SESSION["cedula"] ?? '';

if ($cedula) {
    $consulta_Estado = $coneccion->prepare("UPDATE usuarios SET status = 'Desconectado' WHERE cedula = ?");
    $consulta_Estado->bind_param("s", $cedula);
    $consulta_Estado->execute();
}

// Limpiar todas las variables de sesión
session_unset();
session_destroy();

// Borrar la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirigir al inicio
header("Location: index.php");
exit;
?>

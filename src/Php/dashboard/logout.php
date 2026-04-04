<?php
/**
 * ===================================================================
 * Archivo: logout.php
 * Propósito: Cierra la sesión activa del usuario desde el dashboard 
 *            y lo redirige a la página de login.
 * ===================================================================
 */
session_start();

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir a la página de login
require_once __DIR__ . '/../config/conexion.php';
header('Location: ' . BASE_URL . '/src/Php/login/login.php?logout=1');
exit;
?>
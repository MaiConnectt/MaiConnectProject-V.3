<?php
/**
 * ===================================================================
 * Archivo: auth.php
 * Propósito: Gestionar la autenticación, seguridad de sesiones y 
 *            protección de rutas del dashboard. Comprueba que el 
 *            usuario tenga una sesión válida y carga sus datos.
 * ===================================================================
 */
// Autenticación y gestión de sesión para el dashboard
session_start();

// ===============================================================
// Verificar si la sesión de usuario existe de forma preliminar
// ===============================================================
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    // El usuario no ha iniciado sesión, redirigir a la página de inicio de sesión
    require_once __DIR__ . '/../config/conexion.php';
    header('Location: ' . BASE_URL . '/src/Php/login/login.php');
    exit;
}

// Incluir conexión a la base de datos
require_once __DIR__ . '/../config/conexion.php';

// ===============================================================
// Obtener información actualizada del usuario desde la Base de Datos
// ===============================================================
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_usuario WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_data) {
        $current_user = $user_data;
        // Asegurar que el rol esté disponible (preferir la cadena de rol de sesión si está disponible para visualización de texto)
        $current_user['role'] = $_SESSION['role'] ?? 'user';
    } else {
        // Usuario no encontrado en BD (problema de integridad)
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/src/Php/login/login.php?error=user_not_found');
        exit;
    }
} catch (PDOException $e) {
    // Respaldo si falla la BD
    $current_user = [
        'id_usuario' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'] ?? 'user',
        'nombre' => 'Usuario',
        'apellido' => ''
    ];
}

// ===============================================================
// Lógica de inactividad de sesión (Timeout de 30 minutos)
// ===============================================================
$timeout_duration = 1800; // 30 minutos en segundos

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // La sesión ha expirado
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/src/Php/login/login.php?timeout=1');
    exit;
}

// Actualizar tiempo de última actividad
$_SESSION['last_activity'] = time();
?>
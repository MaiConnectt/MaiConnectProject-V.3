<?php
/**
 * ===================================================================
 * Archivo: logout.php
 * Propósito: Destruye la sesión activa del usuario de forma profunda,
 *            limpia las cookies de sesión y redirige al login.
 * ===================================================================
 */
session_start();

// 1. Limpiar todas las variables de sesión
$_SESSION = array();

// 2. Si se desea destruir la sesión, también se debe borrar la cookie de sesión.
// Nota: ¡Esto destruirá la sesión y no solo los datos de sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruir la sesión.
session_destroy();

// 4. Evitar que el navegador guarde en caché las páginas protegidas
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// 5. Redirigir al inicio de sesión
header('Location: login.php');
exit;

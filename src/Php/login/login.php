<?php
/**
 * ===================================================================
 * Archivo: login.php
 * Propósito: Manejar el proceso de autenticación de usuarios en el 
 *            sistema. Verifica credenciales, roles, estado en el 
 *            sistema y crea las variables de sesión pertinentes.
 * ===================================================================
 */
session_start();
require_once __DIR__ . '/../config/conexion.php';

// Habilitar modo de depuración (establecer en false en producción)
define('DEBUG_MODE', false);

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = null;
$debug_info = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = '⚠️ Email y contraseña son obligatorios';
    } elseif (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = '❌ Error de seguridad (CSRF). Intente de nuevo.';
    } else {
        try {
            // ===============================================================
            // Paso 1: Buscar al usuario en tbl_usuario y unir con tbl_rol
            // ===============================================================
            $stmt = $pdo->prepare("
                SELECT 
                    u.id_usuario,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.contrasena,
                    u.id_rol,
                    r.nombre_rol
                FROM tbl_usuario u
                INNER JOIN tbl_rol r ON r.id_role = u.id_rol
                WHERE u.email = :email
                LIMIT 1
            ");

            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (DEBUG_MODE) {
                $debug_info[] = "Email buscado: $email";
                $debug_info[] = "Usuario encontrado: " . ($user ? 'SÍ' : 'NO');
            }

            if (!$user) {
                $message = '❌ Email o contraseña incorrectos';
            } else {
                if (DEBUG_MODE) {
                    $debug_info[] = "Rol: {$user['nombre_rol']} (ID: {$user['id_rol']})";
                    $debug_info[] = "Hash existe: " . (!empty($user['contrasena']) ? 'SÍ' : 'NO');
                }

                // ===============================================================
                // Paso 2: Verificar la contraseña con el hash de la BD
                // ===============================================================
                $password_valid = password_verify($password, $user['contrasena']);

                if (DEBUG_MODE) {
                    $debug_info[] = "Password verify: " . ($password_valid ? 'CORRECTO' : 'INCORRECTO');
                }

                if (!$password_valid) {
                    $message = '❌ Email o contraseña incorrectos';
                } else {
                    // ===============================================================
                    // Paso 3: Validaciones adicionales para roles específicos (VENDEDOR)
                    // Verificamos si existe en tbl_miembro y su estado (activo)
                    // ===============================================================
                    $member_id = null;
                    $commission_percentage = null;

                    if ($user['nombre_rol'] === 'VENDEDOR') {
                        $stmt_member = $pdo->prepare("
                            SELECT 
                                id_miembro,
                                porcentaje_comision,
                                estado
                            FROM tbl_miembro
                            WHERE id_usuario = :id_usuario
                            LIMIT 1
                        ");

                        $stmt_member->execute(['id_usuario' => $user['id_usuario']]);
                        $member = $stmt_member->fetch();

                        if (DEBUG_MODE) {
                            $debug_info[] = "Vendedor en tbl_miembro: " . ($member ? 'SÍ' : 'NO');
                        }

                        if (!$member) {
                            $message = '❌ Este usuario vendedor no está registrado en tbl_miembro. Contacta al administrador.';
                        } elseif ($member['estado'] === 'eliminado') {
                            $message = '❌ Esta cuenta de vendedor ha sido inhabilitada por completo. Contacta al administrador.';
                        } elseif ($member['estado'] !== 'activo') {
                            $message = '❌ Tu cuenta está inactiva, contacta al administrador.';
                        } else {
                            $member_id = $member['id_miembro'];
                            $commission_percentage = $member['porcentaje_comision'];

                            if (DEBUG_MODE) {
                                $debug_info[] = "ID Miembro: $member_id";
                                $debug_info[] = "Comisión: $commission_percentage%";
                            }
                        }
                    }

                    // ===============================================================
                    // Paso 4: Si todas las validaciones son exitosas, creamos sesión
                    // ===============================================================
                    if ($message === null) {
                        session_regenerate_id(true);

                        // Datos básicos de sesión
                        $_SESSION['user_id'] = $user['id_usuario'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['nombre'] = $user['nombre'];
                        $_SESSION['apellido'] = $user['apellido'];
                        $_SESSION['first_name'] = $user['nombre'];  // Compatibilidad
                        $_SESSION['last_name'] = $user['apellido']; // Compatibilidad
                        $_SESSION['role_id'] = $user['id_rol'];
                        $_SESSION['role'] = $user['nombre_rol'];
                        $_SESSION['role_name'] = $user['nombre_rol'];

                        // Datos de sesión específicos del vendedor
                        if ($user['nombre_rol'] === 'VENDEDOR') {
                            $_SESSION['member_id'] = $member_id;
                            $_SESSION['commission_percentage'] = $commission_percentage;
                        }

                        if (DEBUG_MODE) {
                            error_log("Login exitoso: {$user['email']} ({$user['nombre_rol']})");
                        }

                        // ===============================================================
                        // Paso 5: Redirección según el rol del usuario
                        // ===============================================================
                        if ($user['nombre_rol'] === 'ADMIN') {
                            header('Location: ' . BASE_URL . '/src/Php/dashboard/dash.php');
                            exit;
                        } elseif ($user['nombre_rol'] === 'VENDEDOR') {
                            header('Location: ' . BASE_URL . '/src/Php/seller/seller_dash.php');
                            exit;
                        } else {
                            // Respaldo para roles desconocidos
                            header('Location: ' . BASE_URL . '/src/Php/dashboard/dash.php');
                            exit;
                        }
                    }
                }
            }

        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage() . " | Code: " . $e->getCode());
            $message = '❌ Error técnico en el login. Por favor, reporta el problema.';

            if (DEBUG_MODE) {
                $debug_info[] = "Error DB: " . $e->getMessage();
            }
        }
    }
}

// Prevenir caché para que no se muestren datos antiguos al volver atrás
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Mai Shop</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/styles/landing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/styles/login.css">
    <style>
        .debug-info {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
        }

        .debug-info div {
            margin: 3px 0;
        }
    </style>
</head>

<body>

    <div class="login-container">

        <?php if (!empty($message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>


        <div class="login-header">
            <i class="fas fa-birthday-cake"></i>
            <h2>Bienvenido</h2>
            <p>Ingresa a tu cuenta Mai Shop</p>
        </div>

        <form method="POST" action="login.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@correo.com"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="********"
                        required autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Ingresar <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </button>

            <!-- Link de recuperación de contraseña -->
            <div style="text-align:center; margin-top: 1.5rem;">
                <a href="<?= BASE_URL ?>/src/Php/login/recuperar.php"
                   style="color:var(--gray); font-size:1rem; text-decoration:none;
                          transition: color 0.2s; font-weight:500;"
                   onmouseover="this.style.color='var(--primary-color)'"
                   onmouseout="this.style.color='var(--gray)'">
                    <i class="fas fa-key" style="margin-right:0.4rem; opacity:0.7;"></i>
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </form>

        <a href="<?= BASE_URL ?>/index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al inicio
        </a>
    </div>

</body>

</html>
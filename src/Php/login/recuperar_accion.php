<?php
/**
 * ===================================================================
 * Archivo: recuperar_accion.php
 * Propósito: Backend del formulario de recuperación de contraseña.
 *            1. Verifica que el email exista en tbl_usuario
 *            2. Genera un token JWT firmado (exp: 15 minutos)
 *            3. Envía el correo con PHPMailer via Gmail SMTP
 * ===================================================================
 */

// Cargar autoloader de Composer (firebase/php-jwt + phpmailer)
$vendor_path = realpath(__DIR__ . '/../../../vendor/autoload.php');
if (!$vendor_path) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor (vendor no encontrado).']);
    exit;
}
require_once $vendor_path;

require_once __DIR__ . '/../config/conexion.php';

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

header('Content-Type: application/json');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Ingresa un correo electrónico válido.']);
    exit;
}

try {
    // -------------------------------------------------------
    // PASO 1: Verificar que el email exista en tbl_usuario
    // -------------------------------------------------------
    $stmt = $pdo->prepare("SELECT id_usuario, nombre FROM tbl_usuario WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    // Por seguridad: siempre respondemos éxito para no revelar qué emails están registrados
    if (!$usuario) {
        echo json_encode(['success' => true]);
        exit;
    }

    // -------------------------------------------------------
    // PASO 2: Generar token JWT con expiración de 15 minutos
    // -------------------------------------------------------
    $env_arr = [];
    foreach (file(realpath(__DIR__ . '/../../../.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        $l = trim($l);
        if ($l === '' || $l[0] === '#' || $l[0] === ';') continue;
        $p = strpos($l, '='); if ($p === false) continue;
        $env_arr[trim(substr($l, 0, $p))] = trim(substr($l, $p + 1));
    }
    $jwt_secret = $env_arr['JWT_SECRET'];

    $now = time();
    $payload = [
        'iss'        => 'MaiShop',                 // Emisor
        'iat'        => $now,                       // Emitido en
        'exp'        => $now + (15 * 60),           // Expira en 15 minutos
        'id_usuario' => $usuario['id_usuario'],     // ID del usuario embebido en el token
    ];

    $token = JWT::encode($payload, $jwt_secret, 'HS256');

    // -------------------------------------------------------
    // PASO 3: Construir el enlace absoluto de restablecimiento
    // Detecta protocolo y host automáticamente (funciona en local y producción)
    // -------------------------------------------------------
    $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host       = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $reset_link = $protocol . '://' . $host . BASE_URL . '/src/Php/login/nueva_contrasena.php?token=' . urlencode($token);

    // -------------------------------------------------------
    // PASO 4: Enviar el correo con PHPMailer + Gmail SMTP
    // -------------------------------------------------------
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $env_arr['MAIL_FROM'];
    $mail->Password   = $env_arr['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Remitente y destinatario
    $mail->setFrom($env_arr['MAIL_FROM'], $env_arr['MAIL_FROM_NAME']);
    $mail->addAddress($email, $usuario['nombre']);
    $mail->Subject = '🔐 Restablecer tu contraseña - Mai Shop';

    // Contenido HTML del correo
    $nombre = htmlspecialchars($usuario['nombre']);
    $mail->isHTML(true);
    $mail->Body = "
    <!DOCTYPE html>
    <html lang='es'>
    <head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#f9f5f0;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 20px;'>
            <tr><td align='center'>
                <table width='540' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.08);'>
                    <!-- Header -->
                    <tr>
                        <td style='background:linear-gradient(135deg,#c97c89,#b76e79);padding:36px 40px;text-align:center;'>
                            <h1 style='color:#fff;margin:0;font-size:26px;letter-spacing:-0.5px;'>🔐 Mai Shop</h1>
                            <p style='color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px;'>Restablecimiento de contraseña</p>
                        </td>
                    </tr>
                    <!-- Cuerpo -->
                    <tr>
                        <td style='padding:40px;'>
                            <p style='color:#444;font-size:16px;margin-top:0;'>Hola, <strong>{$nombre}</strong>:</p>
                            <p style='color:#666;font-size:15px;line-height:1.6;'>
                                Recibimos una solicitud para restablecer la contraseña de tu cuenta.
                                Haz clic en el botón de abajo para crear una nueva contraseña.
                            </p>
                            <div style='text-align:center;margin:36px 0;'>
                                <a href='{$reset_link}'
                                   style='display:inline-block;background:linear-gradient(135deg,#c97c89,#b76e79);
                                          color:#ffffff;text-decoration:none;font-size:16px;font-weight:600;
                                          padding:16px 40px;border-radius:50px;
                                          box-shadow:0 6px 20px rgba(183,110,121,0.4);
                                          letter-spacing:0.3px;'>
                                    Restablecer contraseña
                                </a>
                            </div>
                            <p style='color:#999;font-size:13px;text-align:center;'>
                                ⏱ Este enlace expira en <strong>15 minutos</strong>.
                            </p>
                            <hr style='border:none;border-top:1px solid #f0f0f0;margin:28px 0;'>
                            <p style='color:#aaa;font-size:12px;'>
                                Si no solicitaste restablecer tu contraseña, ignora este correo.
                                Tu contraseña no cambiará a menos que hagas clic en el enlace.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style='background:#faf7f5;padding:20px 40px;text-align:center;'>
                            <p style='color:#bbb;font-size:12px;margin:0;'>© 2025 Mai Shop · MaiConnect</p>
                        </td>
                    </tr>
                </table>
            </td></tr>
        </table>
    </body>
    </html>";

    // Versión texto plano (fallback)
    $mail->AltBody = "Hola {$nombre},\n\nRestablece tu contraseña aquí (válido 15 min):\n{$reset_link}\n\nSi no lo solicitaste, ignora este correo.\n\n— Mai Shop";

    $mail->send();

    echo json_encode(['success' => true]);

} catch (MailException $e) {
    error_log('MailError recuperar_accion: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo. Verifica la configuración SMTP.']);
} catch (Exception $e) {
    error_log('Error recuperar_accion: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno. Intenta de nuevo más tarde.']);
}

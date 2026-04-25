<?php
/**
 * ===================================================================
 * Archivo: email_helper.php
 * Propósito: Centralizar el envío de correos electrónicos en el sistema.
 * ===================================================================
 */

$vendor_path = realpath(__DIR__ . '/../../../vendor/autoload.php');
if ($vendor_path) {
    require_once $vendor_path;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Carga las variables de entorno desde el archivo .env
 */
function cargarEnvEmail() {
    $env_arr = [];
    $env_path = realpath(__DIR__ . '/../../../.env');
    if ($env_path && file_exists($env_path)) {
        foreach (file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
            $l = trim($l);
            if ($l === '' || $l[0] === '#' || $l[0] === ';') continue;
            $p = strpos($l, '='); 
            if ($p === false) continue;
            $env_arr[trim(substr($l, 0, $p))] = trim(substr($l, $p + 1));
        }
    }
    return $env_arr;
}

/**
 * Envía un correo al administrador notificando la creación de un nuevo pedido.
 * 
 * @param array $datos_pedido Arreglo con los datos básicos del pedido.
 * @param int $id_pedido El ID generado del pedido.
 * @return bool True si se envió correctamente, False en caso contrario.
 */
function enviarCorreoNuevoPedidoAdmin(array $datos_pedido, int $id_pedido): bool {
    try {
        $env_arr = cargarEnvEmail();
        
        $admin_email = $env_arr['ADMIN_EMAIL'] ?? '';
        if (empty($admin_email)) {
            error_log("EmailHelper Error: ADMIN_EMAIL no está configurado en .env");
            return false;
        }

        $mail = new PHPMailer(true);

        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $env_arr['MAIL_FROM'] ?? '';
        $mail->Password   = $env_arr['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Configuración para forzar notificación en celulares (Alta Prioridad)
        $mail->Priority = 1;
        $mail->addCustomHeader('Importance', 'High');

        // Remitente y destinatario
        $mail->setFrom($env_arr['MAIL_FROM'] ?? '', $env_arr['MAIL_FROM_NAME'] ?? 'Mai Shop');
        $mail->addAddress($admin_email, 'Administrador');
        
        $mail->Subject = "Nuevo pedido registrado " . str_pad($id_pedido, 2, '0', STR_PAD_LEFT);

        // Contenido del correo en HTML
        $telefono = htmlspecialchars($datos_pedido['telefono']);
        $direccion = htmlspecialchars($datos_pedido['direccion']);
        $fecha = htmlspecialchars($datos_pedido['fecha']);
        $vendedor = htmlspecialchars($datos_pedido['vendedor'] ?? 'Desconocido');
        
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
                                <h1 style='color:#fff;margin:0;font-size:26px;letter-spacing:-0.5px;'>🛍️ Nuevo pedido registrado</h1>
                                <p style='color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px;'>Mai Shop Notificaciones</p>
                            </td>
                        </tr>
                        <!-- Cuerpo -->
                        <tr>
                            <td style='padding:40px;'>
                                <p style='color:#444;font-size:16px;margin-top:0;'>Hola <strong>Administrador</strong>,</p>
                                <p style='color:#666;font-size:15px;line-height:1.6;'>
                                    Se acaba de registrar un nuevo pedido en el sistema a través del panel de ventas.
                                </p>
                                <div style='background:#f9f5f0;border-left:4px solid #c97c89;padding:15px;margin:20px 0;'>
                                    <p style='margin:0 0 10px 0;font-size:14px;'><strong>ID del Pedido:</strong> #" . str_pad($id_pedido, 4, '0', STR_PAD_LEFT) . "</p>
                                    <p style='margin:0 0 10px 0;font-size:14px;'><strong>Vendedor:</strong> {$vendedor}</p>
                                    <p style='margin:0 0 10px 0;font-size:14px;'><strong>Teléfono de Contacto:</strong> {$telefono}</p>
                                    <p style='margin:0 0 10px 0;font-size:14px;'><strong>Dirección de Entrega:</strong> {$direccion}</p>
                                    <p style='margin:0;font-size:14px;'><strong>Fecha de Entrega Estimada:</strong> {$fecha}</p>
                                </div>
                                <p style='color:#666;font-size:15px;line-height:1.6;'>
                                    Por favor, revisa el panel de administración para ver más detalles y procesar el pedido.
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

        // Versión en texto plano
        $mail->AltBody = "Hola Administrador,\n\nEl vendedor {$vendedor} ha registrado un nuevo pedido en el sistema.\n\nID: #{$id_pedido}\nTeléfono: {$telefono}\nDirección: {$direccion}\nFecha Estimada: {$fecha}\n\nPor favor, revisa el panel de administración para más detalles.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Error en enviarCorreoNuevoPedidoAdmin: ' . $e->getMessage());
        return false;
    }
}

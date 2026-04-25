<?php
/**
 * ===================================================================
 * Archivo: nueva_contrasena_accion.php
 * Propósito: Backend que valida el JWT y actualiza la contraseña
 *            en tbl_usuario si el token es válido y no expiró.
 * ===================================================================
 */

$vendor_path = realpath(__DIR__ . '/../../../vendor/autoload.php');
require_once $vendor_path;
require_once __DIR__ . '/../config/conexion.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$token    = $_POST['token']    ?? '';
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

// Validaciones básicas
if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token inválido.']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.']);
    exit;
}
if ($password !== $confirm) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
    exit;
}

try {
    // -------------------------------------------------------
    // PASO 1: Decodificar y validar el JWT
    // (Si está expirado o es inválido, lanzará una excepción)
    // -------------------------------------------------------
    $env_arr = [];
    foreach (file(realpath(__DIR__ . '/../../../.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        $l = trim($l);
        if ($l === '' || $l[0] === '#' || $l[0] === ';') continue;
        $p = strpos($l, '='); if ($p === false) continue;
        $env_arr[trim(substr($l, 0, $p))] = trim(substr($l, $p + 1));
    }
    $decoded = JWT::decode($token, new Key($env_arr['JWT_SECRET'], 'HS256'));

    $id_usuario = $decoded->id_usuario ?? null;
    if (!$id_usuario) {
        throw new Exception('Token malformado: sin id_usuario.');
    }

    // -------------------------------------------------------
    // PASO 2: Actualizar la contraseña en tbl_usuario
    //         Se encripta con BCrypt antes de guardar
    // -------------------------------------------------------
    $nuevo_hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("UPDATE tbl_usuario SET contrasena = :contrasena WHERE id_usuario = :id_usuario");
    $stmt->execute([
        'contrasena' => $nuevo_hash,
        'id_usuario' => $id_usuario,
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No se encontró el usuario asociado al token.');
    }

    echo json_encode(['success' => true, 'message' => '¡Contraseña actualizada correctamente!']);

} catch (ExpiredException $e) {
    echo json_encode(['success' => false, 'message' => 'El enlace ha expirado. Solicita uno nuevo.']);
} catch (Exception $e) {
    error_log('Error nueva_contrasena_accion: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'El enlace no es válido o ya fue utilizado.']);
}

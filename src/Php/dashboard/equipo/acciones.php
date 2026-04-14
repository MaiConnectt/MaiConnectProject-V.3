<?php
/**
 * ===================================================================
 * Archivo: acciones.php (Equipo)
 * Propósito: (Endpoint API) Controlador para las operaciones CRUD 
 *            (Create, Update, Delete lógico, Restore) sobre los 
 *            vendedores. Recibe peticiones POST (AJAX) y delega la 
 *            operación a funciones de base de datos PostgreSQL.
 * ===================================================================
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $nombre = limpiar_cadena($_POST['nombre'] ?? '');
            $apellido = limpiar_cadena($_POST['apellido'] ?? '');
            $tipo_documento = limpiar_cadena($_POST['tipo_documento'] ?? '');
            $numero_documento = limpiar_cadena($_POST['numero_documento'] ?? '');
            $email = limpiar_cadena($_POST['email'] ?? '');
            $password = limpiar_cadena($_POST['password'] ?? '');

            $estado = $_POST['status'] ?? ($_POST['estado'] ?? 'activo');
            $telefono = limpiar_cadena($_POST['telefono'] ?? '');
            $universidad = limpiar_cadena($_POST['universidad'] ?? '');

            if (empty($nombre) || empty($email) || empty($password) || empty($telefono) || empty($tipo_documento) || empty($numero_documento)) {
                throw new Exception("Nombre, Documento, Email, Teléfono y Contraseña son obligatorios");
            }

            if (!validar_telefono($telefono)) {
                throw new Exception("El teléfono debe tener exactamente 10 dígitos numéricos");
            }

            // Llamar a función PostgreSQL para crear usuario + miembro en una transacción
            $stmt = $pdo->prepare("SELECT fun_crear_vendedor(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nombre,
                $apellido,
                $tipo_documento,
                $numero_documento,
                $email,
                password_hash($password, PASSWORD_BCRYPT),
                $telefono,
                $universidad,
                $estado
            ]);

            // la función retorna un string JSON, lo parseamos para manejar el éxito/error
            $resultado_json = $stmt->fetchColumn();
            $resultado = json_decode($resultado_json, true);

            if (!$resultado || !$resultado['success']) {
                $msg = $resultado['message'] ?? 'Error desconocido en la base de datos';
                if (
                    strpos($msg, 'unique_documento') !== false ||
                    strpos($msg, '23505') !== false ||
                    strpos($msg, 'numero_documento') !== false ||
                    strpos($msg, 'unicidad') !== false ||
                    strpos($msg, 'unique') !== false
                ) {
                    $msg = 'Ya existe un vendedor con ese número de documento. Verifica el número e intenta de nuevo.';
                } elseif (strpos($msg, 'id_usuario_key') !== false || strpos($msg, 'email') !== false) {
                    $msg = 'Ya existe una cuenta registrada con ese correo electrónico.';
                }
                throw new Exception($msg);
            }

            echo json_encode(['success' => true, 'message' => 'Vendedor creado exitosamente']);
            break;

        case 'edit':
            $id_miembro = intval($_POST['id_miembro'] ?? 0);
            $nombre = limpiar_cadena($_POST['nombre'] ?? '');
            $apellido = limpiar_cadena($_POST['apellido'] ?? '');
            $tipo_documento = limpiar_cadena($_POST['tipo_documento'] ?? '');
            $numero_documento = limpiar_cadena($_POST['numero_documento'] ?? '');
            $email = limpiar_cadena($_POST['email'] ?? '');

            $estado = $_POST['estado'] ?? 'activo';
            $telefono = limpiar_cadena($_POST['telefono'] ?? '');
            $universidad = limpiar_cadena($_POST['universidad'] ?? '');

            if (!$id_miembro || empty($nombre) || empty($email) || empty($telefono) || empty($tipo_documento) || empty($numero_documento)) {
                throw new Exception("Datos incompletos (Nombre, Documento, Email y Teléfono son obligatorios)");
            }

            if (!validar_telefono($telefono)) {
                throw new Exception("El teléfono debe tener exactamente 10 dígitos numéricos");
            }

            // Llamar a función PostgreSQL para editar usuario + miembro en una transacción
            $stmt = $pdo->prepare("SELECT fun_editar_vendedor(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_miembro,
                $nombre,
                $apellido,
                $tipo_documento,
                $numero_documento,
                $email,
                $telefono,
                $universidad,
                $estado
            ]);

            $resultado_json = $stmt->fetchColumn();
            $resultado = json_decode($resultado_json, true);

            if (!$resultado || !$resultado['success']) {
                $msg = $resultado['message'] ?? 'Error desconocido al actualizar en base de datos';
                if (
                    strpos($msg, 'unique_documento') !== false ||
                    strpos($msg, '23505') !== false ||
                    strpos($msg, 'numero_documento') !== false ||
                    strpos($msg, 'unicidad') !== false ||
                    strpos($msg, 'unique') !== false
                ) {
                    $msg = 'Ya existe un vendedor con ese número de documento. Verifica el número e intenta de nuevo.';
                } elseif (strpos($msg, 'id_usuario_key') !== false || strpos($msg, 'email') !== false) {
                    $msg = 'Ya existe una cuenta registrada con ese correo electrónico.';
                }
                throw new Exception($msg);
            }

            echo json_encode(['success' => true, 'message' => 'Vendedor actualizado exitosamente']);
            break;

        case 'delete':
            $id_miembro = intval($_POST['id_miembro'] ?? 0);
            if (!$id_miembro)
                throw new Exception("ID inválido");

            // Ejecuta función en base de datos para borrar lógicamente
            $stmt = $pdo->prepare("SELECT fun_desactivar_vendedor(?)");
            $stmt->execute([$id_miembro]);

            $resultado_json = $stmt->fetchColumn();
            $resultado = json_decode($resultado_json, true);

            if (!$resultado || !$resultado['success']) {
                $msg = $resultado['message'] ?? 'Error desconocido al eliminar el vendedor';
                throw new Exception($msg);
            }

            echo json_encode(['success' => true, 'message' => $resultado['message']]);
            break;

        case 'restore':
            $id_miembro = intval($_POST['id_miembro'] ?? 0);
            if (!$id_miembro)
                throw new Exception("ID inválido");

            // Ejecuta función en base de datos para restaurar lógicamente
            $stmt = $pdo->prepare("SELECT fun_restaurar_vendedor(?)");
            $stmt->execute([$id_miembro]);

            $resultado_json = $stmt->fetchColumn();
            $resultado = json_decode($resultado_json, true);

            if (!$resultado || !$resultado['success']) {
                $msg = $resultado['message'] ?? 'Error desconocido al restaurar el vendedor';
                throw new Exception($msg);
            }

            echo json_encode(['success' => true, 'message' => $resultado['message']]);
            break;

        default:
            throw new Exception("Acción no válida");
    }
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
/**
 * ===================================================================
 * Archivo: acciones.php (Equipo)
 * Propósito: (Endpoint API) Controlador para las operaciones CRUD 
 *            (Create, Update, Delete lógico, Restore) sobre los 
 *            vendedores. Recibe peticiones POST (AJAX) y delega la 
 *            operación a funciones de base de datos PostgreSQL.
 * 
 * Flujo general:
 *   1. nuevo.php / editar.php envían datos por AJAX (fetch) a este archivo
 *   2. Este archivo valida los datos recibidos
 *   3. Llama a una función de PostgreSQL (fun_crear_vendedor, fun_editar_vendedor, etc.)
 *   4. Devuelve un JSON con { success: true/false, message: "..." }
 * ===================================================================
 */

/* Verifica que el usuario tenga sesión activa (protección de seguridad) */
require_once __DIR__ . '/../auth.php';

/* Conecta a la base de datos PostgreSQL y define BASE_URL */
require_once __DIR__ . '/../../config/conexion.php';

/* Carga funciones auxiliares: limpiar_cadena(), validar_telefono(), etc. */
require_once __DIR__ . '/../../config/helpers.php';

/* Indica al navegador que la respuesta será un JSON (no HTML) */
header('Content-Type: application/json');

/* 
 * Seguridad: Solo acepta peticiones POST. 
 * Si alguien intenta acceder por GET (URL directa), se rechaza.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

/* Lee qué acción quiere ejecutar el formulario (create, edit, delete, restore) */
$action = $_POST['action'] ?? '';

try {
    /* 
     * Switch: Ejecuta la operación correspondiente según la acción recibida.
     * Cada caso valida los datos, llama a una función SQL y devuelve resultado.
     */
    switch ($action) {

        /* ============================================================
         * CASO 1: CREAR NUEVO VENDEDOR
         * Se ejecuta cuando el admin envía el formulario de nuevo.php
         * ============================================================ */
        case 'create':

            /* --- Paso 1: Recoger y limpiar los datos del formulario --- */
            $nombre           = limpiar_cadena($_POST['nombre'] ?? '');           // Nombre del vendedor
            $apellido         = limpiar_cadena($_POST['apellido'] ?? '');         // Apellido del vendedor
            $tipo_documento   = limpiar_cadena($_POST['tipo_documento'] ?? '');   // CC, TI o CE
            $numero_documento = limpiar_cadena($_POST['numero_documento'] ?? ''); // Número de identificación
            $email            = limpiar_cadena($_POST['email'] ?? '');           // Correo (será su usuario de login)
            $password         = limpiar_cadena($_POST['password'] ?? '');        // Contraseña en texto plano (se encriptará)

            $estado      = $_POST['status'] ?? ($_POST['estado'] ?? 'activo');   // Estado inicial (activo/inactivo)
            $telefono    = limpiar_cadena($_POST['telefono'] ?? '');             // Teléfono / WhatsApp
            $universidad = limpiar_cadena($_POST['universidad'] ?? '');          // Universidad (opcional)

            /* --- Paso 2: Validar que los campos obligatorios no estén vacíos --- */
            if (empty($nombre) || empty($email) || empty($password) || empty($telefono) || empty($tipo_documento) || empty($numero_documento)) {
                throw new Exception("Nombre, Documento, Email, Teléfono y Contraseña son obligatorios");
            }

            /* --- Paso 3: Validar formato del teléfono (exactamente 10 dígitos) --- */
            if (!validar_telefono($telefono)) {
                throw new Exception("El teléfono debe tener exactamente 10 dígitos numéricos");
            }

            /* --- Paso 4: Ejecutar función PostgreSQL fun_crear_vendedor() ---
             * Esta función SQL hace todo en UNA transacción atómica:
             *   1. Inserta el usuario en tbl_usuario (con rol VENDEDOR)
             *   2. Inserta el perfil en tbl_miembro (comisión, teléfono, universidad)
             * Si algo falla, se revierte todo automáticamente.
             * 
             * password_hash(): Encripta la contraseña con BCrypt antes de enviarla a la BD
             *                  (nunca se guarda la contraseña en texto plano)
             */
            $stmt = $pdo->prepare("SELECT fun_crear_vendedor(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nombre,
                $apellido,
                $tipo_documento,
                $numero_documento,
                $email,
                password_hash($password, PASSWORD_BCRYPT),  // Contraseña encriptada
                $telefono,
                $universidad,
                $estado
            ]);

            /* --- Paso 5: Leer la respuesta de la función SQL ---
             * La función retorna un JSON como: {"success": true, "message": "..."} 
             */
            $resultado_json = $stmt->fetchColumn();
            $resultado = json_decode($resultado_json, true);

            /* --- Paso 6: Manejar errores específicos de la BD ---
             * Si la BD detecta un duplicado (documento o email que ya existe),
             * traducimos el error técnico a un mensaje amigable para el usuario.
             */
            if (!$resultado || !$resultado['success']) {
                $msg = $resultado['message'] ?? 'Error desconocido en la base de datos';

                // Detectar error de documento duplicado
                if (
                    strpos($msg, 'unique_documento') !== false ||
                    strpos($msg, '23505') !== false ||
                    strpos($msg, 'numero_documento') !== false ||
                    strpos($msg, 'unicidad') !== false ||
                    strpos($msg, 'unique') !== false
                ) {
                    $msg = 'Ya existe un vendedor con ese número de documento. Verifica el número e intenta de nuevo.';
                }
                // Detectar error de email duplicado
                elseif (strpos($msg, 'id_usuario_key') !== false || strpos($msg, 'email') !== false) {
                    $msg = 'Ya existe una cuenta registrada con ese correo electrónico.';
                }
                throw new Exception($msg);
            }

            /* Devolver respuesta exitosa al frontend (nuevo.php mostrará un modal de éxito) */
            echo json_encode(['success' => true, 'message' => 'Vendedor creado exitosamente']);
            break;

        /* ============================================================
         * CASO 2: EDITAR VENDEDOR EXISTENTE
         * Se ejecuta cuando el admin envía el formulario de editar.php
         * ============================================================ */
        case 'edit':

            /* --- Recoger y limpiar los datos del formulario --- */
            $id_miembro       = intval($_POST['id_miembro'] ?? 0);               // ID del vendedor a editar
            $nombre           = limpiar_cadena($_POST['nombre'] ?? '');
            $apellido         = limpiar_cadena($_POST['apellido'] ?? '');
            $tipo_documento   = limpiar_cadena($_POST['tipo_documento'] ?? '');
            $numero_documento = limpiar_cadena($_POST['numero_documento'] ?? '');
            $email            = limpiar_cadena($_POST['email'] ?? '');

            $estado      = $_POST['estado'] ?? 'activo';
            $telefono    = limpiar_cadena($_POST['telefono'] ?? '');
            $universidad = limpiar_cadena($_POST['universidad'] ?? '');

            /* --- Validar campos obligatorios --- */
            if (!$id_miembro || empty($nombre) || empty($email) || empty($telefono) || empty($tipo_documento) || empty($numero_documento)) {
                throw new Exception("Datos incompletos (Nombre, Documento, Email y Teléfono son obligatorios)");
            }

            /* --- Validar formato del teléfono --- */
            if (!validar_telefono($telefono)) {
                throw new Exception("El teléfono debe tener exactamente 10 dígitos numéricos");
            }

            /* --- Ejecutar función PostgreSQL fun_editar_vendedor() ---
             * Actualiza los datos del usuario en tbl_usuario y tbl_miembro
             * en una sola transacción atómica.
             */
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

            /* --- Leer y procesar respuesta de la función SQL --- */
            $resultado_json = $stmt->fetchColumn();
            $resultado = json_decode($resultado_json, true);

            /* --- Manejar errores de duplicados --- */
            if (!$resultado || !$resultado['success']) {
                $msg = $resultado['message'] ?? 'Error desconocido al actualizar en base de datos';

                // Detectar error de documento duplicado
                if (
                    strpos($msg, 'unique_documento') !== false ||
                    strpos($msg, '23505') !== false ||
                    strpos($msg, 'numero_documento') !== false ||
                    strpos($msg, 'unicidad') !== false ||
                    strpos($msg, 'unique') !== false
                ) {
                    $msg = 'Ya existe un vendedor con ese número de documento. Verifica el número e intenta de nuevo.';
                }
                // Detectar error de email duplicado
                elseif (strpos($msg, 'id_usuario_key') !== false || strpos($msg, 'email') !== false) {
                    $msg = 'Ya existe una cuenta registrada con ese correo electrónico.';
                }
                throw new Exception($msg);
            }

            /* Devolver respuesta exitosa al frontend */
            echo json_encode(['success' => true, 'message' => 'Vendedor actualizado exitosamente']);
            break;

        /* ============================================================
         * CASO 3: ELIMINAR VENDEDOR (Borrado Lógico)
         * No borra físicamente al vendedor de la BD, solo cambia 
         * su estado a "eliminado" para que no pueda acceder al sistema
         * pero se conservan sus datos históricos (pedidos, comisiones).
         * ============================================================ */
        case 'delete':
            $id_miembro = intval($_POST['id_miembro'] ?? 0);
            if (!$id_miembro)
                throw new Exception("ID inválido");

            /* Ejecuta fun_desactivar_vendedor() que cambia estado a 'eliminado' */
            $stmt = $pdo->prepare("SELECT fun_desactivar_vendedor(?)");
            $stmt->execute([$id_miembro]);

            /* Leer respuesta de la función */
            $resultado_json = $stmt->fetchColumn();
            $resultado = json_decode($resultado_json, true);

            if (!$resultado || !$resultado['success']) {
                $msg = $resultado['message'] ?? 'Error desconocido al eliminar el vendedor';
                throw new Exception($msg);
            }

            echo json_encode(['success' => true, 'message' => $resultado['message']]);
            break;

        /* ============================================================
         * CASO 4: RESTAURAR VENDEDOR
         * Reactiva un vendedor que fue previamente eliminado/desactivado,
         * permitiéndole volver a iniciar sesión y operar en el sistema.
         * ============================================================ */
        case 'restore':
            $id_miembro = intval($_POST['id_miembro'] ?? 0);
            if (!$id_miembro)
                throw new Exception("ID inválido");

            /* Ejecuta fun_restaurar_vendedor() que cambia estado a 'activo' */
            $stmt = $pdo->prepare("SELECT fun_restaurar_vendedor(?)");
            $stmt->execute([$id_miembro]);

            /* Leer respuesta de la función */
            $resultado_json = $stmt->fetchColumn();
            $resultado = json_decode($resultado_json, true);

            if (!$resultado || !$resultado['success']) {
                $msg = $resultado['message'] ?? 'Error desconocido al restaurar el vendedor';
                throw new Exception($msg);
            }

            echo json_encode(['success' => true, 'message' => $resultado['message']]);
            break;

        /* Si la acción recibida no coincide con ningún caso válido */
        default:
            throw new Exception("Acción no válida");
    }

/* ============================================================
 * MANEJO GLOBAL DE ERRORES
 * Cualquier throw new Exception() de arriba cae aquí.
 * Si había una transacción SQL abierta, la revierte.
 * Devuelve el error como JSON para que el frontend lo muestre.
 * ============================================================ */
} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

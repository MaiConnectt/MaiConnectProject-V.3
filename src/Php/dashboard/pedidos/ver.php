<?php
/**
 * ===================================================================
 * Archivo: ver.php (Pedidos)
 * Propósito: Muestra el detalle completo de un pedido individual
 *            (vendedor, cliente, artículos, estado de pago, recibo, 
 *            historial de cambios) y opciones de acción rápida.
 * ===================================================================
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/helpers.php';

// Obtener ID del pedido
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// URL de regreso: si viene del perfil de un vendedor, volver ahí
$seller_from = isset($_GET['seller_id']) ? (int) $_GET['seller_id'] : 0;
$back_url = $seller_from
    ? BASE_URL . '/src/Php/dashboard/equipo/ver.php?id=' . $seller_from
    : 'pedidos.php';

if (empty($order_id)) {
    header('Location: pedidos.php');
    exit;
}


// Inicializar valores por defecto para evitar advertencias de variables indefinidas
$history = [];
$payment_proof = null;
$items = [];

// Obtener detalles del pedido
try {
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            m.id_miembro,
            u.nombre as nombre_vendedor,
            u.apellido as apellido_vendedor,
            u.email as email_vendedor,
            vw.total as monto_total
        FROM tbl_pedido 
        LEFT JOIN tbl_miembro m ON o.id_vendedor = m.id_miembro
        LEFT JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
        LEFT JOIN vw_totales_pedido vw ON o.id_pedido = vw.id_pedido
        WHERE o.id_pedido = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: pedidos.php');
        exit;
    }

    // Obtener artículos del pedido (esquema en español)
    $stmt = $pdo->prepare("
        SELECT 
            od.*,
            p.nombre_producto as nombre_producto,
            (od.cantidad * od.precio_unitario) as subtotal
        FROM tbl_detalle_pedido od
        LEFT JOIN tbl_producto p ON od.id_producto = p.id_producto
        WHERE od.id_pedido = ? AND od.estado = 'activo'
        ORDER BY od.id_detalle_pedido
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    // Obtener comprobante de pago (el más reciente 'pendiente' o 'aprobado' primero)
    $stmt = $pdo->prepare("
        SELECT * FROM tbl_comprobante_pago 
        WHERE id_pedido = ? AND estado_registro = 'activo'
        ORDER BY fecha_subida DESC 
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $payment_proof = $stmt->fetch() ?: null;

    // Obtener historial del pedido — join en usuario_cambio (nombre de columna correcto)
    $stmt = $pdo->prepare("
        SELECT 
            h.*,
            u.nombre as nombre_usuario,
            u.apellido as apellido_usuario
        FROM tbl_historial_pedido h
        LEFT JOIN tbl_usuario u ON h.usuario_cambio = u.id_usuario
        WHERE h.id_pedido = ?
        ORDER BY h.fecha_cambio DESC
    ");
    $stmt->execute([$order_id]);
    $history = $stmt->fetchAll() ?: [];

} catch (PDOException $e) {
    $error = "Error al cargar el pedido: " . $e->getMessage();
}

$success_message = isset($_GET['success']) ? 'Pedido creado exitosamente' : '';

$page_title = 'Detalles del Pedido - Mai Shop';
$extra_css = [BASE_URL . '/styles/pedidos.css'];
require_once __DIR__ . '/../includes/head.php';
?>
<!-- Barra lateral -->
<?php
$base = '..';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Contenido Principal -->
<main class="main-content">
    <?php if ($success_message): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <div class="header-actions no-print">
        <div>
            <h1
                style="font-size: 2.2rem; color: var(--primary-dark); font-family: 'Playfair Display', serif; margin-bottom: 0.2rem; font-weight: 700;">
                <span
                    style="color: var(--primary-color);">#</span><?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
            </h1>
            <div
                style="font-size: 0.95rem; color: #666; display: flex; gap: 1rem; align-items: center; margin-top: 0.5rem;">
                <span><i class="far fa-calendar-alt"></i>
                    <?php echo date('d/m/Y H:i', strtotime($order['fecha_creacion'])); ?></span>
                <span><i class="far fa-user"></i> Vendedor:
                    <strong><?php echo htmlspecialchars($order['nombre_vendedor']); ?></strong></span>
            </div>
        </div>
        <!-- Botones de Acción - NO SE IMPRIMEN -->
        <div class="order-actions" style="display: flex; gap: 1rem; align-items: center; z-index: 10;">
            <a href="<?php echo $back_url; ?>" class="btn-action-large secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>

            <?php if ($order['estado'] == 0 && $order['estado_pago'] == 2): ?>
                <button onclick="handleAction('mandar_produccion')" class="btn-action-large primary"
                    style="background: var(--gradient-secondary);">
                    <i class="fas fa-industry"></i> Mandar a Producción
                </button>
            <?php endif; ?>

            <?php if ($order['estado'] < 2 && !($order['estado'] == 1 && $order['estado_pago'] == 2)): ?>
                <button onclick="handleAction('cancelar_pedido', true)" class="btn-action-large secondary"
                    style="border-color: #ff6b9d; color: #ff6b9d;">
                    <i class="fas fa-ban"></i> Cancelar Pedido
                </button>
            <?php endif; ?>

            <button onclick="window.print()" class="btn-action-large secondary">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>

    <!-- Cuadrícula de Detalles -->
    <div class="details-grid">
        <!-- Columna Izquierda -->
        <div>
            <!-- Información del Vendedor -->
            <div class="detail-card print-show" style="margin-bottom: var(--spacing-md);">
                <h2 class="detail-card-title">
                    <i class="fas fa-user-tie"></i> Información del Vendedor
                </h2>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-address-card"></i> Nombre:</span>
                    <span
                        class="info-value"><?php echo htmlspecialchars($order['nombre_vendedor'] . ' ' . $order['apellido_vendedor']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-envelope"></i> Email:</span>
                    <span
                        class="info-value"><?php echo htmlspecialchars($order['email_vendedor'] ?? 'No disponible'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-phone-alt"></i> Teléfono Contacto:</span>
                    <span
                        class="info-value"><?php echo htmlspecialchars($order['telefono_contacto'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-map-marker-alt"></i> Dirección (Entrega):</span>
                    <span
                        class="info-value"><?php echo htmlspecialchars($order['direccion_entrega'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="far fa-calendar-check"></i> Fecha Programada:</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($order['fecha_entrega'])); ?></span>
                </div>
            </div>

            <!-- Artículos del Pedido -->
            <div class="detail-card print-show">
                <h2 class="detail-card-title">
                    <i class="fas fa-cookie-bite"></i> Productos
                </h2>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($item['nombre_producto']); ?>
                                </td>
                                <td>
                                    <?php echo $item['cantidad']; ?>
                                </td>
                                <td>
                                    <?php echo formato_moneda($item['precio_unitario']); ?>
                                </td>
                                <td>
                                    <?php echo formato_moneda($item['subtotal']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right; font-weight: bold;">Total:</td>
                            <td>
                                <?php echo formato_moneda($order['monto_total']); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Sección de Firmas - Solo Impresión -->
            <div class="print-only signatures-wrapper">
                <!-- Firma del Cliente -->
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-info">
                        <span class="signature-name">Firma del Cliente</span>
                        <span class="signature-role">Recibido a Satisfacción</span>
                        <span class="signature-doc">C.C: _____________________</span>
                    </div>
                </div>
                <!-- Firma de Mai Connett / Despacho -->
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-info">
                        <span class="signature-name">Firma de Entrega</span>
                        <span class="signature-role">Propietaria / Administradora</span>
                        <span class="signature-doc">Mai Connett</span>
                    </div>
                </div>
            </div>

            <!-- Frase Mai Connett - Solo Impresión -->
            <div class="print-only print-footer-quote">
                <p>"Cada vez más cerca de cumplir lo que sueñas"</p>
                <i class="fas fa-heart"></i>
            </div>
        </div>

        <!-- Columna Derecha -->
        <div class="no-print">
            <!-- Estado del Pedido -->
            <div class="detail-card" style="margin-bottom: var(--spacing-md);">
                <h2 class="detail-card-title">
                    <i class="fas fa-info-circle"></i> Estado del Pedido
                </h2>
                <div style="text-align: center; padding: var(--spacing-md) 0;">
                    <div style="font-size: 1.2rem; transform: scale(1.1); margin-bottom: 2rem;">
                        <?php echo getStatusBadge($order['estado']); ?>
                    </div>

                    <div style="margin-top: 1.5rem; border-top: 1px solid var(--gray-light); padding-top: 1rem;">
                        <div class="info-label" style="margin-bottom: 1rem;">Estado de Pago:</div>
                        <div style="transform: scale(1.1);">
                            <?php echo getPaymentBadge($order['estado_pago']); ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($order['notes'])): ?>
                    <div class="info-row">
                        <span class="info-label">Notas:</span>
                    </div>
                    <div
                        style="padding: var(--spacing-sm); background: var(--cream); border-radius: var(--radius-sm); margin-top: 0.5rem;">
                        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Comprobante de Pago -->
            <?php if ($payment_proof): ?>
                <div class="detail-card" style="margin-bottom: var(--spacing-md);">
                    <h2 class="detail-card-title">
                        <i class="fas fa-receipt"></i> Comprobante de Pago
                    </h2>
                    <div style="text-align: center; padding: var(--spacing-sm);">
                        <?php
                        $physical_path = __DIR__ . '/../../' . $payment_proof['ruta_archivo'];
                        $web_path = '../../' . $payment_proof['ruta_archivo'];

                        if (file_exists($physical_path)): ?>
                            <a href="<?php echo htmlspecialchars($web_path); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($web_path); ?>" alt="Comprobante de Pago"
                                    style="max-width: 100%; max-height: 300px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); cursor: pointer; transition: transform 0.2s;"
                                    onmouseover="this.style.transform='scale(1.02)'"
                                    onmouseout="this.style.transform='scale(1)'">
                            </a>
                        <?php else: ?>
                            <div
                                style="padding: 2rem; background: #fff5f8; border: 1px dashed #ff6b9d; border-radius: 12px; color: #ff6b9d;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                <p style="font-weight: 600; margin-bottom: 0.2rem;">Archivo no encontrado</p>
                                <p style="font-size: 0.85rem; opacity: 0.8;">El comprobante no existe en el servidor.
                                    Por favor, solicite al vendedor que lo suba de nuevo.</p>
                            </div>
                        <?php endif; ?>

                        <?php if ($order['estado_pago'] == 1 && file_exists($physical_path)): ?>
                            <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: center;">
                                <button onclick="handleAction('aprobar_pago')" class="btn-action-large primary"
                                    style="background: #20ba5a; padding: 0.5rem 1rem;">
                                    <i class="fas fa-check"></i> Aprobar Pago
                                </button>
                                <button onclick="handleAction('rechazar_pago', true)" class="btn-action-large secondary"
                                    style="border-color: #ff6b9d; color: #ff6b9d; padding: 0.5rem 1rem;">
                                    <i class="fas fa-times"></i> Rechazar
                                </button>
                            </div>
                        <?php elseif ($order['estado_pago'] == 2): ?>
                            <div style="margin-top: 1rem; color: #20ba5a; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Pago aprobado
                            </div>
                        <?php elseif ($order['estado_pago'] == 3): ?>
                            <div style="margin-top: 1rem; color: #ff6b9d; font-weight: 600;">
                                <i class="fas fa-times-circle"></i> Pago rechazado
                                <?php if (!empty($payment_proof['notas'])): ?>
                                    <div style="font-size:0.85rem; color: var(--gray); font-weight:400; margin-top:0.3rem;">
                                        <?php echo htmlspecialchars($payment_proof['notas'] ?? ''); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (file_exists($physical_path)): ?>
                            <p style="margin-top: 0.5rem; color: var(--gray); font-size: 0.85rem;">
                                <i class="fas fa-search-plus"></i> Clic para ampliar
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="detail-card" style="margin-bottom: var(--spacing-md);">
                    <h2 class="detail-card-title">
                        <i class="fas fa-receipt"></i> Comprobante de Pago
                    </h2>
                    <div style="text-align: center; padding: var(--spacing-md); color: var(--gray);">
                        <i class="fas fa-file-upload"
                            style="font-size: 2rem; opacity: 0.4; margin-bottom: 0.5rem; display: block;"></i>
                        <p style="font-size: 0.9rem;">Sin comprobante subido aún.</p>
                        <p style="font-size: 0.8rem;">El vendedor debe subir el comprobante de pago.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Historial del Pedido (Oculto permanentemente de la vista a solicitud)
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <i class="fas fa-history"></i> Historial del Pedido
                        </h2>
                        <div class="timeline">
                            <?php if (empty($history)): ?>
                                <p style="color: var(--gray); font-size: 0.9rem; text-align: center;">Sin registros
                                    históricos</p>
                            <?php endif; ?>
                            <?php foreach ($history as $h): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        <?php echo date('d/m/Y H:i', strtotime($h['fecha_cambio'] ?? 'now')); ?>
                                    </div>
                                    <div class="timeline-content">
                                        <strong>Estado <?php echo (int) ($h['estado_anterior'] ?? '-'); ?> →
                                            <?php echo (int) ($h['estado_nuevo'] ?? '-'); ?></strong> por
                                        <em><?php echo htmlspecialchars(($h['nombre_usuario'] ?? 'Sistema') . ' ' . ($h['apellido_usuario'] ?? '')); ?></em>
                                        <?php if (!empty($h['motivo'])): ?>
                                            <div
                                                style="background: var(--cream); padding: 0.5rem; border-radius: 8px; font-size: 0.85rem; margin-top: 0.3rem; border-left: 3px solid var(--primary-color);">
                                                <?php echo htmlspecialchars($h['motivo'] ?? ''); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        </div>
                    </div>
                    -->
        </div>
    </div>
</main>
</div> <!-- .dashboard-container -->

<form id="actionForm" method="POST" action="acciones.php" style="display:none;">
    <input type="hidden" name="id_pedido" value="<?php echo $order_id; ?>">
    <input type="hidden" name="action" id="formAction">
    <input type="hidden" name="notas" id="formNotas">
</form>

<?php
$extra_scripts = [BASE_URL . '/src/JavaScript/pedidos_ver.js'];
require_once __DIR__ . '/../includes/footer.php';
?>
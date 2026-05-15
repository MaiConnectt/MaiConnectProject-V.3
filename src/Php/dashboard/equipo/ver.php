<?php
/**
 * ===================================================================
 * Archivo: ver.php (Equipo)
 * Propósito: Muestra el perfil detallado de un vendedor particular, 
 *            estadísticas individuales actualizadas (pedidos, ventas, 
 *            comisiones) y el listado de sus pedidos recientes.
 * ===================================================================
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/conexion.php';

// Obtener ID del vendedor
$seller_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (empty($seller_id)) {
    header('Location: ' . BASE_URL . '/src/Php/dashboard/equipo/equipo.php');
    exit;
}

// Obtener detalles del vendedor con estadísticas
try {
    $stmt = $pdo->prepare("
        SELECT 
            m.*,
            u.nombre,
            u.apellido,
            u.email,
            (SELECT COUNT(*) FROM tbl_pedido o WHERE o.id_vendedor = m.id_miembro AND o.estado = 2) as total_orders,
            (SELECT COALESCE(SUM(ot.total),0) FROM tbl_pedido o JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido WHERE o.id_vendedor = m.id_miembro AND o.estado = 2) as total_sales,
            (SELECT COALESCE(SUM(monto_comision),0) FROM tbl_pedido o WHERE o.id_vendedor = m.id_miembro AND o.estado = 2) as total_commissions_earned,
            (SELECT COALESCE(SUM(monto_comision),0) FROM tbl_pedido o WHERE o.id_vendedor = m.id_miembro AND o.estado = 2 AND o.id_pago_comision IS NOT NULL) as total_paid,
            (SELECT COALESCE(SUM(monto_comision),0) FROM tbl_pedido o WHERE o.id_vendedor = m.id_miembro AND o.estado = 2 AND o.id_pago_comision IS NULL) as balance_pending
        FROM tbl_miembro m
        INNER JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
        WHERE m.id_miembro = ?
    ");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch();

    if (!$seller) {
        header('Location: ' . BASE_URL . '/src/Php/dashboard/equipo/equipo.php');
        exit;
    }

    // Obtener pedidos recientes
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            vw.total as monto_total
        FROM tbl_pedido o
        LEFT JOIN vw_totales_pedido vw ON o.id_pedido = vw.id_pedido
        WHERE o.id_vendedor = ?
        ORDER BY o.fecha_creacion DESC
        LIMIT 10
    ");
    $stmt->execute([$seller_id]);
    $orders = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Error al cargar los detalles del vendedor: " . $e->getMessage();
}

/**
 * Genera una insignia HTML (badge) según el código de estado del pedido.
 * 
 * @param int $status Código de estado (0=Pendiente, 1=Proceso, 2=Completado, 3=Cancelado).
 * @return string Snippet HTML con la insignia y clase correspondiente.
 */
function getStatusBadge($status)
{
    switch ($status) {
        case 0:
            return '<span class="status-badge pending">Pendiente</span>';
        case 1:
            return '<span class="status-badge processing">En Proceso</span>';
        case 2:
            return '<span class="status-badge completed">Completado</span>';
        case 3:
            return '<span class="status-badge cancelled">Cancelado</span>';
        default:
            return '<span class="status-badge">Desconocido</span>';
    }
}
?>
<?php
$page_title = 'Perfil del Vendedor - Mai Shop';
$extra_css  = [BASE_URL . '/styles/equipo.css'];
require_once __DIR__ . '/../includes/head.php';
?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<main class="main-content" style="padding: 2rem 2.5rem;">

    <a href="equipo.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al equipo</a>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php else: ?>

    <!-- ===== HERO CARD ===== -->
    <div style="
        background: linear-gradient(135deg, #c97c89 0%, #a65c68 100%);
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: center;
        gap: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(201,124,137,0.35);
    ">
        <!-- Círculo decorativo de fondo -->
        <div style="position:absolute;right:-60px;top:-60px;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,0.07);"></div>
        <div style="position:absolute;right:60px;bottom:-80px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>

        <!-- Avatar -->
        <div style="
            width: 90px; height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(10px);
            border: 3px solid rgba(255,255,255,0.5);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; font-weight: 700;
            color: white;
            flex-shrink: 0;
            letter-spacing: 1px;
        ">
            <?php echo strtoupper(substr($seller['nombre'], 0, 1) . substr($seller['apellido'], 0, 1)); ?>
        </div>

        <!-- Info -->
        <div style="flex:1; min-width:0;">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.35rem;">
                <h1 style="font-family:'Playfair Display',serif; font-size:1.75rem; color:white; margin:0; font-weight:700;">
                    <?php echo htmlspecialchars($seller['nombre'] . ' ' . $seller['apellido']); ?>
                </h1>
                <span style="
                    padding: 0.3rem 0.85rem;
                    border-radius: 20px;
                    font-size: 0.75rem;
                    font-weight: 600;
                    background: <?php echo $seller['estado'] === 'activo' ? 'rgba(198,246,213,0.9)' : 'rgba(254,215,215,0.9)'; ?>;
                    color: <?php echo $seller['estado'] === 'activo' ? '#22543d' : '#742a2a'; ?>;
                ">
                    <?php echo $seller['estado'] === 'activo' ? '✅ Activo' : '⛔ Inactivo'; ?>
                </span>
            </div>

            <div style="display:flex; flex-wrap:wrap; gap:1.25rem; color:rgba(255,255,255,0.88); font-size:0.875rem;">
                <span><i class="fas fa-envelope" style="margin-right:0.4rem;opacity:0.8;"></i><?php echo htmlspecialchars($seller['email']); ?></span>
                <?php if (!empty($seller['telefono'])): ?>
                <span><i class="fas fa-phone" style="margin-right:0.4rem;opacity:0.8;"></i><?php echo htmlspecialchars($seller['telefono']); ?></span>
                <?php endif; ?>
                <?php if (!empty($seller['universidad'])): ?>
                <span><i class="fas fa-graduation-cap" style="margin-right:0.4rem;opacity:0.8;"></i><?php echo htmlspecialchars($seller['universidad']); ?></span>
                <?php endif; ?>
                <span><i class="fas fa-calendar-alt" style="margin-right:0.4rem;opacity:0.8;"></i>Desde <?php echo date('d/m/Y', strtotime($seller['fecha_contratacion'])); ?></span>
                <span><i class="fas fa-id-card" style="margin-right:0.4rem;opacity:0.8;"></i><?php echo htmlspecialchars($seller['tipo_documento'] ?? ''); ?> <?php echo htmlspecialchars($seller['numero_documento'] ?? ''); ?></span>
            </div>
        </div>

        <!-- Botón Editar -->
        <a href="editar.php?id=<?php echo $seller['id_miembro']; ?>" style="
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(255,255,255,0.4);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            flex-shrink: 0;
            white-space: nowrap;
        " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fas fa-edit"></i> Editar
        </a>
    </div>

    <!-- ===== STATS ===== -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1.25rem; margin-bottom:1.75rem;">

        <div style="background:white; border-radius:16px; padding:1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border-top:4px solid #c97c89;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#f9e4e8,#f5c6ce);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-shopping-bag" style="color:#c97c89;font-size:1.25rem;"></i>
                </div>
                <div>
                    <div style="font-size:1.75rem;font-weight:700;color:#2d3748;line-height:1;"><?php echo $seller['total_orders']; ?></div>
                    <div style="font-size:0.8rem;color:#a0aec0;margin-top:0.2rem;">Pedidos Completados</div>
                </div>
            </div>
        </div>

        <div style="background:white; border-radius:16px; padding:1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border-top:4px solid #48bb78;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#c6f6d5,#9ae6b4);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-dollar-sign" style="color:#276749;font-size:1.25rem;"></i>
                </div>
                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:#2d3748;line-height:1;">$<?php echo number_format($seller['total_sales'] ?? 0, 0, ',', '.'); ?></div>
                    <div style="font-size:0.8rem;color:#a0aec0;margin-top:0.2rem;">Ventas Totales</div>
                </div>
            </div>
        </div>

        <div style="background:white; border-radius:16px; padding:1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border-top:4px solid #667eea;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#ebf4ff,#c3dafe);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-wallet" style="color:#434190;font-size:1.25rem;"></i>
                </div>
                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:#2d3748;line-height:1;">$<?php echo number_format($seller['total_commissions_earned'] ?? 0, 0, ',', '.'); ?></div>
                    <div style="font-size:0.8rem;color:#a0aec0;margin-top:0.2rem;">Comisiones Generadas</div>
                </div>
            </div>
        </div>

        <div style="background:white; border-radius:16px; padding:1.5rem; box-shadow:0 2px 12px rgba(0,0,0,0.06); border-top:4px solid #ed8936;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#feebc8,#fbd38d);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-clock" style="color:#c05621;font-size:1.25rem;"></i>
                </div>
                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:#c05621;line-height:1;">$<?php echo number_format($seller['balance_pending'] ?? 0, 0, ',', '.'); ?></div>
                    <div style="font-size:0.8rem;color:#a0aec0;margin-top:0.2rem;">Saldo Pendiente</div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===== TABLA DE PEDIDOS ===== -->
    <div style="background:white; border-radius:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden;">
        <div style="padding:1.5rem 1.75rem; border-bottom:1px solid #f0e4e6; display:flex; align-items:center; gap:0.75rem;">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f9e4e8,#f5c6ce);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-history" style="color:#c97c89;font-size:0.9rem;"></i>
            </div>
            <h2 style="font-family:'Playfair Display',serif; font-size:1.25rem; color:#2d3748; margin:0;">Pedidos Recientes</h2>
            <span style="margin-left:auto; background:#f9e4e8; color:#c97c89; padding:0.3rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
                <?php echo count($orders); ?> pedidos
            </span>
        </div>

        <div style="overflow-x:auto;">
            <div class="table-responsive">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#fdf8f9;">
                        <th style="text-align:left; padding:1rem 1.75rem; font-size:0.75rem; font-weight:600; color:#a78b8f; text-transform:uppercase; letter-spacing:0.05em;">ID</th>
                        <th style="text-align:left; padding:1rem 1rem; font-size:0.75rem; font-weight:600; color:#a78b8f; text-transform:uppercase; letter-spacing:0.05em;">Fecha</th>
                        <th style="text-align:left; padding:1rem 1rem; font-size:0.75rem; font-weight:600; color:#a78b8f; text-transform:uppercase; letter-spacing:0.05em;">Teléfono / Dirección</th>
                        <th style="text-align:left; padding:1rem 1rem; font-size:0.75rem; font-weight:600; color:#a78b8f; text-transform:uppercase; letter-spacing:0.05em;">Total</th>
                        <th style="text-align:left; padding:1rem 1rem; font-size:0.75rem; font-weight:600; color:#a78b8f; text-transform:uppercase; letter-spacing:0.05em;">Estado</th>
                        <th style="text-align:center; padding:1rem 1.75rem; font-size:0.75rem; font-weight:600; color:#a78b8f; text-transform:uppercase; letter-spacing:0.05em;">Ver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:3rem; color:#a0aec0;">
                                <i class="fas fa-inbox" style="font-size:2.5rem; display:block; margin-bottom:0.75rem; opacity:0.4;"></i>
                                Sin pedidos registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $i => $order): ?>
                        <tr style="border-top:1px solid #faf0f2; <?php echo $i % 2 === 0 ? '' : 'background:#fdfbfb;'; ?> transition:background 0.15s;"
                            onmouseover="this.style.background='#fdf2f4'" onmouseout="this.style.background='<?php echo $i % 2 === 0 ? 'white' : '#fdfbfb'; ?>'">
                            <td style="padding:1rem 1.75rem; font-weight:600; color:#c97c89; font-size:0.9rem;">
                                #<?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td style="padding:1rem; color:#718096; font-size:0.875rem;">
                                <?php echo date('d/m/Y', strtotime($order['fecha_creacion'])); ?>
                            </td>
                            <td style="padding:1rem;">
                                <div style="font-weight:600; color:#2d3748; font-size:0.9rem;"><?php echo htmlspecialchars($order['telefono_contacto']); ?></div>
                                <div style="font-size:0.78rem; color:#a0aec0; margin-top:0.15rem;"><?php echo htmlspecialchars($order['direccion_entrega']); ?></div>
                            </td>
                            <td style="padding:1rem; font-weight:700; color:#2d3748; font-size:0.95rem;">
                                $<?php echo number_format($order['monto_total'] ?? 0, 0, ',', '.'); ?>
                            </td>
                            <td style="padding:1rem;">
                                <?php echo getStatusBadge($order['estado']); ?>
                            </td>
                            <td style="padding:1rem 1.75rem; text-align:center;">
                                <a href="<?= BASE_URL ?>/src/Php/dashboard/pedidos/ver.php?id=<?php echo $order['id_pedido']; ?>&seller_id=<?php echo $seller_id; ?>"
                                    style="width:34px;height:34px;border-radius:8px;background:#f0f4ff;color:#667eea;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all 0.2s;"
                                    title="Ver pedido"
                                    onmouseover="this.style.background='#667eea';this.style.color='white';"
                                    onmouseout="this.style.background='#f0f4ff';this.style.color='#667eea';">
                                    <i class="fas fa-eye" style="font-size:0.85rem;"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <?php endif; ?>
</main>

<?php
$extra_scripts = [];
require_once __DIR__ . '/../includes/footer.php';
?>
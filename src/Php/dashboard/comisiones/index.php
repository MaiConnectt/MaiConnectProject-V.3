<?php
/**
 * ===================================================================
 * Archivo: index.php (Comisiones)
 * Propósito: Módulo principal de administración de comisiones. Muestra 
 *            las pestañas de comisiones pendientes por pagar y el 
 *            historial de comisiones ya pagadas a los vendedores.
 * ===================================================================
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/helpers.php';

$current_user = [
    'id' => $_SESSION['user_id'] ?? 0,
    'name' => ($_SESSION['first_name'] ?? 'Usuario') . ' ' . ($_SESSION['last_name'] ?? ''),
    'email' => $_SESSION['email'] ?? '',
    'role' => 'Administrador'
];

// Pestaña Activa
$active_tab = $_GET['tab'] ?? 'pending';

// 1. Obtener Pendientes
try {
    $sql_pending = "
        SELECT 
            p.id_pedido,
            p.fecha_creacion,
            p.monto_comision,
            ot.total as total_pedido,
            u.nombre, 
            u.apellido,
            m.porcentaje_comision
        FROM tbl_pedido p
        JOIN tbl_miembro m ON p.id_vendedor = m.id_miembro
        JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
        JOIN vw_totales_pedido ot ON p.id_pedido = ot.id_pedido
        WHERE p.estado = 2 
        AND p.monto_comision > 0 
        AND p.id_pago_comision IS NULL
        ORDER BY p.fecha_creacion DESC
    ";
    $stmt = $pdo->query($sql_pending);
    $pending_orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending_orders = [];
    $error = "Error al cargar pendientes: " . $e->getMessage();
}

// 2. Obtener Pagadas
try {
    $sql_paid = "
        SELECT 
            p.id_pedido,
            p.fecha_creacion,
            p.monto_comision,
            pc.fecha_pago,
            pc.ruta_archivo,
            u.nombre,
            u.apellido
        FROM tbl_pedido p
        JOIN tbl_pago_comision pc ON p.id_pago_comision = pc.id_pago_comision
        JOIN tbl_miembro m ON p.id_vendedor = m.id_miembro
        JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
        WHERE p.id_pago_comision IS NOT NULL
        ORDER BY pc.fecha_pago DESC
    ";
    $stmt_paid = $pdo->query($sql_paid);
    $paid_orders = $stmt_paid->fetchAll();
} catch (PDOException $e) {
    $paid_orders = [];
}

$page_title = 'Comisiones - Mai Shop';
$extra_css = [BASE_URL . '/styles/comisiones.css'];
require_once __DIR__ . '/../includes/head.php';
?>

<!-- Barra lateral -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<main class="main-content" style="padding: 2rem 2.5rem;">

    <!-- ===== HERO HEADER ===== -->
    <div style="
        background: linear-gradient(135deg, #c97c89 0%, #a65c68 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(201,124,137,0.35);
    ">
        <div style="position:absolute;right:-40px;top:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,0.07);"></div>
        <div style="position:absolute;right:80px;bottom:-60px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>

        <div style="position:relative; z-index:1;">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.4rem;">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-hand-holding-usd" style="color:white;font-size:1.25rem;"></i>
                </div>
                <h1 style="font-family:'Playfair Display',serif;font-size:1.75rem;color:white;margin:0;font-weight:700;">Gestión de Comisiones</h1>
            </div>
            <p style="color:rgba(255,255,255,0.78);font-size:0.875rem;margin:0;padding-left:3.5rem;">
                Administra los pagos pendientes a tu equipo de ventas
            </p>
        </div>

        <!-- Mini Stats -->
        <div style="display:flex; gap:1.5rem; position:relative; z-index:1; flex-shrink:0;">
            <div style="text-align:center; background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border-radius:14px; padding:1rem 1.5rem; border:1px solid rgba(255,255,255,0.2);">
                <div style="font-size:1.5rem;font-weight:700;color:white;"><?php echo count($pending_orders); ?></div>
                <div style="font-size:0.75rem;color:rgba(255,255,255,0.75);margin-top:0.2rem;">Pendientes</div>
            </div>
            <div style="text-align:center; background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border-radius:14px; padding:1rem 1.5rem; border:1px solid rgba(255,255,255,0.2);">
                <div style="font-size:1.5rem;font-weight:700;color:white;">
                    $<?php echo number_format(array_sum(array_column($pending_orders, 'monto_comision')), 0, ',', '.'); ?>
                </div>
                <div style="font-size:0.75rem;color:rgba(255,255,255,0.75);margin-top:0.2rem;">Por Pagar</div>
            </div>
            <div style="text-align:center; background:rgba(255,255,255,0.15); backdrop-filter:blur(8px); border-radius:14px; padding:1rem 1.5rem; border:1px solid rgba(255,255,255,0.2);">
                <div style="font-size:1.5rem;font-weight:700;color:#c6f6d5;">
                    $<?php echo number_format(array_sum(array_column($paid_orders, 'monto_comision')), 0, ',', '.'); ?>
                </div>
                <div style="font-size:0.75rem;color:rgba(255,255,255,0.75);margin-top:0.2rem;">Ya Pagado</div>
            </div>
        </div>
    </div>

    <!-- ===== PESTAÑAS ===== -->
    <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; background:white; padding:0.4rem; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,0.06); width:fit-content;">
        <a href="?tab=pending" style="
            display:flex; align-items:center; gap:0.5rem;
            padding: 0.65rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            <?php echo $active_tab === 'pending'
                ? 'background:linear-gradient(135deg,#c97c89,#a65c68);color:white;box-shadow:0 4px 12px rgba(201,124,137,0.35);'
                : 'color:#718096;'; ?>
        ">
            <i class="fas fa-clock"></i> Pendientes
            <?php if (count($pending_orders) > 0): ?>
                <span style="
                    background:<?php echo $active_tab === 'pending' ? 'rgba(255,255,255,0.3)' : '#fed7d7'; ?>;
                    color:<?php echo $active_tab === 'pending' ? 'white' : '#c53030'; ?>;
                    padding:0.15rem 0.55rem; border-radius:20px; font-size:0.75rem; font-weight:700;
                "><?php echo count($pending_orders); ?></span>
            <?php endif; ?>
        </a>
        <a href="?tab=paid" style="
            display:flex; align-items:center; gap:0.5rem;
            padding: 0.65rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            <?php echo $active_tab === 'paid'
                ? 'background:linear-gradient(135deg,#c97c89,#a65c68);color:white;box-shadow:0 4px 12px rgba(201,124,137,0.35);'
                : 'color:#718096;'; ?>
        ">
            <i class="fas fa-check-circle"></i> Pagadas
            <?php if (count($paid_orders) > 0): ?>
                <span style="
                    background:<?php echo $active_tab === 'paid' ? 'rgba(255,255,255,0.3)' : '#c6f6d5'; ?>;
                    color:<?php echo $active_tab === 'paid' ? 'white' : '#276749'; ?>;
                    padding:0.15rem 0.55rem; border-radius:20px; font-size:0.75rem; font-weight:700;
                "><?php echo count($paid_orders); ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- ===== TABLA ===== -->
    <div style="background:white; border-radius:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden;">

        <!-- Header de la tabla -->
        <div style="padding:1.5rem 1.75rem; border-bottom:1px solid #f0eef8; display:flex; align-items:center; gap:0.75rem;">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f9e4e8,#f5c6ce);display:flex;align-items:center;justify-content:center;">
                <?php if ($active_tab === 'pending'): ?>
                    <i class="fas fa-clock" style="color:#a65c68;font-size:0.9rem;"></i>
                <?php else: ?>
                    <i class="fas fa-history" style="color:#a65c68;font-size:0.9rem;"></i>
                <?php endif; ?>
            </div>
            <h2 style="font-family:'Playfair Display',serif;font-size:1.2rem;color:#2d3748;margin:0;">
                <?php echo $active_tab === 'pending' ? 'Comisiones Pendientes de Pago' : 'Historial de Comisiones Pagadas'; ?>
            </h2>
        </div>

        <div style="overflow-x:auto;">
            <?php if ($active_tab === 'pending'): ?>
            <div class="table-responsive">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#fdf8f9;">
                        <th style="text-align:left;padding:1rem 1.75rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Pedido</th>
                        <th style="text-align:left;padding:1rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Fecha</th>
                        <th style="text-align:left;padding:1rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Vendedor</th>
                        <th style="text-align:left;padding:1rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Total Pedido</th>
                        <th style="text-align:left;padding:1rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Comisión</th>
                        <th style="text-align:center;padding:1rem 1.75rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending_orders)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:4rem;color:#a0aec0;">
                                <i class="fas fa-check-double" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:0.3;color:#c97c89;"></i>
                                <div style="font-weight:600;font-size:1rem;margin-bottom:0.3rem;">¡Todo al día!</div>
                                <div style="font-size:0.875rem;">No hay comisiones pendientes por pagar.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending_orders as $i => $order): ?>
                        <tr style="border-top:1px solid #faf0f2; <?php echo $i % 2 !== 0 ? 'background:#fdfbfb;' : ''; ?> transition:background 0.15s;"
                            onmouseover="this.style.background='#fdf2f4'" onmouseout="this.style.background='<?php echo $i % 2 !== 0 ? '#fdfbfb' : 'white'; ?>'">
                            <td style="padding:1rem 1.75rem; font-weight:700; color:#c97c89; font-size:0.9rem;">
                                #<?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td style="padding:1rem; color:#718096; font-size:0.875rem;">
                                <?php echo date('d/m/Y', strtotime($order['fecha_creacion'])); ?>
                            </td>
                            <td style="padding:1rem;">
                                <div style="font-weight:600;color:#2d3748;font-size:0.9rem;">
                                    <?php echo htmlspecialchars($order['nombre'] . ' ' . $order['apellido']); ?>
                                </div>
                                <div style="font-size:0.78rem;color:#a0aec0;margin-top:0.1rem;">
                                    <?php echo $order['porcentaje_comision']; ?>% de comisión
                                </div>
                            </td>
                            <td style="padding:1rem; color:#2d3748; font-weight:500;">
                                <?php echo formato_moneda($order['total_pedido'] ?? 0); ?>
                            </td>
                            <td style="padding:1rem;">
                                <span style="
                                    background: linear-gradient(135deg,#fed7e2,#fbb6ce);
                                    color: #97266d;
                                    font-weight: 700;
                                    padding: 0.35rem 0.85rem;
                                    border-radius: 20px;
                                    font-size: 0.9rem;
                                    display: inline-block;
                                ">
                                    <?php echo formato_moneda($order['monto_comision'] ?? 0); ?>
                                </span>
                            </td>
                            <td style="padding:1rem 1.75rem; text-align:center;">
                                <a href="<?= BASE_URL ?>/src/Php/dashboard/comisiones/pagar.php?id_pedido=<?php echo $order['id_pedido']; ?>"
                                    style="
                                        display:inline-flex; align-items:center; gap:0.4rem;
                                        padding: 0.55rem 1.1rem;
                                        background: linear-gradient(135deg,#c97c89,#a65c68);
                                        color: white;
                                        border-radius: 10px;
                                        text-decoration: none;
                                        font-size: 0.82rem;
                                        font-weight: 600;
                                        box-shadow: 0 3px 10px rgba(201,124,137,0.35);
                                        transition: all 0.2s;
                                        white-space: nowrap;
                                    "
                                    onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 5px 14px rgba(201,124,137,0.45)'"
                                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 3px 10px rgba(201,124,137,0.35)'">
                                    <i class="fas fa-money-bill-wave"></i> Registrar Pago
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>

            <?php else: ?>
            <!-- TABLA PAGADAS -->
            <div class="table-responsive">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#fdf8f9;">
                        <th style="text-align:left;padding:1rem 1.75rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Pedido</th>
                        <th style="text-align:left;padding:1rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Fecha Pago</th>
                        <th style="text-align:left;padding:1rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Vendedor</th>
                        <th style="text-align:left;padding:1rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Monto Pagado</th>
                        <th style="text-align:center;padding:1rem 1.75rem;font-size:0.75rem;font-weight:600;color:#b07a85;text-transform:uppercase;letter-spacing:0.05em;">Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($paid_orders)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:4rem;color:#a0aec0;">
                                <i class="fas fa-file-invoice-dollar" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:0.3;color:#c97c89;"></i>
                                <div style="font-weight:600;margin-bottom:0.3rem;">Sin historial</div>
                                <div style="font-size:0.875rem;">No hay pagos de comisiones registrados aún.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($paid_orders as $i => $pay): ?>
                        <tr style="border-top:1px solid #faf0f2; <?php echo $i % 2 !== 0 ? 'background:#fdfbfb;' : ''; ?> transition:background 0.15s;"
                            onmouseover="this.style.background='#fdf2f4'" onmouseout="this.style.background='<?php echo $i % 2 !== 0 ? '#fdfbfb' : 'white'; ?>'">
                            <td style="padding:1rem 1.75rem; font-weight:700; color:#c97c89; font-size:0.9rem;">
                                #<?php echo str_pad($pay['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td style="padding:1rem; color:#718096; font-size:0.875rem;">
                                <?php echo date('d/m/Y H:i', strtotime($pay['fecha_pago'])); ?>
                            </td>
                            <td style="padding:1rem; font-weight:600; color:#2d3748; font-size:0.9rem;">
                                <?php echo htmlspecialchars($pay['nombre'] . ' ' . $pay['apellido']); ?>
                            </td>
                            <td style="padding:1rem;">
                                <span style="
                                    background: linear-gradient(135deg,#c6f6d5,#9ae6b4);
                                    color: #276749;
                                    font-weight: 700;
                                    padding: 0.35rem 0.85rem;
                                    border-radius: 20px;
                                    font-size: 0.9rem;
                                    display: inline-block;
                                ">
                                    <?php echo formato_moneda($pay['monto_comision'] ?? 0); ?>
                                </span>
                            </td>
                            <td style="padding:1rem 1.75rem; text-align:center;">
                                <?php if (!empty($pay['ruta_archivo'])): ?>
                                    <a href="<?= BASE_URL ?>/src/Php/<?php echo htmlspecialchars($pay['ruta_archivo']); ?>"
                                        target="_blank"
                                        style="
                                            display:inline-flex; align-items:center; gap:0.4rem;
                                            padding: 0.5rem 1rem;
                                            background: #f0f4ff;
                                            color: #c97c89;
                                            border-radius: 10px;
                                            text-decoration: none;
                                            font-size: 0.82rem;
                                            font-weight: 600;
                                            transition: all 0.2s;
                                        "
                                        onmouseover="this.style.background='#c97c89';this.style.color='white'"
                                        onmouseout="this.style.background='#f0f4ff';this.style.color='#c97c89'">
                                        <i class="fas fa-file-invoice"></i> Ver Recibo
                                    </a>
                                <?php else: ?>
                                    <span style="color:#cbd5e0;font-size:0.85rem;">Sin comprobante</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

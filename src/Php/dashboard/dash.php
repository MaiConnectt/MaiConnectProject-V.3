<?php
/**
 * ===================================================================
 * Archivo: dash.php
 * Propósito: Pantalla principal del Dashboard administrativo.
 *            Muestra tarjetas de resumen (pedidos, ingresos), 
 *            gráficos e información reciente de manera visual.
 * ===================================================================
 */
require_once __DIR__ . '/auth.php';
$page_title = 'Dashboard - Mai Shop';
require_once __DIR__ . '/includes/head.php';
?>
<!-- Barra lateral -->
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<!-- Contenido Principal -->
<main class="main-content">
    <!-- Encabezado -->
    <header class="dashboard-header">
        <div class="header-left">
            <h1>Hola Mai, buen día!</h1>
            <p>Aquí está un resumen de tu negocio hoy</p>
        </div>
        <div class="header-right">
            <div class="user-profile">
                <button class="profile-button" id="profileButton">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($current_user['email'], 0, 1)); ?>
                    </div>
                    <span>
                        <?php echo htmlspecialchars($current_user['role']); ?>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="<?= BASE_URL ?>/src/Php/dashboard/settings.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="<?= BASE_URL ?>/src/Php/dashboard/settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </a>
                    <a href="<?= BASE_URL ?>/src/Php/dashboard/logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Lógica de Estadísticas -->
    <?php
    // Obtener estadísticas reales del dashboard
    try {
        // Pedidos Totales (Todo el tiempo)
        $stmt_count = $pdo->query("SELECT COUNT(*) FROM tbl_pedido");
        $total_orders = $stmt_count->fetchColumn();

        // Ingresos Mensuales (Solo pedidos completados = 2)
        $stmt_income = $pdo->query("
                    SELECT COALESCE(SUM(vw.total), 0) 
                    FROM vw_totales_pedido vw
                    JOIN tbl_pedido o ON vw.id_pedido = o.id_pedido
                    WHERE o.estado = 2 
                    AND o.fecha_creacion >= DATE_TRUNC('month', CURRENT_DATE)
                ");
        $monthly_income = $stmt_income->fetchColumn();

    } catch (PDOException $e) {
        $total_orders = 0;
        $monthly_income = 0;
    }
    ?>

    <!-- Cuadrícula de Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value"><?php echo number_format($total_orders ?? 0); ?></div>
                    <div class="stat-label">Pedidos Totales</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">$<?php echo number_format($monthly_income ?? 0, 0, ',', '.'); ?>
                    </div>
                    <div class="stat-label">Ingresos del Mes</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <?php
        // Estadística de Comisiones Pendientes
        try {
            $stmt_comm = $pdo->query("
                        SELECT COUNT(*), COALESCE(SUM(monto_comision), 0) 
                        FROM tbl_pedido 
                        WHERE estado = 2 AND monto_comision > 0 AND id_pago_comision IS NULL
                    ");
            list($pending_comm_count, $pending_comm_total) = $stmt_comm->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            $pending_comm_count = 0;
            $pending_comm_total = 0;
        }
        ?>
        <div class="stat-card" onclick="window.location.href='<?= BASE_URL ?>/src/Php/dashboard/comisiones/index.php'"
            style="cursor: pointer;">
            <div class="stat-header">
                <div>
                    <div class="stat-value" style="color: #ff6b6b;"><?php echo $pending_comm_count ?? 0; ?>
                    </div>
                    <div class="stat-label">Comisiones Pendientes</div>
                    <div style="font-size: 0.9rem; color: var(--gray); margin-top: 0.2rem;">
                        Por pagar: $<?php echo number_format($pending_comm_total ?? 0, 0, ',', '.'); ?>
                    </div>
                </div>
                <div class="stat-icon" style="background: rgba(255, 107, 107, 0.1); color: #ff6b6b;">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Cuadrícula de Contenido -->
    <div class="content-grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">

        <!-- Gráfico de Universidad -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Vendedores por Universidad</h2>
            </div>
            <div style="padding: 1rem;">
                <canvas id="uniChart" style="width: 100%; max-height: 280px; display: block; margin: 0 auto;"></canvas>
            </div>
            <?php
            try {
                $stmt_uni = $pdo->query("
                            SELECT 
                                COALESCE(NULLIF(TRIM(universidad), ''), 'Sin especificar') AS nombre_universidad,
                                COUNT(id_miembro) AS total_vendedores
                            FROM tbl_miembro
                            WHERE estado != 'eliminado'
                            GROUP BY COALESCE(NULLIF(TRIM(universidad), ''), 'Sin especificar')
                            ORDER BY total_vendedores DESC
                        ");
                $uni_data = $stmt_uni->fetchAll(PDO::FETCH_ASSOC);

                $labels = [];
                $data = [];
                foreach ($uni_data as $row) {
                    $labels[] = $row['nombre_universidad'];
                    $data[] = (int) $row['total_vendedores'];
                }
            } catch (PDOException $e) {
                $labels = [];
                $data = [];
            }
            ?>
        </div>

        <!-- Pedidos Recientes -->
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">Pedidos Recientes</h2>
                <a href="<?= BASE_URL ?>/src/Php/dashboard/pedidos/pedidos.php" class="card-action">Ver todos <i
                        class="fas fa-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Pedido #</th>
                        <th>Vendedor</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Obtener últimos 5 pedidos
                    try {
                        $sql_recent = "
                                    SELECT 
                                        o.id_pedido, 
                                        u.nombre, 
                                        u.apellido,
                                        ot.total,
                                        o.estado,
                                        o.fecha_creacion
                                    FROM tbl_pedido o
                                    LEFT JOIN tbl_miembro m ON o.id_vendedor = m.id_miembro
                                    LEFT JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
                                    JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
                                    ORDER BY o.fecha_creacion DESC
                                    LIMIT 5
                                ";
                        $stmt_recent = $pdo->query($sql_recent);
                        $recent_orders = $stmt_recent->fetchAll();

                        if (empty($recent_orders)) {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 2rem;'>No hay pedidos recientes.</td></tr>";
                        } else {
                            foreach ($recent_orders as $order) {
                                $status_class = '';
                                $status_text = '';
                                switch ($order['estado']) {
                                    case 0:
                                        $status_class = 'pending';
                                        $status_text = 'Pendiente';
                                        break;
                                    case 1:
                                        $status_class = 'processing';
                                        $status_text = 'En Proceso';
                                        break;
                                    case 2:
                                        $status_class = 'completed';
                                        $status_text = 'Completado';
                                        break;
                                    default:
                                        $status_class = 'cancelled';
                                        $status_text = 'Cancelado';
                                }

                                echo "<tr>";
                                echo "<td><span style='font-weight: 600; color: #555;'>#" . str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT) . "</span></td>";
                                echo "<td><div style='display:flex; align-items:center; gap:0.5rem;'><div style='width: 25px; height: 25px; border-radius: 50%; background: var(--accent-color); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold;'>" . strtoupper(substr($order['nombre'] ?? 'A', 0, 1)) . "</div>" . htmlspecialchars(($order['nombre'] ?? 'Admin') . ' ' . ($order['apellido'] ?? '')) . "</div></td>";
                                echo "<td style='color: #777;'>" . date('d/m/Y', strtotime($order['fecha_creacion'])) . "</td>";
                                echo "<td style='font-weight: 600;'>$" . number_format($order['total'] ?? 0, 0, ',', '.') . "</td>";
                                echo "<td><span class='badge {$status_class}'>" . $status_text . "</span></td>";
                                echo "</tr>";
                            }
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='5'>Error al cargar pedidos.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Acciones Rápidas Eliminadas -->
    </div>
</main>
</div>

<!-- dashboard.js cargado por footer.php con BASE_URL -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('uniChart');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            // Crear gradiente para las barras
            const gradientBg = ctx.createLinearGradient(0, 0, 0, 300);
            gradientBg.addColorStop(0, 'rgba(201, 124, 137, 0.8)');   // primary-color con opacidad
            gradientBg.addColorStop(1, 'rgba(201, 124, 137, 0.2)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels ?? []); ?>,
                    datasets: [{
                        label: 'Vendedores',
                        data: <?php echo json_encode($data ?? []); ?>,
                        backgroundColor: gradientBg,
                        borderColor: '#c97c89', // primary-color sólido
                        borderWidth: 1.5,
                        borderRadius: 8,        // Bordes más suaves
                        borderSkipped: false,
                        hoverBackgroundColor: '#c97c89'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>
<?php
$extra_scripts = ['https://cdn.jsdelivr.net/npm/chart.js'];
require_once __DIR__ . '/includes/footer.php';
?>
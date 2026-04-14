<?php
/**
 * ===================================================================
 * Archivo: sidebar.php (Seller Includes)
 * Propósito: Menú lateral de navegación específico para el vendedor.
 *            Resalta la página activa y proporciona acceso a crear 
 *            pedidos, ver historial, comisiones y configuración.
 * ===================================================================
 */
/**
 * Determina si un enlace es la página actual para aplicarle
 * la clase CSS 'active' y marcarlo visualmente en el menú.
 *
 * @param string $link Nombre del archivo de destino a comprobar.
 * @return string Nombre de la clase 'active' o vacío.
 */
function isActive($link)
{
    return basename($_SERVER['PHP_SELF']) === $link ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/src/img/mai.png" alt="Mai Shop" class="sidebar-logo">
        <h2 class="sidebar-title">Mai Shop</h2>
        <p class="sidebar-subtitle">Panel de Vendedor</p>
    </div>

    <nav class="sidebar-nav">
        <a href="seller_dash.php" class="nav-item <?php echo isActive('seller_dash.php'); ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="productos.php" class="nav-item <?php echo isActive('productos.php'); ?>">
            <i class="fas fa-box-open"></i>
            <span>Productos</span>
        </a>

        <a href="nuevo_pedido.php" class="nav-item <?php echo isActive('nuevo_pedido.php'); ?>">
            <i class="fas fa-plus-circle"></i>
            <span>Nuevo Pedido</span>
        </a>
        <a href="mis_pedidos.php" class="nav-item <?php echo isActive('mis_pedidos.php'); ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Mis Pedidos</span>
        </a>
        <a href="comisiones.php" class="nav-item <?php echo isActive('comisiones.php'); ?>">
            <i class="fas fa-dollar-sign"></i>
            <span>Comisiones</span>
        </a>

    </nav>

    <a href="<?= BASE_URL ?>/src/Php/dashboard/logout.php" class="logout-btn" 
       onclick="event.preventDefault(); const logoutUrl = this.href; MaiModal.confirm({
           title: 'Cerrar Sesión',
           message: '¿Estás seguro de que deseas salir del sistema?',
           confirmText: 'Sí, Salir',
           cancelText: 'No, Volver',
           type: 'danger',
           onConfirm: () => { window.location.href = logoutUrl; }
       });">
        <i class="fas fa-sign-out-alt"></i>
        <span>Cerrar Sesión</span>
    </a>
</aside>

<?php include_once __DIR__ . '/../../dashboard/includes/modals.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/styles/mai-modal.css">
<script src="<?= BASE_URL ?>/src/JavaScript/mai-modal.js"></script>
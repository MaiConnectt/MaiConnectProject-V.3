<?php
/**
 * ===================================================================
 * Archivo: productos.php (Seller)
 * Propósito: Catálogo de productos en modo SOLO LECTURA para vendedores.
 *            Muestra los productos activos creados por el administrador
 *            con foto, nombre, descripción y precio. Sin acciones de
 *            edición ni eliminación.
 * ===================================================================
 */
require_once __DIR__ . '/seller_auth.php';
require_once __DIR__ . '/../config/helpers.php';

// Parámetros de búsqueda y paginación
$search         = isset($_GET['search']) ? trim($_GET['search']) : '';
$records_per_page = 12;
$current_page   = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset         = ($current_page - 1) * $records_per_page;

// Construir WHERE
$where_conditions = ["estado = 'activo'"];
$params           = [];

if (!empty($search)) {
    $where_conditions[] = "(nombre_producto ILIKE ? OR descripcion ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Total de productos para paginación
try {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM tbl_producto $where_clause");
    $count_stmt->execute($params);
    $total_records = (int) $count_stmt->fetch()['total'];
    $total_pages   = max(1, (int) ceil($total_records / $records_per_page));
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages   = 1;
}

// Obtener productos
try {
    $stmt = $pdo->prepare("
        SELECT
            id_producto,
            nombre_producto,
            descripcion,
            precio,
            imagen_principal
        FROM tbl_producto
        $where_clause
        ORDER BY nombre_producto ASC
        LIMIT $records_per_page OFFSET $offset
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products      = [];
    $error_message = "Error al cargar los productos.";
}

$pageTitle   = 'Productos';
$extraCss    = BASE_URL . '/styles/seller-productos.css';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="<?= $extraCss ?>">
<?php endif; ?>

<div class="page-header">
    <h1><i class="fas fa-box-open" style="color:var(--primary);font-size:1.6rem;"></i> Catálogo de Productos</h1>
    <p>Consulta los productos disponibles del catálogo</p>
</div>

<!-- Barra de búsqueda -->
<form method="GET" action="productos.php" class="sp-search-bar">
    <div class="sp-search-group">
        <i class="fas fa-search"></i>
        <input
            type="text"
            name="search"
            id="searchInput"
            class="sp-search-input"
            placeholder="Buscar por nombre o descripción..."
            value="<?php echo htmlspecialchars($search); ?>"
            autocomplete="off">
        <?php if (!empty($search)): ?>
            <a href="productos.php" class="sp-clear-btn" title="Limpiar búsqueda">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Buscar
    </button>
</form>

<!-- Contador de resultados -->
<p class="sp-result-count">
    <?php if (!empty($search)): ?>
        <?= $total_records ?> resultado<?= $total_records !== 1 ? 's' : '' ?> para "<strong><?= htmlspecialchars($search) ?></strong>"
    <?php else: ?>
        <?= $total_records ?> producto<?= $total_records !== 1 ? 's' : '' ?> disponibles
    <?php endif; ?>
</p>

<?php if (isset($error_message)): ?>
    <div class="sp-error-msg">
        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<!-- Grilla de productos -->
<?php if (empty($products)): ?>
    <div class="sp-empty-state">
        <div class="sp-empty-icon">
            <i class="fas fa-cookie-bite"></i>
        </div>
        <h3>No se encontraron productos</h3>
        <p><?= !empty($search) ? 'Intenta con otra palabra clave.' : 'Aún no hay productos en el catálogo.' ?></p>
        <?php if (!empty($search)): ?>
            <a href="productos.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Limpiar búsqueda
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="sp-products-grid">
        <?php foreach ($products as $product): ?>
            <div class="sp-product-card">

                <!-- Imagen -->
                <div class="sp-product-image-wrap">
                    <?php if (!empty($product['imagen_principal'])): ?>
                        <img
                            src="<?= BASE_URL ?>/src/Php/<?= htmlspecialchars($product['imagen_principal']) ?>"
                            alt="<?= htmlspecialchars($product['nombre_producto']) ?>"
                            class="sp-product-img"
                            loading="lazy">
                    <?php else: ?>
                        <div class="sp-product-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Información -->
                <div class="sp-product-body">
                    <h3 class="sp-product-name">
                        <?= htmlspecialchars($product['nombre_producto']) ?>
                    </h3>

                    <?php if (!empty($product['descripcion'])): ?>
                        <p class="sp-product-desc">
                            <?= htmlspecialchars($product['descripcion']) ?>
                        </p>
                    <?php else: ?>
                        <p class="sp-product-desc sp-no-desc">Sin descripción disponible.</p>
                    <?php endif; ?>

                    <div class="sp-product-footer">
                        <span class="sp-product-price">
                            <?= formato_moneda($product['precio']) ?>
                        </span>
                        <a href="nuevo_pedido.php?producto=<?= $product['id_producto'] ?>"
                           class="sp-order-btn" title="Crear pedido con este producto">
                            <i class="fas fa-cart-plus"></i> Pedir
                        </a>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($total_pages > 1): ?>
        <div class="sp-pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?= $current_page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                   class="sp-page-btn">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                   class="sp-page-btn <?= $i === $current_page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?= $current_page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                   class="sp-page-btn">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
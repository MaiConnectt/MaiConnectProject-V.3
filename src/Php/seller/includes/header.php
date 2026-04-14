<?php
/**
 * ===================================================================
 * Archivo: header.php (Seller Includes)
 * Propósito: Define el <head> HTML dinámico para el área de vendedores.
 *            Carga tipografías, FontAwesome y hojas de estilo (CSS),
 *            y permite inyectar estilos extra dependiendo de la vista.
 * ===================================================================
 */
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Mai Shop</title>

    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Estilos del Vendedor -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/styles/seller.css">

    <?php if (!empty($extraStyles)): ?>
        <style><?= $extraStyles ?></style>
    <?php endif; ?>

    <?php if (!empty($extraCss)): ?>
        <link rel="stylesheet" href="<?= $extraCss ?>">
    <?php endif; ?>
</head>

<body>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
    <div class="dashboard-container">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="main-content">

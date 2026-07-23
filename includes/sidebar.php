<?php
$base = defined('BASE_URL') ? BASE_URL : '/inventory_system/';
$current = basename($_SERVER['SCRIPT_NAME']);
$dir = basename(dirname($_SERVER['SCRIPT_NAME']));
$role = $_SESSION['role'] ?? 'Viewer';

function nav_active($section, $dir) {
    return $section === $dir ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="mdi mdi-cube-outline"></span>
        <span class="brand-text">Inventory<b>System</b></span>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= $base ?>dashboard/index.php" class="nav-link <?= nav_active('dashboard', $dir) ?>">
            <i class="mdi mdi-view-dashboard-outline"></i> <span>Dashboard</span>
        </a>

        <?php if (in_array($role, ['Admin','Manager','Inventory'])): ?>
        <a href="<?= $base ?>categories/index.php" class="nav-link <?= nav_active('categories', $dir) ?>">
            <i class="mdi mdi-shape-outline"></i> <span>Categories</span>
        </a>
        <a href="<?= $base ?>products/index.php" class="nav-link <?= nav_active('products', $dir) ?>">
            <i class="mdi mdi-package-variant-closed"></i> <span>Products</span>
        </a>
        <?php endif; ?>

        <?php if (in_array($role, ['Admin','Manager'])): ?>
        <a href="<?= $base ?>suppliers/index.php" class="nav-link <?= nav_active('suppliers', $dir) ?>">
            <i class="mdi mdi-truck-outline"></i> <span>Suppliers</span>
        </a>
        <?php endif; ?>

        <?php if (in_array($role, ['Admin','Manager','Inventory'])): ?>
        <div class="nav-section-label">Stock Movement</div>
        <a href="<?= $base ?>stock_in/index.php" class="nav-link <?= nav_active('stock_in', $dir) ?>">
            <i class="mdi mdi-tray-arrow-down"></i> <span>Stock In</span>
        </a>
        <a href="<?= $base ?>stock_out/index.php" class="nav-link <?= nav_active('stock_out', $dir) ?>">
            <i class="mdi mdi-tray-arrow-up"></i> <span>Stock Out</span>
        </a>
        <a href="<?= $base ?>adjustment/index.php" class="nav-link <?= nav_active('adjustment', $dir) ?>">
            <i class="mdi mdi-tune-variant"></i> <span>Stock Adjustment</span>
        </a>
        <?php endif; ?>

        <div class="nav-section-label">Reports</div>
        <a href="<?= $base ?>reports/index.php" class="nav-link <?= nav_active('reports', $dir) ?>">
            <i class="mdi mdi-chart-bar"></i> <span>Inventory Reports</span>
        </a>

        <?php if ($role === 'Admin'): ?>
        <div class="nav-section-label">Administration</div>
        <a href="<?= $base ?>users/index.php" class="nav-link <?= nav_active('users', $dir) ?>">
            <i class="mdi mdi-account-multiple-outline"></i> <span>Manage Users</span>
        </a>
        <?php endif; ?>
    </nav>
</aside>

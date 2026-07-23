<?php
require_once __DIR__ . '/../middleware/auth.php';

// ---- Stat queries ----
$totalProducts   = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$lowStock        = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_qty <= minimum_stock AND stock_qty > 0")->fetchColumn();
$outOfStock      = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_qty <= 0")->fetchColumn();
$stockValue      = $pdo->query("SELECT COALESCE(SUM(stock_qty * cost_price),0) FROM products")->fetchColumn();

// Recent stock transactions (union of stock_in / stock_out / adjustment)
$recent = $pdo->query("
    (SELECT 'Stock In' AS type, si.created_at AS ts, p.product_name, si.quantity AS qty
        FROM stock_in si JOIN products p ON p.product_id = si.product_id)
    UNION ALL
    (SELECT 'Stock Out' AS type, so.created_at AS ts, p.product_name, -so.quantity AS qty
        FROM stock_out so JOIN products p ON p.product_id = so.product_id)
    UNION ALL
    (SELECT 'Adjustment' AS type, sa.created_at AS ts, p.product_name, sa.difference AS qty
        FROM stock_adjustment sa JOIN products p ON p.product_id = sa.product_id)
    ORDER BY ts DESC LIMIT 8
")->fetchAll();

$page_title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading">
            <h3><i class="mdi mdi-view-dashboard-outline"></i> Dashboard</h3>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon"><i class="mdi mdi-package-variant-closed"></i></div>
                    <div>
                        <div class="stat-value"><?= (int)$totalProducts ?></div>
                        <div class="stat-label">Total Products</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon info"><i class="mdi mdi-shape-outline"></i></div>
                    <div>
                        <div class="stat-value"><?= (int)$totalCategories ?></div>
                        <div class="stat-label">Total Categories</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon warn"><i class="mdi mdi-alert-outline"></i></div>
                    <div>
                        <div class="stat-value"><?= (int)$lowStock ?></div>
                        <div class="stat-label">Low Stock Items</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon danger"><i class="mdi mdi-close-circle-outline"></i></div>
                    <div>
                        <div class="stat-value"><?= (int)$outOfStock ?></div>
                        <div class="stat-label">Out of Stock</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="stat-card mb-3">
                    <div class="stat-icon"><i class="mdi mdi-currency-usd"></i></div>
                    <div>
                        <div class="stat-value">$<?= number_format($stockValue, 2) ?></div>
                        <div class="stat-label">Total Stock Value</div>
                    </div>
                </div>
                <a href="../products/index.php" class="btn btn-brand w-100 mb-2"><i class="mdi mdi-package-variant-closed"></i> Manage Products</a>
                <a href="../stock_in/index.php" class="btn btn-outline-brand w-100 mb-2"><i class="mdi mdi-tray-arrow-down"></i> Stock In</a>
                <a href="../reports/index.php" class="btn btn-outline-brand w-100"><i class="mdi mdi-chart-bar"></i> View Reports</a>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>Recent Stock Transactions</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Date</th><th>Type</th><th>Product</th><th class="text-end">Qty Change</th></tr></thead>
                            <tbody>
                            <?php if (empty($recent)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No transactions yet.</td></tr>
                            <?php else: foreach ($recent as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($r['ts']))) ?></td>
                                    <td><span class="badge <?= $r['type'] === 'Stock In' ? 'badge-ok' : ($r['type'] === 'Stock Out' ? 'badge-out' : 'badge-low') ?>"><?= htmlspecialchars($r['type']) ?></span></td>
                                    <td><?= htmlspecialchars($r['product_name']) ?></td>
                                    <td class="text-end <?= $r['qty'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= $r['qty'] > 0 ? '+' . $r['qty'] : $r['qty'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

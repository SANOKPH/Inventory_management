<?php
require_once __DIR__ . '/../middleware/auth.php';

$report = $_GET['report'] ?? 'current_stock';

$reports = [
    'current_stock'   => 'Current Stock Report',
    'low_stock'       => 'Low Stock Report',
    'out_of_stock'    => 'Out of Stock Report',
    'stock_in'        => 'Stock In Report',
    'stock_out'       => 'Stock Out Report',
    'valuation'       => 'Inventory Valuation Report',
    'supplier'        => 'Supplier Purchase Report',
    'movement'        => 'Product Movement Report',
];

$rows = [];
$columns = [];

switch ($report) {
    case 'low_stock':
        $columns = ['SKU', 'Product', 'Category', 'Stock', 'Min. Stock'];
        $rows = $pdo->query("
            SELECT p.sku, p.product_name, c.category_name, p.stock_qty, p.minimum_stock
            FROM products p LEFT JOIN categories c ON c.category_id = p.category_id
            WHERE p.stock_qty <= p.minimum_stock AND p.stock_qty > 0
            ORDER BY p.stock_qty ASC")->fetchAll();
        break;

    case 'out_of_stock':
        $columns = ['SKU', 'Product', 'Category', 'Stock'];
        $rows = $pdo->query("
            SELECT p.sku, p.product_name, c.category_name, p.stock_qty
            FROM products p LEFT JOIN categories c ON c.category_id = p.category_id
            WHERE p.stock_qty <= 0
            ORDER BY p.product_name")->fetchAll();
        break;

    case 'stock_in':
        $columns = ['Date', 'SKU', 'Product', 'Supplier', 'Qty', 'Cost', 'Reference'];
        $rows = $pdo->query("
            SELECT si.purchase_date, p.sku, p.product_name, s.company_name, si.quantity, si.cost_price, si.reference_no
            FROM stock_in si JOIN products p ON p.product_id = si.product_id
            LEFT JOIN suppliers s ON s.supplier_id = si.supplier_id
            ORDER BY si.stock_in_id DESC")->fetchAll();
        break;

    case 'stock_out':
        $columns = ['Date', 'SKU', 'Product', 'Reason', 'Qty', 'Remark'];
        $rows = $pdo->query("
            SELECT so.stock_out_date, p.sku, p.product_name, so.reason, so.quantity, so.remark
            FROM stock_out so JOIN products p ON p.product_id = so.product_id
            ORDER BY so.stock_out_id DESC")->fetchAll();
        break;

    case 'valuation':
        $columns = ['SKU', 'Product', 'Stock', 'Cost Price', 'Total Value'];
        $rows = $pdo->query("
            SELECT sku, product_name, stock_qty, cost_price, (stock_qty * cost_price) AS total_value
            FROM products ORDER BY total_value DESC")->fetchAll();
        break;

    case 'supplier':
        $columns = ['Supplier', 'Total Purchases', 'Total Qty', 'Total Cost'];
        $rows = $pdo->query("
            SELECT s.company_name, COUNT(si.stock_in_id) AS total_purchases, COALESCE(SUM(si.quantity),0) AS total_qty,
                   COALESCE(SUM(si.quantity * si.cost_price),0) AS total_cost
            FROM suppliers s LEFT JOIN stock_in si ON si.supplier_id = s.supplier_id
            GROUP BY s.supplier_id ORDER BY total_cost DESC")->fetchAll();
        break;

    case 'movement':
        $columns = ['Date', 'Product', 'Type', 'Qty Change'];
        $rows = $pdo->query("
            (SELECT si.created_at AS ts, p.product_name, 'Stock In' AS type, si.quantity AS qty
             FROM stock_in si JOIN products p ON p.product_id = si.product_id)
            UNION ALL
            (SELECT so.created_at AS ts, p.product_name, 'Stock Out' AS type, -so.quantity AS qty
             FROM stock_out so JOIN products p ON p.product_id = so.product_id)
            UNION ALL
            (SELECT sa.created_at AS ts, p.product_name, 'Adjustment' AS type, sa.difference AS qty
             FROM stock_adjustment sa JOIN products p ON p.product_id = sa.product_id)
            ORDER BY ts DESC LIMIT 200")->fetchAll();
        break;

    case 'current_stock':
    default:
        $report = 'current_stock';
        $columns = ['SKU', 'Product', 'Category', 'Supplier', 'Stock', 'Status'];
        $rows = $pdo->query("
            SELECT p.sku, p.product_name, c.category_name, s.company_name, p.stock_qty, p.status
            FROM products p LEFT JOIN categories c ON c.category_id = p.category_id
            LEFT JOIN suppliers s ON s.supplier_id = p.supplier_id
            ORDER BY p.product_name")->fetchAll();
        break;
}

$page_title = 'Inventory Reports';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center page-heading">
            <h3><i class="mdi mdi-chart-bar"></i> Inventory Reports</h3>
            <button onclick="window.print()" class="btn btn-outline-brand"><i class="mdi mdi-printer-outline"></i> Print</button>
        </div>

        <ul class="nav nav-pills mb-3 d-print-none">
            <?php foreach ($reports as $key => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $report === $key ? 'active' : '' ?> <?= $report === $key ? '' : 'text-dark' ?>"
                       style="<?= $report === $key ? 'background:#1D9E75;' : '' ?>"
                       href="?report=<?= $key ?>"><?= htmlspecialchars($label) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong><?= htmlspecialchars($reports[$report]) ?></strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><?php foreach ($columns as $col): ?><th><?= htmlspecialchars($col) ?></th><?php endforeach; ?></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="<?= count($columns) ?>" class="text-center text-muted py-4">No data available for this report.</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                        <tr>
                        <?php foreach ($r as $key => $val):
                            if (in_array($key, ['purchase_date','stock_out_date','ts'])) {
                                echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($val))) . '</td>';
                            } elseif (in_array($key, ['cost_price','total_value','total_cost'])) {
                                echo '<td>$' . number_format((float)$val, 2) . '</td>';
                            } elseif ($key === 'qty' || $key === 'quantity') {
                                echo '<td class="' . ($val >= 0 ? 'text-success' : 'text-danger') . '">' . ($val > 0 ? '+' . $val : $val) . '</td>';
                            } else {
                                echo '<td>' . htmlspecialchars($val ?? '—') . '</td>';
                            }
                        endforeach; ?>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

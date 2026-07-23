<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id      = (int)($_POST['product_id'] ?? 0);
    $new_qty         = (int)($_POST['new_qty'] ?? -1);
    $reason          = $_POST['reason'] ?? 'Count Difference';
    $adjustment_date = $_POST['adjustment_date'] ?? date('Y-m-d');

    if ($product_id <= 0 || $new_qty < 0) {
        $error = 'Please select a product and enter a valid new quantity.';
    } else {
        $chk = $pdo->prepare("SELECT stock_qty FROM products WHERE product_id = ?");
        $chk->execute([$product_id]);
        $old_qty = (int)$chk->fetchColumn();
        $difference = $new_qty - $old_qty;

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO stock_adjustment (product_id, old_qty, new_qty, difference, reason, adjustment_date, created_by)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $old_qty, $new_qty, $difference, $reason, $adjustment_date, $_SESSION['user_id']]);

            $upd = $pdo->prepare("UPDATE products SET stock_qty = ? WHERE product_id = ?");
            $upd->execute([$new_qty, $product_id]);

            $pdo->commit();
            header("Location: index.php?msg=" . urlencode("Stock adjusted successfully (" . ($difference >= 0 ? "+$difference" : $difference) . ")."));
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to record adjustment: ' . $e->getMessage();
        }
    }
}

$products = $pdo->query("SELECT product_id, sku, product_name, stock_qty FROM products ORDER BY product_name")->fetchAll();

$history = $pdo->query("
    SELECT sa.*, p.product_name, p.sku
    FROM stock_adjustment sa
    JOIN products p ON p.product_id = sa.product_id
    ORDER BY sa.adjustment_id DESC LIMIT 50
")->fetchAll();

$page_title = 'Stock Adjustment';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-tune-variant"></i> Stock Adjustment</h3></div>

        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success alert-dismissible-auto"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>Correct Inventory Count</strong></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Product</label>
                                <select name="product_id" id="adjProduct" class="form-select" required onchange="document.getElementById('curQty').innerText = this.selectedOptions[0].dataset.qty;">
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['product_id'] ?>" data-qty="<?= (int)$p['stock_qty'] ?>"><?= htmlspecialchars($p['sku'] . ' — ' . $p['product_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Current quantity: <b id="curQty">—</b></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New (Actual) Quantity</label>
                                <input type="number" name="new_qty" min="0" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <select name="reason" class="form-select">
                                    <option value="Count Difference">Count Difference</option>
                                    <option value="Damaged">Damaged</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Missing">Missing</option>
                                    <option value="Returned">Returned</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="adjustment_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <button type="submit" class="btn btn-brand w-100"><i class="mdi mdi-content-save-outline"></i> Save Adjustment</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>Recent Adjustments</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Date</th><th>Product</th><th class="text-end">Old Qty</th><th class="text-end">New Qty</th><th class="text-end">Diff</th><th>Reason</th></tr></thead>
                            <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No adjustments recorded yet.</td></tr>
                            <?php else: foreach ($history as $h): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($h['adjustment_date'] ?? $h['created_at']))) ?></td>
                                    <td><?= htmlspecialchars($h['sku'] . ' — ' . $h['product_name']) ?></td>
                                    <td class="text-end"><?= (int)$h['old_qty'] ?></td>
                                    <td class="text-end"><?= (int)$h['new_qty'] ?></td>
                                    <td class="text-end <?= $h['difference'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= $h['difference'] > 0 ? '+' . $h['difference'] : $h['difference'] ?></td>
                                    <td><span class="badge badge-low"><?= htmlspecialchars($h['reason']) ?></span></td>
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

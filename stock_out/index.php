<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id      = (int)($_POST['product_id'] ?? 0);
    $quantity        = (int)($_POST['quantity'] ?? 0);
    $reason          = $_POST['reason'] ?? 'Sale';
    $stock_out_date  = $_POST['stock_out_date'] ?? date('Y-m-d');
    $remark          = trim($_POST['remark'] ?? '');

    if ($product_id <= 0 || $quantity <= 0) {
        $error = 'Please select a product and enter a valid quantity.';
    } else {
        $chk = $pdo->prepare("SELECT stock_qty FROM products WHERE product_id = ?");
        $chk->execute([$product_id]);
        $current = (int)$chk->fetchColumn();

        if ($quantity > $current) {
            $error = "Cannot remove $quantity units — only $current in stock.";
        } else {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO stock_out (product_id, quantity, reason, stock_out_date, remark, created_by)
                                        VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$product_id, $quantity, $reason, $stock_out_date, $remark, $_SESSION['user_id']]);

                $upd = $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE product_id = ?");
                $upd->execute([$quantity, $product_id]);

                $pdo->commit();
                header("Location: index.php?msg=" . urlencode("Stock Out recorded successfully. Inventory updated (-$quantity)."));
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Failed to record stock out: ' . $e->getMessage();
            }
        }
    }
}

$products = $pdo->query("SELECT product_id, sku, product_name, stock_qty FROM products WHERE status='Active' ORDER BY product_name")->fetchAll();

$history = $pdo->query("
    SELECT so.*, p.product_name, p.sku
    FROM stock_out so
    JOIN products p ON p.product_id = so.product_id
    ORDER BY so.stock_out_id DESC LIMIT 50
")->fetchAll();

$page_title = 'Stock Out';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-tray-arrow-up"></i> Stock Out (Sale / Damage / Loss)</h3></div>

        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success alert-dismissible-auto"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>Remove Product</strong></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Product</label>
                                <select name="product_id" class="form-select" required>
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['sku'] . ' — ' . $p['product_name']) ?> (in stock: <?= (int)$p['stock_qty'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" min="1" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <select name="reason" class="form-select">
                                    <option value="Sale">Sale</option>
                                    <option value="Damage">Damage</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Lost">Lost</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="stock_out_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remark</label>
                                <textarea name="remark" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-brand w-100"><i class="mdi mdi-tray-arrow-up"></i> Save & Update Inventory</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>Recent Stock Out History</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Date</th><th>Product</th><th>Reason</th><th class="text-end">Qty</th><th>Remark</th></tr></thead>
                            <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No stock-out records yet.</td></tr>
                            <?php else: foreach ($history as $h): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($h['stock_out_date'] ?? $h['created_at']))) ?></td>
                                    <td><?= htmlspecialchars($h['sku'] . ' — ' . $h['product_name']) ?></td>
                                    <td><span class="badge badge-out"><?= htmlspecialchars($h['reason']) ?></span></td>
                                    <td class="text-end text-danger">-<?= (int)$h['quantity'] ?></td>
                                    <td><?= htmlspecialchars($h['remark']) ?></td>
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

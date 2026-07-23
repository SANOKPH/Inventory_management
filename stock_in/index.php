<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

$error = '';

// Handle new Stock In submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id    = (int)($_POST['product_id'] ?? 0);
    $supplier_id   = $_POST['supplier_id'] ?: null;
    $quantity      = (int)($_POST['quantity'] ?? 0);
    $cost_price    = (float)($_POST['cost_price'] ?? 0);
    $purchase_date = $_POST['purchase_date'] ?? date('Y-m-d');
    $reference_no  = trim($_POST['reference_no'] ?? '');
    $remark        = trim($_POST['remark'] ?? '');

    if ($product_id <= 0 || $quantity <= 0) {
        $error = 'Please select a product and enter a valid quantity.';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO stock_in
                (product_id, supplier_id, quantity, cost_price, purchase_date, reference_no, remark, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $supplier_id, $quantity, $cost_price, $purchase_date, $reference_no, $remark, $_SESSION['user_id']]);

            $upd = $pdo->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE product_id = ?");
            $upd->execute([$quantity, $product_id]);

            $pdo->commit();
            header("Location: index.php?msg=" . urlencode("Stock In recorded successfully. Inventory updated (+$quantity)."));
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to record stock in: ' . $e->getMessage();
        }
    }
}

$products  = $pdo->query("SELECT product_id, sku, product_name, stock_qty FROM products WHERE status='Active' ORDER BY product_name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY company_name")->fetchAll();

$history = $pdo->query("
    SELECT si.*, p.product_name, p.sku, s.company_name
    FROM stock_in si
    JOIN products p ON p.product_id = si.product_id
    LEFT JOIN suppliers s ON s.supplier_id = si.supplier_id
    ORDER BY si.stock_in_id DESC LIMIT 50
")->fetchAll();

$page_title = 'Stock In';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-tray-arrow-down"></i> Stock In (Receive Products)</h3></div>

        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success alert-dismissible-auto"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>Receive Product</strong></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Product</label>
                                <select name="product_id" class="form-select" required>
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['sku'] . ' — ' . $p['product_name']) ?> (current: <?= (int)$p['stock_qty'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Supplier</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">-- None --</option>
                                    <?php foreach ($suppliers as $s): ?>
                                        <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['company_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" min="1" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cost Price (per unit)</label>
                                <input type="number" step="0.01" min="0" name="cost_price" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reference No.</label>
                                <input type="text" name="reference_no" class="form-control" placeholder="PO-0001">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remark</label>
                                <textarea name="remark" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-brand w-100"><i class="mdi mdi-tray-arrow-down"></i> Save & Update Inventory</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white"><strong>Recent Stock In History</strong></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Date</th><th>Product</th><th>Supplier</th><th class="text-end">Qty</th><th class="text-end">Cost</th><th>Reference</th></tr></thead>
                            <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No stock-in records yet.</td></tr>
                            <?php else: foreach ($history as $h): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($h['purchase_date'] ?? $h['created_at']))) ?></td>
                                    <td><?= htmlspecialchars($h['sku'] . ' — ' . $h['product_name']) ?></td>
                                    <td><?= htmlspecialchars($h['company_name'] ?? '—') ?></td>
                                    <td class="text-end text-success">+<?= (int)$h['quantity'] ?></td>
                                    <td class="text-end">$<?= number_format($h['cost_price'], 2) ?></td>
                                    <td><?= htmlspecialchars($h['reference_no']) ?></td>
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

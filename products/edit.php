<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php?msg=" . urlencode("Product not found."));
    exit();
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
$suppliers  = $pdo->query("SELECT * FROM suppliers ORDER BY company_name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku            = trim($_POST['sku'] ?? '');
    $name           = trim($_POST['product_name'] ?? '');
    $category_id    = $_POST['category_id'] ?: null;
    $supplier_id    = $_POST['supplier_id'] ?: null;
    $cost_price     = (float)($_POST['cost_price'] ?? 0);
    $selling_price  = (float)($_POST['selling_price'] ?? 0);
    $minimum_stock  = (int)($_POST['minimum_stock'] ?? 0);
    $barcode        = trim($_POST['barcode'] ?? '');
    $status         = $_POST['status'] ?? 'Active';
    $image          = $product['image'];

    if ($sku === '' || $name === '') {
        $error = 'SKU and Product Name are required.';
    } else {
        $check = $pdo->prepare("SELECT product_id FROM products WHERE sku = ? AND product_id != ?");
        $check->execute([$sku, $id]);
        if ($check->fetch()) {
            $error = 'This SKU is already used by another product.';
        }
    }

    if (!$error && !empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $filename = 'product_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $filename)) {
                $image = $filename;
            }
        } else {
            $error = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
        }
    }

    if (!$error) {
        // Note: stock_qty is intentionally NOT edited here — use Stock In / Stock Out / Adjustment instead
        $upd = $pdo->prepare("UPDATE products SET category_id=?, supplier_id=?, sku=?, product_name=?, cost_price=?, selling_price=?, minimum_stock=?, barcode=?, image=?, status=? WHERE product_id=?");
        $upd->execute([$category_id, $supplier_id, $sku, $name, $cost_price, $selling_price, $minimum_stock, $barcode, $image, $status, $id]);
        header("Location: index.php?msg=" . urlencode("Product updated successfully."));
        exit();
    }
}

$page_title = 'Edit Product';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-pencil-outline"></i> Edit Product</h3></div>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">SKU / Product Code</label>
                        <input type="text" name="sku" class="form-control" required
                               value="<?= htmlspecialchars($_POST['sku'] ?? $product['sku']) ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control" required
                               value="<?= htmlspecialchars($_POST['product_name'] ?? $product['product_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id'] ?>" <?= $c['category_id'] == $product['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['supplier_id'] ?>" <?= $s['supplier_id'] == $product['supplier_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cost Price</label>
                        <input type="number" step="0.01" min="0" name="cost_price" class="form-control" value="<?= htmlspecialchars($_POST['cost_price'] ?? $product['cost_price']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" min="0" name="selling_price" class="form-control" value="<?= htmlspecialchars($_POST['selling_price'] ?? $product['selling_price']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Current Stock</label>
                        <input type="number" class="form-control" value="<?= (int)$product['stock_qty'] ?>" disabled>
                        <div class="form-text">Use Stock In / Out / Adjustment to change this.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Minimum Stock</label>
                        <input type="number" min="0" name="minimum_stock" class="form-control" value="<?= htmlspecialchars($_POST['minimum_stock'] ?? $product['minimum_stock']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" value="<?= htmlspecialchars($_POST['barcode'] ?? $product['barcode']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" <?= $product['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= $product['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($product['image']): ?>
                            <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" width="50" class="mt-2 rounded">
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-brand"><i class="mdi mdi-content-save-outline"></i> Update Product</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

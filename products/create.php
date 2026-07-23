<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

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
    $stock_qty      = (int)($_POST['stock_qty'] ?? 0);
    $minimum_stock  = (int)($_POST['minimum_stock'] ?? 0);
    $barcode        = trim($_POST['barcode'] ?? '');
    $status         = $_POST['status'] ?? 'Active';
    $image          = null;

    if ($sku === '' || $name === '') {
        $error = 'SKU and Product Name are required.';
    } else {
        $check = $pdo->prepare("SELECT product_id FROM products WHERE sku = ?");
        $check->execute([$sku]);
        if ($check->fetch()) {
            $error = 'This SKU already exists.';
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
        $stmt = $pdo->prepare("INSERT INTO products
            (category_id, supplier_id, sku, product_name, cost_price, selling_price, stock_qty, minimum_stock, barcode, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $supplier_id, $sku, $name, $cost_price, $selling_price, $stock_qty, $minimum_stock, $barcode, $image, $status]);
        header("Location: index.php?msg=" . urlencode("Product '$name' added successfully."));
        exit();
    }
}

$page_title = 'Add Product';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-plus-box-outline"></i> Add Product</h3></div>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">SKU / Product Code</label>
                        <input type="text" name="sku" class="form-control" required value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control" required value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cost Price</label>
                        <input type="number" step="0.01" min="0" name="cost_price" class="form-control" value="<?= htmlspecialchars($_POST['cost_price'] ?? '0') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" min="0" name="selling_price" class="form-control" value="<?= htmlspecialchars($_POST['selling_price'] ?? '0') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Current Stock</label>
                        <input type="number" min="0" name="stock_qty" class="form-control" value="<?= htmlspecialchars($_POST['stock_qty'] ?? '0') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Minimum Stock</label>
                        <input type="number" min="0" name="minimum_stock" class="form-control" value="<?= htmlspecialchars($_POST['minimum_stock'] ?? '0') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" value="<?= htmlspecialchars($_POST['barcode'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-brand"><i class="mdi mdi-content-save-outline"></i> Save Product</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

$search = trim($_GET['q'] ?? '');
$sql = "SELECT p.*, c.category_name, s.company_name
        FROM products p
        LEFT JOIN categories c ON c.category_id = p.category_id
        LEFT JOIN suppliers s ON s.supplier_id = p.supplier_id";
$params = [];
if ($search !== '') {
    $sql .= " WHERE p.product_name LIKE ? OR p.sku LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$sql .= " ORDER BY p.product_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$page_title = 'Product Management';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center page-heading">
            <h3><i class="mdi mdi-package-variant-closed"></i> Product Management</h3>
            <a href="create.php" class="btn btn-brand"><i class="mdi mdi-plus"></i> Add Product</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible-auto"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="mb-3 d-flex gap-2" style="max-width:350px;">
                    <input type="text" name="q" class="form-control" placeholder="Search by name or SKU..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-brand"><i class="mdi mdi-magnify"></i></button>
                </form>

                <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Image</th><th>SKU</th><th>Product Name</th><th>Category</th><th>Supplier</th>
                            <th class="text-end">Cost</th><th class="text-end">Price</th><th class="text-end">Stock</th>
                            <th>Status</th><th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No products found.</td></tr>
                    <?php else: foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <img src="<?= $p['image'] ? '../uploads/' . htmlspecialchars($p['image']) : 'https://placehold.co/40x40/e6f6f1/1D9E75?text=No+Img' ?>"
                                     width="40" height="40" style="object-fit:cover;border-radius:6px;">
                            </td>
                            <td><?= htmlspecialchars($p['sku']) ?></td>
                            <td><?= htmlspecialchars($p['product_name']) ?></td>
                            <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($p['company_name'] ?? '—') ?></td>
                            <td class="text-end">$<?= number_format($p['cost_price'], 2) ?></td>
                            <td class="text-end">$<?= number_format($p['selling_price'], 2) ?></td>
                            <td class="text-end">
                                <?php if ($p['stock_qty'] <= 0): ?>
                                    <span class="badge badge-out"><?= (int)$p['stock_qty'] ?></span>
                                <?php elseif ($p['stock_qty'] <= $p['minimum_stock']): ?>
                                    <span class="badge badge-low"><?= (int)$p['stock_qty'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-ok"><?= (int)$p['stock_qty'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= $p['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                            <td class="text-end">
                                <a href="edit.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-brand"><i class="mdi mdi-pencil-outline"></i></a>
                                <form action="delete.php" method="POST" class="d-inline js-delete-form" data-name="<?= htmlspecialchars($p['product_name']) ?>">
                                    <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete-outline"></i></button>
                                </form>
                            </td>
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

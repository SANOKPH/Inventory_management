<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_name LIKE ? OR description LIKE ? ORDER BY category_id DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_id DESC");
}
$categories = $stmt->fetchAll();

$page_title = 'Category Management';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center page-heading">
            <h3><i class="mdi mdi-shape-outline"></i> Category Management</h3>
            <a href="create.php" class="btn btn-brand"><i class="mdi mdi-plus"></i> Add Category</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible-auto"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="mb-3 d-flex gap-2" style="max-width:350px;">
                    <input type="text" name="q" class="form-control" placeholder="Search category..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-brand"><i class="mdi mdi-magnify"></i></button>
                </form>

                <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>#</th><th>Category Name</th><th>Description</th><th>Created</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No categories found.</td></tr>
                    <?php else: foreach ($categories as $c): ?>
                        <tr>
                            <td><?= (int)$c['category_id'] ?></td>
                            <td><?= htmlspecialchars($c['category_name']) ?></td>
                            <td><?= htmlspecialchars($c['description']) ?></td>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($c['created_at']))) ?></td>
                            <td class="text-end">
                                <a href="edit.php?id=<?= $c['category_id'] ?>" class="btn btn-sm btn-outline-brand"><i class="mdi mdi-pencil-outline"></i></a>
                                <form action="delete.php" method="POST" class="d-inline js-delete-form" data-name="<?= htmlspecialchars($c['category_name']) ?>">
                                    <input type="hidden" name="category_id" value="<?= $c['category_id'] ?>">
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

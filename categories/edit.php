<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    header("Location: index.php?msg=" . urlencode("Category not found."));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        $upd = $pdo->prepare("UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?");
        $upd->execute([$name, $desc, $id]);
        header("Location: index.php?msg=" . urlencode("Category updated successfully."));
        exit();
    }
}

$page_title = 'Edit Category';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-pencil-outline"></i> Edit Category</h3></div>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card shadow-sm" style="max-width:600px;">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name" class="form-control" required
                               value="<?= htmlspecialchars($_POST['category_name'] ?? $category['category_name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? $category['description']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-brand"><i class="mdi mdi-content-save-outline"></i> Update</button>
                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

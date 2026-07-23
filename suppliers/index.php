<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager']);

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE company_name LIKE ? OR contact_person LIKE ? ORDER BY supplier_id DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY supplier_id DESC");
}
$suppliers = $stmt->fetchAll();

$page_title = 'Supplier Management';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center page-heading">
            <h3><i class="mdi mdi-truck-outline"></i> Supplier Management</h3>
            <a href="create.php" class="btn btn-brand"><i class="mdi mdi-plus"></i> Add Supplier</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible-auto"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="mb-3 d-flex gap-2" style="max-width:350px;">
                    <input type="text" name="q" class="form-control" placeholder="Search supplier..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-brand"><i class="mdi mdi-magnify"></i></button>
                </form>

                <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>#</th><th>Company</th><th>Contact Person</th><th>Phone</th><th>Email</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($suppliers)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No suppliers found.</td></tr>
                    <?php else: foreach ($suppliers as $s): ?>
                        <tr>
                            <td><?= (int)$s['supplier_id'] ?></td>
                            <td><?= htmlspecialchars($s['company_name']) ?></td>
                            <td><?= htmlspecialchars($s['contact_person']) ?></td>
                            <td><?= htmlspecialchars($s['phone']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td class="text-end">
                                <a href="edit.php?id=<?= $s['supplier_id'] ?>" class="btn btn-sm btn-outline-brand"><i class="mdi mdi-pencil-outline"></i></a>
                                <form action="delete.php" method="POST" class="d-inline js-delete-form" data-name="<?= htmlspecialchars($s['company_name']) ?>">
                                    <input type="hidden" name="supplier_id" value="<?= $s['supplier_id'] ?>">
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

<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header("Location: index.php?msg=" . urlencode("Supplier not found."));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company_name'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($company === '') {
        $error = 'Company name is required.';
    } else {
        $upd = $pdo->prepare("UPDATE suppliers SET company_name=?, contact_person=?, phone=?, email=?, address=? WHERE supplier_id=?");
        $upd->execute([$company, $contact, $phone, $email, $address, $id]);
        header("Location: index.php?msg=" . urlencode("Supplier updated successfully."));
        exit();
    }
}

$page_title = 'Edit Supplier';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-pencil-outline"></i> Edit Supplier</h3></div>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card shadow-sm" style="max-width:650px;">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" required
                               value="<?= htmlspecialchars($_POST['company_name'] ?? $supplier['company_name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control"
                               value="<?= htmlspecialchars($_POST['contact_person'] ?? $supplier['contact_person']) ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? $supplier['phone']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $supplier['email']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($_POST['address'] ?? $supplier['address']) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-brand"><i class="mdi mdi-content-save-outline"></i> Update</button>
                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

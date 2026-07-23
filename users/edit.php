<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: index.php?msg=" . urlencode("User not found."));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $role      = $_POST['role'] ?? $user['role'];
    $status    = $_POST['status'] ?? $user['status'];
    $new_password = $_POST['new_password'] ?? '';

    if ($full_name === '' || $email === '') {
        $error = 'Full name and email are required.';
    } else {
        if ($new_password !== '') {
            if (strlen($new_password) < 6) {
                $error = 'New password must be at least 6 characters.';
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?")->execute([$hash, $id]);
            }
        }

        if (!$error) {
            $upd = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, status=? WHERE user_id=?");
            $upd->execute([$full_name, $email, $role, $status, $id]);
            header("Location: index.php?msg=" . urlencode("User updated successfully."));
            exit();
        }
    }
}

$page_title = 'Edit User';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-pencil-outline"></i> Edit User</h3></div>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card shadow-sm" style="max-width:600px;">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required
                               value="<?= htmlspecialchars($_POST['full_name'] ?? $user['full_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <?php foreach (['Admin','Manager','Inventory','Cashier','Viewer'] as $r): ?>
                                <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" <?= $user['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= $user['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reset Password <small class="text-muted">(optional)</small></label>
                        <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-brand"><i class="mdi mdi-content-save-outline"></i> Update User</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

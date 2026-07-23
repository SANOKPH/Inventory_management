<?php
require_once __DIR__ . '/../middleware/auth.php';

$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $image     = $user['profile_image'];

    if (!empty($_FILES['profile_image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $target = __DIR__ . '/../uploads/' . $filename;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                $image = $filename;
            }
        } else {
            $error = 'Only JPG, JPEG, PNG, or GIF images are allowed.';
        }
    }

    if ($full_name === '' || $email === '') {
        $error = 'Full name and email are required.';
    }

    if (!$error) {
        $upd = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, profile_image = ? WHERE user_id = ?");
        $upd->execute([$full_name, $email, $image, $_SESSION['user_id']]);
        $_SESSION['full_name'] = $full_name;
        $success = 'Profile updated successfully.';
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}

$page_title = 'My Profile';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="page-heading"><h3><i class="mdi mdi-account-circle-outline"></i> My Profile</h3></div>

        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="card shadow-sm" style="max-width:600px;">
            <div class="card-body text-center">
                <img src="<?= $user['profile_image'] ? '../uploads/' . htmlspecialchars($user['profile_image']) : 'https://ui-avatars.com/api/?background=1D9E75&color=fff&name=' . urlencode($user['full_name']) ?>"
                     class="rounded-circle mb-3" width="90" height="90" style="object-fit:cover;">

                <form method="POST" enctype="multipart/form-data" class="text-start">
                    <div class="mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" disabled>
                    </div>
                    <button type="submit" class="btn btn-brand"><i class="mdi mdi-content-save-outline"></i> Save Changes</button>
                    <a href="change_password.php" class="btn btn-outline-secondary">Change Password</a>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Public self-registration. New accounts default to the 'Viewer' role
// and 'Active' status. An Admin can later upgrade someone's role/status
// directly from the users table (a full "manage users" screen can be
// added later if you want role changes done from the UI).
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard/index.php");
    exit();
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if ($full_name === '' || $username === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password and Confirm Password do not match.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $errors[] = 'Username or Email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role, status)
                                VALUES (?, ?, ?, ?, 'Viewer', 'Active')");
        $stmt->execute([$full_name, $username, $email, $hash]);
        $success = 'Account created successfully! You can now log in.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | Inventory System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper" style="max-width:460px;">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="mdi mdi-cube-outline"></span>
            <h1>Create an Account</h1>
            <p>Sign up to start managing your inventory</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2"><?= htmlspecialchars($success) ?></div>
            <a href="login.php" class="btn btn-brand w-100">Go to Login</a>
        <?php else: ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                    <input type="text" name="full_name" class="form-control" required
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="mdi mdi-account-circle-outline"></i></span>
                    <input type="text" name="username" class="form-control" required
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
                    <input type="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="mdi mdi-lock-check-outline"></i></span>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-brand w-100"><i class="mdi mdi-account-plus-outline"></i> Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Login</a>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

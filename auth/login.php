<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard/index.php");
    exit();
}

$error = '';

if (isset($_GET['timeout'])) {
    $error = 'Your session expired due to inactivity. Please log in again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identity === '' || $password === '') {
        $error = 'Username/Email and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Incorrect username/email or password.';
        } elseif ($user['status'] !== 'Active') {
            $error = 'Your account is inactive. Contact the administrator.';
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Incorrect username/email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']       = $user['user_id'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['full_name']     = $user['full_name'];
            $_SESSION['last_activity'] = time();

            // Log the login
            $log = $pdo->prepare("INSERT INTO login_logs (user_id, login_time, ip_address, device)
                                   VALUES (:uid, NOW(), :ip, :device)");
            $log->execute([
                'uid'    => $user['user_id'],
                'ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
                'device' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);
            $_SESSION['log_id'] = $pdo->lastInsertId();

            header("Location: " . BASE_URL . "dashboard/index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Inventory System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="mdi mdi-cube-outline"></span>
            <h1>Inventory System</h1>
            <p>Sign in to manage your stock</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                    <input type="text" name="identity" class="form-control" required
                           value="<?= htmlspecialchars($_POST['identity'] ?? '') ?>" autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="mdi mdi-lock-outline"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>
            <button type="submit" class="btn btn-brand w-100">Login</button>
        </form>

        <div class="auth-footer">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
        <div class="auth-footer mt-2">
            Don't have an account? <a href="register.php">Register</a>
        </div>
        <p class="text-muted small text-center mt-3 mb-0">Default admin: <b>admin</b> / <b>Admin@123</b></p>
    </div>
</div>
</body>
</html>

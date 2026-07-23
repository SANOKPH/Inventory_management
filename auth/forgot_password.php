<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a reset token + OTP-style code (valid 30 minutes)
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $upd = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE user_id = ?");
        $upd->execute([$token, $expires, $user['user_id']]);

        // In production: email the reset link, e.g. reset_password.php?token=$token
        // mail($user['email'], 'Password Reset', "Reset link: reset_password.php?token=$token");

        $message = "If this email exists in our system, a reset link has been sent. "
                 . "(Demo mode — link: reset_password.php?token=$token)";
    } else {
        // Same generic message to avoid leaking which emails exist
        $message = "If this email exists in our system, a reset link has been sent.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password | Inventory System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="mdi mdi-lock-reset"></span>
            <h1>Forgot Password</h1>
            <p>Enter your email to receive a reset link</p>
        </div>

        <?php if ($message): ?><div class="alert alert-info small"><?= htmlspecialchars($message) ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-brand w-100">Send Reset Link</button>
        </form>
        <div class="auth-footer">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</div>
</body>
</html>

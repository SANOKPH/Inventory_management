<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (isset($_SESSION['log_id'])) {
    $stmt = $pdo->prepare("UPDATE login_logs SET logout_time = NOW() WHERE log_id = ?");
    $stmt->execute([$_SESSION['log_id']]);
}

session_unset();
session_destroy();
header("Location: " . BASE_URL . "auth/login.php");
exit();

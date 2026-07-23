<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['user_id'] ?? 0);

    if ($id === (int)$_SESSION['user_id']) {
        header("Location: index.php?msg=" . urlencode("You cannot delete your own account."));
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=" . urlencode("User deleted successfully."));
    } catch (PDOException $e) {
        header("Location: index.php?msg=" . urlencode("Cannot delete: user has related activity history (e.g. login logs)."));
    }
    exit();
}
header("Location: index.php");
exit();

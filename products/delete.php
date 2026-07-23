<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['product_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=" . urlencode("Product deleted successfully."));
    } catch (PDOException $e) {
        header("Location: index.php?msg=" . urlencode("Cannot delete: product has related stock transaction history."));
    }
    exit();
}
header("Location: index.php");
exit();

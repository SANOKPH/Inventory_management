<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager', 'Inventory']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['category_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=" . urlencode("Category deleted successfully."));
    } catch (PDOException $e) {
        header("Location: index.php?msg=" . urlencode("Cannot delete: category is used by existing products."));
    }
    exit();
}
header("Location: index.php");
exit();

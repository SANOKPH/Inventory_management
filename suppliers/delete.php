<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin', 'Manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['supplier_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=" . urlencode("Supplier deleted successfully."));
    } catch (PDOException $e) {
        header("Location: index.php?msg=" . urlencode("Cannot delete: supplier is linked to existing products."));
    }
    exit();
}
header("Location: index.php");
exit();

<?php
/**
 * Database configuration (PDO)
 * Update these credentials to match your local environment (e.g. Laragon/XAMPP).
 */
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'inventory_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL of the project — adjust if you deploy in a subfolder
define('BASE_URL', '/inventory_system/');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Auto-logout after 30 minutes of inactivity
define('SESSION_TIMEOUT', 1800);

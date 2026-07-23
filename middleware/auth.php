<?php
/**
 * Include this at the top of every protected page.
 * Ensures the user is logged in and enforces the inactivity timeout.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// Inactivity timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "auth/login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

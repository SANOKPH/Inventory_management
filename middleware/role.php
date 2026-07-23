<?php
/**
 * Restrict a page to specific roles.
 * Usage (after including middleware/auth.php):
 *   require_once __DIR__ . '/../middleware/role.php';
 *   allow_roles(['Admin', 'Manager']);
 */
function allow_roles(array $roles): void
{
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center;">
                <h2>403 — Access Denied</h2>
                <p>You do not have permission to view this page.</p>
                <a href="' . BASE_URL . 'dashboard/index.php">Back to Dashboard</a>
             </div>');
    }
}

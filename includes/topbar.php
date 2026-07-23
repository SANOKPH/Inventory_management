<?php
$base = defined('BASE_URL') ? BASE_URL : '/inventory_system/';
?>
<header class="topbar">
    <button class="sidebar-toggle d-lg-none" id="sidebarToggle"><i class="mdi mdi-menu"></i></button>
    <div class="topbar-title"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></div>

    <div class="topbar-right">
        <div class="dropdown">
            <a class="user-menu dropdown-toggle" href="#" data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?background=1D9E75&color=fff&name=<?= urlencode($_SESSION['full_name'] ?? 'U') ?>"
                     class="rounded-circle" width="34" height="34">
                <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></span>
                <small class="text-muted d-none d-md-inline">(<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)</small>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= $base ?>auth/profile.php"><i class="mdi mdi-account-outline me-2"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="<?= $base ?>auth/change_password.php"><i class="mdi mdi-lock-outline me-2"></i>Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= $base ?>auth/logout.php"><i class="mdi mdi-logout me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</header>

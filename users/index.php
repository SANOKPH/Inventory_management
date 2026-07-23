<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
allow_roles(['Admin']);

$users = $pdo->query("SELECT * FROM users ORDER BY user_id DESC")->fetchAll();

$page_title = 'Manage Users';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center page-heading">
            <h3><i class="mdi mdi-account-multiple-outline"></i> Manage Users</h3>
            <a href="create.php" class="btn btn-brand"><i class="mdi mdi-plus"></i> Add User</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible-auto"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>#</th><th>Full Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int)$u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span></td>
                            <td><span class="badge <?= $u['status'] === 'Active' ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($u['created_at']))) ?></td>
                            <td class="text-end">
                                <a href="edit.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-brand"><i class="mdi mdi-pencil-outline"></i></a>
                                <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                <form action="delete.php" method="POST" class="d-inline js-delete-form" data-name="<?= htmlspecialchars($u['username']) ?>">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete-outline"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

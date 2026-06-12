<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Блокировка/разблокировка пользователя
if (isset($_GET['toggle_block'])) {
    $userId = $_GET['toggle_block'];
    $stmt = $pdo->prepare("UPDATE Users SET IsBlocked = NOT IsBlocked WHERE Id = ?");
    $stmt->execute([$userId]);
    header('Location: users.php');
    exit();
}

// Удаление пользователя
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM Users WHERE Id = ?");
    $stmt->execute([$userId]);
    header('Location: users.php');
    exit();
}

$users = $pdo->query("SELECT u.*, r.Name as RoleName FROM Users u JOIN Roles r ON u.RoleId = r.Id ORDER BY u.CreatedAt DESC");
include '../includes/header.php';
?>

<h2>Управление пользователями</h2>
<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr><th>ID</th><th>Логин</th><th>ФИО</th><th>Email</th><th>Телефон</th><th>Роль</th><th>Статус</th><th>Действия</th></tr>
    </thead>
    <tbody>
        <?php while($user = $users->fetch()): ?>
        <tr>
            <td><?= $user['Id'] ?></td>
            <td><?= $user['Login'] ?></td>
            <td><?= $user['FullName'] ?></td>
            <td><?= $user['Email'] ?></td>
            <td><?= $user['Phone'] ?></td>
            <td><?= $user['RoleName'] ?></td>
            <td>
                <?php if($user['IsBlocked']): ?>
                    <span class="badge bg-danger">Заблокирован</span>
                <?php else: ?>
                    <span class="badge bg-success">Активен</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="?toggle_block=<?= $user['Id'] ?>" class="btn btn-sm btn-warning">
                    <?= $user['IsBlocked'] ? 'Разблокировать' : 'Заблокировать' ?>
                </a>
                <a href="?delete=<?= $user['Id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить пользователя?')">Удалить</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>

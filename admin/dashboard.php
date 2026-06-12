<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Статистика
$totalOrders = $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM Users WHERE RoleId = 2")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(TotalPrice) FROM Orders WHERE StatusId = 3")->fetchColumn();
$activeOrders = $pdo->query("SELECT COUNT(*) FROM Orders WHERE StatusId IN (1,2)")->fetchColumn();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2>Панель администратора</h2>
        <hr>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Всего заказов</h5>
                <h2><?= $totalOrders ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Пользователей</h5>
                <h2><?= $totalUsers ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Активных заказов</h5>
                <h2><?= $activeOrders ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Выручка</h5>
                <h2><?= number_format($totalRevenue, 0) ?> ₽</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Быстрые действия</h5>
            </div>
            <div class="card-body">
                <a href="users.php" class="btn btn-primary m-1">👥 Управление пользователями</a>
                <a href="vehicles.php" class="btn btn-success m-1">🚚 Управление авто</a>
                <a href="orders_manage.php" class="btn btn-warning m-1">📦 Управление заказами</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Последние заказы</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr><th>ID</th><th>Клиент</th><th>Сумма</th><th>Статус</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $recentOrders = $pdo->query("SELECT o.*, os.Name as StatusName FROM Orders o JOIN OrderStatuses os ON o.StatusId = os.Id ORDER BY o.OrderDate DESC LIMIT 5");
                        while($order = $recentOrders->fetch()): ?>
                        <tr>
                            <td><?= $order['Id'] ?></td>
                            <td><?= $order['CustomerName'] ?></td>
                            <td><?= number_format($order['TotalPrice'], 2) ?> ₽</td>
                            <td><?= $order['StatusName'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

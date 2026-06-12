<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$orderId = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newWeight = $_POST['weight'];
    
    // Получаем старый вес
    $oldWeight = $pdo->prepare("SELECT WeightKg, VehicleId FROM Orders WHERE Id = ? AND UserId = ?");
    $oldWeight->execute([$orderId, $_SESSION['user_id']]);
    $order = $oldWeight->fetch();
    
    if ($order) {
        // Обновляем вес заказа
        $update = $pdo->prepare("UPDATE Orders SET WeightKg = ? WHERE Id = ?");
        $update->execute([$newWeight, $orderId]);
        
        // Записываем историю
        $history = $pdo->prepare("INSERT INTO OrderWeightHistory (OrderId, OldWeightKg, NewWeightKg, ChangedByUserId) VALUES (?, ?, ?, ?)");
        $history->execute([$orderId, $order['WeightKg'], $newWeight, $_SESSION['user_id']]);
        
        header('Location: view.php?id=' . $orderId);
        exit();
    }
}

$orderInfo = $pdo->prepare("SELECT * FROM Orders WHERE Id = ? AND UserId = ?");
$orderInfo->execute([$orderId, $_SESSION['user_id']]);
$order = $orderInfo->fetch();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <h4>Изменение веса заказа №<?= $order['Id'] ?></h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Текущий вес: <?= $order['WeightKg'] ?> кг</label>
                        <input type="number" name="weight" class="form-control" value="<?= $order['WeightKg'] ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="view.php?id=<?= $orderId ?>" class="btn btn-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

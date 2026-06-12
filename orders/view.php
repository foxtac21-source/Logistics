<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$orderId = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT o.*, os.Name as StatusName, r.StartPoint, r.EndPoint, r.DistanceKm,
           v.Brand, v.Model, v.LicensePlate,
           pm.Name as PaymentName,
           a1.AddressLine as FromAddress, a1.City as FromCity,
           a2.AddressLine as ToAddress, a2.City as ToCity
    FROM Orders o
    JOIN OrderStatuses os ON o.StatusId = os.Id
    JOIN Routes r ON o.RouteId = r.Id
    JOIN Vehicles v ON o.VehicleId = v.Id
    JOIN PaymentMethods pm ON o.PaymentMethodId = pm.Id
    JOIN CustomerAddresses a1 ON o.AddressFromId = a1.Id
    JOIN CustomerAddresses a2 ON o.AddressToId = a2.Id
    WHERE o.Id = ? AND o.UserId = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ../user/profile.php');
    exit();
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Заказ №<?= $order['Id'] ?></h3>
        <span class="badge bg-<?= $order['StatusName'] == 'Доставлен' ? 'success' : 'primary' ?>"><?= $order['StatusName'] ?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Маршрут</h5>
                <p><strong>Откуда:</strong> <?= $order['FromCity'] ?>, <?= $order['FromAddress'] ?></p>
                <p><strong>Куда:</strong> <?= $order['ToCity'] ?>, <?= $order['ToAddress'] ?></p>
                <p><strong>Расстояние:</strong> <?= $order['DistanceKm'] ?> км</p>
                
                <h5 class="mt-3">Груз</h5>
                <p><strong>Вес:</strong> <?= $order['WeightKg'] ?> кг</p>
                
                <h5 class="mt-3">Транспорт</h5>
                <p><strong>Автомобиль:</strong> <?= $order['Brand'] ?> <?= $order['Model'] ?> (<?= $order['LicensePlate'] ?>)</p>
            </div>
            
            <div class="col-md-6">
                <h5>Финансы</h5>
                <p><strong>Базовая цена:</strong> <?= number_format($order['BasePrice'], 2) ?> ₽</p>
                <p><strong>Скидка:</strong> <?= $order['DiscountPercent'] ?>%</p>
                <p><strong>Доставка:</strong> <?= number_format($order['DeliveryPrice'], 2) ?> ₽</p>
                <p><strong>Итого:</strong> <?= number_format($order['TotalPrice'], 2) ?> ₽</p>
                <p><strong>Оплата:</strong> <?= $order['PaymentName'] ?></p>
                
                <h5 class="mt-3">Отслеживание</h5>
                <p><strong>Статус:</strong> <?= $order['StatusName'] ?></p>
                <?php if($order['TrackingStatus']): ?>
                    <p><strong>Местоположение:</strong> <?= $order['TrackingStatus'] ?></p>
                <?php endif; ?>
                
                <?php if($order['StatusId'] == 1): ?>
                    <a href="edit_weight.php?id=<?= $order['Id'] ?>" class="btn btn-warning">Изменить вес</a>
                <?php endif; ?>
                
                <a href="../invoices/generate.php?order_id=<?= $order['Id'] ?>" class="btn btn-success">Скачать накладную</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

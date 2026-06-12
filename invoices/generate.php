<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$orderId = $_GET['order_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT o.*, u.FullName as UserName, u.Email, u.Phone,
           r.StartPoint, r.EndPoint, r.DistanceKm,
           v.Brand, v.Model, v.LicensePlate,
           a1.AddressLine as FromAddress, a1.City as FromCity,
           a2.AddressLine as ToAddress, a2.City as ToCity
    FROM Orders o
    JOIN Users u ON o.UserId = u.Id
    JOIN Routes r ON o.RouteId = r.Id
    JOIN Vehicles v ON o.VehicleId = v.Id
    JOIN CustomerAddresses a1 ON o.AddressFromId = a1.Id
    JOIN CustomerAddresses a2 ON o.AddressToId = a2.Id
    WHERE o.Id = ? AND o.UserId = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    die("Заказ не найден");
}

// Простая HTML-накладная
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Накладная №<?= $order['Id'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .company { font-size: 24px; font-weight: bold; color: #333; }
        .document { font-size: 20px; margin-top: 10px; }
        .info { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 20px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">ТрансКом - Грузоперевозки</div>
        <div class="document">ТОВАРНАЯ НАКЛАДНАЯ №<?= $order['Id'] ?></div>
        <div>от <?= date('d.m.Y', strtotime($order['OrderDate'])) ?></div>
    </div>
    
    <div class="info">
        <p><strong>Клиент:</strong> <?= $order['UserName'] ?></p>
        <p><strong>Телефон:</strong> <?= $order['Phone'] ?></p>
        <p><strong>Email:</strong> <?= $order['Email'] ?></p>
    </div>
    
    <table>
        <tr><th colspan="2">Информация о перевозке</th></tr>
        <tr><td>Маршрут</td><td><?= $order['StartPoint'] ?> → <?= $order['EndPoint'] ?></td></tr>
        <tr><td>Расстояние</td><td><?= $order['DistanceKm'] ?> км</td></tr>
        <tr><td>Адрес отправления</td><td><?= $order['FromCity'] ?>, <?= $order['FromAddress'] ?></td></tr>
        <tr><td>Адрес доставки</td><td><?= $order['ToCity'] ?>, <?= $order['ToAddress'] ?></td></tr>
        <tr><td>Вес груза</td><td><?= $order['WeightKg'] ?> кг</td></tr>
        <tr><td>Автомобиль</td><td><?= $order['Brand'] ?> <?= $order['Model'] ?> (<?= $order['LicensePlate'] ?>)</td></tr>
    </table>
    
    <table>
        <tr><th>Наименование</th><th>Сумма</th></tr>
        <tr><td>Базовая стоимость перевозки</td><td><?= number_format($order['BasePrice'], 2) ?> ₽</td></tr>
        <?php if($order['DiscountPercent'] > 0): ?>
        <tr><td>Скидка (<?= $order['DiscountPercent'] ?>%)</td><td>-<?= number_format($order['BasePrice'] * $order['DiscountPercent'] / 100, 2) ?> ₽</td></tr>
        <?php endif; ?>
        <tr><td>Доставка</td><td><?= number_format($order['DeliveryPrice'], 2) ?> ₽</td></tr>
        <tr><td><strong>ИТОГО</strong></td><td><strong><?= number_format($order['TotalPrice'], 2) ?> ₽</strong></td></tr>
    </table>
    
    <div class="footer">
        <p>Спасибо за выбор нашей компании!</p>
        <p>Телефон поддержки: 8-800-123-45-67</p>
    </div>
    
    <button onclick="window.print()" style="margin-top: 20px; padding: 10px 20px;">Распечатать накладную</button>
</body>
</html>

<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Добавление авто
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_vehicle'])) {
    $stmt = $pdo->prepare("INSERT INTO Vehicles (Brand, Model, LicensePlate, MaxLoadKg, Year, DriverId) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['brand'], $_POST['model'], $_POST['plate'], $_POST['maxload'], $_POST['year'], $_POST['driver_id'] ?: null]);
    header('Location: vehicles.php');
    exit();
}

// Удаление авто
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Vehicles WHERE Id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: vehicles.php');
    exit();
}

$vehicles = $pdo->query("SELECT v.*, d.FullName as DriverName FROM Vehicles v LEFT JOIN Drivers d ON v.DriverId = d.Id");
$drivers = $pdo->query("SELECT * FROM Drivers");
include '../includes/header.php';
?>

<h2>Управление автомобилями</h2>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addVehicleModal">+ Добавить автомобиль</button>

<table class="table table-bordered">
    <thead class="table-dark">
        <tr><th>ID</th><th>Марка</th><th>Модель</th><th>Госномер</th><th>Грузоподъемность</th><th>Водитель</th><th>Статус</th><th>Действия</th></tr>
    </thead>
    <tbody>
        <?php while($v = $vehicles->fetch()): ?>
        <tr>
            <td><?= $v['Id'] ?></td>
            <td><?= $v['Brand'] ?></td>
            <td><?= $v['Model'] ?></td>
            <td><?= $v['LicensePlate'] ?></td>
            <td><?= $v['MaxLoadKg'] ?> кг</td>
            <td><?= $v['DriverName'] ?? 'Не назначен' ?></td>
            <td><?= $v['IsAvailable'] ? '✅ Доступен' : '❌ Занят' ?></td>
            <td>
                <a href="?delete=<?= $v['Id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить авто?')">Удалить</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Modal добавления -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить автомобиль</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><input type="text" name="brand" placeholder="Марка" class="form-control" required></div>
                    <div class="mb-3"><input type="text" name="model" placeholder="Модель" class="form-control" required></div>
                    <div class="mb-3"><input type="text" name="plate" placeholder="Госномер" class="form-control" required></div>
                    <div class="mb-3"><input type="number" name="maxload" placeholder="Грузоподъемность (кг)" class="form-control" required></div>
                    <div class="mb-3"><input type="number" name="year" placeholder="Год выпуска" class="form-control"></div>
                    <div class="mb-3">
                        <select name="driver_id" class="form-control">
                            <option value="">Без водителя</option>
                            <?php while($d = $drivers->fetch()): ?>
                                <option value="<?= $d['Id'] ?>"><?= $d['FullName'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_vehicle" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

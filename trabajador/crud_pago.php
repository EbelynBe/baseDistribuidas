<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

function obtenerConexionPorId($id) {
    $usuario = "phpapp";
    $clave = "1234";

    if ($id >= 1 && $id <= 50) {
        return new PDO("mysql:host=192.168.101.8;port=3305;dbname=sistema_alquiler_vehiculos1;charset=utf8mb4", $usuario, $clave);
    } elseif ($id >= 51 && $id <= 100) {
        return new PDO("mysql:host=192.168.101.9;port=3306;dbname=sistema_alquiler_vehiculos2;charset=utf8mb4", $usuario, $clave);
    } else {
        throw new Exception("ID fuera de rango.");
    }
}

// ========== CRUD PAGO ==========

if (isset($_POST['agregar_pago'])) {
    try {
        $id = $_POST['id'];
        $pdo = obtenerConexionPorId($id);
        $stmt = $pdo->prepare("INSERT INTO pago (id, monto, factura_id, fecha_pago) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $_POST['monto'], $_POST['factura_id'], $_POST['fecha_pago']]);
        header("Location: crud_pago.php");
        exit;
    } catch (Exception $e) {
        $error_pago = $e->getMessage();
    }
}

if (isset($_GET['editar_pago']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("SELECT * FROM pago WHERE id = ?");
    $stmt->execute([$id]);
    $pago_editar = $stmt->fetch();
}

if (isset($_POST['actualizar_pago'])) {
    $id = $_POST['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("UPDATE pago SET monto = ?, factura_id = ?, fecha_pago = ? WHERE id = ?");
    $stmt->execute([$_POST['monto'], $_POST['factura_id'], $_POST['fecha_pago'], $id]);
    header("Location: crud_pago.php");
    exit;
}

if (isset($_GET['eliminar_pago']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("DELETE FROM pago WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: crud_pago.php");
    exit;
}

$pagos = [];
try {
    $pdo1 = obtenerConexionPorId(1);
    $pagos = array_merge($pagos, $pdo1->query("SELECT * FROM pago")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}

try {
    $pdo2 = obtenerConexionPorId(51);
    $pagos = array_merge($pagos, $pdo2->query("SELECT * FROM pago")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>CRUD Pago</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Gestión de Pagos</h2>

    <?php if (isset($error_pago)): ?>
        <div class="alert alert-danger"><?= $error_pago ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-1">
            <input type="number" name="id" class="form-control" placeholder="ID" required value="<?= $pago_editar['id'] ?? '' ?>" <?= isset($pago_editar) ? 'readonly' : '' ?>>
        </div>
        <div class="col-md-2">
            <input type="number" name="monto" class="form-control" step="0.01" placeholder="Monto" required value="<?= $pago_editar['monto'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="factura_id" class="form-control" placeholder="Factura ID" required value="<?= $pago_editar['factura_id'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="fecha_pago" class="form-control" required value="<?= $pago_editar['fecha_pago'] ?? '' ?>">
        </div>
        <div class="col-md-4">
            <?php if (isset($pago_editar)): ?>
                <button type="submit" name="actualizar_pago" class="btn btn-warning">Actualizar</button>
                <a href="crud_pago.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <button type="submit" name="agregar_pago" class="btn btn-success">Agregar</button>
            <?php endif; ?>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Monto</th>
                <th>Factura ID</th>
                <th>Fecha de Pago</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pagos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['monto'] ?></td>
                <td><?= $p['factura_id'] ?></td>
                <td><?= $p['fecha_pago'] ?></td>
                <td>
                    <a href="?editar_pago=1&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?eliminar_pago=1&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este pago?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</body>
</html>

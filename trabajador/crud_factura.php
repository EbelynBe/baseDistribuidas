<?php
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

// ========== CRUD FACTURA ==========

if (isset($_POST['agregar_factura'])) {
    try {
        $id = $_POST['id'];
        $pdo = obtenerConexionPorId($id);
        $stmt = $pdo->prepare("INSERT INTO factura (id, fecha_emision, alquiler_id) VALUES (?, ?, ?)");
        $stmt->execute([$id, $_POST['fecha_emision'], $_POST['alquiler_id']]);
        header("Location: crud_factura.php");
        exit;
    } catch (Exception $e) {
        $error_factura = $e->getMessage();
    }
}

if (isset($_GET['editar_factura']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("SELECT * FROM factura WHERE id = ?");
    $stmt->execute([$id]);
    $factura_editar = $stmt->fetch();
}

if (isset($_POST['actualizar_factura'])) {
    $id = $_POST['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("UPDATE factura SET fecha_emision = ?, alquiler_id = ? WHERE id = ?");
    $stmt->execute([$_POST['fecha_emision'], $_POST['alquiler_id'], $id]);
    header("Location: crud_factura.php");
    exit;
}

if (isset($_GET['eliminar_factura']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("DELETE FROM factura WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: crud_factura.php");
    exit;
}

$facturas = [];
try {
    $pdo1 = obtenerConexionPorId(1);
    $facturas = array_merge($facturas, $pdo1->query("SELECT * FROM factura")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}

try {
    $pdo2 = obtenerConexionPorId(51);
    $facturas = array_merge($facturas, $pdo2->query("SELECT * FROM factura")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>CRUD Factura</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Gestión de Facturas</h2>

    <?php if (isset($error_factura)): ?>
        <div class="alert alert-danger"><?= $error_factura ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-2">
            <input type="number" name="id" class="form-control" placeholder="ID Factura" required value="<?= $factura_editar['id'] ?? '' ?>" <?= isset($factura_editar) ? 'readonly' : '' ?>>
        </div>
        <div class="col-md-3">
            <input type="date" name="fecha_emision" class="form-control" required value="<?= $factura_editar['fecha_emision'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="number" name="alquiler_id" class="form-control" placeholder="ID Alquiler" required value="<?= $factura_editar['alquiler_id'] ?? '' ?>">
        </div>
        <div class="col-md-4">
            <?php if (isset($factura_editar)): ?>
                <button type="submit" name="actualizar_factura" class="btn btn-warning">Actualizar</button>
                <a href="crud_factura.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <button type="submit" name="agregar_factura" class="btn btn-success">Agregar</button>
            <?php endif; ?>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Fecha Emisión</th>
                <th>ID Alquiler</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($facturas as $f): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><?= $f['fecha_emision'] ?></td>
                <td><?= $f['alquiler_id'] ?></td>
                <td>
                    <a href="?editar_factura=1&id=<?= $f['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?eliminar_factura=1&id=<?= $f['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta factura?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</body>
</html>

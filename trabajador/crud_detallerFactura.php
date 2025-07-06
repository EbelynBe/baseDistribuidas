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

// ========== CRUD DETALLES_FACTURA ==========

if (isset($_POST['agregar_detalle'])) {
    try {
        $id = $_POST['id'];
        $pdo = obtenerConexionPorId($id);
        $stmt = $pdo->prepare("INSERT INTO detalles_factura (id, factura_id, descripcion, monto) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $_POST['factura_id'], $_POST['descripcion'], $_POST['monto']]);
        header("Location: crud_detalles_factura.php");
        exit;
    } catch (Exception $e) {
        $error_detalle = $e->getMessage();
    }
}

if (isset($_GET['editar_detalle']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("SELECT * FROM detalles_factura WHERE id = ?");
    $stmt->execute([$id]);
    $detalle_editar = $stmt->fetch();
}

if (isset($_POST['actualizar_detalle'])) {
    $id = $_POST['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("UPDATE detalles_factura SET factura_id = ?, descripcion = ?, monto = ? WHERE id = ?");
    $stmt->execute([$_POST['factura_id'], $_POST['descripcion'], $_POST['monto'], $id]);
    header("Location: crud_detalles_factura.php");
    exit;
}

if (isset($_GET['eliminar_detalle']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("DELETE FROM detalles_factura WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: crud_detalles_factura.php");
    exit;
}

$detalles = [];
try {
    $pdo1 = obtenerConexionPorId(1);
    $detalles = array_merge($detalles, $pdo1->query("SELECT * FROM detalles_factura")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}

try {
    $pdo2 = obtenerConexionPorId(51);
    $detalles = array_merge($detalles, $pdo2->query("SELECT * FROM detalles_factura")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>CRUD Detalles de Factura</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Gestión de Detalles de Factura</h2>

    <?php if (isset($error_detalle)): ?>
        <div class="alert alert-danger"><?= $error_detalle ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-1">
            <input type="number" name="id" class="form-control" placeholder="ID" required value="<?= $detalle_editar['id'] ?? '' ?>" <?= isset($detalle_editar) ? 'readonly' : '' ?>>
        </div>
        <div class="col-md-2">
            <input type="number" name="factura_id" class="form-control" placeholder="Factura ID" required value="<?= $detalle_editar['factura_id'] ?? '' ?>">
        </div>
        <div class="col-md-5">
            <input type="text" name="descripcion" class="form-control" placeholder="Descripción" required value="<?= $detalle_editar['descripcion'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="monto" class="form-control" step="0.01" placeholder="Monto" required value="<?= $detalle_editar['monto'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <?php if (isset($detalle_editar)): ?>
                <button type="submit" name="actualizar_detalle" class="btn btn-warning">Actualizar</button>
                <a href="crud_detalles_factura.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <button type="submit" name="agregar_detalle" class="btn btn-success">Agregar</button>
            <?php endif; ?>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Factura ID</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($detalles as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= $d['factura_id'] ?></td>
                <td><?= $d['descripcion'] ?></td>
                <td><?= $d['monto'] ?></td>
                <td>
                    <a href="?editar_detalle=1&id=<?= $d['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?eliminar_detalle=1&id=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este detalle?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</body>
</html>
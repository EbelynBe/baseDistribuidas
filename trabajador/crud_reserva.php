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

// ========== CRUD RESERVA ==========

if (isset($_POST['agregar_reserva'])) {
    try {
        $id = $_POST['id_reserva'];
        $pdo = obtenerConexionPorId($id);
        $stmt = $pdo->prepare("INSERT INTO reserva (id_reserva, hora, fecha, id_usuario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $_POST['hora'], $_POST['fecha'], $_POST['id_usuario']]);
        header("Location: crud_reserva.php");
        exit;
    } catch (Exception $e) {
        $error_reserva = $e->getMessage();
    }
}

if (isset($_GET['editar_reserva']) && isset($_GET['id_reserva'])) {
    $id = $_GET['id_reserva'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("SELECT * FROM reserva WHERE id_reserva = ?");
    $stmt->execute([$id]);
    $reserva_editar = $stmt->fetch();
}

if (isset($_POST['actualizar_reserva'])) {
    $id = $_POST['id_reserva'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("UPDATE reserva SET hora = ?, fecha = ?, id_usuario = ? WHERE id_reserva = ?");
    $stmt->execute([$_POST['hora'], $_POST['fecha'], $_POST['id_usuario'], $id]);
    header("Location: crud_reserva.php");
    exit;
}

if (isset($_GET['eliminar_reserva']) && isset($_GET['id_reserva'])) {
    $id = $_GET['id_reserva'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("DELETE FROM reserva WHERE id_reserva = ?");
    $stmt->execute([$id]);
    header("Location: crud_reserva.php");
    exit;
}

$reservas = [];
try {
    $pdo1 = obtenerConexionPorId(1);
    $reservas = array_merge($reservas, $pdo1->query("SELECT * FROM reserva")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}

try {
    $pdo2 = obtenerConexionPorId(51);
    $reservas = array_merge($reservas, $pdo2->query("SELECT * FROM reserva")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>CRUD Reserva</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Gestión de Reservas</h2>

    <?php if (isset($error_reserva)): ?>
        <div class="alert alert-danger"><?= $error_reserva ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-2">
            <input type="number" name="id_reserva" class="form-control" placeholder="ID Reserva" required value="<?= $reserva_editar['id_reserva'] ?? '' ?>" <?= $reserva_editar ? 'readonly' : '' ?>>
        </div>
        <div class="col-md-2">
            <input type="time" name="hora" class="form-control" placeholder="Hora" required value="<?= $reserva_editar['hora'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="fecha" class="form-control" required value="<?= $reserva_editar['fecha'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="number" name="id_usuario" class="form-control" placeholder="ID Usuario" required value="<?= $reserva_editar['id_usuario'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <?php if (isset($reserva_editar)): ?>
                <button type="submit" name="actualizar_reserva" class="btn btn-warning">Actualizar</button>
                <a href="crud_reserva.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <button type="submit" name="agregar_reserva" class="btn btn-success">Agregar</button>
            <?php endif; ?>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID Reserva</th>
                <th>Hora</th>
                <th>Fecha</th>
                <th>ID Usuario</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reservas as $r): ?>
            <tr>
                <td><?= $r['id_reserva'] ?></td>
                <td><?= $r['hora'] ?></td>
                <td><?= $r['fecha'] ?></td>
                <td><?= $r['id_usuario'] ?></td>
                <td>
                    <a href="?editar_reserva=1&id_reserva=<?= $r['id_reserva'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?eliminar_reserva=1&id_reserva=<?= $r['id_reserva'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta reserva?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</body>
</html>

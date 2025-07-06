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

// AGREGAR
if (isset($_POST['agregar_alquiler'])) {
    try {
        $id = $_POST['id'];
        $pdo = obtenerConexionPorId($id);
        $stmt = $pdo->prepare("INSERT INTO alquiler (id, vehiculo_id, usuario_id, fecha_inicio, fecha_fin, valor_alquiler_id, sede_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $_POST['vehiculo_id'], $_POST['usuario_id'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $_POST['valor_alquiler_id'], $_POST['sede_id']]);
        header("Location: crud_alquiler.php");
        exit;
    } catch (Exception $e) {
        $error_alquiler = $e->getMessage();
    }
}

// EDITAR
$alquiler_editar = null;
if (isset($_GET['editar_alquiler']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("SELECT * FROM alquiler WHERE id = ?");
    $stmt->execute([$id]);
    $alquiler_editar = $stmt->fetch();
}

// ACTUALIZAR
if (isset($_POST['actualizar_alquiler'])) {
    $id = $_POST['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("UPDATE alquiler SET vehiculo_id = ?, usuario_id = ?, fecha_inicio = ?, fecha_fin = ?, valor_alquiler_id = ?, sede_id = ? WHERE id = ?");
    $stmt->execute([
        $_POST['vehiculo_id'],
        $_POST['usuario_id'],
        $_POST['fecha_inicio'],
        $_POST['fecha_fin'],
        $_POST['valor_alquiler_id'],
        $_POST['sede_id'],
        $id
    ]);
    header("Location: crud_alquiler.php");
    exit;
}

// ELIMINAR
if (isset($_GET['eliminar_alquiler']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("DELETE FROM alquiler WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: crud_alquiler.php");
    exit;
}

// LISTADO
$alquileres = [];
try {
    $pdo1 = obtenerConexionPorId(1);
    $alquileres = array_merge($alquileres, $pdo1->query("SELECT * FROM alquiler")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}

try {
    $pdo2 = obtenerConexionPorId(51);
    $alquileres = array_merge($alquileres, $pdo2->query("SELECT * FROM alquiler")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>CRUD Alquiler</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Gestión de Alquileres</h2>

    <?php if (isset($error_alquiler)): ?>
        <div class="alert alert-danger"><?= $error_alquiler ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-1">
            <input type="number" name="id" class="form-control" placeholder="ID" required value="<?= $alquiler_editar['id'] ?? '' ?>" <?= isset($alquiler_editar) ? 'readonly' : '' ?>>
        </div>
        <div class="col-md-2">
            <input type="text" name="vehiculo_id" class="form-control" placeholder="Placa" required value="<?= $alquiler_editar['vehiculo_id'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="usuario_id" class="form-control" placeholder="ID Usuario" required value="<?= $alquiler_editar['usuario_id'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_inicio" class="form-control" required value="<?= $alquiler_editar['fecha_inicio'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_fin" class="form-control" required value="<?= $alquiler_editar['fecha_fin'] ?? '' ?>">
        </div>
        <div class="col-md-1">
            <input type="number" name="valor_alquiler_id" class="form-control" placeholder="Valor ID" required value="<?= $alquiler_editar['valor_alquiler_id'] ?? '' ?>">
        </div>
        <div class="col-md-1">
            <input type="number" name="sede_id" class="form-control" placeholder="Sede ID" required value="<?= $alquiler_editar['sede_id'] ?? '' ?>">
        </div>
        <div class="col-md-1">
            <?php if (isset($alquiler_editar)): ?>
                <button type="submit" name="actualizar_alquiler" class="btn btn-warning">Actualizar</button>
                <a href="crud_alquiler.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <button type="submit" name="agregar_alquiler" class="btn btn-success">Agregar</button>
            <?php endif; ?>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Vehículo</th>
                <th>Usuario</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Valor</th>
                <th>Sede</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($alquileres as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= $a['vehiculo_id'] ?></td>
                <td><?= $a['usuario_id'] ?></td>
                <td><?= $a['fecha_inicio'] ?></td>
                <td><?= $a['fecha_fin'] ?></td>
                <td><?= $a['valor_alquiler_id'] ?></td>
                <td><?= $a['sede_id'] ?></td>
                <td>
                    <a href="?editar_alquiler=1&id=<?= $a['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?eliminar_alquiler=1&id=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este alquiler?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</body>
</html>

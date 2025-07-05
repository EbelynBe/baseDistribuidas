<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

require '../conexion.php';


if (isset($_POST['agregar'])) {
    $placa = $_POST['placa'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $kilometraje = $_POST['kilometraje'];
    $fecha_adquisicion = $_POST['fecha_adquisicion'];

    $stmt = $pdo->prepare("INSERT INTO vehiculo (placa, marca, modelo, kilometraje, fecha_adquisicion) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$placa, $marca, $modelo, $kilometraje, $fecha_adquisicion]);
    header("Location: crud_vehiculos.php");
}


if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM vehiculo WHERE placa = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: crud_vehiculos.php");
}


$vehiculo_editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM vehiculo WHERE placa = ?");
    $stmt->execute([$_GET['editar']]);
    $vehiculo_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}


if (isset($_POST['actualizar'])) {
    $placa = $_POST['placa'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $kilometraje = $_POST['kilometraje'];
    $fecha_adquisicion = $_POST['fecha_adquisicion'];

    $stmt = $pdo->prepare("UPDATE vehiculo SET marca = ?, modelo = ?, kilometraje = ?, fecha_adquisicion = ? WHERE placa = ?");
    $stmt->execute([$marca, $modelo, $kilometraje, $fecha_adquisicion, $placa]);
    header("Location: crud_vehiculos.php");
}


$vehiculos = $pdo->query("SELECT * FROM vehiculo")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>CRUD Vehículos - Trabajador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Gestión de Vehículos</h2>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-2">
            <input type="text" name="placa" class="form-control" placeholder="Placa" required
                value="<?= $vehiculo_editar ? $vehiculo_editar['placa'] : '' ?>"
                <?= $vehiculo_editar ? 'readonly' : '' ?>>
        </div>
        <div class="col-md-2">
            <input type="text" name="marca" class="form-control" placeholder="Marca" required
                value="<?= $vehiculo_editar ? $vehiculo_editar['marca'] : '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="modelo" class="form-control" placeholder="Modelo" required
                value="<?= $vehiculo_editar ? $vehiculo_editar['modelo'] : '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="kilometraje" class="form-control" placeholder="Kilometraje" required
                value="<?= $vehiculo_editar ? $vehiculo_editar['kilometraje'] : '' ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_adquisicion" class="form-control" required
                value="<?= $vehiculo_editar ? $vehiculo_editar['fecha_adquisicion'] : '' ?>">
        </div>
        <div class="col-md-2">
            <?php if ($vehiculo_editar): ?>
                <button type="submit" name="actualizar" class="btn btn-warning">Actualizar</button>
                <a href="crud_vehiculos.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <button type="submit" name="agregar" class="btn btn-success">Agregar</button>
            <?php endif; ?>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Placa</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Kilometraje</th>
                <th>Fecha Adquisición</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vehiculos as $v): ?>
                <tr>
                    <td><?= $v['placa'] ?></td>
                    <td><?= $v['marca'] ?></td>
                    <td><?= $v['modelo'] ?></td>
                    <td><?= $v['kilometraje'] ?></td>
                    <td><?= $v['fecha_adquisicion'] ?></td>
                    <td>
                        <a href="?eliminar=<?= $v['placa'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este vehículo?')">Eliminar</a>
                        <a href="?editar=<?= $v['placa'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</body>
</html>

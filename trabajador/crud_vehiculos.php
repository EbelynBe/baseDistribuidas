<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario_id'] != 3) {
    header("Location: ../login.php");
    exit;
}


function obtenerEspacioDisponible() {
    $usuario = "phpapp";
    $clave = "1234";

    
    $pdo1 = new PDO("mysql:host=192.168.101.8;port=3305;dbname=sistema_alquiler_vehiculos1;charset=utf8mb4", $usuario, $clave);
    $stmt1 = $pdo1->query("SELECT MAX(numero) AS max_num FROM vehiculo");
    $max1 = $stmt1->fetch(PDO::FETCH_ASSOC)['max_num'] ?? 0;

    if ($max1 < 50) {
        return ['pdo' => $pdo1, 'numero' => $max1 + 1];
    }


    $pdo2 = new PDO("mysql:host=192.168.101.9;port=3306;dbname=sistema_alquiler_vehiculos2;charset=utf8mb4", $usuario, $clave);
    $stmt2 = $pdo2->query("SELECT MAX(numero) AS max_num FROM vehiculo");
    $max2 = $stmt2->fetch(PDO::FETCH_ASSOC)['max_num'] ?? 50;

    if ($max2 < 100) {
        return ['pdo' => $pdo2, 'numero' => $max2 + 1];
    }

    throw new Exception("No hay espacio disponible en ninguna base de datos.");
}


if (isset($_POST['agregar'])) {
    try {
        $resultado = obtenerEspacioDisponible();
        $pdo = $resultado['pdo'];
        $numero = $resultado['numero']; 

        $stmt = $pdo->prepare("INSERT INTO vehiculo (numero, placa, marca, modelo, kilometraje, fecha_adquisicion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $numero,
            $_POST['placa'],
            $_POST['marca'],
            $_POST['modelo'],
            $_POST['kilometraje'],
            $_POST['fecha_adquisicion']
        ]);
        header("Location: crud_vehiculos.php");
        exit;
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}


function obtenerConexionPorNumero($numero) {
    $usuario = "phpapp";
    $clave = "1234";

    if ($numero >= 1 && $numero <= 50) {
        return new PDO("mysql:host=192.168.101.8;port=3305;dbname=sistema_alquiler_vehiculos1;charset=utf8mb4", $usuario, $clave);
    } elseif ($numero >= 51 && $numero <= 100) {
        return new PDO("mysql:host=192.168.101.9;port=3306;dbname=sistema_alquiler_vehiculos2;charset=utf8mb4", $usuario, $clave);
    } else {
        throw new Exception("Número fuera de rango.");
    }
}


$vehiculo_editar = null;
if (isset($_GET['editar']) && isset($_GET['numero'])) {
    $numero = $_GET['numero'];
    $pdo = obtenerConexionPorNumero($numero);
    $stmt = $pdo->prepare("SELECT * FROM vehiculo WHERE numero = ?");
    $stmt->execute([$numero]);
    $vehiculo_editar = $stmt->fetch();
}


if (isset($_POST['actualizar'])) {
    $numero = $_POST['numero'];
    $pdo = obtenerConexionPorNumero($numero);
    $stmt = $pdo->prepare("UPDATE vehiculo SET marca = ?, modelo = ?, kilometraje = ?, fecha_adquisicion = ? WHERE numero = ?");
    $stmt->execute([
        $_POST['marca'],
        $_POST['modelo'],
        $_POST['kilometraje'],
        $_POST['fecha_adquisicion'],
        $numero
    ]);
    header("Location: crud_vehiculos.php");
    exit;
}


if (isset($_GET['eliminar']) && isset($_GET['numero'])) {
    $numero = $_GET['numero'];
    $pdo = obtenerConexionPorNumero($numero);
    $stmt = $pdo->prepare("DELETE FROM vehiculo WHERE numero = ?");
    $stmt->execute([$numero]);
    header("Location: crud_vehiculos.php");
    exit;
}


$vehiculos = [];

try {
    $pdo1 = obtenerConexionPorNumero(1);
    $vehiculos = array_merge($vehiculos, $pdo1->query("SELECT * FROM vehiculo")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}

try {
    $pdo2 = obtenerConexionPorNumero(51);
    $vehiculos = array_merge($vehiculos, $pdo2->query("SELECT * FROM vehiculo")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>CRUD Vehículos - Trabajador</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Gestión de Vehículos</h2>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <!-- Formulario agregar o editar -->
    <form method="POST" class="row g-3 mb-4">
        <?php if ($vehiculo_editar): ?>
            <input type="hidden" name="numero" value="<?= $vehiculo_editar['numero'] ?>">
        <?php endif; ?>

        <div class="col-md-2">
            <input type="text" name="placa" class="form-control" placeholder="Placa" required
                   value="<?= $vehiculo_editar['placa'] ?? '' ?>" <?= $vehiculo_editar ? 'readonly' : '' ?>>
        </div>
        <div class="col-md-2">
            <input type="text" name="marca" class="form-control" placeholder="Marca" required
                   value="<?= $vehiculo_editar['marca'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="modelo" class="form-control" placeholder="Modelo" required
                   value="<?= $vehiculo_editar['modelo'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="kilometraje" class="form-control" placeholder="Kilometraje" required
                   value="<?= $vehiculo_editar['kilometraje'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_adquisicion" class="form-control" required
                   value="<?= $vehiculo_editar['fecha_adquisicion'] ?? '' ?>">
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

    <!-- Tabla de vehículos -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>N°</th>
                <th>Placa</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Kilometraje</th>
                <th>Fecha Adquisición</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($vehiculos as $v): ?>
            <tr>
                <td><?= $v['numero'] ?></td>
                <td><?= $v['placa'] ?></td>
                <td><?= $v['marca'] ?></td>
                <td><?= $v['modelo'] ?></td>
                <td><?= $v['kilometraje'] ?></td>
                <td><?= $v['fecha_adquisicion'] ?></td>
                <td>
                    <a href="?editar=<?= $v['placa'] ?>&numero=<?= $v['numero'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?eliminar=<?= $v['placa'] ?>&numero=<?= $v['numero'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este vehículo?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

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

// AGREGAR
if (isset($_POST['agregar'])) {
    try {
        $id = $_POST['id'];
        $pdo = obtenerConexionPorId($id);
        $stmt = $pdo->prepare("INSERT INTO usuario (id, numero_identificacion, nombre, apellido, email, telefono, direccion, codigo_postal, tipo_usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $_POST['numero_identificacion'],
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['email'],
            $_POST['telefono'],
            $_POST['direccion'],
            $_POST['codigo_postal'],
            $_POST['tipo_usuario_id']
        ]);
        header("Location: crud_usuario.php");
        exit;
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

// EDITAR
$usuario_editar = null;
if (isset($_GET['editar']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id = ?");
    $stmt->execute([$id]);
    $usuario_editar = $stmt->fetch();
}

// ACTUALIZAR
if (isset($_POST['actualizar'])) {
    $id = $_POST['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("UPDATE usuario SET numero_identificacion = ?, nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ?, codigo_postal = ?, tipo_usuario_id = ? WHERE id = ?");
    $stmt->execute([
        $_POST['numero_identificacion'],
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['email'],
        $_POST['telefono'],
        $_POST['direccion'],
        $_POST['codigo_postal'],
        $_POST['tipo_usuario_id'],
        $id
    ]);
    header("Location: crud_usuario.php");
    exit;
}

// ELIMINAR
if (isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = obtenerConexionPorId($id);
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: crud_usuario.php");
    exit;
}

// LISTADO
$usuarios = [];

try {
    $pdo1 = obtenerConexionPorId(1);
    $usuarios = array_merge($usuarios, $pdo1->query("SELECT * FROM usuario")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}

try {
    $pdo2 = obtenerConexionPorId(51);
    $usuarios = array_merge($usuarios, $pdo2->query("SELECT * FROM usuario")->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>CRUD Usuarios</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-4">
    <h2>Gestión de Usuarios</h2>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-1">
            <input type="number" name="id" class="form-control" placeholder="ID" required value="<?= $usuario_editar['id'] ?? '' ?>" <?= $usuario_editar ? 'readonly' : '' ?>>
        </div>
        <div class="col-md-2">
            <input type="text" name="numero_identificacion" class="form-control" placeholder="Identificación" required value="<?= $usuario_editar['numero_identificacion'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre" required value="<?= $usuario_editar['nombre'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="apellido" class="form-control" placeholder="Apellido" required value="<?= $usuario_editar['apellido'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="email" name="email" class="form-control" placeholder="Correo" required value="<?= $usuario_editar['email'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="telefono" class="form-control" placeholder="Teléfono" required value="<?= $usuario_editar['telefono'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="direccion" class="form-control" placeholder="Dirección" required value="<?= $usuario_editar['direccion'] ?? '' ?>">
        </div>
        <div class="col-md-1">
            <input type="text" name="codigo_postal" class="form-control" placeholder="Postal" required value="<?= $usuario_editar['codigo_postal'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <select name="tipo_usuario_id" class="form-control" required>
                <option value="">Tipo Usuario</option>
                <option value="1" <?= (isset($usuario_editar) && $usuario_editar['tipo_usuario_id'] == 1) ? 'selected' : '' ?>>Administrador</option>
                <option value="2" <?= (isset($usuario_editar) && $usuario_editar['tipo_usuario_id'] == 2) ? 'selected' : '' ?>>Cliente</option>
                <option value="3" <?= (isset($usuario_editar) && $usuario_editar['tipo_usuario_id'] == 3) ? 'selected' : '' ?>>Trabajador</option>
            </select>
        </div>
        <div class="col-md-2">
            <?php if ($usuario_editar): ?>
                <button type="submit" name="actualizar" class="btn btn-warning">Actualizar</button>
                <a href="crud_usuario.php" class="btn btn-secondary">Cancelar</a>
            <?php else: ?>
                <button type="submit" name="agregar" class="btn btn-success">Agregar</button>
            <?php endif; ?>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Identificación</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Postal</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= $u['numero_identificacion'] ?></td>
                <td><?= $u['nombre'] ?></td>
                <td><?= $u['apellido'] ?></td>
                <td><?= $u['email'] ?></td>
                <td><?= $u['telefono'] ?></td>
                <td><?= $u['direccion'] ?></td>
                <td><?= $u['codigo_postal'] ?></td>
                <td><?= $u['tipo_usuario_id'] ?></td>
                <td>
                    <a href="?editar=1&id=<?= $u['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="?eliminar=1&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</body>
</html>

<?php
session_start();

function buscarUsuarioPorEmail($email) {
    $servidores = [
        [
            'host' => '192.168.101.8',
            'puerto' => '3305',
            'db' => 'sistema_alquiler_vehiculos1',
            'origen' => 1
        ],
        [
            'host' => '192.168.101.9',
            'puerto' => '3306',
            'db' => 'sistema_alquiler_vehiculos2',
            'origen' => 2
        ]
    ];

    foreach ($servidores as $srv) {
        try {
            $pdo = new PDO(
                "mysql:host={$srv['host']};port={$srv['puerto']};dbname={$srv['db']};charset=utf8mb4",
                "phpapp",
                "1234",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 3
                ]
            );

            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                return ['usuario' => $usuario, 'origen' => $srv['origen']];
            }

        } catch (PDOException $e) {
            // Puedes activar logs si quieres depurar:
            // error_log("Error al conectar a {$srv['host']}:{$srv['puerto']} - " . $e->getMessage());
        }
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $resultado = buscarUsuarioPorEmail($email);

    if ($resultado) {
        $_SESSION['usuario'] = $resultado['usuario'];
        $_SESSION['db_origen'] = $resultado['origen'];

        switch ($resultado['usuario']['tipo_usuario_id']) {
            case 1: header("Location: admin/crud_usuarios.php"); break;
            case 2: header("Location: cliente/reservas.php"); break;
            case 3: header("Location: trabajador/crud_vehiculos.php"); break;
            default: echo "Tipo de usuario desconocido."; exit;
        }

        exit;
    } else {
        $error = "Correo no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>Iniciar sesión</h2>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post">
        <div class="mb-3">
            <label for="email">Correo:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Ingresar</button>
    </form>
</div>
</body>
</html>

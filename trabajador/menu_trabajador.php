<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario_id'] != 3) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú del Trabajador</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2 class="mb-4">Bienvenido, <?= $_SESSION['usuario']['nombre'] ?? 'Trabajador' ?></h2>

    <ul class="list-group">
        <li class="list-group-item"><a href="crud_usuario.php">Gestionar Usuarios</a></li>
        <li class="list-group-item"><a href="crud_reserva.php">Gestionar Reservas</a></li>
        <li class="list-group-item"><a href="crud_alquiler.php">Gestionar Alquileres</a></li>
        <li class="list-group-item"><a href="crud_factura.php">Gestionar Facturas</a></li>
        <li class="list-group-item"><a href="crud_pago.php">Gestionar Pagos</a></li>
        <li class="list-group-item"><a href="crud_detallerFactura.php">Gestionar Detalles de Factura</a></li>
        <li class="list-group-item"><a href="crud_vehiculos.php">Gestionar vehiculo</a></li>
    </ul>
</body>
</html>

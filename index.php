<?php 
include(__DIR__ . "/Extremos/Cabeza.php");
include(__DIR__ . "/Extremos/pie.php");

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$tipo = $_SESSION['usuario']['tipo_usuario_id'];

if ($tipo == 1) {
    header("Location: admin/crud_usuarios.php");
    exit;
} elseif ($tipo == 2) {
    header("Location: cliente/reservas.php");
    exit;
} elseif ($tipo == 3) {
    header("Location: trabajador/crud_vehiculos.php");
    exit;
} else {
    echo "Tipo de usuario desconocido.";
}
?>


<?php
if (!isset($_SESSION)) {
    session_start();
}

function conectar_por_id($id_usuario) {
    $usuario = "phpapp";
    $clave = "1234";
    $host = "";
    $puerto = "";
    $db = "";

    if ($id_usuario >= 1 && $id_usuario <= 50) {
        $host = "192.168.101.8";
        $puerto = "3305";
        $db = "sistema_alquiler_vehiculos1";
    } elseif ($id_usuario >= 51 && $id_usuario <= 100) {
        $host = "192.168.101.9";
        $puerto = "3306";
        $db = "sistema_alquiler_vehiculos2";
    } else {
        die("❌ ID fuera del rango permitido.");
    }

    try {
        return new PDO("mysql:host=$host;port=$puerto;dbname=$db", $usuario, $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3
        ]);
    } catch (PDOException $e) {
        die("❌ Error de conexión: " . $e->getMessage());
    }
}

// Asigna $pdo globalmente
$pdo = conectar_por_id($_SESSION['usuario']['id']);

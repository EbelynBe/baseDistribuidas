<?php
$host = 'localhost';
$port = '3305';
$db = 'sistema_alquiler_vehiculos';
$user = 'ebelyn'; 
$pass = '12345';     
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>

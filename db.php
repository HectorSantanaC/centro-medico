<?php
// Archivo de conexión a PostgreSQL. Usa PDO para seguridad y consultas preparadas.
$host = 'localhost';
$dbname = 'tac';  // Cambia por tu BD
$username = 'postgres';     // Usuario BD
$password = '1234';  // Contraseña segura

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

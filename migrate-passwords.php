<?php

require_once __DIR__ . '/config/Database.php';

echo "=== Migration: Force hash all passwords ===\n\n";

$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->query("SELECT id, email, password FROM usuarios");
$usuarios = $stmt->fetchAll();

echo "Usuarios encontrados: " . count($usuarios) . "\n\n";

$actualizados = 0;

foreach ($usuarios as $usuario) {
  $id = $usuario['id'];
  $email = $usuario['email'];
  $password = $usuario['password'];

  $nuevoHash = password_hash($password, PASSWORD_DEFAULT);

  try {
    $updateStmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $updateStmt->execute([$nuevoHash, $id]);
    echo "[OK] $email - Contraseña hasheada\n";
    $actualizados++;
  } catch (Exception $e) {
    echo "[ERROR] $email - " . $e->getMessage() . "\n";
  }
}

echo "\n=== Resumen ===\n";
echo "Actualizados: $actualizados\n";
echo "\n¡Migración completada!\n";
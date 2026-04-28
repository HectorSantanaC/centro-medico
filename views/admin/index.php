<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../login.php');
  exit;
}
$isAdmin = isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php require_once __DIR__ . '/../layout/navbar-admin.php' ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Bienvenido al Panel de Administración</h1>
      <p>Gestiona los contenidos y usuarios del sistema</p>
    </div>

    <?php if ($isAdmin): ?>
      <h2 class="section-title">Estadisticas</h2>
      <div class="stats-grid" id="stats-grid">
        <div class="stat-card loading">
          <div class="spinner"></div>
          <div>Cargando...</div>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <script src="js/admin-dashboard.js"></script>
  <?php require_once __DIR__ . '/../layout/footer-admin.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../login.php');
  exit;
}
$isAdmin = false;
if (isset($_SESSION['usuario_roles'])) {
  foreach ($_SESSION['usuario_roles'] as $role) {
    if ($role['rol_nombre'] === 'admin') { $isAdmin = true; break; }
  }
}
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

      <h2 class="section-title">Gráficos</h2>
      <div class="charts-grid">
        <div class="chart-card">
          <h3>Citas por Estado</h3>
          <div id="chart-estado" class="chart-container"></div>
        </div>
        <div class="chart-card">
          <h3>Citas por Especialidad</h3>
          <div id="chart-especialidad" class="chart-container"></div>
        </div>
        <div class="chart-card">
          <h3>Evolución de Citas</h3>
          <div id="chart-evolucion" class="chart-container"></div>
        </div>
        <div class="chart-card">
          <h3>Top Médicos</h3>
          <div id="chart-medicos" class="chart-container"></div>
        </div>
        <div class="chart-card">
          <h3>Citas por Día</h3>
          <div id="chart-dias" class="chart-container"></div>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <script src="js/admin-dashboard.js"></script>
  <?php require_once __DIR__ . '/../layout/footer-admin.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['usuario_id'])) {
  header('Location: ../login.php');
  exit;
}
$isAdmin = false;
$isAdministracion = false;
if (isset($_SESSION['usuario_roles'])) {
  foreach ($_SESSION['usuario_roles'] as $role) {
    if ($role['rol_nombre'] === 'admin') { $isAdmin = true; }
    if ($role['rol_nombre'] === 'administracion') { $isAdministracion = true; }
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

    <?php if ($isAdmin || $isAdministracion): ?>
      <h2 class="section-title">Estadisticas</h2>
      <div class="stats-grid" id="stats-grid" data-role="<?= $isAdmin ? 'admin' : 'administracion' ?>">
        <div class="stat-card loading">
          <div class="spinner"></div>
          <div>Cargando...</div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($isAdmin || $isAdministracion): ?>
      <h2 class="section-title">Gráficos</h2>
      <div class="charts-grid">
        <div class="chart-card">
          <div class="chart-header">
            <h3>Citas por Estado</h3>
          </div>
          <div id="chart-estado" class="chart-container"></div>
        </div>
        <?php if ($isAdmin): ?>
        <div class="chart-card">
          <div class="chart-header">
            <h3>Citas por Especialidad</h3>
            <div class="chart-filter">
              <select id="filtro-especialidad">
                <option value="">Todos los años</option>
                <option value="2026">2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
              </select>
            </div>
          </div>
          <div id="chart-especialidad" class="chart-container"></div>
        </div>
        <?php endif; ?>
        <div class="chart-card">
          <div class="chart-header">
            <h3>Evolución de Citas</h3>
            <?php if ($isAdmin || $isAdministracion): ?>
            <div class="chart-filter">
              <select id="filtro-evolucion">
                <option value="3">Últimos 3 meses</option>
                <option value="6">Últimos 6 meses</option>
                <option value="12" selected>Últimos 12 meses</option>
              </select>
            </div>
            <?php endif; ?>
          </div>
          <div id="chart-evolucion" class="chart-container"></div>
        </div>
        <?php if ($isAdmin): ?>
        <div class="chart-card">
          <div class="chart-header">
            <h3>Top Médicos</h3>
            <div class="chart-filter">
              <select id="filtro-medicos">
                <option value="">Todos los años</option>
                <option value="2026">2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
              </select>
            </div>
          </div>
          <div id="chart-medicos" class="chart-container"></div>
        </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>

  <script src="js/admin-dashboard.js"></script>
  <?php require_once __DIR__ . '/../layout/footer-admin.php'; ?>

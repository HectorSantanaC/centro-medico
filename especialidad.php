<?php

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Medico.php';
require_once __DIR__ . '/models/Especialidad.php';

$page_title = 'Especialidad';

$especialidadId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($especialidadId <= 0) {
  header('Location: index.php');
  exit;
}

$especialidadModel = new Especialidad();
$especialidad = $especialidadModel->find($especialidadId);

if (!$especialidad) {
  header('Location: index.php');
  exit;
}

$medicoModel = new Medico();
$medicos = $medicoModel->getByEspecialidad($especialidadId);

$page_title = $especialidad['nombre'] . ' - Centro Médico TAC7';

include './views/layout/header.php';
?>

<section class="especialidad-header">
  <h1><?= htmlspecialchars($especialidad['nombre']) ?></h1>
  <?php if (!empty($medicos)): ?>
    <p>Nuestro equipo de profesionales</p>
  <?php else: ?>
    <p>Próximamente disponible</p>
  <?php endif; ?>
  <a href="cita-online.php" class="btn-nueva-cita">Solicita una cita</a>
</section>

<?php if (!empty($medicos)): ?>
<section class="seccion-equipo">
  <div class="grid-especialidad">
    <?php foreach ($medicos as $medico): ?>
      <div class="card-doctor">
        <img src="./assets/img/medico.jpg" alt="Dr. <?= htmlspecialchars($medico['nombre_completo']) ?>">
        <div class="doctor">
          <h3><?= htmlspecialchars($medico['nombre_completo']) ?></h3>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php include './views/layout/footer.php'; ?>
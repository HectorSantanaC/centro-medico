<?php

require_once __DIR__ . '/config/Database.php';

$db = Database::getInstance();

$medicos = $db->fetchAll(
  "SELECT m.nombre, m.apellidos, e.nombre AS especialidad
   FROM medicos m
   JOIN especialidades e ON m.especialidad_id = e.id
   WHERE m.activo = true AND e.activo = true
   ORDER BY m.nombre, m.apellidos"
);

$page_title = 'Sobre nosotros';

?>

<section class="seccion-equipo" id="nuestro-equipo">
  <h2>Nuestro Equipo Médico</h2>

  <div class="carousel-equipo">
    <button class="carousel-btn prev" aria-label="Anterior">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15 18l-6-6 6-6" />
      </svg>
    </button>
    <div class="carousel" id="carousel-equipo">

      <?php foreach ($medicos as $medico): ?>
        <div class="card-doctor">
          <img src="./assets/img/medico.jpg" alt="Dr. <?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellidos']) ?> - <?= htmlspecialchars($medico['especialidad']) ?>">
          <div class="doctor">
            <h3><?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellidos']) ?></h3>
            <p><?= htmlspecialchars($medico['especialidad']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
    <button class="carousel-btn next" aria-label="Siguiente">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 18l6-6-6-6" />
      </svg>
    </button>
  </div>
</section>
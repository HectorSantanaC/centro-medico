<?php 

require_once __DIR__ . '/config/Database.php';

$db = Database::getInstance();

$especialidades = $db->fetchAll(
  "SELECT nombre FROM especialidades WHERE activo = true ORDER BY nombre"
);

$page_title = 'Servicios'; 
?>

<section class="seccion-especialidades">
  <h2>Nuestras especialidades</h2>
  <div class="carousel-especialidades">
    <button class="carousel-btn prev" aria-label="Anterior">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15 18l-6-6 6-6"/>
      </svg>
    </button>
    <div class="grid-especialidades" id="carousel-especialidades">
      <?php foreach ($especialidades as $especialidad): ?>
      <div class="especialidad">
        <p><?= htmlspecialchars($especialidad['nombre']) ?></p>
        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-narrow-right">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M5 12l14 0" />
          <path d="M15 16l4 -4" />
          <path d="M15 8l4 4" />
        </svg>
      </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-btn next" aria-label="Siguiente">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 18l6-6-6-6"/>
      </svg>
    </button>
  </div>
</section>

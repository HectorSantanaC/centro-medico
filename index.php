<?php 
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Medico.php';
require_once __DIR__ . '/models/Especialidad.php';

$page_title = 'Centro Médico TAC7';

$medicoModel = new Medico();
$medicos = $medicoModel->allActives();

$especialidadModel = new Especialidad();
$especialidades = $especialidadModel->allActives();

include './views/layout/header.php'; 
?>

<!-- Hero -->
<section class="hero">
  <h1>Bienvenidæ</h1>
</section>

<!-- Frase servicios -->
<section class="frase-servicios" id="frase-servicios">
  <h2>Servicios médicos de alta calidad con enfoque en la atención integral, personalizada, a tu medida y a tus tiempos.</h2>
</section>

<!-- Sobre nosotros -->
<section class="sobre-nosotros">

  <div class="cards-sobre-nosotros">
    <picture>
      <img src="./assets/img/sillon1.jpg">
    </picture>

    <div>
      <img src="./assets/img/botox.jpg">
      <p>Tu salud, nuestra razón de ser. Vemos la salud como un viaje continuo hacia el bienestar total. Queremos ser el lugar al que acudas cuando busques mantener y mejorar tu calidad de vida.</p>
      <a href="">Quienes somos
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-narrow-right">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M5 12l14 0" />
          <path d="M15 16l4 -4" />
          <path d="M15 8l4 4" />
        </svg>
      </a>
    </div>

    <div>
      <p>Somos un equipo de profesionales altamente cualificados que nos dedicamos a la atención sanitaria de calidad, basada en la empatía y el respeto.</p>
      <img src="./assets/img/sillon2.jpg">
    </div>
  </div>
</section>

<!-- Sección de equipo médico -->
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
          <img src="./assets/img/medico.jpg" alt="Dr. <?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellidos']) ?> - <?= htmlspecialchars($medico['especialidad_nombre']) ?>">
          <div class="doctor">
            <h3><?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellidos']) ?></h3>
            <p><?= htmlspecialchars($medico['especialidad_nombre']) ?></p>
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

<!-- Quienes somos -->
<section class="quienes-somos" id="quienes-somos">
  <picture class="hero-img">
    <img src="./assets/img/centro-medico.jpg" alt="Centro Médico TAC7">
  </picture>

  <div class="texto-bloque">
    <div class="container">
      <div>
        <h3>Desde 1972, cuidando tu salud y bienestar con innovación, tradición y atención integral.</h3>
        <h4>Tac7Salud</h4>
      </div>
      <div>
        <p>Creemos en un enfoque integral que combina tradición, innovación y cercanía. Trabajamos cada día para ofrecer un entorno único, con servicios innovadores y productos diseñados para ayudarte a cuidar tu cuerpo, mejorar tu imagen y alcanzar más salud.
          Junto a un equipo altamente cualificado y en constante formación, seguimos construyendo un espacio donde la confianza, la calidad y el bienestar sean los pilares de nuestro compromiso contigo.</p>
      </div>
    </div>
  </div>
</section>

<!-- Nuestras especialidades -->
<section class="seccion-especialidades">
  <h2>Nuestras especialidades</h2>
  <div class="carousel-especialidades">
    <button class="carousel-btn prev" aria-label="Anterior">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M15 18l-6-6 6-6" />
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
        <path d="M9 18l6-6-6-6" />
      </svg>
    </button>
  </div>
</section>

<!-- Noticias -->
<section class="noticias">
  <article class="publicaciones">
    <h2>Noticias & Publicaciones</h2>
    <h3>Actualidad, consejos de salud y publicaciones de nuestros profesionales para tu bienestar integral.</h3>
    <a href="blog.php">Visita el blog
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-narrow-right">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M5 12l14 0" />
        <path d="M15 16l4 -4" />
        <path d="M15 8l4 4" />
      </svg>
    </a>
  </article>
  <picture>
    <img src="./assets/img/img-noticias.jpg">
  </picture>
</section>

<!-- Opiniones -->
<section class="opiniones">
  <div class="opiniones-slider" id="opiniones-slider">
    <div class="opinion active">
      <p>
        "Desde que entras en TAC7 las instalaciones y los profesionales que trabajan en el centro te enamoran. 
        Me sentí muy cuidada y entendida desde el primer momento. Muchas gracias!"
      </p>
    </div>
    <div class="opinion">
      <p>
        "Cercanía y clima de confianza, profundización con ejemplos sobre las dudas que iban surgiendo, aporte 
        de estrategias y herramientas para el parto."
      </p>
    </div>
    <div class="opinion">
      <p>
        "¡Muy recomendable! Me sentí muy cómoda! Explicaba todo con claridad y detalle. Se tomó el tiempo necesario 
        para resolver mis dudas y transmitir tranquilidad. Una atención profesional y cercana que se agradece muchísimo."
      </p>
    </div>
    <div class="opinion">
      <p>
        "Me ha encantado mi primera consulta con la Doctora. Me parece que tiene una visión actual de la ginecología. 
        Da diferentes opciones de tratamiento adaptándose a la paciente. La recomiendo 100%."
      </p>
    </div>
  </div>
  <div class="opiniones-dots">
    <button class="dot active" data-index="0"></button>
    <button class="dot" data-index="1"></button>
    <button class="dot" data-index="2"></button>
    <button class="dot" data-index="3"></button>
  </div>
</section>

<section class="aseguradoras">
    <div class="logos-track">
        <!-- 3 REPETICIONES para loop infinito -->
        <div class="logo-set">
            <img src="./assets/img/sanitas-logo.png" alt="Sanitas">
            <img src="./assets/img/mapfre-logo.png" alt="Mapfre">
            <img src="./assets/img/dkv-logo.png" alt="DKV">
        </div>
        <div class="logo-set">
            <img src="./assets/img/sanitas-logo.png" alt="Sanitas">
            <img src="./assets/img/mapfre-logo.png" alt="Mapfre">
            <img src="./assets/img/dkv-logo.png" alt="DKV">
        </div>
        <div class="logo-set">
            <img src="./assets/img/sanitas-logo.png" alt="Sanitas">
            <img src="./assets/img/mapfre-logo.png" alt="Mapfre">
            <img src="./assets/img/dkv-logo.png" alt="DKV">
        </div>
    </div>
</section>

<?php include './views/layout/footer.php'; ?>
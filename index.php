<?php 
require_once __DIR__ . '/config/Database.php';
$page_title = 'Centro Médico TAC7';
include './includes/header.php'; 
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
<?php $page_title = 'Sobre nosotros';
include 'sobre-nosotros.php'; ?>

<!-- Sección de equipo médico -->
<?php $page_title = 'Nuestro Equipo';
include 'equipo.php'; ?>

<!-- Quienes somos -->
<?php $page_title = 'Quienes somos';
include 'quienes-somos.php'; ?>

<!-- Nuestras especialidades -->
<?php $page_title = 'Servicios';
include 'servicios.php'; ?>

<!-- Noticias -->
<?php $page_title = 'Noticias';
include 'noticias.php'; ?>

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

<?php include './includes/footer.php'; ?>
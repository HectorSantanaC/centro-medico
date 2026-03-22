<?php $page_title = 'Centro Médico TAC7';
include './includes/header.php'; ?>

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
  <p>
    "Desde que entras en TAC7 las instalaciones y los profesionales que trabajan en el centro te enamoran.
    Me sentí muy cuidada y entendida desde el primer momento. Muchas gracias!"
  </p>
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
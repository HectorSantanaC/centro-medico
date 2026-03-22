<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Centro Médico TAC7 - <?php echo $page_title ?? 'Inicio'; ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <header>
    <nav class="navbar">
      <h5 class="logo"><a href="index.php">CENTRO MÉDICO TAC7</a></h5>

      <div>
        <ul class="menu">
          <li>
            <a href="index.php#frase-servicios">Centro Médico</a>
            <ul class="submenu">
              <li><a href="index.php#nuestro-equipo">Nuestro equipo</a></li>
              <li><a href="index.php#quienes-somos">Quienes somos</a></li>
            </ul>
          </li>
          <li><a href="#">Especialidades</a></li>
          <li><a href="#">Cursos y Talleres</a></li>
          <li><a href="#">Actualidad</a></li>
          <li><a href="#">Contacto</a></li>
        </ul>
      </div>

      <div>
        <ul class="menu">
          <li><a href="#" class="btn-boutique">La Boutique de TAC7</a></li>

          <li><a href="cita-online.php" class="btn-cita">Cita online</a></li>

          <?php if (isset($_SESSION['usuario_id'])): ?>
            <!-- USUARIO LOGUEADO - Mostrar opciones de usuario -->
            <li>
              <a href="#" class="btn-usuario">
                👤 <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
              </a>
              <ul class="submenu submenu-derecha">
                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                  <li><a href="citas-crud.php">📋 Gestionar Citas</a></li>
                <?php else: ?>
                  <li><a href="mis-citas.php">📅 Mis Citas</a></li>
                <?php endif; ?>
                <li><a href="logout.php">🚪 Cerrar Sesión</a></li>
              </ul>
            </li>
          <?php else: ?>
            <!-- USUARIO NO LOGUEADO - Mostrar botones de login/registro -->
            <li><a href="login.php" class="btn-login">Iniciar Sesión</a></li>
            <li><a href="registro.php" class="btn-registro">Registrarse</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div>
        <ul class="social-icons">
          <li>
            <a href="" class="icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 8a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4l0 -8" />
                <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                <path d="M16.5 7.5v.01" />
              </svg>
            </a>
          </li>
          <li>
            <a href="" class="icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-brand-linkedin">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M17 2a5 5 0 0 1 5 5v10a5 5 0 0 1 -5 5h-10a5 5 0 0 1 -5 -5v-10a5 5 0 0 1 5 -5zm-9 8a1 1 0 0 0 -1 1v5a1 1 0 0 0 2 0v-5a1 1 0 0 0 -1 -1m6 0a3 3 0 0 0 -1.168 .236l-.125 .057a1 1 0 0 0 -1.707 .707v5a1 1 0 0 0 2 0v-3a1 1 0 0 1 2 0v3a1 1 0 0 0 2 0v-3a3 3 0 0 0 -3 -3m-6 -3a1 1 0 0 0 -.993 .883l-.007 .127a1 1 0 0 0 1.993 .117l.007 -.127a1 1 0 0 0 -1 -1" />
              </svg>
            </a>
          </li>
          <li>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-bag">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304" />
              <path d="M9 11v-5a3 3 0 0 1 6 0v5" />
            </svg>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <main>
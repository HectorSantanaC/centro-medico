<?php
$es_admin = false;
$es_gestor = false;
$es_administracion = false;
if (isset($_SESSION['usuario_roles'])) {
  $roles = array_column($_SESSION['usuario_roles'], 'rol_nombre');
  $es_admin = in_array('admin', $roles);
  $es_gestor = in_array('gestor', $roles);
  $es_administracion = in_array('administracion', $roles);
}
$active = $active ?? '';
?>
<nav class="sidebar">
  <div class="sidebar-header">
    <h2>Centro Médico TAC7</h2>
    <span>Panel de Administración</span>
  </div>

  <div class="user-info">
    <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
    <span>
      <?php 
      if (isset($_SESSION['usuario_roles'])) {
        $roleNames = array_column($_SESSION['usuario_roles'], 'rol_nombre');
        $uniqueRoles = array_unique($roleNames);
        echo ucfirst(implode(', ', $uniqueRoles));
      } else {
        echo 'Sin rol';
      }
      ?>
    </span>
  </div>

  <div class="sidebar-menu">
    <a href="admin.php" class="<?= $active === 'inicio' ? 'active' : '' ?>">
      <span class="icon">🏠</span> Inicio
    </a>

    <?php if ($es_admin): ?>
      <a href="admin-usuarios.php" class="<?= $active === 'usuarios' ? 'active' : '' ?>">
        <span class="icon">👥</span> Usuarios
      </a>

      <a href="admin-roles.php" class="<?= $active === 'roles' ? 'active' : '' ?>">
        <span class="icon">🔐</span> Roles
      </a>
    <?php endif; ?>

    <?php if ($es_admin || $es_administracion): ?>
      <a href="citas-crud.php" class="<?= $active === 'citas' ? 'active' : '' ?>">
        <span class="icon">📅</span> Agenda
      </a>
    <?php endif; ?>

    <?php if ($es_admin || $es_gestor): ?>

    <a href="admin-especialidades.php" class="<?= $active === 'especialidades' ? 'active' : '' ?>">
      <span class="icon">🏥</span> Especialidades
    </a>

    <a href="medicos-crud.php" class="<?= $active === 'medicos' ? 'active' : '' ?>">
      <span class="icon">👨‍⚕️</span> Médicos
    </a>

    <a href="articulos-crud.php" class="<?= $active === 'contenidos' ? 'active' : '' ?>">
      <span class="icon">📰</span> Contenidos
    </a>

    <a href="topicos-crud.php" class="<?= $active === 'topicos' ? 'active' : '' ?>">
      <span class="icon">📚</span> Tópicos
    </a>
    <?php endif; ?>
  </div>

  <div class="sidebar-footer">
    <a href="logout.php">
      <span class="icon">🚪</span> Cerrar Sesión
    </a>
  </div>
</nav>
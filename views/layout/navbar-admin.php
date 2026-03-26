<?php
$es_admin = isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
$active = $active ?? '';
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <h2>Centro Médico TAC7</h2>
        <span>Panel de Administración</span>
    </div>
    
    <div class="user-info">
        <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
        <span><?= ucfirst($_SESSION['usuario_rol']) ?></span>
    </div>

    <div class="sidebar-menu">
        <a href="admin.php" class="<?= $active === 'inicio' ? 'active' : '' ?>">
            <span class="icon">🏠</span> Inicio
        </a>
        
        <?php if ($es_admin): ?>
        <a href="usuarios-crud.php" class="<?= $active === 'usuarios' ? 'active' : '' ?>">
            <span class="icon">👥</span> Usuarios
        </a>
        <a href="citas-crud.php" class="<?= $active === 'citas' ? 'active' : '' ?>">
            <span class="icon">📅</span> Citas
        </a>
        <?php else: ?>
        <a href="citas-crud.php" class="<?= $active === 'citas' ? 'active' : '' ?>">
            <span class="icon">📅</span> Citas
        </a>
        <?php endif; ?>
        
        <a href="#" class="<?= $active === 'contenido' ? 'active' : '' ?>">
            <span class="icon">📰</span> Contenido
        </a>
    </div>

    <div class="sidebar-footer">
        <a href="logout.php">
            <span class="icon">🚪</span> Cerrar Sesión
        </a>
    </div>
</nav>

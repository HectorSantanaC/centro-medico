<?php include __DIR__ . '/layout/header.php'; ?>
<link rel="stylesheet" href="css/blog.css">

<section class="articulo-container">
  <a href="blog.php" class="articulo-volver">← Volver al blog</a>

  <header class="articulo-header">
    <?php if ($articulo['topico_nombre']): ?>
      <span class="articulo-categoria">
        <?= htmlspecialchars($articulo['topico_nombre']) ?>
      </span>
    <?php endif; ?>

    <h1 class="articulo-titulo"><?= htmlspecialchars($articulo['titulo']) ?></h1>

    <div class="articulo-meta">
      <?php if ($articulo['autor']): ?>
        <span>👤 <?= htmlspecialchars($articulo['autor']) ?></span>
      <?php endif; ?>
      <span>📅 <?= date('d/m/Y', strtotime($articulo['created_at'])) ?></span>
    </div>
  </header>

  <?php if ($articulo['imagen']): ?>
    <img src="<?= htmlspecialchars($articulo['imagen']) ?>"
      alt="<?= htmlspecialchars($articulo['titulo']) ?>"
      class="articulo-imagen">
  <?php endif; ?>

  <div class="articulo-contenido">
    <?= nl2br(htmlspecialchars($articulo['contenido'])) ?>
  </div>
</section>

<?php include __DIR__ . '/layout/footer.php'; ?>
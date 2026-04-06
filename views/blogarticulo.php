<?php include __DIR__ . '/layout/header.php'; ?>
<link rel="stylesheet" href="css/blog.css">

<div class="blog-container">
  <a href="blog.php" class="articulo-volver">← Volver al blog</a>

  <article class="articulo-container">
    <?php if (!empty($articulo['imagen'])): ?>
      <img src="<?= htmlspecialchars($articulo['imagen']) ?>" 
        alt="<?= htmlspecialchars($articulo['titulo']) ?>" 
        class="articulo-imagen">
    <?php elseif (!empty($articulo['imagen_url'])): ?>
      <img src="<?= htmlspecialchars($articulo['imagen_url']) ?>" 
        alt="<?= htmlspecialchars($articulo['titulo']) ?>" 
        class="articulo-imagen">
    <?php endif; ?>

    <header class="articulo-header">
      <?php if (!empty($articulo['topico_nombre'])): ?>
        <span class="articulo-categoria">
          <?= htmlspecialchars($articulo['topico_nombre']) ?>
        </span>
      <?php endif; ?>

      <h1 class="articulo-titulo">
        <?= htmlspecialchars($articulo['titulo']) ?>
      </h1>

      <div class="articulo-meta">
        <?php if (!empty($articulo['autor'])): ?>
          <span>👤 <?= htmlspecialchars($articulo['autor']) ?></span>
        <?php endif; ?>
        <span>📅 <?= date('d/m/Y', strtotime($articulo['created_at'])) ?></span>
      </div>
    </header>

    <div class="articulo-contenido">
      <?= nl2br(htmlspecialchars($articulo['contenido_completo'] ?? '')) ?>
    </div>
  </article>

  <a href="blog.php" class="articulo-volver">← Volver al blog</a>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>

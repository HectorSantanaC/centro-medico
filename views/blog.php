<?php require_once __DIR__ . '/layout/header.php'; ?>
<link rel="stylesheet" href="css/blog.css">

<section class="blog-container">
  <div class="blog-header">
    <h1>Blog de Salud</h1>
    <p>Consejos, noticias y artículos de nuestros profesionales</p>
  </div>

  <?php if (empty($articulos)): ?>
    <div class="sin-articulos">
      <p>No hay artículos publicados actualmente.</p>
      <p>Vuelve pronto para estar al día.</p>
    </div>
  <?php else: ?>
    <div class="blog-grid">
      <?php foreach ($articulos as $articulo): ?>
        <article class="blog-card">
          <?php if (!empty($articulo['imagen'])): ?>
            <img src="<?= htmlspecialchars($articulo['imagen']) ?>"
              alt="<?= htmlspecialchars($articulo['titulo']) ?>"
              class="blog-card-img">
          <?php elseif (!empty($articulo['imagen_url'])): ?>
            <img src="<?= htmlspecialchars($articulo['imagen_url']) ?>"
              alt="<?= htmlspecialchars($articulo['titulo']) ?>"
              class="blog-card-img">
          <?php else: ?>
            <div class="blog-card-img" style="display:flex;align-items:center;justify-content:center;color:#999;">
              📰 Sin imagen
            </div>
          <?php endif; ?>

          <div class="blog-card-content">
            <?php if ($articulo['topico_nombre']): ?>
              <span class="blog-card-categoria">
                <?= htmlspecialchars($articulo['topico_nombre']) ?>
              </span>
            <?php endif; ?>

            <h3><?= htmlspecialchars($articulo['titulo']) ?></h3>

            <?php if (!empty($articulo['contenido_reducido'])): ?>
              <p class="blog-card-resumen">
                <?= $articulo['contenido_reducido'] ?>
              </p>
            <?php endif; ?>

            <div class="blog-card-meta">
              <?php if ($articulo['autor']): ?>
                <span>👤 <?= htmlspecialchars($articulo['autor']) ?></span>
              <?php endif; ?>
              <span><?= date('d/m/Y', strtotime($articulo['created_at'])) ?></span>
            </div>

            <a href="?action=view&id=<?= $articulo['id'] ?>" class="blog-card-link">
              Leer más →
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
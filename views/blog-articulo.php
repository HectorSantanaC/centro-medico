<?php require_once __DIR__ . '/layout/header.php'; ?>
<link rel="stylesheet" href="css/blog.css">

<div class="blog-container">
  <a href="blog.php" class="articulo-volver">← Volver al blog</a>

  <article class="articulo-container">
    <?php 
    $imagenSrc = '';
    if (!empty($articulo['imagen'])) {
      $img = $articulo['imagen'];
      if (filter_var($img, FILTER_VALIDATE_URL) !== false) {
        $imagenSrc = htmlspecialchars($img);
      } else {
        $imagenSrc = './' . htmlspecialchars($img);
      }
    } elseif (!empty($articulo['imagen_url'])) {
      $imagenSrc = htmlspecialchars($articulo['imagen_url']);
    }
    ?>
    <?php if ($imagenSrc): ?>
      <img src="<?= $imagenSrc ?>" 
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
      <?= nl2br($articulo['contenido_completo'] ?? '') ?>
    </div>
  </article>

  <a href="blog.php" class="articulo-volver">← Volver al blog</a>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Artículos</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php include __DIR__ . '/../layout/navbar-admin.php'; ?>

  <main class="main-content">
    <?php if ($message): ?>
      <div class="message <?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
      <div class="page-header">
        <h1>📰 Gestión de Artículos</h1>
        <a href="?action=create" class="btn btn-primary">+ Nuevo Artículo</a>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Título</th>
              <th>Tópico</th>
              <th>Fecha</th>
              <th>Caducidad</th>
              <th>Publicado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($articulos as $articulo): ?>
              <tr>
                <td><?= htmlspecialchars($articulo['titulo']) ?></td>
                <td><?= htmlspecialchars($articulo['topico_nombre'] ?? '-') ?></td>
                <td><?= !empty($articulo['fecha_contenido']) ? date('d/m/Y', strtotime($articulo['fecha_contenido'])) : '-' ?></td>
                <td><?= !empty($articulo['fecha_caducidad']) ? date('d/m/Y', strtotime($articulo['fecha_caducidad'])) : '-' ?></td>
                <td>
                  <span class="rol-badge rol-<?= $articulo['publicado'] ? 'admin' : 'paciente' ?>">
                    <?= $articulo['publicado'] ? 'Sí' : 'No' ?>
                  </span>
                </td>

                <td class="actions">
                  <a href="?action=edit&id=<?= $articulo['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                  <a href="?action=delete&id=<?= $articulo['id'] ?>"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('¿Eliminar este artículo?')">Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
      <a href="articulos-crud.php" class="back-link">← Volver al listado</a>

      <div class="form-card">
        <h2><?= $action === 'create' ? 'Crear' : 'Editar' ?> Artículo</h2>

        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label>Título *</label>
            <input type="text" name="titulo" required
              value="<?= htmlspecialchars($articulo['titulo'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label>Tópico</label>
            <select name="topico">
              <option value="">Sin tópico</option>
              <?php foreach ($topicos as $top): ?>
                <option value="<?= $top['id'] ?>"
                  <?= ($articulo['topico'] ?? '') == $top['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($top['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Fecha contenido</label>
              <input type="date" name="fecha_contenido"
                value="<?= htmlspecialchars($articulo['fecha_contenido'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Fecha caducidad</label>
              <input type="date" name="fecha_caducidad"
                value="<?= htmlspecialchars($articulo['fecha_caducidad'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Contenido reducido</label>
              <textarea name="contenido_reducido" rows="3"
                placeholder="Breve descripción para tarjetas"><?= htmlspecialchars($articulo['contenido_reducido'] ?? $articulo['resumen'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
              <label>Contenido completo</label>
              <textarea name="contenido_completo" rows="10"
                placeholder="Contenido completo del artículo"><?= htmlspecialchars($articulo['contenido_completo'] ?? $articulo['contenido'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                Orden
                <input type="checkbox" name="publicado"
                  <?= !isset($articulo['publicado']) || $articulo['publicado'] ? 'checked' : '' ?>>
              </label>
            </div>

            <div class="form-group">
              <label>Notas</label>
              <textarea name="notas" rows="2"
                placeholder="Notas privadas solo visibles en admin"><?= htmlspecialchars($articulo['notas'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
              <label>Fotografía principal</label>
              <input type="file" name="imagen_file" accept="image/*">
              <?php if (!empty($articulo['imagen'])): ?>
                <p class="help-text">Imagen actual: <?= htmlspecialchars($articulo['imagen']) ?></p>
                <input type="hidden" name="imagen" value="<?= htmlspecialchars($articulo['imagen']) ?>">
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label>URL</label>
              <input type="text" name="imagen_url"
                value="<?= htmlspecialchars($articulo['imagen_url'] ?? '') ?>"
                placeholder="https://ejemplo.com/imagen.jpg">
            </div>

            <h3 class="info-seo">Información SEO</h3>

            <div class="form-group">
              <label>Título</label>
              <input type="text" name="seo_titulo"
                value="<?= htmlspecialchars($articulo['seo_titulo'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Descripción</label>
              <textarea name="seo_descripcion" rows="2">
                <?= htmlspecialchars($articulo['seo_descripcion'] ?? '') ?>
              </textarea>
            </div>

            <div class="form-group">
              <label>Palabras clave (separadas por comas)</label>
              <input type="text" name="seo_palabras_clave"
                value="<?= htmlspecialchars($articulo['seo_palabras_clave'] ?? '') ?>"
                placeholder="salud, medicina, consejos">
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <?= $action === 'create' ? 'Crear Artículo' : 'Guardar Cambios' ?>
              </button>
              <a href="articulos-crud.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
      </div>
    <?php endif; ?>
  </main>
</body>

</html>
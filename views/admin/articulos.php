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
              <th>ID</th>
              <th>Título</th>
              <th>Categoría</th>
              <th>Autor</th>
              <th>Publicado</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($articulos as $articulo): ?>
              <tr>
                <td><?= $articulo['id'] ?></td>
                <td><?= htmlspecialchars($articulo['titulo']) ?></td>
                <td><?= htmlspecialchars($articulo['categoria'] ?? '-') ?></td>
                <td><?= htmlspecialchars($articulo['autor'] ?? '-') ?></td>
                <td>
                  <span class="rol-badge rol-<?= $articulo['publicado'] ? 'admin' : 'paciente' ?>">
                    <?= $articulo['publicado'] ? 'Sí' : 'No' ?>
                  </span>
                </td>
                <td><?= date('d/m/Y', strtotime($articulo['created_at'])) ?></td>
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

        <form method="POST">
          <div class="form-group">
            <label>Título *</label>
            <input type="text" name="titulo" required
              value="<?= htmlspecialchars($articulo['titulo'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label>Resumen</label>
            <textarea name="resumen" rows="2"
              placeholder="Breve descripción para la tarjeta del artículo"><?= htmlspecialchars($articulo['resumen'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label>Contenido *</label>
            <textarea name="contenido" rows="10" required
              placeholder="Contenido completo del artículo"><?= htmlspecialchars($articulo['contenido'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label>URL de Imagen</label>
            <input type="text" name="imagen"
              value="<?= htmlspecialchars($articulo['imagen'] ?? '') ?>"
              placeholder="https://ejemplo.com/imagen.jpg">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Autor</label>
              <input type="text" name="autor"
                value="<?= htmlspecialchars($articulo['autor'] ?? '') ?>"
                placeholder="Nombre del autor">
            </div>

            <div class="form-group">
              <label>Categoría</label>
              <select name="categoria">
                <option value="">Sin categoría</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= $cat ?>"
                    <?= ($articulo['categoria'] ?? '') === $cat ? 'selected' : '' ?>>
                    <?= $cat ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" name="publicado"
                <?= !isset($articulo['publicado']) || $articulo['publicado'] ? 'checked' : '' ?>>
              Publicar artículo
            </label>
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
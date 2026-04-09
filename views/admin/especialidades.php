<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Especialidades</title>
  <link rel="stylesheet" href="css/admin.css">
  <?php require_once __DIR__ . '/../../helpers/sanitize.php'; ?>
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
        <h1>🏥 Gestión de Especialidades</h1>
        <a href="?action=create" class="btn btn-primary">+ Nueva Especialidad</a>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($especialidades as $esp): ?>
              <tr>
                <td><?= htmlspecialchars($esp['nombre']) ?></td>
                <td>
                  <?php if ($esp['activo']): ?>
                    <span class="estado-badge estado-confirmada">Activa</span>
                  <?php else: ?>
                    <span class="estado-badge estado-cancelada">Inactiva</span>
                  <?php endif; ?>
                </td>
                <td class="actions">
                  <a href="?action=edit&id=<?= $esp['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                  <a href="?action=delete&id=<?= $esp['id'] ?>"
                    class="btn btn-danger btn-sm btn-delete"
                    data-confirm="¿Eliminar esta especialidad?">Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn-secondary">← Anterior</a>
          <?php endif; ?>
          
          <span class="pagination-info">
            Página <?= $page ?> de <?= $totalPages ?>
            (<?= $totalItems ?> especialidades)
          </span>
          
          <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-secondary">Siguiente →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
      <a href="especialidades-crud.php" class="back-link">← Volver al listado</a>

      <div class="form-card">
        <h2><?= $action === 'create' ? 'Crear' : 'Editar' ?> Especialidad</h2>

        <form method="POST">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required
              value="<?= htmlspecialchars($especialidadEdit['nombre'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" name="activo" value="1" 
                <?= ($especialidadEdit['activo'] ?? true) ? 'checked' : '' ?>>
              Especialidad activa
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <?= $action === 'create' ? 'Crear Especialidad' : 'Guardar Cambios' ?>
            </button>
            <a href="especialidades-crud.php" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </main>
<?php include __DIR__ . '/../layout/footer-admin.php'; ?>

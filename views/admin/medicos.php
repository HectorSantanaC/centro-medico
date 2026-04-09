<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Médicos</title>
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
        <h1>👨‍⚕️ Gestión de Médicos</h1>
        <a href="?action=create" class="btn btn-primary">+ Nuevo Médico</a>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Especialidad</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($medicos as $med): ?>
              <tr>
                <td><?= htmlspecialchars($med['nombre'] . ' ' . $med['apellidos']) ?></td>
                <td><?= htmlspecialchars($med['especialidad_nombre'] ?? 'Sin asignar') ?></td>
                <td>
                  <?php if ($med['activo']): ?>
                    <span class="estado-badge estado-confirmada">Activo</span>
                  <?php else: ?>
                    <span class="estado-badge estado-cancelada">Inactivo</span>
                  <?php endif; ?>
                </td>
                <td class="actions">
                  <a href="?action=edit&id=<?= $med['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                  <a href="?action=delete&id=<?= $med['id'] ?>"
                    class="btn btn-danger btn-sm btn-delete"
                    data-confirm="¿Eliminar este médico?">Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
      <a href="medicos-crud.php" class="back-link">← Volver al listado</a>

      <div class="form-card">
        <h2><?= $action === 'create' ? 'Crear' : 'Editar' ?> Médico</h2>

        <form method="POST" class="form-grid">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required
              value="<?= htmlspecialchars($medicoEdit['nombre'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label>Apellidos *</label>
            <input type="text" name="apellidos" required
              value="<?= htmlspecialchars($medicoEdit['apellidos'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label>Especialidad</label>
            <select name="especialidad_id">
              <option value="">Sin asignar</option>
              <?php foreach ($especialidades as $esp): ?>
                <option value="<?= $esp['id'] ?>" 
                  <?= ($medicoEdit['especialidad_id'] ?? '') == $esp['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($esp['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" name="activo" value="1" 
                <?= ($medicoEdit['activo'] ?? true) ? 'checked' : '' ?>>
              Médico activo
            </label>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <?= $action === 'create' ? 'Crear Médico' : 'Guardar Cambios' ?>
            </button>
            <a href="medicos-crud.php" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </main>
<?php include __DIR__ . '/../layout/footer-admin.php'; ?>

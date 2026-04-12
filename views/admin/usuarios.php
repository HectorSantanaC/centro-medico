<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Usuarios</title>
  <link rel="stylesheet" href="css/admin.css">
  <?php require_once __DIR__ . '/../../helpers/sanitize.php'; ?>
</head>

<body>
  <?php require_once __DIR__ . '/../layout/navbar-admin.php'; ?>

  <main class="main-content">
    <?php if ($message): ?>
      <div class="message <?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
      <div class="page-header">
        <h1>👥 Gestión de Usuarios</h1>
        <a href="?action=create" class="btn btn-primary">+ Nuevo Usuario</a>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Apellidos</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Fecha Alta</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $usuario): ?>
              <tr>
                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                <td><?= htmlspecialchars($usuario['apellidos']) ?></td>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
                <td>
                  <span class="rol-badge rol-<?= $usuario['rol'] ?>">
                    <?= ucfirst($usuario['rol']) ?>
                  </span>
                </td>
                <td><?= date('d/m/Y', strtotime($usuario['created_at'])) ?></td>
                <td class="actions">
                  <a href="?action=edit&id=<?= $usuario['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                  <a href="?action=delete&id=<?= $usuario['id'] ?>"
                    class="btn btn-danger btn-sm btn-delete"
                    data-confirm="¿Eliminar este usuario?">Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
      <a href="usuarios-crud.php" class="back-link">← Volver al listado</a>

      <div class="form-card">
        <h2><?= $action === 'create' ? 'Crear' : 'Editar' ?> Usuario</h2>

        <form method="POST">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required
              value="<?= htmlspecialchars($usuarioEdit['nombre'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label>Apellidos *</label>
            <input type="text" name="apellidos" required
              value="<?= htmlspecialchars($usuarioEdit['apellidos'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required
              value="<?= htmlspecialchars($usuarioEdit['email'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label>Contraseña <?= $action === 'edit' ? '(dejar vacío para mantener)' : '*' ?></label>
            <input type="password" name="password"
              placeholder="<?= $action === 'edit' ? 'Sin cambios' : 'Contraseña por defecto: password123' ?>">
          </div>

          <div class="form-group">
            <label>Rol *</label>
            <select name="rol" required>
              <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol ?>"
                  <?= ($usuarioEdit['rol'] ?? 'paciente') === $rol ? 'selected' : '' ?>>
                  <?= ucfirst($rol) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <?= $action === 'create' ? 'Crear Usuario' : 'Guardar Cambios' ?>
            </button>
            <a href="usuarios-crud.php" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </main>
<?php require_once __DIR__ . '/../layout/footer-admin.php'; ?>
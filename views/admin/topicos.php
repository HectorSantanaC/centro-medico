<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Tópicos</title>
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
        <h1>📚 Gestión de Tópicos</h1>
        <a href="?action=create" class="btn btn-primary">+ Nuevo Tópico</a>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topicos as $topico): ?>
              <tr>
                <td><?= htmlspecialchars($topico['nombre']) ?></td>
                <td class="actions">
                  <a href="?action=edit&id=<?= $topico['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                  <a href="?action=delete&id=<?= $topico['id'] ?>"
                    class="btn btn-danger btn-sm btn-delete"
                    data-confirm="¿Eliminar este tópico?">Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
      <a href="topicos-crud.php" class="back-link">← Volver al listado</a>

      <div class="form-card">
        <h2><?= $action === 'create' ? 'Crear' : 'Editar' ?> Tópico</h2>

        <form method="POST">
          <?= csrf_field() ?>
          <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required
              value="<?= htmlspecialchars($topicoEdit['nombre'] ?? '') ?>">
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <?= $action === 'create' ? 'Crear Tópico' : 'Guardar Cambios' ?>
            </button>
            <a href="topicos-crud.php" class="btn btn-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </main>
<?php include __DIR__ . '/../layout/footer-admin.php'; ?>

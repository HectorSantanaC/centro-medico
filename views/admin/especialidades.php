<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Especialidades</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php require_once __DIR__ . '/../layout/navbar-admin.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Gestion de Especialidades</h1>
      <button id="btn-crear" class="btn btn-primary">+ Nueva Especialidad</button>
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Descripcion</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="especialidades-body">
          <tr>
            <td colspan="4" class="loading">
              <div class="spinner"></div>
              Cargando...
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>

  <div id="modal" class="modal">
    <div class="modal-content">
      <span class="modal-close-btn">×</span>
      <h2 id="modal-titulo">Nueva Especialidad</h2>
      <form id="form-especialidad">
        <input type="hidden" id="especialidad-id">

        <div class="form-group">
          <label for="nombre">Nombre *</label>
          <input type="text" id="nombre" required>
        </div>

        <div class="form-group">
          <label for="descripcion">Descripcion</label>
          <textarea id="descripcion" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" id="activo" checked>
            Especialidad activa
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary btn-sm modal-close">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <?php require_once __DIR__ . '/../layout/footer-admin.php'; ?>

  <script src="js/admin-especialidades.js"></script>
</body>

</html>

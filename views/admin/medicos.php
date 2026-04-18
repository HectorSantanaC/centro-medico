<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion de Medicos</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php require_once __DIR__ . '/../layout/navbar-admin.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Gestion de Medicos</h1>
      <button id="btn-crear" class="btn btn-primary">+ Nuevo Medico</button>
    </div>

    <div class="filtros-container">
      <div class="filtros-row">
        <div class="filtro-group">
          <label>Buscar</label>
          <input type="text" id="filtro-nombre" placeholder="Nombre o apellido...">
        </div>
        <div class="filtro-group">
          <label>Especialidad</label>
          <select id="filtro-especialidad">
            <option value="">Todas</option>
          </select>
        </div>
      </div>
      <div class="filtros-actions">
        <button id="btn-filtrar" class="btn btn-primary btn-sm">Buscar</button>
        <button id="btn-limpiar-filtros" class="btn btn-secondary btn-sm">Limpiar</button>
      </div>
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
        <tbody id="medicos-body">
          <tr>
            <td colspan="4" class="loading">
              <div class="spinner"></div>
              Cargando...
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination-container">
      <div class="pagination-info" id="pagination-info"></div>
      <div class="pagination-controls" id="pagination-controls"></div>
    </div>
  </main>

  <div id="modal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modal-titulo">Nuevo Medico</h2>
        <button class="modal-close-btn">&times;</button>
      </div>

      <form id="form-medico">
        <input type="hidden" id="medico-id">

        <div class="form-group">
          <label for="nombre">Nombre *</label>
          <input type="text" id="nombre" required>
        </div>

        <div class="form-group">
          <label for="apellidos">Apellidos *</label>
          <input type="text" id="apellidos" required>
        </div>

        <div class="form-group">
          <label for="especialidad_id">Especialidad</label>
          <select id="especialidad_id">
            <option value="">Sin asignar</option>
          </select>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" id="activo" checked>
            Medico activo
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary btn-sm modal-close">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/admin-medicos.js"></script>
</body>

</html>
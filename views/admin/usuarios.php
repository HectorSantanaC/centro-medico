<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Usuarios</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php require_once __DIR__ . '/../layout/navbar-admin.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Gestión de Usuarios</h1>
      <button id="btn-crear" class="btn btn-primary">+ Nuevo Usuario</button>
    </div>

    <div class="filtros-container">
      <div class="filtros-row">
        <div class="filtro-group">
          <label>Buscar</label>
          <input type="text" id="filtro-nombre" placeholder="Nombre o email...">
        </div>
        
      </div>
      <div class="filtros-actions">
        <button id="btn-filtrar" class="btn btn-primary btn-sm">Buscar</button>
        <button id="btn-limpiar-filtros" class="btn btn-secondary btn-sm">Limpiar</button>
      </div>
    </div>

    <div class="table-container">
      <table id="tabla-usuarios">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Fecha Alta</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="usuarios-body">
          <tr>
            <td colspan="5" class="loading">
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
        <h2 id="modal-titulo">Nuevo Usuario</h2>
        <button class="modal-close-btn">&times;</button>
      </div>

      <form id="form-usuario">
        <input type="hidden" id="usuario-id">

        <div class="form-group">
          <label for="nombre">Nombre *</label>
          <input type="text" id="nombre" required>
        </div>

        <div class="form-group">
          <label for="apellidos">Apellidos *</label>
          <input type="text" id="apellidos" required>
        </div>

        <div class="form-group">
          <label for="email">Email *</label>
          <input type="email" id="email" required>
        </div>

        <div class="form-group">
          <label for="password">Contraseña *</label>
          <input type="password" id="password" required>
          <p id="password-help" class="help-text">Mínimo 6 caracteres</p>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary btn-sm modal-close">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <div id="modal-roles" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modal-roles-titulo">Editar Roles</h2>
        <button class="modal-close-btn">&times;</button>
      </div>

      <div id="roles-info" style="margin-bottom: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
        <p style="margin: 0;"><strong>Usuario:</strong> <span id="roles-usuario-nombre"></span></p>
        <p style="margin: 5px 0 0 0;"><strong>Email:</strong> <span id="roles-usuario-email"></span></p>
      </div>

      <form id="form-roles">
        <input type="hidden" id="roles-usuario-id">

        <div class="form-group">
          <label>Seleccionar Roles</label>
          <div id="roles-tags" style="display: flex; flex-wrap: wrap; gap: 8px;">
          </div>
        </div>

        <div class="form-actions">
          <button type="button" id="btn-guardar-roles" class="btn btn-primary">Guardar Roles</button>
          <button type="button" class="btn btn-secondary btn-sm modal-close">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/admin-usuarios.js"></script>
</body>

</html>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion de Topicos</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php require_once __DIR__ . '/../layout/navbar-admin.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Gestion de Topicos</h1>
      <button id="btn-crear" class="btn btn-primary">+ Nuevo Topico</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="topicos-body">
          <tr>
            <td colspan="2" class="loading">
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
      <span class="modal-close-btn">&times;</span>
      <h2 id="modal-titulo">Nuevo Topico</h2>
      <form id="form-topico">
        <input type="hidden" id="topico-id">

        <div class="form-group">
          <label for="nombre">Nombre *</label>
          <input type="text" id="nombre" required>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary btn-sm modal-close">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/admin-topicos.js"></script>
</body>

</html>
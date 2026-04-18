<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion de Citas</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php require_once __DIR__ . '/../layout/navbar-admin.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Agenda</h1>
      <button id="btn-crear" class="btn btn-primary">+ Nueva Cita</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Paciente</th>
            <th>Medico</th>
            <th>Especialidad</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="citas-body">
          <tr>
            <td colspan="7" class="loading">
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
      <div class="modal-header">
        <h2 id="modal-titulo">Nueva Cita</h2>
        <button class="modal-close-btn">&times;</button>
      </div>

      <form id="formcita">
        <input type="hidden" id="cita-id">

        <div class="form-group">
          <label for="paciente_id">Paciente *</label>
          <select id="paciente_id" required>
            <option value="">Seleccionar paciente</option>
          </select>
        </div>

        <div class="form-group">
          <label for="especialidad_id">Especialidad *</label>
          <select id="especialidad_id" required>
            <option value="">Seleccionar especialidad</option>
          </select>
        </div>

        <div class="form-group">
          <label for="medico_id">Medico *</label>
          <select id="medico_id" required>
            <option value="">Seleccionar medico</option>
          </select>
        </div>

        <div class="form-group">
          <label for="fecha">Fecha *</label>
          <input type="date" id="fecha" required>
        </div>

        <div class="form-group">
          <label for="hora">Hora *</label>
          <select id="hora" required>
            <option value="">Seleccionar hora</option>
          </select>
        </div>

        <div class="form-group">
          <label for="estado">Estado</label>
          <select id="estado">
            <option value="pendiente">Pendiente</option>
            <option value="confirmada">Confirmada</option>
            <option value="cancelada">Cancelada</option>
            <option value="completada">Completada</option>
          </select>
        </div>

        <div class="form-group">
          <label for="notas">Notas</label>
          <textarea id="notas" rows="3"></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary btn-sm modal-close">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/admin-citas.js"></script>
</body>

</html>
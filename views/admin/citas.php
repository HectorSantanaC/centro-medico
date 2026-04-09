<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion de Citas</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php include __DIR__ . '/../layout/navbar-admin.php'; ?>

  <main class="main-content">
    <section class="crud-container">

      <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <?php if ($action === 'list'): ?>

        <div class="page-header">
          <h1>Agenda</h1>
          <a href="cita-online.php" class="btn btn-primary">+ Nueva Cita</a>
        </div>

        <form method="GET" class="filtros-form">
          <input type="date" name="fecha_desde" value="<?= $filtros['fecha_desde'] ?? '' ?>" placeholder="Desde">
          <input type="date" name="fecha_hasta" value="<?= $filtros['fecha_hasta'] ?? '' ?>" placeholder="Hasta">
          <select name="estado">
            <option value="">Todos los estados</option>
            <?php foreach ($estados as $est): ?>
              <option value="<?= $est ?>" <?= ($filtros['estado'] ?? '') === $est ? 'selected' : '' ?>>
                <?= ucfirst($est) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <select name="especialidad_id">
            <option value="">Todas las especialidades</option>
            <?php foreach ($especialidades as $esp): ?>
              <option value="<?= $esp['id'] ?>" <?= ($filtros['especialidad_id'] ?? '') == $esp['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($esp['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <select name="paciente_id">
            <option value="">Todos los pacientes</option>
            <?php foreach ($pacientes as $pac): ?>
              <option value="<?= $pac['id'] ?>" <?= ($filtros['paciente_id'] ?? '') == $pac['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($pac['nombre'] . ' ' . $pac['apellidos']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary">Filtrar</button>
          <a href="citas-crud.php" class="btn btn-secondary">Limpiar</a>
        </form>

        <?php if (empty($citas)): ?>
          <div class="message" style="background: #e7f3ff; color: #004085; border-left: 5px solid #007bff;">
            No hay citas registradas.
            <a href="cita-online.php" style="color: #007bff;">Crear primera cita</a>
          </div>
        <?php else: ?>
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
              <tbody>
                <?php foreach ($citas as $cita): ?>
                  <tr>
                    <td><?= htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellidos']) ?></td>
                    <td><?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellidos']) ?></td>
                    <td><?= htmlspecialchars($cita['especialidad_nombre']) ?></td>
                    <td><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
                    <td><?= date('H:i', strtotime($cita['hora'])) ?></td>
                    <td>
                      <span class="estado-badge estado-<?= $cita['estado'] ?>">
                        <?= $cita['estado'] ?>
                      </span>
                    </td>
                    <td class="actions">
                      <a href="?action=edit&id=<?= $cita['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                      <a href="?action=delete&id=<?= $cita['id'] ?>" class="btn btn-danger btn-sm btn-delete" data-confirm="¿Eliminar esta cita?">Eliminar</a>
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
                (<?= $totalCitas ?> citas)
              </span>
              
              <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-secondary">Siguiente →</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>


      <?php elseif ($action === 'edit'): ?>

        <?php
        $pacienteInfo = $pacienteInfo ?? null;
        ?>

        <div class="form-card">
          <h2>Editar Cita</h2>

          <form method="POST" class="form-grid">
            <?= csrf_field() ?>
            <div class="form-group">
              <label>Paciente</label>
              <input type="text" value="<?= htmlspecialchars(($pacienteInfo['nombre'] ?? '') . ' ' . ($pacienteInfo['apellidos'] ?? '')) ?>" disabled>
              <input type="hidden" name="paciente_id" value="<?= $citaEdit['paciente_id'] ?>">
            </div>

            <div class="form-group">
              <label>Especialidad *</label>
              <select name="especialidad_id" id="especialidadSelect" required>
                <option value="">Seleccionar especialidad</option>
                <?php foreach ($especialidades as $e): ?>
                  <option value="<?= $e['id'] ?>" <?= ($citaEdit['especialidad_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Medico *</label>
              <select name="medico_id" id="medicoSelect" required>
                <option value="">Seleccionar medico</option>
                <?php foreach ($medicos as $m): ?>
                  <option value="<?= $m['id'] ?>" data-especialidad="<?= $m['especialidad_id'] ?>" <?= ($citaEdit['medico_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nombre'] . ' ' . $m['apellidos']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Estado</label>
              <select name="estado">
                <?php foreach ($estados as $est): ?>
                  <option value="<?= $est ?>" <?= ($citaEdit['estado'] ?? 'pendiente') == $est ? 'selected' : '' ?>>
                    <?= ucfirst($est) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label>Fecha *</label>
              <input type="date" name="fecha" value="<?= $citaEdit['fecha'] ?? '' ?>" required>
            </div>

            <div class="form-group">
              <label>Hora *</label>
              <input type="time" name="hora" value="<?= $citaEdit['hora'] ?? '' ?>" required>
            </div>

            <div class="form-group full-width">
              <label>Notas</label>
              <textarea name="notas"><?= htmlspecialchars($citaEdit['notas'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-success">Actualizar Cita</button>
              <a href="citas-crud.php" class="btn btn-secondary">Cancelar</a>
            </div>
          </form>
        </div>

      <?php endif; ?>

    </section>
  </main>

  <script src="js/crud-citas.js"></script>
  <script src="js/scripts.js"></script>
</body>

</html>

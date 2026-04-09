<?php include __DIR__ . '/layout/header.php'; ?>

<section class="section mis-citas-section">
  
  <div class="mis-citas-header">
    <h1>Mis Citas</h1>
    <a href="cita-online.php" class="btn-nueva-cita">+ Nueva Cita</a>
  </div>
  
  <?php if ($message): ?>
    <div class="mis-citas-message <?= htmlspecialchars($messageType) ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>
  
  <?php if (empty($citas)): ?>
    <div class="mis-citas-empty">
      <p>No tienes citas registradas.</p>
      <a href="cita-online.php">Reserva tu primera cita</a>
    </div>
  <?php else: ?>
    <div class="mis-citas-table-wrapper">
      <table class="mis-citas-table">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Médico</th>
            <th>Especialidad</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($citas as $cita): ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
              <td><?= date('H:i', strtotime($cita['hora'])) ?></td>
              <td>
                Dr. <?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellidos']) ?>
              </td>
              <td><?= htmlspecialchars($cita['especialidad_nombre']) ?></td>
              <td>
                <span class="estado-badge estado-<?= $cita['estado'] ?>">
                  <?= ucfirst($cita['estado']) ?>
                </span>
              </td>
              <td>
                <?php if ($cita['estado'] === 'pendiente' || $cita['estado'] === 'confirmada'): ?>
                  <a href="?cancelar=<?= $cita['id'] ?>" 
                    class="btn-delete btn-cancelar"
                    data-confirm="¿Estás seguro de que quieres cancelar esta cita?">
                    Cancelar
                  </a>
                <?php else: ?>
                  <span class="estado-none">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
  
</section>

<?php include __DIR__ . '/layout/footer.php'; ?>

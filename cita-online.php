<?php
require_once __DIR__ . '/config/Database.php';

$page_title = 'Reservar Cita';
$db = Database::getInstance();

// ========================================
// GUARDAR CITA
// ========================================
if ($_POST) {
  $nombre = trim($_POST['nombre']);
  $email = trim($_POST['email']);
  $medico_id = (int)$_POST['medico_id'];
  $especialidad_id = (int)$_POST['especialidad_id'];
  $fecha_cita = $_POST['fecha_cita'];
  $hora_cita = $_POST['hora_cita'];

  // ✅ FIX: fecha_cita → fecha, hora_cita → hora
  $sql = "INSERT INTO citas (paciente_id, medico_id, especialidad_id, fecha, hora, estado, notas) 
          VALUES (1, ?, ?, ?, ?, 'pendiente', ?) RETURNING id";
  $cita_id = $db->insert($sql, [
    $medico_id,
    $especialidad_id,
    $fecha_cita,    // Input fecha_cita → DB fecha
    $hora_cita,     // Input hora_cita → DB hora
    "$nombre ($email)"
  ]);
  $mensaje_exito = "✅ Cita #$cita_id RESERVADA!<br>📅 $fecha_cita $hora_cita";
}

// ========================================
// DATOS FORM
// ========================================
$especialidad_id = $_GET['especialidad_id'] ?? '';
$medico_id = $_GET['medico_id'] ?? '';
$fecha_cita = $_GET['fecha_cita'] ?? '';

// WHERE activo = true
$especialidades = $db->fetchAll("SELECT id, nombre FROM especialidades WHERE activo = true ORDER BY nombre");

$medicos = [];
if ($especialidad_id) {
  $medicos = $db->fetchAll("
        SELECT m.id, m.nombre || ' ' || m.apellidos as nombre_completo
        FROM medicos m
        WHERE m.especialidad_id = ? AND m.activo = true
        ORDER BY m.apellidos
    ", [$especialidad_id]);
}
?>

<?php include './includes/header.php'; ?>

<?php if (isset($mensaje_exito)): ?>
  <div class="cita-success">
    <div style="font-size: 1.5rem;margin-bottom:1rem;">✅</div>
    <?= $mensaje_exito ?>
    <br><a href="">← Reservar otra cita</a>
  </div>
<?php endif; ?>

<section class="cita-section">
  <div class="cita-container">
    <!-- HEADER -->
    <div class="cita-header">
      <h2>Reserva tu cita en línea</h2>
      <p>Selecciona especialidad, médico, fecha y hora que se ajuste a tu disponibilidad</p>
    </div>

    <form method="<?= $medico_id && $fecha_cita ? 'POST' : 'GET' ?>" action="" class="cita-form">
      
      <!-- PASO 1: ESPECIALIDAD -->
      <div class="cita-paso">
        <div class="paso-numero">1</div>
        <div class="form-group">
          <label>Especialidad <span class="required">*</span></label>
          <div class="select-wrapper">
            <select name="especialidad_id" id="especialidad_id" required onchange="this.form.submit()">
              <option value="">Selecciona especialidad...</option>
              <?php foreach ($especialidades as $esp): ?>
                <option value="<?= $esp['id'] ?>" <?= ($especialidad_id == $esp['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($esp['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- PASO 2: MÉDICO -->
      <div class="cita-paso">
        <div class="paso-numero">2</div>
        <div class="form-group">
          <label>Médico <?php if ($especialidad_id): ?><span class="required">*</span><?php endif; ?></label>
          <div class="select-wrapper">
            <select name="medico_id" id="medico_id" <?= ($especialidad_id ? 'required' : '') ?> onchange="this.form.submit()">
              <?php if (empty($medicos)): ?>
                <option value="">Selecciona especialidad primero</option>
              <?php else: ?>
                <option value="">Selecciona médico...</option>
                <?php foreach ($medicos as $med): ?>
                  <option value="<?= $med['id'] ?>" <?= ($medico_id == $med['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($med['nombre_completo']) ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- PASO 3: FECHA y HORA (lado a lado) -->
      <div class="cita-pasos-row">
        <div class="cita-paso">
          <div class="paso-numero">3</div>
          <div class="form-group">
            <label>Fecha <span class="required">*</span></label>
            <div class="input-wrapper">
              <input type="date" name="fecha_cita"
                min="<?= date('Y-m-d') ?>"
                value="<?= htmlspecialchars($fecha_cita) ?>"
                <?= ($medico_id ? 'required' : '') ?>
                onchange="this.form.submit()">
            </div>
          </div>
        </div>

        <div class="cita-paso">
          <div class="paso-numero">4</div>
          <div class="form-group">
            <label>Hora <span class="required">*</span></label>
            <div class="select-wrapper">
              <select name="hora_cita" <?= ($medico_id ? 'required' : '') ?>>
                <option value="">Selecciona hora...</option>
                <option value="09:00">09:00</option>
                <option value="09:30">09:30</option>
                <option value="10:00">10:00</option>
                <option value="10:30">10:30</option>
                <option value="11:00">11:00</option>
                <option value="11:30">11:30</option>
                <option value="12:00">12:00</option>
                <option value="14:00">14:00</option>
                <option value="14:30">14:30</option>
                <option value="15:00">15:00</option>
                <option value="15:30">15:30</option>
                <option value="16:00">16:00</option>
                <option value="16:30">16:30</option>
                <option value="17:00">17:00</option>
                <option value="17:30">17:30</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- PASO 5: DATOS PACIENTE -->
      <?php if ($medico_id): ?>
      <div class="cita-paso">
        <div class="paso-numero">5</div>
        <div class="form-row">
          <div class="form-group">
            <label>Tu Nombre <span class="required">*</span></label>
            <div class="input-wrapper">
              <input type="text" name="nombre" placeholder="Ej: Juan García" required>
            </div>
          </div>
          <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <div class="input-wrapper">
              <input type="email" name="email" placeholder="Ej: juan@email.com" required>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- BOTÓN SUBMIT -->
      <div class="cita-actions">
        <button type="submit" class="btn-cita">
          <span>Confirmar Cita</span>
          <i class="fas fa-arrow-right"></i>
        </button>
      </div>

    </form>
  </div>
</section>

<?php include './includes/footer.php'; ?>
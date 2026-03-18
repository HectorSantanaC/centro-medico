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
  <div style="background:#d4edda;color:#155724;padding:1.5rem;margin:2rem auto;max-width:600px;border-radius:10px;text-align:center;">
    <?= $mensaje_exito ?><br><a href="" style="color:#155724;">← Nueva</a>
  </div>
<?php endif; ?>

<div class="cita-container">
  <div class="cita-titulo">
    <h2><i class="fas fa-calendar-plus"></i> Reserva tu cita</h2>
  </div>

  <form method="<?= $medico_id && $fecha_cita ? 'POST' : 'GET' ?>" action="" class="cita-form">
    <!-- ESPECIALIDAD -->
    <div class="form-group">
      <label>Especialidad *</label>
      <select name="especialidad_id" id="especialidad_id" required onchange="this.form.submit()">
        <option value="">Selecciona especialidad...</option>
        <?php foreach ($especialidades as $esp): ?>
          <option value="<?= $esp['id'] ?>" <?= ($especialidad_id == $esp['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($esp['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- MÉDICO -->
    <div class="form-group">
      <label>Médico</label>
      <select name="medico_id" id="medico_id" onchange="this.form.submit()">
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

    <!-- FECHA -->
    <div class="form-group">
      <label>Fecha *</label>
      <input type="date" name="fecha_cita"
        min="<?= date('Y-m-d') ?>"
        value="<?= htmlspecialchars($fecha_cita) ?>"
        onchange="this.form.submit()">
    </div>

    <!-- HORA -->
    <div class="form-group">
      <label>Hora *</label>
      <select name="hora_cita" <?= ($medico_id ? 'required' : '') ?>>
        <option value="">Selecciona hora...</option>
        <option value="09:00">09:00</option>
        <option value="09:30">09:30</option>
        <option value="10:00">10:00</option>
        <option value="10:30">10:30</option>
        <option value="11:00">11:00</option>
        <option value="11:30">11:30</option>
        <option value="12:00">12:00</option>
        <option value="16:00">16:00</option>
        <option value="16:30">16:30</option>
        <option value="17:00">17:00</option>
      </select>
    </div>

    <!-- DATOS PACIENTE -->
    <?php if ($medico_id): ?>
      <div class="form-group">
        <label>Tu Nombre *</label>
        <input type="text" name="nombre" required>
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required>
      </div>
    <?php endif; ?>

    <button type="submit" class="btn-cita">Reservar Cita</button>
  </form>
</div>

<?php include './includes/footer.php'; ?>
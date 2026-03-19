<?php
require_once __DIR__ . '/config/Database.php';

$page_title = 'Reservar Cita';
$db = Database::getInstance();

// ========================================
// AJAX: OBTENER MÉDICOS
// ========================================
if (isset($_GET['get_medicos']) && $_GET['get_medicos'] == '1') {
  header('Content-Type: application/json');
  $espId = (int)($_GET['especialidad_id'] ?? 0);

  if ($espId > 0) {
    $medicos = $db->fetchAll("
      SELECT m.id, m.nombre || ' ' || m.apellidos as nombre_completo
      FROM medicos m
      WHERE m.especialidad_id = ? AND m.activo = true
      ORDER BY m.apellidos
    ", [$espId]);
    echo json_encode($medicos);
  } else {
    echo json_encode([]);
  }
  exit;
}

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

  $paciente_id = $_SESSION['usuario_id'] ?? 1;

  $sql = "INSERT INTO citas (paciente_id, medico_id, especialidad_id, fecha, hora, estado, notas) 
          VALUES (?, ?, ?, ?, ?, 'pendiente', ?) RETURNING id";
  $cita_id = $db->insert($sql, [
    $paciente_id,
    $medico_id,
    $especialidad_id,
    $fecha_cita,
    $hora_cita,
    "$nombre ($email)"
  ]);
  $mensaje_exito = "✅ Cita #$cita_id RESERVADA!<br>📅 $fecha_cita $hora_cita";
}

// ========================================
// DATOS FORM
// ========================================
$especialidades = $db->fetchAll("SELECT id, nombre FROM especialidades WHERE activo = true ORDER BY nombre");
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
      <a href="citas-crud.php" class="btn-ver-citas" style="margin-top: 10px; display: inline-block; background: #2c5282; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">📋 Ver citas agendadas</a>
    </div>

    <form method="POST" action="" class="cita-form" id="citaForm">
      <input type="hidden" name="ajax" value="1">

      <!-- PASO 1: ESPECIALIDAD -->
      <div class="cita-paso">
        <div class="paso-numero">1</div>
        <div class="form-group">
          <label>Especialidad <span class="required">*</span></label>
          <div class="select-wrapper">
            <select name="especialidad_id" id="especialidad_id" required>
              <option value="">Selecciona especialidad...</option>
              <?php foreach ($especialidades as $esp): ?>
                <option value="<?= $esp['id'] ?>">
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
          <label>Médico <span class="required">*</span></label>
          <div class="select-wrapper">
            <select name="medico_id" id="medico_id">
              <option value="">Selecciona especialidad primero</option>
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
              <input type="date" name="fecha_cita" id="fecha_cita"
                min="<?= date('Y-m-d') ?>">
            </div>
          </div>
        </div>

        <div class="cita-paso">
          <div class="paso-numero">4</div>
          <div class="form-group">
            <label>Hora <span class="required">*</span></label>
            <div class="select-wrapper">
              <select name="hora_cita">
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
      <div class="cita-paso" id="pasoPaciente" style="display: none;">
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const espSelect = document.getElementById('especialidad_id');
    const medSelect = document.getElementById('medico_id');
    const fechaInput = document.getElementById('fecha_cita');
    const horaSelect = document.querySelector('select[name="hora_cita"]');
    const pasoPaciente = document.getElementById('pasoPaciente');

    function updateRequiredFields() {
      const hasMedico = medSelect.value !== '';
      fechaInput.required = hasMedico;
      horaSelect.required = hasMedico;
      pasoPaciente.style.display = hasMedico ? 'block' : 'none';
    }

    espSelect.addEventListener('change', function() {
      const espId = this.value;
      medSelect.innerHTML = '<option value="">Cargando médicos...</option>';
      medSelect.required = !!espId;

      if (!espId) {
        medSelect.innerHTML = '<option value="">Selecciona especialidad primero</option>';
        updateRequiredFields();
        return;
      }

      fetch('cita-online.php?get_medicos=1&especialidad_id=' + espId)
        .then(r => r.json())
        .then(data => {
          let options = '<option value="">Selecciona médico...</option>';
          data.forEach(m => {
            options += `<option value="${m.id}">${m.nombre_completo}</option>`;
          });
          medSelect.innerHTML = options;
          updateRequiredFields();
        })
        .catch(() => {
          medSelect.innerHTML = '<option value="">Error al cargar médicos</option>';
        });
    });

    medSelect.addEventListener('change', updateRequiredFields);
  });
</script>
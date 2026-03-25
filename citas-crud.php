<?php
require_once 'config/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();

// Verificar permisos
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'gestor'])) {
  header('Location: login.php');
  exit;
}

$action = $_REQUEST['action'] ?? 'list';
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = [
    'paciente_id'     => (int)($_POST['paciente_id'] ?? 0),
    'medico_id'       => (int)($_POST['medico_id'] ?? 0),
    'especialidad_id' => (int)($_POST['especialidad_id'] ?? 0),
    'fecha'           => $_POST['fecha'] ?? '',
    'hora'            => $_POST['hora'] ?? '',
    'estado'          => $_POST['estado'] ?? 'pendiente',
    'notas'           => trim($_POST['notas'] ?? '')
  ];

  /**
 * trim() elimina espacios en blanco al principio y final.
   * Ejemplo:
   *   trim("  hola  ") → "hola"
   * 
   * ¿Por qué en notas? Para evitar guardar solo espacios vacíos.
   */

  // --------------------------------------------------
  // ACCIÓN: EDITAR CITA (UPDATE)
  // --------------------------------------------------
  if ($action === 'edit' && $id) {

    /**
     * try { ... } catch (Exception $e) { ... }
     * 
     * "try" = "INTENTA ejecutar este código"
     * "catch" = "SI HAY UN ERROR, captúralo aquí"
     * 
     * $e es el objeto Exception con información del error.
     * $e->getMessage() devuelve el texto del error.
     * 
     * ¿Por qué? Para mostrar un mensaje bonito al usuario
     * en vez de un error técnico de PHP.
     */
    try {
      /**
       * SQL UPDATE - Modificar una cita existente
       * 
       * :nombre_columna = Marcadores de posición con NOMBRE
       * (a diferencia de ? que son posicionales)
       * 
       * Ejemplo:
       *   UPDATE citas SET paciente_id=3, fecha='2024-06-15' WHERE id=5
       */
      $sql = "UPDATE citas SET 
                  paciente_id=:paciente_id, 
                  medico_id=:medico_id, 
                  especialidad_id=:especialidad_id, 
                  fecha=:fecha, 
                  hora=:hora, 
                  estado=:estado, 
                  notas=:notas 
              WHERE id=:id";

      // Añadir el ID a los datos para el WHERE
      $data['id'] = $id;

      // Ejecutar la consulta
      $db->execute($sql, $data);

      // Mensaje de éxito
      $message = 'Cita actualizada exitosamente';
      $messageType = 'success';

      // Volver al listado después de editar
      $action = 'list';
    } catch (Exception $e) {
      // Capturar el error y mostrar mensaje
      $message = 'Error al actualizar la cita: ' . $e->getMessage();
      $messageType = 'error';
    }
  }
}

// --------------------------------------------------
// ACCIÓN: ELIMINAR CITA (DELETE)
// --------------------------------------------------
if ($action === 'delete' && $id) {
  try {
    /**
     * SQL DELETE - Borrar una cita
     * 
     * Ejemplo:
     *   DELETE FROM citas WHERE id = 5
     */
    $sql = "DELETE FROM citas WHERE id = :id";

    $db->execute($sql, ['id' => $id]);

    $message = 'Cita eliminada exitosamente';
    $messageType = 'success';

    // Volver al listado después de eliminar
    $action = 'list';
  } catch (Exception $e) {
    $message = 'Error al eliminar la cita: ' . $e->getMessage();
    $messageType = 'error';
  }
}

$especialidades = $db->fetchAll("SELECT * FROM especialidades WHERE activo = true ORDER BY nombre");
$medicos = $db->fetchAll("SELECT m.*, e.nombre as especialidad_nombre FROM medicos m LEFT JOIN especialidades e ON m.especialidad_id = e.id WHERE m.activo = true ORDER BY m.nombre");
$pacientes = $db->fetchAll("SELECT * FROM usuarios WHERE rol = 'paciente' ORDER BY nombre");

$citas = [];
$citaEdit = null;

if ($action === 'list') {
  $citas = $db->fetchAll("SELECT c.*, u.nombre as paciente_nombre, u.apellidos as paciente_apellidos, m.nombre as medico_nombre, m.apellidos as medico_apellidos, e.nombre as especialidad_nombre FROM citas c LEFT JOIN usuarios u ON c.paciente_id = u.id LEFT JOIN medicos m ON c.medico_id = m.id LEFT JOIN especialidades e ON c.especialidad_id = e.id ORDER BY c.fecha DESC, c.hora DESC");
} elseif ($action === 'edit' && $id) {
  $stmt = $pdo->prepare("SELECT * FROM citas WHERE id = ?");
  $stmt->execute([$id]);
  $citaEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];
$active = 'citas';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Citas</title>

  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <?php include 'includes/navbar-admin.php'; ?>

  <main class="main-content">
    <section class="crud-container">

      <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <?php if ($action === 'list'): ?>

        <div class="page-header">
          <h1>📅 Gestión de Citas</h1>
          <!-- Enlace para crear una nueva cita -->
          <a href="cita-online.php" class="btn btn-primary">+ Nueva Cita</a>
        </div>

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
                  <th>Médico</th>
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
                      <a href="?action=delete&id=<?= $cita['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta cita?')">Eliminar</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>


      <?php elseif ($action === 'edit'): ?>

        <?php
        if (isset($citaEdit['paciente_id'])) {
          $stmtPaciente = $pdo->prepare("SELECT nombre, apellidos FROM usuarios WHERE id = ?");
          $stmtPaciente->execute([$citaEdit['paciente_id']]);
          $pacienteInfo = $stmtPaciente->fetch(PDO::FETCH_ASSOC);
        }
        ?>

        <div class="form-card">
          <h2>Editar Cita</h2>

          <form method="POST" class="form-grid">
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
              <label>Médico *</label>
              <select name="medico_id" id="medicoSelect" required>
                <option value="">Seleccionar médico</option>
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
</body>

</html>
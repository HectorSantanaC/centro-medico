<?php
require_once 'config/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'paciente_id' => (int)($_POST['paciente_id'] ?? 0),
        'medico_id' => (int)($_POST['medico_id'] ?? 0),
        'especialidad_id' => (int)($_POST['especialidad_id'] ?? 0),
        'fecha' => $_POST['fecha'] ?? '',
        'hora' => $_POST['hora'] ?? '',
        'estado' => $_POST['estado'] ?? 'pendiente',
        'notas' => trim($_POST['notas'] ?? '')
    ];

    if ($action === 'create') {
        try {
            $sql = "INSERT INTO citas (paciente_id, medico_id, especialidad_id, fecha, hora, estado, notas) 
                    VALUES (:paciente_id, :medico_id, :especialidad_id, :fecha, :hora, :estado, :notas)";
            $db->execute($sql, $data);
            $message = 'Cita creada exitosamente';
            $messageType = 'success';
            $action = 'list';
        } catch (Exception $e) {
            $message = 'Error al crear la cita: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'edit' && $id) {
        try {
            $sql = "UPDATE citas SET paciente_id=:paciente_id, medico_id=:medico_id, 
                    especialidad_id=:especialidad_id, fecha=:fecha, hora=:hora, 
                    estado=:estado, notas=:notas WHERE id=:id";
            $data['id'] = $id;
            $db->execute($sql, $data);
            $message = 'Cita actualizada exitosamente';
            $messageType = 'success';
            $action = 'list';
        } catch (Exception $e) {
            $message = 'Error al actualizar la cita: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        $sql = "DELETE FROM citas WHERE id = :id";
        $db->execute($sql, ['id' => $id]);
        $message = 'Cita eliminada exitosamente';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error al eliminar la cita: ' . $e->getMessage();
        $messageType = 'error';
    }
    $action = 'list';
}

$especialidades = $db->fetchAll("SELECT * FROM especialidades WHERE activo = true ORDER BY nombre");
$medicos = $db->fetchAll("SELECT m.*, e.nombre as especialidad_nombre FROM medicos m 
                           LEFT JOIN especialidades e ON m.especialidad_id = e.id 
                           WHERE m.activo = true ORDER BY m.nombre");
$pacientes = $db->fetchAll("SELECT * FROM usuarios WHERE rol = 'paciente' ORDER BY nombre");

$citas = [];
$citaEdit = null;

if ($action === 'list') {
    $citas = $db->fetchAll("SELECT c.*, 
                            u.nombre as paciente_nombre, u.apellidos as paciente_apellidos,
                            m.nombre as medico_nombre, m.apellidos as medico_apellidos,
                            e.nombre as especialidad_nombre
                            FROM citas c
                            LEFT JOIN usuarios u ON c.paciente_id = u.id
                            LEFT JOIN medicos m ON c.medico_id = m.id
                            LEFT JOIN especialidades e ON c.especialidad_id = e.id
                            ORDER BY c.fecha DESC, c.hora DESC");
} elseif ($action === 'edit' && $id) {
    $citaEdit = $db->fetchAll("SELECT * FROM citas WHERE id = :id", ['id' => $id])[0] ?? null;
}

$estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas - <?= APP_NOMBRE ?></title>
    <style>
        .crud-container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
        .crud-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .crud-header h1 { font-size: 2rem; color: #1a1a2e; font-weight: 600; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; cursor: pointer; border: none; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.875rem; }
        .message { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; animation: slideDown 0.5s ease; }
        .message.success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .message.error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }
        .table-container { overflow-x: auto; background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: 600; color: #1a1a2e; }
        tr:hover { background: #f8f9fa; }
        .estado-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: capitalize; }
        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-confirmada { background: #cce5ff; color: #004085; }
        .estado-completada { background: #d4edda; color: #155724; }
        .estado-cancelada { background: #f8d7da; color: #721c24; }
        .actions { display: flex; gap: 0.5rem; }
        .form-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); padding: 2rem; max-width: 700px; margin: 0 auto; }
        .form-card h2 { font-size: 1.5rem; color: #1a1a2e; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f0f0f0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group.full-width { grid-column: span 2; }
        .form-group label { display: block; font-weight: 600; color: #1a1a2e; margin-bottom: 0.5rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; font-family: inherit; transition: all 0.3s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1); }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #f0f0f0; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .form-group.full-width { grid-column: span 1; } }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="crud-container">
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="crud-header">
                    <h1>Gestión de Citas</h1>
                    <a href="?action=create" class="btn btn-primary">+ Nueva Cita</a>
                </div>

                <?php if (empty($citas)): ?>
                    <div class="message info" style="background: #e7f3ff; color: #004085; border-left: 5px solid #007bff;">
                        No hay citas registradas. <a href="?action=create" style="color: #007bff;">Crear primera cita</a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
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
                                        <td><?= $cita['id'] ?></td>
                                        <td><?= htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellidos']) ?></td>
                                        <td><?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellidos']) ?></td>
                                        <td><?= htmlspecialchars($cita['especialidad_nombre']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
                                        <td><?= date('H:i', strtotime($cita['hora'])) ?></td>
                                        <td><span class="estado-badge estado-<?= $cita['estado'] ?>"><?= $cita['estado'] ?></span></td>
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

            <?php elseif ($action === 'create' || $action === 'edit'): ?>
                <div class="form-card">
                    <h2><?= $action === 'create' ? 'Nueva Cita' : 'Editar Cita' ?></h2>
                    <form method="POST" class="form-grid">
                        <div class="form-group">
                            <label>Paciente *</label>
                            <select name="paciente_id" required>
                                <option value="">Seleccionar paciente</option>
                                <?php foreach ($pacientes as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($citaEdit['paciente_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                                        <?= htmlspecialchars($m['nombre'] . ' ' . $m['apellidos'] . ' - ' . $m['especialidad_nombre']) ?>
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
                            <button type="submit" class="btn btn-success"><?= $action === 'create' ? 'Crear Cita' : 'Actualizar Cita' ?></button>
                            <a href="citas-crud.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        const especialidadSelect = document.getElementById('especialidadSelect');
        const medicoSelect = document.getElementById('medicoSelect');
        
        if (especialidadSelect && medicoSelect) {
            function filtrarMedicos() {
                const especialidadId = especialidadSelect.value;
                Array.from(medicoSelect.options).forEach(opt => {
                    if (!opt.value) return;
                    opt.style.display = (!especialidadId || opt.dataset.especialidad === especialidadId) ? '' : 'none';
                });
                if (especialidadId && medicoSelect.value && medicoSelect.options[medicoSelect.selectedIndex].dataset.especialidad !== especialidadId) {
                    medicoSelect.value = '';
                }
            }
            especialidadSelect.addEventListener('change', filtrarMedicos);
            filtrarMedicos();
        }
    </script>
</body>
</html>

<?php
/**
 * =====================================================
 * mis-citas.php
 * VER LAS CITAS DEL PACIENTE LOGUEADO
 * =====================================================
 * 
 * Muestra las citas SOLO del paciente que ha iniciado sesión.
 * Los pacientes pueden:
 *   - Ver sus citas
 *   - Cancelar citas (cambiar estado a 'cancelada')
 *   - Reservar nuevas citas
 * 
 * =====================================================
 */

require_once __DIR__ . '/config/Database.php';
$db = Database::getInstance();

// ============================================================
// VERIFICAR QUE EL USUARIO ESTÁ LOGUEADO
// ============================================================
if (!isset($_SESSION['usuario_id'])) {
    // No está logueado → redirigir al login
    header('Location: login.php');
    exit;
}

$page_title = 'Mis Citas';
$usuario_id = $_SESSION['usuario_id'];

// ============================================================
// PROCESAR CANCELACIÓN DE CITA
// ============================================================
if (isset($_GET['cancelar']) && $_GET['cancelar']) {
    $cita_id = (int)$_GET['cancelar'];
    
    try {
        // Solo actualizar si la cita pertenece al usuario
        $sql = "UPDATE citas SET estado = 'cancelada' 
                WHERE id = ? AND paciente_id = ?";
        $db->execute($sql, [$cita_id, $usuario_id]);
        
        $mensaje = 'Cita cancelada correctamente';
        $mensaje_tipo = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error al cancelar la cita';
        $mensaje_tipo = 'error';
    }
}

// ============================================================
// CARGAR LAS CITAS DEL PACIENTE
// ============================================================
$citas = $db->fetchAll(
    "SELECT c.*, 
            m.nombre as medico_nombre, 
            m.apellidos as medico_apellidos,
            e.nombre as especialidad_nombre
     FROM citas c
     LEFT JOIN medicos m ON c.medico_id = m.id
     LEFT JOIN especialidades e ON c.especialidad_id = e.id
     WHERE c.paciente_id = ?
     ORDER BY c.fecha DESC, c.hora DESC",
    [$usuario_id]
);

$estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];
?>

<?php include 'includes/header.php'; ?>

<section class="section" style="max-width: 1000px; margin: 30px auto; padding: 0 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Mis Citas</h1>
        <a href="cita-online.php" style="background: #2c5282; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
            + Nueva Cita
        </a>
    </div>
    
    <!-- Mensaje de éxito/error -->
    <?php if (isset($mensaje)): ?>
        <div style="padding: 15px; border-radius: 5px; margin-bottom: 20px; 
                    background: <?= $mensaje_tipo === 'success' ? '#d4edda; color: #155724' : '#f8d7da; color: #721c24' ?>;">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>
    
    <!-- Tabla de citas -->
    <?php if (empty($citas)): ?>
        <div style="background: #f8f9fa; padding: 40px; text-align: center; border-radius: 8px;">
            <p style="font-size: 1.1rem; color: #666;">No tienes citas registradas.</p>
            <a href="cita-online.php" style="color: #2c5282;">Reserva tu primera cita</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background: #2c5282; color: white;">
                        <th style="padding: 15px; text-align: left;">Fecha</th>
                        <th style="padding: 15px; text-align: left;">Hora</th>
                        <th style="padding: 15px; text-align: left;">Médico</th>
                        <th style="padding: 15px; text-align: left;">Especialidad</th>
                        <th style="padding: 15px; text-align: left;">Estado</th>
                        <th style="padding: 15px; text-align: left;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;"><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
                            <td style="padding: 15px;"><?= date('H:i', strtotime($cita['hora'])) ?></td>
                            <td style="padding: 15px;">
                                Dr. <?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellidos']) ?>
                            </td>
                            <td style="padding: 15px;"><?= htmlspecialchars($cita['especialidad_nombre']) ?></td>
                            <td style="padding: 15px;">
                                <span style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem;
                                    <?php 
                                    switch($cita['estado']) {
                                        case 'pendiente': echo 'background: #fff3cd; color: #856404;'; break;
                                        case 'confirmada': echo 'background: #d4edda; color: #155724;'; break;
                                        case 'completada': echo 'background: #cce5ff; color: #004085;'; break;
                                        case 'cancelada': echo 'background: #f8d7da; color: #721c24;'; break;
                                    }
                                    ?>">
                                    <?= ucfirst($cita['estado']) ?>
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <?php if ($cita['estado'] === 'pendiente' || $cita['estado'] === 'confirmada'): ?>
                                    <a href="?cancelar=<?= $cita['id'] ?>" 
                                       onclick="return confirm('¿Estás seguro de que quieres cancelar esta cita?');"
                                       style="color: #dc3545; text-decoration: none;">
                                        Cancelar
                                    </a>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
</section>

<?php include 'includes/footer.php'; ?>

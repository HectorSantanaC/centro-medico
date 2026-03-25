<?php

require_once __DIR__ . '/config/Database.php';
$db = Database::getInstance();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'gestor'])) {
    header('Location: login.php');
    exit;
}

$es_admin = $_SESSION['usuario_rol'] === 'admin';
$active = 'inicio';

$stats = [];

if ($es_admin) {
    $stats['usuarios'] = $db->fetchAll("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'paciente'")[0]['total'];
    $stats['citas'] = $db->fetchAll("SELECT COUNT(*) as total FROM citas")[0]['total'];
}

$stats['especialidades'] = $db->fetchAll("SELECT COUNT(*) as total FROM especialidades WHERE activo = true")[0]['total'];
$stats['medicos'] = $db->fetchAll("SELECT COUNT(*) as total FROM medicos WHERE activo = true")[0]['total'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>

    <link rel="stylesheet" href="css/admin.css">

</head>
<body>
    <?php include './includes/navbar-admin.php' ?> 

    <main class="main-content">
        <div class="page-header">
            <h1>Bienvenido al Panel de Administración</h1>
            <p>Gestiona los contenidos y usuarios del sistema</p>
        </div>

        <?php if ($es_admin): ?>
        <h2 class="section-title">📊 Estadísticas</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $stats['usuarios'] ?></div>
                <div class="label">Pacientes registrados</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['citas'] ?></div>
                <div class="label">Citas totales</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['especialidades'] ?></div>
                <div class="label">Especialidades</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['medicos'] ?></div>
                <div class="label">Médicos</div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>

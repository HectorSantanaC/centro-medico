<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include __DIR__ . '/../layout/navbar-admin.php' ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Bienvenido al Panel de Administración</h1>
            <p>Gestiona los contenidos y usuarios del sistema</p>
        </div>

        <?php if ($isAdmin): ?>
        <h2 class="section-title">Estadisticas</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $stats['patients'] ?></div>
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
                <div class="label">Medicos</div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>

</html>

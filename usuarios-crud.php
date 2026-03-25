<?php

require_once __DIR__ . '/config/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$action = $_REQUEST['action'] ?? 'list';
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellidos' => trim($_POST['apellidos'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'rol' => $_POST['rol'] ?? 'paciente'
    ];

    if ($action === 'create') {
        try {
            if (empty($data['nombre']) || empty($data['apellidos']) || empty($data['email'])) {
                throw new Exception('Todos los campos obligatorios deben estar completos');
            }

            $passwordHash = !empty($data['password']) ? $data['password'] : 'password123';

            $db->insert(
                "INSERT INTO usuarios (nombre, apellidos, email, password, rol) VALUES (?, ?, ?, ?, ?)",
                [$data['nombre'], $data['apellidos'], $data['email'], $passwordHash, $data['rol']]
            );

            $message = 'Usuario creado exitosamente';
            $messageType = 'success';
            $action = 'list';
        } catch (Exception $e) {
            $message = 'Error al crear usuario: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'edit' && $id) {
        try {
            if (empty($data['nombre']) || empty($data['apellidos']) || empty($data['email'])) {
                throw new Exception('Todos los campos obligatorios deben estar completos');
            }

            if (!empty($data['password'])) {
                $db->execute(
                    "UPDATE usuarios SET nombre=?, apellidos=?, email=?, password=?, rol=? WHERE id=?",
                    [$data['nombre'], $data['apellidos'], $data['email'], $data['password'], $data['rol'], $id]
                );
            } else {
                $db->execute(
                    "UPDATE usuarios SET nombre=?, apellidos=?, email=?, rol=? WHERE id=?",
                    [$data['nombre'], $data['apellidos'], $data['email'], $data['rol'], $id]
                );
            }

            $message = 'Usuario actualizado exitosamente';
            $messageType = 'success';
            $action = 'list';
        } catch (Exception $e) {
            $message = 'Error al actualizar usuario: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if ($action === 'delete' && $id) {
    try {
        $db->execute("DELETE FROM usuarios WHERE id = ?", [$id]);
        $message = 'Usuario eliminado exitosamente';
        $messageType = 'success';
        $action = 'list';
    } catch (Exception $e) {
        $message = 'Error al eliminar usuario: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$usuarios = [];
$usuarioEdit = null;
$roles = ['admin', 'gestor', 'paciente'];

if ($action === 'list') {
    $usuarios = $db->fetchAll("SELECT * FROM usuarios ORDER BY created_at DESC");
} elseif ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuarioEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2c5282 0%, #1a365d 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar-header h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .sidebar-header span {
            font-size: 12px;
            opacity: 0.7;
        }
        .sidebar-menu {
            flex: 1;
        }
        .sidebar-menu a {
            display: block;
            padding: 15px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #4fd1c5;
        }
        .sidebar-menu a .icon {
            margin-right: 10px;
            width: 20px;
            display: inline-block;
        }
        .sidebar-footer {
            padding: 20px 25px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-footer a:hover {
            color: white;
        }
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h1 {
            color: #2c5282;
            font-size: 28px;
        }
        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #2c5282;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        .btn-primary {
            background: #2c5282;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            max-width: 600px;
        }
        .form-card h2 {
            color: #2c5282;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2c5282;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        .rol-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        .rol-admin {
            background: #dc3545;
            color: white;
        }
        .rol-gestor {
            background: #fd7e14;
            color: white;
        }
        .rol-paciente {
            background: #28a745;
            color: white;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #2c5282;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .user-info strong {
            display: block;
            margin-bottom: 3px;
        }
        .user-info span {
            font-size: 12px;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="sidebar-header">
            <h2>Centro Médico TAC7</h2>
            <span>Panel de Administración</span>
        </div>
        
        <div class="user-info">
            <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
            <span><?= ucfirst($_SESSION['usuario_rol']) ?></span>
        </div>

        <div class="sidebar-menu">
            <a href="admin.php">
                <span class="icon">🏠</span> Inicio
            </a>
            <a href="usuarios-crud.php" class="active">
                <span class="icon">👥</span> Usuarios
            </a>
            <a href="citas-crud.php">
                <span class="icon">📅</span> Citas
            </a>
            <a href="noticias.php">
                <span class="icon">📰</span> Contenido
            </a>
        </div>

        <div class="sidebar-footer">
            <a href="logout.php">
                <span class="icon">🚪</span> Cerrar Sesión
            </a>
        </div>
    </nav>

    <main class="main-content">
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <div class="page-header">
                <h1>👥 Gestión de Usuarios</h1>
                <a href="?action=create" class="btn btn-primary">+ Nuevo Usuario</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Alta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= $usuario['id'] ?></td>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['apellidos']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td>
                                    <span class="rol-badge rol-<?= $usuario['rol'] ?>">
                                        <?= ucfirst($usuario['rol']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($usuario['created_at'])) ?></td>
                                <td class="actions">
                                    <a href="?action=edit&id=<?= $usuario['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                                    <a href="?action=delete&id=<?= $usuario['id'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($action === 'create' || $action === 'edit'): ?>
            <a href="usuarios-crud.php" class="back-link">← Volver al listado</a>

            <div class="form-card">
                <h2><?= $action === 'create' ? 'Crear' : 'Editar' ?> Usuario</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required 
                               value="<?= htmlspecialchars($usuarioEdit['nombre'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Apellidos *</label>
                        <input type="text" name="apellidos" required 
                               value="<?= htmlspecialchars($usuarioEdit['apellidos'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required 
                               value="<?= htmlspecialchars($usuarioEdit['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Contraseña <?= $action === 'edit' ? '(dejar vacío para mantener)' : '*' ?></label>
                        <input type="text" name="password" 
                               placeholder="<?= $action === 'edit' ? 'Sin cambios' : 'Contraseña por defecto: password123' ?>">
                    </div>

                    <div class="form-group">
                        <label>Rol *</label>
                        <select name="rol" required>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol ?>" 
                                        <?= ($usuarioEdit['rol'] ?? 'paciente') === $rol ? 'selected' : '' ?>>
                                    <?= ucfirst($rol) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?= $action === 'create' ? 'Crear Usuario' : 'Guardar Cambios' ?>
                        </button>
                        <a href="usuarios-crud.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

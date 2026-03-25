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
$active = 'usuarios';

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

    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include './includes/navbar-admin.php' ?> 

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

<?php
/**
 * =====================================================
 * registro.php
 * FORMULARIO DE REGISTRO DE NUEVOS PACIENTES
 * =====================================================
 * 
 * Este archivo permite a nuevos usuarios registrarse
 * para poder reservar citas.
 * 
 * FLUJO:
 *   1. Mostrar formulario de registro (GET)
 *   2. Procesar datos enviados (POST)
 *   3. Validar que el email no exista
 *   4. Crear usuario en la base de datos
 *   5. Redirigir al login
 * 
 * =====================================================
 */

require_once __DIR__ . '/config/Database.php';
$db = Database::getInstance();

$page_title = 'Registro';

// ============================================================
// PROCESAR FORMULARIO DE REGISTRO
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recoger datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Array para errores
    $errores = [];
    
    // ========================================================
    // VALIDACIONES
    // ========================================================
    
    // Validar nombre (no vacío, mínimo 2 caracteres)
    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio';
    } elseif (strlen($nombre) < 2) {
        $errores[] = 'El nombre debe tener al menos 2 caracteres';
    }
    
    // Validar apellidos (no vacío)
    if (empty($apellidos)) {
        $errores[] = 'Los apellidos son obligatorios';
    }
    
    // Validar email (formato básico)
    if (empty($email)) {
        $errores[] = 'El email es obligatorio';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El formato del email no es válido';
    }
    
    // Validar contraseña (mínimo 6 caracteres)
    if (empty($password)) {
        $errores[] = 'La contraseña es obligatoria';
    } elseif (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    // Validar confirmación de contraseña
    if ($password !== $password_confirm) {
        $errores[] = 'Las contraseñas no coinciden';
    }
    
    // ========================================================
    // COMPROBAR SI EL EMAIL YA EXISTE
    // ========================================================
    if (empty($errores)) {
        $existe = $db->fetchAll(
            "SELECT id FROM usuarios WHERE email = ?",
            [$email]
        );
        
        if (!empty($existe)) {
            $errores[] = 'Este email ya está registrado';
        }
    }
    
    // ========================================================
    // SI NO HAY ERRORES, CREAR EL USUARIO
    // ========================================================
    if (empty($errores)) {
        
        // Guardar contraseña en texto plano (para el proyecto FCT)
        // En producción se usaría password_hash()
        $password_guardada = $password;
        
        try {
            // Insertar usuario en la base de datos
            $sql = "INSERT INTO usuarios (nombre, apellidos, email, password, rol) 
                    VALUES (?, ?, ?, ?, 'paciente') RETURNING id";
            
            $usuario_id = $db->insert($sql, [
                $nombre,
                $apellidos,
                $email,
                $password_guardada
            ]);
            
            // Registro exitoso - redirigir al login
            header('Location: login.php?registro=ok');
            exit;
            
        } catch (Exception $e) {
            $errores[] = 'Error al registrar: ' . $e->getMessage();
        }
    }
}

?>

<?php include 'includes/header.php'; ?>

<section class="section" style="max-width: 500px; margin: 50px auto;">
    <h1 style="text-align: center; margin-bottom: 30px;">Registro de Paciente</h1>
    
    <!-- Mostrar errores si hay -->
    <?php if (!empty($errores)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <strong>Por favor, corrige los siguientes errores:</strong>
            <ul style="margin: 10px 0 0 20px;">
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Formulario de registro -->
    <form method="POST" action="registro.php" style="display: flex; flex-direction: column; gap: 15px;">
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nombre *</label>
            <input type="text" name="nombre" required 
                   value="<?= htmlspecialchars($nombre ?? '') ?>"
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Apellidos *</label>
            <input type="text" name="apellidos" required 
                   value="<?= htmlspecialchars($apellidos ?? '') ?>"
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email *</label>
            <input type="email" name="email" required 
                   value="<?= htmlspecialchars($email ?? '') ?>"
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Contraseña *</label>
            <input type="password" name="password" required minlength="6"
                   placeholder="Mínimo 6 caracteres"
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Confirmar Contraseña *</label>
            <input type="password" name="password_confirm" required
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <button type="submit" style="padding: 12px; background: #2c5282; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px;">
            Registrarse
        </button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        ¿Ya tienes cuenta? <a href="login.php" style="color: #2c5282;">Inicia sesión aquí</a>
    </p>
</section>

<?php include 'includes/footer.php'; ?>

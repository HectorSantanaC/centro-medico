<?php
/**
 * =====================================================
 * login.php
 * INICIO DE SESIÓN DE USUARIOS
 * =====================================================
 * 
 * Este archivo permite a los usuarios iniciar sesión.
 * 
 * FLUJO:
 *   1. Mostrar formulario de login (GET)
 *   2. Procesar credenciales (POST)
 *   3. Verificar email y contraseña
 *   4. Crear sesión con datos del usuario
 *   5. Redirigir según rol (admin → citas-crud, paciente → index)
 * 
 * =====================================================
 */

require_once __DIR__ . '/config/Database.php';
$db = Database::getInstance();

$page_title = 'Login / Registro';

// ============================================================
// PROCESAR FORMULARIO DE LOGIN
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = '';
    
    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        
        // Buscar usuario por email
        $usuarios = $db->fetchAll(
            "SELECT id, nombre, apellidos, email, password, rol 
             FROM usuarios WHERE email = ?",
            [$email]
        );
        
        // Verificar si el usuario existe
        if (empty($usuarios)) {
            $error = 'Email o contraseña incorrectos';
        } else {
            $usuario = $usuarios[0];
            
            // Verificar contraseña (comparación directa en texto plano)
            if ($password !== $usuario['password']) {
                $error = 'Email o contraseña incorrectos';
            } else {
                
                // ========================================================
                // LOGIN CORRECTO - CREAR SESIÓN
                // ========================================================
                
                // Regenerar ID de sesión por seguridad
                // Evita ataques de "session fixation"
                session_regenerate_id(true);
                
                // Guardar datos del usuario en la sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                // Redirigir según el rol
                if ($usuario['rol'] === 'admin' || $usuario['rol'] === 'gestor') {
                    // Admin/Gestor → ir a panel de admin
                    header('Location: admin.php');
                } else {
                    // Paciente → ir a página principal
                    header('Location: index.php');
                }
                exit;
            }
        }
    }
}

?>

<?php include 'includes/header.php'; ?>

<section class="section" style="max-width: 400px; margin: 50px auto;">
    <h1 style="text-align: center; margin-bottom: 30px;">Accede a tu cuenta</h1>
    
    <!-- Mensaje de registro exitoso -->
    <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            ¡Registro completado! Ahora puedes iniciar sesión con tus credenciales.
        </div>
    <?php endif; ?>
    
    <!-- Mensaje de error -->
    <?php if (!empty($error)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <!-- Formulario de login -->
    <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 15px;">
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email</label>
            <input type="email" name="email" required 
                   placeholder="tu@email.com"
                   value="<?= htmlspecialchars($email ?? '') ?>"
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Contraseña</label>
            <input type="password" name="password" required
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <button type="submit" style="padding: 12px; background: #2c5282; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px;">
            Iniciar Sesión
        </button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        ¿No tienes cuenta? <a href="registro.php" style="color: #2c5282;">Regístrate aquí</a>
    </p>
    
    <!-- Info para pruebas -->
    <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px; font-size: 0.9rem;">
        <strong>Usuarios de prueba:</strong>
        <p style="margin: 5px 0;">
            Admin: admin@tac7.com / admin123<br>
            Gestor: gestor@tac7.com / gestor123<br>
            Paciente: juan.garcia@email.com / paciente123
        </p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

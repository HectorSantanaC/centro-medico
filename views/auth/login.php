<?php include __DIR__ . '/../layout/header.php'; ?>

<section class="section" style="max-width: 400px; margin: 50px auto;">
  <h1 style="text-align: center; margin-bottom: 30px;">Accede a tu cuenta</h1>
  
  <?php if ($registro_ok === 'ok'): ?>
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
      ¡Registro completado! Ahora puedes iniciar sesión con tus credenciales.
    </div>
  <?php endif; ?>
  
  <?php if (!empty($error)): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  
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
  
  <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px; font-size: 0.9rem;">
    <strong>Usuarios de prueba:</strong>
    <p style="margin: 5px 0;">
      Admin: admin@tac7.com / admin123<br>
      Gestor: gestor@tac7.com / gestor123<br>
      Paciente: juan.garcia@email.com / paciente123
    </p>
  </div>
</section>

<?php include __DIR__ . '/../layout/footer.php'; ?>

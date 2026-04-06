<?php include __DIR__ . '/layout/header.php'; ?>

<section class="section" style="max-width: 500px; margin: 50px auto;">
  <h1 style="text-align: center; margin-bottom: 30px;">Registro de Paciente</h1>
  
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

<?php include __DIR__ . '/layout/footer.php'; ?>

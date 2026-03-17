<?php 
$page_title = 'Login / Registro'; 
include 'db.php'; 
include 'header.php'; 
// Lógica POST futura: validar con PDO prepared, session_start()
?>
<section class="section">
    <h1>Accede a tu área de paciente</h1>
    <form method="POST" action="login.php">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>
    <p>¿Nuevo? <a href="registro.php">Regístrate aquí</a> para reservar citas.</p>
</section>
<?php include 'footer.php'; ?>

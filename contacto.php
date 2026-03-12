<?php $page_title = 'Contacto'; include 'header.php'; ?>
<section class="section">
    <h1>Contacto</h1>
    <form method="POST" action="contacto.php">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Email" required>
        <textarea name="mensaje" placeholder="Mensaje" required></textarea>
        <button type="submit">Enviar</button>
    </form>
</section>
<?php include 'footer.php'; ?>

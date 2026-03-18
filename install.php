<?php
// install.php - CREA BASE DE DATOS COMPLETA (1 clic)
require_once 'config/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();

echo "<h1>🔨 Instalando base de datos...</h1>";

// 1. TABLAS
$tables = [
    "CREATE TABLE IF NOT EXISTS usuarios (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        rol VARCHAR(20) DEFAULT 'paciente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS especialidades (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        activo BOOLEAN DEFAULT true
    )",

    "CREATE TABLE IF NOT EXISTS medicos (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        especialidad_id INTEGER REFERENCES especialidades(id),
        activo BOOLEAN DEFAULT true
    )",

    "CREATE TABLE IF NOT EXISTS citas (
        id SERIAL PRIMARY KEY,
        paciente_id INTEGER REFERENCES usuarios(id),
        medico_id INTEGER REFERENCES medicos(id),
        especialidad_id INTEGER REFERENCES especialidades(id),
        fecha DATE NOT NULL,
        hora TIME NOT NULL,
        estado VARCHAR(20) DEFAULT 'pendiente',
        notas TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Tabla creada: " . preg_replace('/CREATE TABLE IF NOT EXISTS (\w+).*/', '$1', trim($sql)) . "<br>";
    } catch (Exception $e) {
        echo "⚠️ " . $e->getMessage() . "<br>";
    }
}

// 2. DATOS DE PRUEBA
$pdo->exec("INSERT INTO especialidades (nombre, descripcion) VALUES 
    ('Cardiología', 'Especialistas en corazón'),
    ('Dermatología', 'Piel y cabello'),
    ('Traumatología', 'Huesos y articulaciones'),
    ('Pediatría', 'Niños y adolescentes')
    ON CONFLICT DO NOTHING");

$pdo->exec("INSERT INTO medicos (nombre, apellidos, especialidad_id) VALUES 
    ('Juan', 'Pérez', 1),
    ('María', 'García', 1),
    ('Ana', 'López', 2),
    ('Carlos', 'Martínez', 3)
    ON CONFLICT DO NOTHING");

$pdo->exec("INSERT INTO usuarios (nombre, apellidos, email, password, rol) VALUES 
    ('Admin', 'TAC7', 'admin@tac7.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')
    ON CONFLICT DO NOTHING");

echo "<h2 style='color:green'>🎉 ¡BASE DE DATOS LISTA!</h2>
      <p><strong>Tablas:</strong> usuarios, especialidades, medicos, citas</p>
      <p><strong>Datos prueba:</strong> 4 especialidades, 4 médicos, 1 admin</p>
      <a href='cita-online.php' class='btn'>→ Probar formulario citas</a>
      <a href='index.php' class='btn'>→ Página principal</a>
      <hr><small><strong>IMPORTANTE:</strong> Borra este archivo después: <code>rm install.php</code></small>";

?>
<style>
body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
.btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px; }
</style>

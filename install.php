<?php
// install.php - CREA BASE DE DATOS COMPLETA (1 clic)
require_once 'config/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();

echo "<h1>🔨 Instalando base de datos...</h1>";

// 0. BORRAR TABLAS EXISTENTES (reset completo)
$pdo->exec("DROP TABLE IF EXISTS usuario_departamento_rol CASCADE");
$pdo->exec("DROP TABLE IF EXISTS roles CASCADE");
$pdo->exec("DROP TABLE IF EXISTS departamentos CASCADE");
$pdo->exec("DROP TABLE IF EXISTS citas CASCADE");
$pdo->exec("DROP TABLE IF EXISTS medicos CASCADE");
$pdo->exec("DROP TABLE IF EXISTS especialidades CASCADE");
$pdo->exec("DROP TABLE IF EXISTS usuarios CASCADE");
$pdo->exec("DROP TABLE IF EXISTS topicos CASCADE");
$pdo->exec("DROP TABLE IF EXISTS articulos CASCADE");
echo "✅ Tablas anteriores eliminadas<br>";

// 1. TABLAS
$tables = [
  "CREATE TABLE IF NOT EXISTS departamentos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT true
  )",

  "CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT true
  )",

  "CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )",

  "CREATE TABLE IF NOT EXISTS usuario_departamento_rol (
    usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    departamento_id INTEGER NOT NULL REFERENCES departamentos(id) ON DELETE CASCADE,
    rol_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (usuario_id, departamento_id, rol_id)
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
  )",

  "CREATE TABLE IF NOT EXISTS topicos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )",

  "CREATE TABLE IF NOT EXISTS articulos (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    topico INTEGER REFERENCES topicos(id),
    contenido_completo TEXT,
    contenido_reducido VARCHAR(500),
    fecha_contenido DATE,
    fecha_caducidad DATE,
    orden INTEGER DEFAULT 0,
    notas TEXT,
    imagen VARCHAR(255),
    imagen_url VARCHAR(255),
    autor VARCHAR(255),
    publicado BOOLEAN DEFAULT true,
    seo_titulo VARCHAR(255),
    seo_descripcion TEXT,
    seo_palabras_clave VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

// 2. DATOS DE PRUEBA - DEPARTAMENTOS
$pdo->exec("INSERT INTO departamentos (nombre, descripcion) VALUES
    ('Dirección Médica', 'Gestión y dirección del centro médico'),
    ('Administración', 'Tareas administrativas y gestión de personal'),
    ('Enfermería', 'Cuidados y atención de enfermería'),
    ('Atención al Paciente', 'Recepción y atención directa a pacientes'),
    ('Facturación', 'Gestión de cobros y facturación'),
    ('Recursos Humanos', 'Contratación y gestión de personal')
    ON CONFLICT DO NOTHING");
echo "✅ Departamentos insertados (6)<br>";

// 3. DATOS DE PRUEBA - ROLES
$pdo->exec("INSERT INTO roles (nombre, descripcion) VALUES
    ('admin', 'Administrador con acceso total al sistema'),
    ('gestor', 'Gestor de contenidos y artículos médicos'),
    ('médico', 'Personal médico con acceso a citas y pacientes'),
    ('paciente', 'Usuario paciente para citas online'),
    ('enfermero', 'Personal de enfermería')
    ON CONFLICT DO NOTHING");
echo "✅ Roles insertados (5)<br>";

// 4. DATOS DE PRUEBA - ESPECIALIDADES
$pdo->exec("INSERT INTO especialidades (nombre, descripcion) VALUES 
    ('Cardiología', 'Especialistas en corazón y sistema circulatorio'),
    ('Dermatología', 'Piel, cabello y uñas'),
    ('Traumatología', 'Huesos, articulaciones y músculos'),
    ('Pediatría', 'Salud infantil y adolescentes'),
    ('Oftalmología', 'Salud visual y ojos'),
    ('Ginecología', 'Salud femenina'),
    ('Neurología', 'Sistema nervioso'),
    ('Psicología', 'Salud mental'),
    ('Nutrición', 'Alimentación y dietética'),
    ('Medicina General', 'Atención primaria')
    ON CONFLICT DO NOTHING");
echo "✅ Especialidades insertadas (10)<br>";

// 5. DATOS DE PRUEBA - MÉDICOS (23 total)
$pdo->exec("INSERT INTO medicos (nombre, apellidos, especialidad_id, activo) VALUES
    ('Juan', 'Pérez', 1, true),
    ('María', 'García', 1, true),
    ('Ana', 'López', 2, true),
    ('Carlos', 'Martínez', 3, true),
    ('Alejandro', 'Hernández', 1, true),
    ('Carmen', 'Ruiz', 1, true),
    ('Patricia', 'Vega', 2, true),
    ('Roberto', 'Sanz', 2, true),
    ('Francisco', 'Gil', 3, true),
    ('Isabel', 'Torres', 3, true),
    ('Lucía', 'Navarro', 4, true),
    ('Manuel', 'Crespo', 4, true),
    ('Elena', 'Molina', 5, true),
    ('Jorge', 'Peña', 5, true),
    ('Sonia', 'Ortega', 6, true),
    ('Antonio', 'Vargas', 7, true),
    ('María Jesús', 'Fuentes', 8, true),
    ('Pablo', 'Reyes', 8, true),
    ('Cristina', 'Gallardo', 9, true),
    ('Sergio', 'Rubio', 10, true),
    ('Beatriz', 'Adrián', 10, true),
    ('Miguel', 'Santos', 1, false),
    ('Laura', 'Jiménez', 2, false)
    ON CONFLICT DO NOTHING");
echo "✅ Médicos insertados (23)<br>";

// 6. DATOS DE PRUEBA - USUARIOS (25 total, sin columna rol)
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$gestorPassword = password_hash('gestor123', PASSWORD_DEFAULT);
$pacientePassword = password_hash('paciente123', PASSWORD_DEFAULT);
$enfermeroPassword = password_hash('enfermero123', PASSWORD_DEFAULT);

$pdo->exec("INSERT INTO usuarios (nombre, apellidos, email, password, created_at) VALUES 
    ('Admin', 'TAC7', 'admin@tac7.com', '$adminPassword', '2025-05-01'),
    ('Gestor', 'Contenido', 'gestor@tac7.com', '$gestorPassword', '2025-05-15'),
    ('Juan', 'García López', 'juan.garcia@email.com', '$pacientePassword', '2025-06-01'),
    ('Pedro', 'Martínez Soto', 'pedro.martinez@email.com', '$pacientePassword', '2025-06-15'),
    ('María', 'López Ruiz', 'maria.lopez@email.com', '$pacientePassword', '2025-07-01'),
    ('Carlos', 'González Díaz', 'carlos.gonzalez@email.com', '$pacientePassword', '2025-07-15'),
    ('Ana', 'Fernández Paz', 'ana.fernandez@email.com', '$pacientePassword', '2025-08-01'),
    ('José', 'Rodríguez Lima', 'jose.rodriguez@email.com', '$pacientePassword', '2025-08-15'),
    ('Luisa', 'Sánchez Vega', 'luisa.sanchez@email.com', '$pacientePassword', '2025-09-01'),
    ('Antonio', 'Pérez Hidalgo', 'antonio.perez@email.com', '$pacientePassword', '2025-09-15'),
    ('Carmen', 'Gómez Ortiz', 'carmen.gomez@email.com', '$pacientePassword', '2025-10-01'),
    ('Francisco', 'Díaz Moreno', 'francisco.diaz@email.com', '$pacientePassword', '2025-10-15'),
    ('Isabel', 'Hernández Ríos', 'isabel.hernandez@email.com', '$pacientePassword', '2025-11-01'),
    ('Manuel', 'Jiménez Flores', 'manuel.jimenez@email.com', '$pacientePassword', '2025-11-15'),
    ('Elena', 'Ruiz Navarro', 'elena.ruiz@email.com', '$pacientePassword', '2025-12-01'),
    ('Jorge', 'Torres Molina', 'jorge.torres@email.com', '$pacientePassword', '2025-12-15'),
    ('Sonia', 'Navarro Peña', 'sonia.navarro@email.com', '$pacientePassword', '2026-01-01'),
    ('Roberto', 'Vargas Ortega', 'roberto.vargas@email.com', '$pacientePassword', '2026-01-15'),
    ('Patricia', 'Sanz Crespo', 'patricia.sanz@email.com', '$pacientePassword', '2026-02-01'),
    ('Alejandro', 'Vega Ruiz', 'alejandro.vega@email.com', '$pacientePassword', '2026-02-15'),
    ('Cristina', 'Gil Torres', 'cristina.gil@email.com', '$pacientePassword', '2026-03-01'),
    ('Sergio', 'Rubio Navarro', 'sergio.rubio@email.com', '$pacientePassword', '2026-03-15'),
    ('Beatriz', 'Adrián Soto', 'beatriz.adrian@email.com', '$pacientePassword', '2026-04-01'),
    ('Miguel', 'Santos López', 'miguel.santos@email.com', '$enfermeroPassword', '2025-06-01'),
    ('Laura', 'Jiménez García', 'laura.jimenez@email.com', '$enfermeroPassword', '2025-06-15')
    ON CONFLICT DO NOTHING");
echo "✅ Usuarios insertados (25)<br>";

// 7. DATOS DE PRUEBA - ASIGNACIONES USUARIO-DEPARTAMENTO-ROL (30 asignaciones)
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'admin@tac7.com' AND d.nombre = 'Dirección Médica' AND r.nombre = 'admin'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'admin@tac7.com' AND d.nombre = 'Administración' AND r.nombre = 'admin'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'gestor@tac7.com' AND d.nombre = 'Administración' AND r.nombre = 'gestor'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'juan.garcia@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'pedro.martinez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'maria.lopez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'carlos.gonzalez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'ana.fernandez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'jose.rodriguez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'luisa.sanchez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'antonio.perez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'carmen.gomez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'francisco.diaz@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'isabel.hernandez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'manuel.jimenez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'elena.ruiz@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'jorge.torres@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'sonia.navarro@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'roberto.vargas@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'patricia.sanz@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'alejandro.vega@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'cristina.gil@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'sergio.rubio@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'beatriz.adrian@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'paciente'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'miguel.santos@email.com' AND d.nombre = 'Enfermería' AND r.nombre = 'enfermero'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'laura.jimenez@email.com' AND d.nombre = 'Enfermería' AND r.nombre = 'enfermero'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'miguel.santos@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'enfermero'");
$pdo->exec("INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id)
  SELECT u.id, d.id, r.id FROM usuarios u, departamentos d, roles r
  WHERE u.email = 'laura.jimenez@email.com' AND d.nombre = 'Atención al Paciente' AND r.nombre = 'enfermero'");
echo "✅ Asignaciones usuario-departamento-rol insertadas (30)<br>";

// 8. DATOS DE PRUEBA - CITAS (120+ para CMI)
$pdo->exec("INSERT INTO citas (paciente_id, medico_id, especialidad_id, fecha, hora, estado, notas, created_at) VALUES
    (3, 1, 1, '2025-06-10', '09:00', 'completada', 'Revisión cardíaca', '2025-06-08'),
    (3, 1, 1, '2025-06-20', '10:00', 'completada', 'Control mensual', '2025-06-18'),
    (3, 2, 1, '2025-07-05', '11:30', 'completada', 'Ecocardiograma', '2025-07-03'),
    (4, 3, 2, '2025-06-15', '09:30', 'completada', 'Revisión lunar', '2025-06-13'),
    (4, 3, 2, '2025-07-10', '10:30', 'completada', 'Crioterapia', '2025-07-08'),
    (4, 7, 2, '2025-08-05', '11:00', 'completada', 'Control', '2025-08-03'),
    (5, 4, 3, '2025-06-12', '12:00', 'completada', 'Dolor rodilla', '2025-06-10'),
    (5, 9, 3, '2025-07-08', '09:00', 'completada', 'Rehabilitación', '2025-07-06'),
    (5, 4, 3, '2025-08-10', '10:00', 'completada', 'Control', '2025-08-08'),
    (6, 5, 4, '2025-06-18', '16:00', 'completada', 'Pediatría general', '2025-06-16'),
    (6, 11, 4, '2025-07-15', '17:00', 'completada', 'Vacunación', '2025-07-13'),
    (6, 12, 4, '2025-08-12', '16:30', 'completada', 'Control', '2025-08-10'),
    (7, 6, 1, '2025-06-25', '09:00', 'completada', 'Arritmia', '2025-06-23'),
    (7, 1, 1, '2025-07-20', '10:00', 'completada', 'Control', '2025-07-18'),
    (7, 6, 1, '2025-08-18', '11:00', 'completada', 'Revisión', '2025-08-16'),
    (8, 7, 2, '2025-06-28', '11:00', 'completada', 'Dermatitis', '2025-06-26'),
    (8, 3, 2, '2025-07-25', '12:00', 'completada', 'Control', '2025-07-23'),
    (8, 8, 2, '2025-08-22', '09:30', 'completada', 'Láser', '2025-08-20'),
    (9, 9, 3, '2025-07-02', '16:00', 'completada', 'Lumbalgia', '2025-06-30'),
    (9, 4, 3, '2025-08-02', '17:00', 'completada', 'Control', '2025-07-31'),
    (9, 10, 3, '2025-09-02', '16:30', 'cancelada', 'Rehabilitación', '2025-08-31'),
    (10, 11, 4, '2025-07-05', '09:00', 'completada', 'Fiebre', '2025-07-03'),
    (10, 5, 4, '2025-08-05', '10:00', 'completada', 'Control', '2025-08-03'),
    (10, 12, 4, '2025-09-05', '09:30', 'cancelada', 'Vacuna', '2025-09-03'),
    (11, 13, 5, '2025-07-08', '11:00', 'completada', 'Visión borrosa', '2025-07-06'),
    (11, 14, 5, '2025-08-08', '12:00', 'completada', 'Control', '2025-08-06'),
    (11, 13, 5, '2025-09-08', '11:30', 'cancelada', 'Revisión', '2025-09-06'),
    (12, 15, 6, '2025-07-12', '16:00', 'completada', 'Revisión ginecológica', '2025-07-10'),
    (12, 15, 6, '2025-08-12', '17:00', 'completada', 'Control', '2025-08-10'),
    (12, 15, 6, '2025-09-12', '16:30', 'cancelada', 'PAP', '2025-09-10'),
    (13, 16, 7, '2025-07-15', '09:00', 'completada', 'Migrañas', '2025-07-13'),
    (13, 16, 7, '2025-08-15', '10:00', 'completada', 'Control', '2025-08-13'),
    (13, 16, 7, '2025-09-15', '09:30', 'cancelada', 'Revisión', '2025-09-13'),
    (14, 17, 8, '2025-07-18', '11:00', 'completada', 'Ansiedad', '2025-07-16'),
    (14, 18, 8, '2025-08-18', '12:00', 'completada', 'Terapia', '2025-08-16'),
    (14, 17, 8, '2025-09-18', '11:30', 'cancelada', 'Seguimiento', '2025-09-16'),
    (15, 19, 9, '2025-07-22', '16:00', 'completada', 'Dieta', '2025-07-20'),
    (15, 19, 9, '2025-08-22', '17:00', 'completada', 'Control', '2025-08-20'),
    (15, 19, 9, '2025-09-22', '16:30', 'cancelada', 'Nutrición', '2025-09-20'),
    (16, 20, 10, '2025-07-25', '09:00', 'completada', 'Gripe', '2025-07-23'),
    (16, 20, 10, '2025-08-25', '10:00', 'completada', 'Control', '2025-08-23'),
    (16, 20, 10, '2025-09-25', '09:30', 'cancelada', 'Revisión', '2025-09-23'),
    (17, 1, 1, '2025-08-05', '11:00', 'completada', 'Cardiología deportiva', '2025-08-03'),
    (17, 2, 1, '2025-09-05', '12:00', 'cancelada', 'Control', '2025-09-03'),
    (18, 3, 2, '2025-08-08', '09:00', 'completada', 'Manchas piel', '2025-08-06'),
    (18, 7, 2, '2025-09-08', '10:00', 'cancelada', 'Láser', '2025-09-06'),
    (19, 4, 3, '2025-08-12', '11:00', 'completada', 'Esguince', '2025-08-10'),
    (19, 9, 3, '2025-09-12', '12:00', 'cancelada', 'Control', '2025-09-10'),
    (20, 5, 4, '2025-08-15', '16:00', 'completada', 'Pediatría', '2025-08-13'),
    (20, 11, 4, '2025-09-15', '17:00', 'cancelada', 'Control', '2025-09-13'),
    (21, 6, 1, '2025-08-18', '09:00', 'completada', 'Hipertensión', '2025-08-16'),
    (21, 1, 1, '2025-09-18', '10:00', 'cancelada', 'Control', '2025-09-16'),
    (22, 13, 5, '2025-08-22', '11:00', 'completada', 'Oftalmología', '2025-08-20'),
    (22, 14, 5, '2025-09-22', '12:00', 'completada', 'Control', '2025-09-20'),
    (23, 15, 6, '2025-08-25', '16:00', 'completada', 'Ginecología', '2025-08-23'),
    (23, 15, 6, '2025-09-25', '17:00', 'completada', 'Control', '2025-09-23'),
    (3, 16, 7, '2025-09-02', '09:00', 'completada', 'Neurología', '2025-08-31'),
    (4, 17, 8, '2025-09-05', '10:00', 'completada', 'Psicología', '2025-09-03'),
    (5, 19, 9, '2025-09-08', '11:00', 'completada', 'Nutrición', '2025-09-06'),
    (6, 20, 10, '2025-09-12', '12:00', 'completada', 'Medicina general', '2025-09-10'),
    (7, 1, 1, '2025-09-15', '16:00', 'completada', 'Cardiología', '2025-09-13'),
    (8, 3, 2, '2025-09-18', '17:00', 'completada', 'Dermatología', '2025-09-16'),
    (9, 4, 3, '2025-09-22', '09:00', 'completada', 'Traumatología', '2025-09-20'),
    (10, 5, 4, '2025-09-25', '10:00', 'completada', 'Pediatría', '2025-09-23'),
    (11, 6, 1, '2025-10-02', '11:00', 'completada', 'Cardiología', '2025-09-30'),
    (12, 7, 2, '2025-10-05', '12:00', 'completada', 'Dermatología', '2025-10-03'),
    (13, 9, 3, '2025-10-08', '16:00', 'completada', 'Traumatología', '2025-10-06'),
    (14, 11, 4, '2025-10-12', '17:00', 'completada', 'Pediatría', '2025-10-10'),
    (15, 13, 5, '2025-10-15', '09:00', 'completada', 'Oftalmología', '2025-10-13'),
    (16, 15, 6, '2025-10-18', '10:00', 'completada', 'Ginecología', '2025-10-16'),
    (17, 16, 7, '2025-10-22', '11:00', 'completada', 'Neurología', '2025-10-20'),
    (18, 17, 8, '2025-10-25', '12:00', 'completada', 'Psicología', '2025-10-23'),
    (19, 19, 9, '2025-10-28', '16:00', 'completada', 'Nutrición', '2025-10-26'),
    (20, 20, 10, '2025-11-02', '17:00', 'completada', 'Medicina general', '2025-10-31'),
    (21, 1, 1, '2025-11-05', '09:00', 'completada', 'Cardiología', '2025-11-03'),
    (22, 3, 2, '2025-11-08', '10:00', 'completada', 'Dermatología', '2025-11-06'),
    (23, 4, 3, '2025-11-12', '11:00', 'completada', 'Traumatología', '2025-11-10'),
    (3, 5, 4, '2025-11-15', '12:00', 'completada', 'Pediatría', '2025-11-13'),
    (4, 6, 1, '2025-11-18', '16:00', 'completada', 'Cardiología', '2025-11-16'),
    (5, 7, 2, '2025-11-22', '17:00', 'completada', 'Dermatología', '2025-11-20'),
    (6, 9, 3, '2025-11-25', '09:00', 'completada', 'Traumatología', '2025-11-23'),
    (7, 11, 4, '2025-12-02', '10:00', 'completada', 'Pediatría', '2025-11-30'),
    (8, 13, 5, '2025-12-05', '11:00', 'completada', 'Oftalmología', '2025-12-03'),
    (9, 15, 6, '2025-12-08', '12:00', 'completada', 'Ginecología', '2025-12-06'),
    (10, 16, 7, '2025-12-12', '16:00', 'completada', 'Neurología', '2025-12-10'),
    (11, 17, 8, '2025-12-15', '17:00', 'completada', 'Psicología', '2025-12-13'),
    (12, 19, 9, '2025-12-18', '09:00', 'completada', 'Nutrición', '2025-12-16'),
    (13, 20, 10, '2025-12-22', '10:00', 'completada', 'Medicina general', '2025-12-20'),
    (14, 1, 1, '2026-01-05', '11:00', 'completada', 'Cardiología', '2026-01-03'),
    (15, 3, 2, '2026-01-08', '12:00', 'completada', 'Dermatología', '2026-01-06'),
    (16, 4, 3, '2026-01-12', '16:00', 'completada', 'Traumatología', '2026-01-10'),
    (17, 5, 4, '2026-01-15', '17:00', 'completada', 'Pediatría', '2026-01-13'),
    (18, 6, 1, '2026-01-18', '09:00', 'completada', 'Cardiología', '2026-01-16'),
    (19, 7, 2, '2026-01-22', '10:00', 'completada', 'Dermatología', '2026-01-20'),
    (20, 9, 3, '2026-01-25', '11:00', 'completada', 'Traumatología', '2026-01-23'),
    (21, 11, 4, '2026-02-02', '12:00', 'completada', 'Pediatría', '2026-01-30'),
    (22, 13, 5, '2026-02-05', '16:00', 'completada', 'Oftalmología', '2026-02-03'),
    (23, 15, 6, '2026-02-08', '17:00', 'completada', 'Ginecología', '2026-02-06'),
    (3, 16, 7, '2026-02-12', '09:00', 'completada', 'Neurología', '2026-02-10'),
    (4, 17, 8, '2026-02-15', '10:00', 'completada', 'Psicología', '2026-02-13'),
    (5, 19, 9, '2026-02-18', '11:00', 'completada', 'Nutrición', '2026-02-16'),
    (6, 20, 10, '2026-02-22', '12:00', 'completada', 'Medicina general', '2026-02-20'),
    (7, 1, 1, '2026-03-02', '16:00', 'completada', 'Cardiología', '2026-02-28'),
    (8, 3, 2, '2026-03-05', '17:00', 'completada', 'Dermatología', '2026-03-03'),
    (9, 4, 3, '2026-03-08', '09:00', 'completada', 'Traumatología', '2026-03-06'),
    (10, 5, 4, '2026-03-12', '10:00', 'completada', 'Pediatría', '2026-03-10'),
    (11, 6, 1, '2026-03-15', '11:00', 'completada', 'Cardiología', '2026-03-13'),
    (12, 7, 2, '2026-03-18', '12:00', 'completada', 'Dermatología', '2026-03-16'),
    (13, 9, 3, '2026-03-22', '16:00', 'completada', 'Traumatología', '2026-03-20'),
    (14, 11, 4, '2026-03-25', '17:00', 'completada', 'Pediatría', '2026-03-23'),
    (15, 13, 5, '2026-03-28', '09:00', 'completada', 'Oftalmología', '2026-03-26'),
    (16, 15, 6, '2026-04-02', '10:00', 'completada', 'Ginecología', '2026-03-31'),
    (17, 16, 7, '2026-04-05', '11:00', 'completada', 'Neurología', '2026-04-03'),
    (18, 17, 8, '2026-04-08', '12:00', 'completada', 'Psicología', '2026-04-06'),
    (19, 19, 9, '2026-04-12', '16:00', 'completada', 'Nutrición', '2026-04-10'),
    (20, 20, 10, '2026-04-15', '17:00', 'completada', 'Medicina general', '2026-04-13'),
    (21, 1, 1, '2026-04-18', '09:00', 'completada', 'Cardiología', '2026-04-16'),
    (22, 3, 2, '2026-04-22', '10:00', 'completada', 'Dermatología', '2026-04-20'),
    (23, 4, 3, '2026-04-25', '11:00', 'completada', 'Traumatología', '2026-04-23'),
    (3, 1, 1, '2026-04-28', '09:00', 'pendiente', 'Revisión cardíaca', '2026-04-26'),
    (4, 3, 2, '2026-04-28', '10:00', 'pendiente', 'Revisión lunar', '2026-04-26'),
    (5, 4, 3, '2026-04-28', '11:00', 'pendiente', 'Dolor rodilla', '2026-04-26'),
    (6, 5, 4, '2026-04-29', '09:00', 'pendiente', 'Pediatría general', '2026-04-27'),
    (7, 6, 1, '2026-04-29', '10:00', 'pendiente', 'Arritmia', '2026-04-27'),
    (8, 7, 2, '2026-04-29', '11:00', 'pendiente', 'Dermatitis', '2026-04-27'),
    (9, 9, 3, '2026-04-30', '09:00', 'pendiente', 'Lumbalgia', '2026-04-28'),
    (10, 11, 4, '2026-04-30', '10:00', 'pendiente', 'Pediatría', '2026-04-28'),
    (11, 13, 5, '2026-04-30', '11:00', 'pendiente', 'Visión borrosa', '2026-04-28'),
    (12, 15, 6, '2026-05-01', '09:00', 'pendiente', 'Revisión ginecológica', '2026-04-29'),
    (13, 16, 7, '2026-05-02', '10:00', 'pendiente', 'Migrañas', '2026-04-30'),
    (14, 17, 8, '2026-05-03', '11:00', 'pendiente', 'Ansiedad', '2026-05-01'),
    (15, 19, 9, '2026-05-04', '09:00', 'pendiente', 'Dieta', '2026-05-02'),
    (16, 20, 10, '2026-05-05', '10:00', 'pendiente', 'Gripe', '2026-05-03'),
    (17, 1, 1, '2026-05-06', '11:00', 'pendiente', 'Cardiología deportiva', '2026-05-04'),
    (18, 3, 2, '2026-05-07', '09:00', 'pendiente', 'Manchas piel', '2026-05-05'),
    (19, 4, 3, '2026-05-08', '10:00', 'pendiente', 'Esguince', '2026-05-06'),
    (20, 5, 4, '2026-05-09', '11:00', 'pendiente', 'Control pediatría', '2026-05-07'),
    (21, 6, 1, '2026-05-10', '09:00', 'pendiente', 'Hipertensión', '2026-05-08'),
    (22, 13, 5, '2026-05-11', '10:00', 'pendiente', 'Control oftalmología', '2026-05-09'),
    (23, 15, 6, '2026-05-12', '11:00', 'pendiente', 'Control ginecológico', '2026-05-10'),
    (3, 16, 7, '2026-05-13', '09:00', 'pendiente', 'Neurología', '2026-05-11'),
    (4, 17, 8, '2026-05-14', '10:00', 'pendiente', 'Psicología', '2026-05-12'),
    (5, 19, 9, '2026-05-15', '11:00', 'pendiente', 'Nutrición', '2026-05-13'),
    (6, 20, 10, '2026-05-16', '09:00', 'pendiente', 'Medicina general', '2026-05-14'),
    (7, 1, 1, '2026-05-17', '10:00', 'pendiente', 'Control cardiológico', '2026-05-15'),
    (8, 3, 2, '2026-05-18', '11:00', 'pendiente', 'Control dermatológico', '2026-05-16'),
    (9, 4, 3, '2026-05-19', '09:00', 'pendiente', 'Traumatología', '2026-05-17'),
    (10, 5, 4, '2026-05-20', '10:00', 'pendiente', 'Pediatría', '2026-05-18'),
    (11, 6, 1, '2026-05-21', '11:00', 'pendiente', 'Cardiología', '2026-05-19'),
    (12, 7, 2, '2026-05-22', '09:00', 'pendiente', 'Dermatología', '2026-05-20'),
    (13, 9, 3, '2026-05-25', '10:00', 'pendiente', 'Traumatología', '2026-05-23'),
    (14, 11, 4, '2026-05-28', '11:00', 'pendiente', 'Pediatría', '2026-05-26'),
    (15, 13, 5, '2026-05-30', '09:00', 'pendiente', 'Oftalmología', '2026-05-28'),
    (16, 15, 6, '2026-06-02', '10:00', 'pendiente', 'Ginecología', '2026-05-31'),
    (17, 16, 7, '2026-06-05', '11:00', 'pendiente', 'Neurología', '2026-06-03'),
    (18, 17, 8, '2026-06-08', '09:00', 'pendiente', 'Psicología', '2026-06-06'),
    (19, 19, 9, '2026-06-10', '10:00', 'pendiente', 'Nutrición', '2026-06-08'),
    (20, 20, 10, '2026-06-12', '11:00', 'pendiente', 'Medicina general', '2026-06-10'),
    (21, 1, 1, '2026-06-15', '09:00', 'pendiente', 'Cardiología', '2026-06-13'),
    (22, 3, 2, '2026-06-18', '10:00', 'pendiente', 'Dermatología', '2026-06-16'),
    (23, 4, 3, '2026-06-20', '11:00', 'pendiente', 'Traumatología', '2026-06-18'),
    (3, 5, 4, '2026-06-22', '09:00', 'pendiente', 'Pediatría', '2026-06-20'),
    (4, 6, 1, '2026-06-24', '10:00', 'pendiente', 'Cardiología', '2026-06-22'),
    (5, 7, 2, '2026-06-27', '11:00', 'pendiente', 'Dermatología', '2026-06-25'),
    (6, 9, 3, '2026-06-30', '09:00', 'pendiente', 'Traumatología', '2026-06-28'),
    (7, 11, 4, '2026-07-05', '10:00', 'pendiente', 'Pediatría', '2026-07-03'),
    (8, 13, 5, '2026-07-15', '11:00', 'pendiente', 'Oftalmología', '2026-07-13'),
    (9, 15, 6, '2026-07-20', '09:00', 'pendiente', 'Ginecología', '2026-07-18'),
    (10, 16, 7, '2026-07-31', '10:00', 'pendiente', 'Neurología', '2026-07-29')
    ON CONFLICT DO NOTHING");
echo "✅ Citas insertadas (170+)<br>";

// 9. DATOS DE PRUEBA - TÓPICOS (8 total)
$pdo->exec("INSERT INTO topicos (nombre, created_at) VALUES
    ('Cardiología', '2025-05-01'),
    ('Dermatología', '2025-05-15'),
    ('Traumatología', '2025-06-01'),
    ('Pediatría', '2025-06-15'),
    ('Oftalmología', '2025-07-01'),
    ('Nutrición', '2025-07-15'),
    ('Neurología', '2025-08-01'),
    ('Salud Mental', '2025-08-15')
    ON CONFLICT DO NOTHING");
echo "✅ Tópicos insertados (8)<br>";

// 10. DATOS DE PRUEBA - ARTÍCULOS (25 total)
$pdo->exec("INSERT INTO articulos (titulo, topico, contenido_reducido, contenido_completo, fecha_contenido, autor, publicado) VALUES
    ('Cómo cuidar tu corazón', 1, 'Tips para la salud cardíaca', 'El corazón es un órgano vital...', '2025-06-01', 'Dr. Juan Pérez', true),
    ('Cuidados de la piel en verano', 2, 'Protección solar básica', 'La exposición solar excesiva...', '2025-06-15', 'Dra. Ana López', true),
    ('Ejercicios para rodilla', 3, 'Rehabilitación de rodilla', 'Los ejercicios de fortalecimiento...', '2025-07-01', 'Dr. Carlos Martínez', true),
    ('Vacunación infantil 2025', 4, 'Calendario de vacunas', 'Es fundamental mantener al día...', '2025-07-15', 'Dra. Lucía Navarro', true),
    ('Cuidados de la vista', 5, 'Salud visual tips', 'La fatiga visual es común...', '2025-08-01', 'Dra. Elena Molina', true),
    ('Dieta equilibrada', 6, 'Alimentación saludable', 'Una dieta balanceada incluye...', '2025-08-15', 'Dr. Francisco Gil', true),
    ('Manejo del estrés', 7, 'Técnicas de relajación', 'El estrés crónico afecta...', '2025-09-01', 'Dra. María Jesús Fuentes', true),
    ('Primeros auxilios básicos', 8, 'Qué hacer en emergencias', 'Ante una emergencia médica...', '2025-09-15', 'Dr. Antonio Vargas', true),
    ('Hipertensión arterial', 1, 'Control de la presión', 'La hipertensión es silenciosa...', '2025-10-01', 'Dr. Juan Pérez', true),
    ('Cáncer de piel', 2, 'Detección temprana', 'Los lunares atípicos pueden...', '2025-10-15', 'Dra. Ana López', true),
    ('Dolor de espalda', 3, 'Prevención y tratamiento', 'El dolor lumbar afecta a...', '2025-11-01', 'Dr. Carlos Martínez', true),
    ('Desarrollo infantil', 4, 'Hitos importantes', 'Los niños alcanzan hitos...', '2025-11-15', 'Dra. Lucía Navarro', true),
    ('Miopía en niños', 5, 'Detección y corrección', 'El uso excesivo de pantallas...', '2025-12-01', 'Dra. Elena Molina', true),
    ('Suplementos nutricionales', 6, 'Qué debes saber', 'Los suplementos no reemplazan...', '2025-12-15', 'Dr. Francisco Gil', true),
    ('Trastornos del sueño', 7, 'Insomnio y soluciones', 'Dormir mal afecta la salud...', '2026-01-01', 'Dra. María Jesús Fuentes', true),
    ('RCP básico', 8, 'Cómo salvar vidas', 'La reanimación cardiopulmonar...', '2026-01-15', 'Dr. Antonio Vargas', true),
    ('Alimentación del deportista', 1, 'Nutrición y ejercicio', 'Los atletas requieren una...', '2026-02-01', 'Dr. Juan Pérez', true),
    ('Acné adulto', 2, 'Tratamientos efectivos', 'El acné no es solo juvenil...', '2026-02-15', 'Dra. Ana López', true),
    ('Artroscopia de rodilla', 3, 'Cirugía mínimamente invasiva', 'La artroscopia permite...', '2026-03-01', 'Dr. Carlos Martínez', true),
    ('Lactancia materna', 4, 'Beneficios para el bebé', 'La leche materna es el...', '2026-03-15', 'Dra. Lucía Navarro', true),
    ('Glaucoma: la ladrona de visión', 5, 'Detección temprana', 'El glaucoma no presenta...', '2026-04-01', 'Dra. Elena Molina', true),
    ('Ayuno intermitente', 6, 'Beneficios y riesgos', 'El ayuno intermitente ha...', '2026-04-08', 'Dr. Francisco Gil', true),
    ('Depresión en adolescentes', 7, 'Señales de alerta', 'La salud mental en adolescentes...', '2026-04-15', 'Dra. María Jesús Fuentes', true),
    ('Urgencias cardiovasculares', 8, 'Cuándo acudir al médico', 'Dolor en el pecho puede...', '2026-04-18', 'Dr. Antonio Vargas', true),
    ('Bienestar integral', 6, 'Salud física y mental', 'El bienestar integral requiere...', '2026-04-22', 'Dr. Francisco Gil', true)
    ON CONFLICT DO NOTHING");
echo "✅ Artículos insertados (25)<br>";

// 11. RESUMEN FINAL
echo "<h2 style='color:green'>🎉 ¡BASE DE DATOS LISTA!</h2>
      <p><strong>Tablas:</strong> usuarios, departamentos, roles, usuario_departamento_rol, especialidades, medicos, citas, topicos, articulos</p>
      <p><strong>Datos:</strong> 6 departamentos, 5 roles, 25 usuarios, 30 asignaciones, 10 especialidades, 23 médicos, 170+ citas, 8 tópicos, 25 artículos</p>
      <a href='cita-online.php' class='btn'>→ Probar formulario citas</a>
      <a href='index.php' class='btn'>→ Página principal</a>
      <hr><small><strong>INFO:</strong> Ejecuta este archivo cuando quieras resetear la BD.</small>";

?>
<style>
  body {
    font-family: Arial;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
  }

  .btn {
    background: #007bff;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
    margin: 10px;
  }
</style>
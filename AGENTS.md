# AGENTS.md - Centro Médico TAC7

## Proyecto FCT - Gestión de Citas Médicas

**Estado**: Proyecto funcional con MVC  
**Objetivo**: App de gestión de citas para Centro Médico TAC7  
**Stack**: PHP 8.2 + PostgreSQL + Vanilla HTML/CSS/JS

---

## Ejecución Local (XAMPP)

```bash
# 1. Iniciar Apache y PostgreSQL en XAMPP Control Panel
# 2. Crear base de datos 'centro-medico' en PostgreSQL
# 3. Ejecutar install.php: http://localhost/centro-medico/install.php
# 4. Acceder a la app: http://localhost/centro-medico/
```

## Verificar Sintaxis PHP

```bash
php -l archivo.php
```

---

## Estructura de Archivos (MVC)

```
/centro-medico
├── config/
│   └── Database.php              # Conexión BD + helpers
├── controllers/                  # Lógica de negocio
│   ├── AdminController.php
│   ├── AuthController.php
│   ├── CitaOnlineController.php
│   ├── CitasController.php
│   ├── MisCitasController.php
│   ├── RegistroController.php
│   └── UsuariosController.php
├── models/                       # Interacción con BD
│   ├── Cita.php
│   ├── Especialidad.php
│   ├── Medico.php
│   └── Usuario.php
├── views/                        # Plantillas
│   ├── layout/                  # header.php, footer.php, navbar-admin.php
│   ├── auth/                   # login.php
│   ├── admin/                  # index.php, citas.php, usuarios.php
│   ├── cita-online.php
│   ├── mis-citas.php
│   └── registro.php
├── css/
│   ├── style.css
│   └── admin.css
├── js/
│   ├── scripts.js
│   ├── cita-online.js
│   └── crud-citas.js
├── api/
│   └── medicos.php
├── index.php                     # Página principal
├── admin.php                     # Panel admin
├── registro.php                  # Registro de pacientes
├── login.php                     # Login de usuarios
├── logout.php                    # Cerrar sesión
├── cita-online.php               # Reservar cita (logueados)
├── mis-citas.php                 # Ver MIS citas (pacientes)
├── citas-crud.php                # Gestionar TODAS las citas (admin)
├── usuarios-crud.php             # Gestionar usuarios (admin)
└── install.php                  # Setup BD (borrar después)
```

---

## Sistema de Usuarios

### Roles

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| `admin` | Administrador | Gestionar todas las citas y usuarios |
| `gestor` | Gestor | Gestionar citas |
| `paciente` | Paciente registrado | Reservar citas, ver MIS citas, cancelar |

### Flujo de Login

```
Paciente → registro.php → login.php → index.php/mis-citas.php
Admin    → login.php → admin.php → citas-crud.php / usuarios-crud.php
```

### Usuarios de Prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@tac7.com | admin123 |
| Paciente | juan.garcia@email.com | paciente123 |

---

## Enfoque de Aprendizaje

### Objetivo
Entender PHP y JavaScript a fondo: qué hacemos y por qué.

### Método
- **Explicar el "por qué"** antes de cada refactorización
- **Preguntas de verificación** después de cada concepto
- **Resumen breve** al final de cada tarea

### Conceptos a dominar en PHP

| Concepto | Descripción |
|----------|-------------|
| **MVC** | Separar lógica (controllers), datos (models) y presentación (views) |
| **Sesiones** | Mantener estado del usuario entre páginas |
| **Prepared statements** | Prevenir inyecciones SQL |
| **Include/require** | Reutilizar código (header, footer, controllers) |

### Conceptos a dominar en JavaScript

| Concepto | Descripción |
|----------|-------------|
| **Fetch API** | Hacer peticiones HTTP asíncronas |
| **DOM manipulation** | Modificar el HTML desde JS |
| **Event listeners** | Responder a acciones del usuario |
| **JSON** | Intercambiar datos entre cliente y servidor |

---

## Convenciones de Código

### PHP - Estructura MVC

```php
// Controller: handleRequest() devuelve array con datos
class LoginController {
    public function handleRequest(): array {
        // lógica
        return ['page_title' => 'Login', 'error' => ''];
    }
}

// Model: métodos para interacción con BD
class Usuario {
    public function find(int $id): ?array { ... }
    public function create(array $data): int { ... }
    public function all(): array { ... }
}

// View: solo HTML + datos del controller
<?php include __DIR__ . '/layout/header.php'; ?>
<h1><?= htmlspecialchars($page_title) ?></h1>
<?php include __DIR__ . '/layout/footer.php'; ?>
```

### PHP - Nomenclatura

| Tipo | Formato | Ejemplo |
|------|---------|---------|
| Variables | `$snake_case` | `$fecha_cita`, `$db_url` |
| Métodos clase | `camelCase` | `getInstance()`, `fetchAll()` |
| Tablas BD | `snake_case` plural | `usuarios`, `citas`, `medicos` |
| Columnas BD | `snake_case` | `fecha_cita`, `medico_id` |

### PHP - Reglas de Seguridad

```php
// Incluir Database.php al inicio
require_once __DIR__ . '/config/Database.php';

// Prepared statements SIEMPRE (previene SQL injection)
$stmt = $pdo->prepare("SELECT * FROM tabla WHERE id = ?");
$stmt->execute([$id]);

// Casting para IDs numéricos
$id = (int)$_POST['id'];

// htmlspecialchars para output HTML (previene XSS)
<?= htmlspecialchars($variable) ?>

// Verificar sesión antes de páginas protegidas
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
```

### JavaScript

```javascript
// Usar DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // código aquí
});

// Fetch API para AJAX
fetch(url)
    .then(r => r.json())
    .then(data => { /* usar data */ });
```

### CSS

```css
/* kebab-case para clases */
.navbar { }
.btn-primary { }
```

### Estilos CSS en Vistas

- **NUNCA estilos inline** en las vistas
- Usar archivos CSS externos: `css/blog.css`, `css/admin.css`
- Los estilos van en archivos CSS, NO en etiquetas `<style>` en las vistas

---

## Base de Datos

### Tablas Principales

- `usuarios` - Pacientes y admins (id, nombre, apellidos, email, password, rol)
- `especialidades` - Especialidades médicas (id, nombre, descripcion, activo)
- `medicos` - Médicos (id, nombre, apellidos, especialidad_id, activo)
- `citas` - Citas (id, paciente_id, medico_id, especialidad_id, fecha, hora, estado, notas)

### Helpers de Base de Datos

```php
$db = Database::getInstance();

// SELECT - devolver todas las filas
$filas = $db->fetchAll("SELECT * FROM tabla WHERE id = ?", [$id]);

// INSERT - devolver ID insertado
$id = $db->insert("INSERT INTO tabla (col) VALUES (?) RETURNING id", [$valor]);

// UPDATE/DELETE - sin return
$db->execute("UPDATE tabla SET col = ? WHERE id = ?", [$valor, $id]);
```

---

## Rutas Principales

| URL | Descripción | Acceso | MVC |
|-----|-------------|--------|-----|
| `/index.php` | Página principal | Público | ❌ (integrado) |
| `/registro.php` | Registro de pacientes | Público | ✅ |
| `/login.php` | Login | Público | ✅ |
| `/logout.php` | Cerrar sesión | Logueado | ❌ (simple) |
| `/admin.php` | Panel admin | Admin | ✅ |
| `/cita-online.php` | Reservar cita | Logueado | ✅ |
| `/mis-citas.php` | Ver mis citas | Paciente | ✅ |
| `/citas-crud.php` | Gestionar citas | Admin | ✅ |
| `/usuarios-crud.php` | Gestionar usuarios | Admin | ✅ |
| `/api/medicos.php` | API médicos | Público | - |

### Parámetros URL

**citas-crud.php / usuarios-crud.php**:
```
?action=list          → Ver listado (por defecto)
?action=create        → Crear nuevo
?action=edit&id=5     → Editar ID 5
?action=delete&id=5  → Eliminar ID 5
```

**mis-citas.php**:
```
?cancelar=5           → Cancelar cita ID 5
```

---

## Sesiones PHP

```php
// Al hacer login (en AuthController)
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_rol'] = $usuario['rol'];  // 'admin', 'gestor' o 'paciente'

// Al cerrar sesión (en logout.php)
session_unset();
session_destroy();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar rol de admin
if ($_SESSION['usuario_rol'] === 'admin') {
    // Es administrador - permitir acceso
}
```

---

## Funcionalidades Implementadas

- [x] Registro de pacientes (MVC)
- [x] Login con contraseñas en texto plano (MVC)
- [x] Logout
- [x] Nav dinámico (muestra login/registro o nombre de usuario)
- [x] Reserva de citas con AJAX (MVC)
- [x] Ver mis citas (pacientes) (MVC)
- [x] Cancelar citas (pacientes) (MVC)
- [x] Gestionar todas las citas (admin) (MVC)
- [x] Editar/eliminar citas (admin) (MVC)
- [x] Gestionar usuarios (admin) (MVC)
- [x] Panel admin con estadísticas
- [x] Footer siempre al fondo (flexbox)

---

## Arquitectura MVC

```
┌─────────────────────────────────────────────────────────┐
│                    ENTRY POINT                          │
│              (ej: mis-citas.php)                        │
│  - Include controller                                    │
│  - Obtiene datos del controller                         │
│  - Include vista                                        │
└─────────────────────┬───────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────┐
│                   CONTROLLER                            │
│              (MisCitasController.php)                  │
│  - handleRequest()                                      │
│  - Valida sesión                                        │
│  - Procesa acciones (POST/GET)                          │
│  - Llama al modelo                                      │
│  - Devuelve array con datos                            │
└─────────────────────┬───────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────┐
│                     MODEL                               │
│                  (Cita.php)                             │
│  - Métodos para BD:                                     │
│    • all() - todos los registros                       │
│    • find(id) - uno por ID                              │
│    • create(data) - insertar                           │
│    • update(id, data) - modificar                     │
│    • delete(id) - eliminar                             │
│    • getByPaciente(id) - específicos                  │
└─────────────────────┬───────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────┐
│                      VIEW                               │
│                 (views/mis-citas.php)                   │
│  - Solo HTML + datos del controller                     │
│  - Include header/footer                               │
│  - foreach para iterar datos                           │
│  - htmlspecialchars para seguridad                    │
└─────────────────────────────────────────────────────────┘
```
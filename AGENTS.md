# AGENTS.md - Centro Médico TAC7

## Proyecto FCT - Gestión de Citas Médicas

**Estado**: Proyecto funcional con login/registro  
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

## Estructura de Archivos

```
/centro-medico
├── config/
│   └── Database.php          # Conexión BD + helpers
├── includes/
│   ├── header.php            # Navegación (dinámico según sesión)
│   └── footer.php            # Footer
├── css/
│   ├── style.css             # Estilos principales
│   └── crud-citas.css        # Estilos CRUD
├── js/
│   ├── scripts.js            # JS general
│   ├── cita-online.js        # Formulario de citas (AJAX)
│   └── crud-citas.js         # Filtrar médicos en edit
├── index.php                 # Página principal
├── registro.php              # Registro de nuevos pacientes
├── login.php                 # Login de usuarios
├── logout.php                # Cerrar sesión
├── cita-online.php           # Reservar cita (usuarios logueados)
├── mis-citas.php             # Ver MIS citas (pacientes)
├── citas-crud.php            # Gestionar TODAS las citas (admin)
├── login.php                 # Login
└── install.php              # Setup BD (borrar después)
```

---

## Sistema de Usuarios

### Roles

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| `admin` | Administrador | Gestionar TODAS las citas (crear, editar, eliminar) |
| `paciente` | Paciente registrado | Reservar citas, ver MIS citas, cancelar |

### Flujo de Login

```
Paciente → registro.php → login.php → index.php/mis-citas.php
Admin    → login.php → citas-crud.php
```

### Usuarios de Prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@tac7.com | admin123 |
| Paciente | juan.garcia@email.com | paciente123 |

---

## Convenciones de Código

### PHP - Nomenclatura

| Tipo | Formato | Ejemplo |
|------|---------|---------|
| Variables | `$snake_case` | `$fecha_cita`, `$db_url` |
| Constantes | `UPPER_SNAKE_CASE` | `APP_NOMBRE`, `APP_URL` |
| Métodos clase | `camelCase` | `getInstance()`, `fetchAll()` |
| Tablas BD | `snake_case` plural | `usuarios`, `citas`, `medicos` |
| Columnas BD | `snake_case` | `fecha_cita`, `medico_id` |

### PHP - Reglas

```php
// Incluir Database.php al inicio
require_once __DIR__ . '/config/Database.php';

// Prepared statements SIEMPRE
$stmt = $pdo->prepare("SELECT * FROM tabla WHERE id = ?");
$stmt->execute([$id]);

// Casting para IDs numéricos
$id = (int)$_POST['id'];

// htmlspecialchars para output HTML
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

| URL | Descripción | Acceso |
|-----|-------------|--------|
| `/index.php` | Página principal | Público |
| `/registro.php` | Registro de pacientes | Público |
| `/login.php` | Login | Público |
| `/logout.php` | Cerrar sesión | Logueado |
| `/cita-online.php` | Reservar cita | Logueado |
| `/mis-citas.php` | Ver mis citas | Paciente |
| `/citas-crud.php` | Gestionar citas | Admin |

### Parámetros URL (citas-crud.php)

```
?action=list          → Ver listado (por defecto)
?action=edit&id=5     → Editar cita ID 5
?action=delete&id=5   → Eliminar cita ID 5
```

### Parámetros URL (mis-citas.php)

```
?cancelar=5           → Cancelar cita ID 5
```

---

## Sesiones PHP

```php
// Al hacer login (en login.php)
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario_rol'] = $usuario['rol'];  // 'admin' o 'paciente'

// Al cerrar sesión (en logout.php)
session_unset();
session_destroy();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar rol
if ($_SESSION['usuario_rol'] === 'admin') {
    // Es administrador
}
```

---

## Funcionalidades Implementadas

- [x] Registro de pacientes
- [x] Login con contraseñas encriptadas
- [x] Logout
- [x] Nav dinámico (muestra login/registro o nombre de usuario)
- [x] Reserva de citas (solo logueados)
- [x] Ver mis citas (pacientes)
- [x] Cancelar citas (pacientes)
- [x] Gestionar todas las citas (admin)
- [x] Editar/eliminar citas (admin)

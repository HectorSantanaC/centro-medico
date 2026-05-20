# Centro Médico TAC7

Sistema de gestión integral para Centro Médico TAC7. Incluye panel de administración, reserva de citas online, blog, gestión de pacientes y API REST.

## Características principales

- **Panel de administración** con dashboard de estadísticas (citas por estado, especialidad, médico, gráficos mensuales)
- **Reserva de citas online** por especialidad → médico → fecha → hora disponible
- **Blog/CMS** con gestión de artículos y tópicos, incluyendo campos SEO
- **Gestión completa** de usuarios, roles, médicos, especialidades, citas y artículos
- **Sistema de roles** con permisos granulares (admin, gestor, administración, médico, paciente)
- **API REST** con endpoints para todas las operaciones CRUD
- **Imágenes** con soporte para almacenamiento local o Supabase Storage

## Tecnologías

| Capa | Tecnología |
|------|------------|
| Backend | PHP 8.0+ |
| Base de datos | PostgreSQL 12+ (PDO) |
| Servidor web | Apache con mod_rewrite |
| Frontend | HTML5, CSS3 (BEM), JavaScript vanilla |
| Almacenamiento imágenes | Filesystem local o Supabase Storage |
| Despliegue | Docker (`php:8.2-apache`), Render.com |

## Requisitos previos

- PHP 8.0 o superior
- PostgreSQL 12 o superior
- Extensiones PHP: `pdo_pgsql`, `session`
- Apache con `mod_rewrite` habilitado

## Instalación

### Opción manual (XAMPP o similar)

1. Clonar el repositorio:
   ```bash
   git clone <url-del-repositorio>
   ```

2. Crear la base de datos `centro-medico` en PostgreSQL:
   ```sql
   CREATE DATABASE centro_medico;
   ```

3. Configurar las credenciales de BD en `config/Database.php` (usuario por defecto: `postgres` / `1234`).

4. Ejecutar el instalador para crear las tablas y datos de prueba:
   ```
   http://localhost/centro-medico/install.php
   ```

### Opción Docker

1. Construir la imagen:
   ```bash
   docker build -t centro-medico .
   ```

2. Ejecutar el contenedor:
   ```bash
   docker run -p 8080:80 centro-medico
   ```

3. Acceder a `http://localhost:8080/install.php` para inicializar la BD.

## Variables de entorno

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `DATABASE_URL` | Cadena de conexión PostgreSQL (producción) | `postgres://user:pass@host:5432/db` |
| `SUPABASE_URL` | URL del proyecto Supabase (opcional) | `https://xxx.supabase.co` |
| `SUPABASE_KEY` | API key de Supabase (opcional) | `eyJhbG...` |
| `USE_SUPABASE_STORAGE` | Forzar uso de Supabase (`true`/`false`) | `false` |

> Si no se define `DATABASE_URL`, el sistema usa la configuración local de `config/Database.php`.

## Credenciales de prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@tac7.com | admin123 |
| Gestor | gestor@tac7.com | gestor123 |
| Paciente | paciente@tac7.com | paciente123 |

## Estructura del proyecto

```
centro-medico/
├── config/           # Conexión a BD (Database.php)
├── controllers/      # Controladores MVC (Base, Auth, Citas, etc.)
├── models/           # Modelos de datos (Usuario, Cita, Medico, etc.)
├── views/            # Vistas PHP organizadas por módulo
│   ├── layout/       # Header, footer, navbar admin
│   ├── auth/         # Login
│   ├── admin/        # Paneles de administración
│   └── *.php         # Vistas públicas (blog, citas, registro)
├── helpers/          # Funciones auxiliares (sanitize, auth API, Supabase)
├── api/              # Endpoints REST (citas, medicos, usuarios, etc.)
├── css/              # Estilos (public, admin, blog)
├── js/               # Scripts (admin CRUD, dashboard, booking)
├── assets/img/       # Imágenes del sitio
├── install.php       # Instalador de BD (tablas + seeds)
├── Dockerfile        # Imagen Docker (PHP 8.2 + Apache + PostgreSQL)
└── Procfile          # Despliegue en Render.com
```

## Módulos del CMS

| Módulo | Descripción | Acceso |
|--------|-------------|--------|
| Dashboard | Estadísticas de citas, gráficos, KPIs | Admin, Gestor, Administración |
| Agenda | Gestión de citas con filtros por fecha, médico, estado | Admin, Gestor, Administración |
| Especialidades | CRUD de especialidades médicas | Admin |
| Médicos | CRUD de médicos con imagen y especialidad | Admin, Gestor |
| Contenidos | Gestión de artículos del blog con campos SEO | Admin, Gestor |
| Tópicos | Categorías de artículos | Admin, Gestor |
| Usuarios | Gestión de usuarios del sistema | Admin |
| Roles | Administración de roles y permisos | Admin |

## Roles de usuario

| Rol | Permisos |
|-----|----------|
| **Admin** | Acceso total: usuarios, roles, CRUD completo, estadísticas |
| **Gestor** | Gestión de contenidos, artículos, tópicos, médicos, especialidades, citas |
| **Administración** | Gestión de citas y visualización del dashboard |
| **Médico** | Definido en BD, aún no implementado en UI |
| **Paciente** | Registro, reserva de citas online, visualización y cancelación de sus citas |

## API REST

Todos los endpoints devuelven JSON. Usan autenticación por sesión.

| Endpoint | Métodos | Auth requerida | Descripción |
|----------|---------|----------------|-------------|
| `api/citas.php` | GET, POST, PUT, DELETE | Gestor, Admin, Administración | CRUD de citas |
| `api/medicos.php` | GET, POST, PUT, DELETE | GET: público, POST/PUT/DELETE: Admin | CRUD de médicos |
| `api/usuarios.php` | GET, POST, PUT, DELETE | Gestor, Admin, Administración | CRUD de usuarios |
| `api/especialidades.php` | GET, POST, PUT, DELETE | GET: público, POST/PUT/DELETE: Admin | CRUD de especialidades |
| `api/articulos.php` | GET, POST, PUT, DELETE | GET: público, POST/PUT/DELETE: Gestor/Admin | CRUD de artículos |
| `api/topicos.php` | GET, POST, PUT, DELETE | GET: público, POST/PUT/DELETE: Gestor/Admin | CRUD de tópicos |
| `api/roles.php` | GET, POST, PUT, DELETE | GET: público, POST/PUT/DELETE: Admin | CRUD de roles |
| `api/horas.php` | GET | Cualquier usuario autenticado | Horas disponibles para una fecha y médico |
| `api/dashboard.php` | GET | Gestor, Admin, Administración | Estadísticas del dashboard |

## Páginas principales

### Públicas

| Ruta | Descripción |
|------|-------------|
| `/index.php` | Página principal (equipo, especialidades, opiniones) |
| `/login.php` | Inicio de sesión |
| `/registro.php` | Registro de pacientes |
| `/cita-online.php` | Reserva de citas online |
| `/mis-citas.php` | Panel del paciente (ver/cancelar citas) |
| `/blog.php` | Listado de artículos del blog |
| `/especialidad.php` | Detalle de especialidad con médicos |

### Administración

| Ruta | Descripción |
|------|-------------|
| `/admin.php` | Dashboard con estadísticas |
| `/admin-usuarios.php` | Gestión de usuarios |
| `/admin-roles.php` | Gestión de roles |
| `/admin-especialidades.php` | Gestión de especialidades |
| `/citas-crud.php` | Gestión de citas |
| `/medicos-crud.php` | Gestión de médicos |
| `/articulos-crud.php` | Gestión de artículos |
| `/topicos-crud.php` | Gestión de tópicos |

## Seguridad

- **CSRF:** Tokens en todos los formularios
- **XSS:** Salida escapada con `htmlspecialchars()` en todas las vistas
- **SQL Injection:** Prepared statements (PDO) en todas las queries
- **Contraseñas:** `password_hash()` / `password_verify()` (bcrypt)
- **Sesiones:** `session_regenerate_id(true)` tras login
- **Roles:** Verificación de permisos en cada controlador y endpoint API

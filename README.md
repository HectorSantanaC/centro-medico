# Centro Médico TAC7

Sistema de gestión para Centro Médico TAC7.

## Requisitos

- PHP 8.0+
- PostgreSQL 12+
- XAMPP o similar (con PostgreSQL)

## Instalación

1. Clonar el repositorio
2. Crear la base de datos `centro-medico`
3. Ejecutar el script de instalación (si existe)
4. Configurar credenciales en `config/Database.php`

## Credenciales de prueba

- **Admin:** admin@tac7.com / admin123
- **Gestor:** gestor@tac7.com / gestor123
- **Paciente:** paciente@tac7.com / paciente123

## Estructura del proyecto

```
├── controllers/      # Controladores MVC
├── models/           # Modelos de datos
├── views/            # Vistas PHP
├── helpers/         # Funciones auxiliares
├── config/          # Configuración
├── css/             # Estilos
├── js/              # JavaScript
├── api/             # APIs REST
└── assets/          # Imágenes y recursos
```

## Módulos del CMS

| Módulo | Descripción |
|--------|-------------|
| Agenda | Gestión de citas con filtros |
| Especialidades | CRUD de especialidades médicas |
| Médicos | CRUD de médicos con filtros |
| Contenidos | Gestión de artículos/blog |
| Tópicos | Categorías de contenidos |
| Usuarios | Gestión de usuarios |

## Roles de usuario

| Rol | Permisos |
|-----|----------|
| Admin | Acceso total al CMS |
| Gestor | Gestión de contenidos, citas, médicos, especialidades |
| Paciente | Reserva de citas online, visualización de sus citas |

## Páginas principales

- `/index.php` - Página principal
- `/login.php` - Inicio de sesión
- `/registro.php` - Registro de pacientes
- `/cita-online.php` - Reserva de citas
- `/blog.php` - Blog/Artículos
- `/admin.php` - Panel de administración

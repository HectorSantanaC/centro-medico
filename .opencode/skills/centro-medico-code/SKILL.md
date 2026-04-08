---
name: centro-medico-code
description: Genera código PHP siguiendo los estándares del proyecto Centro Médico TAC7
---
## Estándares del Proyecto

### Indentación
- Usar 2 espacios (no tabs, no 4 espacios)
- Mantener consistencia en todo el codebase

### Estructura MVC
- Controladores en /controllers/{Nombre}Controller.php
- Modelos en /models/{Nombre}.php
- Vistas en /views/{modulo}/{archivo}.php

### Nomenclatura
- Archivos: StudlyCase (AuthController.php)
- Clases: StudlyCase
- Métodos: camelCase (handleRequest)
- Variables: camelCase ($errores, $email)
- SQL: snake_case (ORDER BY nombre)

### Validadores obligatorios
- Sanitizar inputs con htmlspecialchars() para XSS
- Validar campos obligatorios (no vacíos)
- Verificar formatos (email válido, números positivos)
- Usar prepared statements para todas las queries SQL

### Helpers a incluir/recomendar en código
- Función sanitizeInput() para limpiar entradas de usuario
- Función formatFecha() para formatear fechas
- Función redirect() para navegaciones
- Función jsonResponse() para respuestas JSON

### Seguridad
- Verificar sesión activa en controladores privados
- Validar permisos de usuario (admin vs paciente)
- No exponer información sensible en errores
- Usar password_hash() y password_verify() para contraseñas

### Estructura de Controladores
```php
class {Nombre}Controller {
  private $db;
  
  public function __construct() {
    $database = new Database();
    $this->db = $database->getConnection();
  }
  
  public function handleRequest() {
    // Lógica principal
  }
}
```

### Estructura de Modelos
```php
class {Nombre} {
  private $db;
  
  public function __construct() {
    $database = new Database();
    $this->db = $database->getConnection();
  }
  
  public function all() { }
  public function find($id) { }
  public function create($data) { }
  public function update($id, $data) { }
  public function delete($id) { }
}
```

### Vistas
- Usar include para header/footer
- Mantener HTML limpio con PHP mínimo
- Usar htmlspecialchars() al mostrar variables de usuario
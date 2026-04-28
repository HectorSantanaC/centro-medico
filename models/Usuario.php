<?php

require_once __DIR__ . '/../config/Database.php';

class Usuario
{
  private $db;
  private $pdo;

  public function __construct()
  {
    $this->db = Database::getInstance();
    $this->pdo = $this->db->getConnection();
  }

  public function all(): array
  {
    return $this->db->fetchAll("
      SELECT DISTINCT u.* 
      FROM usuarios u
      INNER JOIN usuario_departamento_rol udr ON u.id = udr.usuario_id
      INNER JOIN roles r ON udr.rol_id = r.id
      INNER JOIN departamentos d ON udr.departamento_id = d.id
      WHERE r.nombre = 'paciente' AND d.nombre = 'Pacientes'
      ORDER BY u.nombre
    ");
  }

  public function find(int $id): ?array
  {
    $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function findByEmail(string $email): ?array
  {
    $stmt = $this->pdo->prepare("SELECT id, nombre, apellidos, email, password, created_at FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) return null;
    
    // Obtener roles con departamentos
    $stmt = $this->pdo->prepare("
      SELECT udr.departamento_id, d.nombre as departamento_nombre, 
             udr.rol_id, r.nombre as rol_nombre
      FROM usuario_departamento_rol udr
      LEFT JOIN departamentos d ON udr.departamento_id = d.id
      INNER JOIN roles r ON udr.rol_id = r.id
      WHERE udr.usuario_id = ?
    ");
    $stmt->execute([$usuario['id']]);
    $usuario['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $usuario;
  }

  public function getStats(): array
  {
    $stats = [];
 
    $stats['patients'] = (int) $this->db->fetchAll("
      SELECT COUNT(DISTINCT u.id) as total 
      FROM usuarios u
      INNER JOIN usuario_departamento_rol udr ON u.id = udr.usuario_id
      INNER JOIN roles r ON udr.rol_id = r.id
      INNER JOIN departamentos d ON udr.departamento_id = d.id
      WHERE r.nombre = 'paciente' AND d.nombre = 'Pacientes'
    ")[0]['total'];
 
    $stats['citas'] = (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM citas"
    )[0]['total'];
 
    return $stats;
  }

  public function create(array $data): int
  {
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (nombre, apellidos, email, password) 
      VALUES (?, ?, ?, ?) RETURNING id";
    
    $usuario_id = $this->db->insert($sql, [
      $data['nombre'],
      $data['apellidos'],
      $data['email'],
      $passwordHash
    ]);
    
    // Asignar rol paciente en departamento "Pacientes"
    $rolStmt = $this->pdo->prepare("SELECT id FROM roles WHERE nombre = 'paciente'");
    $rolStmt->execute();
    $rol = $rolStmt->fetch(PDO::FETCH_ASSOC);
    
    $deptStmt = $this->pdo->prepare("SELECT id FROM departamentos WHERE nombre = 'Pacientes'");
    $deptStmt->execute();
    $dept = $deptStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rol && $dept) {
      $stmt = $this->pdo->prepare("
        INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id) 
        VALUES (?, ?, ?)
      ");
      $stmt->execute([$usuario_id, $dept['id'], $rol['id']]);
    }
    
    return $usuario_id;
  }

  public function existsByEmail(string $email): bool
  {
    $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    return (bool) $stmt->fetch();
  }

  public function allUsers(): array
  {
    return $this->db->fetchAll("SELECT * FROM usuarios ORDER BY created_at DESC");
  }

  public function allSafe(): array
  {
    $data = $this->db->fetchAll("
      SELECT u.id, u.nombre, u.apellidos, u.email, u.created_at,
              COALESCE(STRING_AGG(DISTINCT r.nombre || ' (' || COALESCE(d.nombre, 'Sin depto') || ')', ', '), '-') as roles
      FROM usuarios u
      LEFT JOIN usuario_departamento_rol udr ON u.id = udr.usuario_id
      LEFT JOIN roles r ON udr.rol_id = r.id
      LEFT JOIN departamentos d ON udr.departamento_id = d.id
      GROUP BY u.id, u.nombre, u.apellidos, u.email, u.created_at
      ORDER BY u.created_at DESC
    ");
    return $data;
  }

  public function findSafe(int $id): ?array
  {
    $stmt = $this->pdo->prepare("
      SELECT u.id, u.nombre, u.apellidos, u.email, u.created_at
      FROM usuarios u
      WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) return null;
    
    // Obtener roles con departamentos
    $stmt = $this->pdo->prepare("
      SELECT udr.departamento_id, d.nombre as departamento_nombre, 
             udr.rol_id, r.nombre as rol_nombre
      FROM usuario_departamento_rol udr
      LEFT JOIN departamentos d ON udr.departamento_id = d.id
      INNER JOIN roles r ON udr.rol_id = r.id
      WHERE udr.usuario_id = ?
    ");
    $stmt->execute([$id]);
    $usuario['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $usuario;
  }

  public function update(int $id, array $data): bool
  {
    if (isset($data['password']) && !empty($data['password'])) {
      $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
      $this->db->execute(
        "UPDATE usuarios SET nombre=?, apellidos=?, email=?, password=? WHERE id=?",
        [$data['nombre'], $data['apellidos'], $data['email'], $passwordHash, $id]
      );
    } else {
      $this->db->execute(
        "UPDATE usuarios SET nombre=?, apellidos=?, email=? WHERE id=?",
        [$data['nombre'], $data['apellidos'], $data['email'], $id]
      );
    }
    return true;
  }

  public function delete(int $id): bool
  {
    $this->db->execute("DELETE FROM usuarios WHERE id = ?", [$id]);
    return true;
  }

  public function createWithRole(array $data): int
  {
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (nombre, apellidos, email, password) VALUES (?, ?, ?, ?) RETURNING id";
    
    $usuario_id = $this->db->insert($sql, [
      $data['nombre'],
      $data['apellidos'],
      $data['email'],
      $passwordHash
    ]);
    
    // Asignar rol y departamento
    if (isset($data['rol_id']) && isset($data['departamento_id'])) {
      $stmt = $this->pdo->prepare("
        INSERT INTO usuario_departamento_rol (usuario_id, departamento_id, rol_id) 
        VALUES (?, ?, ?)
      ");
      $stmt->execute([$usuario_id, $data['departamento_id'], $data['rol_id']]);
    }
    
    return $usuario_id;
  }

  public function allPaginated(int $page = 1, int $perPage = 10, array $filtros = []): array
  {
    $offset = ($page - 1) * $perPage;
    $where = [];
    $params = [];
 
    if (!empty($filtros['nombre'])) {
      $where[] = "(u.nombre ILIKE ? OR u.apellidos ILIKE ?)";
      $params[] = '%' . $filtros['nombre'] . '%';
      $params[] = '%' . $filtros['nombre'] . '%';
    }
 
    if (!empty($filtros['apellidos'])) {
      $where[] = "u.apellidos ILIKE ?";
      $params[] = '%' . $filtros['apellidos'] . '%';
    }
 
    if (!empty($filtros['email'])) {
      $where[] = "u.email ILIKE ?";
      $params[] = '%' . $filtros['email'] . '%';
    }
 
    if (!empty($filtros['rol']) && $filtros['rol'] !== '') {
      $where[] = "r.nombre = ?";
      $params[] = $filtros['rol'];
    }
 
    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $params[] = $perPage;
    $params[] = $offset;
 
    $data = $this->db->fetchAll("
      SELECT DISTINCT u.id, u.nombre, u.apellidos, u.email, u.created_at,
              COALESCE(STRING_AGG(DISTINCT r.nombre || ' (' || COALESCE(d.nombre, 'Sin depto') || ')', ', '), '-') as roles
      FROM usuarios u
      LEFT JOIN usuario_departamento_rol udr ON u.id = udr.usuario_id
      LEFT JOIN roles r ON udr.rol_id = r.id
      LEFT JOIN departamentos d ON udr.departamento_id = d.id
      $whereSql
      GROUP BY u.id, u.nombre, u.apellidos, u.email, u.created_at
      ORDER BY u.created_at DESC
      LIMIT ? OFFSET ?
    ", $params);
    
    return $data;
  }
 
  public function countAll(array $filtros = []): int
  {
    $where = [];
    $params = [];
 
    if (!empty($filtros['nombre'])) {
      $where[] = "(u.nombre ILIKE ? OR u.apellidos ILIKE ?)";
      $params[] = '%' . $filtros['nombre'] . '%';
      $params[] = '%' . $filtros['nombre'] . '%';
    }
 
    if (!empty($filtros['apellidos'])) {
      $where[] = "u.apellidos ILIKE ?";
      $params[] = '%' . $filtros['apellidos'] . '%';
    }
 
    if (!empty($filtros['email'])) {
      $where[] = "u.email ILIKE ?";
      $params[] = '%' . $filtros['email'] . '%';
    }
 
    if (!empty($filtros['rol']) && $filtros['rol'] !== '') {
      $where[] = "r.nombre = ?";
      $params[] = $filtros['rol'];
    }
 
    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
 
    return (int) $this->db->fetchAll("
      SELECT COUNT(DISTINCT u.id) as total 
      FROM usuarios u
      LEFT JOIN usuario_departamento_rol udr ON u.id = udr.usuario_id
      LEFT JOIN roles r ON udr.rol_id = r.id
      $whereSql
    ", $params)[0]['total'];
  }
}

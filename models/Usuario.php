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
    return $this->db->fetchAll("SELECT * FROM usuarios WHERE rol = 'paciente' ORDER BY nombre");
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
    $stmt = $this->pdo->prepare("SELECT id, nombre, apellidos, email, password, rol FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getStats(): array
  {
    $stats = [];

    $stats['patients'] = (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'paciente'"
    )[0]['total'];

    $stats['citas'] = (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM citas"
    )[0]['total'];

    return $stats;
  }

  public function create(array $data): int
  {
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (nombre, apellidos, email, password, rol) 
      VALUES (?, ?, ?, ?, 'paciente') RETURNING id";
    
    return $this->db->insert($sql, [
      $data['nombre'],
      $data['apellidos'],
      $data['email'],
      $passwordHash
    ]);
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
    return $this->db->fetchAll("SELECT id, nombre, apellidos, email, rol, created_at FROM usuarios ORDER BY created_at DESC");
  }

  public function findSafe(int $id): ?array
  {
    $stmt = $this->pdo->prepare("SELECT id, nombre, apellidos, email, rol, created_at FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function update(int $id, array $data): bool
  {
    if (isset($data['password']) && !empty($data['password'])) {
      $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
      $this->db->execute(
        "UPDATE usuarios SET nombre=?, apellidos=?, email=?, password=?, rol=? WHERE id=?",
        [$data['nombre'], $data['apellidos'], $data['email'], $passwordHash, $data['rol'], $id]
      );
    } else {
      $this->db->execute(
        "UPDATE usuarios SET nombre=?, apellidos=?, email=?, rol=? WHERE id=?",
        [$data['nombre'], $data['apellidos'], $data['email'], $data['rol'], $id]
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
    
    $sql = "INSERT INTO usuarios (nombre, apellidos, email, password, rol) VALUES (?, ?, ?, ?, ?) RETURNING id";
    
    return $this->db->insert($sql, [
      $data['nombre'],
      $data['apellidos'],
      $data['email'],
      $passwordHash,
      $data['rol']
    ]);
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
      $where[] = "u.rol = ?";
      $params[] = $filtros['rol'];
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $params[] = $perPage;
    $params[] = $offset;

    return $this->db->fetchAll("
      SELECT u.id, u.nombre, u.apellidos, u.email, u.rol, u.created_at
      FROM usuarios u
      $whereSql
      ORDER BY u.created_at DESC
      LIMIT ? OFFSET ?
    ", $params);
  }

  public function countAll(array $filtros = []): int
  {
    $where = [];
    $params = [];

    if (!empty($filtros['nombre'])) {
      $where[] = "(nombre ILIKE ? OR apellidos ILIKE ?)";
      $params[] = '%' . $filtros['nombre'] . '%';
      $params[] = '%' . $filtros['nombre'] . '%';
    }

    if (!empty($filtros['apellidos'])) {
      $where[] = "apellidos ILIKE ?";
      $params[] = '%' . $filtros['apellidos'] . '%';
    }

    if (!empty($filtros['email'])) {
      $where[] = "email ILIKE ?";
      $params[] = '%' . $filtros['email'] . '%';
    }

    if (!empty($filtros['rol']) && $filtros['rol'] !== '') {
      $where[] = "rol = ?";
      $params[] = $filtros['rol'];
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM usuarios u $whereSql",
      $params
    )[0]['total'];
  }
}

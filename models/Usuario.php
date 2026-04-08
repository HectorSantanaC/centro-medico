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
}

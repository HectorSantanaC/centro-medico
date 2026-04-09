<?php

require_once __DIR__ . '/../config/Database.php';

class Especialidad
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function allActives(): array
  {
    return $this->db->fetchAll(
      "SELECT * FROM especialidades WHERE activo = true ORDER BY nombre"
    );
  }

  public function all(): array
  {
    return $this->db->fetchAll("SELECT * FROM especialidades ORDER BY nombre");
  }

  public function find(int $id): ?array
  {
    return $this->db->fetchAll(
      "SELECT * FROM especialidades WHERE id = ?",
      [$id]
    )[0] ?? null;
  }

  public function create(array $data): int
  {
    $sql = "INSERT INTO especialidades (nombre, activo) VALUES (?, ?) RETURNING id";
    return $this->db->insert($sql, [
      $data['nombre'],
      $data['activo'] ?? true
    ]);
  }

  public function update(int $id, array $data): bool
  {
    $this->db->execute(
      "UPDATE especialidades SET nombre = ?, activo = ? WHERE id = ?",
      [$data['nombre'], $data['activo'] ?? true, $id]
    );
    return true;
  }

  public function delete(int $id): bool
  {
    $this->db->execute("DELETE FROM especialidades WHERE id = ?", [$id]);
    return true;
  }

  public function count(): int
  {
    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM especialidades WHERE activo = true"
    )[0]['total'];
  }
}

<?php

require_once __DIR__ . '/../config/Database.php';

class Rol
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function all(): array
  {
    return $this->db->fetchAll("SELECT * FROM roles ORDER BY nombre");
  }

  public function find(int $id): ?array
  {
    $result = $this->db->fetchAll("SELECT * FROM roles WHERE id = ?", [$id]);
    return $result ? $result[0] : null;
  }

  public function create(array $data): int
  {
    $sql = "INSERT INTO roles (nombre, descripcion, activo) VALUES (?, ?, ?) RETURNING id";
    return $this->db->insert($sql, [
      $data['nombre'],
      $data['descripcion'] ?? null,
      $data['activo'] ?? true
    ]);
  }

  public function update(int $id, array $data): bool
  {
    $this->db->execute(
      "UPDATE roles SET nombre = ?, descripcion = ?, activo = ? WHERE id = ?",
      [
        $data['nombre'],
        $data['descripcion'] ?? null,
        $data['activo'] ?? true,
        $id
      ]
    );
    return true;
  }

  public function delete(int $id): bool
  {
    $this->db->execute("DELETE FROM roles WHERE id = ?", [$id]);
    return true;
  }

  public function allActives(): array
  {
    return $this->db->fetchAll("SELECT * FROM roles WHERE activo = true ORDER BY nombre");
  }
}
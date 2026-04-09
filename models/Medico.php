<?php

require_once __DIR__ . '/../config/Database.php';

class Medico
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  public function all(): array
  {
    return $this->db->fetchAll("
      SELECT m.*, e.nombre as especialidad_nombre 
      FROM medicos m 
      LEFT JOIN especialidades e ON m.especialidad_id = e.id 
      ORDER BY m.nombre, m.apellidos
    ");
  }

  public function allActives(): array
  {
    return $this->db->fetchAll("
      SELECT m.*, e.nombre as especialidad_nombre 
      FROM medicos m 
      LEFT JOIN especialidades e ON m.especialidad_id = e.id 
      WHERE m.activo = true 
      ORDER BY m.nombre
    ");
  }

  public function find(int $id): ?array
  {
    return $this->db->fetchAll(
      "SELECT m.*, e.nombre as especialidad_nombre 
       FROM medicos m 
       LEFT JOIN especialidades e ON m.especialidad_id = e.id 
       WHERE m.id = ?",
      [$id]
    )[0] ?? null;
  }

  public function create(array $data): int
  {
    $sql = "INSERT INTO medicos (nombre, apellidos, especialidad_id, activo) 
             VALUES (?, ?, ?, ?) RETURNING id";
    return $this->db->insert($sql, [
      $data['nombre'],
      $data['apellidos'],
      $data['especialidad_id'] ?? null,
      $data['activo'] ?? true
    ]);
  }

  public function update(int $id, array $data): bool
  {
    $this->db->execute(
      "UPDATE medicos SET nombre = ?, apellidos = ?, especialidad_id = ?, activo = ? WHERE id = ?",
      [
        $data['nombre'],
        $data['apellidos'],
        $data['especialidad_id'] ?? null,
        $data['activo'] ?? true,
        $id
      ]
    );
    return true;
  }

  public function delete(int $id): bool
  {
    $this->db->execute("DELETE FROM medicos WHERE id = ?", [$id]);
    return true;
  }

  public function allPaginated(int $page = 1, int $perPage = 10): array
  {
    $offset = ($page - 1) * $perPage;
    return $this->db->fetchAll("
      SELECT m.*, e.nombre as especialidad_nombre 
      FROM medicos m 
      LEFT JOIN especialidades e ON m.especialidad_id = e.id 
      ORDER BY m.nombre, m.apellidos
      LIMIT ? OFFSET ?
    ", [$perPage, $offset]);
  }

  public function countAll(): int
  {
    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM medicos"
    )[0]['total'];
  }

  public function getByEspecialidad(int $especialidadId): array
  {
    if ($especialidadId <= 0) {
      return [];
    }
    
    return $this->db->fetchAll(
      "SELECT id, nombre || ' ' || apellidos as nombre_completo
      FROM medicos 
      WHERE especialidad_id = ? AND activo = true 
      ORDER BY apellidos",
      [$especialidadId]
    );
  }

  public function count(): int
  {
    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM medicos WHERE activo = true"
    )[0]['total'];
  }
}

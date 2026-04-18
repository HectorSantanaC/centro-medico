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
    $sql = "INSERT INTO especialidades (nombre, descripcion, activo) VALUES (?, ?, ?) RETURNING id";
    return $this->db->insert($sql, [
      $data['nombre'],
      $data['descripcion'] ?? null,
      $data['activo'] ?? true
    ]);
  }

  public function update(int $id, array $data): bool
  {
    $this->db->execute(
      "UPDATE especialidades SET nombre = ?, descripcion = ?, activo = ? WHERE id = ?",
      [$data['nombre'], $data['descripcion'] ?? null, $data['activo'] ?? true, $id]
    );
    return true;
  }

  public function delete(int $id): bool
  {
    $this->db->execute("DELETE FROM especialidades WHERE id = ?", [$id]);
    return true;
  }

  public function allPaginated(int $page = 1, int $perPage = 10, array $filtros = []): array
  {
    $offset = ($page - 1) * $perPage;
    $where = [];
    $params = [];

    if (!empty($filtros['nombre'])) {
      $where[] = "(e.nombre ILIKE ? OR e.descripcion ILIKE ?)";
      $params[] = '%' . $filtros['nombre'] . '%';
      $params[] = '%' . $filtros['nombre'] . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $params[] = $perPage;
    $params[] = $offset;

    return $this->db->fetchAll("
      SELECT e.*
      FROM especialidades e
      $whereSql
      ORDER BY e.nombre
      LIMIT ? OFFSET ?
    ", $params);
  }

  public function countAll(array $filtros = []): int
  {
    $where = [];
    $params = [];

    if (!empty($filtros['nombre'])) {
      $where[] = "(nombre ILIKE ? OR descripcion ILIKE ?)";
      $params[] = '%' . $filtros['nombre'] . '%';
      $params[] = '%' . $filtros['nombre'] . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM especialidades $whereSql",
      $params
    )[0]['total'];
  }

  public function count(): int
  {
    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM especialidades WHERE activo = true"
    )[0]['total'];
  }

  public function allFiltered(array $filtros = []): array
  {
    $where = [];
    $params = [];

    if (!empty($filtros['nombre'])) {
      $where[] = "(nombre ILIKE ? OR descripcion ILIKE ?)";
      $params[] = '%' . $filtros['nombre'] . '%';
      $params[] = '%' . $filtros['nombre'] . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    return $this->db->fetchAll("
      SELECT * FROM especialidades
      $whereSql
      ORDER BY nombre
    ", $params);
  }
}

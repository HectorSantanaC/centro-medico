<?php

require_once __DIR__ . '/../config/Database.php';

class Topico {

  private $db;
  private $pdo;

  public function __construct() {
    $this->db = Database::getInstance();
    $this->pdo = $this->db->getConnection();
  }

  public function all(): array {
    return $this->db->fetchAll("SELECT * FROM topicos ORDER BY nombre DESC");
  }

  public function find(int $id): ?array {
    $stmt = $this->pdo->prepare("SELECT * FROM topicos WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function create(array $data): int {
    $sql = "INSERT INTO topicos (nombre) VALUES (?) RETURNING id";
    return $this->db->insert($sql, [$data['nombre'] ?? $data['name']]);
  }

  public function update(int $id, array $data): bool {
    $sql = "UPDATE topicos SET nombre = ? WHERE id = ?";
    $this->db->execute($sql, [$data['nombre'] ?? $data['name'], $id]);
    return true;
  }

  public function delete(int $id): bool {
    $this->db->execute("DELETE FROM topicos WHERE id = ?", [$id]);
    return true;
  }

  public function allPaginated(int $page = 1, int $perPage = 10, array $filtros = []): array
  {
    $offset = ($page - 1) * $perPage;
    $where = [];
    $params = [];

    if (!empty($filtros['nombre'])) {
      $where[] = "(t.nombre ILIKE ?)";
      $params[] = '%' . $filtros['nombre'] . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $params[] = $perPage;
    $params[] = $offset;

    return $this->db->fetchAll("
      SELECT t.*
      FROM topicos t
      $whereSql
      ORDER BY t.nombre
      LIMIT ? OFFSET ?
    ", $params);
  }

  public function countAll(array $filtros = []): int
  {
    $where = [];
    $params = [];

    if (!empty($filtros['nombre'])) {
      $where[] = "nombre ILIKE ?";
      $params[] = '%' . $filtros['nombre'] . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM topicos t $whereSql",
      $params
    )[0]['total'];
  }
}

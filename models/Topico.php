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
    return $this->db->insert($sql, [$data['name']]);
  }

  public function update(int $id, array $data): bool {
    $sql = "UPDATE topicos SET nombre = :nombre WHERE id = :id";

    $data['id'] = $id;
    $this->db->execute($sql, $data);

    return true;
  }

  public function delete(int $id): bool {
    $this->db->execute("DELETE FROM topicos WHERE id = :id", ['id' => $id]);
    return true;
  }
}

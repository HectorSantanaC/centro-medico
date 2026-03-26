<?php

require_once __DIR__ . '/../config/Database.php';

class Articulo
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
            SELECT * FROM articulos 
            WHERE publicado = true 
            ORDER BY created_at DESC
        ");
  }

  public function allAdmin(): array
  {
    return $this->db->fetchAll("
            SELECT * FROM articulos 
            ORDER BY created_at DESC
        ");
  }

  public function find(int $id): ?array
  {
    $stmt = $this->pdo->prepare("SELECT * FROM articulos WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function create(array $data): int
  {
    $sql = "INSERT INTO articulos (titulo, contenido, resumen, imagen, autor, categoria, publicado) 
                VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING id";

    return $this->db->insert($sql, [
      $data['titulo'],
      $data['contenido'],
      $data['resumen'] ?? '',
      $data['imagen'] ?? '',
      $data['autor'] ?? '',
      $data['categoria'] ?? '',
      $data['publicado'] ?? true
    ]);
  }

  public function update(int $id, array $data): bool
  {
    $sql = "UPDATE articulos SET 
                    titulo = ?, 
                    contenido = ?, 
                    resumen = ?, 
                    imagen = ?, 
                    autor = ?, 
                    categoria = ?, 
                    publicado = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

    $this->db->execute($sql, [
      $data['titulo'],
      $data['contenido'],
      $data['resumen'] ?? '',
      $data['imagen'] ?? '',
      $data['autor'] ?? '',
      $data['categoria'] ?? '',
      $data['publicado'] ?? true,
      $id
    ]);
    return true;
  }

  public function delete(int $id): bool
  {
    $this->db->execute("DELETE FROM articulos WHERE id = ?", [$id]);
    return true;
  }

  public function getRecientes(int $limit = 3): array
  {
    return $this->db->fetchAll("
            SELECT * FROM articulos 
            WHERE publicado = true 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$limit]);
  }
}

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
            SELECT a.*, t.nombre as topico_nombre
            FROM articulos a
            LEFT JOIN topicos t ON a.topico = t.id
            WHERE a.publicado = true 
            ORDER BY a.created_at DESC
        ");
  }

  public function allAdmin(): array
  {
    return $this->db->fetchAll("
            SELECT a.*, t.nombre as topico_nombre
            FROM articulos a
            LEFT JOIN topicos t ON a.topico = t.id
            ORDER BY a.created_at DESC
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
    $sql = "INSERT INTO articulos (
      titulo, topico, contenido_completo, contenido_reducido, 
      fecha_contenido, fecha_caducidad, orden, notas,
      imagen, imagen_url, autor, publicado,
      seo_titulo, seo_descripcion, seo_palabras_clave
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id";

    return $this->db->insert($sql, [
      $data['titulo'],
      $data['topico'],
      $data['contenido_completo'] ?? '',
      $data['contenido_reducido'] ?? '',
      $data['fecha_contenido'] ?? null,
      $data['fecha_caducidad'] ?? null,
      $data['orden'] ?? 0,
      $data['notas'] ?? '',
      $data['imagen'] ?? '',
      $data['imagen_url'] ?? '',
      $data['autor'] ?? '',
      $data['publicado'] ?? true,
      $data['seo_titulo'] ?? '',
      $data['seo_descripcion'] ?? '',
      $data['seo_palabras_clave'] ?? ''
    ]);
  }

  public function update(int $id, array $data): bool
  {
    $sql = "UPDATE articulos SET 
      titulo = ?, 
      topico = ?, 
      contenido_completo = ?, 
      contenido_reducido = ?, 
      fecha_contenido = ?, 
      fecha_caducidad = ?, 
      orden = ?, 
      notas = ?,
      imagen = ?, 
      imagen_url = ?,
      autor = ?, 
      publicado = ?,
      seo_titulo = ?,
      seo_descripcion = ?,
      seo_palabras_clave = ?,
      updated_at = CURRENT_TIMESTAMP
    WHERE id = ?";

    $this->db->execute($sql, [
      $data['titulo'],
      $data['topico'],
      $data['contenido_completo'] ?? '',
      $data['contenido_reducido'] ?? '',
      $data['fecha_contenido'] ?? null,
      $data['fecha_caducidad'] ?? null,
      $data['orden'] ?? 0,
      $data['notas'] ?? '',
      $data['imagen'] ?? '',
      $data['imagen_url'] ?? '',
      $data['autor'] ?? '',
      $data['publicado'] ?? true,
      $data['seo_titulo'] ?? '',
      $data['seo_descripcion'] ?? '',
      $data['seo_palabras_clave'] ?? '',
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

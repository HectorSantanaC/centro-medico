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

    $topico = !empty($data['topico']) ? (int)$data['topico'] : null;
    $publicado = isset($data['publicado']) ? (bool)$data['publicado'] : true;

    return $this->db->insert($sql, [
      $data['titulo'],
      $topico,
      $data['contenido_completo'] ?? '',
      $data['contenido_reducido'] ?? '',
      $data['fecha_contenido'] ?? null,
      $data['fecha_caducidad'] ?? null,
      $data['orden'] ?? 0,
      $data['notas'] ?? '',
      $data['imagen'] ?? '',
      $data['imagen_url'] ?? '',
      $data['autor'] ?? '',
      $publicado,
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

  public function existsByTitulo(string $titulo, ?int $excludeId = null): bool
  {
    $sql = "SELECT id FROM articulos WHERE titulo = ?";
    $params = [$titulo];
    if ($excludeId !== null) {
      $sql .= " AND id != ?";
      $params[] = $excludeId;
    }
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetch();
  }

  public function deleteWithImage(int $id): bool
  {
    $articulo = $this->find($id);
    if ($articulo && !empty($articulo['imagen'])) {
      $ruta = __DIR__ . '/../' . $articulo['imagen'];
      if (file_exists($ruta)) {
        unlink($ruta);
      }
    }
    return $this->delete($id);
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

  public function getByTopico(int $topicoId): array
  {
    return $this->db->fetchAll("
            SELECT a.*, t.nombre as topico_nombre
            FROM articulos a
            LEFT JOIN topicos t ON a.topico = t.id
            WHERE a.topico = ? AND a.publicado = true
            ORDER BY a.created_at DESC
        ", [$topicoId]);
  }

  public function allPaginated(int $page = 1, int $perPage = 10, array $filtros = []): array
  {
    $offset = ($page - 1) * $perPage;
    $where = [];
    $params = [];

    if (!empty($filtros['titulo'])) {
      $where[] = "(a.titulo ILIKE ? OR a.contenido_completo ILIKE ?)";
      $params[] = '%' . $filtros['titulo'] . '%';
      $params[] = '%' . $filtros['titulo'] . '%';
    }

    if (!empty($filtros['topico']) && $filtros['topico'] > 0) {
      $where[] = "a.topico = ?";
      $params[] = (int) $filtros['topico'];
    }

    if (!empty($filtros['fecha_desde'])) {
      $where[] = "a.created_at >= ?";
      $params[] = $filtros['fecha_desde'];
    }

    if (!empty($filtros['fecha_hasta'])) {
      $where[] = "a.created_at <= ?";
      $params[] = $filtros['fecha_hasta'];
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $params[] = $perPage;
    $params[] = $offset;

    return $this->db->fetchAll("
      SELECT a.*, t.nombre as topico_nombre
      FROM articulos a
      LEFT JOIN topicos t ON a.topico = t.id
      $whereSql
      ORDER BY a.created_at DESC
      LIMIT ? OFFSET ?
    ", $params);
  }

  public function countAll(array $filtros = []): int
  {
    $where = [];
    $params = [];

    if (!empty($filtros['titulo'])) {
      $where[] = "(titulo ILIKE ? OR contenido_completo ILIKE ?)";
      $params[] = '%' . $filtros['titulo'] . '%';
      $params[] = '%' . $filtros['titulo'] . '%';
    }

    if (!empty($filtros['topico']) && $filtros['topico'] > 0) {
      $where[] = "topico = ?";
      $params[] = (int) $filtros['topico'];
    }

    if (!empty($filtros['fecha_desde'])) {
      $where[] = "created_at >= ?";
      $params[] = $filtros['fecha_desde'];
    }

    if (!empty($filtros['fecha_hasta'])) {
      $where[] = "created_at <= ?";
      $params[] = $filtros['fecha_hasta'];
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM articulos a $whereSql",
      $params
    )[0]['total'];
  }
}

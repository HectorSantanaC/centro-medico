<?php

require_once __DIR__ . '/../config/Database.php';

class Cita
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
      SELECT c.*, 
        u.nombre as paciente_nombre, 
        u.apellidos as paciente_apellidos, 
        m.nombre as medico_nombre, 
        m.apellidos as medico_apellidos, 
        e.nombre as especialidad_nombre 
      FROM citas c 
      LEFT JOIN usuarios u ON c.paciente_id = u.id 
      LEFT JOIN medicos m ON c.medico_id = m.id 
      LEFT JOIN especialidades e ON c.especialidad_id = e.id 
      ORDER BY c.fecha DESC, c.hora DESC
    ");
  }

  public function find(int $id): ?array
  {
    $stmt = $this->pdo->prepare("SELECT * FROM citas WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function update(int $id, array $data): bool
  {
    $sql = "UPDATE citas SET 
      paciente_id = :paciente_id, 
      medico_id = :medico_id, 
      especialidad_id = :especialidad_id, 
      fecha = :fecha, 
      hora = :hora, 
      estado = :estado, 
      notas = :notas 
    WHERE id = :id";

    $data['id'] = $id;
    $this->db->execute($sql, $data);
    return true;
  }

  public function delete(int $id): bool
  {
    $this->db->execute("DELETE FROM citas WHERE id = :id", ['id' => $id]);
    return true;
  }

  public function create(array $data): int
  {
    $sql = "INSERT INTO citas (paciente_id, medico_id, especialidad_id, fecha, hora, estado) 
      VALUES (?, ?, ?, ?, ?, 'pendiente') RETURNING id";
    
    return $this->db->insert($sql, [
      $data['paciente_id'],
      $data['medico_id'],
      $data['especialidad_id'],
      $data['fecha'],
      $data['hora']
    ]);
  }

  public function allPaginated(int $page = 1, int $perPage = 10, array $filtros = []): array
  {
    $offset = ($page - 1) * $perPage;
    $where = [];
    $params = [];

    if (!empty($filtros['fecha_desde'])) {
      $where[] = "c.fecha >= :fecha_desde";
      $params['fecha_desde'] = $filtros['fecha_desde'];
    }

    if (!empty($filtros['fecha_hasta'])) {
      $where[] = "c.fecha <= :fecha_hasta";
      $params['fecha_hasta'] = $filtros['fecha_hasta'];
    }

    if (!empty($filtros['estado']) && $filtros['estado'] !== '') {
      $where[] = "c.estado = :estado";
      $params['estado'] = $filtros['estado'];
    }

    if (!empty($filtros['especialidad_id']) && $filtros['especialidad_id'] > 0) {
      $where[] = "c.especialidad_id = :especialidad_id";
      $params['especialidad_id'] = (int) $filtros['especialidad_id'];
    }

    if (!empty($filtros['paciente_id']) && $filtros['paciente_id'] > 0) {
      $where[] = "c.paciente_id = :paciente_id";
      $params['paciente_id'] = (int) $filtros['paciente_id'];
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
      SELECT c.*, 
        u.nombre as paciente_nombre, 
        u.apellidos as paciente_apellidos, 
        m.nombre as medico_nombre, 
        m.apellidos as medico_apellidos, 
        e.nombre as especialidad_nombre 
      FROM citas c 
      LEFT JOIN usuarios u ON c.paciente_id = u.id 
      LEFT JOIN medicos m ON c.medico_id = m.id 
      LEFT JOIN especialidades e ON c.especialidad_id = e.id 
      $whereSql
      ORDER BY c.fecha DESC, c.hora DESC
      LIMIT :limit OFFSET :offset
    ";

    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function countAll(array $filtros = []): int
  {
    $where = [];
    $params = [];

    if (!empty($filtros['fecha_desde'])) {
      $where[] = "fecha >= :fecha_desde";
      $params['fecha_desde'] = $filtros['fecha_desde'];
    }

    if (!empty($filtros['fecha_hasta'])) {
      $where[] = "fecha <= :fecha_hasta";
      $params['fecha_hasta'] = $filtros['fecha_hasta'];
    }

    if (!empty($filtros['estado']) && $filtros['estado'] !== '') {
      $where[] = "estado = :estado";
      $params['estado'] = $filtros['estado'];
    }

    if (!empty($filtros['especialidad_id']) && $filtros['especialidad_id'] > 0) {
      $where[] = "especialidad_id = :especialidad_id";
      $params['especialidad_id'] = (int) $filtros['especialidad_id'];
    }

    if (!empty($filtros['paciente_id']) && $filtros['paciente_id'] > 0) {
      $where[] = "paciente_id = :paciente_id";
      $params['paciente_id'] = (int) $filtros['paciente_id'];
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT COUNT(*) FROM citas $whereSql";
    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }

  public function getByPaciente(int $pacienteId): array
  {
    return $this->db->fetchAll(
      "SELECT c.*, 
        m.nombre as medico_nombre, 
        m.apellidos as medico_apellidos,
        e.nombre as especialidad_nombre
      FROM citas c
      LEFT JOIN medicos m ON c.medico_id = m.id
      LEFT JOIN especialidades e ON c.especialidad_id = e.id
      WHERE c.paciente_id = ?
      ORDER BY c.fecha DESC, c.hora DESC",
      [$pacienteId]
    );
  }

  public function cancel(int $id, int $pacienteId): bool
  {
    $this->db->execute(
      "UPDATE citas SET estado = 'cancelada' WHERE id = ? AND paciente_id = ?",
      [$id, $pacienteId]
    );
    return true;
  }
}

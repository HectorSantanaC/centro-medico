<?php

require_once __DIR__ . '/../config/Database.php';

class Medico
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
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

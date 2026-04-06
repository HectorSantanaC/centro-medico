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

  public function count(): int
  {
    return (int) $this->db->fetchAll(
      "SELECT COUNT(*) as total FROM especialidades WHERE activo = true"
    )[0]['total'];
  }
}

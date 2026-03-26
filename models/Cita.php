<?php

require_once __DIR__ . '/../config/Database.php';

class Cita {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    public function all(): array {
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

    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM citas WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function update(int $id, array $data): bool {
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

    public function delete(int $id): bool {
        $this->db->execute("DELETE FROM citas WHERE id = :id", ['id' => $id]);
        return true;
    }

    public function create(array $data): int {
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

    public function count(): int {
        return (int) $this->db->fetchAll("SELECT COUNT(*) as total FROM citas")[0]['total'];
    }
}

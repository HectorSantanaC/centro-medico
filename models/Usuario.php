<?php

require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    public function all(): array {
        return $this->db->fetchAll("SELECT * FROM usuarios WHERE rol = 'paciente' ORDER BY nombre");
    }

    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT id, nombre, apellidos, email, password, rol FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getStats(): array {
        $stats = [];

        $stats['patients'] = (int) $this->db->fetchAll(
            "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'paciente'"
        )[0]['total'];

        $stats['citas'] = (int) $this->db->fetchAll(
            "SELECT COUNT(*) as total FROM citas"
        )[0]['total'];

        return $stats;
    }
}

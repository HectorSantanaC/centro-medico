<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Especialidad.php';
require_once __DIR__ . '/../models/Medico.php';

class AdminController {
    private Usuario $usuarioModel;
    private Especialidad $especialidadModel;
    private Medico $medicoModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->especialidadModel = new Especialidad();
        $this->medicoModel = new Medico();
    }

    public function handleRequest(): array {
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'gestor'])) {
            header('Location: login.php');
            exit;
        }

        $isAdmin = $_SESSION['usuario_rol'] === 'admin';
        $stats = $this->getStats($isAdmin);

        return [
            'stats' => $stats,
            'isAdmin' => $isAdmin,
            'active' => 'inicio'
        ];
    }

    private function getStats(bool $isAdmin): array {
        $stats = [];

        if ($isAdmin) {
            $stats += $this->usuarioModel->getStats();
        }

        $stats['especialidades'] = $this->especialidadModel->count();
        $stats['medicos'] = $this->medicoModel->count();

        return $stats;
    }
}

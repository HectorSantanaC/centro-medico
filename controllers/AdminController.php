<?php

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Especialidad.php';
require_once __DIR__ . '/../models/Medico.php';

class AdminController extends BaseController
{
  private Usuario $usuarioModel;
  private Especialidad $especialidadModel;
  private Medico $medicoModel;

  public function __construct()
  {
    parent::__construct();
    $this->usuarioModel = new Usuario();
    $this->especialidadModel = new Especialidad();
    $this->medicoModel = new Medico();
  }

  public function handleRequest(): array
  {
    $this->requireRole(['admin', 'gestor']);

    $isAdmin = $this->isAdmin();
    $stats = $this->getStats($isAdmin);

    return [
      'stats' => $stats,
      'isAdmin' => $isAdmin,
      'active' => 'inicio'
    ];
  }

  private function getStats(bool $isAdmin): array
  {
    $stats = [];

    if ($isAdmin) {
      $stats += $this->usuarioModel->getStats();
    }

    $stats['especialidades'] = $this->especialidadModel->count();
    $stats['medicos'] = $this->medicoModel->count();

    return $stats;
  }
}
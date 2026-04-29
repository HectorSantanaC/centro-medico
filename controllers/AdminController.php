<?php

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Especialidad.php';
require_once __DIR__ . '/../models/Medico.php';

class AdminController extends BaseController
{
  public function __construct(
    private Usuario $usuarioModel = new Usuario(),
    private Especialidad $especialidadModel = new Especialidad(),
    private Medico $medicoModel = new Medico()
  ) {
    parent::__construct();
  }

  public function handleRequest(): array
  {
    $this->requireRole(['admin', 'gestor', 'administracion']);

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
<?php

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Especialidad.php';

class CitaOnlineController extends BaseController
{
  private Cita $citaModel;
  private Especialidad $especialidadModel;

  public function __construct()
  {
    parent::__construct();
    $this->citaModel = new Cita();
    $this->especialidadModel = new Especialidad();
  }

  public function handleRequest(): array
  {
    $this->requireAuth();

    $page_title = 'Reservar Cita';
    $mensaje_exito = '';

    if ($_POST) {
      $this->requireCsrfToken();
      
      $data = $this->getPostData(
        [
          'medico_id' => 'int',
          'especialidad_id' => 'int',
          'fecha' => 'string',
          'hora' => 'string'
        ],
        [
          'fecha' => 'fecha_cita',
          'hora' => 'hora_cita'
        ]
      );
      $data['paciente_id'] = $this->getCurrentUserId();
      $this->citaModel->create($data);
      
      $mensaje_exito = "Cita RESERVADA!<br>📅  " . date('d/m/Y', strtotime($data['fecha'])) . " " . $data['hora'];
    }

    $especialidades = $this->especialidadModel->allActives();

    return [
      'page_title' => $page_title,
      'especialidades' => $especialidades,
      'mensaje_exito' => $mensaje_exito,
      'user_role' => $this->getCurrentUserRole()
    ];
  }
}
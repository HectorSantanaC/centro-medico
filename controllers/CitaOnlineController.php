<?php

require_once __DIR__ . '/../helpers/sanitize.php';

require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Especialidad.php';

class CitaOnlineController
{
  private Cita $citaModel;
  private Especialidad $especialidadModel;

  public function __construct()
  {
    $this->citaModel = new Cita();
    $this->especialidadModel = new Especialidad();
  }

  public function handleRequest(): array
  {
    if (!isset($_SESSION['usuario_id'])) {
      header('Location: login.php');
      exit;
    }

    $page_title = 'Reservar Cita';
    $mensaje_exito = '';

    if ($_POST) {
      $data = sanitizePostData([
        'medico_id' => 'int',
        'especialidad_id' => 'int',
        'fecha_cita' => 'string',
        'hora_cita' => 'string'
      ]);
      $data['paciente_id'] = $_SESSION['usuario_id'];
      $cita_id = $this->citaModel->create($data);
      
      $mensaje_exito = "Cita RESERVADA!<br>📅  " . date('d/m/Y', strtotime($data['fecha_cita'])) . " " . $data['hora_cita'];
    }

    $especialidades = $this->especialidadModel->allActives();

    return [
      'page_title' => $page_title,
      'especialidades' => $especialidades,
      'mensaje_exito' => $mensaje_exito,
      'user_role' => $_SESSION['usuario_rol'] ?? 'paciente'
    ];
  }
}

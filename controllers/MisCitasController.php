<?php

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../models/Cita.php';

class MisCitasController extends BaseController
{
  private Cita $citaModel;

  public function __construct()
  {
    parent::__construct();
    $this->citaModel = new Cita();
  }

  public function handleRequest(): array
  {
    $this->requireAuth();

    if ($this->isAdmin()) {
      $this->redirect('citas-crud.php');
    }

    $page_title = 'Mis Citas';
    $usuario_id = $this->getCurrentUserId();
    $message = '';
    $messageType = '';

    if (isset($_GET['cancelar']) && $_GET['cancelar']) {
      $cita_id = (int)$_GET['cancelar'];
      $this->citaModel->cancel($cita_id, $usuario_id);
      $message = 'Cita cancelada correctamente';
      $messageType = 'success';
    }

    $citas = $this->citaModel->getByPaciente($usuario_id);
    $estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];

    return [
      'page_title' => $page_title,
      'citas' => $citas,
      'estados' => $estados,
      'message' => $message,
      'messageType' => $messageType
    ];
  }
}
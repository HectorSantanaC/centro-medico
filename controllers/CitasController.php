<?php

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Especialidad.php';
require_once __DIR__ . '/../models/Medico.php';

class CitasController extends BaseController
{
  private Cita $citaModel;
  private Usuario $usuarioModel;
  private Especialidad $especialidadModel;
  private Medico $medicoModel;

  public function __construct()
  {
    parent::__construct();
    $this->citaModel = new Cita();
    $this->usuarioModel = new Usuario();
    $this->especialidadModel = new Especialidad();
    $this->medicoModel = new Medico();
  }

  public function handleRequest(): array
  {
    $this->requireRole(['admin', 'gestor']);

    $action = $_REQUEST['action'] ?? 'list';
    $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : null;
    $message = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->requireCsrfToken();
      
      $data = $this->getSanitizedInput([
        'paciente_id' => 'int',
        'medico_id' => 'int',
        'especialidad_id' => 'int',
        'fecha' => 'string',
        'hora' => 'string',
        'estado' => 'default',
        'notas' => 'text'
      ]);
      
      if ($action === 'edit' && $id) {
        try {
          $this->citaModel->update($id, $data);
          $message = 'Cita actualizada exitosamente';
          $messageType = 'success';
          $action = 'list';
        } catch (Exception $e) {
          $message = 'Error al actualizar la cita: ' . $e->getMessage();
          $messageType = 'error';
        }
      }
    }

    if ($action === 'delete' && $id) {
      try {
        $this->citaModel->delete($id);
        $message = 'Cita eliminada exitosamente';
        $messageType = 'success';
        $action = 'list';
      } catch (Exception $e) {
        $message = 'Error al eliminar la cita: ' . $e->getMessage();
        $messageType = 'error';
      }
    }

    $citas = [];
    $citaEdit = null;
    $pacienteInfo = null;

    if ($action === 'list') {
      $citas = $this->citaModel->all();
    } elseif ($action === 'edit' && $id) {
      $citaEdit = $this->citaModel->find($id);
      if ($citaEdit && isset($citaEdit['paciente_id'])) {
        $paciente = $this->usuarioModel->find($citaEdit['paciente_id']);
        if ($paciente) {
          $pacienteInfo = [
            'nombre' => $paciente['nombre'],
            'apellidos' => $paciente['apellidos']
          ];
        }
      }
    }

    return [
      'action' => $action,
      'id' => $id,
      'message' => $message,
      'messageType' => $messageType,
      'citas' => $citas,
      'citaEdit' => $citaEdit,
      'pacienteInfo' => $pacienteInfo,
      'especialidades' => $this->especialidadModel->allActives(),
      'medicos' => $this->medicoModel->allActives(),
      'pacientes' => $this->usuarioModel->all(),
      'estados' => ['pendiente', 'confirmada', 'completada', 'cancelada'],
      'active' => 'citas'
    ];
  }
}
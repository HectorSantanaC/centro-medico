<?php

require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Especialidad.php';
require_once __DIR__ . '/../models/Medico.php';

class CitasController
{
  private Cita $citaModel;
  private Usuario $usuarioModel;
  private Especialidad $especialidadModel;
  private Medico $medicoModel;

  public function __construct()
  {
    $this->citaModel = new Cita();
    $this->usuarioModel = new Usuario();
    $this->especialidadModel = new Especialidad();
    $this->medicoModel = new Medico();
  }

  public function handleRequest(): array
  {
    if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'gestor'])) {
      header('Location: login.php');
      exit;
    }

    $action = $_REQUEST['action'] ?? 'list';
    $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : null;
    $message = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = $this->sanitizePostData();
      
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

  private function sanitizePostData(): array
  {
    return [
      'paciente_id' => (int) ($_POST['paciente_id'] ?? 0),
      'medico_id' => (int) ($_POST['medico_id'] ?? 0),
      'especialidad_id' => (int) ($_POST['especialidad_id'] ?? 0),
      'fecha' => $_POST['fecha'] ?? '',
      'hora' => $_POST['hora'] ?? '',
      'estado' => $_POST['estado'] ?? 'pendiente',
      'notas' => trim($_POST['notas'] ?? '')
    ];
  }
}

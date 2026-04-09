<?php

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Especialidad.php';

class MedicosController extends BaseController
{
  private Medico $medicoModel;
  private Especialidad $especialidadModel;

  public function __construct()
  {
    parent::__construct();
    $this->medicoModel = new Medico();
    $this->especialidadModel = new Especialidad();
  }

  public function handleRequest(): array
  {
    $this->requireRole(['admin', 'gestor']);

    $action = $_REQUEST['action'] ?? 'list';
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
    $message = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->requireCsrfToken();
      
      $data = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellidos' => trim($_POST['apellidos'] ?? ''),
        'especialidad_id' => !empty($_POST['especialidad_id']) ? (int)$_POST['especialidad_id'] : null,
        'activo' => isset($_POST['activo']) && $_POST['activo'] === '1'
      ];

      try {
        if (empty($data['nombre'])) {
          throw new Exception('El nombre del médico es obligatorio');
        }
        if (empty($data['apellidos'])) {
          throw new Exception('Los apellidos del médico son obligatorios');
        }

        if ($action === 'create') {
          $this->medicoModel->create($data);
          $message = 'Médico creado exitosamente';
          $messageType = 'success';
          $action = 'list';
        } elseif ($action === 'edit' && $id) {
          $this->medicoModel->update($id, $data);
          $message = 'Médico actualizado exitosamente';
          $messageType = 'success';
          $action = 'list';
        }
      } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
      }
    }

    if ($action === 'delete' && $id) {
      try {
        $this->medicoModel->delete($id);
        $message = 'Médico eliminado exitosamente';
        $messageType = 'success';
        $action = 'list';
      } catch (Exception $e) {
        $message = 'Error al eliminar médico: ' . $e->getMessage();
        $messageType = 'error';
      }
    }

    $medicos = [];
    $medicoEdit = null;
    $especialidades = [];
    $page = 1;
    $totalPages = 1;
    $totalItems = 0;

    if ($action === 'list') {
      $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
      $perPage = 10;
      $medicos = $this->medicoModel->allPaginated($page, $perPage);
      $totalItems = $this->medicoModel->countAll();
      $totalPages = $totalItems > 0 ? ceil($totalItems / $perPage) : 1;
      $especialidades = $this->especialidadModel->allActives();
    } elseif ($action === 'edit' && $id) {
      $medicoEdit = $this->medicoModel->find($id);
      $especialidades = $this->especialidadModel->allActives();
    } elseif ($action === 'create') {
      $especialidades = $this->especialidadModel->allActives();
    }

    return [
      'action' => $action,
      'id' => $id,
      'medicos' => $medicos,
      'medicoEdit' => $medicoEdit,
      'especialidades' => $especialidades,
      'page' => $page,
      'totalPages' => $totalPages,
      'totalItems' => $totalItems,
      'message' => $message,
      'messageType' => $messageType,
      'active' => 'medicos'
    ];
  }
}

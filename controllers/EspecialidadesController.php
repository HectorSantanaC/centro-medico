<?php

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../models/Especialidad.php';

class EspecialidadesController extends BaseController
{
  private Especialidad $especialidadModel;

  public function __construct()
  {
    parent::__construct();
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
        'activo' => isset($_POST['activo']) && $_POST['activo'] === '1'
      ];

      try {
        if (empty($data['nombre'])) {
          throw new Exception('El nombre de la especialidad es obligatorio');
        }

        if ($action === 'create') {
          $this->especialidadModel->create($data);
          $message = 'Especialidad creada exitosamente';
          $messageType = 'success';
          $action = 'list';
        } elseif ($action === 'edit' && $id) {
          $this->especialidadModel->update($id, $data);
          $message = 'Especialidad actualizada exitosamente';
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
        $this->especialidadModel->delete($id);
        $message = 'Especialidad eliminada exitosamente';
        $messageType = 'success';
        $action = 'list';
      } catch (Exception $e) {
        $message = 'Error al eliminar especialidad: ' . $e->getMessage();
        $messageType = 'error';
      }
    }

    $especialidades = [];
    $especialidadEdit = null;
    $page = 1;
    $totalPages = 1;
    $totalItems = 0;

    if ($action === 'list') {
      $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
      $perPage = 10;
      $especialidades = $this->especialidadModel->allPaginated($page, $perPage);
      $totalItems = $this->especialidadModel->countAll();
      $totalPages = $totalItems > 0 ? ceil($totalItems / $perPage) : 1;
    } elseif ($action === 'edit' && $id) {
      $especialidadEdit = $this->especialidadModel->find($id);
    }

    return [
      'action' => $action,
      'id' => $id,
      'especialidades' => $especialidades,
      'especialidadEdit' => $especialidadEdit,
      'page' => $page,
      'totalPages' => $totalPages,
      'totalItems' => $totalItems,
      'message' => $message,
      'messageType' => $messageType,
      'active' => 'especialidades'
    ];
  }
}

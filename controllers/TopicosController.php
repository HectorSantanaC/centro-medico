<?php

require_once __DIR__ . '/../models/Topico.php';

class TopicosController
{
  private Topico $topicoModel;

  public function __construct()
  {
    $this->topicoModel = new Topico();
  }

  public function handleRequest(): array
  {
    if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'gestor'])) {
      header('Location: login.php');
      exit;
    }

    $action = $_REQUEST['action'] ?? 'list';
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
    $message = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'name' => trim($_POST['nombre'] ?? '')
      ];

      try {
        if (empty($data['name'])) {
          throw new Exception('El nombre del tópico es obligatorio');
        }

        if ($action === 'create') {
          $this->topicoModel->create($data);
          $message = 'Tópico creado exitosamente';
          $messageType = 'success';
          $action = 'list';
        } elseif ($action === 'edit' && $id) {
          $this->topicoModel->update($id, ['nombre' => $data['name']]);
          $message = 'Tópico actualizado exitosamente';
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
        $this->topicoModel->delete($id);
        $message = 'Tópico eliminado exitosamente';
        $messageType = 'success';
        $action = 'list';
      } catch (Exception $e) {
        $message = 'Error al eliminar tópico: ' . $e->getMessage();
        $messageType = 'error';
      }
    }

    $topicos = [];
    $topicoEdit = null;

    if ($action === 'list') {
      $topicos = $this->topicoModel->all();
    } elseif ($action === 'edit' && $id) {
      $topicoEdit = $this->topicoModel->find($id);
    }

    return [
      'action' => $action,
      'id' => $id,
      'topicos' => $topicos,
      'topicoEdit' => $topicoEdit,
      'message' => $message,
      'messageType' => $messageType,
      'active' => 'topicos'
    ];
  }
}

<?php

require_once __DIR__ . '/../models/Articulo.php';

class ArticulosController
{
  private Articulo $articuloModel;

  public function __construct()
  {
    $this->articuloModel = new Articulo();
  }

  public function handleRequest(): array
  {
    $action = $_REQUEST['action'] ?? 'list';
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
    $message = '';
    $messageType = '';

    $canManage = isset($_SESSION['usuario_id']) &&
      in_array($_SESSION['usuario_rol'], ['admin', 'gestor']);

    if (!$canManage && $action !== 'list' && $action !== 'view') {
      header('Location: login.php');
      exit;
    }

    if ($canManage && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'contenido' => $_POST['contenido'] ?? '',
        'resumen' => trim($_POST['resumen'] ?? ''),
        'imagen' => trim($_POST['imagen'] ?? ''),
        'autor' => trim($_POST['autor'] ?? ''),
        'categoria' => trim($_POST['categoria'] ?? ''),
        'publicado' => isset($_POST['publicado'])
      ];

      try {
        if (empty($data['titulo']) || empty($data['contenido'])) {
          throw new Exception('El título y contenido son obligatorios');
        }

        if ($action === 'create') {
          $this->articuloModel->create($data);
          $message = 'Artículo creado exitosamente';
          $messageType = 'success';
          $action = 'list';
        } elseif ($action === 'edit' && $id) {
          $this->articuloModel->update($id, $data);
          $message = 'Artículo actualizado exitosamente';
          $messageType = 'success';
          $action = 'list';
        }
      } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
      }
    }

    if ($action === 'delete' && $id && $canManage) {
      try {
        $this->articuloModel->delete($id);
        $message = 'Artículo eliminado exitosamente';
        $messageType = 'success';
        $action = 'list';
      } catch (Exception $e) {
        $message = 'Error al eliminar: ' . $e->getMessage();
        $messageType = 'error';
      }
    }

    $articulos = [];
    $articuloEdit = null;
    $categorias = ['Salud', 'Consejos', 'Noticias', 'Bienestar', 'Medicina'];

    if ($action === 'list') {
      $articulos = $canManage ?
        $this->articuloModel->allAdmin() :
        $this->articuloModel->all();
    } elseif ($action === 'view' && $id) {
      $articuloEdit = $this->articuloModel->find($id);
    } elseif ($action === 'edit' && $id && $canManage) {
      $articuloEdit = $this->articuloModel->find($id);
    }

    return [
      'action' => $action,
      'id' => $id,
      'articulos' => $articulos,
      'articulo' => $articuloEdit,
      'categorias' => $categorias,
      'message' => $message,
      'messageType' => $messageType,
      'canManage' => $canManage
    ];
  }
}

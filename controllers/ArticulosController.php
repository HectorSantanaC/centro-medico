<?php

require_once __DIR__ . '/../models/Articulo.php';
require_once __DIR__ . '/../models/Topico.php';

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
      $imagen = '';
      if (!empty($_FILES['imagen_file']['name'])) {
        $imagen = $this->uploadImage($_FILES['imagen_file']);
        if (!$imagen) {
          $message = 'Error al subir la imagen';
          $messageType = 'error';
        }
      }

      $data = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'topico' => !empty($_POST['topico']) ? (int)$_POST['topico'] : null,
        'contenido_completo' => $_POST['contenido_completo'] ?? '',
        'contenido_reducido' => trim($_POST['contenido_reducido'] ?? ''),
        'fecha_contenido' => !empty($_POST['fecha_contenido']) ? $_POST['fecha_contenido'] : null,
        'fecha_caducidad' => !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null,
        'orden' => (int)($_POST['orden'] ?? 0),
        'notas' => trim($_POST['notas'] ?? ''),
        'imagen' => $imagen ?: trim($_POST['imagen'] ?? ''),
        'imagen_url' => trim($_POST['imagen_url'] ?? ''),
        'autor' => trim($_POST['autor'] ?? ''),
        'publicado' => isset($_POST['publicado']),
        'seo_titulo' => trim($_POST['seo_titulo'] ?? ''),
        'seo_descripcion' => trim($_POST['seo_descripcion'] ?? ''),
        'seo_palabras_clave' => trim($_POST['seo_palabras_clave'] ?? '')
      ];

      try {
        if (empty($data['titulo'])) {
          throw new Exception('El título es obligatorio');
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
    $topicos = (new Topico())->all();

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
      'topicos' => $topicos,
      'message' => $message,
      'messageType' => $messageType,
      'canManage' => $canManage
    ];
  }

  private function uploadImage(array $file): ?string
  {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;

    if (!in_array($file['type'], $allowedTypes)) {
      return null;
    }

    if ($file['size'] > $maxSize) {
      return null;
    }

    $uploadDir = __DIR__ . '/../assets/img/articulos/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('articulo_') . '.' . $extension;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
      return 'assets/img/articulos/' . $filename;
    }

    return null;
  }
}

<?php

require_once __DIR__ . '/../models/Articulo.php';
require_once __DIR__ . '/../models/Topico.php';

$articuloModel = new Articulo();
$topicoModel = new Topico();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);

function procesarImagenUpload(?string &$imagen): ?string
{
  if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    return null;
  }

  $file = $_FILES['imagen'];
  
  if ($file['size'] > MAX_IMAGE_SIZE) {
    return 'La imagen excede el tamaño máximo de 5MB';
  }

  $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mimeReal = $finfo->file($file['tmp_name']);
  
  if (!in_array($mimeReal, $allowedMimes)) {
    return 'Tipo de archivo no permitido';
  }

  $uploadDir = __DIR__ . '/../assets/img/articulos/';
  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
      return 'No se pudo crear el directorio de uploads';
    }
  }

  $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
  $filename = uniqid('articulo_') . '.' . $extension;
  $ruta = $uploadDir . $filename;

  if (!move_uploaded_file($file['tmp_name'], $ruta)) {
    return 'No se pudo guardar la imagen';
  }

  $imagen = 'assets/img/articulos/' . $filename;
  return null;
}

function procesarImagenBase64(?string &$imagen): ?string
{
  if (empty($imagen) || strpos($imagen, 'data:image/') !== 0) {
    return null;
  }

  $matches = [];
  if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $imagen, $matches)) {
    return 'Formato de imagen inválido';
  }

  $extension = $matches[1];
  $datosBase64 = $matches[2];
  $contenido = base64_decode($datosBase64);

  if ($contenido === false || strlen($contenido) < 100) {
    return 'No se pudo decodificar la imagen';
  }

  if (strlen($contenido) > MAX_IMAGE_SIZE) {
    return 'La imagen excede el tamaño máximo de 5MB';
  }

  $allowedExt = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
  if (!in_array($extension, $allowedExt)) {
    return 'Extensión de imagen no permitida';
  }

  $uploadDir = __DIR__ . '/../assets/img/articulos/';
  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
      return 'No se pudo crear el directorio de uploads';
    }
  }

  $filename = uniqid('articulo_') . '.' . $extension;
  $ruta = $uploadDir . $filename;

  if (file_put_contents($ruta, $contenido) === false) {
    return 'No se pudo guardar la imagen';
  }

  $imagen = 'assets/img/articulos/' . $filename;
  return null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
  http_response_code(200);
  exit;
}

try {
  switch ($method) {
    case 'GET':
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $perPage = isset($_GET['per_page']) ? min((int)$_GET['per_page'], 100) : 10;

      if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $articulo = $articuloModel->find($id);

        if (!$articulo) {
          http_response_code(404);
          echo json_encode(['error' => 'Artículo no encontrado']);
          exit;
        }

        http_response_code(200);
        echo json_encode($articulo);
        exit;
      }

      if (isset($_GET['topico'])) {
        $articulos = $articuloModel->getByTopico((int)$_GET['topico']);
        http_response_code(200);
        echo json_encode($articulos);
        exit;
      }

      $filtros = [
        'titulo' => $_GET['titulo'] ?? null,
        'topico' => !empty($_GET['topico_id']) ? (int)$_GET['topico_id'] : null,
        'fecha_desde' => $_GET['fecha_desde'] ?? null,
        'fecha_hasta' => $_GET['fecha_hasta'] ?? null
      ];

      $data = $articuloModel->allPaginated($page, $perPage, $filtros);
      $total = $articuloModel->countAll($filtros);

      http_response_code(200);
      echo json_encode([
        'data' => $data,
        'pagination' => [
          'page' => $page,
          'perPage' => $perPage,
          'total' => $total,
          'totalPages' => $total > 0 ? (int)ceil($total / $perPage) : 0
        ]
      ]);
      break;

    case 'POST':
      $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
      $isJson = strpos($contentType, 'application/json') !== false;
      
      if ($isJson) {
        $data = json_decode(file_get_contents('php://input'), true);
      } else {
        $data = $_POST;
      }
      
      if (!$data || empty($data['titulo'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El título es obligatorio']);
        exit;
      }

      if ($articuloModel->existsByTitulo(trim($data['titulo']))) {
        http_response_code(409);
        echo json_encode(['error' => 'Ya existe un artículo con ese título']);
        exit;
      }

      if (!empty($data['topico']) && !$topicoModel->find((int)$data['topico'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El tópico especificado no existe']);
        exit;
      }

      if (!empty($data['fecha_contenido']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_contenido'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de fecha_contenido inválido (YYYY-MM-DD)']);
        exit;
      }

      if (!empty($data['fecha_caducidad']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_caducidad'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de fecha_caducidad inválido (YYYY-MM-DD)']);
        exit;
      }

      if (!empty($data['fecha_contenido']) && !empty($data['fecha_caducidad']) && $data['fecha_caducidad'] < $data['fecha_contenido']) {
        http_response_code(400);
        echo json_encode(['error' => 'La fecha de caducidad debe ser posterior a la fecha de contenido']);
        exit;
      }

      $data['titulo'] = htmlspecialchars(trim($data['titulo']), ENT_QUOTES, 'UTF-8');

      $imagen = '';
      if ($isJson) {
        $errorImagen = procesarImagenBase64($imagen);
      } else {
        $errorImagen = procesarImagenUpload($imagen);
      }
      if ($errorImagen) {
        http_response_code(400);
        echo json_encode(['error' => $errorImagen]);
        exit;
      }
      if ($imagen) {
        $data['imagen'] = $imagen;
      }

      $id = $articuloModel->create($data);
      http_response_code(201);
      echo json_encode([
        'message' => 'Artículo creado correctamente',
        'id' => $id
      ]);
      break;

    case 'PUT':
      $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
      $isJson = strpos($contentType, 'application/json') !== false;
      
      if ($isJson) {
        $data = json_decode(file_get_contents('php://input'), true);
      } else {
        $data = !empty($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);
      }
      
      if (!$data || empty($data['id']) || empty($data['titulo'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID y título son obligatorios']);
        exit;
      }
      $articulo = $articuloModel->find((int)$data['id']);
      if (!$articulo) {
        http_response_code(404);
        echo json_encode(['error' => 'Artículo no encontrado']);
        exit;
      }

      if ($articuloModel->existsByTitulo(trim($data['titulo']), (int)$data['id'])) {
        http_response_code(409);
        echo json_encode(['error' => 'Ya existe otro artículo con ese título']);
        exit;
      }

      if (!empty($data['topico']) && !$topicoModel->find((int)$data['topico'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El tópico especificado no existe']);
        exit;
      }

      if (!empty($data['fecha_contenido']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_contenido'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de fecha_contenido inválido (YYYY-MM-DD)']);
        exit;
      }

      if (!empty($data['fecha_caducidad']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha_caducidad'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de fecha_caducidad inválido (YYYY-MM-DD)']);
        exit;
      }

      if (!empty($data['fecha_contenido']) && !empty($data['fecha_caducidad']) && $data['fecha_caducidad'] < $data['fecha_contenido']) {
        http_response_code(400);
        echo json_encode(['error' => 'La fecha de caducidad debe ser posterior a la fecha de contenido']);
        exit;
      }

      $data['titulo'] = htmlspecialchars(trim($data['titulo']), ENT_QUOTES, 'UTF-8');

      $imagen = '';
      if ($isJson) {
        $errorImagen = procesarImagenBase64($imagen);
      } else {
        $errorImagen = procesarImagenUpload($imagen);
      }
      if ($errorImagen) {
        http_response_code(400);
        echo json_encode(['error' => $errorImagen]);
        exit;
      }
      if ($imagen) {
        $data['imagen'] = $imagen;
      }

      $articuloModel->update((int)$data['id'], $data);
      http_response_code(200);
      echo json_encode(['message' => 'Artículo actualizado correctamente']);
      break;

    case 'DELETE':
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID obligatorio']);
        exit;
      }
      $articulo = $articuloModel->find((int)$data['id']);
      if (!$articulo) {
        http_response_code(404);
        echo json_encode(['error' => 'Artículo no encontrado']);
        exit;
      }
      $articuloModel->deleteWithImage((int)$data['id']);
      http_response_code(200);
      echo json_encode(['message' => 'Artículo eliminado correctamente']);
      break;

    default:
      http_response_code(405);
      echo json_encode(['error' => 'Método no permitido']);
      break;
  }
} catch (\Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error interno del servidor']);
}
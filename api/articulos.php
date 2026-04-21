<?php

require_once __DIR__ . '/../models/Articulo.php';

$articuloModel = new Articulo();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

function procesarImagenBase64(?string &$imagen): bool
{
  if (empty($imagen) || strpos($imagen, 'data:image/') !== 0) {
    return false;
  }

  $matches = [];
  if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $imagen, $matches)) {
    return false;
  }

  $extension = $matches[1];
  $datosBase64 = $matches[2];
  $contenido = base64_decode($datosBase64);

  if ($contenido === false || strlen($contenido) < 100) {
    return false;
  }

  $allowedExt = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
  if (!in_array($extension, $allowedExt)) {
    return false;
  }

  $uploadDir = __DIR__ . '/../assets/img/articulos/';
  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
      return false;
    }
  }

  $filename = uniqid('articulo_') . '.' . $extension;
  $ruta = $uploadDir . $filename;

  if (file_put_contents($ruta, $contenido) === false) {
    return false;
  }

  $imagen = 'assets/img/articulos/' . $filename;
  return true;
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
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['titulo'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El título es obligatorio']);
        exit;
      }

      procesarImagenBase64($data['imagen']);

      $id = $articuloModel->create($data);
      http_response_code(201);
      echo json_encode([
        'message' => 'Artículo creado correctamente',
        'id' => $id
      ]);
      break;

    case 'PUT':
      $data = json_decode(file_get_contents('php://input'), true);
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

      procesarImagenBase64($data['imagen']);

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
      $articuloModel->delete((int)$data['id']);
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
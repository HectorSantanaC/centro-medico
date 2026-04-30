<?php

session_start();
require_once __DIR__ . '/../helpers/api_auth.php';
require_once __DIR__ . '/../helpers/supabase_storage.php';
require_once __DIR__ . '/../models/Articulo.php';
require_once __DIR__ . '/../models/Topico.php';

$articuloModel = new Articulo();
$topicoModel = new Topico();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
      requireApiAuth(['admin', 'gestor']);
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
      if ($isJson && !empty($data['imagen'])) {
        $imagen = procesarImagen($data['imagen'], 'articulos');
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
      requireApiAuth(['admin', 'gestor']);
      $rawInput = file_get_contents('php://input');
      $data = json_decode($rawInput, true);
      if (!$data) {
        $data = $_POST;
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

      if (!empty($data['imagen']) && strpos($data['imagen'], 'data:image/') === 0) {
        $imagen = procesarImagen($data['imagen'], 'articulos');
        if ($imagen) {
          $data['imagen'] = $imagen;
        }
      }

      $articuloModel->update((int)$data['id'], $data);
      http_response_code(200);
      echo json_encode(['message' => 'Artículo actualizado correctamente']);
      break;

    case 'DELETE':
      requireApiAuth(['admin', 'gestor']);
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
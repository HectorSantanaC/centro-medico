<?php

require_once __DIR__ . '/../models/Articulo.php';

header('Content-Type: application/json; charset=utf-8');

$articuloModel = new Articulo();
$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
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

      if (isset($_GET['admin']) && $_GET['admin'] === '1') {
        $articulos = $articuloModel->allAdmin();
      } else {
        $articulos = $articuloModel->all();
      }
      http_response_code(200);
      echo json_encode($articulos);
      break;

    case 'POST':
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['titulo'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El título es obligatorio']);
        exit;
      }

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
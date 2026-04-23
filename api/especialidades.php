<?php

session_start();
require_once __DIR__ . '/../helpers/api_auth.php';
require_once __DIR__ . '/../models/Especialidad.php';

header('Content-Type: application/json; charset=utf-8');

$especialidadModel = new Especialidad();
$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $perPage = isset($_GET['per_page']) ? min((int)$_GET['per_page'], 100) : 10;

      if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $especialidad = $especialidadModel->find($id);

        if (!$especialidad) {
          http_response_code(404);
          echo json_encode(['error' => 'Especialidad no encontrada']);
          exit;
        }

        http_response_code(200);
        echo json_encode($especialidad);
        exit;
      }

      $filtros = [
        'nombre' => $_GET['nombre'] ?? null
      ];

      $data = $especialidadModel->allPaginated($page, $perPage, $filtros);
      $total = $especialidadModel->countAll($filtros);

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
      requireApiAuth(['admin']);
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['nombre'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre es obligatorio']);
        exit;
      }

      $id = $especialidadModel->create($data);
      http_response_code(201);
      echo json_encode([
        'message' => 'Especialidad creada correctamente',
        'id' => $id
      ]);
      break;

    case 'PUT':
      requireApiAuth(['admin']);
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['id']) || empty($data['nombre'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID y nombre son obligatorios']);
        exit;
      }
      $especialidad = $especialidadModel->find((int)$data['id']);
      if (!$especialidad) {
        http_response_code(404);
        echo json_encode(['error' => 'Especialidad no encontrada']);
        exit;
      }
      $especialidadModel->update((int)$data['id'], $data);
      http_response_code(200);
      echo json_encode(['message' => 'Especialidad actualizada correctamente']);
      break;

    case 'DELETE':
      requireApiAuth(['admin']);
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID obligatorio']);
        exit;
      }
      $especialidad = $especialidadModel->find((int)$data['id']);
      if (!$especialidad) {
        http_response_code(404);
        echo json_encode(['error' => 'Especialidad no encontrada']);
        exit;
      }
      $especialidadModel->delete((int)$data['id']);
      http_response_code(200);
      echo json_encode(['message' => 'Especialidad eliminada correctamente']);
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

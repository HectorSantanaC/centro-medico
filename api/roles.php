<?php

session_start();
require_once __DIR__ . '/../helpers/api_auth.php';
require_once __DIR__ . '/../models/Rol.php';

header('Content-Type: application/json; charset=utf-8');

$rolModel = new Rol();
$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $perPage = isset($_GET['per_page']) ? min((int)$_GET['per_page'], 100) : 10;

      if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $rol = $rolModel->find($id);

        if (!$rol) {
          http_response_code(404);
          echo json_encode(['error' => 'Rol no encontrado']);
          exit;
        }

        http_response_code(200);
        echo json_encode($rol);
        exit;
      }

      if (isset($_GET['activos']) && $_GET['activos'] === 'true') {
        $data = $rolModel->allActives();
        http_response_code(200);
        echo json_encode(['data' => $data]);
        exit;
      }

      $filtros = [
        'nombre' => $_GET['nombre'] ?? null
      ];

      $data = $rolModel->all();
      $total = count($data);

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

      $id = $rolModel->create($data);
      http_response_code(201);
      echo json_encode([
        'message' => 'Rol creado correctamente',
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
      $rol = $rolModel->find((int)$data['id']);
      if (!$rol) {
        http_response_code(404);
        echo json_encode(['error' => 'Rol no encontrado']);
        exit;
      }
      $rolModel->update((int)$data['id'], $data);
      http_response_code(200);
      echo json_encode(['message' => 'Rol actualizado correctamente']);
      break;

    case 'DELETE':
      requireApiAuth(['admin']);
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID obligatorio']);
        exit;
      }
      $rol = $rolModel->find((int)$data['id']);
      if (!$rol) {
        http_response_code(404);
        echo json_encode(['error' => 'Rol no encontrado']);
        exit;
      }
      $rolModel->delete((int)$data['id']);
      http_response_code(200);
      echo json_encode(['message' => 'Rol eliminado correctamente']);
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
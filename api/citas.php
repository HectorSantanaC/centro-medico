<?php

session_start();
require_once __DIR__ . '/../helpers/api_auth.php';
require_once __DIR__ . '/../models/Cita.php';

header('Content-Type: application/json; charset=utf-8');

$citaModel = new Cita();
$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $perPage = isset($_GET['per_page']) ? min((int)$_GET['per_page'], 100) : 10;

      if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $cita = $citaModel->find($id);

        if (!$cita) {
          http_response_code(404);
          echo json_encode(['error' => 'Cita no encontrada']);
          exit;
        }

        http_response_code(200);
        echo json_encode($cita);
        exit;
      }

      if (isset($_GET['paciente_id'])) {
        $citas = $citaModel->getByPaciente((int)$_GET['paciente_id']);
        http_response_code(200);
        echo json_encode($citas);
        exit;
      }

      $filtros = [
        'fecha_desde' => $_GET['fecha_desde'] ?? null,
        'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
        'estado' => $_GET['estado'] ?? null,
        'medico_id' => !empty($_GET['medico_id']) ? (int)$_GET['medico_id'] : null,
        'especialidad_id' => !empty($_GET['especialidad_id']) ? (int)$_GET['especialidad_id'] : null,
        'paciente_id' => !empty($_GET['paciente_id']) ? (int)$_GET['paciente_id'] : null
      ];

      $data = $citaModel->allPaginated($page, $perPage, $filtros);
      $total = $citaModel->countAll($filtros);

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
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['paciente_id']) || empty($data['medico_id']) 
          || empty($data['especialidad_id']) || empty($data['fecha']) || empty($data['hora'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        exit;
      }

      if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de fecha inválido (YYYY-MM-DD)']);
        exit;
      }

      if (!preg_match('/^\d{2}:\d{2}$/', $data['hora'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de hora inválido (HH:MM)']);
        exit;
      }

      $id = $citaModel->create($data);
      http_response_code(201);
      echo json_encode([
        'message' => 'Cita creada correctamente',
        'id' => $id
      ]);
      break;

    case 'PUT':
      requireApiAuth(['admin', 'gestor']);
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['id']) || empty($data['paciente_id']) || empty($data['medico_id']) 
          || empty($data['especialidad_id']) || empty($data['fecha']) || empty($data['hora'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        exit;
      }
      $cita = $citaModel->find((int)$data['id']);
      if (!$cita) {
        http_response_code(404);
        echo json_encode(['error' => 'Cita no encontrada']);
        exit;
      }
      $citaModel->update((int)$data['id'], $data);
      http_response_code(200);
      echo json_encode(['message' => 'Cita actualizada correctamente']);
      break;

    case 'DELETE':
      requireApiAuth(['admin', 'gestor']);
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID obligatorio']);
        exit;
      }
      $cita = $citaModel->find((int)$data['id']);
      if (!$cita) {
        http_response_code(404);
        echo json_encode(['error' => 'Cita no encontrada']);
        exit;
      }
      $citaModel->delete((int)$data['id']);
      http_response_code(200);
      echo json_encode(['message' => 'Cita eliminada correctamente']);
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
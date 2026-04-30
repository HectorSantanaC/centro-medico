<?php

session_start();
require_once __DIR__ . '/../helpers/api_auth.php';
require_once __DIR__ . '/../helpers/supabase_storage.php';
require_once __DIR__ . '/../models/Medico.php';

header('Content-Type: application/json; charset=utf-8');

$medicoModel = new Medico();
$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $perPage = isset($_GET['per_page']) ? min((int)$_GET['per_page'], 100) : 10;

      if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $medico = $medicoModel->find($id);

        if (!$medico) {
          http_response_code(404);
          echo json_encode(['error' => 'Médico no encontrado']);
          exit;
        }

        http_response_code(200);
        echo json_encode($medico);
        exit;
      }

      if (isset($_GET['especialidad_id'])) {
        $espId = (int) $_GET['especialidad_id'];
        $medicos = $medicoModel->getByEspecialidad($espId);
        http_response_code(200);
        echo json_encode($medicos);
        exit;
      }

      $filtros = [
        'nombre' => $_GET['nombre'] ?? null,
        'especialidad_id' => !empty($_GET['especialidad_id']) ? (int)$_GET['especialidad_id'] : null
      ];

      $data = $medicoModel->allPaginated($page, $perPage, $filtros);
      $total = $medicoModel->countAll($filtros);

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
      if (!$data || empty($data['nombre']) || empty($data['apellidos'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre y apellidos son obligatorios']);
        exit;
      }

      $imagen = '';
      $imagen_url = trim($data['imagen_url'] ?? '');

      if (!empty($data['imagen'])) {
        if (strpos($data['imagen'], 'data:image/') === 0) {
          $imagen = procesarImagen($data['imagen'], 'medicos');
          if (!$imagen) {
            $imagen = '';
          }
        } else {
          $imagen = $data['imagen'];
        }
      }

      if ($imagen) {
        $data['imagen'] = $imagen;
      }
      if ($imagen_url) {
        $data['imagen_url'] = $imagen_url;
      }

      $id = $medicoModel->create($data);
      http_response_code(201);
      echo json_encode([
        'message' => 'Médico creado correctamente',
        'id' => $id
      ]);
      break;

    case 'PUT':
      requireApiAuth(['admin']);
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['id']) || empty($data['nombre']) || empty($data['apellidos'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID, nombre y apellidos son obligatorios']);
        exit;
      }
      $medico = $medicoModel->find((int)$data['id']);
      if (!$medico) {
        http_response_code(404);
        echo json_encode(['error' => 'Médico no encontrado']);
        exit;
      }

      $imagen = $medico['imagen'] ?? '';
      $imagen_url = trim($data['imagen_url'] ?? '');

      if (!empty($data['imagen'])) {
        if (strpos($data['imagen'], 'data:image/') === 0) {
          $imagen = procesarImagen($data['imagen'], 'medicos') ?: $imagen;
        } else {
          $imagen = $data['imagen'];
        }
      }

      $data['imagen'] = $imagen;
      $data['imagen_url'] = $imagen_url;

      $medicoModel->update((int)$data['id'], $data);
      http_response_code(200);
      echo json_encode(['message' => 'Médico actualizado correctamente']);
      break;

    case 'DELETE':
      requireApiAuth(['admin']);
      $data = json_decode(file_get_contents('php://input'), true);
      if (!$data || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID obligatorio']);
        exit;
      }
      $medico = $medicoModel->find((int)$data['id']);
      if (!$medico) {
        http_response_code(404);
        echo json_encode(['error' => 'Médico no encontrado']);
        exit;
      }
      $medicoModel->delete((int)$data['id']);
      http_response_code(200);
      echo json_encode(['message' => 'Médico eliminado correctamente']);
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
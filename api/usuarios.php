<?php

require_once __DIR__ . '/../models/Usuario.php';

header('Content-Type: application/json; charset=utf-8');

$usuarioModel = new Usuario();
$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
      if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $usuario = $usuarioModel->findSafe($id);

        if (!$usuario) {
          http_response_code(404);
          echo json_encode(['error' => 'Usuario no encontrado']);
          exit;
        }

        http_response_code(200);
        echo json_encode($usuario);
        exit;
      }

      $usuarios = $usuarioModel->allSafe();
      http_response_code(200);
      echo json_encode($usuarios);
      break;

    case 'POST':
      $data = json_decode(file_get_contents('php://input'), true);

      if (!$data || empty($data['nombre']) || empty($data['apellidos']) || empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        exit;
      }

      if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'El email no es válido']);
        exit;
      }

      if (strlen($data['password']) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
      }

      if ($usuarioModel->existsByEmail($data['email'])) {
        http_response_code(409);
        echo json_encode(['error' => 'El email ya existe']);
        exit;
      }

      $id = $usuarioModel->createWithRole([
        'nombre' => $data['nombre'],
        'apellidos' => $data['apellidos'],
        'email' => $data['email'],
        'password' => $data['password'],
        'rol' => $data['rol'] ?? 'paciente'
      ]);

      http_response_code(201);
      echo json_encode([
        'message' => 'Usuario creado correctamente',
        'id' => $id
      ]);
      break;

    case 'PUT':
      $data = json_decode(file_get_contents('php://input'), true);

      if (!$data || empty($data['id']) || empty($data['nombre']) || empty($data['apellidos']) || empty($data['email']) || empty($data['rol'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        exit;
      }

      $usuario = $usuarioModel->find((int)$data['id']);
      if (!$usuario) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
      }

      $usuarioModel->update((int)$data['id'], $data);

      http_response_code(200);
      echo json_encode(['message' => 'Usuario actualizado correctamente']);
      break;

    case 'DELETE':
      $data = json_decode(file_get_contents('php://input'), true);

      if (!$data || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID obligatorio']);
        exit;
      }

      $usuario = $usuarioModel->find((int)$data['id']);
      if (!$usuario) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
      }

      $usuarioModel->delete((int)$data['id']);

      http_response_code(200);
      echo json_encode(['message' => 'Usuario eliminado correctamente']);
      break;

    default:
      http_response_code(405);
      echo json_encode(['error' => 'Método no permitido']);
      break;
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Error interno del servidor',
  ]);
}

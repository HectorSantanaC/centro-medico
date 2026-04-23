<?php

function requireApiAuth(array $roles = ['admin', 'gestor']) {
  if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
  }
  if (!in_array($_SESSION['usuario_rol'], $roles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Sin permisos']);
    exit;
  }
  return [
    'id' => $_SESSION['usuario_id'],
    'rol' => $_SESSION['usuario_rol']
  ];
}

function requireApiAuthPaciente() {
  if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
  }
  return [
    'id' => $_SESSION['usuario_id'],
    'rol' => $_SESSION['usuario_rol']
  ];
}
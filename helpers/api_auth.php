<?php

function requireApiAuth(array $roles = ['admin', 'gestor']) {
  if (empty($_SESSION['usuario_id']) || !isset($_SESSION['usuario_roles'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
  }
  
  $userRoles = array_column($_SESSION['usuario_roles'], 'rol_nombre');
  $hasRole = false;
  foreach ($roles as $role) {
    if (in_array($role, $userRoles)) {
      $hasRole = true;
      break;
    }
  }
  
  if (!$hasRole) {
    http_response_code(403);
    echo json_encode(['error' => 'Sin permisos']);
    exit;
  }
  
  return [
    'id' => $_SESSION['usuario_id'],
    'roles' => $_SESSION['usuario_roles']
  ];
}

function requireApiAuthPaciente() {
  if (empty($_SESSION['usuario_id']) || !isset($_SESSION['usuario_roles'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
  }
  
  $userRoles = array_column($_SESSION['usuario_roles'], 'rol_nombre');
  if (!in_array('paciente', $userRoles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Sin permisos']);
    exit;
  }
  
  return [
    'id' => $_SESSION['usuario_id'],
    'roles' => $_SESSION['usuario_roles']
  ];
}
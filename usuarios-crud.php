<?php

require_once __DIR__ . '/controllers/UsuariosController.php';

$controller = new UsuariosController();
$data = $controller->handleRequest();

$action = $data['action'];
$id = $data['id'];
$usuarios = $data['usuarios'];
$usuarioEdit = $data['usuarioEdit'];
$roles = $data['roles'];
$message = $data['message'];
$messageType = $data['messageType'];
$active = $data['active'];

require_once __DIR__ . '/views/admin/usuarios.php';
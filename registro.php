<?php

require_once __DIR__ . '/controllers/RegistroController.php';

$controller = new RegistroController();
$data = $controller->handleRequest();

$page_title = $data['page_title'];
$errores = $data['errores'];
$nombre = $data['nombre'];
$apellidos = $data['apellidos'];
$email = $data['email'];

require_once __DIR__ . '/views/registro.php';
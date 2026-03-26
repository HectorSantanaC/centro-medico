<?php

require_once __DIR__ . '/controllers/AuthController.php';

$controller = new AuthController();
$data = $controller->handleLogin();

$page_title = $data['page_title'];
$error = $data['error'];
$email = $data['email'];
$registro_ok = $data['registro_ok'];

require_once __DIR__ . '/views/auth/login.php';

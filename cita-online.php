<?php

require_once __DIR__ . '/controllers/CitaOnlineController.php';

$controller = new CitaOnlineController();
$data = $controller->handleRequest();

$page_title = $data['page_title'];
$especialidades = $data['especialidades'];
$mensaje_exito = $data['mensaje_exito'];
$user_role = $data['user_role'];

require_once __DIR__ . '/views/cita-online.php';

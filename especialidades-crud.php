<?php

require_once __DIR__ . '/controllers/EspecialidadesController.php';

$controller = new EspecialidadesController();
$data = $controller->handleRequest();

$action = $data['action'];
$especialidades = $data['especialidades'];
$especialidadEdit = $data['especialidadEdit'];
$page = $data['page'];
$totalPages = $data['totalPages'];
$totalItems = $data['totalItems'];
$message = $data['message'];
$messageType = $data['messageType'];
$active = $data['active'];

require_once __DIR__ . '/views/admin/especialidades.php';

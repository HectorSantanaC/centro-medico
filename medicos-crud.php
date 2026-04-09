<?php

require_once __DIR__ . '/controllers/MedicosController.php';

$controller = new MedicosController();
$data = $controller->handleRequest();

$action = $data['action'];
$medicos = $data['medicos'];
$medicoEdit = $data['medicoEdit'];
$especialidades = $data['especialidades'];
$page = $data['page'];
$totalPages = $data['totalPages'];
$totalItems = $data['totalItems'];
$message = $data['message'];
$messageType = $data['messageType'];
$active = $data['active'];

require_once __DIR__ . '/views/admin/medicos.php';

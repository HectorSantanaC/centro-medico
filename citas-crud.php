<?php

require_once __DIR__ . '/controllers/CitasController.php';
require_once __DIR__ . '/config/Database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

$controller = new CitasController();
$data = $controller->handleRequest();

$action = $data['action'];
$id = $data['id'];
$message = $data['message'];
$messageType = $data['messageType'];
$citas = $data['citas'];
$page = $data['page'];
$totalPages = $data['totalPages'];
$totalCitas = $data['totalCitas'];
$filtros = $data['filtros'];
$citaEdit = $data['citaEdit'];
$especialidades = $data['especialidades'];
$medicos = $data['medicos'];
$pacientes = $data['pacientes'];
$estados = $data['estados'];
$active = $data['active'];

require_once __DIR__ . '/views/admin/citas.php';

<?php

require_once __DIR__ . '/controllers/MisCitasController.php';

$controller = new MisCitasController();
$data = $controller->handleRequest();

$page_title = $data['page_title'];
$citas = $data['citas'];
$estados = $data['estados'];
$message = $data['message'];
$messageType = $data['messageType'];

require_once __DIR__ . '/views/mis-citas.php';
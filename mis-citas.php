<?php

require_once __DIR__ . '/controllers/MisCitasController.php';

$controller = new MisCitasController();
$data = $controller->handleRequest();

$page_title = $data['page_title'];
$citas = $data['citas'];
$estados = $data['estados'];
$mensaje = $data['mensaje'];
$mensaje_tipo = $data['mensaje_tipo'];

include __DIR__ . '/views/mis-citas.php';
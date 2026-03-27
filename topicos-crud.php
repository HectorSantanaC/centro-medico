<?php

require_once __DIR__ . '/controllers/TopicosController.php';

$controller = new TopicosController();
$data = $controller->handleRequest();

$action = $data['action'];
$id = $data['id'];
$topicos = $data['topicos'];
$topicoEdit = $data['topicoEdit'];
$message = $data['message'];
$messageType = $data['messageType'];
$active = $data['active'];

require_once __DIR__ . '/views/admin/topicos.php';

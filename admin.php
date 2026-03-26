<?php

require_once __DIR__ . '/controllers/AdminController.php';

$controller = new AdminController();
$data = $controller->handleRequest();

$stats = $data['stats'];
$isAdmin = $data['isAdmin'];
$active = $data['active'];

require_once __DIR__ . '/views/admin/index.php';

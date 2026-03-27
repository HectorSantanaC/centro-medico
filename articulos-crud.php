<?php

require_once __DIR__ . '/controllers/ArticulosController.php';

$controller = new ArticulosController();
$data = $controller->handleRequest();

$action = $data['action'];
$articulos = $data['articulos'];
$articulo = $data['articulo'];
$topicos = $data['topicos'];
$message = $data['message'];
$messageType = $data['messageType'];
$canManage = $data['canManage'];

if (!$canManage) {
  header('Location: blog.php');
  exit;
}

require_once __DIR__ . '/views/admin/articulos.php';

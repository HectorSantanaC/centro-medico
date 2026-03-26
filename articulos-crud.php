<?php

require_once __DIR__ . '/controllers/ArticulosController.php';

$controller = new ArticulosController();
$data = $controller->handleRequest();

$action = $data['action'];
$id = $data['id'];
$articulos = $data['articulos'];
$articulo = $data['articulo'];
$categorias = $data['categorias'];
$message = $data['message'];
$messageType = $data['messageType'];
$canManage = $data['canManage'];

if (!$canManage) {
  header('Location: blog.php');
  exit;
}

require_once __DIR__ . '/views/admin/articulos.php';

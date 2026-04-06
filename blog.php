<?php

require_once __DIR__ . '/controllers/ArticulosController.php';

$controller = new ArticulosController();
$data = $controller->handleRequest();

$action = $data['action'];
$id = $data['id'];
$articulos = $data['articulos'];
$articulo = $data['articulo'];
$topicos = $data['topicos'] ?? [];
$message = $data['message'];
$messageType = $data['messageType'];
$canManage = $data['canManage'];

$page_title = 'Blog';

if ($action === 'view' && $articulo) {
  $page_title = $articulo['titulo'];
  include __DIR__ . '/views/blogarticulo.php';
} elseif ($action === 'view' && !$articulo) {
  header('Location: blog.php');
  exit;
} else {
  include __DIR__ . '/views/blog.php';
}

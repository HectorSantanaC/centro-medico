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

$page_title = 'Blog';

if (($action === 'view' || $action === 'edit') && $articulo && !$canManage) {
  $page_title = $articulo['titulo'];
  include __DIR__ . '/views/blogarticulo.php';
} else {
  include __DIR__ . '/views/blog.php';
}

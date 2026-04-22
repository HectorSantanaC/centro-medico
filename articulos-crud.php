<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

$canManage = isset($_SESSION['usuario_rol']) && 
  in_array($_SESSION['usuario_rol'], ['admin', 'gestor']);

if (!$canManage) {
  header('Location: blog.php');
  exit;
}

$active = 'contenidos';

require_once __DIR__ . '/views/admin/articulos.php';
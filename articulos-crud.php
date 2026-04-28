<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.php');
  exit;
}

$canManage = false;
if (isset($_SESSION['usuario_roles'])) {
  foreach ($_SESSION['usuario_roles'] as $role) {
    if (in_array($role['rol_nombre'], ['admin', 'gestor'])) { $canManage = true; break; }
  }
}

if (!$canManage) {
  header('Location: blog.php');
  exit;
}

$active = 'contenidos';

require_once __DIR__ . '/views/admin/articulos.php';
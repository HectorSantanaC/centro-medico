<?php

session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'gestor'])) {
  header('Location: login.php');
  exit;
}

require_once __DIR__ . '/views/admin/usuarios.php';

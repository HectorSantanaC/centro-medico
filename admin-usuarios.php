<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$active = 'usuarios';
require_once __DIR__ . '/views/admin/usuarios.php';

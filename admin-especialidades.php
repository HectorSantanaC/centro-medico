<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$active = 'especialidades';
require_once __DIR__ . '/views/admin/especialidades.php';

<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$active = 'roles';
require_once __DIR__ . '/views/admin/roles.php';
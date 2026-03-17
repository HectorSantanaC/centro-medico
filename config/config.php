<?php
// =====================================================
// CONFIGURACIÓN GENERAL - CENTRO MÉDICO
// =====================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'centro-medico');
define('DB_USER', 'postgres');  // Cambia por tu usuario
define('DB_PASS', '1234');  // Cambia por tu contraseña
define('DB_PORT', '5432');

define('APP_URL', 'http://localhost/centro_medico/');
define('APP_NOMBRE', 'Centro Médico TAC7');

session_start();
date_default_timezone_set('Europe/Madrid');
?>

<?php
/**
 * =====================================================
 * logout.php
 * CERRAR SESIÓN
 * =====================================================
 * 
 * Este archivo destruye la sesión actual y redirige
 * al usuario a la página de login.
 * 
 * FLUJO:
 *   1. Vaciar todas las variables de sesión
 *   2. Destruir la cookie de sesión
 *   3. Redirigir al login
 * 
 * =====================================================
 */

// Iniciar sesión si no está iniciada
// (necesario para poder destruirla)
session_start();

/**
 * session_unset() → Elimina todas las variables de sesión
 * Ejemplo: $_SESSION['usuario_id'] deja de existir
 */
session_unset();

/**
 * session_destroy() → Destruye la sesión completamente
 * Elimina el archivo de sesión en el servidor
 */
session_destroy();

/**
 * Redirigir a la página principal
 * El usuario ya no está logueado
 */
header('Location: index.php');
exit;

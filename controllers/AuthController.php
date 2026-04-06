<?php

require_once __DIR__ . '/../models/Usuario.php';

class AuthController
{
  private Usuario $usuarioModel;

  public function __construct()
  {
    $this->usuarioModel = new Usuario();
  }

  public function handleLogin(): array
  {
    $page_title = 'Login / Registro';
    $error = '';
    $email = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $email = trim($_POST['email'] ?? '');
      $password = $_POST['password'] ?? '';

      if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
      } else {
        $usuario = $this->usuarioModel->findByEmail($email);

        if (!$usuario) {
          $error = 'Email o contraseña incorrectos';
        } elseif ($password !== $usuario['password']) {
          $error = 'Email o contraseña incorrectos';
        } else {
          $this->loginUser($usuario);
        }
      }
    }

    return [
      'page_title' => $page_title,
      'error' => $error,
      'email' => $email,
      'registro_ok' => $_GET['registro'] ?? null
    ];
  }

  private function loginUser(array $usuario): void
  {
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_rol'] = $usuario['rol'];

    if (in_array($usuario['rol'], ['admin', 'gestor'])) {
      header('Location: admin.php');
    } else {
      header('Location: index.php');
    }
    exit;
  }

  public function logout(): void
  {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
  }
}

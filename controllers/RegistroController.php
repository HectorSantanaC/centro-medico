<?php

require_once __DIR__ . '/../models/Usuario.php';

class RegistroController {
    private Usuario $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function handleRequest(): array {
        $page_title = 'Registro';
        $errores = [];
        $nombre = '';
        $apellidos = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($nombre)) {
                $errores[] = 'El nombre es obligatorio';
            } elseif (strlen($nombre) < 2) {
                $errores[] = 'El nombre debe tener al menos 2 caracteres';
            }

            if (empty($apellidos)) {
                $errores[] = 'Los apellidos son obligatorios';
            }

            if (empty($email)) {
                $errores[] = 'El email es obligatorio';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El formato del email no es válido';
            }

            if (empty($password)) {
                $errores[] = 'La contraseña es obligatoria';
            } elseif (strlen($password) < 6) {
                $errores[] = 'La contraseña debe tener al menos 6 caracteres';
            }

            if ($password !== $password_confirm) {
                $errores[] = 'Las contraseñas no coinciden';
            }

            if (empty($errores) && $this->usuarioModel->existsByEmail($email)) {
                $errores[] = 'Este email ya está registrado';
            }

            if (empty($errores)) {
                $this->usuarioModel->create([
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'email' => $email,
                    'password' => $password
                ]);

                header('Location: login.php?registro=ok');
                exit;
            }
        }

        return [
            'page_title' => $page_title,
            'errores' => $errores,
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email
        ];
    }
}
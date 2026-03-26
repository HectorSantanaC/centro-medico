<?php

require_once __DIR__ . '/../models/Usuario.php';

class UsuariosController {
    private Usuario $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function handleRequest(): array {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
            header('Location: login.php');
            exit;
        }

        $action = $_REQUEST['action'] ?? 'list';
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
        $message = '';
        $messageType = '';
        $roles = ['admin', 'gestor', 'paciente'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellidos' => trim($_POST['apellidos'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'rol' => $_POST['rol'] ?? 'paciente'
            ];

            try {
                if (empty($data['nombre']) || empty($data['apellidos']) || empty($data['email'])) {
                    throw new Exception('Todos los campos obligatorios deben estar completos');
                }

                if ($action === 'create') {
                    $password = !empty($data['password']) ? $data['password'] : 'password123';
                    $this->usuarioModel->createWithRole([
                        'nombre' => $data['nombre'],
                        'apellidos' => $data['apellidos'],
                        'email' => $data['email'],
                        'password' => $password,
                        'rol' => $data['rol']
                    ]);
                    $message = 'Usuario creado exitosamente';
                    $messageType = 'success';
                    $action = 'list';
                } elseif ($action === 'edit' && $id) {
                    $this->usuarioModel->update($id, $data);
                    $message = 'Usuario actualizado exitosamente';
                    $messageType = 'success';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }

        if ($action === 'delete' && $id) {
            try {
                $this->usuarioModel->delete($id);
                $message = 'Usuario eliminado exitosamente';
                $messageType = 'success';
                $action = 'list';
            } catch (Exception $e) {
                $message = 'Error al eliminar usuario: ' . $e->getMessage();
                $messageType = 'error';
            }
        }

        $usuarios = [];
        $usuarioEdit = null;

        if ($action === 'list') {
            $usuarios = $this->usuarioModel->allUsers();
        } elseif ($action === 'edit' && $id) {
            $usuarioEdit = $this->usuarioModel->find($id);
        }

        return [
            'action' => $action,
            'id' => $id,
            'usuarios' => $usuarios,
            'usuarioEdit' => $usuarioEdit,
            'roles' => $roles,
            'message' => $message,
            'messageType' => $messageType,
            'active' => 'usuarios'
        ];
    }
}
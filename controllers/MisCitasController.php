<?php

require_once __DIR__ . '/../models/Cita.php';

class MisCitasController {
    private Cita $citaModel;

    public function __construct() {
        $this->citaModel = new Cita();
    }

    public function handleRequest(): array {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        if ($_SESSION['usuario_rol'] === 'admin') {
            header('Location: citas-crud.php');
            exit;
        }

        $page_title = 'Mis Citas';
        $usuario_id = $_SESSION['usuario_id'];
        $mensaje = '';
        $mensaje_tipo = '';

        if (isset($_GET['cancelar']) && $_GET['cancelar']) {
            $cita_id = (int)$_GET['cancelar'];
            $this->citaModel->cancel($cita_id, $usuario_id);
            $mensaje = 'Cita cancelada correctamente';
            $mensaje_tipo = 'success';
        }

        $citas = $this->citaModel->getByPaciente($usuario_id);
        $estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];

        return [
            'page_title' => $page_title,
            'citas' => $citas,
            'estados' => $estados,
            'mensaje' => $mensaje,
            'mensaje_tipo' => $mensaje_tipo
        ];
    }
}
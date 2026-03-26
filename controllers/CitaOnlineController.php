<?php

require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Especialidad.php';

class CitaOnlineController {
    private Cita $citaModel;
    private Especialidad $especialidadModel;

    public function __construct() {
        $this->citaModel = new Cita();
        $this->especialidadModel = new Especialidad();
    }

    public function handleRequest(): array {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        $page_title = 'Reservar Cita';
        $mensaje_exito = '';

        if ($_POST) {
            $data = $this->sanitizePostData();
            $cita_id = $this->citaModel->create($data);
            
            $mensaje_exito = "Cita RESERVADA!<br>📅  " . date('d/m/Y', strtotime($data['fecha'])) . " " . $data['hora'];
        }

        $especialidades = $this->especialidadModel->allActives();

        return [
            'page_title' => $page_title,
            'especialidades' => $especialidades,
            'mensaje_exito' => $mensaje_exito,
            'user_role' => $_SESSION['usuario_rol'] ?? 'paciente'
        ];
    }

    private function sanitizePostData(): array {
        return [
            'paciente_id' => $_SESSION['usuario_id'],
            'medico_id' => (int)($_POST['medico_id'] ?? 0),
            'especialidad_id' => (int)($_POST['especialidad_id'] ?? 0),
            'fecha' => $_POST['fecha_cita'] ?? '',
            'hora' => $_POST['hora_cita'] ?? ''
        ];
    }
}

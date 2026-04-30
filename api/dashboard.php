<?php

session_start();
require_once __DIR__ . '/../helpers/api_auth.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Especialidad.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Cita.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
      requireApiAuth(['admin', 'gestor']);

      $usuarioModel = new Usuario();
      $stats = $usuarioModel->getStats();

      $especialidadModel = new Especialidad();
      $stats['especialidades'] = $especialidadModel->count();

      $medicoModel = new Medico();
      $stats['medicos'] = $medicoModel->count();

      $citaModel = new Cita();
      $citasPorEstado = $citaModel->getStatsPorEstado();
      $stats['citas'] = [
        'total' => array_sum(array_column($citasPorEstado, 'total')),
        'por_estado' => $citasPorEstado,
        'por_especialidad' => $citaModel->getCitasPorEspecialidad(),
        'evolucion_mensual' => $citaModel->getCitasPorMes(12),
        'por_medico' => $citaModel->getCitasPorMedico(),
        'por_dia_semana' => $citaModel->getCitasPorDiaSemana(),
        'citas_hoy' => $citaModel->getCitasHoy(),
        'tasa_cancelacion' => $citaModel->getTasaCancelacion()
      ];

      http_response_code(200);
      echo json_encode($stats);
      break;

    default:
      http_response_code(405);
      echo json_encode(['error' => 'Método no permitido']);
      break;
  }
} catch (\Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error interno del servidor']);
}
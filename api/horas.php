<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Cita.php';

header('Content-Type: application/json');

$fecha = $_GET['fecha'] ?? '';
$medicoId = (int)($_GET['medico_id'] ?? 0);

if (!$fecha || !$medicoId) {
  echo json_encode(['error' => 'Parámetros inválidos']);
  exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
  echo json_encode(['error' => 'Formato de fecha inválido']);
  exit;
}

$diaSemana = (int)date('w', strtotime($fecha));
if ($diaSemana === 0 || $diaSemana === 6) {
  echo json_encode(['horas' => [], 'mensaje' => 'Cerrado fines de semana']);
  exit;
}

$citaModel = new Cita();
$ocupadas = $citaModel->getOcupadasPorFechaMedico($fecha, $medicoId);

$horasOcupadas = array_map(function($c) {
  return date('H:i', strtotime($c['hora']));
}, $ocupadas);

$horasDisponibles = [];
$inicio = strtotime('09:00');
$fin = strtotime('17:30');

for ($t = $inicio; $t <= $fin; $t += 1800) {
  $hora = date('H:i', $t);
  if (!in_array($hora, $horasOcupadas)) {
    $horasDisponibles[] = $hora;
  }
}

echo json_encode(['horas' => $horasDisponibles]);

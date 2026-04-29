<?php

session_start();
require_once __DIR__ . '/../helpers/api_auth.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Cita.php';

if (!isset($_SESSION['usuario_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'No autenticado']);
  exit;
}

header('Content-Type: application/json');

$fecha = $_GET['fecha'] ?? '';
$medicoId = (int)($_GET['medico_id'] ?? 0);
$citaId = (int)($_GET['cita_id'] ?? 0);

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
$ocupadas = $citaModel->getOcupadasPorFechaMedico($fecha, $medicoId, $citaId ?: null);

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

if ($fecha === date('Y-m-d')) {
  $horaActual = date('H:i');
  $horasDisponibles = array_filter($horasDisponibles, function($h) use ($horaActual) {
    return $h > $horaActual;
  });
  $horasDisponibles = array_values($horasDisponibles);
}

echo json_encode(['horas' => $horasDisponibles]);

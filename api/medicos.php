<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Medico.php';

header('Content-Type: application/json');

$espId = (int)($_GET['especialidad_id'] ?? 0);

if ($espId > 0) {
  $medicoModel = new Medico();
  $medicos = $medicoModel->getByEspecialidad($espId);
  echo json_encode($medicos);
} else {
  echo json_encode([]);
}

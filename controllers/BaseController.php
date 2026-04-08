<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/sanitize.php';

class BaseController
{
  protected Database $db;
  protected PDO $pdo;

  public function __construct()
  {
    $this->db = Database::getInstance();
    $this->pdo = $this->db->getConnection();
  }

  protected function requireAuth(): void
  {
    if (!isset($_SESSION['usuario_id'])) {
      header('Location: login.php');
      exit;
    }
  }

  protected function requireRole(array $roles): void
  {
    if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], $roles)) {
      header('Location: login.php');
      exit;
    }
  }

  protected function getPostData(array $fields, array $map = []): array
  {
    return sanitizePostData($fields, $map);
  }

  protected function redirect(string $url): void
  {
    header('Location: ' . $url);
    exit;
  }

  protected function getSanitizedInput(array $fields): array
  {
    return sanitizePostData($fields);
  }

  protected function jsonResponse(array $data, int $statusCode = 200): void
  {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }

  protected function getCurrentUserId(): ?int
  {
    return $_SESSION['usuario_id'] ?? null;
  }

  protected function getCurrentUserRole(): ?string
  {
    return $_SESSION['usuario_rol'] ?? null;
  }

  protected function isAdmin(): bool
  {
    return ($_SESSION['usuario_rol'] ?? '') === 'admin';
  }

  protected function isGestor(): bool
  {
    return ($_SESSION['usuario_rol'] ?? '') === 'gestor';
  }

  protected function isPaciente(): bool
  {
    return ($_SESSION['usuario_rol'] ?? '') === 'paciente';
  }

  protected function generateCsrfToken(): string
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (!isset($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
  }

  protected function validateCsrfToken(?string $token): bool
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (!isset($_SESSION['csrf_token']) || empty($token)) {
      return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
  }

  protected function requireCsrfToken(): void
  {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;

    if (!$this->validateCsrfToken($token)) {
      http_response_code(403);
      die('Token CSRF inválido');
    }
  }
}
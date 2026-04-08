<?php

function sanitizePostData(array $fields, array $map = []): array
{
  $result = [];

  foreach ($fields as $field => $type) {
    $postKey = $map[$field] ?? $field;
    $value = $_POST[$postKey] ?? '';

    switch ($type) {
      case 'int':
        $result[$field] = (int) $value;
        break;
      case 'float':
        $result[$field] = (float) $value;
        break;
      case 'string':
        $result[$field] = trim($value);
        break;
      case 'text':
        $result[$field] = trim($value);
        break;
      case 'default':
      default:
        $result[$field] = $value;
    }
  }

  return $result;
}

function csrf_token(): string
{
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
  return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_url(): string
{
  return 'csrf_token=' . csrf_token();
}
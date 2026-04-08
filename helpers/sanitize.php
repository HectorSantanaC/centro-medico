<?php

function sanitizePostData(array $fields): array
{
  $result = [];

  foreach ($fields as $field => $type) {
    $value = $_POST[$field] ?? '';

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
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
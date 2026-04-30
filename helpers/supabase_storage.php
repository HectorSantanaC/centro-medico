<?php

require_once __DIR__ . '/../config/Database.php';

define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);

function procesarImagen(string $imagenData, string $carpeta): ?string {
  if (empty($imagenData) || strpos($imagenData, 'data:image/') !== 0) {
    return null;
  }

  $matches = [];
  if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $imagenData, $matches)) {
    return 'Formato de imagen inválido';
  }

  $extension = $matches[1];
  $datosBase64 = $matches[2];
  $contenido = base64_decode($datosBase64);

  if ($contenido === false || strlen($contenido) < 100) {
    return 'No se pudo decodificar la imagen';
  }

  if (strlen($contenido) > MAX_IMAGE_SIZE) {
    return 'La imagen excede el tamaño máximo de 5MB';
  }

  $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  if (!in_array(strtolower($extension), $allowedExts)) {
    return 'Extensión de imagen no permitida';
  }

  if (usarSupabaseStorage() && SUPABASE_URL && SUPABASE_KEY) {
    return subirASupabase($contenido, $extension, $carpeta);
  }

  return guardarLocal($contenido, $extension, $carpeta);
}

function guardarLocal(string $contenido, string $extension, string $carpeta): ?string {
  $uploadDir = __DIR__ . '/../assets/img/' . $carpeta . '/';
  
  if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
      return 'No se pudo crear el directorio de uploads';
    }
  }

  $filename = uniqid($carpeta . '_') . '.' . $extension;
  $ruta = $uploadDir . $filename;

  if (file_put_contents($ruta, $contenido) === false) {
    return 'No se pudo guardar la imagen';
  }

  return 'assets/img/' . $carpeta . '/' . $filename;
}

function subirASupabase(string $contenido, string $extension, string $carpeta): ?string {
  $filename = $carpeta . '/' . uniqid($carpeta . '_') . '.' . $extension;
  
  $url = SUPABASE_URL . '/storage/v1/object/media/' . $filename;
  
  $mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp'
  ];
  
  $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $contenido);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . SUPABASE_KEY,
    'Content-Type: ' . $mimeType
  ]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $error = curl_error($ch);
  curl_close($ch);

  if ($httpCode >= 200 && $httpCode < 300) {
    return SUPABASE_URL . '/storage/v1/object/public/media/' . $filename;
  }

  error_log('Supabase Storage error - URL: ' . $url . ' | Error: ' . $error . ' | HTTP: ' . $httpCode . ' | Response: ' . $response);
  return null;
}
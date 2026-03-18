<?php
// db.php - Render + cualquier hosting PostgreSQL
if (getenv('DATABASE_URL')) {
    // Render format: postgres://user:pass@host:port/db
    $url = parse_url(getenv('DATABASE_URL'));
    $host = $url['host'];
    $port = $url['port'];
    $dbname = ltrim($url['path'], '/');
    $user = $url['user'];
    $pass = $url['pass'];
} else {
    // Fallback local
    $host = 'localhost'; $port = 5432; $dbname = 'centro_medico';
    $user = 'postgres'; $pass = '';
}

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
?>

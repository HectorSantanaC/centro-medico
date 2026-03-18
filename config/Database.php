<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        if (getenv('DATABASE_URL')) {
            $url = parse_url(getenv('DATABASE_URL'));
            $host = $url['host'];
            $port = $url['port'];
            $dbname = ltrim($url['path'], '/');
            $user = $url['user'];
            $pass = $url['pass'];
        } else {
            $host = 'localhost'; $port = 5432; $dbname = 'centro_medico';
            $user = 'postgres'; $pass = '1234';
        }
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

define('APP_URL', getenv('APP_URL') ?: 'http://localhost/');
define('APP_NOMBRE', 'Centro Médico TAC7');
session_start();
date_default_timezone_set('Europe/Madrid');
?>

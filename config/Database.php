<?php
// config/Database.php - Render DATABASE_URL FIX
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dbUrl = getenv('DATABASE_URL');
        
        if ($dbUrl) {
            // Parsear Render DATABASE_URL robusto
            $url = parse_url($dbUrl);
            
            $host = $url['host'] ?? 'localhost';
            $port = isset($url['port']) ? $url['port'] : 5432;
            $dbname = trim($url['path'] ?? '', '/');
            $user = $url['user'] ?? 'postgres';
            $pass = $url['pass'] ?? '';
            
            // Debug (quitar en producción)
            error_log("DB Connect: host=$host, port=$port, db=$dbname");
            
        } else {
            // Local
            $host = 'localhost';
            $port = 5432;
            $dbname = 'centro_medico';
            $user = 'postgres';
            $pass = '1234';
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

// Config app
define('APP_URL', '/');
define('APP_NOMBRE', 'Centro Médico TAC7');
session_start();
date_default_timezone_set('Europe/Madrid');
?>

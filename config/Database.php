<?php
// config/Database.php - MÉTODOS COMPLETOS para tu app
class Database
{
	private static $instance = null;
	private $pdo;

	private function __construct()
	{
		$dbUrl = getenv('DATABASE_URL');
		if ($dbUrl) {
			$url = parse_url($dbUrl);
			$host = $url['host'] ?? 'localhost';
			$port = isset($url['port']) ? $url['port'] : 5432;
			$dbname = trim($url['path'] ?? '', '/');
			$user = $url['user'] ?? 'postgres';
			$pass = $url['pass'] ?? '';
		} else {
			// Configuración para local (xampp)
			$host = 'localhost';
			$port = 5432;
			$dbname = 'centro-medico';
			$user = 'postgres';
			$pass = '1234';
		}

		$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
		$this->pdo = new PDO($dsn, $user, $pass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		]);
	}

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function getConnection()
	{
		return $this->pdo;
	}

	// ✅ MÉTODO fetchAll (para SELECT)
	public function fetchAll($sql, $params = [])
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll();
	}

	// ✅ MÉTODO insert (para INSERT RETURNING)
	public function insert($sql, $params = [])
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $this->pdo->lastInsertId();
	}

	// ✅ MÉTODO query (alias fetchAll)
	public function query($sql, $params = [])
	{
		return $this->fetchAll($sql, $params);
	}

	// ✅ MÉTODO execute (solo INSERT/UPDATE sin return)
	public function execute($sql, $params = [])
	{
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute($params);
	}
}

// Config
define('APP_URL', '/');
define('APP_NOMBRE', 'Centro Médico TAC7');
session_start();
date_default_timezone_set('Europe/Madrid');
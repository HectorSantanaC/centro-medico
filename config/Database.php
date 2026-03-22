<?php
/**
 * =====================================================
 * config/Database.php
 * =====================================================
 * 
 * Este archivo es la "puerta de entrada" a la base de datos.
 * Contiene una CLASE llamada Database que nos permite:
 *   - Conectarnos a PostgreSQL
 *   - Ejecutar consultas SQL de forma segura
 *   - Obtener resultados de SELECT, INSERT, UPDATE, DELETE
 * 
 * CONCEPTOS CLAVE QUE APRENDERÁS AQUÍ:
 *   - Clases y objetos (programación orientada a objetos)
 *   - Métodos estáticos (static)
 *   - Patrón Singleton (una sola instancia)
 *   - PDO (PHP Data Objects) para bases de datos
 *   - Prepared statements (consultas seguras)
 * 
 * =====================================================
 */

// ============================================================
// PARTE 1: DEFINIR LA CLASE
// ============================================================
// "class" es una palabra reservada de PHP para crear clases.
// Una clase es como un "molde" para crear objetos.
// El nombre de la clase suele empezar con mayúscula (PascalCase)

class Database
{
    // ========================================================
    // PROPIEDADES (VARIABLES DE LA CLASE)
    // ========================================================
    
    /**
     * $instance - GUARDA LA UNICA INSTANCIA DE LA CLASE
     * 
     * "static" significa que esta variable pertenece a la CLASE,
     * no a un objeto específico. Todas las instancias comparten
     * el mismo valor.
     * 
     * "private" significa que solo se puede acceder desde DENTRO
     * de esta clase (no desde fuera).
     * 
     * Empieza en null porque todavía no hemos creado ninguna conexión.
     */
    private static $instance = null;

    /**
     * $pdo - GUARDA LA CONEXIÓN A LA BASE DE DATOS
     * 
     * PDO es el objeto de PHP que conecta con bases de datos.
     * "private" porque solo Database debe usarlo internamente.
     */
    private $pdo;


    // ========================================================
    // MÉTODO MÁGICO: __construct()
    // ========================================================
    /**
     * Este método se ejecuta AUTOMÁTICAMENTE cuando se crea
     * un objeto de la clase Database con "new Database()"
     * 
     * Aquí es donde NOS CONECTAMOS a la base de datos.
     */
    private function __construct()
    {
        // ----------------------------------------------------
        // PASO 1: OBTENER LA CONFIGURACIÓN
        // ----------------------------------------------------
        // getenv() busca una variable de entorno en el sistema.
        // Las variables de entorno son como "interruptores" de configuración.
        // En producción (como Render), se usa DATABASE_URL.
        // En local (XAMPP), no existe esa variable.
        
        $dbUrl = getenv('DATABASE_URL');

        // ----------------------------------------------------
        // if / else: TOMAR DECISIONES SEGÚN EL ENTORNO
        // ----------------------------------------------------
        // Si existe DATABASE_URL, la parseamos (la descomponemos).
        // Si no existe, usamos los valores por defecto para XAMPP.
        
        if ($dbUrl) {
            // parse_url() divide una URL en partes:
            // postgres://user:pass@host:port/dbname
            // Se convierte en un array asociativo con claves:
            // 'host', 'port', 'user', 'pass', 'path'
            $url = parse_url($dbUrl);
            
            // ?? es el "operador de coalescencia nula"
            // Significa: "si no existe, usa este valor por defecto"
            // Ejemplo: $url['host'] ?? 'localhost'
            //   Si $url['host'] existe, úsalo.
            //   Si no existe, usa 'localhost'.
            $host = $url['host'] ?? 'localhost';
            $port = isset($url['port']) ? $url['port'] : 5432;  // 5432 es el puerto de PostgreSQL
            $dbname = trim($url['path'] ?? '', '/'); // trim() elimina / del principio y final
            $user = $url['user'] ?? 'postgres';
            $pass = $url['pass'] ?? '';
        } else {
            // Si no hay DATABASE_URL, usamos XAMPP local
            $host = 'localhost';
            $port = 5432;
            $dbname = 'centro-medico';
            $user = 'postgres';
            $pass = '1234';
        }

        // ----------------------------------------------------
        // PASO 2: CREAR EL DSN (Data Source Name)
        // ----------------------------------------------------
        // DSN es la "dirección" de la base de datos.
        // Formato para PostgreSQL: "pgsql:host=X;port=Y;dbname=Z"
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        // ----------------------------------------------------
        // PASO 3: CREAR LA CONEXIÓN CON PDO
        // ----------------------------------------------------
        // new PDO() crea una conexión a la base de datos.
        // Parámetros:
        //   1. DSN (la dirección)
        //   2. Usuario de la base de datos
        //   3. Contraseña
        //   4. Opciones (array de configuración)
        
        $this->pdo = new PDO($dsn, $user, $pass, [
            // PDO::ATTR_ERRMODE = PDO::ERRMODE_EXCEPTION
            // Significa: "Si hay un error SQL, LANZA una excepción"
            // Las excepciones son errores que podemos "atrapar" con try/catch
            // y mostrar mensajes amigables al usuario.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // PDO::ATTR_DEFAULT_FETCH_MODE = PDO::FETCH_ASSOC
            // Significa: "Cuando obtengamos datos de un SELECT,
            // devuélvelos como ARRAY ASOCIATIVO (con nombres de columnas)"
            // Ejemplo en vez de: $row[0], $row[1]
            // Obtenemos: $row['nombre'], $row['apellidos']
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }


    // ========================================================
    // MÉTODO ESTÁTICO: getInstance() - PATRÓN SINGLETON
    // ========================================================
    /**
     * "static" significa que se llama así: Database::getInstance()
     * Sin crear un objeto primero, sin "new".
     * 
     * PATRÓN SINGLETON: Este método garantiza que solo existe
     * UNA SOLA conexión a la base de datos en toda la aplicación.
     * 
     * ¿Por qué? Porque crear muchas conexiones es lento y consume recursos.
     * 
     * COMO USA:
     *   $db = Database::getInstance();  // En cualquier archivo PHP
     *   $db->fetchAll("SELECT ...");     // Ya tenemos la conexión lista
     */
    public static function getInstance()
    {
        // Si $instance es null, significa que nunca se ha creado.
        // Creamos una nueva instancia.
        if (self::$instance === null) {
            // self:: se refiere a la propia clase Database
            // new self() crea un nuevo objeto de esta clase
            // Esto llama a __construct() automáticamente
            self::$instance = new self();
        }

        // Si ya existe, devolvemos la que ya estaba creada.
        return self::$instance;
    }


    // ========================================================
    // GETTER: getConnection()
    // ========================================================
    /**
     * Devuelve el objeto PDO (la conexión).
     * 
     * "public" significa que se puede llamar desde fuera:
     *   $pdo = $db->getConnection();
     * 
     * Se usa cuando necesitas el PDO directamente para algo
     * específico (como prepare() con parámetros nombrados).
     */
    public function getConnection()
    {
        return $this->pdo;
    }


    // ========================================================
    // MÉTODO: fetchAll() - PARA SELECTS
    // ========================================================
    /**
     * Ejecuta un SELECT y devuelve TODAS las filas resultantes.
     * 
     * PARÁMETROS:
     *   $sql    = La consulta SQL con marcadores de posición (?)
     *   $params = Array con los valores para esos marcadores
     * 
     * EJEMPLO DE USO:
     *   $medicos = $db->fetchAll(
     *       "SELECT * FROM medicos WHERE especialidad_id = ?",
     *       [$especialidadId]
     *   );
     * 
     * Los "?" se llaman "marcadores de posición".
     * Los valores en $params填补 esos marcadores.
     * Esto protege contra SQL Injection (inyección SQL).
     */
    public function fetchAll($sql, $params = [])
    {
        // 1. Preparar la consulta (evita SQL injection)
        $stmt = $this->pdo->prepare($sql);
        
        // 2. Ejecutar con los parámetros
        $stmt->execute($params);
        
        // 3. Devolver todas las filas como array
        return $stmt->fetchAll();
    }


    // ========================================================
    // MÉTODO: insert() - PARA INSERT CON RETURNING
    // ========================================================
    /**
     * Ejecuta un INSERT y devuelve el ID del registro creado.
     * 
     * "RETURNING id" es específico de PostgreSQL.
     * Después de insertar, devuelve el ID generado automáticamente.
     * 
     * EJEMPLO:
     *   $nuevaCitaId = $db->insert(
     *       "INSERT INTO citas (paciente_id, fecha) VALUES (?, ?) RETURNING id",
     *       [$pacienteId, $fecha]
     *   );
     */
    public function insert($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        // lastInsertId() obtiene el ID del último INSERT
        return $this->pdo->lastInsertId();
    }


    // ========================================================
    // MÉTODO: query() - ALIAS DE fetchAll()
    // ========================================================
    /**
     * Es exactamente igual que fetchAll(), solo que el nombre
     * es más corto y legible para algunos casos.
     */
    public function query($sql, $params = [])
    {
        return $this->fetchAll($sql, $params);
    }


    // ========================================================
    // MÉTODO: execute() - PARA INSERT/UPDATE/DELETE
    // ========================================================
    /**
     * Ejecuta una consulta que NO necesita devolver datos.
     * Útil para UPDATE y DELETE.
     * 
     * EJEMPLO:
     *   $db->execute(
     *       "UPDATE citas SET estado = ? WHERE id = ?",
     *       ['completada', $citaId]
     *   );
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}


// ============================================================
// PARTE 2: CONFIGURACIÓN GLOBAL
// ============================================================

/**
 * define() crea una CONSTANTE.
 * Las constantes son valores que NO CAMBIAN durante la ejecución.
 * Se acceden sin $ (ejemplo: APP_NOMBRE, APP_URL)
 * 
 * CONVENCIONES DE NOMBRE:
 *   - TODO_MAYUSCULA_CON_GUION_BAJO
 *   - Prefijo para evitar colisiones (APP_, DB_, etc.)
 */

// URL base de la aplicación
define('APP_URL', '/');

// Nombre de la aplicación (mostrado en el título)
define('APP_NOMBRE', 'Centro Médico TAC7');

/**
 * session_start() inicia una SESIÓN de PHP.
 * 
 * ¿Qué es una sesión?
 *   Es como una "caja" donde guardamos información del usuario
 *   que persiste entre páginas. Ejemplos:
 *     - $_SESSION['usuario_id'] = ID del usuario logueado
 *     - $_SESSION['carrito'] = Productos del carrito de compras
 * 
 * NOTA: Solo se debe llamar UNA VEZ, al principio del script.
 * Por eso se incluye aquí, en config/Database.php que se carga
 * en todos los archivos que necesitan base de datos.
 */
session_start();

/**
 * date_default_timezone_set() configura la ZONA HORARIA.
 * 
 * ¿Por qué?
 *   PHP usa fechas/horas internas basadas en UTC (tiempo universal).
 *   Si no configuramos la zona, las horas pueden estar mal.
 *   'Europe/Madrid' = Hora de España (incluye cambio de invierno/verano)
 */
date_default_timezone_set('Europe/Madrid');

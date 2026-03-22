<?php

/**
 * =====================================================
 * citas-crud.php
 * GESTIÓN DE CITAS MÉDICAS - CRUD COMPLETO
 * =====================================================
 * 
 * NOTA: Solo los ADMINISTRADORES pueden acceder a este archivo.
 * Los pacientes deben usar mis-citas.php para ver sus citas.
 * 
 * Este archivo hace TODO lo relacionado con las citas:
 *   - LISTAR   → Ver todas las citas en una tabla
 *   - CREAR    → Crear nuevas citas (desde cita-online.php)
 *   - EDITAR   → Modificar una cita existente
 *   - ELIMINAR → Borrar una cita
 * 
 * CONCEPTOS CLAVE:
 *   - Reutilizar código con require_once
 *   - Variables superglobales ($_GET, $_POST, $_REQUEST, $_SERVER)
 *   - Operadores ternarios y de coalescencia
 *   - Consultas SQL con JOINs
 *   - Mezclar PHP con HTML (views)
 *   - isset(), empty(), foreach()
 * 
 * FLUJO DEL ARCHIVO:
 *   1. Cargar configuración (líneas 1-4)
 *   2. Procesar acciones del usuario (líneas 6-77)
 *   3. Generar HTML con los datos (líneas 79-220)
 * 
 * =====================================================
 */

// ============================================================
// PASO 1: CARGAR LA CONFIGURACIÓN
// ============================================================

/**
 * require_once 'config/Database.php'
 * 
 * SIGNIFICADO:
 *   - "require" = "EXIGE que este archivo exista"
 *   - "_once" = "Solo inclúyelo UNA VEZ, aunque se pida varias"
 * 
 * EFECTO:
 *   Carga todas las funciones de Database.php.
 *   Ahora podemos usar:
 *     - Database::getInstance()
 *     - $db->fetchAll()
 *     - $db->execute()
 *     - etc.
 * 
 * NOTA: Si el archivo no existe, PHP MUESTRA ERROR y para.
 *       Usar include si no quieres que sea obligatorio.
 */
require_once 'config/Database.php';

// ============================================================
// PASO 2: CONECTAR A LA BASE DE DATOS
// ============================================================

/**
 * Database::getInstance() → Devuelve la conexión a PostgreSQL
 * 
 * PATRÓN SINGLETON (explicado en Database.php):
 *   - La primera vez crea la conexión
 *   - Las siguientes veces reutiliza la misma
 * 
 * RESULTADO:
 *   $db contiene TODOS los métodos para trabajar con la BD:
 *     - $db->fetchAll($sql, $params) → SELECT
 *     - $db->insert($sql, $params)  → INSERT
 *     - $db->execute($sql, $params) → UPDATE/DELETE
 */
$db = Database::getInstance();

/**
 * También guardamos la conexión PDO directa.
 * ¿Por qué? Porque hay operaciones que necesitan
 * el PDO directamente (prepare con fetch).
 */
$pdo = $db->getConnection();

// ============================================================
// VERIFICAR QUE ES ADMINISTRADOR
// ============================================================

/**
 * Solo los administradores pueden acceder a esta página.
 * Si no está logueado o no es admin, redirigir.
 */
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
  // Si es un paciente logueado, ir a mis-citas.php
  if (isset($_SESSION['usuario_id'])) {
    header('Location: mis-citas.php');
  } else {
    // Si no está logueado, ir al login
    header('Location: login.php');
  }
  exit;
}

// ============================================================
// PASO 3: DETERMINAR QUÉ ACCIÓN HACER
// ============================================================

/**
 * $_REQUEST contiene TODOS los datos que llegan por:
 *   - GET (parámetros en la URL: ?action=edit&id=5)
 *   - POST (datos de formularios)
 * 
 * $_REQUEST['action'] nos dice qué quiere hacer el usuario:
 *   - 'list'   → Ver el listado (valor por defecto)
 *   - 'edit'   → Mostrar formulario de edición
 *   - 'delete' → Eliminar una cita
 * 
 * ?? 'list' = "Si no existe action, usa 'list' por defecto"
 * Esto se llama "operador de coalescencia nula".
 */
$action = $_REQUEST['action'] ?? 'list';

/**
 * $_REQUEST['id'] = El ID de la cita sobre la que actuar.
 * 
 * isset() = ¿EXISTE esta variable? (devuelve true/false)
 * (int) = Convertir a ENTERO (seguridad básica)
 * 
 * Ejemplo:
 *   URL: citas-crud.php?action=edit&id=5
 *   → $id = 5
 *   
 *   URL: citas-crud.php?action=list
 *   → $id = null
 */
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;

/**
 * Variables para guardar mensajes de éxito/error.
 * Se mostrarán en el HTML más abajo.
 */
$message = '';
$messageType = '';

// ============================================================
// PASO 4: PROCESAR LAS ACCIONES
// ============================================================

/**
 * $_SERVER es una variable superglobal que contiene
 * información del servidor y la petición actual.
 * 
 * $_SERVER['REQUEST_METHOD'] = 'GET' o 'POST'
 *   - GET = El usuario escribió la URL o hizo clic en un enlace
 *   - POST = El usuario envió un formulario con method="POST"
 * 
 * Aquí solo procesamos formularios enviados por POST.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  /**
   * Recoger los datos del formulario en un ARRAY.
   * 
   * $data = [ ... ] → Array asociativo con clave => valor
   * Ejemplo:
   *   $data['paciente_id'] = 3
   *   $data['fecha'] = '2024-06-15'
   * 
   * $_POST['campo'] = El valor del campo del formulario.
   * ?? 0 (o '' o 'pendiente') = Valor por defecto si está vacío.
   */
  $data = [
    'paciente_id'     => (int)($_POST['paciente_id'] ?? 0),      // Convertir a número
    'medico_id'       => (int)($_POST['medico_id'] ?? 0),        // Convertir a número
    'especialidad_id' => (int)($_POST['especialidad_id'] ?? 0),  // Convertir a número
    'fecha'           => $_POST['fecha'] ?? '',                  // Texto (fecha)
    'hora'            => $_POST['hora'] ?? '',                   // Texto (hora)
    'estado'          => $_POST['estado'] ?? 'pendiente',        // Texto (estado)
    'notas'           => trim($_POST['notas'] ?? '')             // Texto sin espacios extra
  ];

  /**
 * trim() elimina espacios en blanco al principio y final.
   * Ejemplo:
   *   trim("  hola  ") → "hola"
   * 
   * ¿Por qué en notas? Para evitar guardar solo espacios vacíos.
   */

  // --------------------------------------------------
  // ACCIÓN: EDITAR CITA (UPDATE)
  // --------------------------------------------------
  if ($action === 'edit' && $id) {

    /**
     * try { ... } catch (Exception $e) { ... }
     * 
     * "try" = "INTENTA ejecutar este código"
     * "catch" = "SI HAY UN ERROR, captúralo aquí"
     * 
     * $e es el objeto Exception con información del error.
     * $e->getMessage() devuelve el texto del error.
     * 
     * ¿Por qué? Para mostrar un mensaje bonito al usuario
     * en vez de un error técnico de PHP.
     */
    try {
      /**
       * SQL UPDATE - Modificar una cita existente
       * 
       * :nombre_columna = Marcadores de posición con NOMBRE
       * (a diferencia de ? que son posicionales)
       * 
       * Ejemplo:
       *   UPDATE citas SET paciente_id=3, fecha='2024-06-15' WHERE id=5
       */
      $sql = "UPDATE citas SET 
                  paciente_id=:paciente_id, 
                  medico_id=:medico_id, 
                  especialidad_id=:especialidad_id, 
                  fecha=:fecha, 
                  hora=:hora, 
                  estado=:estado, 
                  notas=:notas 
              WHERE id=:id";

      // Añadir el ID a los datos para el WHERE
      $data['id'] = $id;

      // Ejecutar la consulta
      $db->execute($sql, $data);

      // Mensaje de éxito
      $message = 'Cita actualizada exitosamente';
      $messageType = 'success';

      // Volver al listado después de editar
      $action = 'list';
    } catch (Exception $e) {
      // Capturar el error y mostrar mensaje
      $message = 'Error al actualizar la cita: ' . $e->getMessage();
      $messageType = 'error';
    }
  }
}

// --------------------------------------------------
// ACCIÓN: ELIMINAR CITA (DELETE)
// --------------------------------------------------
if ($action === 'delete' && $id) {
  try {
    /**
     * SQL DELETE - Borrar una cita
     * 
     * Ejemplo:
     *   DELETE FROM citas WHERE id = 5
     */
    $sql = "DELETE FROM citas WHERE id = :id";

    $db->execute($sql, ['id' => $id]);

    $message = 'Cita eliminada exitosamente';
    $messageType = 'success';

    // Volver al listado después de eliminar
    $action = 'list';
  } catch (Exception $e) {
    $message = 'Error al eliminar la cita: ' . $e->getMessage();
    $messageType = 'error';
  }
}

// ============================================================
// PASO 5: CARGAR DATOS PARA LOS SELECTS Y LA TABLA
// ============================================================

/**
 * $db->fetchAll($sql, $params) → Ejecuta SELECT y devuelve array
 * 
 * Los datos se cargan SIEMPRE, aunque no se usen todos.
 * En proyectos más grandes, se optimiza para cargar solo lo necesario.
 */

// Lista de especialidades (para el formulario de edición)
$especialidades = $db->fetchAll(
  "SELECT * FROM especialidades WHERE activo = true ORDER BY nombre"
);

// Lista de médicos con el nombre de su especialidad (JOIN)
// LEFT JOIN une tablas aunque no haya coincidencia
$medicos = $db->fetchAll(
  "SELECT m.*, e.nombre as especialidad_nombre 
    FROM medicos m 
    LEFT JOIN especialidades e ON m.especialidad_id = e.id 
    WHERE m.activo = true 
    ORDER BY m.nombre"
);

// Lista de pacientes (usuarios con rol 'paciente')
$pacientes = $db->fetchAll(
  "SELECT * FROM usuarios WHERE rol = 'paciente' ORDER BY nombre"
);

/**
 * Inicializar variables vacías.
 * Así PHP no da error "undefined variable" más adelante.
 */
$citas = [];
$citaEdit = null;

/**
 * empty($variable) → ¿Está vacía?
 * Devuelve true si:
 *   - Es null
 *   - Es string vacío ''
 *   - Es array vacío []
 */

// --------------------------------------------------
// CARGAR CITAS PARA LISTADO
// --------------------------------------------------
if ($action === 'list') {

  $citas = $db->fetchAll(
    "SELECT c.*, 
                u.nombre as paciente_nombre, 
                u.apellidos as paciente_apellidos,
                m.nombre as medico_nombre, 
                m.apellidos as medico_apellidos,
                e.nombre as especialidad_nombre
      FROM citas c
            LEFT JOIN usuarios u ON c.paciente_id = u.id
            LEFT JOIN medicos m ON c.medico_id = m.id
            LEFT JOIN especialidades e ON c.especialidad_id = e.id
      ORDER BY c.fecha DESC, c.hora DESC"
  );

// --------------------------------------------------
// CARGAR DATOS DE UNA CITA PARA EDITAR
// --------------------------------------------------
} elseif ($action === 'edit' && $id) {

  /**
   * Para editar, necesitamos los datos actuales de la cita.
   * Usamos el PDO directamente porque fetchAll() no permite
   * obtener una sola fila de forma directa.
   */
  $stmt = $pdo->prepare("SELECT * FROM citas WHERE id = ?");
  $stmt->execute([$id]);

  /**
   * fetch() vs fetchAll():
   *   - fetchAll() → Devuelve TODAS las filas en un array
   *   - fetch() → Devuelve SOLO la primera fila (o null si no hay)
   * 
   * Como buscamos por ID (único), solo hay 1 resultado.
   */
  $citaEdit = $stmt->fetch(PDO::FETCH_ASSOC);

  /**
   * $citaEdit ahora contiene:
   *   [
   *     'id' => 5,
   *     'paciente_id' => 3,
   *     'medico_id' => 2,
   *     'fecha' => '2024-06-15',
   *     ...
   *   ]
   * 
   * O null si no existe la cita con ese ID.
   */
}

/**
 * Array con los estados posibles de una cita.
 * Se usa para generar el <select> en el formulario.
 */
$estados = ['pendiente', 'confirmada', 'completada', 'cancelada'];


// ============================================================
// PASO 6: GENERAR EL HTML
// ============================================================
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Citas - <?= APP_NOMBRE ?></title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/crud-citas.css">
</head>

<body>
  <!-- Incluir el header con navegación -->
  <?php include 'includes/header.php'; ?>

  <main>
    <section class="crud-container">

      <!-- ============================================
          MENSAJES DE ÉXITO O ERROR
      ============================================= -->
      <?php if ($message): ?>
        <!-- 
          La clase del div cambia según success o error.
          Esto permite estilarlos diferente en CSS.
        -->
        <div class="message <?= $messageType ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <!-- ============================================
          VISTA 1: LISTADO DE CITAS
      ============================================= -->
      <?php if ($action === 'list'): ?>

        <div class="crud-header">
          <h1>Gestión de Citas</h1>
          <!-- Enlace para crear una nueva cita -->
          <a href="cita-online.php" class="btn btn-primary">+ Nueva Cita</a>
        </div>

        <!-- ¿No hay citas? Mostrar mensaje -->
        <?php if (empty($citas)): ?>
          <div class="message" style="background: #e7f3ff; color: #004085; border-left: 5px solid #007bff;">
            No hay citas registradas.
            <a href="cita-online.php" style="color: #007bff;">Crear primera cita</a>
          </div>

          <!-- ¿Hay citas? Mostrar tabla -->
        <?php else: ?>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Paciente</th>
                  <th>Médico</th>
                  <th>Especialidad</th>
                  <th>Fecha</th>
                  <th>Hora</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <!-- 
                  foreach = BUCLE para recorrer arrays
                  Por cada cita en $citas, genera una fila <tr>
                -->
                <?php foreach ($citas as $cita): ?>
                  <tr>
                    <!-- Concatenar nombre + apellidos -->
                    <td><?= htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellidos']) ?></td>
                    <td><?= htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellidos']) ?></td>
                    <td><?= htmlspecialchars($cita['especialidad_nombre']) ?></td>

                    <!-- Formatear fecha: 2024-06-15 → 15/06/2024 -->
                    <td><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>

                    <!-- Formatear hora: 14:30:00 → 14:30 -->
                    <td><?= date('H:i', strtotime($cita['hora'])) ?></td>

                    <!-- Badge de estado con clase dinámica -->
                    <td>
                      <span class="estado-badge estado-<?= $cita['estado'] ?>">
                        <?= $cita['estado'] ?>
                      </span>
                    </td>

                    <!-- Botones de acción -->
                    <td class="actions">
                      <!-- Editar: Enlace con parámetros GET -->
                      <a href="?action=edit&id=<?= $cita['id'] ?>" class="btn btn-secondary btn-sm">
                        Editar
                      </a>

                      <!-- Eliminar: Confirmación con JavaScript -->
                      <a href="?action=delete&id=<?= $cita['id'] ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('¿Eliminar esta cita?')">
                        Eliminar
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>


        <!-- ============================================
            VISTA 2: FORMULARIO DE EDICIÓN
        ============================================= -->
      <?php elseif ($action === 'edit'): ?>

        <?php
        /**
         * Cargar información del paciente para mostrar nombre.
         * Necesitamos esto porque en el formulario el campo
         * paciente es "disabled" (solo lectura).
         */
        if (isset($citaEdit['paciente_id'])) {
          $stmtPaciente = $pdo->prepare(
            "SELECT nombre, apellidos FROM usuarios WHERE id = ?"
          );
          $stmtPaciente->execute([$citaEdit['paciente_id']]);
          $pacienteInfo = $stmtPaciente->fetch(PDO::FETCH_ASSOC);
        }
        ?>

        <div class="form-card">
          <h2>Editar Cita</h2>

          <!-- 
            method="POST" = Los datos van en el cuerpo de la petición
            El servidor detecta POST y procesa el formulario.
          -->
          <form method="POST" class="form-grid">

            <!-- Campo paciente (solo lectura, no se puede cambiar) -->
            <div class="form-group">
              <label>Paciente</label>
              <!-- disabled = No editable, pero visible -->
              <!-- El valor real se envía con el input hidden debajo -->
              <input type="text"
                value="<?= htmlspecialchars(($pacienteInfo['nombre'] ?? '') . ' ' . ($pacienteInfo['apellidos'] ?? '')) ?>"
                disabled>
              <!-- Hidden field: Envía el ID aunque el campo esté deshabilitado -->
              <input type="hidden" name="paciente_id" value="<?= $citaEdit['paciente_id'] ?>">
            </div>

            <!-- Selector de especialidad -->
            <div class="form-group">
              <label>Especialidad *</label>
              <select name="especialidad_id" id="especialidadSelect" required>
                <option value="">Seleccionar especialidad</option>
                <?php foreach ($especialidades as $e): ?>
                  <!-- 
                    selected = Opción marcada por defecto
                    Comparamos el ID de la opción con el ID guardado en la cita
                  -->
                  <option value="<?= $e['id'] ?>" <?= ($citaEdit['especialidad_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Selector de médico -->
            <div class="form-group">
              <label>Médico *</label>
              <select name="medico_id" id="medicoSelect" required>
                <option value="">Seleccionar médico</option>
                <?php foreach ($medicos as $m): ?>
                  <!-- 
                    data-especialidad = Atributo data HTML
                    Se usa en JavaScript para filtrar médicos
                    por especialidad (ver crud-citas.js)
                  -->
                  <option value="<?= $m['id'] ?>"
                    data-especialidad="<?= $m['especialidad_id'] ?>"
                    <?= ($citaEdit['medico_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nombre'] . ' ' . $m['apellidos']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Selector de estado -->
            <div class="form-group">
              <label>Estado</label>
              <select name="estado">
                <?php foreach ($estados as $est): ?>
                  <option value="<?= $est ?>" <?= ($citaEdit['estado'] ?? 'pendiente') == $est ? 'selected' : '' ?>>
                    <?= ucfirst($est) ?>
                    <!-- ucfirst() = Primera letra mayúscula: "pendiente" → "Pendiente" -->
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Campo fecha -->
            <div class="form-group">
              <label>Fecha *</label>
              <input type="date" name="fecha" value="<?= $citaEdit['fecha'] ?? '' ?>" required>
            </div>

            <!-- Campo hora -->
            <div class="form-group">
              <label>Hora *</label>
              <input type="time" name="hora" value="<?= $citaEdit['hora'] ?? '' ?>" required>
            </div>

            <!-- Campo notas (textarea) -->
            <div class="form-group full-width">
              <label>Notas</label>
              <textarea name="notas"><?= htmlspecialchars($citaEdit['notas'] ?? '') ?></textarea>
            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
              <button type="submit" class="btn btn-success">Actualizar Cita</button>
              <a href="citas-crud.php" class="btn btn-secondary">Cancelar</a>
            </div>
          </form>
        </div>

      <?php endif; ?>

    </section>
  </main>

  <!-- Incluir el footer -->
  <?php include 'includes/footer.php'; ?>

  <!-- Script para filtrar médicos por especialidad -->
  <script src="js/crud-citas.js"></script>
</body>

</html>
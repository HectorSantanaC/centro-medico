<?php
/**
 * =====================================================
 * cita-online.php
 * FORMULARIO DE RESERVA DE CITA PARA PACIENTES
 * =====================================================
 * 
 * Este archivo tiene 3 funciones principales:
 *   1. AJAX → Devolver lista de médicos según especialidad
 *   2. POST → Guardar una nueva cita en la base de datos
 *   3. GET  → Mostrar el formulario de reserva
 * 
 * CONCEPTOS CLAVE QUE APRENDERÁS:
 *   - AJAX (comunicación asíncrona con el servidor)
 *   - Cabeceras HTTP (header())
 *   - JSON (formato de datos para APIs)
 *   - exit() para terminar la ejecución
 * 
 * FLUJO NORMAL:
 *   1. Usuario abre la página → Se muestra el formulario
 *   2. Usuario elige especialidad → JavaScript pide médicos por AJAX
 *   3. Usuario llena todo y envía → PHP guarda la cita
 *   4. Se muestra mensaje de confirmación
 * 
 * =====================================================
 */

// Cargar la configuración de la base de datos
require_once __DIR__ . '/config/Database.php';

// ============================================================
// VERIFICAR QUE EL USUARIO ESTÁ LOGUEADO
// ============================================================

/**
 * Solo los usuarios logueados pueden reservar citas.
 * Si no está logueado, redirigir al login.
 */
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

/**
 * __DIR__ = Ruta de la carpeta donde está este archivo.
 * 
 * Ejemplo:
 *   Si este archivo está en: /htdocs/centro-medico/cita-online.php
 *   __DIR__ = /htdocs/centro-medico
 * 
 * __DIR__ . '/config/Database.php' = /htdocs/centro-medico/config/Database.php
 * 
 * ¿Por qué __DIR__?
 *   Si mueves este archivo a otra carpeta, la ruta sigue funcionando.
 *   Es más seguro que escribir la ruta absoluta a mano.
 */

/**
 * Conectar a la base de datos.
 * Más info: Ver config/Database.php
 */
$db = Database::getInstance();

/**
 * Título de la página (para el header).
 * Se usa en includes/header.php para el <title>
 */
$page_title = 'Reservar Cita';


// ============================================================
// FUNCIONALIDAD 1: AJAX - DEVOLVER MÉDICOS POR ESPECIALIDAD
// ============================================================

/**
 * ¿Qué es AJAX?
 *   AJAX = Asynchronous JavaScript And XML
 *   Es una técnica para pedir datos al servidor SIN recargar la página.
 * 
 * ¿Cómo funciona aquí?
 *   1. JavaScript detecta cambio de especialidad
 *   2. JS pide médicos a este archivo con: cita-online.php?get_medicos=1&especialidad_id=3
 *   3. PHP detecta que es una petición AJAX (por el parámetro get_medicos)
 *   4. PHP devuelve JSON con los médicos
 *   5. JavaScript recibe JSON y actualiza el select de médicos
 */

/**
 * isset($variable) → ¿EXISTE esta variable?
 * && = AND lógico (ambas condiciones deben ser true)
 * 
 * Condición completa:
 *   ¿Existe $_GET['get_medicos']? Y ¿es igual a '1'?
 */
if (isset($_GET['get_medicos']) && $_GET['get_medicos'] == '1') {
    
    /**
     * header() = Enviar una cabecera HTTP al navegador.
     * 
     * 'Content-Type: application/json'
     *   Le dice al navegador: "Lo que viene es JSON, trátalo como datos"
     * 
     * SIEMPRE hay que enviar Content-Type antes de echo con JSON.
     * Si no, el navegador puede interpretar mal los datos.
     */
    header('Content-Type: application/json');
    
    /**
     * $_GET['especialidad_id'] = El ID de especialidad que envían por URL.
     * (int) = Convertir a número entero (seguridad).
     * ?? 0 = Si no existe, usar 0.
     * 
     * Ejemplo de URL:
     *   cita-online.php?get_medicos=1&especialidad_id=3
     */
    $espId = (int)($_GET['especialidad_id'] ?? 0);
    
    /**
     * Solo buscar médicos si el ID de especialidad es válido (> 0).
     * Si espId es 0 o negativo, devolvemos array vacío [].
     */
    if ($espId > 0) {
        
        /**
         * Buscar médicos de la especialidad elegida.
         * 
         * SQL:
         *   SELECT m.id, m.nombre || ' ' || m.apellidos as nombre_completo
         *     → Seleccionar ID y concatenar nombre + apellidos
         *   FROM medicos m
         *     → Tabla de médicos (alias m)
         *   WHERE m.especialidad_id = ? AND m.activo = true
         *     → Solo médicos de esta especialidad y activos
         *   ORDER BY m.apellidos
         *     → Ordenar por apellidos
         * 
         * || es el operador de concatenación en PostgreSQL.
         * Equivale a CONCAT en MySQL.
         */
        $medicos = $db->fetchAll(
            "SELECT m.id, m.nombre || ' ' || m.apellidos as nombre_completo
             FROM medicos m
             WHERE m.especialidad_id = ? AND m.activo = true
             ORDER BY m.apellidos",
            [$espId]
        );
        
        /**
         * json_encode() = Convertir array PHP a texto JSON.
         * 
         * Ejemplo:
         *   $medicos = [
         *     ['id' => 1, 'nombre_completo' => 'Juan Pérez'],
         *     ['id' => 2, 'nombre_completo' => 'María García']
         *   ]
         *   
         *   json_encode($medicos) =
         *   '[{"id":"1","nombre_completo":"Juan Pérez"},{"id":"2","nombre_completo":"María García"}]'
         */
        echo json_encode($medicos);
        
    } else {
        // Si no hay especialidad válida, devolver array vacío
        echo json_encode([]);
    }
    
    /**
     * exit = TERMINAR la ejecución del script INMEDIATAMENTE.
     * 
     * ¿Por qué?
     *   Esta parte es AJAX. El JavaScript espera JSON como respuesta.
     *   Si no hacemos exit, PHP seguiría ejecutando el resto del archivo
     *   (el HTML del formulario) y lo enviaría junto con el JSON.
     *   El JavaScript fallaría porque no puede parsear HTML como JSON.
     * 
     * En resumen: Para AJAX, SIEMPRE haz exit después de responder.
     */
    exit;
}


// ============================================================
// FUNCIONALIDAD 2: GUARDAR NUEVA CITA (POST)
// ============================================================

/**
 * $_POST contiene los datos enviados por el formulario.
 * 
 * if ($_POST) = "¿Hay datos en $_POST?"
 * Es una forma corta de decir: isset($_POST) && count($_POST) > 0
 */
if ($_POST) {
    
    /**
     * Recoger los datos del formulario.
     * 
     * (int) = Convertir a número entero.
     *   Ejemplo: "3" → 3, "abc" → 0
     * 
     * $_POST['medico_id'] = Valor del campo <select name="medico_id">
     */
    $medico_id = (int)$_POST['medico_id'];
    $especialidad_id = (int)$_POST['especialidad_id'];
    $fecha_cita = $_POST['fecha_cita'];      // Texto: '2024-06-15'
    $hora_cita = $_POST['hora_cita'];        // Texto: '14:30'
    
    /**
     * Obtener el ID del paciente logueado.
     * 
     * $_SESSION['usuario_id'] = ID del usuario (guardado al hacer login).
     * Ahora es OBLIGATORIO porque ya verificamos arriba que está logueado.
     */
    $paciente_id = $_SESSION['usuario_id'];
    
    /**
     * SQL INSERT para crear una nueva cita.
     * 
     * INSERT INTO citas (columnas) VALUES (valores) RETURNING id
     *   - INSERT INTO = Insertar en la tabla
     *   - columnas = Lista de columnas a insertar
     *   - VALUES = Los valores correspondientes
     *   - RETURNING id = Devolver el ID del registro creado (PostgreSQL)
     * 
     * ? = Marcadores de posición (placeholders).
     * Los valores se pasan en el array $db->insert().
     * Esto evita SQL Injection.
     */
    $sql = "INSERT INTO citas (paciente_id, medico_id, especialidad_id, fecha, hora, estado) 
            VALUES (?, ?, ?, ?, ?, 'pendiente') RETURNING id";
    
    /**
     * $db->insert() ejecuta el INSERT y devuelve el ID.
     * El ID se genera automáticamente (SERIAL en PostgreSQL).
     */
    $cita_id = $db->insert($sql, [
        $paciente_id,
        $medico_id,
        $especialidad_id,
        $fecha_cita,
        $hora_cita
    ]);
    
    /**
     * Mensaje de éxito para mostrar después.
     * Se usa más abajo en el HTML.
     */
    $mensaje_exito = "✅ Cita #$cita_id RESERVADA!<br>📅 $fecha_cita $hora_cita";
}


// ============================================================
// FUNCIONALIDAD 3: CARGAR DATOS PARA EL FORMULARIO
// ============================================================

/**
 * Obtener lista de especialidades para el primer select.
 * 
 * WHERE activo = true
 *   → Solo especialidades que están activas.
 *   → Permite "desactivar" especialidades sin borrarlas de la BD.
 * 
 * ORDER BY nombre
 *   → Ordenar alfabéticamente.
 */
$especialidades = $db->fetchAll(
    "SELECT id, nombre FROM especialidades WHERE activo = true ORDER BY nombre"
);

?>


<!-- ============================================================
     INCLUIR HEADER (Navegación y estructura HTML inicial)
     ============================================================ -->
<?php include './includes/header.php'; ?>


<!-- ============================================================
     MENSAJE DE ÉXITO (solo visible después de crear cita)
     ============================================================ -->
<?php if (isset($mensaje_exito)): ?>
    <div class="cita-success">
        <div style="font-size: 1.5rem; margin-bottom: 1rem;">✅</div>
        <?= $mensaje_exito ?>
        <br>
        <a href="">← Reservar otra cita</a>
    </div>
<?php endif; ?>


<!-- ============================================================
     FORMULARIO DE RESERVA DE CITA
     ============================================================ -->
<section class="cita-section">
    <div class="cita-container">
        
        <!-- Encabezado -->
        <div class="cita-header">
            <h2>Reserva tu cita en línea</h2>
            <p>Selecciona especialidad, médico, fecha y hora que se ajuste a tu disponibilidad</p>
            
            <!-- Enlace a ver citas (mis-citas.php para pacientes) -->
            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                <a href="citas-crud.php" class="btn-ver-citas" 
                   style="margin-top: 10px; display: inline-block; background: #2c5282; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">
                   📋 Gestionar Citas
                </a>
            <?php else: ?>
                <a href="mis-citas.php" class="btn-ver-citas" 
                   style="margin-top: 10px; display: inline-block; background: #2c5282; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">
                   📋 Mis Citas
                </a>
            <?php endif; ?>
        </div>

        <!-- 
            FORMULARIO
            method="POST" = Los datos se envían al propio archivo (action="")
            action="" = Misma URL actual
        -->
        <form method="POST" action="" class="cita-form" id="citaForm">
            <input type="hidden" name="ajax" value="1">
            <!-- Hidden field: Por si en el futuro necesitamos distinguir AJAX de POST normal -->

            <!-- ----------------------------------------
                 PASO 1: SELECCIONAR ESPECIALIDAD
                 ---------------------------------------- -->
            <div class="cita-paso">
                <div class="paso-numero">1</div>
                <div class="form-group">
                    <label>Especialidad <span class="required">*</span></label>
                    <div class="select-wrapper">
                        <select name="especialidad_id" id="especialidad_id" required>
                            <!-- Primera opción vacía (placeholder) -->
                            <option value="">Selecciona especialidad...</option>
                            
                            <!-- Generar opciones desde la base de datos -->
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= $esp['id'] ?>">
                                    <?= htmlspecialchars($esp['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ----------------------------------------
                 PASO 2: SELECCIONAR MÉDICO
                 ---------------------------------------- -->
            <div class="cita-paso">
                <div class="paso-numero">2</div>
                <div class="form-group">
                    <label>Médico <span class="required">*</span></label>
                    <div class="select-wrapper">
                        <!-- 
                            Este select se ACTUALIZA dinámicamente con JavaScript.
                            Inicia con una opción placeholder.
                        -->
                        <select name="medico_id" id="medico_id">
                            <option value="">Selecciona especialidad primero</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ----------------------------------------
                 PASO 3 y 4: FECHA Y HORA (lado a lado)
                 ---------------------------------------- -->
            <div class="cita-pasos-row">
                
                <!-- Fecha -->
                <div class="cita-paso">
                    <div class="paso-numero">3</div>
                    <div class="form-group">
                        <label>Fecha <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="date" 
                                   name="fecha_cita" 
                                   id="fecha_cita"
                                   min="<?= date('Y-m-d') ?>">
                            <!-- 
                                min = Fecha mínima seleccionable
                                date('Y-m-d') = Fecha de hoy en formato AAAA-MM-DD
                                Ejemplo: 2024-06-15
                            -->
                        </div>
                    </div>
                </div>

                <!-- Hora -->
                <div class="cita-paso">
                    <div class="paso-numero">4</div>
                    <div class="form-group">
                        <label>Hora <span class="required">*</span></label>
                        <div class="select-wrapper">
                            <select name="hora_cita">
                                <option value="">Selecciona hora...</option>
                                <!-- Horas de la clínica (9:00 a 12:00 y 14:00 a 17:30) -->
                                <option value="09:00">09:00</option>
                                <option value="09:30">09:30</option>
                                <option value="10:00">10:00</option>
                                <option value="10:30">10:30</option>
                                <option value="11:00">11:00</option>
                                <option value="11:30">11:30</option>
                                <option value="12:00">12:00</option>
                                <option value="14:00">14:00</option>
                                <option value="14:30">14:30</option>
                                <option value="15:00">15:00</option>
                                <option value="15:30">15:30</option>
                                <option value="16:00">16:00</option>
                                <option value="16:30">16:30</option>
                                <option value="17:00">17:00</option>
                                <option value="17:30">17:30</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ----------------------------------------
                 BOTÓN DE ENVIAR
                 ---------------------------------------- -->
            <div class="cita-actions">
                <button type="submit" class="btn-reservar">
                    <span>Confirmar Cita</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</section>


<!-- ============================================================
     INCLUIR FOOTER Y JAVASCRIPT
     ============================================================ -->
<?php include './includes/footer.php'; ?>

<!-- 
    Script para el formulario de citas.
    Este archivo JavaScript hace:
      1. Detectar cambios en el select de especialidad
      2. Pedir médicos por AJAX al servidor
      3. Actualizar dinámicamente el select de médicos
-->
<script src="js/cita-online.js"></script>

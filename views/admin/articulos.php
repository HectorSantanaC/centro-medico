<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Artículos</title>
  <link rel="stylesheet" href="css/admin.css">
  <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
</head>

<body>
  <?php require_once __DIR__ . '/../layout/navbar-admin.php'; ?>

  <main class="main-content">
    <div id="message-container"></div>

    <div id="seccion-lista">
      <div class="page-header">
        <h1>📰 Gestión de Artículos</h1>
        <button id="btn-crear" class="btn btn-primary">+ Nuevo Artículo</button>
      </div>

      <div class="filtros-container">
        <div class="filtros-row">
          <div class="filtro-group">
            <label>Buscar</label>
            <input type="text" id="filtro-titulo" placeholder="Título...">
          </div>
          <div class="filtro-group">
            <label>Tópico</label>
            <select id="filtro-topico">
              <option value="">Todos</option>
            </select>
          </div>
          <div class="filtro-group">
            <label>Desde</label>
            <input type="date" id="filtro-fecha-desde">
          </div>
          <div class="filtro-group">
            <label>Hasta</label>
            <input type="date" id="filtro-fecha-hasta">
          </div>
        </div>
        <div class="filtros-actions">
          <button id="btn-filtrar" class="btn btn-primary btn-sm">Buscar</button>
          <button id="btn-limpiar-filtros" class="btn btn-secondary btn-sm">Limpiar</button>
        </div>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Título</th>
              <th>Tópico</th>
              <th>Fecha</th>
              <th>Caducidad</th>
              <th>Publicado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tabla-articulos">
            <tr><td colspan="6" class="loading">Cargando...</td></tr>
          </tbody>
        </table>
      </div>

      <div class="pagination-container">
        <div class="pagination-info" id="pagination-info"></div>
        <div class="pagination-controls" id="pagination-controls"></div>
      </div>
    </div>

    <div id="seccion-form" class="hidden">
      <a href="#" id="btn-volver-lista" class="back-link">← Volver al listado</a>

      <div class="form-card">
        <h2 id="form-titulo">Crear Artículo</h2>

        <form id="form-articulo" enctype="multipart/form-data">
          <input type="hidden" id="articulo-id" value="">

          <div class="form-group">
            <label>Título *</label>
            <input type="text" id="articulo-titulo" required>
          </div>

          <div class="form-group">
            <label>Tópico</label>
            <select id="articulo-topico">
              <option value="">Sin tópico</option>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Fecha contenido</label>
              <input type="date" id="articulo-fecha-contenido">
            </div>

            <div class="form-group">
              <label>Fecha caducidad</label>
              <input type="date" id="articulo-fecha-caducidad">
            </div>
          </div>

          <div class="form-group">
            <label>Contenido reducido</label>
            <textarea id="articulo-contenido-reducido" class="editor-html" rows="5"></textarea>
          </div>

          <div class="form-group">
            <label>Contenido completo</label>
            <textarea id="articulo-contenido-completo" class="editor-html" rows="10"></textarea>
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" id="articulo-publicado" checked>
              Publicar
            </label>
          </div>

          <div class="form-group">
            <label>Notas</label>
            <textarea id="articulo-notas" rows="2" placeholder="Notas privadas solo visibles en admin"></textarea>
          </div>

          <div class="form-group">
            <label>Imagen</label>
            <p id="imagen-actual" class="help-text"></p>
            <input type="file" id="articulo-imagen-file" accept="image/*">
            <input type="hidden" id="articulo-imagen" value="">
          </div>

          <div class="form-group">
            <label>URL imagen externa</label>
            <input type="text" id="articulo-imagen-url" placeholder="https://ejemplo.com/imagen.jpg">
          </div>

          <h3 class="info-seo">Información SEO</h3>

          <div class="form-group">
            <label>Título SEO</label>
            <input type="text" id="articulo-seo-titulo">
          </div>

          <div class="form-group">
            <label>Descripción SEO</label>
            <textarea id="articulo-seo-descripcion" rows="2"></textarea>
          </div>

          <div class="form-group">
            <label>Palabras clave (separadas por comas)</label>
            <input type="text" id="articulo-seo-palabras" placeholder="salud, medicina, consejos">
          </div>

          <div class="form-actions">
            <button type="submit" id="btn-guardar" class="btn btn-primary">Crear Artículo</button>
            <button type="button" id="btn-cancelar" class="btn btn-secondary">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script src="js/admin-articulos.js"></script>
  <script>
    CKEDITOR.replaceAll(function(textarea, config) {
      if (textarea.className.indexOf('editor-html') !== -1) {
        config.versionCheck = false;
        config.language = 'es';
        config.height = 170;
        return true;
      }
      return false;
    });
  </script>
</body>
</html>
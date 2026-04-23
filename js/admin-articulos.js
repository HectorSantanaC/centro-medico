const AdminArticulos = {
  apiUrl: 'api/articulos.php',
  apiTopicos: 'api/topicos.php',
  currentPage: 1,
  perPage: 10,
  totalItems: 0,

  init() {
    this.cargarTopicos();
    this.cargarArticulos();
    this.bindEvents();
  },

  async cargarTopicos() {
    try {
      const response = await fetch(this.apiTopicos, { credentials: 'same-origin' });
      const result = await response.json();
      const topicos = result.data || result;
      
      const selects = ['articulo-topico', 'filtro-topico'];
      selects.forEach(id => {
        const select = document.getElementById(id);
        if (!select) return;
        const defaultOpt = id === 'articulo-topico' 
          ? '<option value="">Sin tópico</option>' 
          : '<option value="">Todos</option>';
        select.innerHTML = defaultOpt + topicos.map(t => 
          `<option value="${t.id}">${this.escapeHtml(t.nombre)}</option>`
        ).join('');
      });
    } catch (error) {
      console.error('Error cargando tópicos:', error);
    }
  },

  getFiltros() {
    return {
      titulo: document.getElementById('filtro-titulo').value || null,
      topico_id: document.getElementById('filtro-topico').value || null,
      fecha_desde: document.getElementById('filtro-fecha-desde').value || null,
      fecha_hasta: document.getElementById('filtro-fecha-hasta').value || null
    };
  },

  buildQueryString() {
    const filtros = this.getFiltros();
    const params = new URLSearchParams();
    params.append('page', this.currentPage);
    params.append('per_page', this.perPage);
    Object.entries(filtros).forEach(([key, value]) => {
      if (value) params.append(key, value);
    });
    return params.toString();
  },

  bindEvents() {
    document.getElementById('btn-crear')?.addEventListener('click', () => this.mostrarFormulario());
    
    document.getElementById('btn-volver-lista')?.addEventListener('click', (e) => {
      e.preventDefault();
      this.mostrarLista();
    });

    document.getElementById('btn-cancelar')?.addEventListener('click', () => this.mostrarLista());

    document.getElementById('btn-filtrar')?.addEventListener('click', () => {
      this.currentPage = 1;
      this.cargarArticulos();
    });

    document.getElementById('btn-limpiar-filtros')?.addEventListener('click', () => {
      this.limpiarFiltros();
      this.cargarArticulos();
    });

    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('btn-editar')) {
        this.mostrarFormulario(e.target.dataset.id);
      }
      if (e.target.classList.contains('btn-eliminar')) {
        this.confirmarEliminar(e.target.dataset.id);
      }
    });

    document.getElementById('form-articulo')?.addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarArticulo();
    });
  },

  mostrarLista() {
    document.getElementById('seccion-lista').classList.remove('hidden');
    document.getElementById('seccion-form').classList.add('hidden');
    this.cargarArticulos();
  },

  mostrarFormulario(id = null) {
    document.getElementById('seccion-lista').classList.add('hidden');
    document.getElementById('seccion-form').classList.remove('hidden');
    
    document.getElementById('form-titulo').textContent = id ? 'Editar Artículo' : 'Crear Artículo';
    document.getElementById('btn-guardar').textContent = id ? 'Guardar Cambios' : 'Crear Artículo';
    document.getElementById('articulo-id').value = id || '';
    
    this.limpiarFormulario();

    if (id) {
      this.cargarArticulo(id);
    } else {
      if (CKEDITOR.instances['articulo-contenido-reducido']) {
        CKEDITOR.instances['articulo-contenido-reducido'].setData('');
      }
      if (CKEDITOR.instances['articulo-contenido-completo']) {
        CKEDITOR.instances['articulo-contenido-completo'].setData('');
      }
    }
  },

  limpiarFormulario() {
    document.getElementById('articulo-titulo').value = '';
    document.getElementById('articulo-topico').value = '';
    document.getElementById('articulo-fecha-contenido').value = '';
    document.getElementById('articulo-fecha-caducidad').value = '';
    document.getElementById('articulo-publicado').checked = true;
    document.getElementById('articulo-notas').value = '';
    document.getElementById('articulo-imagen-file').value = '';
    document.getElementById('articulo-imagen').value = '';
    document.getElementById('articulo-imagen-url').value = '';
    document.getElementById('articulo-seo-titulo').value = '';
    document.getElementById('articulo-seo-descripcion').value = '';
    document.getElementById('articulo-seo-palabras').value = '';
    
    if (CKEDITOR.instances['articulo-contenido-reducido']) {
      CKEDITOR.instances['articulo-contenido-reducido'].setData('');
    }
    if (CKEDITOR.instances['articulo-contenido-completo']) {
      CKEDITOR.instances['articulo-contenido-completo'].setData('');
    }
    
    const imgActual = document.getElementById('imagen-actual');
    imgActual.classList.add('hidden');
    imgActual.textContent = '';
  },

  async cargarArticulo(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`, { credentials: 'same-origin' });
      if (!response.ok) {
        this.mostrarMensaje('Artículo no encontrado', 'error');
        return;
      }
      const a = await response.json();
      
      document.getElementById('articulo-id').value = a.id || id;
      document.getElementById('articulo-titulo').value = a.titulo || '';
      document.getElementById('articulo-topico').value = a.topico || '';
      document.getElementById('articulo-fecha-contenido').value = a.fecha_contenido || '';
      document.getElementById('articulo-fecha-caducidad').value = a.fecha_caducidad || '';
      document.getElementById('articulo-publicado').checked = a.publicado !== false;
      document.getElementById('articulo-notas').value = a.notas || '';
      document.getElementById('articulo-imagen').value = a.imagen || '';
      document.getElementById('articulo-imagen-url').value = a.imagen_url || '';
      document.getElementById('articulo-seo-titulo').value = a.seo_titulo || '';
      document.getElementById('articulo-seo-descripcion').value = a.seo_descripcion || '';
      document.getElementById('articulo-seo-palabras').value = a.seo_palabras_clave || '';

      if (CKEDITOR.instances['articulo-contenido-reducido']) {
        CKEDITOR.instances['articulo-contenido-reducido'].setData(a.contenido_reducido || '');
      }
      if (CKEDITOR.instances['articulo-contenido-completo']) {
        CKEDITOR.instances['articulo-contenido-completo'].setData(a.contenido_completo || '');
      }

      if (a.imagen) {
        const imgActual = document.getElementById('imagen-actual');
        imgActual.classList.remove('hidden');
        imgActual.textContent = `Imagen actual: ${a.imagen}`;
      }
    } catch (error) {
      this.mostrarMensaje('Error al cargar artículo', 'error');
    }
  },

  async cargarArticulos() {
    const tbody = document.getElementById('tabla-articulos');
    tbody.innerHTML = '<tr><td colspan="6" class="loading">Cargando...</td></tr>';
    
    try {
      const queryString = this.buildQueryString();
      const response = await fetch(`${this.apiUrl}?${queryString}`, { credentials: 'same-origin' });
      const result = await response.json();
      const articulos = result.data || [];
      this.totalItems = result.pagination?.total || 0;
      
      if (articulos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;">No hay artículos</td></tr>';
        this.renderPagination();
        return;
      }
      
      tbody.innerHTML = articulos.map(a => `
        <tr>
          <td>${this.escapeHtml(a.titulo)}</td>
          <td>${this.escapeHtml(a.topico_nombre || '-')}</td>
          <td>${this.formatFecha(a.fecha_contenido)}</td>
          <td>${this.formatFecha(a.fecha_caducidad)}</td>
          <td>
            <span class="rol-badge rol-${a.publicado ? 'admin' : 'paciente'}">
              ${a.publicado ? 'Sí' : 'No'}
            </span>
          </td>
          <td class="actions">
            <button class="btn btn-secondary btn-sm btn-editar" data-id="${a.id}">Editar</button>
            <button class="btn btn-danger btn-sm btn-eliminar" data-id="${a.id}">Eliminar</button>
          </td>
        </tr>
      `).join('');
      
      this.renderPagination();
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="6" class="message error">Error al cargar artículos</td></tr>';
      this.mostrarMensaje('Error al conectar con el servidor', 'error');
    }
  },

  renderPagination() {
    const pagination = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');
    if (!pagination || !controls) return;
    
    const totalPages = this.totalItems > 0 ? Math.ceil(this.totalItems / this.perPage) : 0;
    
    pagination.textContent = `Página ${this.currentPage} de ${totalPages} (${this.totalItems} resultados)`;
    
    let html = '';
    if (this.currentPage > 1) {
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminArticulos.goToPage(${this.currentPage - 1})">Anterior</button> `;
    }
    
    for (let i = 1; i <= totalPages && i <= 5; i++) {
      const active = i === this.currentPage ? 'active' : '';
      html += `<button class="btn btn-sm ${active}" onclick="AdminArticulos.goToPage(${i})">${i}</button> `;
    }
    
    if (this.currentPage < totalPages) {
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminArticulos.goToPage(${this.currentPage + 1})">Siguiente</button>`;
    }
    
    controls.innerHTML = html;
  },

  goToPage(page) {
    this.currentPage = page;
    this.cargarArticulos();
  },

  async guardarArticulo() {
    const id = document.getElementById('articulo-id').value;
    const titulo = document.getElementById('articulo-titulo').value.trim();
    
    if (!titulo) {
      this.mostrarMensaje('El título es obligatorio', 'error');
      return;
    }

    if (CKEDITOR.instances['articulo-contenido-reducido']) {
      CKEDITOR.instances['articulo-contenido-reducido'].updateElement();
    }
    if (CKEDITOR.instances['articulo-contenido-completo']) {
      CKEDITOR.instances['articulo-contenido-completo'].updateElement();
    }

    const formData = new FormData();
    formData.append('titulo', titulo);
    
    const topico = document.getElementById('articulo-topico').value;
    if (topico) formData.append('topico', topico);
    
    formData.append('contenido_reducido', document.getElementById('articulo-contenido-reducido').value);
    formData.append('contenido_completo', document.getElementById('articulo-contenido-completo').value);
    
    const fechaContenido = document.getElementById('articulo-fecha-contenido').value;
    if (fechaContenido) formData.append('fecha_contenido', fechaContenido);
    
    const fechaCaducidad = document.getElementById('articulo-fecha-caducidad').value;
    if (fechaCaducidad) formData.append('fecha_caducidad', fechaCaducidad);
    
    formData.append('publicado', document.getElementById('articulo-publicado').checked);
    formData.append('notas', document.getElementById('articulo-notas').value);
    formData.append('imagen_url', document.getElementById('articulo-imagen-url').value);
    formData.append('seo_titulo', document.getElementById('articulo-seo-titulo').value);
    formData.append('seo_descripcion', document.getElementById('articulo-seo-descripcion').value);
    formData.append('seo_palabras_clave', document.getElementById('articulo-seo-palabras').value);

    const imagenFile = document.getElementById('articulo-imagen-file').files[0];
    if (imagenFile) {
      const reader = new FileReader();
      reader.onloadend = () => {
        formData.set('imagen', reader.result);
        formData.set('id', id);
        this.enviarArticulo(id, formData);
      };
      reader.readAsDataURL(imagenFile);
      return;
    }

    const imagenActual = document.getElementById('articulo-imagen').value;
    if (imagenActual) formData.append('imagen', imagenActual);
    if (id) formData.append('id', id);
    this.enviarArticulo(id, formData);
  },

  async enviarArticulo(id, formData) {
    if (id) {
      try {
        const response = await fetch(this.apiUrl, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(Object.fromEntries(formData)),
          credentials: 'same-origin'
        });
        
        const result = await response.json();
        
        if (response.ok) {
          this.mostrarMensaje('Artículo actualizado', 'success');
          this.mostrarLista();
        } else {
          this.mostrarMensaje(result.error || 'Error al guardar', 'error');
        }
      } catch (error) {
        this.mostrarMensaje('Error de conexión', 'error');
      }
    } else {
      try {
        const response = await fetch(this.apiUrl, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });
        
        const result = await response.json();
        
        if (response.ok) {
          this.mostrarMensaje('Artículo creado', 'success');
          this.mostrarLista();
        } else {
          this.mostrarMensaje(result.error || 'Error al guardar', 'error');
        }
      } catch (error) {
        this.mostrarMensaje('Error de conexión', 'error');
      }
    }
  },

  confirmarEliminar(id) {
    if (confirm('¿Eliminar este artículo?')) {
      this.eliminarArticulo(id);
    }
  },

  async eliminarArticulo(id) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
        credentials: 'same-origin'
      });

      if (response.ok) {
        this.mostrarMensaje('Artículo eliminado', 'success');
        this.cargarArticulos();
      } else {
        const result = await response.json();
        this.mostrarMensaje(result.error || 'Error al eliminar', 'error');
      }
    } catch (error) {
      this.mostrarMensaje('Error de conexión', 'error');
    }
  },

  limpiarFiltros() {
    document.getElementById('filtro-titulo').value = '';
    document.getElementById('filtro-topico').value = '';
    document.getElementById('filtro-fecha-desde').value = '';
    document.getElementById('filtro-fecha-hasta').value = '';
    this.currentPage = 1;
  },

  mostrarMensaje(mensaje, tipo = 'success') {
    const container = document.getElementById('message-container');
    container.innerHTML = `<div class="message ${tipo}">${mensaje}</div>`;
    setTimeout(() => {
      container.innerHTML = '';
    }, 5000);
  },

  escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
  },

  formatFecha(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-ES');
  }
};

document.addEventListener('DOMContentLoaded', () => {
  AdminArticulos.init();
});
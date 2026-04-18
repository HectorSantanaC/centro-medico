const AdminArticulos = {
  apiUrl: 'api/articulos.php',
  apiTopicos: 'api/topicos.php',
  currentPage: 1,
  perPage: 10,
  totalItems: 0,

  init() {
    this.cargarTopicos();
    this.cargarFiltros();
    this.cargarArticulos();
    this.bindEvents();
  },

  async cargarTopicos() {
    try {
      const response = await fetch(this.apiTopicos);
      const result = await response.json();
      const topicos = result.data || result;
      const select = document.getElementById('topico');
      select.innerHTML = '<option value="">Sin topico</option>';
      select.innerHTML += topicos.map(t => 
        `<option value="${t.id}">${this.escapeHtml(t.nombre)}</option>`
      ).join('');
    } catch (error) {
      console.error('Error cargando topicos:', error);
    }
  },

  async cargarFiltros() {
    try {
      const response = await fetch(this.apiTopicos);
      const result = await response.json();
      const topicos = result.data || result;
      const select = document.getElementById('filtro-topico');
      select.innerHTML = '<option value="">Todos</option>';
      select.innerHTML += topicos.map(t => 
        `<option value="${t.id}">${this.escapeHtml(t.nombre)}</option>`
      ).join('');
    } catch (error) {
      console.error('Error cargando filtros:', error);
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

  limpiarFiltros() {
    document.getElementById('filtro-titulo').value = '';
    document.getElementById('filtro-topico').value = '';
    document.getElementById('filtro-fecha-desde').value = '';
    document.getElementById('filtro-fecha-hasta').value = '';
    this.currentPage = 1;
  },

  bindEvents() {
    document.getElementById('btn-crear')?.addEventListener('click', () => this.mostrarModalCrear());

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
        this.mostrarModalEditar(e.target.dataset.id);
      }
      if (e.target.classList.contains('btn-eliminar')) {
        this.confirmarEliminar(e.target.dataset.id);
      }
      if (e.target.classList.contains('modal-close-btn') || e.target.classList.contains('modal-close')) {
        this.cerrarModal();
      }
    });

    document.getElementById('form-articulo')?.addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarArticulo();
    });
  },

  async cargarArticulos() {
    const tbody = document.getElementById('articulos-body');
    tbody.innerHTML = '<tr><td colspan="6" class="loading"><div class="spinner"></div>Cargando...</td></tr>';
    try {
      const queryString = this.buildQueryString();
      const response = await fetch(`${this.apiUrl}?${queryString}`);
      const result = await response.json();
      const articulos = result.data || [];
      this.totalItems = result.pagination?.total || 0;
      
      if (articulos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;">No hay articulos</td></tr>';
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
          <span class="estado-badge ${a.publicado ? 'estado-confirmada' : 'estado-cancelada'}">
            ${a.publicado ? 'Publicado' : 'Borrador'}
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
      tbody.innerHTML = '<tr><td colspan="6" class="message error">Error al cargar articulos</td></tr>';
      this.mostrarToast('Error al conectar con el servidor', 'error');
    }
  },

  renderPagination() {
    const pagination = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');
    if (!pagination || !controls) return;
    
    const totalPages = Math.ceil(this.totalItems / this.perPage);
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

  mostrarModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nuevo Articulo';
    document.getElementById('articulo-id').value = '';
    document.getElementById('titulo').value = '';
    document.getElementById('topico').value = '';
    document.getElementById('contenido_reducido').value = '';
    document.getElementById('contenido_completo').value = '';
    document.getElementById('fecha_contenido').value = '';
    document.getElementById('fecha_caducidad').value = '';
    document.getElementById('publicado').checked = true;
    document.getElementById('imagen').value = '';
    document.getElementById('imagen_url').value = '';
    document.getElementById('autor').value = '';
    document.getElementById('notas').value = '';
    document.getElementById('seo_titulo').value = '';
    document.getElementById('seo_descripcion').value = '';
    document.getElementById('seo_palabras_clave').value = '';
    document.getElementById('modal').style.display = 'block';
  },

  async mostrarModalEditar(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`);
      if (!response.ok) {
        this.mostrarToast('Articulo no encontrado', 'error');
        return;
      }
      const a = await response.json();
      document.getElementById('modal-titulo').textContent = 'Editar Articulo';
      document.getElementById('articulo-id').value = a.id;
      document.getElementById('titulo').value = a.titulo || '';
      document.getElementById('topico').value = a.topico || '';
      document.getElementById('contenido_reducido').value = a.contenido_reducido || '';
      document.getElementById('contenido_completo').value = a.contenido_completo || '';
      document.getElementById('fecha_contenido').value = a.fecha_contenido || '';
      document.getElementById('fecha_caducidad').value = a.fecha_caducidad || '';
      document.getElementById('publicado').checked = a.publicado;
      document.getElementById('imagen').value = a.imagen || '';
      document.getElementById('imagen_url').value = a.imagen_url || '';
      document.getElementById('autor').value = a.autor || '';
      document.getElementById('notas').value = a.notas || '';
      document.getElementById('seo_titulo').value = a.seo_titulo || '';
      document.getElementById('seo_descripcion').value = a.seo_descripcion || '';
      document.getElementById('seo_palabras_clave').value = a.seo_palabras_clave || '';
      document.getElementById('modal').style.display = 'block';
    } catch (error) {
      this.mostrarToast('Error al cargar articulo', 'error');
    }
  },

  async guardarArticulo() {
    const id = document.getElementById('articulo-id').value;
    const datos = {
      titulo: document.getElementById('titulo').value.trim(),
      topico: document.getElementById('topico').value || null,
      contenido_reducido: document.getElementById('contenido_reducido').value.trim(),
      contenido_completo: document.getElementById('contenido_completo').value.trim(),
      fecha_contenido: document.getElementById('fecha_contenido').value || null,
      fecha_caducidad: document.getElementById('fecha_caducidad').value || null,
      publicado: document.getElementById('publicado').checked,
      imagen: document.getElementById('imagen').value.trim(),
      imagen_url: document.getElementById('imagen_url').value.trim(),
      autor: document.getElementById('autor').value.trim(),
      notas: document.getElementById('notas').value.trim(),
      seo_titulo: document.getElementById('seo_titulo').value.trim(),
      seo_descripcion: document.getElementById('seo_descripcion').value.trim(),
      seo_palabras_clave: document.getElementById('seo_palabras_clave').value.trim()
    };

    if (!datos.titulo) {
      this.mostrarToast('El titulo es obligatorio', 'error');
      return;
    }

    try {
      const options = {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(id ? { ...datos, id } : datos)
      };
      const response = await fetch(this.apiUrl, options);
      const result = await response.json();
      if (response.ok) {
        this.cerrarModal();
        this.cargarArticulos();
        this.mostrarToast(id ? 'Articulo actualizado' : 'Articulo creado', 'success');
      } else {
        this.mostrarToast(result.error || 'Error al guardar', 'error');
      }
    } catch (error) {
      this.mostrarToast('Error de conexión', 'error');
    }
  },

  confirmarEliminar(id) {
    if (confirm('¿Eliminar este articulo?')) {
      this.eliminarArticulo(id);
    }
  },

  async eliminarArticulo(id) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });

      if (response.ok) {
        this.cargarArticulos();
        this.mostrarToast('Articulo eliminado', 'success');
      } else {
        const result = await response.json();
        this.mostrarToast(result.error || 'Error al eliminar', 'error');
      }
    } catch (error) {
      this.mostrarToast('Error de conexión', 'error');
    }
  },

  cerrarModal() {
    document.getElementById('modal').style.display = 'none';
  },

  mostrarToast(mensaje, tipo = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${tipo}`;
    toast.textContent = mensaje;
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '2000';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
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

document.addEventListener('DOMContentLoaded', () => AdminArticulos.init());
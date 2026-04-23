const AdminEspecialidades = {
  apiUrl: 'api/especialidades.php',
  currentPage: 1,
  perPage: 10,
  totalItems: 0,

  init() {
    this.cargarEspecialidades();
    this.bindEvents();
  },

  getFiltros() {
    return {
      nombre: document.getElementById('filtro-nombre').value || null
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
    document.getElementById('filtro-nombre').value = '';
    this.currentPage = 1;
  },

  bindEvents() {
    document.getElementById('btn-crear').addEventListener('click', () => this.mostrarModalCrear());

    document.getElementById('btn-filtrar').addEventListener('click', () => {
      this.currentPage = 1;
      this.cargarEspecialidades();
    });

    document.getElementById('btn-limpiar-filtros').addEventListener('click', () => {
      this.limpiarFiltros();
      this.cargarEspecialidades();
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

    document.getElementById('form-especialidad').addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarEspecialidad();
    });
  },

  async cargarEspecialidades() {
    const tbody = document.getElementById('especialidades-body');
    tbody.innerHTML = '<tr><td colspan="4" class="loading"><div class="spinner"></div>Cargando...</td></tr>';
    try {
      const queryString = this.buildQueryString();
      const response = await fetch(`${this.apiUrl}?${queryString}`, { credentials: 'same-origin' });
      const result = await response.json();
      const especialidades = result.data || [];
      this.totalItems = result.pagination?.total || 0;
      
      if (especialidades.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:30px;">No hay especialidades</td></tr>';
        this.renderPagination();
        return;
      }
      
      tbody.innerHTML = especialidades.map(esp => `
      <tr>
        <td>${this.escapeHtml(esp.nombre)}</td>
        <td>${this.escapeHtml(esp.descripcion || '-')}</td>
        <td>
          <span class="estado-badge ${esp.activo ? 'estado-confirmada' : 'estado-cancelada'}">
            ${esp.activo ? 'Activa' : 'Inactiva'}
          </span>
        </td>
        <td class="actions">
          <button class="btn btn-secondary btn-sm btn-editar" data-id="${esp.id}">Editar</button>
          <button class="btn btn-danger btn-sm btn-eliminar" data-id="${esp.id}">Eliminar</button>
        </td>
      </tr>
      `).join('');
      
      this.renderPagination();
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="4" class="message error">Error al cargar especialidades</td></tr>';
      this.mostrarToast('Error al conectar con el servidor', 'error');
    }
  },

  renderPagination() {
    const pagination = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');
    const totalPages = Math.ceil(this.totalItems / this.perPage);
    
    pagination.textContent = `Página ${this.currentPage} de ${totalPages} (${this.totalItems} resultados)`;
    
    let html = '';
    if (this.currentPage > 1) {
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminEspecialidades.goToPage(${this.currentPage - 1})">Anterior</button> `;
    }
    
    for (let i = 1; i <= totalPages && i <= 5; i++) {
      const active = i === this.currentPage ? 'active' : '';
      html += `<button class="btn btn-sm ${active}" onclick="AdminEspecialidades.goToPage(${i})">${i}</button> `;
    }
    
    if (this.currentPage < totalPages) {
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminEspecialidades.goToPage(${this.currentPage + 1})">Siguiente</button>`;
    }
    
    controls.innerHTML = html;
  },

  goToPage(page) {
    this.currentPage = page;
    this.cargarEspecialidades();
  },

  mostrarModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nueva Especialidad';
    document.getElementById('especialidad-id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('activo').checked = true;
    document.getElementById('modal').style.display = 'block';
  },

  async mostrarModalEditar(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`, { credentials: 'same-origin' });
      if (!response.ok) {
        this.mostrarToast('Especialidad no encontrada', 'error');
        return;
      }
      const esp = await response.json();
      document.getElementById('modal-titulo').textContent = 'Editar Especialidad';
      document.getElementById('especialidad-id').value = esp.id;
      document.getElementById('nombre').value = esp.nombre;
      document.getElementById('descripcion').value = esp.descripcion || '';
      document.getElementById('activo').checked = esp.activo;
      document.getElementById('modal').style.display = 'block';
    } catch (error) {
      this.mostrarToast('Error al cargar especialidad', 'error');
    }
  },

  async guardarEspecialidad() {
    const id = document.getElementById('especialidad-id').value;
    const datos = {
      nombre: document.getElementById('nombre').value.trim(),
      descripcion: document.getElementById('descripcion').value.trim(),
      activo: document.getElementById('activo').checked
    };

    if (!datos.nombre) {
      this.mostrarToast('El nombre es obligatorio', 'error');
      return;
    }

    try {
      const options = {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(id ? { ...datos, id } : datos),
        credentials: 'same-origin'
      };
      const response = await fetch(this.apiUrl, options);
      const result = await response.json();
      if (response.ok) {
        this.cerrarModal();
        this.cargarEspecialidades();
        this.mostrarToast(id ? 'Especialidad actualizada' : 'Especialidad creada', 'success');
      } else {
        this.mostrarToast(result.error || 'Error al guardar', 'error');
      }
    } catch (error) {
      this.mostrarToast('Error de conexión', 'error');
    }
  },

  confirmarEliminar(id) {
    if (confirm('¿Eliminar esta especialidad?')) {
      this.eliminarEspecialidad(id);
    }
  },

  async eliminarEspecialidad(id) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
        credentials: 'same-origin'
      });

      if (response.ok) {
        this.cargarEspecialidades();
        this.mostrarToast('Especialidad eliminada', 'success');
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
  }
};

document.addEventListener('DOMContentLoaded', () => AdminEspecialidades.init());

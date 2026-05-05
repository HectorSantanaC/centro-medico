const AdminRoles = {
  apiUrl: 'api/roles.php',
  currentPage: 1,
  perPage: 10,
  totalItems: 0,

  init() {
    this.cargarRoles();
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
      this.cargarRoles();
    });

    document.getElementById('btn-limpiar-filtros').addEventListener('click', () => {
      this.limpiarFiltros();
      this.cargarRoles();
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

    document.getElementById('form-rol').addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarRol();
    });
  },

  async cargarRoles() {
    const tbody = document.getElementById('roles-body');
    tbody.innerHTML = '<tr><td colspan="5" class="loading"><div class="spinner"></div>Cargando...</td></tr>';
    try {
      const queryString = this.buildQueryString();
      const response = await fetch(`${this.apiUrl}?${queryString}`, { credentials: 'same-origin' });
      const result = await response.json();
      const roles = result.data || [];
      this.totalItems = result.pagination?.total || 0;
      
      if (roles.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:30px;">No hay roles</td></tr>';
        this.renderPagination();
        return;
      }
      
      tbody.innerHTML = roles.map(r => `
      <tr>
        <td>${r.id}</td>
        <td>${this.escapeHtml(r.nombre)}</td>
        <td>${this.escapeHtml(r.descripcion || '-')}</td>
        <td>${r.activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>'}</td>
        <td class="actions">
          <button class="btn btn-secondary btn-sm btn-editar" data-id="${r.id}">Editar</button>
          <button class="btn btn-danger btn-sm btn-eliminar" data-id="${r.id}">Eliminar</button>
        </td>
      </tr>
      `).join('');
      
      this.renderPagination();
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="5" class="message error">Error al cargar roles</td></tr>';
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
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminRoles.goToPage(${this.currentPage - 1})">Anterior</button> `;
    }
    
    let start = Math.max(1, this.currentPage - 2);
    let end = Math.min(totalPages, start + 4);
    if (end - start < 4) {
      start = Math.max(1, end - 4);
    }
    
    for (let i = start; i <= end; i++) {
      const active = i === this.currentPage ? 'active' : '';
      html += `<button class="btn btn-sm ${active}" onclick="AdminRoles.goToPage(${i})">${i}</button> `;
    }
    
    if (this.currentPage < totalPages) {
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminRoles.goToPage(${this.currentPage + 1})">Siguiente</button>`;
    }
    
    controls.innerHTML = html;
  },

  goToPage(page) {
    this.currentPage = page;
    this.cargarRoles();
  },

  mostrarModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nuevo Rol';
    document.getElementById('rol-id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('activo').checked = true;
    document.getElementById('modal').style.display = 'block';
  },

  async mostrarModalEditar(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`, { credentials: 'same-origin' });
      if (!response.ok) {
        this.mostrarToast('Rol no encontrado', 'error');
        return;
      }
      const r = await response.json();
      document.getElementById('modal-titulo').textContent = 'Editar Rol';
      document.getElementById('rol-id').value = r.id;
      document.getElementById('nombre').value = r.nombre;
      document.getElementById('descripcion').value = r.descripcion || '';
      document.getElementById('activo').checked = r.activo;
      document.getElementById('modal').style.display = 'block';
    } catch (error) {
      this.mostrarToast('Error al cargar rol', 'error');
    }
  },

  async guardarRol() {
    const id = document.getElementById('rol-id').value;
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
        this.cargarRoles();
        this.mostrarToast(id ? 'Rol actualizado' : 'Rol creado', 'success');
      } else {
        this.mostrarToast(result.error || 'Error al guardar', 'error');
      }
    } catch (error) {
      this.mostrarToast('Error de conexión', 'error');
    }
  },

  confirmarEliminar(id) {
    if (confirm('¿Eliminar este rol?')) {
      this.eliminarRol(id);
    }
  },

  async eliminarRol(id) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
        credentials: 'same-origin'
      });

      if (response.ok) {
        this.cargarRoles();
        this.mostrarToast('Rol eliminado', 'success');
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

document.addEventListener('DOMContentLoaded', () => AdminRoles.init());
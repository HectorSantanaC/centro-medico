const AdminUsuarios = {
  apiUrl: 'api/usuarios.php',
  currentPage: 1,
  perPage: 10,
  totalItems: 0,

  init() {
    this.cargarUsuarios();
    this.bindEvents();
  },

  getFiltros() {
    return {
      nombre: document.getElementById('filtro-nombre').value || null,
      rol: document.getElementById('filtro-rol').value || null
    };
  },

  formatRoles(roles) {
    if (!roles) return '-';
    // roles viene como string: "admin (Dirección Médica), paciente (Pacientes)"
    return roles;
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
    document.getElementById('filtro-rol').value = '';
    this.currentPage = 1;
  },

  bindEvents() {
    document.getElementById('btn-crear').addEventListener('click', () => this.mostrarModalCrear());

    document.getElementById('btn-filtrar').addEventListener('click', () => {
      this.currentPage = 1;
      this.cargarUsuarios();
    });

    document.getElementById('btn-limpiar-filtros').addEventListener('click', () => {
      this.limpiarFiltros();
      this.cargarUsuarios();
    });

    document.addEventListener('click', (e) => {
      const editBtn = e.target.closest('.btn-editar');
      const deleteBtn = e.target.closest('.btn-eliminar');
      const closeBtn = e.target.closest('.modal-close-btn, .modal-close');
      
      if (editBtn) {
        this.mostrarModalEditar(editBtn.dataset.id);
      }
      if (deleteBtn) {
        this.confirmarEliminar(deleteBtn.dataset.id);
      }
      if (closeBtn) {
        this.cerrarModal();
      }
    });
    document.getElementById('form-usuario').addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarUsuario();
    });
  },

  async cargarUsuarios() {
    const tbody = document.getElementById('usuarios-body');
    tbody.innerHTML = '<tr><td colspan="6" class="loading"><div class="spinner"></div>Cargando...</td></tr>';

    try {
      const queryString = this.buildQueryString();
      const response = await fetch(`${this.apiUrl}?${queryString}`, { credentials: 'same-origin' });
      const result = await response.json();
      const usuarios = result.data || [];
      this.totalItems = result.pagination?.total || 0;

      if (usuarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;">No hay usuarios registrados</td></tr>';
        this.renderPagination();
        return;
      }

      tbody.innerHTML = usuarios.map(u => `
        <tr>
          <td>${this.escapeHtml(u.nombre)}</td>
          <td>${this.escapeHtml(u.apellidos)}</td>
          <td>${this.escapeHtml(u.email)}</td>
          <td>${this.formatRoles(u.roles)}</td>
          <td>${this.formatearFecha(u.created_at)}</td>
          <td class="actions">
            <button class="btn btn-secondary btn-sm btn-editar" data-id="${u.id}">Editar</button>
            <button class="btn btn-danger btn-sm btn-eliminar" data-id="${u.id}">Eliminar</button>
          </td>
        </tr>
      `).join('');
      
      this.renderPagination();
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="6" class="message error">Error al cargar usuarios</td></tr>';
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
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminUsuarios.goToPage(${this.currentPage - 1})">Anterior</button> `;
    }
    
    for (let i = 1; i <= totalPages && i <= 5; i++) {
      const active = i === this.currentPage ? 'active' : '';
      html += `<button class="btn btn-sm ${active}" onclick="AdminUsuarios.goToPage(${i})">${i}</button> `;
    }
    
    if (this.currentPage < totalPages) {
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminUsuarios.goToPage(${this.currentPage + 1})">Siguiente</button>`;
    }
    
    controls.innerHTML = html;
  },

  goToPage(page) {
    this.currentPage = page;
    this.cargarUsuarios();
  },

  mostrarModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nuevo Usuario';
    document.getElementById('usuario-id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('apellidos').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('password-help').style.display = 'block';
    document.getElementById('modal').style.display = 'block';
  },

  async mostrarModalEditar(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`, { credentials: 'same-origin' });
      if (!response.ok) {
        this.mostrarToast('Usuario no encontrado', 'error');
        return;
      }
      const usuario = await response.json();

      document.getElementById('modal-titulo').textContent = 'Editar Usuario';
      document.getElementById('usuario-id').value = usuario.id;
      document.getElementById('nombre').value = usuario.nombre;
      document.getElementById('apellidos').value = usuario.apellidos;
      document.getElementById('email').value = usuario.email;
      document.getElementById('password').value = '';
      document.getElementById('password').required = false;
      
      // Mostrar roles en el display
      const rolesDisplay = document.getElementById('roles-display');
      if (usuario.roles && Array.isArray(usuario.roles) && usuario.roles.length > 0) {
        rolesDisplay.innerHTML = usuario.roles.map(r => 
          `<span class="rol-badge rol-${r.rol_nombre}">${r.rol_nombre} (${r.departamento_nombre || 'Sin depto'})</span>`
        ).join(' ');
      } else {
        rolesDisplay.textContent = 'Sin roles asignados';
      }
      
      document.getElementById('password-help').textContent = 'Dejar vacío para mantener la actual';
      document.getElementById('modal').style.display = 'block';
    } catch (error) {
      this.mostrarToast('Error al cargar usuario', 'error');
    }
  },

  async guardarUsuario() {
    const id = document.getElementById('usuario-id').value;
    const datos = {
      nombre: document.getElementById('nombre').value.trim(),
      apellidos: document.getElementById('apellidos').value.trim(),
      email: document.getElementById('email').value.trim(),
      password: document.getElementById('password').value
    };

    if (!datos.nombre || !datos.apellidos || !datos.email) {
      this.mostrarToast('Todos los campos obligatorios', 'error');
      return;
    }

    if (!id && datos.password.length < 6) {
      this.mostrarToast('La contraseña debe tener al menos 6 caracteres', 'error');
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
        this.cargarUsuarios();
        this.mostrarToast(id ? 'Usuario actualizado' : 'Usuario creado', 'success');
      } else {
        this.mostrarToast(result.error || 'Error al guardar', 'error');
      }
    } catch (error) {
      this.mostrarToast('Error de conexión', 'error');
    }
  },

  confirmarEliminar(id) {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
      this.eliminarUsuario(id);
    }
  },

  async eliminarUsuario(id) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
        credentials: 'same-origin'
      });

      if (response.ok) {
        this.cargarUsuarios();
        this.mostrarToast('Usuario eliminado', 'success');
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

    setTimeout(() => {
      toast.remove();
    }, 3000);
  },

  escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
  },

  formatearFecha(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-ES');
  }
};

document.addEventListener('DOMContentLoaded', () => AdminUsuarios.init());

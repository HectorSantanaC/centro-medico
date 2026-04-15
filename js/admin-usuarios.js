const AdminUsuarios = {
  apiUrl: 'api/usuarios.php',

  init() {
    this.cargarUsuarios();
    this.bindEvents();
  },

  bindEvents() {
    document.getElementById('btn-crear').addEventListener('click', () => this.mostrarModalCrear());
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
    document.getElementById('form-usuario').addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarUsuario();
    });
  },

  async cargarUsuarios() {
    const tbody = document.getElementById('usuarios-body');
    tbody.innerHTML = '<tr><td colspan="6" class="loading"><div class="spinner"></div>Cargando...</td></tr>';

    try {
      const response = await fetch(this.apiUrl);
      const usuarios = await response.json();

      if (usuarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;">No hay usuarios registrados</td></tr>';
        return;
      }

      tbody.innerHTML = usuarios.map(u => `
        <tr>
          <td>${this.escapeHtml(u.nombre)}</td>
          <td>${this.escapeHtml(u.apellidos)}</td>
          <td>${this.escapeHtml(u.email)}</td>
          <td><span class="rol-badge rol-${u.rol}">${u.rol}</span></td>
          <td>${this.formatearFecha(u.created_at)}</td>
          <td class="actions">
            <button class="btn btn-secondary btn-sm btn-editar" data-id="${u.id}">Editar</button>
            <button class="btn btn-danger btn-sm btn-eliminar" data-id="${u.id}">Eliminar</button>
          </td>
        </tr>
      `).join('');
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="6" class="message error">Error al cargar usuarios</td></tr>';
      this.mostrarToast('Error al conectar con el servidor', 'error');
    }
  },

  mostrarModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nuevo Usuario';
    document.getElementById('usuario-id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('apellidos').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('rol').value = 'paciente';
    document.getElementById('password-help').style.display = 'block';
    document.getElementById('modal').style.display = 'block';
  },

  async mostrarModalEditar(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`);
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
      document.getElementById('rol').value = usuario.rol;
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
      password: document.getElementById('password').value,
      rol: document.getElementById('rol').value
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
        body: JSON.stringify(id ? { ...datos, id } : datos)
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
        body: JSON.stringify({ id })
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
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${tipo}`;
    toast.textContent = mensaje;
    container.appendChild(toast);

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

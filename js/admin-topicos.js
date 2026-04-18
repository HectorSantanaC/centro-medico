const AdminTopicos = {
  apiUrl: 'api/topicos.php',

  init() {
    this.cargarTopicos();
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

    document.getElementById('form-topico').addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarTopico();
    });
  },

  async cargarTopicos() {
    const tbody = document.getElementById('topicos-body');
    tbody.innerHTML = '<tr><td colspan="2" class="loading"><div class="spinner"></div>Cargando...</td></tr>';
    try {
      const response = await fetch(this.apiUrl);
      const topicos = await response.json();
      if (topicos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;padding:30px;">No hay topicos</td></tr>';
        return;
      }
      tbody.innerHTML = topicos.map(t => `
      <tr>
        <td>${this.escapeHtml(t.nombre)}</td>
        <td class="actions">
          <button class="btn btn-secondary btn-sm btn-editar" data-id="${t.id}">Editar</button>
          <button class="btn btn-danger btn-sm btn-eliminar" data-id="${t.id}">Eliminar</button>
        </td>
      </tr>
    `).join('');
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="2" class="message error">Error al cargar topicos</td></tr>';
      this.mostrarToast('Error al conectar con el servidor', 'error');
    }
  },

  mostrarModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nuevo Topico';
    document.getElementById('topico-id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('modal').style.display = 'block';
  },

  async mostrarModalEditar(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`);
      if (!response.ok) {
        this.mostrarToast('Topico no encontrado', 'error');
        return;
      }
      const t = await response.json();
      document.getElementById('modal-titulo').textContent = 'Editar Topico';
      document.getElementById('topico-id').value = t.id;
      document.getElementById('nombre').value = t.nombre;
      document.getElementById('modal').style.display = 'block';
    } catch (error) {
      this.mostrarToast('Error al cargar topico', 'error');
    }
  },

  async guardarTopico() {
    const id = document.getElementById('topico-id').value;
    const datos = {
      nombre: document.getElementById('nombre').value.trim()
    };

    if (!datos.nombre) {
      this.mostrarToast('El nombre es obligatorio', 'error');
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
        this.cargarTopicos();
        this.mostrarToast(id ? 'Topico actualizado' : 'Topico creado', 'success');
      } else {
        this.mostrarToast(result.error || 'Error al guardar', 'error');
      }
    } catch (error) {
      this.mostrarToast('Error de conexión', 'error');
    }
  },

  confirmarEliminar(id) {
    if (confirm('¿Eliminar este topico?')) {
      this.eliminarTopico(id);
    }
  },

  async eliminarTopico(id) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });

      if (response.ok) {
        this.cargarTopicos();
        this.mostrarToast('Topico eliminado', 'success');
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

document.addEventListener('DOMContentLoaded', () => AdminTopicos.init());
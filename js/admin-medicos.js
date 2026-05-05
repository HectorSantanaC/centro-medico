const AdminMedicos = {
  apiUrl: 'api/medicos.php',
  apiEspecialidades: 'api/especialidades.php',
  currentPage: 1,
  perPage: 10,
  totalItems: 0,

  init() {
    this.cargarEspecialidades();
    this.cargarFiltros();
    this.cargarMedicos();
    this.bindEvents();
  },

  async cargarEspecialidades() {
    try {
      const response = await fetch(this.apiEspecialidades, { credentials: 'same-origin' });
      const result = await response.json();
      const especialidades = result.data || result;
      const select = document.getElementById('especialidad_id');
      select.innerHTML = '<option value="">Sin asignar</option>';
      select.innerHTML += especialidades.map(e => 
        `<option value="${e.id}">${this.escapeHtml(e.nombre)}</option>`
      ).join('');
    } catch (error) {
      console.error('Error cargando especialidades:', error);
    }
  },

  async cargarFiltros() {
    try {
      const response = await fetch(this.apiEspecialidades, { credentials: 'same-origin' });
      const result = await response.json();
      const especialidades = result.data || result;
      const select = document.getElementById('filtro-especialidad');
      select.innerHTML = '<option value="">Todas</option>';
      select.innerHTML += especialidades.map(e => 
        `<option value="${e.id}">${this.escapeHtml(e.nombre)}</option>`
      ).join('');
    } catch (error) {
      console.error('Error cargando filtros:', error);
    }
  },

  getFiltros() {
    return {
      nombre: document.getElementById('filtro-nombre').value || null,
      especialidad_id: document.getElementById('filtro-especialidad').value || null
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
    document.getElementById('filtro-especialidad').value = '';
    this.currentPage = 1;
  },

  bindEvents() {
    document.getElementById('btn-crear').addEventListener('click', () => this.mostrarModalCrear());

    document.getElementById('btn-filtrar').addEventListener('click', () => {
      this.currentPage = 1;
      this.cargarMedicos();
    });

    document.getElementById('btn-limpiar-filtros').addEventListener('click', () => {
      this.limpiarFiltros();
      this.cargarMedicos();
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

    document.getElementById('form-medico').addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarMedico();
    });

    document.getElementById('medico-imagen-file').addEventListener('change', (e) => {
      this.previewImagen(e.target);
    });
  },

  previewImagen(input) {
    const preview = document.getElementById('imagen-preview');
    const hiddenInput = document.getElementById('medico-imagen');
    
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = (e) => {
        preview.innerHTML = `<img src="${e.target.result}" alt="Previsualización">`;
        hiddenInput.value = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.innerHTML = '';
      hiddenInput.value = '';
    }
  },

  async cargarMedicos() {
    const tbody = document.getElementById('medicos-body');
    tbody.innerHTML = '<tr><td colspan="4" class="loading"><div class="spinner"></div>Cargando...</td></tr>';
    try {
      const queryString = this.buildQueryString();
      const response = await fetch(`${this.apiUrl}?${queryString}`, { credentials: 'same-origin' });
      const result = await response.json();
      const medicos = result.data || [];
      this.totalItems = result.pagination?.total || 0;
      
      if (medicos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:30px;">No hay médicos</td></tr>';
        this.renderPagination();
        return;
      }
      
      tbody.innerHTML = medicos.map(med => `
      <tr>
        <td>${this.escapeHtml(med.nombre)} ${this.escapeHtml(med.apellidos)}</td>
        <td>${this.escapeHtml(med.especialidad_nombre || '-')}</td>
        <td>
          <span class="estado-badge ${med.activo ? 'estado-confirmada' : 'estado-cancelada'}">
            ${med.activo ? 'Activo' : 'Inactivo'}
          </span>
        </td>
        <td class="actions">
          <button class="btn btn-secondary btn-sm btn-editar" data-id="${med.id}">Editar</button>
          <button class="btn btn-danger btn-sm btn-eliminar" data-id="${med.id}">Eliminar</button>
        </td>
      </tr>
    `).join('');
      
      this.renderPagination();
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="4" class="message error">Error al cargar médicos</td></tr>';
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
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminMedicos.goToPage(${this.currentPage - 1})">Anterior</button> `;
    }
    
    let start = Math.max(1, this.currentPage - 2);
    let end = Math.min(totalPages, start + 4);
    if (end - start < 4) {
      start = Math.max(1, end - 4);
    }
    
    for (let i = start; i <= end; i++) {
      const active = i === this.currentPage ? 'active' : '';
      html += `<button class="btn btn-sm ${active}" onclick="AdminMedicos.goToPage(${i})">${i}</button> `;
    }
    
    if (this.currentPage < totalPages) {
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminMedicos.goToPage(${this.currentPage + 1})">Siguiente</button>`;
    }
    
    controls.innerHTML = html;
  },

  goToPage(page) {
    this.currentPage = page;
    this.cargarMedicos();
  },

  mostrarModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nuevo Médico';
    document.getElementById('medico-id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('apellidos').value = '';
    document.getElementById('especialidad_id').value = '';
    document.getElementById('activo').checked = true;
    document.getElementById('medico-imagen').value = '';
    document.getElementById('medico-imagen-url').value = '';
    document.getElementById('medico-imagen-file').value = '';
    document.getElementById('imagen-preview').innerHTML = '';
    document.getElementById('modal').style.display = 'block';
  },

  async mostrarModalEditar(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`, { credentials: 'same-origin' });
      if (!response.ok) {
        this.mostrarToast('Médico no encontrado', 'error');
        return;
      }
      const med = await response.json();
      document.getElementById('modal-titulo').textContent = 'Editar Médico';
      document.getElementById('medico-id').value = med.id;
      document.getElementById('nombre').value = med.nombre;
      document.getElementById('apellidos').value = med.apellidos;
      document.getElementById('especialidad_id').value = med.especialidad_id || '';
      document.getElementById('activo').checked = med.activo;
      
      document.getElementById('medico-imagen').value = med.imagen || '';
      document.getElementById('medico-imagen-url').value = med.imagen_url || '';
      document.getElementById('medico-imagen-file').value = '';
      
      const preview = document.getElementById('imagen-preview');
      if (med.imagen) {
        const imgSrc = med.imagen.startsWith('http://') || med.imagen.startsWith('https://')
          ? med.imagen
          : './' + med.imagen;
        preview.innerHTML = `<img src="${imgSrc}" alt="Imagen actual">`;
      } else if (med.imagen_url) {
        preview.innerHTML = `<img src="${med.imagen_url}" alt="Imagen actual">`;
      } else {
        preview.innerHTML = '';
      }
      
      document.getElementById('modal').style.display = 'block';
    } catch (error) {
      this.mostrarToast('Error al cargar médico', 'error');
    }
  },

  async guardarMedico() {
    const id = document.getElementById('medico-id').value;
    const datos = {
      nombre: document.getElementById('nombre').value.trim(),
      apellidos: document.getElementById('apellidos').value.trim(),
      especialidad_id: document.getElementById('especialidad_id').value || null,
      activo: document.getElementById('activo').checked,
      imagen: document.getElementById('medico-imagen').value,
      imagen_url: document.getElementById('medico-imagen-url').value.trim()
    };

    if (!datos.nombre || !datos.apellidos) {
      this.mostrarToast('El nombre y apellidos son obligatorios', 'error');
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
        this.cargarMedicos();
        this.mostrarToast(id ? 'Médico actualizado' : 'Médico creado', 'success');
      } else {
        this.mostrarToast(result.error || 'Error al guardar', 'error');
      }
    } catch (error) {
      this.mostrarToast('Error de conexión', 'error');
    }
  },

  confirmarEliminar(id) {
    if (confirm('¿Eliminar este médico?')) {
      this.eliminarMedico(id);
    }
  },

  async eliminarMedico(id) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
        credentials: 'same-origin'
      });

      if (response.ok) {
        this.cargarMedicos();
        this.mostrarToast('Médico eliminado', 'success');
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

document.addEventListener('DOMContentLoaded', () => AdminMedicos.init());
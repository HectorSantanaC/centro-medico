const AdminCitas = {
  apiUrl: 'api/citas.php',
  apiUsuarios: 'api/usuarios.php',
  apiEspecialidades: 'api/especialidades.php',
  apiMedicos: 'api/medicos.php',
  apiHoras: 'api/horas.php',
  currentPage: 1,
  perPage: 10,
  totalItems: 0,

  init() {
    this.cargarDatosIniciales();
    this.cargarFiltros();
    this.cargarCitas();
    this.bindEvents();
  },

  async cargarDatosIniciales() {
    await this.cargarPacientes();
    await this.cargarEspecialidades();
  },

  async cargarFiltros() {
    try {
      const response = await fetch(this.apiEspecialidades);
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
      fecha_desde: document.getElementById('filtro-fecha-desde').value || null,
      fecha_hasta: document.getElementById('filtro-fecha-hasta').value || null,
      estado: document.getElementById('filtro-estado').value || null,
      especialidad_id: document.getElementById('filtro-especialidad').value || null,
      medico_id: document.getElementById('filtro-medico').value || null
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
    document.getElementById('filtro-fecha-desde').value = '';
    document.getElementById('filtro-fecha-hasta').value = '';
    document.getElementById('filtro-estado').value = '';
    document.getElementById('filtro-especialidad').value = '';
    document.getElementById('filtro-medico').value = '';
    this.currentPage = 1;
  },

  async cargarPacientes() {
    try {
      const response = await fetch(this.apiUsuarios);
      const result = await response.json();
      const usuarios = result.data || result;
      const pacientes = usuarios.filter(u => u.rol === 'paciente');
      const select = document.getElementById('paciente_id');
      select.innerHTML = '<option value="">Seleccionar paciente</option>';
      select.innerHTML += pacientes.map(p => 
        `<option value="${p.id}">${this.escapeHtml(p.nombre)} ${this.escapeHtml(p.apellidos)}</option>`
      ).join('');
    } catch (error) {
      console.error('Error cargando pacientes:', error);
    }
  },

  async cargarEspecialidades() {
    try {
      const response = await fetch(this.apiEspecialidades);
      const result = await response.json();
      const especialidades = result.data || result;
      const select = document.getElementById('especialidad_id');
      select.innerHTML = '<option value="">Seleccionar especialidad</option>';
      select.innerHTML += especialidades.map(e => 
        `<option value="${e.id}">${this.escapeHtml(e.nombre)}</option>`
      ).join('');
    } catch (error) {
      console.error('Error cargando especialidades:', error);
    }
  },

  async cargarMedicosPorEspecialidad(especialidadId) {
    try {
      const response = await fetch(`${this.apiMedicos}?especialidad_id=${especialidadId}`);
      const result = await response.json();
      const medicos = result.data || result;
      const select = document.getElementById('medico_id');
      select.innerHTML = '<option value="">Seleccionar medico</option>';
      select.innerHTML += medicos.map(m => {
        const nombreMostrar = m.nombre_completo || `${m.nombre || ''} ${m.apellidos || ''}`.trim();
        return `<option value="${m.id}">${this.escapeHtml(nombreMostrar)}</option>`;
      }).join('');
    } catch (error) {
      console.error('Error cargando medicos:', error);
    }
  },

  async cargarMedicosFiltro(especialidadId = null) {
    try {
      const url = especialidadId 
        ? `${this.apiMedicos}?especialidad_id=${especialidadId}`
        : this.apiMedicos;
      const response = await fetch(url);
      const result = await response.json();
      const medicos = result.data || result;
      const select = document.getElementById('filtro-medico');
      select.innerHTML = '<option value="">Todos</option>';
      select.innerHTML += medicos.map(m => {
        const nombreMostrar = m.nombre_completo || `${m.nombre || ''} ${m.apellidos || ''}`.trim();
        return `<option value="${m.id}">${this.escapeHtml(nombreMostrar)}</option>`;
      }).join('');
    } catch (error) {
      console.error('Error cargando medicos filtro:', error);
    }
  },

  async cargarHorasDisponibles(citaId = null) {
    const fecha = document.getElementById('fecha').value;
    const medicoId = document.getElementById('medico_id').value;
    const select = document.getElementById('hora');

    if (!fecha || !medicoId) {
      return;
    }

    try {
      let url = `${this.apiHoras}?fecha=${fecha}&medico_id=${medicoId}`;
      if (citaId) {
        url += `&cita_id=${citaId}`;
      }
      const response = await fetch(url);
      const data = await response.json();
      const horas = data.horas || data.data?.horas || [];
      
      select.innerHTML = '<option value="">Seleccionar hora</option>';
      
      if (horas.length > 0) {
        select.innerHTML += horas.map(h => 
          `<option value="${h}">${h}</option>`
        ).join('');
      } else {
        select.innerHTML += '<option value="">No hay horas disponibles</option>';
      }
    } catch (error) {
      console.error('Error cargando horas:', error);
    }
  },

  bindEvents() {
    document.getElementById('btn-crear').addEventListener('click', () => this.mostrarModalCrear());

    document.getElementById('especialidad_id').addEventListener('change', (e) => {
      if (e.target.value) {
        this.cargarMedicosPorEspecialidad(e.target.value);
      } else {
        document.getElementById('medico_id').innerHTML = '<option value="">Seleccionar medico</option>';
      }
      document.getElementById('hora').innerHTML = '<option value="">Seleccionar hora</option>';
    });

    document.getElementById('medico_id').addEventListener('change', () => {
      this.cargarHorasDisponibles();
    });

    document.getElementById('fecha').addEventListener('change', () => {
      this.cargarHorasDisponibles();
    });

    document.getElementById('filtro-especialidad').addEventListener('change', (e) => {
      this.cargarMedicosFiltro(e.target.value || null);
    });

    document.getElementById('btn-filtrar').addEventListener('click', () => {
      this.currentPage = 1;
      this.cargarCitas();
    });

    document.getElementById('btn-limpiar-filtros').addEventListener('click', () => {
      this.limpiarFiltros();
      this.cargarCitas();
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

    document.getElementById('formcita').addEventListener('submit', (e) => {
      e.preventDefault();
      this.guardarCita();
    });
  },

  async cargarCitas() {
    const tbody = document.getElementById('citas-body');
    tbody.innerHTML = '<tr><td colspan="7" class="loading"><div class="spinner"></div>Cargando...</td></tr>';
    try {
      const queryString = this.buildQueryString();
      const response = await fetch(`${this.apiUrl}?${queryString}`);
      const result = await response.json();
      const citas = result.data || [];
      this.totalItems = result.pagination?.total || 0;
      
      if (citas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;">No hay citas</td></tr>';
        this.renderPagination();
        return;
      }
      
      tbody.innerHTML = citas.map(c => `
      <tr>
        <td>${this.escapeHtml(c.paciente_nombre || '-')} ${this.escapeHtml(c.paciente_apellidos || '')}</td>
        <td>${this.escapeHtml(c.medico_nombre || '-')} ${this.escapeHtml(c.medico_apellidos || '')}</td>
        <td>${this.escapeHtml(c.especialidad_nombre || '-')}</td>
        <td>${this.formatFecha(c.fecha)}</td>
        <td>${this.formatHora(c.hora)}</td>
        <td>
          <span class="estado-badge estado-${c.estado}">${c.estado}</span>
        </td>
        <td class="actions">
          <button class="btn btn-secondary btn-sm btn-editar" data-id="${c.id}">Editar</button>
          <button class="btn btn-danger btn-sm btn-eliminar" data-id="${c.id}">Eliminar</button>
        </td>
      </tr>
    `).join('');
      
      this.renderPagination();
    } catch (error) {
      tbody.innerHTML = '<tr><td colspan="7" class="message error">Error al cargar citas</td></tr>';
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
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminCitas.goToPage(${this.currentPage - 1})">Anterior</button> `;
    }
    
    for (let i = 1; i <= totalPages && i <= 5; i++) {
      const active = i === this.currentPage ? 'active' : '';
      html += `<button class="btn btn-sm ${active}" onclick="AdminCitas.goToPage(${i})">${i}</button> `;
    }
    
    if (this.currentPage < totalPages) {
      html += `<button class="btn btn-sm btn-secondary" onclick="AdminCitas.goToPage(${this.currentPage + 1})">Siguiente</button>`;
    }
    
    controls.innerHTML = html;
  },

  goToPage(page) {
    this.currentPage = page;
    this.cargarCitas();
  },

  getFechaMinima() {
    const hoy = new Date();
    const anno = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia = String(hoy.getDate()).padStart(2, '0');
    return `${anno}-${mes}-${dia}`;
  },

  mostrarModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nueva Cita';
    document.getElementById('cita-id').value = '';
    document.getElementById('paciente_id').value = '';
    document.getElementById('especialidad_id').value = '';
    document.getElementById('medico_id').innerHTML = '<option value="">Seleccionar medico</option>';
    document.getElementById('fecha').value = '';
    document.getElementById('hora').innerHTML = '<option value="">Seleccionar hora</option>';
    document.getElementById('estado').value = 'pendiente';
    document.getElementById('notas').value = '';
    
    document.getElementById('fecha').min = this.getFechaMinima();
    document.getElementById('modal').style.display = 'block';
  },

  async mostrarModalEditar(id) {
    try {
      const response = await fetch(`${this.apiUrl}?id=${id}`);
      if (!response.ok) {
        this.mostrarToast('Cita no encontrada', 'error');
        return;
      }
      const c = await response.json();
      document.getElementById('modal-titulo').textContent = 'Editar Cita';
      document.getElementById('cita-id').value = c.id;
      document.getElementById('paciente_id').value = c.paciente_id;
      document.getElementById('especialidad_id').value = c.especialidad_id;
      await this.cargarMedicosPorEspecialidad(c.especialidad_id);
      document.getElementById('medico_id').value = c.medico_id;
      document.getElementById('fecha').value = c.fecha;
      document.getElementById('fecha').min = this.getFechaMinima();
      await this.cargarHorasDisponibles(c.id);
      document.getElementById('hora').value = c.hora ? c.hora.substring(0, 5) : '';
      document.getElementById('estado').value = c.estado;
      document.getElementById('notas').value = c.notas || '';
      document.getElementById('modal').style.display = 'block';
    } catch (error) {
      this.mostrarToast('Error al cargar cita', 'error');
    }
  },

  async guardarCita() {
    const id = document.getElementById('cita-id').value;
    const datos = {
      paciente_id: parseInt(document.getElementById('paciente_id').value),
      medico_id: parseInt(document.getElementById('medico_id').value),
      especialidad_id: parseInt(document.getElementById('especialidad_id').value),
      fecha: document.getElementById('fecha').value,
      hora: document.getElementById('hora').value,
      estado: document.getElementById('estado').value,
      notas: document.getElementById('notas').value
    };

    if (!datos.paciente_id || !datos.medico_id || !datos.especialidad_id || !datos.fecha || !datos.hora) {
      this.mostrarToast('Todos los campos son obligatorios', 'error');
      return;
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(datos.fecha)) {
      this.mostrarToast('Formato de fecha inválido (YYYY-MM-DD)', 'error');
      return;
    }

    const fechaMin = this.getFechaMinima();
    if (datos.fecha < fechaMin) {
      this.mostrarToast('No se pueden seleccionar fechas pasadas', 'error');
      return;
    }

    if (!/^\d{2}:\d{2}$/.test(datos.hora)) {
      this.mostrarToast('Formato de hora inválido (HH:MM)', 'error');
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
        this.cargarCitas();
        this.mostrarToast(id ? 'Cita actualizada' : 'Cita creada', 'success');
      } else {
        this.mostrarToast(result.error || 'Error al guardar', 'error');
      }
    } catch (error) {
      this.mostrarToast('Error de conexión', 'error');
    }
  },

  confirmarEliminar(id) {
    if (confirm('¿Eliminar esta cita?')) {
      this.eliminarCita(id);
    }
  },

  async eliminarCita(id) {
    try {
      const response = await fetch(this.apiUrl, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });

      if (response.ok) {
        this.cargarCitas();
        this.mostrarToast('Cita eliminada', 'success');
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
  },

  formatHora(hora) {
    if (!hora) return '-';
    return hora.substring(0, 5);
  }
};

document.addEventListener('DOMContentLoaded', () => AdminCitas.init());
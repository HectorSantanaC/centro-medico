const AdminDashboard = {
  apiUrl: 'api/dashboard.php',

  init() {
    this.cargarStats();
  },

  async cargarStats() {
    const container = document.getElementById('stats-grid');
    if (!container) return;

    container.innerHTML = '<div class="stat-card loading"><div class="spinner"></div><div>Cargando...</div></div>';

    try {
      const response = await fetch(this.apiUrl, { credentials: 'same-origin' });
      if (!response.ok) {
        container.innerHTML = '<div class="stat-card error">Error al cargar estadísticas</div>';
        return;
      }
      const stats = await response.json();

      container.innerHTML = `
        <div class="stat-card">
          <div class="number">${stats.patients || 0}</div>
          <div class="label">Pacientes registrados</div>
        </div>
        <div class="stat-card">
          <div class="number">${stats.citas?.total || 0}</div>
          <div class="label">Citas totales</div>
        </div>
        <div class="stat-card">
          <div class="number">${stats.especialidades || 0}</div>
          <div class="label">Especialidades</div>
        </div>
        <div class="stat-card">
          <div class="number">${stats.medicos || 0}</div>
          <div class="label">Médicos</div>
        </div>
      `;
    } catch (error) {
      container.innerHTML = '<div class="stat-card error">Error de conexión</div>';
    }
  }
};

document.addEventListener('DOMContentLoaded', () => AdminDashboard.init());
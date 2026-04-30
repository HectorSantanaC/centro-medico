const AdminDashboard = {
  apiUrl: 'api/dashboard.php',
  charts: {},
  colors: {
    primary: '#2c5282',
    success: '#38a169',
    warning: '#d69e2e',
    danger: '#e53e3e',
    gray: '#718096',
    purple: '#805ad5'
  },

  init() {
    this.cargarStats();
    this.cargarGraficos();
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
          <div class="number">${stats.citas?.citas_hoy || 0}</div>
          <div class="label">Citas hoy</div>
        </div>
        <div class="stat-card">
          <div class="number">${stats.citas?.tasa_cancelacion || 0}%</div>
          <div class="label">Tasa cancelación</div>
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
  },

  async cargarGraficos() {
    try {
      const response = await fetch(this.apiUrl, { credentials: 'same-origin' });
      const stats = await response.json();

      this.renderChartEstado(stats.citas?.por_estado || []);
      this.renderChartEspecialidad(stats.citas?.por_especialidad || []);
      this.renderChartEvolucion(stats.citas?.evolucion_mensual || []);
      this.renderChartMedicos(stats.citas?.por_medico || []);
      this.renderChartDias(stats.citas?.por_dia_semana || []);
    } catch (error) {
      console.error('Error cargando gráficos:', error);
    }
  },

  initChart(id) {
    const el = document.getElementById(id);
    if (!el) return null;
    return echarts.init(el);
  },

  renderChartEstado(data) {
    const chart = this.initChart('chart-estado');
    if (!chart) return;

    const labels = data.map(d => this.getLabelEstado(d.estado));
    const values = data.map(d => parseInt(d.total));
    const colors = data.map(d => this.getColorEstado(d.estado));

    this.charts.estado = chart;
    chart.setOption({
      tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
      legend: { bottom: '0%', left: 'center' },
      series: [{
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        itemStyle: { borderRadius: 8, borderColor: '#fff', borderWidth: 2 },
        label: { show: false },
        emphasis: { label: { show: true, fontSize: 14, fontWeight: 'bold' } },
        data: labels.map((label, i) => ({ name: label, value: values[i], itemStyle: { color: colors[i] } }))
      }]
    });
  },

  renderChartEspecialidad(data) {
    const chart = this.initChart('chart-especialidad');
    if (!chart) return;

    const labels = data.map(d => d.especialidad);
    const values = data.map(d => parseInt(d.total));

    this.charts.especialidad = chart;
    chart.setOption({
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
      xAxis: { type: 'value', splitLine: { lineStyle: { type: 'dashed' } } },
      yAxis: { type: 'category', data: labels.reverse() },
      series: [{
        type: 'bar',
        data: values.reverse(),
        itemStyle: { color: this.colors.primary, borderRadius: [0, 4, 4, 0] },
        barWidth: '60%'
      }]
    });
  },

  renderChartEvolucion(data) {
    const chart = this.initChart('chart-evolucion');
    if (!chart) return;

    const labels = data.map(d => this.formatMes(d.mes));
    const values = data.map(d => parseInt(d.total));

    this.charts.evolucion = chart;
    chart.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
      xAxis: { type: 'category', data: labels, boundaryGap: false },
      yAxis: { type: 'value', splitLine: { lineStyle: { type: 'dashed' } } },
      series: [{
        type: 'line',
        data: values,
        smooth: true,
        areaStyle: { color: 'rgba(44, 82, 130, 0.2)' },
        itemStyle: { color: this.colors.primary },
        lineStyle: { width: 3 }
      }]
    });
  },

  renderChartMedicos(data) {
    const chart = this.initChart('chart-medicos');
    if (!chart) return;

    const labels = data.map(d => `${d.nombre} ${d.apellidos}`.substring(0, 15));
    const values = data.map(d => parseInt(d.total));

    this.charts.medicos = chart;
    chart.setOption({
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
      xAxis: { type: 'value', splitLine: { lineStyle: { type: 'dashed' } } },
      yAxis: { type: 'category', data: labels.reverse() },
      series: [{
        type: 'bar',
        data: values.reverse(),
        itemStyle: { color: this.colors.success, borderRadius: [0, 4, 4, 0] },
        barWidth: '60%'
      }]
    });
  },

  renderChartDias(data) {
    const chart = this.initChart('chart-dias');
    if (!chart) return;

    const diasSemana = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    const values = [0, 0, 0, 0, 0];
    
    data.forEach(d => {
      const index = parseInt(d.dia) - 1;
      if (index >= 0 && index < 5) {
        values[index] = parseInt(d.total);
      }
    });

    this.charts.dias = chart;
    chart.setOption({
      tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
      grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
      xAxis: { type: 'category', data: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] },
      yAxis: { type: 'value', splitLine: { lineStyle: { type: 'dashed' } } },
      series: [{
        type: 'bar',
        data: values,
        itemStyle: { color: this.colors.purple, borderRadius: [4, 4, 0, 0] },
        barWidth: '50%'
      }]
    });
  },

  getLabelEstado(estado) {
    const labels = {
      pendiente: 'Pendiente',
      confirmada: 'Confirmada',
      cancelada: 'Cancelada',
      completada: 'Completada'
    };
    return labels[estado] || estado;
  },

  getColorEstado(estado) {
    const colors = {
      pendiente: this.colors.warning,
      confirmada: this.colors.success,
      cancelada: this.colors.danger,
      completada: this.colors.primary
    };
    return colors[estado] || this.colors.gray;
  },

  formatMes(mes) {
    if (!mes) return '';
    const [year, month] = mes.split('-');
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return `${meses[parseInt(month) - 1]} ${year}`;
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const script = document.createElement('script');
  script.src = 'https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js';
  script.onload = () => AdminDashboard.init();
  document.head.appendChild(script);
});

window.addEventListener('resize', () => {
  Object.values(AdminDashboard.charts).forEach(chart => chart?.resize());
});
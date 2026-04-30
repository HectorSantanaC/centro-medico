const AdminDashboard = {
  apiUrl: 'api/dashboard.php',
  charts: {},
  filtros: {
    evolucion: '12',
    especialidad: '',
    medicos: ''
  },
  colors: {
    primary: '#2c5282',
    success: '#38a169',
    warning: '#d69e2e',
    danger: '#e53e3e',
    gray: '#718096',
    purple: '#805ad5'
  },

  init() {
    this.bindEvents();
    this.cargarStats();
    this.cargarTodosGraficos();
  },

  bindEvents() {
    document.getElementById('filtro-evolucion')?.addEventListener('change', (e) => {
      this.filtros.evolucion = e.target.value;
      this.cargarGrafico('evolucion');
    });
    document.getElementById('filtro-especialidad')?.addEventListener('change', (e) => {
      this.filtros.especialidad = e.target.value;
      this.cargarGrafico('especialidad');
    });
    document.getElementById('filtro-medicos')?.addEventListener('change', (e) => {
      this.filtros.medicos = e.target.value;
      this.cargarGrafico('medicos');
    });
  },

  async cargarTodosGraficos() {
    try {
      const url = `${this.apiUrl}?meses=12`;
      const response = await fetch(url, { credentials: 'same-origin' });
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      const stats = await response.json();

      if (!stats.citas) {
        return;
      }

      this.renderChartEstado(stats.citas.por_estado || []);
      this.renderChartEspecialidad(stats.citas.por_especialidad || []);
      this.renderChartEvolucion(stats.citas.evolucion_mensual || []);
      this.renderChartMedicos(stats.citas.por_medico || []);
      this.renderChartDias(stats.citas.por_dia_semana || []);
    } catch (error) {
      console.error('Error cargando gráficos:', error);
      document.querySelectorAll('.chart-container').forEach(el => {
        el.innerHTML = '<p style="text-align:center;padding:50px;color:#e53e3e;">Error al cargar datos</p>';
      });
    }
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

  async cargarGrafico(tipo) {
    try {
      let url = this.apiUrl;
      if (tipo === 'evolucion') {
        url += `?meses=${this.filtros.evolucion}`;
      } else if (tipo === 'especialidad' && this.filtros.especialidad) {
        url += `?año=${this.filtros.especialidad}`;
      } else if (tipo === 'medicos' && this.filtros.medicos) {
        url += `?año=${this.filtros.medicos}`;
      } else if (tipo === 'especialidad' || tipo === 'medicos') {
        url += `?meses=12`;
      }
      
      const response = await fetch(url, { credentials: 'same-origin' });
      const stats = await response.json();

      if (tipo === 'evolucion') {
        this.renderChartEvolucion(stats.citas?.evolucion_mensual || []);
      } else if (tipo === 'especialidad') {
        this.renderChartEspecialidad(stats.citas?.por_especialidad || []);
      } else if (tipo === 'medicos') {
        this.renderChartMedicos(stats.citas?.por_medico || []);
      }
    } catch (error) {
      console.error(`Error cargando gráfico ${tipo}:`, error);
    }
  },

  initChart(id) {
    const el = document.getElementById(id);
    if (!el) {
      return null;
    }
    const chart = echarts.init(el);
    this.charts[id] = chart;
    return chart;
  },

  renderChartEstado(data) {
    const chart = this.initChart('chart-estado');
    if (!chart) {
      document.getElementById('chart-estado').innerHTML = '<p style="text-align:center;padding:50px;">Gráfico no disponible</p>';
      return;
    }

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
  if (typeof echarts !== 'undefined') {
    AdminDashboard.init();
  } else {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js';
    script.onload = () => AdminDashboard.init();
    script.onerror = () => {
      document.querySelectorAll('.chart-container').forEach(el => {
        el.innerHTML = '<p style="text-align:center;padding:50px;color:#e53e3e;">Error al cargar gráfico</p>';
      });
    };
    document.head.appendChild(script);
  }
});

window.addEventListener('resize', () => {
  Object.values(AdminDashboard.charts).forEach(chart => chart?.resize());
});
document.addEventListener('DOMContentLoaded', function () {
  const especialidadSelect = document.getElementById('especialidadSelect');
  const medicoSelect = document.getElementById('medicoSelect');

  if (especialidadSelect && medicoSelect) {
    function filtrarMedicos() {
      const especialidadId = especialidadSelect.value;
      Array.from(medicoSelect.options).forEach(opt => {
        if (!opt.value) return;
        const espId = opt.dataset.especialidad || '';
        opt.style.display = (!especialidadId || espId === especialidadId) ? '' : 'none';
      });
      if (especialidadId && medicoSelect.value) {
        const selectedEsp = medicoSelect.options[medicoSelect.selectedIndex].dataset.especialidad || '';
        if (selectedEsp !== especialidadId) {
          medicoSelect.value = '';
        }
      }
    }
    especialidadSelect.addEventListener('change', filtrarMedicos);
    filtrarMedicos();
  }
});

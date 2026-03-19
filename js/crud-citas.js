document.addEventListener('DOMContentLoaded', function() {
  const especialidadSelect = document.getElementById('especialidadSelect');
  const medicoSelect = document.getElementById('medicoSelect');
  
  if (especialidadSelect && medicoSelect) {
    function filtrarMedicos() {
      const especialidadId = especialidadSelect.value;
      Array.from(medicoSelect.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!especialidadId || opt.dataset.especialidad === especialidadId) ? '' : 'none';
      });
      if (especialidadId && medicoSelect.value && medicoSelect.options[medicoSelect.selectedIndex].dataset.especialidad !== especialidadId) {
        medicoSelect.value = '';
      }
    }
    especialidadSelect.addEventListener('change', filtrarMedicos);
    filtrarMedicos();
  }
});

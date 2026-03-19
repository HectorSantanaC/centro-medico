document.addEventListener('DOMContentLoaded', function() {
  const espSelect = document.getElementById('especialidad_id');
  const medSelect = document.getElementById('medico_id');
  const fechaInput = document.getElementById('fecha_cita');
  const horaSelect = document.querySelector('select[name="hora_cita"]');

  function updateRequiredFields() {
    const hasMedico = medSelect.value !== '';
    fechaInput.required = hasMedico;
    horaSelect.required = hasMedico;
  }

  espSelect.addEventListener('change', function() {
    const espId = this.value;
    medSelect.innerHTML = '<option value="">Cargando médicos...</option>';
    medSelect.required = !!espId;

    if (!espId) {
      medSelect.innerHTML = '<option value="">Selecciona especialidad primero</option>';
      updateRequiredFields();
      return;
    }

    fetch('cita-online.php?get_medicos=1&especialidad_id=' + espId)
      .then(r => r.json())
      .then(data => {
        let options = '<option value="">Selecciona médico...</option>';
        data.forEach(m => {
          options += `<option value="${m.id}">${m.nombre_completo}</option>`;
        });
        medSelect.innerHTML = options;
        updateRequiredFields();
      })
      .catch(() => {
        medSelect.innerHTML = '<option value="">Error al cargar médicos</option>';
      });
  });

  medSelect.addEventListener('change', updateRequiredFields);
});

document.addEventListener('DOMContentLoaded', function () {
  const espSelect = document.getElementById('especialidad_id');
  const medSelect = document.getElementById('medico_id');
  const fechaInput = document.getElementById('fecha_cita');
  const horaSelect = document.getElementById('hora_cita');

  function updateRequiredFields() {
    const hasMedico = medSelect.value !== '';
    fechaInput.required = hasMedico;
    horaSelect.required = hasMedico;
  }

  function cargarHorasDisponibles() {
    const fecha = fechaInput.value;
    const medicoId = medSelect.value;

    if (!fecha || !medicoId) {
      horaSelect.innerHTML = '<option value="">Selecciona fecha y médico primero</option>';
      return;
    }

    horaSelect.innerHTML = '<option value="">Cargando horas...</option>';

    fetch('api/horas.php?fecha=' + fecha + '&medico_id=' + medicoId)
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          horaSelect.innerHTML = '<option value="">Error: ' + data.error + '</option>';
          return;
        }

        if (!data.horas || data.horas.length === 0) {
          horaSelect.innerHTML = '<option value="">No hay horas disponibles</option>';
          return;
        }

        let options = '<option value="">Selecciona hora...</option>';
        data.horas.forEach(h => {
          options += '<option value="' + h + '">' + h + '</option>';
        });
        horaSelect.innerHTML = options;
      })
      .catch(() => {
        horaSelect.innerHTML = '<option value="">Error al cargar horas</option>';
      });
  }

  espSelect.addEventListener('change', function () {
    const espId = this.value;
    medSelect.innerHTML = '<option value="">Cargando médicos...</option>';
    medSelect.required = !!espId;

    if (!espId) {
      medSelect.innerHTML = '<option value="">Selecciona especialidad primero</option>';
      updateRequiredFields();
      return;
    }

    fetch('api/medicos.php?especialidad_id=' + espId)
      .then(r => r.json())
      .then(data => {
        let options = '<option value="">Selecciona médico...</option>';
        data.forEach(m => {
          options += '<option value="' + m.id + '">' + m.nombre_completo + '</option>';
        });
        medSelect.innerHTML = options;
        updateRequiredFields();
      })
      .catch(() => {
        medSelect.innerHTML = '<option value="">Error al cargar médicos</option>';
      });
  });

  medSelect.addEventListener('change', function () {
    updateRequiredFields();
    cargarHorasDisponibles();
  });

  fechaInput.addEventListener('change', cargarHorasDisponibles);
});

// Validar formularios antes de enviar
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', e => {
    const email = form.querySelector('input[type="email"]').value;
    if (!email.includes('@')) {
      alert('Email inválido');
      e.preventDefault();
    }
  });
});

// Submenú: habilita toggling en pantallas táctiles (o cuando el hover no funciona)
document.querySelectorAll('.menu > li').forEach(menuItem => {
  const submenu = menuItem.querySelector('.submenu');
  if (!submenu) return;

  // Si el usuario toca el enlace principal, mostramos/ocultamos el submenú
  const trigger = menuItem.querySelector('a');
  if (!trigger) return;

  trigger.addEventListener('click', e => {
    // Evitar que el click navegue si el submenú está presente
    e.preventDefault();
    submenu.classList.toggle('mostrar');
  });
});

// Carousels con flechas (especialidades y equipo)
document.addEventListener('DOMContentLoaded', function () {
  const itemWidth = 272;

  document.querySelectorAll('[id^="carousel-"]').forEach(carousel => {
    const wrapper = carousel.parentElement;
    if (!wrapper) return;

    const prev = wrapper.querySelector('.carousel-btn.prev');
    const next = wrapper.querySelector('.carousel-btn.next');

    if (prev) {
      prev.addEventListener('click', () => {
        carousel.scrollBy({ left: -itemWidth, behavior: 'smooth' });
      });
    }

    if (next) {
      next.addEventListener('click', () => {
        carousel.scrollBy({ left: itemWidth, behavior: 'smooth' });
      });
    }
  });
});

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

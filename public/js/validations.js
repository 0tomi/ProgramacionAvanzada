// Validaciones y alertas para formularios

document.addEventListener('DOMContentLoaded', function() {
    // Validación de registro
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let email = document.getElementById('email').value.trim();
            let password = document.getElementById('password').value.trim();
            let errors = [];
            if (!email.match(/^\S+@\S+\.\S+$/)) {
                errors.push('El correo no es válido.');
            }
            if (password.length < 7) {
                errors.push('La contraseña debe tener al menos 7 caracteres.');
            }
            if (errors.length > 0) {
                alert(errors.join('\n'));
                e.preventDefault();
            }
        });
    }

    // Validación de creación de nota
    const noteForm = document.getElementById('noteForm');
    if (noteForm) {
        noteForm.addEventListener('submit', function(e) {
            let username = document.getElementById('username').value.trim();
            let firstName = document.getElementById('first-name').value.trim();
            let lastName = document.getElementById('last-name').value.trim();
            let body = document.getElementById('body').value.trim();
            let errors = [];
            if (username.length < 3) {
                errors.push('El nombre de usuario debe tener al menos 3 caracteres.');
            }
            if (firstName.length < 2) {
                errors.push('El nombre debe tener al menos 2 caracteres.');
            }
            if (lastName.length < 2) {
                errors.push('El apellido debe tener al menos 2 caracteres.');
            }
            if (body.length < 10) {
                errors.push('La nota debe tener al menos 10 caracteres.');
            }
            if (errors.length > 0) {
                alert(errors.join('\n'));
                e.preventDefault();
            }
        });
    }

    // Alerta para correo duplicado (puedes disparar esto desde PHP con JS si detectas el error)
    if (window.location.search.includes('error=correo_duplicado')) {
        alert('Este correo ya está registrado. Por favor inicia sesión.');
    }
});

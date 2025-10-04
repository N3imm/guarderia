<?php
// public/register.php
require_once '../includes/functions.php';
require_once '../viewmodels/AuthViewModel.php';
require_once '../config/config.php';

$authViewModel = new AuthViewModel();
$error_messages = [];
$success_message = '';

// Redirigir si ya está logueado
if ($authViewModel->isLoggedIn()) {
    if ($authViewModel->isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('client/dashboard.php');
    }
}

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $authViewModel->register($_POST);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        redirect('login.php');
    } else {
        $error_messages = $result['errors'];
    }
}

$page_title = 'Crear Cuenta';
$body_class = 'auth-page';
$css_path = '../';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Guardería Happy Pets</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="auth-card row g-0">
                    <!-- Lado izquierdo - Información -->
                    <div class="col-lg-6 auth-left">
                        <div class="auth-logo">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="mb-3">¡Únete a Happy Pets!</h2>
                        <p class="mb-4">
                            Crea tu cuenta y disfruta de nuestros servicios profesionales 
                            de cuidado para mascotas. Tu mejor amigo merece lo mejor.
                        </p>
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle me-3 text-success"></i>
                                <span>Cuidado profesional las 24 horas</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle me-3 text-success"></i>
                                <span>Seguimiento en tiempo real</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle me-3 text-success"></i>
                                <span>Personal veterinario calificado</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-3 text-success"></i>
                                <span>Instalaciones seguras y cómodas</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lado derecho - Formulario -->
                    <div class="col-lg-6 auth-right">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary">Crear Cuenta</h3>
                            <p class="text-muted">Completa tus datos para registrarte</p>
                        </div>

                        <?php if (!empty($error_messages)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <ul class="mb-0">
                                    <?php foreach ($error_messages as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nombre *
                                    </label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                                           placeholder="Tu nombre" required>
                                    <div class="invalid-feedback">
                                        Por favor, ingresa tu nombre.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">
                                        <i class="fas fa-user me-2"></i>Apellido *
                                    </label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                           placeholder="Tu apellido" required>
                                    <div class="invalid-feedback">
                                        Por favor, ingresa tu apellido.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-at me-2"></i>Nombre de Usuario *
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                       placeholder="Elige un nombre de usuario" required minlength="3">
                                <div class="invalid-feedback">
                                    El nombre de usuario debe tener al menos 3 caracteres.
                                </div>
                                <small class="text-muted">Mínimo 3 caracteres, solo letras, números y guiones bajos.</small>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       placeholder="tu@email.com" required>
                                <div class="invalid-feedback">
                                    Por favor, ingresa un email válido.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-2"></i>Teléfono
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       placeholder="3001234567" pattern="[0-9]{10}">
                                <div class="invalid-feedback">
                                    Por favor, ingresa un teléfono válido (10 dígitos).
                                </div>
                                <small class="text-muted">Opcional. Formato: 10 dígitos sin espacios.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Mínimo 6 caracteres" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        La contraseña debe tener al menos 6 caracteres.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirmar *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Repite la contraseña" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        Las contraseñas no coinciden.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la 
                                    <a href="#" class="text-primary">política de privacidad</a> *
                                </label>
                                <div class="invalid-feedback">
                                    Debes aceptar los términos y condiciones.
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Quiero recibir noticias y ofertas especiales por email
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Crear Mi Cuenta
                            </button>

                            <div class="text-center">
                                <p class="mb-0">¿Ya tienes una cuenta?</p>
                                <a href="login.php" class="text-primary text-decoration-none fw-bold">
                                    <i class="fas fa-sign-in-alt me-2"></i>Inicia Sesión Aquí
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    function setupPasswordToggle(toggleId, passwordId) {
        const toggle = document.getElementById(toggleId);
        const password = document.getElementById(passwordId);
        
        toggle.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    setupPasswordToggle('togglePassword', 'password');
    setupPasswordToggle('toggleConfirmPassword', 'confirm_password');
    
    // Form validation
    const form = document.querySelector('.needs-validation');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    // Validar que las contraseñas coincidan
    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
    
    // Validación del formulario
    form.addEventListener('submit', function(event) {
        validatePasswords();
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
    
    // Validar username en tiempo real
    const username = document.getElementById('username');
    username.addEventListener('input', function() {
        const value = this.value;
        const regex = /^[a-zA-Z0-9_]+$/;
        
        if (value.length > 0 && !regex.test(value)) {
            this.setCustomValidity('Solo se permiten letras, números y guiones bajos');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Validar teléfono
    const phone = document.getElementById('phone');
    phone.addEventListener('input', function() {
        // Solo permitir números
        this.value = this.value.replace(/[^0-9]/g, '');
        
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });
});
</script>

</body>
</html>
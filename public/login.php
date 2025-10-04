<?php
// public/login.php
require_once '../includes/functions.php';
require_once '../viewmodels/AuthViewModel.php';
require_once '../config/config.php';

$authViewModel = new AuthViewModel();

// --- Lógica de Mensajes (Patrón PRG) ---
$error_message = '';
$success_message = '';

// Mover mensajes de la sesión a variables locales
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
// -----------------------------------------

// Redirigir si ya está logueado
if ($authViewModel->isLoggedIn()) {
    if ($authViewModel->isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('client/dashboard.php');
    }
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $authViewModel->login($username, $password);
    
    if ($result['success']) {
        redirect($result['redirect']);
    } else {
        // Guardar error en sesión y redirigir
        $_SESSION['error_message'] = implode('<br>', $result['errors']);
        redirect('login.php');
    }
}

$page_title = 'Iniciar Sesión';
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
                            <i class="fas fa-paw"></i>
                        </div>
                        <h2 class="mb-3">¡Bienvenido a Happy Pets!</h2>
                        <p class="mb-4">
                            Cuidamos de tus mascotas con amor y profesionalismo. 
                            Inicia sesión para acceder a tu cuenta.
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <i class="fas fa-shield-alt fa-2x"></i>
                            <i class="fas fa-heart fa-2x"></i>
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <p class="mt-3 small">Seguridad • Cariño • Disponibilidad 24/7</p>
                    </div>
                    
                    <!-- Lado derecho - Formulario -->
                    <div class="col-lg-6 auth-right">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary">Iniciar Sesión</h3>
                            <p class="text-muted">Accede a tu cuenta de Happy Pets</p>
                        </div>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Usuario o Email
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Ingresa tu usuario o email" required>
                                <div class="invalid-feedback">
                                    Por favor, ingresa tu usuario o email.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Contraseña
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Ingresa tu contraseña" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Por favor, ingresa tu contraseña.
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Recordar mi sesión
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </button>

                            <div class="text-center">
                                <a href="#" class="text-decoration-none text-muted">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            <hr class="my-4">

                            <div class="text-center">
                                <p class="mb-2">¿No tienes una cuenta?</p>
                                <a href="register.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>Crear Cuenta Nueva
                                </a>
                            </div>
                        </form>

                        <!-- Credenciales de demo -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-info-circle me-2"></i>Cuentas de Prueba
                            </h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">
                                        <strong>Administrador:</strong><br>
                                        Usuario: admin<br>
                                        Clave: password
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <strong>Cliente:</strong><br>
                                        Usuario: juan_perez<br>
                                        Clave: password
                                    </small>
                                </div>
                            </div>
                        </div>
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
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
    
    // Form validation
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Llenar credenciales de demo al hacer clic
    document.querySelector('.bg-light').addEventListener('click', function(e) {
        if (e.target.textContent.includes('admin')) {
            document.getElementById('username').value = 'admin';
            document.getElementById('password').value = 'password';
        } else if (e.target.textContent.includes('juan_perez')) {
            document.getElementById('username').value = 'juan_perez';
            document.getElementById('password').value = 'password';
        }
    });
});
</script>

</body>
</html>

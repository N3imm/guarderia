<?php
// public/index.php
require_once '../includes/functions.php';
require_once '../config/config.php';

// Si está logueado, redirigir al dashboard apropiado
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('client/dashboard.php');
    }
}

$page_title = 'Bienvenido';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardería Happy Pets - Cuidado Profesional para tu Mascota</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo ASSETS_URL; ?>css/style.css" rel="stylesheet">
    
    <!-- Meta tags para SEO -->
    <meta name="description" content="Guardería profesional para mascotas en Bogotá. Cuidado 24/7, seguimiento en tiempo real y personal veterinario calificado.">
    <meta name="keywords" content="guardería mascotas, cuidado mascotas, veterinario, Bogotá, perros, gatos">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--primary-dark);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-paw me-2"></i>Happy Pets
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#inicio">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#servicios">Servicios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#nosotros">Nosotros</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contacto">Contacto</a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-light text-primary ms-lg-2 px-3" href="register.php">
                        <i class="fas fa-user-plus me-1"></i>Registrarse
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section id="inicio" class="hero-section" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); min-height: 100vh; display: flex; align-items: center; padding-top: 80px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="fade-in-up">
                    <h1 class="display-3 fw-bold text-white mb-4">
                        Tu mascota en las mejores manos
                    </h1>
                    <p class="fs-5 text-white mb-4" style="opacity: 0.95;">
                        Cuidado profesional, amor incondicional y seguimiento 24/7 para tu mejor amigo.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-light btn-lg px-4 py-3 shadow">
                            <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                        </a>
                        <a href="#servicios" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-info-circle me-2"></i>Más Información
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center fade-in-up">
                    <i class="fas fa-heart text-white" style="font-size: 12rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Servicios -->
<section id="servicios" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold" style="color: var(--primary-color);">Nuestros Servicios</h2>
            <p class="lead text-muted">Ofrecemos cuidado integral para tu mascota</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 text-center border-0 shadow-sm hover-card">
                    <div class="card-body p-4">
                        <div class="service-icon mb-3">
                            <i class="fas fa-home"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Guardería</h5>
                        <p class="card-text text-muted">
                            Cuidado diario profesional en instalaciones seguras y cómodas.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 text-center border-0 shadow-sm hover-card">
                    <div class="card-body p-4">
                        <div class="service-icon mb-3">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Consultas Veterinarias</h5>
                        <p class="card-text text-muted">
                            Atención médica especializada con veterinarios certificados.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 text-center border-0 shadow-sm hover-card">
                    <div class="card-body p-4">
                        <div class="service-icon mb-3">
                            <i class="fas fa-cut"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Grooming</h5>
                        <p class="card-text text-muted">
                            Servicios de belleza y aseo para mantener a tu mascota radiante.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 text-center border-0 shadow-sm hover-card">
                    <div class="card-body p-4">
                        <div class="service-icon mb-3">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">Entrenamiento</h5>
                        <p class="card-text text-muted">
                            Programas de entrenamiento personalizados para cada mascota.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Características -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold" style="color: var(--primary-color);">¿Por qué elegirnos?</h2>
            <p class="lead text-muted">Comprometidos con el bienestar de tu mascota</p>
        </div>
        
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center p-3 rounded shadow-sm bg-white">
                            <div class="rounded-circle p-3 me-3" style="background-color: var(--primary-color);">
                                <i class="fas fa-clock text-white fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Disponibilidad 24/7</h5>
                                <p class="text-muted mb-0">Cuidado continuo y seguimiento en tiempo real</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center p-3 rounded shadow-sm bg-white">
                            <div class="rounded-circle p-3 me-3" style="background-color: var(--success-color);">
                                <i class="fas fa-shield-alt text-white fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Instalaciones Seguras</h5>
                                <p class="text-muted mb-0">Espacios diseñados específicamente para el bienestar animal</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center p-3 rounded shadow-sm bg-white">
                            <div class="rounded-circle p-3 me-3" style="background-color: var(--warning-color);">
                                <i class="fas fa-user-md text-white fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Personal Calificado</h5>
                                <p class="text-muted mb-0">Equipo de veterinarios y cuidadores certificados</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="d-flex align-items-center p-3 rounded shadow-sm bg-white">
                            <div class="rounded-circle p-3 me-3" style="background-color: var(--info-color);">
                                <i class="fas fa-mobile-alt text-white fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Seguimiento Digital</h5>
                                <p class="text-muted mb-0">Plataforma web para monitorear el estado de tu mascota</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center shadow" style="width: 300px; height: 300px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                        <i class="fas fa-paw text-white" style="font-size: 8rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nosotros -->
<section id="nosotros" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold" style="color: var(--primary-color);">Sobre Happy Pets</h2>
            <p class="lead text-muted">Más de 10 años cuidando a las mascotas de Bogotá</p>
        </div>
        
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h3 class="fw-bold mb-3">Nuestra Misión</h3>
                <p class="lead mb-4">
                    Brindar el mejor cuidado y atención a las mascotas, ofreciendo tranquilidad a sus dueños 
                    a través de servicios profesionales y un seguimiento detallado.
                </p>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center p-3 bg-white rounded shadow-sm">
                            <h3 class="display-6 fw-bold" style="color: var(--primary-color);">500+</h3>
                            <small class="text-muted">Mascotas Cuidadas</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-white rounded shadow-sm">
                            <h3 class="display-6 fw-bold" style="color: var(--success-color);">10+</h3>
                            <small class="text-muted">Años de Experiencia</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-white rounded shadow-sm">
                            <h3 class="display-6 fw-bold" style="color: var(--warning-color);">15</h3>
                            <small class="text-muted">Profesionales</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 bg-white rounded shadow-sm">
                            <h3 class="display-6 fw-bold" style="color: var(--info-color);">24/7</h3>
                            <small class="text-muted">Disponibilidad</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="rounded p-4 text-white text-center shadow" style="background-color: var(--primary-color);">
                            <i class="fas fa-heart fa-3x mb-3"></i>
                            <h6>Amor y Cuidado</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded p-4 text-white text-center shadow" style="background-color: var(--success-color);">
                            <i class="fas fa-award fa-3x mb-3"></i>
                            <h6>Calidad Certificada</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded p-4 text-white text-center shadow" style="background-color: var(--warning-color);">
                            <i class="fas fa-handshake fa-3x mb-3"></i>
                            <h6>Confianza</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded p-4 text-white text-center shadow" style="background-color: var(--info-color);">
                            <i class="fas fa-star fa-3x mb-3"></i>
                            <h6>Excelencia</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);">
    <div class="container text-center py-4">
        <h2 class="display-5 fw-bold mb-3">¿Listo para darle lo mejor a tu mascota?</h2>
        <p class="lead mb-4" style="opacity: 0.95;">Únete a Happy Pets y dale a tu mejor amigo el cuidado que se merece</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="register.php" class="btn btn-light btn-lg px-4 py-3 shadow">
                <i class="fas fa-user-plus me-2"></i><strong>Crear Cuenta Gratis</strong>
            </a>
            <a href="#contacto" class="btn btn-outline-light btn-lg px-4 py-3">
                <i class="fas fa-phone me-2"></i><strong>Contactar Ahora</strong>
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer id="contacto" class="text-white py-5" style="background-color: #1a1a1a;">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="mb-3 fw-bold">
                    <i class="fas fa-paw me-2"></i>Happy Pets
                </h5>
                <p style="color: #b0b0b0;">
                    Cuidamos de tus mascotas con amor y profesionalismo. 
                    Tu tranquilidad es nuestra prioridad.
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white fs-4" style="opacity: 0.8;" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-white fs-4" style="opacity: 0.8;" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-white fs-4" style="opacity: 0.8;" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-white fs-4" style="opacity: 0.8;" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-2 col-6">
                <h6 class="mb-3 fw-bold">Servicios</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#servicios" class="text-decoration-none" style="color: #b0b0b0;">Guardería</a></li>
                    <li class="mb-2"><a href="#servicios" class="text-decoration-none" style="color: #b0b0b0;">Consultas</a></li>
                    <li class="mb-2"><a href="#servicios" class="text-decoration-none" style="color: #b0b0b0;">Grooming</a></li>
                    <li class="mb-2"><a href="#servicios" class="text-decoration-none" style="color: #b0b0b0;">Entrenamiento</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-6">
                <h6 class="mb-3 fw-bold">Enlaces</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="register.php" class="text-decoration-none" style="color: #b0b0b0;">Crear Cuenta</a></li>
                    <li class="mb-2"><a href="login.php" class="text-decoration-none" style="color: #b0b0b0;">Iniciar Sesión</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none" style="color: #b0b0b0;">Términos y Condiciones</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none" style="color: #b0b0b0;">Política de Privacidad</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3">
                <h6 class="mb-3 fw-bold">Contacto</h6>
                <ul class="list-unstyled" style="color: #b0b0b0;">
                    <li class="mb-2">
                        <i class="fas fa-phone me-2"></i>+57 323 2266 112
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2"></i>info@happypets.com
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>Carrera 28a #88-37<br>
                        <span class="ms-4">Bucaramanga, Colombia</span>
                    </li>
                    <li>
                        <i class="fas fa-clock me-2"></i>24/7 Disponible
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="my-4" style="border-color: #404040;">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <small style="color: #808080;">
                    &copy; <?php echo date('Y'); ?> Happy Pets. Todos los derechos reservados.
                </small>
            </div>
            <div class="col-md-6 text-md-end">
                <small style="color: #808080;">
                    Hecho con <i class="fas fa-heart text-danger"></i> para las mascotas
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo ASSETS_URL; ?>js/app.js"></script>

<!-- Smooth Scroll -->
<script>
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const offset = 70; // Altura del navbar
            const targetPosition = target.offsetTop - offset;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('navbar-scrolled');
    } else {
        navbar.classList.remove('navbar-scrolled');
    }
});
</script>

</body>
</html>
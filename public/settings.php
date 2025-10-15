<?php
// public/settings.php
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../config/config.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    redirect(BASE_URL . 'login.php');
}

$page_title = 'Configuración';
include '../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-cog me-2"></i>Configuración</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Preferencias de Visualización -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-palette me-2"></i>Preferencias de Visualización</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkModeToggle">
                    <label class="form-check-label" for="darkModeToggle">Modo Oscuro</label>
                </div>
                <div class="form-text mt-2">
                    Activa el modo oscuro para reducir el brillo de la pantalla.
                </div>
            </div>
        </div>

        <!-- Otras configuraciones pueden ir aquí -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-bell me-2"></i>Notificaciones (Próximamente)</h5>
            </div>
            <div class="card-body text-muted">
                <p>Aquí podrás configurar tus preferencias de notificaciones por correo electrónico.</p>
                <p class="mb-0"><em>Esta función aún no está disponible.</em></p>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>

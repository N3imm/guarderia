<?php
// public/client/dashboard.php
require_once '../../includes/functions.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/DashboardViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$dashboardViewModel = new DashboardViewModel();

// Verificar que el usuario esté logueado
$sessionResult = $authViewModel->validateSession();
if (!$sessionResult['success']) {
    redirect($sessionResult['redirect']);
}

// Redirigir administradores a su dashboard
if ($authViewModel->isAdmin()) {
    redirect('admin/dashboard.php');
}

$user_id = $_SESSION['user_id'];

// Obtener estadísticas del dashboard
$statsResult = $dashboardViewModel->getClientDashboardStats($user_id);
$stats = $statsResult['success'] ? $statsResult['data'] : [];

$page_title = 'Mi Panel';
$css_path = '../../';

include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
        ¡Hola, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i>Actualizar
            </button>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-plus me-1"></i>Acciones
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="appointments.php?action=add">
                    <i class="fas fa-calendar-plus me-2"></i>Nueva Cita
                </a></li>
                <li><a class="dropdown-item" href="my_pets.php">
                    <i class="fas fa-paw me-2"></i>Ver Mis Mascotas
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="visits.php">
                    <i class="fas fa-history me-2"></i>Historial de Visitas
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Mensajes de sesión -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Resumen de estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-muted mb-2">Mis Mascotas</h6>
                        <span class="h2 font-weight-bold mb-0"><?php echo $stats['total_my_pets'] ?? 0; ?></span>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-paw fa-2x"></i>
                    </div>
                </div>
                <p class="mb-0 mt-2">
                    <small class="text-muted">
                        <i class="fas fa-heart me-1"></i>Registradas en el sistema
                    </small>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-orange">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-muted mb-2">Mis Citas</h6>
                        <span class="h2 font-weight-bold mb-0"><?php echo $stats['total_my_appointments'] ?? 0; ?></span>
                    </div>
                    <div style="color: #fd7e14;">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
                <p class="mb-0 mt-2">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>Total programadas
                    </small>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-green">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-muted mb-2">Visitas</h6>
                        <span class="h2 font-weight-bold mb-0"><?php echo $stats['total_my_visits'] ?? 0; ?></span>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-history fa-2x"></i>
                    </div>
                </div>
                <p class="mb-0 mt-2">
                    <small class="text-muted">
                        <i class="fas fa-check-circle me-1"></i>Visitas realizadas
                    </small>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-purple">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase text-muted mb-2">Próximas</h6>
                        <span class="h2 font-weight-bold mb-0"><?php echo count($stats['my_upcoming_appointments'] ?? []); ?></span>
                    </div>
                    <div style="color: #6f42c1;">
                        <i class="fas fa-calendar-day fa-2x"></i>
                    </div>
                </div>
                <p class="mb-0 mt-2">
                    <small class="text-muted">
                        <i class="fas fa-arrow-right me-1"></i>Citas pendientes
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<div class="row">
    <!-- Estado de Mis Mascotas -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-paw me-2 text-primary"></i>Estado de Mis Mascotas
                    </h5>
                    <a href="my_pets.php" class="btn btn-sm btn-outline-primary">
                        Ver Todas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['pets_status'])): ?>
                    <div class="row g-3">
                        <?php foreach ($stats['pets_status'] as $petData): ?>
                            <?php $pet = $petData['pet']; $status = $petData['status']; ?>
                            <div class="col-lg-6 mb-3">
                                <div class="card border" data-pet-id="<?php echo $pet['id']; ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($pet['photo']) && file_exists(__DIR__ . '/../../assets/images/uploads/' . $pet['photo'])): ?>
                                                <img src="../../assets/images/uploads/<?php echo htmlspecialchars($pet['photo']); ?>" 
                                                     class="pet-photo me-3" 
                                                     alt="<?php echo htmlspecialchars($pet['name']); ?>"
                                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="pet-photo me-3 bg-light align-items-center justify-content-center" style="display:none;">
                                                    <i class="fas fa-<?php echo $pet['species'] === 'perro' ? 'dog' : 'cat'; ?> fa-2x text-muted"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="pet-photo me-3 bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-<?php echo $pet['species'] === 'perro' ? 'dog' : 'cat'; ?> fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($pet['name']); ?></h6>
                                                <p class="text-muted small mb-2">
                                                    <?php echo ucfirst($pet['species']); ?>
                                                    <?php if (!empty($pet['breed'])): ?>
                                                        - <?php echo htmlspecialchars($pet['breed']); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if ($status): ?>
                                                    <span class="status-badge status-<?php echo $status['status']; ?>">
                                                        <i class="fas fa-circle me-1"></i>
                                                        <?php echo ucfirst(str_replace('_', ' ', $status['status'])); ?>
                                                    </span>
                                                    <?php if (!empty($status['status_description'])): ?>
                                                        <p class="small text-muted mt-2 mb-0 status-description">
                                                            <?php echo htmlspecialchars($status['status_description']); ?>
                                                        </p>
                                                        <small class="text-muted status-time">
                                                            <?php echo date('d/m H:i', strtotime($status['created_at'])); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted">Sin actualizaciones</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-paw fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted mb-3">No tienes mascotas registradas</h5>
                        <p class="text-muted mb-3">Para usar nuestros servicios, necesitas tener al menos una mascota registrada.</p>
                        <a href="my_pets.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar Mi Primera Mascota
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Próximas Citas y Actividad Reciente -->
    <div class="col-lg-4">
        <!-- Próximas Citas -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-day me-2 text-primary"></i>Próximas Citas
                    </h6>
                    <a href="appointments.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['my_upcoming_appointments'])): ?>
                    <?php foreach ($stats['my_upcoming_appointments'] as $appointment): ?>
                        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                            <div class="me-3">
                                <i class="fas fa-<?php echo $appointment['species'] === 'perro' ? 'dog' : 'cat'; ?> text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold small"><?php echo htmlspecialchars($appointment['pet_name']); ?></div>
                                <div class="text-muted small">
                                    <?php echo date('d/m/Y H:i', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time'])); ?>
                                </div>
                                <span class="badge bg-secondary small"><?php echo ucfirst($appointment['service_type']); ?></span>
                            </div>
                            <div>
                                <span class="appointment-status appointment-<?php echo $appointment['status']; ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <a href="appointments.php?action=add" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Nueva Cita
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                        <p class="text-muted small mb-2">No tienes citas próximas</p>
                        <a href="appointments.php?action=add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Programar Cita
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial Reciente -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>Visitas Recientes
                    </h6>
                    <a href="visits.php" class="btn btn-sm btn-outline-primary">Ver Historial</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['my_recent_visits'])): ?>
                    <div class="timeline">
                        <?php foreach ($stats['my_recent_visits'] as $visit): ?>
                            <div class="timeline-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-<?php echo $visit['species'] === 'perro' ? 'dog' : 'cat'; ?> text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small"><?php echo htmlspecialchars($visit['pet_name']); ?></h6>
                                        <p class="text-muted small mb-1"><?php echo htmlspecialchars($visit['services_provided']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($visit['visit_date'])); ?>
                                            </small>
                                            <?php if (!empty($visit['check_in_time']) && !empty($visit['check_out_time'])): ?>
                                                <?php 
                                                $duration = strtotime($visit['check_out_time']) - strtotime($visit['check_in_time']);
                                                $hours = floor($duration / 3600);
                                                $minutes = floor(($duration % 3600) / 60);
                                                ?>
                                                <small class="badge bg-secondary">
                                                    <?php echo $hours; ?>h <?php echo $minutes; ?>m
                                                </small>
                                            <?php elseif (!empty($visit['check_in_time'])): ?>
                                                <small class="badge bg-success">En guardería</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-history fa-2x text-muted mb-2"></i>
                        <p class="text-muted small mb-0">No hay visitas registradas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Información de Contacto -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1">
                            <i class="fas fa-headset me-2 text-primary"></i>¿Necesitas ayuda?
                        </h5>
                        <p class="text-muted mb-0">
                            Nuestro equipo está disponible 24/7 para responder cualquier pregunta sobre el cuidado de tu mascota.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <a href="tel:+573001234567" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-phone me-1"></i>Llamar
                            </a>
                            <a href="mailto:info@happypets.com" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-envelope me-1"></i>Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Consejos y Tips -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 bg-pet-gradient text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-2">
                            <i class="fas fa-lightbulb me-2"></i>Consejo del Día
                        </h5>
                        <p class="mb-0">
                            <?php 
                            $tips = [
                                "Asegúrate de que tu mascota tenga siempre agua fresca disponible.",
                                "El ejercicio diario es fundamental para la salud física y mental de tu mascota.",
                                "Las visitas regulares al veterinario ayudan a prevenir problemas de salud.",
                                "Una dieta balanceada contribuye significativamente al bienestar de tu mascota.",
                                "El tiempo de juego fortalece el vínculo entre tú y tu mascota."
                            ];
                            echo $tips[array_rand($tips)];
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <i class="fas fa-heart fa-3x" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript específico del dashboard -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const REFRESH_INTERVAL = 300000; // 5 minutos
    
    // Actualizar estados cada 5 minutos
    setInterval(() => {
        if (!document.hidden) {
            updatePetStatus();
        }
    }, REFRESH_INTERVAL);
});

// Función para actualizar el estado de las mascotas
const updatePetStatus = async () => {
    const formatDate = date => new Date(date).toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });

    const updateElements = (petCard, status) => {
        const elements = {
            badge: petCard.querySelector('.status-badge'),
            desc: petCard.querySelector('.status-description'),
            time: petCard.querySelector('.status-time')
        };

        if (elements.badge) {
            elements.badge.className = `status-badge status-${status.status}`;
            elements.badge.innerHTML = `<i class="fas fa-circle me-1"></i>${status.status.replace('_', ' ')}`;
        }
        
        if (elements.desc && status.status_description) {
            elements.desc.textContent = status.status_description;
        }
        
        if (elements.time) {
            elements.time.textContent = formatDate(status.created_at);
        }
    };

    try {
        const response = await fetch('../../controllers/dashboard_controller.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_pet_status'
        });
        
        const { success, data } = await response.json();
        if (success && Array.isArray(data)) {
            data.forEach(({ pet, status }) => {
                const petCard = document.querySelector(`[data-pet-id="${pet.id}"]`);
                if (petCard && status) {
                    updateElements(petCard, status);
                }
            });
            console.log('Estados de mascotas actualizados');
        }
    } catch (error) {
        console.error('Error al actualizar estados:', error);
    }
};

// Manejo de errores de imágenes
document.addEventListener('DOMContentLoaded', () => {
    const petImages = document.querySelectorAll('.pet-photo[src]');
    petImages.forEach(img => {
        img.addEventListener('error', function() {
            console.warn('Error cargando imagen:', this.src);
            this.style.display = 'none';
            const placeholder = this.nextElementSibling;
            if (placeholder) {
                placeholder.style.display = 'flex';
            }
        });
    });
});

// Auto-dismiss alerts después de 5 segundos
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

<?php include '../../views/layouts/footer.php'; ?>
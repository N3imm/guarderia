<?php
// public/admin/dashboard.php
require_once '../../includes/functions.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/DashboardViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$dashboardViewModel = new DashboardViewModel();

// Verificar que sea administrador
$sessionResult = $authViewModel->validateAdminSession();
if (!$sessionResult['success']) {
    redirect($sessionResult['redirect']);
}

// Obtener estadísticas del dashboard
$statsResult = $dashboardViewModel->getAdminDashboardStats();
$stats = $statsResult['success'] ? $statsResult['data'] : [];

// Obtener alertas
$alertsResult = $dashboardViewModel->getAlerts();
$alerts = $alertsResult['success'] ? $alertsResult['data'] : [];

// Obtener actividad reciente
$activityResult = $dashboardViewModel->getRecentActivity(8);
$activities = $activityResult['success'] ? $activityResult['data'] : [];

$page_title = 'Dashboard Administrativo';
$css_path = '../../';

include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard Administrativo
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i>Actualizar
            </button>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-plus me-1"></i>Acciones Rápidas
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="pets.php?action=add">
                    <i class="fas fa-plus me-2"></i>Nueva Mascota
                </a></li>
                <li><a class="dropdown-item" href="appointments.php?action=add">
                    <i class="fas fa-calendar-plus me-2"></i>Nueva Cita
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="visits.php?action=checkin">
                    <i class="fas fa-sign-in-alt me-2"></i>Check-in Rápido
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Alertas y Notificaciones -->
<?php if (!empty($alerts)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert-container">
            <?php foreach ($alerts as $alert): ?>
                <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                    <i class="fas <?php echo $alert['icon']; ?> me-2"></i>
                    <strong><?php echo $alert['message']; ?></strong>
                    <?php if (isset($alert['action'])): ?>
                        <a href="<?php echo $alert['link']; ?>" class="alert-link ms-2">
                            <?php echo $alert['action']; ?>
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tarjetas de Estadísticas Rápidas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Total Mascotas</h6>
                        <span class="h2 font-weight-bold mb-0"><?php echo $stats['total_pets'] ?? 0; ?></span>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-paw fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-orange">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Total Clientes</h6>
                        <span class="h2 font-weight-bold mb-0"><?php echo $stats['total_clients'] ?? 0; ?></span>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-green">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Check-ins Activos</h6>
                        <span class="h2 font-weight-bold mb-0"><?php echo $stats['active_checkins'] ?? 0; ?></span>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card-blue">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Citas Pendientes</h6>
                        <span class="h2 font-weight-bold mb-0"><?php echo $stats['pending_appointments'] ?? 0; ?></span>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-calendar-day fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                Mascotas por Especie
            </div>
            <div class="card-body">
                <canvas id="petsBySpeciesChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                Citas por Estado
            </div>
            <div class="card-body">
                <canvas id="appointmentsByStatusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                Actividad Mensual (Visitas)
            </div>
            <div class="card-body">
                <canvas id="monthlyActivityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Mascotas por Especie
        var ctxPets = document.getElementById('petsBySpeciesChart').getContext('2d');
        var petsBySpeciesData = <?php echo json_encode($stats['pets_by_species'] ?? []); ?>;
        new Chart(ctxPets, {
            type: 'pie',
            data: {
                labels: petsBySpeciesData.map(item => item.species),
                datasets: [{
                    data: petsBySpeciesData.map(item => item.total),
                    backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#dc3545', '#6c757d']
                }]
            }
        });

        // Citas por Estado
        var ctxAppointments = document.getElementById('appointmentsByStatusChart').getContext('2d');
        var appointmentsByStatusData = <?php echo json_encode($stats['appointments_by_status'] ?? []); ?>;
        new Chart(ctxAppointments, {
            type: 'doughnut',
            data: {
                labels: appointmentsByStatusData.map(item => item.status),
                datasets: [{
                    data: appointmentsByStatusData.map(item => item.total),
                    backgroundColor: ['#ffc107', '#0d6efd', '#198754', '#dc3545']
                }]
            }
        });

        // Actividad Mensual
        var ctxMonthly = document.getElementById('monthlyActivityChart').getContext('2d');
        var monthlyActivityData = <?php echo json_encode($stats['monthly_activity'] ?? []); ?>;
        new Chart(ctxMonthly, {
            type: 'line',
            data: {
                labels: monthlyActivityData.map(item => item.month + '/' + item.year),
                datasets: [{
                    label: 'Visitas',
                    data: monthlyActivityData.map(item => item.total_visits),
                    borderColor: '#0d6efd',
                    tension: 0.1
                }]
            }
        });
    });
</script>

<?php include '../../views/layouts/footer.php'; ?>

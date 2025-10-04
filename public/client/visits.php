<?php
// public/client/visits.php
require_once '../../includes/functions.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/VisitHistoryViewModel.php';
require_once '../../viewmodels/PetViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$visitViewModel = new VisitHistoryViewModel();
$petViewModel = new PetViewModel();

// Verificar sesión
$sessionResult = $authViewModel->validateSession();
if (!$sessionResult['success']) {
    redirect($sessionResult['redirect']);
}

if ($authViewModel->isAdmin()) {
    redirect('admin/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$pet_id = $_GET['pet_id'] ?? null;

// Obtener datos
if ($pet_id) {
    $visitsResult = $visitViewModel->getVisitHistoryByPet($pet_id, $user_id);
    $petResult = $petViewModel->getPetById($pet_id);
    $pet_name = $petResult['success'] ? $petResult['data']['name'] : 'Mascota';
} else {
    $visitsResult = $visitViewModel->getVisitHistoryByUser($user_id);
}

$visits = $visitsResult['success'] ? $visitsResult['data'] : [];
$petsResult = $petViewModel->getPetsByUser($user_id);
$pets = $petsResult['success'] ? $petsResult['data'] : [];

$page_title = 'Historial de Visitas';
$css_path = '../../';
include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-history me-2 text-primary"></i>
        Historial de Visitas
        <?php if ($pet_id): ?>
            - <?php echo htmlspecialchars($pet_name); ?>
        <?php endif; ?>
    </h1>
    <?php if ($pet_id): ?>
        <a href="visits.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Ver Todas
        </a>
    <?php endif; ?>
</div>

<!-- Filtro por mascota -->
<?php if (!$pet_id && !empty($pets)): ?>
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label for="pet_id" class="form-label">Filtrar por mascota</label>
                    <select class="form-select" id="pet_id" name="pet_id" onchange="this.form.submit()">
                        <option value="">Todas las mascotas</option>
                        <?php foreach ($pets as $pet): ?>
                            <option value="<?php echo $pet['id']; ?>">
                                <?php echo htmlspecialchars($pet['name'] . ' (' . ucfirst($pet['species']) . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Lista de visitas -->
<?php if (!empty($visits)): ?>
    <div class="row">
        <?php foreach ($visits as $visit): ?>
            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <span>
                            <strong><?php echo htmlspecialchars($visit['pet_name'] ?? 'N/A'); ?></strong>
                            <?php if (!$pet_id): ?>
                                <small class="text-muted">- <?php echo ucfirst($visit['species'] ?? ''); ?></small>
                            <?php endif; ?>
                        </span>
                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($visit['visit_date'])); ?></small>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Servicios:</strong> <?php echo htmlspecialchars($visit['services_provided']); ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <?php if ($visit['check_in_time']): ?>
                                    <span class="badge bg-success me-1">
                                        Entrada: <?php echo date('H:i', strtotime($visit['check_in_time'])); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($visit['check_out_time']): ?>
                                    <span class="badge bg-info">
                                        Salida: <?php echo date('H:i', strtotime($visit['check_out_time'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">En guardería</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($visit['check_in_time'] && $visit['check_out_time']): ?>
                                <?php 
                                $duration = strtotime($visit['check_out_time']) - strtotime($visit['check_in_time']);
                                $hours = floor($duration / 3600);
                                $minutes = floor(($duration % 3600) / 60);
                                ?>
                                <small class="text-muted">
                                    Duración: <?php echo $hours; ?>h <?php echo $minutes; ?>m
                                </small>
                            <?php endif; ?>
                        </div>

                        <?php if ($visit['observations']): ?>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <strong>Observaciones:</strong> <?php echo htmlspecialchars(substr($visit['observations'], 0, 100)); ?>
                                    <?php echo strlen($visit['observations']) > 100 ? '...' : ''; ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-history fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">
            <?php if ($pet_id): ?>
                No hay visitas registradas para <?php echo htmlspecialchars($pet_name); ?>
            <?php else: ?>
                No tienes visitas registradas
            <?php endif; ?>
        </h4>
        <p class="text-muted">Las visitas aparecerán aquí después de usar nuestros servicios</p>
        <a href="appointments.php?action=add" class="btn btn-primary">
            <i class="fas fa-calendar-plus me-2"></i>Programar Cita
        </a>
    </div>
<?php endif; ?>

<?php include '../../views/layouts/footer.php'; ?>
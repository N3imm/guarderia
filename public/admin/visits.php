<?php
// public/admin/visits.php
require_once '../../includes/functions.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/VisitHistoryViewModel.php';
require_once '../../viewmodels/PetViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$visitViewModel = new VisitHistoryViewModel();
$petViewModel = new PetViewModel();

// Verificar que sea administrador
$sessionResult = $authViewModel->validateAdminSession();
if (!$sessionResult['success']) {
    redirect($sessionResult['redirect']);
}

// Procesar acciones
$action = $_GET['action'] ?? 'list';
$visit_id = $_GET['id'] ?? null;
$pet_id = $_GET['pet_id'] ?? null;
$active_filter = $_GET['active'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$message = '';
$error = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['action'] ?? '';
    
    switch ($form_action) {
        case 'create':
            $result = $visitViewModel->createVisitEntry($_POST);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                redirect('visits.php');
            } else {
                $error = implode('<br>', $result['errors']);
            }
            break;
            
        case 'update':
            $result = $visitViewModel->updateVisitEntry($_POST['id'], $_POST);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                redirect('visits.php');
            } else {
                $error = implode('<br>', $result['errors']);
            }
            break;
            
        case 'delete':
            $result = $visitViewModel->deleteVisitEntry($_POST['id']);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['error'];
            }
            redirect('visits.php');
            break;
            
        case 'checkin':
            $result = $visitViewModel->checkIn($_POST['pet_id'], $_POST['appointment_id'] ?? null, $_POST['services'] ?? '');
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['error'];
            }
            redirect('visits.php');
            break;
            
        case 'checkout':
            $result = $visitViewModel->checkOut($_POST['pet_id'], $_POST['observations'] ?? '');
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['error'];
            }
            redirect('visits.php');
            break;
    }
}

// Obtener datos según la acción
switch ($action) {
    case 'list':
        if ($active_filter) {
            // Mostrar solo check-ins activos
            $visitsResult = $visitViewModel->getActiveCheckIns();
            $visits = $visitsResult['success'] ? $visitsResult['data'] : [];
        } else {
            // Mostrar historial completo con filtros
            $visitsResult = $visitViewModel->getAllVisitHistory($pet_id, $date_from, $date_to);
            $visits = $visitsResult['success'] ? $visitsResult['data'] : [];
        }
        
        // También obtener check-ins activos para el widget
        $activeResult = $visitViewModel->getActiveCheckIns();
        $activeCheckIns = $activeResult['success'] ? $activeResult['data'] : [];
        break;
        
    case 'view':
        if ($visit_id) {
            $visitResult = $visitViewModel->getVisitById($visit_id);
            $visit = $visitResult['success'] ? $visitResult['data'] : null;
            if (!$visit) {
                $_SESSION['error_message'] = 'Visita no encontrada';
                redirect('visits.php');
            }
        }
        break;
        
    case 'add':
    case 'checkin':
        // Obtener mascotas activas
        $petsResult = $petViewModel->getAllPets();
        $pets = $petsResult['success'] ? $petsResult['data'] : [];
        break;
        
    case 'edit':
        if ($visit_id) {
            $visitResult = $visitViewModel->getVisitById($visit_id);
            $visit = $visitResult['success'] ? $visitResult['data'] : null;
            if (!$visit) {
                $_SESSION['error_message'] = 'Visita no encontrada';
                redirect('visits.php');
            }
        }
        break;
}

$page_title = 'Gestión de Visitas';
$css_path = '../../';

include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-history me-2 text-primary"></i>
        <?php 
        switch ($action) {
            case 'add': echo 'Registrar Nueva Visita'; break;
            case 'checkin': echo 'Check-in Rápido'; break;
            case 'edit': echo 'Editar Visita'; break;
            case 'view': echo 'Detalles de Visita'; break;
            default: echo 'Gestión de Visitas';
        }
        ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($action === 'list'): ?>
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar
                </button>
            </div>
            <div class="btn-group">
                <a href="visits.php?action=checkin" class="btn btn-success">
                    <i class="fas fa-sign-in-alt me-2"></i>Check-in Rápido
                </a>
                <a href="visits.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nueva Visita
                </a>
            </div>
        <?php else: ?>
            <a href="visits.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Historial
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- Widget de Check-ins Activos -->
    <?php if (!empty($activeCheckIns) && !$active_filter): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle me-2"></i>
                    <strong><?php echo count($activeCheckIns); ?> mascota(s) actualmente en la guardería</strong>
                </div>
                <div>
                    <a href="visits.php?active=1" class="btn btn-sm btn-outline-info me-2">Ver Activos</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="pet_id" class="form-label">Mascota</label>
                    <select class="form-select" id="pet_id" name="pet_id">
                        <option value="">Todas las mascotas</option>
                        <?php 
                        // Obtener lista de mascotas para el filtro
                        $allPetsResult = $petViewModel->getAllPets();
                        if ($allPetsResult['success']):
                            foreach ($allPetsResult['data'] as $pet): ?>
                                <option value="<?php echo $pet['id']; ?>" <?php echo $pet_id == $pet['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pet['name'] . ' (' . $pet['first_name'] . ' ' . $pet['last_name'] . ')'); ?>
                                </option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Desde</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Hasta</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Filtros rápidos -->
            <div class="mt-3">
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="quickFilter" id="today" autocomplete="off" onchange="setDateFilter('today')">
                    <label class="btn btn-outline-info" for="today">Hoy</label>

                    <input type="radio" class="btn-check" name="quickFilter" id="yesterday" autocomplete="off" onchange="setDateFilter('yesterday')">
                    <label class="btn btn-outline-info" for="yesterday">Ayer</label>

                    <input type="radio" class="btn-check" name="quickFilter" id="week" autocomplete="off" onchange="setDateFilter('week')">
                    <label class="btn btn-outline-info" for="week">Esta Semana</label>

                    <input type="radio" class="btn-check" name="quickFilter" id="month" autocomplete="off" onchange="setDateFilter('month')">
                    <label class="btn btn-outline-info" for="month">Este Mes</label>

                    <input type="radio" class="btn-check" name="quickFilter" id="active" autocomplete="off" <?php echo $active_filter ? 'checked' : ''; ?> onchange="setActiveFilter()">
                    <label class="btn btn-outline-success" for="active">Solo Activos</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Visitas -->
    <?php if (!empty($visits)): ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Mascota</th>
                                <th>Cliente</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Servicios</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visits as $visit): ?>
                                <tr class="<?php echo !$visit['check_out_time'] ? 'table-warning' : ''; ?>">
                                    <td>
                                        <div class="fw-bold">
                                            <?php echo date('d/m/Y', strtotime($visit['visit_date'])); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('l', strtotime($visit['visit_date'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-<?php echo $visit['species'] === 'perro' ? 'dog' : 'cat'; ?> me-2 text-muted"></i>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($visit['pet_name']); ?></div>
                                                <small class="text-muted"><?php echo ucfirst($visit['species']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($visit['owner_first_name'] . ' ' . $visit['owner_last_name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($visit['check_in_time']): ?>
                                            <span class="badge bg-success">
                                                <?php echo date('H:i', strtotime($visit['check_in_time'])); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No registrado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($visit['check_out_time']): ?>
                                            <span class="badge bg-info">
                                                <?php echo date('H:i', strtotime($visit['check_out_time'])); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">En guardería</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($visit['services_provided']); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!$visit['check_out_time']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <?php 
                                            $duration = strtotime($visit['check_out_time']) - strtotime($visit['check_in_time']);
                                            $hours = floor($duration / 3600);
                                            $minutes = floor(($duration % 3600) / 60);
                                            ?>
                                            <span class="badge bg-secondary">
                                                <?php echo $hours; ?>h <?php echo $minutes; ?>m
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="visits.php?action=view&id=<?php echo $visit['id']; ?>" 
                                               class="btn btn-outline-primary" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="visits.php?action=edit&id=<?php echo $visit['id']; ?>" 
                                               class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (!$visit['check_out_time']): ?>
                                                <button class="btn btn-outline-success" 
                                                        onclick="showCheckOutModal(<?php echo $visit['pet_id']; ?>, '<?php echo $visit['pet_name']; ?>')"
                                                        title="Check-out">
                                                    <i class="fas fa-sign-out-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-danger btn-confirm" 
                                                    data-action="delete-visit"
                                                    data-id="<?php echo $visit['id']; ?>"
                                                    data-message="¿Eliminar esta visita?"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-history fa-4x text-muted mb-3"></i>
            <h4 class="text-muted mb-3">No se encontraron visitas</h4>
            <p class="text-muted mb-4">
                <?php if ($active_filter): ?>
                    No hay mascotas actualmente en la guardería
                <?php elseif ($pet_id || $date_from || $date_to): ?>
                    No hay visitas que coincidan con los filtros seleccionados
                <?php else: ?>
                    No hay visitas registradas en el sistema
                <?php endif; ?>
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="visits.php?action=checkin" class="btn btn-success">
                    <i class="fas fa-sign-in-alt me-2"></i>Registrar Check-in
                </a>
                <a href="visits.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nueva Visita
                </a>
                <?php if ($pet_id || $date_from || $date_to || $active_filter): ?>
                    <button class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-times me-2"></i>Limpiar Filtros
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'checkin'): ?>
    <!-- Formulario de Check-in Rápido -->
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>Check-in Rápido
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="checkin">

                        <div class="mb-3">
                            <label for="pet_id" class="form-label">Mascota *</label>
                            <select class="form-select" id="pet_id" name="pet_id" required>
                                <option value="">Seleccionar mascota...</option>
                                <?php foreach ($pets as $pet): ?>
                                    <option value="<?php echo $pet['id']; ?>">
                                        <?php echo htmlspecialchars($pet['name'] . ' - ' . $pet['first_name'] . ' ' . $pet['last_name'] . ' (' . ucfirst($pet['species']) . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecciona una mascota</div>
                        </div>

                        <div class="mb-3">
                            <label for="services" class="form-label">Servicios</label>
                            <input type="text" class="form-control" id="services" name="services" 
                                   value="Guardería general"
                                   placeholder="Describe los servicios a proporcionar">
                            <small class="text-muted">Opcional. Por defecto: "Guardería general"</small>
                        </div>

                        <div class="mb-3">
                            <label for="appointment_id" class="form-label">Cita Asociada (Opcional)</label>
                            <input type="number" class="form-control" id="appointment_id" name="appointment_id" 
                                   placeholder="ID de la cita si aplica">
                            <small class="text-muted">Si esta visita corresponde a una cita específica</small>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Registrar Check-in
                            </button>
                            <a href="visits.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- Formulario de Visita Completa -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $visit['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>Información de la Visita
                                </h5>

                                <?php if ($action === 'add'): ?>
                                    <div class="mb-3">
                                        <label for="pet_id" class="form-label">Mascota *</label>
                                        <select class="form-select" id="pet_id" name="pet_id" required>
                                            <option value="">Seleccionar mascota...</option>
                                            <?php foreach ($pets as $pet): ?>
                                                <option value="<?php echo $pet['id']; ?>">
                                                    <?php echo htmlspecialchars($pet['name'] . ' - ' . $pet['first_name'] . ' ' . $pet['last_name'] . ' (' . ucfirst($pet['species']) . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Selecciona una mascota</div>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <label class="form-label">Mascota</label>
                                        <div class="form-control-plaintext fw-bold">
                                            <?php echo htmlspecialchars($visit['pet_name'] . ' - ' . $visit['owner_first_name'] . ' ' . $visit['owner_last_name']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="visit_date" class="form-label">Fecha de la Visita *</label>
                                    <input type="date" class="form-control" id="visit_date" name="visit_date" 
                                           value="<?php echo htmlspecialchars($visit['visit_date'] ?? date('Y-m-d')); ?>"
                                           max="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback">Selecciona una fecha</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="check_in_time" class="form-label">Hora de Entrada</label>
                                        <input type="time" class="form-control" id="check_in_time" name="check_in_time" 
                                               value="<?php echo htmlspecialchars($visit['check_in_time'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="check_out_time" class="form-label">Hora de Salida</label>
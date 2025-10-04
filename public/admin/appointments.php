<?php
// public/admin/appointments.php
require_once '../../includes/functions.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/AppointmentViewModel.php';
require_once '../../viewmodels/PetViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$appointmentViewModel = new AppointmentViewModel();
$petViewModel = new PetViewModel();

// Verificar que sea administrador
$sessionResult = $authViewModel->validateAdminSession();
if (!$sessionResult['success']) {
    redirect($sessionResult['redirect']);
}

// Procesar acciones
$action = $_GET['action'] ?? 'list';
$appointment_id = $_GET['id'] ?? null;
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$message = '';
$error = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['action'] ?? '';
    
    switch ($form_action) {
        case 'create':
            $result = $appointmentViewModel->createAppointment($_POST);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                redirect('appointments.php');
            } else {
                $error = implode('<br>', $result['errors']);
            }
            break;
            
        case 'update_status':
            $result = $appointmentViewModel->updateAppointmentStatus($_POST['id'], $_POST['status'], $_POST['notes'] ?? '');
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = implode('<br>', $result['errors']);
            }
            redirect('appointments.php');
            break;
            
        case 'cancel':
            $result = $appointmentViewModel->cancelAppointment($_POST['id']);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['error'];
            }
            redirect('appointments.php');
            break;
    }
}

// Obtener datos según la acción
switch ($action) {
    case 'list':
        $appointmentsResult = $appointmentViewModel->getAllAppointments($status_filter, $date_filter);
        $appointments = $appointmentsResult['success'] ? $appointmentsResult['data'] : [];
        break;
        
    case 'view':
        if ($appointment_id) {
            $appointmentResult = $appointmentViewModel->getAppointmentById($appointment_id);
            $appointment = $appointmentResult['success'] ? $appointmentResult['data'] : null;
            if (!$appointment) {
                $_SESSION['error_message'] = 'Cita no encontrada';
                redirect('appointments.php');
            }
        }
        break;
        
    case 'add':
        // Obtener mascotas y clientes para los selects
        $petsResult = $petViewModel->getAllPets();
        $pets = $petsResult['success'] ? $petsResult['data'] : [];
        $clientsResult = $petViewModel->getAllClients();
        $clients = $clientsResult['success'] ? $clientsResult['data'] : [];
        break;
}

$page_title = 'Gestión de Citas';
$css_path = '../../';

include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt me-2 text-primary"></i>
        <?php 
        switch ($action) {
            case 'add': echo 'Programar Nueva Cita'; break;
            case 'view': echo 'Detalles de Cita'; break;
            default: echo 'Gestión de Citas';
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
            <a href="appointments.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nueva Cita
            </a>
        <?php else: ?>
            <a href="appointments.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
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
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?php echo $status_filter === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="confirmada" <?php echo $status_filter === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="completada" <?php echo $status_filter === 'completada' ? 'selected' : ''; ?>>Completada</option>
                        <option value="cancelada" <?php echo $status_filter === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo htmlspecialchars($date_filter); ?>"
                           onchange="this.form.submit()">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </button>
                </div>
                <div class="col-md-3 d-flex align-items-end justify-content-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" onclick="setTodayFilter()">
                            <i class="fas fa-calendar-day me-1"></i>Hoy
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="setWeekFilter()">
                            <i class="fas fa-calendar-week me-1"></i>Esta Semana
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Citas -->
    <?php if (!empty($appointments)): ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Mascota</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr class="<?php echo $appointment['status'] === 'cancelada' ? 'table-secondary' : ''; ?>">
                                    <td>
                                        <div class="fw-bold">
                                            <?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-<?php echo $appointment['species'] === 'perro' ? 'dog' : 'cat'; ?> me-2 text-muted"></i>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($appointment['pet_name']); ?></div>
                                                <small class="text-muted"><?php echo ucfirst($appointment['species']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($appointment['service_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="appointment-status appointment-<?php echo $appointment['status']; ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-phone me-1"></i>
                                            <small><?php echo htmlspecialchars($appointment['phone'] ?? 'N/A'); ?></small>
                                        </div>
                                        <div>
                                            <i class="fas fa-envelope me-1"></i>
                                            <small><?php echo htmlspecialchars($appointment['email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="appointments.php?action=view&id=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-outline-primary" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($appointment['status'] === 'pendiente'): ?>
                                                <button class="btn btn-outline-success" 
                                                        onclick="updateStatus(<?php echo $appointment['id']; ?>, 'confirmada')"
                                                        title="Confirmar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($appointment['status'] === 'confirmada'): ?>
                                                <button class="btn btn-outline-info" 
                                                        onclick="updateStatus(<?php echo $appointment['id']; ?>, 'completada')"
                                                        title="Marcar como completada">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($appointment['status'], ['pendiente', 'confirmada'])): ?>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="cancelAppointment(<?php echo $appointment['id']; ?>)"
                                                        title="Cancelar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
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
            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
            <h4 class="text-muted mb-3">No se encontraron citas</h4>
            <p class="text-muted mb-4">
                <?php if ($status_filter || $date_filter): ?>
                    No hay citas que coincidan con los filtros seleccionados
                <?php else: ?>
                    No hay citas programadas en el sistema
                <?php endif; ?>
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="appointments.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Programar Primera Cita
                </a>
                <?php if ($status_filter || $date_filter): ?>
                    <button class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-times me-2"></i>Limpiar Filtros
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'add'): ?>
    <!-- Formulario de Nueva Cita -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="create">

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>Información de la Cita
                                </h5>

                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Cliente *</label>
                                    <select class="form-select" id="user_id" name="user_id" required onchange="loadUserPets(this.value)">
                                        <option value="">Seleccionar cliente...</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client['id']; ?>">
                                                <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name'] . ' (' . $client['username'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Selecciona un cliente</div>
                                </div>

                                <div class="mb-3">
                                    <label for="pet_id" class="form-label">Mascota *</label>
                                    <select class="form-select" id="pet_id" name="pet_id" required disabled>
                                        <option value="">Primero selecciona un cliente</option>
                                    </select>
                                    <div class="invalid-feedback">Selecciona una mascota</div>
                                </div>

                                <div class="mb-3">
                                    <label for="service_type" class="form-label">Tipo de Servicio *</label>
                                    <select class="form-select" id="service_type" name="service_type" required>
                                        <option value="">Seleccionar servicio...</option>
                                        <option value="guarderia">Guardería</option>
                                        <option value="consulta">Consulta Veterinaria</option>
                                        <option value="grooming">Grooming</option>
                                        <option value="entrenamiento">Entrenamiento</option>
                                    </select>
                                    <div class="invalid-feedback">Selecciona un tipo de servicio</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3">
                                    <i class="fas fa-calendar me-2 text-primary"></i>Fecha y Hora
                                </h5>

                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Fecha de la Cita *</label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required onchange="loadAvailableHours()">
                                    <div class="invalid-feedback">Selecciona una fecha</div>
                                </div>

                                <div class="mb-3">
                                    <label for="appointment_time" class="form-label">Hora *</label>
                                    <select class="form-select" id="appointment_time" name="appointment_time" required disabled>
                                        <option value="">Primero selecciona una fecha</option>
                                    </select>
                                    <div class="invalid-feedback">Selecciona una hora</div>
                                    <small class="text-muted">Horario de atención: 7:00 AM - 6:00 PM</small>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notas Adicionales</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4"
                                              placeholder="Instrucciones especiales, comentarios, etc."></textarea>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Programar Cita
                            </button>
                            <a href="appointments.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($action === 'view'): ?>
    <!-- Ver Detalles de Cita -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Detalles de la Cita
                        </h5>
                        <span class="appointment-status appointment-<?php echo $appointment['status']; ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">INFORMACIÓN DE LA MASCOTA</h6>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-<?php echo $appointment['species'] === 'perro' ? 'dog' : 'cat'; ?> me-2 text-primary"></i>
                                <strong><?php echo htmlspecialchars($appointment['pet_name']); ?></strong>
                            </div>
                            <p class="text-muted mb-0"><?php echo ucfirst($appointment['species']); ?></p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">CLIENTE</h6>
                            <p class="mb-1">
                                <strong><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></strong>
                            </p>
                            <p class="text-muted mb-1">
                                <i class="fas fa-phone me-1"></i>
                                <?php echo htmlspecialchars($appointment['phone'] ?? 'No registrado'); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-envelope me-1"></i>
                                <?php echo htmlspecialchars($appointment['email']); ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">FECHA Y HORA</h6>
                            <p class="mb-1">
                                <i class="fas fa-calendar me-1 text-primary"></i>
                                <strong><?php echo date('l, j \d\e F \d\e Y', strtotime($appointment['appointment_date'])); ?></strong>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-clock me-1 text-primary"></i>
                                <strong><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></strong>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">SERVICIO</h6>
                            <span class="badge bg-secondary fs-6">
                                <?php echo ucfirst($appointment['service_type']); ?>
                            </span>
                        </div>

                        <?php if ($appointment['notes']): ?>
                            <div class="col-12">
                                <h6 class="text-muted mb-2">NOTAS</h6>
                                <div class="alert alert-light">
                                    <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12">
                            <h6 class="text-muted mb-2">INFORMACIÓN ADICIONAL</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Cita creada</small>
                                    <span><?php echo date('d/m/Y H:i', strtotime($appointment['created_at'])); ?></span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Última actualización</small>
                                    <span><?php echo date('d/m/Y H:i', strtotime($appointment['updated_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($appointment['status'] === 'pendiente'): ?>
                        <button class="btn btn-success w-100 mb-2" 
                                onclick="updateStatus(<?php echo $appointment['id']; ?>, 'confirmada')">
                            <i class="fas fa-check me-2"></i>Confirmar Cita
                        </button>
                        <button class="btn btn-danger w-100 mb-2" 
                                onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                            <i class="fas fa-times me-2"></i>Cancelar Cita
                        </button>
                    <?php elseif ($appointment['status'] === 'confirmada'): ?>
                        <button class="btn btn-info w-100 mb-2" 
                                onclick="updateStatus(<?php echo $appointment['id']; ?>, 'completada')">
                            <i class="fas fa-check-double me-2"></i>Marcar como Completada
                        </button>
                        <button class="btn btn-danger w-100 mb-2" 
                                onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                            <i class="fas fa-times me-2"></i>Cancelar Cita
                        </button>
                    <?php endif; ?>

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="pets.php?action=view&id=<?php echo $appointment['pet_id']; ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-paw me-2"></i>Ver Mascota
                        </a>
                        <a href="visits.php?pet_id=<?php echo $appointment['pet_id']; ?>" 
                           class="btn btn-outline-info">
                            <i class="fas fa-history me-2"></i>Historial de Visitas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información de estado -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle me-2 text-info"></i>Estado de la Cita
                    </h6>
                    <div class="timeline-item">
                        <div class="d-flex align-items-center">
                            <span class="appointment-status appointment-<?php echo $appointment['status']; ?> me-2">
                                <?php echo ucfirst($appointment['status']); ?>
                            </span>
                            <small class="text-muted">
                                <?php 
                                $status_messages = [
                                    'pendiente' => 'Esperando confirmación',
                                    'confirmada' => 'Cita confirmada y programada',
                                    'completada' => 'Servicio completado exitosamente',
                                    'cancelada' => 'Cita cancelada'
                                ];
                                echo $status_messages[$appointment['status']];
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Scripts para la página -->
<script>
// Datos de mascotas por cliente (generados desde PHP)
const petsData = <?php echo json_encode($pets ?? []); ?>;

function clearFilters() {
    window.location.href = 'appointments.php';
}

function setTodayFilter() {
    const today = new Date().toISOString().split('T')[0];
    window.location.href = `appointments.php?date=${today}`;
}

function setWeekFilter() {
    const today = new Date();
    const monday = new Date(today.setDate(today.getDate() - today.getDay() + 1));
    const mondayStr = monday.toISOString().split('T')[0];
    window.location.href = `appointments.php?date=${mondayStr}`;
}

function loadUserPets(userId) {
    const petSelect = document.getElementById('pet_id');
    petSelect.innerHTML = '<option value="">Seleccionar mascota...</option>';
    
    if (userId) {
        const userPets = petsData.filter(pet => pet.user_id == userId);
        
        if (userPets.length > 0) {
            petSelect.disabled = false;
            userPets.forEach(pet => {
                const option = document.createElement('option');
                option.value = pet.id;
                option.textContent = `${pet.name} (${pet.species})`;
                petSelect.appendChild(option);
            });
        } else {
            petSelect.innerHTML = '<option value="">Este cliente no tiene mascotas registradas</option>';
            petSelect.disabled = true;
        }
    } else {
        petSelect.disabled = true;
        petSelect.innerHTML = '<option value="">Primero selecciona un cliente</option>';
    }
}

function loadAvailableHours() {
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    
    if (!dateInput.value) return;
    
    // Mostrar loading
    timeSelect.innerHTML = '<option value="">Cargando horarios...</option>';
    timeSelect.disabled = true;
    
    // Simular carga de horarios disponibles
    fetch('../../controllers/appointment_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=get_available_slots&date=${dateInput.value}`
    })
    .then(response => response.json())
    .then(data => {
        timeSelect.innerHTML = '';
        
        if (data.success && data.data.length > 0) {
            timeSelect.disabled = false;
            timeSelect.innerHTML = '<option value="">Seleccionar hora...</option>';
            
            data.data.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot.time;
                option.textContent = slot.display;
                timeSelect.appendChild(option);
            });
        } else {
            timeSelect.innerHTML = '<option value="">No hay horarios disponibles</option>';
            timeSelect.disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        timeSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
        timeSelect.disabled = true;
    });
}

function updateStatus(appointmentId, status) {
    let title, text;
    
    switch (status) {
        case 'confirmada':
            title = 'Confirmar Cita';
            text = '¿Confirmar esta cita?';
            break;
        case 'completada':
            title = 'Completar Cita';
            text = '¿Marcar esta cita como completada?';
            break;
        default:
            title = 'Actualizar Estado';
            text = '¿Actualizar el estado de esta cita?';
    }
    
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear formulario para enviar POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="${appointmentId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function cancelAppointment(appointmentId) {
    Swal.fire({
        title: '¿Cancelar Cita?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar cita',
        cancelButtonText: 'No cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear formulario para enviar POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="id" value="${appointmentId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh para la lista cada 2 minutos
    if (window.location.pathname.includes('appointments.php') && !window.location.search.includes('action=')) {
        setInterval(function() {
            // Solo refrescar si no hay modales abiertos
            if (!document.querySelector('.modal.show')) {
                location.reload();
            }
        }, 120000); // 2 minutos
    }
});
</script>

<?php include '../../views/layouts/footer.php'; ?>
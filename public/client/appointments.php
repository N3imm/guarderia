<?php
// public/client/appointments.php
require_once '../../includes/functions.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/AppointmentViewModel.php';
require_once '../../viewmodels/PetViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$appointmentViewModel = new AppointmentViewModel();
$petViewModel = new PetViewModel();

// Verificar sesión y permisos
$sessionResult = $authViewModel->validateSession();
if (!$sessionResult['success']) {
    redirect($sessionResult['redirect']);
}

if ($authViewModel->isAdmin()) {
    redirect('admin/dashboard.php');
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'list';
$appointment_id = $_GET['id'] ?? null;
$pet_id = $_GET['pet_id'] ?? null;

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'create':
            $_POST['user_id'] = $user_id;
            $result = $appointmentViewModel->createAppointment($_POST);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                redirect('appointments.php');
            } else {
                $error = implode('<br>', $result['errors']);
            }
            break;
            
        case 'cancel':
            $result = $appointmentViewModel->cancelAppointment($_POST['id'], $user_id);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['error'];
            }
            redirect('appointments.php');
            break;
    }
}

// Obtener datos
switch ($action) {
    case 'list':
        $appointmentsResult = $appointmentViewModel->getUserAppointments($user_id);
        $appointments = $appointmentsResult['success'] ? $appointmentsResult['data'] : [];
        break;
        
    case 'add':
        $petsResult = $petViewModel->getPetsByUser($user_id);
        $pets = $petsResult['success'] ? $petsResult['data'] : [];
        break;
        
    case 'view':
        if ($appointment_id) {
            $appointmentResult = $appointmentViewModel->getAppointmentById($appointment_id);
            if (!$appointmentResult['success'] || $appointmentResult['data']['user_id'] != $user_id) {
                $_SESSION['error_message'] = 'Cita no encontrada';
                redirect('appointments.php');
            }
            $appointment = $appointmentResult['data'];
        }
        break;
}

$page_title = 'Mis Citas';
$css_path = '../../';
include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-calendar-alt me-2 text-primary"></i>Mis Citas
    </h1>
    <?php if ($action === 'list'): ?>
        <a href="appointments.php?action=add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Cita
        </a>
    <?php else: ?>
        <a href="appointments.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    <?php endif; ?>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <?php if (!empty($appointments)): ?>
        <div class="row">
            <?php foreach ($appointments as $appointment): ?>
                <div class="col-lg-6 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <span><strong><?php echo htmlspecialchars($appointment['pet_name']); ?></strong></span>
                            <span class="appointment-status appointment-<?php echo $appointment['status']; ?>">
                                <?php echo ucfirst($appointment['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p><i class="fas fa-calendar me-2"></i><?php echo date('d/m/Y H:i', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time'])); ?></p>
                            <p><i class="fas fa-cog me-2"></i><?php echo ucfirst($appointment['service_type']); ?></p>
                            <div class="d-flex gap-2">
                                <a href="appointments.php?action=view&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                                <?php if (in_array($appointment['status'], ['pendiente', 'confirmada'])): ?>
                                    <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">Cancelar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
            <h4>No tienes citas programadas</h4>
            <a href="appointments.php?action=add" class="btn btn-primary mt-3">Programar Primera Cita</a>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'add'): ?>
    <?php if (!empty($pets)): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="create">

                            <div class="mb-3">
                                <label for="pet_id" class="form-label">Mascota *</label>
                                <select class="form-select" id="pet_id" name="pet_id" required>
                                    <option value="">Seleccionar mascota...</option>
                                    <?php foreach ($pets as $pet): ?>
                                        <option value="<?php echo $pet['id']; ?>" <?php echo $pet_id == $pet['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($pet['name'] . ' (' . ucfirst($pet['species']) . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="appointment_date" class="form-label">Fecha *</label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="appointment_time" class="form-label">Hora *</label>
                                    <select class="form-select" id="appointment_time" name="appointment_time" required>
                                        <option value="">Seleccionar hora...</option>
                                        <?php for ($h = 7; $h < 18; $h++): ?>
                                            <option value="<?php echo sprintf('%02d:00', $h); ?>">
                                                <?php echo date('g:00 A', strtotime($h . ':00')); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="service_type" class="form-label">Servicio *</label>
                                <select class="form-select" id="service_type" name="service_type" required>
                                    <option value="">Seleccionar servicio...</option>
                                    <option value="guarderia">Guardería</option>
                                    <option value="consulta">Consulta Veterinaria</option>
                                    <option value="grooming">Grooming</option>
                                    <option value="entrenamiento">Entrenamiento</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notas</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Instrucciones especiales o comentarios..."></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Programar Cita</button>
                                <a href="appointments.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-paw fa-4x text-muted mb-3"></i>
            <h4>Necesitas registrar una mascota primero</h4>
            <a href="my_pets.php?action=add" class="btn btn-primary">Registrar Mascota</a>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'view'): ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>Detalles de la Cita</h5>
                    <span class="appointment-status appointment-<?php echo $appointment['status']; ?>">
                        <?php echo ucfirst($appointment['status']); ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mascota:</strong> <?php echo htmlspecialchars($appointment['pet_name']); ?></p>
                            <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($appointment['appointment_date'])); ?></p>
                            <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($appointment['appointment_time'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Servicio:</strong> <?php echo ucfirst($appointment['service_type']); ?></p>
                            <p><strong>Estado:</strong> <?php echo ucfirst($appointment['status']); ?></p>
                        </div>
                    </div>
                    <?php if ($appointment['notes']): ?>
                        <div class="mt-3">
                            <strong>Notas:</strong>
                            <p><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($appointment['status'], ['pendiente', 'confirmada'])): ?>
                        <hr>
                        <button class="btn btn-danger" onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                            Cancelar Cita
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function cancelAppointment(id) {
    if (confirm('¿Estás seguro de cancelar esta cita?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../../views/layouts/footer.php'; ?>
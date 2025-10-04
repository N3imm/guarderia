<?php
// public/client/my_pets.php
require_once '../../includes/functions.php';
require_once '../../includes/session.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/PetViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$petViewModel = new PetViewModel();

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    redirect(BASE_URL . 'login.php');
}

// Redirigir administradores
if (isAdmin()) {
    redirect(ADMIN_URL . 'dashboard.php');
}

// Obtener el ID del usuario de la sesión
$user_id = getCurrentUserId();
$action = $_GET['action'] ?? 'list';
$pet_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['action'] ?? '';
    
    switch ($form_action) {
        case 'create':
            $_POST['user_id'] = $user_id; // Asegurar que sea del usuario actual
            $result = $petViewModel->createPet($_POST, $_FILES['photo'] ?? null);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                redirect(CLIENT_URL . 'my_pets.php');
            } else {
                $error = implode('<br>', $result['errors']);
            }
            break;
            
        case 'update':
            // Verificar que la mascota pertenece al usuario
            $currentPet = $petViewModel->getPetById($_POST['id']);
            if (!$currentPet['success'] || $currentPet['data']['user_id'] != $user_id) {
                $_SESSION['error_message'] = 'No tienes permisos para editar esta mascota';
                redirect('my_pets.php');
            }
            
            $result = $petViewModel->updatePet($_POST['id'], $_POST, $_FILES['photo'] ?? null);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                redirect('my_pets.php');
            } else {
                $error = implode('<br>', $result['errors']);
            }
            break;
    }
}

// Validar acciones permitidas según el rol
if (!isAdmin() && in_array($action, ['view', 'edit'])) {
    $_SESSION['error_message'] = 'No tienes permisos para realizar esta acción';
    redirect('my_pets.php');
}

// Obtener datos según la acción
switch ($action) {
    case 'list':
        $petsResult = $petViewModel->getPetsByUser($user_id);
        $pets = $petsResult['success'] ? $petsResult['data'] : [];
        break;
        
    case 'view':
        if ($pet_id && isAdmin()) {
            $petResult = $petViewModel->getPetById($pet_id);
            if (!$petResult['success'] || $petResult['data']['user_id'] != $user_id) {
                $_SESSION['error_message'] = 'Mascota no encontrada o no tienes permisos';
                redirect('my_pets.php');
            }
            $pet = $petResult['data'];
            
            // Obtener estado actual
            $statusResult = $petViewModel->getCurrentPetStatus($pet_id);
            $current_status = $statusResult['success'] ? $statusResult['data'] : null;
        }
        break;
        
    case 'edit':
        if ($pet_id && isAdmin()) {
            $petResult = $petViewModel->getPetById($pet_id);
            if (!$petResult['success'] || $petResult['data']['user_id'] != $user_id) {
                $_SESSION['error_message'] = 'Mascota no encontrada o no tienes permisos';
                redirect('my_pets.php');
            }
            $pet = $petResult['data'];
        }
        break;
}

$page_title = 'Mis Mascotas';
$css_path = '../../';

include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-paw me-2 text-primary"></i>
        <?php 
        switch ($action) {
            case 'add': echo 'Registrar Nueva Mascota'; break;
            case 'edit': echo 'Editar Información'; break;
            case 'view': echo 'Mi Mascota'; break;
            default: echo 'Mis Mascotas';
        }
        ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($action === 'list'): ?>
            <a href="my_pets.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Registrar Mascota
            </a>
        <?php else: ?>
            <a href="my_pets.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver a Mis Mascotas
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
    <!-- Lista de Mascotas del Cliente -->
    <?php if (!empty($pets)): ?>
        <div class="row">
            <?php foreach ($pets as $pet): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card pet-card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($pet['photo']): ?>
                                    <img src="../../assets/images/uploads/<?php echo $pet['photo']; ?>" 
                                         class="pet-photo me-3" alt="Foto de <?php echo $pet['name']; ?>">
                                <?php else: ?>
                                    <div class="pet-photo me-3 bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-<?php echo $pet['species'] === 'perro' ? 'dog' : 'cat'; ?> fa-2x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($pet['name']); ?></h5>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-<?php echo $pet['species'] === 'perro' ? 'dog' : 'cat'; ?> me-1"></i>
                                        <?php echo ucfirst($pet['species']); ?>
                                        <?php if ($pet['breed']): ?>
                                            - <?php echo htmlspecialchars($pet['breed']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="pet-details">
                                <p class="mb-2">
                                    <i class="fas fa-birthday-cake me-2 text-muted"></i>
                                    <?php echo date('d/m/Y', strtotime($pet['birth_date'])); ?> 
                                    (<?php echo $petViewModel->calculateAge($pet['birth_date']); ?>)
                                </p>
                                <?php if ($pet['weight']): ?>
                                    <p class="mb-2">
                                        <i class="fas fa-weight me-2 text-muted"></i>
                                        <?php echo number_format($pet['weight'], 1); ?> kg
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if (isAdmin()): ?>
                            <div class="mt-3">
                                <a href="my_pets.php?action=view&id=<?php echo $pet['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fas fa-eye me-1"></i>Ver Detalles
                                </a>
                                <a href="my_pets.php?action=edit&id=<?php echo $pet['id']; ?>" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-edit me-1"></i>Editar
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-paw fa-3x text-muted mb-3"></i>
            <h3>No tienes mascotas registradas</h3>
            <p class="text-muted">¡Registra tu primera mascota para comenzar!</p>
            <a href="my_pets.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Registrar mi Primera Mascota
            </a>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- Formulario para Agregar/Editar Mascota -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?php echo CLIENT_URL; ?>my_pets.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?php echo $action === 'add' ? 'create' : 'update'; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $pet['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre de la Mascota *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo $action === 'edit' ? htmlspecialchars($pet['name']) : ''; ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="species" class="form-label">Especie *</label>
                                <select class="form-select" id="species" name="species" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="perro" <?php echo ($action === 'edit' && $pet['species'] === 'perro') ? 'selected' : ''; ?>>Perro</option>
                                    <option value="gato" <?php echo ($action === 'edit' && $pet['species'] === 'gato') ? 'selected' : ''; ?>>Gato</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="breed" class="form-label">Raza</label>
                                <input type="text" class="form-control" id="breed" name="breed"
                                       value="<?php echo $action === 'edit' ? htmlspecialchars($pet['breed']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="birth_date" class="form-label">Fecha de Nacimiento *</label>
                                <input type="date" class="form-control" id="birth_date" name="birth_date"
                                       value="<?php echo $action === 'edit' ? $pet['birth_date'] : ''; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="weight" class="form-label">Peso (kg)</label>
                                <input type="number" class="form-control" id="weight" name="weight" step="0.1"
                                       value="<?php echo $action === 'edit' ? $pet['weight'] : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Foto</label>
                            <?php if ($action === 'edit' && $pet['photo']): ?>
                                <div class="mb-2">
                                    <img src="../../assets/images/uploads/<?php echo $pet['photo']; ?>" 
                                         alt="Foto actual" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas o Consideraciones Especiales</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php 
                                echo $action === 'edit' ? htmlspecialchars($pet['notes']) : ''; 
                            ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?php echo $action === 'add' ? 'Registrar Mascota' : 'Guardar Cambios'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($action === 'view'): ?>
    <!-- Vista Detallada de la Mascota -->
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <?php if ($pet['photo']): ?>
                        <img src="../../assets/images/uploads/<?php echo $pet['photo']; ?>" 
                             class="img-fluid rounded mb-3" alt="Foto de <?php echo $pet['name']; ?>">
                    <?php else: ?>
                        <div class="pet-photo-lg bg-light d-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-<?php echo $pet['species'] === 'perro' ? 'dog' : 'cat'; ?> fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4 class="card-title"><?php echo htmlspecialchars($pet['name']); ?></h4>
                    <p class="text-muted">
                        <i class="fas fa-<?php echo $pet['species'] === 'perro' ? 'dog' : 'cat'; ?> me-1"></i>
                        <?php echo ucfirst($pet['species']); ?>
                        <?php if ($pet['breed']): ?>
                            - <?php echo htmlspecialchars($pet['breed']); ?>
                        <?php endif; ?>
                    </p>
                    
                    <hr>
                    
                    <div class="pet-info text-start">
                        <p>
                            <i class="fas fa-birthday-cake me-2 text-muted"></i>
                            <?php echo date('d/m/Y', strtotime($pet['birth_date'])); ?>
                            (<?php echo $petViewModel->calculateAge($pet['birth_date']); ?>)
                        </p>
                        <?php if ($pet['weight']): ?>
                            <p>
                                <i class="fas fa-weight me-2 text-muted"></i>
                                <?php echo number_format($pet['weight'], 1); ?> kg
                            </p>
                        <?php endif; ?>
                        <?php if ($pet['notes']): ?>
                            <p class="mb-0">
                                <i class="fas fa-sticky-note me-2 text-muted"></i>
                                <?php echo nl2br(htmlspecialchars($pet['notes'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="appointments.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-primary d-block">
                        <i class="fas fa-calendar-plus me-2"></i>Programar Cita
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <?php if ($current_status): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Estado Actual
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">
                            <?php echo $current_status['message']; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include '../../views/layouts/footer.php'; ?>
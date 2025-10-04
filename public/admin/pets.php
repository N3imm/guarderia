<?php
// public/admin/pets.php
require_once '../../includes/functions.php';
require_once '../../viewmodels/AuthViewModel.php';
require_once '../../viewmodels/PetViewModel.php';
require_once '../../config/config.php';

$authViewModel = new AuthViewModel();
$petViewModel = new PetViewModel();

// Verificar que sea administrador
$sessionResult = $authViewModel->validateAdminSession();
if (!$sessionResult['success']) {
    redirect($sessionResult['redirect']);
}

// Procesar acciones
$action = $_GET['action'] ?? 'list';
$pet_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['action'] ?? '';
    
    switch ($form_action) {
        case 'create':
            $result = $petViewModel->createPet($_POST, $_FILES['photo'] ?? null);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                redirect('pets.php');
            } else {
                $error = implode('<br>', $result['errors']);
            }
            break;
            
        case 'update':
            $result = $petViewModel->updatePet($_POST['id'], $_POST, $_FILES['photo'] ?? null);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
                redirect('pets.php');
            } else {
                $error = implode('<br>', $result['errors']);
            }
            break;
            
        case 'delete':
            $result = $petViewModel->deletePet($_POST['id'], $_SESSION['role']);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['error'];
            }
            redirect('pets.php');
            break;
            
        case 'update_status':
            $result = $petViewModel->updatePetStatus($_POST['pet_id'], $_POST['status'], $_POST['description']);
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = implode('<br>', $result['errors']);
            }
            redirect('pets.php');
            break;
    }
}

// Obtener datos según la acción
switch ($action) {
    case 'list':
        $petsResult = $petViewModel->getAllPets();
        $pets = $petsResult['success'] ? $petsResult['data'] : [];
        break;
        
    case 'view':
    case 'edit':
        if ($pet_id) {
            $petResult = $petViewModel->getPetById($pet_id);
            $pet = $petResult['success'] ? $petResult['data'] : null;
            if (!$pet) {
                $_SESSION['error_message'] = 'Mascota no encontrada';
                redirect('pets.php');
            }
            
            // Obtener estado actual
            $statusResult = $petViewModel->getCurrentPetStatus($pet_id);
            $current_status = $statusResult['success'] ? $statusResult['data'] : null;
        }
        break;
        
    case 'add':
        // Obtener lista de clientes para el select
        $clientsResult = $petViewModel->getAllClients();
        $clients = $clientsResult['success'] ? $clientsResult['data'] : [];
        break;
}

$page_title = 'Gestión de Mascotas';
$css_path = '../../';

include '../../views/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-paw me-2 text-primary"></i>
        <?php 
        switch ($action) {
            case 'add': echo 'Registrar Nueva Mascota'; break;
            case 'edit': echo 'Editar Mascota'; break;
            case 'view': echo 'Detalles de Mascota'; break;
            default: echo 'Gestión de Mascotas';
        }
        ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($action === 'list'): ?>
            <a href="pets.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nueva Mascota
            </a>
        <?php else: ?>
            <a href="pets.php" class="btn btn-secondary">
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
    <!-- Lista de Mascotas -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control search-filter" 
                       placeholder="Buscar mascotas..." 
                       data-target=".pet-row" 
                       data-search-field="data-search">
            </div>
        </div>
        <div class="col-md-6">
            <select class="form-select" id="speciesFilter">
                <option value="">Todas las especies</option>
                <option value="perro">Perros</option>
                <option value="gato">Gatos</option>
                <option value="ave">Aves</option>
                <option value="conejo">Conejos</option>
                <option value="otro">Otros</option>
            </select>
        </div>
    </div>

    <?php if (!empty($pets)): ?>
        <div class="row">
            <?php foreach ($pets as $pet): ?>
                <div class="col-lg-4 col-md-6 mb-4 pet-row" 
                     data-search="<?php echo strtolower($pet['name'] . ' ' . $pet['species'] . ' ' . $pet['breed'] . ' ' . $pet['first_name'] . ' ' . $pet['last_name']); ?>"
                     data-species="<?php echo $pet['species']; ?>">
                    <div class="card pet-card h-100">
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
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo htmlspecialchars($pet['first_name'] . ' ' . $pet['last_name']); ?>
                                    </small>
                                </div>
                            </div>

                            <!-- Estado actual -->
                            <?php if ($pet['status']): ?>
                                <div class="mb-3">
                                    <span class="status-badge status-<?php echo $pet['status']; ?>">
                                        <i class="fas fa-circle me-1"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $pet['status'])); ?>
                                    </span>
                                    <?php if ($pet['status_description']): ?>
                                        <p class="small text-muted mt-2 mb-0">
                                            <?php echo htmlspecialchars($pet['status_description']); ?>
                                        </p>
                                        <small class="text-muted">
                                            Actualizado: <?php echo date('d/m/Y H:i', strtotime($pet['status_updated'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Información adicional -->
                            <div class="row g-2 mb-3">
                                <?php if ($pet['age']): ?>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Edad</small>
                                        <span><?php echo $pet['age']; ?> año<?php echo $pet['age'] != 1 ? 's' : ''; ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($pet['weight']): ?>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Peso</small>
                                        <span><?php echo $pet['weight']; ?> kg</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100" role="group">
                                <a href="pets.php?action=view&id=<?php echo $pet['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="pets.php?action=edit&id=<?php echo $pet['id']; ?>" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-info btn-sm" 
                                        onclick="showStatusModal(<?php echo $pet['id']; ?>, '<?php echo $pet['name']; ?>')">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="btn btn-outline-success btn-sm" 
                                        onclick="HappyPets.quickCheckIn(<?php echo $pet['id']; ?>)">
                                    <i class="fas fa-sign-in-alt"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm btn-confirm" 
                                        data-action="delete-pet"
                                        data-id="<?php echo $pet['id']; ?>"
                                        data-message="¿Estás seguro de eliminar a <?php echo $pet['name']; ?>?">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-paw fa-4x text-muted mb-3"></i>
            <h4 class="text-muted mb-3">No hay mascotas registradas</h4>
            <p class="text-muted mb-4">Comienza registrando la primera mascota en el sistema</p>
            <a href="pets.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Registrar Primera Mascota
            </a>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <!-- Formulario de Mascota -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $pet['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <!-- Información Básica -->
                            <div class="col-md-6">
                                <h5 class="mb-3">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>Información Básica
                                </h5>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre de la Mascota *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($pet['name'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">El nombre es requerido</div>
                                </div>

                                <?php if ($action === 'add'): ?>
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">Dueño *</label>
                                        <select class="form-select" id="user_id" name="user_id" required>
                                            <option value="">Seleccionar cliente...</option>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?php echo $client['id']; ?>">
                                                    <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name'] . ' (' . $client['username'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Selecciona un cliente</div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="species" class="form-label">Especie *</label>
                                    <select class="form-select" id="species" name="species" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="perro" <?php echo ($pet['species'] ?? '') === 'perro' ? 'selected' : ''; ?>>Perro</option>
                                        <option value="gato" <?php echo ($pet['species'] ?? '') === 'gato' ? 'selected' : ''; ?>>Gato</option>
                                        <option value="ave" <?php echo ($pet['species'] ?? '') === 'ave' ? 'selected' : ''; ?>>Ave</option>
                                        <option value="conejo" <?php echo ($pet['species'] ?? '') === 'conejo' ? 'selected' : ''; ?>>Conejo</option>
                                        <option value="otro" <?php echo ($pet['species'] ?? '') === 'otro' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                    <div class="invalid-feedback">Selecciona una especie</div>
                                </div>

                                <div class="mb-3">
                                    <label for="breed" class="form-label">Raza</label>
                                    <input type="text" class="form-control" id="breed" name="breed" 
                                           value="<?php echo htmlspecialchars($pet['breed'] ?? ''); ?>"
                                           placeholder="Ej: Golden Retriever, Persa, etc.">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="age" class="form-label">Edad (años)</label>
                                        <input type="number" class="form-control" id="age" name="age" 
                                               value="<?php echo htmlspecialchars($pet['age'] ?? ''); ?>"
                                               min="0" max="30">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="weight" class="form-label">Peso (kg)</label>
                                        <input type="number" class="form-control" id="weight" name="weight" 
                                               value="<?php echo htmlspecialchars($pet['weight'] ?? ''); ?>"
                                               min="0" step="0.1">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="text" class="form-control" id="color" name="color" 
                                           value="<?php echo htmlspecialchars($pet['color'] ?? ''); ?>"
                                           placeholder="Ej: Dorado, Negro, Blanco con manchas">
                                </div>
                            </div>

                            <!-- Foto y Detalles -->
                            <div class="col-md-6">
                                <h5 class="mb-3">
                                    <i class="fas fa-image me-2 text-primary"></i>Foto y Detalles
                                </h5>

                                <div class="mb-3">
                                    <label for="photo" class="form-label">Foto de la Mascota</label>
                                    <input type="file" class="form-control" id="photo" name="photo" 
                                           accept="image/jpeg,image/png,image/gif">
                                    <small class="text-muted">Máximo 5MB. Formatos: JPG, PNG, GIF</small>
                                    
                                    <?php if ($action === 'edit' && $pet['photo']): ?>
                                        <div class="mt-2">
                                            <img src="../../assets/images/uploads/<?php echo $pet['photo']; ?>" 
                                                 class="pet-photo-large" alt="Foto actual">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"
                                              placeholder="Describe el temperamento, características especiales, etc."><?php echo htmlspecialchars($pet['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="medical_notes" class="form-label">Notas Médicas</label>
                                    <textarea class="form-control" id="medical_notes" name="medical_notes" rows="4"
                                              placeholder="Alergias, medicamentos, condiciones especiales, etc."><?php echo htmlspecialchars($pet['medical_notes'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?php echo $action === 'edit' ? 'Actualizar' : 'Registrar'; ?> Mascota
                            </button>
                            <a href="pets.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($action === 'view'): ?>
    <!-- Ver Detalles de Mascota -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($pet['photo']): ?>
                        <img src="../../assets/images/uploads/<?php echo $pet['photo']; ?>" 
                             class="pet-photo-large mb-3" alt="Foto de <?php echo $pet['name']; ?>">
                    <?php else: ?>
                        <div class="pet-photo-large mb-3 mx-auto bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-<?php echo $pet['species'] === 'perro' ? 'dog' : 'cat'; ?> fa-4x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="mb-1"><?php echo htmlspecialchars($pet['name']); ?></h3>
                    <p class="text-muted mb-3">
                        <?php echo ucfirst($pet['species']); ?>
                        <?php if ($pet['breed']): ?>
                            - <?php echo htmlspecialchars($pet['breed']); ?>
                        <?php endif; ?>
                    </p>

                    <?php if ($current_status): ?>
                        <div class="mb-3">
                            <span class="status-badge status-<?php echo $current_status['status']; ?>">
                                <i class="fas fa-circle me-1"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $current_status['status'])); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <a href="pets.php?action=edit&id=<?php echo $pet['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar Información
                        </a>
                        <button class="btn btn-info" 
                                onclick="showStatusModal(<?php echo $pet['id']; ?>, '<?php echo $pet['name']; ?>')">
                            <i class="fas fa-sync-alt me-2"></i>Actualizar Estado
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Información Detallada -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información Detallada
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong class="text-muted d-block">Dueño</strong>
                            <span><?php echo htmlspecialchars($pet['first_name'] . ' ' . $pet['last_name']); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted d-block">Teléfono</strong>
                            <span><?php echo htmlspecialchars($pet['phone'] ?? 'No registrado'); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted d-block">Email</strong>
                            <span><?php echo htmlspecialchars($pet['email'] ?? 'No registrado'); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted d-block">Fecha de Registro</strong>
                            <span><?php echo date('d/m/Y', strtotime($pet['created_at'])); ?></span>
                        </div>
                        <?php if ($pet['age']): ?>
                            <div class="col-md-6">
                                <strong class="text-muted d-block">Edad</strong>
                                <span><?php echo $pet['age']; ?> año<?php echo $pet['age'] != 1 ? 's' : ''; ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($pet['weight']): ?>
                            <div class="col-md-6">
                                <strong class="text-muted d-block">Peso</strong>
                                <span><?php echo $pet['weight']; ?> kg</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($pet['color']): ?>
                            <div class="col-md-12">
                                <strong class="text-muted d-block">Color</strong>
                                <span><?php echo htmlspecialchars($pet['color']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($pet['description']): ?>
                        <hr>
                        <div>
                            <strong class="text-muted d-block mb-2">Descripción</strong>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($pet['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($pet['medical_notes']): ?>
                        <hr>
                        <div>
                            <strong class="text-muted d-block mb-2">Notas Médicas</strong>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo nl2br(htmlspecialchars($pet['medical_notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estado Actual -->
            <?php if ($current_status): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-heartbeat me-2"></i>Estado Actual
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="status-badge status-<?php echo $current_status['status']; ?> me-3">
                                <i class="fas fa-circle me-1"></i>
                                <?php echo ucfirst(str_replace('_', ' ', $current_status['status'])); ?>
                            </span>
                            <small class="text-muted">
                                Actualizado por <?php echo htmlspecialchars($current_status['first_name'] . ' ' . $current_status['last_name']); ?>
                                el <?php echo date('d/m/Y H:i', strtotime($current_status['created_at'])); ?>
                            </small>
                        </div>
                        <?php if ($current_status['status_description']): ?>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($current_status['status_description'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Modal para actualizar estado -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Actualizar Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="statusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="pet_id" id="modalPetId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modalStatus" class="form-label">Estado *</label>
                        <select class="form-select" id="modalStatus" name="status" required>
                            <option value="descansando">Descansando</option>
                            <option value="jugando">Jugando</option>
                            <option value="comiendo">Comiendo</option>
                            <option value="durmiendo">Durmiendo</option>
                            <option value="paseando">Paseando</option>
                            <option value="en_revision">En Revisión</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modalDescription" class="form-label">Descripción *</label>
                        <textarea class="form-control" id="modalDescription" name="description" rows="3" 
                                  placeholder="Describe el estado actual de la mascota..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtro por especie
    const speciesFilter = document.getElementById('speciesFilter');
    if (speciesFilter) {
        speciesFilter.addEventListener('change', function() {
            const selectedSpecies = this.value;
            const petRows = document.querySelectorAll('.pet-row');
            
            petRows.forEach(row => {
                const petSpecies = row.dataset.species;
                if (selectedSpecies === '' || petSpecies === selectedSpecies) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});

function showStatusModal(petId, petName) {
    document.getElementById('modalPetId').value = petId;
    document.getElementById('statusModalLabel').textContent = `Actualizar Estado - ${petName}`;
    document.getElementById('modalDescription').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

// Confirmar eliminación
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-confirm[data-action="delete-pet"]')) {
        e.preventDefault();
        const button = e.target.closest('.btn-confirm');
        const petId = button.dataset.id;
        const message = button.dataset.message;
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear formulario para enviar POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${petId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>

<?php include '../../views/layouts/footer.php'; ?>
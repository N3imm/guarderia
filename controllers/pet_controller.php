<?php
// controllers/pet_controller.php
require_once '../viewmodels/AuthViewModel.php';
require_once '../viewmodels/PetViewModel.php';
require_once '../config/config.php';

header('Content-Type: application/json');

$authViewModel = new AuthViewModel();
$petViewModel = new PetViewModel();

// Verificar sesión
if (!$authViewModel->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'delete':
        if (!$authViewModel->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Permisos insuficientes']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $result = $petViewModel->deletePet($id);
        echo json_encode($result);
        break;

    case 'update_status':
        if (!$authViewModel->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Permisos insuficientes']);
            exit;
        }
        
        $pet_id = $_POST['pet_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $description = $_POST['description'] ?? '';
        
        $result = $petViewModel->updatePetStatus($pet_id, $status, $description);
        echo json_encode($result);
        break;

    case 'get_status':
        $pet_id = $_GET['pet_id'] ?? 0;
        $result = $petViewModel->getCurrentPetStatus($pet_id);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
}
?>
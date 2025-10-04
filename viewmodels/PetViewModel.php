<?php
// viewmodels/PetViewModel.php
require_once __DIR__ . '/../models/Pet.php';
require_once __DIR__ . '/../models/User.php'; // Añadido para getAllClients
require_once __DIR__ . '/../config/config.php';

class PetViewModel {
    private $pet;
    private $user; // Añadido para getAllClients
    private $errors = [];

    public function __construct() {
        $this->pet = new Pet();
        $this->user = new User(); // Añadido para getAllClients
    }

    public function getAllPets() {
        try {
            $pets = $this->pet->getAll();
            foreach ($pets as &$pet) {
                if (!empty($pet['birth_date'])) {
                    $pet['age'] = $this->calculateAge($pet['birth_date']);
                }
            }
            return ['success' => true, 'data' => $pets];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener las mascotas', 'data' => []];
        }
    }

    public function createPet($data, $photo = null) {
        $this->errors = [];

        if (empty($data['name'])) $this->errors[] = "El nombre es requerido";
        if (empty($data['user_id'])) $this->errors[] = "El dueño es requerido";
        if (empty($data['species'])) $this->errors[] = "La especie es requerida";

        $photo_name = $this->handlePhotoUpload($photo);
        if ($photo_name === false) {
             return ['success' => false, 'errors' => $this->errors];
        }

        if (!empty($this->errors)) {
            return ['success' => false, 'errors' => $this->errors];
        }

        $pet_data = [
            'user_id' => $data['user_id'],
            'name' => trim($data['name']),
            'species' => $data['species'],
            'breed' => !empty($data['breed']) ? trim($data['breed']) : null,
            'birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
            'weight' => !empty($data['weight']) ? floatval($data['weight']) : null,
            'photo' => $photo_name,
            'color' => !empty($data['color']) ? trim($data['color']) : null,
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'medical_notes' => !empty($data['medical_notes']) ? trim($data['medical_notes']) : null
        ];

        if ($this->pet->create($pet_data, $_SESSION['user_id'])) {
            return ['success' => true, 'message' => 'Mascota registrada exitosamente'];
        }

        return ['success' => false, 'errors' => ['Error al registrar la mascota.']];
    }

    public function updatePet($id, $data, $photo = null) {
        $this->errors = [];

        if (empty($data['name'])) $this->errors[] = "El nombre es requerido";
        if (empty($data['species'])) $this->errors[] = "La especie es requerida";

        $photo_name = $this->handlePhotoUpload($photo, $id);
        if ($photo_name === false) {
            return ['success' => false, 'errors' => $this->errors];
        }

        if (!empty($this->errors)) {
            return ['success' => false, 'errors' => $this->errors];
        }

        $pet_data = [
            'name' => trim($data['name']),
            'species' => $data['species'],
            'breed' => !empty($data['breed']) ? trim($data['breed']) : null,
            'birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
            'weight' => !empty($data['weight']) ? floatval($data['weight']) : null,
            'color' => !empty($data['color']) ? trim($data['color']) : null,
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'medical_notes' => !empty($data['medical_notes']) ? trim($data['medical_notes']) : null
        ];

        if ($photo_name) {
            $pet_data['photo'] = $photo_name;
        }

        if ($this->pet->update($id, $pet_data)) {
            return ['success' => true, 'message' => 'Mascota actualizada exitosamente'];
        }

        return ['success' => false, 'errors' => ['Error al actualizar la mascota.']];
    }

    public function deletePet($id, $role) {
        $petResult = $this->pet->getById($id);
        if (!$petResult['success']) {
            return ['success' => false, 'error' => 'Mascota no encontrada'];
        }

        // Los administradores pueden borrar cualquier mascota
        if ($role !== 'admin') {
            // Si no es admin, verificar que sea el dueño
            if ($petResult['data']['user_id'] != $_SESSION['user_id']) {
                 return ['success' => false, 'error' => 'No tienes permisos para eliminar esta mascota'];
            }
        }

        if ($this->pet->delete($id)) {
            // Eliminar foto si existe
            if (!empty($petResult['data']['photo'])) {
                $photo_path = __DIR__ . '/../assets/images/uploads/' . $petResult['data']['photo'];
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
            }
            return ['success' => true, 'message' => 'Mascota eliminada exitosamente'];
        }
        return ['success' => false, 'error' => 'Error al eliminar la mascota'];
    }

    public function updatePetStatus($pet_id, $status, $description) {
        if (empty($status) || empty($description)) {
            return ['success' => false, 'errors' => ['El estado y la descripción son requeridos']];
        }
        if ($this->pet->updateStatus($pet_id, $status, $description, $_SESSION['user_id'])) {
            return ['success' => true, 'message' => 'Estado de la mascota actualizado'];
        }
        return ['success' => false, 'errors' => ['Error al actualizar el estado']];
    }

    public function getPetsByUser($user_id) {
        return ['success' => true, 'data' => $this->pet->getByUserId($user_id)];
    }

    public function getPetById($id) {
        $petResult = $this->pet->getById($id);
        if ($petResult['success']) {
            if (!empty($petResult['data']['birth_date'])) {
                $petResult['data']['age'] = $this->calculateAge($petResult['data']['birth_date']);
            }
        }
        return $petResult;
    }

    public function getAllClients() {
        return ['success' => true, 'data' => $this->user->getAllClients()];
    }

    public function getCurrentPetStatus($pet_id) {
        return ['success' => true, 'data' => $this->pet->getCurrentStatus($pet_id)];
    }

    private function handlePhotoUpload($photo, $existing_pet_id = null) {
        if (!$photo || $photo['error'] !== UPLOAD_ERR_OK) {
            return null; // No hay foto o hubo un error que no es crítico
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            $this->errors[] = "Solo se permiten imágenes JPG, JPEG, PNG, GIF";
            return false;
        }
        if ($photo['size'] > 5242880) { // 5MB
            $this->errors[] = "La imagen no debe superar los 5MB";
            return false;
        }

        $photo_name = uniqid('pet_') . '.' . $file_ext;
        $upload_path = __DIR__ . '/../assets/images/uploads/' . $photo_name;

        if (!is_dir(dirname($upload_path))) mkdir(dirname($upload_path), 0777, true);

        if (move_uploaded_file($photo['tmp_name'], $upload_path)) {
            // Si es una actualización, eliminar foto anterior
            if ($existing_pet_id) {
                $petResult = $this->pet->getById($existing_pet_id);
                if ($petResult['success'] && !empty($petResult['data']['photo'])) {
                    $old_photo_path = __DIR__ . '/../assets/images/uploads/' . $petResult['data']['photo'];
                    if (file_exists($old_photo_path)) unlink($old_photo_path);
                }
            }
            return $photo_name;
        } else {
            $this->errors[] = "Error al subir la nueva imagen";
            return false;
        }
    }

    public function calculateAge($birth_date) {
        try {
            $birth = new DateTime($birth_date);
            $today = new DateTime();
            $diff = $today->diff($birth);
            if ($diff->y > 0) return $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
            if ($diff->m > 0) return $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
            return $diff->d . ' día' . ($diff->d > 1 ? 's' : '');
        } catch (Exception $e) {
            return 'N/A';
        }
    }
}
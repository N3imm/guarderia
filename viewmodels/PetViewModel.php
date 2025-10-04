<?php
// viewmodels/PetViewModel.php
require_once __DIR__ . '/../models/Pet.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';

class PetViewModel {
    private $pet;
    private $user;
    private $errors = [];

    public function __construct() {
        $this->pet = new Pet();
        $this->user = new User();
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

        // 1. VALIDAR SESIÓN PRIMERO
        if (!isset($_SESSION['user_id'])) {
            error_log("ERROR CRÍTICO: No existe user_id en sesión");
            return ['success' => false, 'errors' => ['Error de sesión. Por favor, cierra sesión y vuelve a iniciar.']];
        }

        $actor_id = $_SESSION['user_id'];
        error_log("Sesión válida. Actor ID: " . $actor_id);

        // 2. VALIDACIONES BÁSICAS
        if (empty($data['name'])) {
            $this->errors[] = "El nombre es requerido";
        }
        if (empty($data['user_id'])) {
            $this->errors[] = "El dueño es requerido";
        }
        if (empty($data['species'])) {
            $this->errors[] = "La especie es requerida";
        }

        // 3. VALIDAR FOTO (pero NO subirla todavía)
        $photo_validated = false;
        $photo_data = null;
        
        if ($photo && isset($photo['error']) && $photo['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed)) {
                $this->errors[] = "Solo se permiten imágenes JPG, JPEG, PNG, GIF";
            } else if ($photo['size'] > 5242880) {
                $this->errors[] = "La imagen no debe superar los 5MB";
            } else {
                $photo_validated = true;
                $photo_data = [
                    'tmp_name' => $photo['tmp_name'],
                    'extension' => $file_ext
                ];
            }
        }

        // Si hay errores de validación, retornar antes de hacer cualquier cosa
        if (!empty($this->errors)) {
            error_log("Errores de validación: " . implode(", ", $this->errors));
            return ['success' => false, 'errors' => $this->errors];
        }

        // 4. PREPARAR DATOS (sin foto todavía)
        $pet_data = [
            'user_id' => $data['user_id'],
            'name' => trim($data['name']),
            'species' => $data['species'],
            'breed' => !empty($data['breed']) ? trim($data['breed']) : null,
            'birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
            'weight' => !empty($data['weight']) ? floatval($data['weight']) : null,
            'photo' => null, // Temporalmente null
            'color' => !empty($data['color']) ? trim($data['color']) : null,
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'medical_notes' => !empty($data['medical_notes']) ? trim($data['medical_notes']) : null
        ];

        error_log("Intentando crear mascota en BD: " . json_encode($pet_data));

        // 5. INTENTAR CREAR EN BASE DE DATOS (sin foto)
        try {
            if ($this->pet->create($pet_data, $actor_id)) {
                $pet_id = $this->pet->id;
                error_log("Mascota creada exitosamente con ID: " . $pet_id);
                
                // 6. AHORA SÍ, SUBIR LA FOTO (solo si la BD fue exitosa)
                if ($photo_validated && $photo_data) {
                    $photo_name = $this->uploadPhoto($photo_data);
                    
                    if ($photo_name) {
                        // Actualizar el registro con la foto
                        $update_photo = "UPDATE pets SET photo = :photo WHERE id = :id";
                        $database = new Database();
                        $conn = $database->getConnection();
                        $stmt = $conn->prepare($update_photo);
                        $stmt->bindParam(':photo', $photo_name);
                        $stmt->bindParam(':id', $pet_id);
                        $stmt->execute();
                        error_log("Foto actualizada: " . $photo_name);
                    } else {
                        error_log("Advertencia: Mascota creada pero foto no se pudo subir");
                    }
                }
                
                return ['success' => true, 'message' => 'Mascota registrada exitosamente'];
            } else {
                error_log("El método create() retornó false");
                return ['success' => false, 'errors' => ['Error al registrar la mascota. Revisa los logs para más detalles.']];
            }
        } catch (Exception $e) {
            error_log("Excepción al crear mascota: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Error: ' . $e->getMessage()]];
        }
    }

    private function uploadPhoto($photo_data) {
        try {
            $photo_name = uniqid('pet_') . '.' . $photo_data['extension'];
            $upload_path = __DIR__ . '/../assets/images/uploads/' . $photo_name;

            if (!is_dir(dirname($upload_path))) {
                mkdir(dirname($upload_path), 0777, true);
            }

            if (move_uploaded_file($photo_data['tmp_name'], $upload_path)) {
                return $photo_name;
            }
            
            error_log("Error al mover archivo a: " . $upload_path);
            return null;
        } catch (Exception $e) {
            error_log("Excepción al subir foto: " . $e->getMessage());
            return null;
        }
    }

    public function updatePet($id, $data, $photo = null) {
        $this->errors = [];

        if (empty($data['name'])) $this->errors[] = "El nombre es requerido";
        if (empty($data['species'])) $this->errors[] = "La especie es requerida";

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

        // Manejar foto si se subió una nueva
        if ($photo && isset($photo['error']) && $photo['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed) && $photo['size'] <= 5242880) {
                $photo_name = uniqid('pet_') . '.' . $file_ext;
                $upload_path = __DIR__ . '/../assets/images/uploads/' . $photo_name;

                if (!is_dir(dirname($upload_path))) {
                    mkdir(dirname($upload_path), 0777, true);
                }

                if (move_uploaded_file($photo['tmp_name'], $upload_path)) {
                    // Eliminar foto anterior
                    $petResult = $this->pet->getById($id);
                    if ($petResult['success'] && !empty($petResult['data']['photo'])) {
                        $old_photo = __DIR__ . '/../assets/images/uploads/' . $petResult['data']['photo'];
                        if (file_exists($old_photo)) {
                            unlink($old_photo);
                        }
                    }
                    $pet_data['photo'] = $photo_name;
                }
            }
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

        if ($role !== 'admin') {
            if ($petResult['data']['user_id'] != $_SESSION['user_id']) {
                 return ['success' => false, 'error' => 'No tienes permisos para eliminar esta mascota'];
            }
        }

        if ($this->pet->delete($id)) {
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
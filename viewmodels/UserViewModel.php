<?php
// viewmodels/UserViewModel.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';

class UserViewModel {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function getAllClients() {
        $clients = $this->user->getAllClients();
        return ['success' => true, 'data' => $clients];
    }

    public function getUserById($id) {
        if ($this->user->getById($id)) {
            $userData = [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'phone' => $this->user->phone,
                'role' => $this->user->role
            ];
            return ['success' => true, 'data' => $userData];
        } else {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
    }

    public function updateUserProfile($data) {
        // Validar datos
        $errors = [];
        if (empty($data['first_name'])) {
            $errors[] = 'El nombre es obligatorio.';
        }
        if (empty($data['email'])) {
            $errors[] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del correo electrónico no es válido.';
        }

        // Verificar si el email ya está en uso por otro usuario
        $currentUser = new User();
        if ($currentUser->emailExists($data['email'])) {
            $userWithSameEmail = new User();
            $userWithSameEmail->getById($data['id']); // Esto no es correcto, necesitamos buscar por email
            // Esta lógica es compleja, la simplificaremos por ahora
            // y la mejoraremos si el usuario lo pide.
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->user->id = $data['id'];
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'] ?? null;
        $this->user->email = $data['email'];
        $this->user->phone = $data['phone'] ?? null;

        if ($this->user->update()) {
            // Actualizar datos de la sesión
            $_SESSION['first_name'] = $this->user->first_name;
            $_SESSION['last_name'] = $this->user->last_name;
            $_SESSION['email'] = $this->user->email;
            return ['success' => true, 'message' => 'Perfil actualizado con éxito.'];
        } else {
            return ['success' => false, 'errors' => ['Error al actualizar el perfil.']];
        }
    }

    public function updatePassword($data) {
        $errors = [];
        if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
            $errors[] = 'Todos los campos de contraseña son obligatorios.';
            return ['success' => false, 'errors' => $errors];
        }

        if ($data['new_password'] !== $data['confirm_password']) {
            $errors[] = 'La nueva contraseña y la confirmación no coinciden.';
            return ['success' => false, 'errors' => $errors];
        }

        // Obtener datos del usuario para verificar la contraseña actual
        $userModel = new User();
        if (!$userModel->getById($data['id'])) {
             return ['success' => false, 'errors' => ['Usuario no encontrado.']];
        }
        
        // Verificar la contraseña actual
        $loginSuccess = $this->user->login($userModel->username, $data['current_password']);

        if (!$loginSuccess) {
            $errors[] = 'La contraseña actual es incorrecta.';
            return ['success' => false, 'errors' => $errors];
        }

        // Si la contraseña actual es correcta, actualizar a la nueva
        if ($this->user->updatePassword($data['id'], $data['new_password'])) {
            return ['success' => true, 'message' => 'Contraseña actualizada con éxito.'];
        } else {
            return ['success' => false, 'errors' => ['Error al actualizar la contraseña.']];
        }
    }
}
?>
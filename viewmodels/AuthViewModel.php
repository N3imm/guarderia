<?php
// viewmodels/AuthViewModel.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';

class AuthViewModel {
    private $user;
    private $errors = [];
    private $success_message = '';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->user = new User();
    }

    // Procesar login
    public function login($username, $password) {
        $this->errors = [];

        // Validaciones
        if (empty($username)) {
            $this->errors[] = "El usuario es requerido";
        }
        if (empty($password)) {
            $this->errors[] = "La contraseña es requerida";
        }

        if (empty($this->errors)) {
            if ($this->user->login($username, $password)) {
                // Crear sesión
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['username'] = $this->user->username;
                $_SESSION['first_name'] = $this->user->first_name;
                $_SESSION['last_name'] = $this->user->last_name;
                $_SESSION['role'] = $this->user->role;
                $_SESSION['email'] = $this->user->email;

                // Redireccionar según el rol
                if ($this->user->role === 'admin') {
                    return ['success' => true, 'redirect' => BASE_URL . 'admin/dashboard.php'];
                } else {
                    return ['success' => true, 'redirect' => 'client/dashboard.php'];
                }
            } else {
                $this->errors[] = "Usuario o contraseña incorrectos";
            }
        }

        return ['success' => false, 'errors' => $this->errors];
    }

    // Procesar registro
    public function register($data) {
        $this->errors = [];

        // Validaciones
        if (empty($data['username'])) {
            $this->errors[] = "El nombre de usuario es requerido";
        } elseif (strlen($data['username']) < 3) {
            $this->errors[] = "El nombre de usuario debe tener al menos 3 caracteres";
        } elseif ($this->user->usernameExists($data['username'])) {
            $this->errors[] = "El nombre de usuario ya existe";
        }

        if (empty($data['email'])) {
            $this->errors[] = "El email es requerido";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "El formato del email no es válido";
        } elseif ($this->user->emailExists($data['email'])) {
            $this->errors[] = "El email ya está registrado";
        }

        if (empty($data['password'])) {
            $this->errors[] = "La contraseña es requerida";
        } elseif (strlen($data['password']) < 6) {
            $this->errors[] = "La contraseña debe tener al menos 6 caracteres";
        }

        if (empty($data['confirm_password'])) {
            $this->errors[] = "Confirma tu contraseña";
        } elseif ($data['password'] !== $data['confirm_password']) {
            $this->errors[] = "Las contraseñas no coinciden";
        }

        if (empty($data['first_name'])) {
            $this->errors[] = "El nombre es requerido";
        }

        if (empty($data['last_name'])) {
            $this->errors[] = "El apellido es requerido";
        }

        // Validar teléfono (opcional)
        if (!empty($data['phone']) && !preg_match('/^[0-9]{10}$/', $data['phone'])) {
            $this->errors[] = "El teléfono debe tener 10 dígitos";
        }

        if (empty($this->errors)) {
            // Asignar datos al modelo
            $this->user->username = sanitize_input($data['username']);
            $this->user->email = sanitize_input($data['email']);
            $this->user->password = $data['password'];
            $this->user->first_name = sanitize_input($data['first_name']);
            $this->user->last_name = sanitize_input($data['last_name']);
            $this->user->phone = sanitize_input($data['phone']);
            $this->user->role = 'client'; // Por defecto cliente

            if ($this->user->create()) {
                $this->success_message = "Registro exitoso. Ya puedes iniciar sesión.";
                return ['success' => true, 'message' => $this->success_message];
            } else {
                $this->errors[] = "Error al crear la cuenta. Intenta de nuevo.";
            }
        }

        return ['success' => false, 'errors' => $this->errors];
    }

    // Cerrar sesión
    public function logout() {
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        session_unset();
        session_destroy();
        return ['success' => true, 'redirect' => 'login.php'];
    }

    // Obtener errores
    public function getErrors() {
        return $this->errors;
    }

    // Obtener mensaje de éxito
    public function getSuccessMessage() {
        return $this->success_message;
    }

    // Verificar si el usuario está logueado
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Verificar si es administrador
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Obtener datos del usuario actual
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name'],
                'role' => $_SESSION['role'],
                'email' => $_SESSION['email']
            ];
        }
        return null;
    }

    // Validar sesión activa
    public function validateSession() {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'redirect' => 'login.php'];
        }
        return ['success' => true];
    }

    // Validar sesión de administrador
    public function validateAdminSession() {
        $sessionResult = $this->validateSession();
        if (!$sessionResult['success']) {
            return $sessionResult;
        }

        if (!$this->isAdmin()) {
            return ['success' => false, 'redirect' => 'client/dashboard.php'];
        }

        return ['success' => true];
    }
}
?>
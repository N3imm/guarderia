<?php
// viewmodels/AppointmentViewModel.php
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../models/Pet.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class AppointmentViewModel {
    private $appointment;
    private $pet;
    private $conn;
    private $errors = [];

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->appointment = new Appointment($this->conn);
        $this->pet = new Pet($this->conn);
    }

    public function createAppointment($data) {
        $this->errors = [];

        // Validaciones
        if (empty($data['pet_id'])) {
            $this->errors[] = "Debe seleccionar una mascota";
        } else {
            // Verificar que la mascota exista y pertenezca al usuario
            $stmt = $this->conn->prepare("SELECT id FROM pets WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['pet_id'], $_SESSION['user_id']]);
            if (!$stmt->fetch()) {
                $this->errors[] = "La mascota seleccionada no es válida";
            }
        }

        if (empty($data['appointment_date'])) {
            $this->errors[] = "La fecha es requerida";
        } else {
            // Validar que la fecha no sea en el pasado
            $selected_date = strtotime($data['appointment_date']);
            if ($selected_date < strtotime(date('Y-m-d'))) {
                $this->errors[] = "La fecha no puede ser en el pasado";
            }
        }

        if (empty($data['appointment_time'])) {
            $this->errors[] = "La hora es requerida";
        }

        if (empty($data['service_type'])) {
            $this->errors[] = "El tipo de servicio es requerido";
        }

        if (!empty($this->errors)) {
            return [
                'success' => false,
                'errors' => $this->errors
            ];
        }

        // Verificar disponibilidad
        if (!$this->isTimeSlotAvailable($data['appointment_date'], $data['appointment_time'])) {
            $this->errors[] = "El horario seleccionado no está disponible";
            return [
                'success' => false,
                'errors' => $this->errors
            ];
        }

        // Validar que el usuario esté en sesión
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'errors' => ['Sesión no válida. Por favor, inicie sesión nuevamente.']
            ];
        }

        // Crear la cita
        $appointment_data = [
            'pet_id' => $data['pet_id'],
            'user_id' => $_SESSION['user_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'service_type' => $data['service_type'],
            'notes' => $data['notes'] ?? '',
            'status' => 'pendiente'
        ];

        // Intentar crear la cita
        if ($this->appointment->create($appointment_data)) {
            return [
                'success' => true,
                'message' => 'Cita programada exitosamente'
            ];
        }

        return [
            'success' => false,
            'errors' => ['Error al crear la cita. Por favor, intente nuevamente.']
        ];
    }

    public function getUserAppointments($user_id, $status = null) {
        $appointments = $this->appointment->getByUserId($user_id, $status);
        return [
            'success' => true,
            'data' => $appointments
        ];
    }

    public function getAllAppointments($status = null, $date = null) {
        $appointments = $this->appointment->getAll($status, $date);
        return [
            'success' => true,
            'data' => $appointments
        ];
    }

    public function getPetAppointments($pet_id, $status = null) {
        return $this->appointment->getByPet($pet_id, $status);
    }

    public function getAppointmentById($id) {
        return $this->appointment->getById($id);
    }

    public function cancelAppointment($id, $user_id) {
        $appointment = $this->appointment->getById($id);
        
        if (!$appointment) {
            return [
                'success' => false,
                'error' => 'Cita no encontrada'
            ];
        }

        if ($appointment['user_id'] != $user_id) {
            return [
                'success' => false,
                'error' => 'No tiene permisos para cancelar esta cita'
            ];
        }

        if ($this->appointment->updateStatus($id, 'cancelada')) {
            return [
                'success' => true,
                'message' => 'Cita cancelada exitosamente'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Error al cancelar la cita'
            ];
        }
    }

    public function updateAppointmentStatus($id, $status, $notes = '') {
        $valid_statuses = ['pendiente', 'confirmada', 'completada', 'cancelada'];
        if (!in_array($status, $valid_statuses)) {
            return [
                'success' => false,
                'errors' => ['Estado no válido.']
            ];
        }

        $appointment = $this->appointment->getById($id);
        if (!$appointment) {
            return [
                'success' => false,
                'errors' => ['Cita no encontrada.']
            ];
        }

        if ($this->appointment->updateStatus($id, $status, $notes)) {
            return [
                'success' => true,
                'message' => 'Estado de la cita actualizado exitosamente.'
            ];
        } else {
            return [
                'success' => false,
                'errors' => ['Error al actualizar el estado de la cita.']
            ];
        }
    }

    private function isTimeSlotAvailable($date, $time) {
        // Implementar lógica para verificar disponibilidad
        // Por ahora retorna true
        return true;
    }

    public function getAvailableTimeSlots($date) {
        // Implementar lógica para obtener horarios disponibles
        $timeSlots = [
            '09:00', '10:00', '11:00', '12:00',
            '14:00', '15:00', '16:00', '17:00'
        ];
        
        // Aquí se debería filtrar los horarios ya ocupados
        return $timeSlots;
    }
}
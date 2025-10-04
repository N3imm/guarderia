<?php
// viewmodels/AppointmentViewModel.php
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../models/Pet.php';
require_once __DIR__ . '/../config/config.php';

class AppointmentViewModel {
    private $appointment;
    private $pet;
    private $errors = [];

    public function __construct() {
        $this->appointment = new Appointment();
        $this->pet = new Pet();
    }

    public function createAppointment($data) {
        $this->errors = [];

        // Validaciones
        if (empty($data['pet_id'])) {
            $this->errors[] = "Debe seleccionar una mascota";
        } else {
            // Verificar que la mascota exista y pertenezca al usuario
            $stmt = $this->pet->getConnection()->prepare("SELECT id FROM pets WHERE id = ? AND user_id = ?");
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
        
        if (!$appointment['success']) {
            return [
                'success' => false,
                'errors' => ['Cita no encontrada']
            ];
        }

        if ($appointment['data']['user_id'] != $user_id) {
            return [
                'success' => false,
                'errors' => ['No tiene permisos para cancelar esta cita']
            ];
        }

        return $this->appointment->updateStatus($id, 'cancelada');
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
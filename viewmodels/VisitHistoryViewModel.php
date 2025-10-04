<?php
// viewmodels/VisitHistoryViewModel.php
require_once __DIR__ . '/../models/VisitHistory.php';
require_once __DIR__ . '/../models/Pet.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';

class VisitHistoryViewModel {
    private $visitHistory;
    private $pet;
    private $user;
    private $errors = [];
    private $success_message = '';

    public function __construct() {
        $this->visitHistory = new VisitHistory();
        $this->pet = new Pet();
        $this->user = new User();
    }

    // Crear nueva entrada en el historial
    public function createVisitEntry($data) {
        $this->errors = [];

        // Validaciones
        if (empty($data['pet_id'])) {
            $this->errors[] = "Debes seleccionar una mascota";
        }

        if (empty($data['visit_date'])) {
            $this->errors[] = "La fecha de visita es requerida";
        } else {
            $visit_date = new DateTime($data['visit_date']);
            $today = new DateTime();
            
            if ($visit_date > $today) {
                $this->errors[] = "No puedes registrar visitas futuras";
            }
        }

        if (!empty($data['check_in_time']) && !empty($data['check_out_time'])) {
            $check_in = new DateTime($data['check_in_time']);
            $check_out = new DateTime($data['check_out_time']);
            
            if ($check_out <= $check_in) {
                $this->errors[] = "La hora de salida debe ser posterior a la hora de entrada";
            }
        }

        if (empty($data['services_provided'])) {
            $this->errors[] = "Los servicios proporcionados son requeridos";
        }

        if (empty($this->errors)) {
            // Asignar datos al modelo
            $this->visitHistory->pet_id = (int)$data['pet_id'];
            $this->visitHistory->appointment_id = !empty($data['appointment_id']) ? (int)$data['appointment_id'] : null;
            $this->visitHistory->visit_date = $data['visit_date'];
            $this->visitHistory->check_in_time = !empty($data['check_in_time']) ? $data['check_in_time'] : null;
            $this->visitHistory->check_out_time = !empty($data['check_out_time']) ? $data['check_out_time'] : null;
            $this->visitHistory->services_provided = sanitize_input($data['services_provided']);
            $this->visitHistory->observations = sanitize_input($data['observations']);
            $this->visitHistory->created_by = $_SESSION['user_id'];

            if ($this->visitHistory->create()) {
                $this->success_message = "Visita registrada exitosamente";
                return ['success' => true, 'message' => $this->success_message, 'visit_id' => $this->visitHistory->id];
            } else {
                $this->errors[] = "Error al registrar la visita";
            }
        }

        return ['success' => false, 'errors' => $this->errors];
    }

    // Obtener todo el historial de visitas (para administrador)
    public function getAllVisitHistory($pet_id = null, $date_from = null, $date_to = null) {
        try {
            $visits = $this->visitHistory->getAll($pet_id, $date_from, $date_to);
            return ['success' => true, 'data' => $visits];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener el historial de visitas'];
        }
    }

    // Obtener historial por mascota
    public function getVisitHistoryByPet($pet_id, $user_id = null) {
        try {
            // Si no es admin, verificar que la mascota pertenece al usuario
            if ($user_id && $_SESSION['role'] !== 'admin') {
                $pet_data = $this->pet->getById($pet_id);
                if (!$pet_data || $pet_data['user_id'] != $user_id) {
                    return ['success' => false, 'error' => 'No tienes permisos para ver esta información'];
                }
            }

            $visits = $this->visitHistory->getByPetId($pet_id);
            return ['success' => true, 'data' => $visits];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener el historial de la mascota'];
        }
    }

    // Obtener historial por usuario (todas sus mascotas)
    public function getVisitHistoryByUser($user_id) {
        try {
            $visits = $this->visitHistory->getByUserId($user_id);
            return ['success' => true, 'data' => $visits];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener tu historial de visitas'];
        }
    }

    // Obtener visita por ID
    public function getVisitById($id) {
        try {
            $visit = $this->visitHistory->getById($id);
            if ($visit) {
                return ['success' => true, 'data' => $visit];
            }
            return ['success' => false, 'error' => 'Visita no encontrada'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener la visita'];
        }
    }

    // Actualizar entrada de visita
    public function updateVisitEntry($id, $data) {
        $this->errors = [];

        // Verificar que la visita existe
        $current_visit = $this->visitHistory->getById($id);
        if (!$current_visit) {
            return ['success' => false, 'error' => 'Visita no encontrada'];
        }

        // Validaciones similares a crear
        if (empty($data['visit_date'])) {
            $this->errors[] = "La fecha de visita es requerida";
        } else {
            $visit_date = new DateTime($data['visit_date']);
            $today = new DateTime();
            
            if ($visit_date > $today) {
                $this->errors[] = "No puedes registrar visitas futuras";
            }
        }

        if (!empty($data['check_in_time']) && !empty($data['check_out_time'])) {
            $check_in = new DateTime($data['check_in_time']);
            $check_out = new DateTime($data['check_out_time']);
            
            if ($check_out <= $check_in) {
                $this->errors[] = "La hora de salida debe ser posterior a la hora de entrada";
            }
        }

        if (empty($data['services_provided'])) {
            $this->errors[] = "Los servicios proporcionados son requeridos";
        }

        if (empty($this->errors)) {
            // Asignar datos al modelo
            $this->visitHistory->id = $id;
            $this->visitHistory->visit_date = $data['visit_date'];
            $this->visitHistory->check_in_time = !empty($data['check_in_time']) ? $data['check_in_time'] : null;
            $this->visitHistory->check_out_time = !empty($data['check_out_time']) ? $data['check_out_time'] : null;
            $this->visitHistory->services_provided = sanitize_input($data['services_provided']);
            $this->visitHistory->observations = sanitize_input($data['observations']);

            if ($this->visitHistory->update()) {
                $this->success_message = "Visita actualizada exitosamente";
                return ['success' => true, 'message' => $this->success_message];
            } else {
                $this->errors[] = "Error al actualizar la visita";
            }
        }

        return ['success' => false, 'errors' => $this->errors];
    }

    // Eliminar entrada de visita
    public function deleteVisitEntry($id) {
        try {
            if ($this->visitHistory->delete($id)) {
                return ['success' => true, 'message' => 'Visita eliminada exitosamente'];
            }
            return ['success' => false, 'error' => 'Error al eliminar la visita'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al eliminar la visita'];
        }
    }

    // Obtener visitas recientes
    public function getRecentVisits($limit = 10) {
        try {
            $visits = $this->visitHistory->getRecent($limit);
            return ['success' => true, 'data' => $visits];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener las visitas recientes'];
        }
    }

    // Obtener estadísticas de visitas
    public function getVisitStats($period = 'month') {
        try {
            $stats = [];
            
            switch ($period) {
                case 'week':
                    $stats = $this->visitHistory->getWeeklyStats();
                    break;
                case 'month':
                    $stats = $this->visitHistory->getMonthlyStats();
                    break;
                case 'year':
                    $stats = $this->visitHistory->getYearlyStats();
                    break;
                default:
                    $stats = $this->visitHistory->getMonthlyStats();
            }

            return ['success' => true, 'data' => $stats];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener estadísticas'];
        }
    }

    // Registrar check-in
    public function checkIn($pet_id, $appointment_id = null, $services = '') {
        try {
            $today = date('Y-m-d');
            $current_time = date('H:i:s');

            // Verificar si ya existe un check-in hoy para esta mascota
            $existing_visit = $this->visitHistory->getTodayVisit($pet_id);
            
            if ($existing_visit && $existing_visit['check_in_time'] && !$existing_visit['check_out_time']) {
                return ['success' => false, 'error' => 'Esta mascota ya tiene un check-in registrado hoy'];
            }

            $data = [
                'pet_id' => $pet_id,
                'appointment_id' => $appointment_id,
                'visit_date' => $today,
                'check_in_time' => $current_time,
                'services_provided' => $services ?: 'Guardería general',
                'observations' => 'Check-in automático'
            ];

            return $this->createVisitEntry($data);
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al registrar el check-in'];
        }
    }

    // Registrar check-out
    public function checkOut($pet_id, $observations = '') {
        try {
            $today = date('Y-m-d');
            $current_time = date('H:i:s');

            // Buscar el check-in de hoy para esta mascota
            $today_visit = $this->visitHistory->getTodayVisit($pet_id);
            
            if (!$today_visit) {
                return ['success' => false, 'error' => 'No se encontró un check-in para hoy'];
            }

            if ($today_visit['check_out_time']) {
                return ['success' => false, 'error' => 'Esta mascota ya tiene check-out registrado'];
            }

            // Actualizar con check-out
            $this->visitHistory->id = $today_visit['id'];
            $this->visitHistory->check_out_time = $current_time;
            $this->visitHistory->observations = $today_visit['observations'] . ($observations ? ' | Check-out: ' . $observations : '');

            if ($this->visitHistory->updateCheckOut()) {
                return ['success' => true, 'message' => 'Check-out registrado exitosamente'];
            }

            return ['success' => false, 'error' => 'Error al registrar el check-out'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al registrar el check-out'];
        }
    }

    // Obtener mascotas con check-in activo (sin check-out)
    public function getActiveCheckIns() {
        try {
            $active_visits = $this->visitHistory->getActiveCheckIns();
            return ['success' => true, 'data' => $active_visits];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener check-ins activos'];
        }
    }

    // Calcular tiempo de permanencia promedio
    public function getAverageStayTime($pet_id = null) {
        try {
            $average_time = $this->visitHistory->getAverageStayTime($pet_id);
            return ['success' => true, 'data' => $average_time];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al calcular tiempo promedio'];
        }
    }

    // Obtener errores
    public function getErrors() {
        return $this->errors;
    }

    // Obtener mensaje de éxito
    public function getSuccessMessage() {
        return $this->success_message;
    }
}
?>
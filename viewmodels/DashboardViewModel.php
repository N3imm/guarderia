<?php
// viewmodels/DashboardViewModel.php - VERSIÓN CORREGIDA
require_once __DIR__ . '/../models/Pet.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../models/VisitHistory.php';
require_once __DIR__ . '/../config/config.php';

class DashboardViewModel {
    private $pet;
    private $user;
    private $appointment;
    private $visitHistory;

    public function __construct() {
        $this->pet = new Pet();
        $this->user = new User();
        $this->appointment = new Appointment();
        $this->visitHistory = new VisitHistory();
    }

    // Obtener estadísticas del dashboard de administrador
    public function getAdminDashboardStats() {
        try {
            $stats = [
                'total_pets' => $this->getTotalPets(),
                'total_clients' => $this->getTotalClients(),
                'total_appointments' => $this->getTotalAppointments(),
                'total_visits' => $this->getTotalVisits(),
                'today_appointments' => $this->getTodayAppointments(),
                'active_checkins' => $this->getActiveCheckIns(),
                'pending_appointments' => $this->getPendingAppointments(),
                'upcoming_appointments' => $this->getUpcomingAppointments(5),
                'recent_visits' => $this->getRecentVisits(5),
                'pets_by_species' => $this->getPetsBySpecies(),
                'appointments_by_status' => $this->getAppointmentsByStatus(),
                'monthly_activity' => $this->getMonthlyActivity()
            ];

            return ['success' => true, 'data' => $stats];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener estadísticas del dashboard'];
        }
    }

    // Obtener estadísticas del dashboard de cliente
    public function getClientDashboardStats($user_id) {
        try {
            $stats = [
                'my_pets' => $this->getMyPets($user_id),
                'my_upcoming_appointments' => $this->getMyUpcomingAppointments($user_id, 3),
                'my_recent_visits' => $this->getMyRecentVisits($user_id, 5),
                'total_my_pets' => $this->getMyPetsCount($user_id),
                'total_my_appointments' => $this->getMyAppointmentsCount($user_id),
                'total_my_visits' => $this->getMyVisitsCount($user_id),
                'pets_status' => $this->getMyPetsCurrentStatus($user_id)
            ];

            return ['success' => true, 'data' => $stats];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener información del dashboard'];
        }
    }

    public function getAlerts() {
        $alerts = [];
        $today = date('Y-m-d');

        try {
            // Alerta para citas de hoy
            $upcoming_appointments = $this->appointment->getUpcoming(10); // Traer más para filtrar por hoy
            foreach ($upcoming_appointments as $app) {
                if ($app['appointment_date'] === $today) {
                    $alerts[] = [
                        'type' => 'info',
                        'icon' => 'fa-calendar-check',
                        'message' => sprintf(
                            'Cita para %s (%s) a las %s.',
                            htmlspecialchars($app['pet_name']),
                            htmlspecialchars($app['species']),
                            date('g:i A', strtotime($app['appointment_time']))
                        ),
                        'action' => 'Ver Cita',
                        'link' => 'appointments.php?action=view&id=' . $app['id']
                    ];
                }
            }

            // Alerta para check-ins activos
            $active_checkins = $this->visitHistory->getActiveCheckIns();
            foreach ($active_checkins as $visit) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fa-clock',
                    'message' => sprintf(
                        '%s está en la guardería desde las %s.',
                        htmlspecialchars($visit['pet_name']),
                        date('g:i A', strtotime($visit['check_in_time']))
                    ),
                    'action' => 'Ver Visita',
                    'link' => 'visits.php?action=view&id=' . $visit['id']
                ];
            }

            return ['success' => true, 'data' => $alerts];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener alertas', 'data' => []];
        }
    }

    // NUEVA FUNCIÓN DE ACTIVIDAD RECIENTE
    public function getRecentActivity($limit = 8) {
        try {
            $activities = $this->getRecentVisits($limit);
            return ['success' => true, 'data' => $activities];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener actividad reciente', 'data' => []];
        }
    }

    // Métodos privados simplificados
    private function getTotalPets() {
        return $this->pet->getCount();
    }

    private function getTotalClients() {
        return $this->user->getClientsCount();
    }

    private function getTotalAppointments() {
        return $this->appointment->getCount();
    }

    private function getTotalVisits() {
        return $this->visitHistory->getTotalVisits();
    }

    private function getTodayAppointments() {
        $today = date('Y-m-d');
        return $this->appointment->getCountByDate($today);
    }

    private function getActiveCheckIns() {
        return $this->visitHistory->getActiveCheckInsCount();
    }

    private function getPendingAppointments() {
        return $this->appointment->getCount('pendiente');
    }

    private function getUpcomingAppointments($limit) {
        return $this->appointment->getUpcoming($limit);
    }

    private function getRecentVisits($limit) {
        return $this->visitHistory->getRecent($limit);
    }

    private function getPetsBySpecies() {
        return $this->pet->getPetsBySpecies();
    }

    private function getAppointmentsByStatus() {
        return $this->appointment->getAppointmentsByStatus();
    }

    private function getMonthlyActivity() {
        return $this->visitHistory->getMonthlyActivity();
    }

    // Métodos para cliente
    private function getMyPets($user_id) {
        return $this->pet->getByUserId($user_id);
    }

    private function getMyUpcomingAppointments($user_id, $limit) {
        $appointments = $this->appointment->getByUserId($user_id);
        $upcoming = [];
        $today = date('Y-m-d H:i:s');
        
        foreach ($appointments as $appointment) {
            $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
            if ($appointment_datetime >= $today && $appointment['status'] != 'cancelada') {
                $upcoming[] = $appointment;
            }
            if (count($upcoming) >= $limit) break;
        }
        
        return $upcoming;
    }

    private function getMyRecentVisits($user_id, $limit) {
        $visits = $this->visitHistory->getByUserId($user_id);
        return array_slice($visits, 0, $limit);
    }

    private function getMyPetsCount($user_id) {
        $pets = $this->pet->getByUserId($user_id);
        return count($pets);
    }

    private function getMyAppointmentsCount($user_id) {
        $appointments = $this->appointment->getByUserId($user_id);
        return count($appointments);
    }

    private function getMyVisitsCount($user_id) {
        $visits = $this->visitHistory->getByUserId($user_id);
        return count($visits);
    }

    private function getMyPetsCurrentStatus($user_id) {
        $pets = $this->pet->getByUserId($user_id);
        $pets_with_status = [];
        
        foreach ($pets as $pet) {
            $status = $this->pet->getCurrentStatus($pet['id']);
            $pets_with_status[] = [
                'pet' => $pet,
                'status' => $status
            ];
        }
        
        return $pets_with_status;
    }
}
?>
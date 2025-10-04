<?php
// models/Appointment.php
require_once __DIR__ . '/../config/database.php';

class Appointment {
    private $conn;
    private $table = 'appointments';

    public $id;
    public $user_id;
    public $pet_id;
    public $appointment_date;
    public $appointment_time;
    public $service_type;
    public $status;
    public $notes;
    public $created_at;

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Crear cita
    public function create($data = null) {
        try {
            if ($data !== null) {
                foreach ($data as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
            }

            // Verificar que los IDs existan en sus respectivas tablas
            $checkUser = $this->conn->prepare("SELECT id FROM users WHERE id = ?");
            $checkUser->execute([$this->user_id]);
            if (!$checkUser->fetch()) {
                throw new Exception("Usuario no válido");
            }

            $checkPet = $this->conn->prepare("SELECT id FROM pets WHERE id = ?");
            $checkPet->execute([$this->pet_id]);
            if (!$checkPet->fetch()) {
                throw new Exception("Mascota no válida");
            }

            $query = "INSERT INTO " . $this->table . " 
                      (user_id, pet_id, appointment_date, appointment_time, service_type, status, notes) 
                      VALUES (:user_id, :pet_id, :appointment_date, :appointment_time, :service_type, :status, :notes)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':pet_id', $this->pet_id);
            $stmt->bindParam(':appointment_date', $this->appointment_date);
            $stmt->bindParam(':appointment_time', $this->appointment_time);
            $stmt->bindParam(':service_type', $this->service_type);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':notes', $this->notes);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear cita: " . $e->getMessage());
            return false;
        }
    }

    // Obtener todas las citas
    public function getAll($status = null, $date = null) {
        $query = "SELECT a.*, p.name as pet_name, p.species, 
                         u.first_name, u.last_name, u.phone, u.email
                  FROM " . $this->table . " a
                  LEFT JOIN pets p ON a.pet_id = p.id
                  LEFT JOIN users u ON a.user_id = u.id
                  WHERE 1=1";

        if ($status) {
            $query .= " AND a.status = :status";
        }
        if ($date) {
            $query .= " AND a.appointment_date = :date";
        }

        $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $this->conn->prepare($query);

        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        if ($date) {
            $stmt->bindParam(':date', $date);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener citas por usuario
    public function getByUserId($user_id, $status = null) {
        $query = "SELECT a.*, p.name as pet_name, p.species, p.photo
                  FROM " . $this->table . " a
                  LEFT JOIN pets p ON a.pet_id = p.id
                  WHERE a.user_id = :user_id";

        if ($status) {
            $query .= " AND a.status = :status";
        }

        $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        if ($status) {
            $stmt->bindParam(':status', $status);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener cita por ID
    public function getById($id) {
        $query = "SELECT a.*, p.name as pet_name, p.species, 
                         u.first_name, u.last_name, u.phone, u.email
                  FROM " . $this->table . " a
                  LEFT JOIN pets p ON a.pet_id = p.id
                  LEFT JOIN users u ON a.user_id = u.id
                  WHERE a.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    // Actualizar estado de cita
    public function updateStatus($id, $status, $notes = '') {
        $query = "UPDATE " . $this->table . " 
                  SET status = :status, notes = :notes, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // Obtener próximas citas
    public function getUpcoming($limit = 5) {
        $query = "SELECT a.*, p.name as pet_name, p.species, 
                         u.first_name, u.last_name
                  FROM " . $this->table . " a
                  LEFT JOIN pets p ON a.pet_id = p.id
                  LEFT JOIN users u ON a.user_id = u.id
                  WHERE a.appointment_date >= CURDATE() 
                  AND a.status IN ('pendiente', 'confirmada')
                  ORDER BY a.appointment_date ASC, a.appointment_time ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar citas
    public function getCount($status = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        
        if ($status) {
            $query .= " WHERE status = :status";
        }

        $stmt = $this->conn->prepare($query);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Contar citas por fecha
    public function getCountByDate($date) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE appointment_date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getAppointmentsByStatus() {
        $query = "SELECT status, COUNT(*) as total FROM " . $this->table . " GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
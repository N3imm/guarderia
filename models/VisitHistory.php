<?php
// models/VisitHistory.php - VERSIÓN CORREGIDA
require_once __DIR__ . '/../config/database.php';

class VisitHistory {
    private $conn;
    private $table = 'visit_history';

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function getByPetId($pet_id) {
        try {
            $query = "SELECT vh.*, p.name as pet_name, p.species
                      FROM " . $this->table . " vh
                      LEFT JOIN pets p ON vh.pet_id = p.id
                      WHERE vh.pet_id = :pet_id
                      ORDER BY vh.visit_date DESC, vh.check_in_time DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pet_id', $pet_id);
            $stmt->execute();

            return ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error al obtener el historial de visitas'];
        }
    }

    public $id;
    public $pet_id;
    public $appointment_id;
    public $visit_date;
    public $check_in_time;
    public $check_out_time;
    public $services_provided;
    public $observations;
    public $created_by;
    public $created_at;

    // Crear entrada en historial
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (pet_id, appointment_id, visit_date, check_in_time, check_out_time, 
                   services_provided, observations, created_by) 
                  VALUES (:pet_id, :appointment_id, :visit_date, :check_in_time, :check_out_time, 
                          :services_provided, :observations, :created_by)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':pet_id', $this->pet_id);
        $stmt->bindParam(':appointment_id', $this->appointment_id);
        $stmt->bindParam(':visit_date', $this->visit_date);
        $stmt->bindParam(':check_in_time', $this->check_in_time);
        $stmt->bindParam(':check_out_time', $this->check_out_time);
        $stmt->bindParam(':services_provided', $this->services_provided);
        $stmt->bindParam(':observations', $this->observations);
        $stmt->bindParam(':created_by', $this->created_by);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Obtener todo el historial
    public function getAll($pet_id = null, $date_from = null, $date_to = null) {
        $query = "SELECT vh.*, p.name as pet_name, p.species, 
                         u.first_name as owner_first_name, u.last_name as owner_last_name,
                         created_user.first_name as created_by_name
                  FROM " . $this->table . " vh
                  LEFT JOIN pets p ON vh.pet_id = p.id
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN users created_user ON vh.created_by = created_user.id
                  WHERE 1=1";

        if ($pet_id) {
            $query .= " AND vh.pet_id = :pet_id";
        }
        if ($date_from) {
            $query .= " AND vh.visit_date >= :date_from";
        }
        if ($date_to) {
            $query .= " AND vh.visit_date <= :date_to";
        }

        $query .= " ORDER BY vh.visit_date DESC, vh.created_at DESC";

        $stmt = $this->conn->prepare($query);

        if ($pet_id) {
            $stmt->bindParam(':pet_id', $pet_id);
        }
        if ($date_from) {
            $stmt->bindParam(':date_from', $date_from);
        }
        if ($date_to) {
            $stmt->bindParam(':date_to', $date_to);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener historial por usuario
    public function getByUserId($user_id) {
        $query = "SELECT vh.*, p.name as pet_name, p.species,
                         created_user.first_name as created_by_name
                  FROM " . $this->table . " vh
                  LEFT JOIN pets p ON vh.pet_id = p.id
                  LEFT JOIN users created_user ON vh.created_by = created_user.id
                  WHERE p.user_id = :user_id
                  ORDER BY vh.visit_date DESC, vh.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener visitas recientes
    public function getRecent($limit = 10) {
        $query = "SELECT vh.*, p.name as pet_name, p.species,
                         u.first_name as owner_first_name, u.last_name as owner_last_name
                  FROM " . $this->table . " vh
                  LEFT JOIN pets p ON vh.pet_id = p.id
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY vh.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener check-ins activos
    public function getActiveCheckIns() {
        $today = date('Y-m-d');
        $query = "SELECT vh.*, p.name as pet_name, p.species,
                         u.first_name as owner_first_name, u.last_name as owner_last_name
                  FROM " . $this->table . " vh
                  LEFT JOIN pets p ON vh.pet_id = p.id
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE vh.visit_date = :today 
                  AND vh.check_in_time IS NOT NULL 
                  AND vh.check_out_time IS NULL
                  ORDER BY vh.check_in_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar visitas totales
    public function getTotalVisits($pet_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        
        if ($pet_id) {
            $query .= " WHERE pet_id = :pet_id";
        }

        $stmt = $this->conn->prepare($query);
        
        if ($pet_id) {
            $stmt->bindParam(':pet_id', $pet_id);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getById($id) {
        $query = "SELECT vh.*, p.name as pet_name, p.species, u.first_name as owner_first_name, u.last_name as owner_last_name
                  FROM " . $this->table . " vh
                  LEFT JOIN pets p ON vh.pet_id = p.id
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE vh.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET
                    visit_date = :visit_date,
                    check_in_time = :check_in_time,
                    check_out_time = :check_out_time,
                    services_provided = :services_provided,
                    observations = :observations
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':visit_date', $this->visit_date);
        $stmt->bindParam(':check_in_time', $this->check_in_time);
        $stmt->bindParam(':check_out_time', $this->check_out_time);
        $stmt->bindParam(':services_provided', $this->services_provided);
        $stmt->bindParam(':observations', $this->observations);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function updateCheckOut() {
        $query = "UPDATE " . $this->table . " SET
                    check_out_time = :check_out_time,
                    observations = :observations
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':check_out_time', $this->check_out_time);
        $stmt->bindParam(':observations', $this->observations);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getTodayVisit($pet_id) {
        $today = date('Y-m-d');
        $query = "SELECT * FROM " . $this->table . " WHERE pet_id = :pet_id AND visit_date = :today ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pet_id', $pet_id);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActiveCheckInsCount() {
        $today = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE visit_date = :today AND check_in_time IS NOT NULL AND check_out_time IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getMonthlyActivity() {
        $query = "SELECT YEAR(visit_date) as year, MONTH(visit_date) as month, COUNT(*) as total_visits, COUNT(DISTINCT pet_id) as unique_pets
                  FROM " . $this->table . " 
                  WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  GROUP BY YEAR(visit_date), MONTH(visit_date)
                  ORDER BY year DESC, month DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<?php
// models/Pet.php
require_once __DIR__ . '/../config/database.php';

class Pet {
    private $conn;
    private $table = 'pets';

    public $id;
    public $user_id;
    public $name;
    public $species;
    public $breed;
    public $birth_date;
    public $weight;
    public $photo;
    public $color;
    public $description;
    public $medical_notes;
    public $created_at;

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    // Crear mascota
    public function create($data, $actor_id) {
        try {
            $this->conn->beginTransaction();

            // Log para debug
            error_log("Iniciando creación de mascota. Actor ID: " . $actor_id);
            error_log("Datos de mascota: " . json_encode($data));

            $query = "INSERT INTO " . $this->table . " 
                    (user_id, name, species, breed, birth_date, weight, photo, color, description, medical_notes) 
                    VALUES (:user_id, :name, :species, :breed, :birth_date, :weight, :photo, :color, :description, :medical_notes)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':species', $data['species']);
            $stmt->bindParam(':breed', $data['breed']);
            $stmt->bindParam(':birth_date', $data['birth_date']);
            $stmt->bindParam(':weight', $data['weight']);
            $stmt->bindParam(':photo', $data['photo']);
            $stmt->bindParam(':color', $data['color']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':medical_notes', $data['medical_notes']);

            if (!$stmt->execute()) {
                error_log("Error al ejecutar INSERT en pets: " . json_encode($stmt->errorInfo()));
                throw new PDOException("Error al insertar mascota");
            }

            $this->id = $this->conn->lastInsertId();
            error_log("Mascota insertada con ID: " . $this->id);

            // Crear estado inicial
            if (!$this->createInitialStatus($actor_id)) {
                error_log("Error al crear estado inicial");
                throw new PDOException("Error al crear estado inicial de la mascota");
            }

            $this->conn->commit();
            error_log("Transacción completada exitosamente");
            return true;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
                error_log("Rollback ejecutado");
            }
            error_log("Error en Pet::create() - " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    // Crear estado inicial de la mascota
    private function createInitialStatus($updated_by) {
        try {
            error_log("Creando estado inicial. Updated by: " . $updated_by);

            // Verificar que el usuario existe antes de insertar
            $checkUser = "SELECT id FROM users WHERE id = :user_id";
            $checkStmt = $this->conn->prepare($checkUser);
            $checkStmt->bindParam(':user_id', $updated_by);
            $checkStmt->execute();

            if ($checkStmt->rowCount() === 0) {
                error_log("ERROR: El usuario con ID " . $updated_by . " no existe en la tabla users");
                return false;
            }

            $query = "INSERT INTO pet_status (pet_id, status, status_description, updated_by) 
                    VALUES (:pet_id, 'descansando', 'Mascota registrada en el sistema', :updated_by)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pet_id', $this->id);
            $stmt->bindParam(':updated_by', $updated_by);
            
            if (!$stmt->execute()) {
                error_log("Error al ejecutar INSERT en pet_status: " . json_encode($stmt->errorInfo()));
                return false;
            }

            error_log("Estado inicial creado exitosamente");
            return true;

        } catch (PDOException $e) {
            error_log("Error en createInitialStatus(): " . $e->getMessage());
            return false;
        }
    }

    // Obtener todas las mascotas con su dueño y estado
    public function getAll() {
        $query = "SELECT p.*, u.first_name, u.last_name, u.username,
                         ps.status, ps.status_description, ps.created_at as status_updated
                  FROM " . $this->table . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN (
                      SELECT pet_id, status, status_description, created_at
                      FROM pet_status ps_inner
                      WHERE ps_inner.id = (
                          SELECT MAX(id) 
                          FROM pet_status 
                          WHERE pet_id = ps_inner.pet_id
                      )
                  ) ps ON p.id = ps.pet_id
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener mascotas por usuario
    public function getByUserId($user_id) {
        $query = "SELECT p.*, ps.status, ps.status_description, ps.created_at as status_updated
                  FROM " . $this->table . " p
                  LEFT JOIN (
                      SELECT pet_id, status, status_description, created_at
                      FROM pet_status ps_inner
                      WHERE ps_inner.id = (
                          SELECT MAX(id) 
                          FROM pet_status 
                          WHERE pet_id = ps_inner.pet_id
                      )
                  ) ps ON p.id = ps.pet_id
                  WHERE p.user_id = :user_id
                  ORDER BY p.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener mascota por ID
    public function getById($id) {
        $query = "SELECT p.*, u.first_name, u.last_name, u.phone, u.email
                  FROM " . $this->table . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)];
        }
        return ['success' => false];
    }

    // Actualizar mascota
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET 
                        name = :name, 
                        species = :species, 
                        breed = :breed, 
                        birth_date = :birth_date, 
                        weight = :weight, 
                        color = :color, 
                        description = :description, 
                        medical_notes = :medical_notes";
            
            if (isset($data['photo'])) {
                $query .= ", photo = :photo";
            }
            
            $query .= " WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':species', $data['species']);
            $stmt->bindParam(':breed', $data['breed']);
            $stmt->bindParam(':birth_date', $data['birth_date']);
            $stmt->bindParam(':weight', $data['weight']);
            $stmt->bindParam(':color', $data['color']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':medical_notes', $data['medical_notes']);
            $stmt->bindParam(':id', $id);

            if (isset($data['photo'])) {
                $stmt->bindParam(':photo', $data['photo']);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando mascota: " . $e->getMessage());
            return false;
        }
    }

    // Eliminar mascota
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminando mascota: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar estado de mascota
    public function updateStatus($pet_id, $status, $description, $updated_by) {
        $query = "INSERT INTO pet_status (pet_id, status, status_description, updated_by) 
                  VALUES (:pet_id, :status, :description, :updated_by)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pet_id', $pet_id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':updated_by', $updated_by);
        
        return $stmt->execute();
    }

    // Obtener estado actual de la mascota
    public function getCurrentStatus($pet_id) {
        $query = "SELECT ps.*, u.first_name, u.last_name
                  FROM pet_status ps
                  LEFT JOIN users u ON ps.updated_by = u.id
                  WHERE ps.pet_id = :pet_id
                  ORDER BY ps.created_at DESC
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pet_id', $pet_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getPetsBySpecies() {
        $query = "SELECT species, COUNT(*) as total FROM " . $this->table . " GROUP BY species";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
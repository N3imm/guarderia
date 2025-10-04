<?php
// test_session.php - Crear este archivo en la raíz del proyecto
require_once 'includes/session.php';
require_once 'config/config.php';

echo "<h2>Test de Sesión</h2>";
echo "<pre>";

echo "Estado de la sesión:\n";
echo "-------------------\n";
echo "Sesión iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? "SÍ" : "NO") . "\n";
echo "Usuario logueado: " . (isLoggedIn() ? "SÍ" : "NO") . "\n";
echo "Es admin: " . (isAdmin() ? "SÍ" : "NO") . "\n";
echo "Es cliente: " . (isClient() ? "SÍ" : "NO") . "\n\n";

echo "Contenido de \$_SESSION:\n";
echo "------------------------\n";
print_r($_SESSION);

echo "\n\nID del usuario actual: " . getCurrentUserId() . "\n";
echo "Rol del usuario actual: " . getCurrentUserRole() . "\n";

echo "\n\nConexión a base de datos:\n";
echo "-------------------------\n";
try {
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "Conexión exitosa\n";
        
        // Verificar si el usuario existe
        if (isLoggedIn()) {
            $user_id = getCurrentUserId();
            $query = "SELECT id, username, role FROM users WHERE id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "\nUsuario encontrado en BD:\n";
                print_r($stmt->fetch(PDO::FETCH_ASSOC));
            } else {
                echo "\n⚠️ PROBLEMA: El usuario con ID $user_id NO existe en la base de datos\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "</pre>";
?>
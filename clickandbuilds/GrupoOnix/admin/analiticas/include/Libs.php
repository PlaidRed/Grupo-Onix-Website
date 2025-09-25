<?php

// Start session at the beginning
session_start();

$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

$ruta = "";
$file = $url[count($url) - 1];
for ($i = 1; $i < (count($url) - 1); $i++) {
    $ruta .= "../";
}

// Se incluye la clase Common y la base de datos
include_once($ruta . "include/Common.php");
include_once($ruta . "include/Database.php");

$module = 25;

class Libs extends Common {
    
    /**
     * Get all users from sistema_usuario table
     */
    public function getUsers() {
        header('Content-Type: application/json');
        
        try {
            $query = "SELECT SIU_ID, SIU_NOMBRE_COMPLETO 
                      FROM sistema_usuario 
                      ORDER BY SIU_NOMBRE_COMPLETO ASC";
            
            $consulta = $this->_conexion->prepare($query);
            $consulta->execute();
            
            $users = $consulta->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get analytics data with filters
     */
    public function getAnalytics() {
        header('Content-Type: application/json');
        
        $userId = $_POST['user_id'] ?? null;
        $fromDate = $_POST['from_date'] ?? null;
        $toDate = $_POST['to_date'] ?? null;
        
        try {
            $query = "SELECT * FROM analiticas WHERE 1=1";
            $params = [];
            
            if ($userId) {
                $query .= " AND user_id = :user_id";
                $params[':user_id'] = $userId;
            }
            
            if ($fromDate) {
                $query .= " AND DATE(created_at) >= :from_date";
                $params[':from_date'] = $fromDate;
            }
            
            if ($toDate) {
                $query .= " AND DATE(created_at) <= :to_date";
                $params[':to_date'] = $toDate;
            }
            
            $query .= " ORDER BY created_at DESC";
            
            $consulta = $this->_conexion->prepare($query);
            $consulta->execute($params);
            
            $analytics = $consulta->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $analytics
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    $libs = new Libs();
    $action = $_POST['action'];
    
    if (method_exists($libs, $action)) {
        $libs->$action();
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
    }
}
?>
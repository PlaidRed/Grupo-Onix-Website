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
     * Get all users from sistema_usuario table + Agentes option
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
            
            // Add the "Agentes" option at the beginning
            array_unshift($users, [
                'SIU_ID' => 'agentes',
                'SIU_NOMBRE_COMPLETO' => 'Agentes (Nivel 3)'
            ]);
            
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
     * Get user IDs from analytics that correspond to level 3 users (Agentes)
     */
    private function getAgentUserIds() {
        try {
            // Get distinct user_ids from analiticas table that correspond to level 3 users
            $query = "SELECT DISTINCT a.user_id 
                      FROM analiticas a
                      INNER JOIN sistema_usuario su ON a.user_id = su.SIU_ID
                      WHERE su.SIU_ACCESO = 3";
            
            $consulta = $this->_conexion->prepare($query);
            $consulta->execute();
            
            $result = $consulta->fetchAll(PDO::FETCH_COLUMN);
            return $result;
            
        } catch (Exception $e) {
            error_log("Error getting agent user IDs: " . $e->getMessage());
            return [];
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
            $query = "SELECT 
                        a.*,
                        su.SIU_NOMBRE_COMPLETO as user_name
                      FROM analiticas a
                      LEFT JOIN sistema_usuario su ON a.user_id = su.SIU_ID
                      WHERE 1=1";
            $params = [];
            
            // Handle special "agentes" case
            if ($userId === 'agentes') {
                $agentIds = $this->getAgentUserIds();
                if (!empty($agentIds)) {
                    $placeholders = str_repeat('?,', count($agentIds) - 1) . '?';
                    $query .= " AND a.user_id IN ($placeholders)";
                    $params = array_merge($params, $agentIds);
                } else {
                    // No agents found in analytics, return empty result
                    $query .= " AND 1=0";
                }
            } elseif ($userId) {
                $query .= " AND a.user_id = ?";
                $params[] = $userId;
            }
            
            if ($fromDate) {
                $query .= " AND DATE(a.created_at) >= ?";
                $params[] = $fromDate;
            }
            
            if ($toDate) {
                $query .= " AND DATE(a.created_at) <= ?";
                $params[] = $toDate;
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            $consulta = $this->_conexion->prepare($query);
            $consulta->execute($params);
            
            $analytics = $consulta->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $analytics,
                'debug' => [
                    'user_id' => $userId,
                    'is_agentes' => ($userId === 'agentes'),
                    'agent_ids' => ($userId === 'agentes') ? $this->getAgentUserIds() : null,
                    'query' => $query,
                    'params' => $params
                ]
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
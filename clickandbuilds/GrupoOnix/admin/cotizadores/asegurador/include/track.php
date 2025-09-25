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

class TrackClick extends Common {
    
    /**
     * Get current user ID from session (ONX structure)
     */
    private function getCurrentUserId() {
        return isset($_SESSION['onx']['userid']) ? $_SESSION['onx']['userid'] : null;
    }

    /**
     * Get current user name from session (ONX structure)
     */
    private function getCurrentUserName() {
        return isset($_SESSION['onx']['username']) ? $_SESSION['onx']['username'] : null;
    }
    
    public function trackClick() {
        header('Content-Type: application/json');
        
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        // Check if action is trackClick
        if (!isset($_POST['action']) || $_POST['action'] !== 'trackClick') {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            exit;
        }
        
        // Get data from session and POST
        $userId = $this->getCurrentUserId();
        $userName = $this->getCurrentUserName();
        $cotizadorName = $_POST['cotizador_name'] ?? null;
        $cotizadorId = $_POST['cotizador_id'] ?? null;
        $actionType = 'click';
        $userAgent = $_POST['user_agent'] ?? '';
        $createdAt = date('Y-m-d H:i:s'); // Current timestamp
        
        // Validate required data
        if (!$cotizadorName) {
            echo json_encode(['success' => false, 'error' => 'Missing cotizador name']);
            exit;
        }
        
        // Insert into database using PDO (matching your site's pattern)
        try {
            $query = "INSERT INTO analiticas (
                        user_id, 
                        user_name,
                        cotizador_name,
                        action_type, 
                        user_agent,
                        created_at
                      ) VALUES (
                        :user_id,
                        :user_name,
                        :cotizador_name,
                        :action_type,
                        :user_agent,
                        :created_at
                      )";
            
            $consulta = $this->_conexion->prepare($query);
            $result = $consulta->execute([
                ':user_id' => $userId,
                ':user_name' => $userName,
                ':cotizador_name' => $cotizadorName,
                ':action_type' => $actionType,
                ':user_agent' => $userAgent,
                ':created_at' => $createdAt,
            ]);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Click tracked successfully',
                    'data' => [
                        'user_id' => $userId,
                        'user_name' => $userName,
                        'cotizador_name' => $cotizadorName,
                        'cotizador_id' => $cotizadorId,
                        'action_type' => $actionType,
                        'created_at' => $createdAt
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database insert failed']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

// Instantiate and run
$tracker = new TrackClick();
$tracker->trackClick();
?>
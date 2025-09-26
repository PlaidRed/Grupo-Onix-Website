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
        $userId = $this->getCurrentUserId();            // session SIU_ID (system user id)
        $userName = $this->getCurrentUserName();
        $cotizadorName = $_POST['cotizador_name'] ?? null;
        $actionType = 'click';
        $userAgent = $_POST['user_agent'] ?? '';
        $createdAt = date('Y-m-d H:i:s'); // Current timestamp

        // Determine the siu_id value based on permission level
        // NOTE: getUserPermissionLevel returns 1,2,3 according to your parsePermissionLevel logic
        $siuPermissionLevel = null;
        if ($userId) {
            $siuPermissionLevel = $this->getUserPermissionLevel($userId);
        }
        
        // Validate required data
        if (!$cotizadorName) {
            echo json_encode(['success' => false, 'error' => 'Missing cotizador name']);
            exit;
        }
        
        // Insert into database using PDO (matching your site's pattern)
        try {
            $query = "INSERT INTO analiticas (
                        siu_id,
                        user_id, 
                        user_name,
                        cotizador_name,
                        action_type, 
                        user_agent,
                        created_at
                      ) VALUES (
                        :siu_id,
                        :user_id,
                        :user_name,
                        :cotizador_name,
                        :action_type,
                        :user_agent,
                        :created_at
                      )";
            
            $consulta = $this->_conexion->prepare($query);
            $result = $consulta->execute([
                ':siu_id' => $siuPermissionLevel,   // permission level (1,2,3) per your rules
                ':user_id' => $userId,              // actual session user id (if you want to keep it)
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
                        'siu_id' => $siuPermissionLevel,
                        'user_id' => $userId,
                        'user_name' => $userName,
                        'cotizador_name' => $cotizadorName,
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

    /**
     * Get user permission level from SISTEMA_USUARIO_PERFIL
     */
    private function getUserPermissionLevel($userId) {
        try {
            $query = "SELECT sup.SUP_PERMISO
                      FROM SISTEMA_USUARIO_PERFIL sup
                      INNER JOIN SISTEMA_USUARIO su ON su.SUP_ID = sup.SUP_ID
                      WHERE su.SIU_ID = ?";
            
            $consulta = $this->_conexion->prepare($query);
            $consulta->execute([$userId]);
            
            $result = $consulta->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['SUP_PERMISO']) {
                return $this->parsePermissionLevel($result['SUP_PERMISO']);
            }
            
            return 2; // Default level if no permissions found
            
        } catch (Exception $e) {
            error_log("Error getting user permission level: " . $e->getMessage());
            return 2; // Default level
        }
    }
    
    /**
     * Parse permission string to determine level (1, 2, or 3)
     * Based on the permission patterns from SISTEMA_USUARIO_PERFIL
     */
    private function parsePermissionLevel($permissionString) {
        if (empty($permissionString)) {
            return 2; // Default level
        }
        
        $permissions = explode("|", $permissionString);
        $moduleCount = count($permissions);
        $fullAccessModules = 0;
        $hasModule25 = false;
        $hasRestrictedAccess = false;
        
        foreach ($permissions as $permission) {
            $parts = explode("-", $permission);
            if (count($parts) >= 2) {
                $module = $parts[0];
                $modulePermissions = $parts[1];
                
                // Check for full access (1111)
                if ($modulePermissions === "1111") {
                    $fullAccessModules++;
                }
                
                // Check for module 25 (specific to Administrador)
                if ($module === "25") {
                    $hasModule25 = true;
                }
                
                // Check for restricted access (not 1111)
                if ($modulePermissions !== "1111") {
                    $hasRestrictedAccess = true;
                }
            }
        }
        
        // Determine level based on permission patterns:
        
        // Level 2 (Administrador): Has module 25 and extensive full access
        if ($hasModule25 && $moduleCount >= 24) {
            return 2;
        }
        
        // Level 1 (daemon): Has extensive permissions but no module 25
        if ($moduleCount >= 20 && $fullAccessModules >= 20 && !$hasModule25) {
            return 1;
        }
        
        // Level 3 (Agente): Limited modules with some restrictions
        if ($moduleCount <= 15 && $hasRestrictedAccess) {
            return 3;
        }
        
        // Default logic: if many full access modules, likely admin level
        if ($fullAccessModules >= 15) {
            return $hasModule25 ? 2 : 1;
        }
        
        // Otherwise, agent level
        return 3;
    }
}

// Instantiate and run
$tracker = new TrackClick();
$tracker->trackClick();
?>

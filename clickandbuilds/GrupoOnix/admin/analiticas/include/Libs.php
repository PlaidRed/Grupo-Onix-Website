<?php

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
    function getUsers() {
        try {
            $query = "SELECT SIU_ID, SIU_NOMBRE_COMPLETO 
                     FROM sistema_usuario 
                     ORDER BY SIU_NOMBRE_COMPLETO ASC";
            
            $consulta = $this->_conexion->prepare($query);
            $consulta->execute();
            $puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
            
            $users = [];
            foreach ($puntero as $row) {
                $users[] = [
                    'id' => $row['SIU_ID'],
                    'name' => $row['SIU_NOMBRE_COMPLETO']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'users' => $users,
                'count' => count($users)
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error fetching users: ' . $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    /**
     * Get analytics data based on filters
     */
    function getAnalytics() {
        try {
            $userId = isset($_POST['userId']) ? $_POST['userId'] : '';
            $dateFrom = isset($_POST['dateFrom']) ? $_POST['dateFrom'] : '';
            $dateTo = isset($_POST['dateTo']) ? $_POST['dateTo'] : '';

            // Build the base query - modify this according to your actual analytics tables
            $query = "SELECT 
                        COUNT(*) as total_records,
                        COUNT(DISTINCT user_id) as active_users,
                        DATE(created_at) as date_created
                      FROM analiticas 
                      WHERE 1=1";
            
            $params = [];
            
            // Add user filter if specified
            if (!empty($userId)) {
                $query .= " AND user_id = :userId";
                $params[':userId'] = $userId;
            }
            
            // Add date range filters
            if (!empty($dateFrom)) {
                $query .= " AND DATE(created_at) >= :dateFrom";
                $params[':dateFrom'] = $dateFrom;
            }
            
            if (!empty($dateTo)) {
                $query .= " AND DATE(created_at) <= :dateTo";
                $params[':dateTo'] = $dateTo;
            }
            
            $query .= " GROUP BY DATE(created_at) ORDER BY date_created ASC";
            
            $consulta = $this->_conexion->prepare($query);
            $consulta->execute($params);
            $results = $consulta->fetchAll(PDO::FETCH_ASSOC);
            
            // Process the data for analytics
            $analyticsData = $this->processAnalyticsData($results, $dateFrom, $dateTo);
            
            echo json_encode([
                'success' => true,
                'data' => $analyticsData,
                'filters' => [
                    'userId' => $userId,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo
                ]
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error fetching analytics: ' . $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    /**
     * Process raw analytics data into useful metrics
     */
    private function processAnalyticsData($rawData, $dateFrom, $dateTo) {
        if (empty($rawData)) {
            return [
                'totalRecords' => 0,
                'activeUsers' => 0,
                'dailyAverage' => 0,
                'growth' => 0,
                'chartData' => [
                    'labels' => [],
                    'values' => []
                ]
            ];
        }

        $totalRecords = 0;
        $activeUsers = 0;
        $chartLabels = [];
        $chartValues = [];

        foreach ($rawData as $row) {
            $totalRecords += $row['total_records'];
            $activeUsers = max($activeUsers, $row['active_users']);
            
            $chartLabels[] = date('d/m', strtotime($row['date_created']));
            $chartValues[] = (int)$row['total_records'];
        }

        // Calculate daily average
        $days = count($rawData);
        $dailyAverage = $days > 0 ? round($totalRecords / $days, 2) : 0;

        // Calculate growth (simple comparison between first and last periods)
        $growth = 0;
        if (count($chartValues) >= 2) {
            $firstHalf = array_slice($chartValues, 0, ceil(count($chartValues) / 2));
            $secondHalf = array_slice($chartValues, ceil(count($chartValues) / 2));
            
            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);
            
            if ($firstAvg > 0) {
                $growth = round((($secondAvg - $firstAvg) / $firstAvg) * 100, 2);
            }
        }

        return [
            'totalRecords' => $totalRecords,
            'activeUsers' => $activeUsers,
            'dailyAverage' => $dailyAverage,
            'growth' => $growth,
            'chartData' => [
                'labels' => $chartLabels,
                'values' => $chartValues
            ]
        ];
    }

    /**
     * Track click events from cotizadores
     */
    function trackClick() {
        try {
            $cotizadorName = isset($_POST['cotizador_name']) ? $_POST['cotizador_name'] : '';
            $userAgent = isset($_POST['user_agent']) ? $_POST['user_agent'] : '';
            
            // Get current user ID if available
            $userId = isset($_SESSION['SIU_ID']) ? $_SESSION['SIU_ID'] : null;
            
            // Insert click data into analiticas table
            $query = "INSERT INTO analiticas (
                        user_id, 
                        action_type, 
                        cotizador_name, 
                        user_agent, 
                        created_at
                      ) VALUES (
                        :user_id, 
                        'cotizador_click', 
                        :cotizador_name, 
                        :user_agent, 
                        NOW()
                      )";
            
            $consulta = $this->_conexion->prepare($query);
            $consulta->execute([
                ':user_id' => $userId,
                ':cotizador_name' => $cotizadorName,
                ':user_agent' => $userAgent,
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Click tracked successfully',
                'data' => [
                    'cotizador' => $cotizadorName,
                    'user_id' => $userId,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error tracking click: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get dashboard summary stats
     */
    function getDashboardStats() {
        try {
            // Example queries - modify according to your needs
            $stats = [];
            
            // Total users
            $query = "SELECT COUNT(*) as total FROM sistema_usuario";
            $result = $this->_conexion->query($query)->fetch();
            $stats['totalUsers'] = $result['total'];
            
            // Add more stats as needed...
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error fetching dashboard stats: ' . $e->getMessage()
            ]);
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $libs = new Libs();
    
    switch ($_POST['action']) {
        case 'getUsers':
            $libs->getUsers();
            break;
            
        case 'getAnalytics':
            $libs->getAnalytics();
            break;
            
        case 'trackClick':
            $libs->trackClick();
            break;
            
        case 'getDashboardStats':
            $libs->getDashboardStats();
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action specified'
            ]);
            break;
    }
}

?>
<?php
include_once(__DIR__ . '/../../include/Database.php');

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->_conexion;

    // 1. Obtener carpetas con ordenamiento consistente
    $foldersQuery = "
        SELECT 
            id, 
            name, 
            parent_id, 
            COALESCE(sort_order, id) as sort_order 
        FROM folders 
        ORDER BY 
            COALESCE(parent_id, 0),  -- Agrupa por parent_id (NULL primero)
            COALESCE(sort_order, id), -- Orden principal
            id                        -- Desempate por ID
    ";
    $folders = $conn->query($foldersQuery)->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener links con ordenamiento consistente
    $linksQuery = "
        SELECT 
            id, 
            folder_id, 
            label, 
            url, 
            username, 
            password, 
            created_at, 
            COALESCE(sort_order, id) as sort_order 
        FROM links 
        ORDER BY 
            COALESCE(folder_id, 0),  -- Agrupa por folder_id
            COALESCE(sort_order, id), -- Orden principal
            id                        -- Desempate por ID
    ";
    $links = $conn->query($linksQuery)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Verificar y corregir valores NULL
    array_walk($folders, function(&$folder) {
        $folder['parent_id'] = $folder['parent_id'] ?? null;
        $folder['sort_order'] = $folder['sort_order'] ?? $folder['id'];
    });

    array_walk($links, function(&$link) {
        $link['folder_id'] = $link['folder_id'] ?? null;
        $link['sort_order'] = $link['sort_order'] ?? $link['id'];
    });

    echo json_encode([
        'success' => true,
        'folders' => $folders,
        'links' => $links,
        'timestamp' => time() // Para control de caché
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'msg' => 'Error al cargar datos: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
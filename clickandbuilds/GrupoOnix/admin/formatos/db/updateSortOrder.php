<?php
include_once(__DIR__ . '/../../include/Database.php');

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->_conexion;
    $conn->beginTransaction();

    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['updates']) || !is_array($data['updates'])) {
        throw new Exception('Datos inválidos');
    }

    // Actualizar carpetas
    $folderStmt = $conn->prepare("UPDATE folders SET sort_order = :sort, parent_id = :parent WHERE id = :id");
    
    // Actualizar links
    $linkStmt = $conn->prepare("UPDATE links SET sort_order = :sort, folder_id = :parent WHERE id = :id");

    foreach ($data['updates'] as $item) {
        $params = [
            ':sort' => (int)$item['sort_order'],
            ':parent' => $item['parent_id'] ? (int)$item['parent_id'] : null,
            ':id' => (int)$item['id']
        ];

        if ($item['type'] === 'folder') {
            $folderStmt->execute($params);
        } else {
            $linkStmt->execute($params);
        }
    }

    $conn->commit();

    // Devolver el nuevo estado ordenado
    $folders = $conn->query("
        SELECT * FROM folders 
        ORDER BY COALESCE(parent_id, 0), sort_order, id
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'folders' => $folders
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'msg' => 'Error: ' . $e->getMessage()
    ]);
}
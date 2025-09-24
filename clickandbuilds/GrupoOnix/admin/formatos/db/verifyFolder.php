<?php
include_once(__DIR__ . '/../../include/Database.php');
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $folder_id = isset($input['folder_id']) ? intval($input['folder_id']) : 0;

    if ($folder_id <= 0) {
        throw new Exception('ID de carpeta requerido');
    }

    $db = new Database();
    $conn = $db->_conexion;

    // Verify folder exists
    $stmt = $conn->prepare("SELECT id, name FROM folders WHERE id = ?");
    $stmt->execute([$folder_id]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$folder) {
        throw new Exception('Carpeta no encontrada');
    }

    // Get next sort order for links in this folder
    $stmt = $conn->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_sort_order FROM links WHERE folder_id = ?");
    $stmt->execute([$folder_id]);
    $linkSort = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get next sort order for folders in this folder  
    $stmt = $conn->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_sort_order FROM folders WHERE parent_id = ?");
    $stmt->execute([$folder_id]);
    $folderSort = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'folder' => $folder,
        'next_sort_order' => $linkSort['next_sort_order'],
        'next_folder_sort_order' => $folderSort['next_sort_order']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'msg' => $e->getMessage()
    ]);
}
?>
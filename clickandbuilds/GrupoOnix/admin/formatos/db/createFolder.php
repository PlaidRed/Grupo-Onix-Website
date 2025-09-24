<?php
include_once(__DIR__ . '/../../include/Database.php');

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->_conexion;

    // Check if this is a move operation (has folder_id) or create operation
    $folder_id = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
    $name = trim($_POST['name'] ?? '');
    $parent_id = $_POST['parent_id'] ?? null;
    $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

    if ($parent_id === '' || strtolower($parent_id) === 'null') {
        $parent_id = null;
    } else {
        $parent_id = (int)$parent_id;
    }

    // MOVE FOLDER OPERATION
    if ($folder_id) {
        // Verify folder exists
        $stmt = $conn->prepare("SELECT id, name FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        $folder = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$folder) {
            throw new Exception('Carpeta no encontrada');
        }

        // If parent_id is provided, verify it exists and prevent circular reference
        if ($parent_id) {
            $stmt = $conn->prepare("SELECT id FROM folders WHERE id = ?");
            $stmt->execute([$parent_id]);
            if (!$stmt->fetch()) {
                throw new Exception('Carpeta padre no encontrada');
            }

            // Simple circular reference check
            if (isDescendantFolder($conn, $parent_id, $folder_id)) {
                throw new Exception('No se puede mover una carpeta a su propia subcarpeta');
            }
        }

        // Update folder's parent_id and sort_order
        $stmt = $conn->prepare("UPDATE folders SET parent_id = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([$parent_id, $sort_order, $folder_id]);

        echo json_encode([
            'success' => true,
            'msg' => 'Carpeta movida correctamente',
            'id' => $folder_id,
            'name' => $folder['name']
        ]);
        exit;
    }

    // CREATE FOLDER OPERATION (your existing code)
    if ($name === '') {
        echo json_encode(['success' => false, 'msg' => 'El nombre es obligatorio']);
        exit;
    }

    // Verificar si ya existe carpeta con el mismo nombre en el mismo nivel
    $sqlCheck = "SELECT id FROM folders WHERE name = :name AND 
                 ((parent_id IS NULL AND :parent_id IS NULL) OR parent_id = :parent_id) LIMIT 1";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindParam(':name', $name);

    if (is_null($parent_id)) {
        $stmtCheck->bindValue(':parent_id', null, PDO::PARAM_NULL);
    } else {
        $stmtCheck->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
    }

    $stmtCheck->execute();
    $existingFolder = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($existingFolder) {
        echo json_encode([
            'success' => false,
            'msg' => 'Ya existe una carpeta con ese nombre en esta ubicación'
        ]);
        exit;
    }

    // Incluir sort_order en el INSERT
    $sqlInsert = "INSERT INTO folders (name, parent_id, sort_order, created_at)
                  VALUES (:name, :parent_id, :sort_order, NOW())";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bindParam(':name', $name);
    $stmtInsert->bindValue(':sort_order', $sort_order, PDO::PARAM_INT);

    if (is_null($parent_id)) {
        $stmtInsert->bindValue(':parent_id', null, PDO::PARAM_NULL);
    } else {
        $stmtInsert->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
    }

    $stmtInsert->execute();
    $lastId = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'id' => $lastId,
        'name' => $name
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}

// Helper function to check for circular references
function isDescendantFolder($conn, $potentialDescendant, $ancestorId) {
    $stmt = $conn->prepare("SELECT parent_id FROM folders WHERE id = ?");
    $stmt->execute([$potentialDescendant]);
    $folder = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$folder || !$folder['parent_id']) {
        return false;
    }
    
    if ($folder['parent_id'] == $ancestorId) {
        return true;
    }
    
    return isDescendantFolder($conn, $folder['parent_id'], $ancestorId);
}
?>
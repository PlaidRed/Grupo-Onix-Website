<?php
include_once(__DIR__ . '/../../include/Database.php');
header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
  echo json_encode(['success' => false, 'msg' => 'ID inválido']);
  exit;
}

try {
  $db = new Database();
  $conn = $db->_conexion;

  // Eliminar recursivamente carpetas e hijos
  function deleteFolderRecursively($conn, $folderId) {
    // Eliminar enlaces
    $stmtLinks = $conn->prepare("DELETE FROM links WHERE folder_id = ?");
    $stmtLinks->execute([$folderId]);

    // Buscar subcarpetas
    $stmtSub = $conn->prepare("SELECT id FROM folders WHERE parent_id = ?");
    $stmtSub->execute([$folderId]);
    $subfolders = $stmtSub->fetchAll(PDO::FETCH_COLUMN);

    foreach ($subfolders as $subId) {
      deleteFolderRecursively($conn, $subId);
    }

    // Eliminar carpeta actual
    $stmtFolder = $conn->prepare("DELETE FROM folders WHERE id = ?");
    $stmtFolder->execute([$folderId]);
  }

  deleteFolderRecursively($conn, $id);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}

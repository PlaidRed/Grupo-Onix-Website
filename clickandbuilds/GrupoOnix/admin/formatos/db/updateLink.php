<?php
include_once(__DIR__ . '/../../include/Database.php');
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$id = isset($input['id']) ? intval($input['id']) : 0;
$username = isset($input['username']) ? trim($input['username']) : null;
$password = isset($input['password']) ? trim($input['password']) : null;
$label = isset($input['label']) ? trim($input['label']) : null;
$url = isset($input['url']) ? trim($input['url']) : null;

// Add support for moving links between folders
$folder_id = isset($input['folder_id']) ? intval($input['folder_id']) : null;
$sort_order = isset($input['sort_order']) ? intval($input['sort_order']) : null;

if ($id <= 0) {
  echo json_encode(['success' => false, 'msg' => 'ID inválido']);
  exit;
}

try {
  $db = new Database();
  $conn = $db->_conexion;

  $fields = [];
  $params = [];

  if ($label !== null) {
    $fields[] = "label = ?";
    $params[] = $label;
  }
  if ($url !== null) {
    $fields[] = "url = ?";
    $params[] = $url;
  }
  if ($username !== null) {
    $fields[] = "username = ?";
    $params[] = $username;
  }
  if ($password !== null) {
    $fields[] = "password = ?";
    $params[] = $password;
  }

  // Support for folder_id updates (for drag & drop)
  if ($folder_id !== null) {
    $fields[] = "folder_id = ?";
    $params[] = $folder_id > 0 ? $folder_id : null;
  }

  if ($sort_order !== null) {
      $fields[] = "sort_order = ?";
      $params[] = $sort_order;
  }

  if (empty($fields)) {
    echo json_encode(['success' => false, 'msg' => 'Nada que actualizar.']);
    exit;
  }

  $params[] = $id;

  $sql = "UPDATE links SET " . implode(", ", $fields) . " WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute($params);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
<?php
include_once(__DIR__ . '/../../include/Database.php');
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$folder_id = isset($input['folder_id']) ? intval($input['folder_id']) : 0;
$label = isset($input['label']) ? trim($input['label']) : '';
$url = isset($input['url']) ? trim($input['url']) : '';
$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';
$sort_order = isset($input['sort_order']) ? intval($input['sort_order']) : 0;

if ($folder_id <= 0 || $label === '' || $url === '') {
  echo json_encode(['success' => false, 'msg' => 'Datos inválidos']);
  exit;
}

try {
  $db = new Database();
  $conn = $db->_conexion;

  $stmt = $conn->prepare("INSERT INTO links (folder_id, label, url, username, password, sort_order, created_at)
                          VALUES (?, ?, ?, ?, ?, ?, NOW())");
  $stmt->execute([$folder_id, $label, $url, $username, $password, $sort_order]);

  $id = $conn->lastInsertId();
  echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}

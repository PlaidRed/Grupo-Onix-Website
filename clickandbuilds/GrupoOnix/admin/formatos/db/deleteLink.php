<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once(__DIR__ . '/../../include/Database.php');
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
  echo json_encode(['success' => false, 'msg' => 'No se recibió JSON válido']);
  exit;
}

$link_id = isset($input['id']) ? intval($input['id']) : 0;

if ($link_id <= 0) {
  echo json_encode(['success' => false, 'msg' => 'ID inválido']);
  exit;
}

try {
  $db = new Database();
  $conn = $db->_conexion;

  $stmt = $conn->prepare("DELETE FROM links WHERE id = ?");
  $stmt->execute([$link_id]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}

<?php
include_once(__DIR__ . '/../../include/Database.php');

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['newName']) ? trim($_POST['newName']) : '';

if ($id <= 0 || $name === '') {
  echo json_encode(['success' => false, 'msg' => 'Datos inválidos']);
  exit;
}

try {
  $db = new Database();
  $pdo = $db->_conexion;

  $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ?");
  $stmt->execute([$name, $id]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
?>

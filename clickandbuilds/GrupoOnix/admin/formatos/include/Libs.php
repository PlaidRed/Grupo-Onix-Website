<?php

$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
	$ruta .= "../";
}

// Se incluye la clase Common y la base de datos
include_once($ruta . "include/Common.php");
include_once($ruta . "include/Database.php"); // Asegúrate que la ruta sea correcta

$module = 3;

class Libs extends Common {

	// Crear carpeta
	function crearCarpeta() {
		$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
		$parentId = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

		if ($nombre === '') {
			echo json_encode(['error' => true, 'msg' => 'El nombre de la carpeta es requerido']);
			return;
		}

		try {
			$db = Database::getInstance()->getConnection();

			$stmt = $db->prepare("INSERT INTO folders (name, parent_id) VALUES (?, ?)");
			$stmt->bind_param("si", $nombre, $parentId);
			$stmt->execute();

			echo json_encode([
				'error' => false,
				'msg' => 'Carpeta creada exitosamente',
				'id' => $stmt->insert_id
			]);
		} catch (Exception $e) {
			echo json_encode([
				'error' => true,
				'msg' => 'Error al crear carpeta: ' . $e->getMessage()
			]);
		}
	}

	// Crear un enlace dentro de una carpeta
	function crearEnlace() {
		$folderId  = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
		$label     = isset($_POST['label']) ? trim($_POST['label']) : '';
		$url       = isset($_POST['url']) ? trim($_POST['url']) : '';
		$username  = isset($_POST['username']) ? trim($_POST['username']) : null;
		$password  = isset($_POST['password']) ? trim($_POST['password']) : null;

		if ($folderId <= 0 || $label === '' || $url === '') {
			echo json_encode(['error' => true, 'msg' => 'Faltan datos obligatorios']);
			return;
		}

		try {
			$db = Database::getInstance()->getConnection();

			$stmt = $db->prepare("INSERT INTO links (folder_id, label, url, username, password) VALUES (?, ?, ?, ?, ?)");
			$stmt->bind_param("issss", $folderId, $label, $url, $username, $password);
			$stmt->execute();

			echo json_encode([
				'error' => false,
				'msg' => 'Enlace guardado exitosamente',
				'id' => $stmt->insert_id
			]);
		} catch (Exception $e) {
			echo json_encode([
				'error' => true,
				'msg' => 'Error al guardar enlace: ' . $e->getMessage()
			]);
		}
	}

	function listarEnlaces() {
		$folderId = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;

		if ($folderId <= 0) {
			echo json_encode(['error' => true, 'msg' => 'ID de carpeta inválido']);
			return;
		}

		try {
			$db = Database::getInstance()->getConnection();
			$stmt = $db->prepare("SELECT id, label, url, username, password FROM links WHERE folder_id = ?");
			$stmt->bind_param("i", $folderId);
			$stmt->execute();

			$result = $stmt->get_result();
			$links = [];

			while ($row = $result->fetch_assoc()) {
				$links[] = $row;
			}

			echo json_encode(['error' => false, 'links' => $links]);
		} catch (Exception $e) {
			echo json_encode(['error' => true, 'msg' => 'Error al obtener enlaces: ' . $e->getMessage()]);
		}
	}

	function editarEnlace() {
		$id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$label    = isset($_POST['label']) ? trim($_POST['label']) : '';
		$url      = isset($_POST['url']) ? trim($_POST['url']) : '';
		$username = isset($_POST['username']) ? trim($_POST['username']) : null;
		$password = isset($_POST['password']) ? trim($_POST['password']) : null;

		if ($id <= 0 || $label === '' || $url === '') {
			echo json_encode(['error' => true, 'msg' => 'Faltan datos para actualizar']);
			return;
		}

		try {
			$db = Database::getInstance()->getConnection();
			$stmt = $db->prepare("UPDATE links SET label = ?, url = ?, username = ?, password = ? WHERE id = ?");
			$stmt->bind_param("ssssi", $label, $url, $username, $password, $id);
			$stmt->execute();

			echo json_encode(['error' => false, 'msg' => 'Enlace actualizado']);
		} catch (Exception $e) {
			echo json_encode(['error' => true, 'msg' => 'Error al actualizar: ' . $e->getMessage()]);
		}
	}

	function eliminarEnlace() {
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

		if ($id <= 0) {
			echo json_encode(['error' => true, 'msg' => 'ID de enlace inválido']);
			return;
		}

		try {
			$db = Database::getInstance()->getConnection();
			$stmt = $db->prepare("DELETE FROM links WHERE id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();

			echo json_encode(['error' => false, 'msg' => 'Enlace eliminado']);
		} catch (Exception $e) {
			echo json_encode(['error' => true, 'msg' => 'Error al eliminar: ' . $e->getMessage()]);
		}
	}

}

// Controlador de acciones
if (isset($_REQUEST['accion'])) {
	$libs = new Libs;

	switch ($_REQUEST['accion']) {
		case "crearCarpeta":
			$libs->crearCarpeta();
			break;
		case "crearEnlace":
			$libs->crearEnlace();
			break;
		case "listarEnlaces":
			$libs->listarEnlaces();
			break;
		case "editarEnlace":
			$libs->editarEnlace();
			break;
		case "eliminarEnlace":
			$libs->eliminarEnlace();
			break;
	}
}

?>
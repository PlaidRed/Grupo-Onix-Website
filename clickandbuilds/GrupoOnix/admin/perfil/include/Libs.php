<?php

$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

//$url = explode("/", $_SERVER["REQUEST_URI"]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
	$ruta .= "../";
}

//Se incluye la clase Common
include_once($ruta."include/Common.php");
$module = 0;

class Libs extends Common {
	function saveRecord() {
		global $ruta;
		if(!isset($_SESSION)){
			@session_start();
		}

		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		$obligatorios = array("password", "confirmacion");

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]){
				if($this->is_empty(trim($valor)) && in_array($clave, $obligatorios)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json['msg'] = "El campo ". lcfirst($clave)." es obligatorio.";	
				}
			}
		}

		if(!$json['error']) {
			$foundNumber = false;
			$foundChars = false;
			$numbers = "1234567890";
			$chars = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
			$password = $_POST['password'];
		
			for($i = 0; $i < strlen($password); $i++){
				
				if(!$foundNumber && strpos($numbers, $password[$i]) !== false){
					$foundNumber = true;
				}
			}
			
			for($i = 0; $i < strlen($password); $i++){
				
				if(!$foundChars && strpos($chars, $password[$i]) !== false){
					$foundChars = true;
				}
			}
			
			if(!$foundNumber){
				$json["error"] = true;
				$json["msg"] = "La contraseña debe contener al menos un número (0-9)";
				$json["focus"] = "password";
			}
			elseif(!$foundChars){
				$json["error"] = true;
				$json["msg"] = "La contraseña debe contener al menos una letra mayúscula (A-Z)";
				$json["focus"] = "password";
			}
			elseif($password != $_POST['confirmacion']){
				$json["msg"] = "Las contraseñas no coinciden";
				$json["error"] = true;
				$json["focus"] = "password";
			}
		}

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			$sql = "UPDATE SISTEMA_USUARIO SET SIU_PASSWORD = ?
					WHERE SIU_ID = ?";

			$pass_encr = $this->encrypt($_POST['password']);

			$values = array($pass_encr,
							$_SESSION["onx"]["userid"]);	

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage());
			}

			$db->commit();
			$json['msg'] = 'Contraseña cambiada con éxito.';
		}

		echo json_encode($json);
	}

}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "saveRecord":
			$libs->saveRecord();
			break;		
	}
}

?>
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
$module = 15;

class Libs extends Common {
	/*
	 * @author: Cynthia Castillo 
	 *  
	 * Imprime la tabla de registros de perfil de usuarios EXCEPTUANDO 'daemon'
	 */
	function printTable() {
		global $module;
		global $ruta;

		/*
		 * Query principal
		 */
		$sqlQuery = "SELECT *
					 FROM cotizadores
					 ORDER BY titulo asc";
		
		//Se prepara la consulta de extración de datos
		$consulta = $this->_conexion->prepare($sqlQuery);

		//echo $sqlQueryFiltered;

		//Se ejecuta la consulta
		try {
			
			$consulta->execute();
			
			//Se imprime la tabla
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			
			/*
			* Salida de Datos
			*/
			$data = array();
			$counter = 0;
			
			foreach ($puntero as $cotizadores) {
				$counter++;

				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$cotizadores['id'],
										"title"		=>	"Ver/Editar");
				$btn_editar = $this->printButton($module, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$cotizadores['id'],
										"extras"	=>	"data-name='".$cotizadores["titulo"]."'");
				$btn_borrar = $this->printButton($module, "baja", $params_borrar);

				$aRow = array($cotizadores['titulo'], 
							  $cotizadores["user"], 
							  $cotizadores["password"] ,
							  '<a href="'.$cotizadores["liga"].' target="_blank">Ver liga</a>',
							  '<img src="include/imagenes/'.$cotizadores['imagen'].'">',
							  $btn_editar.$btn_borrar);
				
				//Se guarda la fila en la matriz principal
				$data[] = $aRow;
			}

			$json = array();
			$json['data'] = $data;

			echo json_encode($json);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}

	/*
	 * @author: Cynthia Castillo
	 * 
	 * @param '$id'		int. 	ID de perfil de usuario
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Metodo que borra una fila de la BD
	 */
	function deleteRecord() {
		$json = array();
		$json['error'] = true;
		$json['msg'] = "Experimentamos fallas técnicas.";
		if(isset($_POST['id'])){
			try{
				$consulta = $this->_conexion->prepare("DELETE FROM cotizadores WHERE id = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El mensaje fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El mensaje elegido no pudo ser eliminado.";
				}
			}catch(PDOException $e){
				die($e->getMessage());
			}	
		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo
	 * 
	 * @param '$id'		int. 	ID de perfil de usuario
	 * 
	 * Metodo que imprime la tabla de permisos de un perfil de usuario en base a su id
	 */
	function showRecord() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas.";
		if(isset($_POST['id'])){
			try{
				$sql = "SELECT *
						FROM cotizadores
						WHERE id = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$row = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$row['imagen'] = ($row['imagen'] != '' ? '<a href="include/imagenes/'.$row['imagen'].'" target="_blank" class="btn btn-info"><i class="la la-file-pdf-o
"></i> Ver imagen Actual</a>' : '');

					$json = array_merge($json, $row);
						
				} else {
					$json['error'] = true;
				}

			}catch(PDOException $e){
				die($e->getMessage());
			}
		}
		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2013-12-27
	 * 
	 * 
	 * Guarda el perfil de un usuario
	 */

	function saveRecord() {
		global $ruta;
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		// Set timezone to Mexico
		date_default_timezone_set('America/Monterrey');

		$obligatorios = array("titulo",
							"user",
							"password",
							"liga");
		$excepciones = array("imagen");

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"] && !in_array($clave, $excepciones)){
				if($this->is_empty(trim($valor)) && in_array($clave, $obligatorios)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json['msg'] = "El campo ". lcfirst($clave)." es obligatorio.";	
				}
			}
		}

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			$passwordChanged = false;
			
			if(isset($_POST['id'])) { //UPDATE
				// Check if password is being changed
				$checkSql = "SELECT password FROM cotizadores WHERE id = ?";
				$checkQuery = $db->prepare($checkSql);
				$checkQuery->execute([$_POST['id']]);
				$currentRecord = $checkQuery->fetch(PDO::FETCH_ASSOC);
				
				$passwordChanged = ($currentRecord && $currentRecord['password'] !== $_POST['password']);
				
				if($passwordChanged) {
					$sql = "UPDATE cotizadores SET user = ?,
												password = ?,
												titulo = ?,
												liga = ?,
												fecha_cambio = ?
							WHERE id = ?";
					
					$values = array($_POST['user'],
									$_POST['password'],
									$_POST['titulo'],
									$_POST['liga'],
									date('Y-m-d H:i:s'), // Current timestamp in Mexico timezone
									$_POST['id']);
				} else {
					$sql = "UPDATE cotizadores SET user = ?,
												password = ?,
												titulo = ?,
												liga = ?
							WHERE id = ?";
					
					$values = array($_POST['user'],
									$_POST['password'],
									$_POST['titulo'],
									$_POST['liga'],
									$_POST['id']);
				}

			} else { //INSERCION
				$sql = "INSERT INTO cotizadores (user,
											password,
											titulo,
											liga,
											fecha_cambio)
						VALUES( ?, ?, ?, ?, ?)";
						
				$values = array($_POST['user'],
								$_POST['password'],
								$_POST['titulo'],
								$_POST['liga'],
								date('Y-m-d H:i:s')); // Current timestamp for new records
				
				$passwordChanged = true; // New records count as "password changed"
			}

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

				if(isset($_POST['id'])) {
					$id = $_POST['id'];
				} else {
					$id = $this->last_id();

					//Hacemos una nueva notificación
					//Enviamos en orden: Titulo, Mensaje, URL, Si es URL interna (0) o Externa (1), Icono y Color
					$this->newNotification('Nuevo Cotizador',
										$_POST['titulo'],
										$_POST['liga'],
										1,
										'ft-list',
										'primary');
				}

				$archivo = '';
				if(isset($_FILES['imagen']['name']) && $_FILES['imagen']['name'] != "") {
					$filename = $_FILES['imagen']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$name = uniqid();
					$doc_name = "imagenes/".$name.".".$ext;
					$archivo = $name.'.'.$ext;
					if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $doc_name)) {
						echo 'Error al subir archivo';
					}

					$sql = "UPDATE cotizadores SET imagen = ?
							WHERE id = ?";
					$values = array($archivo, $id);		
					$consulta = $db->prepare($sql);
					$consulta->execute($values);
				}

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage().$sql);
			}

			$db->commit();
			$json['msg'] = 'El cotizador se guardó con éxito.';
			
			// Add flag to indicate if password was changed for frontend
			$json['password_changed'] = $passwordChanged;
		}

		echo json_encode($json);
	}

	function to_permalink($str)
	{
		if($str !== mb_convert_encoding( mb_convert_encoding($str, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32') )
			$str = mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
		$str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
		$str = preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $str);
		$str = html_entity_decode($str, ENT_NOQUOTES, 'UTF-8');
		$str = preg_replace(array('`[^a-z0-9]`i','`[-]+`'), '-', $str);
		$str = strtolower( trim($str, '-') );
		return $str;
	}

	function getMarcas() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['marcas'] = '';

		try{
			$sql = "SELECT *
					FROM productos
					ORDER BY orden ASC";

			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$marcas = $consulta->fetchAll(PDO::FETCH_ASSOC);

			if ($consulta->rowCount() > 0) {
				
				foreach ($marcas as $marca) {
					$json['marcas'] .= '<div id="marca_'.$marca['id'].'" class="col-sm-2 text-center">
											<img src="'.$ruta.'/img/'.$marca['imagen'].'" title="'.$marca['nombre'].'">
										</div>';
				}
					
			} else {
				$json['error'] = true;
			}

		}catch(PDOException $e){
			die($e->getMessage());
		}

		echo json_encode($json);

	}

	function saveOrder() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';

		if(isset($_POST['marca']) && is_array($_POST['marca'])){

			$db = $this->_conexion;
			$db->beginTransaction();

			foreach ($_POST['marca'] as $key => $marca) {
				
				$sql = "UPDATE productos SET orden = ?
						WHERE id = ?";

				$values = array($key, $marca);

				$consulta = $db->prepare($sql);

				try {
					$consulta->execute($values);
				} catch(PDOException $e){
					$db->rollBack();
					die($e->getMessage());
				}	

			}

			$db->commit();
			$json['msg'] = 'Orden guardado con éxito.';


		} else {
			$json['error'] = true;
			$json['msg'] = 'Error al escoger el orden de los productos';
		}

		echo json_encode($json);

	}

	function savePasswordChangeEvent() {
		$json = ['error' => true, 'msg' => 'Invalid request'];
		if(isset($_POST['titulo']) && isset($_POST['fecha'])) {
			try {
				$sql = "INSERT INTO password_changes (titulo, fecha)
						VALUES (:titulo, :fecha)";
				$stmt = $this->_conexion->prepare($sql);
				$stmt->execute([
					':titulo' => $_POST['titulo'],
					':fecha' => $_POST['fecha']
				]);
				$json = ['error' => false, 'msg' => 'Event saved'];
			} catch(PDOException $e) {
				$json['msg'] = $e->getMessage();
			}
		}
		echo json_encode($json);
	}

	function getPasswordChanges() {
		$json = array();
		$json['events'] = [];

		try {
			$sql = "SELECT titulo, fecha_cambio 
					FROM cotizadores 
					WHERE fecha_cambio IS NOT NULL
					ORDER BY fecha_cambio DESC";

			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$records = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach($records as $r) {
				$json['events'][] = [
					'title' => 'Contraseña cambiada: "'.$r['titulo'].'"',
					'start' => $r['fecha_cambio'],
					'allDay' => true,
					'backgroundColor' => '#28a745',
					'borderColor' => '#28a745',
				];
			}

		} catch(PDOException $e) {
			$json['error'] = true;
			$json['msg'] = $e->getMessage();
		}

		echo json_encode($json);
	}


	
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "printTable":
			$libs->printTable();
			break;
		case "deleteRecord":
			$libs->deleteRecord();
			break;
		case "showRecord":
			$libs->showRecord();
			break;	
		case "saveRecord":
			$libs->saveRecord();
			break;
		case "getMarcas":
			$libs->getMarcas();
			break;	
		case "saveOrder":
			$libs->saveOrder();
			break;
		case "savePasswordChangeEvent":
			$libs->savePasswordChangeEvent();
			break;
		case "getPasswordChanges":
			$libs->getPasswordChanges();
			break;		
	}
}

?>
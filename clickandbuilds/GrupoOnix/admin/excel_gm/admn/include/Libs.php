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
$module = 5;

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
					 FROM exceles_gm";
		
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
			
			foreach ($puntero as $excel) {
				$counter++;
				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$excel['id'],
										"title"		=>	"Ver/Editar");
				$btn_editar = $this->printButton($module, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$excel['id'],
										"extras"	=>	"data-name='".$excel["nombre"]."'");
				$btn_borrar = $this->printButton($module, "baja", $params_borrar);

				$aRow = array($excel['nombre'], '<img class="prd-img" src="include/logos/'.$excel['imagen'].'">' ,$btn_editar.$btn_borrar);
				
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
				$consulta = $this->_conexion->prepare("DELETE FROM exceles_gm WHERE id = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El excel fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El excel elegido no pudo ser eliminado.";
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
						FROM exceles_gm
						WHERE id = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$row = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {

					$row['imagen'] = '<img class="img-fluid" src="include/logos/'.$row['imagen'].'">';
					$row['excel'] = ($row['excel'] != '' ? '<a href="include/excel/'.$row['excel'].'" target="_blank" class="btn btn-info">
															<i class="la la-file-excel-o"></i> Ver Excel Actual
														</a>' : '');

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

		$obligatorios = array("nombre");
		$excepciones = array("excel", "imagen");

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

		//Revisamos si está el excel y la imagen
		if(!isset($_POST['id'])) {
			if(!isset($_FILES['imagen']['tmp_name']) || $_FILES['imagen']['name'] == "") {
				$json["error"] = true;
				$json["focus"] = 'imagen';
				$json['msg'] = "El campo de Imagen es obligatorio.";	
			} else if(!isset($_FILES['excel']['tmp_name']) || $_FILES['excel']['name'] == "") {
				$json["error"] = true;
				$json["focus"] = 'excel';
				$json['msg'] = "El campo de Excel es obligatorio.";	
			}
		}

		//Revisamos que tengan las extensiones correctas
		if(isset($_FILES['imagen']['name']) && $_FILES['imagen']['name'] != "") {
			//Verifica si es imagen
			$allowed =  array('jpg', 'png', 'JPG', 'PNG', 'JPEG', 'jpeg');
			$filename = $_FILES['imagen']['name'];
			$ext1 = pathinfo($filename, PATHINFO_EXTENSION);
			if(!in_array($ext1,$allowed) ) {
			   	$json["error"] = true;
				$json["msg"] = "Favor de seleccionar una imagen con la extensión correcta para el Excel.";
			}
		}

		if(isset($_FILES['excel']['name']) && $_FILES['excel']['name'] != "") {
			//Verifica si es excel
			$allowed =  array('xls', 'XLS', 'xlsx', 'XLSX', 'xlsm', 'XLSM');
			$filename = $_FILES['excel']['name'];
			$ext1 = pathinfo($filename, PATHINFO_EXTENSION);
			if(!in_array($ext1,$allowed) ) {
			   	$json["error"] = true;
				$json["msg"] = "Favor de seleccionar un Excel con la extensión correcta.";
			}
		}

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			$values = array($_POST['nombre']);

			if(isset($_POST['id'])) { //UPDATE
				$sql = "UPDATE exceles_gm SET nombre = ?
						WHERE id = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO exceles_gm (nombre)
						VALUES( ? )";
			}

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

				if(isset($_POST['id'])) {
					$id = $_POST['id'];
				} else {
					$id = $this->last_id();
				}

				$archivo = '';
				if(isset($_FILES['excel']['tmp_name']) && $_FILES['excel']['name'] != "") {
					$filename = $_FILES['excel']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$name = uniqid();
					$doc_name = "excel/".$name.".".$ext;
					$archivo = $name.'.'.$ext;
					if(!move_uploaded_file($_FILES['excel']['tmp_name'], $doc_name)) {
						echo 'Error al subir archivo';
					}

					$sql = "UPDATE exceles_gm SET excel = ?
							WHERE id = ?";
					$values = array($archivo, $id);		
					$consulta = $db->prepare($sql);
					$consulta->execute($values);
				}

				if(isset($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['name'] != "") {
					$filename = $_FILES['imagen']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$name = uniqid();
					$doc_name = "logos/".$name.".".$ext;
					$archivo = $name.'.'.$ext;
					if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $doc_name)) {
						echo 'Error al subir archivo';
					}

					$sql = "UPDATE exceles_gm SET imagen = ?
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
			$json['msg'] = 'El Excel se guardó con éxito.';
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
	}
}

?>
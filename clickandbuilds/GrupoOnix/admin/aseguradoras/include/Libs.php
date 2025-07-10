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
					 FROM aseguradoras
					 ORDER BY orden asc";
		
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
			
			foreach ($puntero as $producto) {
				$counter++;


				$img = '<img src="'.$ruta.'img/'.$producto['imagen'].'" alt="'.$producto['nombre'].'" class="img-fluid prd-img">';

				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$producto['id'],
										"title"		=>	"Ver/Editar");
				$btn_editar = $this->printButton($module, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$producto['id'],
										"extras"	=>	"data-name='".$producto["nombre"]."'");
				$btn_borrar = $this->printButton($module, "baja", $params_borrar);

				$aRow = array($producto["nombre"], $img ,$btn_editar.$btn_borrar);
				
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
				$consulta = $this->_conexion->prepare("DELETE FROM aseguradoras WHERE id = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "La aseguradora fue eliminada con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "La aseguradora elegido no pudo ser eliminada.";
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
						FROM aseguradoras
						WHERE id = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$row = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$json = array_merge($json, $row);

					/*Verifica la imagen*/
					$json['img'] = '<img src="'.$ruta.'/img/'.$row['imagen'].'" class="img-fluid">';
						
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

		//Revisamos imagen
		if((!isset($_FILES['imagen']['name']) || $_FILES['imagen']['name'] == "") && !isset($_POST['id'])) {
			$json["focus"] = "imagen";
			$json["error"] = true;
			$json["msg"] = "Favor de seleccionar una imagen para el producto.";
		} else if(isset($_FILES['imagen']['name']) && $_FILES['imagen']['name'] != "") {
			//Verifica si es imagen
			$allowed =  array('jpg', 'png');
			$filename = $_FILES['imagen']['name'];
			$ext1 = pathinfo($filename, PATHINFO_EXTENSION);
			if(!in_array($ext1,$allowed) ) {
			   	$json["error"] = true;
				$json["msg"] = "Favor de seleccionar una imagen con la extensión correcta para el producto.";
			}
		}

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			$values = array($_POST['nombre']);

			if(isset($_POST['id'])) { //UPDATE
				$sql = "UPDATE aseguradoras SET nombre = ?
						WHERE id = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO aseguradoras (nombre)
						VALUES( ? )";
			}

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

				if(isset($_POST['id'])) {
					$pro_id = $_POST['id'];

				} else {
					$pro_id = $this->last_id();
				}

				/*Subimos imagen principal*/
				if(isset($_FILES['imagen']['name']) && $_FILES['imagen']['name'] != "") {
					$filename = $_FILES['imagen']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$name = uniqid();
					$img_name = $ruta."../img/aseguradoras/".$name.".".$ext;

					//Redimensiona la imagen
                    list($orig_width, $orig_height) = getimagesize($_FILES[ 'imagen' ][ 'tmp_name' ]);

                    $width = $orig_width;
                    $height = $orig_height;

                    $max_height = 500;
                    $max_width = 500;

                    // taller
                    if ($height > $max_height) {
                        $width = ($max_height / $height) * $width;
                        $height = $max_height;
                    }

                    // wider
                    if ($width > $max_width) {
                        $height = ($max_width / $width) * $height;
                        $width = $max_width;
                    }

                    $image_p = imagecreatetruecolor($width, $height);

                    $tipofoto =  $_FILES[ 'imagen' ]['type'];
                    if ($tipofoto == 'image/jpg' || $tipofoto == 'image/jpeg') {
                        $image = imagecreatefromjpeg($_FILES[ 'imagen' ][ 'tmp_name' ]);
                    }

                    if ($tipofoto == 'image/png') {
                        // integer representation of the color black (rgb: 0,0,0)
				        $background = imagecolorallocate($image_p , 0, 0, 0);
				        // removing the black from the placeholder
				        imagecolortransparent($image_p, $background);

				        // turning off alpha blending (to ensure alpha channel information
				        // is preserved, rather than removed (blending with the rest of the
				        // image in the form of black))
				        imagealphablending($image_p, false);

				        // turning on alpha channel information saving (to ensure the full range
				        // of transparency is preserved)
				        imagesavealpha($image_p, true);
                       	$image = imagecreatefrompng($_FILES[ 'imagen' ][ 'tmp_name' ]);
                    }
                    
                    imagecopyresampled($image_p, $image, 0, 0, 0, 0, 
                                       $width, $height, $orig_width, $orig_height);

                    if ($tipofoto == 'image/jpg' || $tipofoto == 'image/jpeg') {
                        imagejpeg($image_p,$img_name);
                    }

                    if ($tipofoto == 'image/png') {
                        imagepng($image_p,$img_name);
                    }


					/*if(!move_uploaded_file($_FILES["img-principal"]["tmp_name"], $img_name)){
						$json['error'] = true;
						$json['msg'] = "Error al subir archivo. Inténtelo de nuevo más tarde.";
					} else {*/
						$sql_img = "UPDATE aseguradoras SET imagen = ?
								    WHERE id = ?";
						
						$values_img = array('aseguradoras/'.$name.".".$ext, $pro_id);
						$consulta_img = $db->prepare($sql_img);
						try {
							$consulta_img->execute($values_img);

						} catch (PDOException $e) {
							$db->rollBack();
							die($e->getMessage().$sql_img);
						}
				}

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage().$sql);
			}

			$db->commit();
			$json['msg'] = 'La aseguradora se guardó con éxito.';
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
					FROM aseguradoras
					ORDER BY orden ASC";

			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$marcas = $consulta->fetchAll(PDO::FETCH_ASSOC);

			if ($consulta->rowCount() > 0) {
				
				foreach ($marcas as $marca) {
					$json['marcas'] .= '<div id="marca_'.$marca['id'].'" class="col-sm-2 text-center">
											<img src="'.$ruta.'/img/'.$marca['imagen'].'" title="'.$marca['nombre'].'" class="img-responsive img-fluid">
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
				
				$sql = "UPDATE aseguradoras SET orden = ?
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
			$json['msg'] = 'Error al escoger el orden de las aseguradoras';
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
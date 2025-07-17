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
$module = 16;

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
					 FROM servicios";
		
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

			$array_cambios = array(1, 2, 7);
			
			foreach ($puntero as $articulo) {
				$counter++;

				//Botones
				$lnk_cambio = (in_array($articulo['id'], $array_cambios) ? 'cambios.php?id='.$articulo['id'] : $this->to_permalink($articulo['servicio']).'.php');
				$params_editar = array(	"link"		=>	$lnk_cambio,
										"title"		=>	"Ver/Editar");
				$btn_editar = $this->printButton($module, "cambios", $params_editar);

				$img = '<img src="'.$ruta.'assets/images/'.$articulo['imagen'].'" alt="'.$articulo['servicio'].'" class="img-responsive prd-img">';

				$aRow = array($articulo['servicio'], $img ,$btn_editar);
				
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
				$consulta = $this->_conexion->prepare("DELETE FROM blog_categorias WHERE id = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "La categoría fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "La categoría elegido no pudo ser eliminado.";
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
		$json['msg'] = "";
		$json['productos'] = '';
		$json['aseguradoras'] = '';
		if(isset($_POST['id'])){
			try{

				$db = $this->_conexion;

				$sql = "SELECT *
						FROM contenido
						WHERE id = 5 OR id = 6 OR id = 7";

				$consulta = $db->prepare($sql);
				$consulta->execute();
				$contenidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($contenidos as $contenido) {
					if($contenido['seccion'] == 'unete_banner_img') {
						$json['unete_banner_img'] = '<img src="'.$ruta.'img/'.$contenido['contenido'].'" class="img-fluid">';
					} else {
						$json[$contenido['seccion']] = $contenido['contenido'];	
					}
					
				}

				//Revisamos Ventajas Competitivas
				$sql = "SELECT *
						FROM ventajas";

				$consulta = $db->prepare($sql);
				$consulta->execute();
				$ventajas = $consulta->fetchAll(PDO::FETCH_ASSOC);
				$json['ventajas'] = '';
				foreach ($ventajas as $ventaja) {
					$json['ventajas'] .= '<div class="form-group row row-ventaja">
			                                  <div class="col-sm-10">
			                                    <textarea class="form-control" name="ventaja[]">'.$ventaja['contenido'].'</textarea>
			                                  </div>
			                                  <div>
			                                    <button type="button" class="btn btn-danger eliminar-ventaja">
			                                       <i class="ft-x"></i> Eliminar
			                                    </button>
			                                  </div>
			                                </div>';
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

		$obligatorios = array("unete_banner_txt",
							  "unete_email");
		$excepciones = array("unete_banner_img",
							 "ventaja");

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

			$sql = "UPDATE contenido SET contenido = ?
					WHERE id = 6";
			$values = array($_POST['unete_banner_txt']);

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

				$sql = "UPDATE contenido SET contenido = ?
						WHERE id = 7";
				$values = array($_POST['unete_email']);
				$consulta = $db->prepare($sql);
				$consulta->execute($values);

				if(isset($_FILES['unete_banner_img']['name']) && $_FILES['unete_banner_img']['name'] != "") {
					$filename = $_FILES['unete_banner_img']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$name = uniqid();
					$img_name = $ruta."../img/banners/".$name.".".$ext;

					//Redimensiona la imagen
                    list($orig_width, $orig_height) = getimagesize($_FILES[ 'unete_banner_img' ][ 'tmp_name' ]);

                    $width = $orig_width;
                    $height = $orig_height;

                    $max_height = 1440;
                    $max_width = 1440;

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

                    $tipofoto =  $_FILES[ 'unete_banner_img' ]['type'];
                    if ($tipofoto == 'image/jpg' || $tipofoto == 'image/jpeg') {
                        $image = imagecreatefromjpeg($_FILES[ 'unete_banner_img' ][ 'tmp_name' ]);
                    }

                    if ($tipofoto == 'image/png') {
                       $image = imagecreatefrompng($_FILES[ 'unete_banner_img' ][ 'tmp_name' ]);
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
						$sql_img = "UPDATE contenido SET contenido = ?
								    WHERE id = 5";
						
						$values_img = array('banners/'.$name.".".$ext);
						$consulta_img = $db->prepare($sql_img);
						try {
							$consulta_img->execute($values_img);

						} catch (PDOException $e) {
							$db->rollBack();
							die($e->getMessage().$sql_img);
						}
				}

				//Eliminamos todas las ventajas
				$sql_ventajas = 'DELETE FROM ventajas WHERE 1=1';
				$consulta_ventajas = $db->prepare($sql_ventajas);
				try {
					$consulta_ventajas->execute();
				} catch (PDOException $e) {
					$db->rollBack();
					die($e->getMessage().$sql_ventajas);
				}

				//Guardamos las ventajas que pusieron
				if(isset($_POST['ventaja'])) {
					
					foreach ($_POST['ventaja'] as $ventaja) {
						$sql = "INSERT INTO ventajas (contenido)
								VALUES( ? )";
						$values = array($ventaja);
						$consulta_ventajas = $db->prepare($sql);
						try {
							$consulta_ventajas->execute($values);
						} catch (PDOException $e) {
							$db->rollBack();
							die($e->getMessage().$sql);
						}
					}
				
				}

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage().$sql);
			}

			$db->commit();
			$json['msg'] = 'El contenido se guardó con éxito.';
		}

		echo json_encode($json);
	}

	function saveRecord2() {
		global $ruta;
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		$obligatorios = array("servicio",
							  "servicio_en");
		$excepciones = array();

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

		if((!isset($_FILES['imagen']['name']) || $_FILES['imagen']['name'] == "") && !isset($_POST['id'])) {
			$json["focus"] = "imagen";
			$json["error"] = true;
			$json["msg"] = "Favor de seleccionar una imagen externa para el servicio.";
		} else if(isset($_FILES['imagen']['name']) && $_FILES['imagen']['name'] != "") {
			//Verifica si es imagen
			$allowed =  array('jpg', 'png');
			$filename = $_FILES['imagen']['name'];
			$ext1 = pathinfo($filename, PATHINFO_EXTENSION);
			if(!in_array($ext1,$allowed) ) {
			   	$json["error"] = true;
				$json["msg"] = "Favor de seleccionar una imagen con la extensión correcta para el servicio.";
			}
		}

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			$values = array($_POST['servicio'],
							$_POST['servicio_en'],
							$_POST['texto1'],
							$_POST['texto1_en'],
							$_POST['texto2'],
							$_POST['texto2_en'],
							$_POST['texto3'],
							$_POST['texto3_en'],
							$_POST['texto4'],
							$_POST['texto4_en'],
							$_POST['texto5'],
							$_POST['texto5_en'],
							$_POST['indx'],
							$_POST['indx_en']);

			if(isset($_POST['id'])) { //UPDATE
				$sql = "UPDATE servicios SET servicio = ?,
								   	   		 servicio_en = ?,
								   	   		 texto1 = ?,
								   	   		 texto1_en = ?,
								   	   		 texto2 = ?,
								   	   		 texto2_en = ?,
								   	   		 texto3 = ?,
								   	   		 texto3_en = ?,
								   	   		 texto4 = ?,
								   	   		 texto4_en = ?,
								   	   		 texto5 = ?,
								   	   		 texto5_en = ?,
								   	   		 indx = ?,
								   	   		 indx_en = ?
						WHERE id = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO servicios (servicio,
								   	   		  servicio_en,
								   	   		  contenido,
								   	   		  contenido_en,
								   	   		  indx,
								   	   		  indx_en)
						VALUES( ?, ?, ?, ?, ?, ? )";
			}

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

				if(isset($_POST['id'])) {
					$pro_id = $_POST['id'];
				} else {
					$pro_id = $this->last_id();
				}

				if(isset($_FILES['imagen']['name']) && $_FILES['imagen']['name'] != "") {
					$filename = $_FILES['imagen']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$name = uniqid();
					$img_name = $ruta."../assets/images/servicios/".$name.".".$ext;

					//Redimensiona la imagen
                    list($orig_width, $orig_height) = getimagesize($_FILES[ 'imagen' ][ 'tmp_name' ]);

                    $width = $orig_width;
                    $height = $orig_height;

                    $max_height = 1280;
                    $max_width = 1280;

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
						$sql_img = "UPDATE servicios SET imagen = ?
								    WHERE id = ?";
						
						$values_img = array('servicios/'.$name.".".$ext, $pro_id);
						$consulta_img = $db->prepare($sql_img);
						try {
							$consulta_img->execute($values_img);

						} catch (PDOException $e) {
							$db->rollBack();
							die($e->getMessage().$sql_img);
						}
				}

				if(isset($_FILES['imagen1']['name']) && $_FILES['imagen1']['name'] != "") {
					$filename = $_FILES['imagen1']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$name = uniqid();
					$img_name = $ruta."../assets/images/servicios/".$name.".".$ext;

					//Redimensiona la imagen
                    list($orig_width, $orig_height) = getimagesize($_FILES[ 'imagen1' ][ 'tmp_name' ]);

                    $width = $orig_width;
                    $height = $orig_height;

                    $max_height = 1280;
                    $max_width = 1280;

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

                    $tipofoto =  $_FILES[ 'imagen1' ]['type'];
                    if ($tipofoto == 'image/jpg' || $tipofoto == 'image/jpeg') {
                        $image = imagecreatefromjpeg($_FILES[ 'imagen1' ][ 'tmp_name' ]);
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
                       	$image = imagecreatefrompng($_FILES[ 'imagen1' ][ 'tmp_name' ]);
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
						$sql_img = "UPDATE servicios SET img1 = ?
								    WHERE id = ?";
						
						$values_img = array('servicios/'.$name.".".$ext, $pro_id);
						$consulta_img = $db->prepare($sql_img);
						try {
							$consulta_img->execute($values_img);

						} catch (PDOException $e) {
							$db->rollBack();
							die($e->getMessage().$sql_img);
						}
				}

				if(isset($_FILES['imagen2']['name']) && $_FILES['imagen2']['name'] != "") {
					$filename = $_FILES['imagen2']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$name = uniqid();
					$img_name = $ruta."../assets/images/servicios/".$name.".".$ext;

					//Redimensiona la imagen
                    list($orig_width, $orig_height) = getimagesize($_FILES[ 'imagen2' ][ 'tmp_name' ]);

                    $width = $orig_width;
                    $height = $orig_height;

                    $max_height = 1280;
                    $max_width = 1280;

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

                    $tipofoto =  $_FILES[ 'imagen2' ]['type'];
                    if ($tipofoto == 'image/jpg' || $tipofoto == 'image/jpeg') {
                        $image = imagecreatefromjpeg($_FILES[ 'imagen2' ][ 'tmp_name' ]);
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
                       	$image = imagecreatefrompng($_FILES[ 'imagen2' ][ 'tmp_name' ]);
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
						$sql_img = "UPDATE servicios SET img2 = ?
								    WHERE id = ?";
						
						$values_img = array('servicios/'.$name.".".$ext, $pro_id);
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
			$json['msg'] = 'El servicio se guardó con éxito.';
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
					FROM banners
					ORDER BY orden ASC";

			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$marcas = $consulta->fetchAll(PDO::FETCH_ASSOC);

			if ($consulta->rowCount() > 0) {
				
				foreach ($marcas as $marca) {
					$json['marcas'] .= '<div id="marca_'.$marca['id'].'" class="col-sm-2 text-center">
											<img src="'.$ruta.'/assets/images/sliders/'.$marca['imagen'].'">
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
				
				$sql = "UPDATE banners SET orden = ?
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
			$json['msg'] = 'Error al escoger el orden de las marcas';
		}

		echo json_encode($json);

	}

	function showCategorias() {
		$json = array();
		$json["error"] = false;

		if(!isset($_SESSION)){
			@session_start();
		}

		$json["select"] = '<select id="categoria" name="categoria" class="form-control"><option></option>';
		$consulta = $this->_conexion->prepare("SELECT *
											   FROM blog_categorias");
		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($puntero as $row) {
				$json["select"] .= '<option value="'.$row['id'].'" '.(isset($_POST['id']) && $_POST['id'] == $row['id'] ? 'selected' : '').' >'.$row['categoria'].' ('.$row['categoria_en'].')</option>';
			}
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}
		$json["select"] .= '</select>';
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
		case "saveRecord2":
			$libs->saveRecord2();
			break;	
		case "getMarcas":
			$libs->getMarcas();
			break;	
		case "saveOrder":
			$libs->saveOrder();
			break;	
		case "showCategorias":
			$libs->showCategorias();
			break;			
	}
}

?>
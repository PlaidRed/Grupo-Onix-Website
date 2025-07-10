<?php
/*$url = explode("/aliados/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);*/

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

class Libs extends Common {
	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-27
	 *  
	 * Imprime la tabla de registros de perfil de usuarios EXCEPTUANDO 'daemon'
	 */
	function printTable() {

		/*
		 * Query principal
		 */
		$sqlQuery = "SELECT
						SISTEMA_USUARIO.SIU_ID,
						SISTEMA_USUARIO.SIU_NOMBRE_COMPLETO as nombre,
						SISTEMA_USUARIO.SIU_EMAIL,
						SISTEMA_USUARIO.SIU_TELEFONO,
						SISTEMA_USUARIO_PERFIL.SUP_NOMBRE
					FROM SISTEMA_USUARIO
					LEFT JOIN SISTEMA_USUARIO_PERFIL ON SISTEMA_USUARIO_PERFIL.SUP_ID = SISTEMA_USUARIO.SUP_ID
					WHERE SISTEMA_USUARIO_PERFIL.SUP_ID <> '1'";
		
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
			
			foreach ($puntero as $row) {
				$counter++;
				
				/*$aRow[] = $row["nombre"];
				$aRow[] = $row["SIU_EMAIL"];
				$aRow[] = $row["SIU_CELULAR"]." ".$row['SIU_TELEFONO'];
				$aRow[] = $row["SUP_NOMBRE"];*/


				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$row['SIU_ID'],
										"title"		=>	"Ver/Editar");
				$btn_editar = $this->printButton(2, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['SIU_ID'],
										"extras"	=>	"data-name='".$row["nombre"]."'");
				$btn_borrar = $this->printButton(2, "baja", $params_borrar);

				$aRow = array($row["nombre"], $row["SIU_EMAIL"], $row["SUP_NOMBRE"], $btn_editar.$btn_borrar);
				
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
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-27
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
				$consulta = $this->_conexion->prepare("DELETE FROM SISTEMA_USUARIO WHERE SIU_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El Usuario fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El Usuario elegido no pudo ser eliminado.";
				}
			}catch(PDOException $e){
				die($e->getMessage());
			}	
		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-01-13
	 * 
	 * @param '$id'		int. 	ID de perfil de usuario
	 * 
	 * Metodo que imprime la tabla de permisos de un perfil de usuario en base a su id
	 */
	function showRecord() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas.";
		$json['nombre'] = "";
		$json['apellido'] = "";
		$json['email'] = "";
		if(isset($_POST['id'])){
			try{
				$sql = "SELECT *
					FROM SISTEMA_USUARIO
					WHERE SIU_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$row = $result[0];

					$json['nombre_completo'] = $row['SIU_NOMBRE_COMPLETO'];
					$json['email'] = $row['SIU_EMAIL'];
					$json['profile'] = $row['SUP_ID'];
					$json['telefono'] = $row['SIU_TELEFONO'];
					$json['telefono_oficina'] = $row['SIU_TELEFONO_OFICINA'];
					$json['extencion'] = $row['SIU_EXT'];
					$json['puesto'] = $row['SIU_PUESTO'];
					$json['cedula'] = $row['SIU_CEDULA'];
					$json['cedula_vig'] = date("d/m/Y",strtotime($row['SIU_CEDULA_VIG']));
					$json['fecha_ingreso'] = date("d/m/Y",strtotime($row['SIU_FECHA_INGRESO']));
					$json['esquema'] = $row['SIU_ESQUEMA'];
						
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
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-01-02
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Metodo que regresa un select con las máquinas existentes
	 */
	function showProfiles() {
		$json = array();
		$json["error"] = false;
		$json["select"] = '<select id="perfil" name="perfil" class="form-control">';
		$consulta = $this->_conexion->prepare("SELECT SUP_ID, SUP_NOMBRE FROM SISTEMA_USUARIO_PERFIL WHERE SUP_ID <> '1'");
		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($puntero as $row) {
				$json["select"] .= '<option value="'.$row['SUP_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['SUP_ID'] ? 'selected' : '' : '').' >'.$row['SUP_NOMBRE'].'</option>';
			}
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}
		$json["select"] .= '</select>';
		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
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
		$error_msg = NULL;

		$obligatorios = array("nombre_completo",
							  "email",
							  "perfil",
							  "telefono");

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]){
				if( ($clave == 'pswd' || $clave == 'conf-pswd') && isset($_POST['id'])) {
					//NO HAY ERROR
				} else if($this->is_empty($valor) && in_array($clave, $obligatorios)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json["msg"] = "El campo de ".$clave." es obligatorio";
				}
			}
		}

		/*Valida que el correo no esté ingresado*/
		//Checks email
		$sql = "SELECT SIU_ID FROM SISTEMA_USUARIO WHERE SIU_EMAIL = ? ";
		$params = array($_POST['email']);
		$consulta = $this->_conexion->prepare($sql);
		$consulta->execute($params);
		$resultMail = $consulta->fetchAll(PDO::FETCH_ASSOC);

		if(!$json["error"] && $consulta->rowCount() > 0 && !isset($_POST['id'])){
			$json["error"] = true;
			$json["focus"] = "email";
			$error_msg = 1;
		}

		/*Valida contraseña*/
		//Se valida la estructura de la contraseña
		if (!$json["error"] && strlen($_POST['pswd']) > 6) {
			if(!$json["error"] && isset($_POST['pswd']) && (!$this->is_empty(trim($_POST['pswd'])) | !$this->is_empty(trim($_POST['conf-pswd'])) ) ){
				$foundNumber = false;
				$foundChars = false;
				$numbers = "1234567890";
				$chars = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
				$password = $_POST['pswd'];
			
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
					$json["focus"] = "pswd";
				}
				elseif(!$foundChars){
					$json["error"] = true;
					$json["msg"] = "La contraseña debe contener al menos una letra mayúscula (A-Z)";
					$json["focus"] = "pswd";
				}
				elseif($password != $_POST['conf-pswd']){
					$json["msg"] = "Las contraseñas no coinciden";
					$json["error"] = true;
					$json["focus"] = "pswd";
				}
			}
		} else {
				//$json["error"] = true;

				if($error_msg == 1){
					$json["error"] = true;
					$json["msg"] = "El correo electrónico ingresado ya ha sido registrado anteriormente.";
					$json["focus"] = "email";
				}else if (strlen($_POST['pswd'])<=6 && !isset($_POST['id'])){
					$json["error"] = true;
					$json["msg"] = "La contraseña debe contener al menos siete caracteres.";
					$json["focus"] = "pswd";
				}
		
		}

		if(!$json["error"]) {
			$_POST['cedula_vig'] = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['cedula_vig'])));
			$_POST['fecha_ingreso'] = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_ingreso'])));

			if(isset($_POST['id'])) { //UPDATE
				//Si tiene password
				if(!$this->is_empty(trim($_POST['pswd']))) {
					$sql_user = "UPDATE SISTEMA_USUARIO SET SIU_NOMBRE_COMPLETO = ?,
															SIU_EMAIL = ?,
															SIU_PASSWORD = ?,
															SUP_ID = ?,
															SIU_TELEFONO = ?,
															SIU_TELEFONO_OFICINA = ?,
															SIU_EXT = ?,
															SIU_PUESTO = ?,
															SIU_CEDULA = ?,
															SIU_CEDULA_VIG = ?,
															SIU_ESQUEMA = ?,
															SIU_FECHA_INGRESO = ?
														WHERE SIU_ID = ?";

					$pass_encr = $this->encrypt($_POST['pswd']);

					$values = array($_POST["nombre_completo"],
									$_POST["email"],
									$pass_encr,
									$_POST["perfil"],
									$_POST['telefono'],
									$_POST['telefono_oficina'],
									$_POST['extencion'],
									$_POST['puesto'],
									$_POST['cedula'],
									$_POST['cedula_vig'],
									$_POST['esquema'],
									$_POST['fecha_ingreso'],
									$_POST['id']);							
				} else {
					$sql_user = "UPDATE SISTEMA_USUARIO SET SIU_NOMBRE_COMPLETO = ?,
															SIU_EMAIL = ?,
															SUP_ID = ?,
															SIU_TELEFONO = ?,
															SIU_TELEFONO_OFICINA = ?,
															SIU_EXT = ?,
															SIU_PUESTO = ?,
															SIU_CEDULA = ?,
															SIU_CEDULA_VIG = ?,
															SIU_ESQUEMA = ?,
															SIU_FECHA_INGRESO = ?
														WHERE SIU_ID = ?";

					$values = array($_POST["nombre_completo"],
									$_POST["email"],
									$_POST["perfil"],
									$_POST['telefono'],
									$_POST['telefono_oficina'],
									$_POST['extencion'],
									$_POST['puesto'],
									$_POST['cedula'],
									$_POST['cedula_vig'],
									$_POST['esquema'],
									$_POST['fecha_ingreso'],
									$_POST['id']);
				}

			} else { //INSERCION
				$sql_user = "INSERT INTO SISTEMA_USUARIO (SIU_NOMBRE_COMPLETO,
													SIU_EMAIL,
													SIU_PASSWORD,
													SUP_ID,
													SIU_TELEFONO,
													SIU_TELEFONO_OFICINA,
													SIU_EXT,
													SIU_PUESTO,
													SIU_CEDULA,
													SIU_CEDULA_VIG,
													SIU_ESQUEMA,
													SIU_FECHA_INGRESO) 
							 VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";

				$pass_encr = $this->encrypt($_POST['pswd']);

				$values = array($_POST["nombre_completo"],
								$_POST["email"],
								$pass_encr,
								$_POST["perfil"],
								$_POST['telefono'],
								$_POST['telefono_oficina'],
								$_POST['extencion'],
								$_POST['puesto'],
								$_POST['cedula'],
								$_POST['cedula_vig'],
								$_POST['esquema'],
								$_POST['fecha_ingreso']);
			}

			$consulta = $this->_conexion->prepare($sql_user);

			try {
				$consulta->execute($values);
				$json["valid"] = true;

				//Manda mail si se registra
				if(!isset($_POST['id'])) {
					$json["msg"] = "El Usuario registrado con éxito.";
				} else {
					$json["msg"] = "Los cambios del Usuario han sido realizados exitosamente.";
				}

			} catch(PDOException $e) {
				$json["error"] = true;
				$json["msg"] = $e->getMessage();
			}	
		}

		echo json_encode($json);
	}

	function countPassword() {
		$json = array();
		$json['num'] = 0;

		$sqlQuery = "SELECT
						SISTEMA_USUARIO.SIU_ID,
						SISTEMA_USUARIO.SIU_EMAIL,
						SISTEMA_USUARIO_PERFIL.SUP_NOMBRE
					FROM SISTEMA_USUARIO
					LEFT JOIN SISTEMA_USUARIO_PERFIL ON SISTEMA_USUARIO_PERFIL.SUP_ID = SISTEMA_USUARIO.SUP_ID
					WHERE SISTEMA_USUARIO_PERFIL.SUP_ID <> '1'
						AND SISTEMA_USUARIO.SIU_RECUPERACION = '1'";
		
		//Se prepara la consulta de extración de datos
		$sqlFinalCounter = $this->_conexion->prepare($sqlQuery);

		//Se ejecuta la consulta
		$sqlFinalCounter->execute();
		$json['num'] = $sqlFinalCounter->rowCount();

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
		case "printTablePass":
			$libs->printTablePass();
			break;	
		case "deleteRecord":
			$libs->deleteRecord();
			break;
		case "showRecord":
			$libs->showRecord();
			break;
		case "showProfiles":
			$libs->showProfiles();
			break;	
		case "saveRecord":
			$libs->saveRecord();
			break;
		case "newPassword":
			$libs->newPassword();
			break;
		case "countPassword":		
			$libs->countPassword();
			break;
		case "noticiaVista":		
			$libs->noticiaVista();
			break;		
	}
}

?>
<?php
require_once("Core.php");

class Login extends Core
{
	/*
	* @version: 0.1 2013-12-26
	* log_in: relaciona un correo electrónico a un password
	* returns: un array en JSON con un valor auth verdadero o falso, msg de éxito o error y credenciales solicitadas
	* Si se da true a $log guarda el usuarioNombre en SESSION
	*/
	public function log_in($log = false) {
		$json = array();
		$json['msg'] = "";
		$json['auth'] = false;

		$credentials = array('SIU_NOMBRE_COMPLETO','SIU_ID', 'SUP_ID');

		$cred = isset($_POST['credentials'])?$_POST['credentials']:array();
		$credentials = array_merge($credentials, $cred);
		//array_unique($credentials);

		if (!isset($_POST['email']) || !$this->isEmail($_POST['email'])) {
			$json['msg'] = "Correo electrónico y/o contraseña no válidos.";
		}
		if (!isset($_POST['password']) || empty($_POST['password'])) {
			$json['msg'] = "Correo electrónico y/o contraseña no válidos.";
		}

		if (empty($json['msg'])) {
			$email = $_POST['email'];
			$password = $_POST['password'];
			$result = $this->authUser(array('table'=>'SISTEMA_USUARIO','SIU_EMAIL'=>$email,'SIU_PASSWORD'=>$password),$credentials);
			$json['auth'] = $result['userAuth'];
			$json['credentials'] = isset($result['credentials'])?$result['credentials']:array();
			$json['msg'] = $json['auth']?"Conectado exitosamente.":"Correo electrónico y/o contraseña no válidos.";
			if ($log && $json['auth']) {
				session_start();
				$_SESSION["onx"]["username"] = $json['credentials']['SIU_NOMBRE_COMPLETO'];
				$_SESSION["onx"]["userid"] = $json['credentials']['SIU_ID'];
				$_SESSION["onx"]["userprofile"] = $json['credentials']['SUP_ID'];
				
				$db = $this->_conexion;

				$sql = "UPDATE SISTEMA_USUARIO SET SIU_ACCESO = SIU_ACCESO+1 WHERE SIU_ID = ?";
				$values[] = $json['credentials']['SIU_ID'];	
				$consulta = $db->prepare($sql);
				$consulta->execute($values);
			}
		}
		
		echo json_encode($json);
	}

	/*
	* @author: Alfonso Gómez
	* @version: 0.1 2013-12-26
	* resetPassword: relaciona un correo electrónico a una cuenta y asigna 1 a recuperar la contraseña
	* returns: un array en JSON con un msg de éxito o error
	*/
	public function resetPassword() {
		$json = array();
		$json['msg'] = "";

		if (!isset($_POST['email']) || !$this->isEmail($_POST['email'])) {
			$json['msg'] = "El e-mail proporcionado no es válido.";
		}

		if (empty($json['msg'])) {
			if ($this->isInDb('SISTEMA_USUARIO','SIU_EMAIL',$_POST['email'])) {
				try{
				
					//Enviar correo.
					require_once("Mail.php");
					
					$new_pass = $this->getRandomWord();

					$body = "La nueva contraseña para ingresar a Grupo Onix es: ".$new_pass.". Esta contraseña se genera automáticamente de forma temporal, para cambiar su contraseña accese a su perfil en la plataforma Onix.";


					//Correo para el usuario
					$Mail = new Mail("Cambio de contraseña");
					$Mail->addMail($_POST['email']);
					//$Mail->addMail("ca.castilloe@gmail.com");
					$cuerpo_mensaje = "<tr><td>
								<br><br>
								".$body."
							</td></tr>";
					$Mail->content($cuerpo_mensaje);


					//Registra la nueva contraseña en la DB
					try{
						$sql = "SELECT 
								SIU_ID
							FROM SISTEMA_USUARIO
							WHERE SIU_EMAIL = :valor";

						$consulta = $this->_conexion->prepare($sql);
						$consulta->bindParam(':valor', $_POST['email']);
						$consulta->execute();
						$row = $consulta->fetch(PDO::FETCH_ASSOC);

						$sql_updt = "UPDATE SISTEMA_USUARIO SET SIU_PASSWORD = ?
									 WHERE SIU_ID = ?";	
						$pass_encr = $this->encrypt($new_pass);		

						$values = array($pass_encr,
										$row['SIU_ID']);	

						$consulta = $this->_conexion->prepare($sql_updt);

						try {
							$consulta->execute($values);

							//Envía el correo
							$Mail->send();

							if ($consulta->rowCount()) {
								$json['msg'] = "Se ha solicitado el cambio de contraseña. En seguida recibirá un correo electrónico con su nueva contraseña.";
								$json['reset'] = true;

							}else {
								$json['msg'] = "Ya ha solicitado el cambio de contraseña. Espere a que su administrador se la proporcione.";
								$json['reset'] = false;
							}


						} catch(PDOException $e) {
							die($e->getMessage());
						}												

					}catch(PDOException $e){
						die($e->getMessage());
					}

				}catch(PDOException $e) {
					die($e->getMessage());
				}
			}else {
				$json['msg'] = "El e-mail no está registrado.";
				$json['reset'] = false;
			}


		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2018-10-26
	 * logout: Destruye la sesión actual y redirecciona al index (pantalla de Iniciar Sesión)
	 */
	public function logout() {
		session_start();
		session_destroy();
		header('Location: ../../');
	}

	function getRandomWord($len = 10) {
	    $word = array_merge(range('a', 'z'), range('A', 'Z'));
	    shuffle($word);
	    return substr(implode($word), 0, $len);
	}
}

if (isset($_GET['accion'])) {
	$login = new Login();
	switch ($_GET['accion']) {
		case 'login':
			$login->log_in(true);
			break;
		case 'credentials': 
			$login->log_in();
			break;
		case 'forgot':
			$login->resetPassword();
			break;
		case 'logout':
			$login->logout();
			break;
		default:
			die("Acción no definida");
			break;
	}

}
?>
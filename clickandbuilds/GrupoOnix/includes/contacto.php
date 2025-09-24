<?php
	/**
	* 
	*/
	class Mailer {

		/*public $smtp_url = 'mail.technoweb.mx';
	    public $puerto = 587;
	    public $username = 'sender@technoweb.mx';
	    public $password = 'Huo0lpaw';*/

	    public $smtp_url = 'smtp.ionos.mx';
	    public $puerto = 587;
	    public $username = 'no-reply@segurosonix.com';
	    public $password = 'N0R3ply_0N1x';

		function isEmail($email) {
			return (!preg_match("/^[a-z]([\w\.]*)@[a-z]([\w\.-]*)\.[a-z]{2,3}$/", $email)) ? false : true;
		}

		function sendMail() {
			$json = array();
			$json['error'] = false;
			$json['msg'] = "Tu mensaje ha sido enviado. Nos comunicaremos a la brevedad.";

			if (!isset($_POST['nombre']) || empty($_POST['nombre'])) {
				$json['error'] = true;
				$json['msg'] = "El campo de Nombre es obligatorio.";
				$json['focus'] = "nombre";
			} else if (!isset($_POST['email']) || empty($_POST['email']) || !$this->isEmail($_POST['email'])) {
				$json['error'] = true;
				$json['msg'] = "Favor de ingresar un e-mail válido.";
				$json['focus'] = "email";
			} else if (!isset($_POST['mensaje']) || empty($_POST['mensaje'])) {
				$json['error'] = true;
				$json['msg'] = "El campo de Mensaje es obligatorio.";
				$json['focus'] = "mensaje";
			}
			
			if ($json['error'] == false) {
				
				require_once('class.phpmailer.php'); 
				require_once('class.smtp.php'); 
				$mail = new PHPMailer(true);
				try {
					$mail->CharSet = 'UTF-8';
					$mail->IsSMTP();
					$mail->Host       = $this->smtp_url;
					$mail->SMTPAuth   = true;
					$mail->Port       = $this->puerto;
					$mail->XMailer    = ' ';
					$mail->Username   = $this->username;
					$mail->Password   = $this->password;
					//$mail->SMTPSecure = 'ssl';

					// send from
					$mail->SetFrom('no-reply@segurosonix.com','Seguros Onix Web'); // <-- le va a llegar desde esta persona

					// send reservation email to them and to us
					$mail->AddAddress('direccion@segurosonix.com', 'Direccion Onix');
					//$mail->AddAddress('ca.castilloe@gmail.com', 'Direccion Onix');

					//$mail->AddBCC('ca.castilloe@gmail.com', 'Cynthia Castillo');

					$mail->Subject = "Contacto | Seguros Onix"; 
					
					$contenido = '<div style="width: 600px; text-align:left;">
									<img src="https://segurosonix.com/img/logo_mail.png">
								  </div>
								  <p>Se recibió la siguiente información a través de la página web</p>
								  <p><strong>Nombre:</strong> '.$_POST['nombre'].'</p>
								  <p><strong>E-mail:</strong> '.$_POST['email'].'</p>
								  <p><strong>Mensaje:</strong> '.$_POST['mensaje'].'</p>
								  <div style="margin-top:40px; text-align:center; width: 600px;">
								  	<small style="color:#005EF4;">
								  		Todos los derechos reservados <a style="font-weight: bold;" href="https://segurosonix.com/" target="_blank">Seguros Onix</a>
								  	</small>
								  </div>';

					$mail->MsgHTML($contenido); //
					$mail->Send();
				} catch (phpmailerException $e) {
					die($e->errorMessage());
				}




			}

			echo json_encode($json);

		}
	}

	$mailer = new Mailer();
	$mailer->sendMail();
		

?>
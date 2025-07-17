<?php
/**
 * Clase que extiende a PHPMailer
 * 
 * @author: Héctor Iván Perales Jasso <hector.perales@futurite.com>
 * @version: 0.1 2013-06-17
 */

require_once("phpmailer/class.phpmailer.php");

class Mail extends PHPMailer
{
	/**
	 * Se Inicializa la clase Mail. Recibe El Asunto del correo
	 */
	function Mail($subject = ""){
		$this->subject = $subject;
		$this->hasAttach = false;
		$this->recipients = array();
	}
	
	/*
	 * Función que genera el encabezado del correo.
	 */
	function Header(){
		$result  = '<div style="width: 600px; text-align:left;">
						<img src="https://segurosonix.com/nuevoonix//img/logo_mail.png">
					  </div>';
		return $result;
	}
	
	/*
	 * Función que genera el pie del correo.
	 */
	function Footer(){
		$result  = ('<div style="margin-top:40px; text-align:center; width: 600px;">
					  	<small style="color:#005EF4;">
					  		Todos los derechos reservados <a style="font-weight: bold;" href="https://segurosonix.com/" target="_blank">Seguros Onix</a>
					  	</small>
					  </div>');
		
		return $result;
	}
	
	/*
	 * Función que agrega un destinario.
	 */
	function addMail($email, $name = ""){
		$this->recipients[$email] = $name;
	}
	
	/*
	 * Función que agrega un destinario con Copia.
	 */
	function addCC($email, $name = ""){
		$this->recipientsCC[$email] = $name;
	}
	
	/*
	 * Función que agrega un destinario con Copia.
	 */
	function addBCC($email, $name = ""){
		$this->recipientsBCC[$email] = $name;
	}
	
	/*
	 * Función que recibe el cuerpo del mensaje. Puede ser en HTML.
	 */
	function content($content = ""){
		$this->Body = $content;
	}
	
	/*
	 * Función que agrega un archivo adjunto
	 * @$path: Ruta al archivo.
	 * @name: Nombre del archivo que aparecerá en el correo.
	 */
	function addAttach($path, $name){
		$this->hasAttach = true;
		$this->files[$name] = $path;
	}
	
	function addAt($att) {
		$this->hasAttach = true;
		$this->file = $att;
	}
	/*
	 * Función que envía el mensaje
	 */
	function send(){
		$mail = new PHPMailer();
		$mail->CharSet = "utf-8";
		$mail->From = "no-reply@segurosonix.com";
		$mail->FromName = "Seguros Onix Web";
		
		$mail->Host = "smtp.ionos.mx";
		$mail->SMTPAuth = true;
		$mail->Username = "no-reply@segurosonix.com";
		$mail->Password = "N0R3ply_0N1x";
		$mail->Port = 587;
		
		$mail->Mailer = "smtp";
		//$mail->isSendMail();
		$mail->IsHTML(true);
		
		$mail->Subject = $this->subject;
		$mail->Body = $this->Header() . $this->Body . $this->Footer();
		
		//Agrega los destinatarios.
		foreach($this->recipients as $email => $name){
			$mail->AddAddress($email, $name);
		}
		
		//Valida si se encuentra la matriz con los destinatarios Con Copia.
		if(isset($this->recipientsCC) && is_array($this->recipientsCC) && count($this->recipientsCC) > 0){
			//Se agregan los destinarios con copia.
			foreach($this->recipientsCC as $email => $name){
				$mail->AddCC($email, $name);
			}
		}
		
		//Valida si se encuentra la matriz con los destinatarios Con Copia Oculta.
		if(isset($this->recipientsBCC) && is_array($this->recipientsBCC) && count($this->recipientsBCC) > 0){
			//Se agregan los destinatarios con copia oculta.
			foreach($this->recipientsBCC as $email => $name){
				$mail->AddBCC($email, $name);
			}
		}
		
		//Valida si se agregaron archivos al correo.
		if($this->hasAttach){
			//Se adjuntan los archivos al correo.
			/*foreach($this->files as $filename => $path){
				$mail->AddAttachment($path, $filename, 'base64');
			}*/
			$mail->AddAttachment($this->file);
		}
		
		if(!$mail->Send()){
			return $mail->ErrorInfo;
		}
		else{
			$mail->ClearAllRecipients();
			$mail->ClearAttachments();
			return "success";
		}
	}
}
?>
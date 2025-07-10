<?php
require_once("Core.php");

class Notices extends Core
{
	public function noticiaVista() {
		if(!isset($_SESSION)){
			@session_start();
		}
		$json = array();
		$json['msg'] = "no hizo null";
		try {
			$query = "SELECT * FROM SISTEMA_NOTIFICACIONES WHERE SIN_ESTADO = 0 AND SIN_LIGA = :valor";//query para user
			$consulta = $this->_conexion->prepare($query);
			$consulta->bindParam(":valor",$_POST['liga']);
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($puntero as $row) {
				$usuarios = $row['SIN_USUARIOS'];
				$usuarios = trim($usuarios, '|');
				$usuarios = explode("|", $usuarios);

				if(($key = array_search($_SESSION["onx"]["userid"], $usuarios)) !== false) {
				    unset($usuarios[$key]);
				}

				$usuarios_str = implode("|", $usuarios);
				$estado = 1;
				if(strlen($usuarios_str) > 0) {
					$estado = 0;
				}

				$usuarios_str = '|'.$usuarios_str.'|';


				$query = "UPDATE SISTEMA_NOTIFICACIONES SET SIN_USUARIOS = :usuarios, 
															SIN_ESTADO = :estado
						  WHERE SIN_ID = :valor";
				$consulta = $this->_conexion->prepare($query);
				$consulta->bindParam(":usuarios",$usuarios_str);
				$consulta->bindParam(":estado",$estado);
				$consulta->bindParam(":valor",$row['SIN_ID']);
				$consulta->execute();
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
		}
		echo json_encode($json);
	}
	/*
	public function noticiaVista() {
		$json = array();
		$json['msg'] = "";
		try {
			$query = "UPDATE SISTEMA_MENSAJE SET SIM_ESTADO = 1 WHERE SIM_TIPO = :valor";
			$consulta = $this->_conexion->prepare($query);
				$consulta->bindParam(":valor",$_POST['id_t']);
				$consulta->execute();
				$json['msg'] = "Se realizo";
				
		} catch(PDOException $e) {
			die($e->getMessage());
		}		
		echo json_encode($json);
	}*/

	public function notificaciones() {
		global $ruta;
		global $baseUrl;
		$numtotal = 0;
		$json = array();
		$json['notificaciones'] = '';

		if(!isset($_SESSION)){
			@session_start();
		}

		try {

			$query = "SELECT * FROM SISTEMA_NOTIFICACIONES 
					  WHERE SIN_ESTADO = 0
					  AND SIN_USUARIOS LIKE ?
					  ORDER BY SIN_DATE DESC";
			$value = array('%|'.$_SESSION["onx"]["userid"].'|%');
			$consulta = $this->_conexion->prepare($query);
			$consulta->execute($value);
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$notificaciones = "";

			foreach ($puntero as $row) {
				$fecha = date("d/m/Y H:i",strtotime($row["SIN_DATE"]));

				$url = ($row['SIN_LIGA_EXTERNA'] == 1 ? $row['SIN_LIGA'] : $ruta.$row['SIN_LIGA']);

				$notificaciones .= '<a class="visto" href="'.$url.'" data-liga="'.$row['SIN_LIGA'].'" data-ruta="'.$ruta.'" data-ext="'.($row['SIN_LIGA_EXTERNA'] == 1 ? '1' : '0').'" '.($row['SIN_LIGA_EXTERNA'] == 1 ? 'target=_blank' : '').'>
					                        <div class="media">
					                          <div class="media-left align-self-center"><i class="'.$row['SIN_ICONO'].' font-medium-4 mt-2 '.$row['SIN_COLOR'].'"></i></div>
					                          <div class="media-body">
					                            <h6 class="media-heading '.$row['SIN_COLOR'].'">'.$row['SIN_MENSAJE'].'</h6>
					                            <small><time class="media-meta text-muted">'.$fecha.'</time></small>
					                          </div>
					                        </div>
					                    </a>';
				$numtotal++;
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
		}


		$json['notificaciones'] = '<li class="dropdown dropdown-notification nav-item">
				<a class="nav-link nav-link-label" href="#" data-toggle="dropdown">
				<i class="ficon ft-bell '.($numtotal > 0 ? 'bell-shake' : '').'" id="notification-navbar-link"></i>
				'.($numtotal > 0 ? '<span class="badge badge-pill badge-sm badge-danger badge-default badge-up badge-glow">'.$numtotal.'</span>' : '').'
				</a>
                <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                  <div class="arrow_box_right">
                    <li class="dropdown-menu-header">
                      <h6 class="dropdown-header m-0"><span class="grey darken-2">Notificaciones</span></h6>
                    </li>
                    <li class="scrollable-container media-list w-100">
                    	'.($numtotal > 0 ? $notificaciones : '<a href="javascript:void(0)">
										                        <div class="media">
										                          <div class="media-left align-self-center"><i class="ft-x info font-medium-4 mt-2"></i></div>
										                          <div class="media-body">
										                            <h6 class="media-heading info">No hay Notificaciones</h6>
										                          </div>
										                        </div></a>').'
                    </li>
                  </div>
                </ul>
              </li>';
        echo json_encode($json);

	}
}
if (isset($_GET['accion'])) {
	$notices = new Notices();
	switch ($_GET['accion']) {
		case 'vista':
			$notices->noticiaVista();
			break;
		case 'notificaciones':
			$notices->notificaciones();
			break;
		default:	
			die("Acción no definida");
			break;
	}
}
?>
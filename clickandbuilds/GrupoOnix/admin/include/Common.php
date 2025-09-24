<?php
//Se definen las constante del administrador
define("TITLE_MAIN", "Administrador Ónix");
include_once($ruta."include/Core.php");

//$baseUrl = $_SERVER["SERVER_NAME"]."/demos/onix/admin/";
$baseUrl = $_SERVER["SERVER_NAME"]."/admin/";
date_default_timezone_set('America/Mexico_City');
class Common extends Core {

	/*
	 * @version: 0.1 2013-04-01
	 */
	public function printUserName() {
		//Se revisa el tipo de usuario.
		if(!isset($_SESSION)){
			@session_start();
		}
		
		//Se prepara la consulta
		return $_SESSION["onx"]["username"];
		//return "Cynthia Castillo";
	}

	/*
	 * @author: Cynthia Castillo
	 * @version: 0.1 2013-12-23
	 */
	public function printNotifications() {
		global $ruta;
		global $baseUrl;
		$numtotal = 0;

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


		return '<li class="dropdown dropdown-notification nav-item">
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


		/*echo '<li class="dropdown dropdown-notification nav-item">
				<a class="nav-link nav-link-label" href="#" data-toggle="dropdown">
					<i class="ficon ft-bell bell-shake" id="notification-navbar-link"></i>
					'.($numtotal > 0 ? '<span class="badge badge-pill badge-sm badge-danger badge-default badge-up badge-glow">'.$numtotal.'</span>' : '').'	
				</a>
                <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                  <div class="arrow_box_right">
                    <li class="dropdown-menu-header">
                      <h6 class="dropdown-header m-0"><span class="grey darken-2">'.$notif.'</span></h6>
                    </li>
                    <li class="scrollable-container media-list w-100">
                    '.($numtotal > 0 ? $notificaciones : '<a href="javascript:void(0)">
									                        <div class="media">
									                          <div class="media-left align-self-center"><i class="ft-share info font-medium-4 mt-2"></i></div>
									                          <div class="media-body">
									                            <h6 class="media-heading info">No hay Notificaciones</h6>
									                          </div>
									                        </div>
									                       </a>').'
                    </li>
                  </div>
                </ul>
              </li>';*/
		
	}

	public function printBreadcrumbs($modulo = 0) {
		global $ruta;
		$breadcrumbs = array();
		
		try {
			$query = "SELECT * FROM SISTEMA_MODULOS WHERE SIM_ID = :valor";
			$consulta = $this->_conexion->prepare($query);
			$url_principal = "";
			$i = 0;
			while ($modulo > 0) {
				$consulta->bindParam(":valor",$modulo);
				$consulta->execute();
				$result = $consulta->fetch();
				$modulo = $result['SIM_NIVEL'];
				if($i == 0) {
					$url_principal = $ruta.$result['SIM_URL'];
				}
				$breadcrumbs[] = array('href'=> $url_principal,
								'titulo'=>$result['SIM_NOMBRE'],
								'icon'=>$result['SIM_IMAGEN'],
								'id'=>$result['SIM_ID']);

				$i++;

			}
			$breadcrumbs[] = array('href'=>$ruta.'home.php',
								'titulo'=>'Home',
								'icon'=>'ft-home',
								'id'=>0);	
		} catch(PDOException $e) {
			die($e->getMessage());
		}
		//sort($breadcrumbs);
		$breadcrumbs = $this->array_sort($breadcrumbs, 'id', SORT_ASC);

		echo '<div class="breadcrumbs-top float-md-right">
          <div class="breadcrumb-wrapper mr-1">
            <ol class="breadcrumb">';

		foreach ($breadcrumbs as $breadcrumb) {
			echo '<li class="breadcrumb-item">
				<i class="'.$breadcrumb['icon'].'"></i>
				<a href="'.$breadcrumb['href'].'">'.$breadcrumb['titulo'].'</a>
             </li>';
		}
		echo '</ol>
          </div>
        </div>';
	}

	public function array_sort($array, $on, $order=SORT_ASC) {
	    $new_array = array();
	    $sortable_array = array();

	    if (count($array) > 0) {
	        foreach ($array as $k => $v) {
	            if (is_array($v)) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $on) {
	                        $sortable_array[$k] = $v2;
	                    }
	                }
	            } else {
	                $sortable_array[$k] = $v;
	            }
	        }

	        switch ($order) {
	            case SORT_ASC:
	                asort($sortable_array);
	            break;
	            case SORT_DESC:
	                arsort($sortable_array);
	            break;
	        }

	        foreach ($sortable_array as $k => $v) {
	            $new_array[$k] = $array[$k];
	        }
	    }

	    return $new_array;
	}

	public function printHeader() {
		global $ruta;
		global $baseUrl;
		$name = $this->printUserName();
		$notificaciones = $this->printNotifications();
		echo '		<div class="navbar-header">
				        <ul class="nav navbar-nav flex-row">
				          <li class="nav-item mobile-menu d-md-none mr-auto">
				            <a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#">
				              <i class="ft-menu font-large-1"></i>
				            </a>
				          </li>
				          <li class="nav-item">
				            <a class="navbar-brand" href="http://'.$baseUrl.'">
				              <img class="brand-logo" alt="creaative admin logo" src="'.$ruta.'app-assets/images/logo/logo.png">
				            </a>
				          </li>
				          <li class="nav-item d-md-none">
				            <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile">
				              <i class="la la-ellipsis-v"></i>
				            </a>
				          </li>
				        </ul>
				      </div>
				      <div class="navbar-wrapper">
				        <div class="navbar-container content">
				          <div class="collapse navbar-collapse" id="navbar-mobile">
				            <ul class="nav navbar-nav mr-auto float-left">
				              <li class="nav-item d-none d-md-block"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu"></i></a></li>
				              <li class="nav-item d-none d-md-block"><a class="nav-link nav-link-expand" href="#"><i class="ficon ft-maximize"></i></a></li>
				            </ul>
				            <ul class="nav navbar-nav float-right">  
				            	<li class="nav-item menu-home">
				            		<a class="nav-link nav-link-label" href="http://'.$baseUrl.'"><i class="ft-home"></i></a>
				            	</li>      
				              <span id="cont-notificaciones">'.$notificaciones.'</span>
				              <li class="dropdown dropdown-user nav-item">
				                <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">             
				                  <span class="avatar avatar-online"><img src="'.$ruta.'app-assets/images/portrait/small/avatar.png" alt="avatar"></span>
				                </a>
				                <div class="dropdown-menu dropdown-menu-right">
				                  <div class="arrow_box_right">
				                    <a class="dropdown-item" href="'.$ruta.'perfil">
				                      <span class="avatar avatar-online">
				                        <img src="'.$ruta.'app-assets/images/portrait/small/avatar.png" alt="avatar">
				                        <span class="user-name text-bold-700 ml-1">'.$name.'</span>
				                      </span>
				                    </a>
				                    <div class="dropdown-divider"></div>
				                    <a class="dropdown-item" href="'.$ruta.'include/Login.php?accion=logout"><i class="ft-power"></i>Log out</a>
				                  </div>
				                </div>
				              </li>
				            </ul>
				          </div>
				        </div>
				      </div>';
	}

	public function printFooter() {
    echo '<footer class="footer footer-static footer-light navbar-shadow">
          <div class="clearfix blue-grey lighten-2 text-sm-center mb-0 px-2">
            <ul class="list-inline float-md-right d-block d-md-inline-blockd-none d-lg-block mb-0">
              <li class="list-inline-item"><a class="my-1" href="mailto:web@tna.mx"> Soporte</a></li>
            </ul>
          </div>
        </footer>';

	}

	public function printLeftHeader() {
		global $ruta;
		echo '<div class="navbar-brand">
					<!-- COMPANY LOGO -->
					<a href="'.$ruta.'home.php">
						<img src="'.$ruta.'img/logo/logo.png" alt="Logo" class="img-responsive">
					</a>
					<!-- /COMPANY LOGO -->
					<!-- SIDEBAR COLLAPSE -->
					<div id="sidebar-collapse" class="sidebar-collapse btn">
						<i class="fa fa-bars" 
							data-icon1="fa fa-bars" 
							data-icon2="fa fa-bars" ></i>
					</div>
					<!-- /SIDEBAR COLLAPSE -->
				</div>';
	}
	/*
	 * Print Menu
	 * @version: 0.1 2013-12-27
	 */
	public function printMenu($actual = ""){
		global $ruta;
		//Se revisa el tipo de usuario.
		if(!isset($_SESSION)){
			@session_start();
		}
		$query = "SELECT * FROM SISTEMA_MODULOS ORDER BY SIM_ORDEN ASC";
		$consulta = $this->_conexion->prepare($query);
		try {
			$consulta->execute();
			$modulos = $consulta->fetchAll();
		} catch(PDOException $e) {
			die($e->getMessage());
		}
		
		foreach ($modulos as $modulo) {
			$tree[$modulo['SIM_ID']] = $modulo;
			$tree[$modulo['SIM_ID']]['children'] = array();
		}

		foreach ($tree as $key => &$leaf) {
			$parent = isset($tree[$key]['SIM_NIVEL'])?$tree[$key]['SIM_NIVEL']:array();
			if(isset($leaf['SIM_NIVEL'])) {
				$tree[$parent]['children'][] = &$tree[$key];
			}
		}

		$permisos = self::getPermissions();
		
		echo self::printChildren($tree[0]['children'],$permisos,$actual);

	}

	function printChildren($children, $permisos, $actual = "",$nivel = 0) {
		global $baseUrl;
		//die(print_r($children));
		$lisub = ($nivel == 0 ? 'nav-item' : '');
		$asub = ($nivel == 0 ? 'nav-link' : 'dropdown-item');
		$menu = '<ul '.($nivel>0?'class="dropdown-menu"':'class="nav navbar-nav" id="main-menu-navigation" data-menu="menu-navigation"').'>';
		$menu .= ($nivel > 0 ? '<div class="arrow_box">' : '');
		foreach ($children as $child) {
			//print_r($child);
			if(array_key_exists($child['SIM_ID'], $permisos) && is_array($child['children']) && count($child['children'])) {
				$menu.= '<li class="dropdown '.$lisub.($nivel > 0 ? 'dropdown-submenu' : '').'" data-menu="'.($nivel > 0 ? 'dropdown-submenu' : 'dropdown').'">
							<a href="#" class="'.($nivel == 0 ? 'nav-link' : 'dropdown-item') .' dropdown-toggle" data-toggle="dropdown">
								<i class="'.(empty($child['SIM_IMAGEN'])?"":$child['SIM_IMAGEN']).'"></i><span>'.$child['SIM_NOMBRE'].'</span>
							</a>';
				$menu.= self::printChildren($child['children'],$permisos,$actual,($nivel+1));
				$menu.='</li>';
			}else if(array_key_exists($child['SIM_ID'], $permisos)) {
				$url = ($child['SIM_EXT'] == 0 ? 'http://'.$baseUrl.$child['SIM_URL'] : $child['SIM_URL']);

				$menu.= '<li class="'.($actual == $child['SIM_ID']? 'active':'').' '.$lisub.'">
							<a href="'.$url.'" class="'.($nivel == 0 ? 'nav-link' : 'dropdown-item').'" '.($child['SIM_EXT'] == 1 ? 'target="_blank"' : '').'>
								<i class="'.(empty($child['SIM_IMAGEN'])?"":$child['SIM_IMAGEN']).'"></i><span>'.$child['SIM_NOMBRE'].'</span>
							</a>
						</li>';
			}
		}
		$menu .= ($nivel > 0 ? '</div>' : '');
		$menu.= '</ul>';
		return $menu;
	}

	//Se crea el metodo que indica si una cadena esta vacia o no
	public function is_empty($string){
		//Se limpian los espacios en blanco de la cadena
		$string=trim($string);
		//Se declara la variable de control
		$is_empty = true;
		//Se verifica si la cadena trae contenido
		if(strlen($string)!=0) $is_empty=false;
		//Se devuelve el contenido de la variable de control
		return $is_empty;
	}

}

$common = new Common();
?>
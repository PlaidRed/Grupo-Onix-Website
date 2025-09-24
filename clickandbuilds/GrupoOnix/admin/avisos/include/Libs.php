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
$module = 3;

class Libs extends Common {

	/*
	 * 
	 * Función encargada de imprimir todas las carpetas en Formatos de Drive
	 *
	 */
	function printRoot() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['arbol'] = '';

		$json['arbol'] = $this->printFolder('0B5cPSpK0D9wITTU2eVNlcy05QnM');

		echo json_encode($json);
	}

	/*
	 * 
	 * Recibe el ID de la carpeta y regresa todos los archivos de la carpeta
	 *
	 */
	function printFolder($fileId) {
		global $ruta;
		$arbol = '';

		require_once $ruta.'include/vendor/autoload.php';
        $client = new Google_Client();
        $client->setApplicationName("DriveOnix");
        $client->setDeveloperKey("AIzaSyAoH850PxQu7IVJwG2q8zAgcYt1M7_So6I");
        $client->setAuthConfig($ruta.'include/client_secret_500109226259-7gelgua06itghcu74stq6pe7g7ceq27u.apps.googleusercontent.com.json');

        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$ruta.'include/grupo-onix-8332a1056aab.json');
        $client->useApplicationDefaultCredentials();
        $client->addScope(Google_Service_Drive::DRIVE);
        $service = new Google_Service_Drive($client);
        
        //$file = $service->files->get('0B5cPSpK0D9wIMGJ2V3NCS002bTQ');
        //print_r($file);

        $optParams = array(
          'pageSize' => 25,
          'fields' => 'nextPageToken, files(id, name, mimeType)',
          'q' => 'parents="'.$fileId.'"',
          'orderBy' => 'folder,name'
        );
        $results = $service->files->listFiles($optParams);

        if (count($results->getFiles())) {
        	foreach ($results->getFiles() as $file) {
        		$icon_carpeta = '<i class="fas fa-folder text-warning"></i>';
	        	$class = '';
	        	//Revisamos si es Carpeta o Documento
	        	if($file->getMimeType() == 'application/vnd.google-apps.folder') {
	        		//Revisamos si tiene carpetas o documentos adentro
	        		/*$optParamsChild = array(
			          'pageSize' => 1,
			          'q' => 'parents="'.$file->getId().'"'
			        );
			        $resultsChild = $service->files->listFiles($optParamsChild);
			        if (count($results->getFiles())) {*/
			        	//Ponemos icono especial
			        	$icon_carpeta = '<i id="car-'.$file->getId().'-fa" class="fas fa-folder-plus text-warning"></i>';
						$class = 'has_child nc closed';
			       // }

			        $arbol .= '<div id="car-'.$file->getId().'" class="tree-folder" style="display: block;">				
										<div id="car-'.$file->getId().'-child" class="tree-folder-header tree-car '.$class.'" data-id="'.$file->getId().'">	
											'.$icon_carpeta.'				
											<div class="tree-folder-name">'.$file->getName().'</div>				
										</div>				
										<div class="tree-folder-content"></div>				
										<div class="tree-loader" style="display: none;">
											<div class="tree-loading">
												<i class="fa fa-spinner fa-2x fa-spin"></i>
											</div>
										</div>
									</div>';

	        	} else {
	        		//Revisamos qué tipo de documento es
	        		$icono = '<i class="far fa-file text-info"></i>';
	        		switch ($file->getMimeType()) {
	        			case 'application/pdf':
	        				$icono = '<i class="fa fa-file-pdf text-danger"></i>';
	        				break;
	        			case 'application/vnd.ms-excel':
	        				$icono = '<i class="far fa-file-excel text-success"></i>';
	        				break;
	        			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
	        				$icono = '<i class="far fa-file-excel text-success"></i>';
	        				break;	
	        			case 'application/msword':
							$icono = '<i class="far fa-file-word text-info"></i>';
							break;
						case 'application/vnd.ms-powerpoint':
							$icono = '<i class="far fa-file-powerpoint text-danger"></i>';
							break;		
	        		}

	        		$arbol .= '<div id="tree-item-'.$file->getId().'" class="tree-item" style="display: block;" data-id="'.$file->getId().'">
	    								<div class="tree-item-name">
											'.$icono.' '.$file->getName().'<span class="load-'.$file->getId().' load-file"></span>
										</div>			
									  </div>';
	        	}
        	}
        } else {
        	$arbol .= '<div class="tree-item tree-empty" style="display: block;" >
							<div class="tree-item-name">
								<i>< Esta carpeta está vacía ></i>
							</div>			
						  </div>';
        }

        return $arbol;

	}

	/*
	 * 
	 * Función encargada de imprimir la carpeta que recibe
	 *
	 */
	function printCarpeta() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		$json['arbol'] = '';

		if(isset($_POST['fileId']) && !empty($_POST['fileId'])) {
			$json['arbol'] = $this->printFolder($_POST['fileId']);
		} else {
			$json['error'] = true;
			$json['msg'] = 'Error al escoger carpeta.';
		}

		echo json_encode($json);
	}

	/*
	 * 
	 * Descarga un archivo en base a su ID
	 *
	 */
	function downloadFile() {
		global $ruta;
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';

		if(isset($_POST['fileId']) && !empty($_POST['fileId'])) {
			
			require_once $ruta.'include/vendor/autoload.php';
	        $client = new Google_Client();
	        $client->setApplicationName("DriveOnix");
	        $client->setDeveloperKey("AIzaSyAoH850PxQu7IVJwG2q8zAgcYt1M7_So6I");
	        $client->setAuthConfig($ruta.'include/client_secret_500109226259-7gelgua06itghcu74stq6pe7g7ceq27u.apps.googleusercontent.com.json');

	        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$ruta.'include/grupo-onix-8332a1056aab.json');
	        $client->useApplicationDefaultCredentials();
	        $client->addScope(Google_Service_Drive::DRIVE);
	        $service = new Google_Service_Drive($client);

	        $fileId = $_POST['fileId'];
			$content = $service->files->get($fileId, array(
			    'alt' => 'media'));
			//$content = $response->getBody()->getContents();
			
			// Open file handle for output.
			$file = $service->files->get($fileId);
			$file_name = $file->getName();
			$outHandle = fopen("files/".$file_name, "w+");
			fwrite($outHandle, $content->getBody()->getContents());

			// Until we have reached the EOF, read 1024 bytes at a time and write to the output file handle.

			/*while (!$content->getBody()->eof()) {
			    fwrite($outHandle, $content->getBody()->read(1024));
			}*/

			// Close output file handle.
			fclose($outHandle);

			$json['file'] = $file_name;

		} else {
			$json['error'] = true;
			$json['msg'] = 'Error al escoger carpeta.';
		}

		echo json_encode($json);
	}

	/*
	* Get memos by category
	*/
	function getMemosByCategory($category = null) {
		$json = ['error' => false, 'msg' => '', 'memos' => []];

		try {
			$sql = "SELECT * FROM memos WHERE (fechaExp > CURDATE() OR fechaExp IS NULL OR repetitivo_fechas IS NOT NULL)";
			if ($category && $category !== 'all') {
				$sql .= " AND color = :category";
			}
			$sql .= " ORDER BY fecha DESC";

			$stmt = $this->_conexion->prepare($sql);
			if ($category && $category !== 'all') {
				$stmt->bindParam(':category', $category);
			}
			$stmt->execute();
			$json['memos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
		case "printRoot":
			$libs->printRoot();
			break;
		case "printCarpeta":
			$libs->printCarpeta();
			break;	
		case "downloadFile":
			$libs->downloadFile();
			break;		
		case "getMemosByCategory":
			$libs->getMemosByCategory();
			break;
	}
}

?>
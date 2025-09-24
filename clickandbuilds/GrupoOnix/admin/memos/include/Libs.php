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

		$sqlQuery = "SELECT * FROM memos ORDER BY id desc";
		
		$consulta = $this->_conexion->prepare($sqlQuery);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			
			$data = array();
			
			foreach ($puntero as $memo) {
				// ---- Handle Fecha / Hora ----
				$fecha = '';
				$hora  = '';
				$fechaExp = '';

				if (!empty($memo['repetitivo_fechas'])) {
					$repetitiveDates = json_decode($memo['repetitivo_fechas'], true);
					if (is_array($repetitiveDates) && count($repetitiveDates) > 0) {
						$fechaArray = [];
						$horaInicial = "00:00"; // default start time
						$horaExp     = "23:59"; // default end time
						$fechaExpFinal = '';

						foreach ($repetitiveDates as $date) {
							if (is_array($date)) {
								$start = $date['fecha'] ?? '';
								$end   = $date['fecha_exp'] ?? '';
								$horaInicial = $date['hora_inicial'] ?? $horaInicial;
								$horaExp     = $date['hora_exp'] ?? $horaExp;
								if ($end && $end != '0000-00-00 00:00:00') {
									$fechaExpFinal = $end; // take last available expiration
								}
							} else {
								$start = $date;
								if (!$fechaExpFinal) $fechaExpFinal = $start; // default end date
							}

							$fechaArray[] = date("d/m/Y", strtotime($start));
						}

						$fecha = implode('<br>', $fechaArray);
						$hora  = $horaInicial . " - " . $horaExp;

						// Fecha Expiración column: show only one date/time for all repetitive dates
						if ($fechaExpFinal) {
							$fechaExp = date("d/m/Y H:i", strtotime($fechaExpFinal));
						}
					}
				} else if (!empty($memo['fecha']) && $memo['fecha'] != '0000-00-00 00:00:00') {
					$fecha = date("d/m/Y", strtotime($memo['fecha']));
					if (!empty($memo['fechaExp']) && $memo['fechaExp'] != '0000-00-00 00:00:00') {
						$hora = date("H:i", strtotime($memo['fecha'])) . " - " . date("H:i", strtotime($memo['fechaExp']));
						$fechaExp = date("d/m/Y", strtotime($memo['fechaExp']));
					} else {
						$hora = date("H:i", strtotime($memo['fecha']));
					}
				} else {
					$fecha = 'Sin fecha';
					$hora  = 'Sin hora';
				}

				// ---- Buttons ----
				$params_editar = array(
					"link"  => "cambios.php?id=".$memo['id'],
					"title" => "Ver/Editar"
				);
				$btn_editar = $this->printButton($module, "cambios", $params_editar);

				$params_borrar = array(
					"title"   => "Borrar",
					"classes" => "borrar",
					"data_id" => $memo['id'],
					"extras"  => "data-name='".$memo["titulo"]."'"
				);
				$btn_borrar = $this->printButton($module, "baja", $params_borrar);

				$aRow = array(
					$fecha,
					$hora,
					$memo['titulo'],
					$memo["contenido"],
					$fechaExp,
					$btn_editar.$btn_borrar
				);

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
				$consulta = $this->_conexion->prepare("DELETE FROM memos WHERE id = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El mensaje fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El mensaje elegido no pudo ser eliminado.";
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
						FROM memos
						WHERE id = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$row = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$row['fecha'] = date("d/m/Y H:i", strtotime($row['fecha']));
					if (!empty($row['fechaExp']) && $row['fechaExp'] != '0000-00-00 00:00:00') {
						$row['fechaExp'] = date("d/m/Y H:i", strtotime($row['fechaExp']));
					}
					$row['pdf'] = ($row['pdf'] != '' ? 
						'<a href="include/pdf/'.$row['pdf'].'" target="_blank" class="btn btn-info"><i class="la la-file-pdf-o"></i> Ver PDF Actual</a>' 
						: '');
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

		// Enhanced debugging
		file_put_contents('debug.txt', "=== SAVE RECORD DEBUG ===\n", FILE_APPEND);
		file_put_contents('debug.txt', "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
		file_put_contents('debug.txt', "FILES data: " . print_r($_FILES, true) . "\n", FILE_APPEND);

		$obligatorios = array("mensaje", "titulo");
		$excepciones = array("pdf", "repetitivo_fechas", "fecha", "fecha_exp", "hora_inicial", "hora_exp", "color", "repeat_config", "repeat_dates", "fecha_datetime", "fecha_exp_datetime", "todo_el_dia", "id");

		// VALIDACIÓN DE CAMPOS OBLIGATORIOS
		foreach($_POST as $clave => $valor){
			if(!$json["error"] && !in_array($clave, $excepciones)){
				if($this->is_empty(trim($valor)) && in_array($clave, $obligatorios)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json['msg'] = "El campo ". lcfirst($clave)." es obligatorio.";    
					file_put_contents('debug.txt', "Validation error: " . $json['msg'] . " for field: " . $clave . "\n", FILE_APPEND);
				}
			}
		}

		// Category / color
		$category = isset($_POST['color']) ? $_POST['color'] : 'Otros';
		file_put_contents('debug.txt', "Category: " . $category . "\n", FILE_APPEND);

		// VALIDACIÓN ESPECÍFICA PARA FECHAS
		if(!$json["error"]) {
			$hasRepetitiveDates = !empty($_POST['repetitivo_fechas']);
			$hasRegularDate = !empty($_POST['fecha']) || !empty($_POST['fecha_datetime']);
			
			if (!$hasRepetitiveDates && !$hasRegularDate) {
				$json["error"] = true;
				$json["focus"] = "fecha";
				$json['msg'] = "Debe especificar al menos una fecha (regular o repetitiva).";
			}
			
			file_put_contents('debug.txt', "Has repetitive dates: " . ($hasRepetitiveDates ? 'YES' : 'NO') . "\n", FILE_APPEND);
			file_put_contents('debug.txt', "Has regular date: " . ($hasRegularDate ? 'YES' : 'NO') . "\n", FILE_APPEND);
		}

		if(!$json["error"]) {
			file_put_contents('debug.txt', "Starting database operations...\n", FILE_APPEND);
			$db = $this->_conexion;
			$db->beginTransaction();

			try {
				// ---- PDF Handling ----
				$pdfFileName = null;
				if(isset($_FILES['pdf']['tmp_name']) && $_FILES['pdf']['name'] != "") {
					$filename = $_FILES['pdf']['name'];
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					
					
					
					$name = uniqid();
					$doc_name = "pdf/".$name.".".$ext;
					$archivo = $name.'.'.$ext;
					
					// Create directory if it doesn't exist
					if (!file_exists('pdf/')) {
						mkdir('pdf/', 0777, true);
					}
					
					if(!move_uploaded_file($_FILES['pdf']['tmp_name'], $doc_name)) {
						throw new Exception('Error al subir archivo PDF');
					}
					$pdfFileName = $archivo;
				}
				file_put_contents('debug.txt', "PDF filename: " . ($pdfFileName ?? 'NULL') . "\n", FILE_APPEND);

				// ---- Check if this is an update ----
				$id = isset($_POST['id']) && $_POST['id'] != '-1' && $_POST['id'] != '' ? $_POST['id'] : null;
				file_put_contents('debug.txt', "Record ID: " . ($id ?? 'NEW') . "\n", FILE_APPEND);

				// ---- Handle Repetitive Dates ----
				if(!empty($_POST['repetitivo_fechas'])) {
					file_put_contents('debug.txt', "Processing repetitive dates...\n", FILE_APPEND);
					
					$repetitivoDates = json_decode($_POST['repetitivo_fechas'], true);
					file_put_contents('debug.txt', "Decoded repetitive dates: " . print_r($repetitivoDates, true) . "\n", FILE_APPEND);

					if(!is_array($repetitivoDates) || count($repetitivoDates) == 0) {
						throw new Exception('Formato de fechas repetitivas inválido');
					}

					// Validate each date string
					$validatedDates = array();
					foreach ($repetitivoDates as $key => $dateString) {
						// Expecting format: "YYYY-MM-DD HH:mm:ss"
						if (empty($dateString) || !is_string($dateString)) {
							throw new Exception("Fecha inválida en posición " . ($key + 1));
						}
						
						// Validate date format
						$timestamp = strtotime($dateString);
						if ($timestamp === false) {
							throw new Exception("Formato de fecha inválido: " . $dateString);
						}
						
						$validatedDates[] = $dateString;
					}

					// Store as simple JSON array of date strings (matching existing DB structure)
					$finalRepetitiveDates = json_encode($validatedDates);
					file_put_contents('debug.txt', "Final repetitive dates JSON: " . $finalRepetitiveDates . "\n", FILE_APPEND);

					if($id) {
						// UPDATE repetitivo memo
						$sql = "UPDATE memos SET 
								repetitivo_fechas = ?, 
								contenido = ?, 
								titulo = ?, 
								color = ?, 
								fecha = NULL, 
								fechaExp = NULL" . 
								($pdfFileName ? ", pdf = ?" : "") . " 
								WHERE id = ?";
						
						$params = [
							$finalRepetitiveDates,
							$_POST['mensaje'],
							$_POST['titulo'],
							$category
						];
						
						if ($pdfFileName) {
							$params[] = $pdfFileName;
						}
						$params[] = $id;
					} else {
						// INSERT new repetitivo memo
						$sql = "INSERT INTO memos (repetitivo_fechas, contenido, titulo, color" . 
							($pdfFileName ? ", pdf" : "") . ") VALUES (?, ?, ?, ?" . 
							($pdfFileName ? ", ?" : "") . ")";
						
						$params = [
							$finalRepetitiveDates,
							$_POST['mensaje'],
							$_POST['titulo'],
							$category
						];
						
						if ($pdfFileName) {
							$params[] = $pdfFileName;
						}
					}
					
				} else {
					// ---- Handle Regular Dates ----
					file_put_contents('debug.txt', "Processing regular dates...\n", FILE_APPEND);

					$fecha = null;
					$fechaExp = null;

					// Check for fecha_datetime first (new format), then fallback to fecha + hora_inicial
					if (!empty($_POST['fecha_datetime'])) {
						$fecha = $_POST['fecha_datetime'];
					} else if (!empty($_POST['fecha'])) {
						$horaInicial = !empty($_POST['hora_inicial']) ? $_POST['hora_inicial'] : "00:00";
						// Convert dd/mm/yyyy to yyyy-mm-dd
						$dateParts = explode('/', $_POST['fecha']);
						if (count($dateParts) == 3) {
							$fecha = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0] . ' ' . $horaInicial . ':00';
						}
					}

					// Check for fecha_exp_datetime first, then fallback to fecha_exp + hora_exp
					if (!empty($_POST['fecha_exp_datetime'])) {
						$fechaExp = $_POST['fecha_exp_datetime'];
					} else if (!empty($_POST['fecha_exp'])) {
						$horaExp = !empty($_POST['hora_exp']) ? $_POST['hora_exp'] : "23:59";
						// Convert dd/mm/yyyy to yyyy-mm-dd
						$expParts = explode('/', $_POST['fecha_exp']);
						if (count($expParts) == 3) {
							$fechaExp = $expParts[2] . '-' . $expParts[1] . '-' . $expParts[0] . ' ' . $horaExp . ':00';
						}
					}

					file_put_contents('debug.txt', "Processed fecha: " . ($fecha ?? 'NULL') . "\n", FILE_APPEND);
					file_put_contents('debug.txt', "Processed fechaExp: " . ($fechaExp ?? 'NULL') . "\n", FILE_APPEND);

					// Validate that we have at least a start date
					if (empty($fecha)) {
						throw new Exception('Fecha inicial es requerida para eventos regulares');
					}

					// Validate same-day events with start/end times
					if (!empty($fechaExp) && !empty($fecha)) {
						$startTimestamp = strtotime($fecha);
						$endTimestamp = strtotime($fechaExp);
						
						if ($endTimestamp <= $startTimestamp) {
							throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
						}
					}

					if($id) {
						// UPDATE regular memo
						$sql = "UPDATE memos SET 
								fecha = ?, 
								fechaExp = ?, 
								contenido = ?, 
								titulo = ?, 
								color = ?, 
								repetitivo_fechas = NULL" . 
								($pdfFileName ? ", pdf = ?" : "") . " 
								WHERE id = ?";
						
						$params = [
							$fecha,
							$fechaExp,
							$_POST['mensaje'],
							$_POST['titulo'],
							$category
						];
						
						if ($pdfFileName) {
							$params[] = $pdfFileName;
						}
						$params[] = $id;
					} else {
						// INSERT new regular memo
						$sql = "INSERT INTO memos (fecha, fechaExp, contenido, titulo, color" . 
							($pdfFileName ? ", pdf" : "") . ") VALUES (?, ?, ?, ?, ?" . 
							($pdfFileName ? ", ?" : "") . ")";
						
						$params = [
							$fecha,
							$fechaExp,
							$_POST['mensaje'],
							$_POST['titulo'],
							$category
						];
						
						if ($pdfFileName) {
							$params[] = $pdfFileName;
						}
					}
				}

				$stmt = $db->prepare($sql);
				file_put_contents('debug.txt', "SQL: " . $sql . "\n", FILE_APPEND);
				file_put_contents('debug.txt', "Params: " . print_r($params, true) . "\n", FILE_APPEND);

				$result = $stmt->execute($params);
				file_put_contents('debug.txt', "Execute result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
				file_put_contents('debug.txt', "Affected rows: " . $stmt->rowCount() . "\n", FILE_APPEND);

				if ($stmt->rowCount() > 0) {
					$db->commit();
					$json['msg'] = 'El mensaje se guardó con éxito.';
					$json['error'] = false;
					file_put_contents('debug.txt', "Transaction committed successfully\n", FILE_APPEND);
				} else {
					throw new Exception('No se pudo guardar el registro en la base de datos');
				}

			} catch(PDOException $e) {
				$db->rollBack();
				$json['msg'] = "Error en la base de datos: ".$e->getMessage();
				$json['error'] = true;
				file_put_contents('debug.txt', "PDO Error: " . $e->getMessage() . "\n", FILE_APPEND);
			} catch(Exception $e) {
				$db->rollBack();
				$json['msg'] = $e->getMessage();
				$json['error'] = true;
				file_put_contents('debug.txt', "General Error: " . $e->getMessage() . "\n", FILE_APPEND);
			}
		} else {
			file_put_contents('debug.txt', "Skipping database operations due to validation error\n", FILE_APPEND);
		}

		file_put_contents('debug.txt', "Final response: " . json_encode($json) . "\n", FILE_APPEND);
		file_put_contents('debug.txt', "=== END DEBUG ===\n\n", FILE_APPEND);

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

	/*
 	* Get memos/avisos for calendar display
	* Returns memos formatted for calendar events
	*/

	function getMemosForCalendar() {
		$json = array();
		$json['error'] = false;
		$json['events'] = array();

		try {
			date_default_timezone_set('America/Monterrey');

			$sql = "SELECT id, titulo, fecha, fechaExp, contenido, color, repetitivo_fechas
					FROM memos 
					WHERE fecha IS NOT NULL OR repetitivo_fechas IS NOT NULL
					ORDER BY fecha DESC";

			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$memos = $consulta->fetchAll(PDO::FETCH_ASSOC);

			$today = date('Y-m-d');

			// Map category names → hex colors
			$colorMap = [
				'Circular'          => '#9C27B0',
				'Avisos temporales' => '#FF9800',
				'Vacaciones'        => '#2196F3',
				'Contrasenas'       => '#4CAF50',
				'Importantes'       => '#F44336',
				'Asuetos'			=> '#FF0090FF',
				'Otros'             => '#9E9E9E'
			];

			foreach ($memos as $memo) {
				$category = $memo['color'] ?: 'Otros';
				$eventColor = $colorMap[$category] ?? '#9E9E9E';

				// ---- Check if memo has repetitivo_fechas ----
				if (!empty($memo['repetitivo_fechas'])) {
					$dates = json_decode($memo['repetitivo_fechas'], true);
					if (is_array($dates)) {
						foreach ($dates as $repetitiveDate) {
							// Parse date + optional time
							$start = $repetitiveDate['fecha'] ?? $repetitiveDate;
							$end   = !empty($repetitiveDate['fecha_exp']) 
									? date('c', strtotime($repetitiveDate['fecha_exp']))
									: date('c', strtotime($start . ' +1 day'));

							$json['events'][] = [
								'id'              => (string)$memo['id'],
								'title'           => $memo['titulo'],
								'start'           => date('c', strtotime($start)),
								'end'             => $end,
								'allDay'          => true,
								'backgroundColor' => $eventColor,
								'borderColor'     => $eventColor,
								'textColor'       => '#FFFFFF', // ADDED: Ensure text is visible
								'extendedProps'   => [
									'categoria'       => $category,
									'contenido_short' => substr(strip_tags($memo['contenido']), 0, 100) . '...',
									'contenido_full'  => $memo['contenido'],
									'type'            => 'repetitive',
									'memoId'          => $memo['id']
								]
							];
						}
					}
				} else {
					// Check if memo is still active (not expired)
					$isActive = true;
					if (!empty($memo['fechaExp']) && $memo['fechaExp'] !== '0000-00-00 00:00:00') {
						$expireDate = date('Y-m-d', strtotime($memo['fechaExp']));
						$isActive = ($expireDate >= $today);
					}

					if ($isActive && !empty($memo['fecha']) && $memo['fecha'] !== '0000-00-00 00:00:00') {
						$startDateTime = strtotime($memo['fecha']);
						$isAllDay = (date('H:i:s', $startDateTime) == "00:00:00");
						
						$endDateTime = null;
						if (!empty($memo['fechaExp']) && $memo['fechaExp'] !== '0000-00-00 00:00:00') {
							$endDateTime = strtotime($memo['fechaExp']);
							// If it's the same date, make sure end time is after start time
							if (date('Y-m-d', $startDateTime) === date('Y-m-d', $endDateTime)) {
								// Same day event - use actual times
								$endDateTime = strtotime($memo['fechaExp']);
							} else {
								// Multi-day event - add one day to end date for FullCalendar
								$endDateTime = strtotime($memo['fechaExp'] . ' +1 day');
							}
						} else {
							// No end date specified - FIXED LOGIC
							if ($isAllDay) {
								// All day event without end date - make it span the entire day
								$endDateTime = strtotime(date('Y-m-d', $startDateTime) . ' +1 day');
							} else {
								// Timed event with no end - make it span until end of day for better visibility
								$endDateTime = strtotime(date('Y-m-d', $startDateTime) . ' 23:59:59');
								// Alternative: make it a 2-3 hour event instead of 1 hour
								// $endDateTime = $startDateTime + (60 * 60 * 3); // +3 hours
							}
						}

						$eventData = [
							'id'              => (string)$memo['id'],
							'title'           => $memo['titulo'],
							'start'           => date('c', $startDateTime),
							'end'             => date('c', $endDateTime),
							'allDay'          => $isAllDay,
							'backgroundColor' => $eventColor,
							'borderColor'     => $eventColor,
							'textColor'       => '#FFFFFF', // ADDED: Ensure text is visible
							'opacity'         => 1.0, // ADDED: Ensure full opacity
							'extendedProps'   => [
								'categoria'       => $category,
								'contenido_short' => substr(strip_tags($memo['contenido']), 0, 100) . '...',
								'contenido_full'  => $memo['contenido'],
								'fechaExp'        => $memo['fechaExp'],
								'type'            => empty($memo['fechaExp']) || $memo['fechaExp'] === '0000-00-00 00:00:00' ? 'permanent' : 'temporary',
								'memoId'          => $memo['id']
							]
						];

						$json['events'][] = $eventData;
					}
				}
			}

			$json['debug'] = [
				'total_memos'     => count($memos),
				'active_events'   => count($json['events']),
				'server_timezone' => date_default_timezone_get(),
				'today'           => $today
			];

		} catch(PDOException $e) {
			$json['error'] = true;
			$json['msg'] = 'Error retrieving memos: ' . $e->getMessage();
		}

		header('Content-Type: application/json');
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
		case "getMemosForCalendar":
			$libs->getMemosForCalendar();
		break;		
	}
}

?>
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
require_once($ruta."include/PHPExcel/PHPExcel.php");

class Libs extends Common {
	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-27
	 *  
	 * Imprime la tabla de registros de perfil de usuarios EXCEPTUANDO 'daemon'
	 */
	function printTable() {
		if(!isset($_SESSION)){
			@session_start();
		}
		$where = " WHERE SUP_ID <> '1' ";

		//Si el perfil de usuario es de Asegurador SOLO puede ver a los admins
		if($_SESSION["onx"]["userprofile"] == 3) {
			$where .= " AND SUP_ID = '2' ";
		}

		/*
		 * Query principal
		 */
		$sqlQuery = "SELECT *
					FROM SISTEMA_USUARIO".$where;

		
		
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
			//Muestra solo los admins
			foreach ($puntero as $row) {
				$counter++;
				$email = '<a href="mailto:'.$row["SIU_EMAIL"].'">'.$row["SIU_EMAIL"].'</a>';
				$telefono = '<a href="https://web.whatsapp.com/send?phone=+52'.$row["SIU_TELEFONO"].'" target="_blank">'.$row["SIU_TELEFONO"].'</a>';
				$aRow = array($row["SIU_NOMBRE_COMPLETO"], $email, $telefono, $row["SIU_TELEFONO_OFICINA"],  $row["SIU_CEDULA"], $row["SIU_CEDULA_VIG"],$row["SIU_ACCESO"] ,$row["SIU_ESQUEMA"], $row["SIU_FECHA_INGRESO"]) ;
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
	function printTableAdmin() {

		if(!isset($_SESSION)){
			@session_start();
		}

		$where = " WHERE SUP_ID <> '1' ";

		//Si el perfil de usuario es de Asegurador SOLO puede ver a los admins
		if($_SESSION["onx"]["userprofile"] == 3) {
			$where .= " AND SUP_ID = '2' ";
		}

		/*
		 * Query principal
		 */

		$sqlQuery = "SELECT *
					FROM SISTEMA_USUARIO".$where;

		
		
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
			//Muestra solo los admins
			foreach ($puntero as $row) {
				$counter++;
				$email = '<a href="mailto:'.$row["SIU_EMAIL"].'">'.$row["SIU_EMAIL"].'</a>';
				$telefono = '<a href="https://web.whatsapp.com/send?phone=+52'.$row["SIU_TELEFONO"].'" target="_blank">'.$row["SIU_TELEFONO"].'</a>';
				$aRow = array($row["SIU_NOMBRE_COMPLETO"], $email, $telefono, $row["SIU_TELEFONO_OFICINA"],  $row["SIU_EXT"], $row["SIU_PUESTO"]) ;
				$uRol = 2;
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

	function getExcel() {
		$json = array();
		$json['completado'] = false;

		$columns = array("A",
						 "B",
						 "C",
						 "D",
						 "E",
						 "F",
						 "G",
						 "H",
						 "I");

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Seguros Onixs")
					 ->setLastModifiedBy("Seguros Onixs")
					 ->setTitle("Directorio")
					 ->setSubject("Directorio")
					 ->setDescription("Directorio")
					 ->setKeywords("Directorio");

		$styleArray = array(
				        'font' => array(
				            'bold' => true
				        ),
				        'alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );
		$styleArray2 = array('alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );		    			 

		//Hacemos más grande las columnas, bold la primera y text-center
		foreach ($columns as $column) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(20);
			$objPHPExcel->getActiveSheet()->getStyle($column."1")->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle($column)->applyFromArray($styleArray2);
		}

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);

		//$objPHPExcel->getStyle("M")->getNumberFormat()->setFormatCode('0'); 
		$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', 'NOMBRE COMPLETO')
		            ->setCellValue('B1', 'E-MAIL')
		            ->setCellValue('C1', 'CELULAR')
		            ->setCellValue('D1', 'TEL OFICINA')
		            ->setCellValue('E1', 'CEDULA')
		            ->setCellValue('F1', 'VIG CEDULA')
		            ->setCellValue('G1', 'ACCESOS')
		            ->setCellValue('H1', 'ESQUEMA')
		            ->setCellValue('I1', 'FECHA INGRESO');          


		/*DATOS*/
		$sql = "SELECT * FROM SISTEMA_USUARIO WHERE SUP_ID <> '1'";

		$db = $this->_conexion;
		$consulta = $db->prepare($sql);

		$n = 2;

		try {
			
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($result as $usuario) {

				//AGREGAMOS LA ROW
				$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A'.$n, $usuario['SIU_NOMBRE_COMPLETO'])
		            ->setCellValue('B'.$n, $usuario['SIU_EMAIL'])
		            ->setCellValue('C'.$n, $usuario['SIU_TELEFONO'])
		            ->setCellValue('D'.$n, $usuario['SIU_TELEFONO_OFICINA'])
		            ->setCellValue('E'.$n, $usuario['SIU_CEDULA'])
		            ->setCellValue('F'.$n, $usuario['SIU_CEDULA_VIG'])
		            ->setCellValue('G'.$n, $usuario['SIU_ACCESO'])
		            ->setCellValue('H'.$n, $usuario['SIU_ESQUEMA'])
		            ->setCellValue('I'.$n, $usuario['SIU_FECHA_INGRESO']);
		    	$n++;

			}

		} catch (PDOException $e) {
			die($e->getMessage().$sql);
		}
		

		$objPHPExcel->getActiveSheet()->setTitle('Directorio');  
		$objPHPExcel->setActiveSheetIndex(0);    
		//$objPHPExcel->setOutputEncoding('UTF-8');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('Libs.php', 'directorio.xlsx', __FILE__));      


		$json['completado'] = true;

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
		case "printTableAdmin":
			$libs->printTableAdmin();
			break;	
		case "getExcel":
			$libs->getExcel();
			break;			
	}
}

?>
<?php

/*
 *  Se identifica la ruta 
 */
$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
  $ruta .= "../";
}

//Se incluye la clase Common
include_once($ruta."include/Common.php");

/*
 *  Se definen los parámetros de la página
 */
define("PAGE_TITLE", "Avisos");

$module = 10;

$common->sentinel($module);

//Se definen los js y css - sólo poner los nombres de los archivos no la terminación
$css = array();
$js = array('index');

//Consultamos MEMOS
$db = $common->_conexion;
if(isset($_GET['id'])) {
  try{
    $sql = "SELECT *
        FROM memos
        WHERE id = ? AND (fechaExp >= CURDATE() OR fechaExp IS NULL OR repetitivo_fechas IS NOT NULL)";

    $consulta = $db->prepare($sql);
    $values = array($_GET['id']);
    $consulta->execute($values);

    if ($consulta->rowCount() > 0) {
      $memo = $consulta->fetch(PDO::FETCH_ASSOC);
    } else {
      header("Location: index.php");
      exit();
    }

  }catch(PDOException $e){
      die($e->getMessage());
  }
} else {
  header("Location: index.php");
  exit();
}

function formatMemoDateTime($datetime, $showTime = true) {
    if (!$datetime || $datetime == '0000-00-00 00:00:00') {
        return null;
    }
    
    $timestamp = strtotime($datetime);
    $date = date("d/m/Y", $timestamp);
    $time = date("H:i", $timestamp);
    
    // Don't show time if it's default values (00:00 for start, 23:59 for end)
    if (!$showTime || $time == '00:00' || $time == '23:59') {
        return $date;
    }
    
    return $date . ' ' . $time;
}

// Function to create the date/time badge content for individual memo view
function getMemoDateBadge($memo) {
    $badgeContent = '';
    $badgeClass = 'badge-secondary';
    
    // Check if this is a repetitive memo
    if (!empty($memo['repetitivo_fechas'])) {
        // For repetitive memos, show the initial date from the 'fecha' field
        $startFormatted = formatMemoDateTime($memo['fecha']);
        if ($startFormatted) {
            $badgeContent = $startFormatted;
            $badgeClass = 'badge-info'; // Special color for repetitive events
        }
    } else {
        // Single memo - show start and end dates/times
        $startFormatted = formatMemoDateTime($memo['fecha']);
        $endFormatted = formatMemoDateTime($memo['fechaExp']);
        
        if ($endFormatted && $startFormatted != $endFormatted) {
            // Has different start and end dates/times
            $badgeContent = $startFormatted . ' - ' . $endFormatted;
            $badgeClass = 'badge-primary';
        } elseif ($startFormatted) {
            // Only start date or same start/end
            $badgeContent = $startFormatted;
            
            // Check if it's all day or has time
            $startTime = date("H:i", strtotime($memo['fecha']));
            if ($startTime != '00:00') {
                $badgeClass = 'badge-warning'; // Has specific time
            } else {
                $badgeClass = 'badge-secondary'; // All day event
            }
        }
    }
    
    return [
        'content' => $badgeContent,
        'class' => $badgeClass
    ];
}

// Get the badge information
$dateBadge = getMemoDateBadge($memo);

?>
<!DOCTYPE html>
<html class="loading" lang="es" data-textdirection="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo(TITLE_MAIN); ?></title>
    <link rel="apple-touch-icon" href="<?php echo $ruta;?>app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $ruta;?>app-assets/images/ico/favicon.png">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/vendors/css/charts/chartist.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/vendors/css/charts/chartist-plugin-tooltip.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN CHAMELEON  CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/css/app.css">
    <!-- END CHAMELEON  CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/css/core/colors/palette-gradient.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/css/pages/chat-application.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/css/pages/dashboard-analytics.css">
    <!-- END Page Level CSS-->
    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>assets/css/style.css">
    <script src="https://kit.fontawesome.com/5b53412d5b.js" crossorigin="anonymous"></script>
    <!-- END Custom CSS-->
    <!-- DATATABLES -->
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/vendors/css/tables/datatable/datatables.min.css">

    <!-- CSS -->
    <?php 
      if (count($css) > 0) {
        foreach ($css as $clave => $valor) {
          echo '<link rel="stylesheet" href="'.$ruta.'css/'.$valor.'.css" />';
        }
      }
    ?>

  </head>
  <body class="horizontal-layout horizontal-menu 2-columns   menu-expanded" data-open="hover" data-menu="horizontal-menu" data-color="bg-gradient-x-orange-yellow" data-col="2-columns">

    <!-- fixed-top-->
    <nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow navbar-static-top navbar-light navbar-brand-center">
      <?php echo $common->printHeader(); ?>
    </nav>

    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-dark navbar-without-dd-arrow navbar-shadow" role="navigation" data-menu="menu-wrapper">
      <div class="navbar-container main-menu-content" data-menu="menu-container">
        <?php echo $common->printMenu(); ?>
      </div>
    </div>

    <div class="app-content content">
      <div class="content-wrapper">
        <div class="content-wrapper-before"></div>
        <div class="content-header row">
          <div class="content-header-left col-md-4 col-12 mb-2">
            <h3 class="content-header-title"><?php echo(PAGE_TITLE); ?></h3>
          </div>
          <div class="content-header-right col-md-8 col-12">
            <?php echo $common->printBreadcrumbs($module); ?>
          </div>
        </div>
        <div class="content-body">
          <div class="container">
            <div class="row">
              <div class="col-sm-12">
                <div class="card pull-up border-top-warning border-top-3 rounded-0">
                  <div class="card-header">
                      <h4 class="card-title">
                          <?= htmlspecialchars($memo['titulo']) ?> 
                          <?php if (!empty($dateBadge['content'])): ?>
                              <span class="badge badge-pill <?= $dateBadge['class'] ?> float-right m-0">
                                  <?= $dateBadge['content'] ?>
                                  <?php if (!empty($memo['repetitivo_fechas'])): ?>
                                      <i class="fa fa-repeat ml-1" title="Evento repetitivo"></i>
                                  <?php endif; ?>
                              </span>
                          <?php endif; ?>
                      </h4>
                      
                      <?php if (!empty($memo['repetitivo_fechas'])): ?>
                          <small class="text-muted d-block">
                              <i class="fa fa-repeat"></i> Este evento se repite en múltiples fechas
                          </small>
                      <?php else: ?>
                          <?php 
                          // Show time constraints for single events
                          $startTime = date("H:i", strtotime($memo['fecha']));
                          $endTime = $memo['fechaExp'] ? date("H:i", strtotime($memo['fechaExp'])) : null;
                          
                          if ($startTime != '00:00' || ($endTime && $endTime != '23:59')): ?>
                              <small class="text-muted d-block">
                                  <i class="fa fa-clock-o"></i>
                                  <?php if ($startTime != '00:00'): ?>
                                      Desde: <?= $startTime ?>
                                      <?php if ($endTime && $endTime != '23:59' && $endTime != $startTime): ?>
                                          | Hasta: <?= $endTime ?>
                                      <?php endif; ?>
                                  <?php else: ?>
                                      Evento de todo el día
                                  <?php endif; ?>
                              </small>
                          <?php endif; ?>
                      <?php endif; ?>
                  </div>
                  
                  <div class="card-content collapse show">
                      <div class="card-body p-3">
                        <?= $memo['contenido']; ?>
                      </div>
                  </div>
                  
                  <?php if (!empty($memo['pdf'])): ?>
                  <div class="card-footer">
                    <a href="../memos/include/pdf/<?= $memo['pdf']; ?>" target="_blank" class="btn btn-success">
                        <i class="la la-file-pdf-o"></i> Descargar PDF
                    </a>
                  </div>
                  <?php endif; ?>
                </div>
                
                <a href="index.php">
                    <button type="button" class="btn btn-info mr-1 mt-3">
                       <i class="ft-arrow-left"></i> Regresar
                    </button>
                 </a>
              </div>
            </div>
          </div>
          
          <!-- /CONTENIDO -->
        </div>
      </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <?php echo $common->printFooter(); ?>

    <!-- BEGIN VENDOR JS-->
    <script src="<?php echo $ruta;?>app-assets/vendors/js/vendors.min.js" type="text/javascript"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <script type="text/javascript" src="<?php echo $ruta;?>app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN CHAMELEON  JS-->
    <script src="<?php echo $ruta;?>app-assets/js/core/app-menu.js" type="text/javascript"></script>
    <script src="<?php echo $ruta;?>app-assets/js/core/app.js" type="text/javascript"></script>
    <!-- END CHAMELEON  JS-->
    <!-- DATATABLES -->
    <script src="<?php echo $ruta;?>app-assets/vendors/js/tables/datatable/datatables.min.js" type="text/javascript"></script>
    <!-- BOOTBOX -->
    <script src="<?php echo $ruta;?>assets/js/bootbox.min.js" type="text/javascript"></script>

    <!-- JS -->
    <?php 
      if (count($js) > 0) {
        foreach ($js as $clave => $valor) {
          echo '<script type="text/javascript" src="js/'.$valor.'.js"></script>';
        }
      }
    ?>
    <!-- /JAVASCRIPTS -->
  </body>
</html>
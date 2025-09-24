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

try{
    $sql = "SELECT *
        FROM memos
        WHERE (fechaExp > CURDATE() OR fechaExp IS NULL OR repetitivo_fechas IS NOT NULL)
        ORDER BY fecha DESC";

    $consulta = $db->prepare($sql);
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        $memos = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

}catch(PDOException $e){
    die($e->getMessage());
}

// Function to format datetime for display
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

// Function to create the date/time badge content
function getMemoDateBadge($memo) {
    $badgeContent = '';
    $badgeClass = 'badge-secondary';
    
    // Check if this is a repetitive memo
    if (!empty($memo['repetitivo_fechas'])) {
        $repetitiveDates = json_decode($memo['repetitivo_fechas'], true);
        if (is_array($repetitiveDates) && count($repetitiveDates) > 0) {
            // For repetitive memos, show first and last date
            $firstDate = min($repetitiveDates);
            $lastDate = max($repetitiveDates);
            
            $startFormatted = formatMemoDateTime($firstDate);
            $endFormatted = formatMemoDateTime($lastDate);
            
            if ($startFormatted == $endFormatted) {
                $badgeContent = $startFormatted;
            } else {
                $badgeContent = $startFormatted . ' - ' . $endFormatted;
            }
            
            $badgeClass = 'badge-info';
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

// Function to get color class based on memo category
function getColorClass($memo) {
    $colorMap = [
        'Circular' => 'purple',
        'Avisos temporales' => 'orange', 
        'Vacaciones' => 'blue',
        'Importantes' => 'red',
        'Otros' => 'secondary'
    ];
    
    return $colorMap[$memo['color'] ?? 'Otros'] ?? 'warning';
}

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

    <!-- name para analiticas -->
    <meta name="onix-section" content="Avisos">

    <style>
    .memo-time-info {
        font-size: 0.8em;
        color: #6c757d;
        margin-top: 5px;
    }
    .repetitive-indicator {
        background: linear-gradient(45deg, #007bff, #28a745);
        color: white;
    }
    .card-title.avisos {
        font-size: 1.1em;
        font-weight: 600;
        margin-bottom: 10px;
        color: #2c3e50;
    }
    .memo-content-preview {
        font-size: 0.9em;
        color: #495057;
        line-height: 1.4;
    }

    .searchBar {
      display: flex;
      justify-content: center;  /* keeps everything centered */
      align-items: center;
      gap: 8px;
      max-width: 500px;         /* slightly wider now */
      margin: 0 auto 30px auto; /* centers the bar itself */
    }

    .searchBar input {
      flex: 1;                  /* lets input expand */
      min-width: 200px;
    }

    .searchBar button {
      white-space: nowrap; /* prevents button text from breaking */
      background-color: #6b6F80;
      color: #ffffffff;
      font-size: 14px;
    }

    .searchMemo {
      border-radius: 20px;
      padding: 8px 20px;
      justify-content: center;
      border-radius: 20px;
      border: 1px solid #ddd;
      padding: 8px 15px;
    }

    .filterBtn {
      white-space: nowrap;      /* prevent text wrap */
      border-radius: 20px;

    }

    </style>

  </head>
  <body class="horizontal-layout horizontal-menu 2-columns menu-expanded" data-open="hover" data-menu="horizontal-menu" data-color="bg-gradient-x-orange-yellow" data-col="2-columns">

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
          <div class="container-fluid">
            <div class="searchBar">
              <div class="dropdown">
                <button class="btn btn-primary filterBtn dropdown-toggle" type="button" data-toggle="dropdown">
                  <i class="fa fa-filter"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item filter-option" data-category="all" href="#">Todos</a>
                  <a class="dropdown-item filter-option" data-category="purple" href="#">Circular</a>
                  <a class="dropdown-item filter-option" data-category="orange" href="#">Avisos temporales</a>
                  <a class="dropdown-item filter-option" data-category="blue" href="#">Vacaciones</a>
                  <a class="dropdown-item filter-option" data-category="green" href="#">Contrasenas</a>
                  <a class="dropdown-item filter-option" data-category="red" href="#">Importantes</a>
                  <a class="dropdown-item filter-option" data-category="secondary" href="#">Otros</a>
                </div>
              </div>

              <input type="search" class="searchMemo" placeholder="Buscar...">
              <button class="searchMemo btn btn-secondary">Borrar</button>
            </div>
            <div class="row">
              <?php
                if (!empty($memos)) {
                  foreach ($memos as $memo) {
                    $dateBadge = getMemoDateBadge($memo);
                    $colorClass = getColorClass($memo);
              ?>
              <div class="col-sm-3" 
                   data-fechaexp="<?= $memo['fechaExp'] ? date('Y-m-d', strtotime($memo['fechaExp'])) : '' ?>"
                   data-repetitivo-fechas="<?= htmlspecialchars($memo['repetitivo_fechas'] ?? '') ?>"
                   data-memo-id="<?= $memo['id'] ?>">
                <div class="card pull-up border-top-<?= $colorClass ?> border-top-3 rounded-0">
                  <div class="card-header">
                    <h5 class="card-title avisos"><?= htmlspecialchars($memo['titulo']) ?></h5>
                    
                    <?php if ($dateBadge['content']): ?>
                      <span class="badge badge-pill <?= $dateBadge['class'] ?> fecha-aviso">
                          <?= $dateBadge['content'] ?>
                      </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($memo['repetitivo_fechas'])): ?>
                      <div class="memo-time-info">
                          <i class="fa fa-repeat"></i> Se repite
                      </div>
                    <?php else: ?>
                      <?php 
                      $startTime = date("H:i", strtotime($memo['fecha']));
                      $endTime = $memo['fechaExp'] ? date("H:i", strtotime($memo['fechaExp'])) : null;
                      
                      if ($startTime != '00:00' || ($endTime && $endTime != '23:59')): ?>
                        <div class="memo-time-info">
                            <i class="fa fa-clock-o"></i>
                            <?php if ($startTime != '00:00'): ?>
                              <?= $startTime ?>
                              <?php if ($endTime && $endTime != '23:59' && $endTime != $startTime): ?>
                                - <?= $endTime ?>
                              <?php endif; ?>
                            <?php else: ?>
                              Todo el día
                            <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                  
                  <div class="card-content collapse show">
                    <div class="card-body p-2">
                      <div class="memo-content-preview">
                        <?= substr(strip_tags($memo['contenido']), 0, 120) ?>
                        <?= strlen(strip_tags($memo['contenido'])) > 120 ? '...' : '' ?>
                      </div>
                    </div>
                  </div>
                  
                  <div class="card-footer text-center p-2">
                    <a href="aviso.php?id=<?= $memo['id'] ?>" class="btn btn-sm btn-outline-primary">
                      Ver Aviso <i class="fa fa-arrow-right"></i>
                    </a>
                  </div>
                </div>
              </div>

              <?php
                  }
                } else {
              ?>
              <div class="col-12">
                <div class="card">
                  <div class="card-body text-center py-5">
                    <i class="fa fa-info-circle fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay avisos disponibles</h4>
                    <p class="text-muted">No se encontraron avisos activos en este momento.</p>
                  </div>
                </div>
              </div>
              <?php
                }
              ?>
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
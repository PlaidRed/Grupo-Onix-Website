<?php

$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

$ruta = "";
$file = $url[count($url) - 1];
for ($i = 1; $i < (count($url) - 1); $i++) {
    $ruta .= "../";
}

// Se incluye la clase Common
include_once($ruta . "include/Common.php");

// Include your Libs.php relative to this file
include_once($ruta . "analiticas/include/Libs.php");

if (!isset($core)) {
    $core = new Core();
}

// Se definen los parámetros de la página
define("PAGE_TITLE", "Analíticas");

$module = 25; // Define the module ID for analytics

$common->sentinel($module); // Check permissions for the module

// Se define js
$js  = array('index');

?>
<!DOCTYPE html>
<html class="loading" lang="es" data-textdirection="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (TITLE_MAIN); ?></title>
    <link rel="apple-touch-icon" href="<?php echo $ruta; ?>app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $ruta; ?>app-assets/images/ico/favicon.png">

    <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/vendors/css/charts/chartist.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/vendors/css/charts/chartist-plugin-tooltip.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/css/app.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/css/core/colors/palette-gradient.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/css/pages/chat-application.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/css/pages/dashboard-analytics.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/vendors/css/tables/datatable/datatables.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="css/index.css">
</head>

<body class="horizontal-layout horizontal-menu 2-columns menu-expanded"
      data-open="hover"
      data-menu="horizontal-menu"
      data-color="bg-gradient-x-orange-yellow"
      data-col="2-columns">

    <nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow navbar-static-top navbar-light navbar-brand-center">
        <?php echo $common->printHeader(); ?>
    </nav>

    <div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-dark navbar-without-dd-arrow navbar-shadow"
         role="navigation" data-menu="menu-wrapper">
        <div class="navbar-container main-menu-content" data-menu="menu-container">
            <?php echo $common->printMenu(); ?>
        </div>
    </div>

    <h3 class="content-header-title"><?php echo (PAGE_TITLE); ?></h3>

    <div class="content-body">

        <!-- Filtros -->
        <div class="card mb-2">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="text-muted">Usuario</label>
                        <select id="fltUser" class="form-control">
                            <option value="">Todos</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="text-muted">Desde</label>
                        <input id="fltFrom" type="date" class="form-control">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="text-muted">Hasta</label>
                        <input id="fltTo" type="date" class="form-control">
                    </div>

                    <div class="col-12 col-md-2 mt-1 mt-md-0">
                        <button id="btnApply" class="btn btn-primary btn-block">Aplicar</button>
                        <div class="btn-group btn-group-sm mt-1 d-flex" role="group" aria-label="Rangos">
                            <button class="btn btn-outline-secondary flex-fill qrange" data-days="7">7d</button>
                            <button class="btn btn-outline-secondary flex-fill qrange" data-days="30">30d</button>
                            <button class="btn btn-outline-secondary flex-fill qrange" data-days="90">90d</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Content Area -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Analíticas Dashboard</h4>
                    </div>
                    <div class="card-body">
                        <p>Los datos de analíticas aparecerán aquí una vez que se apliquen los filtros.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php echo $common->printFooter(); ?>

    <!-- BEGIN VENDOR JS (incluye jQuery) -->
    <script src="<?php echo $ruta;?>app-assets/vendors/js/vendors.min.js" type="text/javascript"></script>

    <!-- Select2 CSS + JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Charts -->
    <script src="<?php echo $ruta;?>app-assets/vendors/js/charts/chartist.min.js" type="text/javascript"></script>
    <script src="<?php echo $ruta;?>app-assets/vendors/js/charts/chartist-plugin-tooltip.min.js" type="text/javascript"></script>

    <!-- PAGE VENDOR -->
    <script type="text/javascript" src="<?php echo $ruta;?>app-assets/vendors/js/ui/jquery.sticky.js"></script>

    <!-- CHAMELEON CORE -->
    <script src="<?php echo $ruta;?>app-assets/js/core/app-menu.js" type="text/javascript"></script>
    <script src="<?php echo $ruta;?>app-assets/js/core/app.js" type="text/javascript"></script>

    <!-- DATATABLES -->
    <script src="<?php echo $ruta;?>app-assets/vendors/js/tables/datatable/datatables.min.js" type="text/javascript"></script>

    <!-- BOOTBOX -->
    <script src="<?php echo $ruta;?>assets/js/bootbox.min.js" type="text/javascript"></script>

    <?php 
        // JS específicos de esta página
        if (count($js) > 0) {
            foreach ($js as $clave => $valor) {
                echo '<script type="text/javascript" src="js/'.$valor.'.js"></script>';
            }
        }
    ?>
</body>
</html>
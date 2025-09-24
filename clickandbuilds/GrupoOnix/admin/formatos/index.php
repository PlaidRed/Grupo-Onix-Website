<?php

session_start(); // make sure this is called before reading $_SESSION

$userProfile = $_SESSION["onx"]["userprofile"] ?? 0;
$userIsAdmin = ($userProfile == 2);

// Debug output (remove after confirming)
//echo '<pre>';
//print_r($_SESSION['onx']);
//echo "User profile: " . $userProfile . "\n";
//echo "userIsAdmin: " . ($userIsAdmin ? 'true' : 'false') . "\n";
//echo '</pre>';
//exit;


$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

$ruta = "";
$file = $url[count($url) - 1];
for ($i = 1; $i < (count($url) - 1); $i++) {
  $ruta .= "../";
}

include_once($ruta . "include/Common.php");

define("PAGE_TITLE", "Herramientas");

$module = 3;
$common->sentinel($module);

$css = array();
$js = array('index');
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
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta; ?>app-assets/vendors/css/tables/datatable/datatables.min.css">

    <link rel="stylesheet" type="text/css" href="css/index.css">
    <?php if (!$userIsAdmin) : ?>
      <style>.edit-only { display: none !important; }</style>
    <?php endif; ?>

    <style>
      
      

    </style>

    <meta name="onix-section" content="Herramientas">

  </head>

  <body class="horizontal-layout horizontal-menu 2-columns menu-expanded" data-open="hover" data-menu="horizontal-menu" data-color="bg-gradient-x-orange-yellow" data-col="2-columns">

    <nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow navbar-static-top navbar-light navbar-brand-center">
      <?php echo $common->printHeader(); ?>
    </nav>

    <div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-dark navbar-without-dd-arrow navbar-shadow" role="navigation" data-menu="menu-wrapper">
      <div class="navbar-container main-menu-content" data-menu="menu-container">
        <?php echo $common->printMenu(); ?>
      </div>
    </div>

    <h3 class="content-header-title">Herramientas</h3>
      <div class="app-content content">
        <div class="content-wrapper">
          <div class="content-body">
            <!-- Agrega esta sección para el título HERRAMIENTAS -->
           <div class="tools-header text-center mb-4">
            <!--<h1 class="tools-title">HERRAMIENTAS</h1>-->
         </div>
            <div class="container main-container">
              <div class="row">
                <div class="col-sm-12">
                  <?php if ($userIsAdmin) : ?>
                  <?php endif; ?>
                  
                    <div class="search-container">
                      <div class="row align-items-center">
                        <div class="col-md-3 text-start">
                          <h2 class="explorer-title">Explorador de carpetas</h2>
                        </div>
                        <div class="col-md-6 text-center">
                          <div class="search-group">
                            <input type="text" id="link-search" class="form-control" placeholder="Buscar enlace...">
                            <button id="clear-search" class="btn btn-secondary">Limpiar</button>
                          </div>
                        </div>
                        <div class="col-md-3 text-end">
                          <?php if ($userIsAdmin) : ?>
                            <button id="toggle-edit-mode" class="btn btn-primary edit-btn">🔧 Editar</button>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  
                      <div class="folder-controls">
                        <button id="add-root-folder" class="btn btn-sm btn-primary edit-only">+ Carpeta Principal</button>
                      </div>
                    
                    
                      <div id="folder-workspace" class="folder-workspace">
                        <div class="folder-tree">
                          <ul class="folder-root"></ul>
                        </div>
                      </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    <?php echo $common->printFooter(); ?>

    <script src="<?php echo $ruta; ?>app-assets/vendors/js/vendors.min.js"></script>
    <script src="<?php echo $ruta; ?>app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <script src="<?php echo $ruta; ?>app-assets/js/core/app-menu.js"></script>
    <script src="<?php echo $ruta; ?>app-assets/js/core/app.js"></script>
    <script src="<?php echo $ruta; ?>app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
    <script src="<?php echo $ruta; ?>assets/js/bootbox.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>


    <?php if (count($js) > 0) { foreach ($js as $clave => $valor) { echo '<script src="js/' . $valor . '.js"></script>'; } } ?>

    <div id="custom-modal" class="custom-modal">
      <div class="custom-modal-content">
        <p id="custom-modal-message">Mensaje</p>
        <button id="custom-modal-close">Aceptar</button>
      </div>
    </div>

    <div id="input-modal" class="custom-modal">
      <div class="custom-modal-content">
        <p id="input-modal-message">Ingrese el valor:</p>
        <input type="text" id="input-modal-input" style="width: 100%; margin: 15px 0; padding: 8px;">
        <button id="input-modal-accept">Aceptar</button>
        <button id="input-modal-cancel">Cancelar</button>
      </div>
    </div>

    <script>
      function showCustomModal(message) {
        $('#custom-modal-message').text(message);
        $('#custom-modal').fadeIn();
      }

      $('#custom-modal-close').on('click', function () {
        $('#custom-modal').fadeOut();
      });
    </script>


  </body>
</html>
<?php
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
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo (TITLE_MAIN); ?></title>
  <link rel="apple-touch-icon" href="<?php echo $ruta; ?>app-assets/images/ico/apple-icon-120.png">
  <link rel="shortcut icon" type="image/x-icon" href="<?php echo $ruta; ?>app-assets/images/ico/favicon.png">
  
  
  <!-- API FUENTES DE GOOGLE -->
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

  <?php
  if (count($css) > 0) {
    foreach ($css as $clave => $valor) {
      echo '<link rel="stylesheet" href="' . $ruta . 'css/' . $valor . '.css" />';
    }
  }
  ?>
  <style>
    .folder-tree ul {
      list-style-type: none;
      padding-left: 20px;
    }

    .folder-tree .folder > ul {
      display: none;
    }

    .folder-tree .folder.open > ul {
      display: block;
    }

    .folder-tree .folder-toggle {
      cursor: pointer;
      user-select: none;
      display: inline-block;
      padding: 4px;
      font-weight: bold;
    }

    .folder-actions {
      margin-left: 10px;
      display: inline;
    }

    .folder-actions button {
      margin-left: 5px;
      cursor: pointer;
      font-size: 12px;
    }

    .folder-controls {
      margin-bottom: 10px;
    }

    .folder-text {
      margin-left: 20px;
      font-style: normal;
      color: #333;
      margin-bottom: 10px;
    }

    .folder-text a {
      text-decoration: none;
      color: #007bff;
    }

    .folder-text a:hover {
      text-decoration: underline;
    }

    .folder-text input {
      margin: 5px 5px 0 0;
      padding: 3px 5px;
      font-size: 13px;
    }

    .credentials-section {
      margin-left: 20px;
    }

    .credentials-section button {
      margin-left: 5px;
    }

    .edit-only,
    .folder-actions,
    .btn-edit-link,
    .btn-delete-link,
    .btn-show-credentials {
      display: none;
    }

    .edit-mode .edit-only,
    .edit-mode .folder-actions,
    .edit-mode .btn-edit-link,
    .edit-mode .btn-delete-link,
    .edit-mode .btn-show-credentials {
      display: inline-block;
    }

  </style>
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

  <h3 class="content-header-title"><?php echo (PAGE_TITLE); ?></h3>
  <div class="app-content content">
    <div class="content-wrapper">
      <div class="content-body">
        <!-- CONTENIDO -->
        <div class="container main-container">
          <div class="row">
            <div class="col-sm-12">
              <h4>Explorador de carpetas</h4>
              <div class="folder-controls">
                <button id="add-root-folder" class="btn btn-sm btn-primary edit-only">+ Carpeta Principal</button>
              </div>
              <div class="folder-tree">
                
              <!-- BOTON DE MODO DE EDICION -->
                <button id="toggle-edit-mode" class="btn btn-primary mb-3">🔧 Edit Mode</button>
                <ul class="folder-root"></ul>
              </div>
            </div>
          </div>
        </div>
        <!-- /CONTENIDO -->
      </div>
    </div>
  </div>

  <?php echo $common->printFooter(); ?>

  <script src="<?php echo $ruta; ?>app-assets/vendors/js/vendors.min.js" type="text/javascript"></script>
  <script src="<?php echo $ruta; ?>app-assets/vendors/js/ui/jquery.sticky.js"></script>
  <script src="<?php echo $ruta; ?>app-assets/js/core/app-menu.js" type="text/javascript"></script>
  <script src="<?php echo $ruta; ?>app-assets/js/core/app.js" type="text/javascript"></script>
  <script src="<?php echo $ruta; ?>app-assets/vendors/js/tables/datatable/datatables.min.js" type="text/javascript"></script>
  <script src="<?php echo $ruta; ?>assets/js/bootbox.min.js" type="text/javascript"></script>

  <?php
  if (count($js) > 0) {
    foreach ($js as $clave => $valor) {
      echo '<script type="text/javascript" src="js/' . $valor . '.js"></script>';
    }
  }
  ?>
</body>
</html>
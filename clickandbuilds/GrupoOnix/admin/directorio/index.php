<?php

/*
 *  Se identifica la ruta 
 */
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

if(!isset($_SESSION)){
  @session_start();
}



//Se incluye la clase Common
include_once($ruta."include/Common.php");


/*
 *  Se definen los parámetros de la página
 */
define("PAGE_TITLE", "Directorio");

$module = 13;

$common->sentinel($module);

//Se definen los js y css - sólo poner los nombres de los archivos no la terminación
$css = array();
$js = array('index');

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
    <!-- END Custom CSS-->
    <!-- DATATABLES -->
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/vendors/css/tables/datatable/datatables.min.css">

    <style type="text/css">
        .disabled {
            cursor: not-allowed;
        }
    </style>

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
    <div class="content-wrapper custom-header">
    <div class="content-wrapper-before"></div>
        
    </div>
    <div class="header-navbar navbar-expand-sm navbar navbar-horizontal navbar-fixed navbar-dark navbar-without-dd-arrow navbar-shadow" role="navigation" data-menu="menu-wrapper">
      <div class="navbar-container main-menu-content" data-menu="menu-container">
        <?php echo $common->printMenu(); ?>
      </div>
    </div>
    <h3 class="content-header-title"><?php echo(PAGE_TITLE); ?></h3>
    <div class="app-content content">
      <div class="content-wrapper">
        <div class="content-body">
          <!-- CONTENIDO -->
          <div class="container main-container">
            <div class="row">
                <div class="col-sm-2 offset-7 cont-guardar">
                    
                </div>
                <div class="col-sm-1 loader text-right">
                    
                </div>
                <div class="col-sm-2">
                    <?php

                        $lnk = '<a class="btn-excel" href="#" data-id="">
                                    <button type="button" class="btn btn-info">
                                        <i class="la la-file-excel-o"></i>Generar Excel
                                    </button>
                                </a>';
                        echo $common->printLink($module, 'alta', $lnk);
                    ?>
                </div>
            </div>
            <div class="row table-responsive">
              <table class="table table-striped table-bordered <?=($_SESSION['onx']['userprofile'] == 3)?'table-admin':'table-usuario';?>">
                <?if($_SESSION['onx']['userprofile'] == 3){?>
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>E-mail</th>
                            <th>Celular</th>
                            <th>Teléfono Oficina</th>
                            <th>Extensión</th>
                            <th>Puesto</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>E-mail</th>
                            <th>Celular</th>
                            <th>Teléfono Oficina</th>
                            <th>Extensión</th>
                            <th>Puesto</th>
                        </tr>
                    </tfoot>
                <?}else{?>
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>E-mail</th>
                            <th>Celular</th>
                            <th>Teléfono Oficina</th>
                            <th>Cédula</th>
                            <th>Vig. Cédula</th>
                            <th>Accesos</th>
                            <th>Esquema</th>
                            <th>Fecha Ingreso</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>E-mail</th>
                            <th>Teléfono</th>
                            <th>Teléfono Oficina</th>
                            <th>Cédula</th>
                            <th>Vig. Cédula</th>
                            <th>Accesos</th>
                            <th>Esquema</th>
                            <th>Fecha Ingreso</th>
                        </tr>
                    </tfoot>
                <?}?>
              </table>
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
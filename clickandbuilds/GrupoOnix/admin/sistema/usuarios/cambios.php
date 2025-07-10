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

//Se incluye la clase Common
include_once($ruta."include/Common.php");


/*
 *  Se definen los parámetros de la página
 */
define("PAGE_TITLE", "Edición de Usuarios");

$module = 2;

$common->sentinel($module, 'cambios.php');

//Se definen los js y css - sólo poner los nombres de los archivos no la terminación
$css = array();
$js = array('cambios');

?>
<!DOCTYPE html>
<html class="loading" lang="es" data-textdirection="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
    <!-- JQUERY UI -->
    <link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>app-assets/css/plugins/ui/jquery-ui.css">

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
    <h3 class="content-header-title"><?php echo(PAGE_TITLE); ?></h3>
    <div class="app-content content">
      <div class="content-wrapper">

        <div class="content-body">
          <!-- CONTENIDO -->
          <div class="container main-container">
            <form id="frm-usuario" class="form form-horizontal">
              <input name="id" type="hidden" id="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '-1' ; ?>">
              <div class="row">
                <div class="col-md-12">
                  <div class="card">
                    <div class="card-header">
                      <h4 class="card-title" id="horz-layout-colored-controls"><i class="ft-info"></i> INFORMACIÓN GENERAL</h4>
                      <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                      <div class="heading-elements">
                          <ul class="list-inline mb-0">
                              <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                          </ul>
                      </div>
                    </div>
                    <div class="card-content collpase show">
                      <div class="card-body">
                        <div class="form-body">
                          <div class="row">
                            <div class="col-sm-12">
                              <div class="form-group row">
                                <label class="col-sm-2 label-control" for="nombre_completo">Nombre Completo*</label>
                                <div class="col-sm-4">
                                  <input type="text" id="nombre_completo" class="form-control border-primary"name="nombre_completo">
                                </div>
                                <label class="col-sm-2 label-control" for="puesto">Puesto</label>
                                <div class="col-sm-4">
                                  <input type="text" id="puesto" class="form-control border-primary" name="puesto">
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-sm-12">
                              <div class="form-group row">
                                <label class="col-sm-2 label-control" for="perfil">Perfil de Usuario*</label>
                                <div class="col-sm-4" id="container-sel">
                                  <select class="form-control disabled" disabled>
                                    <option>Cargando...</option>
                                  </select>
                                </div>
                                <label class="col-sm-2 label-control" for="email">E-mail*</label>
                                <div class="col-sm-4">
                                  <input type="email" id="email" class="form-control border-primary" name="email">
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-sm-12">
                              <div class="form-group row">
                                <label class="col-sm-2 label-control" for="pswd">Contraseña*</label>
                                <div class="col-sm-4">
                                  <input type="password" id="pswd" class="form-control border-primary" name="pswd">
                                </div>
                                <label class="col-sm-2 label-control" for="conf-pswd">Confirmación de Contraseña*</label>
                                <div class="col-sm-4">
                                  <input type="password" id="conf-pswd" class="form-control border-primary" name="conf-pswd">
                                </div>
                              </div>
                            </div>
                          </div>                         
                          <div class="row">
                            <div class="col-md-12">
                              <div class="form-group row">
                                <label class="col-sm-2 label-control" for="telefono">Celular*</label>
                                <div class="col-sm-4">
                                  <input type="text" id="telefono" class="form-control border-primary" name="telefono">
                                </div>
                                <label class="col-sm-2 label-control" for="telefono_oficina">Teléfono Oficina</label>
                                <div class="col-sm-4">
                                  <input type="text" id="telefono_oficina" class="form-control border-primary" name="telefono_oficina">
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-12">
                              <div class="form-group row">
                                <label class="col-sm-2 label-control" for="extencion">Extención</label>
                                <div class="col-sm-4">
                                  <input type="text" id="extencion" class="form-control border-primary" name="extencion">
                                </div>
                                <label class="col-sm-2 label-control" for="fecha_ingreso">Fecha Ingreso</label>
                                <div class="col-sm-4">
                                  <input type="text" id="fecha_ingreso" class="form-control border-primary fecha" name="fecha_ingreso">
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-12">
                              <div class="form-group row">
                                <label class="col-sm-2 label-control" for="cedula">Tipo de Cédula</label>
                                <div class="col-sm-4">
                                  <input type="text" id="cedula" class="form-control border-primary" name="cedula">
                                </div>
                                <label class="col-sm-2 label-control" for="cedula_vig">Vigencia Cédula</label>
                                <div class="col-sm-4">
                                  <input type="text" id="cedula_vig" class="form-control border-primary fecha" name="cedula_vig">
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-12">
                              <div class="form-group row">
                                <label class="col-sm-2 label-control" for="esquema">Esquema</label>
                                <div class="col-sm-4">
                                  <select id="esquema" name="esquema" class="form-control border-primary">
                                    <option value="Fundador">Fundador</option>
                                    <option value="10">10%</option>
                                    <option value="15">15%</option>
                                    <option value="20">20%</option>
                                    <option value="30">30%</option>
                                    <option value="40">40%</option>
                                    <option value="50">50%</option>
                                  </select>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="card">
                    <div class="card-content collpase show">
                      <div class="card-body">
                        <div class="form-actions right">
                            <a href="index.php">
                              <button type="button" class="btn btn-danger mr-1">
                                <i class="ft-x"></i> Cancelar
                              </button>
                            </a>
                            <button type="submit" class="btn btn-primary guardar">
                              <i class="la la-check-square-o"></i> Guardar
                            </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
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
    <!-- JQUERY UI -->
    <script src="<?php echo $ruta;?>app-assets/vendors/js/ui/jquery-ui.min-date.js" type="text/javascript"></script>

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
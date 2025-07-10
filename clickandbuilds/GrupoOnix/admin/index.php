<?php
/*
 *  Verificación de Sesión
 */

require_once 'include/Core.php'; // Asegúrate de que la ruta sea correcta
$core = new Core();
$core->sentinel(0); // Ejecuta la redirección falsa para pruebas

if(!isset($_SESSION)){
    @session_start();

}

if (isset($_SESSION["onx"]["userid"])) {
    //Si ya se encuentra registrado el usuario en la session lo redirecciona al sistema
    header("Location: home.php");
}

$error = false;

if (isset($_SESSION["onx"]["loginError"])) {
    $error = true;
    $errorNo = $_SESSION["onx"]["loginError"];
    unset($_SESSION["onx"]["loginError"]);
}

$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

//$url = explode("/", $_SERVER["REQUEST_URI"]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
    $ruta .= "../";
}

?>
<!DOCTYPE html>
<html class="loading" lang="es" data-textdirection="ltr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>Iniciar Sesión | Administrador Grupo Ónix</title>
    <link rel="apple-touch-icon" href="app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="app-assets/images/ico/favicon.png">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/vendors.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN CHAMELEON  CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/app.css">
    <!-- END CHAMELEON  CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="app-assets/css/core/menu/menu-types/horizontal-menu.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/core/colors/palette-gradient.css">
    <link rel="stylesheet" type="text/css" href="app-assets/css/pages/login-register.css">
    <!-- END Page Level CSS-->
    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <!-- END Custom CSS-->

    <style type="text/css">
        #login-info {
            display: none;
        }
    </style>
  </head>
  <body class="horizontal-layout horizontal-menu 1-column  bg-full-screen-image menu-expanded blank-page blank-page" data-open="hover" data-menu="horizontal-menu" data-color="bg-gradient-x-blue-cyan" data-col="1-column">
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <div class="app-content content">
      <div class="content-wrapper">
        <div class="content-wrapper-before"></div>
        <div class="content-header row"></div>
        <div class="content-body"><section class="flexbox-container">
    <div class="col-12 d-flex align-items-center justify-content-center">
        <div class="col-md-4 col-10 box-shadow-2 p-0">
            <div class="card border-grey border-lighten-3 px-1 py-1 m-0">
                <div class="card-header border-0">
                    <div class="text-center mb-1">
                            <!--img src="app-assets/images/logo/logo.png" alt="branding logo"-->
                    </div>
                    <div class="font-large-1  text-center">                       
                        Administrador de <br>Grupo Onix
                    </div>
                </div>
                <div class="card-content">
                   
                    <div class="card-body">
                        <form id="frm-login" class="form-horizontal" action="#" novalidate>
                            <div id="login-info">
                                <div id="login-msg" class="alert alert-light alert-dismissible">
                                    
                                </div>
                            </div>
                            <fieldset class="form-group position-relative has-icon-left">
                                <input type="text" class="form-control round" id="email" name="email" placeholder="Correo Electrónico" required>
                                <div class="form-control-position">
                                    <i class="ft-user"></i>
                                </div>
                            </fieldset>
                            <fieldset class="form-group position-relative has-icon-left">
                                <input type="password" class="form-control round" id="password" name="password" placeholder="Contraseña" required>
                                <div class="form-control-position">
                                    <i class="ft-lock"></i>
                                </div>
                            </fieldset>
                            <div class="form-group row">
                                <div class="col-md-6 col-12 text-center text-sm-left">
                                   
                                </div>
                                <div class="col-md-7 col-12 float-sm-left text-center text-sm-right"><a href="recover-password.php" class="card-link">¿Olvidaste tu contraseña?</a></div>
                            </div>                           
                            <div class="form-group text-center">
                                <button type="submit" class="btn round btn-block btn-glow btn-bg-gradient-x-orange-yellow col-12 mr-1 mb-1">Iniciar Sesión</button>    
                            </div>
                        </form>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</section>

        </div>
      </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <!-- BEGIN VENDOR JS-->
    <script src="app-assets/vendors/js/vendors.min.js" type="text/javascript"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <script type="text/javascript" src="app-assets/vendors/js/ui/jquery.sticky.js"></script>
    <script src="app-assets/vendors/js/forms/validation/jqBootstrapValidation.js" type="text/javascript"></script>
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN CHAMELEON  JS-->
    <script src="app-assets/js/core/app-menu.js" type="text/javascript"></script>
    <script src="app-assets/js/core/app.js" type="text/javascript"></script>
    <!-- END CHAMELEON  JS-->
    <!-- BEGIN PAGE LEVEL JS-->
    <script src="app-assets/js/scripts/forms/form-login-register.js" type="text/javascript"></script>
    <!-- END PAGE LEVEL JS-->
    <script src="assets/js/index.js"></script>
  </body>
</html>
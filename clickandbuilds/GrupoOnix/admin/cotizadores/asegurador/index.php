<?php

// START SESSION AT THE BEGINNING
session_start();

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
define("PAGE_TITLE", "Cotizadores");

$module = 16;

$common->sentinel($module);

// DEBUG: Check what session variables are available
error_log("Available session variables: " . print_r(array_keys($_SESSION), true));

//Se definen los js y css - sólo poner los nombres de los archivos no la terminación
$css = array();
$js = array('index');

//Consultamos MEMOS
$db = $common->_conexion;

try{
    $sql = "SELECT *
            FROM cotizadores
            ORDER BY titulo ASC";
    $consulta = $db->prepare($sql);
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        $cotizadores = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

}catch(PDOException $e){
    die($e->getMessage());
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
         <link rel="stylesheet" type="text/css" href="css/index.css">

    <?php 
      if (count($css) > 0) {
        foreach ($css as $clave => $valor) {
          echo '<link rel="stylesheet" href="'.$ruta.'css/'.$valor.'.css" />';
        }
      }
    ?>

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

<h3 class="content-header-title"><?php echo(PAGE_TITLE); ?></h3>

<div class="app-content content">
  <div class="content-wrapper">
    <div class="content-body">
      <div class="container">
        <!-- AVISO -->
        <div class="aviso-container">
          <div class="content-header-line"></div> 
            <div class="aviso-label">AVISO</div>
            <div class="aviso-message">
              <p>Recuerden que las contraseñas se actualizan con frecuencia. Verifiquen que sus credenciales estén vigentes en este portal</p>
              <p>y eviten más de dos intentos de inicio de sesión seguidos para no bloquear el acceso.</p>
            </div>
        </div>
        <!-- /AVISO -->
      </div>
      <div class="cotizador-wrapper-box p-2 mb-2"> 
      <div class="row">
      <!-- Buscador -->
      <div class="search-container">
        <div class="buscador">
          <input type="text" id="search" class="search-input" placeholder="Buscar aseguradora...">
        </div>
        <div>
          <button 
            class="clear-btn" 
            id="clear-search" 
            title="Limpiar búsqueda">
            Limpiar
          </button>
        </div>
      </div>
      <!-- /Buscador -->

      <div class="container">
        <div class="aseguradoras">
          <?php foreach ($cotizadores as $cotizador): ?>
            <div class="aseguradora-card">
              <img 
                src="../abc/include/imagenes/<?php echo htmlspecialchars($cotizador['imagen']); ?>" 
                alt="<?php echo htmlspecialchars($cotizador['titulo']); ?>" 
                class="aseguradora-logo"
              />

              <div class="aseguradora-nombre"><?php echo htmlspecialchars($cotizador['titulo']); ?></div>

              <div class="aseguradora-label">Usuario</div>
              <div class="aseguradora-input-group">
                <input 
                  type="text" 
                  class="aseguradora-input no-interaction" 
                  value="<?php echo htmlspecialchars($cotizador['user']); ?>" 
                  readonly 
                  id="user-<?php echo $cotizador['id']; ?>" 
                  tabindex="-1"
                >
                <button 
                  class="aseguradora-copy-btn copy-btn" 
                  title="Copiar usuario" 
                  data-target="user-<?php echo $cotizador['id']; ?>">
                  <i class="la la-copy"></i>
                </button>
              </div>

              <div class="aseguradora-label">Contraseña</div>
              <div class="aseguradora-input-group">
                <input 
                  type="text" 
                  class="aseguradora-input no-interaction" 
                  value="<?php echo htmlspecialchars($cotizador['password']); ?>" 
                  readonly 
                  id="pass-<?php echo $cotizador['id']; ?>" 
                  tabindex="-1"
                >
                <button 
                  class="aseguradora-copy-btn copy-btn" 
                  title="Copiar contraseña" 
                  data-target="pass-<?php echo $cotizador['id']; ?>">
                  <i class="la la-copy"></i>
                </button>
              </div>

              <a 
                href="<?php echo htmlspecialchars($cotizador['liga']); ?>" 
                target="_blank" 
                class="clear-btn ir-pagina-link" 
                title="Ir a página del cotizador"
                data-track="ir_pagina_<?php echo preg_replace('/[^a-z0-9]/i', '_', strtolower($cotizador['titulo'])); ?>"
                data-cotizador-id="<?php echo $cotizador['id']; ?>"
                data-cotizador-name="<?php echo htmlspecialchars($cotizador['titulo']); ?>"> 
                Ir a Página
              </a>
            </div>
          <?php endforeach; ?>
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

    <?php 
      if (count($js) > 0) {
        foreach ($js as $clave => $valor) {
          echo '<script type="text/javascript" src="js/'.$valor.'.js"></script>';
        }
      }
    ?>

    <!-- DEBUG: Show current user session info -->
    <script>
      console.log('Current session user info check - open browser console to see tracking status');
    </script>

  </body>
</html>
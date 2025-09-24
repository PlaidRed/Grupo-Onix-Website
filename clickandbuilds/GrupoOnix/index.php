<?php

include_once("admin/include/Database.php");

//Consultamos tarifa de envío
$Database = new Database();
$db = $Database->_conexion;

try{
    $sql = "SELECT *
            FROM contenido";
    $consulta = $db->prepare($sql);
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        $contenido = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $home_banner_img = $contenido[0];
        $home_banner_txt = $contenido[1];
        $home_compania_txt = $contenido[2];
        $home_productos_txt = $contenido[3];
    }

}catch(PDOException $e){
    die($e->getMessage());
}

try{
    $sql = "SELECT *
            FROM productos
            ORDER BY orden ASC";
    $consulta = $db->prepare($sql);
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

}catch(PDOException $e){
    die($e->getMessage());
}

try{
    $sql = "SELECT *
            FROM aseguradoras
            ORDER BY orden ASC";
    $consulta = $db->prepare($sql);
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        $aseguradoras = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

}catch(PDOException $e){
    die($e->getMessage());
}
// Program to display current page URL. 
  
$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
                "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .  
                $_SERVER['REQUEST_URI']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Seguros Ónix</title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,300,400,600&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link rel="stylesheet/less" type="text/css" href="less/base.less" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="https://www.google.com/recaptcha/api.js?render=6LePOscZAAAAAPIeKSSaXpcd5OVsaMIVDKkuXId8"></script>
</head>
<body>
    <header>
        <div class="container">
            <a href="<?=$link;?>"><img src="img/logo_white.png" alt="Logo Onix"></a>
            <div id="menu-container">
                <a href="#nuestracompania">NOSOTROS</a>
                <a href="#nuestrosproductos">PRODUCTOS</a>
                <a href="#contacto">CONTACTO</a>
                <a class="unete" href="<?=$link?>/bolsa_trabajo.php">ÚNETE A G.O.</a>
                <a href="https://app01.copsis.com/sio3vista/login_clientes.aspx?f1=LPxnGNqqAslh5+difquMpQ==" target="_blank">QuattroCRM</a>
                <a href="<?=$link?>/admin" class="last-right">LOG IN</a>
            </div>
        </div>
    </header>
    <div id="banner" style="background-image: url('img/<?=$home_banner_img['contenido'];?>');">
        <div id="slogan-container">
            <div id="slogan">
                <?=$home_banner_txt['contenido'];?>
                <!--p><span>30 años<span class="br"></span>de experiencia</span><span class="br"></span>cuidando<span class="br"></span>lo que mas<span class="br"></span>quieres.</p-->
            </div>
        </div>
    </div>
    <div id="nuestracompania">
        <div class="container">
            <h2>Nuestra<span class="br"></span>Compañía</h2>
            <p><?=$home_compania_txt['contenido'];?></p>
        </div>
    </div>
    <div id="nuestrosproductos">
        <div class="container">
            <div id="productos-info">
                <h2>Nuestros<span class="br"></span>Productos</h2>
                <p><?=$home_productos_txt['contenido'];?></p>
            </div>
            <div id="ProductosOnix" class="carousel slide carousel-showsixmoveone" data-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    foreach ($productos as $key => $producto) {
                    ?>
                    <div class="item clearfix <?=($key == 0 ? 'active' : '');?>">
                        <div class="col-xs-12 col-md-3">
                            <img src="img/<?=$producto['imagen'];?>" alt="<?=$producto['nombre'];?>">
                            <h3><?=$producto['nombre'];?></h3>
                            <p><?=$producto['descripcion'];?></p>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                </div>
                <a class="left carousel-control" href="#ProductosOnix" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#ProductosOnix" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        </div>
    </div>
    <div id="aseguradoras">
        <div class="container">
            <h2>Trabajamos con las mejores aseguradoras</h2>
            <div id="aseguradoras-container">
                <?php
                foreach ($aseguradoras as $aseguradora) {
                ?>
                    <div><img src="img/<?=$aseguradora['imagen'];?>" alt="<?=$aseguradora['nombre'];?>"></div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div id="contacto">
        <div class="container">
            <h2>Contacto</h2>
            <p>¿Tienes alguna duda o requieres de algún servicio o cotización?<br>¡Contáctanos!</p>
            <!--<a href="<?=$link;?>/bolsa_trabajo.php">¡Aqui!</a>-->
            <div id="contacto-container" class="row">
                <form>
                    <div class="container-alert"></div>
                    <label><input type="text" name="nombre" placeholder="Nombre"></label>
                    <label><input type="email" name="email" placeholder="E-mail"></label>
                    <label><textarea placeholder="Mensaje" id="mensaje" name="mensaje"></textarea></label>
                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                    <input type="submit" name="submit" value="Enviar">
                </form>
                <script>
                    grecaptcha.ready(function() {
                        grecaptcha.execute('6LePOscZAAAAAPIeKSSaXpcd5OVsaMIVDKkuXId8', {action:'validate_captcha'}).then(function(token) {
                            document.getElementById('g-recaptcha-response').value = token;
                        });
                    });
                </script>
                <div id="direccion">
                    <h5>Dirección:</h5>
                    Daniel Zambrano 440.<span class="br"></span> 
                    Colonia Chepevera<span class="br"></span>
                    Monterrey N.L C.P. 64030<span class="br"></span>
                    <h5>Correo:</h5>
                    direccion@segurosonix.com<span class="br"></span>
                    <h5>Teléfonos:</h5>
                    81 2318 0138 al 40<span class="br"></span>
                    <div class="social"></div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <a href="avisodeprivacidad.pdf" target="_blank">Aviso de privacidad</a>
    </footer>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/carousel.js"></script>
    <script src="js/index.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js" ></script>
    
</body>
</html>
<?php

include_once("includes/Database.php");

//Consultamos tarifa de envío
$Database = new Database();
$db = $Database->_conexion;

try{
    $sql = "SELECT *
            FROM contenido WHERE  id = 5 OR id = 3 OR id = 6 OR id = 7";
    $consulta = $db->prepare($sql);
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        $contenido = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $home_compania_txt = $contenido[0];
        $unete_banner_img = $contenido[1];
        $unete_banner_txt = $contenido[2];
        $unete_email = $contenido[3];
    }

}catch(PDOException $e){
    die($e->getMessage());
}


try{
    $sql = "SELECT *
            FROM ventajas";
    $consulta = $db->prepare($sql);
    $consulta->execute();

    if ($consulta->rowCount() > 0) {
        $ventajas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

}catch(PDOException $e){
    die($e->getMessage());
}
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
            <a href="/"><img src="img/logo_white.png" alt="Logo Onix"></a>
            <div id="menu-container">
                <a href="/">HOME</a>
                <a href="#ventajascompetitivas">VENTAJAS</a>
                <a href="#formulario">ASÓCIATE</a>
                <a href="//admin" class="last-right">LOG IN</a>
            </div>
        </div>
    </header>
    <div id="banner" class="bolsatrabajo" style="background-image: url('img/<?=$unete_banner_img['contenido'];?>');">
        <div id="slogan-container">
            <div id="slogan">
                <?=$unete_banner_txt['contenido'];?>
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
    <div id="ventajascompetitivas">
            <div class="left">
                <h2>Ventajas Competitivas</h2>
                <ul>
                    <?php
                    foreach ($ventajas as $ventaja) {
                    ?>
                        <li><?=$ventaja['contenido'];?></li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
            <div class="right" style="background:url(img/ventajas.jpg)"></div>
    </div>
    <div id="formulario">
        <div class="container">
            <h2>¡Únete a nuestro equipo!</h2>
            <form>
                <div class="container-alert"></div>
                <div class="row1">
                    <label><input type="text" name="nombre" id="nombre" placeholder="Nombre"></label>
                    <label><input type="number" name="edad" id="edad" placeholder="Edad"></label>
                    <label><input type="email" name="email" id="email" placeholder="Correo Electrónico"></label>
                </div>
                <div class="row2">
                    <label><input type="text" name="cedula" id="cedula" placeholder="Tipo de cédula"></label>
                    <label><input type="number" name="telefono" id="telefono" placeholder="Teléfono celular"></label>
                </div>
                <div class="row2">
                    <label><input type="number" name="experiencia" id="experiencia" placeholder="Años de experiencia en Seguros"></label>
                    <label><input type="text" name="companias" id="companias" placeholder="Compañías con las que trabajas"></label>
                </div>
                <textarea name="ramos" id="ramos" placeholder="Ramos que dominas:"></textarea>
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
    <script src="js/bolsa.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js" ></script>
</body>
</html>
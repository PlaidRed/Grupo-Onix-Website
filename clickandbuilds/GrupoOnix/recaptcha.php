<?
  
  if(isset($_POST['g-recaptcha-response'])){
        $captcha=$_POST['g-recaptcha-response'];
    }
    else
        $captcha = false;

    if(!$captcha){
        echo "es spam";
        exit;
    }
    else{
        $secret = "6LePOscZAAAAAJk5vVlQGBVbmgsp1dK_TwNxcxJL";
        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=
            .$secret.&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
        if($response.success==false)
        {
            echo "es spam";
        exit;
        }
    }

    if ($response.success==true && $response->score <= 0.5) {
        // creo que no es spam
    }
    
    unset($_POST['g-recaptcha-response']); 
    unset($_POST['action']); 
    
?>
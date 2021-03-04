<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Yenten coin - Yenten-pool.ml Faucet</title>
<meta name="robots" content="noindex,nofollow, noodp,noydir"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body style="background:#eee; margin: 30px; padding-top: 50px;">

<?php

require_once("jsonRPCClient.php");
require_once("server_config.php");
$alt = new jsonRPCClient($GLOBALS["RPC_URL"]);
  
  $balance = 0;
  $faucettext_3 = "";
  try {
    $balance = $alt->getbalance();
  } catch(Exception $e) {
    $balance = "0.00";
    $faucettext_3 = " Нет соединения!";
  }
  $faucettext_1 = "На кране осталось ";
  $faucettext_2 =  " енотов";

?>


<style>
    .faucet-nav {
        text-shadow: 0 1px 1px rgba(0,0,0,.3);
        box-shadow: 0 1px 0 rgba(255,165,0,.2);
        background-color: #DDD;
        border-color: #ffa500;
        
    }
    .dropdown-menu>li>a, .dropdown, .faucet-nav .navbar-nav>.active>a, .faucet-nav .navbar-nav>.active>a:hover {
        color: #ffa500;
        background-color: #DDD;
        

    }
     .dropdown-menu{
        background-color: #DDD;
        box-shadow: 0 1px 1px rgba(255,165,0,.2);
     }
     .dropdown-menu>li>a:hover {
       background-color: #CCC;
      color: #000;
      }
    .dropdown open {
      background-color: #DDD;
      color: #ffa500;
    }
    .faucet-nav .navbar-nav>li>a {
        color: #ffa500;
    }
    .faucet-nav .navbar-brand {
        color: #FF8C00;
    }
    div.hidden
    {
       display: none
    }

    a, a:hover{
      color: #ff6600;
    }
</style>


<div class="navbar navbar-inverse navbar-fixed-top faucet-nav" role="navigation">
<div class="container">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" href="#">Двач кран енотов</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">мой мир<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">огромен</a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">а я<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">так скромен</a></li>
<li><a href="">вся жизнь спекталь</a></li>
<li><a href="">я в ней актер</a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">актер лицедей<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">добряк и злодей</a></li>
<li><a href="">не ради людей</a></li>
<li><a href=""><b>а ради искусства!</b></a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">по жизни<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">играю</a></li>
<li><a href="">я все секреты</a></li>
<li><a href="">ваши знаю</a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">вы в зале<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">сидите</a></li>
<li><a href="">и ваши нервы</a></li>
<li><a href="">словно нити</a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">надежно<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">пришиты</a></li>
<li><a href="">к пальцам</a></li>
<li><a href="">моим!</a></li>
</ul>
</li>

</ul>
</div>
</div>
</div>


<div id="container">

<div class="row">
	<div class="col-md-4 col-md-offset-4" style="margin-top: 4px;">
    <img style="width: 150px; float: left; margin-right: 10px;margin-top:10px;" id="loading" src="loading.gif"> 
<a href="https://2ch.hk/cc/res/559349.html"><img style="width: 150px; float: left; margin-right: 10px;margin-top:10px;" id="logo" src="logo.png"> </a>
	<h1 align="center" style="color: #ffa500;margin-top:50px;">Двач кран енотов</h1>
  <h5 align="center" style="color: #ccc;">bubasik soset koshachu jopy</h5>
<div align="center">

<br>

</div>  
	</div>
</div>

<div class="row" style="margin-top: 25px;">
  <div id="error" class="col-md-4 col-md-offset-4" style="margin-top: 5px; margin-bottom: 5px;"></div>
</div>

<div class="col-md-4 col-md-offset-4" style="margin-bottom: 30px;">
<?php if ($faucettext_3 == ""){ 
	echo '<div class="row">
	<form role="form"  id="faucet" class="hidden">
	  <div class="form-group">
	    <label for="address">Yenten Адрес</label>
	    <input type="address" name="address" class="form-control" id="address" placeholder="Введи свой адресс кошелька">
	  </div>
	   <div class="captcha_wrapper" id="recaptcha">
		<div class="g-recaptcha" data-sitekey="'.$GLOBALS['RPC_RECAPTCHA_SITEKEY'].'"></div>
			<br/>
		</div>
	  <button type="submit" class="btn btn-default" id="form_submit" name="submit">Получить YTN</button>
	  <button type="button" class="hidden" id="page_refresh" name="page_refresh" onclick="window.location.reload()">Обновить</button>
	</form>
	</div>';
} else {
	echo ' <div class="col-md-4 col-md-offset-2" style="">
				<img style="width: 256px; float: left;" id="loading" src="noconnection.gif">
			</div>';
	echo '	<div class="col-md-4 col-md-offset-4" style="margin-left:170px; margin-top: 30px;">
				<button type="button" id="page_refresh_2" name="page_refresh_2" onclick="window.location.reload()">Обновить</button>
			</div>';
}
?>
</div>

</div>

<script>
$(window).load(function () {
    $("#faucet").removeClass("hidden");
    $("#logo").removeClass("hidden");
    $("#loading").addClass("hidden");
});
    $("#logo").addClass("hidden");
    $("#loading").removeClass("hidden");
</script>

<div class="row">
<div class="col-md-6 col-md-offset-3" style=" ">

<h4 align="center"><a href="https://ytn.ccore.online/address/ye2ndkfp53wv6zg5gpnucrdkpdicenbey9" placeholder="Пополнить">
    <?php 
      if ($faucettext_3 == "")
      	echo $faucettext_1.(round ($balance,2)).$faucettext_2;
      else 
      	echo $faucettext_3;
    ?>
 </a></h5>

<h5 align="center">
<a href="https://2chpool.cc/" target="_blank">https://2chpool.cc/</a> (<?php echo date("Y") ?>) </h5>
</div>
</div>
</div>

<script src="faucet.js?ver3"></script>

</body>

</html>

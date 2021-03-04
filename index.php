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
<body style="background:#e3f0fb; margin: 30px; padding-top: 50px;">
<?php
require_once("jsonRPCClient.php");
require_once("server_config.php");

$alt = new jsonRPCClient($GLOBALS["RPC_URL"]);
?>


<style>
.faucet-nav {
    text-shadow: 0 -1px 0 rgba(0,0,0,.15);
    background-color: #3D597C;
    border-color: #5478A5;
    box-shadow: 0 1px 0 rgba(255,255,255,.1);
}
.faucet-nav .navbar-nav>.active>a, .faucet-nav .navbar-nav>.active>a:hover {
    color: #fff;
    background-color: #2F4561;
}
.faucet-nav .navbar-nav>li>a {
    color: #BFE0E3;
}
.faucet-nav .navbar-brand {
    color: #fff;
}
div.hidden
{
   display: none
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
<a class="navbar-brand" href="#">2ch Yenten Faucet</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li class="active"><a href="#">Home</a></li>
<li><a href="">Speed table CPU</a></li>
<li><a href="">О монете</a></li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">How to <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">How to use faucet Yenten Coin — free coins</a></li>
<li><a href="">How to install and setup Yenten Coin wallet on windows</a></li>
<li><a href="">How to install the yiimp pool on the ubuntu 16.04 server and configure yenten coin</a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">Инструкции <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">Кран Yenten Coin как пользоваться, YTN — бесплатная раздача монет</a></li>
<li><a href="">Как установить и настроить кошелек yenten coin в windows</a></li>
<li><a href="">Устанавливаем пул yiimp pool на сервер ubuntu 16.04 и настраиваем yenten coin</a></li>
<li><a href="">Устанавливаем Ununtu live usb на флешку, компилируем майнер cpuminer-opt</a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">Pools for mining<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="yenten-pool.ml"></a></li>
<li><a href="#">...</a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">Forums<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="">Russian Forum</a></li>
<li><a href="">World Forum</a></li>
</ul>
</li>
<li><a target="_blank" href="">Биржа (stock)</a></li>
</ul>
</div>
</div>
</div>


<div id="container">
<div class="row">
<div class="col-md-4 col-md-offset-4" style="font-size: 13px;">
<img style="width: 95px; float: left; margin-right: 10px;" src="logo.png">  
Yenten is a cryptocurrency of the cpu, by the cpu, for the cpu.
<br>No ASIC mineable.
<br>Mining coins at using PC. On any PC! Anyone. 
<br>It's great!
</div>
</div>
<div class="row">
	<div class="col-md-12" style="margin-top: 4px;">
	<h1 align="center">2ch Yenten Faucet</h1>
  <h2 align="center">bubasik soset koshachu jopy</h2>
<div align="center">

<br>

</div>  
	</div>
</div>
<div class="row">
<div id="error" class="col-md-4 col-md-offset-4" style="margin-top: 5px; margin-bottom: 5px;">
</div>
</div>
<div class="row">
<div class="col-md-4 col-md-offset-3" style="margin-top: 25px; margin-bottom: 30px;">

<form role="form"  id="faucet" class="hidden">
  <div class="form-group">
    <label for="address">Yenten Address</label>
    <input type="address" name="address" class="form-control" id="address" placeholder="Введи свой адресс кошелька">
  </div>
   <div class="captcha_wrapper">
	<div class="g-recaptcha" data-sitekey="<?php echo $GLOBALS['RPC_RECAPTCHA_SITEKEY']; ?>"></div>
		<br/>
	</div>
  <button type="submit" class="btn btn-default" name="submit">Submit</button>
  
</form>
</div>

<div align="center" style="text-align: center; float: left;">

<br>

</div>

</div>

<script>
$(window).load(function () {
    $("#faucet").removeClass("hidden");
});
</script>


<div class="row">
<div class="col-md-6" style="margin-top: 30px; ">



</div>
<div class="col-md-6" style="margin-top: 30px; ">



</div>

<div class="row">
<div class="col-md-6 col-md-offset-3" style="margin-top: 30px; ">

<h5 align="center">Faucet Balance: <?php 
$balance = "0.00";
try {
  $balance = $alt->getbalance();
} catch(Exception $e) {
  $balance = "No Connection!";
}
echo($balance);
 ?>
<br><br>

<br><br>
<a href="https://2chpool.cc/" target="_blank">https://2chpool.cc/</a> (<?php echo date("Y") ?>) </h5>
</div>
</div>
</div>

<script src="faucet.js?ver2"></script>

</body>

</html>

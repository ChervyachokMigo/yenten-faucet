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

<body style="background:#eee;">

<?php
  require_once("BaseJsonRpcClient.php");
  require_once("server_config.php");
  
  // получение баланса, проверка на подключение к кошельку
  $RPC = new BaseJsonRpcClient($GLOBALS["RPC_URL"]);
  $balance = 0;
  $faucet_balance = "";
  
  $balance = $RPC->getbalance()->Result;

  function GetTransactionsBalance(){
    try {
      $db = new SQLite3('Transactions.db');
      $db->enableExceptions(true);

      $result = $db->query('SELECT SUM(Amount) FROM Rolls' );
      $sum_amount = $result->fetchArray();
      //разлочка базы
      $db->close();
    } catch (Exception $e){
      //что-то не получилось
      return -1;
    }
      return $sum_amount['SUM(Amount)'] / $GLOBALS['DB_COINS_ACCURACCY'];
  }

  $balanceTransaction = GetTransactionsBalance();

  if ($balance && $balanceTransaction != -1){
    $balance = $balance - $balanceTransaction;
    $faucet_balance = "На кране осталось " . (round ($balance,3)) . " енотов";
  } else {
    $balance = 0;
    $faucet_balance = " Нет соединения!";
  }
  ////////////////////////////////////////////

  // Функция: сравнения по времени:
  // первая больше второй на $seconds, default 5 minutes
  // timestamp_1 ввести чтобы проверить, что 
  //         эта дата больше 300(по умолчанию) секунд назад (или 5 минут)

  function CompareTime($timestamp_1, $timestamp_2 = null, $seconds = 300) {
    if ($timestamp_2 == null) {
      $timestamp_2 = (new DateTime())->getTimestamp();
    }
    return intval( $timestamp_1 >= ($timestamp_2 - $seconds) );  
  }

  // Проверка онлайна на сайте
  $number_online = 0;

  $online_db = new SQLite3('Online.db');

  $results_online_db = $online_db->query('SELECT * FROM WalletsOnline');
    
  while ($online_db_wallet = $results_online_db->fetchArray()) {
    if (CompareTime($online_db_wallet['LastActive'])==1){
      $number_online ++;
    } else {
      $online_db->query( 'DELETE FROM WalletsOnline WHERE id='.$online_db_wallet['id'] );
    }
  }
  $online_db->close();
  ///////////////////////////////////////////

?>


<style>
    .faucet-nav {
        text-shadow: 0 1px 1px rgba(0,0,0,.3);
        box-shadow: 0 1px 0 rgba(255,165,0,.2);
        background-color: #00a2e8;
        border-color: #ffa500;
        
    }
    .btn{
    	background-color: #00a2e8;
    	width: 124px;

    }
    .btn:hover,.btn:focus{
    	color:white;
    	background: #0202da;
    	font-weight: bold;
    	
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

    .alert {
    	margin-bottom: 0px;
    }
    .alert-success {
    	background-color: #DDD;
    }
    a{
    	outline:0;
    	color: #ff6600;
    	text-decoration:none !important;
    }
    a:visited{
    	color: #ff6600;
    }
    a:active, a:hover{
    	color: #a74300;
    }

    .faucet_block{
    	margin-left:auto; 
    	margin-right: auto; 
    	margin-top: 15px;
    	width:fit-content;
    }
</style>


<div class="navbar navbar-inverse navbar-fixed-top faucet-nav" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="http://2ch-yenten-faucet.ml/#">Двач кран енотов</a>
		</div>
	</div>
</div>

<div class="container" role="main">
	<div class="jumbotron" style="padding-top:80px;padding-bottom: 0;margin-bottom: 10px;">
		
			<div style="display: grid;grid-template-columns: 150px 250px; width: 410px;margin:0;margin-right:auto;margin-left:auto;">

				<div style="width:min-content;margin:0px;">

				    <img id="loading" width="150px" height="150px" src="loading.gif"> 
					<a href="https://2ch.hk/cc/res/559349.html">
						<img width="150px" height="150px" id="logo" src="logo.png" > 
					</a>

				</div>
				<div style="width:max-content;margin:0px;margin-left:10px;margin-top:30px;">

					<h2 class="display-4 text-nowrap" style="color: #ffa500;width:max-content;">Двач кран енотов</h2>
				  	<h6 align="center" class=" text-nowrap" style="color: #ccc;width:max-content;margin:auto;">bubasik soset koshachu jopy</h6>

			  	</div>

			</div>
		
		<div class="row" style="margin-top: 25px;">
		  <div id="error"  style="margin-top: 5px; margin-bottom: 5px; margin-left:auto; margin-right: auto; width:410px;"></div>
		</div>

	</div>

	<div class="faucet_block" style="margin-bottom: 30px;">
		<?php 
			if ($balance){ 
			echo '
			<div >
				<form role="form"  id="faucet" class="hidden" novalidate method="POST" style="width:380px;width:fit-content;">
					<div id="adress_block" style="width:380px;margin-bottom: 15px;" >
						<label for="address">Yenten Адрес</label>
						<input type="address" name="address" class="form-control" id="address" 
							maxlength="34" required 
							placeholder="Введи свой адрес кошелька">
					</div>
					<div class="captcha_wrapper" id="recaptcha">
						<div class="g-recaptcha" 
							data-callback="imNotARobot"
							data-expired-callback="recaptcha_expiried"
							data-sitekey="'.$GLOBALS['RPC_RECAPTCHA_SITEKEY'].'"></div>
						
					</div>
					<button type="submit" class="btn btn-primary" style="margin-top:13px;" id="form_submit" name="submit">Получить</button>
				</form>
				<button type="button" class="btn faucet_block btn-primary hidden" id="page_refresh" name="page_refresh" onclick="window.location.reload()">Обновить</button>
			</div>';
			} else {
				echo '  <div class="faucet_block" style="">
							<img style="width: 256px; " id="loading" src="noconnection.gif">
						</div>';
				echo '	<div class="faucet_block refresh_button_2" style="margin-top: 30px;">
							<button type="button" class="btn btn-primary" id="page_refresh_2" name="page_refresh_2" onclick="window.location.reload()">Обновить</button>
						</div>';
			}
		?>
	</div>

<script>
  $("#logo").addClass("hidden");
  $("#loading").removeClass("hidden");
</script>

<div class="faucet_block" id="faucet_footer">

<h4 align="center"><a href="https://2chpool.cc/workers/Ye2NDKfp53WV6zG5GPnuCRdkPDicenBEY9" placeholder="Пополнить">
    <?php 
      	echo $faucet_balance;
    ?>
 </a></h4>

<h6 align="center">
  <?php     

   $all_max = $all_max / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];
   $all_min = $all_min / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];

   echo "Возможные выигрыши: ".$all_min." - ".$all_max." енотов";

   ?>
</h6>

<h5 align="center">
	<a href="https://2chpool.cc/" target="_blank">https://2chpool.cc/</a> (<?php echo date("Y"); ?>) 
</h5>
<h6 align="center">
  Сейчас на сайте <?php 
    if ($number_online==0) {
      echo 'никого.';
    } else {
      echo $number_online.' '; 
    }

    if ( $number_online == 1 || ($number_online > 20 && $number_online % 10 == 1) ) 
      echo 'енотоман.';
    if ( $number_online >= 2 && $number_online <=4 
        || ($number_online > 20 && ($number_online % 10 >= 2 && $number_online % 10 <= 4) ) ) 
      echo 'енотомана.';
    if ( $number_online >= 5 && $number_online <=20 
        || ($number_online > 20 && ($number_online % 10 >= 5 && $number_online % 10 <= 9 || $number_online % 10 == 0) ) ) 
      echo 'енотоманов.';
  ?> 
</h6>

<h6 align="center">
  * Выигрыши будут выплачены при достижении накоплений в <?php echo $GLOBALS["PAYOUT_LIMIT"]; ?> енотов или при выигрыше.
</h6>

</div>

</div>

<script src="faucet.js?random=<?php echo time(); ?>"></script>

</body>

</html>

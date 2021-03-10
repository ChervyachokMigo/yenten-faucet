<!doctype html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Двач кран енотов - 2ch-yenten-faucet.ml</title>
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
  
  $last_thread_link = 'https://2ch.hk/cc/res/564347.html';

  // получение баланса, проверка на подключение к кошельку
  $RPC = new BaseJsonRpcClient($GLOBALS["RPC_URL"]);
  $balance = 0;
  $faucet_balance = "";
  
  // баланс кошелька
  $balance = $RPC->getbalance()->Result;

  $RPC = null;


  // соединение с базой
  $db = mysqli_connect( $GLOBALS['MYSQL_HOST'].":".$GLOBALS['MYSQL_PORT'] , $GLOBALS['MYSQL_USER'] , $GLOBALS['MYSQL_PASSWORD'] );

  if ($db->connect_error) {
    error_log('(index.php) Ошибка подключения (' . $db->connect_errno . ') '. $db->connect_error);
  }

  if ($db){

      if ( ! mysqli_select_db( $db , $GLOBALS['MYSQL_DB'] ) ){
        error_log("DB not found");
        die("DB not found");
      }

      //определение баланса неоплаченых транзакций
      $Transactions_now = GetTransactionsBalance( $db );

      if ($Transactions_now != -1) {
        $balanceTransactions = $Transactions_now['SumAmount'];

        //echo $balanceTransactions;
        // определение пени с транзакций
        $feeTransactions = $Transactions_now['Count'] * $GLOBALS['FEE_AMOUNT'];

        // определение баланса, при котором уже не выводить форму
        $EmptyBalanceAt = ( $all_max / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] + $feeTransactions + $balanceTransactions );

        // вывод баланса с учетом неоплаченых выплат
        if ($balance && $balanceTransactions != -1){
          $balanceOut = $balance - $balanceTransactions - $feeTransactions;
          if ($balance > $EmptyBalanceAt ){
            $faucet_balance = "На кране осталось <div id=\"div_balance\" style=\"display:inline-block\">" . (round ($balanceOut,3)) . "</div> енотов **";
          } else {
            $balance = 0;
            $faucet_balance = "На кране не осталось енотов **";
          }
        } else {
          $balance = 0;
          $faucet_balance = "Раздача закончена!";
        }

         // Проверка онлайна на сайте
        $number_online = 0;

        $results_online_db = mysqli_query( $db , 'SELECT * FROM walletsonline' );
          
        if ($results_online_db) {
          while ( $online_db_wallet = mysqli_fetch_array( $results_online_db, MYSQLI_ASSOC ) ) {
            if ( CompareTime( $online_db_wallet['LastActive'] ) == 1 ){
              $number_online ++;
            } else {
              // удалить из таблицы онлайна всех, кто не посылал успешные запросы больше 5 минут
              mysqli_query( $db , 'DELETE FROM walletsonline WHERE ID = '.$online_db_wallet['ID'] );
            }
          }
          
          mysqli_free_result($results_online_db);
          $online_db_wallet = null;
      }

        ///////////////////////////////////////////

      } else {
        $EmptyBalanceAt = 1;
        $balance = 0;
        $faucet_balance = "На кране не осталось енотов **";

        $number_online = 0;
      }
  }

/////////////// функции ////////////////

function GetTransactionsBalance(&$db){
  try {
    //сумма неоплаченых роллов (накоплено)
    $result_1 = mysqli_query( $db , 'SELECT SUM(Amount) as this FROM rolls' );
    
    if ($result_1) {
      $sumAmount_res = mysqli_fetch_array($result_1 , MYSQLI_ASSOC);
      mysqli_free_result($result_1);
    } else {
      $sumAmount_res['this'] = 0;
    }

    //количество неоплаченых юзеров с накоплениями
    $result_2 = mysqli_query( $db , 'SELECT COUNT ( DISTINCT Wallet ) as this FROM rolls' );
    if ($result_2) {
      $countToPayout_res = mysqli_fetch_array($result_2 , MYSQLI_ASSOC);
      mysqli_free_result($result_2);
    } else {
      $countToPayout_res['this'] = 0;
    }

    //количество неоплаченных (ошибочных) транзакций
    $result_3 = mysqli_query( $db , 'SELECT COUNT () as this FROM rollsarchive WHERE TransactionID = \'\'' );
    if ($result_3) {
      $notPayedCount_res = mysqli_fetch_array($result_3 , MYSQLI_ASSOC);
      mysqli_free_result($result_3);
    } else {
      $notPayedCount_res['this'] = 0;
    }

    //сумма неоплаченных (ошибочных) транзакций
    $result_4 = mysqli_query( $db , 'SELECT SUM(SumAmount) as this FROM rollsarchive WHERE TransactionID = \'\'' );
    if ($result_4) {
      $notPayedAmount_res = mysqli_fetch_array($result_4 , MYSQLI_ASSOC);
      mysqli_free_result($result_4);
    } else {
      $notPayedAmount_res['this'] = 0;
    }

  } catch (Exception $e){
    //что-то не получилось
    return -1;

  } catch (mysqli_sql_exception $e) {
     // throw $e;
    return -1;

  }

    $res['SumAmount'] = ( $sumAmount_res['this'] + $notPayedAmount_res['this'] ) / $GLOBALS['DB_COINS_ACCURACCY'];
    $res['Count'] = $countToPayout_res['this'] + $notPayedCount_res['this'];

    return $res;

}

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
			<a class="navbar-brand" href=<?php echo '"' . $last_thread_link . '"' ;?> >Двач кран енотов</a>
		</div>
	</div>
</div>

<div class="container" role="main">
	<div class="jumbotron" style="padding-top:80px;padding-bottom: 0;margin-bottom: 10px;">
		
			<div style="display: grid;grid-template-columns: 150px 250px; width: 410px;margin:0;margin-right:auto;margin-left:auto;">

				<div style="width:min-content;margin:0px;">

				    <img id="loading" width="150px" height="150px" src="loading.gif"> 
					<a href=<?php echo '"' . $last_thread_link . '"' ;?> >
						<img width="150px" height="150px" id="logo" src="logo.png" > 
					</a>

				</div>
				<div style="width:max-content;margin:0px;margin-left:10px;margin-top:30px;">

					<h2 class="display-4 text-nowrap" style="color: #ffa500;width:max-content;">Двач кран енотов</h2>
				  	<h6 align="center" class=" text-nowrap" style="color: #ccc;width:max-content;margin:auto;">бетатест</h6>

			  	</div>

			</div>
		
		<div class="row" style="margin-top: 25px;">
		  <div id="error"  style="margin-top: 5px; margin-bottom: 5px; margin-left:auto; margin-right: auto; width:410px;"></div>
		</div>

	</div>

	<div class="faucet_block" style="margin-bottom: 30px;">
		<?php 

			if ( $db && $balance > $EmptyBalanceAt ){ 
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
  							data-sitekey="i_love_potato"></div>
  						
  					</div>
  					<button type="submit" class="btn btn-primary hidden" style="margin-top:13px;" value="1" id="form_submit_2" name="submit">Получить</button>
            </form>';

            $randomButton = random_int(1,9);

            echo '<input type="hidden" value="'.$randomButton.'" id="checkRandomButton">';

            for ($i = 1; $i<10; $i++){
              echo '<button type="button" class="btn btn-primary hidden submit-btn" style="margin-top:13px;" value="'.$i.'" name="get">Получить</button>';
            }

  				echo'<button type="button" class="btn faucet_block btn-primary hidden" id="page_refresh" name="page_refresh" onclick="window.location.reload()">Обновить</button>
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
  $("#recaptcha > div").attr('data-sitekey', <?php echo "'".$GLOBALS['RPC_RECAPTCHA_SITEKEY']."'"; ?> );
</script>

<div class="faucet_block" id="faucet_footer">

<h4 align="center" ><a href="https://2chpool.cc/workers/Ye2NDKfp53WV6zG5GPnuCRdkPDicenBEY9">
    <?php 
      	echo $faucet_balance;
    ?>
 </a></h4>

<h6 align="center">
  <?php     

   $all_max_out = $all_max / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];
   $all_min_out = $all_min / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];

   echo "Возможные выигрыши: ".$all_min_out." - ".$all_max_out." енотов";

   ?>
</h6>

<h5 align="center">
	<a href="https://2chpool.cc/" target="_blank">https://2chpool.cc/</a> (<?php echo date("Y"); ?>) 
</h5>
<h6 align="center">
  Сейчас на сайте <?php 
    if (!$db || $number_online==0) {
      echo 'никого.';
    } else {
      echo $number_online.' '; 
    
    if ( $number_online == 1 || ($number_online > 20 && $number_online % 10 == 1) ) 
      echo 'енотоман.';
    if ( $number_online >= 2 && $number_online <=4 
        || ($number_online > 20 && ($number_online % 10 >= 2 && $number_online % 10 <= 4) ) ) 
      echo 'енотомана.';
    if ( $number_online >= 5 && $number_online <=20 
        || ($number_online > 20 && ($number_online % 10 >= 5 && $number_online % 10 <= 9 || $number_online % 10 == 0) ) ) 
      echo 'енотоманов.';
    }
  ?> 
</h6>

<h6 align="center">
  * Выигрыши будут выплачены при достижении накоплений в <?php echo $GLOBALS["PAYOUT_LIMIT"]; ?> енотов или при выигрыше.
</h6>

<h6 align="center">
  ** Когда закончится баланс, накопления и ошибки будут обработаны в ручном режиме, спустя какое-то время!
</h6>
  <div id="player"></div>

</div>

</div>


<script src="faucet.js?random=<?php echo time(); ?>"></script>


<?php if ($db) mysqli_close ($db) ?>

</body>

</html>

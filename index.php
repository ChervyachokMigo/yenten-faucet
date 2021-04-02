<!doctype html>
<html>

<head>
	<meta charset="UTF-8">
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
  <meta http-equiv="Content-Language" content="ru">
  <meta name="robots" content="index">
	<title>Двач кран енотов - yenten.top</title>
  <meta name="description" content="Лучший кран, раздаюющий йентены (YTN), созданый аноном с двача. Будешь сосать?">
  <meta name="keywords" content="кран енотов, кран yenten coin, yenten faucet, кран йентенов, yenten, ytn faucet, двач кран енотов, 
  кран йентенов двача, криптовалюта, биткоин, догикоин, dogecoin, bitcoin, 2ch, харкач, анимекоин, animecoin, anime, cpu coin, cpu майнинг, 
  cpumining, cryptocurency, yespowerr16, ytn, elon musk, илон маск, двач пул, Official Dvach, самый известный кран Yenten, халява в интернете,
  криптовалюта для процессора, tesla, btc, blockchain, ethereum , eth, двач, карасик, майнинг, ltc, crane coin, bitcoin newscran, satoshi,
  binarium coin, как майниить криптовалюту, майнинг на процессоре, заработок в интернете, cryptocoin CPU-mining only, курс yenten, курс ytn,
  Lucky Pool, cpu pool, zerg pool, 2ch pool, yenten pool, rplant, supernova, aikapool, nlpool, mining dutch, zpool, yentencoin, ytn mining, 
  yespower, proof of work, yescrypt, sugarchain, cpuchain, goldchain, uraniumx, yenten roadmap, intel core, intel, amd, ryzen, threadripper,
  xeon, jayddee, conan, аниме тян, hashrate, электронные деньги, виртуальные деньги, криптокошелек, электронный кошелек, wallet, майнер, 
  bit coin talk, bits media, 2chpool, ентен, monero, рулетка, геймблинг, roll, gambling, YENTEN Official rus, yenten my waifu, yenten waifu">
	<link rel="stylesheet" href="bootstrap.min.css">
	<script src="jquery.min.js"></script>
	<script src="bootstrap.min.js"></script>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <link rel="icon" href="favicon.ico">
  <link rel="canonical" href="http://yenten.top/"/>
  <meta name="viewport" content="width=device-width, initial-scale=0.57">
  <script data-ad-client="ca-pub-2424868844068150" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
</head>

<body style="background:#eee;">

<?php
  require_once("BaseJsonRpcClient.php");
  require_once("server_config.php");  
  require_once("DB_functions.php");

  $last_thread_link = $GLOBALS["2CH_THREAD_LINK"];

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

      $db->query("SET NAMES utf8mb4");
      $db->query("SET CHARACTER SET utf8mb4");
      $db->set_charset('utf8mb4');

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
            $faucet_balance = "На кране осталось <div id=\"div_balance\" style=\"display:inline-block\">" . ( number_format ( round ( $balanceOut , 2 ) , 2 ) ) . "</div> енотов";
          } else {
            $balance = 0;
            $faucet_balance = "На кране не осталось енотов";
          }
        } else {
          $balance = 0;
          $faucet_balance = "Нет связи с кошельком!";
        }

         // Проверка онлайна на сайте

        $number_online = GetOnlineCount( $db , 0 );
        ///////////////////////////////////////////

      } else {
        $EmptyBalanceAt = 1;
        $balance = 0;
        $faucet_balance = "На кране не осталось енотов";

        $number_online = 0;
      }

      $topCapchers = GetTopCapchers( $db );

      $Winners = GetLastWinners( $db );

      if (isset($_POST['winform_submit']) && isset($_POST['WinID']) && isset($_POST['win_comment']) ){
        if (strlen($_POST['winform_submit']) == 6){
          $_POST['winform_submit'] = preg_replace( "/[^a-z\s]/", '', $_POST['winform_submit'] );
          if ($_POST['winform_submit'] == 'submit'){
            if (strlen($_POST['WinID'])<=32 && strlen($_POST['WinID'])>=16){
              $_POST['WinID'] = preg_replace( "/[^a-zA-Z0-9\s]/", '', $_POST['WinID'] );
              if (strlen($_POST['win_comment'])>300){
                $_POST['win_comment'] = substr($_POST['win_comment'], 0, 300);
              }

              AddCommentToWinner( $_POST['WinID'] , $_POST['win_comment'] , $db);
              
              header("Location: /");
            }
          }
        }
      }

  }


?>

<link rel="stylesheet" href="styles.css">


<?php include("menudiv.php");?>

<div class="btn-group dropleft top_capchers">
  <div class="dropdown-menu">
    <div class="capchasoses">
      <?php
        $colors = array('red','orange','olive','lime','green','aqua','blue','navy','teal','fuchsia','purple','maroon');
        if ( count($topCapchers)>0 ){
          $i = 0;
          foreach ( $topCapchers as $topCapchearsEach){
            echo '<div class="topcapcher_item dropdown-item list-group-item">';
            if ($i==0){
              echo  '<div class="topcapcher_name" id="topcapcher_first">';
            } else {
              echo  '<div class="topcapcher_name" style="color:'. $colors[ rand( 0, count($colors)-1 ) ] .';">';
            }
            echo  $topCapchearsEach['Name'].'</div>'.
              '<div class="topcapcher_capchas">'.
              $topCapchearsEach['AllNumberCaptcha'].'</div>'.
            '</div>';
            $i++;
          }
        }
      ?>
      </div>
    </div>
  <button type="button" class="right_panel_btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Топ капчесосов
  </button>
</div>

<div class="btn-group dropleft winners_btn">
  <div class="dropdown-menu">
    <div class="winners">
      <?php
        if ( count($Winners)>0 ){
          foreach ( $Winners as $WinnersEach){
            echo '<div class="winners_container">';
            $colors = array('red','orange','olive','lime','green','aqua','blue','navy','teal','fuchsia','purple','maroon');
            echo '<div class="winners_item dropdown-item list-group-item">';
            
            echo  '<div class="winners_name" style="color:'. $colors[ rand( 0, count($colors)-1 ) ] .';">';
            echo  $WinnersEach['Name'].'</div>';

            echo '<div class="winners_amount">'.number_format($WinnersEach['Amount']/$GLOBALS['DB_COINS_ACCURACCY'],2).'</div>';

            $WinnersEach['Commentary'] = htmlspecialchars($WinnersEach['Commentary'],ENT_HTML5,"UTF-8");
            $WinnersEach['Commentary'] = str_replace(array('^a1','^a2','^a3'), array('<a href="','">','</a>'),  $WinnersEach['Commentary']);
            $WinnersEach['Commentary'] = str_replace(array('^img1','^img2'), array('<img src="','">'),  $WinnersEach['Commentary']);

            if (strpos($WinnersEach['Commentary'], 'спасибо')!==false || strpos($WinnersEach['Commentary'], 'Спасибо')!==false || strpos($WinnersEach['Commentary'], 'spasibo')!==false || strpos($WinnersEach['Commentary'], 'Spasibo')!==false  ){
              $WinnersEach['Commentary'].= '<br><span style="color: red;display: block;padding-top: 10px;">Абу благословил этот пост!</span>';
            }

            echo  '<div class="winners_commentary">'.$WinnersEach['Commentary'].'</div>';
            
            echo '</div>';

            echo '</div>';
          }
        }
      ?>
      </div>
    </div>
  <button type="button" class="right_panel_btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Выигрыши
  </button>
</div>


<div class="container" role="main">
	<div class="jumbotron" style="padding-top:80px;padding-bottom: 0;margin-bottom: 10px;">
		
			      <?php include ("title_logo.php")?>
		
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

<h4 align="center" ><a href="https://2chpool.cc/workers/Ye2NDKfp53WV6zG5GPnuCRdkPDicenBEY9" title="Оживить">
    <?php 
      	echo $faucet_balance;
        if ($db){
            echo '<div id="wasted">Потрачено '. number_format( round (GetWasted ( $db ) , 2 ) , 2 ) .' енотов</div>';
        }
    ?>
 </a></h4>
 

<h6 align="center" style="margin:0;">
  <?php     

   $all_max_out = $all_max / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];
   $all_min_out = $all_min / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];

   echo "Возможные роллы: ".number_format( round( $all_min_out , 4 ) , 4 )." - ".number_format( round( $all_max_out , 2 ) , 2 )." енотов ";
   echo '<button id="rolls_help" class="rolls_help btn-info" data-html="true" title="'.
   'Ролл — это число которое случайно выпадает. Может выпасть от '.($GLOBALS["PAYOUT_MIN"]/ $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]).' до '.($GLOBALS["PAYOUT_MAX"]/ $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]).' (для удобства пишется целое число) <br>'.
   'Ролл умножается на бонусы за '.
   'Мультикаст (x'.$GLOBALS["PAYOUT_MULTICAST_MAX"].'), '.
   'Удачу (x'.$GLOBALS["PAYOUT_LUCKY_MULTIPLIER"].'), '.
   'Невероятную удачу (x'.$GLOBALS["PAYOUT_RARE_MULTIPLIER"].'), '.
   'Капчу (x'.$GLOBALS["PAYOUT_CAPTCHA_MAX_MULTIPLIER"].'), '.
   'Онлайн капчесосов (x'.$GLOBALS["PAYOUT_MAX_MULTIPLIER_PER_HUMAN"].') на сайте и '.
   'Множитель курса (x'.number_format( $payout_course_multiplier, 2).')'.
   '">?</button>';

   ?>
</h6>

<h6 align="center" style="color: #ccc;margin:0;">
  * Автовывод накоплений при достижении в <?php echo $GLOBALS["PAYOUT_LIMIT"]; ?> енотов или при выигрыше.<br>
</h6>

<h6 align="center">
  Сейчас на кране <?php 
    if (!$db || $number_online==0) {
      echo 'никого';
    } else {
      echo $number_online.' '; 
    
      if ( $number_online == 1 || ($number_online > 20 && $number_online % 10 == 1) ) 
        echo 'енотоман ';
      if ( $number_online >= 2 && $number_online <=4 
          || ($number_online > 20 && ($number_online % 10 >= 2 && $number_online % 10 <= 4) ) ) 
        echo 'енотомана ';
      if ( $number_online >= 5 && $number_online <=20 
          || ($number_online > 20 && ($number_online % 10 >= 5 && $number_online % 10 <= 9 || $number_online % 10 == 0) ) ) 
        echo 'енотоманов ';
      echo '(x' . number_format( round( GetHumansNumberMultiplier($db) , 2 ) , 2 ). ')';
    }
   
  ?> 
</h6>

</div>

</div>

<h5 align="center" style="margin:0;">
  <a href="https://github.com/ChervyachokMigo/2ch-yenten-faucet" target="_blank" title="Исходники">Кран на github</a> (<?php echo date("Y"); ?>) 
</h5>

<h5 align="center">
  <a href="mailto:yenten.top@gmail.com">Для пожеланий и предложений</a>
</h5>

<script src="faucet.js"></script>
<script>$('#rolls_help').tooltip();</script>

<?php if ($db) mysqli_close ($db) ?>

</body>

</html>

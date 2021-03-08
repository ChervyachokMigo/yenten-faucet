<?php
require_once("server_config.php");

$GLOBALS['DB_COINS_ACCURACCY'] = 100000;

if (isset($_POST["g-recaptcha-response"])){
	$response = htmlspecialchars($_POST["g-recaptcha-response"]);
	if (strlen($response)>1000){
	  	$response = substr ( $response  , 0 , 1000 );
	}
	$username = htmlspecialchars($_POST['address']);
	if (strlen($username)>34){
	  	$username = substr ( $username  , 0 , 34 );
	}

	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => $GLOBALS['RPC_RECAPTCHA_SECRETKEY'],
		'response' => $response
	);

	$options = array(
			'http' => array (
	        'header' => "Content-Type: application/x-www-form-urlencoded;".
                    "User-Agent:MyAgent/1.0; Access-Control-Allow-Origin: *;",
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);

	$context  = stream_context_create($options);
	$verify = @file_get_contents($url, false, $context);
	$captcha_success=json_decode($verify);

} else {
	$errors['human'] = 'Неправильная капча.';
	$data['errors'] = true;
	$data['errors']  = $errors;
	echo json_encode($data);
	die;
}
if ($captcha_success->success==false) {
	$errors['human'] = 'Неправильная капча.';
	$data['errors'] = true;
	$data['errors']  = $errors;
	echo json_encode($data);
	die;

} elseif ($captcha_success->success==true) {
	require_once("BaseJsonRpcClient.php");

    $RPC = new BaseJsonRpcClient($GLOBALS["RPC_URL"]);

    // выполнение подсчетов выплат
    include 'calc.php';
      		
    $check = $RPC->validateaddress($username)->Result;

	if($RPC->getbalance()->Result < $payout_yentens){
		$errors['balance'] = 'Кран пуст.';
		$data['errors'] = true;
		$data['errors']  = $errors;
		echo json_encode($data);
  		die;
	} else {
      	if ( $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] <=  $all_max || 
      		  $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] >= $all_min ) {

            if($check['isvalid'] == 1){
            	//добавляем в базу или выплачиваем
            	$AddOrPayResults = AddOrPayYentens($username, $payout_yentens * $GLOBALS['DB_COINS_ACCURACCY']);
            	if ($AddOrPayResults['error'] == 0){
	            	//записываем юзера онлайн на 5 минут
	            	SetWalletOnline($username);
	            	
					$data['success'] = true;
	    
					$data['boa'] = 	'Вы получили <a href="http://2ch-yenten-faucet.ml/#">' . 
									round($payout_yentens,4) . 
									"</a> енотов!<br>" .
									"Выпало: " . $roll . "<br>" . 
									"Мультикаст: " . round($multi,1) . "x<br>" .
									"Удача: " . $chance . "%";

					if ($lucky_multi>1) {
						$data['boa'] .= " (x" . $lucky_multi . "!)";
					}

					$data['boa'] .='<br>';

					if ($isRare == 1){
						$data['boa'] .= "Невероятная удача!!! (х" . $rare_multiplier . ") <br>";
					}

					if ($AddOrPayResults['Sended']==0){
						$data['boa'] .= "<h6>Отправлено в накопления.<br>" .
										"Накоплено: <a href=\"http://2ch-yenten-faucet.ml/#\">" . 
										round($AddOrPayResults['SumAmount'],2) . 
										"</a> енотов</h6><br>";
					} else {
						if ($AddOrPayResults['Sended']==2) $data['boa'] .= "<h4>Вы выиграли!</h4><br>";

						// айди транзакции
						$data['boa'] .= "<h4>Выплачено <a href=\"http://2ch-yenten-faucet.ml/#\">" . 
										round($AddOrPayResults['SumAmount'],2) . 
										"</a> енотов!</h4><br>";
						// отсылаем монеты на адрес
		        	    if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
		        			$e1 = $RPC->walletpassphrase( $GLOBALS["WALLET_PASS_PHRASE"], 60 ); }
		        			
		                //$transucktion_id = $RPC->sendtoaddress($username, $payout_yentens)->Result;
		        		error_log( "Pay to " . $username . " yentens " . $AddOrPayResults['SumAmount'] );

						if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
							$RPC->walletlock();	}
					}
					echo json_encode($data);
					die;
				} else {
					$errors['human'] = 'Лупа и Пупа пошли получать зарплату. В бухгалтерии все перепутали. В итоге, Лупа получил за Пупу, а Пупа за Лупу!';
					$data['errors'] = true;
					$data['errors']  = $errors;
					echo json_encode($data);
					die;
				}
			} else {	// check valid
            		$errors['address'] = 'Неправильный адрес.';
  					$data['errors'] = true;
  					$data['errors']  = $errors;
  					echo json_encode($data);
           			die;
      		}
      	} else {	// payment больше или меньше допустимого по каким-то причинам
      		$errors['human'] = 'Лупа и Пупа пошли получать зарплату. В бухгалтерии все перепутали. В итоге, Лупа получил за Пупу, а Пупа за Лупу!';
			$data['errors'] = true;
			$data['errors']  = $errors;
			echo json_encode($data);
      	}
    }	// конец: кран не пустой

}	// end capcha success=true

function debugarr($arr){
	return "<pre>".print_r($arr)."</pre>";
}

function SetWalletOnline($Wallet){
	$Wallet = '"'.$Wallet.'"';
	$db = new SQLite3('Online.db');
	$date_now = new DateTime();
	$db->query('
    REPLACE INTO WalletsOnline ( Wallet, LastActive) 
    VALUES ( '.$Wallet.', '.($date_now->getTimestamp()).') ' );
    $db->close();
}

function AddOrPayYentens($Wallet, $payout_amount){
	try {
	$Wallet_tosql = '"'.$Wallet.'"';
	$payout_amount = intval($payout_amount);
	$db = new SQLite3('Transactions.db');
	$db->enableExceptions(true);
	$db->query('
    INSERT INTO Rolls ( Wallet, Amount) 
    VALUES ( '.$Wallet_tosql.', '.$payout_amount.') ' );

	$sum_amount_result = $db->query( 'SELECT Amount,ID FROM Rolls WHERE Wallet = ' . $Wallet_tosql );

	$IDs = Array();
	$SumAmount = 0;
	while ($rolls_amount = $sum_amount_result->fetchArray()) {
		$IDs[] = $rolls_amount['ID'];
		$SumAmount += $rolls_amount['Amount']; 
	}

	$SumAmount = $SumAmount / $GLOBALS['DB_COINS_ACCURACCY'];
	
	$isNeedPayout = $SumAmount > $GLOBALS["PAYOUT_LIMIT"];
	$isWinner = ($payout_amount / $GLOBALS['DB_COINS_ACCURACCY']) > $GLOBALS["PAYOUT_AUTOPAY_LIMIT"];
	if ( $isNeedPayout || $isWinner ){
		$Delete_IDs = implode(',', $IDs);
		$db->query('DELETE FROM Rolls WHERE ID in(' . $Delete_IDs . ')');
	} else {
		$result['Sended'] = 0;
	}
    $db->close();
    $result['error'] = 0;
    if ($isNeedPayout) $result['Sended'] = 1;
	if ($isWinner) $result['Sended'] = 2;
    $result['SumAmount'] = $SumAmount;
	} catch (Exception $e){
		$result['error'] = 1;
		return $result;
	}
    return $result;
}
	
?>
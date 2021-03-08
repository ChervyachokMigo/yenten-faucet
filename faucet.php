<?php
require_once("server_config.php");

$GLOBALS['DB_COINS_ACCURACCY'] = 100000;
$executionTime = (new DateTime())->format('Y-m-d H:i:s');

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
	error_log( "[". $executionTime . "] ERROR: #-2 - No sended captcha. \n" );
	echo json_encode($data);
	die;
}
if ($captcha_success->success==false) {
	$errors['human'] = 'Неправильная капча.';
	$data['errors'] = true;
	$data['errors']  = $errors;
	error_log( "[". $executionTime . "] ERROR: #-1 - Incorrect captcha validation. \n" );
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
		error_log( "[". $executionTime . "] ERROR: #0 - Wallet is down. \n" );
		echo json_encode($data);
  		die;
	} else {
      	if ( $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] <=  $all_max || 
      		  $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] >= $all_min ) {

            if($check['isvalid'] == 1){
            	//debug
	            $payout_yentens = 2.6;

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

					try{
						if ($AddOrPayResults['Sended']==0){
							$data['boa'] .= "<h6>Отправлено в накопления.<br>" .
										"Накоплено: <a href=\"http://2ch-yenten-faucet.ml/#\">" . 
										round($AddOrPayResults['SumAmount'],2) . 
										"</a> енотов</h6><br>";
						} else {
							if ($AddOrPayResults['Sended']==2) $data['boa'] .= "<h4>Вы выиграли!</h4><br>";

							// отсылаем монеты на адрес
			        	   
			        	     if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
			        			$e1 = $RPC->walletpassphrase( $GLOBALS["WALLET_PASS_PHRASE"], 60 ); }
			        		
			                $transucktion_id = $RPC->sendtoaddress($username, $AddOrPayResults['SumAmount'])->Result;

			                if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
								$RPC->walletlock();	}

							$data['boa'] .= "<h4>Выплачено <a href=\"https://ytn.ccore.online/transaction/utxo/".  
											$transucktion_id ."\">" . round($AddOrPayResults['SumAmount'],2) . 
											"</a> енотов!</h4><br>";
							
							//записываем в базу отправленные монеты
			                $Transaction_error = SetTransactionID( $transucktion_id, $AddOrPayResults['RollArchiveID']);
			                if ($Transaction_error == 1){
			                	$errors['human'] = 'Бубасик украл твои монеты.';
								$data['errors'] = true;
								$data['errors']  = $errors;
								error_log( "[". $executionTime . "] ERROR: #5 - Can't Set Transaction ID ". $transucktion_id . " by " .  $username . "\n" );
								echo json_encode($data);
								die;
			                } else {
			        			error_log( "[". $executionTime . "] SUCCESS: Pay to " . $username . 
			        				" yentens " . $AddOrPayResults['SumAmount'] . 
			        				" transaction: " . $transucktion_id . "\n" );
			        		}
						}
						// отправка успешного сообщения
						echo json_encode($data);
						die;
					} catch (Exception $e){
						$errors['human'] = 'Бубасик украл твои монеты.';
						$data['errors'] = true;
						$data['errors']  = $errors;
						error_log( "[". $executionTime . "] ERROR: #4 - Can't Send ". $payout_yentens . " Yentens to " .  $username . "\n" );
						echo json_encode($data);
						die;
					}
				} else {
					$errors['human'] = 'Лупа и Пупа пошли получать зарплату. В бухгалтерии все перепутали. В итоге, Лупа получил за Пупу, а Пупа за Лупу!';
					$data['errors'] = true;
					$data['errors']  = $errors;
					error_log( "[". $executionTime . "] ERROR: #3 - Can't connect to DB to add or backup payout ". $payout_yentens . " Yentens to " .  $username . "\n" );
					echo json_encode($data);
					die;
				}
			} else {	// check valid
            		$errors['address'] = 'Неправильный адрес.';
  					$data['errors'] = true;
  					$data['errors']  = $errors;
  					error_log( "[". $executionTime . "] ERROR: #2 - Enter incorrect address \n" );
  					echo json_encode($data);
           			die;
      		}
      	} else {	// payment больше или меньше допустимого по каким-то причинам
      		$errors['human'] = 'Лупа и Пупа пошли получать зарплату. В бухгалтерии все перепутали. В итоге, Лупа получил за Пупу, а Пупа за Лупу!';
			$data['errors'] = true;
			$data['errors']  = $errors;
			error_log( "[". $executionTime . "] ERROR: #1 - Check server_config.php \n" );
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
	// подготовка
	$Wallet_tosql = '"'.$Wallet.'"';
	$payout_amount = intval($payout_amount);
	$db = new SQLite3('Transactions.db');
	$db->enableExceptions(true);

	//заносим в базу текущий ролл
	$db->query('
    INSERT INTO Rolls ( Wallet, Amount) 
    VALUES ( '.$Wallet_tosql.', '.$payout_amount.') ' );

	//получаем все роллы с базы по номеру кошелька
	$sum_amount_result = $db->query( 'SELECT Amount,ID FROM Rolls WHERE Wallet = ' . $Wallet_tosql );

	//суммируем роллы
	$IDs = Array();
	$SumAmount = 0;
	while ($rolls_amount = $sum_amount_result->fetchArray()) {
		$IDs[] = $rolls_amount['ID'];
		$SumAmount += $rolls_amount['Amount']; 
	}

	//превращаем роллы в вид йентенов из целочисленного
	$SumAmount_sql = $SumAmount;
	$SumAmount = $SumAmount / $GLOBALS['DB_COINS_ACCURACCY'];
	
	//устанавливаем флаг выплаты, бекапим и удаляем с базы
	$isNeedPayout = $SumAmount > $GLOBALS["PAYOUT_LIMIT"];
	$isWinner = ($payout_amount / $GLOBALS['DB_COINS_ACCURACCY']) > $GLOBALS["PAYOUT_AUTOPAY_LIMIT"];
	if ( $isNeedPayout || $isWinner ){
		$Delete_IDs = implode(',', $IDs);
		// Бекап
		$Time_now = (new DateTime())->getTimestamp();
		$db->query('
		    INSERT INTO RollsArchive ( Wallet, SumAmount, TransactionTimestamp) 
		    VALUES ( '.$Wallet_tosql.', '.$SumAmount_sql.', ' . $Time_now . ') ' );
		$LastID_result = $db->query('SELECT last_insert_rowid()');
		while ($LastID_result_2 = $LastID_result->fetchArray()){
			$result['RollArchiveID'] = $LastID_result_2['last_insert_rowid()'];
		}
		// Удаление
		$db->query('DELETE FROM Rolls WHERE ID in(' . $Delete_IDs . ')');
	} else {
		// Накапливаем
		$result['Sended'] = 0;
	}

	//разлочка базы
    $db->close();
    //обработка ошибок и возвращение результатов
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
	
function SetTransactionID($id, $last_id){
	try {
	// подготовка
	$id_tosql = '"'.$id.'"';
	
	$db = new SQLite3('Transactions.db');
	$db->enableExceptions(true);

	$db->query('UPDATE RollsArchive SET TransactionID = ' . $id_tosql . ' WHERE ID = '. $last_id );

	//разлочка базы
    $db->close();
	} catch (Exception $e){
		//что-то не получилось
		return 1;
	}
    return 0;
}

?>
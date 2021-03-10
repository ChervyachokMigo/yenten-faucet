<?php
require_once("server_config.php");
require_once("DB_functions.php");

// проверка капчи
if (isset($_POST["g-recaptcha-response"])){
	//error_log(strlen($_POST["g-recaptcha-response"]));
	if ( strlen($_POST["g-recaptcha-response"]) >0 ){

	  	$response = $_POST["g-recaptcha-response"];
		
		if ( strlen($_POST['address']) == 34 && strcmp(substr( $_POST['address'], 0, 1 ),"Y") == 0 ){

		  	$username = htmlspecialchars($_POST['address']);
			
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
			$errors['human'] = 'Неверный адрес.<br>Введите legacy Yenten адрес.';
			$data['errors'] = true;
			$data['errors']  = $errors;
			error_log( "(faucet.php) ERROR: #-4 - Incorrect Wallet address. \n" );
			echo json_encode($data);
			die;
		}
	} else {
		$errors['human'] = 'Неправильная капча.';
		$data['errors'] = true;
		$data['errors']  = $errors;
		error_log( "(faucet.php) ERROR: #-3 - Captcha size 0 bytes. \n" );
		echo json_encode($data);
		die;
	}
} else {
	$errors['human'] = 'Неправильная капча.';
	$data['errors'] = true;
	$data['errors']  = $errors;
	error_log( "(faucet.php) ERROR: #-2 - No sended captcha. \n" );
	echo json_encode($data);
	die;
}
if ($captcha_success->success==false) {
	$errors['human'] = 'Неправильная капча.';
	$data['errors'] = true;
	$data['errors']  = $errors;
	error_log( "(faucet.php) ERROR: #-1 - Incorrect captcha validation. \n" );
	echo json_encode($data);
	die;


//если капча прошла проверку выполняем накопление или отправку денег
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
		error_log( "(faucet.php) ERROR: #0 - Wallet is down or no balance. \n" );
		echo json_encode($data);
  		die;
	} else {
		//если ничего не напутано, то выполняется всегда, подстраховочная проверка, что плата не выходит за минимальный и максимальный возможный
      	if ( $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] <=  $all_max || 
      		  $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] >= $all_min ) {

      		// если валидныый адрес
            if( $check['isvalid'] == 1 ){
            	//debug
	            //$payout_yentens = 2.6;

				// соединение с базой
  				$db = mysqli_connect( $GLOBALS['MYSQL_HOST'].":".$GLOBALS['MYSQL_PORT'] , $GLOBALS['MYSQL_USER'] , $GLOBALS['MYSQL_PASSWORD'] );

  				if ($db->connect_error) {
  					error_log('Ошибка подключения (' . $db->connect_errno . ') '. $db->connect_error);
  				}

  				if ( $db != false ) {
  					if ( ! mysqli_select_db( $db , $GLOBALS['MYSQL_DB'] ) ){
				        error_log("DB not found");
				        die("DB not found");
				    }

				    if ( CheckOnlineTime( $db, $username ) !=0 ){

					    //добавляем в базу или выплачиваем если достигнуты лимиты и удаляем с базы, сохраняя запись транзакции
		            	//$AddOrPayResults['SumAmount'] - сколько накоплено или сколько отправляем
		            	//$AddOrPayResults['error'] - проблемы с подключением к базе
		            	//$AddOrPayResults['Sended'] - 0 - накапливаем, 1 - выплачиваем из-за лимитов, 2 - выплачиваем, потому что выиграли
		            	$AddOrPayResults = AddOrPayYentens( $db , $username , $payout_yentens * $GLOBALS['DB_COINS_ACCURACCY'] );

		            	if ($AddOrPayResults['error'] == 0) {
			            	//записываем юзера в онлайн базу на 5 минут
			            	SetWalletOnline( $db, $username );
			            	
			            	//подготавливаем вывод в клиент
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
												"Накоплено: <a 
												title=\"Выигрыши будут выплачены при достижении накоплений в ".$GLOBALS["PAYOUT_LIMIT"]." енотов или при выигрыше.\" href=\"http://2ch-yenten-faucet.ml/#\">" . 
												round($AddOrPayResults['SumAmount'],2) . 
												"</a> енотов *</h6><br>";

									$data['balanceChange'] = $payout_yentens;

								} else {
									if ($AddOrPayResults['Sended']==2) {
										$data['boa'] .= "<h4>Вы выиграли!</h4><br>"; }

									// отсылаем монеты на адрес
					        	   
					        	    if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
					        			$e1 = $RPC->walletpassphrase( $GLOBALS["WALLET_PASS_PHRASE"], 60 ); }
					        		
					                $transucktion_id = $RPC->sendtoaddress($username, $AddOrPayResults['SumAmount'])->Result;

					                if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
										$RPC->walletlock();	}
									
									//записываем в базу отправленные монеты
									if ($transucktion_id != null || $transucktion_id != "") {

						                $Transaction_error = SetTransactionID( $db , $transucktion_id , $AddOrPayResults['RollArchiveID'] );

						                if ($Transaction_error == 1){
						                	$errors['human'] = 'Бубасик украл твои монеты.';
											$data['errors'] = true;
											$data['errors']  = $errors;
											error_log( "(faucet.php) ERROR: #8 - Can't Set Transaction ID ". $transucktion_id . " by " .  $username . "\n" );
						                } else {
						                	$data['boa'] .= "<h4>Выплачено <a href=\"https://ytn.ccore.online/transaction/utxo/".  
													$transucktion_id ."\">" . round($AddOrPayResults['SumAmount'],2) . 
													"</a> енотов!</h4><br>";
						        			error_log( "(faucet.php) SUCCESS: Pay to " . $username . 
						        				" yentens " . $AddOrPayResults['SumAmount'] . 
						        				" transaction: " . $transucktion_id . "\n" );
						        		}
						        	} else {	//если транзакция зафейлилась
						        		$data['boa'] .= "<h4>Будет выплачено <a href=\"http://2ch-yenten-faucet.ml/#\">" . round($AddOrPayResults['SumAmount'],2) . "</a> енотов в ручном режиме.</h4><br><h6>Не удалось провести транзакцию.</h6><br>";
										$errors['transaction'] = 'Не удалось провести транзакцию.';
										$data['errors'] = true;
										$data['errors']  = $errors;
										error_log( "(faucet.php) ERROR: #7 - Transaction Empty by " .  $username . " with " . $AddOrPayResults['SumAmount'] . "\n" );
						        	}

						        	$data['balanceChange'] = $AddOrPayResults['SumAmount'];

								}

								// отправка успешного сообщения
								echo json_encode($data);

							// если произошла ошибка с подключением к кошельку
							} catch (Exception $e){
								$errors['human'] = 'Бубасик украл твои монеты.';
								$data['errors'] = true;
								$data['errors']  = $errors;
								error_log( "(faucet.php) ERROR: #6 - Can't Send ". $payout_yentens . " Yentens to " .  $username . "\n" );
								echo json_encode($data);
							}


						// что-то напуталось в базе данных
						} else {
							$errors['human'] = 'Лупа и Пупа пошли получать зарплату. В бухгалтерии все перепутали. В итоге, Лупа получил за Пупу, а Пупа за Лупу!';
							$data['errors'] = true;
							$data['errors']  = $errors;
							error_log( "(faucet.php) ERROR: #5 - Can't connect to DB to add or backup payout ". $payout_yentens . " Yentens to " .  $username . "\n" );
							echo json_encode($data);
						}
					} else {
							$errors['human'] = 'Ты капчуешь слишком быстро!';
							$data['errors'] = true;
							$data['errors']  = $errors;
							error_log( "(faucet.php) ERROR: #4 - Fast captcha to " .  $username . "\n" );
							echo json_encode($data);
					}

					mysqli_close ($db);
					$db = null;
				} else {
					$errors['human'] = 'Лупа и Пупа пошли получать зарплату. В бухгалтерии все перепутали. В итоге, Лупа получил за Пупу, а Пупа за Лупу!';
						$data['errors'] = true;
						$data['errors']  = $errors;
						error_log( "(faucet.php) ERROR: #3 - Can't connect to DB to add or backup payout ". $payout_yentens . " Yentens to " .  $username . "\n" );
						echo json_encode($data);
				}
				
      		// валидация адреса кошелька не пройдена
			} else {
            		$errors['address'] = 'Неправильный адрес.';
  					$data['errors'] = true;
  					$data['errors']  = $errors;
  					error_log( "(faucet.php) ERROR: #2 - Enter incorrect address \n" );
  					echo json_encode($data);
      		}
      	// payment больше или меньше допустимого по каким-то причинам
      	} else {	
      		$errors['human'] = 'Лупа и Пупа пошли получать зарплату. В бухгалтерии все перепутали. В итоге, Лупа получил за Пупу, а Пупа за Лупу!';
			$data['errors'] = true;
			$data['errors']  = $errors;
			error_log( "(faucet.php) ERROR: #1 - Check server_config.php \n" );
			echo json_encode($data);
      	}
    // конец: кран не пустой
    }	
    $RPC = null;
    $check = null;

    $errors = null;
    $data = null;
// end capcha success=true
}	



// записываем юзера в онлайн базу на 5 минут, проверяются при каждом обновлении главной
function SetWalletOnline( &$db_online , $Wallet){
	$Wallet = '"'.$Wallet.'"';
	$date_now = new DateTime();

	$query = $db_online->query( "SELECT ID FROM walletsonline WHERE `Wallet` = " . $Wallet );
	$num = mysqli_num_rows($query);
	if($num) {
		$db_online->query("UPDATE walletsonline SET LastActive = " . ($date_now->getTimestamp()) . " WHERE Wallet = ". $Wallet );
	} else {
		$db_online->query('INSERT INTO walletsonline ( Wallet, LastActive ) 
    	VALUES ( '.$Wallet.', '.($date_now->getTimestamp()).') ');
	}
}

function CheckOnlineTime( &$db_online, $Wallet ) {
	$Wallet = '"'.$Wallet.'"';
	$date_now = new DateTime();

	$query = $db_online->query( "SELECT Wallet, LastActive FROM walletsonline WHERE `Wallet` = " . $Wallet );
	$result = $query->fetch_array(MYSQLI_ASSOC);
	$num = mysqli_num_rows($query);
	if($num) {
		if ( ( ( $date_now->getTimestamp() ) - $result['LastActive'] ) <=5 ) {
			return 0;
		}
	} 
	return 1;
}


?>
<?php
require_once("server_config.php");
require_once("DB_functions.php");
require_once("nick_generate.php");

// проверка капчи
if (isset($_POST["g-recaptcha-response"])){
	//error_log(strlen($_POST["g-recaptcha-response"]));
	if ( strlen($_POST["g-recaptcha-response"]) > 0 ){

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
    $WalletBalance = $RPC->getbalance()->Result;
	if($WalletBalance < $payout_yentens){
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

  				if ( $db != false ) {
  					if ( ! mysqli_select_db( $db , $GLOBALS['MYSQL_DB'] ) ){
				        error_log("DB not found");
				        die("DB not found");
				    }

					$db->query("SET NAMES utf8mb4");
					$db->query("SET CHARACTER SET utf8mb4");
					$db->set_charset('utf8mb4');

					$username_id = GetWalletID( $username, $db );	//получаем айди кошелька и работаем с ним

				    if ( CheckOnlineTime( $db, $username_id ) !=0 ){	//проверка 5 секунд

				    	// дополнение калькулешена 
				    	$OnlineHumansMultiplier = GetHumansNumberMultiplier($db);
						$payout_yentens = $payout_yentens * $OnlineHumansMultiplier;

						$CaptchaMultiplier = GetCaptchaMultiplier($db , $username_id);
						$payout_yentens = $payout_yentens * $CaptchaMultiplier;

						$user_Place = GetPlace ($username_id, $db);
						$payout_yentens = $payout_yentens * $user_Place['Multiplier'];

						// вторая проверка баланса потому что я даун
						if($WalletBalance < $payout_yentens){
							$errors['balance'] = 'Кран пуст.';
							$data['errors'] = true;
							$data['errors']  = $errors;
							error_log( "(faucet.php) ERROR: #0 - no balance. \n" );
							echo json_encode($data);
				  			die;
				  		}


					    //добавляем в базу или выплачиваем если достигнуты лимиты и удаляем с базы, сохраняя запись транзакции
		            	//$AddOrPayResults['SumAmount'] - сколько накоплено или сколько отправляем
		            	//$AddOrPayResults['error'] - проблемы с подключением к базе
		            	//$AddOrPayResults['Sended'] - 0 - накапливаем, 1 - выплачиваем из-за лимитов, 2 - выплачиваем, потому что выиграли
		            	$AddOrPayResults = AddOrPayYentens( $db , $username_id , $payout_yentens * $GLOBALS['DB_COINS_ACCURACCY'] );

		            	if ($AddOrPayResults['error'] == 0) {
			            	//записываем юзера в онлайн базу на 5 минут
			            	SetWalletOnline( $db, $username_id );
			            	
			            	//обновляем общий счетчик капчи
			            	UpdateWalletCaptchaCount( $username_id , $db );

			            	//подготавливаем вывод в клиент
							$data['success'] = true;
							$data['boa'] = '<h4 id="alert_capcher_name">'.GetCapcherName( $username_id,$db )."</h4>";
							$data['boa'] .= '<h3>';
							$data['boa'] .= 'Вы получили <a href="http://2ch-yenten-faucet.ml/#">' . 
											round($payout_yentens,4) . 
											"</a> енотов!<br>" .
											"Выпало: " . $roll . "<br>" . 
											"Мультикаст: " . round($multi,1) . "x<br>";

							if ($isRare == 1){
								$data['boa'] .= "Невероятная удача!!! (х" . $rare_multiplier . ") <br>";
							}

							$data['boa'] .= "Удача: " . $chance . '%';

							if ($lucky_multi>1) {
								$data['boa'] .= " (x" . $lucky_multi . "!)";
							}

							$data['boa'] .= '</h3>';
							
							$data['boa'] .= "<h6>Бонус за капчи: x".round($CaptchaMultiplier,3)."</h6>";
							if ($user_Place['Number']>0 && $user_Place['Number']!=1){
								$data['boa'] .= '<h5>Вы на '. $user_Place['Number'] .' месте капчесосов (x'.$user_Place['Multiplier'].')</h5>';
							} elseif ($user_Place['Number']==1){
								$data['boa'] .= '<h4 class="topcapcher_first">Вы на '. $user_Place['Number'] .' месте капчесосов (x'.$user_Place['Multiplier'].')</h4>';
							}
							try{
								$data['isWin'] = 0;
								if ($AddOrPayResults['Sended']==0){
									$data['boa'] .= "<h6>Отправлено в накопления.<br>" .
												"Накоплено: <a 
												title=\"Накопления будут отправлены при достижении в ".$GLOBALS["PAYOUT_LIMIT"]." енотов или при выигрыше.\" href=\"http://2ch-yenten-faucet.ml/#\">" . 
												round($AddOrPayResults['SumAmount'],2) . 
												"</a> енотов</h6><br>";
									

									$data['balanceChange'] = $payout_yentens;

								} else {
									if ($AddOrPayResults['Sended']==2) {
										$WinID_chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
										$WinID = "";
										$WinChars = rand(16,32);
										for($i=0; $i<$WinChars; $i++){
											$WinID .= $WinID_chars[ rand( 0 , strlen($WinID_chars)-1 ) ];
										}
										$winform_html = '<form id="win_form" method="post" action="index.php">
											<textarea placeholder="Введите текст" name="win_comment" cols="50" rows="6" required maxlength="300" class="win_comment"></textarea><br>
											<input type="hidden" name="WinID" value="' . $WinID . '">
											<button type="submit" id="winform_submit" class="btn" name="winform_submit" value="submit">Отправить</button>
										</form>';
										$data['boa'] .= '<h4 class="win_title">Вы выиграли!</h4><p class="win_form_desc">Вы можете прикрепить ваше сообщение, и оно отобразится в выигрышах.<br>
										Если ничего не отправить, выигрыш будет никак не прокомментирован.<br>Не используйте противозаконные, спам, рекламные, использующие мат выражения!</p><label for="wincomment" class="win_comment_label">Комментарий</label>'.$winform_html.'<br>'; 
										$data['isWin'] = 1;
										
										CreateWinner( $username_id , $WinID , $payout_yentens * $GLOBALS['DB_COINS_ACCURACCY'] , $db );
										error_log( "(faucet.php) WINNER - " . $username . " , WinID: ". $WinID . " Amount " . $payout_yentens ."\n" );
									}


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
						        		$data['boa'] .= "<h4 style='width: 350px;'>Будет выплачено <a href=\"http://2ch-yenten-faucet.ml/#\">" . round($AddOrPayResults['SumAmount'],2) . "</a> енотов в ручном режиме.</h4><br><h6>Не удалось провести транзакцию.</h6><br>";
										$errors['transaction'] = 'Не удалось провести транзакцию.';
										$data['errors'] = true;
										$data['errors']  = $errors;
										error_log( "(faucet.php) ERROR: #7 - Transaction Empty by " .  $username . " with " . $AddOrPayResults['SumAmount'] . "\n" );
						        	}
						        	$data['balanceChange'] = $AddOrPayResults['SumAmount'];

								}
								//debug капча спид
								//$data['boa'] .= 'captchaspeed ' . GetCaptchaSpeed($username_id, $db); 

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


?>
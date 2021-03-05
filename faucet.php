<?php

//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");



//данные для добавления в таблицу логов
  require_once("server_config.php");

  $response = htmlspecialchars($_POST["g-recaptcha-response"]);
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
	if ($captcha_success->success==false) {

			$errors['human'] = 'Неправильная капча.';
			$data['errors'] = true;
			$data['errors']  = $errors;
			echo json_encode($data);
      die;

	} elseif ($captcha_success->success==true) {

      require_once("jsonRPCClient.php");

      $alt = new jsonRPCClient($GLOBALS["RPC_URL"]); 
      $alt->setAuthParams($GLOBALS["RPC_CONNECT_NAME"], $GLOBALS["RPC_CONNECT_PASSWORD"]);

      $min = $GLOBALS["PAYOUT_MIN"];
      $max = $GLOBALS["PAYOUT_MAX"];
      $multi_min = $GLOBALS["PAYOUT_MULTICAST_MIN"];
      $multi_max = $GLOBALS["PAYOUT_MULTICAST_MAX"];

      $roll = rand( $min, $max );
      $multi = ( rand( $multi_min*10, $multi_max*10 ) / 100) * 10;

      $amount = $roll * $multi;
      $amount_min = $min * $multi_min;
      $amount_max = $max * $multi_max;

      $chance = round( ($amount / $amount_max) * 100 , 0 ); //вычиисление процентов

      $lucky_multi = 1;
      if ($chance>=$GLOBALS["PAYOUT_LUCKY_CHANCE_CAP"]) {
      	$lucky_multi = $GLOBALS["PAYOUT_LUCKY_MULTIPLIER"];
      }
      $amount = $amount * $lucky_multi;
      $chance = $chance * $lucky_multi;

      
      $isRare = 0;
      $rare_chance = $GLOBALS["PAYOUT_RARE_CHANCE"];
      $rare_multiplier = 1;
      $rare_roll = rand(1,10000);
      	if ( ($rare_roll >= (10000 - ( $rare_chance/2 ) * 100) ) || ( $rare_roll <= ( $rare_chance/2 ) * 100 ) ){
      		$isRare = 1;
      		$rare_multiplier = $GLOBALS["PAYOUT_RARE_MULTIPLIER"];
      	}
      $amount = $amount * $rare_multiplier;
      $chance = $chance * $rare_multiplier;

      $payout_yentens = $amount / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];
      		

      		$check = $alt->validateaddress($username);

      		if($alt->getbalance() < $payout_yentens){
        			$errors['balance'] = 'Кран пуст.';
        			$data['errors'] = true;
        			$data['errors']  = $errors;
        			echo json_encode($data);
              		die;
      		} else {
      			
      			if ( $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] <=  $all_max || $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] >= $all_min ){
		            if($check->{'isvalid'} == 1){
		            	    if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
		            			$alt->walletpassphrase( $GLOBALS["WALLET_PASS_PHRASE"], 60 );
		            	    }

		            		try{
			                	$ifSended = $alt->sendtoaddress($username, $payout_yentens);

								$data['success'] = true;
				    
								$data['boa'] = "Вы получили " . round($payout_yentens,4) . " енотов!<br>Выпало: ".$roll."<br>Мультикаст: ".round($multi,1)."x<br>Удача: ".$chance."%";
								if ($chance>=$GLOBALS["PAYOUT_LUCKY_CHANCE_CAP"]) {
									$data['boa'] .= " (x".$lucky_multi."!)<br>";
								}
								if ($isRare == 1){
									$data['boa'] .= "Невероятная удача!!! (х".$rare_multiplier.")<br>";
								}
								if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
									$alt->walletlock();
								}
								echo json_encode($data);
								die;
							} catch (Exception $e) {
								$errors['address'] = 'Произошла ошибка.';
			  					$data['errors'] = true;
			  					$data['errors']  = $errors;
			  					echo json_encode($data);
			           			die;
							}
					} else {
		            		$errors['address'] = 'Неправильный адрес.';
		  					$data['errors'] = true;
		  					$data['errors']  = $errors;
		  					echo json_encode($data);
		           			die;
		      		}
		      	} else {
		      		$errors['human'] = 'Зачем?';
					$data['errors'] = true;
					$data['errors']  = $errors;
					echo json_encode($data);
		      	}
        	}

    }
?>
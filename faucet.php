<?php
//данные для добавления в таблицу логов
  require_once("server_config.php");

  $response = $_POST["g-recaptcha-response"];

  $url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => $GLOBALS['RPC_RECAPTCHA_SECRETKEY'],
		'response' => $response
	);
	$options = array(
		'http' => array (
       'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
                    "User-Agent:MyAgent/1.0\r\n",
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
        //try{
      		$alt = new jsonRPCClient($GLOBALS["RPC_URL"]); 
        //}
        /*catch(Exception $e) {
            $errors['RPCClient'] = "Нет соединения!";
            $data['errors'] = true;
            $data['errors']  = $errors;
            echo json_encode($data);
            die;
        }*/
      $min = $GLOBALS["PAYOUT_MIN"];
      $max = $GLOBALS["PAYOUT_MAX"];
      $multi_min = $GLOBALS["PAYOUT_MULTICAST_MIN"];
      $multi_max = $GLOBALS["PAYOUT_MULTICAST_MAX"];

      $roll = rand( $min, $max );
      $multi = ( rand( $multi_min*10, $multi_max*10 ) / 100) * 10;

      $amount = $roll * $multi;
      $amount_max = $max * $multi_max;
      $amount=$amount/$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];

      $chance = round( ($amount/$amount_max)*$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]*100 , 0 ); //вычиисление процентов

      $lucky_multi = 1;
      if ($chance>=95) {
      	$lucky_multi = $GLOBALS["PAYOUT_LUCKY_MULTIPLIER"];
      }
      $amount = $amount * $lucky_multi;
      $chance = $chance * $lucky_multi;

      $rare_multiplier = $GLOBALS["PAYOUT_RARE_MULTIPLIER"];
      $isRare = 0;
      $rare_chance = $GLOBALS["PAYOUT_RARE_CHANCE"];
      $rare_roll = rand(1,10000);
      	if ( ($rare_roll >= (10000 - ( $rare_chance/2 ) * 100) ) || ( $rare_roll <= ( $rare_chance/2 ) * 100 ) ){
      		$isRare = 1;
      		$amount = $amount * $rare_chance;
      		$chance = $chance * $rare_chance;
      	}
      
      		$username = $_POST['address'];
      		$check = $alt->validateaddress($username);

      		if($alt->getbalance() < $amount){
        			$errors['balance'] = 'Кран пуст.';
        			$data['errors'] = true;
        			$data['errors']  = $errors;
        			echo json_encode($data);
              die;

      		} else {

            if($check->{'isvalid'} == 1){
    				
                $alt->sendtoaddress($username, $amount);

      					$data['success'] = true;
                
      					$data['boa'] = "Вы получили " . round($amount,4) . " енотов!<br>Выпало: ".$roll."<br>Мультикаст: ".round($multi,1)."x<br>Удача: ".$chance."%";
      					if ($chance>=95) {
      						$data['boa'] .= " (x".$lucky_multi."!)<br>";
      					}
      					if ($isRare == 1){
      						$data['boa'] .= "Невероятная удача!!! (х".$rare_multiplier.")<br>";
      					}
                
      					echo json_encode($data);
                die;
  				} else {
                $errors['address'] = 'Неправильный адрес.';
      					$data['errors'] = true;
      					$data['errors']  = $errors;
      					echo json_encode($data);
                die;
          }
          
        }

    }
?>
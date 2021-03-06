<?php

//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");



//данные для добавления в таблицу логов
require_once("server_config.php");

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

    require_once("jsonRPCClient.php");

    $alt = new jsonRPCClient($GLOBALS["RPC_URL"]); 

    // выполнение подсчетов выплат
    include 'calc.php';
      		
    $check = $alt->validateaddress($username);

	if($alt->getbalance() < $payout_yentens){
		$errors['balance'] = 'Кран пуст.';
		$data['errors'] = true;
		$data['errors']  = $errors;
		echo json_encode($data);
  		die;
	} else {
      	if ( $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] <=  $all_max || 
      		  $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] >= $all_min ) {

            if($check->{'isvalid'} == 1){
        	    if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
        			$alt->walletpassphrase( $GLOBALS["WALLET_PASS_PHRASE"], 60 ); }
        		try{
        			$alt->setAuthParams($username);
                	$ifSended = $alt->sendtoaddress($username, $payout_yentens);

					$data['success'] = true;
	    
					$data['boa'] = 	"Вы получили " . round($payout_yentens,4) . " енотов!<br>" .
									"Выпало: " . $roll . "<br>" . 
									"Мультикаст: " . round($multi,1) . "x<br>" .
									"Удача: " . $chance . "%";
					if ($lucky_multi>1) {
						$data['boa'] .= " (x" . $lucky_multi . "!)<br>";
					}
					if ($isRare == 1){
						$data['boa'] .= "Невероятная удача!!! (х" . $rare_multiplier . ")<br>";
					}
					if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
						$alt->walletlock();
					}
					echo json_encode($data);
					die;
				} catch (Exception $e) {	// ошибка соединения
					$errors['address'] = 'Бубасик украл твои монеты.';
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
      		$errors['human'] = 'Зачем?';
			$data['errors'] = true;
			$data['errors']  = $errors;
			echo json_encode($data);
      	}
    }	// конец: кран не пустой

}	// end capcha success=true
?>
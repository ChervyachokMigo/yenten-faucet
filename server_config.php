<?php
/*
Настройки RPC указываются в yenten/data/yenten.conf
rpcuser=user
rpcpassword=pass
stdinrpcpass=pass
rpcallowip=127.0.0.1
server=1
daemon=1
rpcport=9982
*/
///////////////////////// Настройки сервера ////////////////////////////////////

// Настройки для подключения к серверу
$GLOBALS["RPC_CONNECT_NAME"] = "user";
$GLOBALS["RPC_CONNECT_PASSWORD"] = "pass";
$GLOBALS["RPC_CONNECT_IP"] = "127.0.0.1";
$GLOBALS["RPC_CONNECT_PORT"] = "9982";

//коды для рекапчи http://www.google.com/recaptcha/admin
$GLOBALS['RPC_RECAPTCHA_SITEKEY'] = "";
$GLOBALS['RPC_RECAPTCHA_SECRETKEY'] = '';

// Пасс фраза для выплаты, если стоит
$GLOBALS["WALLET_PASS_PHRASE"] = "";		//оставьте пустую строку - "" если кошелек не зашифрован

// Пароль для админских модулей
$GLOBALS["PETUX_PASSWORD"] = "tAw3ta4lQxU2";

// настройки mysql
$GLOBALS['MYSQL_HOST'] = '127.0.0.1';
$GLOBALS['MYSQL_PORT'] = '3306';
$GLOBALS['MYSQL_USER'] = 'yenten_faucet';
$GLOBALS['MYSQL_PASSWORD'] = 'TkvlQYeUMvqG';
$GLOBALS['MYSQL_DB'] = '2ch_yenten_faucet';

// для хранения в базе - точность целочисленных
$GLOBALS['DB_COINS_ACCURACCY'] = 100000;

// Стоимость комиссии за перевод
$GLOBALS['FEE_AMOUNT'] = 0.01;

// Когда выплачивать
$GLOBALS["PAYOUT_LIMIT"] = 10;					// количество накоплений для выплаты (в йентенах)
$GLOBALS["PAYOUT_WIN_LIMIT"] = 0.75;				// 1 - не выплачивать, 0 - выплачивать всегда


/////////////////////////////////////////////////////////////////////////////////////
/////////////////////////// рейты для гемблинга /////////////////////////////////////
$GLOBALS["PAYOUT_MIN"] = 25;	//100					//дефолтный ролл минимум
$GLOBALS["PAYOUT_MAX"] = 117;   //444					//дефолтный ролл максимум
$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] = 10000;	//чем больше тем меньше
$GLOBALS["PAYOUT_MULTICAST_MIN"] = 0.2;			//1-100
$GLOBALS["PAYOUT_MULTICAST_MAX"] = 15.0;			//1-100
$GLOBALS["PAYOUT_LUCKY_CHANCE_CAP"] = 75;		//0-100% 0 - выпадать всегда / 100% - никогда
$GLOBALS["PAYOUT_LUCKY_MULTIPLIER"] = 1.9;		//x1-infinity
$GLOBALS["PAYOUT_RARE_MULTIPLIER"] = 4;			//x1-infinity
$GLOBALS["PAYOUT_RARE_CHANCE"] = 3;				//0.01-100%

//бонусы за капчу
$GLOBALS["PAYOUT_MULTIPLIER_CAPTCHA_RATE"] = 0.00417;		//множитель за одну капчу
$GLOBALS["PAYOUT_CAPTCHA_MAX_MULTIPLIER"] = 2;		// максимайльный множитель за капчи, 0 без лимита
$GLOBALS["PAYOUT_ONE_CAPTCHA_MULTIPLIER"] = 0.0000028;	//множитель за все капчи
$GLOBALS["ONLINE_MULTIPLIER_CAPTCHA_MIN_RATE"] = 0.033;	//коэфициент скорости капч ниже которого не будет учитываться онлайн

//бонус за людей на сайте
$GLOBALS["PAYOUT_MIN_NUMBER_CAPTCHA"] = 16;		//минимально капчей, чтобы они начались считаться

$GLOBALS["PAYOUT_RATE_PER_HUMAN"] = 0.066;	// 0 - inlinity (recomended)
$GLOBALS["PAYOUT_MAX_MULTIPLIER_PER_HUMAN"] = 2;	// any, 0 - no limit



//////////////////////КОНЕЦ НАСТРОЕК, НИЖЕ ПОДСЧЕТ////////////////////////////////



//////////////////////////////////////////////////////////////////////////////
///// Подсчет, не конфигурировать ///////////////////////////////////////////
$GLOBALS["RPC_URL"] = 	'http://'.$GLOBALS["RPC_CONNECT_NAME"].':'.$GLOBALS["RPC_CONNECT_PASSWORD"].
						'@'.$GLOBALS["RPC_CONNECT_IP"].':'.$GLOBALS["RPC_CONNECT_PORT"];

$all_min = $GLOBALS["PAYOUT_MIN"] * $GLOBALS["PAYOUT_MULTICAST_MIN"];

$all_max_without_captcha_online = $GLOBALS["PAYOUT_MAX"] * $GLOBALS["PAYOUT_MULTICAST_MAX"] * $GLOBALS["PAYOUT_LUCKY_MULTIPLIER"] * $GLOBALS["PAYOUT_RARE_MULTIPLIER"];
$all_max = $all_max_without_captcha_online * $GLOBALS["PAYOUT_CAPTCHA_MAX_MULTIPLIER"] * $GLOBALS["PAYOUT_MAX_MULTIPLIER_PER_HUMAN"];

$all_max *= ( 1 + GetMaxAllCaptchaCount() * $GLOBALS["PAYOUT_ONE_CAPTCHA_MULTIPLIER"] );

// Когда выплачивать (выигрыш)
$GLOBALS["PAYOUT_AUTOPAY_LIMIT_MIN"] = ( $all_max_without_captcha_online * $GLOBALS["PAYOUT_WIN_LIMIT"] ) / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];			// считается выигрышем после этого значения (в йентенах)

///////////////////////////////////////////////////////////////////////////////



/////////////// получить сколько капч у топ капчесоса
function GetMaxAllCaptchaCount(){
	$db = mysqli_connect( $GLOBALS['MYSQL_HOST'].":".$GLOBALS['MYSQL_PORT'] , $GLOBALS['MYSQL_USER'] , $GLOBALS['MYSQL_PASSWORD'] );
	
	$outResult = 1;

	if ( $db != false ) {
		if ( ! mysqli_select_db( $db , $GLOBALS['MYSQL_DB'] ) ){
			error_log("DB not found");
			die("DB not found");
		}

		$db->query("SET NAMES utf8mb4");
		$db->query("SET CHARACTER SET utf8mb4");
		$db->set_charset('utf8mb4');

		$results = $db->query(  'SELECT AllNumberCaptcha FROM wallets ORDER BY AllNumberCaptcha DESC LIMIT 1' );

		if ($results){
			if (mysqli_num_rows($results)!=0){
				$outResult = $results->fetch_array(MYSQLI_ASSOC)['AllNumberCaptcha'];
			}
		}

		mysqli_free_result($results);
		mysqli_close ($db);
		$db = null;
	}

	return $outResult;
}



?>

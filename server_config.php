<?php
/*
Настройки RPC указываются в yenten/data/yenten.conf
rpcuser=test
rpcpassword=pasik
stdinrpcpass=pasik
rpcallowip=127.0.0.1
server=1
daemon=1
rpcport=9982
*/
// Настройки для подключения к серверу
$GLOBALS["RPC_CONNECT_NAME"] = "";
$GLOBALS["RPC_CONNECT_PASSWORD"] = "";
$GLOBALS["RPC_CONNECT_IP"] = "127.0.0.1";
$GLOBALS["RPC_CONNECT_PORT"] = "";
//коды для рекапчи http://www.google.com/recaptcha/admin
$GLOBALS['RPC_RECAPTCHA_SITEKEY'] = "";
$GLOBALS['RPC_RECAPTCHA_SECRETKEY'] = '';
// Пасс фраза для выплаты, если стоит
$GLOBALS["WALLET_PASS_PHRASE"] = "";		//оставьте пустую строку - "" если кошелек не зашифрован
// Пароль для админских модулей
$GLOBALS["PETUX_PASSWORD"] = "FBSufYcBwE5d";
/////////////////////////////////////////////////////////////////////////////////////
// рейты для гемблинга
$GLOBALS["PAYOUT_MIN"] = 1;	//100					//дефолтный ролл минимум
$GLOBALS["PAYOUT_MAX"] = 1;//444					//дефолтный ролл максимум
$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] = 10000;	//чем больше тем меньше
$GLOBALS["PAYOUT_MULTICAST_MIN"] = 0.2;			//1-100
$GLOBALS["PAYOUT_MULTICAST_MAX"] = 15.0;			//1-100
$GLOBALS["PAYOUT_LUCKY_CHANCE_CAP"] = 65;		//0-100% 0 - выпадать всегда / 100% - никогда
$GLOBALS["PAYOUT_LUCKY_MULTIPLIER"] = 1.9;		//x1-infinity
$GLOBALS["PAYOUT_RARE_MULTIPLIER"] = 4;			//x1-infinity
$GLOBALS["PAYOUT_RARE_CHANCE"] = 6;				//0.01-100%
// Когда выплачивать
$GLOBALS["PAYOUT_LIMIT"] = 10;					//number of yentens to payout
$GLOBALS["PAYOUT_AUTOPAY_LIMIT"] = 3;			//если выиграл (больше этого значения), то платим ему всё
// для целочисленных базы
$GLOBALS['DB_COINS_ACCURACCY'] = 100000;
// пени
$GLOBALS['FEE_AMOUNT'] = 0.00172;

//////////////////////////////////////////////////////////////////////////////
///// Подсчет, не конфигурировать ///////////////////////////////////////////
$GLOBALS["RPC_URL"] = 	'http://'.$GLOBALS["RPC_CONNECT_NAME"].':'.$GLOBALS["RPC_CONNECT_PASSWORD"].
						'@'.$GLOBALS["RPC_CONNECT_IP"].':'.$GLOBALS["RPC_CONNECT_PORT"];
$all_max = $GLOBALS["PAYOUT_MAX"] * $GLOBALS["PAYOUT_MULTICAST_MAX"] * $GLOBALS["PAYOUT_LUCKY_MULTIPLIER"] * $GLOBALS["PAYOUT_RARE_MULTIPLIER"];
$all_min = $GLOBALS["PAYOUT_MIN"] * $GLOBALS["PAYOUT_MULTICAST_MIN"];

///////////////////////////////////////////////////////////////////////////////

?>

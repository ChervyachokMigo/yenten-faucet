<?php
/*
Настройки RPC указываются в yenten/data/yenten.conf
rpcuser=name
rpcpassword=pass
stdinrpcpass=pass
rpcallowip=127.0.0.1
server=1
daemon=1
rpcport=9982
*/
/// Проверить работает ли сервер: 
//           > yenten-cli.exe help
////////////////////////////////////////////////////
///// Настройки RPC сервера из yenten.conf ////////
$GLOBALS["RPC_CONNECT_NAME"] = "name";
$GLOBALS["RPC_CONNECT_PASSWORD"] = "pass";
$GLOBALS["RPC_CONNECT_IP"] = "127.0.0.1";
$GLOBALS["RPC_CONNECT_PORT"] = "9982";
//////////////////////////////////////////
///// Пароль от зашифрованого кошелька
$GLOBALS["WALLET_PASS_PHRASE"] = "";		//оставьте пустую строку - "" если кошелек не зашифрован
//////////////////////////////////////////
///// Коды для рекапчи: http://www.google.com/recaptcha/admin
$GLOBALS['RPC_RECAPTCHA_SITEKEY'] = "12312312312312312312321321321312312312312";
$GLOBALS['RPC_RECAPTCHA_SECRETKEY'] = '123123123123123123123123-12312312312312';
//////////////////////////////////////////
//// Настройки выплат ////////
$GLOBALS["PAYOUT_MIN"] = 10;						//дефолтный ролл минимум
$GLOBALS["PAYOUT_MAX"] = 444;					//дефолтный ролл максимум
$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] = 10000;	//чем больше тем меньше
$GLOBALS["PAYOUT_MULTICAST_MIN"] = 1;			//1-100
$GLOBALS["PAYOUT_MULTICAST_MAX"] = 6;			//1-100
$GLOBALS["PAYOUT_LUCKY_CHANCE_CAP"] = 95;		//0-100% 0 - выпадать всегда / 100% - никогда
$GLOBALS["PAYOUT_LUCKY_MULTIPLIER"] = 2;		//x1-infinity
$GLOBALS["PAYOUT_RARE_MULTIPLIER"] = 6;			//x1-infinity
$GLOBALS["PAYOUT_RARE_CHANCE"] = 3;				//0.01-100%

//////////////////////////////////////////////////////////////////////////////
///// Подсчет, не конфигурировать ///////////////////////////////////////////
$GLOBALS["RPC_URL"] = 	'http://'.$GLOBALS["RPC_CONNECT_NAME"].':'.$GLOBALS["RPC_CONNECT_PASSWORD"].
						'@'.$GLOBALS["RPC_CONNECT_IP"].':'.$GLOBALS["RPC_CONNECT_PORT"];
$all_max = $GLOBALS["PAYOUT_MAX"] * $GLOBALS["PAYOUT_MULTICAST_MAX"] * $GLOBALS["PAYOUT_LUCKY_MULTIPLIER"] * $GLOBALS["PAYOUT_RARE_MULTIPLIER"];
$all_min = $GLOBALS["PAYOUT_MIN"] * $GLOBALS["PAYOUT_MULTICAST_MIN"];

///////////////////////////////////////////////////////////////////////////////
?>

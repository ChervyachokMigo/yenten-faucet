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
$GLOBALS["RPC_URL"] = 'http://test:pasik@127.0.0.1:9982';
//коды для рекапчи http://www.google.com/recaptcha/admin
$GLOBALS['RPC_RECAPTCHA_SITEKEY'] = "asdasdasasdassiLL";
$GLOBALS['RPC_RECAPTCHA_SECRETKEY'] = 'asdasdsasasdasda';
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

$all_max = $GLOBALS["PAYOUT_MAX"] * $GLOBALS["PAYOUT_MULTICAST_MAX"] * $GLOBALS["PAYOUT_LUCKY_MULTIPLIER"] * $GLOBALS["PAYOUT_RARE_MULTIPLIER"];
$all_min = $GLOBALS["PAYOUT_MIN"] * $GLOBALS["PAYOUT_MULTICAST_MIN"];

///////////////////////////////////////////////////////////////////////////////
?>

<?php
/*
Настройки RPC указываются в yenten/data/yenten.conf
Содержимое файла:
rpcuser=test
rpcpassword=pasik
stdinrpcpass=pasik
rpcallowip=127.0.0.1
server=1
daemon=1
rpcport=9982

Кошелек должен быть запущен!
*/
$GLOBALS["RPC_URL"] = 'http://test:pasik@127.0.0.1:9982';
//коды для рекапчи http://www.google.com/recaptcha/admin
$GLOBALS['RPC_RECAPTCHA_SITEKEY'] = "6LeDI3Eaцфкм34км234к";
$GLOBALS['RPC_RECAPTCHA_SECRETKEY'] = '6LeDI3E3й4мф3й4мф3йц4';
$GLOBALS["PAYOUT_MIN"] = 1;
$GLOBALS["PAYOUT_MAX"] = 50;
$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] = 10000;
?>

<?php

$isDebug = 0;
if (isset($_GET["debug"])){
	if (strlen($_GET["debug"])<2){
		$isDebug = intval(substr( htmlspecialchars($_GET["debug"]), 0, 1 ));
		$DebugLoopTries = 100000;
		$DebugAccuracy = $DebugLoopTries;
	} else {
		$isDebug = 2;
	}
}

if ($isDebug == 0 || $isDebug == 1){
	error_reporting(E_ALL & ~E_NOTICE);

	require_once("server_config.php");

	debugloop:
	//// start calculation
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

    $amount = $amount * $payout_course_multiplier;

	$payout_yentens = $amount / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];

	//// end calculation
	//// continue debug calculation
    if ($isDebug == 1){

    	$PasswordValid = 0;

    	if (isset($_GET['password'])){
			if (strlen($_GET['password']) == strlen($GLOBALS["PETUX_PASSWORD"]) ){
				if (strcmp($_GET['password'], $GLOBALS["PETUX_PASSWORD"]) == 0 ){

					$PasswordValid = 1;

				} else {
					error_log('calc.php: ERROR #1: Password incorrect (value)');
				}
			} else{
				error_log('calc.php: ERROR #2: Password incorrect (length)');
			}
		} else {
			error_log('calc.php: ERROR #3: not set paassword');
		}

		if ($PasswordValid == 1){

			if ( $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] <=  $all_max || 
				$payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] >= $all_min ){

				$payout_average = $payout_average + $payout_yentens;

				$payout_yentens *= $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];

				//$all_max = $all_max_without_captcha_online;

				if (round($payout_yentens,4)<$all_max/100){
					$out[1]++;
				}
				if (round($payout_yentens,4)>=$all_max/100 && round($payout_yentens,4)<$all_max/50){
					$out[2]++;
				}
				if (round($payout_yentens,4)>=$all_max/50 && round($payout_yentens,4)<$all_max/40){
					$out[3]++;
				}
				if (round($payout_yentens,4)>=$all_max/40 && round($payout_yentens,4)<$all_max/30){
					$out[4]++;
				}
				if (round($payout_yentens,4)>=$all_max/30 && round($payout_yentens,4)<$all_max/20){
					$out[5]++;
				}
				if (round($payout_yentens,4)>=$all_max/20 && round($payout_yentens,4)<$all_max/10){
					$out[6]++;
				}
				if (round($payout_yentens,4)>=$all_max/10 && round($payout_yentens,4)<$all_max/5){
					$out[7]++;
				}
				if (round($payout_yentens,4)>=$all_max/5 && round($payout_yentens,4)<$all_max/2){
					$out[8]++;
				}
				if (round($payout_yentens,4)>=$all_max/2 && round($payout_yentens,4)<=$all_max){
					$out[9]++;
				}

				if (round($payout_yentens,4)>=$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] &&
					round($payout_yentens,4)<$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]*2 ){
					$out["1 ytn"]++;
				}
				if (round($payout_yentens,4)>=$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]*2 &&
					round($payout_yentens,4)<$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]*3 ){
					$out["2 ytn"]++;
				}
				if (round($payout_yentens,4)<=($GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]/10 )){
					$out["01 ytn"]++;
				}
				if (round($payout_yentens,4)>=$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]/10  &&
					round($payout_yentens,4)<$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] ){
					$out["011 ytn"]++;
				}
				if (round($payout_yentens,4)>=$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]/10  &&
					round($payout_yentens,4)<$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]/5 ){
					$out["0105 ytn"]++;
				}
				if (round($payout_yentens,4)>=$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]/5  &&
					round($payout_yentens,4)<$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] ){
					$out["051 ytn"]++;
				}
				if (round($payout_yentens,4)>=$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]*3 &&
					round($payout_yentens,4)<$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]*4 ){
					$out["3 ytn"]++;
				}
				if (round($payout_yentens,4)>=$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]*4 ){
					$out["4 ytn"]++;
				}

				if (round($payout_yentens,4)>=$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]*0.75 ){
					$out["075"]++;
				}

				$out_all++;

				if ($lucky_multi == 1){
					$out["lucky_1"]++;
				} else {
					$out["lucky_2"]++;
				}
				if ($rare_multiplier == 1){
					$out["rare_1"]++;
				} else {
					$out["rare_2"]++;
				}
			} else {
		  		$out["errors"]++;
			}

			// end debug calculation and loop
			$DebugAccuracy--;
			if ($DebugAccuracy>0){
				goto debugloop;
			}
			$payout_average = $payout_average / $DebugLoopTries;
			// debug output

			//// single output
			//echo "pay ".($payout_yentens/$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"]);
			//echo "<br>roll ".$payout_yentens;



			echo "<br>amount ".$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];
			echo "<br>max ".$all_max;
			echo "<br>min ".$all_min;

			echo "<br>";
			echo "1: ".($out[1]/$out_all*100).'%'."<br>";
			echo "2: ".($out[2]/$out_all*100).'%'."<br>";
			echo "3: ".($out[3]/$out_all*100).'%'."<br>";
			echo "4: ".($out[4]/$out_all*100).'%'."<br>";
			echo "5: ".($out[5]/$out_all*100).'%'."<br>";
			echo "6: ".($out[6]/$out_all*100).'%'."<br>";
			echo "7: ".($out[7]/$out_all*100).'%'."<br>";
			echo "8: ".($out[8]/$out_all*100).'%'."<br>";
			echo "9: ".($out[9]/$out_all*100).'%'."<br>";
			echo "0.75: ".($out["075"]/$out_all*100).'%'."<br>";
			echo "win on:".$GLOBALS["PAYOUT_AUTOPAY_LIMIT_MIN"].' YTN'."<br>";
			echo "win on max rates:".$GLOBALS["PAYOUT_AUTOPAY_LIMIT_MIN"] * $GLOBALS["PAYOUT_CAPTCHA_MAX_MULTIPLIER"] * $GLOBALS["PAYOUT_MAX_MULTIPLIER_PER_HUMAN"].' YTN'."<br>";
			echo "less than 0.1 ytn: ".($out["01 ytn"]/$out_all*100).'%'."<br>";
			echo "0.1 - 1: ".($out["011 ytn"]/$out_all*100).'%'."<br>";
			echo "0.1 - 0.5: ".($out["0105 ytn"]/$out_all*100).'%'."<br>";
			echo "0.5 - 1: ".($out["051 ytn"]/$out_all*100).'%'."<br>";
			echo "more than 1 ytn: ".($out["1 ytn"]/$out_all*100).'%'."<br>";
			echo "more than 2 ytn: ".($out["2 ytn"]/$out_all*100).'%'."<br>";
			echo "more than 3 ytn: ".($out["3 ytn"]/$out_all*100).'%'."<br>";
			echo "more than 4 ytn: ".($out["4 ytn"]/$out_all*100).'%'."<br>";
			echo "lucky chance ".($out["lucky_2"]/$out["lucky_1"]*100).'%'."<br>";

			echo "<pre>";
			print_r($out);
			echo "</pre>";
		}
	}//end debug

} else {
	echo "Бубасик, ты? Узнал тебя по твоему говнокоду. Как поживаешь?";
}
?>
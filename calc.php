<?php
error_reporting(E_ALL);
require_once("server_config.php");

for ($i=0;$i<100000;$i++){

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


	if ( $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] <=  $all_max || $payout_yentens * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] >= $all_min ){
		$payout_yentens *= $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];
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

}

echo "pay".$payout_yentens;
echo "<br>amount".$GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"];
echo "<br>max".$all_max;
echo "<br>min".$all_min;

echo "<br>";
echo "1: ".$out[1]/$out_all."<br>";
echo "2: ".$out[2]/$out_all."<br>";
echo "3: ".$out[3]/$out_all."<br>";
echo "4: ".$out[4]/$out_all."<br>";
echo "5: ".$out[5]/$out_all."<br>";
echo "6: ".$out[6]/$out_all."<br>";
echo "7: ".$out[7]/$out_all."<br>";
echo "8: ".$out[8]/$out_all."<br>";
echo "9: ".$out[9]/$out_all."<br>";
echo "<pre>";
print_r($out);
echo "</pre>";
?>
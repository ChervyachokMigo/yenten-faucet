<?php
require_once("server_config.php");
require_once("nick_generate.php");

// Функция: сравнения по времени:
// первая больше второй на $seconds, default 5 minutes
// timestamp_1 ввести чтобы проверить, что 
//         эта дата больше 300(по умолчанию) секунд назад (или 5 минут)
function CompareTime($timestamp_1, $timestamp_2 = null, $seconds = 300) {
  if ($timestamp_2 == null) {
    $timestamp_2 = (new DateTime())->getTimestamp();
  }
  return intval( $timestamp_1 >= ($timestamp_2 - $seconds) );  
}

function UpdateWalletCaptchaCount( $Wallet_id , &$db ){
	$sql = 'UPDATE wallets SET AllNumberCaptcha = AllNumberCaptcha + 1 WHERE ID = ' . $Wallet_id;
	$db->query( $sql );
}

function GetWalletbyID ( $Wallet_id , &$db ){
	$results = $db->query( 'SELECT Wallet FROM wallets WHERE ID = '. $Wallet_id );
	return $results->fetch_array(MYSQLI_NUM)[0];

}

function GetWalletID ( $Wallet , &$db ){
	$Wallet_sql = '"'.$Wallet.'"';
	$isNeedStoreWallet = 0;

	$results_id = $db->query( 'SELECT ID FROM wallets WHERE Wallet = '. $Wallet_sql );

	if ($results_id == false){
		$isNeedStoreWallet = 1;
	} else {
		$WalletID = $results_id->fetch_array(MYSQLI_ASSOC)['ID'];
		if (is_null($WalletID)){
			$isNeedStoreWallet = 1;
		} else {
			if ($WalletID == ''){
				$isNeedStoreWallet = 1;
			}
		}
	}

	if ($isNeedStoreWallet == 1){
		$WalletID = StoreWalletinDB( $Wallet , $db );
	}

	return $WalletID;
}

function StoreWalletinDB( $Wallet , &$db ) {
	$Wallet_sql = '"'.$Wallet.'"';

	$WalletName = Nick_Generate($db);
	$WalletName_sql = '"'.$WalletName.'"';

	$db->query('INSERT INTO wallets (Wallet , Name) VALUES ('. $Wallet_sql .' , ' . $WalletName_sql . ')');
	$New_ID_Wallet = $db->query('SELECT LAST_INSERT_ID() as ID')->fetch_array(MYSQLI_NUM)[0];

	return $New_ID_Wallet;
}

function GetHumansNumberMultiplier( &$db ){
	$Online = GetOnlineCount($db , 1);
	if ($Online>0){
		$multiplier = 1 + $GLOBALS["PAYOUT_RATE_PER_HUMAN"] * $Online;
		if ($GLOBALS["PAYOUT_MAX_MULTIPLIER_PER_HUMAN"] == 0 || $multiplier < $GLOBALS["PAYOUT_MAX_MULTIPLIER_PER_HUMAN"] ){
			return $multiplier;
		} elseif ( $multiplier >= $GLOBALS["PAYOUT_MAX_MULTIPLIER_PER_HUMAN"] ) {
			return $GLOBALS["PAYOUT_MAX_MULTIPLIER_PER_HUMAN"];
		}
	} else {
		return 1.0;
	}
}

function GetCaptchaMultiplier( &$db , $Wallet_id ){
	
	$results_captcha = $db->query(  'SELECT CountCaptcha FROM walletsonline WHERE Wallet_ID = '. $Wallet_id );
	$result = 1;
  	if ($results_captcha) {
  		$results_captcha_count = $results_captcha->fetch_array( MYSQLI_ASSOC );
	  	if (mysqli_num_rows($results_captcha) != 0) {
	  		$result = 1 + intval($results_captcha_count['CountCaptcha']) * $GLOBALS["PAYOUT_MULTIPLIER_CAPTCHA_RATE"];
	  		if ($GLOBALS["PAYOUT_CAPTCHA_MAX_MULTIPLIER"] != 0 && $result >= $GLOBALS["PAYOUT_CAPTCHA_MAX_MULTIPLIER"] ){
	  			$result = $GLOBALS["PAYOUT_CAPTCHA_MAX_MULTIPLIER"];
	  		}
	  	}
	  	$results_captcha_count = null;
  	}
  	mysqli_free_result($results_captcha);
  	return $result;  
}

function GetOnlineCount( &$db, $RealHumans = 0 ){
  $result = 0;
  if ($RealHumans == 0){
	  $results_online_db = $db->query( 'SELECT * FROM walletsonline' );
	  if ($results_online_db) {
	      while ( $online_db_wallet = $results_online_db->fetch_array(  MYSQLI_ASSOC ) ) {
	        if ( CompareTime( $online_db_wallet['LastActive'] ) == 1 ){
	          $result++;
	        } else {
	          // удалить из таблицы онлайна всех, кто не посылал успешные запросы больше 5 минут
	          $db->query( 'DELETE FROM walletsonline WHERE ID = '.$online_db_wallet['ID'] );
	        }
	      }
	      
	      mysqli_free_result($results_online_db);
	      $online_db_wallet = null;
	  }
  }
  if ($RealHumans == 1){
	  $results_online_db_2 = 
	  		 $db->query( 'SELECT COUNT(*) as Humans FROM walletsonline 
	  			WHERE CountCaptcha >= '. $GLOBALS["PAYOUT_MIN_NUMBER_CAPTCHA"] );

	  if ($results_online_db_2) {
	  		$online_db_wallet_2 = $results_online_db_2->fetch_array( MYSQLI_ASSOC );
		  	if (mysqli_num_rows($results_online_db_2) != 0) {
		      $result = intval($online_db_wallet_2['Humans']);
		      mysqli_free_result($results_online_db_2);
		      $online_db_wallet_2 = null;
		  }
	  }
  }
  return $result;  
}

function GetTransactionsBalance(&$db){
  try {
    //сумма неоплаченых роллов (накоплено)
    $result_1 = $db->query( 'SELECT SUM(Amount) as this FROM rolls' );
    
    if ($result_1) {
      $sumAmount_res = $result_1->fetch_array( MYSQLI_ASSOC );
      mysqli_free_result($result_1);
    } else {
      $sumAmount_res['this'] = 0;
    }

    //количество неоплаченых юзеров с накоплениями
    $result_2 = $db->query( 'SELECT COUNT ( DISTINCT Wallet_ID ) as this FROM rolls' );
    if ($result_2) {
      $countToPayout_res = $result_2->fetch_array( MYSQLI_ASSOC );
      mysqli_free_result($result_2);
    } else {
      $countToPayout_res['this'] = 0;
    }

    //количество неоплаченных (ошибочных) транзакций
    $result_3 =  $db->query( 'SELECT COUNT (*) as this FROM rollsarchive WHERE TransactionID = \'\'' );
    if ($result_3) {
      $notPayedCount_res = $result_3->fetch_array( MYSQLI_ASSOC );
      mysqli_free_result($result_3);
    } else {
      $notPayedCount_res['this'] = 0;
    }

    //сумма неоплаченных (ошибочных) транзакций
    $result_4 = mysqli_query( $db , 'SELECT SUM(SumAmount) as this FROM rollsarchive WHERE TransactionID = \'\'' );
    if ($result_4) {
      $notPayedAmount_res = $result_4->fetch_array( MYSQLI_ASSOC );
      mysqli_free_result($result_4);
    } else {
      $notPayedAmount_res['this'] = 0;
    }

  } catch (Exception $e){
    //что-то не получилось
    return -1;

  } catch (mysqli_sql_exception $e) {
     // throw $e;
    return -1;

  }

    $res['SumAmount'] = ( $sumAmount_res['this'] + $notPayedAmount_res['this'] ) / $GLOBALS['DB_COINS_ACCURACCY'];
    $res['Count'] = $countToPayout_res['this'] + $notPayedCount_res['this'];

    return $res;

}

// записываем юзера в онлайн базу на 5 минут, проверяются при каждом обновлении главной
function SetWalletOnline( &$db_online , $Wallet_id){
	$date_now = new DateTime();

	$query = $db_online->query( "SELECT ID, CountCaptcha FROM walletsonline WHERE Wallet_ID = " . $Wallet_id );
	$num = mysqli_num_rows($query);
	if($num) {
		$result = $query->fetch_array(MYSQLI_ASSOC);
		$CountCaptcha = intval($result['CountCaptcha']);
		$CountCaptcha = $CountCaptcha + 1;
		$db_online->query("UPDATE walletsonline SET LastActive = " . ($date_now->getTimestamp()) . ", CountCaptcha = " . $CountCaptcha . " WHERE ID = ". $result['ID'] );
	} else {
		$db_online->query('INSERT INTO walletsonline ( Wallet_ID, LastActive, CountCaptcha ) 
    	VALUES ( '. $Wallet_id .', '. ($date_now->getTimestamp()) .', 1 ) ');
	}
}

function CheckOnlineTime( &$db_online, $Wallet_id ) {

	$date_now = new DateTime();

	$query = $db_online->query( "SELECT Wallet_ID, LastActive FROM walletsonline WHERE `Wallet_ID` = " . $Wallet_id );
	$result = $query->fetch_array(MYSQLI_ASSOC);
	$num = mysqli_num_rows($query);
	if($num) {
		if ( ( ( $date_now->getTimestamp() ) - $result['LastActive'] ) <=5 ) {
			return 0;
		}
	} 
	return 1;
}


function SetTransactionID( &$db_id, $id, $row_id){
	// $id - transaction id
	// $row_id - row id in DB
	try {
	// подготовка
	$id_tosql = "'".$id."'";
	
	$db_id->query('UPDATE rollsarchive SET TransactionID = ' . $id_tosql . ' WHERE `ID` = \''. $row_id .'\'' );

	} catch (Exception $e){
		//что-то не получилось
		return 1;
	}
    return 0;
}

function AddOrPayYentens( &$db_id , $Wallet_id , $payout_amount = 0 , $use_limits = 1 ){
	try {
	// подготовка
	$payout_amount = intval($payout_amount);

	if ($use_limits == 1){
		//заносим в базу текущий ролл
		$db_id->query('
	    INSERT INTO rolls ( Wallet_ID, Amount) 
	    VALUES ( '.$Wallet_id.', '.$payout_amount.') ' );
	}

	//получаем все роллы с базы по id кошелька
	$sum_amount_result = $db_id->query( 'SELECT Amount,ID FROM rolls WHERE Wallet_ID = ' . $Wallet_id );

	//суммируем роллы
	$IDs = Array();
	$SumAmount = 0;
	if ($sum_amount_result) {
		while ($rolls_amount = $sum_amount_result->fetch_array(MYSQLI_ASSOC)) {
			$IDs[] = $rolls_amount['ID'];
			$SumAmount += $rolls_amount['Amount']; 
		}
      mysqli_free_result($sum_amount_result);
    }

	//превращаем роллы в вид йентенов из целочисленного
	$SumAmount_sql = $SumAmount;
	$SumAmount = $SumAmount / $GLOBALS['DB_COINS_ACCURACCY'];
	
	//устанавливаем флаг выплаты, бекапим и удаляем с базы
	
	if ($use_limits == 1){
		$WinPayoutAmount = round( $GLOBALS["PAYOUT_AUTOPAY_LIMIT_MIN"] * $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] *
				GetHumansNumberMultiplier( $db_id ) * GetCaptchaMultiplier( $db_id , $Wallet_id ) , 0) / $GLOBALS["PAYOUT_AMOUNT_MULTIPLIER"] ;
		
		$isWinner = ( $payout_amount / $GLOBALS['DB_COINS_ACCURACCY'] ) > $WinPayoutAmount  ;

		$isNeedPayout = $SumAmount > $GLOBALS["PAYOUT_LIMIT"];
	} else {
		$isWinner = false;
		$isNeedPayout = true;
	}

	if ( $isNeedPayout || $isWinner ){
		// Бекап
		$Time_now = (new DateTime())->getTimestamp();
		$db_id->query('
		    INSERT INTO rollsarchive ( Wallet_ID, SumAmount, TransactionTimestamp ) 
		    VALUES ( ' . $Wallet_id . ', '.$SumAmount_sql.', ' . $Time_now . ') ' );
		$LastID_result = $db_id->query('SELECT LAST_INSERT_ID() as ID');
		
		if ($LastID_result) {
			$LastID_result_2 = $LastID_result->fetch_array(MYSQLI_ASSOC);
			$result['RollArchiveID'] = $LastID_result_2['ID'];
	      	mysqli_free_result($LastID_result);
	    }
		// Удаление
		if ( count($IDs) > 0 ){
			$Delete_IDs = implode(',', $IDs);
			$db_id->query('DELETE FROM rolls WHERE ID in(' . $Delete_IDs . ')');
		}
	} else {
		// Накапливаем
		$result['Sended'] = 0;
	}

    //обработка ошибок и возвращение результатов
    $result['error'] = 0;
    if ($isNeedPayout) $result['Sended'] = 1;
	if ($isWinner) $result['Sended'] = 2;
    $result['SumAmount'] = $SumAmount;
	} catch (Exception $e){
		//что-то не получилось
		$result['error'] = 1;
		return $result;
	}
    return $result;
}

?>
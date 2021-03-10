<?php

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

function AddOrPayYentens( &$db_id , $Wallet, $payout_amount = 0, $use_limits = 1 ){
	try {
	// подготовка
	$Wallet_tosql = '"'.$Wallet.'"';
	$payout_amount = intval($payout_amount);

	if ($use_limits == 1){
		//заносим в базу текущий ролл
		$db_id->query('
	    INSERT INTO Rolls ( Wallet, Amount) 
	    VALUES ( '.$Wallet_tosql.', '.$payout_amount.') ' );
	}

	//получаем все роллы с базы по номеру кошелька
	$sum_amount_result = $db_id->query( 'SELECT Amount,ID FROM rolls WHERE Wallet = ' . $Wallet_tosql );

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
		$isWinner = ($payout_amount / $GLOBALS['DB_COINS_ACCURACCY']) > $GLOBALS["PAYOUT_AUTOPAY_LIMIT"];
		$isNeedPayout = $SumAmount > $GLOBALS["PAYOUT_LIMIT"];
	} else {
		$isWinner = false;
		$isNeedPayout = true;
	}

	if ( $isNeedPayout || $isWinner ){
		// Бекап
		$Time_now = (new DateTime())->getTimestamp();
		$db_id->query('
		    INSERT INTO rollsarchive ( Wallet, SumAmount, TransactionTimestamp ) 
		    VALUES ( ' . $Wallet_tosql . ', '.$SumAmount_sql.', ' . $Time_now . ') ' );
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
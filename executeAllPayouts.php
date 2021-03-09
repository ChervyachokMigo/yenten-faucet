<?php
require_once("server_config.php");
require_once("DB_functions.php");
require_once("BaseJsonRpcClient.php");

if (isset($_GET['password'])){
	if (strlen($_GET['password']) == strlen($GLOBALS["PETUX_PASSWORD"]) ){
		if (strcmp($_GET['password'], $GLOBALS["PETUX_PASSWORD"]) == 0 ){

			ExecutePayout();

		} else {
			error_log('executeAllPayouts.php: ERROR #1: Password incorrect (value)');
		}
	} else{
		error_log('executeAllPayouts.php: ERROR #2: Password incorrect (length)');
	}
} else {
	error_log('executeAllPayouts.php: ERROR #3: not set paassword');
}

function ExecutePayout(){
    try {
    	$RPC = new BaseJsonRpcClient($GLOBALS["RPC_URL"]);
  		// баланс кошелька
  		$balance = $RPC->getbalance()->Result;

		$db = new SQLite3('Transactions.db');
		$db->enableExceptions(true);

     	// количество неоплаченных (ошибочных) транзакций
	    $result_3 = $db->query( 'SELECT ID, Wallet, SumAmount FROM RollsArchive WHERE TransactionID = \'\'' );
	    $notPayedCount_res = $result_3->fetchArray();
	    //разлочка базы
	    echo "<pre>";
      	print_r($notPayedCount_res);
		echo "</pre>";
	    if ($notPayedCount_res){
	    	$PayoutWallet = $notPayedCount_res['Wallet'];
	    	$PayoutAmount = $notPayedCount_res['SumAmount'] / $GLOBALS['DB_COINS_ACCURACCY'];
	    	$PayoutID = $notPayedCount_res['ID'];
	    	// выполняем выплату одной неоплаченой транзакции и занесение айди в базу
	    	if ($balance > $PayoutAmount){
			    if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
	    		$e1 = $RPC->walletpassphrase( $GLOBALS["WALLET_PASS_PHRASE"], 60 ); }
	    		
	            $transucktion_id = ($RPC->sendtoaddress($PayoutWallet, $PayoutAmount))->Result;

	            if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
					$RPC->walletlock();	}

				if ($transucktion_id != null or $transucktion_id != ""){
	            	$Transaction_error = SetTransactionID( $db , $transucktion_id , $PayoutID );
		            if ($Transaction_error == 1){
		            	$error_text = "executeAllPayouts.php: (Unpayed) ERROR #1 - Can't update RollsArchive and add ". $transucktion_id . " to " . $PayoutWallet . " with ID: ". $PayoutID . "\n" ;
		            	echo $error_text;
		            	error_log( $error_text );
		            	
		            } else {
		            	$error_text = "executeAllPayouts.php: (Unpayed) SUCCESS - Pay to ".$PayoutWallet . " amount ".$PayoutAmount." and add transaction " . $transucktion_id . "\n";
						echo $error_text;
			        	error_log( $error_text );
		            	
		            }
		        } else {
		        	$error_text = "executeAllPayouts.php: (Unpayed) ERROR #2 - Can't send to address ".$PayoutWallet . "\n";
					echo $error_text;
		        	error_log( $error_text );
		        	
	        	}
	        } else {
	        	$error_text =  "executeAllPayouts.php: (Unpayed) ERROR #3 - Not enought balance " . $balance . " to payout amount " . $PayoutAmount . " to wallet ".$PayoutWallet . "\n";
	        	echo $error_text;
	        	error_log( $error_text );
		        	
	        }
	    } else {
	    	// выполняем поиск по накопленым роллам
	    	//количество неоплаченых юзеров с накоплениями
	    	$result_2 = $db->query('SELECT DISTINCT Wallet as this FROM Rolls' );
	    	$CollectionWalletToPayout_res = $result_2->fetchArray();
	    	
	    	if ($CollectionWalletToPayout_res){
	    		$PayoutWallet = $CollectionWalletToPayout_res['this'];

	    		$AddOrPayResults = AddOrPayYentens( $db , $PayoutWallet , 0 , 0 );
			    
            	if ($AddOrPayResults['error'] == 0){
            		$PayoutAmount = $AddOrPayResults['SumAmount'];
            		if ($balance > $PayoutAmount){
	            		// выполняем выплату одной неоплаченой транзакции и занесение айди в базу
					    if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
			    		$e1 = $RPC->walletpassphrase( $GLOBALS["WALLET_PASS_PHRASE"], 60 ); }
			    		
			            $transucktion_id = ($RPC->sendtoaddress($PayoutWallet, $PayoutAmount))->Result;

			            if (strlen($GLOBALS["WALLET_PASS_PHRASE"])>0){
							$RPC->walletlock();	}

						if ($transucktion_id != null || $transucktion_id != ""){
			            	$Transaction_error = SetTransactionID( $db , $transucktion_id, $AddOrPayResults['RollArchiveID'] );
				            if ($Transaction_error == 1){
				            	$error_text = "executeAllPayouts.php: (Collection) ERROR #4 - Can't update RollsArchive and add ". $transucktion_id . " to " . $PayoutWallet . " with ID: ". $AddOrPayResults['RollArchiveID'] . " with amount " . $PayoutAmount . "\n";
				            	error_log( $error_text );
				            	
				            } else {
				            	$error_text = "executeAllPayouts.php: (Collection) SUCCESS - Pay to ".$PayoutWallet . " amount ".$PayoutAmount." and add transaction " . $transucktion_id . "\n";
				            	echo $error_text;
				            	error_log( $error_text );
				            }
				        } else {
				        	$error_text = "executeAllPayouts.php: (Collection) ERROR #3 - Can't send ". $PayoutAmount." to address ".$PayoutWallet . "\n" ;
				        	echo $error_text;
				        	error_log( $error_text);
				        	
				        }
				    } else {
				    	$error_text =  "executeAllPayouts.php: (Unpayed) ERROR #2 - Not enought balance " . $balance . " to payout amount " . $PayoutAmount . " to wallet ".$PayoutWallet . "\n";
			        	echo $error_text;
			        	error_log( $error_text );
		        		
				    }
            	} else {
            		$error_text = "executeAllPayouts.php: (Collection) ERROR: #1 - Can't connect to DB to add or backup payout ". $PayoutAmount . " Yentens to " .  $PayoutWallet . "\n" ;
		        	echo $error_text;
		        	error_log( $error_text);
		        	
            	}
	    	} else {
	    		echo "Done.";
	    		error_log( "executeAllPayouts.php: Done. \n" );
	    	}
	    }
      	//разлочка базы
      	$db->close();
    } catch (Exception $e){
      	echo "<pre>";
      	print_r($e);
      	echo "</pre>";
      	return -1;
    }
    return 0;
}

?>
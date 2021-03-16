<?php
require_once("server_config.php");


function Update_Wallet_Nickname($Wallet_id,&$db){
	$sql = 'SELECT Name FROM wallets WHERE ID = ' . $Wallet_id ;

	$isNeedCreate = 0;

	$query_result = $db->query($sql);
	if ($query_result == false){
		$isNeedCreate = 1;
	} else {
		$WalletName = $query_result->fetch_array(MYSQLI_ASSOC)['Name'];
		if (is_null($WalletName)){
			$isNeedCreate = 1;
		} else {
			if ($WalletName == ''){
				$isNeedCreate = 1;
			}
		}
	}

	if ($isNeedCreate == 1){
		$WalletName = Nick_Generate($db);
		$WalletName_sql =  '\'' . $WalletName . '\'';
		$sql = 'UPDATE wallets SET Name = ' . $WalletName_sql . ' WHERE ID = ' . $Wallet_id ;
		$db->query($sql);
		error_log('(nick_generate.php) SUCCESSS: Assign new Name '. $WalletName .' to Wallet ID '. $Wallet_id );

	}
	return $WalletName;
}

function Nick_Generate(&$db){

	$sql1 = 'SELECT Word FROM first_word ORDER BY RAND() LIMIT 1';
	$sql2 = 'SELECT Word FROM second_word ORDER BY RAND() LIMIT 1';
	$sql3 = 'SELECT Word FROM third_word ORDER BY RAND() LIMIT 1';

	$first = $db->query($sql2)->fetch_array(MYSQLI_NUM)[0];
	$second = $db->query($sql1)->fetch_array(MYSQLI_NUM)[0];
	$third = $db->query($sql3)->fetch_array(MYSQLI_NUM)[0];

	$title = array('абу-','ибн-','де ','дель ','ди ','бен ','ван ','дю ','фон ','мак ', 'о ', 'фитц ');

	return $first ." ". $third ." ". $title[ rand( 0, count($title)-1 ) ]. $second;

}


?>
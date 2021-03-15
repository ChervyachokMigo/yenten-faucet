<?php
require_once("server_config.php");
require_once("DB_functions.php");
require_once("nick_generate.php");

add_all_addresses();

function add_all_addresses(){
    $db = mysqli_connect( $GLOBALS['MYSQL_HOST'].":".$GLOBALS['MYSQL_PORT'] , $GLOBALS['MYSQL_USER'] , $GLOBALS['MYSQL_PASSWORD'] );
    echo ' Ошибка подключения (' . $db->connect_errno . ') '. $db->connect_error;
    
    mysqli_select_db( $db , $GLOBALS['MYSQL_DB']);

    $all_wallets = array();

    $result_3 = $db->query( 'SELECT Wallet FROM rollsarchive ' );
    echo mysqli_errno($db) . ": " . mysqli_error($db). "\n";
    if ($result_3 !=false){
        while($wallets_rollsarchive = $result_3->fetch_array(MYSQLI_ASSOC)){
            $all_wallets[] = $wallets_rollsarchive['Wallet'];
        }
    }
    $result_2 = $db->query( 'SELECT DISTINCT Wallet FROM rolls ' );
    echo mysqli_errno($db) . ": " . mysqli_error($db). "\n";
    if ($result_2 !=false){
        while($wallets_rolls = $result_2->fetch_array(MYSQLI_ASSOC)){
            $all_wallets[] = $wallets_rolls['Wallet'];
        }
    }
    


    
	

    array_unique($all_wallets);

    foreach($all_wallets as $key => $wallet){
        $all_wallets[$wallet] = Nick_Generate($db);
        unset($all_wallets[$key]);
    }

    
    $count=0;
    $values="";

    foreach($all_wallets as $wallet => $nick){       
        if ($count>0)
            $values .= ' , ';
        $values .= '(\''. trim($wallet) .'\' , \''. trim($nick) .'\')';
        $count ++;
    }
    

    echo 'INSERT INTO wallets ( Wallet, Name )  VALUES '. $values;
    $db->query( 'INSERT INTO wallets ( Wallet, Name )  VALUES '. $values );
    echo mysqli_errno($db) . ": " . mysqli_error($db). "\n";
    $db->close();

}


?>
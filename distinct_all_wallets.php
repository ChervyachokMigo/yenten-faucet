<?php
require_once("server_config.php");
require_once("DB_functions.php");
require_once("nick_generate.php");
// проверка валидности пароля
if (isset($_GET['password'])){
    if (strlen($_GET['password']) == strlen($GLOBALS["PETUX_PASSWORD"]) ){
        if (strcmp($_GET['password'], $GLOBALS["PETUX_PASSWORD"]) == 0 ){

            add_all_addresses();

        } else {
            error_log('executeAllPayouts.php: ERROR #1: Password incorrect (value)');
        }
    } else{
        error_log('executeAllPayouts.php: ERROR #2: Password incorrect (length)');
    }
} else {
    error_log('executeAllPayouts.php: ERROR #3: not set paassword');
}


function add_all_addresses(){
    $db = mysqli_connect( $GLOBALS['MYSQL_HOST'].":".$GLOBALS['MYSQL_PORT'] , $GLOBALS['MYSQL_USER'] , $GLOBALS['MYSQL_PASSWORD'] );
    echo ' Ошибка подключения (' . $db->connect_errno . ') '. $db->connect_error;
    
    mysqli_select_db( $db , $GLOBALS['MYSQL_DB']);

    $db->query("SET NAMES utf8mb4");
    $db->query("SET CHARACTER SET utf8mb4");
    $db->set_charset('utf8mb4');

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
        $values .= '(\''. $wallet .'\' , \''. $nick .'\')';
        $count ++;
    }
    

    echo 'INSERT INTO wallets ( Wallet, Name )  VALUES '. $values;
    
    $db->query( 'INSERT INTO wallets ( Wallet, Name )  VALUES '. $values );
    echo mysqli_errno($db) . ": " . mysqli_error($db). "\n";

    echo "\n";
    echo $count;
    
    $db->close();

}


?>
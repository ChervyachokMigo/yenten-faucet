<?php
require_once("server_config.php");

function getmd5Ip($ip = 0) {
  	if ($ip = 0){
	  $keys = [
	    'HTTP_CLIENT_IP',
	    'HTTP_X_FORWARDED_FOR',
	    'REMOTE_ADDR'
	  ];
	  foreach ($keys as $key) {
	    if (!empty($_SERVER[$key])) {
	      $ip = trim(end(explode(',', $_SERVER[$key])));
	      if (filter_var($ip, FILTER_VALIDATE_IP)) {
	        $ip = $GLOBALS['DB_IP_SALT']. $ip . $GLOBALS['DB_IP_SALT'];
	        $ip = md5($ip);
	        for ($i=1; $i<=$GLOBALS['DB_IP_NUMBER_CRYPT']; $i++){
	          $ip = crypt($ip, $GLOBALS['DB_MD5_SALT']);
	        }
	        return $ip ;
	      } else {
	      	return 0;
	      }
	    } else {
	    	return 0;
	    }
	  }
	} else {
		$ip = $GLOBALS['DB_IP_SALT']. $ip . $GLOBALS['DB_IP_SALT'];
        $ip = md5($ip);
        for ($i=1; $i<=$GLOBALS['DB_IP_NUMBER_CRYPT']; $i++){
          $ip = crypt($ip, $GLOBALS['DB_MD5_SALT']);
        }
        return $ip ;
	}
}


?>
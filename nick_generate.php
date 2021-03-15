<?php
require_once("server_config.php");


function Nick_Generate(&$db){

	$sql1 = 'SELECT Word FROM first_word ORDER BY RAND() LIMIT 1';
	$sql2 = 'SELECT Word FROM second_word ORDER BY RAND() LIMIT 1';
	$sql3 = 'SELECT Word FROM third_word ORDER BY RAND() LIMIT 1';

	$first = $db->query($sql2)->fetch_array(MYSQLI_NUM)[0];
	$second = $db->query($sql1)->fetch_array(MYSQLI_NUM)[0];
	$third = $db->query($sql3)->fetch_array(MYSQLI_NUM)[0];

	$title = array('абу-','ибн-','де ','дель ','д\' ','бен ','ван ','дю ','фон ','мак ', 'о\' ', 'фитц ');

	return $first ." ". $third ." ". $title[ rand( 0, count($title)-1 ) ]. $second;
	
}


?>
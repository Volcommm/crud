<?php

/**
 * mysql_connect is deprecated
 * using mysqli_connect instead
 */

$databaseHost = 'db';
$databaseName = 'crud_with_login';
$databaseUsername = 'crud';
$databasePassword = 'crud';

$mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName); 
	
?>

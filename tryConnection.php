<?php
$serverName = "localhost\SQLEXPRESS";
$connectionInfo = array( "Database"=>"wms","UID"=>"wms", "PWD"=>"localhost20");
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {
     echo "Connection established.<br />";
}else{
     echo "Connection could not be established.<br />";
     die( print_r( sqlsrv_errors(), true));
}
?>

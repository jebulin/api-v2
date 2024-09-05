<?php

$hostname = 'localhost:3306';
$username = "bluonevahan";
$password = "f]9ixUtYjnzu";
$database = 'evahan';

$conn = mysqli_connect($hostname, $username, $password, $database);

if(!$conn){
    die('Connection failes: '. mysqli_connect_error());
}

?>

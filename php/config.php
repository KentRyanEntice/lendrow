<?php
$host = "localhost";
$dbname = "mylendrow";
$username = "KentRyan";
$password = "KentRyan_db";

$connection = mysqli_connect ($host, $username, $password, $dbname);

if (!$connection) {
	die("Connection failed: " . mysqli_connect_error());
}
?>
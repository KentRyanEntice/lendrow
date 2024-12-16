<?php
session_start();
	require 'config.php';
	require 'functions.php';
	
if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../home?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	$firstname = $_POST["firstname"];
	$middlename = $_POST["middlename"];
	$lastname = $_POST["lastname"];
	$username = $_POST["username"];
	$mobile = $_POST["mobile"];
	$pass = $_POST["pass"];
	$confirmpass = $_POST["confirmpass"];
	
	
	if(emptyInputSignup($firstname, $middlename, $lastname, $username, $mobile, $pass, $confirmpass) !== false) {
		header("location: ../home?error=emptyinput");
		exit();
	}
	
	if(invalidUsername($username) !== false) {
		header("location: ../home?error=invalidusername");
		exit();
	}
	
	if(invalidMobile($mobile) !== false) {
		header("location: ../home?error=invalidmobile");
		exit();
	}
	
	if(invalidPass($pass, $confirmpass) !== false) {
		header("location: ../home?error=passdontmatch");
		exit();
	}
	
	if(fullnameTaken($connection, $firstname, $middlename, $lastname) !== false) {
		header("location: ../home.php?error=fullnametaken");
		exit();
	}
	
	if(usernameTaken($connection, $username) !== false) {
		header("location: ../home?error=usernametaken");
		exit();
	}
	
	if(mobileTaken($connection, $mobile) !== false) {
		header("location: ../home?error=mobiletaken");
		exit();
	}
	
	createUser($connection, $firstname, $middlename, $lastname, $username, $mobile, $pass);
	
	$_SESSION['last_submission_time'] = time();
	exit();
	
}

	else {
		header("location: ../home");
			exit();
	}

?>
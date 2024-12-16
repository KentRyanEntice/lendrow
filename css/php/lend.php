<?php
session_start();
include 'config.php';
include 'lendfunction.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../lenders?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lend'])) {
	$picture = $_POST["picture"];
	$lendername = $_POST["lendername"];
	$amount = $_POST["amount"];
	$interest = $_POST["interest"];
	$term = $_POST["term"];
	$monthly = $_POST["monthly"];
	$status = $_POST["status"];
	$mobile = $_POST["mobile"];
	$id = $_POST["id"];
		
	if(emptyInput($amount, $interest, $term) !== false) {
		header("location: ../lenders?error=emptyinput");
		exit();
	}
	
	if(invalidInput($amount) !== false) {
		header("location: ../lenders?error=invalidinput");
		exit();
	}
	
	if(invalidAmount($amount) !== false) {
		header("location: ../lenders?error=invalidamount");
		exit();
	}
		
	createLend($connection, $picture, $lendername, $amount, $interest, $term, $monthly, $status, $mobile, $id);

	$_SESSION['last_submission_time'] = time();
	exit();
}
else {
    header("location: ../lenders");
    exit();
}

?>
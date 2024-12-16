<?php
session_start();
include 'config.php';
include 'borrowfunction.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../borrowers?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$picture = $_POST["picture"];
	$borrowername = $_POST["borrowername"];
	$mobile = $_POST["mobile"];
	$status = $_POST["status"];
	$lending_terms_id = $_POST['lending_terms_id'];
	$id = $_POST['id'];
		
if(isLender($connection, $lending_terms_id, $id) !== false) {
	header("location: ../borrowers?error=ownlending");
	exit();
}
	
if(existingApproved($connection, $id) !== false) {
	header("location: ../borrowers?error=existingapproved");
	exit();
}

if(existingDebt($connection, $id) !== false) {
	header("location: ../borrowers?error=existingdebt");
	exit();
}
		
	borrow($connection, $picture, $borrowername, $mobile, $status, $lending_terms_id, $id);
	
	$_SESSION['last_submission_time'] = time();
	exit();
}
	
else {
	header("location: ../borrowers");
	exit();
}

?>
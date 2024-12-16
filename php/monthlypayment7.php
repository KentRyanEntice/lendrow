<?php
session_start();
include 'config.php';
include 'monthlypayment7function.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../payment?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$sender = $_POST["sender"];
    $mobile = $_POST["mobile"];
    $amount = $_POST["amount"];
	$receiver = $_POST["receiver"];
	$applicationId = $_POST['applications_id'];
	$id = $_POST['id'];
	
	payInterest7($connection, $sender, $mobile, $amount, $receiver, $applicationId, $id);
	
	$_SESSION['last_submission_time'] = time();
	exit();

} else {
    header("location: ../payment");
    exit();
}

?>
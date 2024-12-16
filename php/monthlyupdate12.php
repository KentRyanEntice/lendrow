<?php
session_start();
include 'config.php';
include 'monthlyupdate12function.php';

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
	$applicationId = $_POST['applications_id'];
	$id = $_POST['id'];
	
	updateInterest12($connection, $applicationId, $id);
	
	$_SESSION['last_submission_time'] = time();
	exit();

} else {
    header("location: ../payment");
    exit();
}

?>
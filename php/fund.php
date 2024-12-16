<?php
session_start();
include 'config.php';
include 'fundfunction.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../lenders?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fund'])) {
	$picture = $_POST["picture"];
	$sender = $_POST["sender"];
    $mobile = $_POST["mobile"];
    $amount = $_POST["amount"];
	$receiver = $_POST["receiver"];
	$interest = $_POST["interest"];
	$term = $_POST["term"];
	$monthly = $_POST["monthly"];
	$applicationsId = $_POST["applications_id"];
	$lendingTermsId = $_POST["lending_terms_id"];
	$lendingAgreementsId = $_POST["lending_agreements_id"];
	
	lendMoney($connection, $picture, $sender, $mobile, $amount, $receiver, $interest, $term, $monthly, $applicationsId, $lendingTermsId, $lendingAgreementsId);
	
	$_SESSION['last_submission_time'] = time();
	exit();
}
else {
    header("location: ../lenders");
    exit();
}

?>
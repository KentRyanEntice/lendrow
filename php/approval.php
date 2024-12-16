<?php
session_start();
include 'config.php';
include 'approvalfunction.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../lenders?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["approve"])) {
	$borrowername = $_POST["borrowername"];
	$mobile = $_POST["mobile"];
	$lendername = $_POST["lendername"];
	$amount = $_POST["amount"];
	$interest = $_POST["interest"];
	$term = $_POST["term"];
	$monthly = $_POST["monthly"];
    $status = $_POST["status"];
    $applicationsId = $_POST['applications_id'];
	$lendingTermsId = $_POST["lending_terms_id"];
	
	if(existingApproved($connection, $mobile) !== false) {
		header("location: ../lenders?error=existingapproved");
		exit();
	}

	if(existingDebt($connection, $mobile) !== false) {
		header("location: ../lenders?error=existingdebt");
		exit();
	}

    approve($connection, $borrowername, $mobile, $lendername, $amount, $interest, $term, $monthly, $status, $applicationsId, $lendingTermsId);
	
	$_SESSION['last_submission_time'] = time();
	exit();

} 

else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reject"])) {

    $status = $_POST["status"];
    $applicationsId = $_POST['applications_id'];

    reject($connection, $status, $applicationsId);
	
	$_SESSION['last_submission_time'] = time();
	exit();

}

else {
    header("location: ../lenders");
    exit();
}
?>

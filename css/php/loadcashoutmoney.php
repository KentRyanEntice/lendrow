<?php
session_start();
include 'config.php';
include 'loadcashoutmoneyfunction.php';

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../adminwallet?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$name = $_POST["name"];
	$sender = $_POST["sender"];
    $mobile = $_POST["mobile"];
    $balance = $_POST["balance"];
	$fullname = $_POST["receiver"];
	$receipt = $_FILES["receipt"];
	$id = $_POST['id'];
	
	if (emptyPicture($receipt) !== false) {
        header("location: ../adminwallet?error=emptyreceipt");
        exit();
    }
	
	if (invalidSize($receipt) !== false) {
        header("location: ../adminwallet?error=invalidsize");
        exit();
    }
	
	if (invalidFormat($receipt) !== false) {
		header("location: ../adminwallet?error=invalidformat");
		exit();
	}
	
	loadCashOutMoney($connection, $name, $sender, $mobile, $balance, $fullname, $_FILES['receipt'], $id);
	
	$_SESSION['last_submission_time'] = time();
	exit();

} else {
    header("location: ../adminwallet");
    exit();
}


?>
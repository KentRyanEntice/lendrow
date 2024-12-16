<?php
session_start();
include 'config.php';
include 'addmoneyfunction.php';

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
	$sender = $_POST["sender"];
    $mobile = $_POST["mobile"];
    $balance = $_POST["balance"];
	$fullname = $_POST["fullname"];

    if (emptyInput($mobile, $balance) !== false) {
        header("location: ../adminwallet?error=emptyinput");
        exit();
    }

    if (invalidInput($balance) !== false) {
        header("location: ../adminwallet?error=invalidinput");
        exit();
    }

    if (!mobileExists($connection, $mobile)) {
        header("location: ../adminwallet?error=mobilenotfound");
        exit();
    }
	
	addMoney($connection, $sender, $mobile, $balance, $fullname);

	$_SESSION['last_submission_time'] = time();
	exit();

} else {
    header("location: ../adminwallet");
    exit();
}


?>

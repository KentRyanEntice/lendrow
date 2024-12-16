<?php
session_start();
include 'config.php';
include 'addvirtualmoneyfunction.php';

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
    $balance = $_POST["amount"];

    if (emptyInput($balance) !== false) {
        header("location: ../adminwallet?error=emptyinput");
        exit();
    }

    if (invalidInput($balance) !== false) {
        header("location: ../adminwallet?error=invalidinput");
        exit();
    }
	
	addVirtualMoney($connection, $name, $balance);

	$_SESSION['last_submission_time'] = time();
	exit();

} else {
    header("location: ../adminwallet");
    exit();
}


?>
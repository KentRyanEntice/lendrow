<?php
session_start();
include 'config.php';
include 'loadcashoutfunction.php';

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../adminwallet?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["approve"])) {
	$name = $_POST["name"];
	$mobile = $_POST["mobile"];
	$amount = $_POST["amount"];
	$status = $_POST["status"];
	$id = $_POST['id'];

    approve($connection, $name, $mobile, $amount, $status, $id);
	
	$_SESSION['last_submission_time'] = time();
	exit();
} 

else if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reject"])) {
    $status = $_POST["status"];
	$id = $_POST['id'];

    reject($connection, $status, $id);
	
	$_SESSION['last_submission_time'] = time();
	exit();
}

else {
    header("location: ../adminwallet");
    exit();
}
?>
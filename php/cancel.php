<?php
session_start();
include 'config.php';
include 'cancelfunction.php';

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
	$id = $_POST['id'];
	
	cancel($connection, $id);
	
	$_SESSION['last_submission_time'] = time();
	exit();

} else {
    header("location: ../borrowers");
    exit();
}

?>
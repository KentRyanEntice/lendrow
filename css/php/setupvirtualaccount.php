<?php
session_start();
include 'config.php';
include 'setupvirtualaccountfunction.php';

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
    $name  = $_POST["name"];

    if (emptyInput($name) !== false) {
        header("location: ../adminwallet?error=emptyinput");
        exit();
    }
	
	if(invalidName($name) !== false) {
		header("location: ../adminwallet?error=invalidname");
		exit();
	}
	
	setUp($connection, $name);

	$_SESSION['last_submission_time'] = time();
	exit();

} else {
    header("location: ../adminwallet");
    exit();
}


?>
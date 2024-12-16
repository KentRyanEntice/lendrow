<?php
session_start();
include 'config.php';
include 'picturefunctions.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    header("Location: ../profile?error=duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
	$id = filter_var($_POST["id"], FILTER_SANITIZE_NUMBER_INT);
     $picture = $_FILES["picture"];
	
	
	if (emptyPicture($picture) !== false) {
        header("location: ../profile?error=emptypicture");
        exit();
    } 
	
	if (invalidSize($picture) !== false) {
        header("location: ../profile?error=invalidsize");
        exit();
    }
	
	if (invalidFormat($picture) !== false) {
		header("location: ../profile?error=invalidformat");
		exit();
	}
	
	addPicture($connection, $id);

	$_SESSION['last_submission_time'] = time();
	exit();

} else {
    header("location: ../profile");
    exit();
}
?>

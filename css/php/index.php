<?php
session_start();

include 'config.php';

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
	session_unset();
	session_destroy();
    header("Location: ../home?error=unauthorizedaccess");
    exit;
} 
else {
	header("Location: ../profile");
    exit;
}

?>
<?php

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

function emptyInput($name) {
	$result;
	if (empty($name)){
		$result = true;
	}
	else {
		$result = false;
	}
	return $result;
}

function invalidName($name) {
	$result;
	if (!preg_match("/^[a-zA-Z0-9]*$/", $name)) {
		$result = true;
	} 
	else {
		$result = false;
	}
	return $result;
}

function setUp($connection, $name) {
	$virtualBalance = '0.00';
	$systemVirtualBalance = '0.00';

    $queryInsert = "INSERT INTO virtual_wallet (name, balance, system_balance) VALUES (?,?,?)";
    $stmtInsert = mysqli_stmt_init($connection);
	
    if (!mysqli_stmt_prepare($stmtInsert, $queryInsert)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }
	
    mysqli_stmt_bind_param($stmtInsert, "sss", $name, $virtualBalance, $systemVirtualBalance);
    mysqli_stmt_execute($stmtInsert);
    mysqli_stmt_close($stmtInsert);

    header("location: ../adminwallet?success=setup");
    exit();
}

?>
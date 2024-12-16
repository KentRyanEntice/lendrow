<?php

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

function approve($connection, $name, $mobile, $amount, $status, $id) {
	$currentVirtualBalance = getCurrentVirtualBalance($connection, $name);
	$currentBalance = getCurrentBalance($connection, $mobile);
	
	if ((float) str_replace(',', '', $currentBalance) < (float) str_replace(',', '', $amount)) {
		header("location: ../adminwallet?error=insufficientuserbalance");
		exit();
	}
	
	$existingAppQuery = "SELECT id FROM cash_loading WHERE method = 'CashOut' AND status = 'Approved'";
    $stmtAppQuery = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtAppQuery, $existingAppQuery)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_execute($stmtAppQuery);
    mysqli_stmt_store_result($stmtAppQuery);

    if (mysqli_stmt_num_rows($stmtAppQuery) > 0) {
        header("location: ../adminwallet?error=existingcashoutapproval");
        exit();
    }

    mysqli_stmt_close($stmtAppQuery);
	
    $query = "UPDATE cash_loading SET status = 'Approved', approved_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: ../adminwallet?success=approvedcashout");
    exit();
}

function getCurrentVirtualBalance($connection, $name) {
    $query = "SELECT balance FROM virtual_wallet WHERE name = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['balance'];
    } else {
        header("location: ../adminwallet?error=virtualbalancenotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function getCurrentBalance($connection, $mobile) {
    $query = "SELECT balance FROM wallet WHERE mobile = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $mobile);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['balance'];
    } else {
        header("location: ../adminwallet?error=balancenotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function reject($connection, $status, $id) {

    $query = "UPDATE cash_loading SET status = 'Rejected', approved_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: ../adminwallet?success=rejectedcashout");
    exit();
}

?>
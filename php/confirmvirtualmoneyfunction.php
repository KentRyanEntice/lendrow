<?php

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

function confirm($connection, $name, $amount, $status, $id) {
	
	$currentVirtualBalance = getCurrentVirtualBalance($connection, $name);
	$currentSystemBalance = getCurrentSystemBalance($connection, $name);

    $newVirtualBalance = number_format((float) str_replace(',', '', $currentVirtualBalance) + (float) str_replace(',', '', $amount), 2, '.', ',');
	
	$newSystemBalance = number_format((float) str_replace(',', '', $currentSystemBalance) + (float) str_replace(',', '', $amount), 2, '.', ',');

    $query = "UPDATE virtual_wallet SET balance = ?, system_balance = ?, updated_at = CURRENT_TIMESTAMP WHERE name = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "sss", $newVirtualBalance, $newSystemBalance, $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $query = "UPDATE virtual_wallet_history SET status = 'Added', updated_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: ../adminwallet?success=confirmed");
    exit();
}

function cancel($connection, $status, $id) {

    $query = "UPDATE virtual_wallet_history SET status = 'Cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: ../adminwallet?success=cancelled");
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

function getCurrentSystemBalance($connection, $name) {
    $query = "SELECT system_balance FROM virtual_wallet WHERE name = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['system_balance'];
    } else {
        header("location: ../adminwallet?error=systembalancenotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

?>
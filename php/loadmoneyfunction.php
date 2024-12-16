<?php

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

function loadMoney($connection, $name, $sender, $mobile, $balance, $fullname, $id) {
	if (!hasEnoughVirtualBalance($connection, $name, $amount)) {
        header("location: ../adminwallet?error=insufficientvirtualbalance");
        exit();
    }
	
	$currentVirtualBalance = getCurrentVirtualBalance($connection, $name);

	$newVirtualBalance = number_format((float) str_replace(',', '', $currentVirtualBalance) - (float) str_replace(',', '', $balance), 2, '.', ',');

	$queryDeduct = "UPDATE virtual_wallet SET balance = ?, updated_at = CURRENT_TIMESTAMP WHERE name = ?;";
	$stmtDeduct = mysqli_stmt_init($connection);

	if (!mysqli_stmt_prepare($stmtDeduct, $queryDeduct)) {
		header("location: ../adminwallet?error=stmtfailed");
		exit();
	}

	mysqli_stmt_bind_param($stmtDeduct, "ss", $newVirtualBalance, $name);
	mysqli_stmt_execute($stmtDeduct);
	mysqli_stmt_close($stmtDeduct);
	
	$amount = number_format((float) str_replace(',', '', $balance), 2, '.', ',');
	$status = 'Deducted';
    $transferMethod = 'Cash In';
	$virtualWalletId = getVirtualWalletId($connection, $name);

    $queryInsert = "INSERT INTO virtual_wallet_history (amount, status, method, virtual_wallet_id) VALUES (?,?,?,?)";
    $stmtInsert = mysqli_stmt_init($connection);
	
    if (!mysqli_stmt_prepare($stmtInsert, $queryInsert)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtInsert, "sssi", $amount, $status, $transferMethod, $virtualWalletId);
    mysqli_stmt_execute($stmtInsert);
    mysqli_stmt_close($stmtInsert);
		
    $currentBalance = getCurrentBalance($connection, $mobile);

    $newBalance = number_format((float) str_replace(',', '', $currentBalance) + (float) str_replace(',', '', $balance), 2, '.', ',');

    $query = "UPDATE wallet SET balance = ?, updated_at = CURRENT_TIMESTAMP WHERE mobile = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $newBalance, $mobile);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

	$amount = number_format((float) str_replace(',', '', $balance), 2, '.', ',');
    $transferMethod = 'added';
    $walletId = getWalletId($connection, $mobile);
	$senderId = getSenderId($connection, $sender);

    $queryInsert = "INSERT INTO wallet_history (sender, mobile, amount, receiver, transfer_method, wallet_id, sender_id) VALUES (?,?,?,?,?,?,?)";
    $stmtInsert = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtInsert, $queryInsert)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtInsert, "sssssii", $sender, $mobile, $amount, $fullname, $transferMethod, $walletId, $senderId);
    mysqli_stmt_execute($stmtInsert);
    mysqli_stmt_close($stmtInsert);
	
	$updateQuery = "UPDATE cash_loading SET status = 'Added', added_at = CURRENT_TIMESTAMP WHERE id = ?";
	$stmtUpdate = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtUpdate, $updateQuery)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtUpdate, "i",  $id);
    mysqli_stmt_execute($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);

    header("location: ../adminwallet?success=cashin");
    exit();
}

function hasEnoughVirtualBalance($connection, $name, $amount) {
    $currentVirtualBalance = getCurrentVirtualBalance($connection, $name);

    return (float) str_replace(',', '', $currentVirtualBalance) >= (float) str_replace(',', '', $amount);
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

function getVirtualWalletId($connection, $name) {
    $query = "SELECT id FROM virtual_wallet WHERE name = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['id'];
    } else {
        header("location: ../adminwallet?error=virtualwalletidnotfound");
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

function getWalletId($connection, $mobile) {
    $query = "SELECT id FROM wallet WHERE mobile = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $mobile);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['id'];
    } else {
        header("location: ../adminwallet?error=walletidnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function getSenderId($connection, $sender) {
    $query = "SELECT id FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $sender);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['id'];
    } else {
        header("location: ../adminwallet?error=senderidnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

?>
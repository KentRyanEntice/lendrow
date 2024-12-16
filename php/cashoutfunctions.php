<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function emptyCashOutInput($paymentMethod, $amount, $paymentNumber, $paymentAccountName) {
	$result;
	if (empty($paymentMethod) || empty($amount) || empty($paymentNumber) || empty($paymentAccountName)){
		$result = true;
	}
	else {
		$result = false;
	}
	return $result;
}
	
function invalidAmountInput($amount) {
    $result;
    $amount = preg_replace('/,/', '', $amount);

    if (!preg_match("/^\d+(\.\d{1,2})?$/", $amount)) {
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

function invalidPaymentMobile($paymentNumber) {
		$result;
		if (!preg_match("/^09\d{9}$/", $paymentNumber)) {
			$result = true;
		} 
		else {
			$result = false;
		}
		return $result;
	}
	
function cashOut($connection, $fullname, $method, $paymentMethod, $amount, $mobile, $paymentNumber, $paymentAccountName, $receipt, $status, $walletId) {
	if (!hasEnoughBalance($connection, $fullname, $amount)) {
        handleCashOutError("cashoutinsufficientbalance");
        exit();
    }
	
	$existingAppQuery = "SELECT id FROM cash_loading WHERE wallet_id = ? AND method = 'CashOut' AND (status <> 'Rejected' AND status <> 'Deducted' OR status IS NULL)";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $existingAppQuery)) {
       handleCashOutError("stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $walletId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        handleCashOutError("existingcashout");
        exit();
    }

    mysqli_stmt_close($stmt);
	
    $newAmount = number_format((float) str_replace(',', '', $amount), 2, '.', ',');

	$query = "INSERT INTO cash_loading (name, method, payment_method, amount, mobile, payment_number, payment_account_name, receipt, status, wallet_id) VALUES (?,?,?,?,?,?,?,?,?,?)";
	$stmt = mysqli_stmt_init($connection);

	if (!mysqli_stmt_prepare($stmt, $query)) {
		handleCashOutError("stmtfailed");
		exit();
	}
			
	mysqli_stmt_bind_param($stmt, "sssssssssi", $fullname, $method, $paymentMethod, $newAmount, $mobile, $paymentNumber, $paymentAccountName, $receipt, $status, $walletId);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	
	handleCashOutSuccess("cashout");
	exit();
}

function hasEnoughBalance($connection, $fullname, $amount) {
    $myCurrentBalance = getMyCurrentBalance($connection, $fullname);

    return (float) str_replace(',', '', $myCurrentBalance) >= (float) str_replace(',', '', $amount);
}

function getMyCurrentBalance($connection, $fullname) {
    $query = "SELECT balance FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        handleCashOutError("stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $fullname);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['balance'];
    } else {
        handleCashOutError("mybalancenotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

?>
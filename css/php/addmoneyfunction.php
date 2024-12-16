<?php
if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

function emptyInput($mobile, $balance) {
	$result;
	if (empty($mobile) || empty($balance)){
		$result = true;
	}
	else {
		$result = false;
	}
	return $result;
}
	
function invalidInput($balance) {
    $result;
    $balance = preg_replace('/,/', '', $balance);

    if (!preg_match("/^\d+(\.\d{1,2})?$/", $balance)) {
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

function mobileExists($connection, $mobile) {
    $query = "SELECT * FROM wallet WHERE mobile = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $mobile);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row;
    } else {
        $result = false;
        return $result;
    }

    mysqli_stmt_close($stmt);
}

function addMoney($connection, $sender, $mobile, $balance, $fullname) {
    $currentBalance = getCurrentBalance($connection, $mobile);

    $newBalance = number_format((float) str_replace(',', '', $currentBalance) + (float) str_replace(',', '', $balance), 2, '.', ',');

    $query = "UPDATE wallet SET balance = ? WHERE mobile = ?;";
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

    header("location: ../adminwallet?success=moneyadded");
    exit();
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
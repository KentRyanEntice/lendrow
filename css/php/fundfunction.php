<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function lendMoney($connection, $picture, $sender, $mobile, $amount, $receiver, $interest, $term, $monthly, $applicationsId, $lendingTermsId, $lendingAgreementsId) {
	
    $query = "UPDATE applications SET status = 'Funded', funded_at = CURRENT_TIMESTAMP WHERE id = ?";
	
	$stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }
	
	mysqli_stmt_bind_param($stmt, "i",  $applicationsId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
	
	$insertQuery = "INSERT INTO financial_details (picture, lendername, mobile, amount, borrowername, interest, term, monthly, applications_id, lending_terms_id, lending_agreements_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
    $insertStmt = mysqli_stmt_init($connection);
	
	if (!mysqli_stmt_prepare($insertStmt, $insertQuery)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }
	
	mysqli_stmt_bind_param($insertStmt, "ssssssssiii",$picture, $sender, $mobile, $amount, $receiver, $interest, $term, $monthly, $applicationsId, $lendingTermsId, $lendingAgreementsId);
    mysqli_stmt_execute($insertStmt);
    mysqli_stmt_close($insertStmt);
	
	$receiverCurrentBalance = getReceiverCurrentBalance($connection, $mobile);

    $receiverNewBalance = number_format((float) str_replace(',', '', $receiverCurrentBalance) + (float) str_replace(',', '', $amount), 2, '.', ',');

    $query = "UPDATE wallet SET balance = ?, updated_at = CURRENT_TIMESTAMP WHERE mobile = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $receiverNewBalance, $mobile);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

	$balance = number_format((float) str_replace(',', '', $amount), 2, '.', ',');
    $transferMethod = 'disbursed';
    $walletId = getWalletId($connection, $mobile);
	$senderId = getSenderId($connection, $sender);

    $queryInsert = "INSERT INTO wallet_history (sender, mobile, amount, receiver, transfer_method, wallet_id, sender_id) VALUES (?,?,?,?,?,?,?)";
    $stmtInsert = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtInsert, $queryInsert)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtInsert, "sssssii", $sender, $mobile, $balance, $receiver, $transferMethod, $walletId, $senderId);
    mysqli_stmt_execute($stmtInsert);
    mysqli_stmt_close($stmtInsert);

    header("location: ../lenders?success=funded");
    exit();
}

function getReceiverCurrentBalance($connection, $mobile) {
    $query = "SELECT balance FROM wallet WHERE mobile = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $mobile);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['balance'];
    } else {
        header("location: ../lenders?error=balancenotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function getWalletId($connection, $mobile) {
    $query = "SELECT id FROM wallet WHERE mobile = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $mobile);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['id'];
    } else {
        header("location: ../lenders?error=walletidnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function getSenderId($connection, $sender) {
    $query = "SELECT id FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $sender);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['id'];
    } else {
        header("location: ../lenders?error=senderidnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

?>
<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function emptyInput($amount, $interest, $term) {
		$result;
		if (empty($amount) || empty($interest) || empty($term)){
			$result = true;
		}
		else {
			$result = false;
		}
		return $result;
	}
	
function invalidInput($amount) {
    $result;
    $amount = preg_replace('/,/', '', $amount);

    if (!preg_match("/^\d+(\.\d{1,2})?$/", $amount)) {
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

function invalidAmount($amount) {
	$amount = str_replace(',', '', $amount);
    if ($amount > 20000) {
        return true;
    } 
	else {
        return false;
    }
}

function createLend($connection, $picture, $lendername, $amount, $interest, $term, $monthly, $status, $mobile, $id) {
	
		if (!hasEnoughBalance($connection, $lendername, $amount)) {
			header("location: ../lenders?error=insufficientbalance");
			exit();
		}
		
		$newAmount = number_format((float) str_replace(',', '', $amount), 2, '.', ',');
		
		$query = "INSERT INTO lending_terms (picture, lendername, amount, interest, term, monthly, status, users_id) VALUES (?,?,?,?,?,?,?,?)";
		$stmt = mysqli_stmt_init($connection);
		
		if (!mysqli_stmt_prepare($stmt, $query)) {
			header("location: ../lenders?error=stmtfailed");
			exit();
		}
		
		mysqli_stmt_bind_param($stmt, "sssssssi", $picture, $lendername, $newAmount, $interest, $term, $monthly, $status, $id);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		
		$lenderCurrentBalance = getLenderCurrentBalance($connection, $lendername);

		$lenderNewBalance = number_format((float) str_replace(',', '', $lenderCurrentBalance) - (float) str_replace(',', '', $amount), 2, '.', ',');

		$queryDeduct = "UPDATE wallet SET balance = ? WHERE fullname = ?;";
		$stmtDeduct = mysqli_stmt_init($connection);

		if (!mysqli_stmt_prepare($stmtDeduct, $queryDeduct)) {
			header("location: ../lenders?error=stmtfailed");
			exit();
		}

		mysqli_stmt_bind_param($stmtDeduct, "ss", $lenderNewBalance, $lendername);
		mysqli_stmt_execute($stmtDeduct);
		mysqli_stmt_close($stmtDeduct);
		
		$balance = number_format((float) str_replace(',', '', $amount), 2, '.', ',');
		$transferMethod = 'lending';
		$walletId = getWalletId($connection, $mobile);
		$lenderId = getLenderId($connection, $lendername);

		$queryInsert = "INSERT INTO wallet_history (sender, mobile, amount, receiver, transfer_method, wallet_id, sender_id) VALUES (?,?,?,?,?,?,?)";
		$stmtInsert = mysqli_stmt_init($connection);

		if (!mysqli_stmt_prepare($stmtInsert, $queryInsert)) {
			header("location: ../lenders?error=stmtfailed");
			exit();
		}

		mysqli_stmt_bind_param($stmtInsert, "sssssii", $lendername, $mobile, $balance, $lendername, $transferMethod, $walletId, $lenderId);
		mysqli_stmt_execute($stmtInsert);
		mysqli_stmt_close($stmtInsert);
		
		header("location: ../lenders?success=created");
		exit();
	}

function hasEnoughBalance($connection, $lendername, $amount) {
    $lenderCurrentBalance = getLenderCurrentBalance($connection, $lendername);

    return (float) str_replace(',', '', $lenderCurrentBalance) >= (float) str_replace(',', '', $amount);
}

function getLenderCurrentBalance($connection, $lendername) {
    $query = "SELECT balance FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $lendername);	
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['balance'];
    } else {
        header("location: ../lenders?error=lendernotfound");
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

function getLenderId($connection, $lendername) {
    $query = "SELECT id FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $lendername);
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
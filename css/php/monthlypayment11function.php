<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function payInterest11($connection, $sender, $mobile, $amount, $receiver, $applicationId, $id) {

	if (!hasEnoughBalance($connection, $sender, $amount)) {
        header("location: ../payment?error=insufficientbalance");
        exit();
    }
	
	if (!isMonth1Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth2Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth3Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth4Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth5Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth6Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth7Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth8Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth9Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	if (!isMonth10Paid($connection, $id)) {
        header("location: ../payment?error=unpaid");
        exit();
    }
	
	$termQuery = "SELECT term FROM financial_details WHERE id = ?";
    $stmtTerm = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtTerm, $termQuery)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtTerm, "i", $id);
    mysqli_stmt_execute($stmtTerm);
    $resultTerm = mysqli_stmt_get_result($stmtTerm);
    $rowTerm = mysqli_fetch_assoc($resultTerm);

    if ($rowTerm['term'] === '11 Months') {
        $statusUpdate = "UPDATE financial_details SET status = 'Pending', updated_at = CURRENT_TIMESTAMP WHERE term = '11 Months' AND id = ?;";
        $stmtStatusUpdate = mysqli_stmt_init($connection);

        if (!mysqli_stmt_prepare($stmtStatusUpdate, $statusUpdate)) {
            header("location: ../payment?error=stmtfailed");
            exit();
        }

        mysqli_stmt_bind_param($stmtStatusUpdate, "i", $id);
        mysqli_stmt_execute($stmtStatusUpdate);
        mysqli_stmt_close($stmtStatusUpdate);
		
		$applicationUpdate = "UPDATE applications SET status = 'Pending', paid_at = CURRENT_TIMESTAMP WHERE id = ?;";
        $stmtApplicationUpdate = mysqli_stmt_init($connection);

        if (!mysqli_stmt_prepare($stmtApplicationUpdate, $applicationUpdate)) {
            header("location: ../payment?error=stmtfailed");
            exit();
        }

        mysqli_stmt_bind_param($stmtApplicationUpdate, "i", $applicationId);
        mysqli_stmt_execute($stmtApplicationUpdate);
        mysqli_stmt_close($stmtApplicationUpdate);
		
		$lendingAgreementUpdate = "UPDATE lending_agreement SET updated_at = CURRENT_TIMESTAMP WHERE applications_id = ?;";
		$stmtLendingAgreementUpdate = mysqli_stmt_init($connection);

		if (!mysqli_stmt_prepare($stmtLendingAgreementUpdate, $lendingAgreementUpdate)) {
			header("location: ../payment?error=stmtfailed");
			exit();
		}

		mysqli_stmt_bind_param($stmtLendingAgreementUpdate, "i", $applicationId);
		mysqli_stmt_execute($stmtLendingAgreementUpdate);
		mysqli_stmt_close($stmtLendingAgreementUpdate);
    }
	
	$paymentUpdate = "UPDATE financial_details SET month_11 = 'Pending', updated_at = CURRENT_TIMESTAMP WHERE id = ?;";
	$stmtPaymentUpdate = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtPaymentUpdate, $paymentUpdate)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtPaymentUpdate, "i", $id);
    mysqli_stmt_execute($stmtPaymentUpdate);
    mysqli_stmt_close($stmtPaymentUpdate);
		
	$senderCurrentBalance = getSenderCurrentBalance($connection, $sender);

    $senderNewBalance = number_format((float) str_replace(',', '', $senderCurrentBalance) - (float) str_replace(',', '', $amount), 2, '.', ',');

    $queryDeduct = "UPDATE wallet SET balance = ?, updated_at = CURRENT_TIMESTAMP WHERE fullname = ?;";
    $stmtDeduct = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtDeduct, $queryDeduct)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtDeduct, "ss", $senderNewBalance, $sender);
    mysqli_stmt_execute($stmtDeduct);
    mysqli_stmt_close($stmtDeduct);
	
	$receiverCurrentBalance = getReceiverCurrentBalance($connection, $receiver);

    $receiverNewBalance = number_format((float) str_replace(',', '', $receiverCurrentBalance) + (float) str_replace(',', '', $amount), 2, '.', ',');

    $query = "UPDATE wallet SET balance = ?, updated_at = CURRENT_TIMESTAMP WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $receiverNewBalance, $receiver);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

	$balance = number_format((float) str_replace(',', '', $amount), 2, '.', ',');
    $transferMethod = 'paid';
    $walletId = getWalletId($connection, $receiver);
	$senderId = getSenderId($connection, $sender);

    $queryInsert = "INSERT INTO wallet_history (sender, mobile, amount, receiver, transfer_method, wallet_id, sender_id) VALUES (?,?,?,?,?,?,?)";
    $stmtInsert = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtInsert, $queryInsert)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtInsert, "sssssii", $sender, $mobile, $balance, $receiver, $transferMethod, $walletId, $senderId);
    mysqli_stmt_execute($stmtInsert);
    mysqli_stmt_close($stmtInsert);

    header("location: ../payment?success=paid");
    exit();

}

function hasEnoughBalance($connection, $sender, $amount) {
    $senderCurrentBalance = getSenderCurrentBalance($connection, $sender);

    return (float) str_replace(',', '', $senderCurrentBalance) >= (float) str_replace(',', '', $amount);
}

function getSenderCurrentBalance($connection, $sender) {
    $query = "SELECT balance FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $sender);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['balance'];
    } else {
        header("location: ../payment?error=sendernotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth1Paid($connection, $id) {
    $query = "SELECT month_1 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_1'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth2Paid($connection, $id) {
    $query = "SELECT month_2 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_2'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth3Paid($connection, $id) {
    $query = "SELECT month_3 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_3'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth4Paid($connection, $id) {
    $query = "SELECT month_4 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_4'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth5Paid($connection, $id) {
    $query = "SELECT month_5 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_5'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth6Paid($connection, $id) {
    $query = "SELECT month_6 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_6'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth7Paid($connection, $id) {
    $query = "SELECT month_7 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_7'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth8Paid($connection, $id) {
    $query = "SELECT month_8 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_8'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth9Paid($connection, $id) {
    $query = "SELECT month_9 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_9'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function isMonth10Paid($connection, $id) {
    $query = "SELECT month_10 FROM financial_details WHERE id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        if ($row['month_10'] === 'Paid') {
            return true;
        } else {
            return false;
        }
    } else {
        header("location: ../payment?error=recordnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function getReceiverCurrentBalance($connection, $receiver) {
    $query = "SELECT balance FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $receiver);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['balance'];
    } else {
        header("location: ../payment?error=balancenotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function getWalletId($connection, $receiver) {
    $query = "SELECT id FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $receiver);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['id'];
    } else {
        header("location: ../payment?error=walletidnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

function getSenderId($connection, $sender) {
    $query = "SELECT id FROM wallet WHERE fullname = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../payment?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $sender);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row['id'];
    } else {
        header("location: ../payment?error=senderidnotfound");
        exit();
    }

    mysqli_stmt_close($stmt);
}

?>
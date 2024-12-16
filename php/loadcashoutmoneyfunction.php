<?php

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

function emptyPicture($receipt) {
    if (empty($receipt["name"])) {
        return true;
	} else {
        return false;		
 }
}
	
function invalidSize($receipt) {
    $maxFileSize = 2 * 1024 * 1024;
    if ($receipt["size"] > $maxFileSize) {
        return true;
    } else {
        return false;
    }
}

function invalidFormat($receipt) {
    $allowedFormats = array("jpg", "jpeg", "png", "gif");

    $fileExtension = strtolower(pathinfo($receipt["name"], PATHINFO_EXTENSION));

    if (in_array($fileExtension, $allowedFormats)) {
        return false;
    } else {
        return true;
    }
}

function loadCashOutMoney($connection, $name, $sender, $mobile, $balance, $fullname, $receipt, $id) {
	if (isset($_FILES['receipt']['tmp_name'])) {
		$file = $_FILES['receipt']['tmp_name'];
        $receipt = addslashes(file_get_contents($_FILES['receipt']['tmp_name']));
		$receipt_name = addslashes($_FILES['receipt']['name']);
		$upload_dir = "../receipt/";
		$upload_path = $upload_dir . $receipt_name;

		if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                header("location: ../adminwallet?error=directorycreateerror");
                exit();
            }
        }
		
		if (file_exists($upload_path)) {
            $fileExtension = pathinfo($receipt_name, PATHINFO_EXTENSION);
            $receipt_name = pathinfo($receipt_name, PATHINFO_FILENAME) . '_' . time() . '.' . $fileExtension;
            $upload_path = $upload_dir . $receipt_name;
        }

        if (move_uploaded_file($file, "../receipt/" . $receipt_name)) {
			
			$query = "UPDATE cash_loading SET status = 'Deducted', receipt = ?, deducted_at = CURRENT_TIMESTAMP WHERE id = ?";
			$stmt = mysqli_stmt_init($connection);

			if (!mysqli_stmt_prepare($stmt, $query)) {
				header("location: ../adminwallet?error=stmtfailed");
				exit();
			}

			mysqli_stmt_bind_param($stmt, "si", $upload_path, $id);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);

			$currentBalance = getCurrentBalance($connection, $mobile);
	
			if ((float) str_replace(',', '', $currentBalance) < (float) str_replace(',', '', $balance)) {
				header("location: ../adminwallet?error=insufficientuserbalance");
				exit();
			}

			$newBalance = number_format((float) str_replace(',', '', $currentBalance) - (float) str_replace(',', '', $balance), 2, '.', ',');

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
			$transferMethod = 'deducted';
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
			
			$currentVirtualBalance = getCurrentVirtualBalance($connection, $name);

			$newVirtualBalance = number_format((float) str_replace(',', '', $currentVirtualBalance) + (float) str_replace(',', '', $balance), 2, '.', ',');

			$queryAdd = "UPDATE virtual_wallet SET balance = ?, updated_at = CURRENT_TIMESTAMP WHERE name = ?;";
			$stmtAdd = mysqli_stmt_init($connection);

			if (!mysqli_stmt_prepare($stmtAdd, $queryAdd)) {
				header("location: ../adminwallet?error=stmtfailed");
				exit();
			}

			mysqli_stmt_bind_param($stmtAdd, "ss", $newVirtualBalance, $name);
			mysqli_stmt_execute($stmtAdd);
			mysqli_stmt_close($stmtAdd);
			
			$amount = number_format((float) str_replace(',', '', $balance), 2, '.', ',');
			$status = 'Added';
			$transferMethod = 'Cash Out';
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
			
            header("location: ../adminwallet?success=cashout");
			exit();
			}
		}
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
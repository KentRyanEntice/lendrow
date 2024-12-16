<?php
if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function emptyCashInInput($paymentMethod, $amount, $paymentNumber, $paymentAccountName) {
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
	
function cashIn($connection, $fullname, $method, $paymentMethod, $amount, $mobile, $paymentNumber, $paymentAccountName, $status, $walletId) {
	$existingAppQuery = "SELECT id FROM cash_loading WHERE wallet_id = ? AND method = 'CashIn' AND (status <> 'Rejected' AND status <> 'Added' OR status IS NULL)";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $existingAppQuery)) {
		handleCashInError("stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $walletId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        handleCashInError("existingcashin");
        exit();
    }

    mysqli_stmt_close($stmt);
	
	if (isset($_FILES['receipt']['tmp_name'])) {
		$file = $_FILES['receipt']['tmp_name'];
        $receipt = addslashes(file_get_contents($_FILES['receipt']['tmp_name']));
		$receipt_name = addslashes($_FILES['receipt']['name']);
		$upload_dir = "../receipt/";
		$upload_path = $upload_dir . $receipt_name;

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                handleCashInError("directorycreateerror");
                exit();
            }
        }
			
		if (file_exists($upload_path)) {
            $fileExtension = pathinfo($receipt_name, PATHINFO_EXTENSION);
            $receipt_name = pathinfo($receipt_name, PATHINFO_FILENAME) . '_' . time() . '.' . $fileExtension;
            $upload_path = $upload_dir . $receipt_name;
        }

        if (move_uploaded_file($file, "../receipt/" . $receipt_name)) {
		
			$query = "INSERT INTO cash_loading (name, method, payment_method, amount, mobile, payment_number, payment_account_name, receipt, status, wallet_id) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $stmt = mysqli_stmt_init($connection);

            if (!mysqli_stmt_prepare($stmt, $query)) {
                handleCashInError("stmtfailed");
                exit();
            }
			
			$newAmount = number_format((float) str_replace(',', '', $amount), 2, '.', ',');
            mysqli_stmt_bind_param($stmt, "sssssssssi", $fullname, $method, $paymentMethod, $newAmount, $mobile, $paymentNumber, $paymentAccountName, $upload_path, $status, $walletId);
            mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);

			handleCashInSuccess("cashin");
			exit();
		}
	else {
        handleCashInError("receiptuploadfailed");
        exit();
    }
}	
}

?>
<?php
if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

function emptyInput($balance) {
	$result;
	if (empty($balance)){
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

function addVirtualMoney($connection, $name, $balance) {
	$virtualWalletId = getVirtualWalletId($connection, $name);
	
	$existingQuery = "SELECT id FROM virtual_wallet_history WHERE virtual_wallet_id = ? AND status = 'Pending'";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $existingQuery)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $virtualWalletId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
         header("location: ../adminwallet?error=existingvirtualcashin");
        exit();
    }
	
	mysqli_stmt_close($stmt);
	
	$amount = number_format((float) str_replace(',', '', $balance), 2, '.', ',');
	$status = 'Pending';
    $transferMethod = 'Virtual Cash In';

    $queryInsert = "INSERT INTO virtual_wallet_history (amount, status, method, virtual_wallet_id) VALUES (?,?,?,?)";
    $stmtInsert = mysqli_stmt_init($connection);
	
    if (!mysqli_stmt_prepare($stmtInsert, $queryInsert)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtInsert, "sssi", $amount, $status, $transferMethod, $virtualWalletId);
    mysqli_stmt_execute($stmtInsert);
    mysqli_stmt_close($stmtInsert);

    header("location: ../adminwallet?success=virtualcashin");
    exit();
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

?>
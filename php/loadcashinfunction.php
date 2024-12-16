<?php

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: ../home");
    exit;
}

function approve($connection, $name, $amount, $status, $id) {
	if (!hasEnoughVirtualBalance($connection, $name, $amount)) {
        header("location: ../adminwallet?error=insufficientvirtualbalance");
        exit();
    }
	
	$existingAppQuery = "SELECT id FROM cash_loading WHERE method = 'CashIn' AND status = 'Approved'";
    $stmtAppQuery = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtAppQuery, $existingAppQuery)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_execute($stmtAppQuery);
    mysqli_stmt_store_result($stmtAppQuery);

    if (mysqli_stmt_num_rows($stmtAppQuery) > 0) {
        header("location: ../adminwallet?error=existingcashinapproval");
        exit();
    }

    mysqli_stmt_close($stmtAppQuery);

    $query = "UPDATE cash_loading SET status = 'Approved', approved_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: ../adminwallet?success=approvedcashin");
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

function reject($connection, $status, $id) {

    $query = "UPDATE cash_loading SET status = 'Rejected', approved_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../adminwallet?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: ../adminwallet?success=rejectedcashin");
    exit();
}

?>
<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function existingApproved($connection, $mobile) {
	$query = "SELECT id FROM applications WHERE mobile = ? AND status = 'Approved'";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $mobile);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $existingApproved = mysqli_stmt_num_rows($stmt) > 0;

    mysqli_stmt_close($stmt);

    return $existingApproved;
}

function existingDebt($connection, $mobile) {
	$query = "SELECT id FROM applications WHERE mobile = ? AND status = 'Funded'";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        return false;
    }

    mysqli_stmt_bind_param($stmt, "s", $mobile);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $existingDebt = mysqli_stmt_num_rows($stmt) > 0;

    mysqli_stmt_close($stmt);

    return $existingDebt;
}

function approve($connection, $borrowername, $mobile, $lendername, $amount, $interest, $term, $monthly, $status,  $applicationsId, $lendingTermsId) {

    $query = "UPDATE applications SET status = 'Approved', approved_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i",  $applicationsId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
	
	$queryLending = "UPDATE lending_terms SET status = 'Closed' WHERE id = ?";
	
	$stmtLending = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtLending, $queryLending)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }
	
	mysqli_stmt_bind_param($stmtLending, "i",  $lendingTermsId);
    mysqli_stmt_execute($stmtLending);
    mysqli_stmt_close($stmtLending);
	
	$rejectQuery = "UPDATE applications SET status = 'Rejected', approved_at = CURRENT_TIMESTAMP WHERE status = 'Pending' AND id <> ? AND lending_terms_id = ?";
    $rejectStmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($rejectStmt, $rejectQuery)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($rejectStmt, "ii", $applicationsId, $lendingTermsId);
    mysqli_stmt_execute($rejectStmt);
    mysqli_stmt_close($rejectStmt);
	
	$insertQuery = "INSERT INTO lending_agreement (borrowername, mobile, lendername, amount, interest, term, monthly, applications_id, lending_terms_id) VALUES (?,?,?,?,?,?,?,?,?)";
    $insertStmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($insertStmt, $insertQuery)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($insertStmt, "sssssssii", $borrowername, $mobile, $lendername, $amount, $interest, $term, $monthly, $applicationsId, $lendingTermsId);
    mysqli_stmt_execute($insertStmt);
    mysqli_stmt_close($insertStmt);

    header("location: ../lenders?success=approved");
    exit();
}

function reject($connection, $status, $applicationsId) {

    $query = "UPDATE applications SET status = 'Rejected', approved_at = CURRENT_TIMESTAMP WHERE id = ?";

    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../lenders?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $applicationsId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("location: ../lenders?success=rejected");
    exit();
}

?>
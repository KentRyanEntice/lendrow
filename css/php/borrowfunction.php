<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function isLender($connection, $lending_terms_id, $id) {
    $query = "SELECT id FROM lending_terms WHERE id = ? AND users_id = ?";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../borrowers?error=stmtfailed");
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $lending_terms_id, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $isLender = mysqli_stmt_num_rows($stmt) > 0;

    mysqli_stmt_close($stmt);

    return $isLender;
}

function existingApproved($connection, $id) {
	$query = "SELECT id FROM applications WHERE users_id = ? AND status = 'Approved'";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../borrowers?error=stmtfailed");
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $existingApproved = mysqli_stmt_num_rows($stmt) > 0;

    mysqli_stmt_close($stmt);

    return $existingApproved;
}

function existingDebt($connection, $id) {
	$query = "SELECT id FROM applications WHERE users_id = ? AND status = 'Funded'";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../borrowers?error=stmtfailed");
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $existingDebt = mysqli_stmt_num_rows($stmt) > 0;

    mysqli_stmt_close($stmt);

    return $existingDebt;
}

function borrow($connection, $picture, $borrowername, $mobile, $status, $lending_terms_id, $id) {
    $existingAppQuery = "SELECT id FROM applications WHERE lending_terms_id = ? AND users_id = ? AND (status <> 'Cancelled' AND status <> 'Rejected' AND status <> 'Paid' OR status IS NULL)";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $existingAppQuery)) {
        header("location: ../borrowers?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ii", $lending_terms_id, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        header("location: ../borrowers?error=existingapplication");
        exit();
    }

    mysqli_stmt_close($stmt);

    $insertQuery = "INSERT INTO applications (picture, borrowername, mobile, status, lending_terms_id, users_id) VALUES (?,?,?,?,?,?)";
    $insertStmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($insertStmt, $insertQuery)) {
        header("location: ../borrowers?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($insertStmt, "ssssii", $picture, $borrowername, $mobile, $status, $lending_terms_id, $id);
    mysqli_stmt_execute($insertStmt);
    mysqli_stmt_close($insertStmt);

    header("location: ../borrowers?success=applied");
    exit();
}

?>
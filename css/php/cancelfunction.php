<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function cancel($connection, $id) {
	
	$fundedAppQuery = "SELECT id FROM applications WHERE id = ? AND (status <> 'Pending' AND status <> 'Approved' AND status <> 'Rejected' AND status <> 'Paid' OR status IS NULL)";
    $stmtFunded = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtFunded, $fundedAppQuery)) {
        header("location: ../borrowers?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtFunded, "i", $id);
    mysqli_stmt_execute($stmtFunded);
    mysqli_stmt_store_result($stmtFunded);

    if (mysqli_stmt_num_rows($stmtFunded) > 0) {
        header("location: ../borrowers?error=fundedapplication");
        exit();
    }
	
	 mysqli_stmt_close($stmtFunded);
	
	$rejectedAppQuery = "SELECT id FROM applications WHERE id = ? AND (status <> 'Pending' AND status <> 'Approved' AND status <> 'Funded' AND status <> 'Paid' OR status IS NULL)";
    $stmtRejected = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtRejected, $rejectedAppQuery)) {
        header("location: ../borrowers?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmtRejected, "i", $id);
    mysqli_stmt_execute($stmtRejected);
    mysqli_stmt_store_result($stmtRejected);

    if (mysqli_stmt_num_rows($stmtRejected) > 0) {
        header("location: ../borrowers?error=rejectedapplication");
        exit();
    }

    mysqli_stmt_close($stmtRejected);

		$cancel = "UPDATE applications SET status = 'Cancelled', created_at = CURRENT_TIMESTAMP WHERE id = ?;";
		$stmtCancel = mysqli_stmt_init($connection);

		if (!mysqli_stmt_prepare($stmtCancel, $cancel)) {
			header("location: ../borrowers?error=stmtfailed");
			exit();
		}

		mysqli_stmt_bind_param($stmtCancel, "i", $id);
		mysqli_stmt_execute($stmtCancel);
		mysqli_stmt_close($stmtCancel);
		
		header("location: ../borrowers?success=cancelled");
    exit();
}
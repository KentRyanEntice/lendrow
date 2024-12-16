<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}


function updateInterest2($connection, $applicationId, $id) {

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
	
	if ($rowTerm['term'] === '2 Months') {
        $statusUpdate = "UPDATE financial_details SET status = 'Paid', updated_at = CURRENT_TIMESTAMP WHERE term = '2 Months' AND id = ?;";
        $stmtStatusUpdate = mysqli_stmt_init($connection);

        if (!mysqli_stmt_prepare($stmtStatusUpdate, $statusUpdate)) {
            header("location: ../payment?error=stmtfailed");
            exit();
        }

        mysqli_stmt_bind_param($stmtStatusUpdate, "i", $id);
        mysqli_stmt_execute($stmtStatusUpdate);
        mysqli_stmt_close($stmtStatusUpdate);
	
		
		$applicationUpdate = "UPDATE applications SET status = 'Paid', paid_at = CURRENT_TIMESTAMP WHERE id = ?;";
        $stmtApplicationUpdate = mysqli_stmt_init($connection);

        if (!mysqli_stmt_prepare($stmtApplicationUpdate, $applicationUpdate)) {
            header("location: ../payment?error=stmtfailed");
            exit();
        }

        mysqli_stmt_bind_param($stmtApplicationUpdate, "i", $applicationId);
        mysqli_stmt_execute($stmtApplicationUpdate);
        mysqli_stmt_close($stmtApplicationUpdate);
		
	}
		
		$paymentUpdate = "UPDATE financial_details SET month_2 = 'Paid', updated_at = CURRENT_TIMESTAMP WHERE id = ?;";
		$stmtPaymentUpdate = mysqli_stmt_init($connection);

		if (!mysqli_stmt_prepare($stmtPaymentUpdate, $paymentUpdate)) {
			header("location: ../payment?error=stmtfailed");
			exit();
		}

		mysqli_stmt_bind_param($stmtPaymentUpdate, "i", $id);
		mysqli_stmt_execute($stmtPaymentUpdate);
		mysqli_stmt_close($stmtPaymentUpdate);
		
		header("location: ../payment?success=update");
    exit();
}

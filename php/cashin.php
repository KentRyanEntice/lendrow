<?php
session_start();
include 'config.php';
include 'cashinfunctions.php';
	
if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    handleCashInError("wallet_duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cashin'])) {
	
	$walletId = $_POST["wallet_id"];
	$fullname = $_POST["fullname"];
	$method = $_POST["method"];
	$paymentMethod = $_POST["payment_method"];
	$amount = $_POST["amount"];
	$mobile = $_POST["mobile"];
	$paymentNumber = $_POST["payment_number"];
	$paymentAccountName = $_POST["payment_account_name"];
	$receipt = $_FILES["receipt"];
	$status = $_POST["status"];
	
	if (emptyCashInInput($paymentMethod, $amount, $paymentNumber, $paymentAccountName) !== false) {
        handleCashInError("emptycashininput");
    }

    if (invalidAmountInput($amount) !== false) {
        handleCashInError("invalidamountinput");
    }

    if (invalidPaymentMobile($paymentNumber) !== false) {
        handleCashInError("invalidpaymentmobile");
    }
	
	if (emptyPicture($receipt) !== false) {
        handleCashInError("emptyreceipt");
        exit();
    }
	
	if (invalidSize($receipt) !== false) {
        handleCashInError("invalidsize");
        exit();
    }
	
	if (invalidFormat($receipt) !== false) {
		handleCashInError("invalidformat");
		exit();
	}

    cashIn($connection, $fullname, $method, $paymentMethod, $amount, $mobile, $paymentNumber, $paymentAccountName, $status, $walletId);

	
	$_SESSION['last_submission_time'] = time();
	exit();
} 

	else {
		header("Location: wallet");
		exit();
}

function handleCashInError($errorType) {
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'wallet';
    $parsedUrl = parse_url($referrer);

    $path = $parsedUrl['path'];
    $query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';

    parse_str($query, $queryParams);
    unset($queryParams['error']);
	unset($queryParams['success']);
    $newQuery = http_build_query($queryParams);

    $redirectUrl = $path . ($newQuery ? '?' . $newQuery : '');

    if ($newQuery) {
        $redirectUrl .= '&error=' . $errorType;
    } else {
        $redirectUrl .= '?error=' . $errorType;
    }

    header("Location: $redirectUrl");
    exit();
}

function handleCashInSuccess($successType) {
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'wallet';
    $parsedUrl = parse_url($referrer);

    $path = $parsedUrl['path'];
    $query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';

    parse_str($query, $queryParams);
    unset($queryParams['error']);
	unset($queryParams['success']);
    $newQuery = http_build_query($queryParams);

    $redirectUrl = $path . ($newQuery ? '?' . $newQuery : '');

    if ($newQuery) {
        $redirectUrl .= '&success=' . $successType;
    } else {
        $redirectUrl .= '?success=' . $successType;
    }

    header("Location: $redirectUrl");
    exit();
}

?>
	
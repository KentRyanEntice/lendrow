<?php
session_start();
include 'config.php';
include 'cashoutfunctions.php';
	
if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

if (isset($_SESSION['last_submission_time']) && time() - $_SESSION['last_submission_time'] < 5) {
    handleCashOutError("wallet_duplicate_submission");
    exit();
}

$_SESSION['last_submission_time'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cashout'])){
	
	$walletId = $_POST["wallet_id"];
	$fullname = $_POST["fullname"];
	$method = $_POST["method"];
	$paymentMethod = $_POST["payment_method"];
	$amount = $_POST["amount"];
	$mobile = $_POST["mobile"];
	$paymentNumber = $_POST["payment_number"];
	$paymentAccountName = $_POST["payment_account_name"];
	$receipt = $_POST["receipt"];
	$status = $_POST["status"];
	
	 if (emptyCashOutInput($paymentMethod, $amount, $paymentNumber, $paymentAccountName) !== false) {
        handleCashOutError("emptycashoutinput");
    }

    if (invalidAmountInput($amount) !== false) {
        handleCashOutError("invalidamountinput");
    }

    if (invalidPaymentMobile($paymentNumber) !== false) {
        handleCashOutError("invalidpaymentmobile");
    }

    cashOut($connection, $fullname, $method, $paymentMethod, $amount, $mobile, $paymentNumber, $paymentAccountName, $receipt, $status, $walletId);
	
	$_SESSION['last_submission_time'] = time();
	exit();
} 

	else {
		header("Location: wallet");
		exit();
}

function handleCashOutError($errorType) {
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

function handleCashOutSuccess($successType) {
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
	
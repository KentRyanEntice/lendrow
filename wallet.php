<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'php/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: home");
    exit;
}

	$username = mysqli_real_escape_string($connection, $_SESSION['username']);

	$queryUserData = "SELECT * FROM users WHERE username='$username'";
	$resultUserData = mysqli_query($connection, $queryUserData);

	if ($resultUserData) {
		$userData = mysqli_fetch_assoc($resultUserData);

		$userId = $userData['id'];
		$queryWallet = "SELECT balance FROM wallet WHERE users_id='$userId'";
			$resultWallet = mysqli_query($connection, $queryWallet);

			if ($resultWallet) {
				$walletData = mysqli_fetch_assoc($resultWallet);
				$userBalance = $walletData['balance'];
			} else {
				echo "Error retrieving user wallet data!" . mysqli_error($connection);
			}
		} else {
			echo "Error retrieving user data!" . mysqli_error($connection);
		}
		
function getWallet($searchWallet = '')
	{
		global $connection, $userData;

		$userId = $userData['id'];

		$query = "SELECT * FROM wallet WHERE users_id = '$userId'";

		if (!empty($searchWallet)) {
			$searchWallet = mysqli_real_escape_string($connection, $searchWallet);
			$query .= " AND (fullname LIKE '%$searchWallet' OR mobile LIKE '%$searchWallet%' OR balance LIKE '%$searchWallet%' OR created_at LIKE '%$searchWallet%')";
		}

		$query .= " ORDER BY created_at DESC";
		$result = mysqli_query($connection, $query);
		$wallet = array();

		while ($row = mysqli_fetch_assoc($result)) {
			$wallet[] = $row;
		}

		return $wallet;
	}

function getWalletHistory($walletId, $senderId, $searchHistory = '') {
    global $connection;

    $walletId = mysqli_real_escape_string($connection, $walletId);
	$senderId = mysqli_real_escape_string($connection, $senderId);

    $query = "SELECT * FROM wallet_history WHERE wallet_id = '$walletId' OR sender_id = '$senderId'";

    if (!empty($searchHistory)) {
        $searchHistory = mysqli_real_escape_string($connection, $searchHistory);
        $query .= " AND (sender LIKE '%$searchHistory%' OR mobile LIKE '%$searchHistory%' OR amount LIKE '%$searchHistory%' OR receiver LIKE '%$searchHistory%' OR transfer_method LIKE '%$searchHistory%' OR created_at LIKE '%$searchHistory%')";
    }

    $query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $walletHistory = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $walletHistory[] = $row;
    }

    return $walletHistory;
}

function getRequest($walletId, $searchRequest = '')
{
    global $connection, $userData;
	
	$userId = $userData['id'];
	
    $query = "SELECT * FROM cash_loading WHERE wallet_id = $userId";
    
    if (!empty($searchRequest)) {
        $searchRequest = mysqli_real_escape_string($connection, $searchRequest);
        $query .= " WHERE name LIKE '%$searchRequest%' OR method LIKE '%$searchRequest%' OR payment_method LIKE '%$searchRequest%' OR amount LIKE '%$searchRequest%' OR payment_number LIKE '%$searchRequest%' OR receipt LIKE '%$searchRequest%' OR status LIKE '%$searchRequest%' OR created_at LIKE '%$searchRequest%'";
    }
    
	$query .= " ORDER BY created_at DESC, approved_at DESC, added_at DESC, deducted_at DESC";
    $result = mysqli_query($connection, $query);
    $request = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $request[] = $row;
    }

    return $request;
}

?>

	<!DOCTYPE HTML>
	<html lang="en">
	<head>
		<title>LendRow Wallet</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" href="pictures/logo.png" type="image/x-icon">
		<link rel="stylesheet" type="text/css" href="css/wallet.css">
		<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
		<link rel="stylesheet" href="css/swiper-bundle.min.css">
	</head>

	<body>
	
	
	
		<div class="wallet-button" onclick="history.back(0)">-<span class="back">Return</span></div>
		
			<div class="wallet-form" id="walletForm">
				<div class="wallet-info">
				
					<div class="account">
						
						<div class="balance">
							<div class="inputBox">
								<h2>Account Balance</h2>
									<label>PHP</label>
										<input type="text" name="balance" id="balance" value="<?php echo htmlspecialchars($userBalance); ?>" readonly>
							</div>
						</div>
						
						<div class="wallet-errors">
							<?php
								if(isset($_GET["error"])) {
									if ($_GET["error"] == "emptycashininput") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>There are empty fields, please fill in all fields!</p>";
									}
									
									if ($_GET["error"] == "emptyreceipt") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Please insert a receipt to upload!</p>";
									}
									
									if ($_GET["error"] == "invalidsize") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Please insert receipt below 2MB!</p>";
									}
									
									if ($_GET["error"] == "invalidformat") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Please insert correct image format!</p>";
									}
									
									else if ($_GET["error"] == "wallet_duplicate_submission") {
										$resubmitTime = $_SESSION['last_submission_time'] + 5;

										$remainingTime = max(0, $resubmitTime - time());

										$minutes = floor($remainingTime / 60);
										$seconds = $remainingTime % 60;

										echo "<i class='bx bxs-error-circle'></i><p class='red'>Duplicate Submission, please wait for ";
										echo $seconds . "s before resubmitting.</p>";
									}
									
									else if ($_GET["error"] == "emptycashoutinput") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>There are empty fields, please fill in all fields!</p>";
									}
									
									else if ($_GET["error"] == "invalidamountinput") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Incorrect Amount Format, Please enter numbers only!</p>";
									}
									
									else if ($_GET["error"] == "invalidpaymentmobile") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Mobile No., Please use correct mobile no. format!</p>";
									}
									
									else if ($_GET["error"] == "existingcashin") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Cash In has been unsuccessful since you have an existing Request.</p>";
									}
									
									else if ($_GET["error"] == "existingcashout") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Cash Out has been unsuccessful since you have an existing Request.</p>";
									}
									
									else if ($_GET["error"] == "cashoutinsufficientbalance") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Cash Out has been unsuccessful since your balance is insufficient.</p>";
									}
									
									else if ($_GET["error"] == "fileuploadfailed") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Cash In has been unsuccessful, please insert valid receipt format!</p>";
									}									
								}
								
								if(isset($_GET["success"])) {
									if ($_GET["success"] == "cashin") {
										echo "<i class='bx bxs-check-circle'></i><p class='blue'>Congratulations! Your Cash In Request has been added successfully.</p>";
									}
									
									else if ($_GET["success"] == "cashout") {
										echo "<i class='bx bxs-check-circle'></i><p class='blue'>Congratulations! Your Cash Out Request has been added successfully.</p>";
									}
								}
							?>
				
						</div>
						
						<div class="accountbuttons">
							<button class="transfer">
								Send Money
							</button>
							<button class="cashin" id="showCashIn">
								Cash In
							</button>
							<button class="cashout" id="showCashOut">
								Cash Out
							</button>
						</div>
						
					</div>
					
						<div class="transaction-history active" id="transactionHistory">
						<div class="history-form">
							<h2>Transaction History
								<button class="history" id="showRequest">
									Requests
								</button>
							</h2>
							
							<div class="history-content">
							 <?php
								$searchWallet = isset($_GET['searchWallet']) ? $_GET['searchWallet'] : '';
								$wallets = getWallet($searchWallet);
								
								$historyFound = false;

								foreach ($wallets as $wallet) {
									$walletId = $wallet['id'];
									$senderId = $wallet['id'];
									
							?>
										
										<?php
											$searchHistory = isset($_GET['searchHistory']) ? $_GET['searchHistory'] : '';
											$walletsHistory = getWalletHistory($walletId, $senderId, $searchHistory);
											
											if (!empty($walletsHistory)) {
											
											foreach ($walletsHistory as $walletHistory) {
											$walletId = $walletHistory['id'];
											
											if ($walletHistory['transfer_method'] == 'added' && $walletHistory['wallet_id'] == $wallet['id']) {
												?>
												
												<div class="transaction">
													<div class="added"></div>
													
														<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
														<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
														
														<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
														
														<p>PHP <?php echo $walletHistory['amount']; ?> has been <?php echo $walletHistory['transfer_method']; ?> by LendRow to your Wallet.<br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
														
												</div>
												<?php
												}
												
												elseif ($walletHistory['transfer_method'] == 'added' && $walletHistory['sender_id'] == $wallet['id']) {
												?>
												
												<div class="transaction">
													<div class="added"></div>
													
														<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
														<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
														
														<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
														
														<p>You <?php echo $walletHistory['transfer_method']; ?> PHP <?php echo $walletHistory['amount']; ?> to <?php echo $walletHistory['receiver']; ?>'s Wallet.<br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
														
												</div>
												<?php
												}
												
												elseif ($walletHistory['transfer_method'] == 'deducted' && $walletHistory['wallet_id'] == $wallet['id']) {
												?>
												
												<div class="transaction">
													<div class="deducted"></div>
													
														<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
														<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
														
														<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
														
														<p>PHP <?php echo $walletHistory['amount']; ?> has been <?php echo $walletHistory['transfer_method']; ?> by LendRow from your Wallet.<br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
														
												</div>
												<?php
												}
												
												elseif ($walletHistory['transfer_method'] == 'deducted' && $walletHistory['sender_id'] == $wallet['id']) {
												?>
												
												<div class="transaction">
													<div class="deducted"></div>
													
														<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
														<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
														
														<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
														
														<p>You <?php echo $walletHistory['transfer_method']; ?> PHP <?php echo $walletHistory['amount']; ?> from <?php echo $walletHistory['receiver']; ?>'s Wallet.<br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
														
												</div>
												<?php
												}
												
													elseif ($walletHistory['transfer_method'] == 'disbursed' && $walletHistory['sender_id'] == $wallet['id']) {
													?>
														<div class="transaction">
														<div class="disbursed"></div>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
															
															<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
														
															<p>PHP <?php echo $walletHistory['amount']; ?> has been <?php echo $walletHistory['transfer_method']; ?> to <?php echo $walletHistory['receiver']; ?><br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
															
														</div>
														<?php
													}
													
													 elseif ($walletHistory['transfer_method'] == 'disbursed' && $walletHistory['sender_id'] != $wallet['id']) {
													?>
														<div class="transaction">
														<div class="disbursed"></div>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
															
															<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
														
															<p>PHP <?php echo $walletHistory['amount']; ?> has been <?php echo $walletHistory['transfer_method']; ?> from <?php echo $walletHistory['sender']; ?><br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
															
														</div>
														<?php
													}
													
													elseif ($walletHistory['transfer_method'] == 'paid' && $walletHistory['sender_id'] == $wallet['id']) {
													?>
														<div class="transaction">
														<div class="received"></div>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
															
															<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
									
															<p>PHP <?php echo $walletHistory['amount']; ?> has been <?php echo $walletHistory['transfer_method']; ?> to <?php echo $walletHistory['receiver']; ?><br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
															
														</div>
													<?php
													}
													
													elseif ($walletHistory['transfer_method'] == 'paid' && $walletHistory['sender_id'] != $wallet['id']) {
													?>
														<div class="transaction">
														<div class="received"></div>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
															
															<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
														
															<p>PHP <?php echo $walletHistory['amount']; ?> has been <?php echo $walletHistory['transfer_method']; ?> from <?php echo $walletHistory['sender']; ?><br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
															
														</div>
													<?php
													}
													
													elseif ($walletHistory['transfer_method'] == 'lending' && $walletHistory['sender_id'] == $wallet['id']) {
													?>
														<div class="transaction">
														<div class="added"></div>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['id']; ?>" disabled>
														
															<input type="hidden" placeholder="<?php echo $walletHistory['wallet_id']; ?>" disabled>
															
															<input type="hidden" placeholder="<?php echo $walletHistory['sender_id']; ?>" disabled>
														
															<p>PHP <?php echo $walletHistory['amount']; ?> has been Deducted from your Wallet through Lending.<br><?php echo date("F d, Y h:i A", strtotime($walletHistory['created_at'])); ?></p>
															
														</div>
													<?php
													}
												}
												 $historyFound = true;
												}
												?>
											
									<?php
										if (!$historyFound) {
											echo '<p class="empty">No wallet history can be found.</p>';
										}
								}
								?>	
								
								</div>

							</div>
						</div>
						
						<div class="request-history" id="requestHistory">
						<div class="history-form">
							<h2>Request History
								<button class="history" id="showTransaction">
									Transactions
								</button>
							</h2>
							
							<div class="history-content">
							
									<?php
										$searchRequest = isset($_GET['searchRequest']) ? $_GET['searchRequest'] : '';
										$requests = getRequest($walletId, $searchRequest);
																
											if (empty($requests)) {
												echo '<p class="empty">There is no existing Request History.</p>';
											} else {

										foreach ($requests as $request) {
										$userId = $request['wallet_id'];
					
										if ($request['status'] == 'Pending' && $request['method'] == 'CashIn') {
										?>
											<div class="mycashin">
											<div class="pending"></div>
																
											<input type="hidden" placeholder="<?php echo $request['id']; ?>" disabled>					
																	
											<p>You had a <?php echo $request['status']; ?> Cash In Request!<br><?php echo date("F d, Y h:i A", strtotime($request['created_at'])); ?></p>
											
											<div class="mycard-view">
												<button class="myview" onclick="showCashIn(<?php echo $request['id'];?>)">View</button>
											</div>
											
											</div>
										<?php
										}
										elseif ($request['status'] == 'Pending' && $request['method'] == 'CashOut') {
										?>
											<div class="mycashin">
											<div class="pending"></div>
																
											<input type="hidden" placeholder="<?php echo $request['id']; ?>" disabled>					
																	
											<p>You had a <?php echo $request['status']; ?> Cash Out Request!<br><?php echo date("F d, Y h:i A", strtotime($request['created_at'])); ?></p>
											
											<div class="mycard-view">
												<button class="myview" onclick="showCashOut(<?php echo $request['id'];?>)">View</button>
											</div>
											
											</div>
										<?php
										}
										elseif ($request['status'] == 'Approved' && $request['method'] == 'CashIn') {
										?>
											<div class="mycashin">
											<div class="approved"></div>
																
											<input type="hidden" placeholder="<?php echo $request['id']; ?>" disabled>					
																	
											<p>Your Cash In Request has been <?php echo $request['status']; ?>!<br><?php echo date("F d, Y h:i A", strtotime($request['approved_at'])); ?></p>
																	
											</div>
										<?php				
										}
										elseif ($request['status'] == 'Approved' && $request['method'] == 'CashOut') {
										?>
											<div class="mycashin">
											<div class="approved"></div>
																
											<input type="hidden" placeholder="<?php echo $request['id']; ?>" disabled>					
																	
											<p>Your Cash Out Request has been <?php echo $request['status']; ?>!<br><?php echo date("F d, Y h:i A", strtotime($request['approved_at'])); ?></p>
																	
											</div>
										<?php				
										}
										elseif ($request['status'] == 'Rejected' && $request['method'] == 'CashIn') {
										?>
											<div class="mycashin">
											<div class="rejected"></div>
																
											<input type="hidden" placeholder="<?php echo $request['id']; ?>" disabled>					
																	
											<p>Your Cash In Request has been <?php echo $request['status']; ?>!<br><?php echo date("F d, Y h:i A", strtotime($request['approved_at'])); ?></p>
																	
											</div>
										<?php				
										}
										elseif ($request['status'] == 'Rejected' && $request['method'] == 'CashOut') {
										?>
											<div class="mycashin">
											<div class="rejected"></div>
																
											<input type="hidden" placeholder="<?php echo $request['id']; ?>" disabled>					
																	
											<p>Your Cash Out Request has been <?php echo $request['status']; ?>!<br><?php echo date("F d, Y h:i A", strtotime($request['approved_at'])); ?></p>
																	
											</div>
										<?php				
										}
										elseif ($request['status'] == 'Added' && $request['method'] == 'CashIn') {
										?>
											<div class="mycashin">
											<div class="added"></div>
																
											<input type="hidden" placeholder="<?php echo $request['id']; ?>" disabled>					
																	
											<p>PHP <?php echo $request['amount']; ?> has been <?php echo $request['status']; ?> to Your Cash In Request!<br><?php echo date("F d, Y h:i A", strtotime($request['added_at'])); ?></p>
																	
											</div>
										<?php				
										}
										elseif ($request['status'] == 'Deducted' && $request['method'] == 'CashOut') {
										?>
											<div class="mycashin">
											<div class="deducted"></div>
																
											<input type="hidden" placeholder="<?php echo $request['id']; ?>" disabled>					
																	
											<p>PHP <?php echo $request['amount']; ?> has been <?php echo $request['status']; ?> from Your Cash Out Request!<br><?php echo date("F d, Y h:i A", strtotime($request['deducted_at'])); ?></p>
											
											<div class="mycard-view">
												<button class="myview" onclick="showCashOut(<?php echo $request['id'];?>)">View</button>
											</div>
																	
											</div>
										<?php				
										}
										}
											
								}
								?>	
								
								</div>

							</div>
						</div>
						
					</div>
					
				</div>
				
				
				<div class="overlay-bgwallet" id="overlayBgwallet"></div>
				<div class="cashin-form" id="cashInForm">
				
					<h2>Cash In Request Form</h2>
					<div class="cashin-form-info">	
						 <form id="myForm" action="php/cashin" method="POST" enctype="multipart/form-data">
						 
						 <input type="hidden" name="wallet_id" id="wallet_id" value="<?php echo $wallet['id']; ?>">
						 
						 <input type="hidden" name="fullname" id="fullname" value="<?php echo $wallet['fullname']; ?>">
						 
						 <input type="hidden" name="mobile" id="mobile" value="<?php echo $wallet['mobile']; ?>">
						 
						 <input type="hidden" name="method" id="method" value="CashIn">
						 
						 <input type="hidden" name="status" id="status" value="Pending">
						 
						 
							<div class="inputBox">
								<i class='bx bx-credit-card'></i>
								<select name="payment_method" id="payment_method">
									<option value="" disabled selected>Select Cash In Payment Method</option>
									<option value="GCash LendRow - 09100119667">GCash LendRow - 09100119667</option>
									<option value="Maya LendRow - 09100119667">Maya LendRow - 09100119667</option>
								</select>
							</div>
							
							<div class="inputBox">
								<i class='bx bxl-product-hunt'></i>
								<input type="text" name="amount" id="amount" placeholder="Enter Cash In Amount">
							</div>
							
							<div class="inputBox">
								<i class='bx bxs-phone'></i>
								<input type="text" name="payment_number" id="payment_number" placeholder="Enter your Payment Mobile No.">
							</div>
							
							<div class="inputBox">
								<i class='bx bxs-user'></i>
								<input type="text" name="payment_account_name" id="payment_account_name" placeholder="Enter your Payment Account Name">
							</div>
						
							<div class="inputBox">
								<label>Insert Payment Receipt (less than 2MB)</label>
								<i class='bx bxs-image-add'></i>
								<input class="file" type="file" name="receipt" id="receipt" placeholder="Payment Receipt"  accept="image/*">
							</div>
							
							<div class="buttons">
								<div class="cancel" onclick="hideCashInForm()">Cancel</div>
								<button type="submit" name="cashin" class="create">Submit</button>
							</div>
						</form>
					</div>
				
				</div>
				
				
				<?php
					$searchRequest = isset($_GET['searchRequest']) ? $_GET['searchRequest'] : '';
					$requests = getRequest($walletId, $searchRequest);

					foreach ($requests as $request) {
					$userId = $request['wallet_id'];
						
				?>
				
				<div class="cashin-card" id="cashInCard<?php echo $request['id']; ?>">
					
					<h2>Cash In Information</h2>
					<form action="#" method="POST">
				
					<input type="hidden" name="id" id="id" value="<?php echo $request['id']; ?>">
					
					<input type="hidden" name="wallet_id" id="wallet_id" value="<?php echo $request['wallet_id']; ?>">
					
					<input type="hidden" name="mobile" id="mobile" value="<?php echo $request['mobile']; ?>">
					
					<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
					
					<div class="info1">
						<label><?php echo date("F d, Y h:i A", strtotime($request['created_at'])); ?></label>
					</div>
					
					<div class="info">
						<label>Cash In Method : </label><input type="text" value="<?php echo $request['payment_method']; ?>" readonly>
					</div>
					
					<div class="info">
						<label>Cash In Amount : </label><input type="text" value="<?php echo $request['amount']; ?>" readonly>
					</div>
					
					<div class="info">
						<label>Payment No : </label><input type="text" value="<?php echo $request['payment_number']; ?>" readonly>
					</div>
					
					<div class="info">
						<label>Account Name : </label><input type="text" name="payment_account_name" value="<?php echo $request['payment_account_name']; ?>" readonly>
					</div>
					
					<div class="info">
						<label>Receipt : </label>
					</div>
					
					<div class="receipt" onclick="showReceipt(<?php echo $request['id'];?>)">
					<?php
						if (!empty($request['receipt'])) {
						$receiptPath = './/' . $request['receipt'];
							echo '<img src="' . $receiptPath . '" alt="Receipt">';
						} else {
							echo 'No receipt available';
						}
						?>
					</div>
					
					
					<div class="fund-card-button">
						<div class="close" onclick="hideCashIn(<?php echo $request['id'];?>)">Close</div>
					</div>

				</form>
				</div>
				
				<?php
				}
				?>
				
				<div class="overlay-bg2" id="overlayBg2"></div>
					
					<?php
						$searchRequest = isset($_GET['searchRequest']) ? $_GET['searchRequest'] : '';
						$requests = getRequest($walletId, $searchRequest);

						foreach ($requests as $request) {
						$userId = $request['wallet_id'];	
					?>
					<div class="receipt2" id="receipt2<?php echo $request['id']; ?>">
					
					<div class="receipt-card">
						<?php
						if (!empty($request['receipt'])) {
						$receiptPath = './/' . $request['receipt'];
							echo '<img src="' . $receiptPath . '" alt="Receipt">';
						} else {
							echo 'No image';
						}
						?>
					</div>
					
					<div class="close-button">
						<div class="close" onclick="hideReceipt(<?php echo $request['id'];?>)">Close</div>
					</div>
					</div>
					<?php
					}
					?>
					
					<div class="cashin-form" id="cashOutForm">
				
					<h2>Cash Out Request Form</h2>
					<div class="cashin-form-info">	
						 <form action="php/cashout" method="POST" enctype="multipart/form-data">
						 
						 <input type="hidden" name="wallet_id" id="wallet_id" value="<?php echo $wallet['id']; ?>">
						 
						 <input type="hidden" name="mobile" id="mobile" value="<?php echo $wallet['mobile']; ?>">
						 
						 <input type="hidden" name="method" id="method" value="CashOut">
						 
						 <input type="hidden" name="status" id="status" value="Pending">
						 
						 <input type="hidden" name="receipt" id="receipt" value="None">
						 
						 <input type="hidden" name="fullname" id="fullname" value="<?php echo $wallet['fullname']; ?>">
						 
						 <div class="cash-out">
						 
							<div class="inputBox">
								<i class='bx bx-credit-card'></i>
								<select name="payment_method" id="payment_method">
									<option value="" disabled selected>Select Cash Out Payment Method</option>
									<option value="GCash">GCash</option>
									<option value="Maya">Maya</option>
								</select>
							</div>
							
							<div class="inputBox">
								<i class='bx bxl-product-hunt'></i>
								<input type="text" name="amount" id="amount" placeholder="Enter Cash Out Amount">
							</div>
							
							<div class="inputBox">
								<i class='bx bxs-phone'></i>
								<input type="text" name="payment_number" id="payment_number" placeholder="Enter your Payment Mobile No.">
							</div>

							<div class="inputBox">
								<i class='bx bxs-user' ></i>
								<input type="text" name="payment_account_name" id="payment_account_name" placeholder="Enter your Payment Account Name">
							</div>
							
							<div class="hidden">
							<div class="inputBox">
								<label>Insert Payment Receipt (less than 5MB)</label>
								<i class='bx bxs-image-add'></i>
								<input class="file" type="file" name="receipt" id="receipt" placeholder="Payment Receipt" accept="image/*">
							</div>
							</div>
							
							</div>
							
							<div class="buttons">
								<div class="cancel" onclick="hideCashOutForm()">Cancel</div>
								<button type="submit" name="cashout" class="create">Submit</button>
							</div>
						</form>
					</div>
				
				</div>
				
				<?php
					$searchRequest = isset($_GET['searchRequest']) ? $_GET['searchRequest'] : '';
					$requests = getRequest($walletId, $searchRequest);

					foreach ($requests as $request) {
					$userId = $request['wallet_id'];
						
				?>
				
				<div class="cashin-card" id="cashOutCard<?php echo $request['id']; ?>">
					
					<h2>Cash Out Information</h2>
					<form action="#" method="POST">
				
					<input type="hidden" name="id" id="id" value="<?php echo $request['id']; ?>">
					
					<input type="hidden" name="wallet_id" id="wallet_id" value="<?php echo $request['wallet_id']; ?>">
					
					<input type="hidden" name="mobile" id="mobile" value="<?php echo $request['mobile']; ?>">
					
					<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
					
					<div class="info1">
						<label><?php echo date("F d, Y h:i A", strtotime($request['created_at'])); ?></label>
					</div>
					
					<input type="hidden" name="receiver" value="<?php echo $request['name']; ?>" readonly>
					
					<div class="info">
						<label>Cash Out Method : </label><input type="text" value="<?php echo $request['payment_method']; ?>" readonly>
					</div>
					
					<div class="info">
						<label>Cash Out Amount : </label><input type="text" value="<?php echo $request['amount']; ?>" readonly>
					</div>
					
					<div class="info">
						<label>Payment No : </label><input type="text" value="<?php echo $request['payment_number']; ?>" readonly>
					</div>
					
					<div class="info">
						<label>Account Name : </label><input type="text" value="<?php echo $request['payment_account_name']; ?>" readonly>
					</div>

					<div class="info">
						<label>Receipt : </label>
					</div>
					
					<div class="receipt" onclick="showCashOutReceipt(<?php echo $request['id'];?>)">
					<?php
						if (!empty($request['receipt'])) {
						$receiptPath = './/' . $request['receipt'];
							echo '<img src="' . $receiptPath . '" alt="Receipt">';
						} else {
							echo 'No image';
						}
						?>
					</div>
					
					
					<div class="fund-card-button">
						<div class="close" onclick="hideCashOut(<?php echo $request['id'];?>)">Close</div>
					</div>

				</form>
				</div>
				
				<?php
				}
				?>
				
				<div class="overlay-bg2" id="overlayBg2"></div>
					
					<?php
						$searchRequest = isset($_GET['searchRequest']) ? $_GET['searchRequest'] : '';
						$requests = getRequest($walletId, $searchRequest);

						foreach ($requests as $request) {
						$userId = $request['wallet_id'];	
					?>
					<div class="receipt2" id="cashOutReceipt<?php echo $request['id']; ?>">
					
					<div class="receipt-card">
						<?php
						if (!empty($request['receipt'])) {
						$receiptPath = './/' . $request['receipt'];
							echo '<img src="' . $receiptPath . '" alt="Cash Out Receipt">';
						} else {
							echo 'No image';
						}
						?>
					</div>
					
					<div class="close-button">
						<div class="close" onclick="hideCashOutReceipt(<?php echo $request['id'];?>)">Close</div>
					</div>
					</div>
					<?php
					}
					?>
		
		<script src="js/wallet.js"></script>
		
	</body>

	</html>
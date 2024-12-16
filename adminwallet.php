<?php
session_start();
include 'php/config.php';

if (!isset($_SESSION['username']) || !$_SESSION['admin']) {
    header("Location: home");
    exit;
}
	
	$username = mysqli_real_escape_string($connection, $_SESSION['username']);
	$query = "SELECT * FROM users WHERE username='$username'";
	$result = mysqli_query($connection, $query);
	
	if($result) {
		$userData = mysqli_fetch_assoc($result);
	}
	
	else {
		echo "Error retrieving user data!" .mysqli_error($connection);
	}
	
function getCashIn($searchCashIn = '')
{
    global $connection;
    $query = "SELECT * FROM cash_loading";
    
    if (!empty($searchCashIn)) {
        $searchCashIn = mysqli_real_escape_string($connection, $searchCashIn);
        $query .= " WHERE name LIKE '%$searchCashIn%' OR payment_method LIKE '%$searchCashIn%' OR amount LIKE '%$searchCashIn%' OR payment_number LIKE '%$searchCashIn%' OR payment_account_name LIKE '%$searchCashIn%' OR receipt LIKE '%$searchCashIn%' OR status LIKE '%$searchCashIn%' OR created_at LIKE '%$searchCashIn%'";
    }
    
	$query .= " ORDER BY approved_at DESC";
    $result = mysqli_query($connection, $query);
    $cashin = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $cashin[] = $row;
    }

    return $cashin;
}

function getCashOut($searchCashOut = '')
{
    global $connection;
    $query = "SELECT * FROM cash_loading";
    
    if (!empty($searchCashOut)) {
        $searchCashOut = mysqli_real_escape_string($connection, $searchCashOut);
        $query .= " WHERE name LIKE '%$searchCashOut%' OR payment_method LIKE '%$searchCashOut%' OR amount LIKE '%$searchCashOut%' OR payment_number LIKE '%$searchCashOut%' OR payment_account_name LIKE '%$searchCashOut%' OR receipt LIKE '%$searchCashOut%' OR status LIKE '%$searchCashOut%' OR created_at LIKE '%$searchCashOut%'";
    }
    
	$query .= " ORDER BY approved_at DESC";
    $result = mysqli_query($connection, $query);
    $cashout = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $cashout[] = $row;
    }

    return $cashout;
}

function getVirtualMoney($searchVirtualMoney = '')
{
    global $connection;
    $query = "SELECT * FROM virtual_wallet";
    
    if (!empty($searchVirtualMoney)) {
        $searchVirtualMoney = mysqli_real_escape_string($connection, $searchVirtualMoney);
        $query .= " WHERE balance LIKE '%$searchVirtualMoney%' OR created_at LIKE '%$searchVirtualMoney%'";
    }
    
	$query .= " ORDER BY updated_at DESC";
    $result = mysqli_query($connection, $query);
    $virtual = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $virtual[] = $row;
    }

    return $virtual;
}

function getVirtualHistory($virtualWalletId, $searchVirtualHistory = '')
{
    global $connection;
    $query = "SELECT * FROM virtual_wallet_history WHERE virtual_wallet_id = '$virtualWalletId'";
    
    if (!empty($searchVirtualHistory)) {
        $searchVirtualHistory = mysqli_real_escape_string($connection, $searchVirtualHistory);
        $query .= " WHERE amount LIKE '%$searchVirtualHistory%' OR created_at LIKE '%$searchVirtualHistory%'";
    }
    
	$query .= " ORDER BY updated_at DESC";
    $result = mysqli_query($connection, $query);
    $virtualHistory = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $virtualHistory[] = $row;
    }

    return $virtualHistory;
}
	
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>LendRow Admin</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="pictures/logo.png" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="css/adminwallet.css">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

	<?php include ('main-sidebar.php') ?>
			
	<div class="adminwallet-buttons">
	
		<div class="addmoney-form-button active" onclick="showAddMoneyForm()">Virtual Money</div>
		<div class="cashin-manager-button" onclick="showCashInManager()">Cash In Request</div>
		<div class="cashout-manager-button" onclick="showCashOutManager()">Cash Out Request</div>
	
	</div>
	
	<div class="overlay-bg1" id="overlayBg1"></div>
	
		<div class="virtual-overlay" id="virtualOverlay"></div>
		
		<div class="virtual-cashin-form" id="virtualCashInForm">
		<h2>Add Virtual Money Form</h2>
			<form action="php/addvirtualmoney" method="POST" enctype="multipart/form-data">
				
			<div class="admin-input">
								<?php
									$searchVirtualMoney = isset($_GET['searchVirtualMoney']) ? $_GET['searchVirtualMoney'] : '';
									$virtuals = getVirtualMoney($searchVirtualMoney);
									
									if (empty($virtuals)) {
										echo '<div class="inputBox">
												<input type="text" value="Not Set Up" readonly>
											</div>';
									} else {
									
										foreach ($virtuals as $virtual) {
											$virtualHistory = $virtual['id'];
								?>
									<div class="inputBox">
										<i class='bx bxs-user'></i>
										<input type="text" name="name" id="name" value="<?php echo $virtual['name']; ?>" readonly>
									</div>

								<?php
										}
									}
								?>
		
			<div class="inputBox">
				<i class='bx bxl-product-hunt'></i>
				<input type="text" name="amount" id="amount" placeholder="Enter Virtual Amount">
			</div>
			</div>
			
			<div class="buttons">
				<div class="cancel" onclick="hideVirtualCashIn()">Cancel</div>
				<button type="submit" class="create">Submit</button>
			</div>
			
			</form>
		</div>
		
		
		
		<div class="virtual-cashin-form" id="virtualSetUp">
		<h2>Virtual Account Set Up</h2>
			<form action="php/setupvirtualaccount" method="POST" enctype="multipart/form-data">
			
			<div class="admin-input">
				<div class="inputBox">
					<i class='bx bxs-user'></i>
                    <input type="text" name="name" id="name" placeholder="Enter Account Name">
                </div>
			</div>
			
			<div class="buttons">
				<div class="cancel" onclick="hideVirtualSetUp()">Cancel</div>
				<button type="submit" class="create">Submit</button>
			</div>
			
			</form>
		</div>
		
		<?php
			$searchVirtualMoney = isset($_GET['searchVirtualMoney']) ? $_GET['searchVirtualMoney'] : '';
			$virtuals = getVirtualMoney($searchVirtualMoney);

				foreach ($virtuals as $virtual) {
					$virtualWalletId = $virtual['id'];
		?>

		<?php
			$searchVirtualHistory = isset($_GET['searchVirtualHistory']) ? $_GET['searchVirtualHistory'] : '';
			$virtualsHistory = getVirtualHistory($virtualWalletId, $searchVirtualHistory);

				foreach ($virtualsHistory as $virtualHistory) {
					$id = $virtualHistory['id'];
		?>
			
		<div class="virtual-cashin-form" id="addVirtualMoney<?php echo $virtualHistory['id'];?>">
		<h2>Add Virtual Money Form</h2>
			<form action="php/confirmvirtualmoney" method="POST" enctype="multipart/form-data">
			
			<input type="hidden" name="id" id="id" value="<?php echo $virtualHistory['id']; ?>" readonly>
			
			<div class="admin-input">
			<div class="inputBox">
				<i class='bx bxs-user'></i>
				<input type="text" name="name" id="name" value="<?php echo $virtual['name']; ?>" readonly>
			</div>
													
			<div class="inputBox">
				<i class='bx bxl-product-hunt'></i>
				<input type="text" name="amount" id="amount" value="<?php echo $virtualHistory['amount']; ?>" readonly>
			</div>
			</div>

			<div class="fund-card-button">
				<div class="close" onclick="hideaddVirtualMoney(<?php echo $virtualHistory['id'];?>)">Close</div>
				<button type="submit" name="cancel" class="reject">Cancel</button>
				<button type="submit" name="confirm" class="accept">Confirm</button>
			</div>
			
			</form>
		</div>
		<?php					
		}
		?>	
									
		<?php
			}
		?>		

	
	<div class="adminwallet">
		<div class="admin-wallet-form active" id="addMoney">
				<div class="virtual-wallet-info">
				
					<div class="virtual-account">
							<?php
									$searchVirtualMoney = isset($_GET['searchVirtualMoney']) ? $_GET['searchVirtualMoney'] : '';
									$virtuals = getVirtualMoney($searchVirtualMoney);
									
									if (empty($virtuals)) {
										echo '<div class="virtual-balance active" id="virtualBalance"> 
												<div class="virtual-inputBox">
												<div class="switch-button" onclick="showSystemBalance()">
												System Balance
												</div>
													<h2>Virtual Balance</h2>
													<label>PHP</label>
													<input type="text" name="balance" id="balance" placeholder="0.00" value="" readonly>
												</div>
											  </div>
											  <div class="system-balance" id="systemBalance">
												<div class="virtual-inputBox">
												<div class="switch-button" onclick="showVirtualBalance()">
												Virtual Balance
												</div>
													<h2>System Balance</h2>
													<label>PHP</label>
													<input type="text" name="balance" id="balance" placeholder="0.00" value="" readonly>
												</div>
											</div>';
									} else {
										foreach ($virtuals as $virtual) {
											$virtualHistory = $virtual['id'];
								?>
											
											<div class="virtual-balance active" id="virtualBalance">
												<div class="virtual-inputBox">
												<div class="switch-button" onclick="showSystemBalance()">
												System Balance
												</div>
													<h2>Virtual Balance</h2>
													<label>PHP</label>
													<input type="text" name="balance" id="balance" placeholder="0.00" value="<?php echo $virtual['balance']; ?>" readonly>
												</div>
											</div>
											<div class="system-balance" id="systemBalance">
												<div class="virtual-inputBox">
												<div class="switch-button" onclick="showVirtualBalance()">
												Virtual Balance
												</div>
													<h2>System Balance</h2>
													<label>PHP</label>
													<input type="text" name="balance" id="balance" placeholder="0.00" value="<?php echo $virtual['system_balance']; ?>" readonly>
												</div>
											</div>
								<?php
										}
									}
								?>
			
			<div class="virtual-errors">
				<?php
					if(isset($_GET["error"])) {
						if ($_GET["error"] == "emptyinput") {
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
						
						else if ($_GET["error"] == "duplicate_submission") {
							$resubmitTime = $_SESSION['last_submission_time'] + 5;

							$remainingTime = max(0, $resubmitTime - time());

							$minutes = floor($remainingTime / 60);
							$seconds = $remainingTime % 60;

							echo "<i class='bx bxs-error-circle'></i><p class='red'>Duplicate Submission, please wait for ";
							echo $seconds . "s before resubmitting.</p>";
						}
						
						else if ($_GET["error"] == "invalidname") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Account Name, please enter letters and numbers only!</p>";
						}
						
						else if ($_GET["error"] == "virtualwalletidnotfound") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You haven't set up your Virtual Account yet!</p>";
						}
						
						else if ($_GET["error"] == "virtualbalancenotfound") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You haven't set up your Virtual Account yet!</p>";
						}
						
						else if ($_GET["error"] == "systembalancenotfound") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You haven't set up your Virtual Account yet!</p>";
						}
						
						else if ($_GET["error"] == "fileuploadfailed") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Receipt field must not be empty, please insert a receipt to cash out!</p>";
						}
						
						else if ($_GET["error"] == "invalidinput") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Amount, Please enter correct amount format!</p>";
						}
						
						else if ($_GET["error"] == "insufficientbalance") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Insufficient Balance, cannot proceed for Cash Out!</p>";
						}
						
						else if ($_GET["error"] == "insufficientvirtualbalance") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Insufficient Virtual Balance, please add virtual balance first!</p>";
						}
						
						else if ($_GET["error"] == "insufficientuserbalance") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Insufficient User Balance, you cannot approve the Cash Out Request!</p>";
						}

						else if ($_GET["error"] == "mobilenotfound") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Mobile No. does not exist, Please enter mobile no. again!</p>";
						}
						
						else if ($_GET["error"] == "existingcashinapproval") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You approved another Cash In Request, please Cash In it first!</p>";
						}
						
						else if ($_GET["error"] == "existingcashoutapproval") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You approved another Cash Out Request, please Cash Out it first!</p>";
						}

						else if ($_GET["error"] == "existingvirtualcashin") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Virtual Cash In has been unsuccessful, since you have pending history!</p>";
						}
					}
					
					if(isset($_GET["success"])) {						
						if ($_GET["success"] == "setup") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>Congratulations! You Set Up your Virtual Account successfully.</p>";
						}
						
						else if ($_GET["success"] == "virtualcashin") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>Congratulations! Your Virtual Cash In successful.</p>";
						}
						
						else if ($_GET["success"] == "confirmed") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>You confirmed your Virtual Cash In!</p>";
						}
						
						else if ($_GET["success"] == "cancelled") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You cancelled your Virtual Cash In!</p>";
						}
						
						else if ($_GET["success"] == "rejectedcashin") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You rejected a Cash In Request!</p>";
						}
						
						else if ($_GET["success"] == "approvedcashin") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>You approved a Cash In Request!</p>";
						}
						
						else if ($_GET["success"] == "cashin") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>Congratulations! You Cashed In the money successfully.</p>";
						}
						
						else if ($_GET["success"] == "rejectedcashout") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You rejected a Cash Out Request!</p>";
						}
						
						else if ($_GET["success"] == "approvedcashout") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>You approved a Cash Out Request!</p>";
						}
						
						else if ($_GET["success"] == "cashout") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>Congratulations! You Cashed Out the money successfully.</p>";
						}
						
						else if ($_GET["success"] == "moneyadded") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>Congratulations! You added the money successfully.</p>";
						}
							
					}
				?>
		</div>
		
						<div class="virtual-accountbuttons">
								<?php
									$searchVirtualMoney = isset($_GET['searchVirtualMoney']) ? $_GET['searchVirtualMoney'] : '';
									$virtuals = getVirtualMoney($searchVirtualMoney);
									
									if (empty($virtuals)) {
										echo '<div class="virtual-cashin" onclick="showVirtualSetUp()">
												Set Up Virtual Account
											</div>';
									} else {
										foreach ($virtuals as $virtual) {
											$virtualHistory = $virtual['id'];
								?>
							<div class="virtual-cashin" onclick="showVirtualCashIn()">
								Add Virtual Money
							</div>
								<?php
										}
									}
								?>
						</div>
						
						</div>
						
						<div class="virtual-transaction-history">
						<div class="virtual-history-form">
						
							<?php
								$searchVirtualMoney = isset($_GET['searchVirtualMoney']) ? $_GET['searchVirtualMoney'] : '';
								$virtuals = getVirtualMoney($searchVirtualMoney);
								
								if (empty($virtuals)) {
									echo '<h2>Virtual Account History</h2>';
								} else {

								foreach ($virtuals as $virtual) {
									$virtualWalletId = $virtual['id'];
								?>
							<h2><?php echo $virtual['name']; ?>'s Virtual Account History</h2>
							<?php
								}
							}
							?>
							
							<div class="virtual-history-content">
							
							<?php
								$searchVirtualMoney = isset($_GET['searchVirtualMoney']) ? $_GET['searchVirtualMoney'] : '';
								$virtuals = getVirtualMoney($searchVirtualMoney);
								
								$virtualsFound = false;

								foreach ($virtuals as $virtual) {
									$virtualWalletId = $virtual['id'];
								?>

							
							<?php
									$searchVirtualHistory = isset($_GET['searchVirtualHistory']) ? $_GET['searchVirtualHistory'] : '';
									$virtualsHistory = getVirtualHistory($virtualWalletId, $searchVirtualHistory);
																
											if (!empty($virtualsHistory)) {
												$virtualsFound = true;

											foreach ($virtualsHistory as $virtualHistory) {
											$id = $virtualHistory['id'];
					
										if ($virtualHistory['status'] == 'Pending' && $virtualHistory['method'] == 'Virtual Cash In') {
										?>
											<div class="mycashin">
											<div class="pending"></div>
																
											<input type="hidden" placeholder="<?php echo $virtualHistory['id']; ?>" disabled>					
																	
											<p>You had a <?php echo $virtualHistory['status']; ?> Virtual Cash In!<br><?php echo date("F d, Y h:i A", strtotime($virtualHistory['created_at'])); ?></p>
											
											<div class="mycard-view">
												<button class="myview" onclick="showVirtualCashInHistory(<?php echo $virtualHistory['id'];?>)">View</button>
											</div>
											
											</div>
										<?php
											}
										elseif ($virtualHistory['status'] == 'Cancelled' && $virtualHistory['method'] == 'Virtual Cash In') {
										?>
											<div class="mycashin">
											<div class="rejected"></div>
																
											<input type="hidden" placeholder="<?php echo $virtualHistory['id']; ?>" disabled>					
																	
											<p>You <?php echo $virtualHistory['status']; ?> your Virtual Cash In!<br><?php echo date("F d, Y h:i A", strtotime($virtualHistory['updated_at'])); ?></p>
											
											</div>
										<?php
											}
										elseif ($virtualHistory['status'] == 'Added' && $virtualHistory['method'] == 'Virtual Cash In') {
										?>
											<div class="mycashin">
											<div class="added"></div>
																
											<input type="hidden" placeholder="<?php echo $virtualHistory['id']; ?>" disabled>					
																	
											<p>PHP <?php echo $virtualHistory['amount']; ?> has been <?php echo $virtualHistory['status']; ?> to your Virtual Wallet!<br><?php echo date("F d, Y h:i A", strtotime($virtualHistory['updated_at'])); ?></p>
											
											</div>
										<?php
											}
											
										elseif ($virtualHistory['status'] == 'Deducted' && $virtualHistory['method'] == 'Cash In') {
										?>
											<div class="mycashin">
											<div class="pending"></div>
																
											<input type="hidden" placeholder="<?php echo $virtualHistory['id']; ?>" disabled>					
																	
											<p>PHP <?php echo $virtualHistory['amount']; ?> has been <?php echo $virtualHistory['status']; ?> from your Virtual Wallet! - Cash In<br><?php echo date("F d, Y h:i A", strtotime($virtualHistory['created_at'])); ?></p>
											
											</div>
										<?php
											}
											
										elseif ($virtualHistory['status'] == 'Added' && $virtualHistory['method'] == 'Cash Out') {
										?>
											<div class="mycashin">
											<div class="added"></div>
																
											<input type="hidden" placeholder="<?php echo $virtualHistory['id']; ?>" disabled>					
																	
											<p>PHP <?php echo $virtualHistory['amount']; ?> has been <?php echo $virtualHistory['status']; ?> to your Virtual Balance! - Cash Out<br><?php echo date("F d, Y h:i A", strtotime($virtualHistory['created_at'])); ?></p>
											
											</div>
										<?php
												}
											}
										}
									?>	
									<?php
										}
									?>	
									
										<?php
											if (!$virtualsFound) {
												echo '<p class="empty">Vitual Account History is empty.</p>';
											}
										?>	
							
							</div>
							
						</div>
						</div>
		</div>
		</div>			
		
		<div class="cashin" id="cashIn">
		
		<div class="cashin-form">
			<h2>Cash In Request</h2>
			
			<div class="cashin-content">
		
			<?php
				$searchCashIn = isset($_GET['searchCashIn']) ? $_GET['searchCashIn'] : '';
				$cashins = getCashIn($searchCashIn);
										
				$hasCashInRequests = false;
				
				foreach ($cashins as $cashin) {
					if ($cashin['method'] == 'CashIn') {
						$hasCashInRequests = true;
						break;
					}
				}

				if (!$hasCashInRequests) {
					echo '<p class="empty">There are no existing Cash In Requests.</p>';
				} else  {

				foreach ($cashins as $cashin) {
				$id = $cashin['id'];

											
				if ($cashin['status'] == 'Pending' && $cashin['method'] == 'CashIn') {
				?>
					<div class="mycashin">
					<div class="pending"></div>
										
					<input type="hidden" placeholder="<?php echo $cashin['id']; ?>" disabled>					
											
					<p><?php echo $cashin['name']; ?> has a <?php echo $cashin['status']; ?> Request!<br><?php echo date("F d, Y h:i A", strtotime($cashin['created_at'])); ?></p>
											
					<div class="mycard-view">
						<button class="myview" onclick="showCashIn(<?php echo $cashin['id'];?>)">View</button>
					</div>
					</div>
				<?php
				}
				elseif ($cashin['status'] == 'Approved'  && $cashin['method'] == 'CashIn') {
				?>
					<div class="mycashin">
					<div class="approved"></div>
										
					<input type="hidden" placeholder="<?php echo $cashin['id']; ?>" disabled>					
											
					<p>You <?php echo $cashin['status']; ?> <?php echo $cashin['name']; ?> Request!<br><?php echo date("F d, Y h:i A", strtotime($cashin['approved_at'])); ?></p>
					
					<div class="mycard-view">
						<button class="myview" onclick="showAddMoney(<?php echo $cashin['id'];?>)">Cash In</button>
					</div>
											
					</div>
				<?php				
				}
				elseif ($cashin['status'] == 'Rejected'  && $cashin['method'] == 'CashIn') {
				?>
					<div class="mycashin">
					<div class="rejected"></div>
										
					<input type="hidden" placeholder="<?php echo $cashin['id']; ?>" disabled>					
											
					<p>You <?php echo $cashin['status']; ?> <?php echo $cashin['name']; ?> Request!<br><?php echo date("F d, Y h:i A", strtotime($cashin['approved_at'])); ?></p>
											
					</div>
				<?php
								
				}
				elseif ($cashin['status'] == 'Added'  && $cashin['method'] == 'CashIn') {
				?>
					<div class="mycashin">
					<div class="added"></div>
										
					<input type="hidden" placeholder="<?php echo $cashin['id']; ?>" disabled>					
											
					<p>You <?php echo $cashin['status']; ?> PHP <?php echo $cashin['amount']; ?> to <?php echo $cashin['name']; ?> Wallet!<br><?php echo date("F d, Y h:i A", strtotime($cashin['added_at'])); ?></p>
											
					</div>
				<?php				
				}
				}
				}
			?>
			</div>
			</div>
		</div>
		
		
		<div class="cashout" id="cashOut">
			
			<div class="cashout-form">
			<h2>Cash Out Request</h2>
			
			<div class="cashout-content">
		
			<?php
				$searchCashOut = isset($_GET['searchCashOut']) ? $_GET['searchCashOut'] : '';
				$cashouts = getCashOut($searchCashOut);
										
				$hasCashOutRequests = false;
				
				foreach ($cashouts as $cashout) {
					if ($cashout['method'] == 'CashOut') {
						$hasCashOutRequests = true;
						break;
					}
				}

				if (!$hasCashOutRequests) {
					echo '<p class="empty">There are no existing Cash Out Requests.</p>';
				} else  {

				foreach ($cashouts as $cashout) {
				$id = $cashout['id'];

											
				if ($cashout['status'] == 'Pending'  && $cashout['method'] == 'CashOut') {
				?>
					<div class="mycashout">
					<div class="pending"></div>
										
					<input type="hidden" placeholder="<?php echo $cashout['id']; ?>" disabled>					
											
					<p><?php echo $cashout['name']; ?> has a <?php echo $cashout['status']; ?> Request!<br><?php echo date("F d, Y h:i A", strtotime($cashout['created_at'])); ?></p>
											
					<div class="mycard-view">
						<button class="myview" onclick="showCashOut(<?php echo $cashout['id'];?>)">View</button>
					</div>
					</div>
					
				<?php
				}
				elseif ($cashout['status'] == 'Approved'  && $cashout['method'] == 'CashOut') {
				?>
					<div class="mycashout">
					<div class="approved"></div>
										
					<input type="hidden" placeholder="<?php echo $cashout['id']; ?>" disabled>					
											
					<p>You <?php echo $cashout['status']; ?> <?php echo $cashout['name']; ?> Request!<br><?php echo date("F d, Y h:i A", strtotime($cashout['approved_at'])); ?></p>
							
					<div class="mycard-view">
						<button class="myview" onclick="showLoadCashOut(<?php echo $cashout['id'];?>)">Cash Out</button>
					</div>

					</div>
				<?php
								
				}
				elseif ($cashout['status'] == 'Rejected' && $cashout['method'] == 'CashOut') {
				?>
					<div class="mycashout">
					<div class="rejected"></div>
										
					<input type="hidden" placeholder="<?php echo $cashout['id']; ?>" disabled>					
											
					<p><?php echo $cashout['name']; ?> has a <?php echo $cashout['status']; ?> Request!<br><?php echo date("F d, Y h:i A", strtotime($cashout['approved_at'])); ?></p>
											
					</div>
				<?php
								
				}
				elseif ($cashout['status'] == 'Deducted'  && $cashout['method'] == 'CashOut') {
				?>
					<div class="mycashout">
					<div class="pending"></div>
										
					<input type="hidden" placeholder="<?php echo $cashout['id']; ?>" disabled>					
											
					<p>You <?php echo $cashout['status']; ?> PHP <?php echo $cashout['amount']; ?> from <?php echo $cashout['name']; ?> Wallet!<br><?php echo date("F d, Y h:i A", strtotime($cashout['deducted_at'])); ?></p>
											
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
	
		<?php
			$searchCashIn = isset($_GET['searchCashIn']) ? $_GET['searchCashIn'] : '';
			$cashins = getCashIn($searchCashIn);

			foreach ($cashins as $cashin) {
			$id = $cashin['id'];
				
		?>
		
		<div class="cashin-card" id="cashInCard<?php echo $cashin['id']; ?>">
			
			<h2>Cash In Information</h2>
			<form action="php/loadcashin" method="POST">
			
			<input type="hidden" name="name" id="name" value="<?php echo $virtual['name']; ?>">	
		
			<input type="hidden" name="id" id="id" value="<?php echo $cashin['id']; ?>">
			
			<input type="hidden" name="wallet_id" id="wallet_id" value="<?php echo $cashin['wallet_id']; ?>">
			
			<input type="hidden" name="mobile" id="mobile" value="<?php echo $cashin['mobile']; ?>">
			
			<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
			
			<div class="info1">
				<label><?php echo date("F d, Y h:i A", strtotime($cashin['created_at'])); ?></label>
			</div>
			
			<div class="info">
				<label>Name : </label><input type="text" name="receiver" value="<?php echo $cashin['name']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Cash In Method : </label><input type="text" value="<?php echo $cashin['payment_method']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Cash In Amount : </label><input type="text" name="amount" id="amount" value="<?php echo $cashin['amount']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Payment No : </label><input type="text" value="<?php echo $cashin['payment_number']; ?>" readonly>
			</div>
			<div class="info">
				<label>Account Name : </label><input type="text" value="<?php echo $cashin['payment_account_name']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Receipt : </label>
			</div>
			
			<div class="receipt" onclick="showReceipt(<?php echo $cashin['id'];?>)">
			<?php
				if (!empty($cashin['receipt'])) {
				$receiptPath = './/' . $cashin['receipt'];
					echo '<img src="' . $receiptPath . '" alt="Receipt">';
				} else {
					echo 'No receipt available';
				}
				?>
			</div>
			
			
			<div class="fund-card-button">
				<div class="close" onclick="hideCashIn(<?php echo $cashin['id'];?>)">Close</div>
				<button type="submit" name="reject" class="reject">Reject</button>
				<button type="submit" name="approve" class="accept">Approve</button>
			</div>
			
		</form>
		</div>
		
		<?php
		}
		?>
		
		<div class="overlay-bg2" id="overlayBg2"></div>
			
			<?php
				$searchCashIn = isset($_GET['searchCashIn']) ? $_GET['searchCashIn'] : '';
				$cashins = getCashIn($searchCashIn);

				foreach ($cashins as $cashin) {
				$id = $cashin['id'];	
			?>
			<div class="receipt2" id="receipt2<?php echo $cashin['id']; ?>">
			
			<div class="receipt-card">
				<?php
				if (!empty($cashin['receipt'])) {
				$receiptPath = './/' . $cashin['receipt'];
					echo '<img src="' . $receiptPath . '" alt="Receipt">';
				} else {
					echo 'No receipt available';
				}
				?>
			</div>
			
			<div class="close-button">
				<div class="close" onclick="hideReceipt(<?php echo $cashin['id'];?>)">Close</div>
			</div>
			</div>
			<?php
			}
			?>
			
			
			<?php
				$searchCashIn = isset($_GET['searchCashIn']) ? $_GET['searchCashIn'] : '';
				$cashins = getCashIn($searchCashIn);

				foreach ($cashins as $cashin) {
				$id = $cashin['id'];	
			?>
			
			<div class="overlay-bg3" id="overlayBg3"></div>
			
			<div class="addmoney-form"  id="cashIn<?php echo $cashin['id'];?>">
			<h2>Cash In Money</h2>
		
			<form action="php/loadmoney" method="POST">
			
				<input type="hidden" name="name" id="name" value="<?php echo $virtual['name']; ?>">
			
				<input type="hidden" name="id" id="id" value="<?php echo $cashin['id']; ?>">
			
				<input type="hidden" name="wallet_id" id="wallet_id" value="<?php echo $cashin['wallet_id']; ?>">
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">

				<div class="info1">
					<label><?php echo date("F d, Y h:i A", strtotime($cashin['created_at'])); ?></label>
				</div>
				
				<div class="load-money">
				
				<div class="info">
				<label>Name : </label><input type="text" name="receiver" value="<?php echo $cashin['name']; ?>" readonly>
				</div>
				
				<div class="info">
					<label>Cash In Amount : </label><input type="text" name="balance" value="<?php echo $cashin['amount']; ?>" readonly>
				</div>
				
				<div class="info">
					<label>Mobile No : </label><input type="text" name="mobile" value="<?php echo $cashin['mobile']; ?>" readonly>
				</div>
				
				<div class="info">
				<label>Receipt : </label>
				</div>
				
				<div class="info-receipt" onclick="showReceipt(<?php echo $cashin['id'];?>)">
				<?php
					if (!empty($cashin['receipt'])) {
					$receiptPath = './/' . $cashin['receipt'];
						echo '<img src="' . $receiptPath . '" alt="Receipt">';
					} else {
						echo 'No receipt available';
					}
					?>
				</div>
				
				</div>
				
				<div class="buttons-form">
					
					<div class="cancel" onclick="hideAddMoney(<?php echo $cashin['id'];?>)">Close</div>
					<button type="submit" class="add">Cash In</button>
					
				</div>
				
			</form>
			</div>
			
			<?php
			}
			?>
			
			
			<?php
			$searchCashOut = isset($_GET['searchCashOut']) ? $_GET['searchCashOut'] : '';
			$cashouts = getCashOut($searchCashOut);

			foreach ($cashouts as $cashout) {
			$id = $cashout['id'];
				
			?>
		
		<div class="cashin-card" id="cashOutCard<?php echo $cashout['id']; ?>">
			
			<h2>Cash Out Information</h2>
			<form action="php/loadcashout" method="POST">
			
			<input type="hidden" name="name" id="name" value="<?php echo $virtual['name']; ?>">
		
			<input type="hidden" name="id" id="id" value="<?php echo $cashout['id']; ?>">
			
			<input type="hidden" name="wallet_id" id="wallet_id" value="<?php echo $cashout['wallet_id']; ?>">
			
			<input type="hidden" name="mobile" id="mobile" value="<?php echo $cashout['mobile']; ?>">
			
			<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
			
			<div class="info1">
				<label><?php echo date("F d, Y h:i A", strtotime($cashout['created_at'])); ?></label>
			</div>
			
			<div class="info">
				<label>Name : </label><input type="text" name="receiver" value="<?php echo $cashout['name']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Cash Out Method : </label><input type="text" value="<?php echo $cashout['payment_method']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Cash Out Amount : </label><input type="text" name="amount" id="amount" value="<?php echo $cashout['amount']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Payment No : </label><input type="text" value="<?php echo $cashout['payment_number']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Account Name : </label><input type="text" value="<?php echo $cashout['payment_account_name']; ?>" readonly>
			</div>
			
			<div class="info">
				<label>Receipt : </label>
			</div>
			
			<div class="receipt" onclick="showCashOutReceipt(<?php echo $cashout['id'];?>)">
			<?php
				if (!empty($cashout['receipt'])) {
				$receiptPath = './/' . $cashout['receipt'];
					echo '<img src="' . $receiptPath . '" alt="Receipt">';
				} else {
					echo 'No receipt available';
				}
				?>
			</div>
			
			
			<div class="fund-card-button">
				<div class="close" onclick="hideCashOut(<?php echo $cashout['id'];?>)">Close</div>
				<button type="submit" name="reject" class="reject">Reject</button>
				<button type="submit" name="approve" class="accept">Approve</button>
			</div>
			
		</form>
		</div>
		
		<?php
		}
		?>
		
		
			
			<?php
				$searchCashOut = isset($_GET['searchCashOut']) ? $_GET['searchCashOut'] : '';
				$cashouts = getCashOut($searchCashOut);

				foreach ($cashouts as $cashout) {
				$id = $cashout['id'];	
			?>
			<div class="receipt2" id="cashOutReceipt2<?php echo $cashout['id']; ?>">
			
			<div class="receipt-card">
				<?php
				if (!empty($cashout['receipt'])) {
				$receiptPath = './/' . $cashout['receipt'];
					echo '<img src="' . $receiptPath . '" alt="Receipt">';
				} else {
					echo 'No receipt available';
				}
				?>
			</div>
			
			<div class="close-button">
				<div class="close" onclick="hideCashOutReceipt(<?php echo $cashout['id'];?>)">Close</div>
			</div>
			</div>
			<?php
			}
			?>
			
			
			<?php
				$searchCashOut = isset($_GET['searchCashOut']) ? $_GET['searchCashOut'] : '';
				$cashouts = getCashOut($searchCashOut);

				foreach ($cashouts as $cashout) {
				$id = $cashout['id'];
				
			?>

			<div class="addmoney-form"  id="cashOut<?php echo $cashout['id'];?>">
			<h2>Cash Out Money</h2>
		
			<form action="php/loadcashoutmoney" method="POST" enctype="multipart/form-data">
			
				<input type="hidden" name="name" id="name" value="<?php echo $virtual['name']; ?>">

				<input type="hidden" name="id" id="id" value="<?php echo $cashout['id']; ?>">
			
				<input type="hidden" name="wallet_id" id="wallet_id" value="<?php echo $cashout['wallet_id']; ?>">
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $cashout['mobile']; ?>">
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="info1">
					<label><?php echo date("F d, Y h:i A", strtotime($cashout['created_at'])); ?></label>
				</div>
				
				<div class="info2">
				<label>Name : </label><input type="text" name="receiver" value="<?php echo $cashout['name']; ?>" readonly>
				</div>
				
				<div class="info2">
					<label>Cash Out Method : </label><input type="text" value="<?php echo $cashout['payment_method']; ?>" readonly>
				</div>
				
				<div class="info2">
					<label>Cash Out Amount : </label><input type="text" name="balance" value="<?php echo $cashout['amount']; ?>" readonly>
				</div>
				
				<div class="info2">
					<label>Payment No : </label><input type="text" value="<?php echo $cashout['payment_number']; ?>" readonly>
				</div>
				
				<div class="info2">
					<label>Account Name : </label><input type="text" value="<?php echo $cashout['payment_account_name']; ?>" readonly>
				</div>
				
				<div class="info2">
					<label>Insert Receipt : </label>
					<input class="file" type="file" name="receipt" id="receipt" placeholder="Payment Receipt"  accept="image/*">  
				</div>
				
				
				<div class="buttons-form">
					
					<div class="cancel" onclick="hideLoadCashOut(<?php echo $cashout['id'];?>)">Close</div>
					<button type="submit" class="add">Cash Out</button>
					
				</div>
				
			</form>
			</div>
			
			<?php
			}
			?>

	<script src="js/adminwallet.js"></script>
</body>

</html>
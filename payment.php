<?php
session_start();
include 'php/config.php';

if (!isset($_SESSION['username'])) {
    header("Location: home");
    exit;
}

	$username = mysqli_real_escape_string($connection, $_SESSION['username']);

	$queryUserData = "SELECT * FROM users WHERE username='$username'";
	$resultUserData = mysqli_query($connection, $queryUserData);
	$userData = mysqli_fetch_assoc($resultUserData);
	
function getApplications($searchApplications = '')
{
    global $connection, $userData;

    $userId = $userData['id'];

    $query = "SELECT * FROM applications WHERE users_id = '$userId'";

    if (!empty($searchApplications)) {
        $searchApplications = mysqli_real_escape_string($connection, $searchApplications);
        $query .= " AND (picture LIKE '%$searchApplications' OR lendername LIKE '%$searchApplications%' OR amount LIKE '%$searchApplications%' OR interest LIKE '%$searchApplications%' OR term LIKE '%$searchApplications%')";
    }

	$query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $application = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $application[] = $row;
    }

    return $application;
}

function getLenderByApplicationsId($applicationsId, $searchLender = '')
{
    global $connection;

    $applicationsId = mysqli_real_escape_string($connection, $applicationsId);

    $query = "SELECT * FROM financial_details WHERE applications_id = '$applicationsId'";

    if (!empty($searchLender)) {
        $searchLender = mysqli_real_escape_string($connection, $searchLender);
        $query .= " AND (lendername LIKE '%$searchLender%' OR mobile LIKE '%$searchLender%' OR amount LIKE '%$searchLender%' OR borrowername LIKE '%$searchLender%' OR interest LIKE '%$searchLender%' OR term LIKE '%$searchLender%' OR monthly LIKE '%$searchLender%' OR created_at LIKE '%$searchLender%')";

    }

    $query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $lender = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $lender[] = $row;
    }

    return $lender;
}

function getLendingTerms($searchLendingTerms = '')
{
    global $connection, $userData;

    $userId = $userData['id'];

    $query = "SELECT * FROM lending_terms WHERE users_id = '$userId'";

    if (!empty($searchLendingTerms)) {
        $searchLendingTerms = mysqli_real_escape_string($connection, $searchLendingTerms);
        $query .= " AND (picture LIKE '%$searchLendingTerms' OR lendername LIKE '%$searchLendingTerms%' OR amount LIKE '%$searchLendingTerms%' OR interest LIKE '%$searchLendingTerms%' OR term LIKE '%$searchLendingTerms%')";
    }

	$query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $lendingTerm = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $lendingTerm[] = $row;
    }

    return $lendingTerm;
}

function getLenderByLendingTermsId($lendingTermsId, $searchLending = '')
{
    global $connection;

    $lendingTermsId = mysqli_real_escape_string($connection, $lendingTermsId);

    $query = "SELECT * FROM financial_details WHERE lending_terms_id = '$lendingTermsId'";

    if (!empty($searchLending)) {
        $searchLending = mysqli_real_escape_string($connection, $searchLending);
        $query .= " AND (lendername LIKE '%$searchLending%' OR mobile LIKE '%$searchLending%' OR amount LIKE '%$searchLending%' OR borrowername LIKE '%$searchLending%' OR interest LIKE '%$searchLending%' OR term LIKE '%$searchLending%' OR monthly LIKE '%$$searchLending%' OR created_at LIKE '%$searchLending%')";

    }

    $query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $lending = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $lending[] = $row;
    }

    return $lending;
}

?>

<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>LendRow Payment Manager</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="pictures/logo.png" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="css/payment.css">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="css/swiper-bundle.min.css">
</head>

<body>

	<?php include ('main-sidebar.php') ?>
	
	<div class="pay-buttons">
	
		<div class="pay-button active" onclick="showPayment()">Pay</div>
		<div class="collect-button" onclick="showCollect()">Collect</div>
		<div class="wallet-button" id="walletButton"><a href="wallet">+</a><span class="wallet">Wallet</span></div>
	
	</div>
	
	<div class="errors">
				<?php
					if(isset($_GET["error"])) {
						if ($_GET["error"] == "duplicate_submission") {
							$resubmitTime = $_SESSION['last_submission_time'] + 5;

							$remainingTime = max(0, $resubmitTime - time());

							$minutes = floor($remainingTime / 60);
							$seconds = $remainingTime % 60;

							echo "<i class='bx bxs-error-circle'></i><p class='red'>Duplicate Submission, please wait for ";
							echo $seconds . "s before resubmitting.</p>";
						}
						
						else if ($_GET["error"] == "insufficientbalance") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Insufficient Balance, please cash in first to continue.</p>";
						}
						
						else if ($_GET["error"] == "unpaid") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Cannot proceed to payment since you have previous unpaid/unupdated interests.</p>";
						}
						
						else if ($_GET["error"] == "unupdated") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Cannot proceed since you have previous unupdated interests.</p>";
						}
					}
					
					if(isset($_GET["success"])) {
						if ($_GET["success"] == "paid") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>You have successfully paid your monthly interest!</p>";
						}
					}
					
					if(isset($_GET["success"])) {
						if ($_GET["success"] == "update") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>You have successfully updated the monthly interest payment!</p>";
						}
					}
				?>
	
			</div>
	
	
	<div class="lenders-content">
	
	<div class="payment-form active" id="paymentManager">
	<div class="view-lending-history" onclick="showLenderHistory()">Lender History</div>
	
		<div class="lending-history" id="lenderHistory">
			<h2>My Lender History</h2>				
			<i class='bx bxs-exit' onclick="hideLenderHistory()"></i>
			
				<div class="lending-history-content">
					<div class="lending-border">
						<table>
							<thead>
								<tr>
									<th>Lender</th>
									<th>Amount</th>
									<th>Interest Rate</th>
									<th>Payment Term</th>
									<th>Monthly Interest</th>
									<th>Lend Date</th>
									<th>Expacted Paid Date</th>
									<th>Month 1</th>
									<th>Month 2</th>
									<th>Month 3</th>
									<th>Month 4</th>
									<th>Month 5</th>
									<th>Month 6</th>
									<th>Month 7</th>
									<th>Month 8</th>
									<th>Month 9</th>
									<th>Month 10</th>
									<th>Month 11</th>
									<th>Month 12</th>
									<th>Status</th>
								</tr>
							</thead>
							
							<tbody>
								<?php
								$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
								$applications = getApplications($searchApplications);
									
								foreach ($applications as $application) {
									$applicationsId = $application['id'];
								?>
									
								<?php
									$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
									$searchLender = getLenderByApplicationsId($searchLender);
									$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
										
											
									foreach ($lenders as $lender) {
										$term = (int) filter_var($lender['term'], FILTER_SANITIZE_NUMBER_INT);
										$monthlyInterest = $lender['monthly'];
										$lendDate = strtotime($lender['created_at']);
										$expectedPaidDate = strtotime("+{$term} months", $lendDate);
										$expectedPaidDateFormatted = date("F d, Y", $expectedPaidDate);
										$id = $lender['id'];
								?>
							 
								<tr>
									<td><?php echo $lender['lendername']; ?></td>
									<td>PHP <?php echo $lender['amount']; ?></td>
									<td><?php echo $lender['interest']; ?></td>
									<td><?php echo $lender['term']; ?></td>
									<td>PHP <?php echo $monthlyInterest; ?></td>
									<td><?php echo date("F d, Y", strtotime($lender['created_at'])); ?></td>
									<td><?php echo $expectedPaidDateFormatted; ?></td>
									<?php 
									for ($month = 1; $month <= 12; $month++) {
										if ($month <= $term) {
											$monthStatus = $lender["month_{$month}"];
											
											if ($monthStatus == "Paid") {
												echo "<td class='paid'>PHP {$monthlyInterest}</td>";
											} elseif ($monthStatus == "Unpaid") {
												echo "<td class='unpaid'>PHP {$monthlyInterest}</td>";
											} else {
												echo "<td class='pending'>PHP {$monthlyInterest}</td>";
											}
										} else {
											echo "<td></td>";
										}
									}
									?>
									
									<?php 
										if ($lender['status'] == 'Paid') {
											echo "<td class='paid'>{$lender['status']}</td>";
										} elseif ($lender['status'] == 'Unpaid') {
											echo "<td class='unpaid'>{$lender['status']}</td>";
										} else { 
											echo "<td class='pending'>{$lender['status']}</td>";
										}
									?>
								</tr>
								
								<?php 
								} 
								?>
								
								<?php 
								} 
								?>
							
							</tbody>
						
						</table>
					</div>
				
				</div>
				
			</div>
	

		<div class="slide-container swiper" id="paymentManagerSlide">
		<div class="slide-content">
			<div class="card-wrapper swiper-wrapper">
			
				<?php
					$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
					$applications = getApplications($searchApplications);
					
					$lendersFound = false;
					
					foreach ($applications as $application) {
						$applicationsId = $application['id'];
					?>
					
					<?php
						$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
						$searchLender = getLenderByApplicationsId($searchLender);
						$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
						
						if (!empty($lenders)) {
							$lendersFound = true;
							
							foreach ($lenders as $lender) {
							$id = $lender['id'];
					?>
				
					<div class="card swiper-slide">
						
						<input type="hidden" placeholder="<?php echo $lender['id']; ?>" disabled>
						
					<div class="profile-pic">	
						<div class="image-content">
							<span class="overlay"></span>
								
								<div class="card-image">
									<div class="card-img">
											<?php
												if (!empty($lender['picture'])) {
													$profilePicturePath = 'php/' . $lender['picture'];
													echo '<img src="' . $profilePicturePath . '" alt="Profile Picture">';
												} else {
													echo 'No profile picture available';
												}
											?>
									</div>
								</div>
								<div class="lendername">
									<?php echo $lender['lendername']; ?>
								</div>
						</div>
					</div>
						
						<div class="card-details">
							<div class="card-content">
							
								<div class="details">
									<label> <?php echo date("F d, Y h:i A", strtotime($lender['created_at'])); ?> </label>
								</div>
									
								<div class="details">
									<label>Amount : </label><input type="text" placeholder="<?php echo $lender['amount']; ?>" disabled>
								</div>
								<div class="details">
									<label>Interest Rate :</label><input type="text" placeholder="<?php echo $lender['interest']; ?>" disabled>
								</div>
								<div class="details">
									<label>Payment Term : </label><input type="text" placeholder="<?php echo $lender['term']; ?>" disabled>
								</div>
								<div class="details">
									<label>Monthly Interest : </label><input type="text" placeholder="<?php echo $lender['monthly']; ?>" disabled>
								</div>	
									
								<div class="details">
									<label></label><input type="hidden" placeholder="null" disabled>
								</div>	
								
								<div class="details">
									<label></label><input type="hidden" placeholder="null" disabled>
								</div>
								
								<div class="details">
									<label></label><input type="hidden" placeholder="null" disabled>
								</div>
							
								<div class="details">
									<label></label><input type="hidden" placeholder="null" disabled>
								</div>
								
							</div>
						
						</div>
						
						<div class="interest">
							<div class="interest-form">
								<h2>Monthly Interests</h2>

								<div class="interest-content">
									
									<div class="pay-interest">
									
									<?php
										$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
										$searchLender = getLenderByApplicationsId($searchLender);
										$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
											
											foreach ($lenders as $lender) {
											$id = $lender['id'];
											
										if ($lender['term'] == '1 Month') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '2 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay"onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '3 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '4 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '5 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_5'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment5(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '6 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_5'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment5(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_6'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment6(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '7 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_5'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment5(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_6'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment6(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_7'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment7(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '8 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_5'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment5(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_6'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment6(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_7'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment7(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_8'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment8(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '9 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_5'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment5(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_6'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment6(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_7'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment7(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_8'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment8(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>9th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +270 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_9'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_9'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment9(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '10 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_5'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment5(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_6'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment6(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_7'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment7(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_8'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment8(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>9th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +270 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_9'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_9'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment9(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>10th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +300 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_10'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_10'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment10(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lender['term'] == '11 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
												<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_5'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment5(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_6'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment6(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_7'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment7(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_8'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment8(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>9th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +270 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_9'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_9'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment9(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>10th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +300 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_10'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_10'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment10(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>11th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +330 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											<?php if ($lender['month_11'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_11'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment11(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										<?php
										}
					
									
										elseif ($lender['term'] == '12 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_1'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment1(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_2'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment2(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_3'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment3(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_4'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment4(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_5'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment5(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_6'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment6(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_7'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment7(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_8'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment8(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>9th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +270 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_9'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_9'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment9(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>10th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +300 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											 <?php if ($lender['month_10'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_10'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment10(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>11th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +330 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											<?php if ($lender['month_11'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lender['month_11'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment11(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>12th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lender['created_at']. ' +360 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lender['monthly']; ?>
											</div>
											<?php if ($lender['month_12'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue' onclick="showPayment(<?php echo $lender['id']; ?>)">Paid</p>
												</div>
											<?php } elseif ($lender['month_12'] == 'Pending') { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>Pending</p>
												</div>
											<?php } else { ?>
												<button class="mypay" onclick="showPayment12(<?php echo $lender['id']; ?>)">
													Pay
												</button>
											<?php } ?>
										</div>
										
										<?php
										}
										}
										?>
										
									</div>
									
								</div>
									
								
							</div>
						</div>
						
					</div>
					
				<?php
					}
				}
					
					}
				?>
											
				<?php
					if (!$lendersFound) {
						echo '<p class="lenders-empty">Payment Manager is empty.</p>';
					}
				
				?>
				
			</div>
		</div>
		
		<div class="swiper-button-next swiper-navBtn"></div>
		<div class="swiper-button-prev swiper-navBtn"></div>
		<div class="swiper-pagination"></div>
	</div>
	
</div>
		<div class="collect-form" id="collectManager">
			<div class="view-lending-history" onclick="showLendingHistory()">Lending History</div>
	
			<div class="lending-history" id="lendingHistory">
			<h2>My Lending History</h2>				
			<i class='bx bxs-exit' onclick="hideLendingHistory()"></i>
			
				<div class="lending-history-content">
					<div class="lending-border">
						<table>
							<thead>
								<tr>
									<th>Borrower</th>
									<th>Amount</th>
									<th>Interest Rate</th>
									<th>Payment Term</th>
									<th>Monthly Interest</th>
									<th>Lend Date</th>
									<th>Expacted Paid Date</th>
									<th>Month 1</th>
									<th>Month 2</th>
									<th>Month 3</th>
									<th>Month 4</th>
									<th>Month 5</th>
									<th>Month 6</th>
									<th>Month 7</th>
									<th>Month 8</th>
									<th>Month 9</th>
									<th>Month 10</th>
									<th>Month 11</th>
									<th>Month 12</th>
									<th>Status</th>
								</tr>
							</thead>
							
							<tbody>
								<?php
									$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
									$lendingTerms = getLendingTerms($searchLendingTerms);
									
									
									foreach ($lendingTerms as $lendingTerm) {
										$lendingTermsId = $lendingTerm['id'];
									?>
									
								<?php
									$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
									$searchLending = getLenderByLendingTermsId($searchLending);
									$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
										
											
									foreach ($lendings as $lending) {
										$term = (int) filter_var($lending['term'], FILTER_SANITIZE_NUMBER_INT);
										$monthlyInterest = $lending['monthly'];
										$lendDate = strtotime($lending['created_at']);
										$expectedPaidDate = strtotime("+{$term} months", $lendDate);
										$expectedPaidDateFormatted = date("F d, Y", $expectedPaidDate);
										$id = $lending['id'];
								?>
							 
								<tr>
									<td><?php echo $lending['borrowername']; ?></td>
									<td>PHP <?php echo $lending['amount']; ?></td>
									<td><?php echo $lending['interest']; ?></td>
									<td><?php echo $lending['term']; ?></td>
									<td>PHP <?php echo $monthlyInterest; ?></td>
									<td><?php echo date("F d, Y", strtotime($lending['created_at'])); ?></td>
									<td><?php echo $expectedPaidDateFormatted; ?></td>
									<?php 
									for ($month = 1; $month <= 12; $month++) {
										if ($month <= $term) {
											$monthStatus = $lending["month_{$month}"];
											
											if ($monthStatus == "Paid") {
												echo "<td class='paid'>PHP {$monthlyInterest}</td>";
											} elseif ($monthStatus == "Unpaid") {
												echo "<td class='unpaid'>PHP {$monthlyInterest}</td>";
											} else {
												echo "<td class='pending'>PHP {$monthlyInterest}</td>";
											}
										} else {
											echo "<td></td>";
										}
									}
									?>
									
									<?php 
										if ($lending['status'] == 'Paid') {
											echo "<td class='paid'>{$lending['status']}</td>";
										} elseif ($lending['status'] == 'Unpaid') {
											echo "<td class='unpaid'>{$lending['status']}</td>";
										} else { 
											echo "<td class='pending'>{$lending['status']}</td>";
										}
									?>
								</tr>
								
								<?php 
								} 
								?>
								
								<?php 
								} 
								?>
							
							</tbody>
						
						</table>
					</div>
				
				</div>
				
			</div>
		
		
		<div class="slide-container swiper" id="collectManagerSlide">
		<div class="slide-content">
			<div class="card-wrapper swiper-wrapper">
			
				<?php
					$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
					$lendingTerms = getLendingTerms($searchLendingTerms);
					
					$lendingsFound = false;
					
					foreach ($lendingTerms as $lendingTerm) {
						$lendingTermsId = $lendingTerm['id'];
					?>
					
					<?php
						$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
						$searchLending = getLenderByLendingTermsId($searchLending);
						$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
						if (!empty($lendings)) {
							$lendingsFound = true;
							
							foreach ($lendings as $lending) {
							$id = $lending['id'];
					?>
				
					<div class="card swiper-slide">
						
						<input type="hidden" placeholder="<?php echo $lending['id']; ?>" disabled>
						
					<div class="profile-pic">	
						<div class="image-content">
							<span class="overlay"></span>
								
								<div class="card-image">
									<div class="card-img">
											<?php
												if (!empty($lending['picture'])) {
													$profilePicturePath = 'php/' . $lending['picture'];
													echo '<img src="' . $profilePicturePath . '" alt="Profile Picture">';
												} else {
													echo 'No profile picture available';
												}
											?>
									</div>
								</div>
								<div class="lendername">
									<?php echo $lending['lendername']; ?>
								</div>
						</div>
					</div>
						
						<div class="card-details">
							<div class="card-content">
							
								<div class="details">
									<label> <?php echo date("F d, Y h:i A", strtotime($lending['created_at'])); ?> </label>
								</div>
									
								<div class="details">
									<label>Amount : </label><input type="text" placeholder="<?php echo $lending['amount']; ?>" disabled>
								</div>
								<div class="details">
									<label>Interest Rate :</label><input type="text" placeholder="<?php echo $lending['interest']; ?>" disabled>
								</div>
								<div class="details">
									<label>Payment Term : </label><input type="text" placeholder="<?php echo $lending['term']; ?>" disabled>
								</div>
								<div class="details">
									<label>Monthly Interest : </label><input type="text" placeholder="<?php echo $lending['monthly']; ?>" disabled>
								</div>	
									
								<div class="details">
									<label></label><input type="hidden" placeholder="null" disabled>
								</div>	
								
								<div class="details">
									<label></label><input type="hidden" placeholder="null" disabled>
								</div>
								
								<div class="details">
									<label></label><input type="hidden" placeholder="null" disabled>
								</div>
							
								<div class="details">
									<label></label><input type="hidden" placeholder="null" disabled>
								</div>
								
							</div>
						
						</div>
						
						<div class="interest">
							<div class="interest-form">
								<h2>Monthly Interests</h2>

								<div class="interest-content">
									
									<div class="pay-interest">
									
									<?php
										$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
										$searchLending = getLenderByLendingTermsId($searchLending);
										$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
											
											foreach ($lendings as $lending) {
											$id = $lending['id'];
											
										if ($lending['term'] == '1 Month') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '2 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd</label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '3 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '4 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '5 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_5'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate5(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '6 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd</label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_5'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate5(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_6'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate6(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '7 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_5'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate5(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_6'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate6(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_7'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate7(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '8 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_5'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate5(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_6'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate6(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_7'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate7(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_8'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate8(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '9 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_5'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate5(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_6'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate6(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_7'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate7(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_8'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate8(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>9th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +270 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_9'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_9'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate9(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '10 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_5'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate5(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_6'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate6(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_7'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate7(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_8'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate8(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>9th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +270 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_9'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_9'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate9(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>10th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +300 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_10'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_10'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate10(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
										
										elseif ($lending['term'] == '11 Months') {
										?>
										
										<div class="mypayment">
										<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_5'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate5(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_6'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate6(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_7'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate7(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_8'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate8(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>9th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +270 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_9'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_9'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate9(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>10th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +300 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_10'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_10'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate10(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>11th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +330 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											<?php if ($lending['month_11'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_11'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate11(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										<?php
										}
					
									
										elseif ($lending['term'] == '12 Months') {
										?>
										
										<div class="mypayment">
											<div class="paydate">
												<label>1st </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_1'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_1'] == 'Pending') { ?>
											
												<button class="mypay" onclick="showUpdate1(<?php echo $lending['id']; ?>)">
													Update
												</button>
											
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>2nd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_2'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_2'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate2(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>3rd </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_3'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_3'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate3(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>4th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_4'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_4'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate4(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>5th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_5'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_5'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate5(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>6th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +180 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_6'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_6'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate6(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>7th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +210 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_7'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_7'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate7(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>8th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +240 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_8'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_8'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate8(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>9th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +270 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_9'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_9'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate9(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>10th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +300 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											 <?php if ($lending['month_10'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_10'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate10(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>11th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +330 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											<?php if ($lending['month_11'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_11'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate11(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<div class="mypayment">
											<div class="paydate">
												<label>12th </label>
											</div>
											<div class="mymonth">
											<?php echo date("F d, Y", strtotime($lending['created_at']. ' +360 days')); ?>
											</div>
											<div class="myinterest">
											PHP <?php echo $lending['monthly']; ?>
											</div>
											<?php if ($lending['month_12'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } elseif ($lending['month_12'] == 'Pending') { ?>
												<button class="mypay" onclick="showUpdate12(<?php echo $lending['id']; ?>)">
													Update
												</button>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
										</div>
										
										<?php
										}
										}
										?>
										
										<div class="mypayment2" onclick="showReport(<?php echo $lending['id']; ?>)">
											View Reports
										</div>
										
									</div>
									
								</div>
							
									
								
							</div>
						</div>
						
					</div>
					
				<?php
					}
				}
					
					}
				?>
											
				<?php
					if (!$lendingsFound) {
						echo '<p class="lenders-empty">Collection Manager is empty.</p>';
					}
				
				?>
				
			</div>
		</div>
		
		<div class="swiper-button-next swiper-navBtn"></div>
		<div class="swiper-button-prev swiper-navBtn"></div>
		<div class="swiper-pagination"></div>
	</div>

	</div>

	<div class="lenders-wallet">
	
		<?php include ('wallet.php') ?>
	
	</div>
	
	</div>
	
	<div class="overlay-bg" id="overlayBg"></div>
	
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
	
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm1<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment1" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>1st </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +30 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment1(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
	
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm2<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment2" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>2nd </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +60 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment2(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
	
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm3<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment3" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>3rd </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +90 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment3(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
	
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm4<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment4" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>4th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +120 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment4(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
	
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm5<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment5" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>5th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +150 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment5(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
		
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm6<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment6" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>6th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +180 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment6(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
		
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm7<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment7" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>7th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +210 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment7(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
		
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm8<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment8" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>8th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +240 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment8(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
		
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm9<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment9" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>9th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +270 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment9(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
		
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm10<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment10" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>10th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +300 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment10(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
		
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm11<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment11" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>11th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +330 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment11(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
			$applications = getApplications($searchApplications);
					
			foreach ($applications as $application) {
			$applicationsId = $application['id'];
		?>
		
		<?php
			$searchLender = isset($_GET['searchLender']) ? $_GET['searchLender'] : '';
			$searchLender = getLenderByApplicationsId($searchLender);
			$lenders = getLenderByApplicationsId($applicationsId, $searchLender);
												
			foreach ($lenders as $lender) {
			$id = $lender['id'];
		?>
	
		<div class="monthly-payment-form"  id="paymentForm12<?php echo $lender['id']; ?>">
			<h2>Interest Payment Form</h2>
		
			<form action="php/monthlypayment12" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lender['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lender['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lender['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>12th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lender['created_at']. ' +360 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lender['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Receiver : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lender['lendername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hidePayment12(<?php echo $lender['id']; ?>)">Close</div>
					<button type="submit" class="pay">Pay</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm1<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate1" method="POST">
			
				<input type="text" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>1st </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +30 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate1(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>

		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm2<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate2" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>2nd </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +60 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate2(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm3<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate3" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>3rd </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +90 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate3(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>

		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm4<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate4" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>4th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +120 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate4(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm5<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate5" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>5th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +150 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate5(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm6<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate6" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>6th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +180 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate6(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>

		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm7<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate7" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>7th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +210 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate7(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>


		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						

				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm8<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate8" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>8th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +240 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate8(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>

		

		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm9<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate9" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>9th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +270 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate9(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>

		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm10<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate10" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>10th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +300 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate10(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>


		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						

				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm11<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate11" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>11th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +330 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate11(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>

		
		
		<?php
			$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
			$lendingTerms = getLendingTerms($searchLendingTerms);
					
					
			foreach ($lendingTerms as $lendingTerm) {
				$lendingTermsId = $lendingTerm['id'];
		?>
		
		<?php
			$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
			$searchLending = getLenderByLendingTermsId($searchLending);
			$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
						
							
				foreach ($lendings as $lending) {
				$id = $lending['id'];
		?>	
	
		<div class="monthly-payment-form"  id="updateForm12<?php echo $lending['id']; ?>">
			<h2>Interest Update Form</h2>
		
			<form action="php/monthlyupdate12" method="POST">
			
				<input type="hidden" name="id" value="<?php echo $lending['id']; ?>" readonly>
				
				<input type="hidden" name="applications_id" value="<?php echo $lending['applications_id']; ?>" readonly>
				
				<input type="hidden" name="mobile" id="mobile" value="<?php echo $lending['mobile']; ?>" readonly>
				
				<input type="hidden" name="sender" id="sender" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
				
				<div class="inputBox1">
					<label>12th </label>
				</div>
				
				<div class="inputBox">
					<label>Payment Date : </label>
					<input type="text" name="payment_date" id="payment_date" value="<?php echo date("F d, Y", strtotime($lending['created_at']. ' +360 days')); ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Monthly Interest : </label>
					<input type="text" name="amount" id="amount" value="<?php echo $lending['monthly']; ?>" readonly>
				</div>
				
				<div class="inputBox">
					<label>Sender : </label>
					<input type="text" name="receiver" id="receiver" value="<?php echo $lending['borrowername']; ?>" readonly>
				</div>
				
				<div class="interest-payment-buttons">
					
					<div class="close" onclick="hideUpdate12(<?php echo $lending['id']; ?>)">Close</div>
					<button type="submit" class="pay">Update</button>
					
				</div>
				
			</form>
		</div>
		
		<?php
		}
		?>
		<?php
		}
		?>
	
					<?php
						function convertToNumber($value) {
								return floatval(str_replace(',', '', $value));
							}
					
						$searchLendingTerms = isset($_GET['searchLendingTerms']) ? $_GET['searchLendingTerms'] : '';
						$lendingTerms = getLendingTerms($searchLendingTerms);
					
							foreach ($lendingTerms as $lendingTerm) {
							$lendingTermsId = $lendingTerm['id'];
					?>
					
					<?php
						$searchLending = isset($_GET['searchLending']) ? $_GET['searchLending'] : '';
						$searchLending = getLenderByLendingTermsId($searchLending);
						$lendings = getLenderByLendingTermsId($lendingTermsId, $searchLending);
							
							foreach ($lendings as $lending) {
							$id = $lending['id'];
							
					?>
						<div class="reports" id="reportForm<?php echo $lending['id']; ?>">
							<h2>©2023 LendRow Official<br>Financial Report</h2>

						<?php
							
							$lend_amount = convertToNumber($lending['amount']);
							$interest_rate_percentage = convertToNumber($lending['interest']);
							$payment_term = convertToNumber($lending['term']);
							$monthly_interest = convertToNumber($lending['monthly']);
							$status = $lending['status'];

							if (empty($status) || strtolower($status) !== "paid") {
								$status = "Ongoing";
							}
							
							$month1 = intval($lending['month_1']);
							$month2 = intval($lending['month_2']);
							$month3 = intval($lending['month_3']);
							$month4 = intval($lending['month_4']);
							$month5 = intval($lending['month_5']);
							$month6 = intval($lending['month_6']);
							$month7 = intval($lending['month_7']);
							$month8 = intval($lending['month_8']);
							$month9 = intval($lending['month_9']);
							$month10 = intval($lending['month_10']);
							$month11 = intval($lending['month_11']);
							$month12 = intval($lending['month_12']);
							
							if (strtolower($lending['month_1']) === "paid") {
									$month1 = 1;
								} else {
									$month1 = 0;
								}
								
							if (strtolower($lending['month_2']) === "paid") {
									$month2 = 1;
								} else {
									$month2 = 0;
								}
								
							if (strtolower($lending['month_3']) === "paid") {
									$month3 = 1;
								} else {
									$month3 = 0;
								}
							if (strtolower($lending['month_4']) === "paid") {
									$month4 = 1;
								} else {
									$month4 = 0;
								}
								
							if (strtolower($lending['month_5']) === "paid") {
									$month5 = 1;
								} else {
									$month5 = 0;
								}
								
							if (strtolower($lending['month_6']) === "paid") {
									$month6 = 1;
								} else {
									$month6 = 0;
								}
							if (strtolower($lending['month_7']) === "paid") {
									$month7 = 1;
								} else {
									$month7 = 0;
								}
								
							if (strtolower($lending['month_8']) === "paid") {
									$month8 = 1;
								} else {
									$month8 = 0;
								}
								
							if (strtolower($lending['month_9']) === "paid") {
									$month9 = 1;
								} else {
									$month9 = 0;
								}
								
							if (strtolower($lending['month_10']) === "paid") {
									$month10 = 1;
								} else {
									$month10 = 0;
								}
								
							if (strtolower($lending['month_11']) === "paid") {
									$month11 = 1;
								} else {
									$month11 = 0;
								}
								
							if (strtolower($lending['month_12']) === "paid") {
									$month12 = 1;
								} else {
									$month12 = 0;
								}
							
							$payment_days = $payment_term * 30;
							$expected_paid_date = date("F d, Y", strtotime($lending['created_at'] . ' + ' . $payment_days . ' days'));
							
							$total_collectable = $monthly_interest * $payment_term;
							$total_collectable_formatted = number_format($total_collectable, 2, '.', ',');
							
							$total_profit = $total_collectable - $lend_amount ;
							$total_profit_formatted = number_format($total_profit, 2, '.', ',');
							
							$paid = $month1 + $month2 + $month3 + $month4 + $month5 + $month6 + $month7 + $month8 + $month9 + $month10 + $month11 + $month12;
							$total_paid = number_format($paid, 2, '.', ',');
							
							$collected = $paid * $monthly_interest;
							$total_collected = number_format($collected, 2, '.', ',');
							
							$remaining = $total_collectable - $collected;
							$remaining_collectable = number_format($remaining, 2, '.', ',');
							
							?>
						
							<div class="financial">
								<div class="asset">Financial Assets</div> <div class="amount">Financial Record</div>
								<div class="financial-asset">Borrower</div> <div class="financial-amount"><?php echo $lending['borrowername'];?></div>
								<div class="financial-asset">Lend Amount</div> <div class="financial-amount"><?php echo $lending['amount'];?></div>
								<div class="financial-asset">Interest Rate</div> <div class="financial-amount"><?php echo $lending['interest'];?></div>
								<div class="financial-asset">Payment Term</div> <div class="financial-amount"><?php echo $lending['term'];?></div>
								<div class="financial-asset">Monthly Interest</div> <div class="financial-amount"><?php echo $lending['monthly'];?></div>
								<div class="financial-asset">Lend Date</div> <div class="financial-amount"><?php echo date("F d, Y", strtotime($lending['created_at'])); ?></div>
								<div class="financial-asset">Expected Paid Date</div> <div class="financial-amount"><?php echo $expected_paid_date; ?></div>
								<div class="asset">Financial Projection</div> <div class="amount">-</div>
								<div class="financial-asset">Status</div> <div class="financial-amount"><?php echo $status; ?></div>
								<div class="financial-asset">Latest Payment Date</div> <div class="financial-amount"><?php echo date("F d, Y", strtotime($lending['updated_at'])); ?></div>
								<div class="financial-asset">Total Collectable</div> <div class="financial-amount"><?php echo $total_collectable_formatted; ?></div>
								<div class="financial-asset">Remaining Collectable</div> <div class="financial-amount"><?php echo $remaining_collectable; ?></div>
								<div class="financial-asset">Total Collected</div> <div class="financial-amount"><?php echo $total_collected; ?></div>
								<div class="financial-asset">Expected Gain</div> <div class="financial-amount"><?php echo $total_profit_formatted; ?></div>
								
							</div>
							
						
						<div class="fund-card-button">
							<div class="close-fund" onclick="hideReport(<?php echo $lending['id'];?>)">Close</div>
						</div>
							
						</div>
						
					<?php
						}
					?>
					
					<?php
						}
					?>
					

		
		
			
	
	<script src="js/swiper-bundle.min.js"></script>
	<script src="js/payment.js"></script>
	
</body>

</html>
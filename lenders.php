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

function getLenders($searchLenders = '')
{
    global $connection, $userData;

    $userId = $userData['id'];

    $query = "SELECT * FROM lending_terms WHERE users_id = '$userId'";

    if (!empty($searchLenders)) {
        $searchLenders = mysqli_real_escape_string($connection, $searchLenders);
        $query .= " AND (picture LIKE '%$searchLenders' OR lendername LIKE '%$searchLenders%' OR amount LIKE '%$searchLenders%' OR interest LIKE '%$searchLenders%' OR term LIKE '%$searchLenders%')";
    }

	$query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $lender = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $lender[] = $row;
    }

    return $lender;
}

function getApplicationsByLendingTermId($lendingTermsId, $searchApplications = '')
{
    global $connection;

    $lendingTermsId = mysqli_real_escape_string($connection, $lendingTermsId);

    $query = "SELECT * FROM applications WHERE lending_terms_id = '$lendingTermsId'";

    if (!empty($searchApplications)) {
        $searchApplications = mysqli_real_escape_string($connection, $searchApplications);
        $query .= " AND (picture LIKE '%$searchApplications' OR borrowername LIKE '%$searchApplications%' OR mobile LIKE '%$searchApplications%' OR created_at LIKE '%$searchApplications%')";
    }

    $query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $application = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $application[] = $row;
    }

    return $application;
}

function getAgreementByApplicationsId($applicationsId, $lendingTermsId, $searchAgreement = '')
{
    global $connection;

    $applicationsId = mysqli_real_escape_string($connection, $applicationsId);
	
	$lendingTermsId = mysqli_real_escape_string($connection, $lendingTermsId);

    $query = "SELECT * FROM lending_agreement WHERE applications_id = '$applicationsId' AND lending_terms_id = '$lendingTermsId'";

    if (!empty($searchAgreement)) {
        $searchAgreement = mysqli_real_escape_string($connection, $searchAgreement);
        $query .= " AND (borrowername LIKE '%$searchAgreement%' OR mobile LIKE '%$searchAgreement%' OR lendername LIKE '%$searchAgreement%' OR amount LIKE '%$searchAgreement%' OR interest LIKE '%$searchAgreement%' OR term LIKE '%$searchAgreement%' OR created_at LIKE '%$searchAgreement%')";
    }

    $query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $agreement = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $agreement[] = $row;
    }

    return $agreement;
}

function getCredit($borrowername, $searchCredit = '')
{
    global $connection;
	
	$borrowername = mysqli_real_escape_string($connection, $borrowername);
	
    $query = "SELECT * FROM financial_details WHERE borrowername = '$borrowername'";
    
    if (!empty($searchCredit)) {
        $searchCredit = mysqli_real_escape_string($connection, $searchCredit);
        $query .= " WHERE lendername LIKE '%$searchCredit%' OR mobile LIKE '%$searchCredit%' OR amount LIKE '%$searchCredit%' OR borrowername LIKE '%$searchCredit%' OR interest LIKE '%$searchCredit%' OR term LIKE '%$searchCredit%' OR monthly LIKE '%$searchCredit%' OR status LIKE '%$searchCredit%' OR created_at LIKE '%$searchCredit%'";
    }
    
	$query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $credit = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $credit[] = $row;
    }

    return $credit;
}


?>

<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>LendRow Lenders</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="pictures/logo.png" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="css/lenders.css">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="css/swiper-bundle.min.css">
</head>

<body>

	<?php include ('main-sidebar.php') ?>

	
	<div class="lend-buttons">
		<div class="lend-form-button active" onclick="showLendForm()">Lend Form</div>
		<div class="lend-manager-button" onclick="showLendManager()">Lend Manager

		</div>
		<div class="wallet-button" id="walletButton"><a href="wallet">+</a><span class="wallet">Wallet</span></div>
	</div>
	
	
	<div class="errors">
				<?php
					if(isset($_GET["error"])) {
						if ($_GET["error"] == "emptyinput") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>There are empty fields, please fill in all fields!</p>";
						}
						
						else if ($_GET["error"] == "duplicate_submission") {
							$resubmitTime = $_SESSION['last_submission_time'] + 5;

							$remainingTime = max(0, $resubmitTime - time());

							$minutes = floor($remainingTime / 60);
							$seconds = $remainingTime % 60;

							echo "<i class='bx bxs-error-circle'></i><p class='red'>Duplicate Submission, please wait for ";
							echo $seconds . "s before resubmitting.</p>";
						}
						
						else if ($_GET["error"] == "invalidinput") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Incorrect Amount Format, Please enter numbers only!</p>";
						}
						
						else if ($_GET["error"] == "invalidamount") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Amount, Please enter an amount of 10,000.00 or below!</p>";
						}
						
						else if ($_GET["error"] == "insufficientbalance") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Insufficient Balance, Please cash in first to continue.</p>";
						}
						
						else if ($_GET["error"] == "existingapproved") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Approval unsuccesful, since this borrower has an existing approved application.</p>";
						}
						
						else if ($_GET["error"] == "existingdebt") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Approval unsuccesful, since this borrower has an existing unpaid application.</p>";
						}
					}
					if(isset($_GET["success"])) {						
						if ($_GET["success"] == "created") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>Congratulations! Your lend information has been added successfully.</p>";
						}
						
						else if ($_GET["success"] == "approved") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>You approved a Borrowers Application!</p>";
						}
						
						else if ($_GET["success"] == "rejected") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>You rejected a Borrowers Application!</p>";
						}
						
						else if ($_GET["success"] == "funded") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>You funded a Borrowers Application!</p>";
						}
					}
				?>
	
			</div>
	
	<div class="lenders-content">
	
	<div class="lenders-form" id="lendForm">
		
		<div class="lend-form" id="lendForm">
				<div class="lend-form-info">
					<form action="php/lend" method="POST">
					
						<div class="calculate">
							<div class="monthly">
								<div class="inputBox">
									<h2>Monthly Interest Rate</h2>
									<label>PHP</label>
									<input type="text" name="monthly" id="monthly" placeholder="0.00" value="" readonly>
								</div>
							</div>
						</div>
						
						<input type="hidden" name="id" id="id" placeholder="User ID" value="<?php echo htmlspecialchars($userData['id']); ?>">
			
						<input type="hidden" name="picture" id="picture" placeholder="Picture" value="<?php echo htmlspecialchars($userData['picture']); ?>">
						
						<input type="hidden" name="status" id="status" placeholder="Status" value="Open">
						
						<input type="hidden" name="mobile" id="mobile" placeholder="Mobile No." value="<?php echo htmlspecialchars($userData['mobile']); ?>">
						
						<div class="lend-input">
						<div class="inputBox">
							<input type="hidden" name="lendername" id="lendername" placeholder="Lender Name" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
						</div>
						<div class="inputBox">
							<i class='bx bxl-product-hunt'></i>
							<input type="text" name="amount" id="amount" placeholder="Enter an Amount" oninput="calculateMonthlyPayment()" value="">
						</div>
						<div class="inputBox">
							<i class='bx bxs-calculator'></i>
							<select name="interest" id="interest" onchange="calculateMonthlyPayment()">
								<option value="" disabled selected>Select Monthly Interest</option>
								<option value="10% Monthly">10% Monthly</option>
								<option value="9.5% Monthly" disabled>9.5% Monthly</option>
								<option value="9.0% Monthly" disabled>9.0% Monthly</option>
								<option value="8.5% Monthly" disabled>8.5% Monthly</option>
								<option value="8.0% Monthly" disabled>8.0% Monthly</option>
							</select>
						</div>
						<div class="inputBox">
							<i class='bx bxs-calendar'></i>
							<select name="term" id="term" onchange="calculateMonthlyPayment()">
								<option value="" disabled selected>Select Payment Term</option>
								<option value="1 Month">1 Month</option>
								<option value="2 Months">2 Months</option>
								<option value="3 Months">3 Months</option>
								<option value="4 Months">4 Months</option>
								<option value="5 Months">5 Months</option>
								<option value="6 Months">6 Months</option>
								<option value="7 Months">7 Months</option>
								<option value="8 Months">8 Months</option>
								<option value="9 Months">9 Months</option>
								<option value="10 Months">10 Months</option>
								<option value="11 Months">11 Months</option>
								<option value="12 Months">12 Months</option>
							</select>
						</div>
						</div>
						<div class="buttons">
							<div class="create" onclick="showLend()">Create</div>
						</div>
						
						<div class="overlaylendbg" id="overlayLend"></div>
						
						<div class="submit-lend" id="lend">
							<h2>Lending Terms Creation</h2>
							
							<p>Are you sure you want to create Lending Informations?</p>
							
							<div class="submit-lend-buttons">
							<div class="cancel" onclick="hideLend()">Close</div>
							<button type="submit" name="lend" class="lend">Create</button>
							</div>
						</div>
					</form>
					
			
				</div>
			</div>
		
	</div>
	
	<div class="borrowers-form" id="lendManager">
	
		<div class="slide-container swiper">
		<div class="slide-content">
			<div class="card-wrapper swiper-wrapper">
			
				<?php
					$searchLenders = isset($_GET['search']) ? $_GET['search'] : '';
					$lenders = getLenders($searchLenders);
					
					if (empty($lenders)) {
						echo '<p class="lenders-empty">Lend Manager is empty.</p>';
					} else {

					foreach ($lenders as $lender) {
						$lendingTermsId = $lender['id'];
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
								
							</div>
						
						</div>
						
						<div class="applicants">
							<div class="applicants-form">
								<h2>Borrower Applications</h2>

								<div class="applicants-content">
									<?php
									$searchApplications = isset($_GET['search']) ? $_GET['search'] : '';
									$searchApplications = getApplicationsByLendingTermId($searchApplications);
									$applications = getApplicationsByLendingTermId($lendingTermsId, $searchApplications);
									
									if (empty($applications)) {
										echo '<p class="empty">No borrower application can be found.</p>';
									} else {

									foreach ($applications as $application) {
										$id = $application['id'];
										if ($application['status'] == 'Pending') {
									?>
										<div class="myborrowers">
										<div class="pending"></div>
										
											<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
											
											<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
											
											<p><?php echo $application['borrowername']; ?> has <?php echo $application['status']; ?> application!<br><?php echo date("F d, Y h:i A", strtotime($application['created_at'])); ?></p>
											
											<div class="card-view">
												<button class="view"onclick="showCard(<?php echo $application['id'];?>)">View</button>
											</div>
										</div>
										<?php
									}
										
										elseif ($application['status'] == 'Cancelled') {
											?>
											<div class="myborrowers">
											<div class="rejected"></div>
											
												<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
												
												<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
												
												<p><?php echo $application['borrowername']; ?> <?php echo $application['status']; ?> their application!<br><?php echo date("F d, Y h:i A", strtotime($application['created_at'])); ?></p>
																						
											</div>
										
										<?php
										}
										
											elseif ($application['status'] == 'Approved') {
											?>
											<div class="myborrowers">
											<div class="approved"></div>
											
												<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
												
												<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
												
												<p>You <?php echo $application['status']; ?> <?php echo $application['borrowername']; ?>'s application!<br><?php echo date("F d, Y h:i A", strtotime($application['approved_at'])); ?></p>
												
												<div class="card-view">
													<button class="view" onclick="showFundCard(<?php echo $application['id'];?>)">Fund</button>
												</div>
											</div>
											<?php
										}
										
											elseif ($application['status'] == 'Rejected') {
											?>
											<div class="myborrowers">
											<div class="rejected"></div>
											
												<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
												
												<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
												
												<p>You <?php echo $application['status']; ?> <?php echo $application['borrowername']; ?>'s application!<br><?php echo date("F d, Y h:i A", strtotime($application['approved_at'])); ?></p>
																						
											</div>
											<?php
										}
										
											elseif ($application['status'] == 'Funded') {
											?>
											<div class="myborrowers">
											<div class="funded"></div>
											
												<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
												
												<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
												
												<p>You <?php echo $application['status']; ?> <?php echo $application['borrowername']; ?>'s application!<br><?php echo date("F d, Y h:i A", strtotime($application['funded_at'])); ?></p>
												
												<div class="card-view">
													<button class="view" onclick="showAgreementCard(<?php echo $application['id'];?>)">View</button>
												</div>
																						
											</div>
											<?php
											}
											
											elseif ($application['status'] == 'Paid') {
											?>
											<div class="myborrowers">
											<div class="funded"></div>
											
												<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
												
												<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
												
												<p><?php echo $application['borrowername']; ?>'s application has been <?php echo $application['status']; ?>!<br><?php echo date("F d, Y h:i A", strtotime($application['paid_at'])); ?></p>
												
												<div class="card-view">
													<button class="view" onclick="showAgreementCard(<?php echo $application['id'];?>)">View</button>
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
					
				<?php
					}
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
		$searchLenders = isset($_GET['search']) ? $_GET['search'] : '';
		$lenders = getLenders($searchLenders);

		foreach ($lenders as $lender) {
			$lendingTermsId = $lender['id'];
	?>
	
	<?php
		$applications = getApplicationsByLendingTermId($lendingTermsId, $searchApplications);

		foreach ($applications as $application) {
			$id = $application['id'];
			$borrowername = $application['borrowername'];
	?>
		<div class="apply-card" id="applyCard<?php echo $application['id']; ?>">
		<form action="php/approval" method="POST">

			<div class="apply-card-content">
						
						<div class="applycard-image-content">
							<span class="applycard-overlay"></span>
								
								<div class="applycard-image">
									<div class="applycard-img">
											<?php
												if (!empty($application['picture'])) {
													$profilePicturePath = 'php/' . $application['picture'];
													echo '<img src="' . $profilePicturePath . '" alt="Profile Picture">';
												} else {
													echo 'No profile picture available';
												}
											?>
									</div>
								</div>
								<div class="borrowername">
									<?php echo $application['borrowername']; ?>
								</div>
						</div>
							
							<div class="applycard-content">

								<input type="hidden" name="applications_id" id="applications_id" placeholder="Application ID" value="<?php echo $application['id']; ?>" readonly>

								<input type="hidden" name="lending_terms_id" id="lending_terms_id" placeholder="Lending Terms ID" value="<?php echo $lender['id']; ?>" readonly>

								<input type="hidden" name="borrowername" id="borrowername" placeholder="Borrower Name" value="<?php echo $application['borrowername']; ?>" readonly>
								
								<input type="hidden" name="mobile" id="mobile" placeholder="Mobile No." value="<?php echo $application['mobile']; ?>" readonly>
								
								<input type="hidden" name="status" id="status" placeholder="Status" value="Approved" readonly>
								
								<input type="hidden" name="lendername" id="lendername" placeholder="Lender Name" value="<?php echo $lender['lendername']; ?>" readonly>
								
								<input type="hidden" name="amount" id="amount" placeholder="Amount" value="<?php echo $lender['amount']; ?>" readonly>
								
								<input type="hidden" name="interest" id="interest" placeholder="Interest Rate" value="<?php echo $lender['interest']; ?>" readonly>
								
								<input type="hidden" name="term" id="term" placeholder="Payment Term" value="<?php echo $lender['term']; ?>" readonly>
								
								<input type="hidden" name="monthly" id="monthly" placeholder="Monthly Interest Rate" value="<?php echo $lender['monthly']; ?>" readonly>

								<div class="applycard-details">
									<label><?php echo date("F d, Y h:i A", strtotime($application['created_at'])); ?> </label>
								</div>
									
								<div class="applycard-details">
									<label>Mobile No: </label><input type="text" placeholder="Mobile No." value="<?php echo $application['mobile']; ?>" readonly>
								</div>
								
							</div>
							
							<div class="credithistory">
							<div class="credithistory-form">
								<h2>Credit History</h2>

								<div class="credithistory-content">
								
								
									<?php
										$searchCredit = isset($_GET['searchCredit']) ? $_GET['searchCredit'] : '';
										$credits = getCredit($borrowername, $searchCredit);
																
											if (empty($credits)) {
												echo '<p class="empty">There is no existing Credit History.</p>';
											} else {

										foreach ($credits as $credit) {
										$borrowername = $credit['borrowername'];
										
										if ($credit['borrowername'] == $application['borrowername']) {
										?>
										<div class="mycredithistory">
											<div class="overlaycredit" id="overlaycredit<?php echo $credit['id']; ?>"></div>
											
											<div class="closedate" onclick="hidedate(<?php echo $credit['id']; ?>)" id="datebtn<?php echo $credit['id']; ?>"></div>
											<div class="mydate" id="date<?php echo $credit['id']; ?>">
											Latest Payment on <?php echo date("F d, Y", strtotime($credit['created_at'])); ?>
											</div>
											
											<div class="paydate" onclick="showdate(<?php echo $credit['id']; ?>)">
												<label>?</label>
											</div>
											<div class="mymonthly">
											PHP <?php echo $credit['monthly']; ?>
											</div>
											<div class="myterm">
											<?php echo $credit['term']; ?>
											</div>
											
											 <?php if ($credit['status'] == 'Paid') { ?>
												<div class="mypaid">
													<i class='bx bxs-check-circle'></i><p class='blue'>Paid</p>
												</div>
											<?php } else { ?>
												<div class="mypaid">
													<i class='bx bxs-error-circle'></i><p class='red'>UnPaid</p>
												</div>
											<?php } ?>
											
										</div>
										<?php
										}
										}
										}
										?>	
										
								</div>
								
							</div>
						</div>
		
			<div class="apply-card-button">
				<div class="close" onclick="hideCard(<?php echo $application['id'];?>)">Close</div>
				<button type="submit" name="reject" class="reject">Reject</button>
				<button type="submit" name="approve" class="accept">Approve</button>
			</div>
			
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
		$searchLenders = isset($_GET['search']) ? $_GET['search'] : '';
		$lenders = getLenders($searchLenders);

		foreach ($lenders as $lender) {
			$lendingTermsId = $lender['id'];
	?>
	
	<?php
		$searchApplications = isset($_GET['search']) ? $_GET['search'] : '';
		$applications = getApplicationsByLendingTermId($lendingTermsId, $searchApplications);

		foreach ($applications as $application) {
			$applicationsId = $application['id'];
	?>
	
	<?php
		$searchAgreement = isset($_GET['search']) ? $_GET['search'] : '';
		$agreements = getAgreementByApplicationsId($applicationsId, $lendingTermsId, $searchAgreement);

		foreach ($agreements as $agreement) {
			$id = $agreement['id'];
	?>
		<div class="fund-card" id="fundCard<?php echo $application['id']; ?>">
			<h2>Lending Agreement</h2>
		<form action="php/fund" method="POST">
		
			<input type="hidden" name="picture" id="picture" placeholder="Picture" value="<?php echo htmlspecialchars($userData['picture']); ?>">
		
			<input type="hidden" name="lending_agreements_id" value="<?php echo $agreement['id']; ?>">
			
			<input type="hidden" name="applications_id" value="<?php echo $application['id']; ?>">
			
			<input type="hidden" name="lending_terms_id" value="<?php echo $lender['id']; ?>">
			
			<div class="funding">
			
				<input type="hidden" name="sender" value="<?php echo $agreement['lendername']; ?>">
				
				<input type="hidden" name="mobile" value="<?php echo $agreement['mobile']; ?>">	
				
				<input type="hidden" name="amount" value="<?php echo $agreement['amount']; ?>">
				
				<input type="hidden" name="receiver" value="<?php echo $agreement['borrowername']; ?>">
				
				<input type="hidden" name="interest" value="<?php echo $agreement['interest']; ?>">
				
				<input type="hidden" name="term" value="<?php echo $agreement['term']; ?>">
				
				<input type="hidden" name="monthly" value="<?php echo $agreement['monthly']; ?>">
			
			</div>
			
			<div class="agreement">
			<p>This Lending Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($agreement['created_at'])); ?></span>. This confirms that I,  <span class="blue-text"><?php echo $agreement['lendername']; ?></span> will be lending an amount of  <span class="blue-text">PHP <?php echo $agreement['amount']; ?></span> to  <span class="blue-text"><?php echo $agreement['borrowername']; ?></span> with a  <span class="blue-text"><?php echo $agreement['interest']; ?></span> Interest, which to be paid within  <span class="blue-text"><?php echo $agreement['term']; ?></span> and a monthly interest rate of  <span class="blue-text">PHP <?php echo $agreement['monthly']; ?></span>.</p>
			
			<div class="check">
			<input type="checkbox" required><label>I confirm the above terms and conditions.</label>
			</div>
			
			</div>
		
		
			<div class="fund-card-button">
				<div class="close-fund" onclick="hideFundCard(<?php echo $application['id'];?>)">Close</div>
				<div class="print" onclick="printCard(<?php echo $application['id'];?>)">Print</div>
				<button type="submit" name="fund" class="disburse">Disburse</button>
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
	}
	?>
	
	
	
	<?php
		$searchLenders = isset($_GET['search']) ? $_GET['search'] : '';
		$lenders = getLenders($searchLenders);

		foreach ($lenders as $lender) {
			$lendingTermsId = $lender['id'];
	?>
	
	<?php
		$searchApplications = isset($_GET['search']) ? $_GET['search'] : '';
		$applications = getApplicationsByLendingTermId($lendingTermsId, $searchApplications);

		foreach ($applications as $application) {
			$applicationsId = $application['id'];
	?>
	
	<?php
		$searchAgreement = isset($_GET['search']) ? $_GET['search'] : '';
		$agreements = getAgreementByApplicationsId($applicationsId, $lendingTermsId, $searchAgreement);

		foreach ($agreements as $agreement) {
			$id = $agreement['id'];
	?>
		<div class="fund-card" id="agreementCard<?php echo $application['id']; ?>">
			<?php if ($application['status'] == 'Funded') { ?>
			<h2>Lending Agreement</h2>
			
			<?php } elseif ($application['status'] == 'Paid') { ?>
			<h2>Closure	 Agreement</h2>
			<?php } ?>
		<form action="#" method="POST">
		
			
			<div class="agreement">
			<?php if ($application['status'] == 'Funded') { ?>
			<p>This Lending Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($application['funded_at'])); ?></span>. This confirms that I, <span class="blue-text"><?php echo $agreement['lendername']; ?></span> was lending an amount of <span class="blue-text">PHP <?php echo $agreement['amount']; ?></span> to <span class="blue-text"><?php echo $agreement['borrowername']; ?></span>  with a <span class="blue-text"><?php echo $agreement['interest']; ?></span> Interest, which to be paid within <span class="blue-text"><?php echo $agreement['term']; ?></span> and a monthly interest rate of <span class="blue-text">PHP <?php echo $agreement['monthly']; ?></span>.</p>
			
			<?php } elseif ($application['status'] == 'Paid') { ?>
			<p>This Closure Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($application['paid_at'])); ?></span>. This serves as a proof that <span class="blue-text"><?php echo $agreement['borrowername']; ?></span> had Paid the amount of <span class="blue-text">PHP <?php echo $agreement['amount']; ?></span> which was owed from me <span class="blue-text"><?php echo $agreement['lendername']; ?></span>  with a <span class="blue-text"><?php echo $agreement['interest']; ?></span> Interest, which has been paid within <span class="blue-text"><?php echo $agreement['term']; ?></span> and a monthly interest rate of <span class="blue-text">PHP <?php echo $agreement['monthly']; ?></span>.</p>
			<?php } ?>
			</div>
		
		
			<div class="fund-card-button">
				<div class="close-fund" onclick="hideAgreementCard(<?php echo $application['id'];?>)">Close</div>
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
	}
	?>
	
	
		<div class="overlayprint" id="overlayPrint"></div>
	
	
	
	<?php
		$searchLenders = isset($_GET['search']) ? $_GET['search'] : '';
		$lenders = getLenders($searchLenders);

		foreach ($lenders as $lender) {
			$lendingTermsId = $lender['id'];
	?>
	
	<?php
		$searchApplications = isset($_GET['search']) ? $_GET['search'] : '';
		$applications = getApplicationsByLendingTermId($lendingTermsId, $searchApplications);

		foreach ($applications as $application) {
			$applicationsId = $application['id'];
	?>
	
	<?php
		$searchAgreement = isset($_GET['search']) ? $_GET['search'] : '';
		$agreements = getAgreementByApplicationsId($applicationsId, $lendingTermsId, $searchAgreement);

		foreach ($agreements as $agreement) {
			$id = $agreement['id'];
	?>
		<div class="print-card" id="printCard<?php echo $application['id']; ?>">
		<div class="print-buttons">
		<button class="closeprint" onclick="closePrint(<?php echo $application['id']; ?>)">Close</button>
		<button class="printnow" onclick="print()">Print</button>
		</div>
		
			<?php if ($application['status'] == 'Approved') { ?>
			<h2>Lending Agreement</h2>
		
			<?php } elseif ($application['status'] == 'Funded') { ?>
			<h2>Lending Agreement</h2>
			
			<?php } elseif ($application['status'] == 'Paid') { ?>
			<h2>Closure	 Agreement</h2>
			
			<?php } ?>
		<form action="#" method="POST">
		
			
			<div class="agreement">
			<?php if ($application['status'] == 'Approved') { ?>
			<p>This Lending Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($application['approved_at'])); ?></span>. This confirms that I, <span class="blue-text"><?php echo $agreement['lendername']; ?></span> was lending an amount of <span class="blue-text">PHP <?php echo $agreement['amount']; ?></span> to <span class="blue-text"><?php echo $agreement['borrowername']; ?></span>  with a <span class="blue-text"><?php echo $agreement['interest']; ?></span> Interest, which to be paid within <span class="blue-text"><?php echo $agreement['term']; ?></span> and a monthly interest rate of <span class="blue-text">PHP <?php echo $agreement['monthly']; ?></span>.</p>
			<br><br><br><br><br><br><br><br>
			<span class="blue-text"><?php echo $agreement['borrowername']; ?></span>
			<br>
			<p>Borrower</p>
			
			<?php } elseif ($application['status'] == 'Funded') { ?>
			<p>This Lending Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($application['funded_at'])); ?></span>. This confirms that I, <span class="blue-text"><?php echo $agreement['lendername']; ?></span> was lending an amount of <span class="blue-text">PHP <?php echo $agreement['amount']; ?></span> to <span class="blue-text"><?php echo $agreement['borrowername']; ?></span>  with a <span class="blue-text"><?php echo $agreement['interest']; ?></span> Interest, which to be paid within <span class="blue-text"><?php echo $agreement['term']; ?></span> and a monthly interest rate of <span class="blue-text">PHP <?php echo $agreement['monthly']; ?></span>.</p>
			<br><br><br><br><br><br><br><br>
			<span class="blue-text"><?php echo $agreement['borrowername']; ?></span>
			<br>
			<p>Borrower</p>
			
			<?php } elseif ($application['status'] == 'Paid') { ?>
			<p>This Closure Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($application['paid_at'])); ?></span>. This serves as a proof that <span class="blue-text"><?php echo $agreement['borrowername']; ?></span> Paid the amount of <span class="blue-text">PHP <?php echo $agreement['amount']; ?></span> which was owed from <span class="blue-text"><?php echo $agreement['lendername']; ?></span>  with a <span class="blue-text"><?php echo $agreement['interest']; ?></span> Interest, which has been paid within <span class="blue-text"><?php echo $agreement['term']; ?></span> and a monthly interest rate of <span class="blue-text">PHP <?php echo $agreement['monthly']; ?></span>.</p>
			<br><br><br><br><br><br><br><br>
			<span class="blue-text"><?php echo $agreement['borrowername']; ?></span>
			<br>
			<p>Borrower</p>
			
			<?php } ?>
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
	}
	?>
	
	<script src="js/swiper-bundle.min.js"></script>
	<script src="js/lenders.js"></script>
	
</body>

</html>
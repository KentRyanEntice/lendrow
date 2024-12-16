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

function getLenders($searchLenders = '')
{
    global $connection;
    $query = "SELECT * FROM lending_terms";
    
    if (!empty($searchLenders)) {
        $searchLenders = mysqli_real_escape_string($connection, $searchLenders);
        $query .= " WHERE picture LIKE '%$searchLenders%' OR created_at LIKE '%$searchLenders%' OR lendername LIKE '%$searchLenders%' OR amount LIKE '%$searchLenders%' OR interest LIKE '%$searchLenders%' OR term LIKE '%$searchLenders%' OR monthly LIKE '%$searchLenders%' OR status LIKE '%$searchLenders%'";
    }
    
	$query .= " ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $lender = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $lender[] = $row;
    }

    return $lender;
}

function getApplications($searchApplications = '')
{
    global $connection, $userData;

    $userId = $userData['id'];

    $query = "SELECT * FROM applications WHERE users_id = '$userId'";

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

function getLenderByLendingTermsId($lendingTermsId)
{
    global $connection;

    $lendingTermsId = mysqli_real_escape_string($connection, $lendingTermsId);

    $query = "SELECT * FROM lending_terms WHERE id='$lendingTermsId'";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    } else {
        return false;
    }
}

function getAgreementByLendingTermsId($lendingTermsId)
{
    global $connection;

    $lendingTermsId = mysqli_real_escape_string($connection, $lendingTermsId);

    $query = "SELECT * FROM lending_agreement WHERE lending_terms_id='$lendingTermsId'";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    } else {
        return false;
    }
}

?>

<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>LendRow Borrowers</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="pictures/logo.png" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="css/borrowers.css">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	
	<link rel="stylesheet" href="css/swiper-bundle.min.css">
</head>

<body>

	<?php include ('main-sidebar.php') ?>
			
			<div class="search" onclick="showSearch()">
				<i class='bx bx-search-alt-2'></i>
			</div>
			
			<div class="searchCard" id="searchForm">
			 <form method="GET">
				<div class="closeSearch" onclick="hideSearch()" id="btn"></div>
				<div class="inputBox">
					<input type="text" name="searchLenders" placeholder="Search lending informations here...">
				</div>
			 </form>
			</div>
	
	<div class="overlay-bg" id="overlayBg"></div>
	<div class="overlay-bg2" id="overlayBg2"></div>
	
	<div class="borrow-buttons">
	
		<div class="borrow-form-button active" onclick="showBorrowForm()">Lenders</div>
		<div class="application-manager-button" onclick="showApplicationManager()">My Applications</div>
		<div class="wallet-button" id="walletButton"><a href="wallet">+</a><span class="wallet">Wallet</span></div>
		<div class="view-lending" onclick="showLending()">Lending Details</div>
	
	</div>
	
	<div class="lending-details" id="lendingDetails">
	<i class='bx bxs-exit' onclick="hideLending()"></i>
		<div class="lending-history-content">
			<div class="lending-border">
				<table>
					<thead>
						<tr>
							<th>Month</th>
							<th>Total Loan Listings</th>
							<th>Total Open Loan</th>
							<th>Total Loan Approvals</th>
							<th>Loan Approval Rate</th>
						</tr>
					</thead>
							
					<tbody>
					
					<?php
                    $searchLenders = isset($_GET['searchLenders']) ? $_GET['searchLenders'] : '';
                    $lenders = getLenders($searchLenders);

                    $monthlyData = [];

                    foreach ($lenders as $lender) {
                        $monthYear = date("Y-m", strtotime($lender['created_at']));
                        if (!isset($monthlyData[$monthYear])) {
                            $monthlyData[$monthYear] = [
                                'total' => 0,
                                'open' => 0,
                                'closed' => 0,
                                'approvals' => 0,
                            ];
                        }

                        $monthlyData[$monthYear]['total']++;

                        if ($lender['status'] == 'Open') {
                            $monthlyData[$monthYear]['open']++;
                        } elseif ($lender['status'] == 'Closed') {
                            $monthlyData[$monthYear]['closed']++;
                            $monthlyData[$monthYear]['approvals']++;
                        }
                    }

                    foreach ($monthlyData as $monthYear => $data) {
                        $totalLoans = $data['total'];
                        $openCount = $data['open'];
                        $closedCount = $data['closed'];
                        $approvalCount = $data['approvals'];

                        $approvalRate = $totalLoans > 0 ? ($approvalCount / $totalLoans) * 100 : 0;

                        $formattedDate = date("F Y", strtotime($monthYear));

                        echo "<tr>
                                <td>$formattedDate</td>
                                <td>$totalLoans</td>
								<td>$openCount</td>
                                <td>$approvalCount</td>
                                <td>" . number_format($approvalRate, 2) . "%</td>
                            </tr>";
                    }
                    ?>
					

					</tbody>
						
				</table>
			</div>	
		</div>
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
						
						else if ($_GET["error"] == "ownlending") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Applying unsuccessful, you cannot apply to your own Lending.</p>";
						}
						else if ($_GET["error"] == "existingapplication") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Applying unsuccessful, since you already applied for this Lending.</p>";
						}
						
						else if ($_GET["error"] == "fundedapplication") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Cancelling unsuccessful, since your application has already been funded.</p>";
						}
						
						else if ($_GET["error"] == "rejectedapplication") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Cancelling unsuccessful, since your application has already been rejected.</p>";
						}
						
						else if ($_GET["error"] == "existingapproved") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Applying unsuccessful, since you currently have an existing approved application.</p>";
						}
						
						else if ($_GET["error"] == "existingdebt") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Applying unsuccessful, since you currently have an existing unpaid application.</p>";
						}
					}
					
					if(isset($_GET["success"])) {
						if ($_GET["success"] == "applied") {
							echo "<i class='bx bxs-check-circle'></i><p class='blue'>Congratulations! Your application has been added successfully.</p>";
						}
					}
					
					if(isset($_GET["success"])) {
						if ($_GET["success"] == "cancelled") {
							echo "<i class='bx bxs-check-circle'></i><p class='blue'>Your cancelled your application successfully.</p>";
						}
					}
				?>
		</div>
	
	<div class="borrowers-content">

	<div class="borrowers-form" id="borrowForm">
		<div class="slide-container swiper">
		<div class="slide-content">
			<div class="card-wrapper swiper-wrapper">
			
				<?php
					$searchLenders = isset($_GET['searchLenders']) ? $_GET['searchLenders'] : '';
					$lenders = getLenders($searchLenders);
					
					if (empty($lenders)) {
						echo '<p class="empty-lenders">No Lending Terms can be found.</p>';
					} else {

					foreach ($lenders as $lender) {
						$id = $lender['id'];
						
					if ($lender['status'] == 'Open') {
					?>
					<div class="card swiper-slide">
					
						<input type="hidden" placeholder="<?php echo $lender['id']; ?>" disabled>
						
						<input type="hidden" placeholder="<?php echo $lender['users_id']; ?>" disabled>
						
						<div class="image-content">
							<span class="overlay"></span>
								
								<div class="card-image">
									<div class="card-img">
											<?php
												if (!empty($lender['picture'])) {
													$profilePicturePath = './/' . $lender['picture'];
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
								
							<div class="card-view">
								<button class="view" onclick="showCard(<?php echo $lender['id'];?>)">Apply</button>
							</div>
							
							</div>
					</div>

				<?php
					}
					
					if ($lender['status'] == 'Closed') {
					?>
					<div class="card swiper-slide">
					
						<input type="hidden" placeholder="<?php echo $lender['id']; ?>" disabled>
						
						<input type="hidden" placeholder="<?php echo $lender['users_id']; ?>" disabled>
						
						<div class="image-content">
							<span class="overlay"></span>
								
								<div class="card-image">
									<div class="card-img">
											<?php
												if (!empty($lender['picture'])) {
													$profilePicturePath = './/' . $lender['picture'];
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
								
							<div class="card-view">
								<button class="view-closed">Closed</button>
							</div>
							
							</div>
					</div>

				<?php
					}
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
	
		<div class="application-form" id="applicationManager">
			<div class="application-card">
				
				<div class="myprofile-pic">	
					<div class="myimage-content">
						<span class="myoverlay"></span>
								
							<div class="mycard-image">
								<div class="mycard-img">
									<?php
									if (!empty($userData['picture'])) {
										$profilePicturePath = './/' . $userData['picture'];
										echo '<img src="' . $profilePicturePath . '" alt="Profile Picture">';
									} else {
										echo 'No profile picture available';
									}
									?>
								</div>
							</div>
							
							<div class="borrowername">
								<?php echo $userData['firstname']; ?> <?php echo $userData['middlename']; ?> <?php echo $userData['lastname']; ?>
							</div>	
					</div>	
				</div>
				
				<div class="mycard-details">
					<div class="mycard-content">
									
						<div class="mydetails">
							<label>Mobile No. : </label><input type="text" placeholder="<?php echo $userData['mobile']; ?>" disabled>
						</div>

					</div>
				</div>
				
				<div class="mycredit">
				</div>
				
				<div class="mylenders">
					<div class="mylenders-form">
						<h2>My Lenders Information</h2>
						
							<div class="applicants-content">
									<?php
										$searchApplications = isset($_GET['searchApplications']) ? $_GET['searchApplications'] : '';
										$applications = getApplications($searchApplications);
										
										if (empty($applications)) {
										echo '<p class="empty">You do not have any existing application.</p>';
									} else {

										foreach ($applications as $application) {
											$id = $application['id'];
											$lendingTermsId = $application['lending_terms_id'];

											$lender = getLenderByLendingTermsId($lendingTermsId);
											
										if ($application['status'] == 'Pending') {
									?>
										<div class="myborrowers">
											<div class="pending"></div>
										
											<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
											
											<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
											
											<p>You have <?php echo $application['status']; ?> application!<br> From: <?php echo $lender['lendername']; ?></p>
											
											<div class="mycard-view">
												<button class="myview" onclick="showLender(<?php echo $application['id'];?>)">View</button>
											</div>
										</div>
										
										<?php
										}
											elseif ($application['status'] == 'Cancelled') {
											?>
											<div class="myborrowers">
												<div class="pending"></div>
											
												<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
												
												<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
												
												<p>You <?php echo $application['status']; ?> your application!</p>
												
												<div class="mycard-view">
													<button class="myview" onclick="showLender(<?php echo $application['id'];?>)">View</button>
												</div>
											</div>
										
										<?php
										}
											elseif ($application['status'] == 'Approved') {
											?>
											<div class="myborrowers">
												<div class="approved"></div>
											
												<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
												
												<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
												
												<p>Your application has been <?php echo $application['status']; ?>!<br> From: <?php echo $lender['lendername']; ?></p>
												
												<div class="mycard-view">
													<button class="myview" onclick="showLender(<?php echo $application['id'];?>)">View</button>
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
												
												<p>Your application has been <?php echo $application['status']; ?>!<br> From: <?php echo $lender['lendername']; ?></p>

												<div class="mycard-view">
													<button class="myview" onclick="showLender(<?php echo $application['id'];?>)">View</button>
												</div>
											</div>
											<?php
										}
										
											elseif ($application['status'] == 'Funded') {
											?>
											<div class="myborrowers">
												<div class="funded"></div>
											
												<input type="hidden" placeholder="<?php echo $application['id']; ?>" disabled>
												
												<input type="hidden" placeholder="<?php echo $application['lending_terms_id']; ?>" disabled>
												
												<p>Your application has been <?php echo $application['status']; ?>!<br> From: <?php echo $lender['lendername']; ?></p>
												
												<div class="mycard-view">
													<button class="myview" onclick="showLender(<?php echo $application['id'];?>)">View</button>
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
												
												<p>Your application has been <?php echo $application['status']; ?>!<br> From: <?php echo $lender['lendername']; ?></p>
												
												<div class="mycard-view">
													<button class="myview" onclick="showLender(<?php echo $application['id'];?>)">View</button>
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
	
		<div class="borrowers-wallet">
	
			<?php include ('wallet.php') ?>
		
		</div>
	
	</div>


	<?php
		$search = isset($_GET['search']) ? $_GET['search'] : '';
		$lenders = getLenders($search);

		foreach ($lenders as $lender) {
		$id = $lender['id'];
	?>
		<div class="apply-card" id="applyCard<?php echo $lender['id']; ?>">
			<h2>Lending Data</h2>
		<form action="php/borrow" method="POST">
		
			<input type="hidden" name="lending_terms_id" value="<?php echo $lender['id']; ?>">
							
			<input type="hidden" name="id" id="id" placeholder="User ID" value="<?php echo htmlspecialchars($userData['id']); ?>">
		
			<input type="hidden" placeholder="Picture" name="picture" id="picture" value="<?php echo htmlspecialchars($userData['picture']); ?>"> 
			
			<input type="hidden" placeholder="Borrower Name" name="borrowername" id="borrowername" value="<?php echo htmlspecialchars($userData['firstname']); ?> <?php echo htmlspecialchars($userData['middlename']); ?> <?php echo htmlspecialchars($userData['lastname']); ?>">
			
			<input type="hidden" placeholder="Mobile No." name="mobile" id="mobile" value="<?php echo htmlspecialchars($userData['mobile']); ?>">

			<input type="hidden" placeholder="Status" name="status" id="status" value="Pending"> 
			
			<div class="agreement">
			<p>The terms below is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($lender['created_at'])); ?></span>. This will serve as a proof that I, <span class="blue-text"><?php echo $userData['firstname']; ?> <?php echo $userData['middlename']; ?> <?php echo $userData['lastname']; ?></span> will be borrowing an amount of <span class="blue-text">PHP <?php echo $lender['amount']; ?></span> with a <span class="blue-text"><?php echo $lender['interest']; ?></span> Interest from <span class="blue-text"><?php echo $lender['lendername']; ?></span>, which to be paid within <span class="blue-text"><?php echo $lender['term']; ?></span>, a monthly interest rate of <span class="blue-text">PHP <?php echo $lender['monthly']; ?></span> and fully disclose my <span class="blue-text">credit history.</span></p>
			
			<div class="check">
			<input type="checkbox" required><label>I agree with the above terms and conditions.</label>
			</div>
			</div>
		
		
			<div class="apply-card-button">
				<div class="decline" onclick="hideCard(<?php echo $lender['id'];?>)">Decline</div>
				<button type="submit" class="accept">Comply</button>
			</div>
		</form>
		</div>
	<?php
	}
	?>
	
	
	<?php
	$searchApplications = isset($_GET['search']) ? $_GET['search'] : '';
	$applications = getApplications($searchApplications);

	foreach ($applications as $application) {
		$id = $application['id'];
		$lendingTermsId = $application['lending_terms_id'];

		$lender = getLenderByLendingTermsId($lendingTermsId);
	?>
	
					<div class="lender-card" id="lenderCard<?php echo $application['id']; ?>">

						<div class="image-content">
							<span class="overlay"></span>
								
								<div class="card-image">
									<div class="card-img">
											<?php
												if (!empty($lender['picture'])) {
													$profilePicturePath = './/' . $lender['picture'];
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
								
								<div class="status <?php echo strtolower($application['status']); ?>">

								</div>

							<div class="card-view">
								<?php if ($application['status'] == 'Approved') { ?>
										<button class="close" onclick="hideLender(<?php echo $application['id']; ?>)">Close</button>
										<button class="view" onclick="showCancel(<?php echo $application['id']; ?>)">Cancel</button>
										<div class="view-agreement" onclick="showAgreementCard(<?php echo $application['id']; ?>)">Agreement</div>
										
								<?php } elseif ($application['status'] == 'Funded' || $application['status'] == 'Paid') { ?>
										<button class="close" onclick="hideLender(<?php echo $application['id']; ?>)">Close</button>
										<div class="view-agreement" onclick="showAgreementCard(<?php echo $application['id']; ?>)">Agreement</div>

								<?php } elseif ($application['status'] == 'Rejected' || $application['status'] == 'Cancelled') { ?>
										<button class="close" onclick="hideLender(<?php echo $application['id']; ?>)">Close</button>
										
								<?php } elseif ($application['status'] == 'Pending') { ?>
										<button class="close" onclick="hideLender(<?php echo $application['id']; ?>)">Close</button>
										<button class="view" onclick="showCancel(<?php echo $application['id']; ?>)">Cancel</button>
								<?php } ?>
							</div>
							
							</div>
							
							<div class="overlaycancelbg" id="overlayCancel"></div>
							
							<div class="cancel-form" id="cancelForm<?php echo $application['id']; ?>">
								<form action="php/cancel" method="POST">
								<h2>Cancel Application Form</h2>
								
									<p>Are you sure you want to cancel your application?</p>
									<input type="hidden" name="id" value="<?php echo $application['id']; ?>" readonly>
									
									<div class="buttons">
										<div class="cancel" onclick="cancel(<?php echo $application['id']; ?>)">Close</div>
										<button type="submit" class="create">Confirm</button>
									</div>
								</form>
							</div>
							
					</div>
					
					
					
					
	<?php
		
	}
	?>
	
	<?php
	$searchApplications = isset($_GET['search']) ? $_GET['search'] : '';
	$applications = getApplications($searchApplications);

	foreach ($applications as $application) {
		$id = $application['id'];
		$lendingTermsId = $application['lending_terms_id'];

		$lender = getAgreementByLendingTermsId($lendingTermsId);
	?>
	
	<div class="fund-card" id="agreementCard<?php echo $application['id']; ?>">
			<?php if ($application['status'] == 'Approved' || $application['status'] == 'Funded') { ?>
			<h2>Lending Agreement</h2>
			
			<?php } elseif ($application['status'] == 'Paid') { ?>
			<h2>Closure	 Agreement</h2>
			<?php } ?>
		<form action="#" method="POST">
		
			
			<div class="agreement">
			<?php if ($application['status'] == 'Approved') { ?>
			<p>This Lending Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($application['approved_at'])); ?></span>. This now confirms that <span class="blue-text"><?php echo $lender['lendername']; ?></span> will be lending an amount of <span class="blue-text">PHP <?php echo $lender['amount']; ?></span> to you <span class="blue-text"><?php echo $lender['borrowername']; ?></span>  with a <span class="blue-text"><?php echo $lender['interest']; ?></span> Interest, which to be paid within <span class="blue-text"><?php echo $lender['term']; ?></span> and a monthly interest rate of <span class="blue-text">PHP <?php echo $lender['monthly']; ?></span>.</p>
			
			<?php } elseif ($application['status'] == 'Funded') { ?>
			<p>This Lending Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($application['funded_at'])); ?></span>. This now confirms that<span class="blue-text"><?php echo $lender['lendername']; ?></span> was lending an amount of <span class="blue-text">PHP <?php echo $lender['amount']; ?></span> to you <span class="blue-text"><?php echo $lender['borrowername']; ?></span> with a <span class="blue-text"><?php echo $lender['interest']; ?></span> Interest, which to be paid within <span class="blue-text"><?php echo $lender['term']; ?></span> and a monthly interest rate of <span class="blue-text">PHP <?php echo $lender['monthly']; ?></span>.</p>
			
			<?php } elseif ($application['status'] == 'Paid') { ?>
			<p>This Closure Agreement is effective as of <span class="blue-text"><?php echo date("F d, Y h:i A", strtotime($application['paid_at'])); ?></span>. This now confirms that <span class="blue-text"><?php echo $lender['borrowername']; ?></span> had <span class="blue-text">Paid</span> the amount of <span class="blue-text">PHP <?php echo $lender['amount']; ?></span> which was owed from <span class="blue-text"><?php echo $lender['lendername']; ?></span>  with a <span class="blue-text"><?php echo $lender['interest']; ?></span> Interest, which has been paid within <span class="blue-text"><?php echo $lender['term']; ?></span> and a monthly interest rate of <span class="blue-text">PHP <?php echo $lender['monthly']; ?>.</span></p>
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

	<script src="js/swiper-bundle.min.js"></script>
	<script src="js/borrowers.js"></script>
	
</body>

</html>
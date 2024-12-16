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
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
	<title>LendRow</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="pictures/logo.png" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="css/sidebar.css">
</head>

<body>

	<h1>LENDROW</h1>
	
			<div class="picture">
				<div class="prof">
				<a href="profile">
					<?php
					if (!empty($userData['picture'])) {
						$profilePicturePath = './/' . $userData['picture'];
						echo '<img src="' . $profilePicturePath . '" alt="Profile Picture">';
					} else {
						echo 'No profile';
					}
					?>
					</a>
				</div>
				
			</div>
			
			<div class="name">
			Welcome!<br>  <?php echo htmlspecialchars($userData['username']); ?>
			</div>

		<div class="menu">
		  <div id="btn"></div>
		</div>
		
		<div class="navigation">
		
		<?php
		$userType = $_SESSION['admin'];

		if ($userType == 'admin') {
		?>
			<li class="list" id="adminwallet">
				<a href="adminwallet">
				<span class="icon"><ion-icon name="card-outline"></ion-icon></span>
				<span class="text">Admin</span>
				</a>
			</li>
		<?php
		}
		?>
		
			<li class="list" id="lenders">
				<a href="lenders">
				<span class="icon"><ion-icon name="person-add-outline"></ion-icon></span>
				<span class="text">Lend</span>
				</a>
			</li>
			
			<li class="list" id="borrowers">
				<a href="borrowers">
				<span class="icon"><ion-icon name="person-remove-outline"></ion-icon></span>
				<span class="text">Borrow</span>
				</a>
			</li>
			
			<li class="list" id="payment">
				<a href="payment">
				<span class="icon"><ion-icon name="card-outline"></ion-icon></span>
				<span class="text">Pay</span>
				</a>
			</li>
			
		</div>

			<div class="content">
				
			</div>

	<div class="copyright">
		<p>©2023 LendRow Official</p>
	</div>
	
	<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
	<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
	
	<script src="js/sidebar.js"></script>
</body>

</html>
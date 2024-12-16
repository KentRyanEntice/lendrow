<?php
session_start();
include 'php/config.php';

if (!isset($_SESSION['username'])) {
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

?>

<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>LendRow Profile</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="pictures/logo.png" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="css/profile.css">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

	<?php include ('main-sidebar.php') ?>

		<div class="h1-profile">PROFILE</div>
			<div class="logout" onclick="logOut()">Log Out</div>
			
			<div class="logout-form" id="logOutForm">
				<form action="php/logout">
				<h2>Log Out Form</h2>
				
					<p>Are you sure you want to Log Out?</p>
					
					<div class="buttons">
						<div class="cancel" onclick="cancelLogOut()">Cancel</div>
						<button type="submit" class="create">Confirm</button>
					</div>
				</form>
			</div>
			

			<div class="prof-picture">
				<div class="my-profile">
						<?php
							if (!empty($userData['picture'])) {
								$profilePicturePath = './/' . $userData['picture'];
								echo '<img src="' . $profilePicturePath . '" alt="Profile Picture">';
							} else {
								echo 'No profile picture available';
							}
						?>
				</div>
					<button class="edit-pic" id="edit-pic">Edit Picture</button>
			</div>		
			
			 <div class="overlay-bg" id="overlayBg"></div>
			
			<div class="picture-form" id="pictureForm">
				<h2>Edit Picture Form</h2>
				<div class="picture-form-info">	
					 <form action="php/picture" method="POST" enctype="multipart/form-data">
					 
					 <input type="hidden" name="id" value="<?php echo $userData['id']; ?>">
					
						<div class="inputBox">
							<label>Insert Profile Picture (less than 2MB)</label>
							<i class='bx bxs-image-add'></i>
							<input class="file" type="file" placeholder="Profile Picture" name="picture" id="picture" accept="image/*">
						</div>
						
						<div class="buttons">
							<div class="cancel" onclick="hidePictureForm()">Cancel</div>
							<button type="submit" name="submit" class="create">Save</button>
						</div>
					</form>
				</div>
			</div>
			
			
			<div class="my-info">
				<div class="profile-info">
					<h2>Profile Information</h2>
					<form action="#">
						<input type="hidden" placeholder="User ID" value="<?php echo htmlspecialchars($userData['id']); ?>" disabled>
						
						<div class="inputBox">
							<i class='bx bxs-user-rectangle' ></i>
							<input type="text" placeholder="Full Name" value="<?php echo htmlspecialchars($userData['firstname']); ?>" disabled>
						</div>
						<div class="inputBox">
							<i class='bx bxs-user-badge'></i>
							<input type="text" placeholder="Full Name" value="<?php echo htmlspecialchars($userData['middlename']); ?>" disabled>
						</div>
						<div class="inputBox">
							<i class='bx bxs-user-account' ></i>
							<input type="text" placeholder="Full Name" value="<?php echo htmlspecialchars($userData['lastname']); ?>" disabled>
						</div>
						<div class="inputBox">
							<i class='bx bxs-user'></i>
							<input type="text" placeholder="Username" value="<?php echo htmlspecialchars($userData['username']); ?>" disabled>
						</div>
						<div class="inputBox">
							<i class='bx bxs-phone'></i>
							<input type="text" placeholder="Mobile No." value="<?php echo htmlspecialchars($userData['mobile']); ?>" disabled>
						</div>
						<div class="inputBox">
							<i class='bx bxl-gmail'></i>
							<input type="email" placeholder="Enter an Email" value="<?php echo htmlspecialchars($userData['email']); ?>" disabled>
						</div>
						
						<div class="errors">
							<?php
								if(isset($_GET["error"])) {
									if ($_GET["error"] == "error") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Please insert correct image format!</p>";
									}
									
									if ($_GET["error"] == "emptypicture") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Please insert an image to upload!</p>";
									}
									
									if ($_GET["error"] == "invalidsize") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Please insert image below 2MB!</p>";
									}
									
									if ($_GET["error"] == "invalidformat") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Please insert correct image format!</p>";
									}
									
									if ($_GET["error"] == "movefailed") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Error while moving picture!</p>";
									}
									
									if ($_GET["error"] == "profileuploadfailed") {
										echo "<i class='bx bxs-error-circle'></i><p class='red'>Profile has not been uploaded!</p>";
									}
									
									else if ($_GET["error"] == "duplicate_submission") {
										$resubmitTime = $_SESSION['last_submission_time'] + 5;

										$remainingTime = max(0, $resubmitTime - time());

										$minutes = floor($remainingTime / 60);
										$seconds = $remainingTime % 60;

										echo "<i class='bx bxs-error-circle'></i><p class='red'>Duplicate Submission, please wait for ";
										echo $seconds . "s before resubmitting.</p>";
									}
								}
								
								if(isset($_GET["success"])) {
									if ($_GET["success"] == "uploaded") {
										echo "<i class='bx bxs-check-circle'></i><p class='blue'>Your image has been uploaded successfully!</p>";
									}
								}
							?>
				
						</div>
			
						<button type="submit" class="edit">Request Edit</button>
					</form>
				</div>
			</div>
	
    <script src="js/profile.js"></script>
</body>

</html>
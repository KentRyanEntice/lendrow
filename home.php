<?php
session_start();

if (isset($_SESSION['username'])) {
    header("Location: profile");
    exit;
}

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>LendRow SignIn/SignUp</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="pictures/logo.png" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="css/home.css">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

	<div class="lendrow-title"><p>LendRow</p></div>

    <div class="wrapper">
        <div class="form sign-up">
            <form action="php/register" method="POST">
                <h2>Sign Up</h2>
				<div class="inputBox">
					<i class='bx bxs-user-rectangle' ></i>
                    <input type="text" name="firstname" id="firstname" placeholder="Enter your Firstname">
                </div>
				<div class="inputBox">
					<i class='bx bxs-user-badge'></i>
                    <input type="text" name="middlename" id="middlename" placeholder="Enter your Middlename">
                </div>
				<div class="inputBox">
					<i class='bx bxs-user-account' ></i>
                    <input type="text" name="lastname" id="lastname" placeholder="Enter your Lastname">
                </div>
                <div class="inputBox">
					<i class='bx bxs-user'></i>
                    <input type="text" name="username" id="username" placeholder="Enter your Username">
                </div>
				<div class="inputBox">
					<i class='bx bxs-phone'></i>
                    <input type="text" name="mobile" id="mobile" placeholder="Enter your Mobile No.">
                </div>
                <div class="inputBox">
					<i id="toggle1" class='bx bxs-lock'></i>
                    <input type="password" name="pass" id="pass1" placeholder="Enter a Password">
                </div>
				<div class="inputBox">
					<i id="toggle2" class='bx bxs-lock'></i>
                    <input type="password" name="confirmpass" id="confirmpass" placeholder="Confirm your Password">
                </div>
                <button type="submit" class="btn">Register</button>
                <div class="link">
                    <p>Already have an account?<a href="#" class="signin-link"> Sign In</a></p>
                </div>
            </form>
        </div>
        <div class="form sign-in">
            <form action="php/login" method="POST">
                <h2>Sign In</h2>
                <div class="inputBox">
					<i class='bx bxs-user'></i>
                    <input type="text" name="username" id="username" placeholder="Enter your Username">
                </div>
                <div class="inputBox">
					<i id="toggle" class='bx bxs-lock'></i>
                    <input type="password" name="pass" id="pass" placeholder="Enter your Password">
                </div>
                <button type="submit" class="btn">Log In</button>
                <div class="link">
                    <p>Don't have an account yet?<a href="#" class="signup-link"> Sign Up</a></p>
                </div>
            </form>
			
			<div class="errors">
				<?php
					if(isset($_GET["error"])) {
						if ($_GET["error"] == "emptyinput") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>There are empty fields, please fill in all fields!</p>";
						}
						
						if ($_GET["error"] == "wronglogin") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Incorrect Login information, Please try again!</p>";
						}
						
						else if ($_GET["error"] == "invalidfirstname") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Firstname! You can only use uppercase, lower case letters and numbers.</p>";
						}
						
						else if ($_GET["error"] == "invalidmiddlename") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Middlename! You can only use uppercase, lower case letters and numbers.</p>";
						}
						
						else if ($_GET["error"] == "invalidlastname") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Lastname! You can only use uppercase, lower case letters and numbers.</p>";
						}
						
						else if ($_GET["error"] == "invalidusername") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Username! You can only use uppercase, lower case letters and numbers.</p>";
						}
						
						else if ($_GET["error"] == "duplicate_submission") {
							$resubmitTime = $_SESSION['last_submission_time'] + 5;

							$remainingTime = max(0, $resubmitTime - time());

							$minutes = floor($remainingTime / 60);
							$seconds = $remainingTime % 60;

							echo "<i class='bx bxs-error-circle'></i><p class='red'>Duplicate Submission, please wait for ";
							echo $seconds . "s before resubmitting.</p>";
						}
						
						else if ($_GET["error"] == "invalidmobile") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Invalid Mobile No., Please use correct mobile no. format!</p>";
						}
						
						else if ($_GET["error"] == "passdontmatch") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Your password didn't matched, Please try again!</p>";
						}
						
						else if ($_GET["error"] == "fullnametaken") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>The Fullname has already been registered, Please choose another one!</p>";
						}
						
						else if ($_GET["error"] == "usernametaken") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>The username has already been taken, Please choose another one!</p>";
						}
						
						else if ($_GET["error"] == "mobiletaken") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Mobile No. has already been registered, Please use another mobile no.!</p>";
						}
						
						else if ($_GET["error"] == "unauthorizedaccess") {
							echo "<i class='bx bxs-error-circle'></i><p class='red'>Unauthorized access has been detected, your session has been destroyed!</p>";
						}
						
					}
					if(isset($_GET["success"])) {
						if ($_GET["success"] == "registered") {
							echo "<i class='bx bxs-check-circle' ></i><p class='blue'>Congratulations! Your information has been registered successfully.</p>";
						}
					}
				?>
		</div>
		
        </div>
    </div>
	
	
	
	<div class="copyright">
		<p>©2023 LendRow Official</p>
	</div>
	
    <script src="js/home.js"></script>
	
	
</body>

</html>
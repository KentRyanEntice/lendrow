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
	<title>LendRow</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="pictures/logo.png" type="image/x-icon">
	<link rel="stylesheet" type="text/css" href="css/index.css">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
            <div class="lendrow">
				<h1>LendRow</h1>
				<h2>LendRow</h2>
				
				<p class="class1">Connect with creditors and debtors</p>
				
				<p class="class3">around you with lendrow.</p>
				
				<div class="start">
				<a href="home" class="animated-dots">Get Started<span class="dots"></span></a>
				</div>
			</div>

	<div class="copyright">
		<p>©2023 LendRow Official</p>
	</div>
	
    <script src="js/index.js"></script>
	
	
</body>

</html>
<?php

function emptyInputSignup($firstname, $middlename, $lastname, $username, $mobile, $pass, $confirmpass) {
		$result;
		if (empty($firstname) || empty($middlename) || empty($lastname) || empty($username) || empty($mobile) || empty($pass) || empty($confirmpass)){
			$result = true;
		}
		else {
			$result = false;
		}
		return $result;
	}

function invalidUsername($username) {
		$result;
		if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
			$result = true;
		} 
		else {
			$result = false;
		}
		return $result;
	}

function invalidMobile($mobile) {
		$result;
		if (!preg_match("/^09\d{9}$/", $mobile)) {
			$result = true;
		} 
		else {
			$result = false;
		}
		return $result;
	}
	
function invalidPass($pass, $confirmpass) {
		$result;
		if ($pass !== $confirmpass) {
			$result = true;
		}
		else {
			$result = false;
		}
		return $result;
	}
	
function fullnameTaken($connection, $firstname, $middlename, $lastname) {
		$query = "SELECT * FROM users WHERE firstname = ? AND middlename = ? AND lastname = ?;";
		$stmt = mysqli_stmt_init($connection);
		
		if (!mysqli_stmt_prepare($stmt, $query)) {
			header("location: ../home?error=stmtfailed");
			exit();
		}
		
		mysqli_stmt_bind_param($stmt, "sss", $firstname, $middlename, $lastname);
		mysqli_stmt_execute($stmt);
		
		$resultData = mysqli_stmt_get_result($stmt);
		
		if ($row = mysqli_fetch_assoc($resultData)) {
			return $row;
		}
		else {
			$result = false;
			return $result;
		}
		
		mysqli_stmt_close($stmt);
	}
	
function usernameTaken($connection, $username) {
		$query = "SELECT * FROM users WHERE username = ?;";
		$stmt = mysqli_stmt_init($connection);
		
		if (!mysqli_stmt_prepare($stmt, $query)) {
			header("location: ../home?error=stmtfailed");
			exit();
		}
		
		mysqli_stmt_bind_param($stmt, "s", $username);
		mysqli_stmt_execute($stmt);
		
		$resultData = mysqli_stmt_get_result($stmt);
		
		if ($row = mysqli_fetch_assoc($resultData)) {
			return $row;
		}
		else {
			$result = false;
			return $result;
		}
		
		mysqli_stmt_close($stmt);
	}
	
function mobileTaken($connection, $mobile) {
    $query = "SELECT * FROM users WHERE mobile = ?;";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../home?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $mobile);
    mysqli_stmt_execute($stmt);

    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row;
    } else {
        $result = false;
        return $result;
    }

    mysqli_stmt_close($stmt);
}
	
function createUser($connection, $firstname, $middlename, $lastname, $username, $mobile, $pass) {
    $query = "INSERT INTO users (firstname, middlename, lastname, username, mobile, pass) VALUES (?,?,?,?,?,?)";
    $stmt = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmt, $query)) {
        header("location: ../home?error=stmtfailed");
        exit();
    }

    $hashedpass = password_hash($pass, PASSWORD_DEFAULT);

    mysqli_stmt_bind_param($stmt, "ssssss", $firstname, $middlename, $lastname, $username, $mobile, $hashedpass);
    mysqli_stmt_execute($stmt);


    $userId = mysqli_insert_id($connection);

    mysqli_stmt_close($stmt);

    $queryWallet = "INSERT INTO wallet (fullname, mobile, balance, users_id) VALUES (?,?,?,?)";
    $stmtWallet = mysqli_stmt_init($connection);

    if (!mysqli_stmt_prepare($stmtWallet, $queryWallet)) {
        header("location: ../home?error=stmtfailed");
        exit();
    }

    $fullname = $firstname . ' ' . $middlename . ' ' . $lastname;
    $initialBalance = '0.00';

    mysqli_stmt_bind_param($stmtWallet, "sssi", $fullname,  $mobile, $initialBalance, $userId);
    mysqli_stmt_execute($stmtWallet);
    mysqli_stmt_close($stmtWallet);

    header("location: ../home?success=registered");
    exit();
}

	
function emptyInputLogin($username, $pass) {
		$result;
		if (empty($username) || empty($pass)) {
			$result = true;
		}
		else {
			$result = false;
		}
		return $result;
	}
	
function loginUser($connection, $username, $pass) {
    $userDetails = usernameTaken($connection, $username);

    if ($userDetails === false) {
        header("location: ../home?error=wronglogin");
        exit();
    }

    $passHashed = $userDetails["pass"];
    $checkpass = password_verify($pass, $passHashed);

    if ($checkpass === false) {
        header("location: ../home?error=wronglogin");
        exit();
    } else if ($checkpass === true) {
        session_start();
        $_SESSION["id"] = $userDetails["id"];
        $_SESSION["username"] = $userDetails["username"];

        if ($userDetails['usertype'] == 'admin') {
            $_SESSION['admin'] = true;
            header("location: ../adminwallet");
            exit();
        } else {
            $_SESSION['admin'] = false;
            header("location: ../lenders");
            exit();
        }
    }
}

?>
<?php

if (!isset($_SESSION['username'])) {
    header("Location: ../home");
    exit;
}

function emptyPicture($picture) {
    if (empty($picture["name"])) {
        return true;
	} else {
        return false;		
 }
}
	
function invalidSize($picture) {
    $maxFileSize = 2 * 1024 * 1024;
    if ($picture["size"] > $maxFileSize) {
        return true;
    } else {
        return false;
    }
}

function invalidFormat($picture) {
    $allowedFormats = array("jpg", "jpeg", "png", "gif");

    $fileExtension = strtolower(pathinfo($picture["name"], PATHINFO_EXTENSION));

    $imageInfo = getimagesize($picture["tmp_name"]);
    
    if ($imageInfo === false) {
        return true;
    }

    if (!in_array($fileExtension, $allowedFormats)) {
        return true;
    }
    
    return false;
}


function addPicture($connection, $id) {
    if (isset($_FILES['picture']['tmp_name'])) {
        $file = $_FILES['picture']['tmp_name'];
        $picture = addslashes(file_get_contents($file));
        $picture_name = addslashes($_FILES['picture']['name']);
        $picture_size = getimagesize($file);
		$upload_dir = "../pictures/";
		
		
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    header("Location: ../profile?error=directorycreateerror");
                    exit();
                }
            }

            $upload_path = $upload_dir . $picture_name;

            if (file_exists($upload_path)) {
                $fileExtension = pathinfo($picture_name, PATHINFO_EXTENSION);
                $picture_name = pathinfo($picture_name, PATHINFO_FILENAME) . '_' . time() . '.' . $fileExtension;
                $upload_path = $upload_dir . $picture_name;
            }

            if (move_uploaded_file($file, $upload_path)) {

                $query = "UPDATE users SET picture = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt = mysqli_stmt_init($connection);

                if (!mysqli_stmt_prepare($stmt, $query)) {
                    header("Location: ../profile?error=stmtfailed");
                    exit();
                }

                mysqli_stmt_bind_param($stmt, "si", $upload_path, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                header("Location: ../profile?success=uploaded");
                exit();
            } else {
                header("Location: ../profile?error=movefailed");
                exit();
		}
    }
}

?>
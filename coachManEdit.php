<?php
session_start();
include('connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $coEmail = $_POST['coEmail'];
    $coPhone = $_POST['coPhone'];
    $coAge = $_POST['coAge'];
    $coAbout = $_POST['coAbout'];

    if (empty($coEmail)) {
        $errors[] = "Email cannot be empty.";
    }
    if (empty($coPhone)) {
        $errors[] = "Phone number cannot be empty.";
    }
    if (empty($coAge)) {
        $errors[] = "Age cannot be empty.";
    }
    if (empty($coAbout)) {
        $errors[] = "About section cannot be empty.";
    }
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
        echo "<meta http-equiv=\"refresh\" content=\"1;URL=coachEdit.php\">";
        exit;
    }

    // Check if a profile picture is uploaded
    if (isset($_FILES['profile_pic']) && !empty($_FILES['profile_pic']['tmp_name'])) {
        $coPic = file_get_contents($_FILES['profile_pic']['tmp_name']);

        // Check if COACH_PROPIC already exists for this user
        $checkStmt = $conn->prepare("SELECT COACH_PROPIC FROM coach WHERE COACH_USERNAME = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // Update if COACH_PROPIC exists
            $stmt = $conn->prepare("UPDATE coach SET COACH_PROPIC = ?, COACH_EMAIL = ?, COACH_PHONE = ?, COACH_AGE = ?, COACH_ABOUT = ? WHERE COACH_USERNAME = ?");
            $stmt->bind_param("bsssss", $coPic, $coEmail, $coPhone, $coAge, $coAbout, $username);
            $stmt->send_long_data(0, $coPic); // Send binary data
        } else {
            // Insert if COACH_PROPIC does not exist
            $stmt = $conn->prepare("INSERT INTO coach (COACH_PROPIC, COACH_EMAIL, COACH_PHONE, COACH_AGE, COACH_ABOUT, COACH_USERNAME) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("bsssss", $coPic, $coEmail, $coPhone, $coAge, $coAbout, $username);
            $stmt->send_long_data(0, $coPic); // Send binary data
        }
        $checkStmt->close();

        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully.');</script>";
            echo "<meta http-equiv=\"refresh\" content=\"0;URL=coachProfile.php\">";
        } else {
            echo "<script>alert('Error updating record: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        // Update the database without the profile picture
        $stmt = $conn->prepare("UPDATE coach SET COACH_EMAIL = ?, COACH_PHONE = ?, COACH_AGE = ?, COACH_ABOUT = ? WHERE COACH_USERNAME = ?");
        $stmt->bind_param("sssss", $coEmail, $coPhone, $coAge, $coAbout, $username);
        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully.');</script>";
            echo "<meta http-equiv=\"refresh\" content=\"0;URL=coachProfile.php\">";
        } else {
            echo "<script>alert('Error updating record: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

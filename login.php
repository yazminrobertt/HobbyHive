<?php
    session_start();
    include('connect.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['password'] = $_POST['pwd']; // Store password in session
    }

    // Query to check both parent and coach tables for the username
    $sql = "SELECT 'parent' AS accountType, PARENT_USERNAME AS username, PARENT_PASS AS pwd 
            FROM parent 
            WHERE PARENT_USERNAME = '".$_SESSION['username']."'
            UNION
            SELECT 'coach' AS accountType, COACH_USERNAME AS username, COACH_PASS AS pwd
            FROM coach
            WHERE COACH_USERNAME = '".$_SESSION['username']."'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if password matches the one from the database
        if ($_SESSION['password'] === $row['pwd']) {
            $_SESSION['loggedIn'] = true;
            $_SESSION['accountType'] = $row['accountType']; 
            
            if ($_SESSION['accountType'] === 'parent') {
                header('Location: index.php');
            } else {
                header('Location: coachProfile.php');
            }
            exit;
        } else {
            echo "<script>alert('Login Failed: Incorrect password');</script>";
            session_unset(); 
            echo "<meta http-equiv=\"refresh\" content=\"0;URL=login.html\">"; 
        }
    } else {
        echo "<script>alert('User not registered');</script>";
        session_unset(); 
        echo "<meta http-equiv=\"refresh\" content=\"0;URL=login.html\">"; 
    }

    $conn->close();
?>

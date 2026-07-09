<?php
    include('connect.php');

    $coach_username = $_POST['coUsername'];
    $coach_name = $_POST['coName'];
    $coach_phone = $_POST['coPhone'];
    $coach_email = $_POST['coEmail'];
    $coach_gender = $_POST['coGender'];
    $coach_pass = $_POST['coPwd'];
    $coach_passcon = $_POST['coPassCon'];

    if ($coach_pass !== $coach_passcon) {
        echo "<script>alert('Passwords do not match.'); window.location.href = 'signCoach.html';</script>";
        exit(); 
    }

    $sql_check = "SELECT * FROM coach WHERE COACH_USERNAME = '$coach_username'";
    $result_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result_check) > 0) {
        echo "<script>alert('Username already exists. Please choose a different username.'); window.location.href = 'signCoach.html';</script>";
        exit();
    } else {
        mysqli_begin_transaction($conn);

        try {
            $sql = "INSERT INTO coach(COACH_USERNAME, COACH_NAME, COACH_PASS, COACH_PHONE, COACH_EMAIL, COACH_GENDER) 
                    VALUES('$coach_username', '$coach_name', '$coach_passcon', '$coach_phone', '$coach_email', '$coach_gender')";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Error inserting data into coach table: " . mysqli_error($conn));
            }

            $initial_amount = 0.00; // Default amount 
            $sql_wallet = "INSERT INTO coach_wallet(COACH_USERNAME, COACH_AMOUNT) VALUES('$coach_username', $initial_amount)";

            if (!mysqli_query($conn, $sql_wallet)) {
                throw new Exception("Error inserting data into coach_wallet table: " . mysqli_error($conn));
            }

            mysqli_commit($conn);

            echo "<script>alert('Sign-up successful! Wallet created.'); window.location.href = 'login.html';</script>";
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);

            echo "Error: " . $e->getMessage();
        }
    }

    mysqli_close($conn);
?>

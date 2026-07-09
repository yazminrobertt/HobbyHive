<?php
    include('connect.php');

    $parent_username = $_POST['username'];
    $parent_name = $_POST['name'];
    $parent_phone = $_POST['phone'];
    $parent_email = $_POST['email'];
    $parent_pass = $_POST['pwd'];
    $parent_passcon = $_POST['parentPwd'];

    if ($parent_pass !== $parent_passcon) {
        echo "<script>alert('Passwords do not match.'); window.location.href = 'signParent.html';</script>";
        exit();
    }

    $sql_check = "SELECT * FROM parent WHERE PARENT_USERNAME = '$parent_username'";
    $result_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result_check) > 0) {
        echo "<script>alert('Username already exists. Please choose a different username.'); window.location.href = 'signParent.html';</script>";
        exit();
    } else {
        mysqli_begin_transaction($conn);

        try {
            $sql = "INSERT INTO parent(PARENT_USERNAME, PARENT_NAME, PARENT_PASS, PARENT_PHONE, PARENT_EMAIL) 
                    VALUES('$parent_username', '$parent_name', '$parent_passcon', '$parent_phone', '$parent_email')";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Error inserting data into parent table: " . mysqli_error($conn));
            }

            $initial_amount = 0.00; // Default amount 
            $sql_wallet = "INSERT INTO parent_wallet(PARENT_USERNAME, PARENT_AMOUNT) VALUES('$parent_username', $initial_amount)";

            if (!mysqli_query($conn, $sql_wallet)) {
                throw new Exception("Error inserting data into parent_wallet table: " . mysqli_error($conn));
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

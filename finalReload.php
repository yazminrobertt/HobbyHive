<?php
    session_start();
    include('connect.php');

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the amount from the POST data
        $amount = $_POST['amount'];
        $parentUsername = $_SESSION['username']; // Assuming username is stored in the session
        
        // Prepare and execute the update query to deduct the amount from parent_wallet
        $updateParentQuery = "UPDATE parent_wallet SET PARENT_AMOUNT = PARENT_AMOUNT + ? WHERE PARENT_USERNAME = ?";
        if ($stmt = $conn->prepare($updateParentQuery)) {
            $stmt->bind_param("ds", $amount, $parentUsername);
            if ($stmt->execute()) {
                // Update successful, now insert the transaction into parent_trans
                $insertTransQuery = "INSERT INTO parent_trans (TRANS_TYPE, TRANS_AMOUNT, PARENTWALLET_ID) 
                VALUES ('RELOAD', ?, (SELECT PARENTWALLET_ID FROM parent_wallet WHERE PARENT_USERNAME = ?))";
                if ($stmt2 = $conn->prepare($insertTransQuery)) {
                    $stmt2->bind_param("ds", $amount, $parentUsername);
                    if ($stmt2->execute()) {
                        // If both queries are successful, show the success message and redirect
                        $message = "Successfully reloaded! Amount has been transferred to your wallet.";
                        echo "<script>alert('$message'); window.location.href = 'parentWallet.php';</script>";
                    } else {
                        // If inserting transaction fails, show an error message
                        $message = "Error while recording the transaction. Please try again.";
                        echo "<script>alert('$message'); window.location.href = 'parentWallet.php';</script>";
                    }
                }
            } else {
                // If updating parent wallet fails, show an error message
                $message = "Error while updating the wallet. Please try again.";
                echo "<script>alert('$message'); window.location.href = 'parentWallet.php';</script>";
            }
        } else {
            // If preparing the update query fails
            $message = "Error with database connection. Please try again.";
            echo "<script>alert('$message'); window.location.href = 'parentWallet.php';</script>";
        }
    }
?>

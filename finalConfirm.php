<?php
session_start();
include('connect.php');

// Check if it's a POST request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve the posted data
        $bookingPax = $_POST['bookingPax'];
        $bookStartDate = $_POST['bookStartDate'];
        $bookEndDate = $_POST['bookEndDate'];
        $bookChosenPricing = $_POST['bookChosenPricing'];
        $bookTotalPrice = $_POST['bookTotalPrice'];
        $bookOfferId = $_POST['bookOfferId'];
        $bookSesh = $_POST['bookSesh'];
        $selectedChildrenIds = isset($_POST['selectedChildrenIds']) ? $_POST['selectedChildrenIds'] : [];
        $parentUsername = $_SESSION['username'];

        $timezone = new DateTimeZone('Asia/Kuala_Lumpur');  
        $today = new DateTime("now", $timezone);
        $todaysDate = $today->format("Y-m-d");

        // Fetch parent wallet details
        $parentQuery = "SELECT PARENT_AMOUNT FROM parent_wallet WHERE PARENT_USERNAME = ?";
        $stmt = $conn->prepare($parentQuery);
        $stmt->bind_param("s", $parentUsername);
        $stmt->execute();
        $parentResult = $stmt->get_result();
        $parent = $parentResult->fetch_assoc();

        if ($parent['PARENT_AMOUNT'] >= $bookTotalPrice) {
            // Proceed with booking and updating balance
            $bookingQuery = "INSERT INTO booking (BOOKING_PLACEMENTDATE,BOOKING_PAX, BOOKING_STARTDATE, BOOKING_ENDDATE, BOOKING_CHOSENPRICING, BOOKING_TOTALPRICE, IS_PAID, IS_DONE,IS_CANCELED, OT_ID, OFFER_ID, PARENT_USERNAME) 
            VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
            $stmt = $conn->prepare($bookingQuery);
            $isPaid = 0;  
            $isDone = 0;
            $isCanceled=0;
            $stmt->bind_param("sisssdiiiiis",$todaysDate, $bookingPax, $bookStartDate, $bookEndDate, $bookChosenPricing, $bookTotalPrice, $isPaid, $isDone,$isCanceled, $bookSesh, $bookOfferId, $parentUsername);
            $stmt->execute();

            // Deduct money from parent's balance
            $updateParentQuery = "UPDATE parent_wallet SET PARENT_AMOUNT = PARENT_AMOUNT - ? WHERE PARENT_USERNAME = ?";
            $stmt = $conn->prepare($updateParentQuery);
            $stmt->bind_param("ds", $bookTotalPrice, $parentUsername);
            $bookingId = $conn->insert_id;

            if ($stmt->execute()) {
                // Update IS_PAID to 1
                $updateBookingQuery = "UPDATE booking SET IS_PAID = 1 WHERE BOOKING_ID = ?";
                $stmt = $conn->prepare($updateBookingQuery);
                $stmt->bind_param("i", $bookingId);
                $stmt->execute();

                $insertTransQuery = "INSERT INTO parent_trans (TRANS_TYPE, TRANS_AMOUNT, PARENTWALLET_ID) 
                VALUES ('PAYMENT', ?, (SELECT PARENTWALLET_ID FROM parent_wallet WHERE PARENT_USERNAME = ?))";
                $stmt = $conn->prepare($insertTransQuery);
                $stmt->bind_param("ds", $bookTotalPrice, $parentUsername);
                $stmt->execute();

                $transId = $conn->insert_id;

                // Insert into payment_record
                $insertPaymentRecordQuery = "INSERT INTO payment_record (TRANS_ID, BOOKING_ID) VALUES (?, ?)";
                $stmt = $conn->prepare($insertPaymentRecordQuery);
                $stmt->bind_param("ii", $transId, $bookingId);
                $stmt->execute();
        
                // Insert selected children into selected_children table
                foreach ($selectedChildrenIds as $childId) {
                    $childQuery = "INSERT INTO selected_child (CHILD_ID, BOOKING_ID) VALUES ( ?, ?)";
                    $stmt = $conn->prepare($childQuery);
                    $stmt->bind_param("ii",  $childId, $bookingId);
                    $stmt->execute();

                    $updateChildStatusQuery = "UPDATE child SET HAS_ACTIVITY = 1 WHERE CHILD_ID = ?";
                    $stmt = $conn->prepare($updateChildStatusQuery);
                    $stmt->bind_param("i", $childId);
                    $stmt->execute();
                }

                // Now, deduct the booked slots from the session
                $sessionQuery = "SELECT OT_SLOTSLEFT FROM offered_time WHERE OT_ID = ?";
                $stmt = $conn->prepare($sessionQuery);
                $stmt->bind_param("s", $bookSesh);
                $stmt->execute();
                $sessionResult = $stmt->get_result();
                
                if ($sessionResult->num_rows > 0) {
                    $session = $sessionResult->fetch_assoc();
                    $slotsLeft = $session['OT_SLOTSLEFT'];

                    // Subtract the booked slots from the remaining slots
                    $newSlotsLeft = $slotsLeft - $bookingPax;

                    // Update the session with the new slots left
                    $updateSessionQuery = "UPDATE offered_time SET OT_SLOTSLEFT = ? WHERE OT_ID = ?";
                    $stmt = $conn->prepare($updateSessionQuery);
                    $stmt->bind_param("ds", $newSlotsLeft, $bookSesh);
                    $stmt->execute();
                }
        
                // Add to coach's account
                $coachQuery = "SELECT COACH_USERNAME FROM offered_activity WHERE OFFER_ID = ?";
                $stmt = $conn->prepare($coachQuery);
                $stmt->bind_param("s", $bookOfferId);
                $stmt->execute();
                $coachResult = $stmt->get_result();
                $coach = $coachResult->fetch_assoc();
        
                $updateCoachQuery = "UPDATE coach_wallet SET COACH_AMOUNT = COACH_AMOUNT + ? WHERE COACH_USERNAME = ?";
                $stmt = $conn->prepare($updateCoachQuery);
                $stmt->bind_param("ds", $bookTotalPrice, $coach['COACH_USERNAME']);
                $stmt->execute();

                $insertCoachTransQuery = "INSERT INTO booking_payment (PAYMENT_AMOUNT,PAYMENT_TYPE, BOOKING_ID, COACHWALLET_ID) 
                VALUES (?,'PAYMENT',?, (SELECT COACHWALLET_ID FROM coach_wallet WHERE COACH_USERNAME = ?))";
                $stmt = $conn->prepare($insertCoachTransQuery);
                $stmt->bind_param("dis", $bookTotalPrice, $bookingId, $coach['COACH_USERNAME']);
                $stmt->execute();
        
                $message = "Successfully booked! Payment has been deducted from your wallet.";
                echo "<script>alert('$message'); window.location.href = 'parentProfile.php';</script>";

            } else {
                $message = "Error updating balance. Please try again.";
                echo "<script>alert('$message'); window.location.href = 'index.php';</script>";
            }
        } else {
            // Insufficient balance
            $message = "Insufficient balance. Please reload your wallet.";
            echo "<script>alert('$message'); window.location.href = 'parentWallet.php';</script>";
        }
    } 
?>

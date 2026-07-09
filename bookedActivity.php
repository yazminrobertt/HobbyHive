<?php
    session_start();
    include('connect.php');

    if (!isset($_SESSION['username'])) {
        header("Location: Login.html");
        exit();
    }

    $username = $_SESSION['username'];

    // Get the booking ID from the URL parameter
    if (isset($_GET['bookingId'])) {
        $bookingId = $_GET['bookingId'];
    } else {
        echo "No booking ID provided!";
        exit();
    }

    // Prepare and execute the SQL query to get the booking details
    $query = "SELECT b.BOOKING_PAX, b.BOOKING_STARTDATE, b.BOOKING_ENDDATE, b.BOOKING_CHOSENPRICING,b.BOOKING_TOTALPRICE,
        a.OFFER_NAME, a.OFFER_LOCATION, 
        t.OT_DAY, t.OT_STARTTIME, t.OT_ENDTIME
        FROM booking b
        JOIN offered_activity a ON b.OFFER_ID = a.OFFER_ID
        JOIN offered_time t ON b.OT_ID = t.OT_ID 
        WHERE b.BOOKING_ID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        echo "Booking not found!";
        exit();
    }

    $childQuery = "SELECT c.CHILD_NAME, c.CHILD_AGE, c.CHILD_GENDER
    FROM selected_child sc
    JOIN child c ON sc.CHILD_ID = c.CHILD_ID
    WHERE sc.BOOKING_ID = ?";

    $childStmt = $conn->prepare($childQuery);
    $childStmt->bind_param("i", $bookingId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    // Check if there are child participants
    $children = [];
    while ($child = $childResult->fetch_assoc()) {
        $children[] = $child;
    }

    $timezone = new DateTimeZone('Asia/Kuala_Lumpur');  
    $today = new DateTime("now", $timezone);
    $todayFormatted = $today->format("Y-m-d");

    $bookingStartDate = new DateTime($booking['BOOKING_STARTDATE'], $timezone);
    $bookingStartDateFormatted = $bookingStartDate->format("Y-m-d");

    $bookingEndDate = new DateTime($booking['BOOKING_ENDDATE'], $timezone);
    $bookingEndDateFormatted = $bookingEndDate->format("Y-m-d");

    // Get the chosen pricing for the booking
    $chosenPricing = $booking['BOOKING_CHOSENPRICING'];

    // Determine if the cancel button should be disabled
    if ($chosenPricing === "TRIAL CLASS" || $chosenPricing === "ONE MONTH PACKAGE") {
        $isCancelDisabled = ($todayFormatted >= $bookingStartDateFormatted);
    } elseif ($chosenPricing === "ONE YEAR PACKAGE") {
        $isCancelDisabled = false;
    } else {
        $isCancelDisabled = true;
    }

    if ($todayFormatted === $bookingEndDateFormatted) {
        $isCancelDisabled = true;
    }

    $isDoneDisabled = ($todayFormatted < $bookingEndDateFormatted);

    // Check if the cancel button was clicked
    if (isset($_POST['cancelBooking']) && !$isCancelDisabled) {
        // Begin transaction to ensure all updates happen together
        $conn->begin_transaction();

        try {
            // Get the parent_wallet_id based on the session's username
            $username = $_SESSION['username'];
            $getParentWalletQuery = "SELECT PARENTWALLET_ID FROM parent_wallet WHERE PARENT_USERNAME = ?";
            $getParentWalletStmt = $conn->prepare($getParentWalletQuery);
            $getParentWalletStmt->bind_param("s", $username);
            $getParentWalletStmt->execute();
            $parentWalletResult = $getParentWalletStmt->get_result();

            if ($parentWalletResult->num_rows > 0) {
                // Fetch the parent_wallet_id
                $parentWallet = $parentWalletResult->fetch_assoc();
                $parentWalletId = $parentWallet['PARENTWALLET_ID'];
            } else {
                throw new Exception("Parent wallet not found.");
            }

            $getCoachQuery = "SELECT a.COACH_USERNAME FROM booking b JOIN offered_activity a ON b.OFFER_ID = a.OFFER_ID
            WHERE b.BOOKING_ID = ?";
            $getCoachStmt = $conn->prepare($getCoachQuery);
            $getCoachStmt->bind_param("i", $bookingId);
            $getCoachStmt->execute();
            $coachResult = $getCoachStmt->get_result();

            if ($coachResult->num_rows > 0) {
                // Fetch the coach_username
                $coach = $coachResult->fetch_assoc();
                $coachUsername = $coach['COACH_USERNAME'];
            } else {
                throw new Exception("Coach not found.");
            }

            // Get the coach_wallet_id using the coach_username
            $getCoachWalletQuery = "SELECT COACHWALLET_ID FROM coach_wallet WHERE COACH_USERNAME = ?";
            $getCoachWalletStmt = $conn->prepare($getCoachWalletQuery);
            $getCoachWalletStmt->bind_param("s", $coachUsername);
            $getCoachWalletStmt->execute();
            $coachWalletResult = $getCoachWalletStmt->get_result();

            if ($coachWalletResult->num_rows > 0) {
                // Fetch the coach_wallet_id
                $coachWallet = $coachWalletResult->fetch_assoc();
                $coachWalletId = $coachWallet['COACHWALLET_ID'];
            } else {
                throw new Exception("Coach wallet not found.");
            }

            $selectQuery = "SELECT OT_ID, BOOKING_PAX, BOOKING_TOTALPRICE,BOOKING_CHOSENPRICING,BOOKING_STARTDATE FROM booking WHERE BOOKING_ID = ?";
            $stmt = $conn->prepare($selectQuery);
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $booking = $result->fetch_assoc();
            } else {
                throw new Exception("Booking details not found for the given ID.");
            }

            $otId = $booking['OT_ID'];
            $bookingPax = $booking['BOOKING_PAX'];

            //ONE YEAR BOOKING CLAC
            $chosenPricing = $booking['BOOKING_CHOSENPRICING'];
            $bookingStartDate = new DateTime($booking['BOOKING_STARTDATE'], $timezone);
            $daysSinceStart = $bookingStartDate->diff(new DateTime($todayFormatted))->days;
            $refundPercentage = 0;
            // $testDate='2024-10-29';
            // $chosenPricing = $booking['BOOKING_CHOSENPRICING'];
            // $bookingStartDate = new DateTime($booking['BOOKING_STARTDATE'], $timezone);
            // $daysSinceStart = $bookingStartDate->diff(new DateTime($testDate))->days;
            // $refundPercentage = 0;
    
            // Determine refund percentage based on chosen pricing
            if ($chosenPricing === "ONE YEAR PACKAGE") {
                if ($todayFormatted >= $bookingStartDateFormatted) {
                    if ($daysSinceStart <= 91) {
                        $refundPercentage = 75;
                    } elseif ($daysSinceStart <= 182) {
                        $refundPercentage = 50;
                    } elseif ($daysSinceStart <= 273) {
                        $refundPercentage = 25;
                    } else {
                        $refundPercentage = 0; // No refund after 273 days
                    }
                } else {
                    $refundPercentage = 100; // Full refund before start
                }
            } elseif ($chosenPricing === "TRIAL CLASS" || $chosenPricing === "ONE MONTH PACKAGE") {
                if ($todayFormatted < $bookingStartDateFormatted) {
                    $refundPercentage = 100; // Full refund only before start
                } else {
                    throw new Exception("Cannot cancel after the activity has started.");
                }
            }
            
            $refundAmount = ($booking['BOOKING_TOTALPRICE'] * $refundPercentage) / 100;

            // echo "<script>
            // console.log('Chosen Pricing: " . $chosenPricing . "');
            // console.log('Booking Start Date: " . $bookingStartDateFormatted . "');
            // console.log('Test Date: " . $testDate . "');
            // console.log('Today Formatted: " . $todayFormatted . "');
            // console.log('Days Since Start: " . $daysSinceStart . "');
            // console.log('Refund Percentage: " . $refundPercentage . "%');
            // console.log('Refund Amount: " . $refundAmount . "');
            // </script>";
            // exit();

            $updateSlotsQuery = "UPDATE offered_time SET OT_SLOTSLEFT = OT_SLOTSLEFT + ? WHERE OT_ID = ?";
            $slotsStmt = $conn->prepare($updateSlotsQuery);
            $slotsStmt->bind_param("ii", $bookingPax, $otId);

            if (!$slotsStmt->execute()) {
                throw new Exception("Failed to update OT_SLOTSLEFT: " . $slotsStmt->error);
            }

            // Update the booking table to set is_canceled to 1
            $updateBookingQuery = "UPDATE booking SET IS_CANCELED = 1 WHERE BOOKING_ID = ?";
            $updateBookingStmt = $conn->prepare($updateBookingQuery);
            $updateBookingStmt->bind_param("i", $bookingId);
            $updateBookingStmt->execute();

            // Update the parent_wallet table by adding the booking_totalprice to parent_amount
            $updateParentWalletQuery = "UPDATE parent_wallet SET PARENT_AMOUNT = PARENT_AMOUNT + ? WHERE PARENTWALLET_ID = ?";
            $updateParentWalletStmt = $conn->prepare($updateParentWalletQuery);
            $updateParentWalletStmt->bind_param("di", $refundAmount, $parentWalletId);
            $updateParentWalletStmt->execute();

            // Insert into the parents_trans table with trans_type 'REFUND' and trans_amount as booking_totalprice
            $insertTransQuery = "INSERT INTO parent_trans (PARENTWALLET_ID, TRANS_TYPE, TRANS_AMOUNT) VALUES (?, 'REFUND', ?)";
            $insertTransStmt = $conn->prepare($insertTransQuery);
            $insertTransStmt->bind_param("id", $parentWalletId, $refundAmount);
            $insertTransStmt->execute();

            $transId = $conn->insert_id;

            // Insert into payment_record
            $insertPaymentRecordQuery = "INSERT INTO payment_record (TRANS_ID, BOOKING_ID) VALUES (?, ?)";
            $stmt = $conn->prepare($insertPaymentRecordQuery);
            $stmt->bind_param("ii", $transId, $bookingId);
            $stmt->execute();

            // Subtract booking_totalprice from the coach_wallet table
            $updateCoachWalletQuery = "UPDATE coach_wallet SET COACH_AMOUNT = COACH_AMOUNT - ? WHERE COACHWALLET_ID = ?";
            $updateCoachWalletStmt = $conn->prepare($updateCoachWalletQuery);
            $updateCoachWalletStmt->bind_param("di", $refundAmount, $coachWalletId);
            $updateCoachWalletStmt->execute();

            // Insert into the booking_payment table with payment_type 'REFUND'
            $insertPaymentQuery = "INSERT INTO booking_payment (BOOKING_ID, COACHWALLET_ID, PAYMENT_AMOUNT, PAYMENT_TYPE) VALUES (?, ?, ?, 'REFUND')";
            $insertPaymentStmt = $conn->prepare($insertPaymentQuery);
            $insertPaymentStmt->bind_param("iid", $bookingId, $coachWalletId, $refundAmount);
            $insertPaymentStmt->execute();

            // Commit the transaction
            $conn->commit();

            // Optionally, provide feedback to the user
            $message = "Booking has been successfully canceled and refunded.";
            echo "<script>alert('$message'); window.location.href = 'parentProfile.php';</script>";
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }

    // Handle the "Done" button logic
    if (isset($_POST['doneBooking'])&& !$isDoneDisabled) {
        try {
            $selectQuery = "SELECT OT_ID, BOOKING_PAX, BOOKING_TOTALPRICE FROM booking WHERE BOOKING_ID = ?";
            $stmt = $conn->prepare($selectQuery);
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $booking = $result->fetch_assoc();
            } else {
                throw new Exception("Booking details not found for the given ID.");
            }

            $otId = $booking['OT_ID'];
            $bookingPax = $booking['BOOKING_PAX'];


            $updateSlotsQuery = "UPDATE offered_time SET OT_SLOTSLEFT = OT_SLOTSLEFT + ? WHERE OT_ID = ?";
            $slotsStmt = $conn->prepare($updateSlotsQuery);
            $slotsStmt->bind_param("ii", $bookingPax, $otId);

            if (!$slotsStmt->execute()) {
                throw new Exception("Failed to update OT_SLOTSLEFT: " . $slotsStmt->error);
            }

            // Prepare the SQL query
            $query = "UPDATE booking SET IS_DONE = 1 WHERE BOOKING_ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $bookingId);
            // Execute the query
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $stmt->error);
            }
    
            $message = "Booking has been marked as done successfully.";
            echo "<script>alert('$message'); window.location.href = 'parentProfile.php';</script>";
        } catch (Exception $e) {
            // Catch and display any errors
            echo "An error occurred: " . $e->getMessage();
        } finally {
            // Ensure the statement is closed if it was successfully created
            if (isset($stmt) && $stmt !== false) {
                $stmt->close();
            }
        }
    }
    
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include('header.php'); ?>
        <title>Booked Activity</title>
    </head>

    <style>
        /* body {
            padding-top: 70px;
            padding-bottom: 20px; 
        } */
        .container {
            width: 1000px;
            padding: 20px;
            border: 2px solid #82b1ff;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        table.forBook{
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.forBook th, table.forBook td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table.forBook th {
            background-color:rgb(177, 196, 245);
            border: 0.5px solid  #004085;
            font-weight: bold;
        }
        table.forBook td {
            background-color: #fafafa;
        }
        table.forParti {
            border: 0.5px solid  #004085;
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.forParti th, table.forParti td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table.forParti th {
            background-color:rgb(177, 196, 245);
            font-weight: bold;
        }
        table.forParti td {
            background-color: #fafafa;
        }
        table.forBtn {
            width: 100%;
            text-align: center; /* Centers table content */
            margin: 20px auto; /* Centers table itself */
            border-collapse: collapse;
        }
        table.forBtn td {
            text-align: center; /* Centers each cell content */
            padding: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 0;
            width: 200px; /* Same width for both buttons */
            background: linear-gradient(135deg, #448aff, #005ecb);
            transition: all 0.3s ease;
            display: inline-block; /* Ensures buttons stay inline */
        }
        .btn:hover {
            background: linear-gradient(135deg, #3b7dd6, #004ba0); 
            transform: scale(1.05);
        }
        .btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }
        .note {
            font-size: 10px; /* Slightly larger font size for notes */
            color: #888;
            margin-top: 5px;
        }
        .note2{
            padding:10px 10px;
            border-radius:15px;
            margin:auto;
            width:900px;
            background-color:   #e3f2fd;
            font-size: 10px; /* Slightly larger font size for notes */
            color: #888;
            margin-top: 5px;
        }
        body .parentSect{
            margin-top: 5px;
            max-width: 1130px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .parentHeader{
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .parentHeader h4 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            padding-top: 90px;
            /* padding-top: 60px; Adjust based on the height of your navbar */
        }
        .main {
            flex: 1; /* Ensures the main content area takes available space */
        }
        .classfooter {
            color: #003366; 
            background-color: #A1C3F6;
            border-color: #A1C3F6; 
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            margin-top: auto; /* Pushes the footer to the bottom */

        }
        .classfooter .footer-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #A1C3F6;
            border-color: #A1C3F6; 
            border-top: 1px solid #ddd;
            color: #003366; 
        }
        .classfooter .footerLeft {
            display: flex;
            gap: 15px;
        }
        .classfooter .footerBtn {
            background: none;
            border: none;
            font-size: 12px;
            color: #003366; 
            cursor: pointer;
            transition: color 0.3s;
        }
        .classfooter .footerBtn:hover {
            color:rgb(6, 42, 79); 
        }
        .classfooter .footerRight {
            display: flex;
            gap: 10px;
        }
        .classfooter .socialIcon img {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }
    </style>

    <body>
        <div class="main">
            <div class="container">
                <div class="parentSect">
                    <div class="parentHeader">
                        <h3><b>Booking Details</b></h3>
                    </div>
                </div>
                <table class="forBook">
                    <tr>
                        <th>Activity Name</th>
                        <td><?php echo htmlspecialchars($booking['OFFER_NAME']); ?></td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td><?php echo htmlspecialchars($booking['OFFER_LOCATION']); ?></td>
                    </tr>
                    <tr>
                        <th>Session Day</th>
                        <td><?php echo htmlspecialchars($booking['OT_DAY']); ?></td>
                    </tr>
                    <tr>
                        <th>Session Time</th>
                        <td><?php echo htmlspecialchars($booking['OT_STARTTIME']). ' - ' . htmlspecialchars($booking['OT_ENDTIME']); ?></td>
                    </tr>
                    <tr>
                        <th>Chosen Pricing</th>
                        <td><?php echo htmlspecialchars($booking['BOOKING_CHOSENPRICING']); ?></td>
                    </tr>
                    <tr>
                        <th>Booking Pax</th>
                        <td><?php echo htmlspecialchars($booking['BOOKING_PAX']); ?></td>
                    </tr>
                    <tr>
                        <th>Total Price</th>
                        <td><?php echo htmlspecialchars($booking['BOOKING_TOTALPRICE']); ?></td>
                    </tr>
                    <tr>
                        <th>Booking Start Date</th>
                        <td><?php echo htmlspecialchars($booking['BOOKING_STARTDATE']); ?></td>
                    </tr>
                    <tr>
                        <th>Booking End Date</th>
                        <td><?php echo htmlspecialchars($booking['BOOKING_ENDDATE']); ?></td>
                    </tr>
                </table>

                <div class="parentSect">
                    <div class="parentHeader">
                        <h3><b>Participant Details</b></h3>
                    </div>
                </div>
                <?php if (count($children) > 0): ?>
                    <table class="forParti">
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($children as $child): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($child['CHILD_NAME']); ?></td>
                                    <td><?php echo htmlspecialchars($child['CHILD_AGE']); ?></td>
                                    <td><?php echo htmlspecialchars($child['CHILD_GENDER']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No participants found for this booking.</p>
                <?php endif; ?>

                <form method="POST">
                    <table class="forBtn">
                        <tr>
                            <td>
                            <button type="submit" name="cancelBooking" class="btn" id="btnCancel" 
                                <?php echo $isCancelDisabled ? 'disabled' : ''; ?>
                                onclick="return confirmCancel();">
                                Cancel
                            </button>
                                <div class="note">
                                    <?php echo $isCancelDisabled ? "Sorry, can't cancel since you've started the activity." : ''; ?>
                                </div>
                            </td>
                            <td>
                                <button type ="submit" name="doneBooking" class="btn" id="btnDone" 
                                    <?php echo $isDoneDisabled ? 'disabled' : ''; ?>
                                    onclick="return confirmDone();">
                                    Done
                                </button>
                                <div class="note">
                                    <?php echo $isDoneDisabled ? "Button will be open once you've completed the activity." : ''; ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
                <div class="note2">
                <p><strong>
                    <?php
                    $interval = $bookingStartDate->diff(new DateTime($todayFormatted));
                    $daysSinceStart = $interval->days; // Absolute difference
                    $isFuture = $interval->invert; // 1 if in the future, 0 if in the past

                    if (!$isFuture) { // Display only if the activity has started
                        echo "Days since activity started: " . number_format($daysSinceStart);
                    }
                    ?>
                </strong></p>
                    <p><strong>Note:</strong></p>
                    <ul>
                        <li>Trial and 1-month packages can be canceled only before the activity starts, while 1-year packages can be canceled at any time.</li>
                        <li>Trial classes and 1-month packages are fully refundable.</li>
                        <li>One-year packages are 100% refundable before the activity starts, 75% refundable within 91 days, 50% within 182 days, and 25% within 273 days. No refunds are provided after 273 days.</li>
                    </ul>
                </div>
            </div>
        </div>


        <div class="classfooter">
            <footer class="footer-main">
                <div class="footerLeft">
                    <button class="footerBtn" onclick="window.location.href='forCoach.php';">For Coaches</button>
                    <button class="footerBtn" onclick="window.location.href='faq.php';">Support FAQ</button>
                </div>
                <div class="footerRight">
                    <div class="socialIcon">
                        <img src="Facebook.jpg" alt="Facebook">
                    </div>
                    <div class="socialIcon">
                        <img src="Instagram.jpg" alt="Instagram">
                    </div>
                    <div class="socialIcon">
                        <img src="Twitter.jpg" alt="Twitter">
                    </div>
                </div>
            </footer>
        </div>
    </body>

    <script>
        function confirmCancel() {
            return confirm("Are you sure you want to cancel this booking?");
        }
        function confirmDone() {
            return confirm("Are you sure you want to mark this booking as done?");
        }
    </script>
</html>

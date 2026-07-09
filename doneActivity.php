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
        a.OFFER_NAME, a.OFFER_LOCATION, a.OFFER_ID, t.OT_DAY, t.OT_STARTTIME, t.OT_ENDTIME
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

    $offerId = $booking['OFFER_ID'];

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

    // Check if a review already exists for this booking and offer
    $reviewQuery = "SELECT REVIEW_RATE, REVIEW_WRITE FROM review WHERE BOOKING_ID = ? AND OFFER_ID = ?";
    $reviewStmt = $conn->prepare($reviewQuery);
    $reviewStmt->bind_param("ii", $bookingId, $offerId);
    $reviewStmt->execute();
    $reviewResult = $reviewStmt->get_result();

    $existingReview = null;
    if ($reviewResult->num_rows > 0) {
        $existingReview = $reviewResult->fetch_assoc(); // Review found
    }

    if (isset($_POST['doneRate'])) {
        try {
            // Get POST data
            $reviewRate = isset($_POST['rating']) ? intval($_POST['rating']) : null;
            $reviewWrite = isset($_POST['review']) ? trim($_POST['review']) : null;
            $timezone = new DateTimeZone('Asia/Kuala_Lumpur');  
            $today = new DateTime("now", $timezone);
            $reviewDate = $today->format("Y-m-d");
            // $reviewDate = date('Y-m-d'); // Current date

            // Validate input
            if (!$reviewRate || !$reviewWrite) {
                throw new Exception("Rating and review text are required.");
            }

            // Insert into the review table
            $reviewQuery = "INSERT INTO review (REVIEW_RATE, REVIEW_WRITE, REVIEW_DATE, BOOKING_ID, OFFER_ID) 
            VALUES (?, ?, ?, ?, ?)";
            $reviewStmt = $conn->prepare($reviewQuery);
            $reviewStmt->bind_param("issii", $reviewRate, $reviewWrite, $reviewDate, $bookingId, $offerId);
            $reviewStmt->execute();

            if ($reviewStmt->affected_rows > 0) {
                echo "Review successfully saved!";
                // Reload the page to show the saved review
                header("Location: " . $_SERVER['PHP_SELF'] . "?bookingId=" . $bookingId);
                exit();
            } else {
                throw new Exception("Failed to save the review.");
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
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
            margin-bottom: 20px;
            border: 2px solid #82b1ff;
            border-radius: 8px;
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
        .review-display {
            display: flex;
            flex-direction: column;
            gap: 10px; /* Space between rating and review text */
            width: calc(100% - 60px);
            padding: 20px;
            margin: 10px auto;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb); /* Blue gradient theme */
            border-radius: 10px;
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease-in-out;
        }
        .review-display:hover {
            transform: translateY(-3px); /* Lift on hover */
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.25);
        }
        .review-rating {
            width:100px;
            background-color: #82b1ff; /* Light blue badge */
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block; /* Ensures badge-like appearance */
        }
        .review-text {
            font-size: 14px;
            color: #2c3e50; /* Darker blue for readability */
            line-height: 1.5;
        }
        .review-form {
            width: calc(100% - 60px);
            padding: 20px;
            margin: 20px auto;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb); /* Same blue gradient theme */
            border-radius: 10px;
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease-in-out;
        }
        .review-form:hover {
            transform: translateY(-3px); /* Lift on hover */
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.25);
        }
        .review-form h3 {
            font-size: 16px;
            color: #004085; /* Darker blue for headings */
            font-weight: bold;
            margin-bottom: 15px;
        }
        .review-form .rating {
            display: flex;
            gap: 10px; /* Space between radio buttons */
            margin-bottom: 15px;
        }
        .review-form .rating label {
            font-size: 14px;
            color: #2c3e50; /* Darker blue for text */
            cursor: pointer;
        }
        .review-form .reviewWrite {
            width: 100%;
            height: 80px;
            padding: 10px;
            font-size: 14px;
            color: #2c3e50;
            border: 1px solid #82b1ff; /* Light blue border */
            border-radius: 5px;
            resize: none; /* Prevent resizing */
            margin-bottom: 15px;
            background-color: #e3f2fd; /* Subtle blue background */
        }
        .review-form .rateBtn {
            background-color: #82b1ff; /* Light blue button */
            color: white;
            font-size: 14px;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .review-form .rateBtn:hover {
            background-color: #64b5f6; /* Darker blue on hover */
            transform: translateY(-2px); /* Lift on hover */
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

            <div class="parentSect">
                <div class="parentHeader">
                    <h3><b>Your Experience</b></h3>
                </div>
            </div>
        
            <div class="review-section">
                <!-- Check if review exists -->
                <?php if ($existingReview): ?>
                    <div class="review-display">
                        <div class="review-rating">
                            <strong>Rating:</strong> <?php echo $existingReview['REVIEW_RATE']; ?> / 5
                        </div>
                        <div class="review-text">
                            <strong>Review:</strong> <?php echo htmlspecialchars($existingReview['REVIEW_WRITE']); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Review Form -->
                    <div class="review-form">
                        <h3>Leave Your Review</h3>
                        <form method="POST" onsubmit="return saveReview();">
                            <div class="rating">
                                <label><input type="radio" name="rating" value="1"> 1</label>
                                <label><input type="radio" name="rating" value="2"> 2</label>
                                <label><input type="radio" name="rating" value="3"> 3</label>
                                <label><input type="radio" name="rating" value="4"> 4</label>
                                <label><input type="radio" name="rating" value="5"> 5</label>
                            </div>
                            <textarea name="review" class="reviewWrite" placeholder="Write your review here..."></textarea>
                            <button type="submit" name="doneRate" class="rateBtn">Save</button>
                        </form>
                    </div>
                <?php endif; ?>
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
        function saveReview() {
            const rating = document.querySelector('input[name="rating"]:checked');
            const reviewText = document.querySelector('.reviewWrite').value.trim(); // Get review text
            if (!rating || reviewText === '') {
                alert('Please provide both a rating and a review text!');
                return false; 
            }
            return true; 
        }
    </script>
</html>

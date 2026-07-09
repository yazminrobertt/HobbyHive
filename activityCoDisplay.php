<?php
    session_start();
    include("connect.php");
    
    // Get the activity ID from the URL
    $activityId = isset($_GET['activityId']) ? $_GET['activityId'] : '';

    if (!empty($_SESSION['username'])) {
        $isLoggedIn = true;
    } else {
        $isLoggedIn = false;
    }
    
    if ($activityId) {
        // Fetch activity details
        $query = "SELECT oa.OFFER_NAME, oa.OFFER_STATE, oa.OFFER_LOCATION, oa.OFFER_MINAGE, oa.OFFER_MAXAGE, oa.OFFER_DESC, ac.CATEGORY_NAME, oa.COACH_USERNAME
        FROM offered_activity oa
        JOIN activity_category ac ON oa.CATEGORY_ID = ac.CATEGORY_ID
        WHERE oa.OFFER_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $activityId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $activity = $result->fetch_assoc();
        } else {
            echo "Activity not found.";
            exit;
        }
    
        // Fetch package details
        $packageQuery = "SELECT PRICING_TYPE, PRICE FROM offered_pricing WHERE OFFER_ID = ?";
        $stmt = $conn->prepare($packageQuery);
        $stmt->bind_param("s", $activityId);
        $stmt->execute();
        $packageResult = $stmt->get_result();
    
        $packages = [];
        while ($row = $packageResult->fetch_assoc()) {
            $packages[] = $row;
        }
    
        // Fetch session details
        $sessionQuery = "SELECT OT_DAY, OT_STARTTIME, OT_ENDTIME, OT_TYPE, OT_PAX, OT_SLOTSLEFT 
        FROM offered_time WHERE OFFER_ID = ? AND IS_REMOVED=0";
        $stmt = $conn->prepare($sessionQuery);
        $stmt->bind_param("s", $activityId);
        $stmt->execute();
        $sessionResult = $stmt->get_result();
    
        $sessions = [];
        while ($row = $sessionResult->fetch_assoc()) {
            $sessions[] = $row;
        }

        $sessionQuery = "SELECT OT_DAY FROM offered_time WHERE OFFER_ID = ?";
        $stmt = $conn->prepare($sessionQuery);
        $stmt->bind_param("s", $activityId);
        $stmt->execute();
        $sessionResult = $stmt->get_result();

        $sessionDays = [];
        while ($row = $sessionResult->fetch_assoc()) {
            $sessionDays[] = $row['OT_DAY'];
        }

        // If no session days are found
        if (empty($sessionDays)) {
            echo "No available sessions for this activity.";
            exit;
        }

        $availableDays = json_encode($sessionDays);
        $sessionData = json_encode($sessions); // Pass all session details to JavaScript
        
    } else {
        echo "No activity selected.";
        exit;
    }

    if ($activityId > 0) {
        // Query to fetch reviews for the given offer ID, ordered by the latest date
        $query = "SELECT REVIEW_DATE, REVIEW_WRITE, REVIEW_RATE 
        FROM review 
        WHERE OFFER_ID = ? 
        ORDER BY REVIEW_DATE DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $activityId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Fetch all reviews
        $reviews = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $reviews = [];
    }

    $querySessionDetails = "
        SELECT 
            OT_ID, OT_STARTTIME, OT_ENDTIME, OT_PAX, OT_SLOTSLEFT, OT_TYPE,OT_DAY
        FROM 
            offered_time
        WHERE 
            IS_REMOVED = 0 AND OFFER_ID = ?";
    $stmtSessionDetails = $conn->prepare($querySessionDetails);
    $stmtSessionDetails->bind_param("i", $activityId);
    $stmtSessionDetails->execute();
    $resultSessionDetails = $stmtSessionDetails->get_result();

    $sessionDetailsList = [];
    while ($rowSession = $resultSessionDetails->fetch_assoc()) {
        $offeredTimeId = $rowSession['OT_ID'];
        
        // Step 2: Get bookings for the session where IS_DONE = 0 and IS_CANCELED = 0
        $querySessionBookings = "
            SELECT 
                b.BOOKING_ID,
                b.PARENT_USERNAME,
                b.BOOKING_STARTDATE,
                b.BOOKING_ENDDATE,
                p.PARENT_NAME
            FROM 
                booking b
            JOIN 
                parent p
            ON 
                b.PARENT_USERNAME = p.PARENT_USERNAME
            WHERE 
                b.OT_ID = ? 
                AND b.IS_DONE = 0 
                AND b.IS_CANCELED = 0;
        ";
        $stmtSessionBookings = $conn->prepare($querySessionBookings);
        $stmtSessionBookings->bind_param("i", $offeredTimeId);
        $stmtSessionBookings->execute();
        $resultSessionBookings = $stmtSessionBookings->get_result();

        $sessionParticipants = [];
        while ($rowBooking = $resultSessionBookings->fetch_assoc()) {
            $bookingId = $rowBooking['BOOKING_ID'];
            $bookingStartDate= $rowBooking['BOOKING_STARTDATE'];
            $bookingEndDate= $rowBooking['BOOKING_ENDDATE'];
            $parentUsername = $rowBooking['PARENT_USERNAME'];
            $parentName = $rowBooking['PARENT_NAME'];
            
            $querySessionChild = "
                SELECT 
                    CHILD_ID
                FROM 
                    selected_child
                WHERE 
                    BOOKING_ID = ?";
            $stmtSessionChild = $conn->prepare($querySessionChild);
            $stmtSessionChild->bind_param("i", $bookingId);
            $stmtSessionChild->execute();
            $resultSessionChild = $stmtSessionChild->get_result();

            while ($rowChild = $resultSessionChild->fetch_assoc()) {
                $childId = $rowChild['CHILD_ID'];

                // Step 4: Get child name from child table using CHILD_ID
                $querySessionChildName = "
                    SELECT 
                        CHILD_NAME
                    FROM 
                        child
                    WHERE 
                        CHILD_ID = ?";
                $stmtSessionChildName = $conn->prepare($querySessionChildName);
                $stmtSessionChildName->bind_param("i", $childId);
                $stmtSessionChildName->execute();
                $resultSessionChildName = $stmtSessionChildName->get_result();

                while ($rowChildName = $resultSessionChildName->fetch_assoc()) {
                    $childName = $rowChildName['CHILD_NAME'];

                    // Store each participant's details
                    $sessionParticipants[] = [
                        'booking_id' => $bookingId,
                        'booking_startdate' => $bookingStartDate,
                        'booking_enddate' => $bookingEndDate,
                        'parent_username' => $parentUsername,
                        'parent_name' => $parentName,
                        'child_name' => $childName,
                    ];
                }
            }
        }

        // Store session details and its participants
        $sessionDetailsList[] = [
            'ot_id' => $offeredTimeId,
            'start_time' => $rowSession['OT_STARTTIME'],
            'session_day' => $rowSession['OT_DAY'],
            'end_time' => $rowSession['OT_ENDTIME'],
            'pax' => $rowSession['OT_PAX'],
            'slots_left' => $rowSession['OT_SLOTSLEFT'],
            'type' => $rowSession['OT_TYPE'],
            'participants' => $sessionParticipants,
        ];
    }

    $confirmDelete = isset($_GET['confirmDelete']) && $_GET['confirmDelete'] == 1;
    if ($activityId && $confirmDelete) {
        // Check if the activity has valid bookings
        $query = "SELECT COUNT(*) AS booking_count 
        FROM booking 
        WHERE OFFER_ID = $activityId 
        AND (IS_DONE = 0 AND IS_CANCELED = 0)";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
    
        if ($row['booking_count'] > 0) {
            // Activity has active bookings
            echo "<script>alert('Cannot delete this activity because it has active bookings.');</script>";
        } else {
            // Check if the user has confirmed the delete action
            if (isset($_GET['confirmDelete']) && $_GET['confirmDelete'] == 1) {
                // Set `is_available` to 0
                $updateQuery = "UPDATE offered_activity SET IS_AVAILABLE = 0 WHERE OFFER_ID = $activityId";
                if ($conn->query($updateQuery)) {
                    echo "<script>alert('Activity successfully marked as unavailable.'); window.location.href = 'coachProfile.php'</script>";
                } else {
                    echo "<script>alert('Failed to update the activity. Please try again.');</script>";
                }
            } else {
                echo "<script>
                    if (confirm('Are you sure you want to delete this activity?')) {
                        window.location.href = '?activityId=$activityId&confirmDelete=1';
                    }
                </script>";
            }
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <?php include('header2.php'); ?>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <title>Offered Activity</title>
    </head>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            padding-top: 50px;
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
            margin-top: 20px; /* Pushes the footer to the bottom */
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
        .container {
            max-width: 1000px;
            /* margin: 50px auto; */
            margin-left:auto;
            margin-right: auto;
            margin-top: 0px;
            margin-bottom: 10px;
            padding: 20px;
            border: 2px solid #448aff;  /* Adds a solid black border with a thickness of 2px */
            border-radius: 8px;
        }
        .container2 {
            display: block; 
            margin-left:auto;
            margin-right:auto;
            margin-top:10px ;
            padding: 20px;
            text-align: left; /* Aligns the text to the left */
        }
        .acName {
            font-size: 30px;
            font-weight: bold;
            color: #004085;
        }
        .top-section {
            display: flex;
            flex-direction: column; /* Stack vertically */
            align-items: center; /* Center the elements */
            /* gap: 20px; */
        }
        .forManDesc{
            width:850px;
            margin:auto;
            font-size: 12px;
            display: flex;
            text-align: justify;
            padding: 20px;
            display: table; /* Ensures it behaves like a table */
            text-align: center; /* Centers the content inside */
        }
        .forManAc {
            display: flex; 
            justify-content: center; 
            gap: 20px; 
            margin-bottom:10px;
        }
        .forManAc button {
            margin: 0 auto; 
            display: block;
            width: calc(100% - 100px); 
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #004085;
            border: none; 
            padding: 10px 0px; 
            font-size: 14px; 
            font-weight: bold; 
            cursor: pointer; 
            border-radius: 10px; 
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease-in-out; 
            text-align: center; 
        }
        .forManAc button:hover {
            transform: translateY(-3px); 
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.25); 
            background: linear-gradient(135deg, #bbdefb, #82b1ff);
        }
        .acDel:hover, .acEdit:hover {
            transform: translateY(-3px); 
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.25); 
            background: linear-gradient(135deg, #bbdefb, #82b1ff); 
        }
        .forDesc{
            padding: 10px; 
            border: 2px solid lightgray;
            margin: 10px 0; Optional: Adds space outside the box
        }
        .acGenDet {
            margin-top: 20px;
            margin-left: 50px;
            margin-right: 50px;
        }
        .acGenDet table {
            width: 100%;
            border-collapse: separate; /* Ensures distinct borders for table and cells */
            margin-top: 15px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1); /* Subtle shadow for sophistication */
            border-radius: 12px; /* Rounded corners for the whole table */
            overflow: hidden; /* Ensures rounded corners are respected */
            border: 1px solid #82b1ff; /* Blue border around the table for a clean look */
        }
        .acGenDet table th, .acGenDet table td {
            padding: 12px 18px; /* Adequate spacing for clarity */
            text-align: left;
            background-color: #e3f2fd; /* Light blue background for cells */
            color: #004085; /* Dark text for contrast */
            border-top: 1px solid #82b1ff; /* Border between rows */
            border-left: 1px solid #82b1ff; /* Border between columns */
            transition: background-color 0.3s ease-in-out; /* Smooth transition for hover effects */
        }
        .acGenDet table th {
            background-color: #82b1ff; /* Header background with theme matching */
            font-weight: bold;
            color: white; /* White text for header */
        }
        .acGenDet table td:first-child, .acGenDet table th:first-child {
            border-left: none; /* Remove left border for the first column */
        }
        .acGenDet table td:last-child, .acGenDet table th:last-child {
            border-right: none; /* Remove right border for the last column */
        }
        body .packSect, .seshSect, .rateSect, .bookingSect{
            margin-top: 30px; 
            max-width: 1130px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left:50px;
            margin-right:50px;
        }
        .packHeader ,.seshHeader,.rateHeader, .bookingHeader{
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .packHeader, .seshHeader, .rateHeader, .bookingHeader h4 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        .packType {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }
        .packCard {
            width: 270px;
            padding: 15px;
            text-align: center;
            background-color: #e3f2fd; /* Soft light blue background */
            border-radius: 8px; /* Soft rounded corners */
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            transition: all 0.3s ease-in-out; /* Smooth transition for hover effect */
        }
        .packCard:hover {
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2); /* Slightly stronger shadow on hover */
            transform: translateY(-5px); /* Elevation effect on hover */
        }
        .packCard h4 {
            margin: 8px 0;
            font-size: 16px;
            color: #004085; /* Dark blue to match the theme */
            font-weight: bold; /* Emphasized title */
        }
        .packCard p {
            margin: 4px 0;
            font-size: 12px;
            color: #555; /* Softer gray for description */
        }
        .packCard .price {
            margin-top: 8px;
            font-size: 20px;
            font-weight: bold;
            color: #448aff; /* Primary theme blue color for price */
        }
        .forSeh {
            width: calc(100% - 100px); /* Subtract left and right margins (50px each) */
            margin: 15px 50px; /* Add 50px margin to left and right, 15px to top and bottom */
            border-collapse: collapse;
            font-size: 12px;
            text-align: center;
            table-layout: fixed; /* Ensure all columns have the same width */
            border-radius: 12px; /* Rounded corners for the table */
            overflow: hidden; /* Ensure rounded corners are respected */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            border: 2px solid #448aff; /* Blue border around the whole table */
        }
        .forSeh td {
            border: 1px solid #448aff; /* Matching blue border for table cells */
            background-color:#82b1ff ;
            padding: 8px;
            width: auto; 
            color: white;
        }
        .forSeh th {
            background-color: #e3f2fd; 
            font-weight: bold;
            text-transform: uppercase;
            color: #004085; /* White text for header */
            text-align: center;
            padding: 8px;
            width: auto;
        }
        .forSeh tbody tr {
            border: 1px solid #448aff; /* Subtle blue border between rows */
        }
        .funcSection  {
            display: flex;
            justify-content: space-around; 
            margin-bottom: 20px;
            padding: 10px 0; 
        }
        .funcSection  button {
            border: none;
            background-color: transparent;
            cursor: pointer;
            padding: 16px 32px;
            font-size: 16px;
            color: black; 
            font-weight: bold; 
            transition: all 0.3s ease; 
        }
        .funcSection button:hover {
            border-bottom: 3px solid #448aff; 
            color: #005ecb; 
            transform: scale(1.05); 
        }
        .funcSection  button:active {
            border-bottom: 3px solid #448aff; 
            color: #448aff;
            transform: scale(0.98); 
        }
        .reviewDisplay {
            display: flex;
            flex-direction: column;
            gap: 8px; /* Reduced gap between cards */
        }
        .review-card {
            width: calc(100% - 80px); /* Reduced width */
            padding: 15px; /* Reduced padding inside cards */
            margin-top: 5px;
            margin-left: 50px; /* Reduced margins */
            margin-right: 50px;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 10px; /* Slightly smaller rounding */
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15); /* Subtle shadow */
            transition: all 0.3s ease-in-out;
        }
        .review-card:hover {
            transform: translateY(-3px); /* Reduced hover movement */
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.25); /* Subtle hover shadow */
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px; /* Reduced spacing */
            font-size: 12px; /* Smaller font size */
            font-weight: bold;
            color: #004085;
        }
        .review-rating {
            background-color: #82b1ff;
            color: white;
            padding: 3px 8px; /* Reduced padding for the badge */
            border-radius: 6px; /* Smaller rounding for badge */
            font-size: 10px; /* Smaller font size for the badge */
        }
        .review-content {
            font-size: 14px; /* Reduced font size for content */
            color: #2c3e50;
            line-height: 1.4; /* Tighter line spacing */
        }
        .no-reviews {
            text-align: center;
            font-size: 14px; /* Smaller font size for fallback message */
            color: #7a7a7a;
            margin-top: 15px; /* Reduced margin */
        }
        .acPics {
            width: 650px; 
            height: 300px; 
            background-color: #f5f5f5;
            border-radius: 10px;
            margin: 20px auto; 
            overflow: hidden; 
            position: relative; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        }
        img {
            object-fit: cover; 
            width: 100%;
            height: 100%; 
        }
        .slider-container {
            width: 100%;
            height: 100%; 
            position: relative;
            overflow: hidden;
        }
        .slider-container .slide {
            display: none;
            width: 100%;
            height: 100%;
        }
        .slider-container .slide img {
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            border-radius: 10px; 
        }
        .slider-container .active {
            display: block; 
        }
        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            color: white;
            font-size: 18px;
            background-color: rgba(0, 0, 0, 0.6);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .slider-btn.prev {
            left: 10px;
        }
        .slider-btn.next {
            right: 10px;
        }
        .slider-btn:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
        .dispSession {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
        }
        .sessionCard {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);  
            border-radius: 12px;
            padding: 20px;
            width: 900px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); 
            margin-bottom: 20px;
            font-family: 'Arial', sans-serif;
            color: #333; 
        }
        .sessionCard h3 {
            color: #004085;   /* Blue color for the title */
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 5px 0 5px 2px; 
            text-transform: uppercase;
            border-bottom: 2px solid #bbdefb;  
        }
        .sessionCard p {
            font-size: 14px;
            color: black;  
            margin: 5px 0;
        }
        .sessionCard h4 {
            color: #333;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .participantTable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #ffffff;  /* White table background to stand out */
            border: 1px solid #bbdefb;  /* Distinct border to separate table */
            border-radius: 8px;  /* Rounded corners for a polished look */
            overflow: hidden;  /* Ensures corners remain rounded */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Slight shadow to lift the table visually */
        }
        .participantTable th {
            background-color: #1e88e5;  /* Darker blue for headers */
            color: #ffffff;  /* White text for contrast */
            font-size: 14px;
            text-transform: uppercase;
            padding: 10px;
        }
        .participantTable td {
            padding: 10px;
            text-align: left;
            font-size: 14px;
            color: #555;  /* Darker gray for readability */
            border-bottom: 1px solid #bbdefb;  /* Subtle row separation */
        }
        .participantTable tr:hover {
            background-color: #f1f8e9;  /* Soft green highlight on hover */
        }
        .participantTable tr:last-child td {
            border-bottom: none;
        }
    </style>

    <script>
        $(document).ready(function(){
            // Initially show the Details section, hide others
            $("#detSect").show();
            $("#bookSect").hide();

            // Apply initial border-bottom to the Details button
            $(".detSectBtn").css("border-bottom", "2px solid rgb(0, 0, 0)");

            // Click event for the Details button
            $(".detSectBtn").click(function(){
                $("#detSect").show();
                $("#bookSect").hide();
                $(".detSectBtn").css("border-bottom", "2px solid rgb(0, 0, 0)");
                $(".bookSectBtn").css("border-bottom", "none");
            });

            // Click event for the Bookings button
            $(".bookSectBtn").click(function(){
                $("#bookSect").show();
                $("#detSect").hide();
                $(".bookSectBtn").css("border-bottom", "2px solid rgb(0, 0, 0)");
                $(".detSectBtn").css("border-bottom", "none");
            });
        });

        function redirectToEdit(activityId) {
            window.location.href = `activityEdit.php?activityId=${encodeURIComponent(activityId)}`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            let currentIndex = 0;
            const slides = document.querySelectorAll('.slider-container .slide');
            const totalSlides = slides.length;

            if (totalSlides > 0) {
                slides[currentIndex].classList.add('active');

                document.querySelector('.slider-container').insertAdjacentHTML('beforeend', `
                    <button class="slider-btn prev">&lt;</button>
                    <button class="slider-btn next">&gt;</button>
                `);

                const showSlide = (index) => {
                    slides.forEach((slide, i) => slide.classList.toggle('active', i === index));
                };

                document.querySelector('.slider-btn.prev').addEventListener('click', () => {
                    currentIndex = (currentIndex > 0) ? currentIndex - 1 : totalSlides - 1;
                    showSlide(currentIndex);
                });

                document.querySelector('.slider-btn.next').addEventListener('click', () => {
                    currentIndex = (currentIndex < totalSlides - 1) ? currentIndex + 1 : 0;
                    showSlide(currentIndex);
                });
            }
        });
    </script>

    <script>
        function confirmDelete(activityId) {
            if (confirm("Are you sure you want to delete this activity?")) {
                window.location.href = "?activityId=" + activityId + "&confirmDelete=1";
            }
        }
    </script>
    
    <body>
        <div class="container2">
            <div class="acName">
                <?php echo htmlspecialchars($activity['OFFER_NAME']); ?>
            </div>
        </div>

        <div class="container">

            <div class="funcSection" style="margin-bottom:20px;">
                <button class="detSectBtn">Details</button>
                <button class="bookSectBtn">Particpants</button>
            </div>
            
            <div id="detSect">
                <div class="top-section">
                    <div class="acPics">
                        <?php
                            if (!empty($activityId)) {
                                // Query to fetch pictures from the offered_pic table
                                $query = "SELECT OP_PIC FROM offered_pic WHERE OFFER_ID = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("i", $activityId);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                // Fetch images and display as a slider
                                if ($result->num_rows > 0) {
                                    echo '<div class="slider-container">';
                                    while ($row = $result->fetch_assoc()) {
                                        // Base64-encoded image data from DB
                                        $imageData = $row['OP_PIC'];  
                                        
                                        // Check if the image data is valid and not empty
                                        if (!empty($imageData)) {
                                            echo '<div class="slide"><img src="data:image/jpeg;base64,' . $imageData . '" alt="Activity Picture"></div>';
                                        } else {
                                            echo '<p>No image available for this activity.</p>';
                                        }
                                    }
                                    echo '</div>';
                                } else {
                                    echo '<p>No pictures found for this activity.</p>';
                                }
                                $stmt->close();
                            } else {
                                echo '<p>Invalid activity ID.</p>';
                            }
                        ?>
                    </div>
                </div>

                    <table class="forManDesc">
                        <tr>
                            <td>
                                <div class="forManAc">
                                    <button class="acEdit" onclick="redirectToEdit('<?php echo $activityId; ?>')">Edit Activity</button>
                                    <!-- <button class="acDel" onclick="window.location.href='someOtherPage.php'">Delete Activity</button> -->
                                    <button class='acDel' onclick='confirmDelete(<?php echo $activityId; ?>)'>Delete Activity</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="forDesc">
                                    <p><?php echo htmlspecialchars($activity['OFFER_DESC']); ?></p>
                                </div>
                            </td>
                        </tr>
                    </table>

                <div class="acGenDet">
                    <table>
                        <tr>
                            <th>State</th>
                            <td><?php echo htmlspecialchars($activity['OFFER_STATE']); ?></td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td><?php echo htmlspecialchars($activity['OFFER_LOCATION']); ?></td>
                        </tr>
                        <tr>
                            <th>Age Range</th>
                            <td><?php echo htmlspecialchars($activity['OFFER_MINAGE']); ?> - <?php echo htmlspecialchars($activity['OFFER_MAXAGE']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="packSect">
                    <div class="packHeader">
                        <h4><b>Package Available</b></h4>
                    </div>
                </div>

                <div class="packType">
                    <?php foreach ($packages as $package) { ?>
                        <div class="packCard">
                            <h4><?php echo htmlspecialchars($package['PRICING_TYPE']); ?></h4>
                            <p>
                                <?php 
                                if ($package['PRICING_TYPE'] === 'TRIAL CLASS') {
                                    echo '*Total of 1 class only';
                                } elseif($package['PRICING_TYPE'] === 'ONE MONTH PACKAGE'){
                                    echo '*Total of 4 classes';
                                }else {
                                    echo '*Total of 48 classes';
                                } 
                                ?>
                            </p>
                            <div class="price">RM <?php echo htmlspecialchars($package['PRICE']); ?></div>
                        </div>
                    <?php } ?>
                </div>

                <div class="seshSect">
                    <div class="seshHeader">
                        <h4><b>Sessions Available</b></h4>
                    </div>
                </div>

                <?php foreach ($sessions as $session) { ?>
                <table class="forSeh">
                    <tr>
                        <td>
                            <strong>Day:</strong>
                        </td>
                        <td>
                            <strong>Time:</strong>
                        </td>
                        <td>
                            <strong>Pax Per Session:</strong>
                        </td>
                        <td>
                            <strong>Slots Left:</strong>
                        </td>
                        <td>
                            <strong>Package Type:</strong>
                        </td>
                    </tr>
                    <tbody>
                        <th>
                            <?php echo htmlspecialchars($session['OT_DAY']); ?></div>
                        </th>
                        <th>
                            <?php echo htmlspecialchars($session['OT_STARTTIME']); ?> - <?php echo htmlspecialchars($session['OT_ENDTIME']); ?></div>
                        </th>
                        <th>
                            <?php echo htmlspecialchars($session['OT_PAX']); ?></div>
                        </th>
                        <th>
                            <?php echo htmlspecialchars($session['OT_SLOTSLEFT']); ?></div>
                        </th>
                        <th>
                            <?php echo htmlspecialchars($session['OT_TYPE']); ?></div>
                        </th>
                    </tbody>
                </table>
                <?php } ?>

                <div class="rateSect">
                    <div class="rateHeader">
                        <h4><b>Reviews & Ratings</b></h4>
                    </div>
                </div>

                <div class="reviewDisplay">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="review-date"><?= htmlspecialchars($review['REVIEW_DATE']) ?></span>
                                    <span class="review-rating">Rating: <?= htmlspecialchars($review['REVIEW_RATE']) ?>/5</span>
                                </div>
                                <div class="review-content">
                                    <?= nl2br(htmlspecialchars($review['REVIEW_WRITE'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-reviews">No reviews available for this activity.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="bookSect">
                
                <div class="bookingSect">
                    <div class="bookingHeader">
                        <h4><b>Participants</b></h4>
                    </div>
                </div>

                <div class="dispSession">
                    <?php
                    foreach ($sessionDetailsList as $sessionDetail) {
                        ?>
                        <div class="sessionCard">
                            <h3><?php echo $sessionDetail['type']; ?></h3>
                            <p><strong>Day:</strong> <?php echo $sessionDetail['session_day']; ?></p>
                            <p><strong>Time:</strong> <?php echo $sessionDetail['start_time'] . ' - ' . $sessionDetail['end_time']; ?></p>
                            <p><strong>Pax:</strong> <?php echo $sessionDetail['pax']; ?></p>
                            <p><strong>Slots Left:</strong> <?php echo $sessionDetail['slots_left']; ?></p>
                            
                            <h4>Participants:</h4>
                            <?php
                            if (!empty($sessionDetail['participants'])) {
                                ?>
                                <table class="participantTable">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Starting Date</th>
                                            <th>Parent Name</th>
                                            <th>Child Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($sessionDetail['participants'] as $participantDetail) {
                                            ?>
                                            <tr>
                                                <td><?php echo $participantDetail['booking_id']; ?></td>
                                                <td><?php echo $participantDetail['booking_startdate']; ?></td>
                                                <td><?php echo $participantDetail['parent_name']; ?></td>
                                                <td><?php echo $participantDetail['child_name']; ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?php
                            } else {
                                echo "<p>No participants found.</p>";
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="classfooter">
            <footer class="footer-main">
                <div class="footerLeft">
                <button class="footerBtn" onclick="window.location.href='forCoach.php';">Support FAQ</button>
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

</html>
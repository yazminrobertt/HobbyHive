<?php
    session_start();
    include("connect.php");

    // Get the activity ID from the URL
    $activityId = isset($_GET['activityId']) ? $_GET['activityId'] : '';
    echo "<script>console.log('Username: " . $username . "');</script>";

    if (!empty($_SESSION['username'])) {
        $isLoggedIn = true;
    } else {
        $isLoggedIn = false;
    }

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        // Query to check if the logged-in user has children
        $query = "SELECT COUNT(CHILD_ID) AS childCount FROM child WHERE PARENT_USERNAME = '$username'";
        $result = mysqli_query($conn, $query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $childCount = $row['childCount'];
        } else {
            $childCount = 0; 
        }
    } else {
        $childCount = 0;
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

        $coachQuery = "SELECT coach.COACH_NAME, coach.COACH_PROPIC
        FROM coach
        WHERE coach.COACH_USERNAME = ?";
        $stmt = $conn->prepare($coachQuery);
        $stmt->bind_param("s", $activity['COACH_USERNAME']);
        $stmt->execute();
        $coachResult = $stmt->get_result();

        if ($coachResult->num_rows > 0) {
            $coach = $coachResult->fetch_assoc();
            $coachName = $coach['COACH_NAME'];
            $coachPic = base64_encode($coach['COACH_PROPIC']); // Convert the BLOB to base64
        } else {
            $coachName = "Coach not found";
            $coachPic = null; // Default if no picture is found
        }

        // Fetch package details
        $packageQuery = "SELECT PRICING_TYPE, PRICE
        FROM offered_pricing
        WHERE OFFER_ID = ?";
        $stmt = $conn->prepare($packageQuery);
        $stmt->bind_param("s", $activityId);
        $stmt->execute();
        $packageResult = $stmt->get_result();

        $packages = [];
        while ($row = $packageResult->fetch_assoc()) {
            $packages[] = $row;
        }

        // Fetch session details
        $sessionQuery = "SELECT OT_DAY, OT_STARTTIME, OT_ENDTIME, OT_PAX, OT_TYPE
        FROM offered_time
        WHERE OFFER_ID = ?
        AND IS_REMOVED=0";
        $stmt = $conn->prepare($sessionQuery);
        $stmt->bind_param("s", $activityId);
        $stmt->execute();
        $sessionResult = $stmt->get_result();

        $sessions = [];
        while ($row = $sessionResult->fetch_assoc()) {
            $sessions[] = $row;
        }
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
?>


<!DOCTYPE html>
<html>
    <head>
        <?php include('header.php'); ?>
        <title>Activity Details</title>
    </head>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin-top: 70px; /* Reduced margin */
            padding: 0;
            background-color: white;
        }
        .container {
            max-width: 1000px; /* Reduced max-width */
            margin: 30px auto; /* Reduced margin */
            padding: 15px; /* Reduced padding */
            border: 1px solid #82b1ff;
            border-radius: 8px; /* Slight rounding for cleaner look */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }
        .acName {
            font-size: 30px; /* Reduced font size */
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px; /* Reduced margin */
            text-align: center;
            color: #004085;
        }
        .top-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px; /* Reduced gap */
        }
        .coPicSec {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .coPic {
            width: 150px; /* Smaller size */
            height: 150px;
            background-color: #d3d3d3;
            border-radius: 50%;
            margin-bottom: 8px; /* Reduced margin */
        }
        .forCoDesc {
            max-width: 700px; /* Match container size */
            width: 100%;
        }
        .forDesc {
            font-size: 14px; /* Reduced font size */
            display: flex;
            text-align: justify;
            padding: 30px; /* Reduced padding */
        }
        .coName {
            padding-top: 8px; /* Reduced padding */
            font-weight: bold;
            text-decoration: underline;
            color: #333;
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
        body .packSect, .seshSect, .rateSect {
            margin-top: 20px; /* Reduced margin */
            max-width: 900px; /* Reduced max-width */
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 50px;
            margin-right: 50px;
        }
        .packHeader, .seshHeader, .rateHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 2px solid black; /* Reduced thickness */
        }
        .packHeader, .seshHeader, .rateHeader h4 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 8px; /* Reduced margin */
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
        .btnBookNow {
            margin: 0 auto; /* Center the button */
            display: block;
            width: calc(100% - 100px);  /* Match width proportions with the review cards */
            background: linear-gradient(135deg, #448aff, #005ecb);
            color: white;  /* Same text color as review headers */
            border: none;
            padding: 10px 15px; /* Slightly larger padding for better balance */
            font-size: 14px; /* Maintain consistent font size */
            font-weight: bold;
            cursor: pointer;
            border-radius: 10px; /* Match border radius of review cards */
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.15); /* Match shadow with review cards */
            transition: all 0.3s ease-in-out; /* Smooth hover effect */
            text-align: center; /* Center-align text */
        }
        .btnBookNow:hover {
            background: linear-gradient(135deg, #3b7dd6, #004ba0); /* Darker shade on hover */
            transform: translateY(-3px); /* Slight hover lift */
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); /* Enhanced shadow */
        }
        .acPics {
            width: 700px; /* Adjust the width to fit within your layout */
            height: 300px; /* Set a fixed height */
            background-color: #f5f5f5;
            border-radius: 10px;
            margin: 20px auto; /* Center the slider horizontally */
            overflow: hidden; /* Prevent overflowing content */
            position: relative; /* Ensure it stays within bounds */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
        }
        .slider-container {
            width: 100%; /* Match the width of .acPics */
            height: 100%; /* Match the height of .acPics */
            position: relative;
            overflow: hidden;
        }
        .slider-container .slide {
            display: none; /* Hide slides by default */
            width: 100%;
            height: 100%;
        }
        .slider-container .slide img {
            width: 100%; /* Make images fill the width */
            height: 100%; /* Make images fill the height */
            object-fit: cover; /* Ensure images fit the container */
            border-radius: 10px; /* Apply rounded corners */
        }
        .slider-container .active {
            display: block; /* Show the active slide */
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
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
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
    </style>

    <script>
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

    
    <body>
        <div class="main">
            <div class="container">
                <div class="acName">
                    <?php echo htmlspecialchars($activity['OFFER_NAME']); ?>
                </div>

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

                    <div class="packSect" style="margin-bottom:30px">
                        <div class="packHeader">
                            <h4><b>General Details</b></h4>
                        </div>
                    </div>
                    
                <div class="top-section">   
                    <table class="forCoDesc">
                        <tr>
                            <td>
                            <div class="coPicSec">
                                <div class="coPic" 
                                    style="background-image: url('data:image/jpeg;base64, <?= $coachPic ?: '' ?>'); 
                                            background-size: cover; 
                                            background-position: center;">
                                </div>
                            </div>

                            </td>
                            <td>
                                <div class="forDesc">
                                    <p><?php echo htmlspecialchars($activity['OFFER_DESC']); ?></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">
                                <a href="coachUsProfile.php?coachId=<?php echo urlencode($activity['COACH_USERNAME']); ?>" class="coName"><?php echo htmlspecialchars($coachName); ?></a>
                            </td>
                        </tr>
                    </table>
                </div>

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
                            <?php echo htmlspecialchars($session['OT_TYPE']); ?></div>
                        </th>
                    </tbody>
                </table>
                <?php } ?>

                <!-- <form action="<?php echo $isLoggedIn ? 'activityBooking.php' : 'login.html'; ?>" method="GET">
                    <input type="hidden" name="offer_id" value="<?php echo htmlspecialchars($activityId); ?>">
                    <button type="submit" class="btnBookNow">Book Now</button>
                </form> -->

                <form action="<?php echo $isLoggedIn ? 'activityBooking.php' : 'login.html'; ?>" method="GET" onsubmit="return handleRedirection()">
                    <input type="hidden" name="offer_id" value="<?php echo htmlspecialchars($activityId); ?>">
                    <button type="submit" class="btnBookNow">Book Now</button>
                </form>

                <div class="rateSect" style="margin-bottom:3px;">
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
        // function handleRedirection() {
        //     <?php if (!$isLoggedIn): ?>
        //         alert('You need to log in first!');
        //         window.location.href = 'login.html'; 
        //         return false; 
        //     <?php endif; ?>
        //     return true;
        // }
    function  handleRedirection() {
        <?php if (!$isLoggedIn): ?>
            alert('Please log in first.');
            window.location.href = 'login.html'; 
            return false; // Prevent booking
        <?php elseif ($childCount == 0): ?>
            alert('Please add child first.');
            window.location.href = 'childAdd.php'; 
            return false; // Prevent booking
        <?php else: ?>

            return true;
        <?php endif; ?>
    }



    </script>
</html>
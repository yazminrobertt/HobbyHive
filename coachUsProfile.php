<?php 
    session_start();
    include('connect.php'); 

    if (!empty($_SESSION['username'])) {
        $isLoggedIn = true;
    } else {
        $isLoggedIn = false;
    }

    $coachId = isset($_GET['coachId']) ? $_GET['coachId'] : '';


    // Fetch parent data
    $sql = "SELECT * FROM coach WHERE COACH_USERNAME = '$coachId'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $coach_username = $row['COACH_USERNAME'];
        $coach_name = $row['COACH_NAME'];
        $coach_phone = $row['COACH_PHONE'];
        $coach_email = $row['COACH_EMAIL'];
        $coach_age = $row['COACH_AGE'];
        $coach_gender = $row['COACH_GENDER'];
        $coach_about = $row['COACH_ABOUT'];
        // $coach_pic = $row['COACH_PROPIC'];
        // $coach_pic = !empty($row['COACH_PROPIC']) ? $row['COACH_PROPIC'] : 'add.png';
        $coach_pic = !empty($row['COACH_PROPIC']) ? "data:image/jpeg;base64," . base64_encode($row['COACH_PROPIC']) : 'add.png';
    } else {
        echo "<script>alert('No data found.');</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT ACHIEVE_DESC FROM coach_achievement WHERE COACH_USERNAME = ?");
    $stmt->bind_param("s", $coachId); // Using the current username to fetch achievements
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare the data to display achievements
    $achievements = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $achievements[] = $row['ACHIEVE_DESC'];
        }
    } else {
        $achievements[] = "No achievements to display."; // In case there are no achievements
    }

    $stmt = $conn->prepare("SELECT CREDIT_DESC FROM coach_accreditation WHERE COACH_USERNAME = ?");
    $stmt->bind_param("s", $coachId); // Using the current username to fetch achievements
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare the data to display achievements
    $credits= [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $credits[] = $row['CREDIT_DESC'];
        }
    } else {
        $credits[] = "No accreditations to display."; // In case there are no achievements
    }

    $stmt = $conn->prepare("SELECT CREDIT_PIC FROM coach_accreditation WHERE COACH_USERNAME = ?");
    $stmt->bind_param("s", $coachId); // Using the current username to fetch achievements
    $stmt->execute();
    $result = $stmt->get_result();
    $images = [];

    while ($row = $result->fetch_assoc()) {
        $images[] = 'data:image/jpeg;base64,' . $row['CREDIT_PIC']; // Storing the image as a base64 encoded string
    }

    // If no images are found, push a message to indicate that
    if (empty($images)) {
        $images[] = "No accreditations to display."; // This ensures there's a fallback
    }
    $stmt->close();

    // Fetch offered activities for the logged-in coach
    $sql = "SELECT * FROM offered_activity WHERE COACH_USERNAME = '$coachId'";
    $activitiesResult = $conn->query($sql);
    $activities = [];

    while ($activity = $activitiesResult->fetch_assoc()) {
        $activities[] = $activity;
    }
?>

<!DOCTYPE html>

    <head>
        <?php include("header2.php"); ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
        <title>Coach Profile</title>
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
            padding-top: 100px; 
        }
        main {
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
        .roundedBox {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 15px;
            padding: 20px;
            max-width: 950px;
            width: 100%;
            height: 650px;
            margin: 5px auto;
            display: flex; 
            align-items: center; 
            justify-content: flex-start; 
            gap: 20px;
        }
        .image-container {
            position: relative;
            width: 250px; 
            height: 250px; 
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%; 
            overflow: hidden;
            background-color: #ddd; 
            align-self: center; 
            margin-right: 10px;
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%; 
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); 
        }
        .roundedBox2 {
            border-radius: 15px;
            padding: 20px;
            flex-grow: 1; 
            max-width: unset; 
            background-color: white;
            box-shadow: 0 4px 8px rgba(72, 161, 255, 0.3);
            color: #004085;
            border: 1px solid #ddd;
        }
        .forAgeGender {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;  /* Reduced margin-top */
        }
        .forAgeGender th, .forAgeGender td {
            padding: 5px 10px;  /* Smaller padding to reduce the row gap */
            text-align: left;
        }
        .smallInput {
            display: block;
            width: 100%;
            padding: 5px 8px;
            font-size: 12px;
            color: #004085;
            text-align: left;
            background-color: transparent;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .roundedBox2 .form-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            align-items: center;
        }
        .roundedBox2 .form-group label {
            font-size: 12px;
            font-weight: bold;
            color: #004085;
            width: 150px;
            text-align: left;
        }
        .roundedBox2 .form-control {
            font-size: 12px;
            font-weight: normal;
            padding: 10px;
            background-color: transparent; /* Keeps background clean */
            border: 1px solid #ddd; /* Adds a subtle border for clarity */
            border-radius: 5px;
            color: #004085;
            width: calc(100% - 170px);
            margin-left: 15px;
            text-align: left;
            pointer-events: none;
        }          /* Ensure inputs fill the available width */
        .funcSection {
            display: flex;
            justify-content: space-around; 
            margin-bottom: 20px;
            padding: 5px 0; 
        }
        .funcSection button {
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
        .funcSection button:active {
            border-bottom: 3px solid #448aff; 
            color: #448aff;
            transform: scale(0.98); 
        }
        body .activitySection, body .creditSection,body .certSection,body .achieveSection,body .saleSection,body .reviewSection{
            margin-top: 20px;
            padding: 0 20px;
            max-width: 1130px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .activityHeader, .creditHeader, .certHeader, .achieveHeader, .saleHeader, .reviewHeader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-bottom: 3px solid black;
        }
        .activityHeader h2,.creditHeader h2,.certHeader h2, .achieveHeader h2, .saleHeader h2, .reviewHeader h2 {
            flex-grow: 1;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
            padding-bottom: 2px;
        }
        #offActivities, #portfolio, #report {
            border: 2px solid #448aff; 
            border-radius: 8px;
            padding-top: 5px; /* Reduced padding to bring content closer */
            padding-left: 10px; /* Optional: reduce left padding */
            padding-right: 10px; /* Optional: reduce right padding */
            padding-bottom: 10px; /* Optional: reduce bottom padding */
            border-radius: 10px; /* Rounded corners */
            max-width: 1000px; /* Ensure a maximum width */
            width: 100%; /* Make it responsive */
            margin: 0 auto; 
        }
        .allAcc{
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .activityCard {
            margin-top: 20px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            text-align: left;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .activityCard:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Stronger shadow on hover */
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }
        .activityImage {
            width: 250px;            
            height: 150px;         
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat;
            border-radius: 8px; 
            overflow: hidden;  
        }
        .activityDetails {
            padding: 15px;
        }
        .activityDetails p {
            margin: 5px 0;
            font-size: 14px;
        }
        .noActivity {
            margin-top: 20px;
            text-align: center;
            font-size: 16px;
            color: #888;
        }
        .allAchieve, .allCredit, .allCert {
            display: flex;
            flex-direction: column;
            margin-top: 8px;
            padding: 10px;
            border-radius: 5px;
        }
        .achieveItem, .creditItem {
            background-color: #f0f0f0; 
            padding: 8px;
            margin-bottom: 6px;
            border-left: 3px solid #007bff; 
            border-radius: 4px;
            box-shadow: none; 
            font-size: 12px; 
            transition: all 0.2s ease-in-out;
        }
        .achieveItem:hover, .creditItem:hover {
            background-color: #e0e0e0; 
            border-left: 3px solid #0056b3; 
        }
        .achieveDesc, .creditDesc {
            font-size: 12px; 
            font-weight: 400; 
            color: #333; 
            line-height: 1.5; 
        }
        .no-achievements {
            font-size: 12px; 
            color: #999; 
            text-align: center;
            margin-top: 10px;
        }
        .carousel-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 400px; /* Adjust height of the wrapper */
            transform: scale(1); /* Scales the entire carousel smaller */
            transform-origin: center; /* Ensures scaling happens from the center */
        }
        .carousel-container {
            position: relative;
            width: 800px; /* Smaller width */
            height: 400px; /* Smaller height */
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            border: 2px solid #ddd;
        }
        .carousel {
            display: flex;
            transition: transform 0.5s ease;
        }
        .carousel-item {
            min-width: 33.33%; /* Adjusted to show 3 images at once */
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        .carousel-item img {
            width: 100%; /* Ensure images fit within their container */
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }
        .carousel-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 16px;
            padding: 8px;
            cursor: pointer;
            border: none;
            border-radius: 50%;
            z-index: 10;
            transition: background-color 0.3s ease;
        }
        .carousel-button.prev {
            left: 10px;
        }
        .carousel-button.next {
            right: 10px;
        }
        .carousel-button:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
        
    </style>

    <script>
        $(document).ready(function(){
            // Initially show Report section, hide others
            $("#portfolio").show();
            $("#offActivities").hide();

            // Apply initial border-bottom to the Report button
            $(".btnPort").css("border-bottom", "2px solid rgb(0, 0, 0)");

            // Click event for the Portfolio button
            $(".btnPort").click(function(){
                $("#portfolio").show();
                $("#offActivities").hide(); 
                $(".btnPort").css("border-bottom", "2px solid rgb(0, 0, 0)");
                $(".btnActivities").css("border-bottom", "none");
            });

            // Click event for the All Activities button
            $(".btnActivities").click(function(){
                $("#offActivities").show();
                $("#portfolio").hide();
                $(".btnActivities").css("border-bottom", "2px solid rgb(0, 0, 0)");
                $(".btnPort").css("border-bottom", "none");
            });

        });

        let currentIndex = 0;
        function moveSlide(direction) {
            const slides = document.querySelectorAll('.carousel-item');
            const totalSlides = slides.length;
            const itemsPerSlide = 3; // Number of items to show per slide (change as necessary)
            
            currentIndex = (currentIndex + direction + Math.ceil(totalSlides / itemsPerSlide)) % Math.ceil(totalSlides / itemsPerSlide);
            
            const newTransformValue = -currentIndex * 100; 
            document.querySelector('.carousel').style.transform = `translateX(${newTransformValue}%)`;
        }

        function loadCarousel(images) {
            const carousel = document.querySelector('.carousel');
            images.forEach(imageSrc => {
                const item = document.createElement('div');
                item.classList.add('carousel-item');
                item.innerHTML = `<img src="${imageSrc}" alt="Certificate Image">`;
                carousel.appendChild(item);
            });
        }

        fetch('your_php_endpoint.php')
            .then(response => response.json())
            .then(data => loadCarousel(data))
            .catch(error => console.error('Error loading carousel images:', error));
    </script>

    <body>
        <div class="roundedBox">
            <div class="image-container">
                <img src="<?php echo $coach_pic; ?>" alt="Coach Image">
            </div>

                <!-- Form container on the right -->
            <div class="roundedBox2">
                <div class="form-group">
                    <label for="coUsername">Username:</label>
                    <span id="coUsername" class="form-control" name="coUsername"><?php echo $coach_username; ?></span>
                </div>
                <div class="form-group">
                    <label for="coName">Name:</label>
                    <span id="coName" class="form-control" name="coName"><?php echo $coach_name; ?></span>
                </div>
                <div class="form-group">
                    <label for="coEmail">Email:</label>
                    <span id="coEmail" class="form-control" name="coEmail"><?php echo $coach_email; ?></span>
                </div>
                <div class="form-group">
                    <label for="coPhone">Phone Number:</label>
                    <span id="coPhone" class="form-control" name="coPhone"><?php echo $coach_phone; ?></span>
                </div>
                <div class="form-group">
                    <label for="coAbout">About Me:</label>
                    <span id="coAbout" class="form-control" name="coAbout"><?php echo $coach_about; ?></span>
                </div>
                <table class="forAgeGender">
                    <tr>
                        <th>Age</th>
                        <th>Gender</th>
                    </tr>
                    <tr>

                        <td>
                            <div class="form-group">
                                <span id="coAge" class="smallInput" name="coAge"><?php echo $coach_age; ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <span id="coGender" class="smallInput" name="coGender"><?php echo $coach_gender; ?></span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- for sections in profile -->
        <div class="funcSection" style="margin-top: 20px; margin-bottom:50px;">
            <button class="btnPort">Portfolio</button>
            <button class="btnActivities">All Activities</button>
        </div>

        <div id="offActivities">
            <div class="activitySection">
                <div class="activityHeader">
                    <h2> Activities</h2>
                </div>
            </div>

            <div class="allAcc">
                <?php
                    // Fetch offered activities for the coach
                    $sqlActivities = "SELECT OFFER_ID, OFFER_NAME FROM offered_activity WHERE COACH_USERNAME = '$coach_username' AND IS_AVAILABLE=1";
                    $resultActivities = $conn->query($sqlActivities);

                    if ($resultActivities->num_rows > 0):
                        while ($activity = $resultActivities->fetch_assoc()):
                            // Fetch the Base64 image for the current activity
                            $offerId = $activity['OFFER_ID'];
                            $sqlImage = "SELECT OP_PIC FROM offered_pic WHERE OFFER_ID = '$offerId' LIMIT 1";
                            $resultImage = $conn->query($sqlImage);
                            $imageData = $resultImage->fetch_assoc();
                            
                            // Check if image exists and is in Base64 format
                            if ($imageData && !empty($imageData['OP_PIC'])) {
                                // If image exists in the DB, use it as Base64 image
                                $imageSrc = 'data:image/jpeg;base64,' . $imageData['OP_PIC'];
                            } else {
                                // Default image if not available
                                $imageSrc = 'default-image.jpg';
                            }
                            ?>
                            <div class="activityCard" onclick="window.location.href='activityDisplay.php?activityId=<?php echo urlencode($offerId); ?>'">
                                <div class="activityImage" style="background-image: url('<?php echo $imageSrc; ?>');"></div>
                                <div class="activityDetails">
                                    <p><b><?php echo htmlspecialchars($activity['OFFER_NAME']); ?></b></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="noActivity">No activities available.</div>
                    <?php endif; ?>
            </div>
        </div>

        <div id="portfolio">
            <!-- credit sect -->
            <div class="creditSection">
                <div class="creditHeader">
                    <h2>Accreditations</h2>
                </div>
            </div>

            <div class="allCredit">
                <?php
                    foreach ($credits as $credit) {
                        echo '<div class="creditItem">';
                        echo '<p class="creditDesc">' . htmlspecialchars($credit) . '</p>';
                        echo '</div>';
                    }
                ?>
            </div>
            
            <!-- cert sect -->
            <div class="certSection">
                <div class="certHeader">
                    <h2>Certificates</h2>
                </div>
            </div>

            <?php if ($images[0] !== "No accreditations to display."): ?>
                <div class="carousel-wrapper">
                    <div class="carousel-container">
                        <div class="carousel">
                            <?php foreach ($images as $image): ?>
                                <div class="carousel-item">
                                    <img src="<?php echo $image; ?>" alt="Accreditation Image">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-button prev" onclick="moveSlide(-1)">&#10094;</button>
                        <button class="carousel-button next" onclick="moveSlide(1)">&#10095;</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="allCert">
                    <div class="creditItem">
                        <p class="creditDesc">No certificates to display.</p>
                    </div>
                </div>
            <?php endif; ?>
            <script src="carousel.js"></script>

            <div class="achieveSection">
                <div class="achieveHeader">
                    <h2>Achievements</h2>
                </div>
            </div>

            <div class="allAchieve">
                <?php
                    foreach ($achievements as $achievement) {
                        echo '<div class="achieveItem">';
                        echo '<p class="achieveDesc">' . htmlspecialchars($achievement) . '</p>';
                        echo '</div>';
                    }
                ?>
            </div>
        </div>

        <div class="classfooter" style="margin-top:20px;">
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

    <script type="text/javascript">
        google.charts.load('current', { packages: ['corechart', 'pie','bar'] });

        function drawChart() {
            // Get selected activity ID and date range
            var activityId = document.getElementById('activity').value;
            var startDate = document.getElementById('startDate').value;
            var endDate = document.getElementById('endDate').value;

            if (activityId === "") {
                alert("Please select an activity.");
                return;
            }

            // Ajax to fetch ratings for the selected activity and date range
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'getRatings.php?activityId=' + activityId + '&startDate=' + startDate + '&endDate=' + endDate, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);

                    // If no ratings found
                    if (response.error) {
                        document.getElementById('graphReview').innerHTML = "<p>No ratings left for this activity.</p>";
                        return;
                    }

                    // Create the chart data
                    var data = google.visualization.arrayToDataTable([
                        ['Rating', 'Number of Ratings'],
                        ['1 Star', response.ratings[0]],
                        ['2 Star', response.ratings[1]],
                        ['3 Star', response.ratings[2]],
                        ['4 Star', response.ratings[3]],
                        ['5 Star', response.ratings[4]]
                    ]);

                    // Chart options with normal colors
                    var options = {
                        title: 'Rating Distribution for Selected Activity',
                        pieSliceText: 'percentage',
                        is3D: true,
                        slices: {
                            0: { offset: 0.1, color: '#FF9999' }, // Light Red for 1-star
                            1: { offset: 0.1, color: '#FFCC99' }, // Light Orange for 2-star
                            2: { offset: 0.1, color: '#99FF99' }, // Light Green for 3-star
                            3: { offset: 0.1, color: '#66CCFF' }, // Light Blue for 4-star
                            4: { offset: 0.1, color: '#CCCCFF' }  // Light Purple for 5-star
                        }
                    };

                    // Create and draw the Pie Chart
                    var chart = new google.visualization.PieChart(document.getElementById('graphReview'));
                    chart.draw(data, options);
                }
            };
            xhr.send();
        }

        document.getElementById('activity').addEventListener('change', function(e) {
            e.preventDefault();
        });

        google.charts.setOnLoadCallback(drawChart);

        $(document).ready(function () {
            drawBookingChart();
        });

        function drawBookingChart() {
            // PHP data passed to JavaScript
            var bookingData = <?php echo json_encode($bookingData); ?>;
            
            // Debugging: Check if data is passed correctly to JavaScript
            console.log(bookingData);

            // Create a data table for the pie chart
            var chartData = new google.visualization.DataTable();
            chartData.addColumn('string', 'Offer Name');
            chartData.addColumn('number', 'Booking Count');

            // Add rows to the data table from PHP data
            bookingData.forEach(function (item) {
                chartData.addRow([item.OFFER_NAME, parseInt(item.booking_count)]);
            });

            // Chart options with custom colors for each slice
            var options = {
                title: 'Booking Counts for Offered Activity',
                pieSliceText: 'percentage',
                is3D: true,
                slices: {},  // Define slice colors dynamically
                width: 600,  // Set chart width (adjust to desired size)
                height: 300,  // Set chart height (adjust to desired size)
            };

            // Assign custom colors and offsets for each slice
            var colors = ['#FF9999', '#FFCC99', '#99FF99', '#66CCFF', '#CCCCFF'];  // Colors for slices
            bookingData.forEach(function (item, index) {
                options.slices[index] = {
                    offset: 0.1,  // Slightly offset each slice
                    color: colors[index % colors.length]  // Cycle through colors
                };
            });

            // Draw the pie chart
            var chart = new google.visualization.PieChart(document.getElementById('graphActivity'));
            chart.draw(chartData, options);
        }
    </script>


</html>
